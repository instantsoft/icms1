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

function mod_random_image($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    $catsql = '';

    if ($cfg['album_id'] != 0) {
        if ($cfg['subs']) {
            $rootcat = $inDB->get_fields('cms_photo_albums', 'id='.$cfg['album_id'], 'NSLeft, NSRight');
            $catsql = " AND a.NSLeft >= {$rootcat['NSLeft']} AND a.NSRight <= {$rootcat['NSRight']}";
    } else {
            $catsql = "AND f.album_id = ". $cfg['album_id'];
        }
    }

    $sql = "SELECT f.*, a.title album_title
            FROM cms_photo_files f
            LEFT JOIN cms_photo_albums a ON a.id = f.album_id
            WHERE f.published = 1 ".$catsql."
            ORDER BY RAND()
            LIMIT 1
            ";

    $result = $inDB->query($sql) ;

    $is_img = false;

    if ($inDB->num_rows($result)){

        $is_img = true;

        $item=$inDB->fetch_assoc($result);

    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('item', $item)->
            assign('is_img', $is_img)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}