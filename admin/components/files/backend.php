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

	$opt = cmsCore::request('opt', 'str', 'config');

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.optform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components');

	cpToolMenu($toolmenu);

	$cfg = $inCore->loadComponentConfig('files');

	if($opt=='saveconfig'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$cfg = array(
            'check_link'    => cmsCore::request('check_link', 'int', 0),
            'redirect_time' => cmsCore::request('redirect_time', 'int', 0),
            'file_time'     => cmsCore::request('file_time', 'int', 0),
            'white_list'    => cmsCore::request('white_list', 'str', '')
        );

		$inCore->saveComponentConfig('files', $cfg);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

		cmsCore::redirectBack();

	}

?>
<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>" method="post" name="optform" id="form1">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <table width="650" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_FILES_CHECK_LINK']; ?>:</strong></td>
            <td width="300" valign="top">
                <label><input name="check_link" type="radio" value="1" <?php if ($cfg['check_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                <label><input name="check_link" type="radio" value="0" <?php if (!$cfg['check_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_FILES_WHITE_LIST']; ?>: </strong><br><span class="hinttext"><?php echo $_LANG['AD_FILES_WHITE_LIST_HINT']; ?></span></td>
            <td valign="top">
                <input name="white_list" type="text" value="<?php echo htmlspecialchars($cfg['white_list']); ?>" style="width: 99%;"/>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_FILES_REDIRECT_TIME']; ?>:</strong></td>
            <td valign="top">
                <input name="redirect_time" size="5" class="uispin" type="text" value="<?php echo htmlspecialchars($cfg['redirect_time']); ?>"/>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_FILES_FILE_TIME']; ?>:</strong></td>
            <td valign="top">
                <input name="file_time" size="5" class="uispin" type="text" value="<?php echo htmlspecialchars($cfg['file_time']); ?>"/>
            </td>
        </tr>
    </table>
    <p>
        <input name="opt" type="hidden" value="saveconfig" />
        <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
    </p>
</form>