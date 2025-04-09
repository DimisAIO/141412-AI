<?php
session_start();
if(empty($_SESSION["id"])) exit(header("Location: /"));
if(empty($_SESSION["token"])) {
  // New update! CSRF Tokens :)
  session_destroy();
  exit(header("Location: /auth"));
}
echo '<input type="hidden" id="token" name="token" value="' . $_SESSION["token"] . '">';
header("Access-Control-Allow-Origin: " . $_SERVER['SERVER_NAME']);

require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(printBan());

require __DIR__ . "/../libs/db.php";
require __DIR__ . "/../config/other.php";

/*
$query = $db->prepare("SELECT banned FROM users WHERE id = :id");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() == 0) exit(header("Location: /"));
if($query->fetchColumn() == 1) {
  include __DIR__ . "/../libs/banMessage.php";
  exit;
}
*/

$query = $db->prepare("SELECT username FROM users WHERE id = :id");
$query->execute([':id' => intval($_SESSION["id"])]);

$username = $query->fetchColumn();

if($username != $_SESSION["username"]) $_SESSION["username"] = $username;


$query = $db->prepare("SELECT ID, name, sysmes, intro FROM systemchats WHERE userID = :id");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() != 0) {
  $rs = $query->fetchAll(PDO::FETCH_ASSOC);
}

$query = $db->prepare("SELECT chatID, userID, title, saID FROM chats WHERE userID = :id ORDER BY timestamp DESC");
$query->execute([':id' => intval($_SESSION["id"])]);
if($query->rowCount() != 0) {
  $chats = $query->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$aiName?> - Dashboard</title>
    <link rel="manifest" href="/manifest.json">
  	<script src="https://polyfill-fastly.io/v3/polyfill.min.js?features=default%2CString.prototype.replaceAll"></script>
  	<style>      
      
      .pfp {
        width: 4%;
      	border-style: solid;
        border-radius: 20px;
      }
      
      #aniPC {
      	display: none;
      }
      
      #aniMobi {
      	display: none;
      }
      
      .rainbow {
          display: flex;
          align-items: center;
          text-align: center;
          text-decoration: none;
          font-family: monospace;
          letter-spacing: 5px;
      }
      .rainbow_text_animated {
          background: linear-gradient(to right, #6666ff, #0099ff , #00ff00, #ff3399, #6666ff);
          -webkit-background-clip: text;
          background-clip: text;
          color: transparent;
          animation: rainbow_animation 6s ease-in-out infinite;
          background-size: 400% 100%;
      }

      @keyframes rainbow_animation {
          0%,100% {
              background-position: 0 0;
          }

          50% {
              background-position: 100% 0;
          }
      }
      
      button, input[type="submit"] {
      	background-color: black;
        color: white;
        border-style: solid;
        border-color: white;
        border-radius: 1px;
        transition: .25s ease-in-out;
      }
      
      button:hover, input[type="submit"]:hover {
		background-color: #18183B;
      }
      
        #ai, #me {
          background-color: black;
          color: white;
          width: 85%;
          border-style: solid;
          border-radius: 10px;
          transition: .25s ease-in-out;
          padding: 5px;
        }
        #ai:hover, #me:hover {
          box-shadow: 0px 0px 25px white;
        }
        html, body {
          height: 100%;
          width: 100%;
          overflow: hidden;
          margin: 0;
          color: white;
          font-family: Arial, Helvetica, sans-serif;
        }
        #chat {
            flex-grow: 1; /* Allow #chat to expand and take available space */
            overflow-y: auto; /* Enable vertical scrolling in the chat div */
            padding: 10px; /* Add padding for chat content */
            background-color: #18181B;
            max-height: calc(100vh - 70px); /* Ensure it respects viewport height */
        }
        .container {
            display: flex;
            height: 100%;
        }
        .left-div {
            padding-top: 1%;
            /* padding-left: 2.5%; */
            line-height: 1.8;
            width: 20%;
            /* background-color: rgb(17, 17, 17); /* Specify your desired background color */
            background-color: #131316;
        }
        .right-div, #accountManager, #exploreTab {
            width: 80%;
            flex: 1;
            background-color: #18181B; /* Specify your desired background color */
        }
        #convo {
            display: flex;
            align-items: center;
            justify-content: space-around;
            position: absolute;
            width: 80%;
            bottom: 10px;
        }
        #messageText {
            width: 85%;
            resize: none;
            font-size: 16px;
          	min-height: 35px;
          	background-color: transparent;
          	color: white;
          	border-color: white;
          	border-width: 2px;
        }
        #holder, #sendMessage, #sendImage {
            height: 40px; /* Set the same height as the textarea */
            margin-left: 5px; /* Optional: Add a little space between elements */
            font-size: 16px; /* Ensure font size consistency */
            margin-right: 5px;
            /* width: 5%; */
            padding: 0 10px; /* Add some padding to buttons */
        }
        #imageDiv {
          display: none;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          height: 100%;
          width: 100%;
          background-color: 18181B;
        }
        #sendMessage {
            display: none;
            order: 2;
        }
        #holder {
            order: 2;  
        }
        #sendImage {
          order: 1;
        }
        pre, code {
          background-color: #000000eb;
          color: gold;
          border-style: solid;
          border-color: #000000eb;
          border-radius: 3px;
          box-shadow: 0px 0px 6vw #000000eb;
        }
      #die {
        padding-left: 12.5%;
      	display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-left: 0;
        margin-right: 15%;
        margin-bottom: 5%;
      }
      #rdivbtn {
      	margin-left: 2.5%;
        margin-top: 1%;
        font-size: 30px;
        display: none;
      }
      #rdivbtnperm {
      	margin-left: 2.5%;
        margin-top: 1%;
		margin-right: 30px;
        position: absolute;
        right: 0;
      }
      #rdivbtnpermbutton {
        font-size: 30px;
      }
      #ldivbtn {
        font-size: 30px;
      }
     @media only screen 
       and (min-width: 1024px) 
       and (max-height: 1366px) 
       and (-webkit-min-device-pixel-ratio: 1.5)
       and (hover: none)
       and (pointer: coarse) {
         #ldivbtn {
           font-size: 20px;
         }
         .left-div {
         	width: 30vw;
         }
      }
      @media only screen 
	   and (orientation: portrait) 
       and (-webkit-min-device-pixel-ratio: 1.5)
       and (hover: none)
       and (pointer: coarse) {
         #ldivbtn {
           font-size: 20px;
         }
         .left-div {
         	width: 30vw;
         }
      }

      #wtf {
     	  display: flex;
        flex-direction: row;
      }
      #iframe, #accountManager, #exploreTab {
        display: none;
      }
      #exitbtn {
        font-size: 24px;
        width: 100%;
        background-color: black;
        display: flex;
        justify-content: center;
      }
      #mychats {
        height: 60%;
        overflow-y: auto;
      }
      
      @media screen
        and (max-device-width: 480px)
        and (orientation: portrait){
          .left-div {
            display: none;
            width: 100%;
          }
          .right-div {
            width: 100%;
          }
          #convo {
            width: 100%;
          }
          #chat {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            max-width: 100vw !important;
            overflow: hidden !important;
            overflow-y: auto !important;
            height: 85%;
          }
          html ::-webkit-scrollbar {
            display: none;
          }
          #ldivbtn {
            margin-left: 2.5%;
          }
          #mychats {
            height: 60%;
          }
          #ldivbtn, #rdivbtn, #rdivbtnperm, #rdivbtnpermbutton {
            font-size: 22px;
          }
          #rdivbtnperm {
            margin-right: 5px;
          }
          iframe {
          	height: 100vh;
            width: 100vw;
          }
          #die {
            display: flex;
            justify-content: flex-start;
			padding-left: 0;
          }
          #mychat > img {
          	width: 20%
          }
          .pfp {
          	width: 8%;
          }
      }
      
      @media screen
        and (max-device-width: 320px)
        and (orientation: portrait){
          #ldivbtn, #rdivbtn, #rdivbtnperm, #rdivbtnpermbutton {
            font-size: 18px;
          }
          #mychats {
            height: 40vh;
          }
      }
      
      @media screen
        and (max-height: 560px)
        and (orientation: portrait){
          #mychats {
          	height: 55%;
          }
      }
      
      button, input[type="submit"] {
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -o-user-select: none;
        user-select: none;
      }
      #intro {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        margin-top: 5%;
      }
      #mychat {
        display: flex;
        justify-content: flex-start;
		margin-left: 10%;
        margin-right: 10%;
        margin-bottom: 10px;
        align-items: center;
        font-size: 125%;
      }
      #savedchat {
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        overflow-x: hidden;
        white-space: nowrap;
        scrollbar-width: thin;
      }
      #insidesc {
        padding-left: 5px;
        display: flex;
        align-items: center;
      }
      #insidesc > img {
        width: 10%;
        height: 10%;
        min-width: 10%;
  		min-height: 10%;
      }
      #you {
        display: flex;
        align-items: center;
        margin: 0;
        margin-top: 5px;
        margin-bottom: 5px;
      }
      a, #henry {
		text-decoration: none;
        transition: .25s ease-in-out;
        color: rgb(202, 202, 202);
      }
      a:hover, #henry:hover {
        color: lime;
      }
      #danger:hover {
      	color: red;
      }
      #henry {
        width: 90%; /* the link should take all the place so that it is easily clickable */
      }
      #danger {
      	width: 10%;
      }
      #mychat > img {
  		cursor: pointer;
      }
      #mychat > a {
      	width: 100%
      }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-div">
          <div id="die"><button onclick="rotation()" id="ldivbtn">≡</button><code id="aniPC" class="rainbow rainbow_text_animated"><?=$aiName?></code><button id="ldivbtn" onclick="clearChat()">+</button><code id="aniMobi" class="rainbow rainbow_text_animated"><?=$aiName?></code></div>
            <div id="chats">
               <?php
                echo "<div id='savedchat'>";
                if(!empty($chats)) {
                  foreach($chats as $chat) {
                    $id = $chat["chatID"];
                    $title = str_replace("&lt;br&gt;", " ", $chat["title"]);
                    $saID = $chat["saID"];
                    if(!empty($saID)) {
                      $query = $db->prepare("SELECT name FROM systemchats WHERE ID = :id");
                      $query->execute([':id' => $saID]);
                      $saName = htmlspecialchars($query->fetchColumn());
                    } else $saName = $aiName;
                    echo "<div id='insidesc'>";
                    echo "<img src='/image/$saID'>&nbsp;";
                    echo "<span>";
                    echo "<a href='javascript:void(0)' onclick='delChat($id)' id='danger'>[X]</a>&nbsp;";
                    echo "<a href='javascript:void(0)' onclick='loadChat($id, $saID, \"$saName\")' id='henry'>$title</a>";
                    echo "</span>";
                    echo "</div>";
                    echo "<hr>";
                  } 
                } else {
                    echo "<center><a>Strike up a convo!</a></center>";
                }
                echo "</div>";
              ?>
            </div>
        </div>
        <div class="right-div">
          <div id="wtf"><button onclick="rotation()" id="rdivbtn">≡</button><button id="rdivbtn" onclick="clearChat()">+ New</button><div id="rdivbtnperm"><form method="POST" action="/api/logoff.php"><input type="hidden" name="token" value="<?=$_SESSION["token"]?>"><button type="button" onclick="charMgmt(2)" id="rdivbtnpermbutton">👤&nbsp;<?=$_SESSION["username"]?></button>&nbsp;<input type="submit" id="rdivbtnpermbutton" value="🔑"></form></div></div>
          <div id="intro">
              <!-- <p>Epic custom system moment uwu</p>
              <input type="number" id="system">
              <p>kms</p> -->
              <?php
                if(!empty($rs)) {
                  require_once __DIR__ . '/../libs/HTMLPurifier/HTMLPurifier.auto.php';
                  require_once __DIR__ . '/../libs/Parsedown.php';
                  
                  $pd = new Parsedown();

                  $config = HTMLPurifier_Config::createDefault();
                  $purifier = new HTMLPurifier($config);

                  echo "<h2>Choose a chat or <a href='javascript:charMgmt(3)' style='text-decoration: underline dashed'>explore</a>!</h2>";
                  echo "<div id='mychats'>";
                  foreach($rs as $chat) {
                    echo "<div id='mychat'>";
                    $id = $chat["ID"];
                    $sysmes = htmlspecialchars($chat["name"]);
                    $intro = $purifier->purify($pd->text($chat["intro"]));
                    echo "<img src='/image/$id' width='10%' onclick='choose($id, \"$intro\", \"$sysmes\")'>&nbsp;<a href='javascript:void(0)' onclick='choose($id, \"$intro\", \"$sysmes\")'>$sysmes</a>";
                    echo "</div>";
                  } 
                  echo "</div>";
                } else {
                    echo "<h2>No chats yet. <a href='javascript:charMgmt(3)' style='text-decoration: underline dashed'>Explore</a>?</h2>";
                }
              ?>
              <h3><a href="javascript:charMgmt(1)">Manage!</a></h3>
          </div>
            <div id="chat">
				
            </div>
            <div id="convo">
                <textarea id="messageText" name="message" form="msgform" placeholder="Message ChatBox..."></textarea>
                <button id="holder">🎙</button>
                <button id="sendImage">📷</button>
                <button id="sendMessage">📤</button>
            </div>
        </div>
        <div class="right-div" id="iframe">
          <a href="javascript:charMgmt(0)" id="exitbtn">Exit</a>
          <iframe src="/dash/chats.php" frameborder="0" width="100%" height="100%"></iframe>
        </div>
        <div class="right-div" id="imageDiv">
          <h1>Generating...</h1>
          <center><a id="imGenLink"><img id="imGen" width="45%"></a></center>
          <button onclick="exitwtf()" style="margin-top: 10px; font-size: 24px;">Exit</button>
        </div>
        <div id="accountManager">
          <a href="javascript:charMgmt(0)" id="exitbtn">Exit</a>
          <iframe src="/auth/tools.php" frameborder="0" width="100%" height="100%"></iframe>
        </div>
        <div class="right-div" id="exploreTab">
          <a href="javascript:charMgmt(0)" id="exitbtn">Exit</a>
          <iframe src="/explore/" frameborder="0" width="100%" height="100%"></iframe>
        </div>
    </div>
    <input type="hidden" id="system">
    <input type="hidden" id="chatID">
    <script>
        let chatbotname = "<?=$aiName?>";
	
      	function showGenerating() {
			const tst = chatbotname ? chatbotname : "<?=$aiName?>";
          
          	var messageArray = [
            	"Generating, please wait...",
                "I'm thinking :)",
              	"Generating some yap",
                "Hm... 🤔",
              	"Remember! What " + tst + " says should not be taken as fact and is made up!"
            ]
          	var randomMessage = messageArray[Math.floor(Math.random() * messageArray.length)]; // this is real
          
            const sys = system.value ? system.value : 0;
            var what = document.createElement("div");
          	what.setAttribute("id", "generating");
          	chat.appendChild(what);
            generating.innerHTML += '<p id="you"><img src="/image/' + sys + '" class="pfp">&nbsp;' + tst + '</p>';
            generating.innerHTML += "<div id=ai>" + randomMessage + "</div>";
        }
      
      	function delChat(chatID) {
        	if(!confirm("Are you sure to delete this chat?\nThis action is IRREVERSIBLE!")) return;
			fetch("/api/deleteConv.php", {method: "POST", credentials: 'include', headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `chatID=${chatID}&token=${token.value}`}).then(response => response).then(response => response.text()).then((body) => {
              if(body == "-1") alert("Failed to delete! :(");
              else location.reload();
          	});
        }
      
        function exitwtf() {
          document.getElementsByClassName("right-div")[0].style.display = "block";
          document.getElementsByClassName("right-div")[1].style.display = "none";
          document.getElementsByClassName("right-div")[2].style.display = "none";
          imageDiv.style.display = "none";
          sendMessage.style.display = "none";
          holder.style.display = "block";
        }

        function clearChat() {
          chat.innerHTML = "";
          chatID.value = 0;
          system.value = 0;
          chatbotname = "<?=$aiName?>";
          intro.style.display = "flex";
          messageText.placeholder = `Message <?=$aiName?>...`;
          if(mobileCheck() && ldshown) rotation();
        }

        function loadChat(chatID, saID, cbn) {
          fetch("/api/getChatHistory.php", {method: "POST", credentials: 'include', headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `chatID=${chatID}`}).then(response => response).then(response => response.text()).then((body) => {
            if(body == "-1") return;
            chat.innerHTML = body.replaceAll("&lt;br&gt;", "<br>");
            intro.style.display = "none";
            document.getElementById("chatID").value = chatID;
            document.getElementById("system").value = saID;
            chatbotname = cbn;
            messageText.placeholder = `Message ${cbn}...`;
            if(mobileCheck()) rotation();
          });
        }
        function charMgmt(yes) {
          if(yes == 1) {
            // Character Manager (1)
            document.getElementsByClassName("right-div")[0].style.display = "none";
            document.getElementsByClassName("right-div")[1].style.display = "block";
			exploreTab.style.display = "none";
            accountManager.style.display = "none";
          } else if (yes == 2) {
            // Account Manager (2)
            document.getElementsByClassName("right-div")[0].style.display = "none";
            document.getElementsByClassName("right-div")[1].style.display = "none";
			exploreTab.style.display = "none";
            accountManager.style.display = "block";
          } else if (yes == 3) {
            // Character Explorer (3)
            document.getElementsByClassName("right-div")[0].style.display = "none";
            document.getElementsByClassName("right-div")[1].style.display = "none";
			exploreTab.style.display = "block";
            accountManager.style.display = "none";
          } else {
            // Chat (0)
            document.getElementsByClassName("right-div")[0].style.display = "block";
            document.getElementsByClassName("right-div")[1].style.display = "none";
			exploreTab.style.display = "none";
            accountManager.style.display = "none";
          }
        }

        function mobileCheck() {
          let check = false;
          (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
          return check;
        };

        messageText.addEventListener('input', () => {
            if(messageText.value.length == 0 && !nomic) {
                holder.style.display = "block";
                sendMessage.style.display = "none";
            } else {
                holder.style.display = "none";
                sendMessage.style.display = "block";
            }
        });

        function choose(chattxt, introtxt, name) {
          // cmon
	  document.getElementById("chatID").value = 0;
          system.value = chattxt; // cyka blyat
          intro.style.display = "none";
          chat.innerHTML = `<center><h1 class="rainbow_text_animated">${name}</h1><a href="javascript:void(0)" onclick='choose(${chattxt}, "${introtxt}", "${escapeHtml(name)}")'>New chat with bot</a></center>`;
          chat.innerHTML += '<p id="you"><img class="pfp" src="/image/' + chattxt + '" width="4%">&nbsp;' + name + '</p>';
          introtxt = introtxt ? introtxt : "(No Introductory Message)";
          chat.innerHTML += `<div id=ai>${introtxt}</div><br>`;
          chatbotname = name;
          messageText.placeholder = `Message ${name}...`;
        }
    </script>
</body>
</html>
<!--
<p id="tesla"></p>
<textarea id="messageText" name="message" form="msgform"></textarea><br><br>
<input type="number" id="system"><br><br>
<button id="sendMessage">Send</button>
<button id="holder">🎙</button>
<input type="file" id="audioFile" name="file" style="display:none;">
-->
<script>    
  let msg;
  let ldshown = true;
  let nomic = false;
  
  if(mobileCheck()) {
    aniMobi.style.display = "flex";
    aniMobi.style.marginLeft = "10px";
    rdivbtn[1].innerText = "+";
    rdivbtnpermbutton[0].innerText = "👤"; // To avoid issues with overflowing!
  	rotation();
  } else aniPC.style.display = "flex";

  function rotation() {
  	if(ldshown) {
    	ldshown = false;
      document.getElementsByClassName("left-div")[0].style.display = "none";
      document.getElementsByClassName("right-div")[0].style.width = "100%";
      if(mobileCheck()) {
        document.getElementsByClassName("right-div")[0].style.display = "block";
        document.getElementsByClassName("left-div")[0].style.display = "none";
      } else {
        intro.style.marginTop = "0";
      }
      convo.style.width = "100%";
      holder.style.marginRight = "0";
      sendMessage.style.marginRight = "0";
      rdivbtn[0].style.display = "block";
     	rdivbtn[1].style.display = "block";
    } else {
    	ldshown = true;
      document.getElementsByClassName("left-div")[0].style.display = "block";
      convo.style.width = "80%";
      if(mobileCheck()) {
        document.getElementsByClassName("right-div")[0].style.display = "none";
        document.getElementsByClassName("left-div")[0].style.display = "block";
      } else {
        intro.style.marginTop = "5%";
        document.getElementsByClassName("right-div")[0].style.width = "80%";
        holder.style.marginRight = sendMessage.style.marginRight = "5px";
      }
      rdivbtn[0].style.display = "none";
     	rdivbtn[1].style.display = "none";
    }
  }
  
  function escapeHtml(text) {
      return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
  }
    
  const xhr = new XMLHttpRequest();

  sendMessage.addEventListener("click", () => {
    if(messageText.value.length < 1) return;
    messageText.disabled = holder.disabled = sendMessage.disabled = sendImage.disabled = true;
    intro.style.display = "none";
    messageText.style.height = "5px";
    xhr.open("POST", "/api/sendMessage.php", true);
    // Send the proper header information along with the request
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = () => {
      // Call a function when the state changes.
      if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
        const message = xhr.response;
        const match = xhr.response.split("-")[0];
        const tst = chatbotname ? chatbotname : "<?=$aiName?>";
        const sys = system.value ? system.value : 0;
        generating.remove();
        if(message.startsWith("Error: ")) return alert(message);
        chat.innerHTML += '<p id="you"><img src="/image/' + sys + '" class="pfp">&nbsp;' + tst + '</p>';
        chat.innerHTML += "<div id=ai>" + message.slice(match.length+1) + "</div>";
        if(!chatID.value || chatID.value == 0) savedchat.innerHTML = `<div id="insidesc"><img src="/image/${sys}" width="10%">&nbsp;<a href="javascript:void(0)" onclick="delChat(${match})" id="danger">[X]</a>&nbsp;<a href="javascript:void(0)" onclick='loadChat(${match}, 0, "${tst}")'>${escapeHtml(decodeURI(msg.replaceAll("<br>", " ").substr(0, 70)))}</a></div><hr>` + savedchat.innerHTML
        chatID.value = match;
        messageText.disabled = holder.disabled = sendMessage.disabled = sendImage.disabled = false;
      }
    };
    msg = messageText.value;
    msg = encodeURI(msg).replaceAll("%0A", "<br>");
    chat.innerHTML += '<p id="you"><img src="/image/-1" class="pfp">&nbsp;<?=$_SESSION["username"]?></p>';
    /* So bad, firefox 52.9.0 ESR needs to work
      chat.innerHTML += "<div id=me></div>";
      if(me.length === undefined) me.innerText = messageText.value;
      else me[me.length-1].innerText = messageText.value;
    */
    const newDiv = document.createElement("div");
    const newPTag = document.createElement("p");
    newDiv.id = "me";
    newPTag.innerText = messageText.value;
    newDiv.append(newPTag);
    chat.append(newDiv);
    messageText.value = "";
    xhr.send("message=" + msg + "&system=" + system.value + "&chatID=" + chatID.value + "&token=" + token.value);
    showGenerating();
	if(nomic) {
      	holder.style.display = "none";
    	sendMessage.style.display = "block";
    } else {
      	holder.style.display = "block";
    	sendMessage.style.display = "none";
    }
  });

  sendImage.addEventListener("click", () => {
    xhr.open("POST", "/api/genImage.php", true);
    // Send the proper header information along with the request
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = () => {
      // Call a function when the state changes.
      if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
        imGen.src = "data:image/jpeg;base64," + xhr.response;
        imGenLink.href = imGen.src;
        imGenLink.download = "generation";
      }
    };
    xhr.send("prompt=" + messageText.value);
    messageText.value = "";
    imGen.src = "/loading-pigeon.gif";
    document.getElementsByClassName("right-div")[0].style.display = "none";
    document.getElementsByClassName("right-div")[1].style.display = "none";
    imageDiv.style.display = "flex";
  });
  
  // moz docs
  
  // Older browsers might not implement mediaDevices at all, so we set an empty object first
if (navigator.mediaDevices === undefined) {
  navigator.mediaDevices = {};
}

// Some browsers partially implement mediaDevices. We can't just assign an object
// with getUserMedia as it would overwrite existing properties.
// Here, we will just add the getUserMedia property if it's missing.
if (navigator.mediaDevices.getUserMedia === undefined) {
  navigator.mediaDevices.getUserMedia = function (constraints) {
    // First get ahold of the legacy getUserMedia, if present
    var getUserMedia =
      navigator.webkitGetUserMedia || navigator.mozGetUserMedia;

    // Some browsers just don't implement it - return a rejected promise with an error
    // to keep a consistent interface
    if (!getUserMedia) {
      return Promise.reject(
        new Error("getUserMedia is not implemented in this browser"),
      );
    }

    // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
    return new Promise(function (resolve, reject) {
      getUserMedia.call(navigator, constraints, resolve, reject);
    });
  };
}

  
  
  
  
      if(!window.MediaRecorder) {
        // polyfill taken from https://github.com/ai/audio-recorder-polyfill
      	(function () {var h={};h=function(){var t=2,n=[];onmessage=function(e){"encode"===e.data[0]?function(e){for(var s=e.length,a=new Uint8Array(s*t),i=0;i<s;i++){var r=i*t,U=e[i];U>1?U=1:U<-1&&(U=-1),U*=32768,a[r]=U,a[r+1]=U>>8}n.push(a)}(e.data[1]):"dump"===e.data[0]&&function(e){var s=n.length?n[0].length:0,a=n.length*s,i=new Uint8Array(44+a),r=new DataView(i.buffer);r.setUint32(0,1380533830,!1),r.setUint32(4,36+a,!0),r.setUint32(8,1463899717,!1),r.setUint32(12,1718449184,!1),r.setUint32(16,16,!0),r.setUint16(20,1,!0),r.setUint16(22,1,!0),r.setUint32(24,e,!0),r.setUint32(28,e*t,!0),r.setUint16(32,t,!0),r.setUint16(34,8*t,!0),r.setUint32(36,1684108385,!1),r.setUint32(40,a,!0);for(var U=0;U<n.length;U++)i.set(n[U],U*s+44);n=[],postMessage(i.buffer,[i.buffer])}(e.data[1])}};var j={};function k(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function f(e,t){for(var r=0;r<t.length;r++){var a=t[r];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function l(e,t,r){return t&&f(e.prototype,t),r&&f(e,r),e}var b,g=window.AudioContext||window.webkitAudioContext,m=function(e){var t=e.toString().replace(/^(\(\)\s*=>|function\s*\(\))\s*{/,"").replace(/}$/,""),r=new Blob([t]);return new Worker(URL.createObjectURL(r))},d=function(e){var t=new Event("error");return t.data=new Error("Wrong state for "+e),t},c=function(){function e(t){var r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;k(this,e),this.stream=t,this.config=r,this.state="inactive",this.em=document.createDocumentFragment(),this.encoder=m(e.encoder);var a=this;this.encoder.addEventListener("message",function(e){var t=new Event("dataavailable");t.data=new Blob([e.data],{type:a.mimeType}),a.em.dispatchEvent(t),"inactive"===a.state&&a.em.dispatchEvent(new Event("stop"))})}return l(e,[{key:"start",value:function(e){var t=this;if("inactive"!==this.state)return this.em.dispatchEvent(d("start"));this.state="recording",b||(b=new g(this.config)),this.clone=this.stream.clone(),this.input=b.createMediaStreamSource(this.clone),this.processor=b.createScriptProcessor(2048,1,1),this.encoder.postMessage(["init",b.sampleRate]),this.processor.onaudioprocess=function(e){"recording"===t.state&&t.encoder.postMessage(["encode",e.inputBuffer.getChannelData(0)])},this.input.connect(this.processor),this.processor.connect(b.destination),this.em.dispatchEvent(new Event("start")),e&&(this.slicing=setInterval(function(){"recording"===t.state&&t.requestData()},e))}},{key:"stop",value:function(){return"inactive"===this.state?this.em.dispatchEvent(d("stop")):(this.requestData(),this.state="inactive",this.clone.getTracks().forEach(function(e){e.stop()}),this.processor.disconnect(),this.input.disconnect(),clearInterval(this.slicing))}},{key:"pause",value:function(){return"recording"!==this.state?this.em.dispatchEvent(d("pause")):(this.state="paused",this.em.dispatchEvent(new Event("pause")))}},{key:"resume",value:function(){return"paused"!==this.state?this.em.dispatchEvent(d("resume")):(this.state="recording",this.em.dispatchEvent(new Event("resume")))}},{key:"requestData",value:function(){return"inactive"===this.state?this.em.dispatchEvent(d("requestData")):this.encoder.postMessage(["dump",b.sampleRate])}},{key:"addEventListener",value:function(){var e;(e=this.em).addEventListener.apply(e,arguments)}},{key:"removeEventListener",value:function(){var e;(e=this.em).removeEventListener.apply(e,arguments)}},{key:"dispatchEvent",value:function(){var e;(e=this.em).dispatchEvent.apply(e,arguments)}}]),e}();c.prototype.mimeType="audio/wav",c.isTypeSupported=function(e){return c.prototype.mimeType===e},c.notSupported=!navigator.mediaDevices||!g,c.encoder=h,j=c;document.addEventListener("DOMContentLoaded",function(){console.log("de polyfill is real, thx audio-recorder-polyfill")}),window.MediaRecorder=j;})();
      }
navigator.mediaDevices.getUserMedia({ audio: true })
  .then(function (stream) {
    var mediaRecorder = new MediaRecorder(stream);
    var chunks = [];

    mediaRecorder.addEventListener('dataavailable', function (event) {
      chunks.push(event.data);
    });

    mediaRecorder.addEventListener('stop', function () {
      
      messageText.disabled = holder.disabled = sendMessage.disabled = sendImage.disabled = true;
      intro.style.display = "none";
      
      var blob = new Blob(chunks, { type: 'audio/ogg; codecs=opus' });

      // Create a File object from the blob
      var file = new File([blob], "recorded_audio.ogg", { type: 'audio/ogg' });
      
      xhr.open("POST", "/api/getTranscription.php", true);
      const formData = new FormData();

      formData.append('file', file, file.name);

      xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
          if(!me) return;
          if(me.length === undefined) me.remove();
          else me[me.length-1].remove();
          messageText.disabled = holder.disabled = sendMessage.disabled = sendImage.disabled = false;
          messageText.value = xhr.response;
          if(messageText.value.startsWith("Error: ")) return alert(xhr.response);
          sendMessage.click();
        }
      };
      chat.innerHTML += "<div id=me>Loading transcription...<div>";
      xhr.send(formData);
      chunks = [];
    });

    holder.addEventListener("mousedown", () => {
      holder.style.backgroundColor = "white";
      holder.style.color = "black";
      mediaRecorder.start();
    });

    holder.addEventListener("mouseup", () => {
      holder.style.backgroundColor = "black";
      holder.style.color = "white";
      mediaRecorder.stop();
    });

    holder.addEventListener("touchstart", (event) => {
      event.preventDefault();
      holder.style.backgroundColor = "white";
      holder.style.color = "black";
      mediaRecorder.start();
    });

    holder.addEventListener("touchend", (event) => {
      event.preventDefault();
      holder.style.backgroundColor = "black";
      holder.style.color = "white";
      mediaRecorder.stop();
    });
  })
  .catch(function (error) {
    // Handle errors such as no microphones found or permissions denied
    nomic = true;
  	holder.style.display = "none";
    sendMessage.style.display = "block";
    console.error("o noes: " + error);
  });
  
  messageText.addEventListener("keydown", (event) => {
    messageText.style.height = "5px";
    if(event.keyCode == 13 && event.shiftKey != true && !mobileCheck()) {
    	sendMessage.click(); // epok key even listener
    } else messageText.style.height = (messageText.scrollHeight) + "px";
  });
  
  <?php
  	if(!empty($_GET["start"])) {
    	$query = $db->prepare("SELECT name, intro FROM systemchats WHERE id = :id AND isPub=1");
        $query->execute([':id' => intval($_GET["start"])]);

        $result = $query->fetch();
      	$id = intval(intval($_GET["start"]));
      	$name = $result["name"];
      	$sysmes = $result["intro"];
      	echo "choose($id, \"$sysmes\", \"$name\");";
      	echo "window.history.replaceState({}, document.title, '/dash');";
    }
  ?>

</script>
