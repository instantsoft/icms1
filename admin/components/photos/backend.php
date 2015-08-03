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

    $cfg = $inCore->loadComponentConfig('photos');

	cmsCore::loadClass('photo');
    cmsCore::loadModel('photos');
    $model = new cms_model_photos();

    $opt = cmsCore::request('opt', 'str', 'list_albums');

//=================================================================================================//

	if($opt=='saveconfig'){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$cfg = array();
		$cfg['link']                = cmsCore::request('show_link', 'int', 0);
        $cfg['saveorig']            = cmsCore::request('saveorig', 'int', 0);
        $cfg['maxcols']             = cmsCore::request('maxcols', 'int', 0);
        $cfg['orderby']             = cmsCore::request('orderby', 'str', '');
        $cfg['orderto']             = cmsCore::request('orderto', 'str', '');
        $cfg['showlat']             = cmsCore::request('showlat', 'int', 0);
        $cfg['watermark']           = cmsCore::request('watermark', 'int', 0);
        $cfg['meta_keys']           = cmsCore::request('meta_keys', 'str', '');
        $cfg['meta_desc']           = cmsCore::request('meta_desc', 'str', '');
        $cfg['seo_user_access']     = cmsCore::request('seo_user_access', 'int', 0);
        $cfg['best_latest_perpage'] = cmsCore::request('best_latest_perpage', 'int', 0);
        $cfg['best_latest_maxcols'] = cmsCore::request('best_latest_maxcols', 'int', 0);

        $inCore->saveComponentConfig('photos', $cfg);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

		cmsCore::redirectBack();

	}

//=================================================================================================//
//=================================================================================================//

	if ($opt=='list_albums'){

        $toolmenu[] = array('icon'=>'newfolder.gif', 'title'=>$_LANG['AD_ALBUM_ADD'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_album');
        $toolmenu[] = array('icon'=>'folders.gif', 'title'=>$_LANG['AD_ALBUMS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_albums');
        $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['AD_SETTINGS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=config');

	}

	if (in_array($opt, array('config','add_album','edit_album'))){

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

	}

	cpToolMenu($toolmenu);

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'config') {

        cpAddPathway($_LANG['AD_SETTINGS']);

        cpCheckWritable('/images/photos', 'folder');
		cpCheckWritable('/images/photos/medium', 'folder');
		cpCheckWritable('/images/photos/small', 'folder'); ?>

        <form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>" method="post" enctype="multipart/form-data" name="addform">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="" border="0" cellpadding="10" cellspacing="0" class="proptable" style="width: 550px;">
            <tr>
              <td width="300"><strong><?php echo $_LANG['AD_SHOW_LINKS_ORIGINAL']; ?>: </strong></td>
              <td width="">
                <label><input name="show_link" type="radio" value="1" <?php if ($cfg['link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                <label><input name="show_link" type="radio" value="0" <?php if (!$cfg['link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
              </td>
            </tr>
            <tr>
              <td><strong><?php echo $_LANG['AD_RETAIN_BOOT']; ?>:</strong> </td>
              <td>
                  <label><input name="saveorig" type="radio" value="1" <?php if ($cfg['saveorig']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?> </label>
                  <label><input name="saveorig" type="radio" value="0" <?php if (!$cfg['saveorig']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label></td>
            </tr>
            <tr>
              <td><strong><?php echo $_LANG['AD_NUMBER_COLUMS']; ?>: </strong></td>
              <td><input class="uispin" name="maxcols" type="text" id="maxcols" size="5" value="<?php echo $cfg['maxcols'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
              <td valign="top"><strong><?php echo $_LANG['AD_ALBUM_SORT']; ?>: </strong></td>
              <td><select name="orderby" style="width:190px">
                <option value="title" <?php if($cfg['orderby']=='title') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_ALPHABET']; ?></option>
                <option value="pubdate" <?php if($cfg['orderby']=='pubdate') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_CALENDAR']; ?></option>
              </select>
                <select name="orderto" style="width:190px">
                  <option value="desc" <?php if($cfg['orderto']=='desc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_DECREMENT']; ?></option>
                  <option value="asc" <?php if($cfg['orderto']=='asc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_INCREMENT']; ?></option>
                </select></td>
            </tr>
            <tr>
              <td><strong><?php echo $_LANG['AD_SHOW_LINKS_LATEST']; ?>: </strong></td>
              <td>
                <label><input name="showlat" type="radio" value="1" <?php if ($cfg['showlat']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                <label><input name="showlat" type="radio" value="0" <?php if (!$cfg['showlat']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
              </td>
            </tr>
            <tr>
              <td><strong><?php echo $_LANG['AD_SHOW_NUMBER']; ?>: </strong></td>
              <td>
                <input class="uispin" name="best_latest_perpage" type="text" size="5" value="<?php echo $cfg['best_latest_perpage']; ?>"/> <?php echo $_LANG['AD_PIECES']; ?>
              </td>
            </tr>
            <tr>
              <td><strong><?php echo $_LANG['AD_SHOW_NUMBER_COLUMN']; ?>: </strong></td>
              <td>
                <input class="uispin" name="best_latest_maxcols" type="text" size="5" value="<?php echo $cfg['best_latest_maxcols']; ?>"/> <?php echo $_LANG['AD_PIECES']; ?>
              </td>
            </tr>
            <tr>
              <td>
                  <strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong><br />
                  <span class="hinttext"><?php echo $_LANG['AD_WATERMARK_PHOTOS_HINT']; ?> "<a href="/images/watermark.png" target="_blank">/images/watermark.png</a>"</span></td>
              <td>
                <label><input name="watermark" type="radio" value="1" <?php if ($cfg['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                <label><input name="watermark" type="radio" value="0" <?php if (!$cfg['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?>	</label>  				  </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METAKEYS']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:580px"><?php echo $cfg['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METADESC']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:580px"><?php echo $cfg['meta_desc'] ?></textarea>
                </td>
            </tr>
            <tr>
                 <td><strong><?php echo $_LANG['AD_USER_SEO_ACCESS']; ?> </strong></td>
                 <td>
                     <label><input name="seo_user_access" type="radio" value="1" <?php if ($cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="seo_user_access" type="radio" value="0"  <?php if (!$cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
          </table>
          <p>
            <input name="opt" type="hidden" value="saveconfig" />
            <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id;?>';"/>
          </p>
    </form>
		<?php
	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'show_album'){
        $item_id = cmsCore::request('item_id', 'int', 0);
        $inDB->query("UPDATE cms_photo_albums SET published = 1 WHERE id = '$item_id'") ;
        echo '1'; exit;
	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'hide_album'){
        $item_id = cmsCore::request('item_id', 'int', 0);
        $inDB->query("UPDATE cms_photo_albums SET published = 0 WHERE id = '$item_id'") ;
        echo '1'; exit;
	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'submit_album'){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $album['title']       = cmsCore::request('title', 'str', 'NO_TITLE');
        $album['description'] = cmsCore::request('description', 'str');
        $album['published']   = cmsCore::request('published', 'int');
        $album['showdate']    = cmsCore::request('showdate', 'int');
        $album['parent_id']   = cmsCore::request('parent_id', 'int');
        $album['showtype']    = cmsCore::request('showtype', 'str');
        $album['public']      = cmsCore::request('public', 'int');
        $album['orderby']     = cmsCore::request('orderby', 'str');
        $album['orderto']     = cmsCore::request('orderto', 'str');
        $album['perpage']     = cmsCore::request('perpage', 'int');
        $album['thumb1']      = cmsCore::request('thumb1', 'int');
        $album['thumb2']      = cmsCore::request('thumb2', 'int');
        $album['thumbsqr']    = cmsCore::request('thumbsqr', 'int');
        $album['cssprefix']   = cmsCore::request('cssprefix', 'str');
        $album['nav']         = cmsCore::request('nav', 'int');
        $album['uplimit']     = cmsCore::request('uplimit', 'int');
        $album['maxcols']     = cmsCore::request('maxcols', 'int');
        $album['orderform']   = cmsCore::request('orderform', 'int');
        $album['showtags']    = cmsCore::request('showtags', 'int');
        $album['bbcode']      = cmsCore::request('bbcode', 'int');
        $album['is_comments'] = cmsCore::request('is_comments', 'int');
        $album['meta_keys']   = cmsCore::request('meta_keys', 'str', '');
        $album['meta_desc']   = cmsCore::request('meta_desc', 'str', '');
        $album['pagetitle']   = cmsCore::request('pagetitle', 'str', '');

        $album = cmsCore::callEvent('ADD_ALBUM', $album);

		$inDB->addNsCategory('cms_photo_albums', $album);

		cmsCore::addSessionMessage($_LANG['AD_ALBUM'].' "'.stripslashes($album['title']).'" '.$_LANG['AD_ALBUM_CREATED'], 'success');

		cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_albums');

	}

//=================================================================================================//
//=================================================================================================//

	if($opt == 'delete_album'){

		if(cmsCore::inRequest('item_id')){

			$album = $inDB->getNsCategory('cms_photo_albums', cmsCore::request('item_id', 'int', 0));
			if (!$album) { cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_albums'); }

			cmsCore::addSessionMessage($_LANG['AD_ALBUM'].' "'.stripslashes($album['title']).'", '.$_LANG['AD_EMBEDED_PHOTOS_REMOVED'].'.', 'success');

			cmsPhoto::getInstance()->deleteAlbum($album['id'], '', $model->initUploadClass($album));

		}

		cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_albums');

	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'update_album'){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $item_id = cmsCore::request('item_id', 'int', 0);

        $old_album = $inDB->getNsCategory('cms_photo_albums', $item_id);
        if (!$old_album) { cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_albums'); }

        $album['title']         = cmsCore::request('title', 'str', 'NO_TITLE');
        $album['description']   = cmsCore::request('description', 'str', '');
        $album['published']     = cmsCore::request('published', 'int');
        $album['showdate']      = cmsCore::request('showdate', 'int');
        $album['parent_id']     = cmsCore::request('parent_id', 'int');
        $album['is_comments']   = cmsCore::request('is_comments', 'int');
        $album['showtype']      = cmsCore::request('showtype', 'str');
        $album['public']        = cmsCore::request('public', 'int');
        $album['orderby']       = cmsCore::request('orderby', 'str');
        $album['orderto']       = cmsCore::request('orderto', 'str');
        $album['perpage']       = cmsCore::request('perpage', 'int');
        $album['thumb1']        = cmsCore::request('thumb1', 'int');
        $album['thumb2']        = cmsCore::request('thumb2', 'int');
        $album['thumbsqr']      = cmsCore::request('thumbsqr', 'int');
        $album['cssprefix']     = cmsCore::request('cssprefix', 'str');
        $album['nav']           = cmsCore::request('nav', 'int');
        $album['uplimit']       = cmsCore::request('uplimit', 'int');
        $album['maxcols']       = cmsCore::request('maxcols', 'int');
        $album['orderform']     = cmsCore::request('orderform', 'int');
        $album['showtags']      = cmsCore::request('showtags', 'int');
        $album['bbcode']        = cmsCore::request('bbcode', 'int');
        $album['iconurl']       = cmsCore::request('iconurl', 'str');
        $album['meta_keys']     = cmsCore::request('meta_keys', 'str', '');
        $album['meta_desc']     = cmsCore::request('meta_desc', 'str', '');
        $album['pagetitle']     = cmsCore::request('pagetitle', 'str', '');

        // если сменили категорию
        if($old_album['parent_id'] != $album['parent_id']){
            // перемещаем ее в дереве
            $inCore->nestedSetsInit('cms_photo_albums')->MoveNode($item_id, $album['parent_id']);
        }

        $inDB->update('cms_photo_albums', $album, $item_id);
        cmsCore::addSessionMessage($_LANG['AD_ALBUM'].' "'.stripslashes($album['title']).'" '.$_LANG['AD_ALBUM_SAVED'].'.', 'success');
        cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_albums');

	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'list_albums'){

		echo '<h3>'.$_LANG['AD_ALBUMS'].'</h3>';

		$fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
		$fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_album&item_id=%id%');
		$fields[] = array('title'=>$_LANG['AD_ALBUM_COMMENTS'], 'field'=>'is_comments', 'width'=>'95', 'prc'=>'cpYesNo');
		$fields[] = array('title'=>$_LANG['AD_ADDING_USERS'], 'field'=>'public', 'width'=>'100', 'prc'=>'cpYesNo');
		$fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'60', 'do'=>'opt', 'do_suffix'=>'_album');

        $actions[] = array('title'=>$_LANG['AD_VIEW_ONLINE'], 'icon'=>'search.gif', 'link'=>'/photos/%id%');
        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_album&item_id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_ALBUM_PHOTOS_DEL'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_album&item_id=%id%');

		cpListTable('cms_photo_albums', $fields, $actions, 'parent_id>0 AND NSDiffer=""', 'NSLeft');

	}

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'add_album' || $opt == 'edit_album'){
        if ($opt=='add_album'){
             cpAddPathway($_LANG['AD_ALBUM_ADD']);
             echo '<h3>'.$_LANG['AD_ALBUM_ADD'].'</h3>';
        } else {

            $item_id = cmsCore::request('item_id', 'int', 0);

            $mod = $inDB->getNsCategory('cms_photo_albums', $item_id);

            cpAddPathway($_LANG['AD_ALBUM_EDIT']);
            echo '<h3>'.$_LANG['AD_ALBUM_EDIT'].' "'.$mod['title'].'"</h3>';

        }

        //DEFAULT VALUES
        if (!isset($mod['thumb1'])) { $mod['thumb1'] = 96; }
        if (!isset($mod['thumb2'])) { $mod['thumb2'] = 450; }
        if (!isset($mod['thumbsqr'])) { $mod['thumbsqr'] = 1; }
        if (!isset($mod['is_comments'])) { $mod['is_comments'] = 0; }
        if (!isset($mod['maxcols'])) { $mod['maxcols'] = 4; }
        if (!isset($mod['showtype'])) { $mod['showtype'] = 'lightbox'; }
        if (!isset($mod['perpage'])) { $mod['perpage'] = '20'; }
        if (!isset($mod['uplimit'])) { $mod['uplimit'] = 20; }
        if (!isset($mod['published'])) { $mod['published'] = 1; }
        if (!isset($mod['orderby'])) { $mod['orderby'] = 'pubdate'; }

		?>
		<script type="text/javascript">
        function showMapMarker(){
            var file = $('select[name=iconurl]').val();
            if(file){
                $('#marker_demo').attr('src', '/images/photos/small/'+file).fadeIn();
            } else {
                $('#marker_demo').hide();
            }

        }
        </script>

        <form id="addform" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id;?>">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="610" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="300"><?php echo $_LANG['AD_ALBUM_TITLE']; ?>:</td>
                <td><input name="title" type="text" id="title" style="width:280px" value="<?php echo htmlspecialchars($mod['title']); ?>"/></td>
            </tr>
            <tr>
                <td valign="top"><?php echo $_LANG['AD_ALBUM_PARENT']; ?>:</td>
                <td valign="top">
                    <?php $rootid = $inDB->get_field('cms_photo_albums', "parent_id=0 AND NSDiffer=''", 'id'); ?>
                    <select name="parent_id" size="8" id="parent_id" style="width:285px">
                        <option value="<?php echo $rootid; ?>" <?php if (@$mod['parent_id']==$rootid || !isset($mod['parent_id'])) { echo 'selected'; }?>><?php echo $_LANG['AD_ALBUM_ROOT']; ?></option>
                        <?php
                            if (isset($mod['parent_id'])){
                                echo $inCore->getListItemsNS('cms_photo_albums', $mod['parent_id']);
                            } else {
                                echo $inCore->getListItemsNS('cms_photo_albums');
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_ALBUM_POST']; ?>?</td>
                    <td>
                        <label><input name="published" type="radio" value="1" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                        <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                    </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_SHOW_DATES_COMMENTS']; ?>?</td>
                    <td>
                        <label><input name="showdate" type="radio" value="1" <?php if (@$mod['showdate']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                        <label><input name="showdate" type="radio" value="0"  <?php if (@!$mod['showdate']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                    </td>
            </tr>
            <tr>
                <td valign="top"><?php echo $_LANG['AD_SHOW_TAGS']; ?>:</td>
                <td valign="top">
                    <label><input name="showtags" type="radio" value="1" checked="checked" <?php if (@$mod['showtags']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="showtags" type="radio" value="0"  <?php if (@!$mod['showtags']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top"><?php echo $_LANG['AD_SHOW_CODE_FORUM'] ; ?>:</td>
                <td valign="top">
                    <label><input name="bbcode" type="radio" value="1" checked="checked" <?php if (@$mod['bbcode']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="bbcode" type="radio" value="0"  <?php if (@!$mod['bbcode']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top"><?php echo $_LANG['AD_COMMENTS_ALBUM']; ?>:</td>
                <td valign="top">
                    <label><input name="is_comments" type="radio" value="1" checked="checked" <?php if (@$mod['is_comments']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="is_comments" type="radio" value="0"  <?php if (@!$mod['is_comments']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_SORT_PHOTOS']; ?>:</td>
                <td>
                    <select name="orderby" id="orderby" style="width:285px">
                        <option value="title" <?php if(@$mod['orderby']=='title') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_ALPHABET']; ?></option>
                        <option value="pubdate" <?php if(@$mod['orderby']=='pubdate') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_CALENDAR']; ?></option>
                        <option value="rating" <?php if(@$mod['orderby']=='rating') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_RATING']; ?></option>
                        <option value="hits" <?php if(@$mod['orderby']=='hits') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_VIEWS']; ?></option>
                    </select>
                    <select name="orderto" id="orderto" style="width:285px">
                        <option value="desc" <?php if(@$mod['orderto']=='desc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_DECREMENT']; ?></option>
                        <option value="asc" <?php if(@$mod['orderto']=='asc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_INCREMENT']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_OUTPUT_PHOTOS']; ?>:</td>
                <td>
                    <select name="showtype" id="showtype" style="width:285px">
                        <option value="thumb" <?php if(@$mod['showtype']=='thumb') { echo 'selected'; } ?>><?php echo $_LANG['AD_GALLERY']; ?></option>
                        <option value="lightbox" <?php if(@$mod['showtype']=='lightbox') { echo 'selected'; } ?>><?php echo $_LANG['AD_GALLERY_LIGHTBOX']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_NUMBER_COLUMS_PHOTOS']; ?>:</td>
                <td>
                    <input class="uispin" name="maxcols" type="text" id="maxcols" size="5" value="<?php echo @$mod['maxcols'];?>"/> <?php echo $_LANG['AD_PIECES']; ?>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_ADD_PHOTOS_USERS']; ?>:</td>
                <td>
                    <select name="public" id="select" style="width:285px">
                        <option value="0" <?php if(@$mod['public']=='0') { echo 'selected'; } ?>><?php echo $_LANG['AD_PROCHBITED']; ?></option>
                        <option value="1" <?php if(@$mod['public']=='1') { echo 'selected'; } ?>><?php echo $_LANG['AD_FROM_PREMODERATION']; ?></option>
                        <option value="2" <?php if(@$mod['public']=='2') { echo 'selected'; } ?>><?php echo $_LANG['AD_WITHOUT_PREMODERATION']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_UPLOAD_MAX']; ?>:</td>
                <td>
                    <input class="uispin" name="uplimit" type="text" id="uplimit" size="5" value="<?php echo @$mod['uplimit'];?>"/> <?php echo $_LANG['AD_PIECES']; ?>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_FORM_SORTING']; ?>:</td>
                <td>
                    <label><input name="orderform" type="radio" value="1" checked="checked" <?php if (@$mod['orderform']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['SHOW']; ?></label>
                    <label><input name="orderform" type="radio" value="0"  <?php if (@!$mod['orderform']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['HIDE']; ?></label>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_ALBUM_NAVIGATTING']; ?>:</td>
                <td>
                    <label><input name="nav" type="radio" value="1" <?php if (@$mod['nav']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="nav" type="radio" value="0"  <?php if (@!$mod['nav']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_CSS_PREFIX']; ?>:</td>
                <td><input name="cssprefix" type="text" id="cssprefix" size="10" value="<?php echo @$mod['cssprefix'];?>"/></td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_PHOTOS_ON_PAGE']; ?>:</td>
                <td>
                    <input class="uispin" name="perpage" type="text" id="perpage" size="5" value="<?php echo @$mod['perpage'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_WIDTH_SMALL_COPY']; ?>: </td>
                <td>
                    <table border="0" cellspacing="0" cellpadding="1">
                        <tr>
                            <td width="100" valign="middle">
                                <input class="uispin" name="thumb1" type="text" id="thumb1" size="3" value="<?php echo @$mod['thumb1'];?>"/> <?php echo $_LANG['AD_PX']; ?>.
                            </td>
                            <td width="100" align="center" valign="middle" style="background-color:#EBEBEB"><?php echo $_LANG['AD_PHOTOS_SQUARE']; ?>:</td>
                            <td width="115" align="center" valign="middle" style="background-color:#EBEBEB">
                                <label><input name="thumbsqr" type="radio" value="1" checked="checked" <?php if (@$mod['thumbsqr']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                                <label><input name="thumbsqr" type="radio" value="0"  <?php if (@!$mod['thumbsqr']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?> </label>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td><?php echo $_LANG['AD_WIDTH_MIDDLE_COPY']; ?>: </td>
                <td>
                    <input class="uispin" name="thumb2" type="text" id="thumb2" size="3" value="<?php echo @$mod['thumb2'];?>"/> <?php echo $_LANG['AD_PX']; ?>.
                </td>
            </tr>
            <?php
                if ($opt=='edit_album'){ ?>
            <tr>
                <td valign="top"><?php echo $_LANG['AD_MINI_SKETCH']; ?>:<br />
                <?php if (!empty($mod['iconurl']) && file_exists(PATH.'/images/photos/small/'.$mod['iconurl'])){ ?>
                    <img id="marker_demo" src="/images/photos/small/<?php echo $mod['iconurl']; ?>">
                <?php  } else { ?>
                    <img id="marker_demo" src="/images/photos/no_image.png" style="display: none;">
                <?php  } ?>
                </td>
                <td valign="top">
                <?php if ($inDB->rows_count('cms_photo_files', 'album_id = '.$item_id.'')) { ?>
                    <select name="iconurl" id="iconurl" style="width:285px" onchange="showMapMarker()">
                        <?php
                            if (!empty($mod['iconurl']) && file_exists(PATH.'/images/photos/small/'.$mod['iconurl'])){
                                echo $inCore->getListItems('cms_photo_files', $mod['iconurl'], 'id', 'ASC', 'album_id = '.$item_id.' AND published = 1', 'file');
                            } else {
                                echo '<option value="" selected="selected">'.$_LANG['AD_MINI_SKETCH_CHOOSE'].'</option>';
                                echo $inCore->getListItems('cms_photo_files', '', 'id', 'ASC', 'album_id = '.$item_id.' AND published = 1', 'file');
                            }
                        ?>
                    </select>
                   <?php  } else { ?>
                        <?php echo $_LANG['AD_ALBUM_NO_PHOTOS']; ?>.
                   <?php  } ?>
                </td>
            </tr>
        <?php
            }
        ?>
        </table>
        <table border="0" width="610" cellspacing="5" class="proptable">
            <tr>
                <td>
                <div style="margin:5px 0px 5px 0px"><strong><?php echo $_LANG['AD_ALBUM_DESCR']; ?>:</strong></div>
                <textarea name="description" style="width:580px" rows="4"><?php echo @$mod['description']?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_PAGETITLE'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_PAGETITLE_HINT'] ?><br /></div>
                    <textarea name="pagetitle" rows="2" style="width:580px"><?php echo @$mod['pagetitle'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METAKEYS'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:580px"><?php echo @$mod['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METADESCR'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:580px"><?php echo @$mod['meta_desc'] ?></textarea>
                </td>
            </tr>
        </table>

        <p>
            <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_album') { echo 'value="submit_album"'; } else { echo 'value="update_album"'; } ?> />
            <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back2" type="button" id="back2" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id; ?>';"/>
            <?php
                if ($opt=='edit_album'){
                    echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
                }
            ?>
        </p>
    </form>
<?php	}