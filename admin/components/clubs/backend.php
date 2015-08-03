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

$cfg = $inCore->loadComponentConfig('clubs');

$opt = cmsCore::request('opt', 'str', 'list');

cmsCore::loadModel('clubs');
$model = new cms_model_clubs();

if($opt=='list'){

    $toolmenu[] = array('icon'=>'new.gif', 'title'=>$_LANG['CREATE_CLUB'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add');
    $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id={$id}&opt=show_club&multiple=1');");
    $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id={$id}&opt=hide_club&multiple=1');");
    $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id={$id}&opt=edit&multiple=1');");
    $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['AD_SETTINGS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=config');

}

if (in_array($opt, array('add', 'edit', 'config'))){

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

}

if ($opt=='saveconfig'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg = array();
    $cfg['enabled_blogs']      = cmsCore::request('enabled_blogs', 'str');
    $cfg['enabled_photos']     = cmsCore::request('enabled_photos', 'str');
    $cfg['thumb1']             = cmsCore::request('thumb1', 'int');
    $cfg['thumb2']             = cmsCore::request('thumb2', 'int');
    $cfg['thumbsqr']           = cmsCore::request('thumbsqr', 'int');
    $cfg['cancreate']          = cmsCore::request('cancreate', 'int');
    $cfg['perpage']            = cmsCore::request('perpage', 'int');
    $cfg['member_perpage']     = cmsCore::request('member_perpage', 'int');
    $cfg['club_perpage']       = cmsCore::request('club_perpage', 'int');
    $cfg['wall_perpage']       = cmsCore::request('wall_perpage', 'int');
    $cfg['club_album_perpage'] = cmsCore::request('club_album_perpage', 'int');
    $cfg['posts_perpage']      = cmsCore::request('posts_perpage', 'int');
    $cfg['club_posts_perpage'] = cmsCore::request('club_posts_perpage', 'int');
    $cfg['photo_perpage']      = cmsCore::request('photo_perpage', 'int');
    $cfg['create_min_karma']   = cmsCore::request('create_min_karma', 'int');
    $cfg['create_min_rating']  = cmsCore::request('create_min_rating', 'int');
    $cfg['notify_in']          = cmsCore::request('notify_in', 'int');
    $cfg['notify_out']         = cmsCore::request('notify_out', 'int');
    $cfg['every_karma']        = cmsCore::request('every_karma', 'int', 100);
    $cfg['photo_watermark']    = cmsCore::request('photo_watermark', 'int', 0);
    $cfg['photo_thumb_small']  = cmsCore::request('photo_thumb_small', 'int', 96);
    $cfg['photo_thumbsqr']     = cmsCore::request('photo_thumbsqr', 'int', 0);
    $cfg['photo_thumb_medium'] = cmsCore::request('photo_thumb_medium', 'int', 450);
    $cfg['photo_maxcols']      = cmsCore::request('photo_maxcols', 'int', 4);
    $cfg['meta_keys']          = cmsCore::request('meta_keys', 'str', '');
    $cfg['meta_desc']          = cmsCore::request('meta_desc', 'str', '');
    $cfg['seo_user_access']    = cmsCore::request('seo_user_access', 'int', 0);
    $cfg['is_saveorig']        = cmsCore::request('is_saveorig', 'int', 0);

    $inCore->saveComponentConfig('clubs', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

	cmsCore::redirectBack();

}

if ($opt == 'show_club'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbShow('cms_clubs', $_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbShowList('cms_clubs', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'hide_club'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbHide('cms_clubs', $_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbHideList('cms_clubs', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'submit'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $title 			= cmsCore::request('title', 'str', 'NO_TITLE');
    $description    = $inDB->escape_string(cmsCore::request('description', 'html', ''));
    $published 		= cmsCore::request('published', 'int');
    $admin_id 		= cmsCore::request('admin_id', 'int');
    $clubtype		= cmsCore::request('clubtype', 'str');
    $maxsize 		= cmsCore::request('maxsize', 'int');
    $enabled_blogs	= cmsCore::request('enabled_blogs', 'int');
    $enabled_photos	= cmsCore::request('enabled_photos', 'int');

    $date = explode('.', $_REQUEST['pubdate']);
    $pubdate = (int)$date[2] . '-' . (int)$date[1] . '-' . (int)$date[0];

	$new_imageurl = $model->uploadClubImage();
	$filename = @$new_imageurl['filename'] ? $new_imageurl['filename'] : '';

	$model->addClub(array('admin_id'=>$admin_id,
                            'title'=>$title,
                            'description'=>$description,
                            'imageurl'=>$filename,
                            'pubdate'=>$pubdate,
                            'clubtype'=>$clubtype,
                            'published'=>$published,
                            'maxsize'=>$maxsize,
                            'create_karma'=>cmsUser::getKarma($admin_id),
                            'enabled_blogs'=>$enabled_blogs,
                            'enabled_photos'=>$enabled_photos));

	cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
	cmsCore::redirect('index.php?view=components&do=config&opt=list&id='.$id);

}

if ($opt == 'update'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

	$item_id = cmsCore::request('item_id', 'int', 0);

	$new_club['title'] 			= cmsCore::request('title', 'str', 'NO_TITLE');
	$new_club['description']    = $inDB->escape_string(cmsCore::request('description', 'html', ''));
	$new_club['published'] 		= cmsCore::request('published', 'int');
	$new_club['admin_id'] 		= cmsCore::request('admin_id', 'int');
	$new_club['clubtype']		= cmsCore::request('clubtype', 'str');
	$new_club['maxsize'] 		= cmsCore::request('maxsize', 'int');
    $new_club['enabled_blogs']	= cmsCore::request('enabled_blogs', 'int');
    $new_club['enabled_photos']	= cmsCore::request('enabled_photos', 'int');

	$olddate 		= cmsCore::request('olddate', 'str');
	$pubdate 		= cmsCore::request('pubdate', 'str');

	$club = $model->getClub($item_id);
	if(!$club){	cmsCore::error404(); }

	if ($olddate != $pubdate){
		$date = explode('.', $pubdate);
		$new_club['pubdate'] = (int)$date[2] . '-' . (int)$date[1] . '-' . (int)$date[0];
	}

	$new_imageurl = $model->uploadClubImage($club['imageurl']);
	$new_club['imageurl'] = @$new_imageurl['filename'] ? $new_imageurl['filename'] : $club['imageurl'];

	$model->updateClub($item_id, $new_club);

	cmsCore::addSessionMessage($_LANG['CONFIG_SAVE_OK'], 'success');

    if (empty($_SESSION['editlist'])){
        cmsCore::redirect('index.php?view=components&do=config&id='.$id.'&opt=list');
    } else {
        cmsCore::redirect('index.php?view=components&do=config&id='.$id.'&opt=edit');
    }

}

if($opt == 'delete'){
    $model->deleteClub(cmsCore::request('item_id', 'int', 0));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('index.php?view=components&do=config&id='.$id.'&opt=list');
}

cpToolMenu($toolmenu);

if ($opt == 'list'){

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['DATE'], 'field'=>'pubdate', 'width'=>'100', 'filter'=>'15', 'fdate'=>'%d/%m/%Y');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'filter'=>'15', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%');
    $fields[] = array('title'=>$_LANG['CLUB_TYPE'], 'field'=>'clubtype', 'width'=>'100');
    $fields[] = array('title'=>$_LANG['MEMBERS'], 'field'=>'members_count', 'width'=>'80');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'100', 'do'=>'opt', 'do_suffix'=>'_club');

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_DELETE_CLUB'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete&item_id=%id%');

    cpListTable('cms_clubs', $fields, $actions, '', 'pubdate DESC');

}

if ($opt == 'add' || $opt == 'edit'){

    if ($opt=='add'){
        echo '<h3>'.$_LANG['CREATE_CLUB'] .'</h3>';
		cpAddPathway($_LANG['CREATE_CLUB'] );
    } else {
        if(isset($_REQUEST['multiple'])){
            if (isset($_REQUEST['item'])){
                $_SESSION['editlist'] = cmsCore::request('item', 'array_int', array());
            } else {
                cmsCore::addSessionMessage($_LANG['AD_NO_SELECT_OBJECTS'], 'error');
                cmsCore::redirectBack();
            }
        }

        $ostatok = '';

        if (isset($_SESSION['editlist'])){
           $item_id = array_shift($_SESSION['editlist']);
           if (sizeof($_SESSION['editlist'])==0) { unset($_SESSION['editlist']); } else
           { $ostatok = '('.$_LANG['AD_NEXT_IN'].sizeof($_SESSION['editlist']).')'; }
        } else { $item_id = cmsCore::request('item_id', 'int', 0); }

        $mod = $model->getClub($item_id);
		if(!$mod){ cmsCore::error404(); }

        echo '<h3>'.$mod['title'].' '.$ostatok.'</h3>';
        cpAddPathway($mod['title']);

    }

    if(!isset($mod['maxsize'])) { $mod['maxsize'] = 0; }
    if(!isset($mod['admin_id'])) { $mod['admin_id'] = $inUser->id; }
    if(!isset($mod['clubtype'])) { $mod['clubtype'] = 'public'; }

    require('../includes/jwtabs.php');
    $GLOBALS['cp_page_head'][] = jwHeader();

    ob_start(); ?>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" enctype="multipart/form-data" name="addform" id="addform">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    {tab=<?php echo $_LANG['AD_OVERALL']; ?>}
    <table width="625" border="0" cellspacing="5" class="proptable">
        <tr>
            <td width="298"><strong><?php echo $_LANG['CLUB_NAME']; ?>: </strong></td>
            <td width="">
                <input name="title" type="text" id="title" style="width:300px" value="<?php echo htmlspecialchars($mod['title']);?>"/>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['UPLOAD_LOGO'];?>:</strong></td>
            <td>
                <?php if (@$mod['imageurl']){ echo '<div style="margin-bottom:5px;"><img src="/images/clubs/small/'.$mod['imageurl'].'" /></div>'; } ?>
                <input name="picture" type="file" id="picture" size="33" />
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['MAX_MEMBERS']; ?>: </strong><br />
                <span class="hinttext"><?php echo $_LANG['MAX_MEMBERS_TEXT']; ?></span>
            </td>
            <td><input class="uispin" name="maxsize" type="text" id="maxsize" style="width:300px" value="<?php echo @$mod['maxsize'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_CLUB_DATE']; ?>:</strong></td>
            <td><input name="pubdate" type="text" id="pubdate" style="width:278px" <?php if(@!$mod['pubdate']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.date('d.m.Y', strtotime($mod['pubdate'])).'"'; } ?>/>

            <input type="hidden" name="olddate" value="<?php echo date('d.m.Y', strtotime(@$mod['pubdate']))?>"/></td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_PUBLISH_CLUB']; ?></strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_PUBLISH_CLUB_HINT']; ?></span>
            </td>
            <td>
                <label><input name="published" type="radio" value="1" checked="checked" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['CLUB_BLOG']; ?>:</strong></td>
            <td>
                <select name="enabled_blogs" id="enabled_blogs" style="width:300px">
                    <option value="-1" <?php if (@$mod['orig_enabled_blogs']=='-1') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_BY_DEFAULT']; ?></option>
                    <option value="1" <?php if (@$mod['orig_enabled_blogs']=='1') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_IS_ENABLED']; ?></option>
                    <option value="0" <?php if (@$mod['orig_enabled_blogs']=='0') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_IS_DISABLED']; ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['CLUB_PHOTOALBUMS']; ?>:</strong></td>
            <td>
                <select name="enabled_photos" id="enabled_photos" style="width:300px">
                    <option value="-1" <?php if (@$mod['orig_enabled_photos']=='-1') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_BY_DEFAULT']; ?></option>
                    <option value="1" <?php if (@$mod['orig_enabled_photos']=='1') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_IS_ENABLED']; ?></option>
                    <option value="0" <?php if (@$mod['orig_enabled_photos']=='0') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_IS_DISABLED']; ?></option>
                </select>
            </td>
        </tr>
    </table>
    {tab=<?php echo $_LANG['CLUB_DESC']; ?>}
    <table width="100%" border="0" cellspacing="5" class="proptable">
        <tr>
            <td>
                <?php $inCore->insertEditor('description', $mod['description'], '400', '100%'); ?>
            </td>
        </tr>
    </table>
    {tab=<?php echo $_LANG['AD_TAB_ACCESS']; ?>}
    <table width="625" border="0" cellspacing="5" class="proptable">
        <tr>
            <td width="298"><strong><?php echo $_LANG['CLUB_ADMIN']; ?>:</strong></td>
            <td width="308">
                <select name="admin_id" id="admin_id" style="width:300px">
                    <?php
                        if (isset($mod['admin_id'])) {
                            echo $inCore->getListItems('cms_users', $mod['admin_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                        } else {
                            echo $inCore->getListItems('cms_users', 0, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                        }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['CLUB_TYPE']; ?>:</strong></td>
            <td>
                <select name="clubtype" id="clubtype" style="width:300px">
                    <option value="public" <?php if (@$mod['clubtype']=='public') { echo 'selected="selected"'; } ?>><?php echo $_LANG['PUBLIC']; ?></option>
                    <option value="private" <?php if (@$mod['clubtype']=='private') { echo 'selected="selected"'; } ?>><?php echo $_LANG['PRIVATE']; ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
			<?php if($opt == 'edit'){ ?>
				<?php echo $_LANG['AD_MEMBERS_EDIT_ON_SITE']; ?> <a target="_blank" href="/clubs/<?php echo $mod['id']; ?>/config.html#moders"><?php echo $_LANG['AD_EDIT_ON_SITE']; ?></a>.
			<?php } ?>
		    </td>
    	</tr>
    </table>
    {/tabs}
    <p>
        <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
        <input name="opt" type="hidden" id="opt" <?php if ($opt=='add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
        <?php
        if ($opt=='edit'){
            echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
        }
        ?>
    </p>
</form>

    <?php	echo jwTabs(ob_get_clean());

}

if ($opt=='config') {

	$GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/includes/jquery/tabs/jquery.ui.min.js"></script>';
	$GLOBALS['cp_page_head'][] = '<link href="/includes/jquery/tabs/tabs.css" rel="stylesheet" type="text/css" />';

    cpAddPathway($_LANG['AD_SETTINGS']);

    ?>

<form action="index.php?view=components&do=config&id=<?php echo $id;?>" method="post" name="addform" id="addform">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
<div id="config_tabs" style="margin-top:12px;" class="uitabs">

    <ul id="tabs">
        <li><a href="#basic"><span><?php echo $_LANG['AD_OVERALL']; ?></span></a></li>
        <li><a href="#limits"><span><?php echo $_LANG['AD_LISTS_LIMIT']; ?></span></a></li>
        <li><a href="#photos"><span><?php echo $_LANG['AD_FOTO']; ?></span></a></li>
        <li><a href="#restrict"><span><?php echo $_LANG['LIMITS']; ?></span></a></li>
        <li><a href="#seo"><span><?php echo $_LANG['AD_SEO']; ?></span></a></li>
    </ul>
    <div id="seo">

        <table width="680" border="0" cellspacing="5" class="proptable">
                <tr>
                    <td>
                        <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METAKEYS']; ?></strong><br />
                        <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                        <textarea name="meta_keys" rows="2" style="width:580px"><?php echo $cfg['meta_keys'] ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METADESC']; ?></strong><br />
                        <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                        <textarea name="meta_desc" rows="4" style="width:580px"><?php echo $cfg['meta_desc'] ?></textarea>
                    </td>
                </tr>
        </table>

        <table width="680" border="0" cellspacing="5" class="proptable">
                <tr>
                     <td><strong><?php echo $_LANG['AD_USER_SEO_ACCESS']; ?> </strong></td>
                     <td>
                         <label><input name="seo_user_access" type="radio" value="1" <?php if ($cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                         <label><input name="seo_user_access" type="radio" value="0"  <?php if (!$cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                     </td>
                 </tr>
         </table>
    </div>
	<div id="basic">
    <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['CLUB_BLOG']; ?>:</strong></td>
            <td width="300">
                <label><input name="enabled_blogs" type="radio" value="1"  <?php if ($cfg['enabled_blogs']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                <label><input name="enabled_blogs" type="radio" value="0"  <?php if (!$cfg['enabled_blogs']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_LOGO_SMALL_WIDTH']; ?>:</strong><br />
            <span class="hinttext"><?php echo $_LANG['AD_PX']; ?></span></td>
            <td><input class="uispin" name="thumb1" type="text" id="thumb1" style="width:300px" value="<?php echo $cfg['thumb1'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_LOGO_MEDIUM_WIDTH']; ?>:</strong><br />
            <span class="hinttext"><?php echo $_LANG['AD_PX']; ?></span></td>
            <td><input class="uispin" name="thumb2" type="text" id="thumb2" style="width:300px" value="<?php echo $cfg['thumb2'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_SQUARE_LOGO']; ?>:</strong></td>
            <td>
                <label><input name="thumbsqr" type="radio" value="1"  <?php if ($cfg['thumbsqr']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                <label><input name="thumbsqr" type="radio" value="0"  <?php if (!$cfg['thumbsqr']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_NOTIFICATION_IN']; ?></strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_NOTIFICATION_IN_HINT']; ?></span>
            </td>
            <td valign="top">
                <label><input name="notify_in" type="radio" value="1"  <?php if ($cfg['notify_in']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                <label><input name="notify_in" type="radio" value="0"  <?php if (!$cfg['notify_in']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_NOTIFICATION_OUT']; ?></strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_NOTIFICATION_OUT_HINT']; ?></span>
            </td>
            <td valign="top">
                <label><input name="notify_out" type="radio" value="1"  <?php if ($cfg['notify_out']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                <label><input name="notify_out" type="radio" value="0"  <?php if (!$cfg['notify_out']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
    </table>
	</div>
	<div id="limits">
    <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_CLUB_COUNT']; ?>:</strong><br /></td>
            <td><input class="uispin" name="perpage" type="text" style="width:300px" value="<?php echo $cfg['perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_MEMBER_COUNT_CLUB_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="club_perpage" type="text" style="width:300px" value="<?php echo $cfg['club_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_MEMBER_COUNT_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="member_perpage" type="text" style="width:300px" value="<?php echo $cfg['member_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_RECORDS_COUNT']; ?>:</strong><br /></td>
            <td><input class="uispin" name="wall_perpage" type="text" style="width:300px" value="<?php echo $cfg['wall_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_POST_COUNT_CLUB_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="club_posts_perpage" type="text" style="width:300px" value="<?php echo $cfg['club_posts_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_POST_COUNT_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="posts_perpage" type="text" style="width:300px" value="<?php echo $cfg['posts_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_ALBUM_COUNT_CLUB_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="club_album_perpage" type="text" style="width:300px" value="<?php echo $cfg['club_album_perpage'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_ALBUM_COUNT_PAGE']; ?>:</strong><br /></td>
            <td><input class="uispin" name="photo_perpage" type="text" style="width:300px" value="<?php echo $cfg['photo_perpage'];?>"/></td>
        </tr>
    </table>
	</div>
    <div id="photos">
    <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['CLUB_PHOTOALBUMS']; ?>:</strong></td>
            <td width="300">
                <label><input name="enabled_photos" type="radio" value="1"  <?php if ($cfg['enabled_photos']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                <label><input name="enabled_photos" type="radio" value="0"  <?php if (!$cfg['enabled_photos']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
          <td><strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong><br>
            <span class="hinttext">
              <?php echo $_LANG['AD_ENABLE_WATERMARK_HINT']; ?> "<a href="/images/watermark.png" target="_blank">/images/watermark.png</a>"
            </span>
          </td>
          <td>
            <label><input name="photo_watermark" type="radio" value="1"  <?php if ($cfg['photo_watermark']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['YES']; ?></label>
            <label><input name="photo_watermark" type="radio" value="0" <?php if (!$cfg['photo_watermark']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['NO']; ?></label>
          </td>
        </tr>
        <tr>
          <td><strong><?php echo $_LANG['AD_RETAIN_BOOT']; ?></strong></td>
          <td>
            <label><input name="is_saveorig" type="radio" value="1"  <?php if ($cfg['is_saveorig']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['YES']; ?></label>
            <label><input name="is_saveorig" type="radio" value="0" <?php if (!$cfg['is_saveorig']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['NO']; ?></label>
          </td>
        </tr>
        <tr>
          <td><strong><?php echo $_LANG['AD_PHOTO_SMALL_WIDTH']; ?>:</strong></td>
          <td>
            <table border="0" cellspacing="0" cellpadding="1">
              <tbody>
                <tr>
                  <td width="100" valign="middle">
                    <input class="uispin" name="photo_thumb_small" type="text" size="3" value="<?php echo $cfg['photo_thumb_small']; ?>"> <?php echo $_LANG['AD_PX']; ?>
                  </td>
                  <td width="100" align="center" valign="middle"><?php echo $_LANG['AD_SQUARE_PHOTO']; ?>:</td>
                  <td width="115" align="center" valign="middle">
                    <label><input name="photo_thumbsqr" type="radio" value="1" <?php if ($cfg['photo_thumbsqr']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['YES']; ?> </label>
                    <label><input name="photo_thumbsqr" type="radio" value="0" <?php if (!$cfg['photo_thumbsqr']) { echo 'checked="checked"'; } ?>> <?php echo $_LANG['NO']; ?></label>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td><strong><?php echo $_LANG['AD_PHOTO_MEDIUM_WIDTH']; ?>:</strong></td>
          <td><input class="uispin" name="photo_thumb_medium" type="text" size="3" value="<?php echo $cfg['photo_thumb_medium']; ?>"> <?php echo $_LANG['AD_PX']; ?></td>
        </tr>
        <tr>
          <td><strong><?php echo $_LANG['AD_PHOTO_MAXCOLS']; ?>:</strong></td>
          <td><input class="uispin" name="photo_maxcols" type="text" size="5" value="<?php echo $cfg['photo_maxcols']; ?>"></td>
        </tr>
    </table>
    </div>
    <div id="restrict">
    <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td><strong><?php echo $_LANG['AD_CANCREATE']; ?>:</strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_CANCREATE_HINT']; ?></span></td>
            <td valign="top">
                <label><input name="cancreate" type="radio" value="1"  <?php if ($cfg['cancreate']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
            	<label><input name="cancreate" type="radio" value="0"  <?php if (!$cfg['cancreate']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
            </td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_EVERY_KARMA']; ?>:</strong><br />
            <span class="hinttext"><?php echo $_LANG['AD_EVERY_KARMA_HINT']; ?></span></td>
            <td valign="top"><input class="uispin" name="every_karma" type="text" id="every_karma" style="width:300px" value="<?php echo $cfg['every_karma'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_CREATE_MIN_KARMA']; ?>:</strong><br />
            <span class="hinttext"><?php echo $_LANG['AD_CREATE_MIN_KARMA_HINT']; ?></span></td>
            <td valign="top"><input class="uispin" name="create_min_karma" type="text" id="create_min_karma" style="width:300px" value="<?php echo $cfg['create_min_karma'];?>"/></td>
        </tr>
        <tr>
            <td><strong><?php echo $_LANG['AD_CREATE_MIN_RATING']; ?>:</strong><br />
            <span class="hinttext"><?php echo $_LANG['AD_CREATE_MIN_RATING_HINT']; ?></span></td>
            <td valign="top"><input class="uispin" name="create_min_rating" type="text" id="create_min_rating" style="width:300px" value="<?php echo $cfg['create_min_rating'];?>"/></td>
        </tr>
    </table>
    </div>

</div>
<p>
    <input name="opt" type="hidden" value="saveconfig" />
    <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
    <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id; ?>'"/>
</p>
</form>
<?php } ?>