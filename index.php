<?php
# php 7+
# error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('GMT');

# session
session_name('dsn');
session_start();

require_once 'secure.php';

require_once 'layers/ApiLayer.php';
require_once 'layers/DataLayer.php';

require_once 'functions/GUID.php';
require_once 'functions/KeyboardOnly.php';
require_once 'functions/Dates.php';
require_once 'functions/CurlHeader.php';
require_once 'functions/Curl.php';

$dataLayer = new DataLayer(DATA_HOST, DATA_USER, DATA_PASS, DATA_BASE);

$path = explode('?', $_SERVER['REQUEST_URI'])[0];

if(substr($path, 0, strlen('/api')) === '/api')
{
    $api = new ApiLayer($dataLayer);
    $res = $api->Process(str_replace('/api/', '', $path), json_decode(json_encode($_REQUEST)), json_decode(json_encode($_SERVER)));
    header("Content-Type: application/json");
    exit(json_encode($res));
}

$_SESSION['user_id'] = $_SESSION['user_id'] ?? 0;
$_SESSION['hour_offset'] = $_SESSION['hour_offset'] ?? 0;

$path = explode('?', $_SERVER['REQUEST_URI'])[0];
$root = explode('/', $path)[1];


ob_start();
if($_SESSION['user_id']) {
    switch($root) {
        case 'account':
            require_once 'pages/account.php';
            break;
        default:
            require_once 'pages/feed.php';
    }
} else {
    require_once 'pages/home.php';
}
$PAGE_HTML = ob_get_clean();

require_once 'masterpages/default.php';






