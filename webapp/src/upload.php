<?php
   // Initialize session
   session_start();
   
   // Check and set login session; else send back to login.php
   if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
   {
       header("Location: login.php");
       exit;
   }
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <title>Scrapesy - Upload</title>
      <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
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
         <a class="navbar-brand" href="/scrapesy.php"><img src="images/scrapesy-logo_whitetext.png" height="30" width="145"></a>
         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
         <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
               <li class="nav-item">
                  <a class="nav-link" aria-current="page" href="scrapesy.php">Search</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link active" href="upload.php">Upload</a>
               </li>
               <?php if($_SESSION["isAdmin"] == "Yes") { 
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
                     <li><a class="dropdown-item" href="reset_password.php">Reset Password</a></li>
                     <li><a class="dropdown-item" href="resources/help.php">Scrapesy Help</a></li>
                     <li>
                        <hr class="dropdown-divider">
                     </li>
                     <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                  </ul>
               </li>
               </li>
            </ul>
         </div>
      </nav>
      <!-- END navbar -->

      <br />

      <center>
         <div class="container">
            <div class="mb-3">
               <form style="max-width:400px; margin: auto;" action="uploader.php" method="POST" enctype="multipart/form-data">
                  <input type="file" class="form-control" name="fileToUpload">
                  <br />
                  <input type="submit" class="btn btn-outline-light btn-sm" name="upload" value="Upload">
               </form>
            </div>
         </div>
      </center>

      <br />

      <p style="color: #FFFFFF;"><strong>NOTE:</strong> the credentials in your upload must be in a text file and be in a format of <code>email@example.com:password</code></p>
      <p style="color: #FFFFFF;">See the Scrapesy <a href="resources/help.php#uploading" class="link-primary">help page</a> for more information</p>

      <!-- Bootstrap 5 JS -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
      
   </body>
</html>