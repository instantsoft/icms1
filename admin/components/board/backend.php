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

function uploadCategoryIcon($file='') {

    // Загружаем класс загрузки фото
    cmsCore::loadClass('upload_photo');
    $inUploadPhoto = cmsUploadPhoto::getInstance();
    // Выставляем конфигурационные параметры
    $inUploadPhoto->upload_dir    = PATH.'/upload/board/';
    $inUploadPhoto->dir_medium    = 'cat_icons/';
    $inUploadPhoto->medium_size_w = 32;
    $inUploadPhoto->medium_size_h = 32;
    $inUploadPhoto->only_medium   = true;
    $inUploadPhoto->is_watermark  = false;
    // Процесс загрузки фото
    $files = $inUploadPhoto->uploadPhoto($file);

    $icon = $files['filename'] ? $files['filename'] : $file;

    return $icon;

}
// ========================================================================== //

$cfg = $inCore->loadComponentConfig('board');
cmsCore::loadModel('board');
$model = new cms_model_board();

define('IS_BILLING', $inCore->isComponentInstalled('billing'));
if (IS_BILLING) { cmsCore::loadClass('billing'); }

$opt = cmsCore::request('opt', 'str', 'list_items');

// ========================================================================== //

if($opt=='saveconfig'){

    if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg = array();

    $cfg['maxcols']            = cmsCore::request('maxcols', 'int', 0);
    $cfg['obtypes']            = cmsCore::request('obtypes', 'html', '');
    $cfg['showlat']            = cmsCore::request('showlat', 'str', '');
    $cfg['public']             = cmsCore::request('public', 'int', 0);
    $cfg['photos']             = cmsCore::request('photos', 'int', 0);
    $cfg['srok']               = cmsCore::request('srok', 'int', 0);
    $cfg['pubdays']            = cmsCore::request('pubdays', 'int', 0);
    $cfg['watermark']          = cmsCore::request('watermark', 'int', 0);
    $cfg['aftertime']          = cmsCore::request('aftertime', 'str', '');
    $cfg['comments']           = cmsCore::request('comments', 'int', 0);
    $cfg['extend']             = cmsCore::request('extend', 'int', 0);
    $cfg['auto_link']          = cmsCore::request('auto_link', 'int', 0);
    $cfg['vip_enabled']        = cmsCore::request('vip_enabled', 'int', 0);
    $cfg['vip_prolong']        = cmsCore::request('vip_prolong', 'int', 0);
    $cfg['vip_max_days']       = cmsCore::request('vip_max_days', 'int', 30);
    $cfg['vip_day_cost']       = cmsCore::request('vip_day_cost', 'str', 5);
    $cfg['home_perpage']       = cmsCore::request('home_perpage', 'int', 15);
    $cfg['maxcols_on_home']    = cmsCore::request('maxcols_on_home', 'int', 1);
    $cfg['publish_after_edit'] = cmsCore::request('publish_after_edit', 'int', 0);

    $cfg['vip_day_cost'] = str_replace(',', '.', trim($cfg['vip_day_cost']));

	$cfg['root_description'] = cmsCore::request('root_description', 'html', '');
    $cfg['meta_keys']        = cmsCore::request('meta_keys', 'str', '');
    $cfg['meta_desc']        = cmsCore::request('meta_desc', 'str', '');
    $cfg['seo_user_access']  = cmsCore::request('seo_user_access', 'int', 0);

    $inCore->saveComponentConfig('board', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
    cmsCore::redirectBack();

}

if ($opt=='list_items' || $opt=='list_cats' || $opt=='config'){

    $toolmenu[] = array('icon'=>'newstuff.gif', 'title'=>$_LANG['ADD_ADV'], 'link'=>'/board/add.html');
    $toolmenu[] = array('icon'=>'newfolder.gif', 'title'=>$_LANG['AD_NEW_CAT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_cat');
    $toolmenu[] = array('icon'=>'liststuff.gif', 'title'=>$_LANG['AD_ALL_AD'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_items');
    $toolmenu[] = array('icon'=>'folders.gif', 'title'=>$_LANG['AD_ALL_CAT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_cats');

    if($opt=='list_items'){

        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id={$id}&opt=show_item&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id={$id}&opt=hide_item&multiple=1');");

    }

    $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['AD_SETTINGS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=config');

}

if ($opt=='add_cat' || $opt=='edit_cat'){

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

}

cpToolMenu($toolmenu);

if ($opt == 'show_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbShow('cms_board_items', (int)$_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbShowList('cms_board_items', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'hide_item'){
    if (!isset($_REQUEST['item'])){
        dbHide('cms_board_items', cmsCore::request('item_id', 'int', 0));
        echo '1'; exit;
    } else {
        dbHideList('cms_board_items', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if($opt == 'delete_item'){
    $model->deleteRecord(cmsCore::request('item_id', 'int', 0));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

if ($opt == 'config') {

    cpAddPathway($_LANG['AD_SETTINGS']);

    cpCheckWritable('/images/board', 'folder');
    cpCheckWritable('/images/board/medium', 'folder');
    cpCheckWritable('/images/board/small', 'folder');
?>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id; ?>" method="post" name="optform" target="_self" id="form1">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
<div id="config_tabs" style="margin-top:12px;" class="uitabs">

    <ul id="tabs">
        <li><a href="#basic"><span><?php echo $_LANG['AD_OVERALL']; ?></span></a></li>
        <li><a href="#access"><span><?php echo $_LANG['AD_TAB_ACCESS']; ?></span></a></li>
        <li><a href="#types"><span><?php echo $_LANG['AD_TYPES']; ?></span></a></li>
        <li><a href="#vip"><span><?php echo $_LANG['AD_VIP']; ?></span></a></li>
		<li><a href="#seo"><span><?php echo $_LANG['AD_SEO']; ?></span></a></li>
    </ul>

    <div id="basic">
        <table width="600" border="0" cellpadding="0" cellspacing="10" class="proptable" style="border:none">
            <tr>
                <td><strong><?php echo $_LANG['AD_PHOTO_ENABLE']; ?>:</strong></td>
                <td width="250">
                    <label><input name="photos" type="radio" value="1" <?php if (@$cfg['photos']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="photos" type="radio" value="0" <?php if (@!$cfg['photos']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong>
                </td>
                <td valign="top">
                    <label><input name="watermark" type="radio" value="1" <?php if (@$cfg['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="watermark" type="radio" value="0" <?php if (@!$cfg['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_COMENT_TO_AD']; ?>:</strong>
                </td>
                <td valign="top">
                    <label><input name="comments" type="radio" value="1" <?php if (@$cfg['comments']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="comments" type="radio" value="0" <?php if (@!$cfg['comments']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_HOW_MANY_AD']; ?>: </strong></td>
                <td width="250"><input class="uispin" name="home_perpage" type="text" id="home_perpage" size="5" value="<?php echo @$cfg['home_perpage'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_HOW_MANY_COLUMNS_AD']; ?>: </strong></td>
                <td width="250"><input class="uispin" name="maxcols_on_home" type="text" id="maxcols_on_home" size="5" value="<?php echo @$cfg['maxcols_on_home'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_HOW_MANY_COLUMNS_CAT']; ?>: </strong></td>
                <td width="250"><input class="uispin" name="maxcols" type="text" id="maxcols" size="5" value="<?php echo @$cfg['maxcols'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_AUTOLINK_ENABLE']; ?>:</strong></td>
                <td width="250">
                    <label><input name="auto_link" type="radio" value="1" <?php if (@$cfg['auto_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="auto_link" type="radio" value="0" <?php if (@!$cfg['auto_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div id="access">
        <table width="600" border="0" cellpadding="0" cellspacing="10" class="proptable" style="border:none">
            <tr>
                <td width="250">
                    <strong><?php echo $_LANG['AD_ADD_AD']; ?>: </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_RELATION_SETTING']; ?></span>
                </td>
                <td valign="top">
                    <select name="public" id="public" style="width:260px">
                        <option value="0" <?php if(@$cfg['public']=='0') { echo 'selected'; } ?>><?php echo $_LANG['AD_TABOO']; ?></option>
                        <option value="1" <?php if(@$cfg['public']=='1') { echo 'selected'; } ?>><?php echo $_LANG['AD_PREMODERATION']; ?></option>
                        <option value="2" <?php if(@$cfg['public']=='2') { echo 'selected'; } ?>><?php echo $_LANG['AD_WITHOUT_MODERATION']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="250">
                    <strong><?php echo $_LANG['AD_WITH_MODERATION']; ?>: </strong><br/>
                </td>
                <td valign="top">
                    <select name="publish_after_edit" id="publish_after_edit" style="width:260px">
                        <option value="0" <?php if(@$cfg['publish_after_edit']=='0') { echo 'selected'; } ?>><?php echo $_LANG['AD_DEFAULT']; ?></option>
                        <option value="1" <?php if(@$cfg['publish_after_edit']=='1') { echo 'selected'; } ?>><?php echo $_LANG['AD_NO_MODERATION']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_DATA_AD']; ?>:</strong></td>
                <td valign="top">
                    <div><label><input name="srok" type="radio" value="1" <?php if (@$cfg['srok']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_ENABLE_SELECTION']; ?></label></div>
                    <div><label><input name="srok" type="radio" value="0" <?php if (@!$cfg['srok']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_FIXED']; ?>:</label> <input class="uispin" name="pubdays" type="text" id="pubdays" size="3" value="<?php echo @$cfg['pubdays'];?>"/> <?php echo $_LANG['DAY10']; ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_OVERDUE_AD']; ?>: </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_ACTION_SELECT']; ?></span>
                </td>
                <td valign="top">
                    <select name="aftertime" id="aftertime" style="width:260px">
                        <option value="delete" <?php if(@$cfg['aftertime']=='delete') { echo 'selected'; } ?>><?php echo $_LANG['DELETE']; ?></option>
                        <option value="hide" <?php if(@$cfg['aftertime']=='hide') { echo 'selected'; } ?>><?php echo $_LANG['HIDE']; ?></option>
                        <option value="" <?php if(@$cfg['aftertime']=='') { echo 'selected'; } ?>><?php echo $_LANG['AD_NOTHING']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_PROLONGATION']; ?> </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_IF_HIDE']; ?></span>
                </td>
                <td valign="top">
                    <label><input name="extend" type="radio" value="1" <?php if (@$cfg['extend']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="extend" type="radio" value="0" <?php if (@!$cfg['extend']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div id="types">
        <table width="600" border="0" cellpadding="0" cellspacing="10" class="proptable" style="border:none">
            <tr>
                <td width="250" valign="top">
                    <div><strong><?php echo $_LANG['AD_TYPES_AD']; ?></strong></div>
                    <div class="hinttext"><?php echo $_LANG['AD_NEW_LINE_TYPES']; ?></div>
                    <div class="hinttext"><?php echo $_LANG['AD_DIFFERENT_TYPES']; ?></div>
                </td>
                <td valign="top">
                    <textarea name="obtypes" style="width:250px" rows="10"><?php echo @$cfg['obtypes'];?></textarea>
                </td>
            </tr>
        </table>
    </div>

    <div id="vip">
        <?php if (!IS_BILLING){ ?>
            <p>
                <?php echo $_LANG['AD_SUPPORT_VIP_AD']; ?> &laquo;<a href="http://www.instantcms.ru/billing/about.html"><?php echo $_LANG['AD_BILLING']; ?></a>&raquo;
            </p>
            <p>
                <?php echo $_LANG['AD_INFO_0']; ?>
            </p>
            <p>
                <?php echo $_LANG['AD_WITHOUT_COMPONENT']; ?> &laquo;<a href="http://www.instantcms.ru/billing/about.html"><?php echo $_LANG['AD_BILLING']; ?></a>&raquo; <?php echo $_LANG['AD_INFO_1']; ?>
            </p>
        <?php } else { ?>
            <table width="550" border="0" cellpadding="0" cellspacing="10" class="proptable" style="border:none">
                <tr>
                    <td><strong><?php echo $_LANG['AD_ENABLE_VIP_AD']; ?></strong></td>
                    <td width="250">
                        <label><input name="vip_enabled" type="radio" value="1" <?php if (@$cfg['vip_enabled']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                        <label><input name="vip_enabled" type="radio" value="0" <?php if (@!$cfg['vip_enabled']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo $_LANG['AD_ENABLE_VIP_STATUS']; ?></strong></td>
                    <td width="250">
                        <label><input name="vip_prolong" type="radio" value="1" <?php if (@$cfg['vip_prolong']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                        <label><input name="vip_prolong" type="radio" value="0" <?php if (@!$cfg['vip_prolong']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo $_LANG['AD_MAX_DATE_VIP_STATUS']; ?> </strong></td>
                    <td width="250">
                        <input name="vip_max_days" type="text" id="vip_max_days" size="5" value="<?php echo @$cfg['vip_max_days'];?>"/> <?php echo $_LANG['DAY10']; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo $_LANG['AD_COST_VIP_STATUS']; ?> </strong></td>
                    <td width="250">
                        <input name="vip_day_cost" type="text" id="vip_day_cost" size="5" value="<?php echo @$cfg['vip_day_cost'];?>"/> <?php echo $_LANG['AD_COST_ONE_DAY']; ?>
                    </td>
                </tr>
            </table>
        <?php } ?>
    </div>

    <div id="seo">

        <table width="610" border="0" cellspacing="5" class="proptable">
                <tr>
                    <td><strong><?php echo $_LANG['AD_ROOT_DESCRIPION']; ?></strong>
                        <p><textarea name="root_description" rows="6" style="width:580px"><?php echo @$cfg['root_description']; ?></textarea></p>
                    </td>
                </tr>
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

        <table width="610" border="0" cellspacing="5" class="proptable">
                <tr>
                     <td><strong><?php echo $_LANG['AD_USER_SEO_ACCESS']; ?> </strong></td>
                     <td>
                         <label><input name="seo_user_access" type="radio" value="1" <?php if ($cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                         <label><input name="seo_user_access" type="radio" value="0"  <?php if (!$cfg['seo_user_access']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                     </td>
                 </tr>
         </table>
    </div>

<p>
    <input name="opt" type="hidden" id="do" value="saveconfig" />
    <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
    <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
</p>
</form>

<?php }

if ($opt == 'show_cat'){
    $item_id = cmsCore::request('item_id', 'int', 0);
    $inDB->query("UPDATE cms_board_cats SET published = 1 WHERE id = '$item_id'") ;
    echo '1'; exit;
}

if ($opt == 'hide_cat'){
    $item_id = cmsCore::request('item_id', 'int', 0);
    $inDB->query("UPDATE cms_board_cats SET published = 0 WHERE id = '$item_id'") ;
    echo '1'; exit;
}

if ($opt == 'submit_cat' || $opt == 'update_cat'){

    if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $types = array('title'=>array('title', 'str', $_LANG['AD_UNTITLED_CAT']),
                   'description'=>array('description', 'str', ''),
                   'published'=>array('published', 'int', 0),
                   'showdate'=>array('showdate', 'int', 0),
                   'parent_id'=>array('parent_id', 'int', 0),
                   'public'=>array('public', 'int', 0),
                   'orderby'=>array('orderby', 'str', 'pubdate'),
                   'orderto'=>array('orderto', 'str', 'desc'),
                   'perpage'=>array('perpage', 'int', 10),
                   'is_photos'=>array('is_photos', 'int', 0),
                   'thumb1'=>array('thumb1', 'int', 0),
                   'thumb2'=>array('thumb2', 'int', 0),
                   'thumbsqr'=>array('thumbsqr', 'int', 0),
                   'uplimit'=>array('uplimit', 'int', 0),
                   'maxcols'=>array('maxcols', 'int', 0),
                   'orderform'=>array('orderform', 'int', 0),
                   'form_id'=>array('form_id', 'int', 0),
				   'obtypes'=>array('obtypes', 'str', ''),
                   'pagetitle'=>array('pagetitle', 'str', ''),
				   'meta_keys'=>array('meta_keys', 'str', ''),
				   'meta_desc'=>array('meta_desc', 'str', ''));

    $item = cmsCore::getArrayFromRequest($types);

    if($opt == 'submit_cat'){

        $item['icon'] = uploadCategoryIcon();
        $item['pubdate'] = date("Y-m-d H:i:s");

        $inDB->addNsCategory('cms_board_cats', $item);

    } else {

        $item_id = cmsCore::request('item_id', 'int', 0);
        $mod = $inDB->get_fields('cms_board_cats', "id = '$item_id'", '*');
        if(!$mod){ cmsCore::error404(); }
        $mod['icon'] = ($mod['icon'] == 'folder_grey.png') ? '' : $mod['icon'];
        $icon = uploadCategoryIcon($mod['icon']);
        $item['icon'] = $icon ? $icon : $mod['icon'];

        if($item['parent_id'] != $mod['parent_id']){
            cmsCore::nestedSetsInit('cms_board_cats')->MoveNode($item_id, $item['parent_id']);
        }

        $inDB->update('cms_board_cats', $item, $item_id);

    }

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

if($opt == 'delete_cat'){

    $item_id = cmsCore::request('item_id', 'int', 0);

    $sql = "SELECT id FROM cms_board_items WHERE category_id = '$item_id'";
    $result = $inDB->query($sql);
    if ($inDB->num_rows($result)){
        while($photo = $inDB->fetch_assoc($result)){
            $model->deleteRecord($photo['id']);
        }
    }
    $f_icon = $inDB->get_field('cms_board_cats', "id = '$item_id'", 'icon');
    $inDB->deleteNS('cms_board_cats', $item_id);
    if(file_exists(PATH.'/upload/board/cat_icons/'.$f_icon)){
        @chmod(PATH.'/upload/board/cat_icons/'.$f_icon, 0777);
        @unlink(PATH.'/upload/board/cat_icons/'.$f_icon);
    }

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

if ($opt == 'list_cats'){

    cpAddPathway($_LANG['AD_ALL_CAT']);

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'100', 'do'=>'opt', 'do_suffix'=>'_cat');

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_IF_CAT_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_cat&item_id=%id%');

    cpListTable('cms_board_cats', $fields, $actions, 'parent_id>0', 'NSLeft');

}

if ($opt == 'list_items'){

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['DATE'], 'field'=>'pubdate', 'width'=>'80', 'filter'=>'15', 'fdate'=>'%d/%m/%Y');
    $fields[] = array('title'=>$_LANG['TYPE'], 'field'=>'obtype', 'width'=>'80', 'filter'=>'15');
    $fields[] = array('title'=>$_LANG['AD_TITLE'], 'field'=>'title', 'width'=>'', 'filter'=>'15', 'link'=>'/board/edit%id%.html');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'50', 'do'=>'opt', 'do_suffix'=>'_item');
    $fields[] = array('title'=>$_LANG['AD_VIEWS'], 'field'=>'hits', 'width'=>'80');
    $fields[] = array('title'=>'IP', 'field'=>'ip', 'width'=>'80', 'prc'=>'long2ip');
    $fields[] = array('title'=>$_LANG['CAT_BOARD'], 'field'=>'category_id', 'width'=>'230', 'prc'=>'cpBoardCatById', 'filter'=>'1', 'filterlist'=>cpGetList('cms_board_cats'));

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'/board/edit%id%.html');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['DELETE_ADV'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_item&item_id=%id%');

    cpListTable('cms_board_items', $fields, $actions, '', 'pubdate DESC');

}

if ($opt == 'add_cat' || $opt == 'edit_cat'){
    cpAddPathway($_LANG['AD_ALL_CAT'], '?view=components&do=config&id='.$id.'&opt=list_cats');
    if ($opt=='add_cat'){
        cpAddPathway($_LANG['AD_NEW_CAT']);
    } else {

        $item_id = cmsCore::request('item_id', 'int', 0);

        $mod = $inDB->get_fields('cms_board_cats', "id = '$item_id'", '*');
        if(!$mod){ cmsCore::error404(); }

        echo '<h3>'.$_LANG['AD_CAT_EDIT'].'</h3>';
        cpAddPathway($_LANG['AD_CAT_EDIT'].' "'.$mod['title'].'"');

    }

    //DEFAULT VALUES
    if (!isset($mod['thumb1'])) { $mod['thumb1'] = 64; }
    if (!isset($mod['thumb2'])) { $mod['thumb2'] = 400; }
    if (!isset($mod['thumbsqr'])) { $mod['thumbsqr'] = 0; }
    if (!isset($mod['maxcols'])) { $mod['maxcols'] = 1; }
    if (!isset($mod['perpage'])) { $mod['perpage'] = '20'; }
    if (!isset($mod['uplimit'])) { $mod['uplimit'] = 10; }
    if (!isset($mod['public'])) { $mod['public'] = -1; }
    if (!isset($mod['published'])) { $mod['published'] = 1; }
    if (!isset($mod['showdate'])) { $mod['showdate'] = 1; }
    if (!isset($mod['orderform'])) { $mod['orderform'] = 1; }
    if (!isset($mod['orderby'])) { $mod['orderby'] = 'pubdate'; }
    if (!isset($mod['orderto'])) { $mod['orderto'] = 'desc'; }
?>

    <form id="addform" name="addform" enctype="multipart/form-data" method="post" action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="610" border="0" cellpadding="0" cellspacing="10" class="proptable">
          <tr>
            <td><strong><?php echo $_LANG['AD_CAT_NAME'];?> </strong></td>
            <td width="250"><input name="title" type="text" id="title" style="width:250px" value="<?php echo htmlspecialchars($mod['title']);?>"/></td>
          </tr>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_CAT_PARENT'];?></strong></td>
            <td valign="top"><select name="parent_id" id="parent_id" style="width:250px">
                <?php  //FIND BOARD ROOT
                    $rootid = $inDB->get_field('cms_board_cats', 'parent_id=0', 'id');
                ?>
                <option value="<?php echo $rootid?>" <?php if (@$mod['parent_id']==$rootid || !isset($mod['parent_id'])) { echo 'selected'; }?>><?php echo $_LANG['AD_CAT_ROOT'];?></option>
                <?php
                    if (isset($mod['parent_id'])){
                        echo $inCore->getListItemsNS('cms_board_cats', $mod['parent_id']);
                    } else {
                        echo $inCore->getListItemsNS('cms_board_cats');
                    }
                ?>
            </select></td>
          </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_CAT_ICON'];?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_INFO_3'];?></span></td>
                <td valign="middle"> <?php if (@$mod['icon']) { ?><img src="/upload/board/cat_icons/<?php echo @$mod['icon'];?>" border="0" /><?php } ?>
                    <input name="Filedata" type="file" style="width:215px; margin:0 0 0 5px; vertical-align:top" />
                </td>
            </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_ATTACH_FORM'];?></strong><br/>
                <span class="hinttext"><?php echo $_LANG['AD_FORM_FIELDS_EXIST'];?></span></td>
            <td>
                <select name="form_id" style="width:250px">
                    <option value="" <?php if (@!$mod['form_id']) { echo 'selected'; }?>><?php echo $_LANG['AD_DONT_ATTACH'];?></option>
                    <?php
                    $sql = "SELECT id, title FROM cms_forms";
                    $rs = $inDB->query($sql);

                    if ($inDB->num_rows($rs)){
                        while($f = $inDB->fetch_assoc($rs)){
                            if ($f['id']==$mod['form_id']) { $selected='selected="selected"'; } else { $selected = ''; }
                            echo '<option value="'.$f['id'].'" '.$selected.'>'.$f['title'].'</option>';
                        }
                    }

                    ?>
                </select>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_IF_PUBLIC_CAT'];?></strong></td>
            <td><label><input name="published" type="radio" value="1" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['YES']; ?></label>
              <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?></label></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_IF_DATA_VIEW']; ?> </strong></td>
            <td><label><input name="showdate" type="radio" value="1" checked="checked" <?php if (@$mod['showdate']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['YES']; ?></label>
              <label><input name="showdate" type="radio" value="0"  <?php if (@!$mod['showdate']) { echo 'checked="checked"'; } ?> />
                <?php echo $_LANG['NO']; ?></label></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_SORT_AD']; ?> </strong></td>
            <td><select name="orderby" id="orderby" style="width:250px">
              <option value="title" <?php if(@$mod['orderby']=='title') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_ALPHABET']; ?></option>
              <option value="pubdate" <?php if(@$mod['orderby']=='pubdate') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_CALENDAR']; ?></option>
              <option value="hits" <?php if(@$mod['orderby']=='hits') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_VIEWS']; ?></option>
              <option value="obtype" <?php if(@$mod['orderby']=='obtype') { echo 'selected'; } ?>><?php echo $_LANG['ORDERBY_TYPE']; ?></option>
              <option value="user_id" <?php if(@$mod['orderby']=='user_id') { echo 'selected'; } ?>><?php echo $_LANG['ORDERBY_AVTOR']; ?></option>
            </select>
              <select name="orderto" id="orderto" style="width:250px">
                <option value="desc" <?php if(@$mod['orderto']=='desc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_DECREMENT']; ?></option>
                <option value="asc" <?php if(@$mod['orderto']=='asc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_INCREMENT']; ?></option>
              </select></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_SORT_FORM']; ?> </strong></td>
            <td><label><input name="orderform" type="radio" value="1" checked="checked" <?php if (@$mod['orderform']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['SHOW']; ?> </label>
              <label><input name="orderform" type="radio" value="0"  <?php if (@!$mod['orderform']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['HIDE']; ?> </label></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_HOW_MANY_COLUMNS_VIEW']; ?></strong></td>
            <td><input name="maxcols" type="text" id="maxcols" size="5" value="<?php echo @$mod['maxcols'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_USERS_AD_ADD']; ?> </strong></td>
            <td><select name="public" id="select" style="width:250px">
                  <option value="0" <?php if(@$mod['public']=='0') { echo 'selected'; } ?>><?php echo $_LANG['AD_TABOO']; ?></option>
                  <option value="1" <?php if(@$mod['public']=='1') { echo 'selected'; } ?>><?php echo $_LANG['AD_PREMODERATION']; ?></option>
                  <option value="2" <?php if(@$mod['public']=='2') { echo 'selected'; } ?>><?php echo $_LANG['AD_WITHOUT_MODERATION']; ?></option>
                  <option value="-1" <?php if(@$mod['public']=='-1') { echo 'selected'; } ?>><?php echo $_LANG['AD_DEFAULT']; ?></option>
              </select></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_MAX_AD']; ?> </strong> <br />
            <span class="hinttext"><?php echo $_LANG['AD_ONE_USER_ONE_DAY']; ?></span></td>
            <td><input name="uplimit" type="text" id="uplimit" size="5" value="<?php echo @$mod['uplimit'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_HOW_MANY_AD_TO_PAGE']; ?> </strong></td>
            <td><input name="perpage" type="text" id="perpage" size="5" value="<?php echo @$mod['perpage'];?>"/> <?php echo $_LANG['AD_PIECES']; ?></td>
          </tr>
          <tr>
            <td><p><strong><?php echo $_LANG['AD_PHOTO_TO_AD']; ?> </strong></p></td>
            <td><label><input name="is_photos" type="radio" value="1" checked="checked" <?php if (@$mod['is_photos']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['YES']; ?> </label>
                <label><input name="is_photos" type="radio" value="0"  <?php if (@!$mod['is_photos']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?> </label></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_MINI_PHOTO_WIDTH']; ?> </strong><br/><span class="hinttext"><?php echo $_LANG['AD_IN_PIXELS']; ?></span></td>
            <td><table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td width="60" valign="middle"><input name="thumb1" type="text" id="thumb1" size="5" value="<?php echo @$mod['thumb1'];?>"/></td>
                <td width="100" align="center" valign="middle" style="background-color:#EBEBEB"><?php echo $_LANG['AD_SQUARE']; ?></td>
                <td width="115" align="center" valign="middle" style="background-color:#EBEBEB">
                    <label><input name="thumbsqr" type="radio" value="1" checked="checked" <?php if (@$mod['thumbsqr']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['YES']; ?> </label>
                    <label><input name="thumbsqr" type="radio" value="0"  <?php if (@!$mod['thumbsqr']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?></label></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_MIDI_PHOTO_WIDTH']; ?> </strong><br/><span class="hinttext"><?php echo $_LANG['AD_IN_PIXELS']; ?></span></td>
            <td><input name="thumb2" type="text" id="thumb2" size="5" value="<?php echo @$mod['thumb2'];?>"/></td>
          </tr>
          <tr>
            <td valign="top">
                <div><strong><?php echo $_LANG['AD_TYPES_AD']; ?></strong></div>
                <div class="hinttext"><?php echo $_LANG['AD_NEW_LINE_TYPES']; ?></div>
                <div class="hinttext"><?php echo $_LANG['AD_PARENT_CAT_DEFAULT']; ?></div>
            </td>
            <td valign="top">
                <textarea name="obtypes" style="width:220px" rows="6"><?php echo @$mod['obtypes'];?></textarea>
            </td>
          </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_PAGETITLE'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_PAGETITLE_HINT'] ?><br /></div>
                    <textarea name="pagetitle" rows="2" style="width:580px"><?php echo @$mod['pagetitle'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METAKEYS'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:580px"><?php echo @$mod['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METADESCR'] ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:580px"><?php echo @$mod['meta_desc'] ?></textarea>
                </td>
            </tr>
      </table>
    <table width="100%" border="0">
      <tr>
        <h3><?php echo $_LANG['AD_CAT_DESCRIPTION']; ?></h3>
        <textarea name="description" style="width:580px" rows="4"><?php echo @$mod['description']?></textarea>
      </tr>
    </table>
    <p>
      <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_cat') { echo 'value="submit_cat"'; } else { echo 'value="update_cat"'; } ?> />
      <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
      <input name="back2" type="button" id="back2" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
      <?php
        if ($opt=='edit_cat'){
         echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
        }
      ?>
    </p>
</form>
<?php } ?>