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

function rss_content($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('content')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

	cmsCore::loadModel('content');
	$model = new cms_model_content();

	$channel = array();
	$items   = array();

	if ($item_id){

		$cat = $inDB->getNsCategory('cms_category', (int)$item_id);
		if (!$cat) { return false; }

		$cat = cmsCore::callEvent('GET_CONTENT_CAT', $cat);

		if (!$cat['published']) { return false; }

		if(!cmsCore::checkUserAccess('category', $cat['id']) ){
			return false;
		}

		$model->whereThisAndNestedCats($cat['NSLeft'], $cat['NSRight']);

		$channel['title'] = $cat['title'] ;
		$channel['description'] = $cat['description'];
		$channel['link'] = HOST . $model->getCategoryURL(0, $cat['seolink']);

	} else {

		$channel['title'] = $_LANG['NEW_MATERIALS'];
		$channel['description'] = $_LANG['LAST_ARTICLES_NEWS'];
		$channel['link'] = HOST.'/content';

	}

	$inDB->where("con.showlatest = 1");

	$inDB->orderBy('con.pubdate', 'DESC');
	$inDB->limit($cfg['maxitems']);

	$content = $model->getArticlesList();

	if($content){
		foreach($content as $con){

			$con['link']     = HOST . $con['url'];
			$con['comments'] = $con['link'].'#c';
			$con['category'] = $con['cat_title'];

			if($con['image']){
				$con['size']  = round(filesize(PATH.'/images/photos/small/'.$con['image']));
				$con['image'] = HOST . '/images/photos/small/'.$con['image'];
			}

			$items[] = $con;

		}
	}

	return array('channel' => $channel,
				 'items' => $items);

}