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

    if(!$inCore->isComponentEnable('comments')) { cmsCore::halt(); }

	cmsCore::loadLib('karma');

    $comment_id = cmsCore::request('comment_id', 'int');
    $vote       = cmsCore::request('vote', 'int');

	if(!$comment_id || abs($vote) != 1) { cmsCore::halt(); }

	$com_user_id = $inDB->get_field('cms_comments', "id='$comment_id'", 'user_id');
	if(!$com_user_id) { cmsCore::halt(); }

    if ($inUser->id != $com_user_id){
        cmsSubmitKarma('comment', $comment_id, $vote);
    }

    $karma = cmsKarma('comment', $comment_id);

    if ($karma['points']>0){
        $karma['points'] = '<span class="cmm_good">+'.$karma['points'].'</span>';
    } elseif ($karma['points']<0){
        $karma['points'] = '<span class="cmm_bad">'.$karma['points'].'</span>';
    }

    echo $karma['points'];

?>