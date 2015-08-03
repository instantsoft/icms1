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
	// при ajaxfileupload HTTP_X_REQUESTED_WITH не передается, устанавливем его - костыль :-) см. /core/ajax/ajax_core.php
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

	// загружать могут только авторизованные
    if (!$inUser->id) {	cmsCore::halt(); }

	// Получаем компонент, с которого идет загрузка
	$component = cmsCore::request('component', 'str', '');
	// Проверяем установлен ли он
	if(!$inCore->isComponentInstalled($component)) { cmsCore::halt(); }
	// Загружаем конфигурацию компонента
	$cfg = $inCore->loadComponentConfig($component);
	// проверяем не выключен ли он
	if(!$cfg['component_enabled']) { cmsCore::halt(); }

	// id места назначения
	$target_id = cmsCore::request('target_id', 'int', 0);
	// место назначения в компоненте
	$target = cmsCore::request('target', 'str', '');

	if (!isset($cfg['img_max'])) { $cfg['img_max'] = 50; }
	if (!isset($cfg['img_on'])) { $cfg['img_on'] = 1; }
	if (!isset($cfg['watermark'])) { $cfg['watermark'] = 1; }
    if (!isset($cfg['img_w'])) { $cfg['img_w'] = 900; }
    if (!isset($cfg['img_h'])) { $cfg['img_h'] = 900; }

	// Разрешена ли загрузка
	if (!$cfg['img_on']){ cmsCore::jsonOutput(array('error' => $_LANG['UPLOAD_IMG_IS_DISABLE'], 'msg' => ''), false); }

	// Не превышен ли лимит
	if (cmsCore::getTargetCount($target_id) >= $cfg['img_max']){ cmsCore::jsonOutput(array('error' => $_LANG['UPLOAD_IMG_LIMIT'], 'msg' => ''), false); }

	// Подготавливаем класс загрузки фото
	cmsCore::loadClass('upload_photo');
	$inUploadPhoto = cmsUploadPhoto::getInstance();
	$inUploadPhoto->upload_dir    = PATH.'/upload/';
	$inUploadPhoto->dir_medium    = $component.'/';
	$inUploadPhoto->medium_size_w = $cfg['img_w'];
	$inUploadPhoto->medium_size_h = $cfg['img_h'];
	$inUploadPhoto->is_watermark  = $cfg['watermark'];
	$inUploadPhoto->only_medium   = true;
	$inUploadPhoto->input_name    = 'attach_img';
	// загружаем фото
	$file = $inUploadPhoto->uploadPhoto();

	if (!$file){ cmsCore::jsonOutput(array('error' => cmsCore::uploadError(), 'msg' => ''), false); }

	$fileurl = '/upload/'.$component.'/'.$file['filename'];

	cmsCore::registerUploadImages($target_id, $target, $fileurl, $component);

	cmsCore::jsonOutput(array('error' => '', 'msg' => $fileurl), false);