<?php
session_start();
require __DIR__ . "/../libs/libAccount.php";
if(!csrfCheck()) exit("No Token");

if(empty($_SESSION["goBack"]) || $_SESSION["goBack"] == $_SESSION["id"]) {
	// JE VAIS TOUT BRULER
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/'); // oui
} else {
	$_SESSION["id"] = $_SESSION["goBack"];
}
exit(header("Location: /"));