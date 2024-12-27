<?php
function lock() {
    include __DIR__ . "/../config/other.php";
    if(!$useLockFile) return true;
    $id = intval($_SESSION["id"]);
    if(file_exists(__DIR__ . "/../locks/$id")) return true;
    else return file_put_contents(__DIR__ . "/../locks/$id", "");
}

function unlock() {
    include __DIR__ . "/../config/other.php";
    if(!$useLockFile) return true;
    $id = intval($_SESSION["id"]);
    if(!file_exists(__DIR__ . "/../locks/$id")) return true;
    else return unlink(__DIR__ . "/../locks/$id");
}

function isLocked() {
    include __DIR__ . "/../config/other.php";
    if(!$useLockFile) return false;
    $id = intval($_SESSION["id"]);
    return file_exists(__DIR__ . "/../locks/$id");
}