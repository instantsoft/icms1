<?php
if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }
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

$opt = cmsCore::request('opt', 'str', 'list');

$toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.optform.submit();');
$toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components');

cpToolMenu($toolmenu);

$cfg = $inCore->loadComponentConfig('registration');

if ($opt=='saveconfig'){

    if (!cmsCore::validateForm()) { cmsCore::error404(); }

    $cfg['reg_type']    = cmsCore::request('reg_type', 'str', '');
    $cfg['inv_count']   = cmsCore::request('inv_count', 'int', 0);
    $cfg['inv_karma']   = cmsCore::request('inv_karma', 'int', 0);
    $cfg['inv_period']  = cmsCore::request('inv_period', 'str', '');

    $cfg['default_gid'] = cmsCore::request('default_gid', 'int', 0);

    $cfg['is_on']       = cmsCore::request('is_on', 'int', 0);
    $cfg['act']         = cmsCore::request('act', 'int', 0);
    $cfg['send']        = cmsCore::request('send', 'int', 0);
    $cfg['offmsg']      = cmsCore::request('offmsg', 'html', '');

    $cfg['first_auth_redirect'] = cmsCore::request('first_auth_redirect', 'str', '');
    $cfg['auth_redirect']       = cmsCore::request('auth_redirect', 'str', '');

    $cfg['name_mode']       = cmsCore::request('name_mode', 'str', '');
	$cfg['badnickname']     = mb_strtolower(cmsCore::request('badnickname', 'html', ''));
    $cfg['ask_icq']         = cmsCore::request('ask_icq', 'int', 0);
    $cfg['ask_birthdate']   = cmsCore::request('ask_birthdate', 'int', 0);
    $cfg['ask_city']        = cmsCore::request('ask_city', 'int', 0);

    $cfg['send_greetmsg']   = cmsCore::request('send_greetmsg', 'int');
    $cfg['greetmsg']        = cmsCore::request('greetmsg', 'html', '');

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

    $inCore->saveComponentConfig('registration', $cfg);

    if (cmsCore::request('inv_now', 'int', 0)){

        cmsCore::loadModel('users');
        $model = new cms_model_users();

        $inv_count = $cfg['inv_count'];
        $inv_karma = $cfg['inv_karma'];

        if ($inv_count){
            $invites_given = $model->giveInvites($inv_count, $inv_karma);

            if ($invites_given){
                cmsCore::addSessionMessage($_LANG['AD_ISSUED_INVITES'].': '.$invites_given, 'success');
            } else {
                cmsCore::addSessionMessage($_LANG['AD_INVITES_NOT_ISSUED'], 'success');
            }
        }

    }

    if (cmsCore::request('inv_delete', 'int', 0)){

        cmsCore::loadModel('users');
        $model = new cms_model_users();

        $model->deleteInvites();

        cmsCore::addSessionMessage($_LANG['AD_INVITES_DELETE'], 'success');

    }

    cmsCore::redirectBack();

}

?>
<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id ?>" method="post" name="optform" target="_self" id="optform">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
<div id="config_tabs" style="margin-top:12px;" class="uitabs">

    <ul id="tabs">
        <li><a href="#basic"><span><?php echo $_LANG['AD_GENERAL']; ?></span></a></li>
        <li><a href="#form"><span><?php echo $_LANG['AD_FORM']; ?></span></a></li>
        <li><a href="#greets"><span><?php echo $_LANG['AD_WELCOME']; ?></span></a></li>
    </ul>

    <div id="basic">
        <table width="661" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td width="110"><strong><?php echo $_LANG['AD_REGISTRATION_TYPE']; ?>:</strong></td>
                <td>
                    <select name="reg_type" id="name_mode" style="width:300px" onchange="if($(this).val()=='invite'){ $('.inv').show(); } else { $('.inv').hide(); }">
                        <option value="open" <?php if ($cfg['reg_type']=='open') {echo 'selected';} ?>><?php echo $_LANG['AD_REGISTRATION_OPEN']; ?></option>
                        <option value="invite" <?php if ($cfg['reg_type']=='invite') {echo 'selected';} ?>><?php echo $_LANG['AD_REGISTRATION_INVITES']; ?></option>
                    </select>
                </td>
            </tr>
            <tr class="inv" <?php if($cfg['reg_type']=='open'){ ?>style="display:none"<?php } ?>>
                <td valign="top" style="padding-top:20px"><strong><?php echo $_LANG['AD_ISSUE_ON']; ?>:</strong></td>
                <td>
                    <table cellpadding="4" cellspacing="0" border="0">
                        <tr>
                            <td style="padding-left:0px;">
                                <input type="text" style="width:30px" name="inv_count" value="<?php echo $cfg['inv_count']; ?>">
                            </td>
                            <td> <?php echo $_LANG['AD_INVITES_KARMA']; ?> &ge; </td>
                            <td>
                                <input type="text" style="width:30px" name="inv_karma" value="<?php echo $cfg['inv_karma']; ?>">
                            </td>
                            <td> <?php echo $_LANG['AD_ONCE']; ?> </td>
                            <td>
                                <select name="inv_period">
                                    <option value="DAY" <?php if ($cfg['inv_period']=='DAY') {echo 'selected';} ?>><?php echo $_LANG['AD_DAY']; ?></option>
                                    <option value="WEEK" <?php if ($cfg['inv_period']=='WEEK') {echo 'selected';} ?>><?php echo $_LANG['AD_WEEKLY']; ?></option>
                                    <option value="MONTH" <?php if ($cfg['inv_period']=='MONTH') {echo 'selected';} ?>><?php echo $_LANG['AD_MONTH']; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left:0px;" colspan="5">
                                <input type="hidden" id="inv_now" name="inv_now" value="0" />
                                <input type="hidden" id="inv_delete" name="inv_delete" value="0" />
                                <input type="button" value="<?php echo $_LANG['AD_ISSUE_INVITES']; ?>" onclick="if(confirm('<?php echo $_LANG['AD_GIVE_INVITES']; ?>?')){ $('#inv_now').val('1'); $('#optform').submit(); }" />
                                <input type="button" value="<?php echo $_LANG['AD_DELETE_INVITES']; ?>" onclick="if(confirm('<?php echo $_LANG['AD_DELETE_INVITES_QUEST']; ?>?')){ $('#inv_delete').val('1'); $('#optform').submit(); }" />
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
        <table width="661" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td width="308"><strong><?php echo $_LANG['AD_REGISTRATION_ON']; ?>: </strong></td>
                <td width="313">
                    <label><input name="is_on" type="radio" value="1" <?php if ($cfg['is_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="is_on" type="radio" value="0" <?php if (!$cfg['is_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_REGISTRATION_OFF_POST']; ?>:</strong> </td>
                <td valign="top"><textarea  name="offmsg" type="text" id="offmsg" rows="2" style="border: solid 1px gray;width:300px;"><?php echo $cfg['offmsg'];?></textarea></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_ACTIVATION_MAIL']; ?>: </strong></td>
                <td>
                    <label><input name="act" type="radio" value="1" <?php if ($cfg['act']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="act" type="radio" value="0" <?php if (!$cfg['act']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_ACTIVATION_POST']; ?>:</strong> </td>
                <td><?php echo '/languages/'.cmsConfig::getConfig('lang').'/letters/activation.txt'; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_GROUP_DEFAULT']; ?>:</strong></td>
                <td>
                    <?php $groups = cmsUser::getGroups(true); ?>
                    <select name="default_gid" id="default_gid" style="width:300px">
                        <?php foreach($groups as $group){ ?>
                        <option value="<?php echo $group['id']; ?>" <?php if ($cfg['default_gid']==$group['id']){ ?>selected="selected"<?php } ?>><?php echo $group['title']; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FIRST_LOGIN']; ?>:</strong></td>
                <td>
                    <select name="first_auth_redirect" id="first_auth_redirect" style="width:300px">
                        <option value="none" <?php if ($cfg['first_auth_redirect']=='none') {echo 'selected';} ?>><?php echo $_LANG['AD_DO_NOTHING']; ?></option>
                        <option value="index" <?php if ($cfg['first_auth_redirect']=='index') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_HOME']; ?></option>
                        <option value="profile" <?php if ($cfg['first_auth_redirect']=='profile') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_PROFILE']; ?></option>
                        <option value="editprofile" <?php if ($cfg['first_auth_redirect']=='editprofile') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_PROFILE_SETTIGS']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_NEXT_LOGIN']; ?>:</strong></td>
                <td>
                    <select name="auth_redirect" id="auth_redirect" style="width:300px">
                        <option value="none" <?php if ($cfg['auth_redirect']=='none') {echo 'selected';} ?>><?php echo $_LANG['AD_DO_NOTHING']; ?></option>
                        <option value="index" <?php if ($cfg['auth_redirect']=='index') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_HOME']; ?></option>
                        <option value="profile" <?php if ($cfg['auth_redirect']=='profile') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_PROFILE']; ?></option>
                        <option value="editprofile" <?php if ($cfg['auth_redirect']=='editprofile') {echo 'selected';} ?>><?php echo $_LANG['AD_OPEN_PROFILE_SETTIGS']; ?></option>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <div id="form">
        <table width="661" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td width="308"><strong><?php echo $_LANG['AD_NAME_FORMAT']; ?>:</strong></td>
                <td>
                    <select name="name_mode" id="name_mode" style="width:300px">
                        <option value="nickname" <?php if ($cfg['name_mode']=='nickname') {echo 'selected';} ?>><?php echo $_LANG['AD_NICKNAME']; ?></option>
                        <option value="realname" <?php if ($cfg['name_mode']=='realname') {echo 'selected';} ?>><?php echo $_LANG['AD_NAME_SURNAME']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_NAME_PROCHIBITED']; ?>:</strong><br /><?php echo $_LANG['AD_ENTER_BANNED_NAME']; ?>.</td>
                <td valign="top"><textarea  name="badnickname" type="text" id="badnickname" rows="5" style="border: solid 1px gray;width:300px;"><?php echo $cfg['badnickname'];?></textarea></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_REQUIRE_ICQ']; ?>:</strong> </td>
                <td>
                    <label><input name="ask_icq" type="radio" value="1" <?php if ($cfg['ask_icq']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="ask_icq" type="radio" value="0" <?php if (!$cfg['ask_icq']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_REQUIRE_BIRD']; ?>:</strong> </td>
                <td>
                    <label><input name="ask_birthdate" type="radio" value="1" <?php if ($cfg['ask_birthdate']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="ask_birthdate" type="radio" value="0" <?php if (!$cfg['ask_birthdate']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_REQUIRE_CITY']; ?>:</strong> </td>
                <td>
                    <label><input name="ask_city" type="radio" value="1" <?php if ($cfg['ask_city']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="ask_city" type="radio" value="0" <?php if (!$cfg['ask_city']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div id="greets">
        <table width="800" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td width="308"><strong><?php echo $_LANG['AD_SEND_MASSAGE']; ?>:</strong></td>
                <td>
                    <label><input name="send_greetmsg" type="radio" value="1" <?php if ($cfg['send_greetmsg']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="send_greetmsg" type="radio" value="0" <?php if (!$cfg['send_greetmsg']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
        </table>
        <?php $inCore->insertEditor('greetmsg', $cfg['greetmsg'], '300', '800'); ?>
    </div>

</div>

<p>
    <input name="opt" type="hidden" value="saveconfig" />
    <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
    <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
</p>
</form>