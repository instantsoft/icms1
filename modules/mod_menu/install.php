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

function info_module_mod_menu()
{
    $_module['title']       = 'Меню';
    $_module['name']        = 'Меню';
    $_module['description'] = 'Выводит на сайте меню';
    $_module['link']        = 'mod_menu';
    $_module['position']    = 'topmenu';
    $_module['author']      = 'instantCMS team';
    $_module['version']     = '1.10.7';
    $_module['config']      = array();

    return $_module;

}

function install_module_mod_menu()
{
    return true;
}

function upgrade_module_mod_menu()
{
    return true;
}
