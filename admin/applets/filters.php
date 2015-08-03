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

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

function applet_filters(){

	global $_LANG;

	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }
	if (!cmsUser::isAdminCan('admin/filters', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_FILTERS'];
 	cpAddPathway($_LANG['AD_FILTERS'], 'index.php?view=filters');

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);

	if ($do == 'hide'){
		dbHide('cms_filters', $id);
		echo '1'; exit;
	}

	if ($do == 'show'){
		dbShow('cms_filters', $id);
		echo '1'; exit;
	}

	if ($do == 'list'){

        $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
        $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'250');
        $fields[] = array('title'=>$_LANG['DESCRIPTION'], 'field'=>'description', 'width'=>'');
        $fields[] = array('title'=>$_LANG['AD_ENABLE'], 'field'=>'published', 'width'=>'100');

		$actions = array();

		cpListTable('cms_filters', $fields, $actions);

	}

}

?>