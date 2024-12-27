<?php
session_start();
if(!empty($_SESSION["id"])) exit(header("Location: /dash"));
include "../libs/libAccount.php";
include "../libs/libCaptcha.php";
include __DIR__ . "/../config/other.php";
include __DIR__ . "/../config/mail.php";
function handleLogin() {
  if(empty($_POST["type"])) return 0;
  $verify = Captcha::validateCaptcha();
  if(!$verify) return -2;
  $valtype = intval($_POST["type"]);
  if($valtype == 1) $val = login($_POST["username"], $_POST["password"]);
  else $val = register($_POST["username"], $_POST["password"], $_POST["email"]);
  if($val != 1) {
  	return $valtype == 1 ? -1 : $val;
  }
  return $valtype == 1 ? 2 : 1;
}
$ok = handleLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php if($turnstileEnabled) echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>'; ?>
    <style>
        html, body {
            background-color: black;
            color: white;
            width: 100%;
            height: 100%;
            overflow: hidden;
			font-family: Arial, Helvetica, sans-serif;
        }
        #container {
            width: 100%;
            height: 100%;
            display: flex;
            text-align: center;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        #options {
            display: flex;
            flex-direction: row;
            font-size: 24px;
            margin-bottom: 20px;
        }
        #register, #hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="options">
            <a href="javascript:void(0)" onclick="load(true)">Login</a>&nbsp;|&nbsp;<a href="javascript:void(0)" onclick="load(false)">Register</a>
        </div>
        <?php
            if($ok > 0) {
                if($ok == 2) {
                    echo '<meta http-equiv="refresh" content="2;URL=/dash">';
                    echo "<span style='color:green'>Connected!</span>";
                }
                elseif($mailEnabled) echo "<span style='color:green'>Registered! You will receive an Email!</span>";
                else echo "<span style='color:green'>Registered! You can log in</span>";
            } elseif($ok < 0) {
              	switch($ok) {
                  case -1:
                    $msg = "Invalid Info!";
                    break;
                  case -2:
                    $msg = "Bad Captcha!";
                    break;
                  case -3:
                    $msg = "Empty/Invalid Username and/or Password";
                    break;
                  case -4:
                    $msg = "Password does not meet password requirements";
                    break;
                  case -5:
                    $msg = "Invalid Email";
                    break;
                  case -6:
                    $msg = "Account already exists!";
                    break;
                  default:
                    $msg = "Error :(";
                    break;
                }
            	echo "<span style='color:red'>$msg</span>";
            } elseif(!empty($_GET["email"])) echo "<span style='color:green'>Verified! Please log in!</span>";
        ?>
        <br>
        <div id="login">
            <form method="post">
                <input type="text" name="username" placeholder="Username"><br><br>
                <input type="password" name="password" placeholder="Password"><br><br>
                <input type="text" name="type" value="1" id="hidden">
                <?php if($enableCaptcha) Captcha::displayCaptcha(); ?>
              	<br>
              	<label>
                   <input type="checkbox" name="remember">
                   Remember Me
                </label>
              	<br><br>
                <input type="submit">
            </form>
        </div>
        <div id="register">
            <form method="post">
                <input type="text" name="username" placeholder="Username"><br><br>
                <input type="text" name="email" placeholder="Email"><br><br>
                <input type="password" name="password" placeholder="Password"><br><br>
                <input type="text" name="type" value="2" id="hidden">
              	<center>
                	<?php if($enableCaptcha) Captcha::displayCaptcha(); ?>
              	</center>
				<p>Password must have min. 12 chars, upper and lowercase letters as well as numbers.</p>
                <input type="submit">
            </form>
        </div>
        <br>
        <div id="options">
            <a href="/home">Home</a>
        </div>
    </div>
    <script>
        function load(hacc) {
            if(hacc) {
                login.style.display = "block";
                register.style.display = "none";
            } else {
                login.style.display = "none";
                register.style.display = "block";
            }
        }
    </script>
</body>
</html>