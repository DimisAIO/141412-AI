<?php
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));
if(!csrfCheck()) exit("No Token");

// this is stupid
// if(empty($_POST["confirm"]) || $_POST["confirm"] != $_SESSION["username"]) exit("-1");

session_start();
require __DIR__ . "/../libs/db.php";
$query = $db->prepare("SELECT chatID FROM chats WHERE userID = :id");
$query->execute([':id' => intval($_SESSION["id"])]);

foreach($query->fetchAll() as $chat) {
	  $chatID = $chat["chatID"];
  	$session = $_SESSION["id"];
    $destination = __DIR__ . "/../data/accounts/$session/$chatID.json";

    unlink($destination);
}

$query = $db->prepare("DELETE FROM chats WHERE userID = :ID");
$query->execute([':ID' => $_SESSION["id"]]);