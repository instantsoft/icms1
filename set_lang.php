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

header('Content-Type: text/html; charset=utf-8');
Error_Reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();

define('PATH', dirname(__FILE__));
define("VALID_CMS", 1);

include(PATH.'/core/cms.php');

cmsCore::getInstance();

if(!cmsConfig::getConfig('is_change_lang')){
    cmsCore::error404();
}

$set_lang = cmsCore::request('lang', 'str', 'ru');

$langs = cmsCore::getDirsList('/languages');

if(!in_array($set_lang, $langs)){
    cmsCore::error404();
}

$_SESSION['lang'] = $set_lang;

cmsCore::redirectBack();