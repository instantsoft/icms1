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
    function smarty_function_comments($params, &$smarty) {

        if (!$params['target']) { return false; }
        if (!$params['target_id']) { return false; }

        cmsCore::includeComments();

        comments($params['target'], $params['target_id'], (is_array($params['labels']) ? $params['labels'] : array()), (isset($params['can_delete']) ? $params['can_delete'] : false));

        return;

    }