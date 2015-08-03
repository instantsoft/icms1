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

function mod_usersearch($mod, $cfg){

    cmsCore::loadLanguage('components/users');

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}