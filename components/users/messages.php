<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }
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
$opt      = cmsCore::request('opt', 'str', 'in');
$whith_id = cmsCore::request('with_id', 'int', 0);
$perpage = 15;
$show_notice = false;

$new_msg = $inUser->getNewMsg();

$friends = cmsUser::getFriends($inUser->id);
$interlocutors = cmsCore::getListItems("cms_users u INNER JOIN cms_user_msg m ON m.from_id = u.id",
                 $whith_id, 'm.from_id', 'ASC', "m.from_del = 0 AND m.to_del = 0 AND m.to_id = '{$id}' GROUP BY m.from_id", 'from_id', 'nickname');

// каких сообщений больше, то и показываем
if($new_msg['messages'] > $new_msg['notices']){
    $opt = 'in';
}
if($new_msg['notices'] > $new_msg['messages']){
    $opt = 'notices';
}

switch ($opt){

    case 'in':

        $page_title = $_LANG['INBOX'];

        $inDB->addJoin("INNER JOIN cms_user_msg m ON m.from_id = u.id AND m.to_id = '$id' AND m.to_del = 0");

        $msg_count = $model->getMessagesCount();

        $pagebar = cmsPage::getPagebar($msg_count, $page, $perpage, 'javascript:centerLink(\'/users/'.$id.'/messages%page%.html\')');

        break;

    case 'out':

        $page_title = $_LANG['SENT'];

        $inDB->addJoin("INNER JOIN cms_user_msg m ON m.to_id = u.id AND m.from_id = '$id' AND m.from_del = 0");

        $msg_count = $model->getMessagesCount();

        $pagebar = cmsPage::getPagebar($msg_count, $page, $perpage, 'javascript:centerLink(\'/users/'.$id.'/messages-sent%page%.html\')');

        break;

    case 'notices':

        $page_title = $_LANG['NOTICES'];

        $show_notice = true;

        $inDB->where("m.to_id = '$id'");

        $msg_count = $model->getMessagesCount($show_notice);

        $pagebar = cmsPage::getPagebar($msg_count, $page, $perpage, 'javascript:centerLink(\'/users/'.$id.'/messages-notices%page%.html\')');

        break;

    case 'history':

        if($whith_id){

            $with_usr = cmsUser::getShortUserData($whith_id);
            if (!$with_usr) { cmsCore::error404(); }

            $page_title = $_LANG['MESSEN_WITH'].' '.$with_usr['nickname'];

            $inDB->addJoin("INNER JOIN cms_user_msg m ON m.from_id = u.id AND
                            m.from_id IN ({$id}, {$with_usr['id']}) AND
                            m.to_id IN ({$id}, {$with_usr['id']}) AND
                            m.from_del = 0 AND m.to_del = 0");

            $msg_count = $model->getMessagesCount();

            $pagebar = cmsPage::getPagebar($msg_count, $page, $perpage, 'javascript:centerLink(\'/users/'.$id.'/messages-history'.$with_usr['id'].'-%page%.html\')');

        } else {

            $page_title = $_LANG['DIALOGS'];
            $msg_count = 0;

        }

        break;

    default: return;

}

$inDB->orderBy('m.id', 'DESC');
$inDB->limitPage($page, $perpage);

$records = $msg_count ?
                    $model->getMessages($show_notice) :
                    array(); $inDB->resetConditions();

if($new_msg['messages'] && $opt == 'in'){
    $model->markAsReadMessage($id, $perpage);
}
if($new_msg['notices'] && $opt == 'notices'){
    $model->markAsReadMessage($id, $perpage, false);
}

$inPage->addPathway($page_title);

cmsPage::initTemplate('components', 'com_users_messages')->
        assign('opt', $opt)->
        assign('id', $id)->
        assign('is_admin', $inUser->is_admin)->
        assign('page_title', $page_title)->
        assign('with_usr', isset($with_usr) ? $with_usr : array())->
        assign('msg_count', $msg_count)->
        assign('pagebar', $pagebar)->
        assign('new_messages', $new_msg)->
        assign('friends', isset($friends) ? $friends : array())->
        assign('interlocutors', isset($interlocutors) ? $interlocutors : array())->
        assign('records', $records)->
        display('com_users_messages.tpl');