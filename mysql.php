<?php
$db = new mysqli('localhost:3307', 'maks', '1234', 'database_test');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}
?>
