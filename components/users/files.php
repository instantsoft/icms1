<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }
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
if ($fdo=='view'){

    $usr = cmsUser::getShortUserData($id);
	if (!$usr) { cmsCore::error404(); }

    if($inUser->id){
    	$inPage->addHeadJS('components/users/js/pageselfiles.js');
    }
	$inPage->setTitle($usr['nickname'].' - '.$_LANG['FILES']);
	$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
	$inPage->addPathway($_LANG['FILES_ARCHIVE'], '/users/'.$id.'/files.html');
    $inPage->addHeadJsLang(array('NO_SELECT_FILE'));

    $orderby = cmsCore::getSearchVar('orderby', 'pubdate');
    $orderto = cmsCore::getSearchVar('orderto', 'desc');

	if(!in_array($orderby, array('pubdate', 'filename', 'filesize', 'hits'))) { $orderby = 'pubdate'; }
	if(!in_array($orderto, array('asc', 'desc'))) { $orderto = 'desc'; }

	$perpage   = 20;
	$myprofile = ($inUser->id==$usr['id']);

    $inDB->where("user_id = '{$usr['id']}'");

    $total_files = $model->getUserFilesCount($myprofile || $inUser->is_admin);

    $inDB->orderBy($orderby, $orderto);

    $inDB->limitPage($page, $perpage);

    $files = $model->getUserFiles($myprofile || $inUser->is_admin);

    $free_mb = $model->config['filessize'] ?
               round($model->config['filessize'] - round(($model->getUserFilesSize($usr['id']) / 1024) / 1024, 2), 2) :
               '';

	cmsPage::initTemplate('components', 'com_users_file_view')->
            assign('usr', $usr)->
            assign('orderby', $orderby)->
            assign('orderto', $orderto)->
            assign('cfg', $model->config)->
            assign('total_files', $total_files)->
            assign('free_mb', $free_mb)->
            assign('pagination', cmsPage::getPagebar($total_files, $page, $perpage, '/users/'.$id.'/files%page%.html'))->
            assign('myprofile', $myprofile)->
            assign('is_admin', $inUser->is_admin)->
            assign('files', $files)->
            display('com_users_file_view.tpl');

}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($fdo=='download'){

    $file_id = cmsCore::request('fileid', 'int', 0);

    $allowsql = $inUser->id ? '' : "AND allow_who='all'";

    $file = $inDB->get_fields('cms_user_files', "id = '$file_id' {$allowsql}", 'user_id, filename, allow_who');
    if(!$file){ cmsCore::error404(); }

    $name    = preg_replace('/\.+\//', '', $file['filename']);
    $fileurl = '/upload/userfiles/'.$file['user_id'].'/'.$name;

    if (!file_exists(PATH.$fileurl)){ cmsCore::error404(); }

    if ($file['user_id'] != $inUser->id && $file['allow_who'] != 'all' && !$inUser->is_admin) { $inCore->halt($_LANG['FILE_HIDEN']); }

    $inDB->query("UPDATE cms_user_files SET hits = hits + 1 WHERE id = $file_id");

    header('Content-Disposition: attachment; filename='.basename($fileurl) . "\n");
    header('Content-Type: application/x-force-download; name="'.$fileurl.'"' . "\n");
    header('Location:'.$fileurl);
    cmsCore::halt();

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($fdo=='addfile'){

	if (!$inUser->id) { cmsUser::goToLogin(); }

    $usr = cmsUser::getShortUserData($inUser->id);
    if (!$usr) { cmsCore::error404(); }

    $free_mb = $model->config['filessize'] ?
           round($model->config['filessize'] - round(($model->getUserFilesSize($usr['id']) / 1024) / 1024, 2), 2) :
           '';

	if(cmsCore::inRequest('upload')){

		$size_mb      = 0;
		$loaded_files = array();

		$list_files = array();

		foreach($_FILES['upfile'] as $key=>$value) {
			foreach($value as $k=>$v) { $list_files['upfile'.$k][$key] = $v; }
		}

		foreach ($list_files as $key=>$data_array) {

			if ($data_array['error'] != UPLOAD_ERR_OK) { continue; }

			$upload_dir = PATH.'/upload/userfiles/'.$usr['id'];
			@mkdir($upload_dir);

			$name       = $data_array["name"];
			$size       = cmsCore::strClear($data_array["size"]);
			$size_mb    += round(($size/1024)/1024, 2);

			// проверяем тип файла
			$maytypes 	= explode(',', str_replace(' ', '', $model->config['filestype']));
			$path_parts = pathinfo($name);
			// расширение файла
			$ext        = mb_strtolower($path_parts['extension']);

			if(in_array($ext, array('php','htm','html','htaccess'))) { cmsCore::addSessionMessage($_LANG['ERROR_TYPE_FILE'].': '.$model->config['filestype'], 'error'); cmsCore::redirectBack(); }
			if(!in_array($ext, $maytypes)) { cmsCore::addSessionMessage($_LANG['ERROR_TYPE_FILE'].': '.$model->config['filestype'], 'error'); cmsCore::redirectBack(); }

			// Переводим имя файла в транслит
			// отделяем имя файла от расширения
			$name  = mb_substr($name, 0, mb_strrpos($name, '.'));
			// транслитируем
			$name  = cmsCore::strToURL(preg_replace('/\.+\//', '', $name)).uniqid();
			// присоединяем расширения файла
			$name .= '.'.$ext;
			// Обрабатываем получившееся имя файла для записи в БД
			$name  = cmsCore::strClear($name);

			// Проверяем свободное место
			if ($size_mb > $free_mb && $model->config['filessize']){ cmsCore::addSessionMessage($_LANG['YOUR_FILE_LIMIT'].' ('.$max_mb.' '.$_LANG['MBITE'].') '.$_LANG['IS_OVER_LIMIT'].'<br>'.$_LANG['FOR_NEW_FILE_DEL_OLD'], 'error'); cmsCore::redirectBack(); }

			// Загружаем файл
			if ($inCore->moveUploadedFile($data_array["tmp_name"], PATH."/upload/userfiles/{$usr['id']}/$name", $data_array['error'])) {

				$loaded_files[] = $name;

				$sql = "INSERT INTO cms_user_files(user_id, filename, pubdate, allow_who, filesize, hits)
						VALUES ({$usr['id']}, '$name', NOW(), 'all', '$size', 0)";
				$inDB->query($sql);
				$file_id = $inDB->get_last_id('cms_user_files');
				cmsActions::log('add_file', array(
					  'object' => $name,
					  'object_url' => '/users/files/download'.$file_id.'.html',
					  'object_id' => $file_id,
					  'target' => '',
					  'target_url' => '',
					  'description' => ''
				));

			}

		}

		if (sizeof($loaded_files)){

            cmsCore::addSessionMessage($_LANG['FILE_UPLOAD_FINISH'], 'success');

			if ($model->config['filessize']){
                cmsCore::addSessionMessage('<strong>'.$_LANG['FREE_SPACE_LEFT'].':</strong> '.round($free_mb-$size_mb, 2).' '.$_LANG['MBITE'], 'info');
			}

            cmsCore::redirect('/users/'.$usr['id'].'/files.html');

		} else {
			cmsCore::addSessionMessage($_LANG['ERR_BIG_FILE'].' '.$_LANG['ERR_FILE_NAME'], 'error');
            cmsCore::redirectBack();
		}

	}

	if(!cmsCore::inRequest('upload')){

		$inPage->setTitle($_LANG['UPLOAD_FILES']);
		$inPage->addHeadJS('includes/jquery/multifile/jquery.multifile.js');

		$inPage->addPathway($usr['nickname'], cmsUser::getProfileURL($usr['login']));
		$inPage->addPathway($_LANG['FILES_ARCHIVE'], '/users/'.$usr['id'].'/files.html');
		$inPage->addPathway($_LANG['UPLOAD_FILES']);
        $inPage->addHeadJsLang(array('FILE_SELECTED','FILE_DENIED','FILE_DUPLICATE'));

		$post_max_b = trim(@ini_get('upload_max_filesize'));
		$last = mb_strtolower($post_max_b{mb_strlen($post_max_b)-1});
		switch($last) {
			case 'g':
				$post_max_b *= 1024;
			case 'm':
				$post_max_b *= 1024;
			case 'k':
				$post_max_b *= 1024;
		}

		cmsPage::initTemplate('components', 'com_users_file_add')->
                assign('free_mb', $free_mb)->
                assign('post_max_b', $post_max_b)->
                assign('post_max_mb', (round($post_max_b/1024)/1024) . ' '.$_LANG['MBITE'])->
                assign('cfg', $model->config)->
                assign('types', $model->config['filestype'])->
                display('com_users_file_add.tpl');

	}

}

/////////////////////////////// MULTIPLE FILES DELETE /////////////////////////////////////////////////////////////////////////////////////////
if ($fdo=='delfilelist'){

	$files = cmsCore::request('files', 'array_int');
	if (!$files) { cmsCore::error404(); }

	if (!$inUser->id || ($inUser->id!=$id && !$inUser->is_admin)){
        cmsCore::error404();
    }

    $a_list = rtrim(implode(',', $files), ',');
    $fsql = '';
    if ($a_list){
        $fsql .= "id IN ({$a_list})";
    } else {
        $fsql .= '1=0';
    }

    $sql = "SELECT id, filename FROM cms_user_files WHERE user_id = '$id' AND {$fsql}";
    $result = $inDB->query($sql);

    if ($inDB->num_rows($result)){
        while ($file = $inDB->fetch_assoc($result)){

            @unlink(PATH.'/upload/userfiles/'.$id.'/'.$file['filename']);
            cmsActions::removeObjectLog('add_file', $file['id']);

        }
        $inDB->query("DELETE FROM cms_user_files WHERE user_id = '$id' AND {$fsql}");
    }

    cmsCore::redirect('/users/'.$id.'/files.html');

}

/////////////////////////////// MULTIPLE FILES PUBLISHING /////////////////////////////////////////////////////////////////////////////////////////
if ($fdo=='pubfilelist'){

    $files = cmsCore::request('files', 'array_int', array());
    if (!$files) { cmsCore::error404(); }

	$allow = cmsCore::request('allow', 'str', 'nobody');

	if (!$inUser->id || ($inUser->id!=$id && !$inUser->is_admin)){
        cmsCore::error404();
    }

    $a_list = rtrim(implode(',', $files), ',');
    $fsql = '';
    if ($a_list){
        $fsql .= "id IN ({$a_list})";
    } else {
        $fsql .= '1=0';
    }

    $inDB->query("UPDATE cms_user_files SET allow_who = '$allow' WHERE user_id = '$id' AND {$fsql}");

	cmsCore::redirect('/users/'.$id.'/files.html');

}

?>