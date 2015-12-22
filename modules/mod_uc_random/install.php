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

function info_module_mod_random_uc()
{
    $_module['title']       = 'Случайное в каталоге';
    $_module['name']        = 'Случайное в каталоге';
    $_module['description'] = 'Выводит на сайте случайную записи из универсального каталоге';
    $_module['link']        = 'mod_random_uc';
    $_module['position']    = 'maintop';
    $_module['author']      = 'instantCMS team';
    $_module['version']     = '1.10.7';
    $_module['config']      = array();

    return $_module;

}

function install_module_mod_random_uc()
{
    return true;
}

function upgrade_module_mod_random_uc()
{
    return true;
}
