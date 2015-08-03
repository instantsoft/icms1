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

define('PATH', $_SERVER['DOCUMENT_ROOT']);
define("VALID_CMS_ADMIN", 1);
include(PATH.'/core/ajax/ajax_core.php');

cmsCore::includeFile('admin/includes/cp.php');
cmsCore::loadClass('formgen');
cmsCore::loadLanguage('admin/lang');
cmsCore::loadLanguage('admin/applets/applet_modules');

if (!$inUser->is_admin) { cmsCore::halt($_LANG['ACCESS_DENIED']); }

$adminAccess = cmsUser::getAdminAccess();

if (!cmsUser::isAdminCan('admin/modules', $adminAccess)) {
    cmsCore::halt($_LANG['ACCESS_DENIED']);
}

$module_id = cmsCore::request('id', 'int');

$mod = $inDB->get_fields('cms_modules', "id='{$module_id}'", '*');
if (!$mod) { cmsCore::halt(); }

$mod_name = $mod['user'] ? '' : preg_replace('/[^a-z0-9_\-]/iu', '', $mod['content']);

$xml_file = PATH.'/admin/modules/'.$mod_name.'/backend.xml';
$php_file = PATH.'/admin/modules/'.$mod_name.'/backend.php';

$mode       = 'none';
$cfg_form   = '';

if (file_exists($xml_file)){
    $cfg = $inCore->loadModuleConfig($module_id);
    $formGen = new cmsFormGen($xml_file, $cfg);
    $cfg_form = $formGen->getHTML();
    $mode = 'xml';
} elseif (file_exists($php_file)){
    $mode = 'php';
} elseif ($mod['user']){
    $mode = 'custom';
}

cmsPage::includeTemplateFile('admin/modconfig.php');
