<?php

date_default_timezone_set('Asia/Kolkata');

$host = "localhost";
$username = "root";
$password = "";
$database_name = "diet_management";
$port = 3306;

$conn = new mysqli($host,$username,$password,$database_name,$port);
$conn->set_charset("utf8mb4");

?>