<?php
if(empty($_GET["mail"])) exit("No Email");
require __DIR__ . "/../libs/db.php";
require __DIR__ . "/../config/mail.php";

if(!$mailEnabled) exit("No email verification required!");

// Query the database for the user
$query = $db->prepare("SELECT id FROM users WHERE activationCode = :id");
$query->execute([':id' => $_GET["mail"]]);

// Check if the user exists
if ($query->rowCount() == 0) exit("Unknown Activation Code"); // User not found

$id = $query->fetchColumn();

$query = $db->prepare("UPDATE users SET isActivated=1, activationCode=NULL WHERE activationCode=:id");
$query->execute([':id' => $_GET["mail"]]);

exit(header("Location: /auth/?email=true"));