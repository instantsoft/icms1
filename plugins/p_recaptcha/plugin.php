<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2016                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

class p_recaptcha extends cmsPlugin {

    private $api_url = 'https://www.google.com/recaptcha/api/siteverify';

    public $config = array(
        'public_key'  => '0',
        'private_key' => '',
        'theme'       => 'light',
        'size'        => 'normal',
        'lang'        => 'ru'
    );

    public function __construct() {

        global $_LANG;

        $this->info = array(
            'plugin'      => 'p_recaptcha',
            'title'       => $_LANG['P_RECAPTCHA_TITLE'],
            'description' => $_LANG['P_RECAPTCHA_DESCRIPTION'],
            'author'      => 'InstantCMS Team',
            'version'     => '1.0',
            'published'   => 1,
            'plugin_type' => 'captcha'
        );

        $this->events = array(
            'GET_CAPTCHA',
            'CHECK_CAPTCHA'
        );

        parent::__construct();

    }

    /**
     * Обработка событий
     * @param string $event
     * @param array $item
     * @return html
     */
    public function execute($event='', $item=array()){

        switch ($event){
            case 'GET_CAPTCHA':   return $this->getCaptcha();
            case 'CHECK_CAPTCHA': return $this->checkCaptcha();
        }

        return $item;

    }


    /**
     * Возвращает код каптчи
     * @return html
     */
    public function getCaptcha() {

        ob_start();

        cmsPage::initTemplate('plugins','p_recaptcha.tpl')->
                assign('config', $this->config)->
            display('p_recaptcha.tpl');

        return ob_get_clean();

    }

    /**
     * Проверяет код каптчи
     * @return bool
     */
    public function checkCaptcha() {

        $response = cmsCore::request('g-recaptcha-response', 'html', '');
        if(!$response){ return false; }

        return $this->callApi(array(
            'secret'   => $this->config['private_key'],
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ));

    }

    private function callApi($params) {

        if (!function_exists('curl_init')){

            $data = @file_get_contents($this->api_url.'?'.http_build_query($params));

        } else {

            $curl = curl_init();

            if(strpos($this->api_url, 'https') !== false){
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($curl, CURLOPT_URL, $this->api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            $data = curl_exec($curl);

            curl_close($curl);

        }

        if(!$data){ return false; }

        $data = json_decode($data, true);

        return !empty($data['success']);

    }

}
