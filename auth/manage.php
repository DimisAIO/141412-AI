<?php 
session_start();
if(empty($_SESSION["id"]) || empty($_SESSION["token"])) exit(header("Location: /"));

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(printBan());
echo '<input type="hidden" id="token" name="token" value="' . $_SESSION["token"] . '">';

$emailStat = 0;
$passStat = 0;

if(!empty($_POST["username"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
  if(!csrfCheck()) exit("No Token");
  chgEmail($_POST["username"], $_POST["email"]);
  $emailStat = 1;
}

if(!empty($_POST["oldpassword"]) && !empty($_POST["newpassword"]) && !empty($_POST["confirmpassword"])) {
  if(!csrfCheck()) exit("No Token");
	$passStat = chgPassword($_POST["oldpassword"], $_POST["newpassword"], $_POST["confirmpassword"]);
} 

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
  	</center>
  	<center>
  	<?php
  		if($emailStat == 1) echo "Email/Username Changed!";
      	else switch($passStat) {
          case -1:
            echo "Wrong old password";
            break;
          case -2:
           	echo "New passwords don't match";
            break;
          case -3:
            echo "Password doesn't abide by password rules!";
            break;
          case 1:
            echo "Password changed successfully!";
            break;
          default:
            break; // fuck off
        }
 	?>
  	</center>
    <center>
        <form method="POST">
            <input type="hidden" name="token" value="<?=$_SESSION["token"]?>">
            <input type="text" name="username" value="<?=$_SESSION['username']?>">
            <input type="text" name="email" value="<?=$_SESSION['email']?>">
            <input type="submit">
        </form>
        <form method="POST">
            <input type="hidden" name="token" value="<?=$_SESSION["token"]?>">
            <input autocomplete="username" hidden> <!-- CHROME YOU FRICKEN BASTARD -->
            <input type="password" placeholder="Old Password" name="oldpassword" id="oldpassword" autocomplete="current-password">
            <input type="password" placeholder="New Password" name="newpassword" id="newpassword" autocomplete="new-password">
            <input type="password" placeholder="Confirm Password" name="confirmpassword" id="confirmpassword" autocomplete="new-password">
            <p>Password must have min. 12 chars, upper and lowercase letters as well as numbers.<br>You can't use your current password either.</p>
            <input type="submit" id="go" disabled>
        </form>
    </center>
    <script>
        var chk = () => {
            if(!oldpassword.value || !newpassword.value || !confirmpassword.value) return go.disabled = true;
            if(oldpassword.value == newpassword.value) return go.disabled = true;
            if(newpassword.value == confirmpassword.value) go.removeAttribute("disabled");
            else return go.disabled = true;
        }
        newpassword.addEventListener("input", chk);
        confirmpassword.addEventListener("input", chk);
        oldpassword.addEventListener("input", chk);
    </script>
    <script src="./back.js?-1"></script>
</body>
</html>