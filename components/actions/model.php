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

class cms_model_actions{

    private $inDB;
	public  $config = array();

    public function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('actions');
		cmsCore::loadClass('actions');
		cmsCore::loadLanguage('components/users');
    }

/* ========================================================================== */

    public static function getDefaultConfig() {

        return array(
            'show_target'=>1,
            'perpage'=>10,
            'perpage_tab'=>15,
            'action_types'=>'',
            'meta_keys' => '',
            'meta_desc' => ''
        );

    }

/* ========================================================================== */

    public function deleteAction($id){

        cmsActions::removeLogById($id);

        return true;

    }

}