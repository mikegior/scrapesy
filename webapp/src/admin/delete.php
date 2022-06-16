<?php

// Initialize session
session_start();

// Check if user is admin
if($_SESSION["isAdmin"] == "Yes")
{
    if(isset($_POST["deleteUser"]))
    {
        // Call MySQL configuration file
        require_once("../config.php");

        // Store User ID in $uid
        $uid = $_POST["delete_id"];

        // Create new PDO connection
        $db = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME",$DB_USERNAME,$DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare DELETE statement
        $delete_stmt = "DELETE FROM users WHERE id=" . $db->quote($uid);

        // Execute statement
        $results = $db->query($delete_stmt);
        
        if($results)
        {
            header("refresh:0, url=admin.php");
        }
        else
        {
            echo "<pre>Something went wrong!</pre>";
        }
    }
}
else
{
    // If not authorized to be here, send to error.php
    header("Location: ../error.php");
}