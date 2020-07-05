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
<body style="background-color: transparent; color:white; margin-bottom:-5px; vertical-align: top; display: block" >

<div style="display:none;">
	<img id="AtlsMap" width="2048" height="2048"
	src="images/map_atls.png" alt="Atlas Map">
	<img id="RoadMap" width="2048" height="2048"
	src="images/map_road.png" alt="Road Map">
	<img id="SatlMap" width="2048" height="2048"
	src="images/map_satl.png" alt="Satellite Map">
</div><br>

	<canvas id="elv" width="1215" height="63">
	</canvas><canvas id="btn" width="63" height="63">
	</canvas><canvas id="bmap" width="1280" height="655" >
	</canvas><script>

		// Because the buttons have monitor functions, related variables set up here
		// The numbers here control much of the map selection button layout.
		// could probably be const's.
			var btnc = document.getElementById("btn");
			var btnctx = btnc.getContext("2d");
			var link1Text = "Atlas";
			var link1URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=atlas&met=Metric&route=the_tourist";
			var link2Text = "Road";
			var link2URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=road&met=Metric&route=the_tourist";
			var link3Text = "Satellite";
			var link3URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=satellite&met=Metric&route=the_tourist";
			var textoffsetx = 12;
			var textoffsety = 14;
			var link1X = 0;
			var link1Y = 0;
			var link1Height = 21;
			var link1Width = 63;
			var isLink1 = false;
			var link2X = 0;
			var link2Y = 20;
			var link2Height = 21;
			var link2Width = 63;
			var isLink2 = false;
			var link3X = 0;
			var link3Y = 40;
			var link3Height = 21;
			var link3Width = 63;
			var isLink3 = false;


		//  This is what's loaded initially with page
		window.onload = function() {

			const queryString = window.location.search;
			const urlParams = new URLSearchParams(queryString);

			// These define conversion factors to convert from the .fit x/y lat/long to
			//  equivalent map pixels, based on 2048x2048 map images with 0,0 being top left, 
			//  and z into meters.  These factors are constant.
			const xfactor = 17200; // this is multipler to convert to latlong to pixels
			const xoffset = 169.9255; // this is offset to convert latlong to to pixels
			const yfactor = -18200; // this is multipler to convert latlong to to pixels
			const yoffset = 19.0305	; // this is offset to convert latlong to to pixels
			const xhilim = 2007; // the map has wide boundaries with no roads
			const xlolim = 41; // and limiting this makes some other stuff easier
			const yhilim = 2007; // so we use 98% of 2048= 2007
			const ylolim = 41;
			const zhilim = 50000; // just sanity imposed here - you're not 50km in the air
			const zlolim = -5000; // or 5km underground 
			const meters2feet = 3.28084; // this is multipler for conversion to either imperial
			const km2mi = 0.621371; // this is multipler for conversion to either imperial
		
			// these are not necessarily constant.  Many initializers are just placeholders.
			var zfactor2 = 1; // zfactor2&zoffset2 are used to scale from "real" numbers to pixels
			var zoffset2 = 0; // zfactor2&zoffset2 are used to scale from "real" numbers to pixels
			var tfactor2 = 1; // and tfactor converts the time units into a scale of 0-240 for various purposes
			var toffset2 = 0; // 
			var zoomfactorx = 1; // handles x/y zoom for main panel
			var zoomfactory = 1; // handles x/y zoom for main panel
			var zoomfactorxy = 1; // handles x/y zoom for main panel
			var translatefactorx = 0; // shift to center for x
			var translatefactory = 0; // shift to center for y
			//NOTE: zfactor/zoffset currently just left at 1/0 (no impact) - so not really implemented.  Left as a stub.


			// setting up the 3 canvases (buttons, elevation profile, map)
			var elvc = document.getElementById("elv");
			var elvctx = elvc.getContext("2d");


			var mcanvas = document.getElementById("bmap");
			var ctx = mcanvas.getContext("2d");
			var img;
			var elunit = "m";
			var dstunit = "km";
			var mapbg;
			var mapline;

			var route = urlParams.get('route'); // this drives loading of the file.  Required.
			var maptype = urlParams.get('maptype'); // this carries user selection of the map type
			var met = urlParams.get('met'); // this carries user selection of the units of measurement

			// initialize the arrays for the route
			let xarray = []; 
			let yarray = []; 
			let zarray = []; 
			let tarray = []; 
			let darray = [];  // new array for cumulative distance

			// url parameter handling
			if (route === null) {
				route = "something"; // default... but this is really required.
			}	

			if (maptype === null) {
				maptype = "atlas"; // default
			}	

			if (met === null){
				met = "Metric"; // default
			}
			
			// file for route preview 
			// for some reason gpx won't pick up, have to name it xml.
			var gpxfilename = "gpx/"+route+".xml";

			// pick up file
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", gpxfilename, false);
			// NOTE: Using deprecated synchronous mode to load the file because the page has to wait anyway.
			xhttp.send();
				  
			// really need to consolidate all my variable definitions - sooo messsy.
			var i;
			var lastTimestamp,thisTimestamp,lastLat,thislat,lastLon,thisLon,thisElev,lastElev;
			var cmlDist = 0;
			var cmlTime = 0;
			var cmlElev = 0;
			var cmlDesc = 0;
			var xmlDoc = xhttp.responseXML;

			// now we really start parsing xml.  Pick out all the trkpt items and extract specfiic attributes:
			var x = xmlDoc.getElementsByTagName("trkpt");
			for (i = 0; i < x.length; i++) { 

				// load values from xml formatted gpx file into array, store in variable first for some stuff
				thisLon=(x[i].getAttribute("lon") *1);
				thisLat=(x[i].getAttribute("lat") *1);
				thisElev=(x[i].getElementsByTagName("ele")[0].childNodes[0].nodeValue *1);
				// for time convert from standardized text into time value and preserve in var
				thisTimestamp = new Date(x[i].getElementsByTagName("time")[0].childNodes[0].nodeValue);

				if(i===0) {
					// first time that's all
				} else {
					// thereafter, calculate the difference in time and add too the cumulative time value.
					cmlTime=cmlTime+Math.abs(thisTimestamp-lastTimestamp);
					if(thisElev>lastElev){
						cmlElev=cmlElev+(thisElev-lastElev);
					} else if(thisElev<lastElev) {
						cmlDesc=cmlDesc+(lastElev-thisElev);
					}
					cmlDist = cmlDist + getDistanceFromLatLonInKm(lastLat,lastLon,thisLat,thisLon);
				}
				lastTimestamp = thisTimestamp;
				lastElev = thisElev;
				lastLat = thisLat;
				lastLon = thisLon;

				// minor concern: I'm referencing i as an index explicitly at some points, but at others
				//  I'm doing non-indexed pushes into the array.  Could be a vector for bugs, seems like 
				//  it would be easy to make a mistake and get them out of sync.
				xarray.push((thisLon+(xoffset*1))*xfactor);
				yarray.push((thisLat+(yoffset*1))*yfactor);
				zarray.push(thisElev);
				tarray.push(cmlTime);
				// now impose sanity limits on values
				if(xarray[i] > xhilim) {xarray[i] = xhilim;};
				if(xarray[i] < xlolim) {xarray[i] = xlolim;};
				if(yarray[i] > yhilim) {yarray[i] = yhilim;};
				if(yarray[i] < ylolim) {yarray[i] = ylolim;};
				if(zarray[i] > zhilim) {zarray[i] = zhilim;};
				if(zarray[i] < zlolim) {zarray[i] = zlolim;};
			};

			// Preserve min and max values - we'll possibly use these to control zooming...
			var xmin = Math.min.apply(null, xarray); 
			var xmax = Math.max.apply(null, xarray);
			var ymin = Math.min.apply(null, yarray);
			var ymax = Math.max.apply(null, yarray);
			var zmin = Math.min.apply(null, zarray);
			var zmax = Math.max.apply(null, zarray);
			var tmin = Math.min.apply(null, tarray);
			var tmax = Math.max.apply(null, tarray);
			
			// determine convesion for z into pixel value (0-50)
			// need to calculate before zmax and zmin are adjusted for metric/imperial so 
			// that behavior is consistent.
			if( (Math.max.apply(null, zarray) - Math.min.apply(null, zarray)) < 20 ) {
				// if the values are too low to scale
				zfactor2 = 1;
				zoffset2 = 10;
			} else {
				// otherwise scale
				zfactor2 = 50/(Math.max.apply(null, zarray) - Math.min.apply(null, zarray));
				zoffset2 = 0-(zmin*zfactor2);
			}
			

			if (met === "Imperial") {
				zmin = zmin*meters2feet; // now we can use these values for our axis
				zmax = zmax*meters2feet; // and it will align with user selection
				cmlElev = cmlElev*meters2feet;
				cmlDesc = cmlDesc*meters2feet;
				cmlDist = cmlDist*km2mi;
				elunit = "ft";
				dstunit = "mi";
			}
/* 
			console.log(zmin); // lowest elevation in ft or meters from user selection.
			console.log(zmax); // highest elevation in ft or meters from user selection.
			console.log(cmlElev); //total climb in ft or m 
			console.log(cmlDesc); //total descent in ft or m
			console.log(cmlDist); // total distiance in mi or km
			// all seemed OK.
*/		
			// determine convesion for t into time driver (0-240) (intent is to display in ~4 seconds, so time equates to 16.67 milliseconds, ~60fps)
			tfactor2 = 240/(Math.max.apply(null, tarray) - Math.min.apply(null, tarray));
			toffset2 = 0-(tmin*tfactor2);
			
			
			for (var i=0, len=xarray.length; i<len; i++) { // Now adjust to fit scales and zooms.
				zarray[i] = ((zarray[i]*1)+(zoffset2*1))*zfactor2;
				tarray[i] = ((tarray[i]*1)+(toffset2*1))*tfactor2;
			}

//  ********** LIKELY OPTION FOR LOAD SPEED IMPROVEMENT: 
//    Right now the page loads all 3 2048x2048 map images in the html with hidden tag, then displays them in js.
//    that's me being lazy!!!  All 3 large files are loaded every time, and that's wasteful.
//    Better would be to define the objects pointed to images in js, just when you do that
//    you also have to build in a wait, and I was lazy :p 
//      BUT IT WOULD CUT LOAD TIME IN 1/3 SO SHOULD DO IT SOMETIME.

			if (maptype === "atlas") {
				img = document.getElementById("AtlsMap");
				mapbg = "#0fa8d2";
				mapline = "#0000ff";
			} else if (maptype === "road") {
				img = document.getElementById("RoadMap");
				mapbg = "#1862ad";
				mapline = "#ff0000";
			} else if (maptype === "satellite") {
				img = document.getElementById("SatlMap");
				mapbg = "#143d6b";
				mapline = "#ff00ff";
			} else  {
				img = document.getElementById("AtlsMap");
				mapbg = "#0fa8d2";
				mapline = "#0000ff";
			}
			


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
			ctx.translate(translatefactorx,translatefactory);
			ctx.scale(zoomfactorxy, zoomfactorxy);  // same zoom factor to avoid weird aspect ratios
			ctx.drawImage(img, 0, 0);


			// FINALLY all the prep is done - let's draw the route!  Static for now, but canvas will let me
			//  animate.  Plan is to use 't' value to drive animation.
// ***** IMPROVEMENT: "close off" the "shape" of line and fill with different color (darker grey?)
// ******* CRITICAL: At least change the color based on map seletion - this is completely unreadable on satellite.
//  ***** ISSUE: As I fixed mapping, elevation display stopped working...
			ctx.moveTo(xarray[0],yarray[0]);
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				ctx.lineTo(xarray[i],yarray[i]);
			}
			
			ctx.strokeStyle = mapline;
			ctx.lineWidth = 2;
			ctx.stroke();


			link1URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=atlas&met="+met+"&route="+route;
			link2URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=road&met="+met+"&route="+route;
			link3URL = "https://gtbikevroutes.fun/MapPreview.php?maptype=satellite&met="+met+"&route="+route;

// implement buttons - basically just make 3 21*63 image-buttons and stripe them, click and reload with appropriate.

			btnctx.fillStyle='rgb(128,185,35)';
			btnctx.fillRect(0,0,63,21);

			btnctx.fillStyle='rgb(146, 210, 187)';
			btnctx.fillRect(0,22,63,21);

			btnctx.fillStyle='rgb(0, 153, 0)';
			btnctx.fillRect(0,43,63,21);
			btnctx.stroke();

	 
			btnctx.fillStyle='rgb(0, 0, 0)';
			btnctx.font = "12px Arial";
			btnctx.fillText(link1Text, link1X+textoffsetx, link1Y+textoffsety);
			btnctx.fillText(link2Text, link2X+textoffsetx, link2Y+textoffsety);
			btnctx.fillText(link3Text, link3X+textoffsetx, link3Y+textoffsety);
			btnctx.stroke();

			btnc.addEventListener("mousemove", CanvasMouseMove, false);
			btnc.addEventListener("click", Link_click, false);



//  hm...  would be nice to allow x axis to be distance as well as time...
//  feature creep is just so exciting!
			elvctx.beginPath();
			elvctx.moveTo(11,63);
			elvctx.lineTo(11,60-zarray[0]);
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				elvctx.lineTo((tarray[i]*5)+11,60-zarray[i]);
			} // 240*5=1200, so each 'time unit' is 4 pixels, with 15 pixels buffer...
			//elvctx.lineTo(1215,zarray[0]); // looping back to beginning -- Needed?
			// but if we do that, t axis continuity is broken, and we have no means of estimating t...
			// better to skip this for now...
			// if the provided ride file loops back to beginning, this will still work, and if it doesn't, so be it :P 
			// now close off the shape
			elvctx.lineTo(1214,63);
			elvctx.lineTo(11,63);
			elvctx.closePath();
			elvctx.stroke();
			elvctx.fillStyle = "#505050";
			elvctx.fill();
			// put buffer on left.  We'll use for axis.

			elvctx.fillStyle='rgb(0, 0, 0)';
			elvctx.font = "12px Arial";
			console.log(zmax);
			console.log(zmin);
			elvctx.fillText("Max "+Math.round(zmax), 5, 15);
			elvctx.fillText("Min "+Math.round(zmin), 5, 55);
			elvctx.stroke();

			// draw backgrounds last, and draw behind.
			elvctx.globalCompositeOperation = 'destination-over'
			elvctx.fillStyle = "#A0A0A0";
			elvctx.fillRect(0, 0, elvc.width, elvc.height);
			elvctx.stroke();
			ctx.globalCompositeOperation = 'destination-over'
			ctx.fillStyle = mapbg;
			ctx.fillRect(-5000, -5000, 10000, 10000); // in this case the point is to still show when panning and zooming.
			ctx.stroke();

		};




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
 
        function Link_click(e) {

            if (isLink1) {
                window.location = link1URL;
            }
            if (isLink2) {
                window.location = link2URL;
            }
            if (isLink3) {
                window.location = link3URL;
            }
        }

	function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
		var R = 6371; // Radius of the earth in km
		var dLat = deg2rad(lat2-lat1);  // deg2rad below
		var dLon = deg2rad(lon2-lon1); 
		var lat1 = deg2rad(lat1);
		var lat2 = deg2rad(lat2);
		var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
		var d = R * c;
		return d;
    }

	function deg2rad(deg) {
		return deg * (Math.PI/180)
	}

	</script>
</body>
</html>







