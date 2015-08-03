<?php Error_Reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
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

header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: DENY');

session_start();

define('VALID_CMS', 1);
define('VALID_CMS_ADMIN', 1);

define('PATH', $_SERVER['DOCUMENT_ROOT']);

require(PATH.'/core/cms.php');
require(PATH.'/admin/includes/cp.php');
require(PATH.'/includes/tools.inc.php');

$inCore = cmsCore::getInstance(false, true);

cmsCore::loadClass('page');
cmsCore::loadClass('user');
cmsCore::loadClass('actions');

$inPage = cmsPage::getInstance();
$inConf = cmsConfig::getInstance();
$inDB   = cmsDatabase::getInstance();
$inUser = cmsUser::getInstance();

if (!$inUser->update()) { cmsCore::error404(); }

// проверяем доступ по Ip
if(!cmsCore::checkAccessByIp($inConf->allow_ip)) { cmsCore::error404(); }

define('TEMPLATE_DIR', PATH.'/templates/'.$inConf->template.'/');
define('DEFAULT_TEMPLATE_DIR', PATH.'/templates/_default_/');

cmsCore::loadLanguage('admin/lang');

//-------CHECK AUTHENTICATION--------------------------------------//
if (!$inUser->is_admin){
    include PATH.'/admin/login.php';
    cmsCore::halt();
}
//--------LOAD ACCESS OPTIONS LIST---------------------------------//

$adminAccess = cmsUser::getAdminAccess();

//------------------------------------------------------------------//

$inUser->onlineStats();

$GLOBALS['applet'] = cmsCore::request('view', 'str', 'main');
if (!preg_match('/^[a-z0-9]+$/i', $GLOBALS['applet'])) { cmsCore::error404(); }

$GLOBALS['cp_page_title'] = '';
$GLOBALS['cp_page_head']  = array();
$GLOBALS['cp_page_body']  = '';

$GLOBALS['cp_pathway']= array(
    array(
        'title'=>$_LANG['PATH_HOME'],
        'link'=>'/admin/',
    )
);

cpProceedBody();

include(PATH.'/admin/template.php');
