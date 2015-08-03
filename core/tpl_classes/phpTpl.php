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
 * Пример класса для работы с php шаблонами
 */
class phpTpl{

    private $tpl_vars   = array();
    private $tpl_folder;
    private $tpl_file;

    public function __construct($tpl_folder, $tpl_file){
        $this->tpl_folder = $tpl_folder;
        $this->tpl_file   = $tpl_file;
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Показывает файл шаблона
     * параметр $tpl_file оставлен для совместимости
     */
    public function display($tpl_file){

        global $_LANG;
        extract($this->tpl_vars);

        include(TEMPLATE_DIR . $this->tpl_folder.'/'.$this->tpl_file);

    }
    /**
     * Добавляет переменную в набор
     */
    public function assign($tpl_var, $value){

        if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $val) {
                if ($key) {
                    $this->tpl_vars[$key] = $val;
                }
            }
        } else {
            if ($tpl_var){
                $this->tpl_vars[$tpl_var] = $value;
            }
        }

        return $this;

    }

}

?>
