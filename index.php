<?php
$db = new mysqli('localhost:3307', 'maks', '1234', 'database_test');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

require_once('mysql.php');

$link = !empty($_POST['link']) ? trim($_POST['link']) : null;

if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
    $link = $db->real_escape_string($link);

    // Use a prepared statement to avoid SQL injection
    $stmt = $db->prepare("SELECT * FROM `short_urls` WHERE `long_url` = ?");
    $stmt->bind_param('s', $link);
    $stmt->execute();
    $result_sql = $stmt->get_result();

    if ($result_sql) {
        if ($result_sql->num_rows > 0) {
            $select = $result_sql->fetch_assoc();
            $result = [
                'url'  => $select['long_url'],
                'key'  => $select['short_code'],
                'link' => 'http://localhost:63342/test/redirect.php?code=' . $select['short_code']
            ];
            print_r($result);
        } else {
            $shortCode = generateShortCode();

            // Check if the generated short code already exists
            while (shortCodeExists($db, $shortCode)) {
                $shortCode = generateShortCode();
            }

            $stmt = $db->prepare("INSERT INTO `short_urls` (`id`, `long_url`, `short_code`) VALUES (NULL, ?, ?)");
            $stmt->bind_param('ss', $link, $shortCode);
            $stmt->execute();

            $result = [
                'url'  => $link,
                'key'  => $shortCode,
                'link' => 'http://localhost:63342/test/redirect.php?code=' . $shortCode
            ];
            print_r($result);
        }
    } else {
        echo 'Error executing query: ' . $db->error;
    }
}

function generateShortCode() {
    $letters = 'qwertyuiopasdfghjklzxcvbnm1234567890';
    $count = strlen($letters);
    $intval = time();
    $result = '';
    for ($i = 0; $i < 4; $i++) {
        $last = $intval % $count;
        $intval = ($intval - $last) / $count;
        $result .= $letters[$last];
    }
    return $result;
}

function shortCodeExists($db, $shortCode) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM `short_urls` WHERE `short_code` = ?");
    $stmt->bind_param('s', $shortCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['COUNT(*)'];
    return $count > 0;
}
?>

<form method="post" action="/test/index.php">
    <input type="text" name="link">
    <input type="submit" name="submit">
</form>
