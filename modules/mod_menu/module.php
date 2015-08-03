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

function mod_menu($mod, $cfg){

    $inCore      = cmsCore::getInstance();
    $inUser      = cmsUser::getInstance();
    $menuid      = $inCore->menuId();
    $full_menu   = $inCore->getMenuStruct();
    $current_uri = '/'.$inCore->getUri();

    if (!isset($cfg['menu'])) { $menu = 'mainmenu'; } else { $menu = $cfg['menu']; }
    if (!isset($cfg['show_home'])) { $cfg['show_home'] = 1; }
    if (!isset($cfg['is_sub_menu'])) { $cfg['is_sub_menu'] = 0; }

    // текущий пункт меню
    $currentmenu = isset($full_menu[$menuid]) ? $full_menu[$menuid] : array();

    // результирующий массив меню
    $items = array();

    // id корня меню если обычный вывод меню, $menuid если режим подменю
    if($cfg['is_sub_menu']){

        // в подменю не должно быть ссылки на главную
        $cfg['show_home'] = 0;
        // на главной или нет активного пункта меню
        if($menuid == 1 || !$currentmenu){
            return false;
        }
        foreach ($full_menu as $item) {
            if($item['NSLeft'] > $currentmenu['NSLeft'] &&
                    $item['NSRight'] < $currentmenu['NSRight'] &&
                    in_array($menu, $item['menu']) &&
                    ($item['is_lax'] || cmsCore::checkContentAccess($item['access_list'], false)) && $item['published']){
                $item['link']  = cmsUser::stringReplaceUserProperties($item['link']);
                $item['title'] = cmsUser::stringReplaceUserProperties($item['title'], true);
                $items[] = $item;
                // массивы для сортировки
                $nsl[] = $item['NSLeft'];
                $ord[] = $item['ordering'];
            }
        }

    } else {

        foreach ($full_menu as $item) {
            if(in_array($menu, $item['menu']) &&
                    ($item['is_lax'] || cmsCore::checkContentAccess($item['access_list'], false)) && $item['published']){
                $item['link']  = cmsUser::stringReplaceUserProperties($item['link']);
                $item['title'] = cmsUser::stringReplaceUserProperties($item['title'], true);
                $items[] = $item;
                // массивы для сортировки
                $nsl[] = $item['NSLeft'];
                $ord[] = $item['ordering'];
            }
        }
    }

    if(!$items) { return false; }

    // сортируем массив
    array_multisort($nsl, SORT_ASC, $ord, SORT_ASC, $items);

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('menuid', $menuid)->
            assign('currentmenu', $currentmenu)->
            assign('current_uri', $current_uri)->
            assign('menu', $menu)->
            assign('items', $items)->
            assign('last_level', 0)->
            assign('user_id', $inUser->id)->
            assign('is_admin', $inUser->is_admin)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}