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

$module_id = cmsCore::request('module_id', 'int', 0);
if(!$module_id){ cmsCore::halt(); }

$cfg = $inCore->loadModuleConfig($module_id);

cmsCore::includeFile('modules/mod_polls/module.php');

mod_polls(array('id'=>$module_id), $cfg);