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

function mod_bestcontent($mod, $cfg){

	$inDB = cmsDatabase::getInstance();

	cmsCore::loadModel('content');
	$model = new cms_model_content();

	if (!isset($cfg['shownum'])){ $cfg['shownum'] = 5; }
	if (!isset($cfg['subs'])) { $cfg['subs'] = 1; }
	if (!isset($cfg['cat_id'])) { $cfg['cat_id'] = 1; }

	$inDB->where("con.canrate = 1");

	if($cfg['cat_id']){

		if (!$cfg['subs']){

			//выбираем из категории
			$model->whereCatIs($cfg['cat_id']);

		} else {

			//выбираем из категории и подкатегорий
			$rootcat = $inDB->getNsCategory('cms_category', $cfg['cat_id']);
			if(!$rootcat) { return false; }
			$model->whereThisAndNestedCats($rootcat['NSLeft'], $rootcat['NSRight']);

		}

	}

	$inDB->orderBy('con.rating', 'DESC');
	$inDB->limitPage(1, $cfg['shownum']);

	$content_list = $model->getArticlesList();

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('articles', $content_list)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

	return true;

}