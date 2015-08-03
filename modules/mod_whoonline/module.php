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

function mod_whoonline($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    if (!isset($cfg['color_admin'])) { $cfg['color_admin'] = '#FF0000'; }
    if (!isset($cfg['color_editor'])) { $cfg['color_editor'] = '#009900'; }

    $online_count = cmsUser::getOnlineCount();

    $users       = array();
    $today_users = array();

    if($online_count['users']){

        $sql = "SELECT
                o.user_id as id,
                u.login,
                u.nickname,
                p.gender as gender
                FROM cms_online o
                LEFT JOIN cms_users u ON  u.id = o.user_id
                LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE u.is_locked = 0 AND u.is_deleted = 0
                GROUP BY o.user_id";

        $result = $inDB->query($sql);
        $users  = getUsersArray($result, $cfg, $inDB);

    }

    if($cfg['show_today']){

        $today = date("Y-m-d");
        $sql = "SELECT u.id as id, u.nickname as nickname, u.login as login, p.gender as gender
                FROM cms_users u
                LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE u.is_locked = 0 AND u.is_deleted = 0 AND DATE_FORMAT(u.logdate, '%Y-%m-%d')='$today'
                ORDER BY u.logdate DESC";
        $result = $inDB->query($sql);
        if ($inDB->num_rows($result)){
            $today_users = getUsersArray($result, $cfg, $inDB);
        }

    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('cfg', $cfg)->
            assign('users', $users)->
            assign('guests', $online_count['guests'])->
            assign('today_users', $today_users)->
            display($cfg['tpl']);

    return true;

}

function getUsersArray($result, $cfg, $inDB) {
    $users = array();
    while($usr = $inDB->fetch_assoc($result)){
        if($cfg['admin_editor']){
            if (cmsUser::userIsAdmin($usr['id'])){

                $usr['genderlink'] = cmsUser::getGenderLink($usr['id'], $usr['nickname'], $usr['gender'], $usr['login'], "color:{$cfg['color_admin']}");
            } elseif (cmsUser::userIsEditor($usr['id'])) {
                $usr['genderlink'] = cmsUser::getGenderLink($usr['id'], $usr['nickname'], $usr['gender'], $usr['login'], "color:{$cfg['color_editor']}");
            } else {
                $usr['genderlink'] = cmsUser::getGenderLink($usr['id'], $usr['nickname'], $usr['gender'], $usr['login']);
            }
        } else {
            $usr['genderlink'] = cmsUser::getGenderLink($usr['id'], $usr['nickname'], $usr['gender'], $usr['login']);
        }
        $users[] = $usr['genderlink'];
    }
    return $users;
}