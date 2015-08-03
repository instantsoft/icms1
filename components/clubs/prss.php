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

function rss_clubs($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('clubs')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

	cmsCore::loadModel('clubs');
	$model = new cms_model_clubs();

	$inBlog = $model->initBlog();

	$channel = array();
	$items   = array();

	// Формируем канал
	if ($item_id){

		$blog = $inBlog->getBlog($item_id);
		if (!$blog) { return false; }

		$club = $model->getClub($blog['user_id']);
		if(!$club) { return false; }

		if(!$club['enabled_blogs']){ return false; }
		if ($club['clubtype']=='private'){ return false; }

		$inBlog->whereBlogIs($blog['id']);

		$channel['title']       = $blog['title'];
		$channel['description'] = $_LANG['NEW_POSTS_IN_CLUB_BLOG'].' '.$club['title'];
		$channel['link']        = HOST.'/clubs/'.$club['id'];

	} else {

		$channel['title']       = $_LANG['NEW_POSTS_IN_CLUB_BLOGS'];
		$channel['description'] = $_LANG['NEW_POSTS_IN_CLUB_BLOGS'];
		$channel['link']        = HOST.'/clubs';

	}

	// В RSS всегда только публичные посты
	$inBlog->whereOnlyPublic();

    $inDB->orderBy('p.pubdate', 'DESC');

    $inDB->limit($cfg['maxitems']);

	$inDB->addSelect('b.user_id as bloglink');

	$posts = $inBlog->getPosts(false, $model, true);

	if($posts){
		foreach($posts as $post){

			$post['link']        = HOST . $post['url'];
			$post['description'] = mb_substr(strip_tags($post['content_html']), 0, 350). '...';
			$post['comments'] = $post['link'].'#c';
			$post['category'] = $post['blog_title'];
			$items[] = $post;

		}
	}

	return array('channel' => $channel,
				 'items' => $items);

}

?>