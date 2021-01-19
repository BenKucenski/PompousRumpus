<?php
# database
define('DATA_HOST','localhost');
define('DATA_USER','root');
define('DATA_PASS','root');
define('DATA_BASE','dsn');

# meta
define('META_TITLE','PompousRumpus.com');

# post limits
define('POST_NUMBER_LIMIT', 100);
define('POST_DAYS_LIMIT', 30);

# allow registrations - undefined = true
define('ALLOW_REGISTRATIONS', true);

// you may need to disable this on your local machine, but must be enabled on production
// https://stackoverflow.com/questions/85816/how-can-i-force-users-to-access-my-page-over-https-instead-of-http

// Use HTTP Strict Transport Security to force client to use secure connections only
$use_sts = true;

// iis sets HTTPS to 'off' for non-SSL requests
if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    header('Strict-Transport-Security: max-age=31536000');
} elseif ($use_sts) {
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
    // we are in cleartext at the moment, prevent further execution and output
    die();
}