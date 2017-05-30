<?php

function loginForm() {
    echo'
	<div id="loginform">
	<form action="." method="post">
		<p>Please enter your name to continue:</p>
		<label for="name">Name:</label>
		<input type="text" name="name" id="name" autofocus />
		<input type="submit" name="enter" id="enter" value="Enter" />
	</form>
	</div>
	';
}

function getSetup($key = null) {
    $arr = parse_ini_file('setup.ini');
    return isset($key) ? $arr[$key] : $arr;
}

function deleteOldHistory() {
    $expireHistory = getSetup('expire_history');
    $expireDate = date('Y-m-d', strtotime("-$expireHistory day"));
    foreach (glob('./history/*') as $f) {
        if (basename($f) < $expireDate) {
            unlink($f);
        }
    }
}



//-------------------------

session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ./"); //Redirect the user
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
    } else {
        echo '<span class="error">Please type in a name</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Chat - Customer Module</title>
        <meta charset="UTF-8" />
        <link type="text/css" rel="stylesheet" href="style.css" />
    </head>

    <?php
    if (!isset($_SESSION['name'])) {
        loginForm();
        deleteOldHistory();
    } else {
        ?>
        <div id="wrapper">
            <div id="menu">
                <p class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></p>
                <p class="logout"><a id="exit" href="#">Exit Chat</a></p>
                <p class="logout" style="margin-right: 1em;"><a target="_blank" href="history.php">History</a></p>
                <div style="clear:both"></div>
            </div>	
            <div id="chatbox"><?php
        ?></div>

            <form name="message" action=".">
                <input name="usermsg" type="text" id="usermsg" size="63" autocomplete="off" autofocus />
                <input name="submitmsg" type="submit"  id="submitmsg" value="Send" />
            </form>
        </div>
        <script type="text/javascript" src="jquery.min.js"></script>
        <script type="text/javascript">
            // jQuery Document
            $(document).ready(function() {
                var id = 'undefined';
                var oldId = null;
                var isRunLoadLog = false;
                //If user submits the form
                $("#submitmsg").click(function() {
                    var clientmsg = $("#usermsg").val();
                    $("#usermsg").val('');
                    $("#usermsg").focus();
                    $.ajax({
                        type: 'POST',
                        url: 'post.php',
                        data: {text: clientmsg},
                        //cache: false,
                        async: true,
                        success: function(data) {
                            if (!isRunLoadLog) {
                                loadLog();
                            }
                        },
                        error: function(request, status, error) {
                            $("#usermsg").val(clientmsg);
                        },
                    });
                    return false;
                });

                //Load the file containing the chat log
                function loadLog() {
                    isRunLoadLog = true;
                    var oldscrollHeight = $("#chatbox")[0].scrollHeight;
                    $.ajax({
                        type: 'POST',
                        url: 'server.php',
                        data: {id: id},
                        dataType: 'json',
                        //cache: false,
                        async: true,
                        success: function(data) {
                            id = data.id;
                            if (oldId !== id) {
                                oldId = id;
                                var html = '';
                                var date;
                                for (var k in data.data.reverse()) {
                                        date = new Date(parseInt(data.data[k][0])*1000);
                                        date = date.toLocaleTimeString();
                                        date = date.replace(/([\d]+\D+[\d]{2})\D+[\d]{2}(.*)/, '$1$2');
                                        html = html
                                                +"<div class='msgln'>("+date+") <b>"
                                                +data.data[k][1]+"</b>: "+data.data[k][2]+"<br></div>";
                                }
                                $("#chatbox").append(html); //Insert chat messages into the #chatbox div
                                var newscrollHeight = $("#chatbox")[0].scrollHeight;
                                if (newscrollHeight > oldscrollHeight) {
                                    $("#chatbox").scrollTop($("#chatbox")[0].scrollHeight);
                                }    
                            }
                            isRunLoadLog = false;
                        },
                    });
                }
                loadLog();
                setInterval(loadLog, <?php echo getSetup('interval') ?>);	//Reload file every 2.5 seconds

                //If user wants to end session
                $("#exit").click(function() {
                    var exit = confirm("Are you sure you want to end the session?");
                    if (exit == true) {
                        window.location = 'index.php?logout=true';
                    }
                });
            });
        </script>
        <?php
    }
    ?>
</body>
</html>
