<?php
//Initialize session
session_start();

//Check and set login session; else send back to login.php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scrapesy - Help</title>
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/asciinema-player.css" />
    <style type="text/css">
        body { font: 14px sans-serif; background: #121212; }
        pre { border: 1; width: 1000px; }
    </style>
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
                        <a class="nav-link" aria-current="page" href="/scrapesy.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/upload.php">Upload</a>
                    </li>
                    <?php if($_SESSION["isAdmin"] == "Yes") { 
                        echo '<li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Administration
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="adminNavbarDropdown">
                                    <li><a class="dropdown-item" href="/admin/admin.php">Manage Users</a></li>
                                    <li><a class="dropdown-item" href="/admin/register.php">Create New User</a></li>
                                </ul>
                            </li>'; } ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hello, <?php echo $_SESSION['username'];  ?>!
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="/reset_password.php">Reset Password</a></li>
                            <li><a class="dropdown-item active" href="help.php">Scrapesy Help</a></li>
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

<div class="container-md" id="searching" style="color: #FFFFFF;">
            <h3><u>Searching with Scrapesy 101</u></h3>
            <p>Using Scrapesy, you can search for the following types of data:</p>
            <ul>
                <li>Domain Names</li>
                <li>Email Addresses</li>
                <li>Passwords</li>
            </ul>
            <p>While searching for this type of data, you have the ability to perform wildcard-style searches against <b>domain names</b> and <b>email addresses</b>. Passwords, however, must be searched for explicitly.</p>
            <p>Here are a few examples of how you might search for <b>domains</b>:</p>
            <ul>
                <li><code>example.com</code> - searches explicity for any findings matching <code>example.com</code></li>
                <li><code>example*</code> - wildcard search for any variation of a domain that belongs to your organization. This may return <code>example.com example.co.uk example-blog.com</code>, for example.</li>
                <li><code>*.com</code> - wildcard search for any domains belonging to your organization containing the <code>.com</code> TLD</li>
                <li><code>*.co*</code> - wildcard search for any domains belonging to your organization, which may include TLD's such as <code>.com .co .co.uk</code></li>
                <li><code>brett*</code> - wildcard search for any email addresses containing <code>brett</code> as a portion email address. This may return <code>brett@example.com</code>, for example.</li>
                <li><code>*</code> - wildcard search that will return all findings in the Scrapesy Elasticsearch database</li>
            </ul>
            <p>You can also search by combolists, or the filename where credentials were discovered. This is useful when searching by "combolist" after you have uploaded a file manually. Below are some examples:</p>
            <ul>
                <li><code>*</code> - wildcard search that will return all findings in the Scrapesy Elasticsearch database, regardless of the originating file where credentials were discovered.</li>
                <li><code>*file*</code> - wildcard search that will return any findings where the originating file where the credentials were discovered had the word <code>file</code> in the filename.</li>
                <li><code>file.txt</code> - explicit search for file name of a specific credential dump (i.e. found automatically or uploaded via WebUI)</li>
            </ul>
            <p>Remember, findings that are stored in Scrapesy's Elasticsearch instance are defined by your <code>[PARSE_CRITERIA]</code> stanza, as defined in your Scrapesy <code>config.ini</code> file located in the Scrapesy install directory. For more information regarding the <code>config.ini</code> file and the configurations within it, refer to the <a href="https://github.com/mgiord/scrapesy" class="link-primary">Scrapesy GitHub</a> page.</p>
</div>

<br />
<hr style="color: #FFFFFF;" />
<br />

<div class="container-md" id="validations" style="color: #FFFFFF;">
            <h3><u>What is the "Active User" column in my results?</u></h3>
            <p>In your search results, there is a column labled as "Active User" and by default this will show each finding as "Validation Not Configured" if you have not configured any validation methods in Scrapesy's <code>config.ini</code> file.</p>
            <p>Scrapesy has the ability to take findings (email addresses) and validate if it is an active user via:
            <ul>
                <li>GSuite (API)</li>
                <li>Active Directory (LDAP)</li>
            </ul>
            <p>This allows users of Scrapesy, such as Incident Response teams, to quickly determine if a particular credential discovered by Scrapesy is active in your organization. This helps reduce manual effort to determine if the account is still active, as it may be an older finding of an employee that is no longer with the organization, for example.</p>
            <p>If you configured Scrapesy to user either of these methods, the value in the "Active User" column will change to "<b>Active</b>" rather than "<b>Validation Not Configured</b>" Conversely, if an account is found to not be present or active within in your environment, the value will show "<b>Not Active</b>"</p>
            <p>To configure validations, you will need to make changes to Scrapesy's <code>config.ini</code> file as well as performing other steps outside of Scrapesy. Please refer to the <a href="https://github.com/mgiord/scrapesy" class="link-primary">Scrapesy GitHub</a> page for more information and documentation.</p>
</div>

<br />
<hr style="color: #FFFFFF;" />
<br />

<div class="container-md" id="uploading" style="color: #FFFFFF;">
    <h3><u>Can I upload a list of Credentials manually?</u></h3>
    <p>Absolutely! There are two ways you can submit a list of credentials for validation with Scrapesy.</p>
    <ul>
        <li>Scrapesy WebUI</li>
        <li>Command Line</li>
    </ul>
    <p>Within the Scrapesy WebUI, the top-level nav bar contains a link called "Upload" From this page, you can select a text (TXT) file containing credentials you have come across that you want to validate with Scrapesy.</p>
    <ol>
        <li>Click "Upload" from the top-level navbar</li>
        <li>Click anywhere within the "Choose File" selection bar</li>
        <li>Click the "Upload" button below</li>
    </ol>
    <p>Scrapesy's engine will take the uploaded file, read, and parse it for items you have configured in the <code>config.ini</code> file defined within the <code>[PARSE_CRITERIA]</code> stanza.</p>
    <p>Please note, your credentials must be in a text (TXT) file and the credentials within it must be properly separated, such as <code>user@example.com:password</code></p>
    <p>To send a file of credentials to Scrapesy manually, you will first need to upload your text (TXT) file to your Scrapesy server to your home directory, for example. After uploading the file to your Scrapesy server, issue the following command:</p>
    <ul>
        <li><code>sudo python3 /opt/scrapesy/CredsToES.py /path/to/credentials.txt localhost 9200 -i scrapesy-index</code></li>
    </ul>
    <p>You will recieve output from the command, such as in the screenshow below, if there were any relevant findings as defined in the <code>[PARSE_CRITERIA]</code> stanza of the <code>config.ini</code> file.
    <p>The above syntax for <code>CredsToES.py</code> is as follows:</p>
    <ul>
        <li><code>/path/to/credentials.txt</code> - fully qualified path to file containing credentials to check</li>
        <li><code>localhost</code> - instance of Elasticsearch that is installed locally during the installation of Scrapesy</li>
        <li><code>9200</code> - TCP port of Elasticsearch that is set by default during the installation of Scrapesy</li>
        <li><code>-i scrapesy-index</code> - the <code>-i</code> flag indicates the destination Elasticsearch index with <code>scrapesy-index</code> being the default index</li>
    </ul>
    <asciinema-player autoplay loop src="scrapesy-cli.cast"></asciinema-player>
</div>

<br />
<hr style="color: #FFFFFF;" />
<br />

<div class="container-md" id="exporting" style="color: #FFFFFF;">
    <h3><u>Exporting Results to CSV</u></h3>
    <p>While using Scrapesy to search for any findings related to your organization, you have the ability to export the results, as shown in the results table, directly to a CSV file.</p>
    <p>Use the "Export Results to CSV" button located below the search results table - you may need to scroll down to see the button, depending on how many results are returned. This is especially true if you are using the <code>*</code> wildcard search for Domains/Email Addresses.</p>
    <img src="exportButton.png">
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!-- asciinema Player JS -->
<script src="/js/asciinema-player.js"></script>

</body>
</html>
