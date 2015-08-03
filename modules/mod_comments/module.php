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

function mod_comments($mod, $cfg){

	$inDB   = cmsDatabase::getInstance();
	$inUser = cmsUser::getInstance();

	if (!isset($cfg['showrss'])) { $cfg['showrss'] = 1;}
	if (!isset($cfg['minrate'])) { $cfg['minrate'] = 0;}
	if (!isset($cfg['showguest'])) { $cfg['showguest'] = 0;}
	if (!sizeof($cfg['targets'])){ return true; }

	cmsCore::loadModel('comments');
	$model = new cms_model_comments();
	$model->initAccess();

	// Комментарии только нужного назначения
	$model->whereTargetIn($cfg['targets']);
	// Если не показывать гостей, добавляем условие
	if(!$cfg['showguest']){ $model->whereOnlyUsers(); }
	// Администраторам и админам показываем все комментарии
	if(!($inUser->is_admin || $model->is_can_moderate)){
		$model->whereIsShow();
	}
	// Комментарии в зависимости от рейтинга
	if($cfg['minrate'] <> 0){
		$model->whereRatingOver($cfg['minrate']);
	}

	$inDB->orderBy('c.pubdate', 'DESC');
	$inDB->limitPage(1, $cfg['shownum']);

	$comments = $model->getComments(true, false, true);
	if(!$comments) { return false; }

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('comments', $comments)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

	return true;

}