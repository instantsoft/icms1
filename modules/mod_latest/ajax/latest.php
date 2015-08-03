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

cmsCore::loadLanguage('modules/mod_latest');

$module_id = cmsCore::request('module_id', 'int', '');

if(!$module_id) { cmsCore::halt(); }

$cfg = $inCore->loadModuleConfig($module_id);
// номер страницы передаем через конфиг
$cfg['page'] = cmsCore::request('page', 'int', 1);

cmsCore::includeFile('modules/mod_latest/module.php');

mod_latest(array('id'=>$module_id), $cfg);