<?php 
session_start();
if(empty($_SESSION["id"]) || empty($_SESSION["token"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
require __DIR__ . "/../libs/db.php";
if(isBanned()) exit(printBan());
echo '<input type="hidden" id="token" name="token" value="' . $_SESSION["token"] . '">';
$query = $db->prepare("SELECT isAdmin FROM users WHERE id = :id");
$query->execute([':id' => $_SESSION["id"]]);
$check = $query->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools</title>
  	<style>
      html, body {
        display: flex;
        flex-direction: column;
		align-items: center;
      	height: 100%;
        width: 100%;
		color: white;
        background-color: #18181B;
        font-family: Arial, Helvetica, sans-serif;
        text-align: center;
      }
      button {
      	background-color: black;
        color: white;
        border-style: solid;
        border-color: white;
        border-radius: 1px;
        transition: .25s ease-in-out;
        font-size: 16px;
		margin-bottom: 8px;
      }
      
      button:hover {
		background-color: #18183B;
      }
      
      .danger:hover {
      	background-color: red;	
      }
  	</style>
</head>
<body>
  <h2>My Account (ID: <?=$_SESSION["id"]?>)</h2>
  <a href="./manage.php"><button>My Profile</button></a>
  <a href="./api.php"><button>Developer (API Key, BETA)</button></a>
  <?php
  	if($check) echo '<a href="/admin" target="_parent"><button>Admin</button></a>';
  ?>
<!-- Abandoned feature:
  <button>My Devices (WIP)</button>
  <button>Disconnect All Devices (WIP)</button>
  <button id="delaccount" class="danger">Delete Account (WIP)</button>
   -->
  <h2>Conversations</h2>
  <button id="delallconv" class="danger">Delete all conversations.</button>
  <a href="../api/downloadAllChats.php"><button>Request chat history</button></a>
  <script>
    delallconv.addEventListener("click", () => {
    	if(confirm("Are you sure you want to delete all conversations?\nOnce done, there's no going back!")) {
        	// TODO: Fetch request to deleteAllChats.php
          	fetch("/api/deleteAllChats.php", {method: "POST", credentials: 'include', headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `token=${token.value}`}).then((response) => {
              if(!response.ok) alert("Something went wrong!");
              else alert("Done! Refresh to see changes!");
          	});
        }
    });
  </script>
</body>
</html>