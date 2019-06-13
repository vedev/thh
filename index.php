<?php

# uncomment for debug
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

$thh = new Thh('chains.thh');
$host = idn_to_utf8($_SERVER['HTTP_HOST'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
$scheme = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https' : 'http';;
$target = $thh->target($host);

if (is_string($target))
    header("Location: $scheme://$target", true, 301);
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

