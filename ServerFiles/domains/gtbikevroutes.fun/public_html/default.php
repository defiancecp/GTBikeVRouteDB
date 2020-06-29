<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="RTStyles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</head> 
<body style="background-color:#49422D;">
<div class="container-fluid" >
  <div class="topbanner">
  <!-- <style>body {background-image:URL("images/gtbv_banr.jpg");}</style> -->
    <h1 style="color:#C0D0C8;">GTBike V Routes</h1>
    <p style="color:#C0D0C8;">Below you'll find the current tracked list of routes included in Matthias Urech's GTBike V course repository.<br>This site is super early and missing lots<br>such as callouts to all the awsome people making it possible<br><br><br><br><br><br></p>
  </div>
</div>
    <?php
    include('/home/u544302174/dbscripts/dbconfig.php');
    include('/home/u544302174/dbscripts/getip.php');

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
    echo '<div style="color:#C0D0C8;"><a href="default.php'.$metswtag.$routetag.'"><img src="'.$metsw.'" height="20px"/></a>'.$met.'';
    
    if($route === NULL) {
        echo '</div>';
        $sql = "CALL GetRouteData('".get_ip_address()."','ALL');";
        // execute SQL query submitting the rating.
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
                echo '<table style="color:#a0b0a8;" class="table table-dark table-striped"><tr><th>Route</th><th>Author</th><th>Type</th><th>Distance</th><th>Elevation</th><th>Download</th><th>Map</th><th width="145px">Rating</th></tr>'; // table opener & header row
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') {
                        $velevation = $row["ElevationM"]."m"; // need to intelligently switch
                        $vdistance = $row["DistanceKM"]."km"; // need to intelligently switch
                    } else {
                        $velevation = $row["ElevationFT"]."ft"; // need to intelligently switch
                        $vdistance = $row["DistanceMI"]."mi"; // need to intelligently switch
                    }
                    $mapstring = str_replace('Map</a>','<img src="images/map.png" class="link" height="20px"/></a>',$row["Map"]);
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a></td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td>'.$vdistance.'</td><td>'.$velevation.'</td><td><a href="data:text/plain;charset=UTF-8,https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json" download="'.$row["RouteName"].'.json"><img src=/images/dl.png class="link" height="20px"></a></td><td>'.$mapstring.'</td><td><iframe src="'.$ratinglink.'" class="embed-responsive-item" width="100%" height="20px" allowtransparency="true" style="border:0px solid black;"></iframe></td></tr>';
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
    else
    {
        echo '&nbsp;&nbsp;&nbsp;<a href="default.php?met='.$met.'"><img src="images/bkup.png" height="20px"/></a>Return';
        echo '</div>';
        $sql = "CALL GetRouteData('".get_ip_address()."','".$route."');";
        // execute SQL query submitting the rating.
        // execute SQL query submitting the rating.
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
                echo '<table style="color:#a0b0a8;" class="table table-dark table-striped"><tr><th>Route</th><th>Author</th><th>Type</th><th>Distance</th><th>Elevation</th><th>Download</th><th>Map</th><th width="145px">Rating</th></tr>'; // table opener & header row
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') {
                        $velevation = $row["ElevationM"]."m"; // need to intelligently switch
                        $vdistance = $row["DistanceKM"]."km"; // need to intelligently switch
                    } else {
                        $velevation = $row["ElevationFT"]."ft"; // need to intelligently switch
                        $vdistance = $row["DistanceMI"]."mi"; // need to intelligently switch
                    }
                    $mapstring = str_replace('Map</a>','<img src="images/map.png" class="link" height="20px"/></a>',$row["Map"]);
                    $mappic = str_replace('">Map</a>','',str_replace('<a href="','',$row["Map"]));
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a></td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td>'.$vdistance.'</td><td>'.$velevation.'</td><td><a href="data:text/plain;charset=UTF-8,https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json" download="'.$row["RouteName"].'.json"><img src=/images/dl.png class="link" height="20px"></a></td><td>'.$mapstring.'</td><td><iframe src="'.$ratinglink.'" class="embed-responsive-item" width="100%" height="20px" allowtransparency="true" style="border:0px solid black;"></iframe></td></tr>';
                    echo '<tr><td colspan="8">'.$row["Description"].'</td></tr>';
                    echo '<tr><td colspan="8" height=100%><img src="'.$mappic.'"/></td></tr>';
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
</body>
</html>