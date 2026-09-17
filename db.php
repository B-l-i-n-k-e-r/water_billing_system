<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'waterbilling');

if (!$conn) {
    die('Could not connect: ' . mysqli_connect_error());
}
?>