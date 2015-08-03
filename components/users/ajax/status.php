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

    if (!$inUser->id) { cmsCore::halt(); }

    $status  = cmsCore::request('status', 'str', '');
    $user_id = cmsCore::request('id', 'int', 0);

    if (!$user_id) { $user_id = $inUser->id; }

    if ($user_id != $inUser->id && !$inUser->is_admin) { cmsCore::halt(); }

    if (mb_strlen($status)>140){ $status = mb_substr($status, 0, 140); }

    $sql = "UPDATE cms_users
            SET status = '{$status}', status_date = NOW()
            WHERE id = '{$user_id}'
            LIMIT 1";

    $inDB->query($sql);

    //регистрируем событие
    if ($status){
        cmsActions::log('set_status', array(
            'object' => '',
            'object_url' => '',
            'object_id' => 0,
            'target' => '',
            'target_url' => '',
            'target_id' => 0,
            'description' => $status,
            'user_id' => $user_id
        ));
    }

    cmsCore::halt();

?>
