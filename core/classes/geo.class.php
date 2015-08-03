<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

class cmsgeo {

    private $url = 'http://ipgeobase.ru:7020/geo?ip=';
    private $ip;
    private static $valid_keys = array('inetnum', 'country', 'city', 'region', 'district', 'lat', 'lng');
    private static $data = array();

    private function __construct($ip) {
        $this->ip = $ip;
    }

// ============================================================================ //
    /**
     * Возвращает массив данных с ключами $valid_keys
     * где inetnum - сеть, country - индекс страны, city - название города
     * region - название региона, district - название округа, lat, lng - координаты
     * @param str $ip IP адрес клиента
     * @param str $key Один из параметров, перечисленных выше
     * @param bool $cache Кешировать в куки и отдавать из них
     * @return mixed
     */
    public static function getInfo($ip, $key = false, $cache = true) {

        // для работы требуется CURL
        if(!function_exists('curl_setopt') || !function_exists('curl_init')) { return false; }

        // если уже получали данные, возвращаем их сразу
        $cookie_data = (string)cmsCore::getCookie('geodata');
        if(!empty(self::$data[$ip])){
            $data = self::$data[$ip];
        } elseif($cookie_data && $cache){
            $data = unserialize($cookie_data);
            if(is_array($data)){
                $data = cmsCore::cleanVar($data, 'array_str', null);
            } else {
                unset($data);
            }
        }

        if(!isset($data)){

            // выполняем запрос к ipgeobase.ru
            $thisObj = new self($ip);

            $data = $thisObj->getData();
            if(!$data){ return false; }

            // сохраняем ответ
            // в свойство
            self::$data[$ip] = $data;
            // и в куки на сутки
            if($cache){
                cmsCore::setCookie('geodata', serialize($data), time()+3600*24);
            }

        }

        // можно запрашивать только определенные ключи
        if(!in_array($key, self::$valid_keys)){
            $key = false;
        }

        // что возвращаем
        if($key && isset($data[$key])){
            return $data[$key];
        } else {
            return $data;
        }

    }

// ============================================================================ //
    /**
     * Возвращает массив данных от ipgeobase.ru
     * @return array
     */
    private function getData() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$this->ip);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'InstantCMS');
        $out = simplexml_load_string(curl_exec($ch));

        foreach ($out->ip[0] as $key=>$value) {
            $data[$key] = (string)$value;
        }

        return $data;

    }

}
?>