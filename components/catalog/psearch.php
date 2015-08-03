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

function search_catalog($query, $look){

        $inDB   = cmsDatabase::getInstance();
		$searchModel = cms_model_search::initModel();

		$sql = "SELECT i.*, c.title as cat, c.id as cat_id
				FROM cms_uc_items i
				INNER JOIN cms_uc_cats c ON c.id = i.category_id AND c.published = 1
				WHERE MATCH(i.title, i.fieldsdata) AGAINST ('$query' IN BOOLEAN MODE) AND i.published = 1 LIMIT 100";

		$result = $inDB->query($sql);

		if ($inDB->num_rows($result)){

			while($item = $inDB->fetch_assoc($result)){

				$result_array = array();

				$result_array['link']        = "/catalog/item".$item['id'].".html";
				$result_array['place']       = $item['cat'];
				$result_array['placelink']   = "/catalog/".$item['cat_id'];
				$result_array['title']       = $item['title'];
				$result_array['pubdate']     = $item['pubdate'];
                $result_array['imageurl']    = (file_exists(PATH.'/images/catalog/medium/'.$item['imageurl']) ? '/images/catalog/medium/'.$item['imageurl'] : '');
				$result_array['session_id']  = session_id();

				$searchModel->addResult($result_array);
			}
		}

		return;
}