<?php
session_start();
require __DIR__ . "/../libs/libAccount.php";
if(!csrfCheck()) exit("No Token");

// JE VAIS TOUT BRULER
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/'); // oui
exit(header("Location: /"));