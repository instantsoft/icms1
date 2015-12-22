<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.7                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

class p_composer extends cmsPlugin {

    public function __construct() {

        global $_LANG;

        $this->info = array(
            'plugin'      => 'p_composer',
            'title'       => $_LANG['P_COMPOSER_TITLE'],
            'description' => $_LANG['P_COMPOSER_DESCRIPTION'],
            'author'      => 'maxiSoft & InstantCMS Team',
            'version'     => '0.0.1',
            'published'   => 1,
            'plugin_type' => 'system',
        );

        $this->events = array('GET_INDEX');

        parent::__construct();

    }

    /**
     * Обработка события получения индекссной страницы
     * @param string $event
     * @param array $item
     * @return array
     */
    public function execute($event = '', $item = array()){

        $file_name = PATH.'/vendor/autoload.php';

        if (file_exists($file_name)) {
            include_once $file_name;
        }

        return $item;
    }
}
