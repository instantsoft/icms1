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

function blogs(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

	cmsCore::loadClass('blog');
	$inBlog = cmsBlogs::getInstance();
	$inBlog->owner = 'user';

    global $_LANG;

    $model = new cms_model_blogs();

    define('IS_BILLING', $inCore->isComponentInstalled('billing'));
    if (IS_BILLING) { cmsCore::loadClass('billing'); }

	//Получаем параметры
	$id 	   = cmsCore::request('id', 'int', 0);
	$post_id   = cmsCore::request('post_id', 'int', 0);
	$bloglink  = cmsCore::request('bloglink', 'str', '');
	$seolink   = cmsCore::request('seolink', 'str', '');
    $do = $inCore->do;
	$page      = cmsCore::request('page', 'int', 1);
	$cat_id    = cmsCore::request('cat_id', 'int', 0);
	$ownertype = cmsCore::request('ownertype', 'str', '');
	$on_moderate = cmsCore::request('on_moderate', 'int', 0);

    $pagetitle = $inCore->getComponentTitle();

	$inPage->addPathway($pagetitle, '/blogs');
	$inPage->setTitle($pagetitle);
	$inPage->setDescription($model->config['meta_desc'] ? $model->config['meta_desc'] : $pagetitle);
    $inPage->setKeywords($model->config['meta_keys'] ? $model->config['meta_keys'] : $pagetitle);
    $inPage->addHeadJsLang(array('CONFIG_BLOG','DEL_BLOG','YOU_REALY_DELETE_BLOG','NEW_CAT','RENAME_CAT','YOU_REALY_DELETE_CAT','YOU_REALY_DELETE_POST','NO_PUBLISHED'));

///////////////////////// МОЙ БЛОГ /////////////////////////////////////////////
if ($do=='my_blog'){

	if(!$inUser->id){ cmsCore::error404(); }

	$my_blog = $inBlog->getBlogByUserId($inUser->id);

    if (!$my_blog) {
		cmsCore::redirect('/blogs/createblog.html');
	} else {
		cmsCore::redirect($model->getBlogURL($my_blog['seolink']));
	}

}
///////////////////////// ПОСЛЕДНИЕ ПОСТЫ //////////////////////////////////////
if ($do=='view'){

	$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['RSS_BLOGS'].'" href="'.HOST.'/rss/blogs/all/feed.rss">');

	// кроме админов в списке только с доступом для всех
	if(!$inUser->is_admin){
		$inBlog->whereOnlyPublic();
	}

	// ограничиваем по рейтингу если надо
	if($model->config['list_min_rating']){
		$inBlog->ratingGreaterThan($model->config['list_min_rating']);
	}

	// всего постов
	$total = $inBlog->getPostsCount($inUser->is_admin);

    //устанавливаем сортировку
    $inDB->orderBy('p.pubdate', 'DESC');

    $inDB->limitPage($page, $model->config['perpage']);

	// сами посты
	$posts = $inBlog->getPosts($inUser->is_admin, $model);
	if(!$posts && $page > 1){ cmsCore::error404(); }

	cmsPage::initTemplate('components', 'com_blog_view_posts')->
            assign('pagetitle', $pagetitle)->
            assign('ownertype', $ownertype)->
            assign('total', $total)->
            assign('posts', $posts)->
            assign('pagination', cmsPage::getPagebar($total, $page, $model->config['perpage'], '/blogs/latest-%page%.html'))->
            assign('cfg', $model->config)->
            display('com_blog_view_posts.tpl');

}

////////// СОЗДАНИЕ БЛОГА //////////////////////////////////////////////////////
if ($do=='create'){

    //Проверяем авторизацию
    if (!$inUser->id){ cmsUser::goToLogin();  }

    //Если у пользователя уже есть блог, то выходим
    if ($inBlog->getUserBlogId($inUser->id)) { cmsCore::redirectBack(); }

	$inPage->addPathway($_LANG['PATH_CREATING_BLOG']);
	$inPage->setTitle($_LANG['CREATE_BLOG']);

    if (IS_BILLING){ cmsBilling::checkBalance('blogs', 'add_blog'); }

    //Показ формы создания блога
    if (!cmsCore::inRequest('goadd')){

        cmsPage::initTemplate('components', 'com_blog_create')->
                assign('is_restrictions', (!$inUser->is_admin && $model->config['min_karma']))->
                assign('cfg', $model->config)->
                display('com_blog_create.tpl');

    }

    //Сам процесс создания блога
    if (cmsCore::inRequest('goadd')){

        $title     = cmsCore::request('title', 'str');
        $allow_who = cmsCore::request('allow_who', 'str', 'all');
        $ownertype = cmsCore::request('ownertype', 'str', 'single');

        //Проверяем название
        if (mb_strlen($title)<5){
			cmsCore::addSessionMessage($_LANG['BLOG_ERR_TITLE'], 'error');
			cmsCore::redirect('/blogs/createblog.html');
		}

        //Проверяем хватает ли кармы, но только если это не админ
        if ($model->config['min_karma'] && !$inUser->is_admin){

			// если персональный блог
            if ($ownertype=='single' && ($inUser->karma < $model->config['min_karma_private'])){
				cmsCore::addSessionMessage($_LANG['BLOG_YOU_NEED'].' <a href="/users/'.$inUser->id.'/karma.html">'.$_LANG['BLOG_KARMS'].'</a> '.$_LANG['FOR_CREATE_PERSON_BLOG'].' &mdash; '.$model->config['min_karma_private'].', '.$_LANG['BLOG_HEAVING'].' &mdash; '.$inUser->karma, 'error');
				cmsCore::redirect('/blogs/createblog.html');
			}

			// если коллективный блог
            if ($ownertype=='multi' && ($inUser->karma < $model->config['min_karma_public'])){
				cmsCore::addSessionMessage($_LANG['BLOG_YOU_NEED'].' <a href="/users/'.$inUser->id.'/karma.html">'.$_LANG['BLOG_KARMS'].'</a> '.$_LANG['FOR_CREATE_TEAM_BLOG'].' &mdash; '.$model->config['min_karma_public'].', '.$_LANG['BLOG_HEAVING'].' &mdash; '.$inUser->karma, 'error');
				cmsCore::redirect('/blogs/createblog.html');
			}

        }

		//Добавляем блог в базу
		$blog_id   = $inBlog->addBlog(array('user_id'=>$inUser->id, 'title'=>$title, 'allow_who'=>$allow_who, 'ownertype'=>$ownertype, 'forall'=>1));
		$blog_link = $inDB->get_field('cms_blogs', "id='{$blog_id}'", 'seolink');
		//регистрируем событие
		cmsActions::log('add_blog', array(
			'object' => $title,
			'object_url' => $model->getBlogURL($blog_link),
			'object_id' => $blog_id,
			'target' => '',
			'target_url' => '',
			'target_id' => 0,
			'description' => ''
		));

		if (IS_BILLING){ cmsBilling::process('blogs', 'add_blog'); }

		cmsCore::addSessionMessage($_LANG['BLOG_CREATED_TEXT'], 'info');
		cmsCore::redirect($model->getBlogURL($blog_link));

    }


}
////////// НАСТРОЙКИ БЛОГА /////////////////////////////////////////////////////
if ($do=='config'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

	// получаем блог
	$blog = $inBlog->getBlog($id);
	if (!$blog) { cmsCore::error404(); }

	//Проверяем является пользователь хозяином блога или админом
    if ($blog['user_id'] != $inUser->id && !$inUser->is_admin ) { cmsCore::halt(); }

    //Если нет запроса на сохранение, показываем форму настроек блога
    if (!cmsCore::inRequest('goadd')){

        //Получаем список авторов блога
        $authors = $inBlog->getBlogAuthors($blog['id']);

        cmsPage::initTemplate('components', 'com_blog_config')->
                assign('blog', $blog)->
                assign('form_action', '/blogs/'.$blog['id'].'/editblog.html')->
                assign('authors_list', cmsUser::getAuthorsList($authors))->
                assign('users_list', cmsUser::getUsersList(false, $authors))->
                assign('is_restrictions', (!$inUser->is_admin && $model->config['min_karma']))->
                assign('cfg', $model->config)->
                assign('is_admin', $inUser->is_admin)->
                display('com_blog_config.tpl');

		cmsCore::jsonOutput(array('error' => false, 'html' => ob_get_clean()));

    }

    //Если пришел запрос на сохранение
    if (cmsCore::inRequest('goadd')){

        //Получаем настройки
        $title 	   = cmsCore::request('title', 'str');
        $allow_who = cmsCore::request('allow_who', 'str', 'all');
        $ownertype = cmsCore::request('ownertype', 'str', 'single');
        $premod	   = cmsCore::request('premod', 'int', 0);
        $forall    = cmsCore::request('forall', 'int', 1);
        $showcats  = cmsCore::request('showcats', 'int', 1);
		$authors   = cmsCore::request('authorslist', 'array_int', array());
        if($model->config['seo_user_access'] || $inUser->is_admin){
            $page_title = cmsCore::request('pagetitle', 'str', '');
            $meta_keys  = cmsCore::request('meta_keys', 'str', '');
            $meta_desc  = cmsCore::request('meta_desc', 'str', '');
        } else {
            $page_title = $meta_keys = $meta_desc = '';
        }

        //Проверяем настройки
        if (mb_strlen($title)<5) { $title = $blog['title']; }

        //Проверяем ограничения по карме (для смены типа блога)
        if ($model->config['min_karma'] && !$inUser->is_admin){

			// если персональный блог
            if ($ownertype=='single' && ($inUser->karma < $model->config['min_karma_private'])){

				cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['BLOG_YOU_NEED'].' <a href="/users/'.$inUser->id.'/karma.html">'.$_LANG['BLOG_KARMS'].'</a> '.$_LANG['FOR_CREATE_PERSON_BLOG'].' &mdash; '.$model->config['min_karma_private'].', '.$_LANG['BLOG_HEAVING'].' &mdash; '.$inUser->karma));

			}

			// если коллективный блог
            if ($ownertype=='multi' && ($inUser->karma < $model->config['min_karma_public'])){

				cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['BLOG_YOU_NEED'].' <a href="/users/'.$inUser->id.'/karma.html">'.$_LANG['BLOG_KARMS'].'</a> '.$_LANG['FOR_CREATE_TEAM_BLOG'].' &mdash; '.$model->config['min_karma_public'].', '.$_LANG['BLOG_HEAVING'].' &mdash; '.$inUser->karma));

			}

        }

		if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

		//сохраняем авторов
		$inBlog->updateBlogAuthors($blog['id'], $authors);

		//сохраняем настройки блога
		$blog['seolink_new'] = $inBlog->updateBlog($blog['id'], array(
                'title'=>$title,
                'pagetitle'=>$page_title,
                'meta_keys'=>$meta_keys,
                'meta_desc'=>$meta_desc,
                'allow_who'=>$allow_who,
                'showcats'=>$showcats,
                'ownertype'=>$ownertype,
                'premod'=>$premod,
                'forall'=>$forall
            ), $model->config['update_seo_link_blog']);

		$blog['seolink'] = $blog['seolink_new'] ? $blog['seolink_new'] : $blog['seolink'];

		if(stripslashes($title) != $blog['title']){
			// обновляем записи постов
			cmsActions::updateLog('add_post', array('target' => $title, 'target_url' => $model->getBlogURL($blog['seolink'])), 0, $blog['id']);
			// обновляем запись добавления блога
			cmsActions::updateLog('add_blog', array('object' => $title, 'object_url' => $model->getBlogURL($blog['seolink'])), $blog['id']);
		}

		cmsCore::jsonOutput(array('error' => false, 'redirect'  => $model->getBlogURL($blog['seolink'])));

    }

}
////////// СПИСОК БЛОГОВ ///////////////////////////////////////////////////////
if ($do=='view_blogs'){

	// rss в адресной строке
	$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['BLOGS'].'" href="'.HOST.'/rss/blogs/all/feed.rss">');

	// тип блога
	if($ownertype && $ownertype != 'all'){
		$inBlog->whereOwnerTypeIs($ownertype);
	}

	// всего блогов
	$total = $inBlog->getBlogsCount();

    //устанавливаем сортировку
    $inDB->orderBy('b.rating', 'DESC');

    $inDB->limitPage($page, $model->config['perpage_blog']);

    //Получаем список блогов
    $blogs = $inBlog->getBlogs($model);
	if(!$blogs && $page > 1){ cmsCore::error404(); }

    //Генерируем панель со страницами и устанавливаем заголовки страниц и глубиномера
	switch ($ownertype){
			case 'all': 	$inPage->setTitle($_LANG['ALL_BLOGS']);
                            $inPage->setDescription($_LANG['BLOGS'].' - '.$_LANG['ALL_BLOGS']);
							$inPage->addPathway($_LANG['ALL_BLOGS']);
							$link = '/blogs/all-%page%.html';
							break;
			case 'single':	$inPage->setTitle($_LANG['PERSONALS']);
                            $inPage->setDescription($_LANG['PERSONALS'].' '.$_LANG['BLOGS']);
							$inPage->addPathway($_LANG['PERSONALS']);
							$link = '/blogs/single-%page%.html';
							break;
			case 'multi':  	$inPage->setTitle($_LANG['COLLECTIVES']);
                            $inPage->setDescription($_LANG['COLLECTIVES'].' '.$_LANG['BLOGS']);
							$inPage->addPathway($_LANG['COLLECTIVES']);
							$link = '/blogs/multi-%page%.html';
							break;
	}

    if($blogs){
        foreach($blogs as $b){
            $k[] = $b['title'];
        }
        $inPage->setKeywords(implode(', ', $k));
    }

	cmsPage::initTemplate('components', 'com_blog_view_all')->
            assign('cfg', $model->config)->
            assign('total', $total)->
            assign('ownertype', $ownertype)->
            assign('blogs', $blogs)->
            assign('pagination', cmsPage::getPagebar($total, $page, $model->config['perpage_blog'], $link))->
            display('com_blog_view_all.tpl');

}
////////// ПРОСМОТР БЛОГА //////////////////////////////////////////////////////
if ($do=='blog'){

	// получаем блог
	$blog = $inBlog->getBlog($bloglink);

    // Совместимость со старыми ссылками на клубные блоги
    // Пробуем клубный блог получить по ссылке
    if (!$blog) {
        $blog_user_id = $inDB->get_field('cms_blogs', "seolink = '$bloglink' AND owner = 'club'", 'user_id');
        if($blog_user_id){
            cmsCore::redirect('/clubs/'.$blog_user_id.'_blog', '301');
        }
    }

	if (!$blog) { cmsCore::error404(); }

	// Права доступа
	$myblog = ($inUser->id && $inUser->id == $blog['user_id']); // автор блога
	$is_writer = $inBlog->isUserBlogWriter($blog, $inUser->id); // может ли пользователь писать в блог

	$inPage->addPathway($blog['title'], $model->getBlogURL($blog['seolink']));
	// rss в адресной строке
	$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.htmlspecialchars(strip_tags($blog['title'])).'" href="'.HOST.'/rss/blogs/'.$blog['id'].'/feed.rss">');
	if($myblog || $inUser->is_admin){
	    $inPage->addHeadJS('components/blogs/js/blog.js');
	}

    //Если доступа нет, возвращаемся и выводим сообщение об ошибке
	if (!cmsUser::checkUserContentAccess($blog['allow_who'], $blog['user_id'])){

		cmsCore::addSessionMessage($_LANG['CLOSED_BLOG'].'<br>'.$_LANG['CLOSED_BLOG_TEXT'], 'error');
		cmsCore::redirect('/blogs');

    }

	// Если показываем посты на модерации, если запрашиваем их
	if($on_moderate){

		if(!$inUser->is_admin && !($myblog && $blog['ownertype'] == 'multi' && $blog['premod'])){
			cmsCore::error404();
		}

		$inBlog->whereNotPublished();

		$inPage->setTitle($_LANG['POSTS_ON_MODERATE']);
		$inPage->addPathway($_LANG['POSTS_ON_MODERATE']);

		$blog['title'] .= ' - '.$_LANG['POSTS_ON_MODERATE'];

	}

	//Получаем html-код ссылки на автора с иконкой его пола
	$blog['author'] = cmsUser::getGenderLink($blog['user_id']);

	// посты данного блога
	$inBlog->whereBlogIs($blog['id']);

	// кроме админов автора в списке только с доступом для всех
	if(!$inUser->is_admin && !$myblog && !$inUser->isFriend($blog['user_id'])){
		$inBlog->whereOnlyPublic();
	}

	// если пришла категория
	if($cat_id){
		$all_total = $inBlog->getPostsCount($inUser->is_admin || $myblog);
		$inBlog->whereCatIs($cat_id);
	}

	// всего постов
	$total = $inBlog->getPostsCount($inUser->is_admin || $myblog);

    //устанавливаем сортировку
    $inDB->orderBy('p.pubdate', 'DESC');

    $inDB->limitPage($page, $model->config['perpage']);

	// сами посты
	$posts = $inBlog->getPosts(($inUser->is_admin || $myblog), $model);
	if(!$posts && $page > 1){ cmsCore::error404(); }

    //Если нужно, получаем список рубрик (категорий) этого блога
    $blogcats = $blog['showcats'] ? $inBlog->getBlogCats($blog['id']) : false;

    //Считаем количество постов, ожидающих модерации
    $on_moderate = ($inUser->is_admin || $myblog) && !$on_moderate ? $inBlog->getModerationCount($blog['id']) : false;

	// админлинки
	$blog['moderate_link'] = $model->getBlogURL($blog['seolink']).'/moderate.html';
	$blog['blog_link']     = $model->getBlogURL($blog['seolink']);
	$blog['add_post_link'] = '/blogs/'.$blog['id'].'/newpost'.($cat_id ? $cat_id : '').'.html';

	//Генерируем панель со страницами
	if ($cat_id){
		$pagination = cmsPage::getPagebar($total, $page, $model->config['perpage'], $blog['blog_link'].'/page-%page%/cat-'.$cat_id);
	} else {
		$pagination = cmsPage::getPagebar($total, $page, $model->config['perpage'], $blog['blog_link'].'/page-%page%');
	}

    // SEO
	$inPage->setTitle($blog['pagetitle'] ? $blog['pagetitle'] : $blog['title']);
	$inPage->setDescription($blog['meta_desc'] ? $blog['meta_desc'] : $blog['title']);
    // keywords
    if($blog['meta_keys']){
        $meta_keys = $blog['meta_keys'];
    } elseif($posts){
        foreach($posts as $p){
            $k[] = $p['title'];
        }
        $meta_keys = implode(', ', $k);
    } else {
        $meta_keys = $blog['title'];
    }
    $inPage->setKeywords($meta_keys);

	cmsPage::initTemplate('components', 'com_blog_view')->
            assign('myblog', $myblog)->
            assign('is_config', true)->
            assign('is_admin', $inUser->is_admin)->
            assign('is_writer', $is_writer)->
            assign('on_moderate', $on_moderate)->
            assign('cat_id', $cat_id)->
            assign('blogcats', $blogcats)->
            assign('total', $total)->
            assign('all_total', (isset($all_total) ? $all_total : 0))->
            assign('blog', $blog)->assign('posts', $posts)->
            assign('pagination', $pagination)->
            display('com_blog_view.tpl');

}

////////// НОВЫЙ ПОСТ / РЕДАКТИРОВАНИЕ ПОСТА ///////////////////////////////////
if ($do=='newpost' || $do=='editpost'){

    if (!$inUser->id){ cmsUser::goToLogin();  }

	// для редактирования сначала получаем пост
	if($do=='editpost'){
        $post = $inBlog->getPost($post_id);
        if (!$post){ cmsCore::error404(); }
		$id = $post['blog_id'];
        $post['tags'] = cmsTagLine('blogpost', $post['id'], false);
	}

	// получаем блог
	$blog = $inBlog->getBlog($id);
	if (!$blog) { cmsCore::error404(); }

    //Если доступа нет, возвращаемся и выводим сообщение об ошибке
	if (!cmsUser::checkUserContentAccess($blog['allow_who'], $blog['user_id'])){

		cmsCore::addSessionMessage($_LANG['CLOSED_BLOG'].'<br>'.$_LANG['CLOSED_BLOG_TEXT'], 'error');
		cmsCore::redirect('/blogs');

    }

	// Права доступа
	$myblog = ($inUser->id && $inUser->id == $blog['user_id']); // автор блога
	$is_writer = $inBlog->isUserBlogWriter($blog, $inUser->id); // может ли пользователь писать в блог
	// если не его блог, пользователь не писатель и не админ, вне зависимости от авторства показываем 404
    if (!$myblog && !$is_writer && !$inUser->is_admin ) { cmsCore::error404(); }
	// проверяем является ли пользователь автором, если редактируем пост
	if (($do=='editpost') && !$inUser->is_admin && $post['user_id'] != $inUser->id) { cmsCore::error404(); }

    //Если еще не было запроса на сохранение
    if (!cmsCore::inRequest('goadd')){

		$inPage->addPathway($blog['title'], $model->getBlogURL($blog['seolink']));

		//для нового поста
		if ($do=='newpost'){

			if (IS_BILLING){ cmsBilling::checkBalance('blogs', 'add_post'); }

			$inPage->addPathway($_LANG['NEW_POST']);
			$inPage->setTitle($_LANG['NEW_POST']);

			$post = cmsUser::sessionGet('mod');
			if ($post){
				cmsUser::sessionDel('mod');
			} else {
				$post['cat_id'] = $cat_id;
				$post['comments'] = 1;

			}

		}

		//для редактирования поста
		if ($do=='editpost'){

			$inPage->addPathway($post['title'], $model->getPostURL($blog['seolink'], $post['seolink']));
			$inPage->addPathway($_LANG['EDIT_POST']);
			$inPage->setTitle($_LANG['EDIT_POST']);

		}

		$inPage->initAutocomplete();
		$autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

        //получаем рубрики блога
        $cat_list = cmsCore::getListItems('cms_blog_cats', $post['cat_id'], 'id', 'ASC', "blog_id = '{$blog['id']}'");

        //получаем код панелей bbcode и смайлов
        $bb_toolbar = cmsPage::getBBCodeToolbar('message',$model->config['img_on'], 'blogs', 'blog_post', $post_id);
        $smilies    = cmsPage::getSmilesPanel('message');

        $inCore->initAutoGrowText('#message');

        //показываем форму
        cmsPage::initTemplate('components', 'com_blog_edit_post')->
                assign('blog', $blog)->
                assign('pagetitle', ($do=='editpost' ? $_LANG['EDIT_POST'] : $_LANG['NEW_POST']))->
                assign('mod', $post)->
                assign('cat_list', $cat_list)->
                assign('bb_toolbar', $bb_toolbar)->
                assign('smilies', $smilies)->
                assign('is_admin', $inUser->is_admin)->
                assign('cfg', $model->config)->
                assign('myblog', $myblog)->
                assign('user_can_iscomments', cmsUser::isUserCan('comments/iscomments'))->
                assign('autocomplete_js', $autocomplete_js)->
                display('com_blog_edit_post.tpl');

    }

    //Если есть запрос на сохранение
    if (cmsCore::inRequest('goadd')) {

        $errors = false;

        //Получаем параметры
        $mod['title'] 	  = cmsCore::request('title', 'str');
        $mod['content']   = cmsCore::request('content', 'html');
        $mod['feel'] 	  = cmsCore::request('feel', 'str', '');
        $mod['music'] 	  = cmsCore::request('music', 'str', '');
        $mod['cat_id'] 	  = cmsCore::request('cat_id', 'int');
        $mod['allow_who'] = cmsCore::request('allow_who', 'str', $blog['allow_who']);
        $mod['tags'] 	  = cmsCore::request('tags', 'str', '');
		$mod['comments']  = cmsCore::request('comments', 'int', 1);

        if($model->config['seo_user_access'] || $inUser->is_admin){
            $mod['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
            $mod['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
            $mod['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
        }

		$mod['published'] = ($myblog || !$blog['premod']) ? 1 : 0;
		$mod['blog_id']   = $blog['id'];

        //Проверяем их
        if (mb_strlen($mod['title'])<2) {  cmsCore::addSessionMessage($_LANG['POST_ERR_TITLE'], 'error'); $errors = true; }
        if (mb_strlen($mod['content'])<5) { cmsCore::addSessionMessage($_LANG['POST_ERR_TEXT'], 'error'); $errors = true; }

		// Если есть ошибки, возвращаемся назад
		if($errors){
			cmsUser::sessionPut('mod', $mod);
			cmsCore::redirectBack();
        }

        //Если нет ошибок
		//добавляем новый пост...
		if ($do=='newpost'){

			if (IS_BILLING){ cmsBilling::process('blogs', 'add_post'); }

			$mod['pubdate'] = date( 'Y-m-d H:i:s');
			$mod['user_id'] = $inUser->id;

			// добавляем пост, получая его id и seolink
			$added = $inBlog->addPost($mod);
            $mod = array_merge($mod, $added);

			if ($mod['published']) {

                $mod['seolink'] = $model->getPostURL($blog['seolink'], $mod['seolink']);

				if ($blog['allow_who'] != 'nobody' && $mod['allow_who'] != 'nobody'){

                    cmsCore::callEvent('ADD_POST_DONE', $mod);

					cmsActions::log('add_post', array(
						'object' => $mod['title'],
						'object_url' => $mod['seolink'],
						'object_id' => $mod['id'],
						'target' => $blog['title'],
						'target_url' => $model->getBlogURL($blog['seolink']),
						'target_id' => $blog['id'],
						'description' => '',
						'is_friends_only' => (int)($blog['allow_who'] == 'friends' || $mod['allow_who'] == 'friends')
					));

				}

				cmsCore::addSessionMessage($_LANG['POST_CREATED'], 'success');

				cmsCore::redirect($mod['seolink']);

			}

			if (!$mod['published']) {

				$message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_POST_SUBMIT']);
				$message = str_replace('%post%', '<a href="'.$model->getPostURL($blog['seolink'], $added['seolink']).'">'.$mod['title'].'</a>', $message);
				$message = str_replace('%blog%', '<a href="'.$model->getBlogURL($blog['seolink']).'">'.$blog['title'].'</a>', $message);

				cmsUser::sendMessage(USER_UPDATER, $blog['user_id'], $message);

				cmsCore::addSessionMessage($_LANG['POST_PREMODER_TEXT'], 'info');

				cmsCore::redirect($model->getBlogURL($blog['seolink']));

			}

		}

		//...или сохраняем пост после редактирования
		if ($do=='editpost') {

			if ($model->config['update_date']){
				$mod['pubdate'] = date( 'Y-m-d H:i:s');
			}

			$mod['edit_times'] = (int)$post['edit_times']+1;

			$new_post_seolink = $inBlog->updatePost($post['id'], $mod, $model->config['update_seo_link']);

			$post['seolink'] = is_string($new_post_seolink) ? $new_post_seolink : $post['seolink'];

			cmsActions::updateLog('add_post', array('object' => $mod['title'],
                                                    'pubdate' => $model->config['update_date'] ? $mod['pubdate'] : $post['pubdate'],
                                                    'object_url' => $model->getPostURL($blog['seolink'], $post['seolink'])), $post['id']);

			if (!$mod['published']) {

				$message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_POST_UPDATE']);
				$message = str_replace('%post%', '<a href="'.$model->getPostURL($blog['seolink'], $post['seolink']).'">'.$mod['title'].'</a>', $message);
				$message = str_replace('%blog%', '<a href="'.$model->getBlogURL($blog['seolink']).'">'.$blog['title'].'</a>', $message);

				cmsUser::sendMessage(USER_UPDATER, $blog['user_id'], $message);

				cmsCore::addSessionMessage($_LANG['POST_PREMODER_TEXT'], 'info');

			} else {
				cmsCore::addSessionMessage($_LANG['POST_UPDATED'], 'success');
			}

			cmsCore::redirect($model->getPostURL($blog['seolink'], $post['seolink']));

		}

    }

}
////////// НОВАЯ РУБРИКА / РЕДАКТИРОВАНИЕ РУБРИКИ //////////////////////////////
if ($do=='newcat' || $do=='editcat'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

    $cat = array();

	// Для редактирования сначала получаем рубрику
    if ($do=='editcat'){

        $cat = $inBlog->getBlogCategory($cat_id);
        if (!$cat) { cmsCore::halt(); }
		$id = $cat['blog_id'];

	}

	// получаем блог
	$blog = $inBlog->getBlog($id);
	if (!$blog) { cmsCore::halt(); }

	//Проверяем является пользователь хозяином блога или админом
    if ($blog['user_id'] != $inUser->id && !$inUser->is_admin ) { cmsCore::halt(); }

    //Если нет запроса на сохранение
    if (!cmsCore::inRequest('goadd')){

        cmsPage::initTemplate('components', 'com_blog_edit_cat')->
                assign('mod', $cat)->
                assign('form_action', ($do=='newcat' ? '/blogs/'.$blog['id'].'/newcat.html' : '/blogs/editcat'.$cat['id'].'.html'))->
                display('com_blog_edit_cat.tpl');

		cmsCore::jsonOutput(array('error' => false, 'html' => ob_get_clean()));

    }

    //Если есть запрос на сохранение
    if (cmsCore::inRequest('goadd')){

        $new_cat['title']       = cmsCore::request('title', 'str', '');
		$new_cat['description'] = cmsCore::request('description', 'str', '');
		$new_cat['blog_id']     = $blog['id'];
        if (mb_strlen($new_cat['title'])<3) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['CAT_ERR_TITLE'])); }

		if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

		//новая рубрика
		if ($do=='newcat'){
			$cat['id'] = $inBlog->addBlogCategory($new_cat);
			cmsCore::addSessionMessage($_LANG['CAT_IS_ADDED'], 'success');
		}
		//редактирование рубрики
		if ($do=='editcat'){
			$inBlog->updateBlogCategory($cat['id'], $new_cat);
			cmsCore::addSessionMessage($_LANG['CAT_IS_UPDATED'], 'success');
		}

		cmsCore::jsonOutput(array('error' => false, 'redirect'  => $model->getBlogURL($blog['seolink'], 1, $cat['id'])));

    }

}
///////////////////////// УДАЛЕНИЕ РУБРИКИ /////////////////////////////////////
if ($do == 'delcat'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

	$cat = $inBlog->getBlogCategory($cat_id);
	if (!$cat) { cmsCore::halt(); }

	$blog = $inBlog->getBlog($cat['blog_id']);
	if (!$blog) { cmsCore::halt(); }

    if ($blog['user_id'] != $inUser->id && !$inUser->is_admin) { cmsCore::halt(); }

	if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

	$inBlog->deleteBlogCategory($cat['id']);

	cmsCore::addSessionMessage($_LANG['CAT_IS_DELETED'], 'success');

	cmsCore::jsonOutput(array('error' => false, 'redirect'  => $model->getBlogURL($blog['seolink'])));

}
////////////////////////// ПРОСМОТР ПОСТА //////////////////////////////////////
if($do=='post'){

	$post = $inBlog->getPost($seolink);
	if (!$post){ cmsCore::error404(); }

	$blog = $inBlog->getBlog($post['blog_id']);
    // Совместимость со старыми ссылками на клубные посты блога
    if (!$blog) {
        $blog_user_id = $inDB->get_field('cms_blogs', "id = '{$post['blog_id']}' AND owner = 'club'", 'user_id');
        if($blog_user_id){
            cmsCore::redirect('/clubs/'.$blog_user_id.'_'.$post['seolink'].'.html', '301');
        }
    }

	if (!$blog) { cmsCore::error404(); }

	// Проверяем сеолинк блога и делаем редирект если он изменился
	if($bloglink != $blog['seolink']) { cmsCore::redirect($model->getPostURL($blog['seolink'], $post['seolink']), '301'); }

	// право просмотра блога
	if (!cmsUser::checkUserContentAccess($blog['allow_who'], $blog['user_id'])){

		cmsCore::addSessionMessage($_LANG['CLOSED_BLOG'].'<br>'.$_LANG['CLOSED_BLOG_TEXT'], 'error');
		cmsCore::redirect('/blogs');

    }

	// право просмотра самого поста
	if (!cmsUser::checkUserContentAccess($post['allow_who'], $post['user_id'])){

		cmsCore::addSessionMessage($_LANG['CLOSED_POST'].'<br>'.$_LANG['CLOSED_POST_TEXT'], 'error');
		cmsCore::redirect($model->getBlogURL($blog['seolink']));

    }

	if($inUser->id){
	    $inPage->addHeadJS('components/blogs/js/blog.js');
	}
	$inPage->addPathway($blog['title'], $model->getBlogURL($blog['seolink']));
    $inPage->addPathway($post['title']);

    $inPage->setTitle($post['pagetitle'] ? $post['pagetitle'] : $post['title']);
	$inPage->setDescription($post['meta_desc'] ? $post['meta_desc'] : crop($post['content_html']));
    $inPage->setKeywords($post['meta_keys'] ? $post['meta_keys'] : $post['title']);

    if ($post['cat_id']){
        $cat = $inBlog->getBlogCategory($post['cat_id']);
    }

	$post['tags'] = cmsTagBar('blogpost', $post['id']);

	$is_author = ($inUser->id && $inUser->id == $post['user_id']);

	// увеличиваем кол-во просмотров
	if(!$is_author){
	    $inDB->setFlag('cms_blog_posts', $post['id'], 'hits', $post['hits']+1);
	}

    cmsPage::initTemplate('components', 'com_blog_view_post')->
            assign('post', $post)->
            assign('blog', $blog)->assign('cat', $cat)->
            assign('is_author', $is_author)->
            assign('is_writer', $inBlog->isUserBlogWriter($blog, $inUser->id))->
            assign('myblog', ($inUser->id && $inUser->id == $blog['user_id']))->
            assign('is_admin', $inUser->is_admin)->
            assign('karma_form', cmsKarmaForm('blogpost', $post['id'], $post['rating'], $is_author))->
            assign('navigation', $inBlog->getPostNavigation($post['id'], $blog['id'], $model, $blog['seolink']))->
            display('com_blog_view_post.tpl');

    if($inCore->isComponentInstalled('comments') && $post['comments']){
        cmsCore::includeComments();
        comments('blog', $post['id'], array(), $is_author);
    }

}

///////////////////////// УДАЛЕНИЕ ПОСТА ///////////////////////////////////////
if ($do == 'delpost'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

	$post = $inBlog->getPost($post_id);
	if (!$post){ cmsCore::halt(); }

	$blog = $inBlog->getBlog($post['blog_id']);
	if (!$blog) { cmsCore::halt(); }

	$myblog = ($inUser->id == $blog['user_id']); // автор блога
	$is_writer = $inBlog->isUserBlogWriter($blog, $inUser->id);
	// если не его блог, пользователь не писатель и не админ
    if (!$myblog && !$is_writer && !$inUser->is_admin ) { cmsCore::halt(); }
	// проверяем является ли пользователь автором
	if (!$inUser->is_admin && !$myblog && $post['user_id'] != $inUser->id) { cmsCore::halt(); }

	if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

	$inBlog->deletePost($post['id']);

	if ($inUser->id != $post['user_id']){

		cmsUser::sendMessage(USER_UPDATER, $post['user_id'], $_LANG['YOUR_POST'].' <b>&laquo;'.$post['title'].'&raquo;</b> '.$_LANG['WAS_DELETED_FROM_BLOG'].' <b>&laquo;<a href="'.$model->getBlogURL($blog['seolink']).'">'.$blog['title'].'</a>&raquo;</b>');

	}

	cmsCore::addSessionMessage($_LANG['POST_IS_DELETED'], 'success');

	cmsCore::jsonOutput(array('error' => false, 'redirect'  => $model->getBlogURL($blog['seolink'])));

}
///////////////////////// ПУБЛИКАЦИЯ ПОСТА /////////////////////////////////////
if ($do == 'publishpost'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

	$post = $inBlog->getPost($post_id);
	if (!$post){ cmsCore::halt(); }

	$blog = $inBlog->getBlog($post['blog_id']);
	if (!$blog) { cmsCore::halt(); }

	// публикуют авторы блога и админы
    if ($blog['user_id'] != $inUser->id && !$inUser->is_admin) { cmsCore::halt(); }

	$inBlog->publishPost($post_id);

	$post['seolink'] = $model->getPostURL($blog['seolink'], $post['seolink']);

    if ($blog['allow_who'] == 'all' && $post['allow_who'] == 'all') { cmsCore::callEvent('ADD_POST_DONE', $post); }

	if ($blog['allow_who'] != 'nobody' && $post['allow_who'] != 'nobody'){

		cmsActions::log('add_post', array(
			'object' => $post['title'],
			'user_id' => $post['user_id'],
			'object_url' => $post['seolink'],
			'object_id' => $post['id'],
			'target' => $blog['title'],
			'target_url' => $model->getBlogURL($blog['seolink']),
			'target_id' => $blog['id'],
			'description' => '',
			'is_friends_only' => (int)($blog['allow_who'] == 'friends' || $post['allow_who'] == 'friends')
		));

	}

    cmsUser::sendMessage(USER_UPDATER, $post['user_id'], $_LANG['YOUR_POST'].' <b>&laquo;<a href="'.$post['seolink'].'">'.$post['title'].'</a>&raquo;</b> '.$_LANG['PUBLISHED_IN_BLOG'].' <b>&laquo;<a href="'.$model->getBlogURL($blog['seolink']).'">'.$blog['title'].'</a>&raquo;</b>');

    cmsCore::halt('ok');

}

///////////////////////// УДАЛЕНИЕ БЛОГА ///////////////////////////////////////
if ($do == 'delblog'){

    if(!$inUser->id) { cmsCore::error404(); }

    if(!cmsCore::isAjax()) { cmsCore::error404(); }

	// получаем блог
	$blog = $inBlog->getBlog($id);
	if (!$blog) { cmsCore::error404(); }

	//Проверяем является пользователь хозяином блога или админом
    if ($blog['user_id'] != $inUser->id && !$inUser->is_admin ) { cmsCore::halt(); }

	if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

	$inBlog->deleteBlog($blog['id']);

	cmsCore::addSessionMessage($_LANG['BLOG_IS_DELETED'], 'success');

	cmsCore::jsonOutput(array('error' => false, 'redirect'  => '/blogs'));

}

////////// VIEW POPULAR POSTS //////////////////////////////////////////////////
if ($do=='best'){

	$inPage->setTitle($_LANG['POPULAR_IN_BLOGS']);
	$inPage->addPathway($_LANG['POPULAR_IN_BLOGS']);
	$inPage->setDescription($_LANG['POPULAR_IN_BLOGS']);

	// кроме админов в списке только с доступом для всех
	if(!$inUser->is_admin){
		$inBlog->whereOnlyPublic();
	}

	// ограничиваем по рейтингу если надо
	if($model->config['list_min_rating']){
		$inBlog->ratingGreaterThan($model->config['list_min_rating']);
	}

	// всего постов
	$total = $inBlog->getPostsCount($inUser->is_admin);

    //устанавливаем сортировку
    $inDB->orderBy('p.rating', 'DESC');

    $inDB->limitPage($page, $model->config['perpage']);

	// сами посты
	$posts = $inBlog->getPosts($inUser->is_admin, $model);
	if(!$posts && $page > 1){ cmsCore::error404(); }

	cmsPage::initTemplate('components', 'com_blog_view_posts')->
            assign('pagetitle', $_LANG['POPULAR_IN_BLOGS'])->
            assign('total', $total)->
            assign('ownertype', $ownertype)->
            assign('posts', $posts)->
            assign('pagination', cmsPage::getPagebar($total, $page, $model->config['perpage'], '/blogs/popular-%page%.html'))->
            assign('cfg', $model->config)->
            display('com_blog_view_posts.tpl');

}

}