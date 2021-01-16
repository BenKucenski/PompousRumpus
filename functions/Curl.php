<?php

/**
 * Class Curl
 * @property CurlHeader Header
 * @property string Body
 * @property string[] HeaderHash
 * @property string HeaderRaw
 * @property int StatusCode
 */
class Curl
{
    public $Body;
    public $HeaderHash;
    public $HeaderRaw;
    public $Header;
    public $StatusCode;

    public $URL;
    public $Params;
    public $SentHeader;

    public static $FollowLocation = true;

    /**
     * @param $path
     * @param $params
     * @return Curl
     */
    public static function Post($path, $params, $additional_headers = null)
    {
        if (is_array($params)) {
            $params = http_build_query($params);
        }

        // Initiate CURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, true);

        $parts = parse_url($path);
        $host = isset($parts['host']) ? $parts['host'] : '';

        $header[] = "Accept: */*";
        $header[] = "Accept-Language: en-us";
        $header[] = "UA-CPU: x86";
        $header[] = "Host: $host";
        $header[] = "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)";
        $header[] = "Connection: Keep-Alive";

        if($additional_headers) {
            foreach($additional_headers as $k => $v) {
                $header[] = $k . ': ' . $v;
            }
        }

        if (!defined('COOKIE_FILE')) {
            define('COOKIE_FILE', './cookie.txt');
        }
        // Pass our values
        curl_setopt($ch, CURLOPT_URL, $path);

        if ($params != "") {
            $header[] = 'Content-Length' . ': ' . strlen($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, self::$FollowLocation);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);

        $content = curl_exec($ch);

        $err = curl_error($ch);
        if ($err) {
            return new Curl();
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response_header = substr($content, 0, $header_size);
        $body = substr($content, $header_size);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);


        $head = explode("\n", $response_header);
        $head_hash = [];
        foreach ($head as $val) {
            $val = explode(": ", $val);
            if (isset($val[1])) {
                $head_hash[$val[0]] = trim($val[1]);
            }
        }

        $res = new Curl();
        $res->Body = $body;
        $res->HeaderRaw = $response_header;
        $res->Header = new CurlHeader($head_hash);
        $res->HeaderHash = $head_hash;
        $res->StatusCode = $status;

        $res->URL = $path;
        $res->Params = $params;
        $res->SentHeader = $header;

        return $res;
    }

    /**
     * @param $path
     * @param null $params
     * @param null $username
     * @param null $password
     * @return Curl
     */
    public static function Get($path, $params = null, $additional_headers = null)
    {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        // Initiate CURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, false);

        $parts = parse_url($path);
        $host = isset($parts['host']) ? $parts['host'] : '';

        $header[] = "Accept: */*";
        $header[] = "Accept-Language: en-us";
        $header[] = "UA-CPU: x86";
        $header[] = "Host: $host";
        $header[] = "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)";
        $header[] = "Connection: Keep-Alive";

        if($additional_headers) {
            foreach($additional_headers as $k => $v) {
                $header[] = $k . ': ' . $v;
            }
        }

        if (!defined('COOKIE_FILE')) {
            define('COOKIE_FILE', './cookie.txt');
        }

        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, self::$FollowLocation);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);


        // Pass our values
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $path . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $path);
        }

        $content = curl_exec($ch);

        $err = curl_error($ch);
        if ($err) {
            return new Curl();
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response_header = substr($content, 0, $header_size);
        $body = substr($content, $header_size);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $head = explode("\n", $response_header);
        $head_hash = [];
        foreach ($head as $val) {
            $val = explode(": ", $val);
            if (isset($val[1])) {
                $head_hash[$val[0]] = trim($val[1]);
            }
        }

        $res = new Curl();
        $res->Body = $body;
        $res->HeaderRaw = $response_header;
        $res->Header = new CurlHeader($head_hash);
        $res->HeaderHash = $head_hash;
        $res->StatusCode = $status;

        $res->URL = $path;
        $res->Params = $params;
        $res->SentHeader = $header;

        return $res;
    }
}



