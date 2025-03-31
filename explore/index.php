<?php
// ini_set("display_errors", 1);
session_start();
if(empty($_SESSION["id"])) exit(header("Location: /"));
if(empty($_SESSION["token"])) {
  // New update! CSRF Tokens :)
  session_destroy();
  exit(header("Location: /auth"));
}
require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(printBan());

require __DIR__ . "/../libs/db.php";
require __DIR__ . "/../config/other.php";
$query = $db->prepare("
    SELECT s.name, s.ID, u.username 
    FROM systemchats s
    JOIN users u ON s.userID = u.id
    WHERE s.isPub = 1 AND s.name LIKE :name ORDER BY s.ID DESC LIMIT 10
");
$query->execute([':name' => '%'.$_GET["query"].'%']);
$chats = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$aiName?> - Explore</title>
    <style>
        html, body {
            background-color: #18181B;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            padding: 5px 5px 5px 5px;
        }
        #container {
            height: 100%;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }
        #box {
            background-color: black;
            border-style: solid;
            border-color: white;
            width: 250px;
            height: max-content;
            display: flex;
            justify-content: space-around;
            flex-direction: column;
            text-align: center;
          	white-space: collapse;
          	overflow: hidden;
          	padding-bottom: 5px;
        }
      	input[type="text"] {
      		width: 70vw;
          	height: 5vh
      	}
		input[type="submit"], button {
      		width: 10vw;
          	height: 5vh
      	}
      	input, button {
      		background-color: black;
          	color: white;
          	border-style: solid;
          	border-color: white;
          	margin-bottom: 20px;
      	}
      	#search {
      		display: flex;
          	justify-content: center;
          	align-items: center;
      	}
      
      	@media screen
        and (max-device-width: 480px)
        and (orientation: portrait){
          input[type="text"] {
          	width: 50vw;
          }
		  input[type="submit"] {
          	width: 20vw;
          }
      	}
    </style>
</head>
<body>
  	<div id="search">
    	<a href="/dash"></a>&nbsp;
      	<form>
        	<input type="text" name="query">
        	<input type="submit">
      	</form>
    </div>
    <div id="container">
        <?php
      		foreach($chats as $chat) {
              	$chatName = $chat["name"];
              	$creator = $chat["username"];
              	$id = $chat["ID"];
              	echo <<<EOF
                      	<div id="box">
                          <img src="/image/$id">
                          <h1>$chatName</h1>
                          <h3>$creator</h3>
                          <h2><a href="/dash/?start=$id" target="_parent">Chat</a></h2>
                        </div>  
                EOF;
            }   	
      	?>      
    </div>
</body>
</html>