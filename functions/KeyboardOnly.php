<?php

/**
 * @param $str
 * @return null|string|string[]
 */
function KeyboardOnly($str)
{
    $str = preg_replace('/[^a-z0-9\!\@\#\$\%\^\&\*\(\)\-\=\_\+\[\]\\\{\}\|\;\'\:\"\,\.\/\<\>\\\?\ \r\n]/si', '', $str);
    return preg_replace('/\s+/si', ' ', $str);
}
