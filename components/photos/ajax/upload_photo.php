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
define("VALID_CMS", 1);

header('Content-Type: text/html; charset=utf-8');

include(PATH.'/core/cms.php');
$inCore = cmsCore::getInstance();

// Принимаем значение session_id из флешки
$sess_id = cmsCore::request("sess_id", 'str');
if (!$sess_id) { header("HTTP/1.1 500 File Upload Error"); exit(0); }
session_id($sess_id);
session_start();

cmsCore::loadClass('user');
cmsCore::loadClass('actions');
cmsCore::loadClass('photo');

$inDB   = cmsDatabase::getInstance();
$inConf = cmsConfig::getInstance();
$inUser = cmsUser::getInstance();
$inPhoto = cmsPhoto::getInstance();

if (!$inUser->update()) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

if ($inConf->siteoff && !$inUser->is_admin){ header("HTTP/1.1 500 File Upload Error"); exit(0); }

cmsCore::loadModel('photos');
$model = new cms_model_photos();
if(!$model->config['component_enabled']) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

$album = $inDB->getNsCategory('cms_photo_albums', cmsCore::request('album_id', 'int', 0));
if (!$album) { header("HTTP/1.1 500 File Upload Error"); exit(0); }
$album = cmsCore::callEvent('GET_PHOTO_ALBUM', $album);
if (!$album['published'] && !$inUser->is_admin) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

if (!$album['public'] && !$inUser->is_admin){ header("HTTP/1.1 500 File Upload Error"); exit(0); }
$today_uploaded = $album['uplimit'] ? $model->loadedByUser24h($inUser->id, $album['id']) : 0;
if (!$inUser->is_admin && $album['uplimit'] && $today_uploaded >= $album['uplimit']){
    header("HTTP/1.1 500 File Upload Error"); exit(0);
}

// Массив с первого шага
$photo = cmsUser::sessionGet('mod');
if (!$photo) { header("HTTP/1.1 500 Internal Server Error"); exit(0); }

// Загружаем фото
$file = $model->initUploadClass($album)->uploadPhoto();

if ($file) {

    if (!cmsCore::inRequest('upload')) {
        $last_id = $inDB->get_field('cms_photo_files', 'published=1 ORDER BY id DESC', 'id');
    }

    $photo['album_id']  = $album['id'];
    $photo['file']      = $file['filename'];
    $photo['title']     = $photo['title'] ? $photo['title'] . $last_id : $file['realfile'];
    $photo['published'] = ($inUser->is_admin || $album['public'] == 2) ? 1 : 0;
    $photo['owner']     = 'photos';
    $photo['user_id']   = $inUser->id;

    $photo['id'] = $inPhoto->addPhoto($photo);

    if($photo['published']){

        cmsCore::callEvent('ADD_PHOTO_DONE', $photo);

        $description = '<a href="/photos/photo'.$photo['id'].'.html" class="act_photo"><img src="/images/photos/small/'.$photo['file'].'" alt="'.htmlspecialchars(stripslashes($photo['title'])).'" /></a>';

        cmsActions::log('add_photo', array(
              'object' => $photo['title'],
              'object_url' => '/photos/photo'.$photo['id'].'.html',
              'object_id' => $photo['id'],
              'target' => $album['title'],
              'target_id' => $album['id'],
              'target_url' => '/photos/'.$album['id'],
              'description' => $description
        ));

    }

    if(!$photo['published']) {

        $message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_PHOTO_SUBMIT']);
        $message = str_replace('%photos%', '<a href="/photos/photo'.$photo['id'].'.html">'.$photo['title'].'</a>', $message);
        $message = str_replace('%album%', '<a href="/photos/'.$album['id'].'">'.$album['title'].'</a>', $message);

        cmsUser::sendMessage(USER_UPDATER, 1, $message);

        cmsCore::addSessionMessage($_LANG['PHOTO_PREMODER_TEXT'], 'info');

    }

    if (cmsCore::inRequest('upload')) { cmsCore::redirect('/photos/'.$album['id'].'/uploaded.html'); }

    echo "FILEID:" . $photo['id'];

} else {

    header("HTTP/1.1 500 Internal Server Error");
    echo $inCore->uploadError();

}