<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
</head> 
<body style="background-color: transparent; color:white">
    <?php
    include('/home/u544302174/dbscripts/dbconfig.php');
    $route = $_GET['route'];
	if($route === NULL) {
		echo 'no route';
	}
	else {
		$dist = $_GET['dist'];
		if($dist === NULL) {$dist = 'NULL';}
		$asc = $_GET['asc'];
		if($asc === NULL) {$asc = 'NULL';}
		$desc = $_GET['desc'];
		if($desc === NULL) {$desc = 'NULL';}
		$sql = "CALL LogGPXDistElev('".$route."',".$dist.",".$asc.",".$desc.");";
		$conn = myConnection();
		if ($result = $conn->prepare($sql)) {
			$result->execute();
			$result->store_result();
			$result->free_result();
			echo 'uploaded';
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		mysqli_close($conn);
	}
    ?>
</body>
</html>


