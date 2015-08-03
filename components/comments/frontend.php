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
/**
 *
 * @param type $target Место назначения (к чему комментарии)
 * @param type $target_id ID места назначения
 * @param type $labels Массив фраз (может быть частично изменен)
 * @param type $can_delete Флаг, что пользователь может удалять комментарии
 * @return boolean
 */
function comments($target='', $target_id=0, $labels=array(), $can_delete=null){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    cmsCore::loadModel('comments');
    $model = new cms_model_comments($labels);

    // Проверяем включени ли компонент
    if(!$inCore->isComponentEnable('comments')) { return false; }

	// Инициализируем права доступа для группы текущего пользователя
	$model->initAccess();

    global $_LANG;

    $do    = $inCore->do;
	$page  = cmsCore::request('page', 'int', 1);
	$id    = cmsCore::request('id', 'int', 0);
	$login = cmsCore::strClear(urldecode(cmsCore::request('login', 'html', '')));

    // права доступа на удаление комментариев авторами
    if(isset($can_delete)){
        // сохраняем в сессию
        $_SESSION['comments'][$target][$target_id] = $can_delete;
        $can_delete = ($can_delete && $model->target_author_delete);
    }

	$inPage->addHeadJS('components/comments/js/comments.js');
    $inPage->addHeadJsLang(array('EDIT_COMMENT','CONFIRM_DEL_COMMENT','COMMENT_IN_LINK'));

//========================================================================================================================//
//========================================================================================================================//
if ($do == 'view' && !$target && !$target_id){

	if(!$login){
		$myprofile  = false;
		$page_title = $inCore->getComponentTitle();
		$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['COMMENTS'].'" href="'.HOST.'/rss/comments/all/feed.rss">');
	} else {
		// проверяем что пользователь есть
		$user = cmsUser::getShortUserData($login);
		if (!$user) { cmsCore::error404(); }
		// Мои комментарии
		$myprofile = ($inUser->id == $user['id']);
		$page_title = $_LANG['COMMENTS'].' - '.$user['nickname'];
		$inPage->addPathway($user['nickname'], cmsUser::getProfileURL($user['login']));
		// Добавляем условие в выборку
		$model->whereUserIs($user['id']);
	}
	$inPage->setTitle($page_title);
	$inPage->addPathway($page_title);
	$inPage->setDescription($model->config['meta_desc'] ? $model->config['meta_desc'] : $page_title);
    $inPage->setKeywords($model->config['meta_keys'] ? $model->config['meta_keys'] : $page_title);

	// флаг модератора
	$is_moder = ($inUser->is_admin || $model->is_can_moderate);

	// Не админам только открытые комментарии
	if(!($is_moder || $myprofile)){
		$model->whereIsShow();
	}

	// Общее количество комментариев
	$total = $model->getCommentsCount(!($is_moder || $myprofile));

	// Сортировка и разбивка на страницы
	$inDB->orderBy('c.pubdate', 'DESC');
	$inDB->limitPage($page, $model->config['perpage']);

	// Сами комментарии
	$comments = $total ?
				$model->getComments(!($is_moder || $myprofile)) :
				array(); $inDB->resetConditions();
    if(!$comments && $page > 1){ cmsCore::error404(); }

	// пагинация
	if(!$login){
		$pagebar = cmsPage::getPagebar($total, $page, $model->config['perpage'], '/comments/page-%page%');
	} else {
		$pagebar = cmsPage::getPagebar($total, $page, $model->config['perpage'], 'javascript:centerLink(\'/comments/by_user_'.$user['login'].'/page-%page%\')');
	}

	// Отдаем в шаблон
	cmsPage::initTemplate('components', 'com_comments_list_all')->
            assign('comments_count', $total)->
            assign('comments', $comments)->
            assign('pagebar', $pagebar)->
            assign('is_user', $inUser->id)->
            assign('page_title', $page_title)->
            assign('cfg', $model->config)->
            assign('is_admin', $is_moder)->
            display('com_comments_list_all.tpl');

}

//========================================================================================================================//
//========================================================================================================================//
if (!in_array($do, array('add', 'edit', 'delete')) && $target && $target_id){

	if (!$model->config['cmm_ajax']){

		$model->whereTargetIs($target, $target_id);

		$inDB->orderBy('c.pubdate', 'ASC');

		$comments = cmsCore::callEvent('BEFORE_SHOW_COMMENTS', $model->getComments(!($inUser->is_admin || $model->is_can_moderate), true));

		$total = count($comments);

		ob_start();

		cmsPage::initTemplate('components', 'com_comments_list')->
                assign('comments_count', $total)->
                assign('comments', $comments)->
                assign('user_can_moderate', $model->is_can_moderate)->
                assign('user_can_delete', $model->is_can_delete)->
                assign('target_author_can_delete', $can_delete)->
                assign('user_can_add', $model->is_can_add)->
                assign('is_admin', $inUser->is_admin)->
                assign('is_user', $inUser->id)->
                assign('cfg', $model->config)->
                assign('labels', $model->labels)->
                assign('target', $target)->
                assign('target_id', $target_id)->
                display('com_comments_list.tpl');

		$html = ob_get_clean();

	} else {

		$model->whereTargetIs($target, $target_id);
		$total = $model->getCommentsCount(!($inUser->is_admin || $model->is_can_moderate));
        $inDB->resetConditions();

	}

	cmsPage::initTemplate('components', 'com_comments_view')->
            assign('comments_count', $total)->
            assign('target', $target)->
            assign('target_id', $target_id)->
            assign('target_author_can_delete', (int)$can_delete)->
            assign('is_admin', $inUser->is_admin)->
            assign('labels', $model->labels)->
            assign('is_user', $inUser->id)->
            assign('cfg', $model->config)->
            assign('user_can_add', $model->is_can_add)->
            assign('html', isset($html) ? $html : '')->
            assign('add_comment_js', "addComment('".$target."', '".$target_id."', 0)")->
            assign('user_subscribed', cmsUser::isSubscribed($inUser->id, $target, $target_id))->
            display('com_comments_view.tpl');

}

//========================================================================================================================//
//========================================================================================================================//
// Добавление комментария, форма добавления в addform.php
if ($do=='add'){

	// Только аякс
	if(!cmsCore::isAjax()) { cmsCore::error404(); }

	// Очищаем буфер
	ob_end_clean();

	// Добавлять могут только админы и те, кому разрешено в настройках группы
	if (!$model->is_can_add && !$inUser->is_admin){ cmsCore::error404(); }

	// Входные данные
	$comment['guestname'] = cmsCore::request('guestname', 'str', '');
	$comment['user_id']   = $inUser->id;

	if ($model->is_can_bbcode){
		$content = cmsCore::request('content', 'html', '');
		$comment['content_bbcode'] = $inDB->escape_string($content);
		$content = cmsCore::parseSmiles($content, true);
		$comment['content'] = $inDB->escape_string($content);
	} else {
		$comment['content']        = cmsCore::request('content', 'str', '');
		$comment['content_bbcode'] = $comment['content'];
		$comment['content']        = str_replace(array('\r', '\n'), '<br>', $comment['content']);
	}

	$comment['parent_id'] = cmsCore::request('parent_id', 'int', 0);
	$comment['target']    = cmsCore::request('target', 'str', '');
	$comment['target_id'] = cmsCore::request('target_id', 'int', 0);
	$comment['ip']        = cmsCore::strClear($_SERVER['REMOTE_ADDR']);;

	// Проверяем правильность/наличие входных парамеров
	// цель комментария
	if (!$comment['target'] || !$comment['target_id']) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_UNKNOWN_TARGET'])); }
	// Имя гостя отсутствует
	if (!$comment['guestname'] && !$inUser->id) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_USER_NAME'])); }
	// Текст комментраия отсутствует
	if (!$comment['content']) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_COMMENT_TEXT'])); }
	// проверяем каптчу
	$need_captcha = $model->config['regcap'] ? true : ($inUser->id ? false : true);
	if ($need_captcha && !cmsPage::checkCaptchaCode()) { cmsCore::jsonOutput(array('error' => true, 'is_captcha' => true, 'text' => $_LANG['ERR_CAPTCHA'])); }

	// получаем массив со ссылкой и заголовком цели комментария
	// для этого:
	//  1. узнаем ответственный компонент из cms_comment_targets
	$target = $inDB->get_fields('cms_comment_targets', "target='{$comment['target']}'", '*');
	if (!$target) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_UNKNOWN_TARGET'] . ' #1')); }

	//  2. подключим модель этого компонента
	if(cmsCore::loadModel($target['component'])){

		$model_class = 'cms_model_'.$target['component'];
		if(class_exists($model_class)){
			$target_model = new $model_class();
		}

	}

	if (!isset($target_model)) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_UNKNOWN_TARGET'] . ' #2')); }

	//  3. запросим массив $target_data[link, title] у метода getCommentTarget модели
	$target_data = $target_model->getCommentTarget($comment['target'], $comment['target_id']);
	if (!$target_data) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_UNKNOWN_TARGET'] . ' #3')); }
	$comment['target_title'] = $target_data['title'];
	$comment['target_link']  = $target_data['link'];

	// 4. Узнаем видимость комментария в модели $target_model
	if(method_exists($target_model, 'getVisibility')){
		$comment['is_hidden'] = $target_model->getVisibility($comment['target'], $comment['target_id']);
	} else {
		$comment['is_hidden'] = 0;
	}

	// публикация согласно настроек
	$comment['published'] = ($inUser->is_admin || $model->is_can_moderate || $model->is_add_published) ? 1 : 0;

    // Проверяем токен перед самым добавлением комментария
	if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

	// 5. добавляем комментарий в базу
	$comment_id = $model->addComment($comment);

	// 6. Пересчитываем количество комментариев у цели если нужно
	if(method_exists($target_model, 'updateCommentsCount')){
		$target_model->updateCommentsCount($comment['target'], $comment['target_id']);
	}

	if(!$comment['is_hidden'] && $comment['published']){
		//регистрируем событие
		$content_short = strip_tags($comment['content']);
		cmsActions::log('add_comment', array(
			'object' => $_LANG['COMMENT'],
			'object_url' => $comment['target_link'] . '#c' . $comment_id,
			'object_id' => $comment_id,
			'target' => $comment['target_title'],
			'target_url' => $comment['target_link'],
			'target_id' => $comment['target_id'],
			'description' => mb_strlen($content_short)>140 ? mb_substr($content_short, 0, 140) : $content_short
		));
	}

    ////////////////////////////////////////////////////////////////
    ///////////////// Операции по уведомлениям /////////////////////

    $inConf = cmsConfig::getInstance();

    $from_nick  = $inUser->id ? $inUser->nickname : $comment['guestname'];
    $targetlink = HOST.$comment['target_link'] . '#c' . $comment_id;
	//получаем ID и e-mail автора
	$author = $inUser->id ?
				$model->getTargetAuthor($target['target_table'], $comment['target_id']) :
				'';

	//подписываем пользователя на обновления, если нужно
	if ($inUser->id && cmsCore::inRequest('subscribe')){
		cmsUser::subscribe($inUser->id, $comment['target'], $comment['target_id']);
	}

	if ($comment['published']){
		//рассылаем уведомления о новом комменте
		cmsUser::sendUpdateNotify($comment['target'], $comment['target_id'],
                                    array('link' => $comment['target_link'] . '#c' . $comment_id,
                                          'title' => stripslashes($comment['target_title']),
                                          'letter_file' => 'newcomment',
                                          'author' => ($inUser->id ? $inUser->nickname : $comment['guestname'])));

		//проверяем и выдаем награду если нужно
		cmsUser::checkAwards($inUser->id);
	}

	//отправляем админу уведомление о комментарии на e-mail, если нужно
	if($model->config['email']) {

        $mailmsg = str_replace(array('{sitename}','{date}','{from}',
                                     '{subjtitle}','{targetlink}','{content}'),
                               array($inConf->sitename,date('d/m/Y (H:i)'),$from_nick,
                                     stripslashes($comment['target_title']),$targetlink,strip_tags($comment['content'])),
                              cmsCore::getLanguageTextFile('newcomment_admin'));

		$inCore->mailText($model->config['email'], '', $mailmsg);

	}

	//отправляем автору уведомление на e-mail
	if ($author && $comment['published']){

		if ($model->isAuthorNeedMail($author['id']) && $inUser->id != $author['id']){

			$letter = cmsCore::getLanguageTextFile('newpostcomment');
			$letter = str_replace('{sitename}', $inConf->sitename, $letter);
			$letter = str_replace('{subj}', $target['subj'], $letter);
            $letter = str_replace('{subjtitle}', stripslashes($comment['target_title']), $letter);
			$letter = str_replace('{targetlink}', $targetlink, $letter);
			$letter = str_replace('{date}', date('d/m/Y H:i:s'), $letter);
			$letter = str_replace('{from}', $from_nick, $letter);
			$inCore->mailText($author['email'], '', $letter);

		}

	}

	if (!$comment['published']){

        $message = str_replace(array('%user%','%targetlink%'), array($from_nick,$targetlink), $_LANG['COMM_PREMODER_ADMIN_TEXT']);

        cmsUser::sendMessage(USER_UPDATER, 1, $message);

	}

	cmsCore::jsonOutput(array('error' => false,
								'target' => $comment['target'],
								'target_id' => $comment['target_id'],
								'is_premod' => ($comment['published'] ? 0 : $_LANG['COMM_PREMODER_TEXT']),
								'comment_id' => $comment_id));

}

//========================================================================================================================//
//========================================================================================================================//
if ($do=='edit'){

	if(!cmsCore::isAjax()) { cmsCore::error404(); }

	$comment = $model->getComment(cmsCore::request('comment_id', 'int', 0));
	if(!$comment) { die(); }

	// редактировать могут авторы (если время редактирования есть)
	// модераторы и администраторы
	if (!$model->is_can_moderate &&
			!$inUser->is_admin &&
			!(($inUser->id == $comment['user_id']) && $comment['is_editable'])){ cmsCore::error404(); }

	if ($model->is_can_bbcode){
		$content = cmsCore::request('content', 'html', '');
		$com_new['content_bbcode'] = $inDB->escape_string($content);
		$com_new['content'] = $inDB->escape_string(cmsCore::parseSmiles($content, true));
	} else {
		$com_new['content']        = cmsCore::request('content', 'str', '');
		$com_new['content_bbcode'] = $com_new['content'];
		$com_new['content']        = str_replace(array('\r', '\n'), '<br>', $com_new['content']);
	}

	// Текст комментраия отсутствует
	if (!$com_new['content']) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_COMMENT_TEXT'])); }

	if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

	//Если ошибок не было,
	//обновляем комментарий в базе
	$model->updateComment($comment['id'], $com_new);

	// Обновляем в ленте активности
	$content_short = mb_substr(strip_tags($com_new['content']), 0, 140);
	cmsActions::updateLog('add_comment', array('description' => $content_short), $comment['id']);

    $com_new['content'] =  stripslashes(str_replace(array('\r', '\n'), ' ', $com_new['content']));

    $com_new = cmsCore::callEvent('GET_COMMENT', $com_new);

	cmsCore::jsonOutput(array('error' => false, 'text' => $com_new['content'], 'comment_id' => $comment['id']));

}

//========================================================================================================================//
//========================================================================================================================//
if ($do == 'delete'){

	if(!cmsCore::isAjax() || !$inUser->id) { cmsCore::error404(); }

	$comment = $model->getComment($id);
	if(!$comment) { cmsCore::error404(); }

    if(isset($_SESSION['comments'][$comment['target']][$comment['target_id']])){
        $can_delete = ($_SESSION['comments'][$comment['target']][$comment['target_id']] && $model->target_author_delete);
    } else {
        $can_delete = false;
    }

	if (!($model->is_can_delete && $inUser->id == $comment['user_id']) &&
        !$model->is_can_moderate &&
        !$inUser->is_admin &&
        !$can_delete){
        cmsCore::error404();
    }

	//узнаем ответственный компонент из cms_comment_targets
	$target = $inDB->get_fields('cms_comment_targets', "target='{$comment['target']}'", '*');
	if (!$target) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ERR_UNKNOWN_TARGET'] . ' #1')); }

	if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

	$model->deleteComment($id);

	//подключим модель этого компонента
	if(cmsCore::loadModel($target['component'])){

		$model_class = 'cms_model_'.$target['component'];
		if(class_exists($model_class)){
			$target_model = new $model_class();

			// Пересчитываем количество комментариев у цели если нужно
			if(method_exists($target_model, 'updateCommentsCount')){
				$target_model->updateCommentsCount($comment['target'], $comment['target_id']);
			}

		}

	}

	cmsCore::jsonOutput(array('error' => false,
								'target' => $comment['target'],
								'target_id' => $comment['target_id']));

}

}