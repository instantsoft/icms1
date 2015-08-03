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

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

function applet_help(){

    $topic = cmsCore::request('topic', 'str', '');

    $help_url['menu']       = 'http://www.instantcms.ru/wiki/doku.php/%D0%BC%D0%B5%D0%BD%D1%8E_%D1%81%D0%B0%D0%B9%D1%82%D0%B0';
    $help_url['modules']    = 'http://www.instantcms.ru/wiki/doku.php/%D0%BC%D0%BE%D0%B4%D1%83%D0%BB%D0%B8';
    $help_url['content']    = 'http://www.instantcms.ru/wiki/doku.php/%D0%BA%D0%BE%D0%BD%D1%82%D0%B5%D0%BD%D1%82';
    $help_url['cats']       = 'http://www.instantcms.ru/wiki/doku.php/%D0%BA%D0%BE%D0%BD%D1%82%D0%B5%D0%BD%D1%82';
    $help_url['components'] = 'http://www.instantcms.ru/wiki/doku.php/%D0%BA%D0%BE%D0%BC%D0%BF%D0%BE%D0%BD%D0%B5%D0%BD%D1%82%D1%8B';
    $help_url['users']      = 'http://www.instantcms.ru/wiki/doku.php/%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D0%B8';
    $help_url['config']     = 'http://www.instantcms.ru/wiki/doku.php/%D0%BD%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B0_%D1%81%D0%B0%D0%B9%D1%82%D0%B0';

    if (isset($help_url[$topic])){
        cmsCore::redirect($help_url[$topic]);
    }

	cmsCore::redirect('http://www.instantcms.ru/wiki');

}

?>