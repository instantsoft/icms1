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

if (!$inUser->update()) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

if ($inConf->siteoff && !$inUser->is_admin){ header("HTTP/1.1 500 File Upload Error"); exit(0); }

cmsCore::loadModel('clubs');
$model = new cms_model_clubs();

$inPhoto = $model->initPhoto();

$album = $inDB->getNsCategory('cms_photo_albums', cmsCore::request('album_id', 'int', 0), null);
if (!$album) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

$club = $model->getClub($album['user_id']);
if(!$club) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

// если фотоальбомы запрещены
if(!$club['enabled_photos']){ header("HTTP/1.1 500 File Upload Error"); exit(0); }

// Инициализируем участников клуба
$model->initClubMembers($club['id']);
// права доступа
$is_admin  = $inUser->is_admin || ($inUser->id == $club['admin_id']);
$is_moder  = $model->checkUserRightsInClub('moderator');
$is_member = $model->checkUserRightsInClub('member');

$is_karma_enabled = (($inUser->karma >= $club['photo_min_karma']) && $is_member) ? true : false;

if(!$is_admin && !$is_moder && !$is_karma_enabled) { header("HTTP/1.1 500 File Upload Error"); exit(0); }

// Массив с первого шага
$photo = cmsUser::sessionGet('mod');
if (!$photo) { header("HTTP/1.1 500 Internal Server Error"); exit(0); }

// Загружаем фото
$file = $model->initUploadClass()->uploadPhoto();

if ($file) {

    if (!cmsCore::inRequest('upload')) {
        $last_id = $inDB->get_field('cms_photo_files', 'published=1 ORDER BY id DESC', 'id');
    }

    $photo['album_id']  = $album['id'];
    $photo['file']      = $file['filename'];
    $photo['title']     = $photo['title'] ? $photo['title'] . $last_id : $file['realfile'];
    $photo['published'] = ($is_admin || $is_moder) ? 1 : (int)!$club['photo_premod'];
    $photo['owner']     = 'club'.$club['id'];
    $photo['user_id']   = $inUser->id;

    $photo_id = $inPhoto->addPhoto($photo);

    if($photo['published']) {

        $description = $club['clubtype']=='private' ? '' :
                       '<a href="/clubs/photo'.$photo_id.'.html" class="act_photo"><img border="0" src="/images/photos/small/'.$photo['file'].'" /></a>';

        cmsActions::log('add_photo_club', array(
              'object' => $photo['title'],
              'object_url' => '/clubs/photo'.$photo_id.'.html',
              'object_id' => $photo_id,
              'target' => $club['title'],
              'target_id' => $photo['album_id'],
              'target_url' => '/clubs/'.$club['id'],
              'description' => $description
        ));

    }

    if(!$photo['published']) {

        $message = sprintf($_LANG['MSG_CLUB_PHOTO_SUBMIT'],
                cmsUser::getProfileLink($inUser->login, $inUser->nickname),
                '<a href="/clubs/photo'.$photo_id.'.html">'.$photo['title'].'</a>',
                '<a href="/clubs/'.$club['id'].'">'.$club['title'].'</a>');

        cmsUser::sendMessage(USER_UPDATER, $club['admin_id'], $message);

        cmsCore::addSessionMessage($_LANG['PHOTO_PREMODER_TEXT'], 'info');

    }

    if (cmsCore::inRequest('upload')) { cmsCore::redirect('/clubs/uploaded'.$album['id'].'.html'); }

    echo "FILEID:" . $photo_id;

} else {

    header("HTTP/1.1 500 Internal Server Error");
    echo $inCore->uploadError();

}