<?php
require "libs/db.php";
session_start();
// stmt made to support PHP 5!
$stmt = function_exists("str_starts_with") ? str_starts_with($_SERVER["REQUEST_URI"], "/image") : (strpos($_SERVER["REQUEST_URI"], "/image") === 0);
if($stmt && !empty($_SESSION["id"])) {
    $pfp = intval(str_replace("/image/", "", $_SERVER["REQUEST_URI"]));
  	header_remove("Expires");
    header_remove("Pragma");
  	header("Cache-Control: public, max-age=315360000, immutable"); // dafuq 2
	if($pfp < 1) {
        header("Content-Type: image/png");
        $file = __DIR__ . "/data/" . $pfp;
        echo file_get_contents($file);
        exit;
    }
    $query = $db->prepare("SELECT userID FROM systemchats WHERE ID = :id");
    $query->execute([':id' => $pfp]);
    if($query->rowCount() == 0) exit(http_response_code(404));
    if($query->fetchColumn() != $_SESSION["id"]) {
      	$query = $db->prepare("SELECT isPub FROM systemchats WHERE ID = :id");
    	$query->execute([':id' => $pfp]);
      	$isPub = $query->fetchColumn();
      	if(!$isPub) exit(http_response_code(403));
    }
    $file = __DIR__ . "/data/$pfp";
    if(!file_exists($file)) $file = __DIR__ . "/data/0";
    $mimeType = mime_content_type($file);
    $supportsWebP = strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;

    if ($mimeType === 'image/webp' && !$supportsWebP) {
        // Convert WebP to PNG
        $image = imagecreatefromwebp($file);
        if ($image === false) {
            exit(http_response_code(500)); // Error creating image from WebP
        }
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
    } else {
        // Serve the original file
        header("Content-Type: $mimeType");
        header("Etag: " . md5_file($file));
        echo file_get_contents($file);
    }

    exit;
}
http_response_code(301);
if(!empty($_SESSION["id"])) {
    // User has been authenticated, continue
    header("Location: /dash");
} else {
    header("Location: /home");
}
exit();