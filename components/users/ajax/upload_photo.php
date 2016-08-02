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

Error_Reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

@set_time_limit(0);

define('PATH', $_SERVER['DOCUMENT_ROOT']);
define('VALID_CMS', 1);

header('Content-Type: text/html; charset=utf-8');

include_once(PATH . '/core/cms.php');
$inCore = cmsCore::getInstance();

cmsCore::loadClass('user');

$inDB   = cmsDatabase::getInstance();
$inConf = cmsConfig::getInstance();
$inUser = cmsUser::getInstance();

cmsCore::loadModel('users');
$model = new cms_model_users();

if (empty($_FILES['Filedata']['name'])) {
    return false;
}

$sess_id = cmsCore::request('sess_id', 'str');

if (!$sess_id) {
    header("HTTP/1.1 500 File Upload Error");
    exit(0);
}

session_id($sess_id);
session_start();

$user_id = (int)$_SESSION['user']['id'];

if (!$user_id) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(0);
}

if (( $model->config['photosize'] > 0 ) && ( $model->getUserPhotoCount($user_id) >= $model->config['photosize'] ) && !$inUser->is_admin) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(0);
}

include_once( PATH . '/includes/graphic.inc.php' );

$uploaddir = PATH . '/images/users/photos/';
$realfile  = $inDB->escape_string($_FILES['Filedata']['name']);

$path_parts = pathinfo($realfile);
$ext        = mb_strtolower($path_parts['extension']);

if (!$ext || !in_array($ext, array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) {
    exit(0);
}

$lid      = $inDB->get_fields('cms_user_photos', ' id > 0 ', 'id', 'id DESC');
$lastid   = $lid['id'] + 1;
$filename = md5($lastid . $realfile).'.'.$ext;

$uploadphoto = $uploaddir . $filename;
$small       = $uploaddir . 'small/' . $filename;
$medium      = $uploaddir . 'medium/' . $filename;

$source    = $_FILES['Filedata']['tmp_name'];
$errorCode = $_FILES['Filedata']['error'];

if ($inCore->moveUploadedFile($source, $uploadphoto, $errorCode)) {

    @img_resize($uploadphoto, $small, 96, 96, true);
    @img_resize($uploadphoto, $medium, 600, 600, false, $model->config['watermark']);

    @unlink($uploadphoto);

    $model->addUploadedPhoto($user_id, array('filename' => $realfile, 'imageurl' => $filename));
    echo "FILEID:" . $lastid;

    if (cmsCore::inRequest('upload')) {
        cmsCore::redirect('/users/' . $inUser->login . '/photos/submit');
    }

} else {

    header("HTTP/1.1 500 Internal Server Error");
    echo cmsCore::uploadError();

}