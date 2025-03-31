<?php
session_start();
if($_SERVER["REQUEST_METHOD"] != "POST" || empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));

require __DIR__ . "/../libs/lockFile.php";
require __DIR__ . "/../config/cloudflare.php";

if(isLocked()) exit("Error: You're still sending a message!"); // Rate limit :)
lock();

if(empty($_FILES["file"]["tmp_name"])) exit("-1");

$musicFile = $_FILES["file"]["tmp_name"];

// my mic was broken so I had to do this => copy($musicFile, __DIR__ . "/../data/test.ogg");

/*
$content_type = mime_content_type($musicFile);
$filesize = filesize($musicFile);
$stream = fopen($musicFile, 'r');
*/

$ch = curl_init();

$headers = [
    // "Content-Type: $content_type",
  	"Content-Type: application/octet-stream",
    'Authorization: Bearer ' . $authToken
];

$curl_opts = array(
    CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/$accountID/ai/run/$voiceAI",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
  	/*
    CURLOPT_INFILE => $stream,
    CURLOPT_INFILESIZE => $filesize,
    */
  	CURLOPT_POSTFIELDS => file_get_contents($musicFile),
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
);

curl_setopt_array($ch, $curl_opts);

$server_output = json_decode(curl_exec($ch));
// fclose($stream);

unlock();

// no copy($musicFile, __DIR__ . "/../test.ogg");

if($server_output->success) echo $server_output->result->text;
else echo "Error: Couldn't get voice transcription.";

exit;