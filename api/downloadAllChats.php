<?php
session_start();
if(empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));

require __DIR__ . "/../libs/db.php";
$query = $db->prepare("SELECT ID, name FROM systemchats WHERE userID = :id");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() == 0) exit("-1");

$chatbots = $query->fetchAll(PDO::FETCH_ASSOC);

$cache[0] = "141412 AI";

foreach($chatbots as $chatbot) {
	$cache[$chatbot["ID"]] = $chatbot["name"];
}

$query = $db->prepare("SELECT * FROM chats WHERE userID = :id ORDER BY timestamp DESC");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() == 0) exit("-1");

$chats = $query->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . $_SESSION["username"] . "'s chats.txt");
   
foreach($chats as $chat) {
	echo "=======================================================================\n";
  	echo htmlspecialchars_decode($chat["title"]) . " at " . $chat["timestamp"] . "\n\n";
  	$chatbotname = $cache[$chat['saID']];
  	$messages = json_decode(file_get_contents(__DIR__ . "/../data/accounts/" . $_SESSION["id"] . "/" . $chat["chatID"] . ".json"), true);
  	foreach($messages as $message) {
    	if($message["role"] == "user") echo "Me: ";
      	else echo $chatbotname . ": ";
      	echo $message["content"] . "\n";
      	echo "--------------------------------------------------------------\n";
    }
  	echo "\n";
}