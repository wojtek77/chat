<?php

function loginForm() {
    echo'
	<div id="loginform">
	<form action="" method="post">
		<p>Please enter your name to continue:</p>
		<label for="name">Name:</label>
		<input type="text" name="name" id="name" />
		<input type="submit" name="enter" id="enter" value="Enter" />
	</form>
	</div>
	';
}

function getSetup($key = null) {
    $arr = parse_ini_file('setup.ini');
    return isset($key) ? $arr[$key] : $arr;
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Chat - Customer Module</title>
        <link type="text/css" rel="stylesheet" href="style.css" />
    </head>

    <?php
    if (!isset($_SESSION['name'])) {
        loginForm();
    } else {
        ?>
        <div id="wrapper">
            <div id="menu">
                <p class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></p>
                <p class="logout"><a id="exit" href="#">Exit Chat</a></p>
                <div style="clear:both"></div>
            </div>	
            <div id="chatbox"><?php
        ?></div>

            <form name="message" action="">
                <input name="usermsg" type="text" id="usermsg" size="63" autocomplete="off" />
                <input name="submitmsg" type="submit"  id="submitmsg" value="Send" />
            </form>
        </div>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script type="text/javascript">
            // jQuery Document
            $(document).ready(function() {
                var id = 'undefined';
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
                        async: false,
                        success: function(data) {
                            loadLog();
                        },
                        error: function(request, status, error) {
                            $("#usermsg").val(clientmsg);
                        },
                    });
                    return false;
                });

                //Load the file containing the chat log
                function loadLog() {
                    var oldscrollHeight = $("#chatbox")[0].scrollHeight;
                    $.ajax({
                        type: 'POST',
                        url: 'server.php',
                        data: {id: id},
                        dataType: 'json',
                        //cache: false,
                        async: false,
                        success: function(data) {
                            id = data.id;
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
