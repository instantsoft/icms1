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

function rss_comments($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('comments')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

    cmsCore::loadModel('comments');
    $model = new cms_model_comments();

	$channel = array();
	$items   = array();

	if ($item_id){

		// Выделяем назначение и id назначения
		$target_array = explode('-', $item_id);

		$target_str = $target_array[0];
		$target_id  = (int)$target_array[1];

		$target = $inDB->get_fields('cms_comment_targets', "target='{$target_str}'", '*');
		if(!$target){ return false; }

		if(cmsCore::loadModel($target['component'])){

			$model_class = 'cms_model_'.$target['component'];
			if(class_exists($model_class)){
				$target_model = new $model_class();
			}

		}

		if (!isset($target_model)) { return false; }

		$target_data = $target_model->getCommentTarget($target_str, $target_id);
		if (!$target_data) { return false; }

		$model->whereTargetIs($target_str, $target_id);

		$channel['title']       = $target_data['title'];
		$channel['description'] = $target['title'];
		$channel['link']        = HOST . $target_data['link'];

	} else {

		$channel['title']       = $_LANG['COMMENTS_ON_SITE'];
		$channel['description'] = $_LANG['COMMENTS_ON_SITE'];
		$channel['link']        = HOST.'/comments';

	}

	$model->whereIsShow();

	$inDB->orderBy('c.pubdate', 'DESC');

	$inDB->limit($cfg['maxitems']);

	$comments = $model->getComments(true, false, true);

	if($comments){
		foreach($comments as $comment){

			$comment['title']    = $comment['content'];
            $comment['link']     = HOST . $comment['target_link'] . '#c' . $comment['id'];
            $comment['comments'] = HOST . $comment['target_link'] . '#c' . $comment['id'];
            $comment['category'] = $target_data['title'];
            $items[]             = $comment;

        }
	}

	return array('channel' => $channel,
				 'items' => $items);

}