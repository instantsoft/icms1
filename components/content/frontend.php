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

function content(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    $model = new cms_model_content();

    define('IS_BILLING', $inCore->isComponentInstalled('billing'));
    if (IS_BILLING) { cmsCore::loadClass('billing'); }

    global $_LANG;

    $id = cmsCore::request('id', 'int', 0);
    $do = $inCore->do;

    $seolink = cmsCore::strClear(urldecode(cmsCore::request('seolink', 'html', '')));
    if(is_numeric($seolink)){ cmsCore::error404(); }

	$page = cmsCore::request('page', 'int', 1);

///////////////////////////////////// VIEW CATEGORY ////////////////////////////////////////////////////////////////////////////////
if ($do=='view'){

	$cat = $inDB->getNsCategory('cms_category', $seolink);

	// если не найдена категория и мы не на главной, 404
    if (!$cat && $inCore->menuId() !== 1) { cmsCore::error404(); }

    // языки
    $cat = translations::process(cmsConfig::getConfig('lang'), 'content_category', $cat);

	// Плагины
	$cat = cmsCore::callEvent('GET_CONTENT_CAT', $cat);

	// Неопубликованные показываем только админам
	if (!$cat['published'] && !$inUser->is_admin) { cmsCore::error404(); }

	// Проверяем доступ к категории
	if(!$inCore->checkUserAccess('category', $cat['id']) ){

		cmsCore::addSessionMessage($_LANG['NO_PERM_FOR_VIEW_TEXT'].'<br>'.$_LANG['NO_PERM_FOR_VIEW_RULES'], 'error');
		cmsCore::redirect('/content');

	}

	// если не корень категорий
	if($cat['NSLevel'] > 0){

		$inPage->setTitle($cat['pagetitle'] ? $cat['pagetitle'] : $cat['title']);

		$pagetitle = $cat['title'];
		$showdate  = $cat['showdate'];
		$showcomm  = $cat['showcomm'];
		$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.htmlspecialchars($cat['title']).'" href="'.HOST.'/rss/content/'.$cat['id'].'/feed.rss">');
	}

	// Если корневая категория
    if ($cat['NSLevel'] == 0){

        if($model->config['hide_root']){ cmsCore::error404(); }

		$inPage->setTitle($_LANG['CATALOG_ARTICLES']);
		$pagetitle = $_LANG['CATALOG_ARTICLES'];
		$showdate  = 1;
		$showcomm  = 1;
	}

	// Получаем дерево категорий
    $path_list = $inDB->getNsCategoryPath('cms_category', $cat['NSLeft'], $cat['NSRight'], 'id, title, NSLevel, seolink, url');

    if ($path_list){

        $path_list = translations::process(cmsConfig::getConfig('lang'), 'content_category', $path_list);

        foreach($path_list as $pcat){

			if(!$inCore->checkUserAccess('category', $pcat['id'])){
				cmsCore::addSessionMessage($_LANG['NO_PERM_FOR_VIEW_TEXT'].'<br>'.$_LANG['NO_PERM_FOR_VIEW_RULES'], 'error');
				cmsCore::redirect('/content');
			}

            $inPage->addPathway($pcat['title'], $model->getCategoryURL(null, $pcat['seolink']));

        }

    }

	// Получаем подкатегории
    $subcats_list = $model->getSubCats($cat['id']);

	// Привязанный фотоальбом
	$cat_photos = $model->getCatPhotoAlbum($cat['photoalbum']);

	// Получаем статьи
	// Редактор/администратор
	$is_editor = (($cat['modgrp_id'] == $inUser->group_id  && cmsUser::isUserCan('content/autoadd')) || $inUser->is_admin);

	// Условия
	$model->whereCatIs($cat['id']);

	// Общее количество статей
	$total = $model->getArticlesCount($is_editor);

	// Сортировка и разбивка на страницы
    $inDB->orderBy($cat['orderby'], $cat['orderto']);
    $inDB->limitPage($page, $model->config['perpage']);

	// Получаем статьи
    $content_list = $total ?
					$model->getArticlesList(!$is_editor) :
					array(); $inDB->resetConditions();
    if(!$content_list && $page > 1){ cmsCore::error404(); }

    $pagebar  = cmsPage::getPagebar($total, $page, $model->config['perpage'], $model->getCategoryURL(null, $cat['seolink'], 0, true));

	$template = ($cat['tpl'] ? $cat['tpl'] : 'com_content_view.tpl');

	if($cat['NSLevel'] > 0){

        // meta description
        if($cat['meta_desc']){
            $meta_desc = $cat['meta_desc'];
        } elseif(mb_strlen(strip_tags($cat['description']))>=250){
            $meta_desc = crop($cat['description']);
        } else {
            $meta_desc = $cat['title'];
        }
		$inPage->setDescription($meta_desc);
        // meta keywords
        if($cat['meta_keys']){
            $meta_keys = $cat['meta_keys'];
        } elseif($content_list){
            foreach($content_list as $c){
                $k[] = $c['title'];
            }
            $meta_keys = implode(', ', $k);
        } else {
            $meta_keys = $cat['title'];
        }
		$inPage->setKeywords($meta_keys);

	}

	cmsPage::initTemplate('components', $template)->
            assign('cat', $cat)->
            assign('is_homepage', (bool)($inCore->menuId()==1))->
            assign('showdate', $showdate)->
            assign('showcomm', $showcomm)->
            assign('pagetitle', $pagetitle)->
            assign('subcats', $subcats_list)->
            assign('cat_photos', $cat_photos)->
            assign('articles', $content_list)->
            assign('pagebar', $pagebar)->
            display($template);

}
///////////////////////////////////// READ ARTICLE ////////////////////////////////////////////////////////////////////////////////
if ($do=='read'){

	// Получаем статью
	$article = $model->getArticle($seolink);
    if (!$article) { cmsCore::error404(); }

    $article = translations::process(cmsConfig::getConfig('lang'), 'content_content', $article);

	$article = cmsCore::callEvent('GET_ARTICLE', $article);

	$is_admin      = $inUser->is_admin;
	$is_author     = $inUser->id == $article['user_id'];
	$is_author_del = cmsUser::isUserCan('content/delete');
	$is_editor     = ($article['modgrp_id'] == $inUser->group_id && cmsUser::isUserCan('content/autoadd'));

	// если статья не опубликована или дата публикации позже, 404
	if ((!$article['published'] || strtotime($article['pubdate']) > time()) && !$is_admin && !$is_editor && !$is_author) { cmsCore::error404(); }

	if(!$inCore->checkUserAccess('material', $article['id'])){
		cmsCore::addSessionMessage($_LANG['NO_PERM_FOR_VIEW_TEXT'].'<br>'.$_LANG['NO_PERM_FOR_VIEW_RULES'], 'error');
		cmsCore::redirect($model->getCategoryURL(null, $article['catseolink']));
	}

	// увеличиваем кол-во просмотров
	if(@!$is_author){
	    $inDB->setFlag('cms_content', $article['id'], 'hits', $article['hits']+1);
	}

	// Картинка статьи
    $article['image'] = (file_exists(PATH.'/images/photos/medium/article'.$article['id'].'.jpg') ? 'article'.$article['id'].'.jpg' : '');
	// Заголовок страницы
	$article['pagetitle'] = $article['pagetitle'] ? $article['pagetitle'] : $article['title'];
    // Тело статьи в зависимости от настроек
    $article['content'] = $model->config['readdesc'] ? $article['description'].$article['content'] : $article['content'];
	// Дата публикации
	$article['pubdate'] = cmsCore::dateFormat($article['pubdate']);
	// Шаблон статьи
	$article['tpl'] = $article['tpl'] ? $article['tpl'] : 'com_content_read.tpl';

    $inPage->setTitle($article['pagetitle']);

	// Получаем дерево категорий
    $path_list = $article['showpath'] ?
						$inDB->getNsCategoryPath('cms_category', $article['leftkey'], $article['rightkey'], 'id, title, NSLevel, seolink, url') :
						array();

    if ($path_list){

        $path_list = translations::process(cmsConfig::getConfig('lang'), 'content_category', $path_list);

        foreach($path_list as $pcat){

			if(!$inCore->checkUserAccess('category', $pcat['id'])){
				cmsCore::addSessionMessage($_LANG['NO_PERM_FOR_VIEW_TEXT'].'<br>'.$_LANG['NO_PERM_FOR_VIEW_RULES'], 'error');
				cmsCore::redirect('/content');
			}

            $inPage->addPathway($pcat['title'], $model->getCategoryURL(null, $pcat['seolink']));

        }

    }

    $inPage->addPathway($article['title']);

    // Мета теги KEYWORDS и DESCRIPTION
    if ($article['meta_keys']){
		$inPage->setKeywords($article['meta_keys']);
	} else {
        if (mb_strlen($article['content'])>30){
            $inPage->setKeywords(cmsCore::getKeywords(cmsCore::strClear($article['content'])));
        }
    }
    if (mb_strlen($article['meta_desc'])){
		$inPage->setDescription($article['meta_desc']);
	}

    // Выполняем фильтры
    $article['content'] = cmsCore::processFilters($article['content']);

	// Разбивка статей на страницы
    $pt_pages = array();
    if(!empty($GLOBALS['pt'])){
        foreach($GLOBALS['pt'] as $num=>$page_title){
            $pt_pages[$num]['title'] = $page_title;
            $pt_pages[$num]['url']   = $model->getArticleURL(null, $article['seolink'], $num+1);
        }
    }

	// Рейтинг статьи
    if($model->config['rating'] && $article['canrate']){

        $karma = cmsKarma('content', $article['id']);
		$karma_points = cmsKarmaFormatSmall($karma['points']);
        $btns = cmsKarmaButtonsText('content', $article['id'], $karma['points'], $is_author);

    }

    cmsPage::initTemplate('components', $article['tpl'])->
            assign('article', $article)->
            assign('cfg', $model->config)->
            assign('page', $page)->
            assign('is_pages', !empty($GLOBALS['pt']))->
            assign('pt_pages', $pt_pages)->
            assign('is_admin', $is_admin)->
            assign('is_editor', $is_editor)->
            assign('is_author', $is_author)->
            assign('is_author_del', $is_author_del)->
            assign('tagbar', cmsTagBar('content', $article['id']))->
            assign('karma_points', @$karma_points)->
            assign('karma_votes', @$karma['votes'])->
            assign('karma_buttons', @$btns)->
            display($article['tpl']);

    // Комментарии статьи
    if($article['published'] && $article['comments'] && $inCore->isComponentInstalled('comments')){
        cmsCore::includeComments();
        comments('article', $article['id'], array(), $is_author);
    }

}
///////////////////////////////////// ADD ARTICLE //////////////////////////////////////////////////////////////////////////////////
if ($do=='addarticle' || $do=='editarticle'){

	$is_add      = cmsUser::isUserCan('content/add');     // может добавлять статьи
	$is_auto_add = cmsUser::isUserCan('content/autoadd'); // добавлять статьи без модерации

	if (!$is_add && !$is_auto_add){ cmsCore::error404(); }

	// Для редактирования получаем статью и проверяем доступ
    if ($do=='editarticle'){

		// Получаем статью
		$item = $model->getArticle($id);
		if (!$item) { cmsCore::error404(); }

        $pubcats = array();

		// доступ к редактированию админам, авторам и редакторам
		if(!$inUser->is_admin &&
				($item['user_id'] != $inUser->id) &&
				!($item['modgrp_id'] == $inUser->group_id && cmsUser::isUserCan('content/autoadd'))){
			cmsCore::error404();
		}

    }

	// Для добавления проверяем не вводили ли мы данные ранее
	if ($do=='addarticle'){

		$item = cmsUser::sessionGet('article');
		if ($item) { cmsUser::sessionDel('article'); }

        // Категории, в которые разрешено публиковать
        $pubcats = $model->getPublicCats();
        if(!$pubcats) { cmsCore::addSessionMessage($_LANG['ADD_ARTICLE_ERR_CAT'], 'error'); cmsCore::redirectBack(); }

	}

	// не было запроса на сохранение, показываем форму
    if (!cmsCore::inRequest('add_mod')){

        $dynamic_cost = false;

		// Если добавляем статью
        if ($do=='addarticle'){

            $pagetitle = $_LANG['ADD_ARTICLE'];

            $inPage->setTitle($pagetitle);
			$inPage->addPathway($_LANG['USERS'], '/'.str_replace('/', '', cmsUser::PROFILE_LINK_PREFIX));
			$inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
			$inPage->addPathway($_LANG['MY_ARTICLES'], '/content/my.html');
			$inPage->addPathway($pagetitle);

            // поддержка биллинга
            if (IS_BILLING){
                $action = cmsBilling::getAction('content', 'add_content');
                foreach($pubcats as $p=>$pubcat){
                    if ($pubcat['cost']){
                        $dynamic_cost = true;
                    } else {
                        $pubcats[$p]['cost'] = $action['point_cost'][$inUser->group_id];
                    }
                }
                cmsBilling::checkBalance('content', 'add_content', $dynamic_cost);
            }

        }

		// Если редактируем статью
        if ($do=='editarticle'){

            $pagetitle = $_LANG['EDIT_ARTICLE'];

            $inPage->setTitle($pagetitle);
			$inPage->addPathway($_LANG['USERS'], '/'.str_replace('/', '', cmsUser::PROFILE_LINK_PREFIX));
			if($item['user_id'] != $inUser->id){
				$user = $inDB->get_fields('cms_users', "id='{$item['user_id']}'", 'login, nickname');
        		$inPage->addPathway($user['nickname'], cmsUser::getProfileURL($user['login']));
			} else {
				$inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
			}
			$inPage->addPathway($_LANG['MY_ARTICLES'], '/content/my.html');
            $inPage->addPathway($pagetitle);

            $item['tags']  = cmsTagLine('content', $item['id'], false);
            $item['image'] = (file_exists(PATH.'/images/photos/small/article'.$item['id'].'.jpg') ? 'article'.$item['id'].'.jpg' : '');

            if (!$is_auto_add){
				cmsCore::addSessionMessage($_LANG['ATTENTION'].': '.$_LANG['EDIT_ARTICLE_PREMODER'], 'info');
            }

        }

        $inPage->initAutocomplete();
        $autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

        $item = cmsCore::callEvent('PRE_EDIT_ARTICLE', (@$item ? $item : array()));

		cmsPage::initTemplate('components', 'com_content_edit')->
                assign('mod', $item)->
                assign('do', $do)->
                assign('cfg', $model->config)->
                assign('pubcats', $pubcats)->
                assign('pagetitle', $pagetitle)->
                assign('is_admin', $inUser->is_admin)->
                assign('is_billing', IS_BILLING)->
                assign('dynamic_cost', $dynamic_cost)->
                assign('autocomplete_js', $autocomplete_js)->
                display('com_content_edit.tpl');

    }

	// Пришел запрос на сохранение статьи
    if (cmsCore::inRequest('add_mod')){

        $errors = false;

        $article['category_id']  = cmsCore::request('category_id', 'int', 1);
        $article['user_id']      = $item['user_id'] ? $item['user_id'] : $inUser->id;
        $article['title']        = cmsCore::request('title', 'str', '');
        $article['tags']         = cmsCore::request('tags', 'str', '');

        $article['description']  = cmsCore::request('description', 'html', '');
        $article['content']      = cmsCore::request('content', 'html', '');
        $article['description']  = cmsCore::badTagClear($article['description']);
        $article['content']      = cmsCore::badTagClear($article['content']);

        $article['published']    = $is_auto_add ? 1 : 0;
        if ($do=='editarticle'){
           $article['published'] = ($item['published'] == 0) ? $item['published'] : $article['published'];
        }
        $article['pubdate']      = $do=='editarticle' ? $item['pubdate'] : date('Y-m-d H:i');
        $article['enddate']      = $do=='editarticle' ? $item['enddate'] : $article['pubdate'];
        $article['is_end']       = $do=='editarticle' ? $item['is_end'] : 0;
        $article['showtitle']    = $do=='editarticle' ? $item['showtitle'] : 1;

		$article['meta_desc']    = $do=='addarticle' ? mb_strtolower($article['title']) : $inDB->escape_string($item['meta_desc']);
		$article['meta_keys']    = $do=='addarticle' ? $inCore->getKeywords($article['content']) : $inDB->escape_string($item['meta_keys']);

        $article['showdate']     = $do=='editarticle' ? $item['showdate'] : 1;
        $article['showlatest']   = $do=='editarticle' ? $item['showlatest'] : 1;
        $article['showpath']     = $do=='editarticle' ? $item['showpath'] : 1;
        $article['comments']     = $do=='editarticle' ? $item['comments'] : 1;
        $article['canrate']      = $do=='editarticle' ? $item['canrate'] : 1;
        $article['pagetitle']    = '';
        if ($do=='editarticle'){
           $article['tpl']       = $item['tpl'];
        }

        if (mb_strlen($article['title'])<2){ cmsCore::addSessionMessage($_LANG['REQ_TITLE'], 'error'); $errors = true; }
        if (mb_strlen($article['content'])<10){ cmsCore::addSessionMessage($_LANG['REQ_CONTENT'], 'error'); $errors = true; }

		if($errors) {

			// При добавлении статьи при ошибках сохраняем введенные поля
			if ($do=='addarticle'){
				cmsUser::sessionPut('article', $article);
			}

			cmsCore::redirectBack();
		}

        $article['description']  = $inDB->escape_string($article['description']);
        $article['content']      = $inDB->escape_string($article['content']);

        $article = cmsCore::callEvent('AFTER_EDIT_ARTICLE', $article);

		// добавление статьи
        if ($do=='addarticle'){
            $article_id = $model->addArticle($article);
		}

		// загрузка фото
		$file = 'article'.(@$article_id ? $article_id : $item['id']).'.jpg';

		if (cmsCore::request('delete_image', 'int', 0)){
			@unlink(PATH."/images/photos/small/$file");
			@unlink(PATH."/images/photos/medium/$file");
		}

		// Загружаем класс загрузки фото
		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/photos/';
		$inUploadPhoto->small_size_w  = $model->config['img_small_w'];
		$inUploadPhoto->medium_size_w = $model->config['img_big_w'];
		$inUploadPhoto->thumbsqr      = $model->config['img_sqr'];
		$inUploadPhoto->is_watermark  = $model->config['watermark'];
		$inUploadPhoto->input_name    = 'picture';
		$inUploadPhoto->filename      = $file;
		// Процесс загрузки фото
		$inUploadPhoto->uploadPhoto();

		// операции после добавления/редактирования статьи
		// добавление статьи
        if ($do=='addarticle'){

			// Получаем добавленную статью
            $article = $model->getArticle($article_id);

			if (!$article['published']){

				cmsCore::addSessionMessage($_LANG['ARTICLE_PREMODER_TEXT'], 'info');

				// отсылаем уведомление администраторам
				$link = '<a href="'.$model->getArticleURL(null, $article['seolink']).'">'.$article['title'].'</a>';
				$message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_ARTICLE_SUBMIT']);
				$message = str_replace('%link%', $link, $message);

                cmsUser::sendMessageToGroup(USER_UPDATER, cmsUser::getAdminGroups(), $message);

            } else {

                //регистрируем событие
                cmsActions::log('add_article', array(
                    'object' => $article['title'],
                    'object_url' =>  $model->getArticleURL(null, $article['seolink']),
                    'object_id' =>  $article['id'],
                    'target' => $article['cat_title'],
                    'target_url' => $model->getCategoryURL(null, $article['catseolink']),
                    'target_id' =>  $article['category_id'],
                    'description' => ''
                ));

                if (IS_BILLING){
                    $category_cost = $article['cost']==='' ? false : (int)$article['cost'];
                    cmsBilling::process('content', 'add_content', $category_cost);
                }

				cmsUser::checkAwards($inUser->id);

            }

			cmsCore::addSessionMessage($_LANG['ARTICLE_SAVE'], 'info');

            cmsCore::redirect('/my.html');

        }

		// Редактирование статьи
        if ($do=='editarticle'){

            $model->updateArticle($item['id'], $article, true);

			cmsActions::updateLog('add_article', array('object' => $article['title']), $item['id']);

			if (!$article['published']){

				$link = '<a href="'.$model->getArticleURL(null, $item['seolink']).'">'.$article['title'].'</a>';
				$message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_ARTICLE_EDITED']);
				$message = str_replace('%link%', $link, $message);

				cmsUser::sendMessageToGroup(USER_UPDATER, cmsUser::getAdminGroups(), $message);

			}

			$mess = $article['published'] ? $_LANG['ARTICLE_SAVE'] : $_LANG['ARTICLE_SAVE'].' '.$_LANG['ARTICLE_PREMODER_TEXT'];
			cmsCore::addSessionMessage($mess, 'info');

            cmsCore::redirect($model->getArticleURL(null, $item['seolink']));

        }

    }
}
///////////////////////// PUBLISH ARTICLE /////////////////////////////////////////////////////////////////////////////
if ($do == 'publisharticle'){

	if (!$inUser->id){ cmsCore::error404(); }

    $article = $model->getArticle($id);
	if (!$article) { cmsCore::error404(); }

    // Редактор с правами на добавление без модерации или администраторы могут публиковать
    if (!(($article['modgrp_id'] == $inUser->group_id) && cmsUser::isUserCan('content/autoadd')) && !$inUser->is_admin) { cmsCore::error404(); }

	$inDB->setFlag('cms_content', $article['id'], 'published', 1);

	cmsCore::callEvent('ADD_ARTICLE_DONE', $article);

    if (IS_BILLING){
        $author = $inDB->get_fields('cms_users', "id='{$article['user_id']}'", '*');
        $category_cost = $article['cost']==='' ? false : (int)$article['cost'];
        cmsBilling::process('content', 'add_content', $category_cost, $author);
    }

    //регистрируем событие
    cmsActions::log('add_article', array(
           'object' => $article['title'],
		   'user_id' => $article['user_id'],
           'object_url' =>  $model->getArticleURL(null, $article['seolink']),
           'object_id' =>  $article['id'],
           'target' => $article['cat_title'],
           'target_url' => $model->getCategoryURL(null, $article['catseolink']),
           'target_id' =>  $article['cat_id'],
           'description' => ''
    ));

	$link = '<a href="'.$model->getArticleURL(null, $article['seolink']).'">'.$article['title'].'</a>';
	$message = str_replace('%link%', $link, $_LANG['MSG_ARTICLE_ACCEPTED']);
    cmsUser::sendMessage(USER_UPDATER, $article['user_id'], $message);

	cmsUser::checkAwards($article['user_id']);

    cmsCore::redirectBack();

}
///////////////////////////////////// DELETE ARTICLE ///////////////////////////////////////////////////////////////////////////////////
if ($do=='deletearticle'){

	if (!$inUser->id){ cmsCore::error404(); }

    $article = $model->getArticle($id);
	if (!$article) { cmsCore::error404(); }

    // права доступа
	$is_author = cmsUser::isUserCan('content/delete') && ($article['user_id'] == $inUser->id);
	$is_editor = ($article['modgrp_id'] == $inUser->group_id) && cmsUser::isUserCan('content/autoadd');

    if (!$is_author && !$is_editor && !$inUser->is_admin) { cmsCore::error404(); }

	if (!cmsCore::inRequest('goadd')){

		$inPage->setTitle($_LANG['ARTICLE_REMOVAL']);
		$inPage->addPathway($_LANG['ARTICLE_REMOVAL']);

		$confirm['title']              = $_LANG['ARTICLE_REMOVAL'];
		$confirm['text']               = $_LANG['ARTICLE_REMOVAL_TEXT'].' <a href="'.$model->getArticleURL(null, $article['seolink']).'">'.$article['title'].'</a>?';
		$confirm['action']             = $_SERVER['REQUEST_URI'];
		$confirm['yes_button']         = array();
		$confirm['yes_button']['type'] = 'submit';
		$confirm['yes_button']['name'] = 'goadd';
		cmsPage::initTemplate('components', 'action_confirm')->
                assign('confirm', $confirm)->
                display('action_confirm.tpl');

	} else {

		$model->deleteArticle($article['id']);

		if ($_SERVER['HTTP_REFERER'] == '/my.html' ) {

			cmsCore::addSessionMessage($_LANG['ARTICLE_DELETED'], 'info');
			cmsCore::redirectBack();

		} else {

			// если удалили как администратор или редактор и мы не авторы статьи, отсылаем сообщение автору
			if (($is_editor || $inUser->is_admin) && $article['user_id'] != $inUser->id){

				$link = '<a href="'.$model->getArticleURL(null, $article['seolink']).'">'.$article['title'].'</a>';
				$message = str_replace('%link%', $link, ($article['published'] ? $_LANG['MSG_ARTICLE_DELETED'] : $_LANG['MSG_ARTICLE_REJECTED']));
				cmsUser::sendMessage(USER_UPDATER, $article['user_id'], $message);

			} else {
				cmsCore::addSessionMessage($_LANG['ARTICLE_DELETED'], 'info');
			}

        	cmsCore::redirect($model->getCategoryURL(null, $article['catseolink']));

		}

	}

}
///////////////////////////////////// MY ARTICLES ///////////////////////////////////////////////////////////////////////////////////
if ($do=='my'){

    if (!cmsUser::isUserCan('content/add')){ cmsCore::error404(); }

    $inPage->setTitle($_LANG['MY_ARTICLES']);
	$inPage->addPathway($_LANG['USERS'], '/'.str_replace('/', '', cmsUser::PROFILE_LINK_PREFIX));
	$inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
    $inPage->addPathway($_LANG['MY_ARTICLES']);

	$perpage = 15;

	// Условия
	$model->whereUserIs($inUser->id);

	// Общее количество статей
	$total = $model->getArticlesCount(false);

	// Сортировка и разбивка на страницы
    $inDB->orderBy('con.pubdate', 'DESC');
    $inDB->limitPage($page, $perpage);

	// Получаем статьи
    $content_list = $total ?
					$model->getArticlesList(false) :
					array(); $inDB->resetConditions();

    cmsPage::initTemplate('components', 'com_content_my')->
            assign('articles', $content_list)->
            assign('total', $total)->
            assign('user_can_delete', cmsUser::isUserCan('content/delete'))->
            assign('pagebar', cmsPage::getPagebar($total, $page, $perpage, '/content/my%page%.html'))->
            display('com_content_my.tpl');

}
///////////////////////////////////// BEST ARTICLES ///////////////////////////////////////////////////////////////////////////////////
if ($do=='best'){

    $inPage->setTitle($_LANG['ARTICLES_RATING']);
    $inPage->addPathway($_LANG['ARTICLES_RATING']);

	// Только статьи, за которые можно голосовать
	$inDB->where("con.canrate = 1");

	// Сортировка и разбивка на страницы
    $inDB->orderBy('con.rating', 'DESC');
    $inDB->limitPage(1, 30);

	// Получаем статьи
    $content_list = $model->getArticlesList();

    cmsPage::initTemplate('components', 'com_content_rating')->
            assign('articles', $content_list)->
            display('com_content_rating.tpl');

}

}