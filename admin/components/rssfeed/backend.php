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

	$cfg = $inCore->loadComponentConfig('rssfeed');

	if($opt=='saveconfig'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

		$cfg = array();
		$cfg['addsite']  = cmsCore::request('addsite', 'int');
		$cfg['maxitems'] = cmsCore::request('maxitems', 'int');
		$cfg['icon_on']  = cmsCore::request('icon_on', 'int');
		$cfg['icon_url'] = cmsCore::request('icon_url', 'str', '');

		$inCore->saveComponentConfig('rssfeed', $cfg);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

		cmsCore::redirectBack();

	}

?>
<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="optform" target="_self" id="form1">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="650" border="0" cellpadding="10" cellspacing="0" class="proptable">
          <tr>
            <td colspan="2" bgcolor="#EBEBEB"><strong><?php echo $_LANG['AD_FEEDS']; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo $_LANG['AD_RSS_CHANNELS']; ?>:</td>
            <td width="300" valign="top">
            <label><input name="addsite" type="radio" value="1" <?php if ($cfg['addsite']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
            <label><input name="addsite" type="radio" value="0" <?php if (!$cfg['addsite']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
            </td>
          </tr>
          <tr>
            <td><?php echo $_LANG['AD_NUMBER_DISPLAY']; ?>: </td>
            <td valign="top"><input class="uispin" name="maxitems" type="text" id="maxitems" size="6" value="<?php echo $cfg['maxitems'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
          </tr>
        </table>
        <table width="650" border="0" cellpadding="10" cellspacing="0" class="proptable" style="margin-top:2px">
          <tr>
            <td colspan="2" bgcolor="#EBEBEB"><strong><?php echo $_LANG['AD_RSS_ICON']; ?> </strong></td>
          </tr>
          <tr>
            <td><?php echo $_LANG['AD_RSS_ICON']; ?>:</td>
            <td width="300" valign="top">
            <label><input name="icon_on" type="radio" value="1" <?php if ($cfg['icon_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
            <label><input name="icon_on" type="radio" value="0" <?php if (!$cfg['icon_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
            </td>
          </tr>
          <tr>
            <td><?php echo $_LANG['AD_RSS_ICON_URL']; ?>: </td>
            <td valign="top"><input name="icon_url" type="text" id="icon_url" size="45" value="<?php echo $cfg['icon_url'];?>"/></td>
          </tr>
        </table>
        <p>
          <input name="opt" type="hidden" value="saveconfig" />
          <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
          <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
        </p>
</form>