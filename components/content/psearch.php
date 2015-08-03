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

function search_content($query, $look){

        $inDB   = cmsDatabase::getInstance();
		$searchModel = cms_model_search::initModel();

        global $_LANG;

		$sql = "SELECT con.*, cat.title cat_title, cat.id cat_id, cat.seolink as cat_seolink, cat.parent_id as cat_parent_id
				FROM cms_content con
				INNER JOIN cms_category cat ON cat.id = con.category_id AND cat.published = 1
				WHERE MATCH(con.title, con.content) AGAINST ('$query' IN BOOLEAN MODE) AND con.is_end = 0 AND con.published = 1 LIMIT 100";

		$result = $inDB->query($sql);

		if ($inDB->num_rows($result)){

			cmsCore::loadLanguage('components/content');

			while($item = $inDB->fetch_assoc($result)){

				$result_array = array();

				$result_array['link']        = "/".$item['seolink'].".html";
				$result_array['place']       = $_LANG['CATALOG_ARTICLES'];
				$result_array['placelink']   = $item['cat_parent_id']>0 ? "/".$item['cat_seolink'] : $link;
				$result_array['description'] = $searchModel->getProposalWithSearchWord($item['content']);
				$result_array['title']       = $item['title'];
				$result_array['pubdate']     = $item['pubdate'];
                $result_array['imageurl']    = (file_exists(PATH.'/images/photos/medium/article'.$item['id'].'.jpg') ? '/images/photos/medium/article'.$item['id'].'.jpg' : '');
				$result_array['session_id']  = session_id();

				$searchModel->addResult($result_array);
			}
		}

		return;

}