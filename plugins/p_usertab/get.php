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
include(PATH.'/core/ajax/ajax_core.php');

// Входные переменые
$user_id  = cmsCore::request('user_id', 'int', 0);
if(!$user_id) { cmsCore::halt(); }

$plugin = cmsCore::loadPlugin('p_usertab');

if ($plugin!==false){
    $plugin->execute('', array('user_id'=>$user_id));
    $html = $plugin->viewTab($user_id);
}

cmsCore::halt($html);
