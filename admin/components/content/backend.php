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

	echo '<h3>'.$_LANG['AD_SETTINGS'].'</h3>';

	$cfg = $inCore->loadComponentConfig('content');

	if($opt=='saveconfig'){

		if(!cmsCore::validateForm()) { cmsCore::error404(); }

		$cfg = array();
        $cfg['readdesc']    = cmsCore::request('readdesc', 'int', 0);
		$cfg['is_url_cyrillic'] = cmsCore::request('is_url_cyrillic', 'int', 0);
		$cfg['rating']      = cmsCore::request('rating', 'int', 0);
		$cfg['perpage']     = cmsCore::request('perpage', 'int', 0);
        $cfg['pt_show']     = cmsCore::request('pt_show', 'int', 0);
		$cfg['pt_disp']     = cmsCore::request('pt_disp', 'int', 0);
		$cfg['pt_hide']     = cmsCore::request('pt_hide', 'int', 0);
		$cfg['autokeys']    = cmsCore::request('autokeys', 'int', 0);
		$cfg['hide_root']   = cmsCore::request('hide_root', 'int', 0);
        $cfg['img_small_w'] = cmsCore::request('img_small_w', 'int', 100);
        $cfg['img_big_w']   = cmsCore::request('img_big_w', 'int', 200);
        $cfg['img_sqr']     = cmsCore::request('img_sqr', 'int', 1);
        $cfg['img_users']   = cmsCore::request('img_users', 'int', 1);
		$cfg['watermark']   = cmsCore::request('watermark', 'int', 0);
		$cfg['watermark_only_big'] = cmsCore::request('watermark_only_big', 'int', 0);

		$inCore->saveComponentConfig('content', $cfg);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

		cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=config');

	}

    require('../includes/jwtabs.php');
    $GLOBALS['cp_page_head'][] = jwHeader();

?>

<form action="index.php?view=components&do=config&id=<?php echo $id;?>" method="post" name="optform" target="_self" id="form1">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <?php ob_start(); ?>
    {tab=<?php echo $_LANG['AD_OVERALL']; ?>}
    <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_GENERATE_CYRYLLIC_URL']; ?>: </strong></td>
            <td width="110">
                <label><input name="is_url_cyrillic" type="radio" value="1" <?php if ($cfg['is_url_cyrillic']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="is_url_cyrillic" type="radio" value="0" <?php if (!$cfg['is_url_cyrillic']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_OUTPUT_ANNOUNCEMENTS']; ?>: </strong></td>
            <td width="110">
                <label><input name="readdesc" type="radio" value="1" <?php if ($cfg['readdesc']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="readdesc" type="radio" value="0" <?php if (!$cfg['readdesc']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['ARTICLES_RATING']; ?>: </strong></td>
            <td>
                <label><input name="rating" type="radio" value="1" <?php if ($cfg['rating']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="rating" type="radio" value="0" <?php if (!$cfg['rating']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_GENERATE_KEY_DESCR']; ?>:</strong>
            </td>
            <td valign="top">
                <label><input name="autokeys" type="radio" value="1" <?php if ($cfg['autokeys']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="autokeys" type="radio" value="0" <?php if (!$cfg['autokeys']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_HIDE_ROOT_CATS']; ?>:</strong>
            </td>
            <td valign="top">
                <label><input name="hide_root" type="radio" value="1" <?php if ($cfg['hide_root']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="hide_root" type="radio" value="0" <?php if (!$cfg['hide_root']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_NUMBER_PER_PAGE']; ?>: </strong></td>
            <td><input class="uispin" name="perpage" type="text" id="perpage" value="<?php echo $cfg['perpage'];?>" size="5" /></td>
        </tr>
    </table>
    <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_SHOW_CONTENT']; ?>: </strong></td>
            <td width="110">
                <label><input name="pt_show" type="radio" value="1" <?php if ($cfg['pt_show']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="pt_show" type="radio" value="0" <?php if (!$cfg['pt_show']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_DEPLOY_CONTENT']; ?>: </strong></td>
            <td>
                <label><input name="pt_disp" type="radio" value="1" <?php if ($cfg['pt_disp']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="pt_disp" type="radio" value="0" <?php if (!$cfg['pt_disp']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_HIDE CONTENT']; ?>: </strong></td>
            <td>
                <label><input name="pt_hide" type="radio" value="1" <?php if ($cfg['pt_hide']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="pt_hide" type="radio" value="0" <?php if (!$cfg['pt_hide']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
    </table>
    {tab=<?php echo $_LANG['AD_PHOTO_ART']; ?>}
    <table width="550" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_PHOTO_SMALL']; ?>:</strong></td>
            <td width="120">
                <input class="uispin" name="img_small_w" type="text" id="img_small_w" value="<?php echo $cfg['img_small_w'];?>" size="5" />
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_PHOTO_BIG']; ?>:</strong></td>
            <td>
                <input class="uispin" name="img_big_w" type="text" id="img_big_w" value="<?php echo $cfg['img_big_w'];?>" size="5" />
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_PHOTO_SQUARE']; ?>:</strong></td>
            <td>
                <label><input name="img_sqr" type="radio" value="1" <?php if ($cfg['img_sqr']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="img_sqr" type="radio" value="0" <?php if (!$cfg['img_sqr']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_ALLOW_USERS_TO']; ?>:</strong><br/>
                <span class="hinttext"><?php echo $_LANG['AD_ALLOW_USERS_TO_HINT']; ?></span>
            </td>
            <td>
                <label><input name="img_users" type="radio" value="1" <?php if ($cfg['img_users']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="img_users" type="radio" value="0" <?php if (!$cfg['img_users']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
           <td><strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong><br />
		   <span class="hinttext"><?php echo $_LANG['AD_WATERMARK_HINT']; ?> "<a href="/images/watermark.png" target="_blank">/images/watermark.png</a>"</span></td>
           <td width="260">
               <label><input name="watermark" type="radio" value="1" <?php if ($cfg['watermark']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
               <label><input name="watermark" type="radio" value="0"  <?php if (!$cfg['watermark']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
           </td>
        </tr>
    </table>
    {/tabs}
    <?php echo jwTabs(ob_get_clean()); ?>
    <p>
        <input name="opt" type="hidden" value="saveconfig" />
        <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
    </p>
</form>