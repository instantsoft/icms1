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

function mod_respect($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    if (!isset($cfg['view_aw'])) { $cfg['view_aw'] = 0; }
    if (!isset($cfg['limit'])) { $cfg['limit'] = 5; }
    if (!isset($cfg['order'])) { $cfg['order'] = 'desc'; }
    if (!isset($cfg['show_awards'])) { $cfg['show_awards'] = 1; }

    if ($cfg['order']=='rand') { $order_sql = 'RAND()'; } else { $order_sql = 'awards_count DESC'; }
    if (!$cfg['view_aw']){ $view_sql = ''; } else { $view_sql = " AND a.title = '{$cfg['view_aw']}'"; }

    $sql = "SELECT u.id, u.nickname, u.login, COUNT(a.id) as awards_count, p.imageurl, u.is_deleted
            FROM cms_users u, cms_user_profiles p, cms_user_awards a
            WHERE a.user_id = u.id AND p.user_id = u.id AND u.is_deleted = 0 AND u.is_locked = 0 {$view_sql}
            GROUP BY a.user_id
            ORDER BY {$order_sql}
            LIMIT {$cfg['limit']}";

    $result = $inDB->query($sql) ;

    if (!$inDB->num_rows($result)){ return false; }

    while($user = $inDB->fetch_assoc($result)){

        $user['avatar'] = cmsUser::getUserAvatarUrl($user['id'], 'small', $user['imageurl'], $user['is_deleted']);

        if ($cfg['show_awards']){
            $user['awards'] = $inDB->get_table('cms_user_awards', 'user_id='.$user['id'], 'id, title');
        }

        $users[] = $user;
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('users', $users)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}