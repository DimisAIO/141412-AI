<?php
chdir(__DIR__);
include "../config/sql.php";
try {
    $db = new PDO("mysql:host=$db;port=3306;dbname=$dbname", $dbusername, $dbpassword, array(
    PDO::ATTR_PERSISTENT => true
));
    // set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    exit("Zut");
}
return $db;