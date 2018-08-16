<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 15:43
 */

namespace Dandelion;


class Http
{
    public static function get($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}