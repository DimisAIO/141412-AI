<?php
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));
if(!csrfCheck()) exit("No Token");

require __DIR__ . "/../libs/db.php";

// chatName and systemText are all mendatory. Intro text can be blank!
// No chatID? We'll create it!

$chatName = !empty($_POST["chatName"]) ? $_POST["chatName"] : exit("-1");
$systemText = !empty($_POST["systemText"]) ? $_POST["systemText"] : exit("-1");
$introText = $_POST["introText"];

if(!empty($_POST["chatID"])) {
    // Update Chat
    $chatID = intval($_POST["chatID"]);

    // Check whether user owns the chat!
    $query = $db->prepare("SELECT userID FROM systemchats WHERE ID = :id");
    $query->execute([':id' => $chatID]);
    if($query->rowCount() == 0) exit(http_response_code(500));
    if($query->fetchColumn() != $_SESSION["id"]) exit("-2");

    $query = $db->prepare("UPDATE systemchats SET name=:name, sysmes=:sysmes, intro=:intro WHERE ID=:id");
    $query->execute([':name' => $chatName, ':sysmes' => $systemText, ':intro' => $introText, ':id' => $chatID]);
} else {
    // New Chat (0 => Empty)
    $query = $db->prepare("INSERT INTO systemchats (name, sysmes, intro, userID) VALUES (:name, :sysmes, :intro, :userID)");
    $query->execute([':name' => $chatName, ':sysmes' => $systemText, ':intro' => $introText, ':userID' => $_SESSION["id"]]);
    $chatID = $db->lastInsertId();
}

if (is_uploaded_file($_FILES['pfp']['tmp_name'])) {
    // Notice how to grab MIME type.
    $mime_type = mime_content_type($_FILES['pfp']['tmp_name']);

    // If you want to allow only image files
    if (strpos($mime_type, 'image/') !== 0) {
        exit("-2");
    }

    // Set up destination of the file
    $destination = __DIR__ . "/../data/$chatID";

    // Now you move/upload your file
    if (move_uploaded_file ($_FILES['pfp']['tmp_name'] , $destination)) {
        // File moved to the destination
    }
}