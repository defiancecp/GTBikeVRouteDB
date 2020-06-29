<?php
function myServername(){
    return "localhost";
}   
function myUsername(){
    return "u544302174_defiancecp";
}   
function myDatabase(){
    return"u544302174_GTBikeVRoutes";
}   
function myPassword(){
    return "UserPassword";
}
function myConnection(){
// parameters
// Create connection
    $dbcservername = myServername();
    $dbcusername = myUsername();
    $dbcpassword = myPassword();
    $dbcdatabase = myDatabase();
    $myConnection = mysqli_connect($dbcservername, $dbcusername, $dbcpassword, $dbcdatabase);
// Check connection
    if (!$myConnection) {
        die("Connection failed: " . mysqli_connect_error());
    }
// return connection
    return $myConnection;
}   
?>