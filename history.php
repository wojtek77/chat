<!DOCTYPE html>
<html lang="pl">

    <head>
        <title></title>
        <link rel="stylesheet" href="history.css" />
        <meta charset="UTF-8" />
    </head>

    <body>

<?php

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