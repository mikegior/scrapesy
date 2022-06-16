<?php
// Initialize session
session_start();

if($_SESSION["isAdmin"] == "Yes")
{
    // Call MySQL configuration file
    require_once("../config.php");

    // Get user 'id' from $_GET and place in $uid
    $uid = $_GET["id"];

    // Create new PDO connection
    $db = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME",$DB_USERNAME,$DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare UPDATE statement
    $update_stmt = "UPDATE users SET is_disabled='Yes' WHERE id = " . $db->quote($uid);

    // Execute statement
    $results = $db->query($update_stmt);

    if($results)
    {
        // If disable was successful, redirect to admin.php (User Management)
        header("refresh:0, url=admin.php");
    }
    else
    {
        echo "<pre>Something went wrong!</pre>";
    }
}
else
{
    // If user does not have permissions to use this, send to error.php
    header("Location: ../error.php");
}

?>