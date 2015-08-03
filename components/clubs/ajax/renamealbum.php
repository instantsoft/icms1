<?php

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

	if(!$inUser->id) { cmsCore::halt(); }

    cmsCore::loadModel('clubs');
    $model = new cms_model_clubs();

	$title = cmsCore::request('title', 'str', '');
	if (!$title){ cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ALBUM_REQ_TITLE']));  }

	// Получаем альбом
	$album = $inDB->getNsCategory('cms_photo_albums', cmsCore::request('album_id', 'int', 0), null);
	if (!$album) { cmsCore::halt(); }

	// получаем клуб
	$club = $model->getClub($album['user_id']);
	if(!$club) { cmsCore::halt(); }

	if(!$club['enabled_photos']){ cmsCore::halt(); }

	// Инициализируем участников клуба
	$model->initClubMembers($club['id']);
	// права доступа
    $is_admin = $inUser->is_admin || ($inUser->id == $club['admin_id']);
    $is_moder = $model->checkUserRightsInClub('moderator');

    if ($is_admin || $is_moder){

		$inDB->update('cms_photo_albums', array('title' => $title), $album['id']);

		cmsCore::jsonOutput(array('error' => false, 'text' => htmlspecialchars(stripslashes($title))));

    }

	cmsCore::halt();

?>