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

function mod_user_rating($mod, $cfg){

    $inDB   = cmsDatabase::getInstance();
    cmsCore::loadModel('users');
    $model = new cms_model_users();

    if (!isset($cfg['count'])) { $cfg['count'] = 20; }
    if (!isset($cfg['view_type'])) { $cfg['view_type'] = 'rating'; }

    if(!in_array($cfg['view_type'], array('karma', 'rating'))) { $cfg['view_type'] = 'rating'; }

    $inDB->orderBy($cfg['view_type'], 'DESC');

    $inDB->limitPage(1, $cfg['count']);

    $users = $model->getUsers();

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('users', $users)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}