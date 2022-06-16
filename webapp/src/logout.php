<?php
// Initialize the session
session_start();
 
// Remove any session variables
$_SESSION = array();
 
// Destroy session
session_destroy();
 
// Redirect back to login.php after session_destroy() completes
header("Location: login.php");

exit();

?>
