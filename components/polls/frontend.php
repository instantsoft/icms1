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

function polls(){

    $model = new cms_model_polls();

    global $_LANG;

    $do = cmsCore::getInstance()->do;

//========================================================================================================================//
//========================================================================================================================//
    if ($do=='view'){

        $answer  = cmsCore::request('answer', 'str', '');
        $poll_id = cmsCore::request('poll_id', 'int');

        if (!$answer || !$poll_id) {
            if(cmsCore::isAjax()){
                cmsCore::jsonOutput(array('error' => true, 'text'  => $_LANG['SELECT_THE_OPTION']));
            } else {
                cmsCore::error404();
            }
        }

        $poll = $model->getPoll($poll_id);
        if (!$poll) { cmsCore::jsonOutput(array('error' => true, 'text'  => '')); }

        if($model->isUserVoted($poll_id)){ cmsCore::jsonOutput(array('error' => true, 'text'  => '')); }

        if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

        $model->votePoll($poll, $answer);

        cmsCore::jsonOutput(array('error' => false, 'text'  => $_LANG['VOTE_ACCEPTED']));

    }

}
?>