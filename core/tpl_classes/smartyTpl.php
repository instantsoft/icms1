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

        $this->smarty->compile_id = $is_exists_tpl_file ? TEMPLATE : pathinfo(DEFAULT_TEMPLATE_DIR, PATHINFO_BASENAME);

        $template_dir = $is_exists_tpl_file ? TEMPLATE_DIR : DEFAULT_TEMPLATE_DIR;

        $this->smarty->setTemplateDir(array(
            $template_dir,
            $template_dir . '/admin',
            $template_dir . '/components',
            $template_dir . '/modules',
            $template_dir . '/plugins',
            $template_dir . '/special',
        ));

        $this->smarty->assign(array(
            'LANG'      => $_LANG,
            'is_ajax'   => cmsCore::isAjax(),
            'is_auth'   => cmsUser::getInstance()->id,
            'is_admin'  => cmsUser::getInstance()->is_admin,
            'do'        => cmsCore::getInstance()->do,
            'component' => cmsCore::getInstance()->component,
        ));
    }

    private function loadSmarty(){

        if(isset(self::$i_smarty)){
            return self::$i_smarty;
        }

        cmsCore::includeFile('/includes/smarty/libs/Smarty.class.php');

        $smarty = new Smarty();

        $smarty->setCompileDir(PATH.'/cache/');
        $smarty->setCacheDir(PATH.'/cache/');

        $smarty->addPluginsDir(array(
            TEMPLATE_DIR . '/assets/plugins/smarty',
            __DIR__.'/plugins/smarty'
        ));

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
