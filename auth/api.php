<?php 
// ini_set("display_errors", 1);
session_start();
if(empty($_SESSION["id"]) || empty($_SESSION["token"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
require __DIR__ . "/../libs/db.php";
if(isBanned()) exit(printBan());

// $query = $db->prepare("SELECT apikeys.keyName, users.banned FROM apikeys JOIN users ON users.id = apikeys.accountID WHERE users.id = :id;");
if(!empty($_POST["dkey"])) {
	$query = $db->prepare("DELETE FROM apikeys WHERE accountID=:id AND id=:key");
	$query->execute([':id' => $_SESSION["id"], ':key' => intval($_POST["key"])]);
} elseif(!empty($_POST["nkey"])) {
	$query = $db->prepare("INSERT INTO apikeys (keyName, accountID) VALUES (:key, :id);");
	$query->execute([':id' => $_SESSION["id"], ':key' => bin2hex(random_bytes(18))]);
}


$query = $db->prepare("SELECT id, keyName FROM apikeys WHERE accountID=:id");
$query->execute([':id' => $_SESSION["id"]]);

$keys = $query->fetchAll();
$token = $_SESSION["token"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Manager</title>
  	<link rel="stylesheet" href="./back.css">
    <style>
        body {
            color: white;
            font-family: Arial, Helvetica, sans-serif;
            zoom: 150%;
        }
        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        input {
            margin-top: 10px;
        }
      	@media screen
        and (max-device-width: 480px)
        and (orientation: portrait){
          body {
              zoom: 95%;
          }
        }
      h1, a {
      	margin-bottom: 10px;
      }
      a {
      	transition: .25s ease-in-out;
      }
      a:hover {
      	color: lime;
      }
</style>
</head>
<body>
  	<button id="back"><=</button>
    <center>
      <h1>Hi, <?=$_SESSION["username"]?>!</h1>
      <a href="tools.php">Other Tools</a>
      <p>truly a janitor ai moment. also who the heck would use that broken crap instead of directly using cloudflare ai</p>
      <form method="POST">
        <input type="submit" name="nkey" value="New API Key">
      </form>
  	</center>
    <center>
        <?php
  			foreach($keys as $key) {
				$keyID = $key["id"];
				$keyName = $key["keyName"];
                echo <<<EOF
                        <form method="POST">
                            <div><input type="hidden" name="token" value="$token"><span>$keyName</span><input type="hidden" name="key" value="$keyID"> <input type="submit" name="dkey" value="Delete Key"><div>
                        </form>
                EOF;
            }
  		?>
    </center>
    <script src="./back.js?-1"></script>
</body>
</html>