<?php
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));
if(!csrfCheck()) exit("No Token");

if(empty(intval($_POST["chatID"]))) exit("-1");
$chatID = intval($_POST["chatID"]);

require __DIR__ . "/../libs/db.php"; // NGAAAAAAAAAH FUCK YOUUUUUUUUUUu

// Check whether user owns the chat!
$query = $db->prepare("SELECT userID FROM systemchats WHERE ID = :id");
$query->execute([':id' => $chatID]);
if($query->rowCount() == 0) exit(http_response_code(500));
if($query->fetchColumn() != $_SESSION["id"]) exit("-2");

$query = $db->prepare("DELETE FROM systemchats WHERE ID = :ID");
$query->execute([':ID' => $chatID]);

$destination = __DIR__ . "/../data/$chatID";

unlink($destination);