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
/**
 * Класс инициализации шаблонизатора Smarty
 */
class smartyTpl{

    private static $i_smarty;
    private $smarty;

    public function __construct($tpl_folder, $tpl_file){

        global $_LANG;

        $this->smarty = $this->loadSmarty();

        $is_exists_tpl_file = file_exists(TEMPLATE_DIR . $tpl_folder.'/'.$tpl_file);

        $template_dir = $is_exists_tpl_file ? TEMPLATE_DIR : DEFAULT_TEMPLATE_DIR;

        $this->smarty->setTemplateDir($template_dir.'/'.$tpl_folder);

        $this->smarty->compile_id = $is_exists_tpl_file ? TEMPLATE : '_default_';
        $this->smarty->assign('LANG', $_LANG);

    }

    private function loadSmarty(){

        if(isset(self::$i_smarty)){
            return self::$i_smarty;
        }

        cmsCore::includeFile('/includes/smarty/libs/Smarty.class.php');

        $smarty = new Smarty();

        $smarty->setCompileDir(PATH.'/cache/');
        $smarty->setCacheDir(PATH.'/cache/');
        $smarty->assign('is_ajax', cmsCore::isAjax());
        $smarty->assign('is_auth', cmsUser::getInstance()->id);

        self::$i_smarty = $smarty;

        return $smarty;

    }

    public function __set($name, $value){
        $this->smarty->{$name} = $value;
    }

    public function __get($name){
        return $this->smarty->{$name};
    }

    public function __call($name, $arguments){
        return call_user_func_array(array($this->smarty, $name), $arguments);
    }

}