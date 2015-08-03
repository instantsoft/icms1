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

function rss_catalog($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('catalog')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

	$channel = array();
	$items   = array();

	//CHANNEL
	if ($item_id){
		$cat = $inDB->get_fields('cms_uc_cats', "id='$item_id'", 'id, title, description, NSLeft, NSRight');
		$catsql = "AND cat.NSLeft >= {$cat['NSLeft']} AND cat.NSRight <= {$cat['NSRight']}";
		$channel['title']       = $cat['title'] ;
		$channel['description'] = $cat['description'];
		$channel['link']        = HOST . '/catalog/' . $item_id;
	} else {
		$catsql = '';
		$channel['title']       = $_LANG['NEW_RECORDS'];
		$channel['description'] = $_LANG['NEW_RECORDS'];
		$channel['link']        = HOST . '/catalog';
	}

	//ITEMS
	$sql = "SELECT c.*, cat.title as category
			FROM cms_uc_items c, cms_uc_cats cat
			WHERE c.published=1 AND c.category_id = cat.id $catsql
			ORDER by c.pubdate DESC
			LIMIT {$cfg['maxitems']}";

	$rs = $inDB->query($sql) or die('RSS building error!');

	$items = array();

	if ($inDB->num_rows($rs)){

		while ($item = $inDB->fetch_assoc($rs)){
			$id = $item['id'];
			$items[$id] = $item;
			$items[$id]['link']     = HOST . '/catalog/item'.$id.'.html';
			$items[$id]['comments'] = $items[$id]['link'].'#c';
			$items[$id]['category'] = $item['category'];

			$image_file = PATH.'/images/catalog/medium/'.$item['imageurl'];
			$image_url  = HOST . '/images/catalog/medium/'.$item['imageurl'];

			$items[$id]['image'] = file_exists($image_file) ? $image_url : '';
			$items[$id]['size'] = $items[$id]['image'] ? round(filesize($image_file)) : 0;
		}

	}

	return array('channel' => $channel,
				 'items' => $items);

}