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

session_start();

define("VALID_CMS", 1);
define('PATH', $_SERVER['DOCUMENT_ROOT']);

include(PATH.'/core/cms.php');
cmsCore::getInstance();

$template = preg_replace ('/[^a-zA-Z_\-]/i', '', cmsCore::request('template', 'str', ''));

if ($template){
    $_SESSION['template'] = $template;
} else {
    unset($_SESSION['template']);
}

cmsCore::clearCache();

cmsCore::redirectBack();