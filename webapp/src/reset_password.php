<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("Location: login.php");
    exit;
}

// Call MySQL configuration file
require_once "config.php";

if(isset($_REQUEST['resetPassword']))
{
    // Define variables from POST; define register_error and make NULL
    $reset_pass_error = "";
    $pdo_error = "";
    $reset_success = "";
    $new_password = $_REQUEST['new_password'];
    $confirm_password = $_REQUEST['confirm_password'];
    $uid = $_SESSION["id"];
    
    // Check if username and/or password was empty; check if password is >= 8 characters
    if(empty($new_password))
    {
        $reset_pass_error ='<p style="color: #FF0000;">Please provide a new password!</p>';
    }
    else if(empty($confirm_password))
    {
        $reset_pass_error = '<p style="color: #FF0000;">Please confirm the new password!</p>';
    }
    else if($new_password != $confirm_password)
    {
        $reset_pass_error = '<p style="color: #FF0000;">Passwords do not match!</p>';
    }
    else if(strlen($new_password) < 8)
    {
        $reset_pass_error = '<p style="color: #FF0000;">Password must be at least 8 characters!</p>';
    }
    else
    {
        try
        {
            // Create new MySQL connection via PDO for UPDATE
            $db = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME",$DB_USERNAME,$DB_PASSWORD);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if($new_password == $confirm_password)
            {
                // Hash provided password from POST #bcrypt()
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Prepare UPDATE statement
                $update_stmt = $db->prepare("UPDATE users SET password=:password WHERE id=:id");
                $update_stmt->bindParam(":password",$hashed_password);
                $update_stmt->bindParam(":id",$uid);

                // Execute UPDATE statement
                if($update_stmt->execute())
                {
                    $reset_success = '<p style="color: #00FF00;">Password reset successful! Redirecting back to Scrapesy login page...</p>';
                        
                    //Password update successful - destroy session & force logout; redirect back to login
                    header("refresh:3; url=logout.php");
                }
            }
        }
        catch(PDOException $pdo_error)
        {
            echo $pdo_error->getMessage();
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scrapesy - Reset Password</title>
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <style type="text/css">
        body { font: 14px sans-serif; text-align: center; background: #121212; }
        pre { border: 1; width: 1000px; }
    </style>
    <script src="/js/jquery-3.6.0.js"></script>
</head>
<body>

    <!-- START navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/scrapesy.php"><img src="/images/scrapesy-logo_whitetext.png" height="30" width="145"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="scrapesy.php">Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="upload.php">Upload</a>
                </li>
                <?php if($_SESSION['isAdmin'] == "Yes") { 
                    echo '<li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Administration
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="adminNavbarDropdown">
                                <li><a class="dropdown-item" href="admin/admin.php">Manage Users</a></li>
                                <li><a class="dropdown-item" href="admin/register.php">Create New User</a></li>
                            </ul>
                        </li>'; } ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Hello, <?php echo $_SESSION['username'];  ?>!
                    </a>
               <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item active" href="reset_password.php">Reset Password</a></li>
                    <li><a class="dropdown-item" href="resources/help.php">Scrapesy Help</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
  </div>
</nav>
<!-- END navbar -->

<br />

<div class="wrapper" style="color: #FFFFFF;">
    <h2>Reset Password</h2>
    <form style="max-width:400px; margin: auto;" method="POST"> 
        <div class="form-group">
            <input type="password" name="new_password" placeholder="New Password..." class="form-control">
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password..." class="form-control">
        </div>
        <br />
        <div class="form-group">
            <input type="submit" name="resetPassword" class="btn btn-primary" value="Reset">
            <a class="btn btn-secondary" href="scrapesy.php">Cancel</a>
        </div>
        </form>
        <br />
    <span class="help-block"><?php echo $pdo_error; ?></span>
    <span class="help-block"><?php echo $reset_pass_error; ?></span>
    <span class="help-block"><?php echo $reset_success; ?></span>
</div>
 
  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>