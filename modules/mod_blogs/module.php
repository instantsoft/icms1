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

function mod_blogs($mod, $cfg){

	$inDB = cmsDatabase::getInstance();

	$default_cfg = array (
				  'sort' => 'pubdate',
				  'owner' => 'user',
				  'shownum' => 5,
				  'minrate' => 0,
                  'blog_id' => 0,
				  'showrss' => 1
				);
	$cfg = array_merge($default_cfg, $cfg);

	cmsCore::loadClass('blog');
	$inBlog = cmsBlogs::getInstance();
	$inBlog->owner = $cfg['owner'];

	if($cfg['owner'] == 'club'){
		cmsCore::loadModel('clubs');
		$model = new cms_model_clubs();
		$inDB->addSelect('b.user_id as bloglink');
	} else {
		cmsCore::loadModel('blogs');
		$model = new cms_model_blogs();
	}

	// получаем аватары владельцев
	$inDB->addSelect('up.imageurl, img.fileurl');
	$inDB->addJoin('LEFT JOIN cms_user_profiles up ON up.user_id = u.id');
	$inDB->addJoin("LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'");

	$inBlog->whereOnlyPublic();

	if($cfg['minrate']){
		$inBlog->ratingGreaterThan($cfg['minrate']);
	}

	if($cfg['blog_id']){
		$inBlog->whereBlogIs($cfg['blog_id']);
	}

    $inDB->orderBy('p.'.$cfg['sort'], 'DESC')->groupBy('p.id');
    $inDB->limit($cfg['shownum']);

	$posts = $inBlog->getPosts(false, $model);
	if(!$posts){ return false; }

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('posts', $posts)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

	return true;

}