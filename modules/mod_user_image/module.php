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

function mod_user_image($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    $sql = "SELECT u.id uid, u.nickname author, u.login as login, p.imageurl, p.title, p.id, pr.gender gender
            FROM cms_user_photos p
            LEFT JOIN cms_users u ON u.id = p.user_id
            LEFT JOIN cms_user_profiles pr ON pr.user_id = u.id
            LEFT JOIN cms_user_albums a ON a.id = p.album_id
            WHERE p.allow_who = 'all' AND u.is_deleted = 0 AND u.is_locked = 0
                  AND p.album_id > 0 AND a.allow_who = 'all'
            ORDER BY RAND()
            LIMIT 1";

    $result = $inDB->query($sql) ;

    if (!$inDB->num_rows($result)){ return false; }

    while ($usr = $inDB->fetch_assoc($result)){

        $usr['genderlink'] = cmsUser::getGenderLink($usr['uid'], $usr['author'], $usr['gender'], $usr['login']);

        $users[] = $usr;

    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('users', $users)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}