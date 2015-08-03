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

cmsCore::loadModel('users');
$model = new cms_model_users();

$opt = cmsCore::request('opt', 'str', 'list');

$toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.optform.submit();');
$toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components');

cpToolMenu($toolmenu);

if ($opt=='saveconfig'){

    if (!cmsCore::validateForm()) { cmsCore::error404(); }

	$cfg = array();
    $cfg['sw_comm']   = cmsCore::request('sw_comm', 'int', 0);
    $cfg['sw_search'] = cmsCore::request('sw_search', 'int', 0);
    $cfg['sw_forum']  = cmsCore::request('sw_forum', 'int', 0);
    $cfg['sw_photo']  = cmsCore::request('sw_photo', 'int', 0);
    $cfg['sw_wall']   = cmsCore::request('sw_wall', 'int', 0);
    $cfg['sw_blogs']  = cmsCore::request('sw_blogs', 'int', 0);
    $cfg['sw_clubs']  = cmsCore::request('sw_clubs', 'int', 0);
    $cfg['sw_feed']   = cmsCore::request('sw_feed', 'int', 0);
    $cfg['sw_awards'] = cmsCore::request('sw_awards', 'int', 0);
    $cfg['sw_board']  = cmsCore::request('sw_board', 'int', 0);
    $cfg['sw_msg']    = cmsCore::request('sw_msg', 'int', 0);
    $cfg['sw_guest']  = cmsCore::request('sw_guest', 'int', 0);
    $cfg['sw_files']  = cmsCore::request('sw_files', 'int', 0);

    $cfg['karmatime'] = cmsCore::request('karmatime', 'int', 0);
    $cfg['karmaint']  = cmsCore::request('karmaint', 'str', 'DAY');

    $cfg['photosize'] = cmsCore::request('photosize', 'int', 0);
    $cfg['watermark'] = cmsCore::request('watermark', 'int', 0);
    $cfg['smallw']    = cmsCore::request('smallw', 'int', 64);
    $cfg['medw']      = cmsCore::request('medw', 'int', 200);
    $cfg['medh']      = cmsCore::request('medh', 'int', 200);

    $cfg['filessize'] = cmsCore::request('filessize', 'int', 0);
	$cfg['filestype'] = mb_strtolower(cmsCore::request('filestype', 'str', 'jpeg,gif,png,jpg,bmp,zip,rar,tar'));
    while (mb_strpos($cfg['fa_ext'], 'htm') ||
           mb_strpos($cfg['fa_ext'], 'php') ||
           mb_strpos($cfg['fa_ext'], 'ht')) {
        $cfg['filestype']  = str_replace(array('htm','php','ht'), '', $cfg['filestype']);
    }

    $cfg['privforms'] = cmsCore::request('privforms', 'array_int');

	$cfg['deltime']   = cmsCore::request('deltime', 'int', 0);
	$cfg['users_perpage'] = cmsCore::request('users_perpage', 'int', 10);
	$cfg['wall_perpage']  = cmsCore::request('wall_perpage', 'int', 10);

    $inCore->saveComponentConfig('users', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

	cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=config');

}

cpCheckWritable('/images/users/avatars', 'folder');
cpCheckWritable('/images/users/avatars/small', 'folder');
cpCheckWritable('/images/users/photos', 'folder');
cpCheckWritable('/images/users/photos/small', 'folder');
cpCheckWritable('/images/users/photos/medium', 'folder'); ?>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="optform" id="form1">
	<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <div id="config_tabs" style="margin-top:12px;" class="uitabs">

    <ul id="tabs">
        <li><a href="#basic"><span><?php echo $_LANG['AD_PROFILE_SETTINGS']; ?></span></a></li>
        <li><a href="#avatars"><span><?php echo $_LANG['AD_AVATARS']; ?></span></a></li>
        <li><a href="#proftabs"><span><?php echo $_LANG['AD_PROFILES_TAB']; ?></span></a></li>
        <li><a href="#forms"><span><?php echo $_LANG['AD_MORE_FIELDS']; ?></span></a></li>
        <li><a href="#photos"><span><?php echo $_LANG['AD_PROFILE_SETTINGS']; ?></span></a></li>
        <li><a href="#files"><span><?php echo $_LANG['AD_FILE_ARCHIVES']; ?></span></a></li>
    </ul>

    <div id="basic">
        <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_VIEV_PROFILES']; ?>: </strong></td>
                <td width="230">
                    <label><input name="sw_guest" type="radio" value="1" <?php if ($model->config['sw_guest']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_guest" type="radio" value="0" <?php if (!$model->config['sw_guest']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_SEARCH_USERS']; ?>: </strong></td>
                <td>
                    <label><input name="sw_search" type="radio" value="1" <?php if ($model->config['sw_search'] == 1) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_search" type="radio" value="0" <?php if (!$model->config['sw_search']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['AD_YES_ONLY_VIEW']; ?></label>
                    <label><input name="sw_search" type="radio" value="2" <?php if ($model->config['sw_search'] == 2) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_SHOW_MORE_COMMENTS']; ?>: </strong></td>
                <td width="">
                    <label><input name="sw_comm" type="radio" value="1" <?php if ($model->config['sw_comm']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_comm" type="radio" value="0" <?php if (!$model->config['sw_comm']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_SHOW_FORUM']; ?>: </strong></td>
                <td>
                    <label><input name="sw_forum" type="radio" value="1" <?php if ($model->config['sw_forum']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_forum" type="radio" value="0" <?php if (!$model->config['sw_forum']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_USERS_WALL']; ?>: </strong></td>
                <td>
                    <label><input name="sw_wall" type="radio" value="1" <?php if ($model->config['sw_wall']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_wall" type="radio" value="0" <?php if (!$model->config['sw_wall']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_PERSONAL_BLOGS']; ?>:</strong></td>
                <td>
                    <label><input name="sw_blogs" type="radio" value="1" <?php if ($model->config['sw_blogs']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_blogs" type="radio" value="0" <?php if (!$model->config['sw_blogs']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_SHOW_ADS']; ?>:</strong></td>
                <td>
                    <label><input name="sw_board" type="radio" value="1" <?php if ($model->config['sw_board']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_board" type="radio" value="0" <?php if (!$model->config['sw_board']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_PRIVATE_MESS']; ?>:</strong> </td>
                <td>
                    <label><input name="sw_msg" type="radio" value="1" <?php if ($model->config['sw_msg']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_msg" type="radio" value="0" <?php if (!$model->config['sw_msg']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_NOTIFICATION_TEXT']; ?>: </strong></td>
                <td><?php echo '/languages/'.cmsConfig::getConfig('lang').'/letters/newmessage.txt'; ?></td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_PEROD_KARMA']; ?>:</strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_PEROD_KARMA_HINT']; ?> </span>
                </td>
                <td valign="top">
                    <input class="uispin" name="karmatime" type="text" id="int_1" size="5" value="<?php echo $model->config['karmatime']?>"/>
                    <select name="karmaint" id="int_2">
                        <option value="MINUTE"  <?php if (mb_strstr($model->config['karmaint'], 'MINUTE')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['MINUTE10']; ?></option>
                        <option value="HOUR"  <?php if (mb_strstr($model->config['karmaint'], 'HOUR')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['HOUR10']; ?></option>
                        <option value="DAY" <?php if (mb_strstr($model->config['karmaint'], 'DAY')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['DAY10']; ?></option>
                        <option value="MONTH" <?php if (mb_strstr($model->config['karmaint'], 'MONTH')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['MONTH10']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_DELETE_INACTIVE']; ?>:</strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_DELETE_INACTIVE_HINT']; ?></span>
                </td>
                <td valign="top">
                    <input class="uispin" name="deltime" type="text" id="deltime" size="5" value="<?php echo $model->config['deltime']; ?>"/> <?php echo $_LANG['MONTH10']; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_USERS_ON_PAGE']; ?>:</strong>
                </td>
                <td valign="top">
                    <input class="uispin" name="users_perpage" type="text" id="users_perpage" size="5" value="<?php echo $model->config['users_perpage']; ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_NUMBER_ON_WALL']; ?>:</strong>
                </td>
                <td valign="top">
                    <input class="uispin" name="wall_perpage" type="text" id="wall_perpage" size="5" value="<?php echo $model->config['wall_perpage']; ?>"/>
                </td>
            </tr>
        </table>
    </div>

    <div id="avatars">
        <table width="450" border="0" cellpadding="10" cellspacing="0" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_WIDTH_SMALL_AVATAR']; ?>: </strong></td>
                <td width="210"><input class="uispin" name="smallw" type="text" id="smallw" size="5" value="<?php echo $model->config['smallw'];?>"/> <?php echo $_LANG['AD_PX']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_WIDTH_LARGE_AVATAR']; ?>: </strong></td>
                <td><input class="uispin" name="medw" type="text" id="medw" size="5" value="<?php echo $model->config['medw'];?>"/> <?php echo $_LANG['AD_PX']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_HEIGHT_LARGE_AVATAR']; ?>: </strong></td>
                <td><input class="uispin" name="medh" type="text" id="medh" size="5" value="<?php echo $model->config['medh'];?>"/> <?php echo $_LANG['AD_PX']; ?></td>
            </tr>
        </table>
    </div>


    <div id="proftabs">
        <table width="450" border="0" cellpadding="10" cellspacing="0" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_TAB_RIBBON']; ?></strong></td>
                <td width="210">
                    <label><input name="sw_feed" type="radio" value="1" <?php if ($model->config['sw_feed']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_feed" type="radio" value="0" <?php if (!$model->config['sw_feed']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_TAB_CLUBS']; ?></strong></td>
                <td>
                    <label><input name="sw_clubs" type="radio" value="1" <?php if ($model->config['sw_clubs']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_clubs" type="radio" value="0" <?php if (!$model->config['sw_clubs']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_TAB_AWARDS']; ?></strong></td>
                <td>
                    <label><input name="sw_awards" type="radio" value="1" <?php if ($model->config['sw_awards']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_awards" type="radio" value="0" <?php if (!$model->config['sw_awards']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div id="forms">
        <table width="380" border="0" cellspacing="0" cellpadding="10" class="proptable" style="border:none">
            <tr>
                <td valign="top">
                    <div><?php echo $_LANG['AD_FORMS_IN_PROFILES']; ?>: </div>
                    <p>
                        <select name="privforms[]" size="10" style="width:350px; border:solid 1px silver;" multiple="multiple">
                            <?php

                            $sql = "SELECT * FROM cms_forms";
                            $rs = $inDB->query($sql);

                            if ($inDB->num_rows($rs)){
                                while($f = $inDB->fetch_assoc($rs)){
                                    if (in_array($f['id'], $model->config['privforms'])) { $selected='selected="selected"'; } else { $selected = ''; }
                                    echo '<option value="'.$f['id'].'" '.$selected.'>'.$f['title'].'</option>';
                                }
                            }

                            ?>
                        </select>
                    </p>
                    <p><?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL']; ?>.</p>
                    <p><?php echo $_LANG['AD_FORMS_IN_PROFILES_EDIT']; ?> <a href="index.php?view=components&do=config&link=forms"><?php echo $_LANG['AD_FORM_DESIGNER']; ?></a>.</p>
                </td>
            </tr>
        </table>
    </div>

    <div id="photos">
        <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_PHOTO_ALBUMS']; ?>: </strong></td>
                <td width="210">
                    <label><input name="sw_photo" type="radio" value="1" <?php if ($model->config['sw_photo']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_photo" type="radio" value="0" <?php if (!$model->config['sw_photo']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong> <br />
                    <span class="hinttext"><?php echo $_LANG['AD_APPLY_WATERMARK_HINT']; ?> &quot;<a href="/images/watermark.png" target="_blank">/images/watermark.png</a>&quot;</span>
                </td>
                <td valign="top">
                    <label><input name="watermark" type="radio" value="1" <?php if ($model->config['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="watermark" type="radio" value="0" <?php if (!$model->config['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_LOTS_PHOTOS'] ; ?>:</strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_LOTS_PHOTOS_HINT']; ?></span>
                </td>
                <td><input class="uispin" name="photosize" type="text" id="photosize" size="5" value="<?php echo $model->config['photosize'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
        </table>
    </div>

    <div id="files">
         <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_USER_FILES']; ?>: </strong></td>
                <td width="210">
                    <label><input name="sw_files" type="radio" value="1" <?php if ($model->config['sw_files']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="sw_files" type="radio" value="0" <?php if (!$model->config['sw_files']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_DISK_SPACE']; ?>:</strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_DISK_SPACE_HINT']; ?></span>
                </td>
                <td><input class="uispin" name="filessize" type="text" id="filessize" size="5" value="<?php echo $model->config['filessize'];?>"/> <?php echo $_LANG['SIZE_MB']; ?></td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_FILE_TIPES']; ?>:</strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_FILE_TIPES_HINT']; ?></span>
                </td>
                <td><input name="filestype" type="text" id="filestype" size="30" value="<?php echo $model->config['filestype'];?>"/></td>
            </tr>
        </table>
    </div>

</div>
<p>
    <input name="opt" type="hidden" value="saveconfig" />
    <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
    <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
</p>
</form>