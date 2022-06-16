<?php
   // Initialize session
   session_start();
   
   // Define error messages and set to NULL
   $upload_success = "";
   $upload_error = "";
   
   // Get file name from upload and set upload location
   $fileName = $_FILES['fileToUpload']['name'];
   $location = "uploads/".$fileName;

   // Get file extention for check
   $ext = pathinfo($fileName, PATHINFO_EXTENSION);
   
   // Check file upload
   if($ext == "")
   {
      $upload_error = '<p style="color: #FF0000;">You did not select a file to upload! Redirecting...</p><br /><p>If this page does not redirect, use the nagivation bar.</p>';
      header("refresh:3, url=upload.php");
   }
   elseif($ext != "txt")
   {
       $upload_error = '<p style="color: #FF0000;">Only files with a TXT extension are permitted for upload! Redirecting...</p><br /><p>If this page does not redirect, use the navigation bar,</p>';
       header("refresh:3; url=upload.php");
   }
   else
   {
       if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'],$location))
       {
           $upload_success = '<p style="color: #00FF00">File uploaded successfully! Redirecting to Scrapesy Search...</p><br /><p>If this page does not redirect, use the navigation bar.</p>';
           header("refresh:3; url=scrapesy.php");
       }
       else
       {
           $upload_error = '<p style="color: #FF0000;">File upload failed. Did you upload a TXT file? Redirecting back to Upload...</p><br /><p>If this page does not redirect, use the navigation bar.</p>';
           header("refresh:3; url=upload.php");
       }
   }
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <title>Scrapesy - Upload Result</title>
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

      <div class="wrapper" style="color: #FFFFFF;">
         <h2>Credential File Upload</h2>
         <br />
         <span class="help-block"><?php echo $upload_success; ?></span>
         <span class="help-block"><?php echo $upload_error; ?></span>
      </div>

      <!-- Bootstrap 5 JS -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

   </body>
</html>