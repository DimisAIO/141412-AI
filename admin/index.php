<?php
// ini_set("display_errors", 1);
session_start();
if(empty($_SESSION["id"])) exit(header("Location: /home"));
require __DIR__ . "/../libs/db.php";
$query = $db->prepare("SELECT isAdmin FROM users WHERE id = :id");
$query->execute([':id' => $_SESSION["id"]]);
$check = $query->fetchColumn();
if(!$check) exit(header("Location: /dash"));
if(!empty($_POST["id"])) {
  require __DIR__ . "/../libs/libAccount.php";
  if(!csrfCheck()) exit("No Token");
  switch($_POST["action"]) {
    case "login":
      $_SESSION["goBack"] = $_SESSION["id"];
      $_SESSION["id"] = intval($_POST["id"]);
      exit(header("Location: /dash"));
      break;
    case "unban":
    case "ban":
      $query = $db->prepare("UPDATE users SET banned = NOT banned, banreason = :reason WHERE id=:id");
      $query->execute([":id" => intval($_POST["id"]), ":reason" => $_POST["reason"]]);
      break;
    default:
      echo "No Action given!";
      break;
  }
}
$query = $db->prepare("SELECT id, username, email, banned, isAdmin, isActivated FROM users WHERE 1 ORDER BY id ASC");
$query->execute();
$infos = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
 	<input type="hidden" id="token" value="<?=$_SESSION["token"]?>">
    <style>
        html, body {
            background-color: black;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
        }
        #container {
            margin-top: 20px;
            margin-left: 20px;
        }
      #btn {
      	background-color: black;
        color: white;
        border-style: solid;
        border-color: white;
      }
    </style>
</head>
<body>
    <div id="container">
      <h1>This is a true <i style="color: blue">Admin</i> moment</h1>
        <form method="POST" id="loginForm">
          	
            <input type="hidden" name="id" id="accid" placeholder="Account ID">
        </form>
      	<h2>Accounts (<?=count($infos)?>)</h2>
      	<h4><font color="lime">Activated</font> <font color="red">Banned</font> <font color="lightgrey">Not Activated</font> <font color="gold"><i>Admin</i></font></h4>
      	<?php
  			foreach($infos as $user) {
                echo "<hr>";
              	$color = "lime";
              	if($user["banned"]) $color = "red";
				if(!$user["isActivated"]) $color = "lightgrey";
                if($user["isAdmin"]) {
                	$color = "gold";
					echo "<i><b style='color: $color'>" . $user["username"] . " (". $user["id"] .")</b></i>&nbsp;<button id='btn' onclick='action(".$user["id"].", \"login\")'>Log In</button>&nbsp;<button id='btn' onclick='action(".$user["id"].", \"". ($user["banned"] ? "unban" : "ban") ."\")'>" .  ($user["banned"] ? "Unban" : "Ban")  . " User</button><br>";
                } else echo "<span style='color: $color'>" . $user["username"] . " (". $user["id"] .")</span>&nbsp;<button id='btn' onclick='action(".$user["id"].", \"login\")'>Log In</button>&nbsp;<button id='btn' onclick='action(".$user["id"].", \"". ($user["banned"] ? "unban" : "ban") ."\")'>" .  ($user["banned"] ? "Unban" : "Ban")  . " User</button><br>";
              	echo $user["email"] . "<br>";
  			}
  		?>
    </div>
  	<script>
		function action(id, type) {
          	var form = document.createElement("form");
          	form.style.display = "none";
          	form.method = "POST";
          
			var csrf = document.createElement("input");
          	csrf.name = "token";
        	csrf.value = token.value;
          
          	var option1 = document.createElement("input");
          	option1.name = "id";
        	option1.value = id;
          
            var option2 = document.createElement("input");
          	option2.name = "action";
        	option2.value = type;
          
          	if(type == "ban") {
              var reason = document.createElement("input");
              reason.name = "reason";
              reason.value = prompt("Reason: ");
			  form.appendChild(reason);	
            }
          
          	form.appendChild(option1);	
			form.appendChild(option2);	
			form.appendChild(csrf);	
          
          	document.body.appendChild(form);
          
          	form.submit();
        }
  	</script>
</body>
</html>