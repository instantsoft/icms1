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

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

	$title   = cmsCore::request('title', 'str');
	$club_id = cmsCore::request('club_id', 'int');

    cmsCore::loadModel('clubs');
    $model = new cms_model_clubs();

	if (!$title || !$club_id){ cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ALBUM_REQ_TITLE'])); }

	$club = $model->getClub($club_id);

	if (!($club && $inUser->id) || !$club['published']){ cmsCore::halt();  }

	if(!$club['enabled_photos']){ cmsCore::halt(); }

	// Инициализируем участников клуба
	$model->initClubMembers($club['id']);
	// права доступа
    $is_admin  = $inUser->is_admin || ($inUser->id == $club['admin_id']);
    $is_moder  = $model->checkUserRightsInClub('moderator');
    $is_member = $model->checkUserRightsInClub('member');

    $is_karma_enabled = (($inUser->karma >= $club['album_min_karma']) && $is_member) ? true : false;

    if ($is_admin || $is_moder || $is_karma_enabled){

		$parent_id = $inDB->getNsRootCatId('cms_photo_albums', 'club'.$club['id']);

		$album_id = $inDB->addNsCategory('cms_photo_albums', array('parent_id'=>$parent_id,'title'=>$title,'user_id'=>$club['id'],'published'=>1), 'club'.$club['id']);

		cmsCore::jsonOutput(array('error' => false, 'album_id' => (string)$album_id));

	} elseif(!$is_karma_enabled){
		cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['NEED_KARMA_ALBUM'].' '.$_LANG['NEEDED'].' '.$club['album_min_karma'].', '.$_LANG['HAVE_ONLY'].' '.$inUser->karma.'.'));
    } else {
        cmsCore::halt();
    }