<?php

function strIntersectStart($s1, $s2) {
    $i = min(strlen($s1), strlen($s2));
    while ($i && substr_compare($s1, $s2, 0, $i) != 0) {
        --$i;
    }
    return substr($s1, 0, $i);
}

//---------------

session_start();
if (isset($_SESSION['name'])) {
    $data = array();
    $from = empty($_POST['time']) ? null : date('YmdHis', $_POST['time']+1);
    $t = time()-1;
    $to = date('YmdHis', $t);
    $intersect = strIntersectStart($from, $to);
    $files = glob('./tmp/'.$intersect.'*');
    foreach ($files as $f) {
        $basename = basename($f);
        if ($from <= $basename && $basename <= $to) {
            $data = array_merge($data, unserialize(file_get_contents($f)));
        }
    }
    echo json_encode(array('time' => $t, 'data' => $data));
}

?>
