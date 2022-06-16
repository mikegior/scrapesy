<?php

// Initialize session
session_start();

// Check if user is admin
if($_SESSION["isAdmin"] == "Yes")
{
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Scrapesy - Manage Users</title>
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
                                        <li><a class="dropdown-item active" href="admin.php">Manage Users</a></li>
                                        <li><a class="dropdown-item" href="register.php">Create New User</a></li>
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

    <?php

    require_once("../config.php");

    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if(mysqli_connect_error())
    {
        echo "Failed to connect to MySQL database: " . mysqli_connect_error();
    }

    $result = mysqli_query($conn,"SELECT * FROM scrapesy.users");

    mysqli_close($conn);

    ?>

        <div class="container" style="padding-top: 30px;">
            <table id="results" class="table table-dark table-striped">
                <thread>
                    <tr>
                        <th><center><u>User ID</u></center></th>
                        <th><center><u>Username</u></center></th>
                        <th><center><u>Created</u></center></th>
                        <th><center><u>Account Disabled?</u></center></th>
                        <th><center><u>Disable/Enable Account</u></center></th>
                        <th><center><u>Reset Password</u></center></th>
                        <th><center><u>Delete Account</u></center></th>
                    </tr>
                </thread>
                <tbody>
                <?php
                if(isset($result))
                {
                    foreach($result as $r)
                    {
                        ?>
                        <tr>
                            <td><?php echo $r['id'] ?></td>
                            <td><?php echo $r['username'] ?></td>
                            <td><?php echo $r['created_at'] ?></td>
                            <td><?php echo $r['is_disabled'] ?></td>
                            <td><a href="disable.php?id=<?php echo $r["id"] ?>" method="POST" class="btn btn-dark btn-sm" role="button">Disable</a> <a href="enable.php?id=<?php echo $r['id'] ?>" method="GET" class="btn btn-success btn-sm" role="button">Enable</a></td>
                            <td><button class="btn btn-warning btn-sm resetUserButton">Reset</button></td>
                            <td><button class="btn btn-danger btn-sm deleteUserButton">Delete</button></td>
                        </tr>
                        <?php
                    }
                }?>
                </tbody>
            </table>
        </div>

        <!-- START Reset Password Modal -->
        <div class="modal fade" id="resetPassword" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="password_reset.php" method="POST">
                        <input type="hidden" name="reset_id" id="reset_id">
                            <div class="mb-3">
                                <input type="password" name="new_password" placeholder="New Password..." class="form-control">
                            </div>
                            <div class="mb-3">
                                <input type="password" name="confirm_password" placeholder="Repeat New Password..." class="form-control">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <input type="submit" name="resetPassword" class="btn btn-danger">
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- END Reset Password Modal -->

        <!-- START Delete User Modal -->
        <div class="modal fade" id="deleteUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="delete.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="delete_id" id="delete_id">
                            Are you sure you want to delete this user?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="deleteUser" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- END Delete User Modal -->
        
        <!-- START Reset Password Modal JS -->
        <script>
        $(document).ready(function () {
            $('.resetUserButton').on('click', function () {
                $('#resetPassword').modal('show');
                    $tr = $(this).closest("tr");
                    var data = $tr.children("td").map(function() {
                        return $(this).text();
                    }).get();

                    console.log(data);

                    $('#reset_id').val(data[0]);
            });
        });
        </script>
        <!-- END Reser Password Modal JS -->

        <!-- START Delete User Modal JS -->
        <script>
        $(document).ready(function () {
            $('.deleteUserButton').on('click', function () {
                $('#deleteUser').modal('show');
                    $tr = $(this).closest("tr");
                    var data = $tr.children("td").map(function() {
                        return $(this).text();
                    }).get();

                    console.log(data);

                    $('#delete_id').val(data[0]);
            });
        });
        </script>
        <!-- END Delete User Modal JS -->

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