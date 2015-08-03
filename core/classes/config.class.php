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

class cmsConfig {

    private static $instance = null;
    private static $config = array();

    private function __construct(){

		mb_internal_encoding("UTF-8");

		self::$config = self::getDefaultConfig();

		date_default_timezone_set(self::$config['timezone']);

		setlocale(LC_ALL, "ru_RU.UTF-8");

        return true;

	}

    private function __clone() {}

////////////////////////////////////////////////////////////////////////////////

    public function __get($name) {
        return self::$config[$name];
    }
    public function __set($name, $value){
        self::$config[$name] = $value;
    }
    public function __isset($name){
        return isset(self::$config[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает оригинальный массив конфигурации системы
     * отдельно используется только в админке и при установке
     * @return array
     */
    public static function getDefaultConfig() {

        $d_cfg = array('sitename'=>'',
                'title_and_sitename'=>1,
                'title_and_page'=>1,
                'hometitle'=>'',
                'homecom'=>'',
                'siteoff'=>0,
                'debug'=>0,
                'offtext'=>'',
                'keywords'=>'',
                'metadesc'=>'',
                'lang'=>'ru',
                'is_change_lang'=>0,
                'sitemail'=>'',
                'sitemail_name'=>'',
                'wmark'=>'watermark.png',
                'template'=>'_default_',
                'com_without_name_in_url'=>'content',
                'splash'=>0,
                'slight'=>1,
                'db_host'=>'',
                'db_base'=>'',
                'db_user'=>'',
                'db_pass'=>'',
                'db_prefix'=>'cms',
                'show_pw'=>1,
                'last_item_pw'=>1,
                'index_pw'=>0,
                'fastcfg'=>1,
                'mailer'=>'mail',
                'smtpsecure'=>'',
                'smtpauth'=>0,
                'smtpuser'=>'',
                'smtppass'=>'',
                'smtphost'=>'localhost',
                'smtpport'=>25,
                'timezone'=>'Europe/Moscow',
                'timediff'=>'',
                'user_stats'=>1,
                'seo_url_count'=>40,
                'allow_ip'=>'');

        $f = PATH.'/includes/config.inc.php';
        if (file_exists($f)){ require($f); } else { $_CFG = array(); }

        $cfg = array_merge($d_cfg, $_CFG);

        foreach ($cfg as $key => $value) {
            $cfg[$key] = stripslashes($value);
        }

        $cfg['cookie_key'] = md5($cfg['sitename']);

        return $cfg;

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает значение опции конфигурации
     * или полный массив значений
     * @param str $value
     * @return mixed
     */
    public static function getConfig($value = '') {

		if($value){
            if(isset(self::$config[$value])){
                return self::$config[$value];
            } else {
                return null;
            }
		} else {
			return self::$config;
		}

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Сохраняет массив в файл конфигурации
     * @param array $_CFG
     */
    public static function saveToFile($_CFG, $file='config.inc.php'){

        global $_LANG;
        $filepath = PATH.'/includes/'.$file;

        if (file_exists($filepath)){
            if (!@is_writable($filepath)){ die(sprintf($_LANG['FILE_NOT_WRITABLE'], '/includes/'.$file)); }
        } else {
            if (!@is_writable(dirname($filepath))){ die(sprintf($_LANG['DIR_NOT_WRITABLE'], '/includes')); }
        }

        $cfg_file = fopen($filepath, 'w+');

        fputs($cfg_file, "<?php \n");
        fputs($cfg_file, "if(!defined('VALID_CMS')) { die('ACCESS DENIED'); } \n");
        fputs($cfg_file, '$_CFG = array();'."\n");

        foreach($_CFG as $key=>$value){
            if (is_int($value)){
                $s = '$_CFG' . "['$key'] \t= $value;\n";
            } else {
                $s = '$_CFG' . "['$key'] \t= '".addslashes($value)."';\n";
            }
            fwrite($cfg_file, $s);
        }

        fwrite($cfg_file, "?>");
        fclose($cfg_file);

        return true;

    }

}