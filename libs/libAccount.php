<?php
// session_start();

/*
function getAccount() {
    $query = $db->prepare("SELECT id FROM users WHERE sid LIKE :id");
    $query->execute([':id' => $_COOKIE['PHPSESSID']]);
    if ($query->rowCount() == 0) {
        $query = $db->prepare("INSERT INTO users (sid) VALUES (:id)");

        $query->execute([':id' => $_COOKIE['PHPSESSID']]);
        $userID = $db->lastInsertId();
    } else {
        $userID = $query->fetchColumn();
    }
    return $userID;
}
*/

function csrfCheck($one = null, $two = null) {
  $one = $one ?? $_SESSION["token"];
  $two = $two ?? $_POST["token"];
  return $one == $two;
}

function chgEmail($username, $email) {
	require "db.php";
    $query = $db->prepare("UPDATE users SET username=:username, email=:email WHERE ID=:id");
    $query->execute([':username' => htmlspecialchars(strtolower($username)), ':email' => $email, ':id' => $_SESSION["id"]]);
    $_SESSION["username"] = htmlspecialchars(strtolower($username));
    $_SESSION["email"] = $email;
}

function chgPassword($old, $new, $confirm) {
	require "db.php";
    $query = $db->prepare("SELECT password FROM users WHERE id = :id");
    $query->execute([':id' => $_SESSION["id"]]);
    $old2 = $query->fetchColumn();
    if(!password_verify($old, $old2)) return -1; // Old Pass Chk Failed
  	if($new != $confirm) return -2; // New and Confirm Pass Don't Match
    $password = $new; // fuck my life
    if($password != $old && $password != $_SESSION["username"] && intval($password) != $password && strtolower($password) != $password && strlen($password) >= 12) {
      $query = $db->prepare("UPDATE users SET password=:password WHERE ID=:id");
      $query->execute([':password' => password_hash($password, PASSWORD_DEFAULT), ':id' => $_SESSION["id"]]);
      return 1;
    } else return -3; // Failed to abide by password rules
}


function checkUser($type, $string) {
    require "db.php";
    $query = $db->prepare("SELECT id FROM users WHERE $type LIKE :id");
    $query->execute([':id' => $string]);

    return $query->rowCount() != 0;
}

function isBanned() {
  	$userID = $_SESSION["id"];
  	$username = $_SESSION["username"];
    require "db.php";
    if(empty($userID) || empty($username)) exit(header("Location: /"));
  	if(!checkUser("username", $username)) exit(header("Location: /"));
    $query = $db->prepare("SELECT banned FROM users WHERE id = :id");
    $query->execute([':id' => intval($userID)]);
    if($query->rowCount() == 0) exit(header("Location: /"));
    return $query->fetchColumn() == 1;
}

function printBan() {
	include "banMessage.php";
}

function newCSRFToken() {
  $secretKey = bin2hex(random_bytes(16)) . time(); // 16 bytes for randomness + timestamp
  $randomString = bin2hex(random_bytes(32)); // Increase size for more entropy
  $token = hash_hmac('sha256', $randomString, $secretKey);
  return $token;
}

function login($useremail, $password) {
    require "db.php";

    // Check and sanitize user email
    if (!empty($useremail)) {
        $username = strtolower(htmlspecialchars($useremail));
    } else {
        return -1; // Invalid email
    }

    // Check and sanitize password
    if (!empty($password)) {
        $password = htmlspecialchars($password);
    } else {
        return -2; // Invalid password
    }

    // Determine if input is an email or username
    $string = filter_var($useremail, FILTER_VALIDATE_EMAIL) ? "email" : "username";

    // Query the database for the user
    $query = $db->prepare("SELECT id, password, username, email FROM users WHERE $string LIKE :id AND isActivated=1");
    $query->execute([':id' => $username]);

    // Check if the user exists
    if ($query->rowCount() == 0) return -3; // User not found

    $response = $query->fetch(PDO::FETCH_ASSOC);

    // Verify the password
    if (!password_verify(htmlspecialchars($password), $response["password"])) return -4; // Invalid password

    if($_POST["remember"] == "on") {
      // plan b: tout bruler
      session_unset();
      session_destroy();
      setcookie(session_name(), '', time() - 3600, '/'); // Delete the old session cookie
      ini_set('session.gc_maxlifetime', 315360000);
      ini_set('session.cookie_lifetime', 315360000);
      session_set_cookie_params([
        'lifetime' => 31536000, // 1 year
        'path' => '/',
        'domain' => '', // Adjust if using subdomains
      ]);
      session_start();
      session_regenerate_id(true);
    }
  
    // Set session and return success
    $_SESSION["id"] = $response["id"];
    $_SESSION["username"] = $response["username"];
    $_SESSION["email"] = $response["email"];
    $_SESSION["token"] = newCSRFToken();
    $userID = $response["id"];

    /*
      There was supposed to be a connected devices feature. It didn't make out, though, you could PR it if you want.
      Here's a start:
        require __DIR__ . "/libs/Browser.php";
        $browser = new Browser();
        $bname = $browser->getBrowser();
        $bos = $browser->getPlatform();
        $ipinfo = json_decode(file_get_contents("https://freeipapi.com/api/json/" . $_SERVER["HTTP_CF_CONNECTING_IP"]), true);
        $loccity = $ipinfo["cityName"];
        $loccountry = $ipinfo["countryName"];

        $ua = "$bname ($bos) from $loccity, $loccountry";
        echo $ua;
    */

    if(!is_dir(__DIR__ . "/../data/accounts/$userID")) mkdir(__DIR__ . "/../data/accounts/$userID", 0777, true);


    return 1;
}

function register($username, $password, $email) {
    require "db.php";
    $ip = $_SERVER["REMOTE_ADDR"]; // needs fix

    // Check and sanitize username
    if(!empty($username)) {
        $username = strtolower(htmlspecialchars($username));
    } else {
        return -3; // Invalid username
    }

    // Check and hash password
    if(!empty($password)) {
        // Some dumb password checks, why do I do that to myself!
        if($password == $username || intval($password) == $password || strtolower($password) == $password || strlen($password) < 12) return -4;

        $password = password_hash(htmlspecialchars($password), PASSWORD_DEFAULT);
    } else {
        return -3; // Invalid password
    }



    // Validate and sanitize email
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = htmlspecialchars($email);
    } else {
        return -5; // Invalid email
    }

    // Check if username or email already exists
    if(checkUser("username", $username)) return -6; // Username already exists
    if(checkUser("email", $email)) return -6; // Email already exists
  
    require __DIR__."/../config/mail.php";

  	$actkey = $mailEnabled ? 0 : 1; 
  
    // Insert new user into the database
    $query = $db->prepare("INSERT INTO users (username, password, email, IP, isActivated) VALUES (:username, :password, :email, :ip, :act)");
    $query->execute([':username' => $username, ':password' => $password, ':email' => $email, ':ip' => $ip, ':act' => $actkey]);

    $userID = $db->lastInsertId();

    mkdir(__DIR__ . "/../data/accounts/$userID", 0777, true);
  
  	if($mailEnabled) sendMail($email, $username);

    return 1; // Registration successful
}

function sendMail($mail = '', $user = '') {
  // Taken from GMDPrivateServer => https://github.com/MegaSa1nt/GMDprivateServer/blob/master/incl/lib/mainLib.php#L3001-L3050
  if(empty($mail) OR empty($user)) return;
  require __DIR__."/../config/mail.php";
  if($mailEnabled) {
    require __DIR__."/db.php";
    require __DIR__."/PHPMailer/PHPMailer.php";
    require __DIR__."/PHPMailer/SMTP.php";
    require __DIR__."/PHPMailer/Exception.php";
    $m = new PHPMailer\PHPMailer\PHPMailer();
    $m->CharSet = 'utf-8';
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = $mailbox;
    $m->Username = $mailuser;
    $m->Password = $mailpass;
    $m->Port = $mailport;
    if($mailtype) $m->SMTPSecure = $mailtype;
    else {
      $m->SMTPSecure = 'tls';
      $m->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );
    }
    $m->setFrom($yourmail, "141412 AI");
    $m->addAddress($mail, $user);
    $m->isHTML(true);
    $string = bin2hex(random_bytes(5));
    $query = $db->prepare("UPDATE users SET activationCode = :mail WHERE userName = :user");
    $query->execute([':mail' => $string, ':user' => $user]);
    $m->Subject = 'Finalize activating your account!';
    $m->Body = '<h1 align=center>Hello, <b>'.$user.'</b>!</h1><br>
				<h2 align=center>Welcome on 141412 AI!</h2><br>
				<h2 align=center>Here is your activation link:</h2><br>
				<h1 align=center>'.dirname('https://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']).'/auth/activate.php?mail='.$string.'</h1>';
    return $m->send();
  }
  
}