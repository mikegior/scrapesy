<?php

// NOTE: Set 'DB_PASSWORD' to new value if you have changed the MySQL root password after installation!
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'scrapesy'); // Set MySQL password here!
define('DB_NAME', 'scrapesy');

// NOTE: Set 'DB_PASSWORD' to new value if you have changed the MySQL root password after installation!
$DB_SERVER = "localhost";
$DB_USERNAME = "root";
$DB_PASSWORD = "scrapesy"; // Set MySQL password here!
$DB_NAME = "scrapesy";

// Attempt to connect to MySQL using defined parameters
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection to MySQL; throw an error if connection failed
if($conn === false)
{
    die("ERROR: Could not connect to database: " . mysqli_connect_error());
}

?>
