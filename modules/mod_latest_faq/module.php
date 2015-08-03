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

function mod_latest_faq($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    if (!isset($cfg['newscount'])) { $cfg['newscount'] = 2;}
    if (!isset($cfg['cat_id'])) { $cfg['cat_id'] = 0;}
    if (!isset($cfg['maxlen'])) { $cfg['maxlen'] = 120;}

    if ($cfg['cat_id']) {
        $catsql = 'AND category_id = '.$cfg['cat_id'];
    } else { $catsql = ''; }

    $sql = "SELECT *
            FROM cms_faq_quests
            WHERE published = 1 ".$catsql."
            ORDER BY pubdate DESC
            LIMIT ".$cfg['newscount'];

    $result = $inDB->query($sql) ;

    $faq = array();

    if ($inDB->num_rows($result)){

        while($con = $inDB->fetch_assoc($result)){
            $con['date'] = cmsCore::dateFormat($con['pubdate']);
            $con['href'] = '/faq/quest'.$con['id'].'.html';
            $faq[] = $con;
        }

    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('faq', $faq)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}