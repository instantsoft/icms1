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

function mod_user_friend($mod, $cfg){

    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    if (!$inUser->id){ return false; }

    if ($cfg['view_type'] == 'table') {

        $sql = "SELECT
                CASE
                WHEN f.from_id = {$inUser->id}
                THEN f.to_id
                WHEN f.to_id = {$inUser->id}
                THEN f.from_id
                END AS user_id, u.login, u.nickname, u.is_deleted, p.imageurl
                FROM cms_user_friends f
                INNER JOIN cms_online o ON o.user_id = CASE WHEN f.from_id = {$inUser->id} THEN f.to_id WHEN f.to_id = {$inUser->id} THEN f.from_id END
                LEFT JOIN cms_users u ON u.id = o.user_id
                LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE (from_id = {$inUser->id} OR to_id = {$inUser->id}) AND is_accepted =1 LIMIT ".$cfg['limit'];
    } else {

        $sql = "SELECT
                CASE
                WHEN f.from_id = {$inUser->id}
                THEN f.to_id
                WHEN f.to_id = {$inUser->id}
                THEN f.from_id
                END AS user_id, u.login, u.nickname
                FROM cms_user_friends f
                INNER JOIN cms_online o ON o.user_id = CASE WHEN f.from_id = {$inUser->id} THEN f.to_id WHEN f.to_id = {$inUser->id} THEN f.from_id END
                LEFT JOIN cms_users u ON u.id = o.user_id
                WHERE (from_id = {$inUser->id} OR to_id = {$inUser->id}) AND is_accepted =1 LIMIT ".$cfg['limit'];

    }

    $result = $inDB->query($sql) ;
    $total	= $inDB->num_rows($result);

    if ($total){
        $friends = array();
        while($friend = $inDB->fetch_assoc($result)){
            $friend['avatar'] = ($cfg['view_type'] == 'table') ? cmsUser::getUserAvatarUrl($friend['user_id'], 'small', $friend['imageurl'], $friend['is_deleted']) : false;
            $friend['user_link'] = cmsUser::getProfileLink($friend['login'], $friend['nickname']);
            $friends[$friend['user_id']] = $friend;
        }
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('friends', $friends)->
            assign('total', $total)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}