<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h3>Environment</h3>";
echo "PHP: " . PHP_VERSION . "<br>";
echo "OS: " . PHP_OS . "<br>";
echo "error_log: " . ini_get('error_log') . "<br>";
echo "<hr>";

require_once 'mailer.php';
echo "mailer.php loaded OK<br><hr>";

$result = sendEmail(
    'vinniemarib2004@gmail.com',
    'Test Email from NCWSC',
    '<p>Hello — this is a test. If you received this, mailer.php works.</p>',
    'Test User'
);

echo "<h3>sendEmail() result:</h3>";
echo "<pre>";
var_dump($result);
echo "</pre>";