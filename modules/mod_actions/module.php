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

function mod_actions(array $mod, $cfg){

    $inDB      = cmsDatabase::getInstance();
    $inActions = cmsActions::getInstance();

    global $_LANG;

    if (!isset($cfg['show_target'])) { $cfg['show_target'] = 1; }
    if (!isset($cfg['limit'])) { $cfg['limit'] = 15; }
    if (!isset($cfg['show_link'])) { $cfg['show_link'] = 1; }
    if (!isset($cfg['action_types'])) { echo $_LANG['MODULE_NOT_CONFIGURED']; return true; }

    if (!$cfg['show_target']){ $inActions->showTargets(false); }

    $inActions->onlySelectedTypes($cfg['action_types']);
    $inDB->limitIs($cfg['limit']);

    $actions = $inActions->getActionsLog();
    if(!$actions){ return false; }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
        assign('actions', $actions)->
        assign('cfg', $cfg)->
        assign('user_id', cmsUser::getInstance()->id)->
        display($cfg['tpl']);

    return true;

}