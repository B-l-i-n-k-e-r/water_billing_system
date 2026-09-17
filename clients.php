```php
<?php
session_start();

include 'auth.php';
checkLevel([1, 2, 3]);

// Check whether user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$session = $_SESSION['id'];

// Database connection
include 'db.php';

// Get logged-in user's name
$sessionname = "";

$stmt = mysqli_prepare($conn, "SELECT name FROM user WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $session);
mysqli_stmt_execute($stmt);

$resultUser = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($resultUser)) {
    $sessionname = $row['name'];
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>Water Billing System</title>

    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />

    <link rel="stylesheet"
          type="text/css"
          href="css/bootstrap/dist/css/bootstrap.css" />

    <link rel="stylesheet"
          type="text/css"
          href="css/bootstrap/dist/css/bootstrap.min.css" />

    <link rel="stylesheet"
          type="text/css"
          href="css/bootstrap-theme.css" />

    <link rel="stylesheet"
          type="text/css"
          href="css/bootstrap-theme.min.css" />

    <script src="css/bootstrap/dist/js/jquery.js"></script>

    <script src="css/bootstrap/dist/js/bootstrap.min.js"></script>

    <script src="lib/jquery.js" type="text/javascript"></script>

    <script src="src/facebox.js" type="text/javascript"></script>

    <script src="js/application.js"
            type="text/javascript"
            charset="utf-8"></script>

    <script type="text/javascript">

        jQuery(document).ready(function($) {

            $('a[rel*=facebox]').facebox({
                loadingImage: 'src/loading.gif',
                closeImage: 'src/closelabel.png'
            });

        });

    </script>

    <style type="text/css">

        #wrapper {
            width: 100%;
            margin: 0 auto;
            border: 3px solid rgba(0,0,0,0);
            border-radius: 5px;
            box-shadow: 0 0 18px rgba(0,0,0,0.4);
            margin-top: 2%;
            padding: 10px;
            min-height: 550px;
        }

        #header {
            width: 900px;
            height: 100px;
        }

        table th {
            background: #999;
        }

        #header ul li {
            list-style: none;
            float: left;
            margin-top: 30px;
            margin-left: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <div id="wrapper">

        <h1>
            <center>
                <b>Water Billing System</b>
            </center>
        </h1>

        <div style="color:#F00; font-size:12px; text-align:right;">

            <span>
                <?php echo htmlspecialchars($sessionname); ?>
            </span>

            &nbsp;

            <a href="logout.php">
                <span class="btn btn-danger glyphicon glyphicon-log-out">
                    &nbsp;Logout
                </span>
            </a>

        </div>

        <!-- Navigation -->

        <ul class="nav nav-pills">

            <li>
                <a href="billing.php">
                    <span class="glyphicon glyphicon-home"></span>
                    &nbsp;Home
                </a>
            </li>

            <li>
                <a href="bill.php">
                    <span class="glyphicon glyphicon-usd"></span>
                    &nbsp;Billing
                </a>
            </li>

            <li>
                <a href="user.php">
                    <span class="glyphicon glyphicon-user"></span>
                    &nbsp;Users
                </a>
            </li>

            <li class="active">
                <a href="clients.php">
                    <span class="glyphicon glyphicon-list"></span>
                    &nbsp;Clients
                </a>
            </li>

        </ul>

        <hr color="#999999" />

        <div style="overflow:auto; height:350px;">

            <!-- Add Client Modal -->

            <div class="modal fade" id="myModal" role="dialog">

                <div class="modal-dialog" style="width:400px;">

                    <div class="modal-content">

                        <div class="modal-header">

                            <button type="button"
                                    class="close"
                                    data-dismiss="modal">
                                &times;
                            </button>

                            <h4 class="modal-title">
                                Water Billing System
                            </h4>

                        </div>

                        <div class="modal-body">

                            <?php include "addclient.php"; ?>

                        </div>

                        <div class="modal-footer">

                            <button type="button"
                                    class="btn btn-default"
                                    data-dismiss="modal">
                                Close
                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <!-- End Add Client Modal -->


            <div class="panel panel-info">

                <div class="panel-heading">

                    <div class="panel-title">

                        <h5>System Clients</h5>

                        <button type="button"
                                class="btn btn-primary btn-xs"
                                data-toggle="modal"
                                data-target="#myModal">
                            + Add client
                        </button>

                        &nbsp;

                        <a href="deleteclient.php"
                           onclick="return confirm('Are you sure you want to delete all clients?');">

                            <button type="button"
                                    class="btn btn-danger btn-xs">
                                Delete all
                            </button>

                        </a>

                    </div>

                </div>


                <div class="panel-body">

                    <?php

                    // Get all clients
                    $result = mysqli_query($conn, "SELECT * FROM owners ORDER BY id DESC");

                    if (!$result) {

                        echo '<div class="alert alert-danger">
                                Error loading clients: '
                                . htmlspecialchars(mysqli_error($conn)) .
                              '</div>';

                    } else {

                        echo '<table class="table table-bordered table-striped">';

                        echo '<tr>';

                        echo '<th>Id</th>';
                        echo '<th>Firstname</th>';
                        echo '<th>Lastname</th>';
                        echo '<th>Mi</th>';
                        echo '<th>Address</th>';
                        echo '<th>Contact</th>';
                        echo '<th>Action</th>';

                        echo '</tr>';


                        if (mysqli_num_rows($result) == 0) {

                            echo '<tr>';

                            echo '<td colspan="7" style="text-align:center;">
                                    No clients found.
                                  </td>';

                            echo '</tr>';

                        } else {

                            while ($row = mysqli_fetch_assoc($result)) {

                                echo '<tr>';

                                echo '<td>'
                                    . htmlspecialchars($row['id'])
                                    . '</td>';

                                echo '<td>'
                                    . htmlspecialchars($row['fname'])
                                    . '</td>';

                                echo '<td>'
                                    . htmlspecialchars($row['lname'])
                                    . '</td>';

                                echo '<td>'
                                    . htmlspecialchars($row['mi'])
                                    . '</td>';

                                echo '<td>'
                                    . htmlspecialchars($row['address'])
                                    . '</td>';

                                echo '<td>'
                                    . htmlspecialchars($row['contact'])
                                    . '</td>';

                                echo '<td>';

                                echo '<a rel="facebox"
                                         href="edit.php?id='
                                         . urlencode($row['id']) .
                                         '">

                                        <button type="button"
                                                class="btn btn-default btn-xs">

                                            <span class="glyphicon glyphicon-edit"></span>

                                        </button>

                                      </a>';

                                echo ' &nbsp; ';

                                echo '<a rel="facebox"
                                         href="del.php?id='
                                         . urlencode($row['id']) .
                                         '">

                                        <button type="button"
                                                class="btn btn-danger btn-xs">

                                            <span class="glyphicon glyphicon-trash"></span>

                                        </button>

                                      </a>';

                                echo '</td>';

                                echo '</tr>';
                            }
                        }

                        echo '</table>';
                    }

                    ?>

                </div>

            </div>

        </div>

    </div>

</div>


<script src="js/jquery.js"></script>

<script type="text/javascript">

$(function() {

    $(".delbutton").click(function() {

        var element = $(this);

        var del_id = element.attr("id");

        var info = 'id=' + del_id;

        if (confirm("Sure you want to delete this client? There is NO undo!")) {

            $.ajax({

                type: "GET",

                url: "delete.php",

                data: info,

                success: function() {

                }

            });

            $(this).parents(".record")
                .animate({ backgroundColor: "#fbc7c7" }, "fast")
                .animate({ opacity: "hide" }, "slow");
        }

        return false;

    });

});

</script>

</body>

</html>