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

function info_module_mod_polls()
{
    $_module['title']       = 'Голосования';
    $_module['name']        = 'Голосования';
    $_module['description'] = 'Выводит на сайте голосование';
    $_module['link']        = 'mod_polls';
    $_module['position']    = 'sidebar';
    $_module['author']      = 'instantCMS team';
    $_module['version']     = '1.10.7';
    $_module['config']      = array();

    return $_module;

}

function install_module_mod_polls()
{
    return true;
}

function upgrade_module_mod_polls()
{
    return true;
}
