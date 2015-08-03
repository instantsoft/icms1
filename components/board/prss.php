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

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function rss_board($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('board')) { return false; }

	$inDB = cmsDatabase::getInstance();

    cmsCore::loadModel('board');
    $model = new cms_model_board();

    global $_LANG;

	$channel = array();
	$items   = array();

	if ($item_id && preg_match('/^([0-9]+)$/ui', $item_id)) {

		$cat = $model->getCategory($item_id);
		if(!$cat) { return false; }

		$model->whereCatIs($cat['id']);

		$channel['title']       = $cat['title'];
		$channel['description'] = preg_replace ("'&([a-z]{2,5});'iu", '', $cat['description']);
		$channel['link']        = HOST.'/board/'.$cat['id'];

	} else {

		$channel['title'] = $_LANG['BOARD'];
		$channel['description'] = $_LANG['BOARD'];
		$channel['link'] = HOST;

	}

	$inDB->orderBy('pubdate', 'DESC');

	$inDB->limit($cfg['maxitems']);

	$advs = $model->getAdverts(false, false, false, true);

	if($advs){
		foreach($advs as $item){

			$item['link']     = HOST.'/board/read'.$item['id'].'.html';
			$item['comments'] = $item['link'].'#c';
			$item['category'] = $item['cat_title'];
			$item['description'] = mb_substr(strip_tags($item['content']), 0, 250). '...';
			$image_file = PATH.'/images/board/small/'.$item['file'];
			$image_url  = HOST.'/images/board/small/'.$item['file'];
			$item['image'] = file_exists($image_file) ? $image_url : '';
			$item['size']  = round(filesize($image_file));
			$items[] = $item;

		}
	}

	return array('channel' => $channel,
				 'items' => $items);

}


?>