<?php

//$_POST['id'] = '1305177620-53c14f147c456';
if (!isset($_POST['id'])) return;
$isApc = extension_loaded('apc');
$id = $_POST['id'];
$cache = $isApc ? apc_fetch('chat') : @unserialize(file_get_contents('./tmp/cache'));
$data = array();
if ($id === 'undefined') {
    $id = empty($cache) ? 0 : $cache[0][0];
} elseif ($id === '0') {
    if (!empty($cache)) {
        $id = $cache[0][0];
        $data = $cache;
    }
} else {
    $end = end($cache);
    // read data from cache
    if ($id >= $end[0]) {
        foreach ($cache as $k => $c) {
            if ($c[0] === $id) break;
        }
        $data = array_slice($cache, 0, $k);
    }
    // read data from history (any problem witch Internet and are delays)
    else {
        $date = date('Y-m-d');
        $history = array();
        while (($history = array_merge(file('./history/'.$date, FILE_IGNORE_NEW_LINES), $history)) && $history[0] > $id) {
            $date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
            if (!file_exists('./history/'.$date)) break;
        }
        // prepare history
        $history = array_reverse($history);
        foreach ($history as & $ref) {
            $ref = explode('&', $ref);
        }
        // get data
        foreach ($history as $k => $h) {
            if ($h[0] === $id) break;
        }
        $data = array_slice($history, 0, $k);
    }
    $id = $cache[0][0];
}

echo json_encode(array('id' => $id, 'data' => $data));
