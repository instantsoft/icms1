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

function mod_latest($mod, $cfg){

	$inDB = cmsDatabase::getInstance();

	cmsCore::loadModel('content');
	$model = new cms_model_content();

	if (!isset($cfg['showrss'])) { $cfg['showrss'] = 1; }
	if (!isset($cfg['subs'])) { $cfg['subs'] = 1; }
	if (!isset($cfg['cat_id'])) { $cfg['cat_id'] = 1; }
	if (!isset($cfg['newscount'])) { $cfg['newscount'] = 5; }
	if (!isset($cfg['is_pag'])) { $cfg['is_pag'] = 0; }
	if (!isset($cfg['page'])) { $cfg['page'] = 1; }

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

	$inDB->where("con.showlatest = 1");

	if ($cfg['is_pag']){
		$total = $model->getArticlesCount();
	}

	$inDB->orderBy('con.pubdate', 'DESC');
	$inDB->limitPage($cfg['page'], $cfg['newscount']);

	$content_list = $model->getArticlesList();
	if(!$content_list) { return false; }

    $pagebar = $cfg['is_pag'] ?
                    cmsPage::getPagebar($total, $cfg['page'], $cfg['newscount'], 'javascript:conPage(%page%, '.$mod['id'].')') : '';

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('articles', $content_list)->
            assign('pagebar_module', $pagebar)->
            assign('module_id', $mod['id'])->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

	return true;

}