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

function search_clubs($query, $look){

	$inDB   = cmsDatabase::getInstance();
	$searchModel = cms_model_search::initModel();

	global $_LANG;

	cmsCore::loadModel('clubs');
	$model = new cms_model_clubs();

	/////// поиск по клубным блогам //////////

	$sql = "SELECT con.*, cat.title cat_title, cat.id cat_id, cat.owner owner, cat.user_id, img.fileurl
			FROM cms_blog_posts con
			INNER JOIN cms_blogs cat ON cat.id = con.blog_id AND cat.allow_who = 'all' AND cat.owner = 'club'
            LEFT JOIN cms_upload_images img ON img.target_id = con.id AND img.target = 'blog_post' AND img.component = 'clubs'
			WHERE MATCH(con.title, con.content) AGAINST ('$query' IN BOOLEAN MODE) AND con.published = 1 LIMIT 100";

	$result = $inDB->query($sql);

	if ($inDB->num_rows($result)){
		while($item = $inDB->fetch_assoc($result)){

			$result_array = array();

			$result_array['link']        = $model->getPostURL($item['user_id'], $item['seolink']);
			$result_array['place']       = ' &laquo;'.$item['cat_title'].'&raquo;';
			$result_array['placelink']   = $model->getBlogURL($item['user_id']);
			$result_array['description'] = $searchModel->getProposalWithSearchWord($item['content_html']);
			$result_array['title']       = $item['title'];
			$result_array['pubdate']     = $item['pubdate'];
            $result_array['imageurl']    = $item['fileurl'];
			$result_array['session_id']  = session_id();

			$searchModel->addResult($result_array);

		}
	}

	/////// поиск по клубным фоткам //////////

	$sql = "SELECT f.*, a.title as cat, a.id as cat_id
			FROM cms_photo_files f
			INNER JOIN cms_photo_albums a ON a.id = f.album_id AND a.published = 1 AND a.NSDiffer != ''
			WHERE MATCH(f.title, f.description) AGAINST ('$query' IN BOOLEAN MODE) AND f.published = 1";

	$result = $inDB->query($sql);

	if ($inDB->num_rows($result)){

		while($item = $inDB->fetch_assoc($result)){

			$result_array = array();

			$result_array['link']        = "/clubs/photo".$item['id'].".html";
			$result_array['place']       = $_LANG['CLUBS_PHOTOALBUM'] .' &laquo;'. $item['cat'].'&raquo;';
			$result_array['placelink']   = '/clubs/photoalbum'.$item['cat_id'];
			$result_array['description'] = $searchModel->getProposalWithSearchWord($item['description']);
			$result_array['title']       = $item['title'];
			$result_array['pubdate']     = $item['pubdate'];
            $result_array['imageurl']    = HOST.'/images/photos/medium/'.$item['file'] ? '/images/photos/medium/'.$item['file'] : '';
			$result_array['session_id']  = session_id();

			$searchModel->addResult($result_array);
		}
	}

	return;

}