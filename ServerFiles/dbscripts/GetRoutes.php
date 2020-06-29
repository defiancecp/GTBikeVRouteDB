<!DOCTYPE html>
<html>
<body>

<h1>Load from git courses list into db</h1>

<?php

include('/home/u544302174/dbscripts/dbconfig.php');

echo "Starting. <br>";

$sql = "TRUNCATE TABLE RouteImportStaging";
$conn = myConnection();


if ($result = $conn->prepare($sql)) {
    $result->execute();
    $result->store_result();
    $result->free_result();
    echo "Staging table cleared. <br>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Now we parse the table.
// First, skip ahead until we find the table marker
$IsolatedText = "";
$RawFile = fopen("https://raw.githubusercontent.com/gtbikev/courses/master/README.md", "r");
$RawText = fgets($RawFile);
$EndPos = strpos($RawText, "COURSE_TABLE_BEGIN");
WHILE ($RawText AND $EndPos === false )
{
    $RawText = fgets($RawFile); // just skip until we find the first row
    $EndPos = strpos($RawText, "COURSE_TABLE_BEGIN");
}
// Now, if it's never found, this will be false, otherwise we go on starting row before table.
IF($RawText)
{
    $RawText = fgets($RawFile); // get next row, first row after marker
    $RowPos = strpos($RawText, "</tr>"); //skip to end of header row; this returns false until then
    WHILE ($RawText AND $RowPos === false)
        {
            $RawText = fgets($RawFile); // get next row, first row after marker
            $RowPos = strpos($RawText, "</tr>"); //skip to end of header row; this returns false until then

        }

    $EndPos = strpos($RawText, "COURSE_TABLE_END");
// Now we should be at the end of the header row.  Ingest full table row of data at a time, looping until end mark
    WHILE ($RawText AND $EndPos === false)
        {    


        $RawText = fgets($RawFile);
        $RowPos = strpos($RawText, "</tr>");
        // Within main table processing loop, ingest a row at a time - that means many rows in the text file
        // look for /tr.

        WHILE ($RawText AND $RowPos === false)
        {
            $RawText .= fgets($RawFile); // append each time.
            $RowPos = strpos($RawText, "</tr>");
        }
        // good - now the whole row is in a string.  
        // remove escape chars:
        $ProcText = mysqli_real_escape_string($conn, $RawText);
        $ProcText = str_replace("\n"," | ",$ProcText);
        
        //And assuming still good data, load it into the database in 3 steps:
        $EndPos = strpos($ProcText, "COURSE_TABLE_END");
        IF ($EndPos === false) 
        {
            // flag the cell locations in the string
            $st1 = strpos ($ProcText, "<td>" , 0)+4;
            $ste1 = strpos ($ProcText, "</td>" , $st1);
            $st2 = strpos ($ProcText, "<td>" , $ste1)+4;
            $ste2 = strpos ($ProcText, "</td>" , $st2);
            $st3 = strpos ($ProcText, "<td>" , $ste2)+4;
            $ste3 = strpos ($ProcText, "</td>" , $st3);
            $st4 = strpos ($ProcText, "<td>" , $ste3)+4;
            $ste4 = strpos ($ProcText, "</td>" , $st4);
            $st5 = strpos ($ProcText, "<td>" , $ste4)+4;
            $ste5 = strpos ($ProcText, "</td>" , $st5);
            $st6 = strpos ($ProcText, "<td>" , $ste5)+4;
            $ste6 = strpos ($ProcText, "</td>" , $st6);
            $st7 = strpos ($ProcText, "<td>" , $ste6)+4;
            $ste7 = strpos ($ProcText, "</td>" , $st7);
            $st8 = strpos ($ProcText, "<td>" , $ste7)+4;
            $ste8 = strpos ($ProcText, "</td>" , $st8);
            $st9 = strpos ($ProcText, "<td>" , $ste8)+4;
            $ste9 = strpos ($ProcText, "</td>" , $st9);
            // and use those locations to pull cell data into variables
            $name = substr($ProcText,$st1,$ste1-$st1);
            $author = substr($ProcText,$st2,$ste2-$st2);
            $map = substr($ProcText,$st3,$ste3-$st3);
            $type = substr($ProcText,$st4,$ste4-$st4);
            $distkm = substr($ProcText,$st5,$ste5-$st5);
            $distmi = substr($ProcText,$st6,$ste6-$st6);
            $elevm = substr($ProcText,$st7,$ste7-$st7);
            $elevft = substr($ProcText,$st8,$ste8-$st8);
            $desc = substr($ProcText,$st9,$ste9-$st9);
            // And finally build & run an insert query from that detail.
            $sql = "INSERT INTO RouteImportStaging (RouteName, Author, Map, Type, DistKM, DistMI, ElevM, ElevFT, Description, UploadDateTime) VALUES ('$name','$author','$map','$type','$distkm','$distmi','$elevm','$elevft','$desc',NOW())";
            if ($result = $conn->prepare($sql)) {
                $result->execute();
                $result->store_result();
                $result->free_result();
                echo "Records for ";
                echo $name;
                echo " inserted. <br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    $sql = "CALL ProcessImportedRoutes();";
    if ($result = $conn->prepare($sql)) {
        $result->execute();
        $result->store_result();
        $result->free_result();
        $result->close();
        echo "New Data Processed. <br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }    

    mysqli_close($conn);

}
ELSE // here's what happens when no table is found.
{
    ECHO("Something went wrong finding the table <br>");
    $IsolatedText = NULL;
}

mysqli_close($conn);
?>

</body>
</html>