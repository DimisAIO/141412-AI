<?php
require __DIR__ . "/../libs/db.php";
session_start();
if(empty($_POST["chatID"]) || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));

$query = $db->prepare("SELECT userID FROM chats WHERE chatID = :id");
$query->execute([':id' => intval($_POST["chatID"])]);

if($query->rowCount() == 0 || intval($query->fetchColumn()) != intval($_SESSION["id"])) exit("-1");

$file = file_get_contents(__DIR__."/../data/accounts/".$_SESSION["id"]."/".intval($_POST["chatID"]).".json");
$file = json_decode($file, true);

$query = $db->prepare("SELECT username FROM users WHERE id = :id");
$query->execute([':id' => $_SESSION["id"]]);
$username = htmlspecialchars($query->fetchColumn());

$query = $db->prepare("SELECT saID FROM chats WHERE chatID = :id");
$query->execute([':id' => intval($_POST["chatID"])]);

$aiID = $query->fetchColumn();

if($aiID > 0) {
    $query = $db->prepare("SELECT name FROM systemchats WHERE ID = :id");
    $query->execute([':id' => $aiID]);

    $aiName = $query->fetchColumn();
} else $aiName = "141412 AI";

require __DIR__ . "/../libs/Parsedown.php";
$pd = new Parsedown();

foreach ($file as $message) {
    // $message['role']
    // $message['content']
    if($message['role'] == 'user') {
        echo '<p id="you"><img src="/image/-1" width="4%">&nbsp;'.$username.'</p>';
        echo "<div id=me>".urldecode($message['content'])."</div>";
    } else {
        $message['content'] = $pd->text($message['content']);
        echo '<p id="you"><img src="/image/'.$aiID.'" width="4%">&nbsp;'.$aiName.'</p>';
        echo "<div id=ai>".$message['content']."</div>";
    }
}

exit;