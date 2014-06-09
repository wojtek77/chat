<?php

session_start();
if (isset($_SESSION['name'])) {
    $text = isset($_POST['text']) ? $_POST['text'] : null;
    
    $time = time();
    $dir = './tmp/';
    $filename = $dir.date('YmdHis',$time);
    
    $fp = @fopen($filename, 'c+');
    if ($fp === false) {
        mkdir($dir);
        $fp = @fopen($filename, 'c+');
    }
    flock($fp, LOCK_EX);
    
    $data = array(
        array(
            'date' => date('g:i A',$time),
            'name' => $_SESSION['name'],
            'text' => stripslashes(htmlspecialchars($text)),
        )
    );
    
    $filesize = filesize($filename);
    if ($filesize > 0) {
        $oldData = unserialize(fread($fp, $filesize));
        fseek($fp, 0);
        $data = array_merge($data, $oldData);
    }
    
    fwrite($fp, serialize($data));
    flock($fp, LOCK_UN);
    fclose($fp);

//    $fp = fopen("log.html", 'a');
//    fwrite($fp, "<div class='msgln'>(" . date("g:i A") . ") <b>" . $_SESSION['name'] . "</b>: " . stripslashes(htmlspecialchars($text)) . "<br></div>");
//    fclose($fp);
}

?>
