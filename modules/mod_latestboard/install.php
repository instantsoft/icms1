<?php

/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.7                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

function info_module_mod_latestboard()
{
    $_module['title']       = 'Новые объявления';
    $_module['name']        = 'Новые объявления';
    $_module['description'] = 'Выводит на сайте новые объявления';
    $_module['link']        = 'mod_latestboard';
    $_module['position']    = 'maintop';
    $_module['author']      = 'instantCMS team';
    $_module['version']     = '1.10.7';
    $_module['config']      = array();

    return $_module;

}

function install_module_mod_latestboard()
{
    return true;
}

function upgrade_module_mod_latestboard()
{
    return true;
}
