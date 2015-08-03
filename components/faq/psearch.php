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

function search_faq($query, $look){

	$inDB   = cmsDatabase::getInstance();
	$searchModel = cms_model_search::initModel();

	global $_LANG;

	$sql = "SELECT con.*, cat.title cat_title, cat.id cat_id
			FROM cms_faq_quests con
			INNER JOIN cms_faq_cats cat ON cat.id = con.category_id AND cat.published = 1
			WHERE MATCH(con.quest, con.answer) AGAINST ('$query' IN BOOLEAN MODE) AND con.published = 1 LIMIT 100";

	$result = $inDB->query($sql);

	if ($inDB->num_rows($result)){

		cmsCore::loadLanguage('components/faq');

		while($item = $inDB->fetch_assoc($result)){

			$result_array = array();

			$result_array['link']        = '/faq/quest'.$item['id'].'.html';
			$result_array['place']       = $_LANG['FAQ'].' &rarr; '.$item['cat_title'];
			$result_array['placelink']   = '/faq/'.$item['cat_id'];
			$result_array['description'] = $searchModel->getProposalWithSearchWord($item['answer']);
			$result_array['title']       = mb_substr($item['quest'], 0, 70).'...';
			$result_array['pubdate']     = $item['pubdate'];
			$result_array['session_id']  = session_id();

			$searchModel->addResult($result_array);
		}
	}

	return;

}