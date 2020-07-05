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

<!-- 
Map preview overlayed atop 2048x2048 maps created by: http://blog.damonpollard.com/grand-theft-auto-v-the-map/#comments

-->
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
// ********   NEED TO SET UP MAP CANVAS WITH BACKGROUND - PROBABLY MATCH SEA COLOR OF SELECTED MAP.
	
// ********   PENDING:  INTENT IS TO DRAW/TRACE EACH POINT IN THE ARRAY, ANIMATED OVER ~4S PERIOD
//    Static draw is phase 1, but is done using canvas, and sets up arrays for future animation...
//    The line drawing is even in a for loop, so (loosely), my thought is to build a loop that cycles 
//    very quickly, with iterations counting up from 0 to 240 over 4 seconds, and each cycle, draw the
//    map elements ( and possibly elevation elements) where the 't' array value is < that 0-240 counter...
//    I think changing from static to animated may be easier than just getting it static :)

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




		window.onload = function() {
			const queryString = window.location.search;
			const urlParams = new URLSearchParams(queryString);

			// These define conversion factors to convert from the .fit x/y lat/long to
			//  equivalent map pixels, based on 2048x2048 map images with 0,0 being top left, 
			//  and z into meters.  These factors are constant.
// *******    NEED TO WORK OUT APPROPRIATE CONVERSION FACTORS FROM LAT/LONG/ELEV/TIME
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
		
			
			// --- changed Z to variable: can sub in imperial instead of metric based on URL parameter.
			var zfactor = 1; // this is multipler for conversion to either metric or imperial
			var zoffset = 0; // this is offset for conversion to either metric or imperial
			var zfactor2 = 1;
			var zoffset2 = 0;
			var tfactor2 = 1;
			var toffset2 = 0;
			
			// setting up all the vars
			var zoomfactorx = 1; // handles x/y zoom for main panel
			var zoomfactory = 1; // handles x/y zoom for main panel
			var zoomfactorxy = 1; // handles x/y zoom for main panel
			var translatefactorx = 0; // shift to center for x
			var translatefactory = 0; // shift to center for y

			var zoomfactorz = 1; // elevation zoom factor for the z axis 
			var zoomfactort = 1; // elevation time zoom factor for t axis
			var translatefactorz = 0; // offset for elevation -- mostly this will be zero, but:
			// if there's negative elevation, offset negative and draw a 0 axis.  also if there's very
			// otherwise low elevation variation (<40), shift up by 10 pixels and draw a 0 axis.

			var elvc = document.getElementById("elv");
			var elvctx = elvc.getContext("2d");
			var mcanvas = document.getElementById("bmap");
			var ctx = mcanvas.getContext("2d");
			var img;

			var route = urlParams.get('route'); // this drives loading of the file.  Required.
			var maptype = urlParams.get('maptype'); // this carries user selection of the map type
			var met = urlParams.get('met'); // this carries user selection of the units of measurement

			let xarray = []; 
			let yarray = []; 
			let zarray = []; 
			let tarray = []; 

			// url parameter handling
			if (route === null) {
				route = "something"; // default... but this is really required.
			}	

			if (maptype === null) {
				maptype = "atlas"; // default
			}	

			if (met === null){
				met = "Metric";
			}
			if(met === "Imperial")
			{
				zfactor = 1; // this is multiplier to convert whatever the z axis is in to feet
				zoffset = 0; // this is offset to convert whatever the z axis is in to meters
			} else {
				zfactor = 1; // this is multiplier to convert whatever the z axis is in to feet
				zoffset = 0; // this is offset to convert whatever the z axis is in to feet
			}

			var gpxfilename = "gpx/"+route+".xml";

			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", gpxfilename, false);
			xhttp.send();
				  
// ********** NEED TO PROPERLY HANDLE TIME 
//  Right now just dumping the data in the array.
//  Need to preserve prior element time and determine elapsed time between points and return a second value, or millisecond, or something useful like that

			//parseTrack(this);
			var i;
			var lastTimestamp, thistimestamp
			var cmlDist = 0;
			var xmlDoc = xhttp.responseXML;
			var x = xmlDoc.getElementsByTagName("trkpt");
			for (i = 0; i < x.length; i++) { 
				xarray.push(  ((x[i].getAttribute("lon") *1)+(xoffset*1))*xfactor);
				yarray.push(  ((x[i].getAttribute("lat") *1)+(yoffset*1))*yfactor);
				zarray.push(  ((x[i].getElementsByTagName("ele")[0].childNodes[0].nodeValue *1)+(zoffset*1))*zfactor);
				thisTimestamp = new Date(x[i].getElementsByTagName("time")[0].childNodes[0].nodeValue);
				if(i===0) {
					lastTimestamp = thisTimestamp;
				} else {
					cmlDist=cmlDist+Math.abs(thisTimestamp-lastTimestamp);
					lastTimestamp = thisTimestamp;
				}
				tarray.push(cmlDist);
				
			// Now we identify ranges and then convert x/y into new range: 0,0 = top left corner, 2048,2048 = bottom right corner
			// and z into meters, take min/max for scale on graph, then convert to 0-60 range for elevation chart.  Note: if Z variation is <20m, leave as is.
			// it's a little redundant to cycle through the array twice, but the alternative would be to convert all values as
			//  well as the min/max values...  If there's no load time issue this way is simpler :)
			//  Basically, cycle through array to convert into preferred units (pixels & feet & seconds), then take min-max, then cycle through
			//  again to handle scaling and zooming adjustments.
			//  LATER: actually, turns out canvas just handles the scaling for me, so yay.
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

			console.log("zmin:"+xmin);
			console.log("zmax:"+xmax);
			console.log("tmin:"+tmin);
			console.log("tmax:"+tmax);
			// determine convesion for z into pixel value (0-60)
			if( (Math.max.apply(null, zarray) - Math.min.apply(null, zarray)) < 20 ) {
				zfactor2 = 1;
				zoffset2 = 10;
			} else {
				zfactor2 = 50/(Math.max.apply(null, zarray) - Math.min.apply(null, zarray));
				zoffset2 = 0-(zmin*zfactor2);
			}
			
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
			} else if (maptype === "road") {
				img = document.getElementById("RoadMap");
			} else if (maptype === "satellite") {
				img = document.getElementById("SatlMap");
			} else  {
				img = document.getElementById("AtlsMap");
			}
			
			zoomfactory = (655/(ymax - ymin));
			zoomfactorx = (1280/(xmax - xmin));
			if (zoomfactory < zoomfactorx) {
				zoomfactorxy = zoomfactory*.98;

//  *********** FUTURE IMPROVEMENT:  Left justified and top justified sucks for map centering!!!
//   suggestion: Instead of just building in a small margin, for the non-driving axis (in other
//   words, if Y axis drives the zoom level, X axis would get this treatment) -- Just figure out
//   how much space is "left over" and offset the axis by half that amount to center.
//   But for now it's just left justified.

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

// ***** IMPROVEMENT: Let's pretty up this line, eh? *********
// ******* CRITICAL: At least change the color based on map seletion - this is completely unreadable on satellite.
//  ***** ISSUE: As I fixed mapping, elevation display stopped working...
			ctx.moveTo(xarray[0],yarray[0]);
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				ctx.lineTo(xarray[i],yarray[i]);
			}
			// ctx.lineTo(xarray[0],yarray[0]); // assuming we loop back to start ...  Needed?
			// but if we do that, t axis continuity is broken, and we have no means of estimating t...
			// better to skip this for now...
			// if the provided ride file loops back to beginning, this will still work, and if it doesn't, so be it :P 
			
			ctx.strokeStyle = '#0000ff';
			ctx.lineWidth = 2;
/* // this broke when I enabled, but blue works well enough I haven't bothered to troubleshoot. switch would be cleaner anyway.
			if(mapstyle==="road") {
				ctx.strokeStyle = '#0000ff'; // color for road
				ctx.lineWidth = 2;
			} else if (mapstyle==="satellite") {
				ctx.strokeStyle = '#0000ff'; // color for sat
				ctx.lineWidth = 2;
			} else {
				ctx.strokeStyle = '#0000ff'; // color for atlas
				ctx.lineWidth = 2;
			}
*/
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



// need to implement zooming on z and t axis
// would be nice to animate along t as well.
// add axis labels
// MAKE PRETTY - fill in bottom? background?  ugh is so uuugly.
// Given that t axis data is now jacked, this will likely need to be altered to align to how the time data looks when
//  that gets fixed.
//  hm...  would be nice to allow x axis to be distance as well as time...
//  feature creep is just so exciting!
			elvctx.moveTo(11,zarray[0]);
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				elvctx.lineTo((tarray[i]*5)+11,60-zarray[i]);
			} // 240*5=1200, so each 'time unit' is 4 pixels, with 15 pixels buffer...
			//elvctx.lineTo(1215,zarray[0]); // looping back to beginning -- Needed?
			// but if we do that, t axis continuity is broken, and we have no means of estimating t...
			// better to skip this for now...
			// if the provided ride file loops back to beginning, this will still work, and if it doesn't, so be it :P 
			elvctx.stroke();
			// put buffer on left.  We'll use for axis.

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
		var a = 
			Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
			Math.sin(dLon/2) * Math.sin(dLon/2); 
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
		var d = R * c; // Distance in km
		return d;
	}

	function deg2rad(deg) {
		return deg * (Math.PI/180)
	}

	</script>
</body>
</html>







