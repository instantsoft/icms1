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

function mod_template($mod, $cfg){

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('current_template', (isset($_SESSION['template']) ? $_SESSION['template'] : ''))->
            assign('templates', cmsCore::getDirsList('/templates'))->
            display($cfg['tpl']);

    return true;

}