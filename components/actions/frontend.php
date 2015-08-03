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

function actions(){

    $inCore = cmsCore::getInstance();
    $inUser = cmsUser::getInstance();
	$inPage = cmsPage::getInstance();
	$inDB   = cmsDatabase::getInstance();
	$inActions = cmsActions::getInstance();

    $model = new cms_model_actions();

    global $_LANG;

    $do      = $inCore->do;
	$page    = cmsCore::request('page', 'int', 1);
	$user_id = cmsCore::request('user_id', 'int', 0);
	$perpage = 6;

    $pagetitle = $inCore->getComponentTitle();

    $inPage->setTitle($pagetitle);
    $inPage->addPathway($pagetitle, '/actions');
	$inPage->setDescription($model->config['meta_desc'] ? $model->config['meta_desc'] : $pagetitle);
    $inPage->setKeywords($model->config['meta_keys'] ? $model->config['meta_keys'] : $pagetitle);

//============================================================================//

    if ($do=='delete'){

        if (!$inUser->is_admin) { cmsCore::error404(); }

        $id = cmsCore::request('id', 'int', 0);
        if (!$id) { cmsCore::error404(); }

        $model->deleteAction($id);
        cmsCore::redirectBack();

    }

//============================================================================//

    if ($do=='view'){

        $inActions->showTargets($model->config['show_target']);

		if($model->config['act_type'] && !$model->config['is_all']){
        	$inActions->onlySelectedTypes($model->config['act_type']);
		}

		$total = $inActions->getCountActions();

        $inDB->limitPage($page, $model->config['perpage']);

        $actions = $inActions->getActionsLog();
		if(!$actions && $page > 1){ cmsCore::error404(); }

        cmsPage::initTemplate('components', 'com_actions_view')->
                assign('actions', $actions)->
                assign('pagetitle', $pagetitle)->
                assign('total', $total)->
                assign('user_id', $inUser->id)->
                assign('pagebar', cmsPage::getPagebar($total, $page, $model->config['perpage'], '/actions/page-%page%'))->
                display('com_actions_view.tpl');

    }

//============================================================================//

    if ($do=='view_user_feed'){

		if(!$inUser->id) { cmsCore::error404(); }

		if(!cmsCore::isAjax()) { cmsCore::error404(); }

		// Получаем друзей
		$friends = cmsUser::getFriends($inUser->id);

		$friends_total = count($friends);

		// нам нужно только определенное количество друзей
		$friends = array_slice($friends, ($page-1)*$perpage, $perpage, true);

		if($friends){

			$inActions->onlyMyFriends();

			$inActions->showTargets($model->config['show_target']);
			$inDB->limitIs($model->config['perpage_tab']);
        	$actions = $inActions->getActionsLog();

		} else {
            $actions = array();
		}

        cmsPage::initTemplate('components', 'com_actions_view_tab')->
                assign('actions', $actions)->
                assign('friends', $friends)->
                assign('user_id', $user_id)->
                assign('page', $page)->
                assign('cfg', $model->config)->
                assign('total_pages', ceil($friends_total / $perpage))->
                assign('friends_total', $friends_total)->
                display('com_actions_view_tab.tpl');

    }
//============================================================================//
    if ($do=='view_user_feed_only'){

		if(!$inUser->id) { cmsCore::error404(); }

		if(!cmsCore::isAjax()) { cmsCore::error404(); }

		if($user_id){
			if(!cmsUser::isFriend($user_id)) { cmsCore::error404(); }
			$inActions->whereUserIs($user_id);
		} else {
			$inActions->onlyMyFriends();
		}

		$inActions->showTargets($model->config['show_target']);
		$inDB->limitIs($model->config['perpage_tab']);
		$actions = $inActions->getActionsLog();
		// получаем последний элемент массива для выборки имя пользователя и ссылки на профиль.
        if($actions){
			$user = end($actions);
        } else {
            $user = cmsUser::getShortUserData($user_id);
        }

        cmsPage::initTemplate('components', 'com_actions_tab')->
                assign('actions', $actions)->
                assign('user_id', $user_id)->
                assign('user', $user)->
                assign('cfg', $model->config)->
                display('com_actions_tab.tpl');

    }
//============================================================================//
    if ($do=='view_user_friends_only'){

		if(!$inUser->id) { cmsCore::error404(); }

		if(!cmsCore::isAjax()) { cmsCore::error404(); }

		// Получаем друзей
		$friends = cmsUser::getFriends($inUser->id);

		$friends_total = count($friends);

		// нам нужно только определенное количество друзей
		$friends = array_slice($friends, ($page-1)*$perpage, $perpage, true);

        cmsPage::initTemplate('components', 'com_actions_friends')->
				assign('friends', $friends)->
				assign('page', $page)->
				assign('user_id', $user_id)->
				assign('total_pages', ceil($friends_total / $perpage))->
				assign('friends_total', $friends_total)->
                display('com_actions_friends.tpl');

    }

}