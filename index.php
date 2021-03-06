<?php

# uncomment for debug
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

function isSecure() {
    return
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

$thh = new Thh('chains.thh');
$host = idn_to_utf8($_SERVER['HTTP_HOST'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
$host = ltrim(mb_strtolower($host), 'www.'); 
$scheme = isSecure() ? 'https' : 'http';
$target = $thh->target($host);

// find with subdomain by asterix
if(!is_string($target)) {
    preg_match('/^([a-zA-Z0-9-_]+)\.([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+)$/', $host, $matches);
    if(count($matches) == 3) {
        $target = $matches[1] . '.' . $thh->target('*.'.$matches[2]);
    }
}

if (is_string($target))
    header("Location: $scheme://$target".$_SERVER['REQUEST_URI'], true, 301);
else
    http_response_code(404);

/**
 * Имплементация работы с target host host форматом
 */
class Thh
{
    protected $thh;

    /**
     *  $filename - путь к .thh файлу
     */
    public function __construct($filename)
    {
        $this->thh = self::load($filename);
    }

    /**
     * Возвращает target для хоста или null
     * true - если $host сам является целью
     * string - цель для указанного хоста
     * null если хост не найден
     */
    public function target($host)
    {
        $host = mb_strtolower($host);
        $result = null;
        foreach ($this->thh as $chain) {
            foreach ($chain as $index => $item) {
                if($host == $item) {
                    $result = true;
                    if($index) return $chain[0]; 
                }
            }
        }
        return $result;
    }

    /**
     * Читает thh файл в thh массив
     */
    public static function load($filename)
    {
        $thh = [];
        foreach (file($filename) as $line) {
            $chain = array_filter(preg_split('/\s+/', $line));
            if(count($chain) > 1) {
                $thh[] = array_map(function($item) { return mb_strtolower($item); }, $chain);
            }
        }
        return $thh;
    }

}

