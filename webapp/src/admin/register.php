<?php

// Initialize session
session_start();

// Check if user is admin
if($_SESSION["isAdmin"] == "Yes")
{
    // Call MySQL configuration file
    require_once "../config.php";

if(isset($_REQUEST['register']))
{
    // Define variables from POST; define error and make NULL
    $error = "";
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $role = $_REQUEST['roles'];

    // Check if username and/or password was empty; check if password is >= 8 characters
    if(empty($username))
    {
        $error ='<p style="color: #FF0000;">Please provide an email address!</p>';
    }
    else if(empty($password))
    {
        $error = '<p style="color: #FF0000;">Please supply a password!</p>';
    }
    else if(strlen($password) < 8)
    {
        $error = '<p style="color: #FF0000;">Password must be at least 8 characters!</p>';
    }
    else if(empty($role))
    {
        $error = '<p style="color: #FF0000;">You must select a User Role!</p>';
    }
    else
    {
        try
        {
            // Create new MySQL connection via PDO
            $db = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME",$DB_USERNAME,$DB_PASSWORD);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare SELECT statement to get username to check if it's in use later
            $select_stmt=$db->prepare("SELECT username FROM users WHERE username=:username");

            // Bind username for select statement to $username from POST
            $select_stmt->bindParam(":username",$username);
            $select_stmt->execute();
            $row=$select_stmt->fetch(PDO::FETCH_ASSOC);

            // Check if username provided already exists
            if($row["username"] == $username)
            {
                $error = '<p style="color: #FF0000;">Sorry, that username is already taken!</p>';
            }
            else if(empty($error))
            {
                // Check if the role provided via POST is "admin" or "user" and prepare $user accordingly for INSERT
                if($role == "admin")
                {
                    $role = "Yes";
                }
                else if($role == "user")
                {
                    $role = "No";
                }

                // Hash provided password from POST #bcrypt()
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare INSERT statement; set 'is_disabled' to 'No' by default
                $insert_stmt = $db->prepare("INSERT INTO users (username,password,is_admin,is_disabled) VALUES(:username,:password,:role,'No')");
                $insert_stmt->bindParam(":username",$username);
                $insert_stmt->bindParam(":password",$hashed_password);
                $insert_stmt->bindParam(":role",$role);

                // Execute INSERT statement; if successful, redirect to admin.php (User Management)
                if($insert_stmt->execute())
                {
                    $register_success = '<p style="color: #00FF00;">Registration successful! Redirecting to User Management...</p><br /><p style="color: #FFFFFF;">If this page does not refresh, use the navigation bar.</p>';
                    header("refresh:3; url=admin.php");
                }
            }
        }
        catch(PDOException $pdo_error){
            echo $pdo_error->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Scrapesy - Register New User</title>
        <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <style type="text/css">
            body { font: 14px sans-serif; text-align: center; background: #121212; }
            pre { border: 1; width: 1000px; }
        </style>
    </head>
    <body>

        <!-- START navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="/scrapesy.php"><img src="../images/scrapesy-logo_whitetext.png" height="30" width="145"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="../scrapesy.php">Search</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../upload.php">Upload</a>
                        </li>
                        <?php if($_SESSION["isAdmin"] == "Yes") { 
                            echo '<li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle active" href="#" id="adminNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Administration
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="adminNavbarDropdown">
                                        <li><a class="dropdown-item" href="admin.php">Manage Users</a></li>
                                        <li><a class="dropdown-item active" href="register.php">Create New User</a></li>
                                    </ul>
                                </li>'; } ?>
                            <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hello, <?php echo $_SESSION["username"];  ?>!
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                <li><a class="dropdown-item" href="/reset_password.php">Reset Password</a></li>
                                <li><a class="dropdown-item" href="/resources/help.php">Scrapesy Help</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- END navbar -->

        <br />
        <div class="wrapper">
            <h2 style="color: #FFFFFF;">Register New User Account</h2>
            <form style="max-width:400px; margin: auto;" method="POST">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Email Address">
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password...">
                </div>
                <br />
                <div class="form-group">
                    <select class="form-control" name="roles">
                        <option value="" select="selected">Select Role...</option>
                        <option value="user">Standard User</option>
                        <option value="admin">Administrative User</option>
                    </select>
                </div>
                <br />
                <div class="form-group">
                    <input type="submit" name="register" class="btn btn-primary" value="Register">
                </div>
                <br />
                <span class="help-block"><?php echo $error; ?></span>
                <span class="help-block"><?php echo $pdo_error; ?></span>
                <span class="help-block"><?php echo $register_success ?></span>
            </form>
        </div>
            
        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    </body>
    </html>
<?php
}
else
{
    // If user is not an admin, send them to error.php
    header("Location: ../error.php");
}

?> 