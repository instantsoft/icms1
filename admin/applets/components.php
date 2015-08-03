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

function cpComponentHasConfig($item){
	return file_exists('components/'.$item['link'].'/backend.php');
}

function cpComponentCanRemove($item){
	if($item['system']) { return false; }
	global $adminAccess;
	return cmsUser::isAdminCan('admin/com_'.$item['link'], $adminAccess);
}

function applet_components(){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();
	$inUser = cmsUser::getInstance();

    global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/components', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_COMPONENTS'];
 	cpAddPathway($_LANG['AD_COMPONENTS'], 'index.php?view=components');

	$do = cmsCore::request('do', 'str', 'list');

	$id   = cmsCore::request('id', 'int', 0);
	$link = cmsCore::request('link', 'str', '');
	if($link){
        $_REQUEST['id'] = $id = $inCore->getComponentId($link);
	}

    if ($do != 'list'){
        $com = $inCore->getComponent($id);
        if(!$com){ cmsCore::error404(); }
        if (!cmsUser::isAdminCan('admin/com_'.$com['link'], $adminAccess)) { cpAccessDenied(); }
    }

    if ($do == 'show'){

		dbShow('cms_components', $id);
		echo '1'; exit;

	}

	if ($do == 'hide'){

		dbHide('cms_components', $id);
		echo '1'; exit;

	}

	if ($do == 'config'){

        $file = PATH.'/admin/components/'.$com['link'].'/backend.php';

        if (file_exists($file)){
            cpAddPathway($com['title'].' v'.$com['version'], '?view=components&do=config&id='.$com['id']);
            cmsCore::loadLanguage('components/'.$com['link']);
            cmsCore::loadLanguage('admin/components/'.$com['link']);
            include $file; return;
        } else {
            cmsCore::redirect('index.php?view=components');
        }

	}

	if ($do == 'list'){

        $toolmenu[] = array('icon'=>'install.gif', 'title'=>$_LANG['AD_INSTALL_COMPONENTS'], 'link'=>'?view=install&do=component');
        $toolmenu[] = array('icon'=>'help.gif', 'title'=>$_LANG['AD_HELP'], 'link'=>'?view=help&topic=components');

		cpToolMenu($toolmenu);

        $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
        $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title','link'=>'?view=components&do=config&id=%id%', 'width'=>'');
        $fields[] = array('title'=>$_LANG['AD_VERSION'], 'field'=>'version', 'width'=>'60');
        $fields[] = array('title'=>$_LANG['AD_ENABLE'], 'field'=>'published', 'width'=>'65');
        $fields[] = array('title'=>$_LANG['AD_AUTHOR'], 'field'=>'author', 'width'=>'200');
        $fields[] = array('title'=>$_LANG['AD_LINK'], 'field'=>'link', 'width'=>'100');

        $actions[] = array('title'=>$_LANG['AD_CONFIG'], 'icon'=>'config.gif', 'link'=>'?view=components&do=config&id=%id%', 'condition'=>'cpComponentHasConfig');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'link'=>'?view=install&do=remove_component&id=%id%', 'condition'=>'cpComponentCanRemove', 'confirm'=>$_LANG['AD_DELETED_COMPONENT_FROM']);

		$where = '';

        if ($inUser->id > 1){
            foreach($adminAccess as $key=>$value){
                if (mb_strstr($value, 'admin/com_')){
                    if ($where) { $where .= ' OR '; }
                    $value = str_replace('admin/com_', '', $value);
                    $where .= "link='{$value}'";
                }
            }
        }

		if (!$where) { $where = 'id>0'; }

		cpListTable('cms_components', $fields, $actions, $where);

	}

}

?>