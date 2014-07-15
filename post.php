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



function getSetup($key = null) {
    $arr = parse_ini_file('setup.ini');
    return isset($key) ? $arr[$key] : $arr;
}



//$_POST['text'] = 'abc';
session_start();
if (!isset($_SESSION['name'])) return;
$text = isset($_POST['text']) ? $_POST['text'] : '';
if ($text === '') return;

$isApc = extension_loaded('apc');

$setup = getSetup();
$time = time();
$date = date('Y-m-d', $time);
$uniqid = uniqid();
$id = $time.'-'.$uniqid;

$tmpDir = './tmp/';
$historyDir = './history/';

$tmpFile = $tmpDir.'cache';
$historyFile = $historyDir.$date;

$fh = @fopen($historyFile, 'a');
if ($fh === false) {
    mkdir($historyDir);
    if (!is_dir($tmpDir)) mkdir($tmpDir);
    $fh = @fopen($historyFile, 'a');
}

/* start semafore */
flock($fh, LOCK_EX);

// data
$data = array($id, $_SESSION['name'], stripslashes(htmlspecialchars($text)));

// write history
fwrite($fh, implode('&', $data)."\n");

// cache
if ($isApc) {
    $cache = apc_fetch('chat');
    if ($cache === false) {
        $cache = array();
    }
} else {
    $cache = @file_get_contents($tmpFile);
    if ($cache === false) {
        $cache = array();
    } else {
        $cache = unserialize($cache);
    }    
}

array_unshift($cache, $data);

// delete expired cache
$expireTime = floor($time - $setup['interval']/1000 - $setup['expire_cache']);
foreach (array_reverse($cache,true) as $k => $e) {
    if ($e[0] < $expireTime) {
        unset($cache[$k]);
    } else {
        break;
    }
}

if ($isApc) {
    apc_store('chat', $cache);
} else {
    file_put_contents($tmpFile, serialize($cache));
}

/* end semafore */
flock($fh, LOCK_UN);
fclose($fh);
