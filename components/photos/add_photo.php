<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

///////////////////// форма загрузки фотографий 1 шаг //////////////////////////
if ($do_photo == 'addphoto'){

	$inPage->addPathway($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_1']);
	$inPage->setTitle($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_1']);

	if (!cmsCore::inRequest('submit')){

		$inPage->initAutocomplete();
		$autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

		cmsPage::initTemplate('components', 'com_photos_add1')->
                assign('no_tags', false)->
                assign('is_admin', $inUser->is_admin)->
                assign('cfg', $model->config)->
                assign('autocomplete_js', $autocomplete_js)->
                display('com_photos_add1.tpl');

	}

	if (cmsCore::inRequest('submit')){

		$mod = array();

		$mod['title']       = cmsCore::request('title', 'str', '');
		$mod['description'] = cmsCore::request('description', 'str');
		$mod['is_multi']    = cmsCore::request('only_mod', 'int', 0);
		$mod['tags']        = cmsCore::request('tags', 'str');
		$mod['comments']    = $inUser->is_admin ? cmsCore::request('comments', 'int') : 1;
        if($model->config['seo_user_access'] || $inUser->is_admin){
            $mod['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
            $mod['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
            $mod['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
        }

		cmsUser::sessionPut('mod', $mod);

		cmsCore::redirect('/photos/'.$album['id'].'/submit_photo.html');

	}

}

////////////////// форма загрузки фотографий 2 шаг /////////////////////////////
if ($do_photo == 'submit_photo'){

	$mod = cmsUser::sessionGet('mod');
	if (!$mod) { cmsCore::error404(); }

	$inPage->addPathway($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_2']);
	$inPage->setTitle($_LANG['ADD_PHOTO'].': '.$_LANG['STEP_2']);

    if($album['uplimit'] && !$inUser->is_admin) {

        $max_limit  = true;
        $max_files  = (int)$album['uplimit'] - $today_uploaded;
		$stop_photo = $today_uploaded >= (int)$album['uplimit'];

    } else {

        $max_limit  = false;
        $max_files  = 0;
		$stop_photo = false;

    }

    cmsPage::initTemplate('components', 'com_photos_add2')->
            assign('upload_url', '/components/photos/ajax/upload_photo.php')->
            assign('upload_complete_url', '/photos/'.$album['id'].'/uploaded.html')->
            assign('sess_id', session_id())->
            assign('max_limit', $max_limit)->
            assign('album', $album)->
            assign('max_files', $max_files)->
            assign('uload_type', $mod['is_multi'] ? 'multi' : 'single')->
            assign('stop_photo', $stop_photo)->
            display('com_photos_add2.tpl');

}

///////////////// фотографии загружены /////////////////////////////////////////
if ($do_photo == 'uploaded'){

	cmsUser::sessionDel('mod');
	cmsCore::redirect('/photos/'.$album['id']);

}
