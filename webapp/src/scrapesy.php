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
      <title>Scrapesy</title>
      <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
      <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
      <style type="text/css">
         body { font: 14px sans-serif; text-align: center; background: #121212; }
         pre { border: 1; width: 1000px; }
      </style>
      <script src="/js/jquery-3.6.0.js"></script>
      <script src="/js/table2csv.js"></script>
   </head>
   <body>

      <!-- START navbar -->
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
         <div class="container-fluid">
            <a class="navbar-brand" href="#"><img src="/images/scrapesy-logo_whitetext.png" height="30" width="145"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
               <ul class="navbar-nav">
                  <li class="nav-item">
                     <a class="nav-link active" aria-current="page" href="scrapesy.php">Search</a>
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
                        <li><a class="dropdown-item" href="reset_password.php">Reset Password</a></li>
                        <li><a class="dropdown-item" href="resources/help.php">Scrapesy Help</a></li>
                        <li>
                           <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                     </ul>
                  </li>
               </ul>
            </div>
         </div>
      </nav>
      <!-- END navbar -->

      <br />
      <br />

      <?php
         // Call Elasticsearch helper
         require_once "es.php";
         
         // Define query variables for GET
         $domainQuery = $_GET['domainQuery'];
         $passQuery = $_GET['passQuery'];
         $listQuery = $_GET['listQuery'];
         
         // Structure for Elasticsearch Domain/Email Address query
         if(isset($_GET['domainQuery']))
         {
             $domain_query = $client->search([
                 'index' => 'scrapesy-index',
                 'size' => 200,
                 'body' => [
                     'query' => [
                         'bool' => [
                             'should' => [
                                 'wildcard' => ['username' => $domainQuery]
                             ]
                         ]
                     ]
                 ]
             ]);
         
             // If query contains 1 or more results, store in $results
             if($domain_query['hits']['total'] >= 1)
             {
                 $results = $domain_query['hits']['hits'];
             }
         }
         
         // Structure for Elasticsearch Password query
         if(isset($_GET['passQuery']))
         {
             $pass_query = $client->search([
                 'index' => 'scrapesy-index',
                 'size' => 200,
                 'body' => [
                     'query' => [
                         'bool' => [
                             'should' => [
                                 'match' => ['password' => $passQuery]
                             ]
                         ]
                     ]
                 ]
             ]);
         
             // If query contains 1 or more results, store in $results
             if($pass_query['hits']['total'] >= 1)
             {
                 $results = $pass_query['hits']['hits'];
             }
         
         }
         
         // Structure for Elasticsearch Combolist query
         if(isset($_GET['listQuery']))
         {
             $list_query = $client->search([
                 'index' => 'scrapesy-index',
                 'size' => 200,
                 'body' => [
                     'query' => [
                         'bool' => [
                             'should' => [
                                 'wildcard' => ['source' => $listQuery]
                                 ]
                             ]
                         ]
                     ]
                 ]);
         
             // If query contains 1 or more results, store in $results
             if($list_query['hits']['total'] >= 1)
             {
                 $results = $list_query['hits']['hits'];
             }
         }
         ?>

      <div class="container col-sm-4 col-sm-offset-4">
         <form action="scrapesy.php" method="GET" autocomplete="off">
            <div class="input-group">
               <input type="text" name="domainQuery" class="form-control" placeholder="Seach by Domain or Email Address..." aria-label="Domain or Email Address" aria-describeby="button-addon1">
               <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
            </div>
         </form>
      </div>

      <br />

      <div class="container col-sm-4 col-sm-offset-4">
         <form action="scrapesy.php" method="GET" autocomplete="off">
            <div class="input-group">
               <input type="text" name="passQuery" class="form-control" placeholder="Seach for Passwords..." aria-label="Password" aria-describeby="button-addon1">
               <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
            </div>
         </form>
      </div>

      <br />

      <div class="container col-sm-4 col-sm-offset-4">
         <form action="scrapesy.php" method="GET" autocomplete="off">
            <div class="input-group">
               <input type="text" name="listQuery" class="form-control" placeholder="Seach by Source..." aria-label="Combolist" aria-describeby="button-addon1">
               <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
            </div>
         </form>
      </div>

      <div class="container" style="padding-top: 30px;">
         <table id="results" class="table table-dark table-striped">
            <thread>
               <tr>
                  <th><center><u>Username</u></center></th>
                  <th><center><u>Password</u></center></th>
                  <th><center><u>Source</u></center></th>
                  <th><center><u>Date</u></center></th>
                  <th><center><u>Active User</u></center></th>
               </tr>
            </thread>
            <tbody>
               <?php
                  if(isset($results))
                  {
                      foreach($results as $r)
                      {
                          ?>
                          <tr>
                          <td><?php echo $r['_source']['username'] ?></td>
                          <td><?php echo $r['_source']['password'] ?></td>
                          <td><?php echo $r['_source']['source'] ?></td>
                          <td><?php echo $r['_source']['import_time'] ?></td>
                          <td><?php echo $r['_source']['is_active'] ?></td>
                            </tr>
                        <?php
                        }
                    }
                  ?>
            </tbody>
         </table>
      </div>

      <!-- START CSV Export JS function -->
      <p>
         <button id="downloadCsv" class="btn btn-outline-light btn-sm">Export Results to CSV</button>
         <script>
            $('#downloadCsv').on('click',function()
            {
                $('#results').table2csv();
            });
         </script>
      </p>
      <!-- END CSV Export JS function -->

      <!-- Bootstrap 5 JS -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
      
   </body>
</html>