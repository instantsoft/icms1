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

function mod_rss($mod, $cfg){

    cmsCore::includeFile('includes/rss/lastRSS.php');

    $rss = new lastRSS;

    $rss->cache_dir   = PATH.'/cache';
    $rss->cache_time  = (int)@$cfg['cachetime']*3600;
    $rss->cp          = 'UTF-8';
    $rss->items_limit = $cfg['itemslimit'];

    $rs = $rss->Get($cfg['rssurl']);
    if(!$rs){ return false; }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('rs', $rs)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}