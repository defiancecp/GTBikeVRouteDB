<!DOCTYPE html>
<html>
<head>
	<!-- <a id="start">Start</a> <a id="stop">Stop</a> -->
	<meta charset="utf-8" http-equiv="Content-Type" name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
	<script src="html2canvas.min.js"></script>
	<script src="FileSaver.js"></script>
	<script src="mpconsts.js"></script> 
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
    <script type="text/javascript" src="easy-fit.bundle.js"></script>
	<link rel="stylesheet" href="MPStyles.css">
</head> 
<body>
<div id="topbanner">
  <h1>GTBike V Ride Viewer</h1>
  <div id="hiddenContainer">
	<br>
	<input type="file" id="xmlfile"></input>
	<button onclick="clkImpMet()" id="btnImpMet">Imperial/Metric switch</button>
	<button onclick="clkTmDst()" id="btnTmDst">Time/Distance switch</button>
	<button onclick="clkScrSht()" id="btnScrSht">Take Screenshot</button> 
	<br><div id="warntext"></div>
  </div>
</div>
<div id="main">
<div id="canvasContainer">
	<canvas id="elv" width="1278" height="62">
	</canvas><canvas id="bmap" width="1278" height="654" >
	</canvas><div id="hiddenCadGphCon"><canvas id="cad" width="1278" height="62">
	</div><div id="hiddenPwrGphCon"><canvas id="pwr" width="1278" height="62">
	</div><div id="hiddenHrmGphCon"><canvas id="hrm" width="1278" height="62">
	</div>
</div>
</div>
<div id="scrContainer"> 
	<canvas id="scrCanvas" ></canvas><iframe src = 'SubmitGPXData.php' id="frmGPX" ></iframe>
</div>
<script>

	// variables
		var isLink1,isLink2,isLink3,link1URL,link2URL,link3URL,elvc,elvctx,mcanvas,ctx,hrmc,hrmctx,pwrc,pwrctx,cadc,cadctx,img,xmlDoc,blobDoc,gpxfilename,fitfilename,xhttp,checkFIT,zfactor2,zoffset2,ifactor2,ioffset2,zoomfactorx,zoomfactory,zoomfactorxy,translatefactorx,translatefactory,img,elunit,dstunit,mapbg,mapline,route,maptype,met,elex,cmlDist,cmlTime,cmlElev,cmlDesc,cmlKCal,x,lastTimestamp,thisTimestamp,lastLat,thislat,lastLon,thisLon,thisElev,lastElev,xmin,xmax,ymin,ymax,zmin,zmax,tmin,tmax,imin,imax,xmlLoaded,imgLoaded,currAniIx,elAniX,elAniY,mpAniX,mpAniY,mapdot,mpLineWidth,mpAniR,xmapoffset,ymapoffset,fileLoaded,actDocType,easyFit,zCount,zFrames,lastZoom,lastTransX,lastTransY,thisHRM,thisCAD,thisPWR,EasyFit,EFreader,inEasyFit,hrmscale,hrmoffset,pwrscale,pwroffset,cadscale,cadoffset,hrmmin,hrmmax,pwrmin,pwrmax,cadmin,cadmax,hrmAniX,hrmAniY,cadAniX,cadAniY,pwrAniX,pwrAniY,hrmavg,cadavg,pwravg,mouseMsgText,aniIndex,maplineedge;

		EasyFit = window.easyFit.default;
		EFreader = new FileReader();
		inEasyFit = new EasyFit({
			force: true,
			speedUnit: 'km/h',
			lengthUnit: 'km',
			temperatureUnit: 'celcius',
			elapsedRecordField: true,
			mode: 'list'
		});
	// array variables:
		let xarray = []; 
		let yarray = []; 
		let zarray = []; 
		let elevarray = []; 
		let tarray = []; 
		let darray = []; // new array for cumulative distance
		let iarray = []; // Instead of just using the "t" array, build an index array based on either t or cumulative distance
		
		let hrmArray = [];
		let cadArray = [];
		let pwrArray = [];
		
		// now these arrays are for animated zoom/translate:
		let aniZ = [];
		let aniTX = [];
		let aniTY = [];
		zCount = 0;
		zFrames = 60;
		lastZoom = 1;
		lastTransX = 0;
		lastTransY = 0;
		zoomfactorxy = INITIAL_ZOOM;
		translatefactorx = INITIAL_TRANSLATION_X;
		translatefactory = INITIAL_TRANSLATION_Y;
		
	// based on zoom level, but initialize at these values
		mpLineWidth = 1;
		mpAniR = 5;

	// initializing some containers and variables that will be referenced by triggered functions so must be initialized globally
		img = new Image();   // Create new img element
		xhttp = new XMLHttpRequest(); //handler for gpx files loaded from server
		checkFIT = new XMLHttpRequest(); // handler fir fit files loaded from server
		xmlLoaded = 0; // indicates that activity data is fully processed for display
		imgLoaded = 0; // indicator that the map image has been loaded and is ready to display.
		currAniIx = 0; // animation frame counter
		xmapoffset = 0; // for map centering: offset
		ymapoffset = 0; // for map centering: offset
		fileLoaded = 0; // indicator that an activity file has been loaded and is ready for processing
		mcanvas = document.getElementById("bmap"); // map canvas
		elvc = document.getElementById("elv"); // elevation profile canvas
		hrmc = document.getElementById("hrm"); //  canvas
		pwrc = document.getElementById("pwr"); //  canvas
		cadc = document.getElementById("cad"); //  canvas
		elvc.addEventListener("mousemove", CanvasMouseMove, false);
		hrmc.addEventListener("mousemove", CanvasMouseMoveLower, false);
		cadc.addEventListener("mousemove", CanvasMouseMoveLower, false);
		pwrc.addEventListener("mousemove", CanvasMouseMoveLower, false);
		elvc.addEventListener("click", Link_click, false);
		isLink1 = false; // indicates whether mouse position currently hovering is over link 1
		isLink2 = false; // indicates whether mouse position currently hovering is over link 2
		isLink3 = false; // indicates whether mouse position currently hovering is over link 3
		actDocType = 0; // 1- fit 2- gpx

	// To monitor for clicks on the map change buttons canvas.
	// credit for much of this part of code: http://www.authorcode.com/how-to-create-hyper-link-on-the-canvas-in-html5/
	// modified for canvas-relevant position rather than absolute
	//  This is executed every time mouse movement occurs over the button canvas (event handler)
        function CanvasMouseMove(e) {
            var x, y;
			var cpos = { top: e.pageY + 10, left: e.pageX + 10 };
			if (e.pageX || e.pageY) { 
			  x = e.pageX;
			  y = e.pageY;
			}
			else { 
			  x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			  y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
			} 
			x -= elvc.offsetLeft;
			y -= elvc.offsetTop;
			
			isLink3 = false;
			isLink1 = false;
			isLink2 = false;
			document.body.style.cursor = "";
            if (x >= LINK_1_X && x <= (LINK_1_X + link1Width) 
                    && y >= LINK_1_Y && y <= (LINK_1_Y + LINK_1_HEIGHT)) {
                document.body.style.cursor = "pointer";
                isLink1 = true;
            }
            if (x >= LINK_2_X && x <= (LINK_2_X + LINK_2_WIDTHh) 
                    && y >= LINK_2_Y && y <= (LINK_2_Y + LINK_2_HEIGHT)) {
                document.body.style.cursor = "pointer";
                isLink2 = true;
            }
            if (x >= LINK_3_X && x <= (LINK_3_X + LINK_3_WIDTHh) 
                    && y >= LINK_3_Y && y <= (LINK_3_Y + LINK_3_HEIGHT)) {
                document.body.style.cursor = "pointer";
                isLink3 = true;
            }
			if (currAniIx == 0 && zCount == 0 && imgLoaded === 1 && xmlLoaded === 1 && x < LINK_1_X) {
				displayFrame(Math.round(x/5));
				if(met === "Imperial") {
					mouseMsgText = Math.round(elevarray[aniIndex]*METERS_2_FEET)+elunit;
				} else {
					mouseMsgText = Math.round(elevarray[aniIndex])+elunit;
				}
				$('#besideMouse').offset(cpos);
				$("#besideMouse").html(mouseMsgText);
			} else {
			}
        }


        function CanvasMouseMoveLower(e) {
            var x, y;
			var cpos = { top: e.pageY + 10, left: e.pageX + 10 };
			if (e.pageX || e.pageY) { 
			  x = e.pageX;
			  y = e.pageY;
			}
			else { 
			  x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			  y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
			} 
			x -= elvc.offsetLeft;
			y -= elvc.offsetTop;
			
			document.body.style.cursor = "";
			if (currAniIx == 0 && zCount == 0 && imgLoaded === 1 && xmlLoaded === 1) {

				mouseMsgText = "figure this out";
				displayFrame(Math.round(x/5.25));

				if (this.id == "hrm") {
					mouseMsgText = Math.round(hrmArray[aniIndex])+"bpm";
				};
				if (this.id == "cad") {
					mouseMsgText = Math.round(cadArray[aniIndex])+"rpm";
				};
				if (this.id == "pwr") {
					mouseMsgText = Math.round(pwrArray[aniIndex])+"w";
				};
				$('#besideMouse').offset(cpos);
				$("#besideMouse").html(mouseMsgText);

			} else {
			}
        }


	// when a click is detcted, determine if it was on one of the map buttons.
	//  position determination based on CanvasMouseMove
	// if it was, reload with the selected map.
	//  executed each time the button canvas is clicked (event handler)
		function Link_click() {
			var hfcnt = document.getElementById("hiddenContainer")

			if (isLink1) {
				maptype = "atlas";
				procMapLoad();
				hfcnt.style.display = "none";
			}
			if (isLink2) {
				maptype = "road";
				procMapLoad();
				hfcnt.style.display = "none";
			}
			if (isLink3) {
				maptype = "satellite";
				procMapLoad();
				hfcnt.style.display = "none";
			}
		}

	// getting distance based on standard earth-surface lat-long distance calc.  Result in kliometers.
	// executed when processing data, used to calc point to point distance between each nav point.
		function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
			var R = EARTH_RADIUS; // Radius of the earth in km
			var dLat = deg2rad(lat2-lat1);  // deg2rad below
			var dLon = deg2rad(lon2-lon1); 
			var lat1 = deg2rad(lat1);
			var lat2 = deg2rad(lat2);
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
			var d = R * c;
			return d;
		}

	// quick math function used in distance calc; executed by it.
		function deg2rad(deg) {
			return deg * (Math.PI/180)
		}

	// this function is triggered when the image is loaded.  Nothing more to do with the image specifically, but check animation readiness.
		img.onload = function () {
			ctx = mcanvas.getContext("2d"); // map canvas context
			console.log("drawing...");
			img.draw; // put 'em on the canvas.
			imgLoaded = 1;
	// here's where we confirm both xml and image are done, and if so, start the drawings.
			// initially thought this was a bit klunky, but I think it works well here.
			if(imgLoaded === 1 && xmlLoaded === 1) {
				var thm = drawLoop();
			} else {
				resetCanvas(mcanvas);
				ctx.translate(translatefactorx,translatefactory);
				ctx.scale(zoomfactorxy, zoomfactorxy);  // same zoom factor to avoid weird aspect ratios
				ctx.drawImage(img, 0, 0);
				ctx.globalCompositeOperation = 'destination-over'
				ctx.fillStyle = mapbg;
				ctx.fillRect(-5000, -5000, 10000, 10000); // in this case the point is to still show when panning and zooming.
				ctx.stroke();
				ctx.globalCompositeOperation = 'source-over'; // just never ever 
			};
		};


	// this switches from imperial to metric and triggers re-calculating.
	// executed when the user clicks the button (onclick event)
		function clkImpMet() {
			var hfbtn = document.getElementById("hiddenContainer")
			if(imgLoaded === 1 && xmlLoaded === 1) {
				hfbtn.style.display = "none";
				if (met === "Metric") {
					met = "Imperial"
				} else {
					met = "Metric"
					cmlElev = cmlElev/METERS_2_FEET; // total ascent feet to meters
					cmlDesc = cmlDesc/METERS_2_FEET; // total descent feet to meters
					cmlDist = cmlDist/KM_2_MI; // total distance mi to km
				};
				processData();
			} else {console.log("ignore user click on impet.");}
		}

	// this switches from time to distance axis and triggers re-calculating.
	// executed when the user clicks the button (onclick event)
		function clkTmDst() {
			var hfbtn = document.getElementById("hiddenContainer")
			if(imgLoaded === 1 && xmlLoaded === 1) {
				hfbtn.style.display = "none";
				if (elex === "d") {elex = "t"} else {elex = "d"};
				processDoc(); // need to reprocess the whole doc since the axis need re-indexed
			} else {console.log("ignore user click on tmdst.");}
		}
		
		
	// ********** SCREENSHOT FUNCTIONALITY 
	//  executed by user clicking screenshot (onclick event)
		function clkScrSht()
		{
			//this works by using the hidden canvas object (inside a div with display:none)
			// all the screen content is sent to that canvas, it's then converted to a blob, and
			// then the blob is streamed to the user as a file. 
			// all file/data handling client side, which allows us to work around cross domain link limitations.
			// One downside right now: screenspace impacts the image, so it picks up extra whitespace, or 
			// loses some image space if the user's window is small.
			var thescrcanvas = document.getElementById("scrCanvas")
			resetCanvas(thescrcanvas);
			html2canvas(document.getElementById("main"), {scale: 1}).then(canvas =>
			{
				canvas.id = "scrCanvas";
				var ss = document.getElementById("scrContainer");
				while (ss.firstChild) { ss.removeChild(ss.firstChild); }
				ss.appendChild(canvas);
				var link = document.createElement('a');
				canvas.toBlob(function(blob) {
					saveAs(blob, "screenshot.png", {type: "image/png"}); 
				});
			});
		};



	// if no file is passed as parameters, the user file button is enabled.
	//  This function is triggered when a file is loaded.
		$("#xmlfile").change(function(e){
			var hfcnt = document.getElementById("hiddenContainer")
			hfcnt.style.display = "none";
			if (this.value.substring(this.value.length-3,this.value.length).toUpperCase() == "FIT") {
				fitFileLoad(this.files[0]);
			} else if (this.value.substring(this.value.length-3,this.value.length).toUpperCase() == "GPX") {
				var selectedFile = document.getElementById("xmlfile").files[0];
				//You could insert a check here to ensure proper file type
				var xmreader = new FileReader();
				xmreader.onload = function(e){
					readXml=e.target.result;
					var parser = new DOMParser();
					if(fileLoaded === 0) {
						actDocType = 2;
						xmlDoc = parser.parseFromString(readXml, "application/xml");
						fileLoaded = 1;
						processDoc();
					};
				}
				xmreader.readAsText(selectedFile);
			};
		});

		xhttp.onload = function () {
			if (this.status === 404) {
				// not found, add some error handling
				console.log("gpx check found no file");
				return;
			} else {
				console.log("gpx check found a file");
				if(route === null) {
				} else {
					// javascript with the libraries I'm using makes XML pretty effortless.
					// so -- Pick out all the trkpt items and extract specfiic attributes:
					if(fileLoaded === 0) {
						actDocType = 2;
						xmlDoc = xhttp.responseXML;
						fileLoaded = 1;
						processDoc();
					};
				}
			}
		}; // runs when a gpx file is loaded from the server, just xml's it and starts the gpx processor.

		checkFIT.onload = function () {
			if (this.status === 404) {
				// not found, add some error handling
				console.log("fit check found no file");
				return;
			} else {
				console.log("fit check found a file");
				if(route === null) {
				} else {
					fitFileLoad(checkFIT.response);
				}
			}
		}; // runs when a fit file is loaded from the server, just xml's it and starts the fit processor.




		function fitFileLoad(file) { // file is a blob.  This interprets it into an object and hands it to processor.
			EFreader.onloadend = function() {
				// Create a EasyFit instance (options argument is optional)

				inEasyFit.parse(this.result, function (error, data) {
					if (error) {
						console.log(error);
					} else {
						if(fileLoaded === 0) {
							actDocType = 1;
							easyFit = data.records;
							fileLoaded = 1;
							processDoc();
						};
					}
				});
			};
			EFreader.readAsArrayBuffer(file);
		}


		function processDoc() { // handler to take the elements out of the object and load them into array.
			xarray = []; 
			yarray = []; 
			zarray = []; 
			elevarray = [];
			tarray = []; 
			darray = [];
			iarray = [];
			hrmArray = [];
			cadArray = [];
			pwrArray = [];
			cmlKCal = 0;
			cmlDist = 0;
			cmlTime = 0;
			cmlElev = 0;
			cmlDesc = 0;
			lastTimestamp = "";
			lastElev = "";
			lastLat = "";
			lastLon = "";

			if(actDocType == 1){
				var lencount = easyFit.length;
			} else if (actDocType == 2) {
				var x = xmlDoc.getElementsByTagName("trkpt");
				var lencount = x.length;
			};
			for (i = 0; i < lencount; i++) { 
				if(actDocType == 1) {
					thisLon=easyFit[i].position_long;
					if(thisLon>180){thisLon=thisLon-360;}; // encoding format thing
					thisLat=easyFit[i].position_lat;
					if(thisLat>180){thisLat=thisLat-360;}; // encoding format thing
					thisElev=(easyFit[i].altitude-1)*1000;
					thisTimestamp=easyFit[i].timestamp;
					if(easyFit[i].heart_rate) {thisHRM=easyFit[i].heart_rate} else {thisHRM=0};
					if(easyFit[i].cadence) {thisCAD=easyFit[i].cadence} else {thisCAD=0};
					if(easyFit[i].power) {thisPWR=easyFit[i].power} else {thisPWR=0};

				} else if(actDocType == 2) {

					thisLon=(x[i].getAttribute("lon") *1);
					thisLat=(x[i].getAttribute("lat") *1);
					thisElev=(x[i].getElementsByTagName("ele")[0].childNodes[0].nodeValue *1);
					// for time convert from standardized text into time value and preserve in var
					thisTimestamp = new Date(x[i].getElementsByTagName("time")[0].childNodes[0].nodeValue);
					if(x[i].getElementsByTagName("power")[0]) {thisPWR=(x[i].getElementsByTagName("power")[0].childNodes[0].nodeValue *1)} else {thisPWR=0};
					if(x[i].getElementsByTagName("gpxdata:hr")[0]) {thisHRM=(x[i].getElementsByTagName("gpxdata:hr")[0].childNodes[0].nodeValue *1)} else {thisHRM=0};
					if(x[i].getElementsByTagName("gpxdata:cadence")[0]) {thisCAD=(x[i].getElementsByTagName("gpxdata:cadence")[0].childNodes[0].nodeValue *1)} else {thisCAD=0};

				}
				if(i > 0) { // first iteration: Initialize to zero.
					// thereafter, calculate the difference and add too the cumulative values.
					cmlTime=cmlTime+Math.abs(thisTimestamp-lastTimestamp);
					
					cmlKCal= cmlKCal+thisPWR* (Math.abs(thisTimestamp-lastTimestamp)); // ex: 200 watts * 1 hour * 3.6 = 720 kcal
					
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

				xarray[i]=(thisLon+(XOFFSET*1))*XFACTOR;
				yarray[i]=(thisLat+(YOFFSET*1))*YFACTOR;
				zarray[i]=thisElev;
				elevarray[i]=thisElev;
				tarray[i]=cmlTime;
				darray[i]=cmlDist;
				hrmArray[i]=thisHRM;
				cadArray[i]=thisCAD;
				pwrArray[i]=thisPWR;

				if(elex === "d") {
					iarray[i]=cmlDist; 
				}  else {
					iarray[i]=cmlTime; 
				};

				// now impose sanity limits on values - just confirm they're not outside predefined limits.
				if(xarray[i] > X_HI_LIM) {xarray[i] = X_HI_LIM;};
				if(xarray[i] < X_LO_LIM) {xarray[i] = X_LO_LIM;};
				if(yarray[i] > Y_HI_LIM) {yarray[i] = Y_HI_LIM;};
				if(yarray[i] < Y_LO_LIM) {yarray[i] = Y_LO_LIM;};
				if(zarray[i] > Z_HI_LIM) {zarray[i] = Z_HI_LIM;};
				if(zarray[i] < Z_LO_LIM) {zarray[i] = Z_LO_LIM;};
				if(elevarray[i] > Z_HI_LIM) {elevarray[i] = Z_HI_LIM;};
				if(elevarray[i] < Z_LO_LIM) {elevarray[i] = Z_LO_LIM;};
			}
			processData();
		}

		function processData() {
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
			imin = Math.min.apply(null, iarray);
			imax = Math.max.apply(null, iarray);
			hrmmin = Math.min.apply(null, hrmArray);
			hrmmax = Math.max.apply(null, hrmArray);
			pwrmin = Math.min.apply(null, pwrArray);
			pwrmax = Math.max.apply(null, pwrArray);
			cadmin = Math.min.apply(null, cadArray);
			cadmax = Math.max.apply(null, cadArray);
			
			
			var sum = 0;
			for( var i = 0; i < hrmArray.length; i++ ){
				sum += parseInt(hrmArray[i], 10 ); //don't forget to add the base
			}
			hrmavg = sum/hrmArray.length;
			
			sum = 0;
			for( var i = 0; i < cadArray.length; i++ ){
				sum += parseInt(cadArray[i], 10 ); //don't forget to add the base
			}
			cadavg = sum/cadArray.length;
			
			sum = 0;
			for( var i = 0; i < pwrArray.length; i++ ){
				sum += parseInt(pwrArray[i], 10 ); //don't forget to add the base
			}
			pwravg = sum/pwrArray.length;

			var canCon = document.getElementById("hiddenCadGphCon")
			if(cadmax > 0 && route === null){

				canCon.style.display = "block";
				console.log("cadence found range: "+cadmin+" - "+cadmax);
				if( (cadmax - cadmin) < 2 ) {
					// if the values are too low to scale
					cadscale = 25; // just scale at the closest level allowed
					cadoffset = -55; // and offset to near the bottom
				} else {
					// otherwise scale
					cadscale = 60/(cadmax-cadmin);
					cadoffset = 0-cadmin; // (zmin*zfactor2)+20;
				}
			} else {
				canCon.style.display = "none";
				console.log("no cadence");
				cadscale=0;
				cadoffset=0;
			};
			var canHrm = document.getElementById("hiddenHrmGphCon")
			if(hrmmax > 0 && route === null){
				canHrm.style.display = "block";
				console.log("hrm found range: "+hrmmin+" - "+hrmmax);
				
				if( (hrmmax - hrmmin) < 2 ) {
					// if the values are too low to scale
					hrmscale = 25; // just scale at the closest level allowed
					hrmoffset = -55; // and offset to near the bottom
				} else {
					// otherwise scale
					hrmscale = 60/(hrmmax-hrmmin);
					hrmoffset = 0-hrmmin; // (zmin*zfactor2)+20;
				}
			} else {
				canHrm.style.display = "none";
				console.log("no hrm");
				hrmscale=0;
				hrmoffset=0;
			};
			var canPwr = document.getElementById("hiddenPwrGphCon")
			if(pwrmax > 0 && route === null){
				canPwr.style.display = "block";
				console.log("power found range: "+pwrmin+" - "+pwrmax);
				if( (pwrmax - pwrmin) < 2 ) {
					// if the values are too low to scale
					pwrscale = 25; // just scale at the closest level allowed
					pwroffset = -55; // and offset to near the bottom
				} else {
					// otherwise scale
					pwrscale = 60/(pwrmax-pwrmin);
					pwroffset = 0-pwrmin; // (zmin*zfactor2)+20;
				};
			} else {
				canPwr.style.display = "none";
				console.log("no power");
				pwrscale=0;
				pwroffset=0;
			};

	// determine conversion for z into pixel value (0-60)
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
			
			if (URLPARAMS.get('route') === null) {
				// not logging in this case
				console.log("not logging empty");
			} else {
				document.getElementById("frmGPX").src = 'SubmitGPXData.php?route='+URLPARAMS.get('route')+'&dist='+cmlDist+'&asc='+cmlElev+'&desc='+cmlDesc;
				console.log("just tried to submit GPX/Fit data back to db.  Route = "+URLPARAMS.get('route')+" dist = "+cmlDist+" asc = "+cmlElev+" desc = "+cmlDesc);
			};

			// convert from default metric values to imperial as needed.
			if (met === "Imperial") {
				zmin = zmin*METERS_2_FEET; // lowest elevation meters to feet
				zmax = zmax*METERS_2_FEET; // highest elevation meters to feet
				cmlElev = cmlElev*METERS_2_FEET; // total ascent meters to feet
				cmlDesc = cmlDesc*METERS_2_FEET; // total descent meters to feet
				cmlDist = cmlDist*KM_2_MI; // total distance km to mi
				elunit = "ft"; // update unit tags
				dstunit = "mi";
			} else {
				elunit = "m"; // update unit tags
				dstunit = "km";
			};

	// determine convesion for index into animation index (0-243 because 243 works with this width)
			// intent is to display in ~4 seconds, so time equates to 16.67 milliseconds, ~60fps
			ifactor2 = 243/(Math.max.apply(null, iarray) - Math.min.apply(null, iarray));
			ioffset2 = 0-(imin*ifactor2);
			
			lastZoom = zoomfactorxy;
			lastTransX = translatefactorx;
			lastTransY = translatefactory; 

			// Now adjust to fit scales and zooms.
			for (var i=0, len=xarray.length; i<len; i++) { 
				zarray[i] = ((zarray[i]*1)+(zoffset2*1))*zfactor2;
				iarray[i] = ((iarray[i]*1)+(ioffset2*1))*ifactor2;
			}

	// this chunk determine optimal scaling on each axis to fit most of the route onscreen
			zoomfactory = (655/(ymax - ymin));
			zoomfactorx = (1280/(xmax - xmin));
			if (zoomfactory < zoomfactorx) {
				zoomfactorxy = zoomfactory*.98;
				translatefactory = ymin*-1*zoomfactorxy;
				translatefactory = translatefactory + (655*.01);
				translatefactorx = xmin*-1*zoomfactorxy; 
				// now to center the axis not filled by zoom:
				xmapoffset =   
					(((1280/zoomfactorxy) // total pixel space
					-(xmax-xmin)) /2 // subtract occupied pixel space which gives empty, divide by half to be one side
					)*zoomfactorxy; // scaling factor applied to result
				ymapoffset = 0;
				translatefactorx = translatefactorx + (1280*.01) + xmapoffset;
			} else {
				zoomfactorxy = zoomfactorx*.98;
				translatefactorx = xmin*-1*zoomfactorxy; 
				translatefactorx = translatefactorx + (1280*.01); // this is NICE :) 
				translatefactory = ymin*-1*zoomfactorxy;
				ymapoffset =   
					(((655/zoomfactorxy) // total pixel space
					-(ymax-ymin)) /2 // occupied pixel space /2
					)*zoomfactorxy; // scaling factor applied to result
				ymapoffset = 0;
				translatefactory = translatefactory + (655*.01) + ymapoffset;
			}
	
			// now let's calculate the animated zoom sequence.
			zCount = 0;
//			if(lastZoom == 1 && lastTransX == 0 && lastTransY == 0) {
			zFrames = 60;
			for (i = 0; i <= zFrames; i++) { 
					aniZ[i] = lastZoom+(((zoomfactorxy-lastZoom)/60)*i);
					aniTX[i] = lastTransX+(((translatefactorx-lastTransX)/60)*i);
					aniTY[i] = lastTransY+(((translatefactory-lastTransY)/60)*i);
			}

		// dynamic line sizing here :)
			mpLineWidth = (zoomfactorxy*ZOOM2LINE_FACTOR)+ZOOM2LINE_OFFSET;
			if(mpLineWidth<MIN_LN_WIDTH){mpLineWidth=MIN_LN_WIDTH};
			mpAniR = mpLineWidth*2.25;

		//  *** ANALYZE DATASET COMPLETE ***
		// here's where we confirm both xml and image are done, and if so, start the drawings.
			// initially thought this was a bit klunky, but I think it works well here.
			xmlLoaded = 1;
			if(imgLoaded === 1 && xmlLoaded === 1) {
				var thm = drawLoop();
				fileLoaded = 0;
				// reset for further file loads if needed
			};
		};
		
	// helpful to retain standard way of clearing canvases for redraw.
	// note that this function clears transforms and scales - this is done because zoom is not absolute, but relative -- so rather than tracking and adjusting, better to just clear between frames.
		function resetCanvas(inCvs)
		{
			var inCtx = inCvs.getContext("2d");
			inCtx.setTransform(1, 0, 0, 1, 0, 0);
			inCtx.clearRect(0,0, inCvs.width, inCvs.height);
		};

	// More of a loop initializer.  Name stuck after I changed things :) The 0 check is to prevent overlaps.
	// executed any time both image and gpx loads are completed (when one completes, it confirms the other and runs this if true)
		function drawLoop() {
			if(currAniIx == 0 && zCount == 0) {
				zAniRun = 1;
				zRtRun = 0;
				window.requestAnimationFrame(drawZoom);
			};
		}
		
		function drawZoom() { 
			
			ctx = mcanvas.getContext("2d"); // map canvas context
			ctx.globalCompositeOperation = 'source-over';
			// zoom and pan!
			resetCanvas(mcanvas);
			ctx.translate(aniTX[zCount],aniTY[zCount]);
			ctx.scale(aniZ[zCount], aniZ[zCount]);  // same zoom factor to avoid weird aspect ratios
			ctx.drawImage(img, 0, 0);
			
			zCount += 1;
			if( zCount > zFrames || zAniRun == 0) {
				zCount= 0; // and we're done with anim.
				zAniRun = 0;
				zRtRun = 1;
				currAniIx = 0;
				window.requestAnimationFrame(drawMapAndElv);
			}
			else {
				window.requestAnimationFrame(drawZoom); // call itself again when finished to continue animating- at least until frames run out. Each time it executes, it waits until the frame is ready and then draws, then executes itself again, unti the counter hits the ANIMATED_FRAMES (max).
			};
		}
	// this is the function, run each frame, to draw the map and elevation profile.
	// executed first when the loop is initialized by drawloop then reruns itself.



	// this function displays frame number (frameNumber).
	// it's called by the animation as well as by the mouseover positioning.
		function displayFrame(frameNumber) {
			ctx.drawImage(img, 0, 0);
			// now prep & draw route;
			ctx.beginPath();
			ctx.moveTo(xarray[0],yarray[0]);

			resetCanvas(elvc);
			elvctx = elvc.getContext("2d"); // elevation profile canvas context
			elvctx.globalCompositeOperation = 'source-over';

			resetCanvas(hrmc);
			hrmctx = hrmc.getContext("2d");
			hrmctx.globalCompositeOperation = 'source-over';

			resetCanvas(pwrc);
			pwrctx = pwrc.getContext("2d");
			pwrctx.globalCompositeOperation = 'source-over';

			resetCanvas(cadc);
			cadctx = cadc.getContext("2d");
			cadctx.globalCompositeOperation = 'source-over';

			elvctx.strokeStyle = ELV_LN_COLOR;
			elvctx.fillStyle = ELV_FL_COLOR;
			hrmctx.strokeStyle = HRM_LN_COLOR;
			hrmctx.fillStyle = HRM_FL_COLOR;

 			pwrctx.strokeStyle = PWR_LN_COLOR;
			pwrctx.fillStyle = PWR_FL_COLOR;
			cadctx.strokeStyle = CAD_LN_COLOR;
			cadctx.fillStyle = CAD_FL_COLOR;
			
			elvctx.beginPath();
			elvctx.moveTo(0,63);
			elvctx.lineTo(0,60-zarray[0]);

			hrmctx.beginPath();
			hrmctx.moveTo(0,63);
			hrmctx.lineTo(0,60-((hrmArray[0]+hrmoffset)*hrmscale));

			cadctx.beginPath();
			cadctx.moveTo(0,63);
			cadctx.lineTo(0,60-((cadArray[0]+cadoffset)*cadscale));

			pwrctx.beginPath();
			pwrctx.moveTo(0,63);
			pwrctx.lineTo(0,60-((pwrArray[0]+pwroffset)*pwrscale));

			// use path to trace the elevation line, then loop back along the bottom edge of the canvas to create a 'shape'
			// then close and fill it.
			aniIndex=0;
			for (var i=1, len=xarray.length; i<len; i++) { // note: assumes length alignment x/y/z/t
				ctx.lineTo(xarray[i],yarray[i]);
				elvctx.lineTo((iarray[i]*5),60-zarray[i]);
				hrmctx.lineTo((iarray[i]*5.25),60-((hrmArray[i]+hrmoffset)*hrmscale));
				cadctx.lineTo((iarray[i]*5.25),60-((cadArray[i]+cadoffset)*cadscale));
				pwrctx.lineTo((iarray[i]*5.25),60-((pwrArray[i]+pwroffset)*pwrscale)); 
				// this just keeps updating until it finds the last rendedered
				// thus the result after the loop will be location for the dot now.
				if(iarray[i] <= frameNumber) {
					elAniX = (iarray[i]*5);
					elAniY = 60-zarray[i];
					hrmAniX = (iarray[i]*5.25);
					hrmAniY = 60-((hrmArray[i]+hrmoffset)*hrmscale);
					cadAniX = (iarray[i]*5.25);
					cadAniY = 60-((cadArray[i]+cadoffset)*cadscale);
					pwrAniX = (iarray[i]*5.25);
					pwrAniY = 60-((pwrArray[i]+pwroffset)*pwrscale);
					mpAniX = (xarray[i]);
					mpAniY = yarray[i];
					aniIndex = i;
				}
			}
			// ok so lines with border colors turned out to be absurdly easy :)
			ctx.strokeStyle = maplineedge;
			ctx.lineWidth = mpLineWidth;
			ctx.stroke();
			ctx.strokeStyle = mapline;
			ctx.lineWidth = mpLineWidth*LINE_EDGE_MULT;
			ctx.stroke();


			// now close off the shapes and fill
			elvctx.lineTo(1214,63);
			elvctx.lineTo(0,63);
			elvctx.closePath();
			elvctx.stroke();
			elvctx.fill();
			hrmctx.lineTo(1280,63);
			hrmctx.lineTo(0,63);
			hrmctx.closePath();
			hrmctx.stroke(); 
			hrmctx.fill();
			pwrctx.lineTo(1280,63);
			pwrctx.lineTo(0,63);
			pwrctx.closePath();
			pwrctx.stroke();
			pwrctx.fill();
			cadctx.lineTo(1280,63);
			cadctx.lineTo(0,63);
			cadctx.closePath();
			cadctx.stroke();
			cadctx.fill();

		// And finally, trace with an animated dot:

			ctx.font = "12px Arial";
			elvctx.font = "12px Arial";
			hrmctx.font = "12px Arial";
			cadctx.font = "12px Arial";
			pwrctx.font = "12px Arial";

			ctx.beginPath();
			ctx.fillStyle = mapdot;
			ctx.arc(mpAniX, mpAniY, mpAniR, 0, 2 * Math.PI, false);
			ctx.closePath();
			ctx.fill();

			elvctx.beginPath();
			elvctx.fillStyle = ELV_LN_COLOR;
			elvctx.arc(elAniX, elAniY, ANI_DOT_R, 0, 2 * Math.PI, false);
			elvctx.closePath();
			elvctx.fill();

			hrmctx.beginPath();
			hrmctx.fillStyle = HRM_LN_COLOR;
			hrmctx.arc(hrmAniX, hrmAniY, ANI_DOT_R, 0, 2 * Math.PI, false);
			hrmctx.closePath();
			hrmctx.fill();

			cadctx.beginPath();
			cadctx.fillStyle = CAD_LN_COLOR;
			cadctx.arc(cadAniX, cadAniY, ANI_DOT_R, 0, 2 * Math.PI, false);
			cadctx.closePath();
			cadctx.fill();

			pwrctx.beginPath();
			pwrctx.fillStyle = PWR_LN_COLOR;
			pwrctx.arc(pwrAniX, pwrAniY, ANI_DOT_R, 0, 2 * Math.PI, false);
			pwrctx.closePath();
			pwrctx.fill();

		// Now display z axis min max and chosen axis type
			elvctx.fillStyle = AXIS_COLOR;
			elvctx.fillText(Math.round(zmax)+" "+elunit, AX_MX_LABEL_X, AX_MX_LABEL_Y);
			elvctx.fillText(Math.round(zmin)+" "+elunit, AX_MN_LABEL_X, AX_MN_LABEL_Y);
			if (elex === "d") {
				elvctx.fillText("X Axis: Dist", AX_CT_LABEL_X,AX_CT_LABEL_Y);
			} else {
				elvctx.fillText("X Axis: Time", AX_CT_LABEL_X,AX_CT_LABEL_Y);
			};
			elvctx.fillText("Elevation", AX_LABEL_X, AX_LABEL_Y);
			elvctx.stroke();
			
			hrmctx.fillStyle = AXIS_COLOR;
			hrmctx.fillText(Math.round(hrmmax)+" bpm", AX_MX_LABEL_X, AX_MX_LABEL_Y);
			hrmctx.fillText("Avg: "+Math.round(hrmavg)+"bpm", AX_CT_LABEL_X,AX_CT_LABEL_Y);
			hrmctx.fillText(Math.round(hrmmin)+" bpm", AX_MN_LABEL_X, AX_MN_LABEL_Y);
			hrmctx.fillText("Heart Rate", AX_LABEL_X, AX_LABEL_Y);
			hrmctx.stroke();

			pwrctx.fillStyle = AXIS_COLOR;
			pwrctx.fillText(Math.round(pwrmax)+" w", AX_MX_LABEL_X, AX_MX_LABEL_Y);
			pwrctx.fillText("Avg: "+Math.round(pwravg)+"w", AX_CT1_LABEL_X,AX_CT1_LABEL_Y);
			pwrctx.fillText("kCal: "+Math.round(cmlKCal/1000000), AX_CT2_LABEL_X,AX_CT2_LABEL_Y);
			pwrctx.fillText(Math.round(pwrmin)+" w", AX_MN_LABEL_X, AX_MN_LABEL_Y);
			pwrctx.fillText("Power", AX_LABEL_X, AX_LABEL_Y);
			pwrctx.stroke();
			
			cadctx.fillStyle = AXIS_COLOR;
			cadctx.fillText(Math.round(cadmax)+" rpm", AX_MX_LABEL_X, AX_MX_LABEL_Y);
			cadctx.fillText("Avg: "+Math.round(cadavg)+"rpm", AX_CT_LABEL_X,AX_CT_LABEL_Y);
			cadctx.fillText(Math.round(cadmin)+" rpm", AX_MN_LABEL_X, AX_MN_LABEL_Y);
			cadctx.fillText("Cadence", AX_LABEL_X, AX_LABEL_Y);
			cadctx.stroke();


		// Now display ride stats.
			elvctx.fillStyle = AXIS_COLOR;
			elvctx.font = "12px Arial";
			elvctx.fillText("Dist:   "+Math.round(cmlDist*10)/10+" "+dstunit, ELV_DIST_LABEL_X, ELV_DIST_LABEL_Y);

			var hrs=Math.trunc(cmlTime/(3600000));
			var mins=Math.trunc((cmlTime-(hrs*3600000))/60000);
			if(mins<10) {dph=hrs+":0"+mins+":";} else {dph=hrs+":"+mins+":";};
			var secs=Math.trunc((cmlTime-(hrs*3600000)-(mins*60000))/1000);
			if(secs<10) {dph=dph+"0"+secs;} else {dph=dph+""+secs;};
			elvctx.fillText("Time: "+dph, ELV_TIME_LABEL_X, ELV_TIME_LABEL_Y);
			elvctx.fillText("Asc:   "+Math.round(cmlElev)+" "+elunit, ELV_ASCE_LABEL_X, ELV_ASCE_LABEL_Y);
			elvctx.fillText("Desc: "+Math.round(cmlDesc)+" "+elunit, ELV_DESC_LABEL_X, ELV_DESC_LABEL_Y);
			elvctx.stroke();
			
		// draw backgrounds last, and draw behind.
			ctx.globalCompositeOperation = 'destination-over'
			ctx.fillStyle = mapbg;
			ctx.fillRect(-5000, -5000, 10000, 10000); // in this case the point is to still show when panning and zooming.
			ctx.stroke();
			ctx.globalCompositeOperation = 'source-over'; // just never ever leave this on background it's annoying :) 
			elvctx.globalCompositeOperation = 'destination-over';
			elvctx.fillStyle = ELV_BG_COLOR;
			elvctx.fillRect(0, 0, elvc.width, elvc.height);
			elvctx.stroke();
			elvctx.globalCompositeOperation = 'source-over';

			hrmctx.globalCompositeOperation = 'destination-over'
			hrmctx.fillStyle = HRM_BG_COLOR;
			hrmctx.fillRect(-5000, -5000, 10000, 10000);
			hrmctx.stroke();
			hrmctx.globalCompositeOperation = 'source-over';

			cadctx.globalCompositeOperation = 'destination-over'
			cadctx.fillStyle = CAD_BG_COLOR;
			cadctx.fillRect(-5000, -5000, 10000, 10000);
			cadctx.stroke();
			cadctx.globalCompositeOperation = 'source-over';

			pwrctx.globalCompositeOperation = 'destination-over'
			pwrctx.fillStyle = PWR_BG_COLOR;
			pwrctx.fillRect(-5000, -5000, 10000, 10000);
			pwrctx.stroke();
			pwrctx.globalCompositeOperation = 'source-over';



	//  *** BUTTON CANVAS HANDLING *** -- merged with elevation canvas to make formatting easier.
			// implement buttons - basically just make 3 21*63 image-buttons and stripe them, click and reload with appropriate.
			elvctx.fillStyle=BTN_ATLAS_COLOR;
			elvctx.fillRect(LINK_1_X,LINK_1_Y,link1Width,LINK_1_HEIGHT);
			elvctx.fillStyle=BTN_ROAD_COLOR;
			elvctx.fillRect(LINK_2_X,LINK_2_Y,LINK_2_WIDTHh,LINK_2_HEIGHT);
			elvctx.fillStyle=BTN_SATL_COLOR;
			elvctx.fillRect(LINK_3_X,LINK_3_Y,LINK_3_WIDTHh,LINK_3_HEIGHT);
			elvctx.stroke();
			
			// button labels
			elvctx.fillStyle='rgb(0, 0, 0)';
			elvctx.font = "12px Arial";
			elvctx.fillText(LINK_1_TEXT, LINK_1_X+TEXT_OFFSET_X, LINK_1_Y+TEXT_OFFSET_Y);
			elvctx.fillText(LINK_2_TEXT, LINK_2_X+TEXT_OFFSET_X, LINK_2_Y+TEXT_OFFSET_Y);
			elvctx.fillText(LINK_3_TEXT, LINK_3_X+TEXT_OFFSET_X, LINK_3_Y+TEXT_OFFSET_Y);
			elvctx.stroke();

			}

		function drawMapAndElv() { 
			displayFrame(currAniIx);
			var hfbtn = document.getElementById("hiddenContainer")

			currAniIx += 1;
			if( currAniIx > ANIMATED_FRAMES || zRtRun == 0) {
				currAniIx = 0; // and we're done with anim.
				zRtRun=0;
				if (route === null) {hfbtn.style.display = "block"};
			}
			else {
				window.requestAnimationFrame(drawMapAndElv); // call itself again when finished to continue animating.
				// at least until frames run out.
				// each time it executes, it waits until the frame is ready and then draws.
				// then executes itself again, unti the counter hits the ANIMATED_FRAMES (max).
			};

		};

	// this pics up the map and loads it into the image object that's used by the canvas.
	// note: this loading takes quite a bit of time (from a code perspective), so it happens
	// async.  When the "src" of the image is changed loading is initiated, and when laoding
	// completes it will trigger the function that's set to trigger on load of img: img.onload.
	//  If the user changes it, it'll reset here and when loaded, again it will trigger the onload.
	// executed when called by initial load, or again when a user changes map type.
		function procMapLoad() {
			if (maptype === "atlas") {
				img.src = ATLAS_PNG; //'images/map_atls.png'; // Set source path -- triggers loading!
				mapbg = ATLAS_BG;
				mapline = ATLAS_LN; 
				maplineedge = ATLAS_LN_EDGE; 
				mapdot = mapline;
			} else if (maptype === "road") {
				img.src = ROAD_PNG;
				mapbg = ROAD_BG;
				mapline = ROAD_LN;
				maplineedge = ROAD_LN_EDGE; 
				mapdot = mapline;
			} else if (maptype === "satellite") {
				img.src = SATL_PNG;
				mapbg = SATL_BG; 
				mapline = SATL_LN; 
				maplineedge = SATL_LN_EDGE; 
				mapdot = mapline;
			} else  {
				maptype = DEFAULT_MAP_TYPE;
				img.src = ATLAS_PNG;
				mapbg = ATLAS_BG;
				mapline = ATLAS_LN;
				maplineedge = ATLAS_LN_EDGE; 
				mapdot = mapline;
			}
		};
		

// *************** MAIN EXECUTION BEGINS HERE WHEN PAGE IS LOADED *********************
// all other functions are triggered by events or by this script.
// so if you're trying to follow, start here :)

		//  This function is executed when the window loads
		window.onload = function() {
			// initialize values:
			elunit = "m"; // displays the type of elevation unit
			dstunit = "km"; // displays the type of distance unit
			elex = DEFAULT_ELEX; // default
			// url parameter handling
			maptype = URLPARAMS.get('maptype'); // this carries user selection of the map type
			if (maptype === null) {
				maptype = DEFAULT_MAP_TYPE; // default
			}	
			met = URLPARAMS.get('met'); // this carries user selection of the units of measurement
			if (met === null){
				met = DEFAULT_MET; // default
			}
			route = URLPARAMS.get('route'); // this drives loading of the file.  Required.
			// main variable initialization complete! :)	

		// now trigger file & image loading, which is all async so not consumed in this loop..
		//  Instead each has an "onload" function triggered when they finish loading.
			if (route === null) {
			// If no route is shared, user is viewing directly - Expose controls and let them load their file.
			// see $("#xmlfile").change for that function.
				var hcont = document.getElementById("hiddenContainer")
				hcont.style.display = "block";
				document.body.style.backgroundColor = VISIBLE_BODY_BG;
				var tbcont = document.getElementById("topbanner")
				tbcont.style.display = "block";

			} else {
				// this is tricky to follow becuase of asynchronous standards. 
				// logical description is check for .fit an if found execute fit routine.  
				//  Then if not found, then check for .gpx and if found execute gpx routine.
				// Async means that has to be set up as:
				// Set up a function to run when gpx file checking is complete. If found, load .gpx routine.
				//  If not, end.
				// Set up a function to run when fit file checking is complete. If found, load .fit routine.
				//  If not, start checking for the gpx file.
				// main code execution begins:: start checking for the fit file.  

				fitfilename = "gpx/"+route+".fit";
				gpxfilename = "gpx/"+route+".gpx";

			// This function is triggered when a 'load' is complete on the xhttp object.  xhttp is our xml file container, so when this
			// is triggered it means the xml file load is complete and we can parse values into the array and cultivate/analyze the data.

				checkFIT.open('GET',fitfilename,true)
				checkFIT.responseType = 'blob';
				checkFIT.send();

				xhttp.open("GET",gpxfilename,true)
				xhttp.responseType = '';
				xhttp.send();

			// Nuttin'.  Maybe this should transition to user mode?
			}
			
		// now this triggers image loading.  When it's finished, it'll load the img.onload routine.
			procMapLoad();
		};
	</script>

<p id="besideMouse"></p>
</body>
</html>







