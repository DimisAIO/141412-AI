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

require __DIR__ . "/../config/other.php";
if($aiID > 0) {
    $query = $db->prepare("SELECT name FROM systemchats WHERE ID = :id");
    $query->execute([':id' => $aiID]);

    $aiName = htmlspecialchars($query->fetchColumn());
}

require_once __DIR__ . '/../libs/HTMLPurifier/HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

require __DIR__ . "/../libs/Parsedown.php";
$pd = new Parsedown();

$query = $db->prepare("SELECT intro FROM systemchats WHERE ID = :id");
$query->execute([':id' => $aiID]);

$aiIntro = $pd->text($query->fetchColumn());

echo <<<EOF
	<center>
    	<h1 class="rainbow_text_animated">$aiName</h1>
        <a href="javascript:void(0)" onclick='choose($aiID, "$aiIntro", "$aiName")'>New chat with bot</a>
    </center>
EOF;

foreach ($file as $message) {
    // $message['role']
    // $message['content']
    if($message['role'] == 'user') {
        echo '<p id="you"><img src="/image/-1" class="pfp">&nbsp;'.$username.'</p>';
        echo "<div id=me><p>".$purifier->purify(urldecode($message['content']))."</p></div>";
    } else {
        $message['content'] = $purifier->purify($pd->text($message['content']));
        echo '<p id="you"><img src="/image/'.$aiID.'" class="pfp">&nbsp;'.$aiName.'</p>';
        echo "<div id=ai>".$message['content']."</div>";
    }
}

exit;