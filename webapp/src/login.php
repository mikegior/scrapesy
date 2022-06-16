<?php
// Initialize session
session_start();
 
// Check if the user is already logged in; if so, proceed to Scrapesy (scrapesy.php)
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
{
    header("Location: scrapesy.php");
    exit;
}
 
// Call MySQL configuration file
require_once "config.php";

 
// Process data submitted via form
if(isset($_REQUEST['signin']))
{
    // Define username and password variables and make NULL
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $isDisabled_error = "";
    $username_error = "";
    $password_error = "";
    $pdo_error = "";

    try
    {
        // Create new MySQL connection via PDO
        $db = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME",$DB_USERNAME,$DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if(isset($_POST['signin']))
        {
            if(empty($username))
            {
                $username_error = '<p style="color: #FF0000;">Please provide a username</p>';
            }
            elseif(empty($password))
            {
                $password_error = '<p style="color: #FF0000;">Please provide a password</p>';
            }
            elseif((empty($username_error)) || (empty($password_error)))
            {
                // Prepare SELECT statement to get username to check if it's in use later
                $select_stmt = $db->prepare("SELECT id,username,password,is_disabled,is_admin FROM users WHERE username=:username");
                $select_stmt->bindParam(":username",$username);
                $select_stmt->execute();

                // Check if anything returned
                $row = $select_stmt->fetch(PDO::FETCH_ASSOC);

                if($select_stmt->rowCount() > 0)
                {
                    if($username == $row['username'])
                    {
                        $hashed_password = $row['password'];
                        if(password_verify($password, $hashed_password))
                        {
                            if($row['is_disabled'] == "No")
                            {
                                // Password is valid; start a new session
                                session_start();
                                        
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $row['id'];
                                $_SESSION["username"] = $row['username'];
                                $_SESSION["isAdmin"] = $row['is_admin'];
                                                                                        
                                // Redirect to the login page (login.php)
                                header("Location: login.php");
                            }
                            else
                            {
                                $isDisabled_error = '<p style="color: #FF0000;">Your account is currently disabled. Please contact the Scrapesy administrator.</p>';
                            }
                        }
                        else
                        {
                            $password_error = '<p style="color: #FF0000;">Password provided is incorrect.</p>';
                        }
                    }
                    else
                    {
                        $username_error = '<p style="color: #FF0000;">Username provided does not exist.</p>';
                    }
                }
                else
                {
                    $username_error = '<p style="color: #FF0000;">Invalid username or password!</p>';
                }
            }
        }
    }
    catch(PDOException $pdo_error)
    {
        $pdo_error = $pdo_error->getMessage();
    }
    unset($db);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scrapesy - Login</title>
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <style type="text/css">
        body { font: 14px sans-serif; background: #121212; }
        .wrapper { width: 350px; padding: 20px; }
        .footer { position: fixed; left: 0; bottom: 0; width: 100%; text-align: center; }
    </style>
</head>
<body>

    <section class="vh-100">
        <div class="container h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="bg-dark" style="border-radius: 1rem;">
                        <div class="p-5 text-center">
                            <div class="mb-md-4 mt-md-4">
                                <img src="/images/scrapesy-logo_whitetext.png">
                                <p class="text-white mb-5">#OSINT</p>

                                <form method="POST">
                                    <div class="mb-4">
                                        <input type="text" name="username" class="form-control form-control-lg" placeholder="Email Address">
                                    </div>

                                    <div class="mb-4">
                                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Password">
                                    </div>

                                    <br />

                                    <!-- Display login-related errors -->
                                    <span class="help-block"><?php echo $username_error; ?></span>
                                    <span class="help-block"><?php echo $password_error; ?></span>
                                    <span class="help-block"><?php echo $isDisabled_error; ?></span>
                                    <span class="help-block"><?php echo $pdo_error; ?></span>

                                    <br />

                                    <input class="btn btn-outline-light btn-sm px-5" name="signin" type="submit" value="Login">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="footer">
        <p style="color: #FFFFFF;">© Copyright 2022</p>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>
