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

    cmsCore::loadClass('upload_photo');
    $inUploadPhoto = cmsUploadPhoto::getInstance();
    $inUploadPhoto->upload_dir    = PATH.'/upload/forum/';
    $inUploadPhoto->dir_medium    = 'cat_icons/';
    $inUploadPhoto->medium_size_w = 32;
    $inUploadPhoto->medium_size_h = 32;
    $inUploadPhoto->only_medium   = true;
    $inUploadPhoto->is_watermark  = false;
    $files = $inUploadPhoto->uploadPhoto($file);
    $icon = $files['filename'] ? $files['filename'] : $file;
    return $icon;

}

define('IS_BILLING', $inCore->isComponentInstalled('billing'));
if (IS_BILLING) { cmsCore::loadClass('billing'); }

$opt = cmsCore::request('opt', 'str', 'list_forums');

cmsCore::loadModel('forum');
$model = new cms_model_forum();

$cfg = $model->config;

if ($opt=='list_forums' || $opt=='list_cats' || $opt=='config'){

    $toolmenu[] = array('icon'=>'newfolder.gif', 'title'=>$_LANG['AD_CREATE_CATEGORY'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_cat');
    $toolmenu[] = array('icon'=>'newforum.gif', 'title'=>$_LANG['AD_FORUM_NEW'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_forum');
    $toolmenu[] = array('icon'=>'folders.gif', 'title'=>$_LANG['AD_FORUMS_CATS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_cats');
    $toolmenu[] = array('icon'=>'listforums.gif', 'title'=>$_LANG['AD_FORUMS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_forums');
    $toolmenu[] = array('icon'=>'ranks.gif', 'title'=>$_LANG['AD_RANKS_FORUM'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_ranks');
    if($opt=='list_forums'){

        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=edit_forum&multiple=1');");
        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=show_forum&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=hide_forum&multiple=1');");

    }
    $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['AD_SETTINGS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=config');

} else {

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

}

cpToolMenu($toolmenu);

if ($opt=='saveconfig'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg['is_rss']      = cmsCore::request('is_rss', 'int', 1);
    $cfg['pp_thread']   = cmsCore::request('pp_thread', 'int', 15);
    $cfg['pp_forum']    = cmsCore::request('pp_forum', 'int', 15);
    $cfg['showimg']     = cmsCore::request('showimg', 'int', 1);
    $cfg['img_on']      = cmsCore::request('img_on', 'int', 1);
    $cfg['img_max']     = cmsCore::request('img_max', 'int', 1);
    $cfg['fast_on']     = cmsCore::request('fast_on', 'int', 1);
    $cfg['fast_bb']     = cmsCore::request('fast_bb', 'int', 1);
    $cfg['fa_on']       = cmsCore::request('fa_on', 'int');
    $cfg['fa_max']      = cmsCore::request('fa_max', 'int');
    $cfg['fa_ext']      = cmsCore::request('fa_ext', 'str');

    while (mb_strpos($cfg['fa_ext'], 'htm') ||
           mb_strpos($cfg['fa_ext'], 'php') ||
           mb_strpos($cfg['fa_ext'], 'ht')) {
        $cfg['fa_ext']  = str_replace(array('htm','php','ht'), '', mb_strtolower($cfg['fa_ext']));
    }
    $cfg['fa_size']       = cmsCore::request('fa_size', 'int');
    $cfg['edit_minutes']  = cmsCore::request('edit_minutes', 'int');
    $cfg['watermark']     = cmsCore::request('watermark', 'int');
    $cfg['min_karma_add'] = cmsCore::request('min_karma_add', 'int', 0);

    $cfg['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
    $cfg['meta_desc'] = cmsCore::request('meta_desc', 'str', '');

    $is_access = cmsCore::request('is_access', 'int', '');
    if (!$is_access){
        $cfg['group_access'] = cmsCore::request('allow_group', 'array_int', '');
    } else { $cfg['group_access'] = ''; }

    $inCore->saveComponentConfig('forum', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'info');

    cmsCore::redirectBack();

}

if ($opt=='saveranks'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $ranks = cmsCore::request('rank', 'array_str', array());
    $cfg['modrank'] = cmsCore::request('modrank', 'int');

    foreach ($ranks as $key => $row) {
        $msg[$key]  = $row['msg'];
    }
    array_multisort($msg, SORT_ASC, $ranks); $num = 1; $cfg['ranks'] = array();
    foreach ($ranks as $key=>$row) {
        if(!$row['msg'] || !$row['title']){
            unset($ranks[$key]); continue;
        }
        $cfg['ranks'][$num] = $row; $num++;
    }

    $inCore->saveComponentConfig('forum', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'info');

    cmsCore::redirectBack();

}

if ($opt == 'show_forum'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbShow('cms_forums', $_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbShowList('cms_forums', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'hide_forum'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbHide('cms_forums', $_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbHideList('cms_forums', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'submit_forum'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $category_id = cmsCore::request('category_id', 'int');
    $title       = cmsCore::request('title', 'str', 'NO_TITLE');
    $published   = cmsCore::request('published', 'int');
    $parent_id   = cmsCore::request('parent_id', 'int');
    $description = cmsCore::request('description', 'str');
    $topic_cost  = cmsCore::request('topic_cost', 'int', 0);
    $moder_list  = cmsCore::request('moder_list', 'array_int', array());
    $moder_list  = $moder_list ? cmsCore::arrayToYaml($moder_list) : '';

    $is_access = cmsCore::request('is_access', 'int', '');
    if (!$is_access){
        $access_list = cmsCore::request('access_list', 'array_int');
        $group_access = $access_list ? cmsCore::arrayToYaml($access_list) : '';
    } else {
        $group_access = '';
    }

    $icon = uploadCategoryIcon();

    $inDB->addNsCategory('cms_forums', array('category_id' => $category_id,
        'parent_id'   => $parent_id,
        'title'       => $title,
        'description' => $description,
        'access_list' => $group_access,
        'moder_list'  => $moder_list,
        'published'   => $published,
        'icon'        => $icon,
        'pagetitle'   => cmsCore::request('pagetitle', 'str', ''),
        'meta_keys'   => cmsCore::request('meta_keys', 'str', ''),
        'meta_desc'   => cmsCore::request('meta_desc', 'str', ''),
        'topic_cost'  => $topic_cost)
    );

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'info');

    cmsCore::redirect('?view=components&do=config&opt=list_forums&id='.$id);

}

if ($opt == 'update_forum'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item_id     = cmsCore::request('item_id', 'int');
    $category_id = cmsCore::request('category_id', 'int');
    $title       = cmsCore::request('title', 'str', 'NO_TITLE');
    $pagetitle   = cmsCore::request('pagetitle', 'str', '');
    $meta_keys   = cmsCore::request('meta_keys', 'str', '');
    $meta_desc   = cmsCore::request('meta_desc', 'str', '');
    $published   = cmsCore::request('published', 'int');
    $parent_id   = cmsCore::request('parent_id', 'int');
    $description = cmsCore::request('description', 'str');
    $topic_cost  = cmsCore::request('topic_cost', 'int', 0);
    $moder_list  = cmsCore::request('moder_list', 'array_int', array());
    $moder_list  = $moder_list ? cmsCore::arrayToYaml($moder_list) : '';

    $is_access = cmsCore::request('is_access', 'int', '');
    if (!$is_access){
        $access_list = cmsCore::request('access_list', 'array_int');
        $group_access = $access_list ? cmsCore::arrayToYaml($access_list) : '';
        $inDB->query("UPDATE cms_forum_threads SET is_hidden = 1 WHERE forum_id = '$item_id'");
    } else {
        $group_access = '';
        $inDB->query("UPDATE cms_forum_threads SET is_hidden = 0 WHERE forum_id = '$item_id'");
    }

    $ns = $inCore->nestedSetsInit('cms_forums');
    $old = $inDB->get_fields('cms_forums', "id='$item_id'", '*');

    $icon = uploadCategoryIcon($old['icon']);

    if($parent_id != $old['parent_id']){
        $ns->MoveNode($item_id, $parent_id);
    }

    $sql = "UPDATE cms_forums
            SET category_id=$category_id,
                title='$title',
                description='$description',
                access_list='$group_access',
                moder_list='$moder_list',
                published=$published,
                icon='$icon',
                topic_cost='$topic_cost',
                pagetitle = '$pagetitle',
                meta_keys = '$meta_keys',
                meta_desc = '$meta_desc'
            WHERE id = '$item_id'
            LIMIT 1";

    $inDB->query($sql);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'info');

    if (empty($_SESSION['editlist'])){
        cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_forums');
    } else {
        cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=edit_forum');
    }
}

if($opt == 'delete_forum'){

    $forum = $model->getForum(cmsCore::request('item_id', 'int'));
    if(!$forum){ cmsCore::error404(); }

    $inDB->addJoin('INNER JOIN cms_forums f ON f.id = t.forum_id');
    $model->whereThisAndNestedForum($forum['NSLeft'], $forum['NSRight']);

    $threads = $model->getThreads();

    foreach ($threads as $thread) {
        $model->deleteThread($thread['id']);
    }

    $inDB->deleteNS('cms_forums', $forum['id']);
    if(file_exists(PATH.'/upload/forum/cat_icons/'.$forum['icon'])){
        @chmod(PATH.'/upload/forum/cat_icons/'.$forum['icon'], 0777);
        @unlink(PATH.'/upload/forum/cat_icons/'.$forum['icon']);
    }

    cmsCore::addSessionMessage($_LANG['AD_FORUM_IS_DELETE'], 'info');

    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_forums');

}


if ($opt == 'config') {

    require('../includes/jwtabs.php');
    $GLOBALS['cp_page_head'][] = jwHeader();
    cpAddPathway($_LANG['AD_SETTINGS']);

    ?>
    <form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="addform" target="_self" id="form1" style="margin-top:10px">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <?php ob_start(); ?>
       {tab=<?php echo $_LANG['AD_REVIEV']; ?>}
       <table width="609" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td colspan="2" valign="top" bgcolor="#EBEBEB"><h4><?php echo $_LANG['AD_FORUM_REVIEV']; ?></h4></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_TOPICS_PER_PAGE']; ?> </strong></td>
                <td valign="top"><input class="uispin" name="pp_forum" type="text" id="pp_forum" value="<?php echo $cfg['pp_forum'];?>" size="5" /></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_ICON_RSS']; ?> </strong></td>
                <td valign="top">
                    <label><input name="is_rss" type="radio" value="1" <?php if ($cfg['is_rss']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="is_rss" type="radio" value="0" <?php if (!$cfg['is_rss']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td colspan="2" valign="top" bgcolor="#EBEBEB"><h4><?php echo $_LANG['AD_TOPIC_REVIEV']; ?> </h4></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_POSTS_PER_PAGE']; ?> </strong></td>
                <td valign="top"><input class="uispin" name="pp_thread" type="text" id="pp_thread" value="<?php echo $cfg['pp_thread'];?>" size="5" /></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_SHOW_PICCTURES']; ?> </strong></td>
                <td valign="top">
                    <label><input name="showimg" type="radio" value="1" <?php if ($cfg['showimg']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="showimg" type="radio" value="0" <?php if (!$cfg['showimg']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_FORM_QUICK_RESPONCE']; ?> </strong></td>
                <td valign="top">
                    <label><input name="fast_on" type="radio" value="1" <?php if ($cfg['fast_on'] || !isset($cfg['fast_on'])) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="fast_on" type="radio" value="0" <?php if (!$cfg['fast_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top"><p><strong><?php echo $_LANG['AD_BBCODE_RENSPONCE']; ?></strong><strong>: </strong></p></td>
                <td valign="top">
                    <label><input name="fast_bb" type="radio" value="1" <?php if ($cfg['fast_bb'] || !isset($cfg['fast_bb'])) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="fast_bb" type="radio" value="0" <?php if (!$cfg['fast_bb']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
       </table>
       {tab=<?php echo $_LANG['AD_PICTURES']; ?>}
       <table width="609" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td colspan="2" valign="top" bgcolor="#EBEBEB"><h4><?php echo $_LANG['AD_PICTURES_MESS']; ?> </h4></td>
            </tr>
            <tr>
                <td valign="top" width="400px"><strong><?php echo $_LANG['AD_PICTURES_INSERT']; ?> </strong></td>
                <td valign="top">
                    <label><input name="img_on" type="radio" value="1" <?php if ($cfg['img_on']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="img_on" type="radio" value="0" <?php if (!$cfg['img_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_PICTURES_MAX']; ?></strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_PICTURES_NUMBER']; ?></span>
                </td>
                <td valign="top"><input class="uispin" name="img_max" type="text" id="img_max" value="<?php echo $cfg['img_max'];?>" size="5" /></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_PICTURES_WATERMARK']; ?> </strong></td>
                <td valign="top">
                    <label><input name="watermark" type="radio" value="1" <?php if ($cfg['watermark']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="watermark" type="radio" value="0" <?php if (!$cfg['watermark']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
       </table>
       {tab=<?php echo $_LANG['AD_INVESTMENTS']; ?>}
       <table width="609" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td colspan="2" valign="top" bgcolor="#EBEBEB"><h4><?php echo $_LANG['AD_FILES_ATTACHMENTS']; ?> </h4></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_FILES_ATTACH']; ?> </strong></td>
                <td valign="top">
                    <label><input name="fa_on" type="radio" value="1" <?php if ($cfg['fa_on']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="fa_on" type="radio" value="0" <?php if (!$cfg['fa_on']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_AVAILABLES_FOR_GROUPS']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_AVAILABLE_GROUPS']; ?></span>
                </td>
                <td valign="top">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                      <tr>
                          <td width="20">
                              <?php
                                $groups = cmsUser::getGroups();

                                $style  = 'disabled="disabled"';
                                $public = 'checked="checked"';

                                if ($cfg['group_access']){
                                    $public = '';
                                    $style  = '';
                                }

                              ?>
                              <input name="is_access" type="checkbox" id="is_access" onclick="checkGroupList()" value="1" <?php echo $public?> />
                          </td>
                          <td><label for="is_access"><strong><?php echo $_LANG['AD_ALL_GROUPS']; ?></strong></label></td>
                      </tr>
                  </table>
                  <div style="padding:5px">
                      <span class="hinttext">
                          <?php echo $_LANG['AD_ALL_GROUPS_HINT']; ?>
                      </span>
                  </div>

                  <div style="margin-top:10px;padding:5px;padding-right:0px;" id="grp">
                      <div>
                          <strong><?php echo $_LANG['AD_ALL_GROUPS_ONLY']; ?></strong><br />
                          <span class="hinttext">
                              <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL']; ?>
                          </span>
                      </div>
                      <div>
                          <?php
                              echo '<select style="width: 245px" name="allow_group[]" id="showin" size="6" multiple="multiple" '.$style.'>';

                                if ($groups){
                                    foreach($groups as $group){
                                        if($group['alias'] != 'guest' && !$group['is_admin']){
                                            echo '<option value="'.$group['id'].'"';
                                            if ($cfg['group_access']){
                                                if (inArray($cfg['group_access'], $group['id'])){
                                                    echo 'selected';
                                                }
                                            }

                                            echo '>';
                                            echo $group['title'].'</option>';
                                        }
                                    }

                                }

                              echo '</select>';
                          ?>
                      </div>
                  </div>
<script type="text/javascript">
function checkGroupList(){
if($('input#is_access').prop('checked')){
    $('select#showin').prop('disabled', true);
} else {
    $('select#showin').prop('disabled', false);
}

}
</script>
               </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_FILES_MAX']; ?></strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_FILES_MAX_HINT']; ?></span>
                </td>
                <td valign="top">
                    <input class="uispin" name="fa_max" type="text" id="fa_max" value="<?php echo $cfg['fa_max'];?>" size="5" /></td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo $_LANG['AD_ALLOWED_EXTENSIONS']; ?> </strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_ALLOWED_EXTENSIONS_HINT']; ?></span>
                </td>
                <td valign="top">
                    <textarea name="fa_ext" cols="35" rows="3" id="fa_ext"><?php echo $cfg['fa_ext'];?></textarea>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_MAX_FILE_SIZE']; ?> </strong></td>
                <td valign="top">
                    <input class="uispin" name="fa_size" type="text" id="fa_size" value="<?php echo $cfg['fa_size'];?>" size="10" /> <?php echo $_LANG['KILOBITE']; ?>
                </td>
            </tr>
        </table>
       {tab=SEO}
        <table width="610" border="0" cellspacing="5" class="proptable">
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
       {tab=<?php echo $_LANG['AD_LIMIT']; ?>}
       <table width="609" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_EDIT_DELIT']; ?></strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_EDIT_DELIT_TIME']; ?></span>
                </td>
                <td valign="top">
                    <select name="edit_minutes" style="width:200px">
                        <option value="0" <?php if(!$cfg['edit_minutes']) { echo 'selected'; } ?>><?php echo $_LANG['AD_NOT_PROHIBIT']; ?></option>
                        <option value="-1" <?php if($cfg['edit_minutes']==-1) { echo 'selected'; } ?>><?php echo $_LANG['AD_PROHIBIT']; ?></option>
                        <option value="1" <?php if($cfg['edit_minutes']==1) { echo 'selected'; } ?>>1 <?php echo $_LANG['MINUTU1']; ?></option>
                        <option value="5" <?php if($cfg['edit_minutes']==5) { echo 'selected'; } ?>>5 <?php echo $_LANG['MINUTE10']; ?></option>
                        <option value="10" <?php if($cfg['edit_minutes']==10) { echo 'selected'; } ?>>10 <?php echo $_LANG['MINUTE10']; ?></option>
                        <option value="15" <?php if($cfg['edit_minutes']==15) { echo 'selected'; } ?>>15 <?php echo $_LANG['MINUTE10']; ?></option>
                        <option value="30" <?php if($cfg['edit_minutes']==30) { echo 'selected'; } ?>>30 <?php echo $_LANG['MINUTE10']; ?></option>
                        <option value="60" <?php if($cfg['edit_minutes']==60) { echo 'selected'; } ?>>1 <?php echo $_LANG['HOUR1']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_FORUM_MIN_KARMA_ADD']; ?> </strong></td>
                <td valign="top"><input class="uispin" name="min_karma_add" type="text" value="<?php echo $cfg['min_karma_add'];?>" size="5" /></td>
            </tr>
       </table>
        {/tabs}
        <?php echo jwTabs(ob_get_clean()); ?>
        <p>
            <input name="opt" type="hidden" id="do" value="saveconfig" />
            <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id;?>';"/>
        </p>
    </form>
    <?php
}

if ($opt == 'list_ranks') {

    cpAddPathway($_LANG['AD_RANKS_FORUM']);

    ?>
        <form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="addform" target="_self" id="form1">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
            <table width="500" border="0" cellpadding="10" cellspacing="0" class="proptable" style="margin-bottom:2px">
                <tr>
                    <td valign="middle"><strong><?php echo $_LANG['AD_RANKS_FORUM_MODER']; ?> </strong></td>
                    <td width="120" valign="middle">
                        <label><input name="modrank" type="radio" value="1" <?php if ($cfg['modrank']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                        <label><input name="modrank" type="radio" value="0" <?php if (!$cfg['modrank']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?> </label>
                    </td>
                </tr>
            </table>
            <table width="500" border="0" cellpadding="10" cellspacing="0" class="proptable">
                <tr>
                    <td valign="middle" bgcolor="#EBEBEB"><strong><?php echo $_LANG['AD_RANKS']; ?></strong></td>
                    <td width="120" valign="middle" bgcolor="#EBEBEB"><strong><?php echo $_LANG['AD_NUMBER_POSTS']; ?> </strong></td>
                </tr>
                <?php for($r = 1; $r <= 10; $r++){ ?>
                <tr>
                    <td valign="top"><input type="text" name="rank[<?php echo $r?>][title]" style="width:250px;" value="<?php echo htmlspecialchars($cfg['ranks'][$r]['title']) ?>"></td>
                    <td valign="top"><input class="uispin" name="rank[<?php echo $r?>][msg]" type="text" id="" value="<?php echo htmlspecialchars($cfg['ranks'][$r]['msg']) ?>" size="10" /></td>
                </tr>
                <?php } ?>
            </table>
            <p>
                <input name="opt" type="hidden" id="do" value="saveranks" />
                <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
                <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>';"/>
            </p>
        </form>
    <?php
}


if ($opt == 'show_cat'){
    if(isset($_REQUEST['item_id'])) {
        $item_id = $_REQUEST['item_id'];
        $sql = "UPDATE cms_forum_cats SET published = 1 WHERE id = $item_id";
        $inDB->query($sql) ;
        echo '1'; exit;
    }
}

if ($opt == 'hide_cat'){
    if(isset($_REQUEST['item_id'])) {
        $item_id = $_REQUEST['item_id'];
        $sql = "UPDATE cms_forum_cats SET published = 0 WHERE id = $item_id";
        $inDB->query($sql) ;
        echo '1'; exit;
    }
}

if ($opt == 'submit_cat'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cat['title']     = cmsCore::request('title', 'str', 'NO_TITLE');
    $cat['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
    $cat['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
    $cat['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
    $cat['published'] = cmsCore::request('published', 'int');
    $cat['ordering']  = cmsCore::request('ordering', 'int');
    $cat['seolink']   = $model->getCatSeoLink($cat['title']);

    $inDB->insert('cms_forum_cats', $cat);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'info');

    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

if($opt == 'delete_cat'){

    $item_id = cmsCore::request('item_id', 'int');
    $inDB->query("UPDATE cms_forums SET category_id = 0, published = 0  WHERE category_id = '$item_id'");
    $inDB->query("DELETE FROM cms_forum_cats WHERE id = '$item_id'");

    cmsCore::addSessionMessage($_LANG['AD_CATEGORY_REMOVED'], 'info');

    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

if ($opt == 'update_cat'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item_id = cmsCore::request('item_id', 'int');

    $cat['title']     = cmsCore::request('title', 'str', 'NO_TITLE');
    $cat['pagetitle'] = cmsCore::request('pagetitle', 'str', '');
    $cat['meta_keys'] = cmsCore::request('meta_keys', 'str', '');
    $cat['meta_desc'] = cmsCore::request('meta_desc', 'str', '');
    $cat['published'] = cmsCore::request('published', 'int');
    $cat['ordering']  = cmsCore::request('ordering', 'int');
    $cat['seolink']   = $model->getCatSeoLink($cat['title'], $item_id);

    $inDB->update('cms_forum_cats', $cat, $item_id);
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'info');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

if ($opt == 'list_cats'){

    cpAddPathway($_LANG['AD_FORUMS_CATS']);
    echo '<h3>'.$_LANG['AD_FORUMS_CATS'].'</h3>';

    $fields[] = array('title'=>'ID', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'100', 'do'=>'opt', 'do_suffix'=>'_cat');

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_DELETE_CATEGORY'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_cat&item_id=%id%');

    cpListTable('cms_forum_cats', $fields, $actions);

}

if ($opt == 'list_forums'){

    echo '<h3>'.$_LANG['AD_FORUMS'].'</h3>';

    $fields[] = array('title'=>'ID', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_forum&item_id=%id%', 'filter'=>'15');
    $fields[] = array('title'=>$_LANG['AD_TOPICS'], 'field'=>'thread_count', 'width'=>'50');
    $fields[] = array('title'=>$_LANG['AD_FORUM_MESSAGES'], 'field'=>'post_count', 'width'=>'80');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'60', 'do'=>'opt', 'do_suffix'=>'_forum');
    $fields[] = array('title'=>$_LANG['AD_CATEGORY'], 'field'=>'category_id', 'width'=>'150', 'prc'=>'cpForumCatById', 'filter'=>'1', 'filterlist'=>cpGetList('cms_forum_cats'));

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_forum&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_FORUM_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_forum&item_id=%id%');

    cpListTable('cms_forums', $fields, $actions, 'parent_id>0', 'NSLeft');

}

if ($opt == 'add_cat' || $opt == 'edit_cat'){

    if ($opt=='add_cat'){
         echo '<h3>'.$_LANG['AD_CREATE_CATEGORY'].'</h3>';
         cpAddPathway($_LANG['AD_CREATE_CATEGORY']);
		 $mod['published'] = 1;
		 $mod['ordering']  = (int)$inDB->get_field('cms_forum_cats', '1=1 ORDER BY ordering DESC', 'ordering')+1;
    } else {

        $mod = $model->getForumCat(cmsCore::request('item_id', 'int', 0));
        if(!$mod){ cmsCore::error404(); }
        cpAddPathway($_LANG['AD_EDIT_CATEGORY']);
        echo '<h3>'.$_LANG['AD_EDIT_CATEGORY'].'</h3>';

   }
    ?>
    <form id="addform" name="addform" method="post" action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="600" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="211" valign="top"><strong><?php echo $_LANG['AD_CATEGORY_NAME']; ?></strong> <?php printLangPanel('forum_forum_cats', @$mod['id'], 'title'); ?></td>
                <td width="195" valign="top"><input name="title" type="text" id="title" size="30" value="<?php echo htmlspecialchars($mod['title']);?>"/></td>
                <td width="168" valign="top">&nbsp;</td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_CATEGORY_POST']; ?>?</strong></td>
                <td valign="top">
                    <label><input name="published" type="radio" value="1" <?php if ($mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="published" type="radio" value="0"  <?php if (!$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
                <td valign="top">&nbsp;</td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_SERIAL_NUMBER']; ?></strong></td>
                <td valign="top"><input name="ordering" type="text" id="ordering" value="<?php echo $mod['ordering'];?>" size="5" /></td>
                <td valign="top">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_PAGETITLE'] ?></strong> <?php printLangPanel('forum_forum_cats', @$mod['id'], 'pagetitle'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_PAGETITLE_HINT'] ?><br /></div>
                    <textarea name="pagetitle" rows="2" style="width:580px"><?php echo @$mod['pagetitle'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METAKEYS'] ?></strong> <?php printLangPanel('forum_forum_cats', @$mod['id'], 'meta_keys'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:580px"><?php echo @$mod['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METADESCR'] ?></strong> <?php printLangPanel('forum_forum_cats', @$mod['id'], 'meta_desc'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:580px"><?php echo @$mod['meta_desc'] ?></textarea>
                </td>
            </tr>
        </table>
        <p>
            <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_cat') { echo 'value="submit_cat"'; } else { echo 'value="update_cat"'; } ?> />
            <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back2" type="button" id="back2" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id; ?>';"/>
            <?php
                if ($opt=='edit_cat'){
                    echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
                }
            ?>
        </p>
    </form>
    <?php
}

if ($opt == 'add_forum' || $opt == 'edit_forum'){

    if ($opt=='add_forum'){
         echo '<h3>'.$_LANG['AD_FORUM_NEW'].'</h3>';
         cpAddPathway($_LANG['AD_FORUM_NEW']);
         $mod['published'] = 1;
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

        $mod = $model->getForum($item_id);
        if(!$mod){ cmsCore::error404(); }

        echo '<h3>'.$mod['title'].' '.$ostatok.'</h3>';
        cpAddPathway($mod['title']);

	}
    ?>
    <form action="index.php?view=components&do=config&id=<?php echo $id;?>" method="post" name="addform" id="addform" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="614" border="0" cellspacing="10" class="proptable">
            <tr>
                <td width=""><strong><?php echo $_LANG['AD_FORUM_TITLE']; ?>:</strong> <?php printLangPanel('forum_forums', @$mod['id'], 'title'); ?></td>
                <td width="450"><input name="title" type="text" id="title" size="30" value="<?php echo htmlspecialchars($mod['title']);?>" style="width:254px"/></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_FORUM_DESCR']; ?>:</strong> <?php printLangPanel('forum_forums', @$mod['id'], 'description'); ?></td>
                <td><textarea name="description" cols="35" rows="2" id="description" style="width:250px"><?php echo $mod['description']?></textarea></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FORUM_POST']; ?>?</strong></td>
                <td>
                    <label><input name="published" type="radio" value="1" checked="checked" <?php if ($mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="published" type="radio" value="0"  <?php if (!$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FORUM_PARENTS']; ?>:</strong></td>
                <td>
                    <?php $rootid = $inDB->get_field('cms_forums', 'parent_id=0', 'id'); ?>
                    <select name="parent_id" id="parent_id" style="width:260px">
                            <option value="<?php echo $rootid?>" <?php if ($mod['parent_id']==$rootid || !isset($mod['parent_id'])) { echo 'selected'; }?>><?php echo $_LANG['AD_FORUM_SQUARE']; ?> </option>
                    <?php
                        if (isset($mod['parent_id'])){
                           echo $inCore->getListItemsNS('cms_forums', $mod['parent_id']);
                        } else {
                           echo $inCore->getListItemsNS('cms_forums');
                        }
                    ?>
                    </select>
               </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_CATEGORY']; ?>:</strong></td>
                <td>
                    <select name="category_id" id="category_id" style="width:260px">
                    <?php
                        if (isset($mod['category_id'])) {
                            echo $inCore->getListItems('cms_forum_cats', $mod['category_id'], 'ordering');
                        } else {
                            if (isset($_REQUEST['addto'])){
                                echo $inCore->getListItems('cms_forum_cats', $_REQUEST['addto'], 'ordering');
                            } else {
                               echo $inCore->getListItems('cms_forum_cats', 0, 'ordering');
                            }
                        }
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_SHOW_GROUP']; ?>:</strong><br />
                  <span class="hinttext">
                      <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL']; ?>.
                  </span>
                </td>
                <td>
                <?php
                $groups = cmsUser::getGroups();

                $style  = 'disabled="disabled"';
                $public = 'checked="checked"';

                if ($mod['access_list']){
                    $public = '';
                    $style  = '';

                    $access_list = $inCore->yamlToArray($mod['access_list']);

                }

                echo '<select style="width: 260px" name="access_list[]" id="showin" size="6" multiple="multiple" '.$style.'>';

                if ($groups){
                    foreach($groups as $group){
                        if(!$group['is_admin']){
                            echo '<option value="'.$group['id'].'"';
                            if ($access_list){
                                if (inArray($access_list, $group['id'])){
                                    echo 'selected';
                                }
                            }

                            echo '>';
                            echo $group['title'].'</option>';
                        }
                    }

                }

                echo '</select>';
                ?>

                <label><input name="is_access" type="checkbox" id="is_access" onclick="checkAccesList()" value="1" <?php echo $public?> /> <strong><?php echo $_LANG['AD_ALL_GROUPS']; ?></strong></label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FORUM_MODERATORS']; ?>:</strong><br />
                  <span class="hinttext">
                      <?php echo $_LANG['AD_FORUM_HINT']; ?>.
                  </span>
                </td>
                <td>
                <?php

                if ($mod['moder_list']){
                    $public = '';
                    $style  = '';

                    $moder_list = $inCore->yamlToArray($mod['moder_list']);
                    if($moder_list){
                        $moder_list = cmsUser::getAuthorsList($moder_list, $moder_list);
                    }

                }

                echo '<select style="width: 260px" name="users_list" id="users_list">';
                echo cmsUser::getUsersList();
                echo '</select> <a class="ajaxlink" href="javascript:" onclick="addModer()">'.$_LANG['AD_ADD_SELECTED'].'</a><br>';
                ?>

                <select name="moder_list[]" size="8" multiple id="moder_list" style="width:260px; margin: 5px 0 0 0;">
                    <?php
                    if($moder_list){
                        echo $moder_list;
                    }
                    ?>
                </select>  <a class="ajaxlink" href="javascript:" onclick="deleteModer()"><?php echo $_LANG['AD_DELETE_SELECTED']; ?></a>

                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FORUM_ICON']; ?>:</strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_FORUM_ICON_HINT']; ?></span></td>
                <td valign="middle"> <?php if ($mod['icon']) { ?><img src="/upload/forum/cat_icons/<?php echo $mod['icon'];?>" border="0" /><?php } ?>
                    <input name="Filedata" type="file" style="width:215px; margin:0 0 0 5px; vertical-align:top" />
                </td>
            </tr>
            <tr>
                <td width="236">
                    <strong><?php echo $_LANG['AD_COST_CREATING']; ?>:</strong><br/>
                    <span class="hinttext">0 &mdash; <?php echo $_LANG['AD_COST_FREE']; ?></span>
                </td>
                <td width="259">
                    <?php if (IS_BILLING) { ?>
                        <input name="topic_cost" type="text" id="title" value="<?php echo $mod['topic_cost'];?>" style="width:60px"/> <?php echo $_LANG['BILLING_POINT10']; ?>
                    <?php } else { ?>
                        <?php echo $_LANG['AD_REGUIRED']; ?> &laquo;<a href="http://www.instantcms.ru/billing/about.html"><?php echo $_LANG['AD_BILLING_USERS']; ?></a>&raquo;
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_PAGETITLE'] ?></strong> <?php printLangPanel('forum_forums', @$mod['id'], 'pagetitle'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_PAGETITLE_HINT'] ?><br /></div>
                    <textarea name="pagetitle" rows="2" style="width:580px"><?php echo @$mod['pagetitle'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METAKEYS'] ?></strong> <?php printLangPanel('forum_forums', @$mod['id'], 'meta_keys'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:580px"><?php echo @$mod['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['SEO_METADESCR'] ?></strong> <?php printLangPanel('forum_forums', @$mod['id'], 'meta_desc'); ?><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:580px"><?php echo @$mod['meta_desc'] ?></textarea>
                </td>
            </tr>
    </table>
    <p>
        <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id;?>';"/>
        <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_forum') { echo 'value="submit_forum"'; } else { echo 'value="update_forum"'; } ?> />
        <?php
        if ($opt=='edit_forum'){
            echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
        }
        ?>
    </p>
    </form>
<script type="text/javascript">
$().ready(function() {
    $("#addform").submit(function() {
          $('#moder_list').each(function(){
              $('#moder_list option').prop("selected", true);
          });
    });
});
function deleteModer(){
    $('#moder_list option:selected').each(function () {
        $(this).remove();
    });
}
function addModer(){
    $('#users_list option:selected').each(function () {
        $(this).appendTo('#moder_list');
    });
}
function checkAccesList(){
if(document.addform.is_access.checked){
    $('select#showin').prop('disabled', true);
} else {
    $('select#showin').prop('disabled', false);
}

}
</script>
 <?php }