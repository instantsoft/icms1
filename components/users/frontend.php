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
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function users(){

    header('X-Frame-Options: DENY');

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    global $_LANG;

    $model = new cms_model_users();

	// id пользователя
	$id = cmsCore::request('id', 'int', 0);
	// логин пользователя
	$login = cmsCore::strClear(urldecode(cmsCore::request('login', 'html', '')));

	$do   = $inCore->do;
	$page = cmsCore::request('page', 'int', 1);

	$pagetitle = $inCore->getComponentTitle();

	$inPage->addPathway($pagetitle, '/users');
	$inPage->setTitle($pagetitle);
	$inPage->setDescription($pagetitle);

	// js только авторизованным
	if($inUser->id){
		$inPage->addHeadJS('components/users/js/profile.js');
        $inPage->addHeadJsLang(array('CONFIRM_CLEAN_CAT','CHOOSE_RECIPIENT','SEND_TO_USER','FRIENDSHIP_OFFER','STOP_FRIENDLY','REALY_STOP_FRIENDLY','ENTER_STATUS','HAVE_JUST'));
	}

//============================================================================//
//========================= Список пользователей  ============================//
//============================================================================//
if ($do == 'view'){

    // если запрещен просмотр всех пользователей, 404
    if($model->config['sw_search'] == 2){
        cmsCore::error404();
    }

    //очищаем поисковые запросы если пришли со другой страницы
    if(!strstr(cmsCore::getBackURL(), '/users')){
        cmsUser::sessionClearAll();
    }

	$stext = array();

	// Возможные входные переменные
	$name    = cmsCore::getSearchVar('name');
	$city    = cmsCore::getSearchVar('city');
	$hobby   = cmsCore::getSearchVar('hobby');
	$gender  = cmsCore::getSearchVar('gender');
	$orderby = cmsCore::request('orderby', array('karma', 'rating', 'regdate'), 'regdate');
	$orderto = cmsCore::request('orderto', array('asc', 'desc'), 'desc');
	$age_to  = (int)cmsCore::getSearchVar('ageto', 'all');
	$age_fr  = (int)cmsCore::getSearchVar('agefrom', 'all');
	$group_id = cmsCore::request('group_id', 'int', 0);

	// Флаг о показе только онлайн пользователей
	if (cmsCore::inRequest('online')) {
		cmsUser::sessionPut('usr_online', (bool)cmsCore::request('online', 'int'));
		$page = 1;
	}
	$only_online = cmsUser::sessionGet('usr_online');

	if($only_online){
		$stext[] = $_LANG['SHOWING_ONLY_ONLINE'];
	}

	///////////////////////////////////////
	//////////Условия выборки//////////////
	///////////////////////////////////////

	// группа
	if($group_id){
		$model->whereUserGroupIs($group_id);
        $link['group'] = '/users/group/'.$group_id;
        $_LANG['GROUP_SEARCH_NAME'] = cmsUser::getGroupTitle($group_id);
	}

	// Добавляем в выборку имя, если оно есть
	if($name){
		$model->whereNameIs($name);
		$stext[] = $_LANG['NAME']." &mdash; ".htmlspecialchars(stripslashes($name));
	}

	// Добавляем в выборку город, если он есть
	if($city){
		$model->whereCityIs($city);
		$stext[] = $_LANG['CITY']." &mdash; ".htmlspecialchars(stripslashes($city));
	}

	// Добавляем в выборку хобби, если есть
	if($hobby){
		$model->whereHobbyIs($hobby);
		$stext[] = $_LANG['HOBBY']." &mdash; ".htmlspecialchars(stripslashes($hobby));
	}
	// Добавляем в выборку пол, если есть
	if($gender){
		$model->whereGenderIs($gender);
		if($gender == 'm'){
			$stext[] = $_LANG['MALE'];
		} else {
			$stext[] = $_LANG['FEMALE'];
		}
	}
	// Добавляем в выборку возраст, более
	if($age_fr){
		$model->whereAgeFrom($age_fr);
		$stext[] = $_LANG['NOT_YOUNG']." $age_fr ".$_LANG['YEARS'];
	}
	// Добавляем в выборку возраст, менее
	if($age_to){
		$model->whereAgeTo($age_to);
		$stext[] = $_LANG['NOT_OLD']." $age_fr ".$_LANG['YEARS'];
	}

	// Считаем общее количество согласно выборки
	$total = $model->getUsersCount($only_online);

	if($total){

		//устанавливаем сортировку
		$inDB->orderBy($orderby, $orderto);

		//устанавливаем номер текущей страницы и кол-во пользователей на странице
		$inDB->limitPage($page, $model->config['users_perpage']);

		// Загружаем пользователей согласно выборки
		$users = $model->getUsers($only_online);

	} else {
        $inDB->resetConditions();
    }

	$link['latest']   = '/users';
	$link['positive'] = '/users/positive.html';
	$link['rating']   = '/users/rating.html';

	if($orderby=='regdate') { $link['selected'] = 'latest'; }
	if($orderby=='karma') { $link['selected'] = 'positive'; }
	if($orderby=='rating') { $link['selected'] = 'rating'; }
    $pagebar_link = '/users/'.$link['selected'].'%page%.html';
    if($group_id){
        $link['selected'] = 'group';
        $pagebar_link = '/users/'.$link['selected'].'/'.$group_id.'-%page%';
    }

	cmsPage::initTemplate('components', 'com_users_view')->
            assign('stext', $stext)->
            assign('orderby', $orderby)->
            assign('orderto', $orderto)->
            assign('users', $users)->
            assign('total', $total)->
            assign('only_online', $only_online)->
            assign('gender', $gender)->
            assign('name', stripslashes($name))->
            assign('city', stripslashes($city))->
            assign('hobby', stripslashes($hobby))->
            assign('age_to', $age_to)->
            assign('age_fr', $age_fr)->
            assign('cfg', $model->config)->
            assign('link', $link)->
            assign('pagebar', cmsPage::getPagebar($total, $page, $model->config['users_perpage'], $pagebar_link))->
            display('com_users_view.tpl');

}

//============================================================================//
//======================= Редактирование профиля  ============================//
//============================================================================//
if ($do=='editprofile'){

	// неавторизованным, не владельцам и не админам тут делать нечего
	if (!$inUser->id || ($inUser->id != $id && !$inUser->is_admin)){ cmsCore::error404(); }

    $usr = $model->getUser($id);
    if (!$usr){ cmsCore::error404(); }

	$opt = cmsCore::request('opt', 'str', 'edit');

    // главного админа может редактировать только он сам
    if($id == 1 && $inUser->id != $id){
        cmsCore::error404();
    }

	// показываем форму
	if ($opt == 'edit'){

		$inPage->setTitle($_LANG['CONFIG_PROFILE'].' - '.$usr['nickname']);
		$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
		$inPage->addPathway($_LANG['CONFIG_PROFILE']);

		$private_forms = array();
		if(isset($model->config['privforms'])){
			if (is_array($model->config['privforms'])){
				foreach($model->config['privforms'] as $form_id){
					$private_forms = array_merge($private_forms, cmsForm::getFieldsHtml($form_id, $usr['formsdata']));
				}
			}
		}

		cmsPage::initTemplate('components', 'com_users_edit_profile')->
                assign('opt', $opt)->
                assign('usr', $usr)->
                assign('private_forms', $private_forms)->
                assign('cfg_forum', $inCore->loadComponentConfig('forum'))->
                assign('cfg', $model->config)->
                display('com_users_edit_profile.tpl');
		return;

	}

	// Если сохраняем профиль
	if ($opt == 'save'){

        if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$errors = false;

		$users['nickname'] = cmsCore::request('nickname', 'str');
		if (mb_strlen($users['nickname'])<2) { cmsCore::addSessionMessage($_LANG['SHORT_NICKNAME'], 'error'); $errors = true; }
		cmsCore::loadModel('registration');
		$modreg = new cms_model_registration();
		if (!$inUser->is_admin){
			if($modreg->getBadNickname($users['nickname'])) { cmsCore::addSessionMessage($_LANG['ERR_NICK_EXISTS'], 'error'); $errors = true; }
		}

		$profiles['gender'] = cmsCore::request('gender', 'str');
		$profiles['city']   = cmsCore::request('city', 'str');
		if (mb_strlen($profiles['city'])>50) { cmsCore::addSessionMessage($_LANG['LONG_CITY_NAME'], 'error'); $errors = true; }

		$users['email'] = cmsCore::request('email', 'email');
		if (!$users['email']) { cmsCore::addSessionMessage($_LANG['REALY_ADRESS_EMAIL'], 'error'); $errors = true; }
		if($usr['email'] != $users['email']){
			$is_set_email = $inDB->get_field('cms_users', "email='{$users['email']}'", 'id');
			if ($is_set_email) {
                cmsCore::addSessionMessage($_LANG['ADRESS_EMAIL_IS_BUSY'], 'error'); $errors = true;
            } else {
                // формируем токен
                $token = md5($usr['email'].uniqid().microtime());
                $inDB->insert('cms_users_activate', array('user_id'=>$inUser->id, 'pubdate'=>date("Y-m-d H:i:s"), 'code'=>$token));
                $codelink = HOST.'/users/change_email/'.$token.'/'.$users['email'];
                // по старому адресу высылаем письмо с подтверждением
                $letter = cmsCore::getLanguageTextFile('change_email');
                $letter = str_replace(array('{nickname}','{codelink}'), array($inUser->nickname, $codelink), $letter);
                cmsCore::mailText($usr['email'], '', $letter);
                cmsCore::addSessionMessage(sprintf($_LANG['YOU_CHANGE_EMAIL'], $usr['email']), 'info');
                // email не меняем
                $users['email'] = $usr['email'];
            }
		}

		$profiles['showphone']    = cmsCore::request('showphone', 'int', 0);
		$profiles['showmail']     = cmsCore::request('showmail', 'int');
		$profiles['email_newmsg'] = cmsCore::request('email_newmsg', 'int');
		$profiles['showbirth']    = cmsCore::request('showbirth', 'int');
		$profiles['description']  = cmsCore::request('description', 'str', '');
		$users['birthdate']    = (int)$_REQUEST['birthdate']['year'].'-'.(int)$_REQUEST['birthdate']['month'].'-'.(int)$_REQUEST['birthdate']['day'];
        $profiles['signature']      = $inDB->escape_string(cmsCore::badTagClear(cmsCore::request('signature', 'html', '')));
        $profiles['signature_html'] = $inDB->escape_string(cmsCore::parseSmiles(cmsCore::request('signature', 'html', ''), true));
		$profiles['allow_who']    = cmsCore::request('allow_who', 'str');
		if (!preg_match('/^([a-zA-Z]+)$/ui', $profiles['allow_who'])) { $errors = true; }
		$users['icq']             = cmsCore::request('icq', 'str', '');
		$profiles['showicq']      = cmsCore::request('showicq', 'int');
		$profiles['cm_subscribe'] = cmsCore::request('cm_subscribe', 'str');
		if (!preg_match('/^([a-zA-Z]+)$/ui', $profiles['cm_subscribe'])) { $errors = true; }
        $users['phone']           = cmsCore::request('phone', 'int', 0);

		// получаем данные форм
		$profiles['formsdata'] = '';
		if(isset($model->config['privforms'])){
			if (is_array($model->config['privforms'])){
				foreach($model->config['privforms'] as $form_id){
					$form_input  = cmsForm::getFieldsInputValues($form_id);
					$profiles['formsdata'] .= $inDB->escape_string(cmsCore::arrayToYaml($form_input['values']));
					// Проверяем значения формы
					foreach ($form_input['errors'] as $field_error) {
						if($field_error){ cmsCore::addSessionMessage($field_error, 'error'); $errors = true; }
					}
				}
			}
		}

		if($errors) { cmsCore::redirectBack(); }

		$inDB->update('cms_user_profiles', cmsCore::callEvent('UPDATE_USER_PROFILES', array_merge(array('id'=>$usr['pid'], 'user_id'=>$usr['id']), $profiles)), $usr['pid']);
		$inDB->update('cms_users', cmsCore::callEvent('UPDATE_USER_USERS', array_merge(array('id'=>$usr['id']), $users)), $usr['id']);

		cmsCore::addSessionMessage($_LANG['PROFILE_SAVED'], 'info');
		cmsCore::redirect(cmsUser::getProfileURL($usr['login']));

	}

	if ($opt == 'changepass'){

		$errors = false;

		$oldpass  = cmsCore::request('oldpass', 'str');
		$newpass  = cmsCore::request('newpass', 'str');
		$newpass2 = cmsCore::request('newpass2', 'str');

		if ($inUser->password != md5($oldpass)) { cmsCore::addSessionMessage($_LANG['OLD_PASS_WRONG'], 'error'); $errors = true;}
		if ($newpass != $newpass2) { cmsCore::addSessionMessage($_LANG['WRONG_PASS'], 'error'); $errors = true; }
		if($oldpass && $newpass && $newpass2 && mb_strlen($newpass )<6) { cmsCore::addSessionMessage($_LANG['PASS_SHORT'], 'error'); $errors = true; }

		if($errors) { cmsCore::redirectBack(); }

        cmsCore::callEvent('UPDATE_USER_PASSWORD', array('user_id'=>$usr['id'], 'oldpass'=>$oldpass, 'newpass'=>$newpass));

		$sql = "UPDATE cms_users SET password='".md5($newpass)."' WHERE id = '$id' AND password='".md5($oldpass)."'";
		$inDB->query($sql);
		cmsCore::addSessionMessage($_LANG['PASS_CHANGED'], 'info');
		cmsCore::redirect(cmsUser::getProfileURL($inUser->login));

	}

}

//============================================================================//
//============================= Просмотр профиля  ============================//
//============================================================================//
if ($do=='profile'){

    $inPage->addHeadJsLang(array('NEW_POST_ON_WALL','CONFIRM_DEL_POST_ON_WALL'));

	// если просмотр профиля гостям запрещен
	if (!$inUser->id && !$model->config['sw_guest']) {
        cmsUser::goToLogin();
	}

    if(is_numeric($login)) { cmsCore::error404(); }

    $usr = $model->getUser($login);
    if (!$usr){ cmsCore::error404(); }

    $myprofile  = ($inUser->id == $usr['id']);

    $inPage->setTitle($usr['nickname']);
    $inPage->addPathway($usr['nickname']);

	// просмотр профиля запрещен
    if (!cmsUser::checkUserContentAccess($usr['allow_who'], $usr['id'])){
        cmsPage::initTemplate('components', 'com_users_not_allow')->
                assign('is_auth', $inUser->id)->
                assign('usr', $usr)->
                display('com_users_not_allow.tpl');
        return;
    }
	// Профиль удален
    if ($usr['is_deleted']){
        cmsPage::initTemplate('components', 'com_users_deleted.tpl')->
                assign('usr', $usr)->
                assign('is_admin', $inUser->is_admin)->
                assign('others_active', $inDB->rows_count('cms_users', "login='{$usr['login']}' AND is_deleted=0", 1))->
                display('com_users_deleted.tpl');
        return;
    }

	// Данные о друзьях
	$usr['friends_total'] = cmsUser::getFriendsCount($usr['id']);
	$usr['friends']		  = cmsUser::getFriends($usr['id']);
	// очищать сессию друзей если в своем профиле и количество друзей из базы не совпадает с количеством друзей в сессии
	if ($myprofile && sizeof($usr['friends']) != $usr['friends_total']) { cmsUser::clearSessionFriends(); }
	// обрезаем список
	$usr['friends'] = array_slice($usr['friends'], 0, 6);
	// выясняем друзья ли мы с текущим пользователем
    $usr['isfriend'] = !$myprofile ? cmsUser::isFriend($usr['id']) : false;

	// награды пользователя
    $usr['awards'] = $model->config['sw_awards'] ? $model->getUserAwards($usr['id']) : false;

	// стена
	if($model->config['sw_wall']){
		$inDB->limitPage(1, $model->config['wall_perpage']);
        $usr['wall_html'] = cmsUser::getUserWall($usr['id'], 'users', $myprofile, $inUser->is_admin);
	}

	// можно ли пользователю изменять карму
    $usr['can_change_karma'] = $model->isUserCanChangeKarma($usr['id']) && $inUser->id;

	// Фотоальбомы пользователя
    if ($model->config['sw_photo']){
        $usr['albums']       = $model->getPhotoAlbums($usr['id'], $usr['isfriend'], !$inCore->isComponentEnable('photos'));
        $usr['albums_total'] = sizeof($usr['albums']);
        $usr['albums_show']  = 6;
        if ($usr['albums_total']>$usr['albums_show']){
            array_splice($usr['albums'], $usr['albums_show']);
        }
    }

    $usr['board_count']    = $model->config['sw_board'] ?
								$inDB->rows_count('cms_board_items', "user_id='{$usr['id']}' AND published=1") : 0;
    $usr['comments_count'] = $model->config['sw_comm'] ?
								$inDB->rows_count('cms_comments', "user_id='{$usr['id']}' AND published=1") : 0;
	$usr['forum_count']    = $model->config['sw_forum'] ?
								$inDB->rows_count('cms_forum_posts', "user_id = '{$usr['id']}'") : 0;
	$usr['files_count']    = $model->config['sw_files'] ?
								$inDB->rows_count('cms_user_files', "user_id = '{$usr['id']}'") : 0;

	$cfg_reg = $inCore->loadComponentConfig('registration');
	$usr['invites_count'] = ($inUser->id && $myprofile && $cfg_reg['reg_type'] == 'invite') ?
								$model->getUserInvitesCount($inUser->id) : 0;

	$usr['blog'] = $model->config['sw_blogs'] ?
								$inDB->get_fields('cms_blogs', "user_id = '{$usr['id']}' AND owner = 'user'", 'title, seolink') : false;

    $usr['form_fields'] = array();
	if (is_array($model->config['privforms'])){
		foreach($model->config['privforms'] as $form_id){
			$usr['form_fields'] = array_merge($usr['form_fields'], cmsForm::getFieldsValues($form_id, $usr['formsdata']));
		}
	}

    if($usr['city']){
        cmsCore::loadModel('geo');
        $geo = new cms_model_geo();
        $city_parents = $geo->getCityParents($usr['city']);
        if($city_parents){
            $usr['country'] = $city_parents['country_name'];
        }
    }

    $plugins = $model->getPluginsOutput($usr);

    cmsPage::initTemplate('components', 'com_users_profile.tpl')->
            assign('usr', $usr)->
            assign('plugins', $plugins)->
            assign('cfg', $model->config)->
            assign('myprofile', $myprofile)->
            assign('cfg_forum', $inCore->loadComponentConfig('forum'))->
            assign('is_admin', $inUser->is_admin)->
            assign('is_auth', $inUser->id)->
            display('com_users_profile.tpl');

}

//============================================================================//
//============================= Список сообщений  ============================//
//============================================================================//
if ($do=='messages'){

	if (!$model->config['sw_msg']) { cmsCore::error404(); }

	if (!$inUser->id || ($inUser->id != $id && !$inUser->is_admin)){ cmsUser::goToLogin(); }

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

	$inPage->setTitle($_LANG['MY_MESS']);
	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['MY_MESS'], '/users/'.$id.'/messages.html');

	include 'components/users/messages.php';

}

//============================================================================//
//=========================== Отправка сообщения  ============================//
//============================================================================//
if ($do=='sendmessage'){

	if (!$model->config['sw_msg']) { cmsCore::halt(); }

    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id || ($inUser->id==$id &&
							!cmsCore::inRequest('massmail') &&
							!cmsCore::request('send_to_group', 'int', 0))){ cmsCore::halt(); }

	if(!cmsCore::inRequest('gosend')){

		$replyid = cmsCore::request('replyid', 'int', 0);

		if ($replyid){

			$msg = $model->getReplyMessage($replyid, $inUser->id);
			if(!$msg) { cmsCore::halt(); }

		}

        $inPage->setRequestIsAjax();

		cmsPage::initTemplate('components', 'com_users_messages_add')->
                assign('msg', isset($msg) ? $msg : array())->
                assign('is_reply_user', $replyid)->
                assign('id', $id)->
                assign('bbcodetoolbar', cmsPage::getBBCodeToolbar('message'))->
                assign('smilestoolbar', cmsPage::getSmilesPanel('message'))->
                assign('groups', $inUser->is_admin ? cmsUser::getGroups(true) : array())->
                assign('friends', cmsUser::getFriends($inUser->id))->
                assign('id_admin', $inUser->is_admin)->
                display('com_users_messages_add.tpl');

		cmsCore::jsonOutput(array('error' => false,
								  'html'  => ob_get_clean()
								));

	}

	if(cmsCore::inRequest('gosend')){

        // Кому отправляем
        $usr = cmsUser::getShortUserData($id);
        if (!$usr) { cmsCore::halt(); }

		$message = cmsCore::parseSmiles(cmsCore::request('message', 'html', ''), true);

		if (mb_strlen($message)<2){
			cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_SEND_MESS']));
		}

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $output = cmsCore::callEvent('USER_SEND_MESSEDGE', array('text'=>$message, 'to_id'=>$id));

        $message = $output['text'];
        $id      = $output['to_id'];

		$send_to_group = cmsCore::request('send_to_group', 'int', 0);
		$group_id      = cmsCore::request('group_id', 'int', 0);

		//
		// Обычная отправка (1 получатель)
		//
		if (!cmsCore::inRequest('massmail') && !$send_to_group){

			//отправляем сообщение
			$msg_id = cmsUser::sendMessage($inUser->id, $id, $message);
			// отправляем уведомление на email если нужно
			$model->sendNotificationByEmail($id, $inUser->id, $msg_id);

			cmsCore::jsonOutput(array('error' => false, 'text' => $_LANG['SEND_MESS_OK']));

		}

		//
		// далее идут массовые рассылки, доступные только админам
		//
		if (!$inUser->is_admin){ cmsCore::halt(); }

		// отправить всем: получаем список всех пользователей
		if (cmsCore::inRequest('massmail')) {

			$userlist = cmsUser::getAllUsers();
			// проверяем что есть кому отправлять
			if (!$userlist){
				cmsCore::jsonOutput(array('error' => false, 'text' => $_LANG['ERR_SEND_MESS']));
			}
			$count = array();
			// отправляем всем по списку
			foreach ($userlist as $usr){
				$count[] = cmsUser::sendMessage(USER_MASSMAIL, $usr['id'], $message);
			}

			cmsCore::jsonOutput(array('error' => false, 'text' => sprintf($_LANG['SEND_MESS_ALL_OK'], sizeof($count))));

		}

		// отправить группе: получаем список членов группы
		if ($send_to_group) {

			$count = cmsUser::sendMessageToGroup(USER_MASSMAIL, $group_id, $message);
			$success_msg = sprintf($_LANG['SEND_MESS_GROUP_OK'], $count, cmsUser::getGroupTitle($group_id));

			cmsCore::jsonOutput(array('error' => false, 'text' => $success_msg));

		}

	}

}
//============================================================================//
//============================= Удаление сообщения  ==========================//
//============================================================================//
if ($do=='delmessage'){

    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

    if (!$model->config['sw_msg']) { cmsCore::halt(); }

    if (!$inUser->id) { cmsCore::halt(); }

    $msg = $inDB->get_fields('cms_user_msg', "id='$id'", '*');
    if (!$msg){ cmsCore::halt(); }

    $can_delete = ($inUser->id == $msg['to_id'] || $inUser->id == $msg['from_id']) ? true : false;
    if(!$can_delete && !$inUser->is_admin){ cmsCore::halt(); }

    // Сообщения с from_id < 0
    if ($msg['from_id'] < 0){
        $inDB->query("DELETE FROM cms_user_msg WHERE id = '$id' LIMIT 1");
        $info_text = $_LANG['MESS_NOTICE_DEL_OK'];
    }
    // мне сообщение от пользователя
    if ($msg['to_id']==$inUser->id && $msg['from_id'] > 0){
        $inDB->query("UPDATE cms_user_msg SET to_del=1 WHERE id='{$id}'");
        $info_text = $_LANG['MESS_DEL_OK'];
    }
    // от меня сообщение
    if ($msg['from_id']==$inUser->id && !$msg['is_new']){
        $inDB->query("UPDATE cms_user_msg SET from_del=1 WHERE id='{$id}'");
        $info_text = $_LANG['MESS_DEL_OK'];
    }
    // отзываем сообщение
    if ($msg['from_id']==$inUser->id && $msg['is_new']){
        $inDB->query("DELETE FROM cms_user_msg WHERE id = '$id' LIMIT 1");
        $info_text = $_LANG['MESS_BACK_OK'];
    }
    // удаляем сообщения, которые удалены с двух сторон
    $inDB->query("DELETE FROM cms_user_msg WHERE to_del=1 AND from_del=1");

    cmsCore::jsonOutput(array('error' => false, 'text' => $info_text));

}
//============================================================================//
//=========================== Удаление сообщений  ============================//
//============================================================================//
if ($do=='delmessages'){

	if (!$model->config['sw_msg']) { cmsCore::error404(); }

    if ($inUser->id != $id && !$inUser->is_admin){ cmsCore::error404(); }

    $usr = cmsUser::getShortUserData($id);
    if (!$usr) { cmsCore::error404(); }

    $opt = cmsCore::request('opt', 'str', 'in');

    if($opt == 'notices'){

        $inDB->query("DELETE FROM cms_user_msg WHERE to_id = '{$id}' AND from_id < 0");

    } else {

        $del_flag = $opt=='in' ? 'to_del' : 'from_del';
        $id_flag  = $opt=='in' ? 'to_id' : 'from_id';

        $inDB->query("UPDATE cms_user_msg SET {$del_flag}=1 WHERE {$id_flag}='{$id}'");
        $inDB->query("DELETE FROM cms_user_msg WHERE to_del=1 AND from_del=1");

    }

    cmsCore::addSessionMessage($_LANG['MESS_ALL_DEL_OK'], 'info');

	cmsCore::redirectBack();

}
//============================================================================//
//============================= Загрузка аватара  ============================//
//============================================================================//
if ($do=='avatar'){

	if (!$inUser->id || ($inUser->id && $inUser->id != $id)){ cmsCore::error404(); }

	$inPage->setTitle($_LANG['LOAD_AVATAR']);
	$inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
	$inPage->addPathway($_LANG['LOAD_AVATAR']);

	if (cmsCore::inRequest('upload')) {

		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/';
		$inUploadPhoto->dir_medium    = 'users/avatars/';
		$inUploadPhoto->dir_small     = 'users/avatars/small/';
		$inUploadPhoto->small_size_w  = $model->config['smallw'];
		$inUploadPhoto->medium_size_w = $model->config['medw'];
		$inUploadPhoto->medium_size_h = $model->config['medh'];
		$inUploadPhoto->is_watermark  = false;
		$inUploadPhoto->input_name    = 'picture';

		$file = $inUploadPhoto->uploadPhoto($inUser->orig_imageurl);

		if(!$file){

			cmsCore::addSessionMessage('<strong>'.$_LANG['ERROR'].':</strong> '.cmsCore::uploadError().'!', 'error');
			cmsCore::redirect('/users/'.$id.'/avatar.html');

		}

		$sql = "UPDATE cms_user_profiles SET imageurl = '{$file['filename']}' WHERE user_id = '$id' LIMIT 1";
		$inDB->query($sql);
		// очищаем предыдущую запись о смене аватара
		cmsActions::removeObjectLog('add_avatar', $id);
		// выводим сообщение в ленту
		cmsActions::log('add_avatar', array(
			  'object' => '',
			  'object_url' => '',
			  'object_id' => $id,
			  'target' => '',
			  'target_url' => '',
			  'description' => '<a href="'.cmsUser::getProfileURL($inUser->login).'" class="act_usr_ava">
								   <img border="0" src="/images/users/avatars/small/'.$file['filename'].'">
								</a>'
		));

		cmsCore::redirect(cmsUser::getProfileURL($inUser->login));

	} else {

		cmsPage::initTemplate('components', 'com_users_avatar_upload')->
                assign('id', $id)->
                display('com_users_avatar_upload.tpl');

	}
}
//============================================================================//
//============================= Библиотека аватаров  =========================//
//============================================================================//
if ($do=='select_avatar'){

	if (!$inUser->id || ($inUser->id && $inUser->id != $id)){ cmsCore::error404(); }

	$avatars_dir     = PATH."/images/users/avatars/library";
	$avatars_dir_rel = "/images/users/avatars/library";

	$avatars_dir_handle = opendir($avatars_dir);
	$avatars            = array();

	while ($nextfile = readdir($avatars_dir_handle)){
		if(($nextfile!='.')&&($nextfile!='..')&&( mb_strstr($nextfile, '.gif') || mb_strstr($nextfile, '.jpg') || mb_strstr($nextfile, '.jpeg') || mb_strstr($nextfile, '.png')  ) ){
			$avatars[] = $nextfile;
		}
	}

	closedir($avatars_dir_handle);

	if (!cmsCore::inRequest('set_avatar')){

		$inPage->setTitle($_LANG['SELECT_AVATAR']);
		$inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
		$inPage->addPathway($_LANG['SELECT_AVATAR']);

		$perpage = 20;

		$total   = sizeof($avatars);
		$avatars = array_slice($avatars, ($page-1)*$perpage, $perpage);

		cmsPage::initTemplate('components', 'com_users_avatars')->
                assign('userid', $id)->
                assign('avatars', $avatars)->
                assign('avatars_dir', $avatars_dir_rel)->
                assign('page', $page)->
                assign('perpage', $perpage)->
                assign('pagebar', cmsPage::getPagebar($total, $page, $perpage, '/users/%user_id%/select-avatar-%page%.html', array('user_id'=>$id)))->
                display('com_users_avatars.tpl');

	} else {

		$avatar_id = cmsCore::request('avatar_id', 'int', 0);
		$file      = $avatars[$avatar_id];

		if (file_exists($avatars_dir.'/'.$file)){

			$uploaddir 	  = PATH.'/images/users/avatars/';
			$realfile     = $file;
			$filename 	  = md5($realfile . '-' . $id . '-' . time()).'.jpg';
			$uploadfile	  = $avatars_dir . '/' . $realfile;
			$uploadavatar = $uploaddir . $filename;
			$uploadthumb  = $uploaddir . 'small/' . $filename;

			if ($inUser->orig_imageurl && $inUser->orig_imageurl != 'nopic.jpg'){
				@unlink(PATH.'/images/users/avatars/'.$inUser->orig_imageurl);
				@unlink(PATH.'/images/users/avatars/small/'.$inUser->orig_imageurl);
			}

			cmsCore::includeGraphics();
			copy($uploadfile, $uploadavatar);
			@img_resize($uploadfile, $uploadthumb, $model->config['smallw'], $model->config['smallw']);

			$sql = "UPDATE cms_user_profiles SET imageurl = '$filename' WHERE user_id = '$id' LIMIT 1";
			$inDB->query($sql);

			// очищаем предыдущую запись о смене аватара
			cmsActions::removeObjectLog('add_avatar', $id);
			// выводим сообщение в ленту
			cmsActions::log('add_avatar', array(
				  'object' => '',
				  'object_url' => '',
				  'object_id' => $id,
				  'target' => '',
				  'target_url' => '',
				  'description' => '<a href="'.cmsUser::getProfileURL($inUser->login).'" class="act_usr_ava">
										<img border="0" src="/images/users/avatars/small/'.$filename.'">
									</a>'
			));

		}

		cmsCore::redirect(cmsUser::getProfileURL($inUser->login));

	}

}
//============================================================================//
//======================== Работа с фотографиями  ============================//
//============================================================================//
if ($do=='photos'){

    if (!$model->config['sw_photo']) { cmsCore::error404(); }

    $pdo = cmsCore::request('pdo', 'str', '');

    include 'components/users/photos.php';

}
//============================================================================//
//============================= Друзья пользователя  =========================//
//============================================================================//
if ($do=='friendlist'){

	if (!$inUser->id) { cmsUser::goToLogin(); }

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

	$perpage = 10;

	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['FRIENDS']);
	$inPage->setTitle($_LANG['FRIENDS']);

	// все друзья
	$friends = cmsUser::getFriends($usr['id']);
	// их общее количество
	$total = count($friends);
	// получаем только нужных на странице
	$friends = array_slice($friends, ($page-1)*$perpage, $perpage);

    cmsPage::initTemplate('components', 'com_users_friends')->
            assign('friends', $friends)->
            assign('usr', $usr)->
            assign('myprofile', ($id == $inUser->id))->
            assign('total', $total)->
            assign('pagebar', cmsPage::getPagebar($total, $page, $perpage, 'javascript:centerLink(\'/users/'.$id.'/friendlist%page%.html\')'))->
            display('com_users_friends.tpl');

}

//============================================================================//
//============================= Запрос на дружбу  ============================//
//============================================================================//
if ($do == 'addfriend'){

    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id || $inUser->id == $id) { cmsCore::halt(); }

    $usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::halt(); }

    cmsUser::clearSessionFriends();

	if(cmsUser::isFriend($id)){ cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['YOU_ARE_BE_FRIENDS'])); }

    // проверяем был ли ранее запрос на дружбу
    // если был, то делаем accept запросу
    $is_need_accept_id = cmsUser::getFriendFieldId($id, 0, 'to_me');
    if($is_need_accept_id){

        $inDB->query("UPDATE cms_user_friends SET is_accepted = 1 WHERE id = '{$is_need_accept_id}'");
        //регистрируем событие
        cmsActions::log('add_friend', array(
            'object' => $inUser->nickname,
            'user_id' => $usr['id'],
            'object_url' => cmsUser::getProfileURL($inUser->login),
            'object_id' => $is_need_accept_id,
            'target' => '',
            'target_url' => '',
            'target_id' => 0,
            'description' => ''
        ));

        cmsCore::callEvent('USER_ACCEPT_FRIEND', $id);

        cmsCore::jsonOutput(array('error' => false, 'text' => $_LANG['ADD_FRIEND_OK'] . $usr['nickname']));

    }

    // Если пользователь пытается добавиться в друзья к
    // пользователю, к которому уже отправил запрос
    if(cmsUser::getFriendFieldId($id, 0, 'from_me')){
        cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ADD_TO_FRIEND_SEND_ERR']));
    }

    // Мы вообще не друзья с пользователем, создаем запрос
    cmsUser::addFriend($id);

    cmsUser::sendMessage(USER_UPDATER, $id, sprintf($_LANG['RECEIVED_F_O'],
        cmsUser::getProfileLink($inUser->login, $inUser->nickname),
        '<a class="ajaxlink" href="javascript:void(0)" onclick="users.acceptFriend('.$inUser->id.', this);return false;">'.$_LANG['ACCEPT'].'</a>',
        '<a class="ajaxlink" href="javascript:void(0)" onclick="users.rejectFriend('.$inUser->id.', this);return false;">'.$_LANG['REJECT'].'</a>'));

    cmsCore::jsonOutput(array('error' => false, 'text' => $_LANG['ADD_TO_FRIEND_SEND']));

}
//============================================================================//
//============================= Прекращение дружбы  ==========================//
//============================================================================//
if ($do == 'delfriend'){

    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id || $inUser->id == $id){ cmsCore::halt(); }

    $usr = cmsUser::getShortUserData($id);
    if (!$usr) { cmsCore::error404(); }

    if(cmsUser::getFriendFieldId($id)){

        $is_accepted_friend = cmsUser::isFriend($id);

        if(cmsUser::deleteFriend($id)){

            // Если подтвержденный друг
            if($is_accepted_friend){
                cmsCore::jsonOutput(array('error' => false, 'text' => $usr['nickname'] . $_LANG['DEL_FRIEND']));
            } else {
                cmsCore::jsonOutput(array('error' => false, 'text' => $_LANG['REJECT_FRIEND'].$usr['nickname']));
            }

        } else {
            cmsCore::halt();
        }

    } else {

        cmsCore::halt();

    }

}
//============================================================================//
//============================= История кармы  ===============================//
//============================================================================//
if ($do=='karma'){

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

	$inPage->setTitle($_LANG['KARMA_HISTORY']);
	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['KARMA_HISTORY']);

	cmsPage::initTemplate('components', 'com_users_karma')->
            assign('karma', $model->getUserKarma($usr['id']))->
            assign('usr', $usr)->
            display('com_users_karma.tpl');

}
//============================================================================//
//============================= Изменение кармы  =============================//
//============================================================================//
if ($do=='votekarma'){

    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id){ cmsCore::halt(); }

	$points = (cmsCore::request('sign', 'str', 'plus')=='plus' ? 1 : -1);
    $to     = cmsCore::request('to', 'int', 0);

    $user = cmsUser::getShortUserData($to);
    if (!$user) { cmsCore::halt(); }

	if (!$model->isUserCanChangeKarma($to)){ cmsCore::halt(); }

	cmsCore::halt(cmsUser::changeKarmaUser($to, $points));

}
//============================================================================//
//======================= Наградить пользователя  ============================//
//============================================================================//
if ($do=='giveaward'){

    if (!$inUser->is_admin) { cmsCore::error404(); }

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

	$inPage->setTitle($_LANG['AWARD_USER']);
	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['AWARD']);

	if(!cmsCore::inRequest('gosend')){

		cmsPage::initTemplate('components', 'com_users_awards_give')->
                assign('usr', $usr)->
                assign('awardslist', cmsUser::getAwardsImages())->
                display('com_users_awards_give.tpl');

	} else {

		$award['title']       = cmsCore::request('title', 'str', $_LANG['AWRD']);
		$award['description'] = cmsCore::request('description', 'str', '');
		$award['imageurl']    = cmsCore::request('imageurl', 'str', '');
        $award['from_id']     = $inUser->id;
        $award['id'] = 0;

        cmsUser::giveAward($award, $id);

		cmsCore::redirect(cmsUser::getProfileURL($usr['login']));

	}

}
//============================================================================//
//============================= Удаление награды  ============================//
//============================================================================//
if ($do=='delaward'){

	$aw = $inDB->get_fields('cms_user_awards', "id = '$id'", '*');
    if (!$aw){ cmsCore::error404(); }

	if (!$inUser->id || ($inUser->id!=$aw['user_id'] && !$inUser->is_admin)){ cmsCore::error404(); }

	$inDB->delete('cms_user_awards', "id = '$id'", 1);

	cmsActions::removeObjectLog('add_award', $id);

	cmsCore::redirectBack();

}
//============================================================================//
//============================= Награды на сайте  ============================//
//============================================================================//
if ($do=='awardslist'){

	$inPage->setTitle($_LANG['SITE_AWARDS']);
	$inPage->addPathway($_LANG['SITE_AWARDS']);

	$awards = cmsUser::getAutoAwards();
    if (!$awards){ cmsCore::error404(); }

    foreach ($awards as $aw) {

        //Перебираем все награды и ищем пользователей с текущей наградой
        $sql =  "SELECT u.id as id, u.nickname as nickname, u.login as login, IFNULL(p.gender, 'm') as gender
                 FROM cms_user_awards aw
                 LEFT JOIN cms_users u ON u.id = aw.user_id
                 LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                 WHERE aw.award_id = '{$aw['id']}'";
        $rs = $inDB->query($sql);
        $aw['uhtml'] = '';
        if ($inDB->num_rows($rs)){

            while ($user = $inDB->fetch_assoc($rs)){
                $aw['uhtml'] .= cmsUser::getGenderLink($user['id'], $user['nickname'], $user['gender'], $user['login']).', ';
            }

            $aw['uhtml'] = rtrim($aw['uhtml'], ', ');

        } else {
            $aw['uhtml'] = $_LANG['NOT_USERS_WITH_THIS_AWARD'];
        }

        $aws[] = $aw;

    }

	cmsPage::initTemplate('components', 'com_users_awards_site')->
            assign('aws', $aws)->
            display('com_users_awards_site.tpl');

}
//============================================================================//
//============================= Удаление профиля  ============================//
//============================================================================//
if ($do == 'delprofile'){

	// неавторизованным тут делать нечего
	if (!$inUser->id) { cmsCore::error404(); }

	// есть ли удаляемый профиль
	$data = cmsUser::getShortUserData($id);
	if (!$data) { cmsCore::error404(); }

	// владелец профиля или админ
	if($inUser->is_admin){
		// могут ли администраторы удалять профиль
		if (!cmsUser::isAdminCan('admin/users', cmsUser::getAdminAccess())) { cmsCore::error404(); }
		// администратор сам себя не удалит
		if ($inUser->id == $data['id']){ cmsCore::error404(); }
	} else {
		// удаляем только свой профиль
		if ($inUser->id != $data['id']){ cmsCore::error404(); }
	}

	if (isset($_POST['csrf_token'])){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$model->deleteUser($id);

		if (!$inUser->is_admin){
			session_destroy();
			cmsCore::redirect('/logout');
		} else {
			cmsCore::addSessionMessage($_LANG['DELETING_PROFILE_OK'], 'info');
			cmsCore::redirect('/users');
		}

	} else {

		$inPage->setTitle($_LANG['DELETING_PROFILE']);
		$inPage->addPathway($data['nickname'], $inUser->getProfileURL($data['login']));
		$inPage->addPathway($_LANG['DELETING_PROFILE']);

		$confirm['title'] = $_LANG['DELETING_PROFILE'];
		$confirm['text'] = '<p>'.$_LANG['REALLY_DEL_PROFILE'].'</p>';
		$confirm['action'] = '/users/'.$id.'/delprofile.html';
		$confirm['yes_button'] = array();
		$confirm['yes_button']['type'] = 'submit';
		cmsPage::initTemplate('components', 'action_confirm.tpl')->
                assign('confirm', $confirm)->
                display('action_confirm.tpl');

	}

}
//============================================================================//
//============================ Восстановить профиль  =========================//
//============================================================================//
if ($do=='restoreprofile'){

    if (!$inUser->is_admin) { cmsCore::error404(); }

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

	$inDB->query("UPDATE cms_users SET is_deleted = 0 WHERE id = '$id'") ;

	cmsCore::redirectBack();

}
//============================================================================//
//============================= Файлы пользователей  =========================//
//============================================================================//
if ($do=='files'){

    if (!$model->config['sw_files']) { cmsCore::error404(); }

    $fdo = cmsCore::request('fdo', 'str', '');

    include 'components/users/files.php';

}

//============================================================================//
//================================  Инвайты  =================================//
//============================================================================//
if ($do=='invites'){

    $reg_cfg = $inCore->loadComponentConfig('registration');
    if ($reg_cfg['reg_type'] != 'invite') { cmsCore::error404(); }

    $invites_count = $model->getUserInvitesCount($inUser->id);
    if (!$invites_count) { cmsCore::error404(); }

    if (!cmsCore::inRequest('send_invite')){

        $inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
        $inPage->addPathway($_LANG['MY_INVITES']);

        cmsPage::initTemplate('components', 'com_users_invites')->
                assign('invites_count', $invites_count)->
                display('com_users_invites.tpl');

        return;

    }

    if (cmsCore::inRequest('send_invite')){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $invite_email = cmsCore::request('invite_email', 'email', '');
        if (!$invite_email) { cmsCore::redirectBack(); }

        if ($model->sendInvite($inUser->id, $invite_email)){

            cmsCore::addSessionMessage(sprintf($_LANG['INVITE_SENDED'], $invite_email), 'success');

        } else {

            cmsCore::addSessionMessage($_LANG['INVITE_ERROR'], 'error');

        }

        cmsCore::redirect(cmsUser::getProfileURL($inUser->login));

    }

}
if ($do=='change_email'){

    if(!$inUser->id){
        cmsUser::goToLogin();
    }

    $email = cmsCore::request('email', 'email', '');
    $token = cmsCore::request('token', 'str', '');

    // не занят ли email
    $is_email = $inDB->get_field('cms_users', "email='{$email}'", 'id');

    if($is_email || !$email || !$token){
        cmsCore::error404();
    }

    // проверяем токен
    $valid_id = $inDB->get_field('cms_users_activate', "code='{$token}' AND user_id = '{$inUser->id}'", 'id');
    if(!$valid_id){
        cmsCore::error404();
    }

    $inDB->delete('cms_users_activate', "id = '{$valid_id}'");

    // Сохраняем новый email
    $inDB->update('cms_users', array('email'=>$email), $inUser->id);

    cmsCore::addSessionMessage($_LANG['NEW_EMAIL_SAVED'], 'success');

    cmsCore::redirect(cmsUser::getProfileURL($inUser->login));

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}