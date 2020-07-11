<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="SRStyles.css">
</head> 
<body>
    <?php
	/* Basically the straight HTML does nothing but load the stylesheet and required libraries (mostly for boostrap) */
    include('/home/u544302174/dbscripts/dbconfig.php');
    include('/home/u544302174/dbscripts/getip.php');
    // input parameters
    $route = $_GET['route'];
    $rating = $_GET['rating'];
    $ratingcount = $_GET['ratingcount'];
    if($_GET['submit'] === "TRUE") {
        $submit = TRUE;
    } else {
        $submit = FALSE;
    }
    // without a route this is nonsense so just do nothing and leave.
    if($route === NULL) {
        $route = NULL;
    }
    else
    {
        // if submit parameter not passed assume false.
        if($submit === NULL) {
            $submit = FALSE;
        }
        if($ratingcount === NULL) {
            $ratingcount = 0;
        }
        // if rating isn't passed, set to 5.0 and prevent submit.
        if($rating === NULL) {
            $rating = 5.0;
            $ratingcount = 0;
            $submit = FALSE;
        }

        // Default all to lit, then use simple cascading switch case to
        // turn off as needed per rating.
        $img = array(
            1 => "images/s_l_lit.png",
            2 => "images/s_r_lit.png",
            3 => "images/s_l_lit.png",
            4 => "images/s_r_lit.png",
            5 => "images/s_l_lit.png",
            6 => "images/s_r_lit.png",
            7 => "images/s_l_lit.png",
            8 => "images/s_r_lit.png",
            9 => "images/s_l_lit.png",
            10 => "images/s_r_lit.png"
            );
        // look ma, no breaks! intentional cascading :) 
        switch ($rating) {
            case 0.5: 
                $img[2] = "images/s_r_drk.png";
            case 1.0:
                $img[3] = "images/s_l_drk.png";
            case 1.5:
                $img[4] = "images/s_r_drk.png";
            case 2.0:
                $img[5] = "images/s_l_drk.png";
            case 2.5:
                $img[6] = "images/s_r_drk.png";
            case 3.0:
                $img[7] = "images/s_l_drk.png";
            case 3.5:
                $img[8] = "images/s_r_drk.png";
            case 4.0:
                $img[9] = "images/s_l_drk.png";
            case 4.5:
                $img[10] = "images/s_r_drk.png";
        }

        //now that the right stars are mapped into the ordered array, cycle through it to display the link-driven stars.
        echo '<div id="starContainer" class="container">';
		foreach ($img as $key => $value) {
            echo '<a id="myHref" href=SubmitRating.php?route='.$route.'&rating='.($key*0.5).'&submit=TRUE&ratingcount='.$ratingcount.'><img id="myImg" src="'.$value.'"></a>';
        }
        echo '<small id="smallTxt">('.$ratingcount.')</small>';
        echo '</div>';

        // This is submitting: if submit parameter isn't set, skip and leave.
        if($submit === FALSE OR $rating === NULL) {
            $submit = FALSE;
        }
        else {
            // submit set means the user clicked on a star: Submit their ranking and update display to reflect it.
            // This submits the rating to the db using SubmitRating() stored proc.
            $sql = "CALL SubmitRating('".get_ip_address()."','".$route."',".$rating.");";
            // execute SQL query submitting the rating.
            $conn = myConnection();
            // This is just handling of connection results.  Not used since this query returns no data, but needed for hygiene.
            if ($result = $conn->prepare($sql)) {
                $result->execute();
                $result->store_result();
                $result->free_result();
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        mysqli_close($conn);
    }
    ?>
</body>
</html>


