<?php
/**
 * @param $date
 * @param null $null
 * @return false|int|null
 */
function DateToInt($date, $null = null)
{
    if ($date instanceof DateTime) {
        $temp = $date->getTimestamp();
        $str = $date->format('Y-m-d H:i:s');

        if (!$temp && !$str) { // don't interpret 1970-01-01 as not set
            return $null;
        }
        return $temp;
    }

    if (!$date) {
        return $null;
    }

    if (!is_numeric($date)) {
        $date = strtotime($date);
    }

    return $date;
}

/**
 * @param int $time
 * @param null $null
 * @param string $format
 * @return false|null|string
 */
function Datestamp($date = null, $null = null, $format = 'Y-m-d', $offset = null)
{
    if (!$format) {
        $format = 'Y-m-d';
    }

    if (is_null($null) && is_null($date)) {
        $date = time();
    }
    $date = DateToInt($date, $null);
    if ($date === $null) {
        return $null;
    }

    if (!$date && $null) {
        return $null;
    }

    if ($offset) {
        $date += $offset * 3600;
    }
    return date($format, $date);
}

/**
 * @param int $time
 *
 * @return bool|string
 */
function Timestamp($date = null, $null = null, $format = 'Y-m-d H:i:s')
{
    if (!$format) {
        $format = 'Y-m-d H:i:s';
    }
    return Datestamp($date, $null, $format);
}