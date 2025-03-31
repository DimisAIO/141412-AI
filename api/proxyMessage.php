<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require __DIR__ . "/../libs/db.php";

$key = str_replace("Bearer ", "", $_SERVER['HTTP_AUTHORIZATION']);

$query = $db->prepare("SELECT accountID FROM apikeys WHERE keyName=:key");
$query->execute([':key' => $key]);

if($query->rowCount() == 0) exit(http_response_code(403));
$accID = $query->fetchColumn();

$query = $db->prepare("SELECT banned FROM users WHERE id=:id");
$query->execute([':id' => $accID]);

$ban = $query->fetchColumn();

if($ban) exit(http_response_code(403));

require __DIR__ . "/../config/cloudflare.php";

$req = json_decode(file_get_contents("php://input"), true);

if($req["stream"]) {
	http_response_code(500);
 	exit("TEXT STREAMING IS NOT SUPPORTED");
}

// Old => $input = ["messages" => $req["messages"]];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/accounts/$accountID/ai/run/$textAI");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req)); // Post Fields
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
    "Content-Type: application/json",
    'Authorization: Bearer ' . $authToken
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$server_output = curl_exec($ch);

$server_output = json_decode($server_output, true);
$message = $server_output["result"]["response"];
$status = $server_output["success"];

curl_close($ch);

$end = $status ? $message : "Error";
if($end == "Error") exit(http_status_code(500));

$json = <<<EOF
{
    "choices": [
        {
            "message": {
                "role": "assistant",
                "content": ""
            }
        }
    ]
}
EOF;

$json = json_decode($json, true);
$json["choices"][0]["message"]["content"] = $end;
$json = json_encode($json);

echo $json;

?>