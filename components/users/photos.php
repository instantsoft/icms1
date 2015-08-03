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

/////////////////////////////// PHOTO UPLOAD /////////////////////////////////////////////////////////////////////////////////////////
if ($pdo=='addphoto'){

    if (!$inUser->id) { cmsUser::goToLogin(); }

	$uload_type = cmsCore::request('uload_type', 'str', 'multi');

	$photos = $model->getUploadedPhotos($inUser->id);
	$total_no_pub = $photos ? sizeof($photos) : 0; unset($photos);

	$photo_count = $model->getUserPhotoCount($inUser->id);

    if($model->config['photosize']>0 && !$inUser->is_admin) {
        $max_limit = true;
        $max_files  = $model->config['photosize'] - $photo_count;
		$stop_photo = $photo_count >= $model->config['photosize'];
    } else {
        $max_limit = false;
        $max_files = 0;
		$stop_photo = false;
    }

    $inPage->setTitle($_LANG['ADD_PHOTOS']);
    $inPage->addPathway($inUser->nickname, cmsUser::getProfileURL($inUser->login));
	$inPage->addPathway($_LANG['PHOTOALBUMS'], '/users/'.$inUser->id.'/photoalbum.html');
    $inPage->addPathway($_LANG['ADD_PHOTOS']);

    cmsPage::initTemplate('components', 'com_users_photo_add')->
            assign('user_id', $inUser->id)->
            assign('user_login', $inUser->login)->
            assign('total_no_pub', $total_no_pub)->
            assign('sess_id', session_id())->
            assign('max_limit', $max_limit)->
            assign('max_files', $max_files)->
            assign('uload_type', $uload_type)->
            assign('stop_photo', $stop_photo)->
            display('com_users_photo_add.tpl');

}

if ($pdo=='uploadphotos'){

    if (!$_FILES['Filedata']['name']) { cmsCore::error404(); }

    // Code for Session Cookie workaround
	if (cmsCore::inRequest("PHPSESSID")) {
        $sess_id = cmsCore::request("PHPSESSID", 'str');
        if ($sess_id != session_id()) { session_destroy(); }
        session_id($sess_id);
        session_start();
	}

    $user_id = $_SESSION['user']['id'];

    if (!$user_id) { header("HTTP/1.1 500 Internal Server Error"); exit(0); }
	if (($model->config['photosize']>0) && ($model->getUserPhotoCount($user_id) >= $model->config['photosize']) && !$inUser->is_admin) {
        header("HTTP/1.1 500 Internal Server Error"); exit(0);
    }

    cmsCore::includeGraphics();

    $uploaddir 				= PATH.'/images/users/photos/';
    $realfile 				= $inDB->escape_string($_FILES['Filedata']['name']);

	$path_parts             = pathinfo($realfile);
    $ext                    = mb_strtolower($path_parts['extension']);
	if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png' && $ext != 'bmp') {  exit(0); }

    $lid 					= $inDB->get_fields('cms_user_photos', 'id>0', 'id', 'id DESC');
    $lastid 				= $lid['id']+1;
    $filename 				= md5($lastid.$realfile).'.jpg';

    $uploadphoto 			= $uploaddir . $filename;
    $uploadthumb['small'] 	= $uploaddir . 'small/' . $filename;
    $uploadthumb['medium']	= $uploaddir . 'medium/' . $filename;

    $source					= $_FILES['Filedata']['tmp_name'];
    $errorCode				= $_FILES['Filedata']['error'];

    if ($inCore->moveUploadedFile($source, $uploadphoto, $errorCode)) {

        @img_resize($uploadphoto, $uploadthumb['small'], 96, 96, true);
        @img_resize($uploadphoto, $uploadthumb['medium'], 600, 600, false, false);
		if ($model->config['watermark']) { @img_add_watermark($uploadthumb['medium']); }
		@unlink($uploadphoto);

        $model->addUploadedPhoto($user_id, array('filename'=>$realfile, 'imageurl'=>$filename));
		if (cmsCore::inRequest('upload')) { cmsCore::redirect('/users/'.$inUser->login.'/photos/submit'); }

    } else {

        header("HTTP/1.1 500 Internal Server Error");
        echo cmsCore::uploadError();

    }

    exit(0);

}

if ($pdo=='submitphotos'){

    if (!$inUser->id) { cmsCore::error404(); }

    $usr = cmsUser::getShortUserData($login);
    if (!$usr){ cmsCore::error404(); }

    if ($usr['id'] != $inUser->id && !$inUser->is_admin) { cmsCore::error404(); }

    $photos = $model->getUploadedPhotos($usr['id']);
    if (!$photos) { cmsCore::error404(); }

    cmsCore::loadLanguage('components/photos');

    if (!cmsCore::inRequest('submit')){

		$p = end($photos);
		$album_id = $p['album_id'];

        $albums = $model->getPhotoAlbums($usr['id'], true, true);

        $inPage->setTitle($_LANG['PHOTOS_CONFIG']);
        $inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
		$inPage->addPathway($_LANG['PHOTOALBUMS'], '/users/'.$usr['id'].'/photoalbum.html');
        $inPage->addPathway($_LANG['PHOTOS_CONFIG']);

        cmsPage::initTemplate('components', 'com_users_photo_submit')->
                assign('user_id', $usr['id'])->
                assign('albums', $albums)->
                assign('photos', $photos)->
                assign('album_id', $album_id)->
                assign('is_edit', cmsCore::request('is_edit', 'int', 0))->
                display('com_users_photo_submit.tpl');

    }

    if (cmsCore::inRequest('submit')){

        cmsUser::sessionDel('photos_list');

        $new_album  = cmsCore::request('new_album', 'int', 0);

        $delete  = cmsCore::request('delete', 'array_int');
        $titles  = cmsCore::request('title', 'array_str');
        $allow   = cmsCore::request('allow', 'array_str');
        $desc    = cmsCore::request('desc', 'array_str');
		$is_edit = cmsCore::request('is_edit', 'int', 0);

        foreach($delete as $photo_id){
            $model->deletePhoto($photo_id);
        }

        if ($new_album){
            $album['user_id']   = $usr['id'];
            $album['title']     = cmsCore::request('album_title', 'str', $_LANG['PHOTOALBUM'].' '.date('d.m.Y'));
            $album['allow_who'] = cmsCore::request('album_allow_who', 'str', 'all');
			$album['description'] = cmsCore::request('description', 'str', '');
            $album_id = $model->addPhotoAlbum($album);
        } else {
            $album_id = cmsCore::request('album_id', 'int');
        }

		$total_foto = sizeof($titles);

		$album = !$album ? $model->getPhotoAlbum('private', $album_id) : $album;

		$descr_next = 1;

        foreach($titles as $photo_id => $title){

            $description = isset($desc[$photo_id]) ? $desc[$photo_id] : '';
            $allow_who   = isset($allow[$photo_id]) ? $allow[$photo_id] : 'all';
			$imageurl    = $photos[$photo_id]['imageurl'];
			$title       = $title ? $title : $_LANG['PHOTO_WITHOUT_NAME'];

            $photo_sql = "UPDATE cms_user_photos
                          SET title='{$title}',
                              description = '{$description}',
                              album_id = '{$album_id}',
                              allow_who = '{$allow_who}'
                          WHERE id = '{$photo_id}' AND user_id = '{$usr['id']}'
                          LIMIT 1";

            if($is_edit){
                cmsActions::updateLog('add_user_photo', array('object' => $title), $photo_id);
                cmsActions::updateLog('add_user_photo_multi', array('object' => $title), $photo_id);
            }

            $inDB->query($photo_sql);

			if ($total_foto == 1 && !$is_edit) {
				$is_friends_only = $allow_who == 'friends' ? 1 : 0;
				$is_users_only = $allow_who == 'registered' ? 1 : 0;
				cmsActions::log('add_user_photo', array(
					  'object' => $title,
					  'object_url' => '/users/'.$usr['id'].'/photo'.$photo_id.'.html',
					  'object_id' => $photo_id,
					  'target' => $album['title'],
					  'target_id' => $album_id,
					  'target_url' => '/users/'.$usr['login'].'/photos/private'.$album_id.'.html',
					  'description' => '<a href="/users/'.$usr['id'].'/photo'.$photo_id.'.html" class="act_photo">
											<img alt="'.htmlspecialchars(stripslashes($title)).'" src="/images/users/photos/small/'.$imageurl.'" />
										  </a>',
					  'is_friends_only' => $is_friends_only,
					  'is_users_only' => $is_users_only
				));

			} elseif ($descr_next < 4) {

					$photo_descr .= ' <a href="/users/'.$usr['id'].'/photo'.$photo_id.'.html" class="act_photo">
											<img alt="'.htmlspecialchars(stripslashes($title)).'" src="/images/users/photos/small/'.$imageurl.'" />
									</a> ';
			}
			$descr_next++;

        }
		if ($total_foto > 1 && !$is_edit) {
			$is_friends_only = $album['allow_who'] == 'friends' ? 1 : 0;
			$is_users_only = $album['allow_who'] == 'registered' ? 1 : 0;
			cmsActions::log('add_user_photo_multi', array(
				  'object' => $total_foto,
				  'object_url' => '',
				  'object_id' => '',
				  'target' => $album['title'],
				  'target_id' => $album_id,
				  'target_url' => '/users/'.$usr['login'].'/photos/private'.$album_id.'.html',
				  'description' => $photo_descr,
				  'is_friends_only' => $is_friends_only,
				  'is_users_only' => $is_users_only
			));
        }

        if(!$is_edit){
            cmsUser::checkAwards($inUser->id);
            cmsCore::redirect("/users/{$usr['login']}/photos/private{$album_id}.html");
        } else {
            cmsCore::redirect("/users/{$usr['id']}/photo{$photo_id}.html");
        }

    }

}

/////////////////////////////// PHOTO DELETE /////////////////////////////////////////////////////////////////////////////////////////
if ($pdo=='delphoto'){

	cmsCore::loadLib('tags');
	cmsCore::loadLanguage('components/photos');
	$photo_id = cmsCore::request('photoid', 'int', '');

	if ($inUser->id && ($inUser->id == $id || $inUser->is_admin)){

        $usr = cmsUser::getShortUserData($id);
        if (!$usr) { cmsCore::error404(); }

		$photo = $inDB->get_fields('cms_user_photos', "id = '{$photo_id}' AND user_id = '{$id}'", 'title, album_id');

        if (!$photo){ cmsCore::error404(); }

		if (!isset($_POST['godelete'])){

            $inPage->setTitle($_LANG['DELETE_PHOTO']);
            $inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
            $inPage->addPathway($_LANG['PHOTOALBUMS'], '/users/'.$usr['id'].'/photoalbum.html');
            $inPage->addPathway($_LANG['DELETE_PHOTO']);

            $confirm['title']              = $_LANG['DELETING_PHOTO'];
            $confirm['text']               = "".$_LANG['REALLY_DELETE_PHOTO']." &laquo;".$photo['title']."&raquo;?";
            $confirm['action']             = $_SERVER['REQUEST_URI'];
            $confirm['yes_button']         = array();
            $confirm['yes_button']['type'] = 'submit';
            $confirm['yes_button']['name'] = 'godelete';
            cmsPage::initTemplate('components', 'action_confirm')->
                    assign('confirm', $confirm)->
                    display('action_confirm.tpl');

		} else {

            $model->deletePhoto($photo_id);

            $album_has_photos = $inDB->rows_count('cms_user_photos', "album_id = {$photo['album_id']}", 1);

            if ($album_has_photos){
                $inCore->redirect('/users/'.$usr['login'].'/photos/private'.$photo['album_id'].'.html');
            } else {
                $model->deletePhotoAlbum($id, $photo['album_id']);
                cmsCore::redirect(cmsUser::getProfileURL($usr['login']));
            }

		}

	} else { cmsCore::error404(); }
}

/////////////////////////////// ALBUM EDIT /////////////////////////////////////////////////////////////////////////////////////////
if ($pdo=='editalbum'){

    $usr = cmsUser::getShortUserData($id);
    if (!$usr) { cmsCore::error404(); }

	$album_id = cmsCore::request('album_id', 'int', '');

    $album = $model->getPhotoAlbum('private', $album_id);
    if (!$album) { cmsCore::error404(); }

    if ($album['user_id'] != $inUser->id && !$inUser->is_admin){ cmsCore::error404(); }

	unset($album);

    $album['title']       = cmsCore::request('album_title', 'str', $_LANG['PHOTOALBUM'].' '.date('d.m.Y'));
    $album['allow_who']   = cmsCore::request('album_allow_who', 'str', 'all');
	$album['description'] = cmsCore::request('description', 'str', '');
	$album['id']          = $album_id;

	$model->updatePhotoAlbum($album);

    cmsActions::updateLog('add_user_photo', array('target' => $album['title']), 0, $album_id);
    cmsActions::updateLog('add_user_photo_multi', array('target' => $album['title']), 0, $album_id);

    cmsCore::redirect('/users/'.$usr['login'].'/photos/private'.$album_id.'.html');

}

/////////////////////////////// PHOTO EDIT /////////////////////////////////////////////////////////////////////////////////////////
if ($pdo=='editphoto'){

    $usr = cmsUser::getShortUserData($id);
    if (!$usr) { cmsCore::error404(); }

    $photo = $model->getPhoto(cmsCore::request('photoid', 'int', ''));
    if (!$photo) { cmsCore::error404(); }

    if ($photo['user_id'] != $inUser->id && !$inUser->is_admin){ cmsCore::error404(); }

	cmsUser::sessionPut('photos_list', array($photo['id']));

    cmsCore::redirect('/users/'.$usr['login'].'/photos/submit-edit');

}

//============================================================================//
//====================== Пакетное редактирование фотографий ==================//
//============================================================================//

if ($pdo=='editphotolist'){

    if (!cmsCore::inRequest('photos')) { cmsCore::error404(); }

    $photo_ids = cmsCore::request('photos', 'array_int');
    $album_id  = cmsCore::request('album_id', 'int');
    $photos    = array();

    $usr = cmsUser::getShortUserData($id);
    if (!$usr) { cmsCore::error404(); }

    //проверяем доступ
    foreach($photo_ids as $photo_id){

        $photo = $model->getPhoto($photo_id);

        if ($photo['user_id'] != $inUser->id && !$inUser->is_admin){ cmsCore::error404(); }

    }

    if (cmsCore::inRequest('delete')){

        foreach($photo_ids as $photo_id){
            $model->deletePhoto($photo_id);
        }

        $album_has_photos = $inDB->rows_count('cms_user_photos', "album_id = {$album_id}", 1);

        if ($album_has_photos){
            cmsCore::redirectBack();
        } else {
            $model->deletePhotoAlbum($id, $album_id);
            cmsCore::redirect(cmsUser::getProfileURL($usr['login']));
        }

    }

    if (cmsCore::inRequest('edit')){

        foreach($photo_ids as $photo_id){
            $photos[] = $photo_id;
        }

        if ($photos){ cmsUser::sessionPut('photos_list', $photos); }

        cmsCore::redirect('/users/'.$usr['login'].'/photos/submit-edit');

    }

}

//============================================================================//
//============================ Все фотографии ================================//
//============================================================================//

if ($pdo=='viewphotos'){

	if (!$inUser->id && !$model->config['sw_guest']) {
        cmsUser::goToLogin();
	}

	$usr = cmsUser::getShortUserData($id);
	if (!$usr){ cmsCore::error404(); }

    //Мой профиль или нет
    $my_profile = ($inUser->id == $id) ? true : false;

    //Определяем, друзья мы или нет
	$we_friends = ($inUser->id && !$my_profile) ? cmsUser::isFriend($usr['id']) : 0;

    $albums = $model->getPhotoAlbums($id, $we_friends, !$inCore->isComponentEnable('photos'));

    $inPage->setTitle($_LANG['PHOTOALBUMS']);
    $inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
    $inPage->addPathway($_LANG['PHOTOALBUMS']);

    //Отдаем в шаблон
    cmsPage::initTemplate('components', 'com_users_albums')->
            assign('albums', $albums)->
            assign('my_profile', $my_profile)->
            assign('user', $usr)->
            display('com_users_albums.tpl');

}

/////////////////////////////// VIEW PHOTO /////////////////////////////////////////////////////////////////////////////////////////
if ($pdo=='viewphoto'){

	if (!$inUser->id && !$model->config['sw_guest']) {
        cmsUser::goToLogin();
	}

    $photoid = cmsCore::request('photoid', 'int', 0);

	$myprofile = ($inUser->id == $id);

	$usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

    cmsCore::loadLib('tags');

	$sql = "SELECT p.*, a.title as album, pr.gender
            FROM cms_user_photos p
			INNER JOIN cms_user_albums a ON a.id = p.album_id
			INNER JOIN cms_user_profiles pr ON pr.user_id = p.user_id
            WHERE p.id = '$photoid' AND p.user_id = '$id' LIMIT 1";
	$result = $inDB->query($sql) ;

	if (!$inDB->num_rows($result)){ cmsCore::error404(); }

	$photo = $inDB->fetch_assoc($result);

	$inPage->setTitle($photo['title']);
	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['PHOTOALBUMS'], '/users/'.$usr['id'].'/photoalbum.html');
    $inPage->addPathway($photo['album'], '/users/'.$usr['login'].'/photos/private'.$photo['album_id'].'.html');
    $inPage->addPathway($photo['title']);

    $photo['pubdate'] = cmsCore::dateFormat($photo['pubdate'], true, false, false);
	$photo['genderlink'] = cmsUser::getGenderLink($usr['id'], $usr['nickname'], $photo['gender'], $usr['login']);
    $photo['filesize'] = round(filesize(PATH.'/images/users/photos/medium/'.$photo['imageurl'])/1024, 2);
    //ссылки на предыдущую и следующую фотографии
    $previd = $inDB->get_fields('cms_user_photos', "id>'{$photo['id']}' AND user_id = '{$usr['id']}' AND album_id='{$photo['album_id']}'", 'id, title, pubdate', 'id ASC');
    $nextid = $inDB->get_fields('cms_user_photos', "id<'{$photo['id']}' AND user_id = '{$usr['id']}' AND album_id='{$photo['album_id']}'", 'id, title, pubdate', 'id DESC');
	// Проверяем права доступа
	$is_allow = cmsUser::checkUserContentAccess($photo['allow_who'], $id);
	// Если видим фото, обновляем просмотры
	if ($is_allow) { $inDB->query("UPDATE cms_user_photos SET hits = hits + 1 WHERE id = ".$photo['id']) ; }

    cmsPage::initTemplate('components', 'com_users_photos_view')->
            assign('photo', $photo)->
            assign('bbcode', '[IMG]'.HOST.'/images/users/photos/medium/'.$photo['imageurl'].'[/IMG]')->
            assign('previd', $previd)->
            assign('nextid', $nextid)->
            assign('usr', $usr)->
            assign('myprofile', $myprofile)->
            assign('is_admin', cmsUser::userIsAdmin($inUser->id))->
            assign('is_allow', $is_allow)->
            assign('tagbar', ($is_allow ? cmsTagBar('userphoto', $photo['id']) : ''))->
            display('com_users_photos_view.tpl');

	if($inCore->isComponentInstalled('comments') && $is_allow){
        cmsCore::includeComments();
        comments('userphoto', $photo['id'], array(), $myprofile);
    }

}
//============================================================================//
//============================ Один фотоальбом ===============================//
//============================================================================//

if ($pdo=='viewalbum'){

	if (!$inUser->id && !$model->config['sw_guest']) {
        cmsUser::goToLogin();
	}

    $usr = cmsUser::getShortUserData($login);
    if (!$usr){ cmsCore::error404(); }

    $album_type = cmsCore::request('album_type', 'str', 'private');
    $album_id   = cmsCore::request('album_id', 'int', '0');

    $album = $model->getPhotoAlbum($album_type, $album_id);
    if (!$album){ cmsCore::error404(); }

    if ($album_type != 'private') { $album['allow_who'] = 'all'; }

    $inPage->setTitle($album['title']);
    $inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['PHOTOALBUMS'], '/users/'.$usr['id'].'/photoalbum.html');
    $inPage->addPathway($album['title']);

    $photos = array();

    //Мой профиль или нет
    $my_profile = ($inUser->id == $usr['id']) ? true : false;

    //Определяем, друзья мы или нет
	$we_friends = ($inUser->id && !$my_profile) ? cmsUser::isFriend($usr['id']) : 0;

	if ($album['allow_who'] == 'all' || $my_profile || ($album['allow_who'] == 'friends' && $we_friends) || ($album['allow_who'] == 'registered' && $inUser->id)) {
        $photos = $model->getAlbumPhotos($usr['id'], $album_type, $album_id, $we_friends);
	}

    //Делим на страницы
    $total = sizeof($photos);

    if ($total){
        $perpage     = 21;
        $pagination  = cmsPage::getPagebar($total, $page, $perpage, '/users/%user%/photos/%album%%id%-%page%.html', array('user'=>$usr['login'], 'album'=>$album_type, 'id'=>$album_id));
        $page_photos = array();
        $start       = $perpage*($page-1);
        for($p=$start; $p<$start+$perpage; $p++){
            if ($photos[$p]){
                $page_photos[] = $photos[$p];
            }
        }
        $photos = $page_photos; unset($page_photos);
    }

    //Отдаем в шаблон
    cmsPage::initTemplate('components', 'com_users_photos')->
            assign('page_title', $album['title'])->
            assign('album_type', $album_type)->
            assign('album', $album)->
            assign('photos', $photos)->
            assign('user_id', $usr['id'])->
            assign('usr', $usr)->
            assign('my_profile', $my_profile)->
            assign('is_admin', $inUser->is_admin)->
            assign('pagebar', $pagination)->
            display('com_users_photos.tpl');

}

//============================================================================//
//============================ Удалить фотоальбом ============================//
//============================================================================//
if ($pdo=='delalbum'){

    $album_id = cmsCore::request('album_id', 'int', '0');

    $album = $model->getPhotoAlbum('private', $album_id);
    if (!$album){ cmsCore::error404(); }

    if (!$inUser->is_admin && ($album['user_id'] != $inUser->id)) { cmsCore::error404(); }

    $model->deletePhotoAlbum($id, $album_id);

    $user = cmsUser::getShortUserData($album['user_id']);

    cmsCore::redirect(cmsUser::getProfileURL($user['login']));

}

?>