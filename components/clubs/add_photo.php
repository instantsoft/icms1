<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

/////////////////// форма загрузки фотографий 1 шаг ////////////////////////////
if ($do_photo == 'addphoto'){

	$inPage->addPathway($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_1']);
	$inPage->setTitle($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_1']);

	if (!cmsCore::inRequest('submit')){

		cmsPage::initTemplate('components', 'com_photos_add1')->
                assign('no_tags', true)->
                assign('is_admin', ($is_admin || $is_moder))->
                display('com_photos_add1.tpl');

	}

	if (cmsCore::inRequest('submit')){

		$mod = array();

		$mod['title']       = cmsCore::request('title', 'str', '');
		$mod['description'] = cmsCore::request('description', 'str');
		$mod['is_multi']    = cmsCore::request('only_mod', 'int', 0);
		$mod['comments']    = ($is_admin || $is_moder) ? cmsCore::request('comments', 'int') : 1;
        if($model->config['seo_user_access'] || $inUser->is_admin){
            $mod['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
            $mod['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
            $mod['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
        }

		cmsUser::sessionPut('mod', $mod);

		cmsCore::redirect('/clubs/photoalbum'.$album['id'].'/submit_photo.html');

	}

}

/////////////////// форма загрузки фотографий 2 шаг ////////////////////////////
if ($do_photo == 'submit_photo'){

	$mod = cmsUser::sessionGet('mod');
	if (!$mod) { cmsCore::error404(); }

	$inPage->addPathway($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_2']);
	$inPage->setTitle($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_2']);

    cmsPage::initTemplate('components', 'com_photos_add2')->
            assign('upload_url', '/components/clubs/ajax/upload_photo.php')->
            assign('upload_complete_url', '/clubs/uploaded'.$album['id'].'.html')->
            assign('sess_id', session_id())->
            assign('max_limit', false)->
            assign('album', $album)->
            assign('max_files', 0)->
            assign('uload_type', $mod['is_multi'] ? 'multi' : 'single')->
            assign('stop_photo', false)->
            display('com_photos_add2.tpl');

}

/////////////////////// фотографии загружены ///////////////////////////////////
if ($do_photo == 'uploaded'){

	cmsUser::sessionDel('mod');

	cmsCore::redirect('/clubs/photoalbum'.$album['id']);

}
