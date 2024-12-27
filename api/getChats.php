<?php
require __DIR__ . "/../libs/db.php";
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));

$query = $db->prepare("SELECT ID, name, sysmes, intro FROM systemchats WHERE userID = :id");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() == 0) exit(http_response_code(500));
$rs = json_encode($query->fetchAll(PDO::FETCH_ASSOC));
echo($rs);