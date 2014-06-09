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

function cleanData() {
    $pr = './tmp/';
    $date = $pr.date('YmdHis', time()-getSetup('expire'));
    $files = glob($pr.'*');
    if (is_array($files)) {
        foreach ($files as $f) {
            if ($f < $date) {
                unlink($f);
            }
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Chat - Customer Module</title>
        <link type="text/css" rel="stylesheet" href="style.css" />
    </head>

    <?php
    if (!isset($_SESSION['name'])) {
        loginForm();
        cleanData();
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
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script type="text/javascript">
            // jQuery Document
            $(document).ready(function() {
                var time = 0;
                //If user submits the form
                $("#submitmsg").click(function() {
                    var clientmsg = $("#usermsg").val();
                    $.post("post.php", {text: clientmsg});
                    $("#usermsg").attr("value", "");
                    return false;
                });

                //Load the file containing the chat log
                function loadLog() {
                    var oldscrollHeight = $("#chatbox").attr("scrollHeight") - 20;
                    $.ajax({
                        type: 'POST',
                        url: 'server.php',
                        data: {time: time},
                        dataType: 'json',
                        cache: false,
                        success: function(data) {
                            time = data.time;
                            var html = '';
                            for (var k in data.data) {
                                html = html
                                        +"<div class='msgln'>("+data.data[k].date+") <b>"
                                        +data.data[k].name+"</b>: "+data.data[k].text+"<br></div>";
                            }
                            $("#chatbox").append(html); //Insert chat messages into the #chatbox div
                            var newscrollHeight = $("#chatbox").attr("scrollHeight") - 20;
                            if (newscrollHeight > oldscrollHeight) {
                                $("#chatbox").animate({scrollTop: newscrollHeight}, 'normal'); //Autoscroll to bottom of div
                            }
                        },
                    });
                }
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
