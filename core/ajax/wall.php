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

$target_id = cmsCore::request('target_id', 'int', 0);
$component = preg_replace ('/[^a-z0-9_\-]/ui', '', cmsCore::request('component', 'str', 'users'));
$page      = cmsCore::request('page', 'int', 1);
$do        = cmsCore::request('do_wall', 'str', 'view');

$my_profile = false;
$is_admin   = false;

// Убедимся, что место назначения передали
if(!$target_id) { cmsCore::halt(); }
// Проверяем установлен ли компонент
if(!$inCore->isComponentInstalled($component)) { cmsCore::halt(); }
// Загружаем конфигурацию компонента
$cfg = $inCore->loadComponentConfig($component);
// проверяем не выключен ли он
if(!$cfg['component_enabled']) { cmsCore::halt(); }

// Подключаем модель компонента
cmsCore::loadModel($component);
$model_class = 'cms_model_'.$component;
if(class_exists($model_class)){
	$model = new $model_class();
}

// получаем у модели флаг, мое ли место назначения
if(method_exists($model, 'forWallIsMyProfile')){
	$my_profile = $model->forWallIsMyProfile($target_id);
}

// получаем у модели флаг, администратор ли я места назначения
if(method_exists($model, 'forWallIsAdmin')){
	$is_admin = $model->forWallIsAdmin($target_id);
}

// проверяем наличие метда для добавления записи
if(!method_exists($model, 'addWall')){ cmsCore::halt(); }

cmsCore::loadLanguage('components/users');

/* ==================================================================================================== */
/* ==================================================================================================== */
if($do == 'view'){

	$inDB->limitPage($page, $cfg['wall_perpage']);

	echo cmsUser::getUserWall($target_id, $component, $my_profile, $is_admin);

}
/* ==================================================================================================== */
/* ==================================================================================================== */
if($do == 'add'){

    if (!$inUser->id) { cmsCore::jsonOutput(array('error' => true, 'text' => $_LANG['ONLY_REG_USER_CAN_WALL'])); }

	if (!cmsCore::inRequest('submit')){

		ob_start();

        cmsPage::initTemplate('components', 'com_users_addwall')->
                assign('target_id', $target_id)->
                assign('component', $component)->
                assign('bb_toolbar', cmsPage::getBBCodeToolbar('message', true, $component, 'wall'))->
                assign('smilies', cmsPage::getSmilesPanel('message'))->
                display('com_users_addwall.tpl');

		cmsCore::jsonOutput(array('error' => false, 'html' => ob_get_clean()));

	}

	$message = $inDB->escape_string(cmsCore::parseSmiles(cmsCore::request('message', 'html', ''), true));

	if (mb_strlen($message)<2) {
		cmsCore::jsonOutput(array('error' => true, 'text'  => $_LANG['ERR_SEND_WALL']));
	}

	if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

	// добавляем запись методом модели места назначения
	$wall_id = $model->addWall(array('user_id'=>$target_id,
									 'author_id'=>$inUser->id,
									 'nickname'=>$inUser->nickname,
									 'content'=>$message,
									 'usertype'=>$component,
									 'pubdate'=>date('Y-m-d H:i:s')));

	if($wall_id){

		// регистрируем загруженные фотографии к записи
		cmsCore::setIdUploadImage('wall', $wall_id);

		cmsCore::jsonOutput(array('error' => false, 'html'  => $_LANG['WALL_MESG_ADD']));

	} else {

		cmsCore::jsonOutput(array('error' => true, 'text'  => $_LANG['ERR_SUBMIT_WALL']));

	}

}
/* ==================================================================================================== */
/* ==================================================================================================== */
if($do == 'delete'){

    if (!$inUser->id) { cmsCore::halt(); }

    if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

	$record_id = cmsCore::request('record_id', 'int', 0);
    if (!$record_id) { cmsCore::halt(); }

    $is_author = $inDB->rows_count('cms_user_wall', "id = '$record_id' AND author_id = '{$inUser->id}'");

    if($is_author || $is_admin || $my_profile){
        $model->deleteWallRecord($record_id);
    }

    cmsCore::halt($_LANG['WALL_MESG_DEL']);

}

cmsCore::halt();

?>