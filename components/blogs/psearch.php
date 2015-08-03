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

function search_blogs($query, $look){

	$inDB   = cmsDatabase::getInstance();
	$searchModel = cms_model_search::initModel();

	global $_LANG;

	$sql = "SELECT con.*, cat.title cat_title, cat.id cat_id, cat.owner owner, cat.user_id user_id, cat.seolink as bloglink, img.fileurl
			FROM cms_blog_posts con
			INNER JOIN cms_blogs cat ON cat.id = con.blog_id AND cat.allow_who = 'all' AND cat.owner = 'user'
            LEFT JOIN cms_upload_images img ON img.target_id = con.id AND img.target = 'blog_post' AND img.component = 'blogs'
			WHERE MATCH(con.title, con.content) AGAINST ('$query' IN BOOLEAN MODE) AND con.published = 1 LIMIT 100";

	$result = $inDB->query($sql);

	if ($inDB->num_rows($result)){

		cmsCore::loadLanguage('components/blogs');

		cmsCore::loadModel('blogs');
		$model = new cms_model_blogs();

		while($item = $inDB->fetch_assoc($result)){

			$result_array = array();

			$result_array['link']        = $model->getPostURL($item['bloglink'], $item['seolink']);
			$result_array['place']       = $_LANG['BLOG'].' &laquo;'.$item['cat_title'].'&raquo;';
			$result_array['placelink']   = $model->getBlogURL($item['bloglink']);
			$result_array['description'] = $searchModel->getProposalWithSearchWord($item['content_html']);
			$result_array['title']       = $item['title'];
			$result_array['pubdate']     = $item['pubdate'];
			$result_array['imageurl']    = $item['fileurl'];
			$result_array['session_id']  = session_id();

			$searchModel->addResult($result_array);

		}
	}

	return;

}