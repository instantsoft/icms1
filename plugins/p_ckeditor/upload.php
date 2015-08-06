<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.7                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

define("VALID_CMS", 1);
define('PATH', $_SERVER['DOCUMENT_ROOT']);

include(PATH.'/core/cms.php');

$inCore = cmsCore::getInstance();

cmsCore::loadClass('page');
cmsCore::loadClass('user');
cmsCore::loadClass('upload_photo');

$inUser = cmsUser::getInstance();

if (!$inUser->update()) { cmsCore::error404(); }
if (!$inUser->id) { cmsCore::error404(); }

// Получаем компонент, с которого идет загрузка
$component = cmsCore::request('component', 'str', 'content');
// Проверяем установлен ли он
if(!$inCore->isComponentInstalled($component)) { cmsCore::error404(); }

// Что загружаем: изображения или другие файлы
$type = cmsCore::request('type', 'str', '');

// объект плагина
$plugin = $inCore->loadPlugin('p_ckeditor');

global $_LANG;

// умолчания
$error     = $_LANG['CK_UPLOAD_ERROR'];
$http_path = '';

// грузим изображения
if($type === 'image'){

    $inUploadPhoto = cmsUploadPhoto::getInstance();

    // разрешена ли загрузка фото
    if(!$plugin->canUpload()){ cmsCore::error404(); }

    // Выставляем конфигурационные параметры
    $inUploadPhoto->upload_dir    = PATH.'/upload/';
    $inUploadPhoto->medium_size_w = $plugin->config['photo_width'];
    $inUploadPhoto->medium_size_h = $plugin->config['photo_height'];
    $inUploadPhoto->thumbsqr      = false;
    $inUploadPhoto->is_watermark  = $plugin->config['iswatermark'];
    $inUploadPhoto->only_medium   = true;
    $inUploadPhoto->dir_medium    = 'wysiwyg/';
    $inUploadPhoto->input_name    = 'upload';
    // Процесс загрузки фото
    $file = $inUploadPhoto->uploadPhoto();

    if ($file['filename']) {
        $http_path = '/upload/wysiwyg/'.$file['filename'];
        $error     = '';
    }

}

// грузим другие файлы
if($type === 'file'){

    if(empty($plugin->config['allow_file_ext']) || !$plugin->canFileUpload()){ cmsCore::error404(); }

    $allow_ext = explode(',', $plugin->config['allow_file_ext']);
    $allow_ext = array_map(function($val){ return trim($val); }, $allow_ext);

    if (!empty($_FILES['upload']['name'])){

        $input_name = preg_replace('/[^a-zA-Zа-яёЁА-Я0-9\.\-_ ]/ui', '', basename(strval($_FILES['upload']['name'])));
        $ext        = mb_strtolower(pathinfo($input_name, PATHINFO_EXTENSION));

        if ($ext && in_array($ext, $allow_ext, true)) {

            $uploadpath = PATH.'/upload/wysiwyg/'.md5(microtime().uniqid()).'.'.$ext;
            $source	    = $_FILES['upload']['tmp_name'];
            $errorCode  = $_FILES['upload']['error'];

            if (cmsCore::moveUploadedFile($source, $uploadpath, $errorCode)) {

                $http_path = str_replace(PATH, '', $uploadpath);
                $error     = '';

            }

        } else {
            $error = $_LANG['CK_UPLOAD_EXT_ERROR'];
        }

    }

}

cmsCore::halt('<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.cmsCore::request('CKEditorFuncNum', 'int', 0).',  "'.$http_path.'", "'.$error.'" );</script>');
