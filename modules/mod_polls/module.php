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

function mod_polls($mod, $cfg){

    cmsCore::loadModel('polls');
    $model = new cms_model_polls();

    if ($cfg['poll_id']>0){

        $poll = $model->getPoll($cfg['poll_id']);

    } else {

        $poll = $model->getPoll(0, 'RAND()');

    }

    if (!$poll) { return false; }

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('poll', $poll)->
            assign('is_voted', $model->isUserVoted($poll['id']))->
            assign('module_id', $mod['id'])->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}