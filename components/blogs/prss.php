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

function rss_blogs($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('blogs')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

	cmsCore::loadModel('blogs');
	$model = new cms_model_blogs();

	cmsCore::loadClass('blog');
	$inBlog = cmsBlogs::getInstance();
	$inBlog->owner = 'user';

	$channel = array();
	$items   = array();

	// Формируем канал
	if ($item_id){

		$blog = $inBlog->getBlog($item_id);
		if (!$blog) { return false; }

		//Если доступа к блогу нет, возвращаемся
		if (!cmsUser::checkUserContentAccess($blog['allow_who'], $blog['user_id'])){
			return false;
		}

		$inBlog->whereBlogIs($blog['id']);

		$channel['title']       = $blog['title'];
		$channel['description'] = $_LANG['NEW_POSTS_IN_BLOGS'];
		$channel['link']        = HOST . $model->getBlogURL($blog['seolink']);

	} else {

		$channel['title']       = $_LANG['NEW_POSTS_IN_BLOGS'];
		$channel['description'] = $_LANG['NEW_POSTS_IN_BLOGS'];
		$channel['link']        = HOST . '/blogs';

	}

	// В RSS всегда только публичные посты
	$inBlog->whereOnlyPublic();

    $inDB->orderBy('p.pubdate', 'DESC');

    $inDB->limit($cfg['maxitems']);

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