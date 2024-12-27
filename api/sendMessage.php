<?php
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));
if(!csrfCheck()) exit("No Token");

require __DIR__ . "/../libs/lockFile.php";

if(isLocked()) exit("Error: You're still sending a message!"); // Rate limit :)
lock();

require __DIR__ . "/../config/cloudflare.php";
require __DIR__ . "/../libs/db.php";

if(empty(intval($_POST["chatID"]))) $createNewChat = true;
else {
  $query = $db->prepare("SELECT userID FROM chats WHERE chatID = :id");
  $query->execute([':id' => intval($_POST["chatID"])]);

  if($query->rowCount() != 0 && intval($query->fetchColumn()) == intval($_SESSION["id"])) {
    $createNewChat = false;

    $query = $db->prepare("UPDATE chats SET timestamp = :timestamp WHERE chatID = :id");
    $query->execute([':timestamp' => date('Y-m-d H:i:s'), ':id' => intval($_POST["chatID"])]);

  } else $createNewChat = true;
}

if(!empty($_POST["system"])) {
  $query = $db->prepare("SELECT userID, sysmes, intro FROM systemchats WHERE ID = :id");
  $query->execute([':id' => intval($_POST["system"])]);
  if($query->rowCount() == 0) exit("NO".unlock());
  $rs = $query->fetch(PDO::FETCH_ASSOC);
  if($rs["userID"] != $_SESSION["id"]) exit("Error".unlock());
  $defaultSys = $rs["sysmes"];
}

if(!$createNewChat) {
  $query = $db->prepare("SELECT saID FROM chats WHERE chatID = :id");
  $query->execute([':id' => intval($_POST["chatID"])]);

  $tchat = $query->fetchColumn();

  if(!empty($tchat)) {
    $query = $db->prepare("SELECT sysmes, userID FROM systemchats WHERE ID = :id");
    $query->execute([':id' => $tchat]);
    if($query->rowCount() == 0) exit("NO".unlock());
    $rs = $query->fetch(PDO::FETCH_ASSOC);
    if($rs["userID"] != $_SESSION["id"]) exit("Error".unlock());
    $defaultSys = $rs["sysmes"];
  }
}

if(empty($_POST["message"])) exit("-1".unlock());

$inputs = [];

$inputs[] = ["role" => "system", "content" => $defaultSys];

if($createNewChat) {
  if(!empty($rs["sysmes"])) {
    $inputs[] = ["role" => "assistant", "content" => $rs["intro"]];
  }
} else {
  // TO DO: FIX THIS CRAP
  $conHist = json_decode(file_get_contents(__DIR__."/../data/accounts/".$_SESSION["id"]."/".intval($_POST["chatID"]).".json"), true);
  foreach ($conHist as $message) {
    if (isset($message['role']) && isset($message['content'])) {
        $inputs[] = $message; // Add each message individually to avoid nesting
    }
  }
}

$inputs[] = ["role" => "user", "content" => $_POST["message"]];

$input = ["messages" => $inputs];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/accounts/$accountID/ai/run/$textAI");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input)); // Post Fields
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
    "Content-Type: application/json",
    'Authorization: Bearer ' . $authToken
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$server_output = curl_exec($ch);

$server_output = json_decode($server_output, true);
require __DIR__ . "/../libs/Parsedown.php";
$pd = new Parsedown();
$message = $pd->text($server_output["result"]["response"]);
$status = $server_output["success"];

curl_close($ch);

$end = $status ? $message : "Error";

// LOG THE CONVERSATION

if($createNewChat) {
  $query = $db->prepare("INSERT INTO chats (userID, title, saID) VALUES (:id, :title, :saID)");
  $query->execute([':id' => $_SESSION["id"], ':title' => htmlspecialchars(substr(str_replace("<br>", " ", $_POST["message"]), 0, 100)), ':saID' => !empty($_POST["system"]) ? intval($_POST["system"]) : 0]);
  $_POST["chatID"] = $db->lastInsertId();
}

$filepath = __DIR__."/../data/accounts/".$_SESSION["id"]."/".intval($_POST["chatID"]).".json";

$fileinput = file_exists($filepath) ? json_decode(file_get_contents($filepath), true) : [];
if($createNewChat && !empty($rs["intro"])) $fileinput[] = ["role" => "assistant", "content" => $rs["intro"]];
$fileinput[] = ["role" => "user", "content" => htmlspecialchars($_POST["message"])];
$fileinput[] = ["role" => "assistant", "content" => $end];
file_put_contents($filepath, json_encode($fileinput));

unlock();

exit(intval($_POST["chatID"]) . "-$end");

?>