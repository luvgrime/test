<?php
$db = new mysqli('localhost:3307', 'maks', '1234', 'database_test');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

echo 'Redirect script is running!';

$code = $_GET['code'] ?? null;
echo 'Code: ' . $code;

if ($code) {
    $code = $db->real_escape_string($code);
    $result_sql = $db->query("SELECT * FROM `short_urls` WHERE `short_code` = '{$code}'");

    if ($result_sql) {
        if ($result_sql->num_rows > 0) {
            $select = $result_sql->fetch_assoc();
            if (isset($select['long_url'])) {
                $redirect_url = $select['long_url'];
                header('Location: ' . $redirect_url);
                exit;
            } else {
                echo 'No "long_url" found for this code';
            }
        } else {
            echo 'No rows found for this code';
        }
    } else {
        echo 'Error executing query: ' . $db->error;
    }
} else {
    echo 'No code provided';
}
?>

