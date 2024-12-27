<?php
require __DIR__ . "/../config/cloudflare.php";
session_start();
if(empty($_SESSION["id"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(http_response_code(403));

if(!empty($_POST["prompt"])) {
    $input = ["prompt" => $_POST["prompt"]];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/accounts/$accountID/ai/run/$imageAI");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input)); // Post Fields
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        "Content-Type: application/json",
        'Authorization: Bearer ' . $authToken
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $server_output = curl_exec($ch);
    echo base64_encode($server_output);
}
?>