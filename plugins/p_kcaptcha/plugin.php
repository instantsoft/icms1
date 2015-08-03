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

class p_kcaptcha extends cmsPlugin {

    public function __construct() {

        global $_LANG;

        // Информация о плагине
        $this->info['plugin']      = 'p_kcaptcha';
        $this->info['title']       = $_LANG['P_CAPTCHA_TITLE'];
        $this->info['description'] = $_LANG['P_CAPTCHA_DESCRIPTION'];
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.0';
        $this->info['published']   = 1;
        $this->info['plugin_type'] = 'captcha';

        $this->events[] = 'GET_CAPTCHA';
        $this->events[] = 'CHECK_CAPTCHA';

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

        cmsPage::initTemplate('plugins','p_kcaptcha.tpl')->
                        assign('input_id', md5(uniqid()))->
                        display('p_kcaptcha.tpl');

        return ob_get_clean();

    }

    /**
     * Проверяет код каптчи
     * @return bool
     */
    public function checkCaptcha() {

        $captcha_code = cmsCore::request('captcha_code', 'str', '');
        $captcha_id   = cmsCore::request('captcha_id', 'str', '');

        if(!$captcha_id || empty($_SESSION['captcha'][$captcha_id]) || !$captcha_code) { return false; }

        $real_code = $_SESSION['captcha'][$captcha_id];
        unset($_SESSION['captcha'][$captcha_id]);

        return ($real_code === $captcha_code);

    }

}
