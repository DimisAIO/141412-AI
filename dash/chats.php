<?php
session_start();
if(empty($_SESSION["id"]) || empty($_SESSION["token"])) exit(header("Location: /"));
echo '<input type="hidden" id="token" name="token" value="' . $_SESSION["token"] . '">';
require __DIR__ . "/../libs/libAccount.php";
if(isBanned()) exit(printBan());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats</title>
</head>
<style>
    * {
        background-color: #27272c;
        color: white;
    }
    html, body, #container {
        height: 100%;
        width: 100%;
        overflow: hidden;
        background-color: #18181B;
        color: white;
        font-family: Arial, Helvetica, sans-serif;
    }
    input, textarea, select, img {
        margin-bottom: 20px;
    }
    textarea {
        width: 70%;
        height: 8vh;
    }
    img {
        height: 30%;
    }
  	
    #container {
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }
  	@media screen
    and (max-device-width: 480px)
    and (orientation: portrait){
      img {
      	height: 15%;
      }      
      #container {
        justify-content: flex-start;
        margin-top: 5%;
      }
  	}
  
    #deletebtn {
        color: white;
        background-color: transparent;
        text-decoration: none;
        transition: .25s ease-in-out;
    }
    #deletebtn:hover {
        color: red;
    }
    #forcetogether {
        display: flex;
        flex-direction: row;
        background-color: transparent;
    }
</style>
<body>
    <div id="container">
        <img src="" alt="" id="imgprev">
        <input type="file" id="image" accept="image/*">
    
        <select name="chats" id="chats">
            
        </select>
        <input type="hidden" id="chatID">
        <div id="forcetogether"><input type="text" id="chatName">&nbsp;<a href="javascript:void(0)" id="deletebtn">(delete)</a></div>
        <textarea id="systemText"></textarea>
        <textarea id="introText"></textarea>
        <button id="submit">Submit</button>
    </div>
    <script>
        var newcontent = {ID: 0, name: "New Bot", sysmes: "Describe to me what I am!", intro: "How should I greet myself?"};
        let content;

        deletebtn.addEventListener("click", () => {
            var id = id = chatID.value;
            if(!id) return;
            if(!confirm("Are you sure you want to delete it?")) return;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/api/deleteChat.php", true);

            xhr.onreadystatechange = function () {
            //Appelle une fonction au changement d'état.
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                // Requête finie, traitement ici.
                console.log("Loaded");
                chats.innerHTML = "";
                go();
            }
            };
            const formData = new FormData();
            formData.append('chatID', id);

            xhr.send(formData);
        })

        image.addEventListener("change", () => {
            var reader = new FileReader();
            reader.onload = (e) => {
                imgprev.src = e.target.result;
            }
            reader.readAsDataURL(image.files[0]);
        })

        submit.addEventListener("click", () => {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/api/updateChat.php", true);

            xhr.onreadystatechange = function () {
            //Appelle une fonction au changement d'état.
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                // Requête finie, traitement ici.
                console.log("Loaded");
                chats.innerHTML = "";
                go();
            }
            };
            const formData = new FormData();
            formData.append('chatID', chatID.value);
            formData.append('chatName', chatName.value);
            formData.append('systemText', systemText.value);
            if(introText.value) {
                formData.append('introText', introText.value);
            }

            if(image.files[0]) {
                formData.append('pfp', image.files[0], image.files[0].name); // 'file' is the key, and 'file' is the File object
            }

            formData.append("token", token.value);

            xhr.send(formData);
        });

        var loadContent = () => {
            var carray = content[parseInt(chats.value)];
            imgprev.src = "/image/" + carray.ID;
            chatID.value = carray.ID;
            chatName.value = carray.name;
            systemText.value = carray.sysmes;
            introText.value = carray.intro;
        }

        chats.addEventListener("change", () => {
            loadContent();
        });

        var go = () => {
            (async () => {
                try {
                    const rawResponse = await fetch('/api/getChats.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                    });
                    content = await rawResponse.json();

                    if(!content) {
                        throw new Error("Request error. (No chats very likely!)");
                    }

                    content.push(newcontent);

                } catch (error) {
                    content = [newcontent];
                }

                var x = 0;
                content.forEach((element) => {
                    var option = document.createElement("option");
                    option.value = x; // element.ID? wtf?
                    option.text = element.name;
                    chats.add(option, null);
                    x++;
                });

                loadContent();
            })();
        }

        go();
    </script>
</body>
</html>