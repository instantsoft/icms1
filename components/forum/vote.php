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

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

	if(!$inUser->id) { cmsCore::halt(); }

	cmsCore::loadLib('karma');

    $post_id = cmsCore::request('post_id', 'int');
    $vote    = cmsCore::request('vote', 'int');

	if(!$post_id || abs($vote) != 1) { cmsCore::halt(); }

	$user_id = $inDB->get_field('cms_forum_posts', "id='$post_id'", 'user_id');
	if(!$user_id) { cmsCore::halt(); }

    if ($inUser->id != $user_id){
        cmsSubmitKarma('forum_post', $post_id, $vote);
    }

    $karma = cmsKarma('forum_post', $post_id);

    if ($karma['points']>0){
        $karma['points'] = '<span class="cmm_good">+'.$karma['points'].'</span>';
    } elseif ($karma['points']<0){
        $karma['points'] = '<span class="cmm_bad">'.$karma['points'].'</span>';
    }

    echo $karma['points'];

?>