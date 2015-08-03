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

function board(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    global $_LANG;

    define('IS_BILLING', $inCore->isComponentInstalled('billing'));
    if (IS_BILLING) { cmsCore::loadClass('billing'); }

    $model = new cms_model_board();

	$do = $inCore->do;

    $pagetitle = $inCore->getComponentTitle();
    $pagekeys = $pagedesc  = '';

	$inPage->setTitle($pagetitle);
	$inPage->addPathway($pagetitle, '/board');

/////////////////////////////// VIEW CATEGORY ///////////////////////////////////////////////////////////////////////////////////////////
if ($do=='view'){

	//Получаем текущую категорию
	$category = $model->getCategory($model->category_id);
	if (!$category) { cmsCore::error404(); }

    if ($category['id'] != $model->root_cat['id']) {

		$pagetitle = $category['pagetitle'] ? $category['pagetitle'] : $category['title'];
        $pagekeys  = $category['meta_keys'];
        $pagedesc  = $category['meta_desc'];

        $category_path  = $inDB->getNsCategoryPath('cms_board_cats', $category['NSLeft'], $category['NSRight']);
		if($category_path){
			foreach($category_path as $pcat){
				$inPage->addPathway($pcat['title'], '/board/'.$pcat['id']);
			}
		}

	} else {
        $category['title'] = $pagetitle = $inCore->menuTitle();
        $category['description'] = $model->config['root_description'];
        $pagekeys = $model->config['meta_keys'];
        $pagedesc = $model->config['meta_desc'];
    }

	// rss в адресной строке
	$rss_cat_id = $category['id'] == $model->root_cat['id'] ? 'all' : $category['id'];
	$inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['BOARD'].'" href="'.HOST.'/rss/board/'.$rss_cat_id.'/feed.rss">');

	//Формируем категории
	$cats = $model->getSubCats($category['id']);

	// Формируем список объявлений
	// Устанавливаем категорию
	if ($category['id'] != $model->root_cat['id']) {
		$model->whereThisAndNestedCats($category['NSLeft'], $category['NSRight']);
	}

	//Город
	if ($model->city) {
    	$model->whereCityIs($model->city);
		$pagetitle .= ' :: '.$model->city;
	}

    // Типы объявлений
	if ($model->obtype && mb_stristr(icms_ucfirst($category['obtypes']), $model->obtype)) {
    	$model->whereTypeIs($model->obtype);
		$pagetitle .= ' :: '.$model->obtype;
	}

	// модератор или админ
	$is_moder = $inUser->is_admin || $model->is_moderator_by_group;

    // Общее количество объявлений по заданным выше условиям
    $total = $model->getAdvertsCount($is_moder, true);

    //устанавливаем сортировку
	$orderby = $model->getOrder('orderby', $category['orderby']);
	$orderto = $model->getOrder('orderto', $category['orderto']);
    $inDB->orderBy('is_vip DESC, '.$orderby, $orderto);

    //устанавливаем номер текущей страницы и кол-во объявлений на странице
    $inDB->limitPage($model->page, $category['perpage']);

	// Получаем объявления
	$items = $model->getAdverts($is_moder, true, false, true);
	// Если объявлений на странице большей чем 1 нет, 404
	if(!$items && $model->page > 1){ cmsCore::error404(); }

    // если не указаны ключевые слова, формируем их из названий рубрик и типов
    if(!$pagekeys && $cats){
        foreach($cats as $c){
            $keys[] = $c['title'];
            foreach (explode("\n", $c['obtypes']) as $obtype) {
                $keys[] = trim($obtype);
            }
        }
        $pagekeys = implode(',', $keys);
    } elseif(!$cats) {
        $pagekeys = $category['title'];
    }
    // если не указано описание, формируем из текущих объявлений
    if(!$pagedesc && $items){
        foreach($items as $i){
            $desc[] = $i['title'];
        }
        $pagedesc = implode('. ', $desc);
    } elseif(!$items && $category['description']){
        $pagedesc = crop($category['description']);
    }

	// Проставляем заголовки страницы и описание согласно выборки
	$inPage->setDescription(crop($pagedesc));
    $inPage->setKeywords($pagekeys);
	$inPage->setTitle($pagetitle);

    // Отдаем в шаблон категории
	cmsPage::initTemplate('components', 'com_board_cats')->
            assign('cats', $cats)->
            assign('category', $category)->
            assign('root_id', $model->root_cat['id'])->
            assign('is_user', $inUser->id)->
            assign('maxcols', $model->config['maxcols'])->
            display('com_board_cats.tpl');

	$pagebar = cmsPage::getPagebar($total, $model->page, $category['perpage'], '/board/%catid%-%page%', array('catid'=>$category['id']));
    $order_form = $category['orderform'] ? $model->orderForm($orderby, $orderto, $category) : '';

	// Отдаем в шаблон объявления
    cmsPage::initTemplate('components', 'com_board_items')->
            assign('order_form', $order_form)->
            assign('cfg', $model->config)->
            assign('root_id', $model->root_cat['id'])->
            assign('items', $items)->
            assign('cat', $category)->
            assign('maxcols', $category['maxcols'])->
            assign('colwidth', round(100/$category['maxcols']))->
            assign('pagebar', $pagebar)->
            display('com_board_items.tpl');

}
/////////////////////////////// VIEW USER ADV ///////////////////////////////////////////////////////////////////////////////////////
if ($do=='by_user'){

	// логин пользователя
	$login = cmsCore::request('login', 'str', ''.$inUser->login.'');
	// получаем данные пользователя
	$user = cmsUser::getShortUserData($login);
	if (!$user) { cmsCore::error404(); }

	$myprofile = $model->checkAccess($user['id']);

	$inPage->addPathway($user['nickname']);
    $inPage->setTitle($_LANG['BOARD'].' - '.$user['nickname']);
	$inPage->setDescription($_LANG['BOARD'].' - '.$user['nickname']);

	// Формируем список объявлений
	$model->whereUserIs($user['id']);

    // Общее количество объявлений по заданным выше условиям
    $total = $model->getAdvertsCount($myprofile);

    //устанавливаем сортировку
    $inDB->orderBy('pubdate', 'DESC');

    //устанавливаем номер текущей страницы и кол-во объявлений на странице
    $inDB->limitPage($model->page, 15);

	// Получаем объявления
	$items = $model->getAdverts($myprofile, true, false, true);
	// Если объявлений на странице большей чем 1 нет, 404
	if(!$items && $model->page > 1){ cmsCore::error404(); }

	// Пагинация
	$pagebar = cmsPage::getPagebar($total, $model->page, 15, '/board/by_user_'.$login.'/page-%page%');

	// Показываем даты
	$category['showdate'] = 1;

	cmsPage::initTemplate('components', 'com_board_items')->
            assign('cfg', $model->config)->
            assign('page_title', $_LANG['BOARD'].' - '.$user['nickname'])->
            assign('root_id', $model->root_cat['id'])->
            assign('items', $items)->
            assign('cat', $category)->
            assign('maxcols', 1)->
            assign('colwidth', 100)->
            assign('pagebar', $pagebar)->
            display('com_board_items.tpl');

}
/////////////////////////////// VIEW ITEM ///////////////////////////////////////////////////////////////////////////////////////////
if($do=='read'){

	// получаем объявление
	$item = $model->getRecord($model->item_id);
	if (!$item){ cmsCore::error404(); }

	// неопубликованные показываем админам, модераторам и автору
	if (!$item['published'] && !$item['moderator']) { cmsCore::error404(); }

	// для неопубликованного показываем инфо: просрочено/на модерации
	if (!$item['published']) {
		$info_text = $item['is_overdue'] ? $_LANG['ADV_IS_EXTEND'] : $_LANG['ADV_IS_MODER'];
		cmsCore::addSessionMessage($info_text, 'info');
	} else {
		// увеличиваем кол-во просмотров
		$inDB->setFlag('cms_board_items', $model->item_id, 'hits', $item['hits']+1);
	}

	// формируем заголовок и тело сообщения
	$item['title']   = $item['obtype'].' '.$item['title'];
	$item['content'] = nl2br($item['content']);
	$item['content'] = $model->config['auto_link'] ? $inCore->parseSmiles($item['content']) : $item['content'];

	$category_path = $inDB->getNsCategoryPath('cms_board_cats', $item['NSLeft'], $item['NSRight']);
	if($category_path){
		foreach($category_path as $pcat){
			$inPage->addPathway($pcat['title'], '/board/'.$pcat['id']);
		}
	}
	$inPage->addPathway($item['title']);

    $pagetitle = $item['pagetitle'] ? $item['pagetitle'] : $item['title'];
    $pagekeys  = $item['meta_keys'] ? $item['meta_keys'] : $item['title'];
    $pagedesc  = $item['meta_desc'] ? $item['meta_desc'] : $item['content'];

    $inPage->setTitle($pagetitle);
	$inPage->setDescription(crop($pagedesc));
    $inPage->setKeywords($pagekeys);

	cmsPage::initTemplate('components', 'com_board_item')->
            assign('item', $item)->
            assign('cfg', $model->config)->
            assign('user_id', $inUser->id)->
            assign('is_admin', $inUser->is_admin)->
            assign('formsdata', cmsForm::getFieldsValues($item['form_id'], $item['form_array']))->
            assign('is_moder', $model->is_moderator_by_group)->
            display('com_board_item.tpl');

}
/////////////////////////////// NEW BOARD ITEM /////////////////////////////////////////////////////////////////////////////////////////
if ($do=='additem'){

	// Получаем категории, в которые может загружать пользователь
	$catslist = $model->getPublicCats($model->category_id);
	if(!$catslist) {
		cmsCore::addSessionMessage($_LANG['YOU_CANT_ADD_ADV_ANY'], 'error');
		$inCore->redirect('/board');
	}

	$cat['is_photos'] = 1;
    $formsdata = array();
	if ($model->category_id && $model->category_id != $model->root_cat['id']) {
		$cat = $model->getCategory($model->category_id);
        $formsdata = cmsForm::getFieldsHtml($cat['form_id']);
	}

	$inPage->addPathway($_LANG['ADD_ADV']);

    if ( !cmsCore::inRequest('submit') ) {

        if (IS_BILLING) { cmsBilling::checkBalance('board', 'add_item'); }
        $inPage->setTitle($_LANG['ADD_ADV']);

		$item = cmsUser::sessionGet('item');
		if ($item) { cmsUser::sessionDel('item'); }

		$item['city'] = !empty($item['city']) ? $item['city'] : $inUser->city;

        cmsPage::initTemplate('components', 'com_board_edit')->
                assign('action', "/board/add.html")->
                assign('form_do', 'add')->
                assign('cfg', $model->config)->
                assign('cat', $cat)->
                assign('item', $item)->
                assign('pagetitle', $_LANG['ADD_ADV'])->
                assign('formsdata', $formsdata)->
                assign('is_admin', $inUser->is_admin)->
                assign('is_user', $inUser->id)->
                assign('catslist', $catslist)->
                assign('is_billing', IS_BILLING)->assign('balance', $inUser->balance)->
                display('com_board_edit.tpl');

		cmsUser::sessionClearAll();
        return;

    }

    if ( cmsCore::inRequest('submit') ) {

		// проверяем на заполненость скрытое поле
		$title_fake = cmsCore::request('title_fake', 'str', '');
		// если оно заполнено, считаем что это бот, 404
		if ($title_fake) { cmsCore::error404(); }

		$errors = false;

		// проверяем наличие категории
		if (!$cat['id']) { cmsCore::addSessionMessage($_LANG['NEED_CAT_ADV'], 'error'); $errors = true; }

		// Проверяем количество добавленных за сутки
		if (!$model->checkLoadedByUser24h($cat)){
			cmsCore::addSessionMessage($_LANG['MAX_VALUE_OF_ADD_ADV'], 'error'); $errors = true;
		}
		// Можем ли добавлять в эту рубрику
		if (!$model->checkAdd($cat)){
			cmsCore::addSessionMessage($_LANG['YOU_CANT_ADD_ADV'], 'error'); $errors = true;
		}

        // входные данные
        $obtype     = icms_ucfirst(cmsCore::request('obtype', 'str', ''));
		$title      = trim(str_ireplace($obtype, '', cmsCore::request('title', 'str', '')));
        $content 	= cmsCore::request('content', 'str', '');
        $city       = cmsCore::request('city', 'str', '');
		$pagetitle 	= cmsCore::request('pagetitle', 'str', '');
		$meta_keys 	= cmsCore::request('meta_keys', 'str', '');
		$meta_desc 	= cmsCore::request('meta_desc', 'str', '');

        $form_input = cmsForm::getFieldsInputValues($cat['form_id']);
        $formsdata  = $inDB->escape_string(cmsCore::arrayToYaml($form_input['values']));

        $vipdays    = cmsCore::request('vipdays', 'int', 0);

        $published  = $model->checkPublished($cat);

        if ($model->config['srok']){  $pubdays = (cmsCore::request('pubdays', 'int') <= 50) ? cmsCore::request('pubdays', 'int') : 50; }
        if (!$model->config['srok']){ $pubdays = isset($model->config['pubdays']) ? $model->config['pubdays'] : 14; }

		// Проверяем значения
        if (!$title) { cmsCore::addSessionMessage($_LANG['NEED_TITLE'], 'error'); $errors = true; }
        if (!$content) { cmsCore::addSessionMessage($_LANG['NEED_TEXT_ADV'], 'error'); $errors = true; }
        if (!$city) { cmsCore::addSessionMessage($_LANG['NEED_CITY'], 'error'); $errors = true; }
		if (!$inUser->id && !cmsPage::checkCaptchaCode()) { cmsCore::addSessionMessage($_LANG['ERR_CAPTCHA'], 'error'); $errors = true; }
		// Проверяем значения формы
		foreach ($form_input['errors'] as $field_error) {
			if($field_error){ cmsCore::addSessionMessage($field_error, 'error'); $errors = true; }
		}

        if ($errors){
			$item['content'] = htmlspecialchars(stripslashes($_REQUEST['content']));
			$item['city']    = stripslashes($city);
			$item['title']   = stripslashes($title);
			$item['obtype']  = $obtype;
			cmsUser::sessionPut('item', $item);
			cmsCore::redirect('/board/'.$model->category_id.'/add.html');
        }

		if($cat['is_photos']){
			// Загружаем фото
			$file = $model->uploadPhoto('', $cat);
		} else {
			$file['filename'] = '';
			cmsCore::addSessionMessage($_LANG['INFO_CAT_NO_PHOTO'], 'info');
		}

        $add = array(
            'category_id'=>$model->category_id,
            'user_id'=>$inUser->id,
            'obtype'=>$obtype,
            'title'=>$title,
            'content'=>$content,
            'formsdata'=>$formsdata,
            'city'=>$city,
            'pubdays'=>$pubdays,
            'published'=>$published,
            'pagetitle'=>((($model->config['seo_user_access'] && $inUser->id) || $inUser->is_admin) ? $pagetitle : ''),
            'meta_keys'=>((($model->config['seo_user_access'] && $inUser->id) || $inUser->is_admin) ? $meta_keys : ''),
            'meta_desc'=>((($model->config['seo_user_access'] && $inUser->id) || $inUser->is_admin) ? $meta_desc : ''),
            'file'=>$file['filename']
        );

        $add['id'] = $model->addRecord($add);

        if ($inUser->is_admin && $vipdays){
            $model->setVip($add['id'], $vipdays);
        }

        if (IS_BILLING) {
            cmsBilling::process('board', 'add_item');
            if ($model->config['vip_enabled'] && $vipdays && $model->config['vip_day_cost']){
                if ($vipdays > $model->config['vip_max_days']) { $vipdays = $model->config['vip_max_days']; }
                $summ = $vipdays * $model->config['vip_day_cost'];
                if ($inUser->balance >= $summ){
                    cmsBilling::pay($inUser->id, $summ, $_LANG['VIP_ITEM']);
                    $model->setVip($add['id'], $vipdays);
                }
            }
        }

		cmsUser::sessionClearAll();

		if ($published) {
			//регистрируем событие
			cmsActions::log('add_board', array(
						'object' => $obtype.' '.$title,
						'object_url' => '/board/read'.$add['id'].'.html',
						'object_id' => $add['id'],
						'target' => $cat['title'],
						'target_url' => '/board/'.$cat['id'],
						'target_id' => $cat['id'],
						'description' => ''
			));
			cmsCore::addSessionMessage($_LANG['ADV_IS_ADDED'], 'success');
			cmsCore::callEvent('ADD_BOARD_DONE', $add);
			cmsCore::redirect('/board/read'.$add['id'].'.html');
		}

		if (!$published) {

			$link = '<a href="/board/read'.$add['id'].'.html">'.$obtype.' '.$title.'</a>';
			if($inUser->id){
				$user = '<a href="'.cmsUser::getProfileURL($inUser->login).'">'.$inUser->nickname.'</a>';
			} else {
				$user = $_LANG['BOARD_GUEST'].', ip: '.$inUser->ip;
			}
			$message = str_replace('%user%', $user, $_LANG['MSG_ADV_SUBMIT']);
			$message = str_replace('%link%', $link, $message);
			cmsUser::sendMessage(USER_UPDATER, 1, $message);

			cmsCore::addSessionMessage($_LANG['ADV_IS_ADDED'].'<br>'.$_LANG['ADV_PREMODER_TEXT'], 'success');
			cmsCore::redirect('/board/'.$model->category_id);
		}

    }

}
/////////////////////////////// EDIT BOARD ITEM /////////////////////////////////////////////////////////////////////////////////////////
if ($do=='edititem'){

    $item = $model->getRecord($model->item_id);
    $cat  = $model->getCategory($item['category_id']);
	if (!$cat) { cmsCore::error404(); }
	if (!$item) { cmsCore::error404(); }

    $inPage->setTitle($_LANG['EDIT_ADV']);
    $inPage->addPathway($item['category'], '/board/'.$item['cat_id']);
    $inPage->addPathway($_LANG['EDIT_ADV']);

	if (!$item['moderator']){
		cmsCore::addSessionMessage($_LANG['YOU_HAVENT_ACCESS'], 'error');
		cmsCore::redirect('/board/read'.$item['id'].'.html');
	}

    $errors = false;

    if (!cmsCore::inRequest('submit')){

        cmsPage::initTemplate('components', 'com_board_edit')->
                assign('action', "/board/edit{$item['id']}.html")->
                assign('form_do', 'edit')->
                assign('cfg', $model->config)->
                assign('cat', $cat)->
                assign('item', $item)->
                assign('pagetitle', $_LANG['EDIT_ADV'])->
                assign('is_admin', $inUser->is_admin)->
                assign('catslist', $model->getPublicCats($item['category_id'], true))->
                assign('formsdata', cmsForm::getFieldsHtml($cat['form_id'], $item['form_array']))->
                assign('is_user', $inUser->id)->
                assign('is_billing', IS_BILLING)->assign('balance', $inUser->balance)->
                display('com_board_edit.tpl');

		cmsUser::sessionClearAll();

    }

    if (cmsCore::inRequest('submit')){

        $new_cat_id = cmsCore::request('category_id', 'int', 0);
        if ($new_cat_id){ $item['category_id'] = $new_cat_id; }

		$form_input = cmsForm::getFieldsInputValues($cat['form_id']);
		$formsdata  = $inDB->escape_string(cmsCore::arrayToYaml($form_input['values']));

		if ($item['is_overdue'] && !$item['published']) {
			if ($model->config['srok']){
				$pubdays = (cmsCore::request('pubdays', 'int') <= 50) ? cmsCore::request('pubdays', 'int') : 50;
			}
        	if (!$model->config['srok']){
				$pubdays = isset($model->config['pubdays']) ? $model->config['pubdays'] : 14;
			}
			$pubdate = date("Y-m-d H:i:s");
		} else {
			$pubdays = $item['pubdays'];
			$pubdate = $item['fpubdate'];
		}

        $update['obtype']      = icms_ucfirst(cmsCore::request('obtype', 'str'));
        $update['title']       = trim(str_ireplace($update['obtype'], '', cmsCore::request('title', 'str', '')));
        $update['category_id'] = $item['category_id'];
        $update['content']     = cmsCore::request('content', 'str', '');
        $update['formsdata']   = $formsdata;
        $update['city']        = cmsCore::request('city', 'str', '');
        $update['pubdate']     = $pubdate;
        $update['pubdays']     = $pubdays;
        $update['published']   = $model->checkPublished($cat, true);
        if(($model->config['seo_user_access'] && $inUser->id) || $inUser->is_admin){
            $update['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
            $update['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
            $update['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
        }

        if (!$update['title']) { cmsCore::addSessionMessage($_LANG['NEED_TITLE'], 'error'); $errors = true; }
        if (!$update['content']) { cmsCore::addSessionMessage($_LANG['NEED_TEXT_ADV'], 'error'); $errors = true; }
        if (!$update['city'])    { cmsCore::addSessionMessage($_LANG['NEED_CITY'], 'error'); $errors = true; }

		// Проверяем значения формы
		foreach ($form_input['errors'] as $field_error) {
			if($field_error){ cmsCore::addSessionMessage($field_error, 'error'); $errors = true; }
		}

		if ($errors){ $inCore->redirect('/board/edit'.$item['id'].'.html'); }

		if($cat['is_photos']){
			// Загружаем фото
			$file = $model->uploadPhoto($item['file'], $cat);
		}

		$update['file'] = $file['filename'] ? $file['filename'] : $item['file'];

		// обновляем объявление
        $model->updateRecord($item['id'], $update);
		// обновляем запись в ленте активности
		cmsActions::updateLog('add_board', array('object' => $update['obtype'].' '.$update['title']), $item['id']);

        $vipdays = cmsCore::request('vipdays', 'int', 0);

        if ($inUser->is_admin){
            if($vipdays>0){
                $model->setVip($item['id'], $vipdays);
            }
            if($vipdays == -1){
                $model->deleteVip($item['id']);
            }
        }

        if (IS_BILLING) {
            if ($model->config['vip_enabled'] && $model->config['vip_prolong'] && $vipdays && $model->config['vip_day_cost']){
                if ($vipdays > $model->config['vip_max_days']) { $vipdays = $model->config['vip_max_days']; }
                $summ = $vipdays * $model->config['vip_day_cost'];
                if ($inUser->balance >= $summ){
                    cmsBilling::pay($inUser->id, $summ, $_LANG['VIP_ITEM']);
                    $model->setVip($item['id'], $vipdays);
                }
            }
        }

		cmsUser::sessionClearAll();

		if (!$update['published']) {

			$link = '<a href="/board/read'.$item['id'].'.html">'.$update['obtype'].' '.$update['title'].'</a>';
			$user = '<a href="'.cmsUser::getProfileURL($inUser->login).'">'.$inUser->nickname.'</a>';

			$message = str_replace(array('%link%','%user%'), array($link,$user), $_LANG['MSG_ADV_EDITED']);
			cmsUser::sendMessage(USER_UPDATER, 1, $message);

			cmsCore::addSessionMessage($_LANG['ADV_EDIT_PREMODER_TEXT'], 'info');

		}

		cmsCore::addSessionMessage($_LANG['ADV_MODIFIED'], 'success');
		cmsCore::redirect('/board/read'.$item['id'].'.html');

    }
}
///////////////////////// PUBLISH BOARD ITEM /////////////////////////////////////////////////////////////////////////////
if ($do == 'publish'){

	$item = $model->getRecord($model->item_id);
    if (!$item){ cmsCore::error404(); }

	// если уже опубликовано, 404
	if ($item['published']) { cmsCore::error404(); }

	// публиковать могут админы и модераторы доски
	if(!$inUser->is_admin && !$model->is_moderator_by_group) { cmsCore::error404(); }

	// публикуем
    $inDB->setFlag('cms_board_items', $model->item_id, 'published', 1);

	cmsCore::callEvent('ADD_BOARD_DONE', $item);

 	if($item['user_id']){
		//регистрируем событие
		cmsActions::log('add_board', array(
					'object' => $item['obtype'].' '.$item['title'],
					'user_id' => $item['user_id'],
					'object_url' => '/board/read'.$item['id'].'.html',
					'object_id' => $item['id'],
					'target' => $item['category'],
					'target_url' => '/board/'.$item['cat_id'],
					'target_id' => $item['cat_id'],
					'description' => ''
		));

		$link = '<a href="/board/read'.$item['id'].'.html">'.$item['obtype'].' '.$item['title'].'</a>';
		$message = str_replace('%link%', $link, $_LANG['MSG_ADV_ACCEPTED']);
		cmsUser::sendMessage(USER_UPDATER, $item['user_id'], $message);
	}

	cmsCore::addSessionMessage($_LANG['ADV_IS_ACCEPTED'], 'success');

    cmsCore::redirect('/board/read'.$item['id'].'.html');

}
/////////////////////////////// DELETE BOARD ITEM /////////////////////////////////////////////////////////////////////////////////////////
if ($do == 'delete'){

	$item = $model->getRecord($model->item_id);
    if (!$item){ cmsCore::error404(); }

	if (!$item['moderator']){
		cmsCore::addSessionMessage($_LANG['YOU_HAVENT_ACCESS'], 'error');
		cmsCore::redirect('/board/'.$item['cat_id']);
	}

	if (!cmsCore::inRequest('godelete')){

		$inPage->setTitle($_LANG['DELETE_ADV']);
		$inPage->addPathway($item['category'], '/board/'.$item['cat_id']);
		$inPage->addPathway($_LANG['DELETE_ADV']);

		$confirm['title']               = $_LANG['DELETING_ADV'];
		$confirm['text']                = $_LANG['YOU_SURE_DELETE_ADV'].' "'.$item['title'].'"?';
		$confirm['action']              = $_SERVER['REQUEST_URI'];
		$confirm['yes_button']['name']  = 'godelete';

		cmsPage::initTemplate('components', 'action_confirm')->
                assign('confirm', $confirm)->
                display('action_confirm.tpl');
	}

	if (cmsCore::inRequest('godelete')){
		$model->deleteRecord($model->item_id);
		cmsCore::addSessionMessage($_LANG['ADV_IS_DELETED'], 'success');
		cmsCore::redirect('/board/'.$item['cat_id']);
	}

}

}