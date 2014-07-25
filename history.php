<!DOCTYPE html>
<html lang="pl">

    <head>
        <title></title>
        <link rel="stylesheet" href="history.css" />
        <meta charset="UTF-8" />
    </head>

    <body>

<?php

function v($v, $czyscHtmlIExit = false) {
	if ($czyscHtmlIExit) ob_end_clean();
    echo '<pre>' . print_r($v, true) . '</pre>';
	if ($czyscHtmlIExit) exit;
}
function vv($v, $czyscHtmlIExit = false) {
	if ($czyscHtmlIExit) ob_end_clean();
    echo '<pre>';
	var_dump($v);
	echo '</pre>';
	if ($czyscHtmlIExit) exit;
}
function vvv($var, & $result = null, $is_view = true)
{
    if (is_array($var) || is_object($var)) foreach ($var as $key=> $value) vvv($value, $result[$key], false);
    else $result = $var;

    if ($is_view) v($result);
}



foreach (glob('./history/*') as $f) {
    
    echo '<h1>'.basename($f).'</h1>';
    
    $data = file($f, FILE_IGNORE_NEW_LINES);
    echo '<table>';
    foreach ($data as $e) {
        $d = explode('&', $e);
        echo '<tr>'
                .'<td>'.date('H:i:s', (int)$d[0]).'</td>'
                .'<td>'.$d[1].'</td>'
                .'<td>'.$d[2].'</td>'
            .'</tr>';
    }
    echo '</table>';
}

?>
    
    </body>
</html>