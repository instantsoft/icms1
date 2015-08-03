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

function photos(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

	cmsCore::loadClass('photo');
	$inPhoto = cmsPhoto::getInstance();

    global $_LANG;

    $model = new cms_model_photos();

	$pagetitle = $inCore->menuTitle();

	$root_album_id = $inDB->getNsRootCatId('cms_photo_albums');

	$id   = cmsCore::request('id', 'int', $root_album_id);
	$do   = $inCore->do;
	$page = cmsCore::request('page', 'int', 1);

	$inPage->addPathway($inCore->getComponentTitle(), '/photos');

	// только авторизованные пользуются js
	if($inUser->id){
		$inPage->addHeadJS('components/photos/js/photos.js');
        $inPage->addHeadJsLang(array('NO_PUBLISH','EDIT_PHOTO','YOU_REALLY_DELETE_PHOTO','MOVE_PHOTO'));
	}

	$inPage->setDescription($model->config['meta_desc'] ? $model->config['meta_desc'] : $pagetitle);
    $inPage->setKeywords($model->config['meta_keys'] ? $model->config['meta_keys'] : $pagetitle);

/////////////////////////////// Просмотр альбома ///////////////////////////////////////////////////////////////////////////////////////////
if ($do=='view'){

	$album = $inDB->getNsCategory('cms_photo_albums', $id, null);

    if (!$album && $inCore->menuId() !== 1) { cmsCore::error404(); }
	// Неопубликованные альбомы показываем только админам
	if (!$album['published'] && !$inUser->is_admin) { cmsCore::error404(); }

	$album = cmsCore::callEvent('GET_PHOTO_ALBUM', $album);

	// если не корневой альбом
	if($album['id'] != $root_album_id){

		$path_list = $inDB->getNsCategoryPath('cms_photo_albums', $album['NSLeft'], $album['NSRight'], 'id, title, NSLevel');

		if ($path_list){
			foreach($path_list as $pcat){
				$inPage->addPathway($pcat['title'], '/photos/'.$pcat['id']);
			}
		}

		$pagetitle = ($pagetitle && $inCore->isMenuIdStrict()) ? $pagetitle : ($album['title']  . ' - '.$_LANG['PHOTOGALLERY']);
		$inPage->setTitle($album['pagetitle'] ? $album['pagetitle'] : $pagetitle);

	} else {
		$pagetitle = ($pagetitle && $inCore->isMenuIdStrict()) ? $pagetitle : $_LANG['PHOTOGALLERY'];
		$inPage->setTitle($pagetitle);
		$album['title'] = $pagetitle;
	}

	//Формируем подкатегории альбома
	$inDB->orderBy('f.'.$model->config['orderby'], $model->config['orderto']);
    $subcats = $inPhoto->getAlbums($album['id']);

	// Сортировка фотографий
	$orderby = cmsCore::getSearchVar('orderby', $album['orderby']);
	$orderto = cmsCore::getSearchVar('orderto', $album['orderto']);
    if(!in_array($orderby, array('title','pubdate','rating','hits'))){
        $orderby = $album['orderby'];
    }
    if(!in_array($orderto, array('desc','asc'))){
        $orderto = $album['orderto'];
    }

	// Устанавливаем альбом
	$inPhoto->whereAlbumIs($album['id']);

    // Общее количество фото по заданным выше условиям
    $total = $inPhoto->getPhotosCount($inUser->is_admin);

    //устанавливаем сортировку
    $inDB->orderBy('f.'.$orderby, $orderto);

    //устанавливаем номер текущей страницы и кол-во фото на странице
    $inDB->limitPage($page, $album['perpage']);

	$photos = $inPhoto->getPhotos($inUser->is_admin, $album['showdate']);
	if(!$photos && $page > 1){ cmsCore::error404(); }

	if($album['id'] != $root_album_id){

        // meta description
        if($album['meta_desc']){
            $meta_desc = $album['meta_desc'];
        } elseif(mb_strlen(strip_tags($album['description']))>=250){
            $meta_desc = crop($album['description']);
        } else {
            $meta_desc = $album['title'];
        }
		$inPage->setDescription($meta_desc);
        // meta keywords
        if($album['meta_keys']){
            $meta_keys = $album['meta_keys'];
        } elseif($photos){
            foreach($photos as $p){
                $k[] = $p['title'];
            }
            $meta_keys = implode(', ', $k);
        } else {
            $meta_keys = $album['title'];
        }
		$inPage->setKeywords($meta_keys);

	}

	cmsPage::initTemplate('components', 'com_photos_view')->
            assign('root_album_id', $root_album_id)->
            assign('cfg', $model->config)->
            assign('album', $album)->
            assign('can_add_photo', (($album['public'] && $inUser->id) || $inUser->is_admin))->
            assign('subcats', $subcats)->
            assign('photos', $photos)->
            assign('pagebar', cmsPage::getPagebar($total, $page, $album['perpage'], '/photos/'.$album['id'].'-%page%'))->
            assign('total', $total)->
            assign('orderby', $orderby)->
            assign('orderto', $orderto)->
            display('com_photos_view.tpl');

	// если есть фотограйии в альбоме и включены комментарии в альбоме, то показываем их
	if($album['is_comments'] && $photos && $inCore->isComponentInstalled('comments')){
          cmsCore::includeComments();
          comments('palbum', $album['id']);
     }

}
/////////////////////////////// VIEW PHOTO ///////////////////////////////////////////////////////////////////////////////////////////
if($do=='viewphoto'){

	// получаем фото
	$photo = cmsCore::callEvent('GET_PHOTO', $inPhoto->getPhoto($id));
	if (!$photo) { cmsCore::error404(); }

	// Если фото клуба редиректим на новый алрес
	if (mb_strstr($photo['NSDiffer'],'club')) { cmsCore::redirect('/clubs/photo'.$photo['id'].'.html', '301'); }

	$is_author = (($photo['user_id'] == $inUser->id) && $inUser->id);

	// неопубликованное фото видно админам и автору
	if (!$photo['published'] && !$inUser->is_admin && !$is_author) { cmsCore::error404(); }

	$path_list = $inDB->getNsCategoryPath('cms_photo_albums', $photo['NSLeft'], $photo['NSRight'], 'id, title, NSLevel');

	if ($path_list){
		foreach($path_list as $pcat){
			$inPage->addPathway($pcat['title'], '/photos/'.$pcat['id']);
		}
	}

	$inPage->addPathway($photo['title']);
	$inPage->setTitle($photo['pagetitle'] ? $photo['pagetitle'] : $photo['title']);
    $inPage->setKeywords($photo['meta_keys'] ? $photo['meta_keys'] : $photo['title']);
    if(!$photo['meta_desc']){
        if($photo['description']){
            $inPage->setDescription(crop($photo['description']));
        } else {
            $inPage->setDescription($photo['title']);
        }
    } else {
        $inPage->setDescription($photo['meta_desc']);
    }

	// Обновляем количество просмотров фотографии
	if(!$is_author){
		$inDB->setFlag('cms_photo_files', $photo['id'], 'hits', $photo['hits']+1);
	}

	//навигация
	if($photo['album_nav']){
		$nextid = $inDB->get_fields('cms_photo_files', 'id<'.$photo['id'].' AND album_id = '.$photo['album_id'].' AND published=1', 'id, file', 'id DESC');
		$previd = $inDB->get_fields('cms_photo_files', 'id>'.$photo['id'].' AND album_id = '.$photo['album_id'].' AND published=1', 'id, file', 'id ASC');
	} else {
		$previd = false;
		$nextid = false;
	}

	$photo['karma_buttons'] = cmsKarmaButtons('photo', $photo['id'], $photo['rating'], $is_author);

	$photo['genderlink'] = cmsUser::getGenderLink($photo['user_id'], $photo['nickname'], $photo['gender'], $photo['login']);

	cmsPage::initTemplate('components', 'com_photos_view_photo')->
            assign('photo', $photo)->
            assign('bbcode', '[IMG]'.HOST.'/images/photos/medium/'.$photo['file'].'[/IMG]')->
            assign('previd', $previd)->
            assign('nextid', $nextid)->
            assign('cfg', $model->config)->
            assign('is_author', $is_author)->
            assign('is_admin', $inUser->is_admin)->
            assign('tagbar', ($photo['a_tags'] ? cmsTagBar('photo', $photo['id']) : ''))->
            display('com_photos_view_photo.tpl');
	//выводим комментарии, если они разрешены и фото опубликовано
	if($photo['comments'] && $inCore->isComponentInstalled('comments')){
		cmsCore::includeComments();
		comments('photo', $photo['id'], array(), $is_author);
	}

}
/////////////////////////////// PHOTO UPLOAD  ////////////////////////////////////////////////////////////////////////////////
if ($do=='addphoto'){

	// Неавторизованных просим авторизоваться
	if (!$inUser->id) { cmsUser::goToLogin(); }

	$do_photo = cmsCore::request('do_photo', 'str', 'addphoto');

	// получаем альбом
	$album = $inDB->getNsCategory('cms_photo_albums', $id);
    if (!$album) { cmsCore::error404(); }
	if (!$album['published'] && !$inUser->is_admin) { cmsCore::error404(); }
	$album = cmsCore::callEvent('GET_PHOTO_ALBUM', $album);

	// права доступа
	// загружаем только в разрешенные альбомы
	if (!$album['public'] && !$inUser->is_admin){ cmsCore::error404(); }
	// Смотрим ограничения загрузки в сутки
	$today_uploaded = $album['uplimit'] ? $model->loadedByUser24h($inUser->id, $album['id']) : 0;
	if (!$inUser->is_admin && $album['uplimit'] && $today_uploaded >= $album['uplimit']){

		cmsCore::addSessionMessage('<strong>'.$_LANG['MAX_UPLOAD_IN_DAY'].'</strong> '.$_LANG['CAN_UPLOAD_TOMORROW'], 'error');
		cmsCore::redirectBack();

	}

	// глубиномер
	$path_list = $inDB->getNsCategoryPath('cms_photo_albums', $album['NSLeft'], $album['NSRight'], 'id, title, NSLevel');
	if ($path_list){
		foreach($path_list as $pcat){
			$inPage->addPathway($pcat['title'], '/photos/'.$pcat['id']);
		}
	}

	include 'components/photos/add_photo.php';

}

/////////////////////////////// PHOTO EDIT ///////////////////////////////////////////////////////////////////////////////////////////
if ($do=='editphoto'){

	if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id) { cmsCore::halt(); }

	// получаем фото
	$photo = cmsCore::callEvent('GET_PHOTO', $inPhoto->getPhoto($id));
	if (!$photo) { cmsCore::halt(); }

	if (mb_strstr($photo['NSDiffer'],'club')) { cmsCore::halt(); }

	$is_author = (($photo['user_id'] == $inUser->id) && $inUser->id);

	if (!$inUser->is_admin && !$is_author) { cmsCore::halt(); }

    if (cmsCore::inRequest('edit_photo')){

		$mod['title']       = cmsCore::request('title', 'str', '');
		$mod['title']       = $mod['title'] ? $mod['title'] : $photo['title'];
		$mod['description'] = cmsCore::request('description', 'str', '');
		$mod['tags']        = cmsCore::request('tags', 'str', '');
		$mod['comments']    = $inUser->is_admin ? cmsCore::request('comments', 'int') : $photo['comments'];
        if($model->config['seo_user_access'] || $inUser->is_admin){
            $mod['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
            $mod['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
            $mod['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
        }

		$file = $model->initUploadClass($inDB->getNsCategory('cms_photo_albums', $photo['album_id']))->uploadPhoto($photo['file']);
		$mod['file'] = $file['filename'] ? $file['filename'] : $photo['file'];

		$inPhoto->updatePhoto($mod, $photo['id']);

		$description = '<a href="/photos/photo'.$photo['id'].'.html" class="act_photo"><img src="/images/photos/small/'.$mod['file'].'" alt="'.htmlspecialchars(stripslashes($mod['title'])).'" /></a>';

		cmsActions::updateLog('add_photo', array('object' => $mod['title'], 'description' => $description), $photo['id']);

		cmsCore::addSessionMessage($_LANG['PHOTO_SAVED'], 'success');

		cmsCore::jsonOutput(array('error' => false, 'redirect' => '/photos/photo'.$photo['id'].'.html'));

	} else {

		$photo['tags'] = cmsTagLine('photo', $photo['id'], false);

		cmsPage::initTemplate('components', 'com_photos_edit')->
                assign('photo', $photo)->
                assign('form_action', '/photos/editphoto'.$photo['id'].'.html')->
                assign('no_tags', false)->
                assign('is_admin', $inUser->is_admin)->
                assign('cfg', $model->config)->
                display('com_photos_edit.tpl');

		cmsCore::jsonOutput(array('error' => false, 'html' => ob_get_clean()));

	}

}
/////////////////////////////// PHOTO MOVE /////////////////////////////////////////////////////////////////////////////////////////
if ($do=='movephoto'){

	if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id) { cmsCore::halt(); }

	$photo = cmsCore::callEvent('GET_PHOTO', $inPhoto->getPhoto($id));
	if (!$photo) { cmsCore::halt(); }

	if (mb_strstr($photo['NSDiffer'],'club')) { cmsCore::halt(); }

	if (!$inUser->is_admin) { cmsCore::halt(); }

	if (!cmsCore::inRequest('move_photo')){

		cmsPage::initTemplate('components', 'com_photos_move')->
                assign('form_action', '/photos/movephoto'.$photo['id'].'.html')->
                assign('html', $inPhoto->getAlbumsOption('', $photo['album_id']))->
                display('com_photos_move.tpl');

		cmsCore::jsonOutput(array('error' => false, 'html' => ob_get_clean()));

	} else {

		$album = cmsCore::callEvent('GET_PHOTO_ALBUM', $inDB->getNsCategory('cms_photo_albums', cmsCore::request('album_id', 'int')));
		if (!$album) { cmsCore::halt(); }

		if (!$album['public'] && !$inUser->is_admin){ cmsCore::error404(); }
		// Смотрим ограничения загрузки в сутки
		$today_uploaded = $album['uplimit'] ? $model->loadedByUser24h($inUser->id, $album['id']) : 0;
		if (!$inUser->is_admin && $album['uplimit'] && $today_uploaded >= $album['uplimit']){

			cmsCore::jsonOutput(array('error' => true, 'text' => '<strong>'.$_LANG['MAX_UPLOAD_IN_DAY'].'</strong> '.$_LANG['CAN_UPLOAD_TOMORROW']));

		}

		$inDB->query("UPDATE cms_photo_files SET album_id = '{$album['id']}' WHERE id = '{$photo['id']}'");

		cmsActions::updateLog('add_photo', array('target' => $album['title'], 'target_url' => '/photos/'.$album['id'], 'target_id' => $album['id']), $photo['id']);

		cmsCore::addSessionMessage($_LANG['PHOTO_MOVED'], 'info');

		cmsCore::jsonOutput(array('error' => false, 'redirect' => '/photos/'.$album['id']));

	}

}
/////////////////////////////// PHOTO DELETE /////////////////////////////////////////////////////////////////////////////////////////
if ($do=='delphoto'){

	if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id) { cmsCore::halt(); }

	if(!cmsCore::validateForm()) { cmsCore::halt(); }

	$photo = cmsCore::callEvent('GET_PHOTO', $inPhoto->getPhoto($id));
	if (!$photo) { cmsCore::halt(); }

	if (mb_strstr($photo['NSDiffer'],'club')) { cmsCore::halt(); }

	$is_author = (($photo['user_id'] == $inUser->id) && $inUser->id);

	if (!$inUser->is_admin && !$is_author) { cmsCore::halt(); }

	$inPhoto->deletePhoto($photo, $model->initUploadClass($inDB->getNsCategory('cms_photo_albums', $photo['album_id'])));

	cmsCore::addSessionMessage($_LANG['PHOTO_DELETED'], 'success');

    cmsUser::clearCsrfToken();

	cmsCore::jsonOutput(array('error' => false, 'redirect' => '/photos/'.$photo['album_id']));

}
/////////////////////////////// PHOTO PUBLISH /////////////////////////////////////////////////////////////////////////////////////////
if ($do=='publish_photo'){

	if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { cmsCore::halt(); }

	if (!$inUser->id) { cmsCore::halt(); }

	$photo = cmsCore::callEvent('GET_PHOTO', $inPhoto->getPhoto($id));
	if (!$photo) { cmsCore::halt(); }

	if (!$inUser->is_admin) { cmsCore::halt(); }

	$inPhoto->publishPhoto($photo['id']);

    cmsCore::callEvent('ADD_PHOTO_DONE', $photo);

	$description = '<a href="/photos/photo'.$photo['id'].'.html" class="act_photo"><img src="/images/photos/small/'.$photo['file'].'" alt="'.htmlspecialchars(stripslashes($photo['title'])).'" /></a>';

	cmsActions::log('add_photo', array(
		  'object' => $photo['title'],
		  'object_url' => '/photos/photo'.$photo['id'].'.html',
		  'object_id' => $photo['id'],
          'user_id' => $photo['user_id'],
		  'target' => $photo['cat_title'],
		  'target_id' => $photo['album_id'],
		  'target_url' => '/photos/'.$photo['album_id'],
		  'description' => $description
	));

	cmsCore::halt('ok');

}
/////////////////////////////// VIEW LATEST/BEST PHOTOS //////////////////////////////////////////////////////////////////////////////
if (in_array($do, array('latest', 'best'))){

	if($do=='latest'){
    	$inDB->orderBy('f.pubdate', 'DESC');
		$pagetitle = ($pagetitle && $inCore->isMenuIdStrict()) ? $pagetitle : $_LANG['NEW_PHOTO_IN_GALLERY'];
	} else {
		$inDB->orderBy('f.rating', 'DESC');
		$pagetitle = ($pagetitle && $inCore->isMenuIdStrict()) ? $pagetitle : $_LANG['BEST_PHOTOS'];
	}

    $inDB->limit($model->config['best_latest_perpage']);

	// выбираем категории фото
    $inDB->addJoin("INNER JOIN cms_photo_albums a ON a.id = f.album_id AND a.published = 1 AND a.NSDiffer = ''");
    $inDB->addSelect('a.title as cat_title');

	$photos = $inPhoto->getPhotos(false, 'with_comments');
	if (!$photos) { cmsCore::error404(); }

	$inPage->addPathway($pagetitle);
	$inPage->setTitle($pagetitle);

	cmsPage::initTemplate('components', 'com_photos_bl')->
            assign('maxcols', $model->config['best_latest_maxcols'])->
            assign('pagetitle', $pagetitle)->
            assign('photos', $photos)->
            display('com_photos_bl.tpl');

}
/////////////////////////////// /////////////////////////////// ////////////////

}