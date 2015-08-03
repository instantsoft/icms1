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

cmsCore::loadLanguage('components/registration');

$opt  = cmsCore::request('opt', 'str', '');
$data = cmsCore::request('data', 'str', '');

if (!$data) { cmsCore::halt(); }

if(mb_strlen($data)<2 ||
        mb_strlen($data)>15 ||
        is_numeric($data) ||
        !preg_match("/^([a-z0-9])+$/ui", $data)) {

    cmsCore::halt('<span style="color:red">'.$_LANG['ERR_LOGIN'].'</span>');

}

if ($opt=='checklogin'){

    $sql    = "SELECT id, login FROM cms_users WHERE LOWER(login) = '".mb_strtolower($data)."' AND is_deleted = 0 LIMIT 1";
    $result = $inDB->query($sql);

    if($inDB->num_rows($result)==0){
        echo '<span style="color:green">'.$_LANG['YOU_LOGIN_COMPLETE'].'</span>';
    } else {
        echo '<span style="color:red">'.$_LANG['LOGIN'].' "'.$data.'" '.$_LANG['IS_BUSY'].'</span>';
    }

}

cmsCore::halt();