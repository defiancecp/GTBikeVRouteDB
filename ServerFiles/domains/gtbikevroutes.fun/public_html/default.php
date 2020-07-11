<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="RTStyles.css">
</head> 
<body>
<div class="container-fluid" >
  <div id="topbanner">
    <h1>GTBike V Routes</h1>
	<br>
	<br>
	<br>
  </div>
</div>
    <?php
    include('/home/u544302174/dbscripts/dbconfig.php');
    include('/home/u544302174/dbscripts/getip.php');
	$unicodeUdArrow = '&#x21f5';

    // parameter handling: Metric vs. Imperial measures
    $met = $_GET['met'];
    if($met === NULL) {
        $met = 'Metric';
    }
    if($met === 'Metric') {
        $opmet = 'Imperial';
        $mettag = '?met='.$met;
        $metswtag = '?met='.$opmet;
        $metsw = 'images/tg_on.png';
    }
    if($met === 'Imperial') {
        $opmet = 'Metric';
        $mettag = '?met='.$met;
        $metswtag = '?met='.$opmet;
        $metsw = 'images/tg_off.png';
    }

    // page displays either focused on a single route, or general of all routes.  This makes that distinction.
    $route = $_GET['route'];
    if($route === NULL) {
        $routetag = '';
    } else {
        $routetag = '&route='.$route;
    }    
   
	// if a route is passed, go to details.  If it isn't, show summary.
    if($route === NULL) {

        $sql = "CALL GetRouteData('".get_ip_address()."','ALL');";
        // execute SQL query to get all the route data.
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
				// now create a table to show results, and then cycle through each result row and build an html "tr" for each row and "td" for each displayed element.
				echo '<div id="fltDescrip">Use this form to filter, or click on column headers for Elevation, Distance, or Rating to sort. The <img id="smallMpIcn" src="images/map.png"/> icon means a route preview is available on this ride detail page.</div>';
				echo '<table id="tblSummary" class="table table-dark table-striped">';

				// Filter Controls (sort is with real header row):
				echo '<tr id="rowSortFilter">';
				echo '<th id="colInRoute"><input id="myInRoute" type="text" onkeyup="fltFn()" placeholder="Search route.." title="Route"></th>';
				echo '<th id="colInAuthor"><input id="myInAuthor" type="text" onkeyup="fltFn()" placeholder="Search author.." title="Author"></th>';
				echo '<th id="colInType"><input id="myInType" type="text" onkeyup="fltFn()" placeholder="Type.." title="Type"></th>';
				echo '<th id="colDst" ><input id="myDistMin" type="number" onkeyup="fltFn()" placeholder="Min distance" title="Min dist"><br>';
				echo '<input id="myDistMax" onkeyup="fltFn()" placeholder="Max distance" title="Max dist"></th>';
				echo '<th id="colElv" ><input id="myElvMin" type="number" onkeyup="fltFn()" placeholder="Min elevation" title="Min elev"><br>';
				echo '<input id="myElvMax" type="number" onkeyup="fltFn()" placeholder="Max elevation" title="Max elev"></th>';
				echo '<th id="colRt"><input stype="number" id="myRateMin" onkeyup="fltFn()" placeholder="Min rating" title="Min rating"></th>';
				echo '<th id="metSwCell"><a href="default.php'.$metswtag.$routetag.'"><img id="metSwImg" src="'.$metsw.'"/></a>'.$met.'</th>';
				echo '<th id="hdnCol">rtg</th><th id="hdnCol">numRating</th><th id="hdnCol">numKM</th><th id="hdnCol">numMI</th><th id="hdnCol">numM</th><th id="hdnCol">numFT</th></tr>';
				// header row
				echo '<tr id="tblHeaderRow">';
				echo '<th>Route</th><th>Author</th><th>Type</th>';
				echo '<th id="cellDstSort" onclick="srtFn(8,1)">Dist   '.$unicodeUdArrow.'</th>';
				echo '<th id="cellElvSort" onclick="srtFn(10,1)">Elev   '.$unicodeUdArrow.'</th>';
				echo '<th id="cellRtgSort" onclick="srtFn(7,1)">Rating   '.$unicodeUdArrow.'</th>';
				echo '<th>Download</th>';
				// these entire columns are hidden - raw numeric values used for javascript sorting and filtering.
				echo '<th id="hdnCol">rtg</th><th id="hdnCol">numRating</th><th id="hdnCol">numKM</th><th id="hdnCol">numMI</th><th id="hdnCol">numM</th><th id="hdnCol">numFT</th></tr>'; // table opener & header row

                // output data of each row
                while($row = $result->fetch_assoc()) { // fetch is pop-like so each row is cycled through. when done, result of the assignment will be false, ending loop.
					// this value contains the definition for each row's rating link URL.  See submitrating.php for implementation of this rating.
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') { // shift displayed metrics based on user selection
                        $velevation = $row["ElevationM"]."m"; 
                        $vdistance = $row["DistanceKM"]."km"; 
                    } else {
                        $velevation = $row["ElevationFT"]."ft"; 
                        $vdistance = $row["DistanceMI"]."mi"; 
                    }
                    $mapstring = "";
					if(file_exists ("./gpx/".$row["RouteName"].".gpx") OR file_exists ("./gpx/".$row["RouteName"].".fit"))  {$mapstring = '    <img id ="grdMap" src="images/map.png"/>';};
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a>'.$mapstring.'</td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td>'.$vdistance.'</td><td>'.$velevation.'</td><td><iframe id="linkIFrame" src="'.$ratinglink.'" class="embed-responsive-item"></iframe></td><td><img src=/images/dl.png class="link" id="tallDlIcn" onclick="downloadResource(\'https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json\',\''.$row["RouteName"].'.json\')"></td><td id="hdnCol">'.$row["CurrentRating"].'</td><td id="hdnCol">'.$row["DistanceKM"].'</td><td id="hdnCol">'.$row["DistanceMI"].'</td><td id="hdnCol">'.$row["ElevationM"].'</td><td id="hdnCol">'.$row["ElevationFT"].'</td></tr>';
				}
					// all tr's done, close it up.
				echo "</table>"; // table opener & header row
			} else {
				echo "0 results";
			}
				// connection cleanup
			$result->free_result();
			$conn->close();
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		// This is the specific route page.  Most of the code is similar to summary.
		echo '<div id="fltDescrip">';

        echo '';

        $sql = "CALL GetRouteData('".get_ip_address()."','".$route."');";
        // execute SQL query gathering table details
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
				echo '<div id="fltDescrip">Detail page for a single route.  Click return to see the full listing.</div>';
                echo '<table id="tblSingle" class="table table-dark table-striped">';// table opener 

				echo '<tr id="rowSortFilter">';
				echo '<th id="retCell"><a class="link" href="default.php'.$mettag.'">Return<img id="stbkup" src="images/bkup.png" /></a></th>';
				echo '<th colspan="5" id="colInRoute"></th>';
				echo '<th id="metSwCell"><a href="default.php'.$metswtag.$routetag.'"><img id="metSwImg" src="'.$metsw.'"/></a>'.$met.'</th></tr>';

			// header row
				echo '<tr id="tblHeaderRow">';
				echo '<th>Route</th>';
				echo '<th>Author</th>';
				echo '<th>Type</th>';
				echo '<th id="cellDstSort" >Dist</th>';
				echo '<th id="cellElvSort">Elev</th>';
				echo '<th id="cellRtgSort">Rating</th>';
				echo '<th>Download</th></tr>';

                // output data of each row
                while($row = $result->fetch_assoc()) {// fetch is pop-like so each row is cycled through. when done, result of the assignment will be false, ending loop.
					// this value contains the definition for each row's rating link URL.  See submitrating.php for implementation of this rating.
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') { // shift displayed metrics based on user selection
                        $velevation = $row["ElevationM"]."m";
                        $vdistance = $row["DistanceKM"]."km";
                    } else {
                        $velevation = $row["ElevationFT"]."ft";
                        $vdistance = $row["DistanceMI"]."mi";
                    }
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a></td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td>'.$vdistance.'</td><td>'.$velevation.'</td><td><iframe src="'.$ratinglink.'" class="embed-responsive-item" id="linkIFrame"></iframe></td><td><img src=/images/dl.png class="link" id="tallDlIcn" onclick="downloadResource(\'https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json\',\''.$row["RouteName"].'.json\')"></td></tr>';
					echo '<tr><td colspan="8">'.$row["Description"].'</td></tr>';
 
					// but for the map link, if there's a GPX file for this route, skip the map and use route preview instead.
					if(file_exists ("./gpx/".$row["RouteName"].".gpx") OR file_exists ("./gpx/".$row["RouteName"].".fit")) { 
						echo '<tr><td colspan="8" id="mapRow"><iframe id="mapFrame" src="MapPreview.php?route='.$row["RouteName"].'&met='.$met.'" class="embed-responsive-item"></iframe></td></tr>';
					}
				}
				echo "</table>"; // table opener & header row
			} else {
				echo "0 results";
			}
			$result->free_result();
			$conn->close();
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
?>

<script>
const minFileThreshold = 200; 
const distOffset = 8;
const elevOffset = 10;
const ratgOffset = 7;

// minimum number of bytes for valid file
// cross-domain download requires client .js to pull into a blob and reconstruct the file.  This is critical because
//  it means the file could possibly be created out of (for example) a 404 message.
//  Intent of this threshold is to throw out bad results instead of giving them to the user as a file, with no indication
//  that it's not a "real" file.

const unicodeUpArrow = '\u2191';
const unicodeDnArrow = '\u2193';
const unicodeUdArrow = '\u21f5';

	// this function is called by filter text entry boxes.  They all call a common routine, which checks all filters.
	function fltFn() {
		const defaultMet = "Metric";
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		var rtTable,filterRoute,filterAuthor,filterType,filterRateMin,filterDistMin,filterElvMin,filterDistMax,filterElvMax,tblRow,tdRoute,tdAuthor,tdType,tdDistance,tdElv,tdRating,rowRoute,rowAuthor,rowType,rowDistance,rowElv,rowRating,met;
		met = urlParams.get('met'); // this carries user selection of the units of measurement
		if (met === null){
			met = defaultMet; // default
		}
		filterRoute = document.getElementById("myInRoute").value.toUpperCase();
		filterAuthor = document.getElementById("myInAuthor").value.toUpperCase();
		filterType = document.getElementById("myInType").value.toUpperCase();
		filterRateMin = document.getElementById("myRateMin").value*1;
		filterDistMin = document.getElementById("myDistMin").value*1;
		filterElvMin = document.getElementById("myElvMin").value*1;
		filterDistMax = document.getElementById("myDistMax").value*1;
		filterElvMax = document.getElementById("myElvMax").value*1;
		rtTable = document.getElementById("tblSummary");
		tblRow = rtTable.getElementsByTagName("tr");
		// basically the routine cycles through each row checking all filters
		for (i = 0; i < tblRow.length; i++) {
			tdRoute = tblRow[i].getElementsByTagName("td")[0];
			if (tdRoute) {rowRoute = tdRoute.textContent || tdRoute.innerText;};
			
			tdAuthor = tblRow[i].getElementsByTagName("td")[1];
			if (tdAuthor) {rowAuthor = tdAuthor.textContent || tdAuthor.innerText;};
			
			tdType = tblRow[i].getElementsByTagName("td")[2];
			if (tdType) {rowType  = tdType .textContent || tdType .innerText;};
			
			if(met === "Imperial"){tdDistance = tblRow[i].getElementsByTagName("td")[distOffset+1];} else {tdDistance = tblRow[i].getElementsByTagName("td")[distOffset];};
			if (tdDistance) {rowDistance = tdDistance.textContent || tdDistance.innerText;};
			
			if(met === "Imperial"){tdElv = tblRow[i].getElementsByTagName("td")[elevOffset+1];} else {tdElv = tblRow[i].getElementsByTagName("td")[elevOffset];};
			if (tdElv) {rowElv = tdElv.textContent || tdElv.innerText;};
			
			tdRating = tblRow[i].getElementsByTagName("td")[ratgOffset];
			if (tdRating) {rowRating = tdRating.textContent || tdRating.innerText;};

			if (tdRating || tdElv || tdDistance || tdType || tdAuthor || tdRoute){
				tblRow[i].style.display = "";
				if (!(rowRoute.toUpperCase().indexOf(filterRoute) > -1)){
					tblRow[i].style.display = "none";
				};
				if (!(rowAuthor.toUpperCase().indexOf(filterAuthor) > -1)){
					tblRow[i].style.display = "none";
				};
				if (!(rowType.toUpperCase().indexOf(filterType) > -1)){
					tblRow[i].style.display = "none";
				};
				if (filterRateMin > 0) {
					if (!(rowRating >= filterRateMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterDistMin > 0) {
					if (!(rowDistance >= filterDistMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterDistMax > 0) {
					if (!(rowDistance <= filterDistMax )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterElvMin > 0) {
					if (!(rowElv >= filterElvMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterElvMax > 0) {
					if (!(rowElv <= filterElvMax )) {
						tblRow[i].style.display = "none";
					};
				};
			};
		}
	}

// this routine is called by sort buttons, which pass it these values:
function srtFn(sIndex, sOrder) {
	// first handle updating the displayed text and onclick functions based on the user click.
	if(sIndex === distOffset) {
		//distance
		if(sOrder === 1){
			document.getElementById("cellDstSort").innerHTML = "Dist - "+unicodeUpArrow;
			document.getElementById("cellDstSort").setAttribute( "onClick", "srtFn("+distOffset+",-1)");
		} else {
			document.getElementById("cellDstSort").innerHTML = "Dist - "+unicodeDnArrow;
			document.getElementById("cellDstSort").setAttribute( "onClick", "srtFn("+distOffset+",1)");
		}
		document.getElementById("cellElvSort").innerHTML = "Elev - "+unicodeUdArrow;
		document.getElementById("cellElvSort").setAttribute( "onClick", "srtFn("+elevOffset+",1)");
		document.getElementById("cellRtgSort").innerHTML = "Rating - "+unicodeUdArrow;
		document.getElementById("cellRtgSort").setAttribute( "onClick", "srtFn("+ratgOffset+",1)");
	} else if(sIndex === elevOffset) {
		//elevation
		if(sOrder === 1){
			document.getElementById("cellElvSort").innerHTML = "Elev - "+unicodeUpArrow;
			document.getElementById("cellElvSort").setAttribute( "onClick", "srtFn("+elevOffset+",-1)");
		} else {
			document.getElementById("cellElvSort").innerHTML = "Elev - "+unicodeDnArrow;
			document.getElementById("cellElvSort").setAttribute( "onClick", "srtFn("+elevOffset+",1)");
		}srtFn
		document.getElementById("cellDstSort").innerHTML = "Dist - "+unicodeUdArrow;
		document.getElementById("cellDstSort").setAttribute( "onClick", "srtFn("+distOffset+",1)");
		document.getElementById("cellRtgSort").innerHTML = "Rating - "+unicodeUdArrow;
		document.getElementById("cellRtgSort").setAttribute( "onClick", "srtFn("+ratgOffset+",1)");
	} else if(sIndex === ratgOffset) {
		//rating
		if(sOrder === 1){
			document.getElementById("cellRtgSort").innerHTML = "Rating- "+unicodeUpArrow;
			document.getElementById("cellRtgSort").setAttribute( "onClick", "srtFn("+ratgOffset+",-1)");
		} else {
			document.getElementById("cellRtgSort").innerHTML = "Rating - "+unicodeDnArrow;
			document.getElementById("cellRtgSort").setAttribute( "onClick", "srtFn("+ratgOffset+",1)");
		}
		document.getElementById("cellDstSort").innerHTML= "Dist - "+unicodeUdArrow;
		document.getElementById("cellDstSort").setAttribute( "onClick", "srtFn("+distOffset+",1)");
		document.getElementById("cellElvSort").innerHTML = "Elev - "+unicodeUdArrow;
		document.getElementById("cellElvSort").setAttribute( "onClick", "srtFn("+elevOffset+",1)");
	}

	var table, rows, switching, i, x, y, shouldSwitch, sOrder, sIndex;
	table = document.getElementById("tblSummary");
	switching = true;
	while (switching) {
		switching = false;
		rows = table.rows;
		for (i = 0; i < rows.length; i++) {
			shouldSwitch = false;
			console.log(sIndex);
			x = rows[i].getElementsByTagName("TD")[sIndex];
			y = rows[i + 1].getElementsByTagName("TD")[sIndex];
			if(x && y) {
				if (sIndex >= 7) { // all the indexes above 8 are numeric
					if ((x.innerHTML*sOrder) > (y.innerHTML*sOrder)) {
						shouldSwitch = true;
						break;
					};
	// this is the broken text sort routine.
				} else {
					if(
						(sOrder = -1 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
						|| (sOrder = 1 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
					) {
						shouldSwitch = true;
						break;
					};
	// end broken text sort routine.
				};
			};
		};
		if (shouldSwitch) {
		rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
		switching = true;
		}
	}
}

function forceDownload(blob, filename) {
	var a = document.createElement('a');
	a.download = filename;
	a.href = blob;
	document.body.appendChild(a);
	a.click();
	a.remove();
}
// this function handles picking up a file from a designated location, processing, and preparing for user.  here we use to distribute routes.
function downloadResource(url, filename) {
  if (!filename) filename = url.split('\\').pop().split('/').pop();
  fetch(url, {
      headers: new Headers({
        'Origin': location.origin
      }),
      mode: 'cors'
    })
    .then(response => response.blob())
    .then(blob => {
      let blobUrl = window.URL.createObjectURL(blob);
	  if(blob.size>minFileThreshold){;
		forceDownload(blobUrl, filename)
	  } else {alert("File not found on github server. This is normal for original included routes (no need to download them, they're included!).  If this was not an included route, please submit an issue in the github repository linked at https://github.com/defiancecp/GTBikeVRouteDB .  Thanks!")};
    })
    .catch(e => console.error(e));
}
</script>
  <div id="bottombanner">
    <p id="intro">Here you'll find the current tracked list of routes included in the <a class="link" href="https://github.com/gtbikev/courses">GTBike V course repository.</a><br>Be sure to check the repository out if you have interest in building your own routes, or to submit issues with routes.<br>For general discussion about the mod or routes, come visit the community <a class="link" href="https://www.facebook.com/groups/1089053124812221">Facebook group.</a><br>
	For issues with this site, development details, of just to take a look at how it all comes together, this project is maintained on <a class="link" href="https://github.com/defiancecp/GTBikeVRouteDB">Github.</a><br>When you stop by, be sure to take a look at the readme for the latest list of attributions and thanks.<br>And finally, these courses all utilize the brilliant <a class="link" href="gta5-mods.com/scripts/gt-bike-v">GT Bike V mod</a> for GTA V.<br><br>
	<b>You can now view your GT Bike V rides mapped on the GTA V Map, using the <a class="link" href="https://gtbikevroutes.fun/MapPreview.php" >GT Bike V ride viewer.</a> </b>
	</p>
</div>
</body>
</html>