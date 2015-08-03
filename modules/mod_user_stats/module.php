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

function mod_user_stats($mod, $cfg){

    $inDB = cmsDatabase::getInstance();
    cmsCore::loadLanguage('components/users');

    global $_LANG;

    if (!isset($cfg['show_total'])) { $cfg['show_total'] = 1; }
    if (!isset($cfg['show_online'])) { $cfg['show_online'] = 1; }
    if (!isset($cfg['show_gender'])) { $cfg['show_gender'] = 1; }
    if (!isset($cfg['show_city'])) { $cfg['show_city'] = 1; }

    $total_usr = cmsUser::getCountAllUsers();

    if ($cfg['show_gender']){

        $gender_stats = array();
        //male
        $gender_stats['male'] = $inDB->rows_count('cms_users u INNER JOIN cms_user_profiles p ON p.user_id = u.id', "u.is_locked = 0 AND u.is_deleted = 0 AND p.gender = 'm'");
        //female
        $gender_stats['female'] = $inDB->rows_count('cms_users u INNER JOIN cms_user_profiles p ON p.user_id = u.id', "u.is_locked = 0 AND u.is_deleted = 0 AND p.gender = 'f'");
        //unknown
        $gender_stats['unknown'] = $total_usr - $gender_stats['male'] - $gender_stats['female'];

    }

    if ($cfg['show_city']){

        $sql = "SELECT IF (p.city != '', p.city, '{$_LANG['NOT_DECIDE']}') city, COUNT( p.user_id ) count
                FROM cms_users u
                LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE u.is_locked =0 AND u.is_deleted =0
                GROUP BY p.city";
        $rs = $inDB->query($sql);

        $city_stats = array();

        if ($inDB->num_rows($rs)){
            while($row = $inDB->fetch_assoc($rs)){
                if ($row['city'] != $_LANG['NOT_DECIDE']) { $row['href'] = '/users/city/'.urlencode($row['city']); } else { $row['href'] = ''; }
                $row['city'] = icms_ucfirst(mb_strtolower($row['city']));
                $city_stats[] = $row;
            }
        }

    }

    if ($cfg['show_online']){
        $people = cmsUser::getOnlineCount();
    }

    if ($cfg['show_bday']){
        $bday = cmsUser::getBirthdayUsers();
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('cfg', $cfg)->
            assign('total_usr', $total_usr)->
            assign('gender_stats', $gender_stats)->
            assign('city_stats', $city_stats)->
            assign('usr_online', cmsUser::sessionGet('usr_online'))->
            assign('people', $people)->
            assign('bday', $bday)->
            display($cfg['tpl']);

    return true;

}