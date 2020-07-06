<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" http-equiv="Content-Type" name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="MPStyles.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
</head> 
<body style="background-color: transparent; color:white; vertical-align: top; display: block" >

<input type="file" id="xmlfile" style="display:none" /> 
	<canvas id="elv" width="1214" height="62">
	</canvas><canvas id="btn" width="62" height="62">
	</canvas><canvas id="bmap" width="1278" height="654" >
	</canvas><script>

		// set up a bunch of constant definitions at the outset to simplify 
		// adjusting if needed.
		// first set define aspects of the map style buttons.
		const textoffsetx = 12; // how far all button text is offset from top
		const textoffsety = 14; // how far all button text is offset from left

		// details for first button
		const link1Text = "Atlas"; // text
		const link1X = 0; // position
		const link1Y = 0;
		const link1Height = 21; // size
		const link1Width = 63;

		// repeat for other buttons.
		const link2Text = "Road";
		const link2X = 0;
		const link2Y = 20;
		const link2Height = 21;
		const link2Width = 63;

		// repeat for other buttons.
		const link3Text = "Satellite";
		const link3X = 0;
		const link3Y = 40;
		const link3Height = 21;
		const link3Width = 63;
		
		// set up params from the URL for use in the rest of the script
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);

		// These define conversion factors to convert from the .fit x/y lat/long to
		//  equivalent map pixels, based on 2048x2048 map images with 0,0 being top left 
		const xfactor = 17200; // this is multipler to convert to latlong to pixels
		const xoffset = 169.9255; // this is offset to convert latlong to to pixels
		const yfactor = -18200; // this is multipler to convert latlong to to pixels
		const yoffset = 19.0305	; // this is offset to convert latlong to to pixels
		// not sure why x/y are different, but they are ... maybe the map images are squished slightly?

		// sanity limits on values
		const xhilim = 2007; // the map has wide boundaries with no roads
		const xlolim = 41; // and limiting this makes some other stuff easier
		const yhilim = 2007; // so we use 98% of 2048= 2007
		const ylolim = 41; // and 2048-2007=41
		const zhilim = 50000; // just sanity imposed here - you're not 50km in the air
		const zlolim = -5000; // or 5km underground 
		
		// metric/imperial conversion factors.
		const meters2feet = 3.28084; // this is multipler for conversion to either imperial
		const km2mi = 0.621371; // this is multipler for conversion to either imperial
		const earthRadius = 6371; // in km
		const elvAxMxLabelX = 2; // pixels from left border for maximum elevation label on z axis
		const elvAxMxLabelY = 10; //  pixels from top border for maximum elevation label on z axis
		const elvAxMnLabelX = 2; // pixels from left border for minimum elevation label on z axis
		const elvAxMnLabelY = 60; // pixels from top border for minimum elevation label on z axis

		const elvDistLabelX = 1130;
		const elvDistLabelY = 15;
		const elvTimeLabelX = 1130;
		const elvTimeLabelY = 30;
		const elvAsceLabelX = 1130;
		const elvAsceLabelY = 45;
		const elvDescLabelX = 1130;
		const elvDescLabelY = 60;

		const defaultMaptype = "atlas"; // just setting a default
		const defaultMet = "Metric"; // just setting a default
		
		const atlasPng = 'images/map_atls.png'; // file source for atlas map
		const roadPng = 'images/map_road.png'; // file source for road map
		const satlPng = 'images/map_satl.png'; // file source for satellite map

		/// COLORS!!! woo.  Lots of colors for canvas elements defined here.
		const atlasBg = "#0fa8d2"; // background color for atlas map
		const atlasLn = "#0000ff"; // line color for atlas map
		const roadBg = "#1862ad"; // background color for road map
		const roadLn = "#ff0000"; // line color for road map
		const satlBg = "#143d6b"; // background color for satellite map
		const satlLn = "#ff00ff"; // line color for satellite map
		const btnAtlsColr = 'rgb(128,185,35)'; // color of bg for atlas
		const btnRoadColr = 'rgb(146, 210, 187)'; // color of bg for road
		const btnSatlColr = 'rgb(0, 153, 0)'; // color of bg for sat
		const elvAxColorAtls = "#ffffff"; // color of the axis labels in elevation chart
		const elvLnColorAtls = "#121280"; // color of the line in elevation chart
		const elvFlColorAtls = "#707070"; // color of the graph fill in elevation chart
		const elvBgColorAtls = "#404040"; // color of the background in elevation chart
		const elvAxColorRoad = "#ffffff"; // color of the axis labels in elevation chart
		const elvLnColorRoad = "#121280"; // color of the line in elevation chart
		const elvFlColorRoad = "#707070"; // color of the graph fill in elevation chart
		const elvBgColorRoad = "#404040"; // color of the background in elevation chart
		const elvAxColorSatl = "#ffffff"; // color of the axis labels in elevation chart
		const elvLnColorSatl = "#121280"; // color of the line in elevation chart
		const elvFlColorSatl = "#707070"; // color of the graph fill in elevation chart
		const elvBgColorSatl = "#404040"; // color of the background in elevation chart
	
	// variables
		var isLink1,isLink2,isLink3,btnc,btnctx,link1URL,link2URL,link3URL,elvc,elvctx,mcanvas,ctx,img,xmlDoc,gpxfilename,xhttp,zfactor2,zoffset2,tfactor2,toffset2,zoomfactorx,zoomfactory,zoomfactorxy,translatefactorx,translatefactory,img,elunit,dstunit,mapbg,mapline,route,maptype,met,i,cmlDist,cmlTime,cmlElev,cmlDesc,x,lastTimestamp,thisTimestamp,lastLat,thislat,lastLon,thisLon,thisElev,lastElev,xmin,xmax,ymin,ymax,zmin,zmax,tmin,tmax,elvAxColor,elvLnColor,elvFlColor,elvBgColor,xmlLoaded,imgLoaded;
	
	// array variables:
		let xarray = []; 
		let yarray = []; 
		let zarray = []; 
		let tarray = []; 
		let darray = []; // new array for cumulative distance
		let iarray = []; // future use: Instead of just using the "t" array, build an index array based on either t or cumulative distance
		// based on user selection.

		// initializing some containers and variables that will be referenced by triggered functions so must be initialized globally
		img = new Image();   // Create new img element
		xhttp = new XMLHttpRequest();
		xmlLoaded = 0;
		imgLoaded = 0;
		mcanvas = document.getElementById("bmap"); // map canvas
		elvc = document.getElementById("elv"); // elevation profile canvas
		isLink1 = false; // indicates whether mouse position currently hovering is over link 1
		isLink2 = false; // indicates whether mouse position currently hovering is over link 2
		isLink3 = false; // indicates whether mouse position currently hovering is over link 3
		
	/* -- list of uninitialized variables and their usage:
			zfactor2 = 1; // zfactor2&zoffset2 are used to scale from "real" numbers to pixels.  Calculated, initial is meaningless.
			zoffset2 = 0; // zfactor2&zoffset2 are used to scale from "real" numbers to pixels.  Calculated, initial is meaningless.
			tfactor2 = 1; // and tfactor/offset converts the time units into a scale of 0-240 for various purposes.  Calculated, initial is meaningless.
			toffset2 = 0; // .  Calculated, initial is meaningless.
			zoomfactorx = 1; // handles x/y zoom for main panel.  Calculated, initial is meaningless.
			zoomfactory = 1; // handles x/y zoom for main panel.  Calculated, initial is meaningless.
			zoomfactorxy = 1; // handles x/y zoom for main panel.  Calculated, initial is meaningless.
			translatefactorx = 0; // shift to center for x.  Calculated, initial is meaningless.
			translatefactory = 0; // shift to center for y.  Calculated, initial is meaningless.
			mapbg = "#ffffff"; // background color for map canvas
			mapline = "#ffffff"; // color for map line
			i = 0; // gp counter
			cmlDist = 0; // cumulative distance
			cmlTime = 0; // cumulative time
			cmlElev = 0; // cumulative ascent
			cmlDesc = 0; // cumulative descent
			xmlDoc = 0; // holder for xmldoc
			x = 0; // xml file referencer
			lastTimestamp = 0; // most recent point timestamp
			thisTimestamp = 0; // current point timestamp
			lastLat = 0; // most recent lat
			thislat = 0; // current lat
			lastLon = 0; // most recent long
			thisLon = 0; // current long
			lastElev = 0; // most recent elevation
			thisElev = 0; // curent elevation
			xmin = 0; // min and max on each axis
			xmax = 0; // ..
			ymin = 0; // ..
			ymax = 0; // ..
			zmin = 0; // ..
			zmax = 0; // ..
			tmin = 0; // ..
			tmax = 0; // ..
	*/

	// now function defines.


	// To monitor for clicks on the map change tab.  
	// credit for much of this part of code: http://www.authorcode.com/how-to-create-hyper-link-on-the-canvas-in-html5/
	// modified for canvas-relevant position rather than absolute
        function CanvasMouseMove(e) {
            var x, y;
			if (e.pageX || e.pageY) { 
			  x = e.pageX;
			  y = e.pageY;
			}
			else { 
			  x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			  y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
			} 
			x -= btnc.offsetLeft;
			y -= btnc.offsetTop;
 
            if (x >= link1X && x <= (link1X + link1Width) 
                    && y >= link1Y && y <= (link1Y + link1Height)) {
                document.body.style.cursor = "pointer";
                isLink1 = true;
            }
            else {
                document.body.style.cursor = "";
                isLink1 = false;
            }
            if (x >= link2X && x <= (link2X + link2Width) 
                    && y >= link2Y && y <= (link2Y + link2Height)) {
                document.body.style.cursor = "pointer";
                isLink2 = true;
            }
            else {
                document.body.style.cursor = "";
                isLink2 = false;
            }
            if (x >= link3X && x <= (link3X + link3Width) 
                    && y >= link3Y && y <= (link3Y + link3Height)) {
                document.body.style.cursor = "pointer";
                isLink3 = true;
            }
            else {
                document.body.style.cursor = "";
                isLink3 = false;
            }
        }

	// when a click is detcted, determine if it was on one of the map buttons.
	//  position determination based on CanvasMouseMove
	// if it was, reload with the selected map.
		function Link_click(e) {

			if (isLink1) {
				maptype = "atlas";
				procMapLoad();
			}
			if (isLink2) {
				maptype = "road";
				procMapLoad();
			}
			if (isLink3) {
				maptype = "satellite";
				procMapLoad();
			}
		}

	// getting distance based on standard earth-surface lat-long distance calc.  Result in kliometers.
		function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
			var R = earthRadius; // Radius of the earth in km
			var dLat = deg2rad(lat2-lat1);  // deg2rad below
			var dLon = deg2rad(lon2-lon1); 
			var lat1 = deg2rad(lat1);
			var lat2 = deg2rad(lat2);
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
			var d = R * c;
			return d;
		}

	// used in distance calc
		function deg2rad(deg) {
			return deg * (Math.PI/180)
		}

		img.onload = function () {
			imgLoaded = 1;
			img.draw;
			// this is probably a kludgy/klunky approach...
			// basically it needs both of these items processed before proceeding to this last draw
			// because the order of drawn objects on the map pane is critical.
			// so after each of the two, check load, and only run the final if the other has completed too.
			if(imgLoaded === 1 && xmlLoaded === 1) {
				var thm = xmlImageBothLoaded();
			};
		};


	// This function is triggered when a 'load' is complete on the xhttp object.  xhttp is our xml file container, so when this
	// is triggered it means the xml file load is complete and we can parse values into the array and cultivate/analyze the data.
		xhttp.onload = function () {
			if(route === null) {
			} else {
				// javascript with the libraries I'm using makes XML pretty effortless.
				// so -- Pick out all the trkpt items and extract specfiic attributes:
				xmlDoc = xhttp.responseXML;
				processXmlDoc();
			}
		};

	// if no file is passed as parameters, the user file button is enabled.
	// This function is triggered when a file is loaded.
	//  so far it seems to load a file, but passing the result to xml doc isn't working...
		$("#xmlfile").change(function(e){
			var selectedFile = document.getElementById("xmlfile").files[0];
			//You could insert a check here to ensure proper file type
			var reader = new FileReader();
			reader.onload = function(e){
				readXml=e.target.result;
				var parser = new DOMParser();
				xmlDoc = parser.parseFromString(readXml, "application/xml");
				processXmlDoc();
           }
			reader.readAsText(selectedFile);
       });

		function processXmlDoc() {
			x = xmlDoc.getElementsByTagName("trkpt");
			xarray = []; 
			yarray = []; 
			zarray = []; 
			tarray = []; 
			darray = [];
			iarray = [];
			for (i = 0; i < x.length; i++) { 

				// load values from xml formatted gpx file into array, store in variable first for some stuff
				thisLon=(x[i].getAttribute("lon") *1);
				thisLat=(x[i].getAttribute("lat") *1);
				thisElev=(x[i].getElementsByTagName("ele")[0].childNodes[0].nodeValue *1);
				// for time convert from standardized text into time value and preserve in var
				thisTimestamp = new Date(x[i].getElementsByTagName("time")[0].childNodes[0].nodeValue);

				if(i===0) { // first iteration: Initialize to zero.
					cmlDist = 0;
					cmlTime = 0;
					cmlElev = 0;
					cmlDesc = 0;
				} else {
					// thereafter, calculate the difference and add too the cumulative values.
					cmlTime=cmlTime+Math.abs(thisTimestamp-lastTimestamp);
					if(thisElev>lastElev){
						cmlElev=cmlElev+(thisElev-lastElev);
					} else if(thisElev<lastElev) {
						cmlDesc=cmlDesc+(lastElev-thisElev);
					}
					cmlDist = cmlDist + getDistanceFromLatLonInKm(lastLat,lastLon,thisLat,thisLon);
				}
				// then preserve current value as last in preparation for next cycle.
				lastTimestamp = thisTimestamp;
				lastElev = thisElev;
				lastLat = thisLat;
				lastLon = thisLon;

				// and then pop into arrays.
				// minor concern: I'm referencing i as an index explicitly at some points, but at others
				//  I'm doing non-indexed pushes into the array.  Could be a vector for bugs, seems like 
				//  it would be easy to make a mistake and get them out of sync.
				xarray.push((thisLon+(xoffset*1))*xfactor);
				yarray.push((thisLat+(yoffset*1))*yfactor);
				zarray.push(thisElev);
				tarray.push(cmlTime);

				// now impose sanity limits on values - just confirm they're not outside predefined limits.
				if(xarray[i] > xhilim) {xarray[i] = xhilim;};
				if(xarray[i] < xlolim) {xarray[i] = xlolim;};
				if(yarray[i] > yhilim) {yarray[i] = yhilim;};
				if(yarray[i] < ylolim) {yarray[i] = ylolim;};
				if(zarray[i] > zhilim) {zarray[i] = zhilim;};
				if(zarray[i] < zlolim) {zarray[i] = zlolim;};
			}

		//  *** ANALYZE DATASET ***
			// Preserve min and max values - we'll possibly use these to control zooming...
			xmin = Math.min.apply(null, xarray); 
			xmax = Math.max.apply(null, xarray);
			ymin = Math.min.apply(null, yarray);
			ymax = Math.max.apply(null, yarray);
			zmin = Math.min.apply(null, zarray);
			zmax = Math.max.apply(null, zarray);
			tmin = Math.min.apply(null, tarray);
			tmax = Math.max.apply(null, tarray);
			

			// determine convesion for z into pixel value (0-60)
			// need to calculate before zmax and zmin are adjusted for metric/imperial so 
			// that behavior is consistent.
			if( (Math.max.apply(null, zarray) - Math.min.apply(null, zarray)) < 2 ) {
				// if the values are too low to scale
				zfactor2 = 25; // just scale at the closest level allowed
				zoffset2 = -55; // and offset to near the bottom
			} else {
				// otherwise scale
				zfactor2 = 60/(Math.max.apply(null, zarray) - Math.min.apply(null, zarray));
				zoffset2 = 0-zmin; // (zmin*zfactor2)+20;
			}
			
			// convert from default metric values to imperial as needed.
			if (met === "Imperial") {
				zmin = zmin*meters2feet; // lowest elevation meters to feet
				zmax = zmax*meters2feet; // highest elevation meters to feet
				cmlElev = cmlElev*meters2feet; // total ascent meters to feet
				cmlDesc = cmlDesc*meters2feet; // total descent meters to feet
				cmlDist = cmlDist*km2mi; // total distance km to mi
				elunit = "ft"; // update unit tags
				dstunit = "mi";
			}
			// determine convesion for t into time driver (0-243 because 243 works with this width)
			// intent is to display in ~4 seconds, so time equates to 16.67 milliseconds, ~60fps
			tfactor2 = 243/(Math.max.apply(null, tarray) - Math.min.apply(null, tarray));
			toffset2 = 0-(tmin*tfactor2);
			
			// Now adjust to fit scales and zooms.
			for (var i=0, len=xarray.length; i<len; i++) { 
				zarray[i] = ((zarray[i]*1)+(zoffset2*1))*zfactor2;
				tarray[i] = ((tarray[i]*1)+(toffset2*1))*tfactor2;
			}

			// determine optimal scaling on each axis to fit most of the route onscreen
			zoomfactory = (655/(ymax - ymin));
			zoomfactorx = (1280/(xmax - xmin));

//  *********** FUTURE IMPROVEMENT:  Left justified and top justified sucks for map centering!!!
//   suggestion: Instead of just building in a small margin, for the non-driving axis (in other
//   words, if Y axis drives the zoom level, X axis would get this treatment) -- Just figure out
//   how much space is "left over" and offset the axis by half that amount to center.
//   But for now it's just left justified.
			if (zoomfactory < zoomfactorx) {
				zoomfactorxy = zoomfactory*.98;
				translatefactory = ymin*-1*zoomfactorxy;
				translatefactory = translatefactory + (655*.01); // this is NICE :) 
				translatefactorx = xmin*-1*zoomfactorxy; 
				translatefactorx = translatefactorx + (1280*.01);; // this is NOT CENTERED :(
			} else {
				zoomfactorxy = zoomfactorx*.98;
				translatefactorx = xmin*-1*zoomfactorxy; 
				translatefactorx = translatefactorx + (1280*.01); // this is NICE :) 
				translatefactory = ymin*-1*zoomfactorxy;
				translatefactory = translatefactory + (655*.01); // this is NOT CENTERED :(
			}

			//  *** ANALYZE DATASET COMPLETE ***
			xmlLoaded = 1;

			// this is probably a kludgy/klunky approach...
			// basically it needs both of these items processed before proceeding to this last draw
			// because the order of drawn objects on the map pane is critical.
			// so after each of the two, check load, and only run the final if the other has completed too.
			if(imgLoaded === 1 && xmlLoaded === 1) {
				var thm = xmlImageBothLoaded();
			};
		};
		

		function resetCanvas(inCvs)
		{
			var inCtx = inCvs.getContext("2d");
			inCtx.setTransform(1, 0, 0, 1, 0, 0);
			inCtx.clearRect(0,0, inCvs.width, inCvs.height);
		};

		function xmlImageBothLoaded(e) {
			// FINALLY all the prep is done - let's draw the route!  Static for now, but canvas will let me
			//  animate.  Plan is to use 't' value to drive animation?  Or better yet make an 'index' array that's
			//  populated based on either cumulative time or cumulative distance depending on parameters
			// Dynamic file loading: javascript loads files asynchronously.  This is key to note because draw
			//  ordering is key in what is displayed, so to enforce proper order we can't draw background or route
			//  until the image is loaded.
			// Note: normal approach to loading involves using html a hrefs and pointing to them, but the files are huge
			//  and there are different ones selected by user.  Load times were horrendous using that approach.  This is
			//  more complicated, but only loads the user-selected map file, cutting most of the load time.
			// So: This is the function that runs whe the file load is completed.

	//  *** MAP CANVAS HANDLING ***
			// clear for re-draw.
			resetCanvas(mcanvas);
			resetCanvas(elvc);
			ctx = mcanvas.getContext("2d"); // map canvas context
			elvctx = elvc.getContext("2d"); // elevation profile canvas context
	
			// this gave me fits ... I'd switched to do backgrounds, and when I started trying to allow re-loading, 
			//  it just wouldn't clear.  Standard canvas clearing methods are to draw white over them, but this context
			//  was set to background on subsequent draws ...  Just gotta reset it back to foreground :)
			ctx.globalCompositeOperation = 'source-over';
			elvctx.globalCompositeOperation = 'source-over';

			// zoom and pan!
			ctx.translate(translatefactorx,translatefactory);
			ctx.scale(zoomfactorxy, zoomfactorxy);  // same zoom factor to avoid weird aspect ratios
			ctx.drawImage(img, 0, 0);

			// now prep & draw route;
			ctx.beginPath();
			ctx.moveTo(xarray[0],yarray[0]);
			ctx.strokeStyle = mapline;
			ctx.lineWidth = 2;
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				ctx.lineTo(xarray[i],yarray[i]);
			}
			ctx.stroke();




	// **** Elevation profile drawing!
//  hm...  would be nice to allow x axis to be distance as well as time...
//  feature creep is just so exciting!
			// use path to trace the elevation line, then loop back along the bottom edge of the canvas to create a 'shape'
			// then close and fill it.
			elvctx.beginPath();
			elvctx.moveTo(0,63);
			elvctx.lineTo(0,60-zarray[0]);
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				elvctx.lineTo((tarray[i]*5),60-zarray[i]);
			} // 243*5=1215, so each 'time unit' is 5 pixels
			//elvctx.lineTo(1215,zarray[0]); // looping back to beginning -- Needed?
			// but if we do that, t axis continuity is broken, and we have no means of estimating t...
			// better to skip this for now...
			// if the provided ride file loops back to beginning, this will still work, and if it doesn't, so be it :P 
			// now close off the shape
			elvctx.lineTo(1214,63);
			elvctx.lineTo(0,63);
			elvctx.closePath();
			elvctx.strokeStyle = elvLnColor;
			elvctx.stroke();

			elvctx.fillStyle = elvFlColor;
			elvctx.fill();
			// put buffer on left.  We'll use for axis.

		// Now display z axis min max.
			elvctx.fillStyle = elvAxColor;
			elvctx.font = "12px Arial";
			// elvAxColor
			// elvLnColor
			elvctx.fillText(Math.round(zmax)+" "+elunit, elvAxMxLabelX, elvAxMxLabelY);
			elvctx.fillText(Math.round(zmin)+" "+elunit, elvAxMnLabelX, elvAxMnLabelY);
			elvctx.stroke();

		// Now display ride stats.
			elvctx.fillStyle = elvAxColor;
			elvctx.font = "12px Arial";
			// elvAxColor
			// elvLnColor
			elvctx.fillText("Dist:   "+Math.round(cmlDist*10)/10+" "+dstunit, elvDistLabelX, elvDistLabelY);

			console.log(cmlTime);

			var hrs=Math.trunc(cmlTime/(3600000));
			var mins=Math.trunc((cmlTime-(hrs*3600000))/60000);
			if(mins<10) {dph=hrs+":0"+mins+":";} else {dph=hrs+":"+mins+":";};
			var secs=Math.trunc((cmlTime-(hrs*3600000)-(mins*60000))/1000);
			if(secs<10) {dph=dph+"0"+secs;} else {dph=dph+""+secs;};
			console.log("Time: "+dph);
			elvctx.fillText("Time: "+dph, elvTimeLabelX, elvTimeLabelY);
			elvctx.fillText("Asc:   "+Math.round(cmlElev)+" "+elunit, elvAsceLabelX, elvAsceLabelY);
			elvctx.fillText("Desc: "+Math.round(cmlDesc)+" "+elunit, elvDescLabelX, elvDescLabelY);
			elvctx.stroke();


		// draw backgrounds last, and draw behind.
			ctx.globalCompositeOperation = 'destination-over'
			ctx.fillStyle = mapbg;
			ctx.fillRect(-5000, -5000, 10000, 10000); // in this case the point is to still show when panning and zooming.
			ctx.stroke();
			elvctx.globalCompositeOperation = 'destination-over';
			elvctx.fillStyle = elvBgColor; // "#A0A0A0"
			elvctx.fillRect(0, 0, elvc.width, elvc.height);
			elvctx.stroke();

		};


		function procMapLoad() {
			if (maptype === "atlas") {
				img.src = atlasPng; //'images/map_atls.png'; // Set source path -- triggers loading!
				mapbg = atlasBg; //"#0fa8d2";
				mapline = atlasLn; //"#0000ff";
				elvAxColor = elvAxColorAtls; // "#ffffff"; // color of the axis labels in elevation chart
				elvLnColor = elvLnColorAtls; // "#121280"; // color of the line in elevation chart
				elvFlColor = elvFlColorAtls; // "#707070"; // color of the graph fill in elevation chart
				elvBgColor = elvBgColorAtls; // "#404040"; // color of the background in elevation chart
			} else if (maptype === "road") {
				img.src = roadPng; //'images/map_road.png'; // Set source path -- triggers loading!
				mapbg = roadBg; //"#1862ad";
				mapline = roadLn; //"#ff0000";
				elvAxColor = elvAxColorRoad; // "#ffffff"; // color of the axis labels in elevation chart
				elvLnColor = elvLnColorRoad; // "#121280"; // color of the line in elevation chart
				elvFlColor = elvFlColorRoad; // "#707070"; // color of the graph fill in elevation chart
				elvBgColor = elvBgColorRoad; // "#404040"; // color of the background in elevation chart
			} else if (maptype === "satellite") {
				img.src = satlPng; //'images/map_satl.png'; // Set source path -- triggers loading!
				mapbg = satlBg; //"#143d6b";
				mapline = satlLn; //"#ff00ff";
				elvAxColor = elvAxColorSatl; // "#ffffff"; // color of the axis labels in elevation chart
				elvLnColor = elvLnColorSatl; // "#121280"; // color of the line in elevation chart
				elvFlColor = elvFlColorSatl; // "#707070"; // color of the graph fill in elevation chart
				elvBgColor = elvBgColorSatl; // "#404040"; // color of the background in elevation chart
			} else  {
				maptype = defaultMaptype;
				img.src = atlasPng; //'images/map_atls.png'; // Set source path -- triggers loading!
				mapbg = atlasBg; //"#0fa8d2";
				mapline = atlasLn; //"#0000ff";
				elvAxColor = elvAxColorAtls; // "#ffffff"; // color of the axis labels in elevation chart
				elvLnColor = elvLnColorAtls; // "#121280"; // color of the line in elevation chart
				elvFlColor = elvFlColorAtls; // "#707070"; // color of the graph fill in elevation chart
				elvBgColor = elvBgColorAtls; // "#404040"; // color of the background in elevation chart
			}
		};
		

// *************** MAIN LOAD BEGINS HERE *********************
// all other functions are triggered by events or by this script.
// so if you're trying to follow, start here :)

		//  This function is executed when the window loads
		window.onload = function() {
			// initialize values:
			btnc = document.getElementById("btn"); // button canvas
			btnctx = btnc.getContext("2d"); // button canvas context
			elunit = "m"; // displays the type of elevation unit
			dstunit = "km"; // displays the type of distance unit
			// url parameter handling
			maptype = urlParams.get('maptype'); // this carries user selection of the map type
			if (maptype === null) {
				maptype = defaultMaptype; // default
			}	
			met = urlParams.get('met'); // this carries user selection of the units of measurement
			if (met === null){
				met = defaultMet; // default
			}
			route = urlParams.get('route'); // this drives loading of the file.  Required.
			// main variable initialization complete! :)	

			if (route === null) {
	// start loading the arrays using user driven file load (expose button)
				var expform = document.getElementById("xmlfile")
				expform.style.display = "block";
			} else {
	// start loading the arrays from the selected route's associated gpx file
				document.getElementById("xmlfile").style.display = "hidden";
				gpxfilename = "gpx/"+route+".gpx";
				xhttp.open("GET", gpxfilename, true);
				xhttp.send();
			}

			procMapLoad();

		// now image is loading.  When it's finished, it'll load the 'xmlIsLoaded' routine.

	//  *** BUTTON CANVAS HANDLING ***

			// implement buttons - basically just make 3 21*63 image-buttons and stripe them, click and reload with appropriate.
			btnctx.fillStyle=btnAtlsColr;
			btnctx.fillRect(0,0,63,21);
			btnctx.fillStyle=btnRoadColr;
			btnctx.fillRect(0,22,63,21);
			btnctx.fillStyle=btnSatlColr;
			btnctx.fillRect(0,43,63,21);
			btnctx.stroke();

			// button labels
			btnctx.fillStyle='rgb(0, 0, 0)';
			btnctx.font = "12px Arial";
			btnctx.fillText(link1Text, link1X+textoffsetx, link1Y+textoffsety);
			btnctx.fillText(link2Text, link2X+textoffsetx, link2Y+textoffsety);
			btnctx.fillText(link3Text, link3X+textoffsetx, link3Y+textoffsety);
			btnctx.stroke();

			// and monitor, run the below functions to determine mouse location and respond to clicks
			btnc.addEventListener("mousemove", CanvasMouseMove, false);
			btnc.addEventListener("click", Link_click, false);

		};
	</script>
<br />  

</body>
</html>







