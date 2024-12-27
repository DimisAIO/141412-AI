<?php
    include __DIR__ . "/db.php";
    include __DIR__ . "/../config/other.php";
    $query = $db->prepare("SELECT banreason FROM users WHERE id = :id");
    $query->execute([':id' => $_SESSION["id"]]);
    $banReason = $query->fetchColumn();
    $banReason = !empty($banReason) ? htmlspecialchars($banReason) : "No Reason Given";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You have been banned</title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: black;
            font-family: Arial, Helvetica, sans-serif;
        }
        #container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        #contents {
            padding: 20px;
            border-style: solid;
            border-radius: 20px;
            text-align: center;
            background-color: red;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="contents">
            <h1>You have been banned!</h1>
            <p>Reason: <?=$banReason?></p>
            <p>ID: <?=$_SESSION["id"]?></p>
            <p>Contact <a href="mailto:<?=$adminmail?>"><?=$adminmail?></a> for assistance</p>
        </div>
    </div>
</body>
</html>