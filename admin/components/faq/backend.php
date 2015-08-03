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

$opt = cmsCore::request('opt', 'str', 'list_items');

$toolmenu = array();
if($opt != 'config'){
    $toolmenu[0]['icon'] = 'newstuff.gif';
    $toolmenu[0]['title'] = $_LANG['AD_NEW_QUESTION'];
    $toolmenu[0]['link'] = '?view=components&do=config&id='.$id.'&opt=add_item';

    $toolmenu[1]['icon'] = 'newfolder.gif';
    $toolmenu[1]['title'] = $_LANG['AD_CREATE_CATEGORY'];
    $toolmenu[1]['link'] = '?view=components&do=config&id='.$id.'&opt=add_cat';

    $toolmenu[2]['icon'] = 'liststuff.gif';
    $toolmenu[2]['title'] = $_LANG['AD_QUESTIONS'];
    $toolmenu[2]['link'] = '?view=components&do=config&id='.$id.'&opt=list_items';

    $toolmenu[3]['icon'] = 'folders.gif';
    $toolmenu[3]['title'] = $_LANG['AD_CAT_QUESTION'];
    $toolmenu[3]['link'] = '?view=components&do=config&id='.$id.'&opt=list_cats';

    if($opt == 'list_items'){
        $toolmenu[11]['icon'] = 'edit.gif';
        $toolmenu[11]['title'] = $_LANG['AD_EDIT_SELECTED'];
        $toolmenu[11]['link'] = "javascript:checkSel('?view=components&do=config&id=".$id."&opt=edit_item&multiple=1');";

        $toolmenu[12]['icon'] = 'show.gif';
        $toolmenu[12]['title'] = $_LANG['AD_ALLOW_SELECTED'];
        $toolmenu[12]['link'] = "javascript:checkSel('?view=components&do=config&id=".$id."&opt=show_item&multiple=1');";

        $toolmenu[13]['icon'] = 'hide.gif';
        $toolmenu[13]['title'] = $_LANG['AD_DISALLOW_SELECTED'];
        $toolmenu[13]['link'] = "javascript:checkSel('?view=components&do=config&id=".$id."&opt=hide_item&multiple=1');";

        $toolmenu[14]['icon'] = 'delete.gif';
        $toolmenu[14]['title'] = $_LANG['AD_DELETE_SELECTED'];
        $toolmenu[14]['link'] = "javascript:checkSel('?view=components&do=config&id=".$id."&opt=delete_item&multiple=1');";
    }
    $toolmenu[15]['icon'] = 'config.gif';
    $toolmenu[15]['title'] = $_LANG['AD_SETTINGS'];
    $toolmenu[15]['link'] = '?view=components&do=config&id='.$id.'&opt=config';
}
if($opt == 'config'){
    $toolmenu[16]['icon'] = 'save.gif';
    $toolmenu[16]['title'] = $_LANG['SAVE'];
    $toolmenu[16]['link'] = 'javascript:document.optform.submit();';
    $toolmenu[17]['icon'] = 'cancel.gif';
    $toolmenu[17]['title'] = $_LANG['CANCEL'];
    $toolmenu[17]['link'] = '?view=components&do=config&id='.$id;
}

cpToolMenu($toolmenu);

$cfg = $inCore->loadComponentConfig('faq');

if(!isset($cfg['guest_enabled'])) { $cfg['guest_enabled'] = 1; }
if(!isset($cfg['user_link'])) { $cfg['user_link'] = 1; }
if(!isset($cfg['publish'])) { $cfg['publish'] = 0; }
if(!isset($cfg['is_comment'])) { $cfg['is_comment'] = 1; }

$inCore->loadModel('faq');
$model = new cms_model_faq();

if ($opt=='saveconfig'){

    if (!cmsCore::validateForm()) { cmsCore::error404(); }

    $cfg = array();
    $cfg['guest_enabled'] = cmsCore::request('guest_enabled', 'int', 0);
    $cfg['user_link']     = cmsCore::request('user_link', 'int', 0);
    $cfg['publish']       = cmsCore::request('publish', 'int', 0);
    $cfg['is_comment']    = cmsCore::request('is_comment', 'int', 0);

    $inCore->saveComponentConfig('faq', $cfg);
    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

if ($opt=='config') {

    cpAddPathway($_LANG['AD_SETTINGS']);

?>

<form action="index.php?view=components&do=config&id=<?php echo (int)$_REQUEST['id'];?>&opt=config" method="post" name="optform" target="_self" id="form1">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_QUEST_FROM_UNREG']; ?>:</strong><br />
            </td>
            <td valign="top">
                <label><input name="guest_enabled" type="radio" value="1"  <?php if (@$cfg['guest_enabled']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="guest_enabled" type="radio" value="0"  <?php if (@!$cfg['guest_enabled']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_SHOW_LINK_QUEST_MEM']; ?>:</strong><br />
            </td>
            <td valign="top">
                <label><input name="user_link" type="radio" value="1"  <?php if (@$cfg['user_link']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="user_link" type="radio" value="0"  <?php if (@!$cfg['user_link']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_POST_QUEST_NO_MODERAT']; ?>:</strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_POST_QUEST_NO_MODERAT_HINT']; ?>.</span>
            </td>
            <td valign="top">
                <label><input name="publish" type="radio" value="1"  <?php if (@$cfg['publish']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="publish" type="radio" value="0"  <?php if (@!$cfg['publish']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo $_LANG['AD_ALLOW_COMMENTS']; ?>:</strong><br />
            </td>
            <td valign="top">
                <label><input name="is_comment" type="radio" value="1"  <?php if (@$cfg['is_comment']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                <label><input name="is_comment" type="radio" value="0"  <?php if (@!$cfg['is_comment']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
            </td>
        </tr>
    </table>
    <p>
        <input name="opt" type="hidden" value="saveconfig" />
        <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id; ?>';"/>
    </p>
</form>

<?php }

if ($opt == 'show_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbShow('cms_faq_quests', (int)$_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbShowList('cms_faq_quests', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'hide_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbHide('cms_faq_quests', (int)$_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbHideList('cms_faq_quests', $_REQUEST['item']);
        cmsCore::redirectBack();
    }
}

if ($opt == 'submit_item'){
    if (!cmsCore::validateForm()) { cmsCore::error404(); }
    $category_id = (int)$_REQUEST['category_id'];
    $published = (int)$_REQUEST['published'];
    $quest = $_REQUEST['quest'];
    $answer = $_REQUEST['answer'];
    $answeruser_id = $_SESSION['user']['id'];
    $user_id = (int)$_REQUEST['user_id'];

    $pubdate = $_REQUEST['pubdate'];
    $answerdate = $_REQUEST['answerdate'];

    $date = explode('.', $pubdate);
    $pubdate = $date[2] . '-' . $date[1] . '-' . $date[0];
    $date = explode('.', $answerdate);
    $answerdate = $date[2] . '-' . $date[1] . '-' . $date[0];

    $sql = "INSERT INTO cms_faq_quests (category_id, pubdate, published, quest, answer, user_id, answeruser_id, answerdate)
            VALUES ('$category_id', '$pubdate', $published, '$quest', '$answer', $user_id, $answeruser_id, '$answerdate')";

    $inDB->query($sql);

    cmsCore::redirect('?view=components&do=config&opt=list_items&id='.$id);

}

if ($opt == 'update_item'){
    if (!cmsCore::validateForm()) { cmsCore::error404(); }
    if (isset($_REQUEST['item_id'])) {
        $id = (int)$_REQUEST['item_id'];

        $category_id = (int)$_REQUEST['category_id'];
        $published = (int)$_REQUEST['published'];
        $quest = $_REQUEST['quest'];
        $answer = $_REQUEST['answer'];
        $answeruser_id = $_SESSION['user']['id'];
        $user_id = (int)$_REQUEST['user_id'];

        $pubdate = $_REQUEST['pubdate'];
        $answerdate = $_REQUEST['answerdate'];

        $date = explode('.', $pubdate);
        $pubdate = $date[2] . '-' . $date[1] . '-' . $date[0];
        $date = explode('.', $answerdate);
        $answerdate = $date[2] . '-' . $date[1] . '-' . $date[0];

        $sql = "UPDATE cms_faq_quests
                SET category_id = $category_id,
                    quest='$quest',
                    answer='$answer',
                    user_id='$user_id',
                    published=$published,
                    answeruser_id=$answeruser_id,
                    pubdate='$pubdate',
                    answerdate='$answerdate'
                WHERE id = $id
                LIMIT 1";
        $inDB->query($sql);
    }

    if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
        cmsCore::redirect('?view=components&do=config&opt=list_items&id='.(int)$_REQUEST['id']);
    } else {
        cmsCore::redirect('?view=components&do=config&opt=edit_item&id='.(int)$_REQUEST['id']);
    }
}

if($opt == 'delete_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ $model->deleteQuest((int)$_REQUEST['item_id']); }
    } else {
        $model->deleteQuests($_REQUEST['item']);
    }
    cmsCore::redirect('?view=components&do=config&opt=list_items&id='.$id);
}

if ($opt == 'show_cat'){
    if(isset($_REQUEST['item_id'])) {
        $id = (int)$_REQUEST['item_id'];
        $sql = "UPDATE cms_faq_cats SET published = 1 WHERE id = $id";
        $inDB->query($sql) ;
        echo '1'; exit;
    }
}

if ($opt == 'hide_cat'){
    if(isset($_REQUEST['item_id'])) {
        $id = (int)$_REQUEST['item_id'];
        $sql = "UPDATE cms_faq_cats SET published = 0 WHERE id = $id";
        $inDB->query($sql) ;
        echo '1'; exit;
    }
}

if ($opt == 'submit_cat'){
    if (!cmsCore::validateForm()) { cmsCore::error404(); }
    $parent_id = (int)$_REQUEST['parent_id'];
    $title = $_REQUEST['title'];
    $published = (int)$_REQUEST['published'];
    $description = $_REQUEST['description'];

    $sql = "INSERT INTO cms_faq_cats (parent_id, title, published, description)
            VALUES ($parent_id, '$title', $published, '$description')";
    $inDB->query($sql);
    cmsCore::redirect('?view=components&do=config&opt=list_cats&id='.(int)$_REQUEST['id']);
}

if($opt == 'delete_cat'){
    if(isset($_REQUEST['item_id'])) {
        $id = (int)$_REQUEST['item_id'];
        //DELETE ITEMS
        $sql = "DELETE FROM cms_faq_quests WHERE category_id = $id";
        $inDB->query($sql) ;
        //DELETE CATEGORY
        $sql = "DELETE FROM cms_faq_cats WHERE id = $id LIMIT 1";
        $inDB->query($sql) ;
    }
    cmsCore::redirect('?view=components&do=config&opt=list_cats&id='.(int)$_REQUEST['id']);
}

if ($opt == 'update_cat'){
    if (!cmsCore::validateForm()) { cmsCore::error404(); }
    if (isset($_REQUEST['item_id'])) {
        $id = (int)$_REQUEST['item_id'];

        $parent_id = (int)$_REQUEST['parent_id'];
        $title = $_REQUEST['title'];
        $published = (int)$_REQUEST['published'];
        $description = $_REQUEST['description'];

        $sql = "UPDATE cms_faq_cats
                SET title='$title',
                    parent_id = $parent_id,
                    description='$description',
                    published=$published
                WHERE id = $id
                LIMIT 1";
        $inDB->query($sql) ;

        cmsCore::redirect('?view=components&do=config&opt=list_cats&id='.(int)$_REQUEST['id']);

    }
}

if ($opt == 'list_cats'){
    cpAddPathway($_LANG['AD_CAT_QUESTION']);
    echo '<h3>'.$_LANG['AD_CAT_QUESTION'].'</h3>';

    //TABLE COLUMNS
    $fields = array();

    $fields[0]['title'] = 'id';			$fields[0]['field'] = 'id';			$fields[0]['width'] = '30';

    $fields[1]['title'] = $_LANG['TITLE'];	$fields[1]['field'] = 'title';		$fields[1]['width'] = '';
    $fields[1]['filter'] = 20;
    $fields[1]['link'] = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id=%id%';

    $fields[2]['title'] = $_LANG['AD_ALLOW_PARENT'];	$fields[2]['field'] = 'parent_id'; $fields[2]['width'] = '300';
    $fields[2]['prc'] = 'cpFaqCatById';  $fields[2]['filter'] = 1;  $fields[2]['filterlist'] = cpGetList('cms_faq_cats');

    $fields[3]['title'] = $_LANG['AD_SHOW'];		$fields[3]['field'] = 'published';	$fields[3]['width'] = '100';
    $fields[3]['do'] = 'opt'; $fields[3]['do_suffix'] = '_cat';

    //ACTIONS
    $actions = array();
    $actions[0]['title'] = $_LANG['EDIT'];
    $actions[0]['icon']  = 'edit.gif';
    $actions[0]['link']  = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id=%id%';

    $actions[1]['title'] = $_LANG['DELETE'];
    $actions[1]['icon']  = 'delete.gif';
    $actions[1]['confirm'] = $_LANG['AD_DEL_CATEGORY_QUESTION'];
    $actions[1]['link']  = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=delete_cat&item_id=%id%';

    //Print table
    cpListTable('cms_faq_cats', $fields, $actions);
}

if ($opt == 'list_items'){

    echo '<h3>'.$_LANG['AD_QUESTIONS'].'</h3>';

    //TABLE COLUMNS
    $fields = array();

    $fields[0]['title'] = 'id';			$fields[0]['field'] = 'id';			$fields[0]['width'] = '30';

    $fields[1]['title'] = $_LANG['AD_QUESTION'];		$fields[1]['field'] = 'quest';		$fields[1]['width'] = '';
    $fields[1]['link'] = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_item&item_id=%id%';
    $fields[1]['filter'] = 15;
    $fields[1]['maxlen'] = 80;

    $fields[2]['title'] = $_LANG['AD_CATEGORY'];	$fields[2]['field'] = 'category_id';$fields[2]['width'] = '300';
    $fields[2]['prc'] = 'cpFaqCatById';  $fields[2]['filter'] = 1;  $fields[2]['filterlist'] = cpGetList('cms_faq_cats');

    $fields[3]['title'] = $_LANG['AD_SHOW'];		$fields[3]['field'] = 'published';	$fields[3]['width'] = '100';
    $fields[3]['do'] = 'opt'; $fields[3]['do_suffix'] = '_item';

    //ACTIONS
    $actions = array();
    $actions[0]['title'] = $_LANG['EDIT'];
    $actions[0]['icon']  = 'edit.gif';
    $actions[0]['link']  = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_item&item_id=%id%';

    $actions[1]['title'] = $_LANG['DELETE'];
    $actions[1]['icon']  = 'delete.gif';
    $actions[1]['confirm'] = $_LANG['AD_REMOVE_QUESTION'];
    $actions[1]['link']  = '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=delete_item&item_id=%id%';

    //Print table
    cpListTable('cms_faq_quests', $fields, $actions, '', 'pubdate DESC');
}

if ($opt == 'add_item' || $opt == 'edit_item'){
    if ($opt=='add_item'){
        echo '<h3>'.$_LANG['AD_ADD_QUESTION'].'</h3>';
        cpAddPathway($_LANG['AD_ADD_QUESTION']);
    } else {
        if(isset($_REQUEST['multiple'])){
           if (isset($_REQUEST['item'])){
               $_SESSION['editlist'] = $_REQUEST['item'];
           } else {
               echo '<p class="error">'.$_LANG['AD_NO_SELECT_OBJECTS'].'</p>';
               return;
           }
        }

        $ostatok = '';

        if (isset($_SESSION['editlist'])){
           $id = array_shift($_SESSION['editlist']);
           if (sizeof($_SESSION['editlist'])==0) {
               unset($_SESSION['editlist']);
           } else {
                   $ostatok = '('.$_LANG['AD_NEXT_IN'].' '.sizeof($_SESSION['editlist']).')';
           }
        } else {
            $id = (int)$_REQUEST['item_id'];
        }


        $sql = "SELECT *, DATE_FORMAT(pubdate, '%d.%m.%Y') as pubdate, DATE_FORMAT(answerdate, '%d.%m.%Y') as answerdate
                FROM cms_faq_quests
                WHERE id = $id LIMIT 1";
        $result = $inDB->query($sql) ;
        if ($inDB->num_rows($result)){
           $mod = $inDB->fetch_assoc($result);
        }

        echo '<h3>'.$_LANG['AD_VIEW_QUESTION'].'</h3>';
        cpAddPathway($_LANG['AD_VIEW_QUESTION']);
    }

    ?>
<form action="index.php?view=components&amp;do=config&amp;id=<?php echo (int)$_REQUEST['id'];?>" method="post" enctype="multipart/form-data" name="addform" id="addform">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <table width="620" border="0" cellpadding="0" cellspacing="10" class="proptable">
      <tr>
        <td><strong><?php echo $_LANG['AD_CAT_QUESTION']; ?>:</strong></td>
        <td width="220"><select name="category_id" id="category_id" style="width:220px">
            <?php
                if (isset($mod['category_id'])) {
                    echo $inCore->getListItems('cms_faq_cats', $mod['category_id']);
                } else {
                    if (isset($_REQUEST['addto'])){
                        echo $inCore->getListItems('cms_faq_cats', $_REQUEST['addto']);
                    } else {
                        echo $inCore->getListItems('cms_faq_cats');
                    }
                }
            ?>
        </select></td>
      </tr>
      <tr>
        <td><strong><?php echo $_LANG['AD_ASKER']; ?>:</strong></td>
        <td><select name="user_id" id="user_id" style="width:220px">
            <option value="0" <?php if (!$mod['user_id']) { echo 'selected="selected"'; } ?>>-- <?php echo $_LANG['AD_ANONYMOUS']; ?> --</option>
          <?php
              if (isset($mod['user_id'])) {
                    echo $inCore->getListItems('cms_users', $mod['user_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
              } else {
                    echo $inCore->getListItems('cms_users', $inUser->id, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
              }
          ?>
        </select></td>
      </tr>
      <tr>
        <td><strong><?php echo $_LANG['AD_POST_QUESTION']; ?>?</strong></td>
        <td><label><input name="published" type="radio" value="1" checked="checked" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
          <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> />
            <?php echo $_LANG['NO']; ?></label></td>
      </tr>
      <tr>
        <td valign="top"><strong><?php echo $_LANG['AD_DATE_QUESTION']; ?>: </strong></td>
        <td valign="top"><input name="pubdate" type="text" style="width:190px" id="pubdate" <?php if(@!$mod['pubdate']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.$mod['pubdate'].'"'; } ?>/>

            <input type="hidden" name="oldpubdate" value="<?php echo @$mod['pubdate']?>"/> </td>
      </tr>
      <tr>
        <td valign="top"><strong><?php echo $_LANG['AD_DATE_REPLY']; ?>: </strong></td>
        <td valign="top"><input name="answerdate" style="width:190px" type="text" id="answerdate" <?php if(@!$mod['answerdate']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.$mod['answerdate'].'"'; } ?>/>

            <input type="hidden" name="oldanswerdate" value="<?php echo @$mod['answerdate']?>"/>
        </td>
      </tr>
</table>
    <table width="507" border="0" cellspacing="5" class="proptable">
      <tr>
        <td width="377">
        <div style="margin-bottom:10px"><strong><?php echo $_LANG['AD_TEXT_QUESTION']; ?>:</strong></div>
        <div>
            <textarea name="quest" rows="6" id="quest" style="border:solid 1px gray;width:605px"><?php echo @$mod['quest'];?></textarea>
        </div>			</td>
      </tr>
      <tr>
        <td>
        <div style="margin-bottom:10px"><strong><?php echo $_LANG['AD_ANSWER_QUESTION']; ?>:</strong></div>
        <div>
        <?php
            $inCore->insertEditor('answer', $mod['answer'], '300', '605');
        ?>
        </div>			</td>
      </tr>
    </table>
    <p>
      <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
      <input name="back2" type="button" id="back2" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $_REQUEST['id']; ?>';"/>
      <input name="opt" type="hidden" id="do" <?php if ($opt=='add_item') { echo 'value="submit_item"'; } else { echo 'value="update_item"'; } ?> />
      <?php
        if ($opt=='edit_item'){
             echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
        }
      ?>
    </p>
</form>
    <?php
}

if ($opt == 'add_cat' || $opt == 'edit_cat'){
    if ($opt=='add_cat'){
        echo '<h3>'.$_LANG['AD_CREATE_CATEGORY'].'</h3>';
        cpAddPathway($_LANG['AD_CREATE_CATEGORY']);
    } else {
        if(isset($_REQUEST['item_id'])){
            $id = (int)$_REQUEST['item_id'];
            $sql = "SELECT * FROM cms_faq_cats WHERE id = $id LIMIT 1";
            $result = $inDB->query($sql) ;
            if ($inDB->num_rows($result)){
               $mod = $inDB->fetch_assoc($result);
            }
        }

        echo '<h3>'.$_LANG['AD_CATEGORY'].': '.$mod['title'].'</h3>';
        cpAddPathway($_LANG['AD_CAT_QUESTION'], '?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=list_cats');
        cpAddPathway($mod['title']);
    }
        ?>
    <form id="addform" name="addform" method="post" action="index.php?view=components&amp;do=config&amp;id=<?php echo (int)$_REQUEST['id'];?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="620" border="0" cellpadding="0" cellspacing="10" class="proptable">
          <tr>
            <td><strong><?php echo $_LANG['AD_NAME_CATEGORY']; ?>: </strong></td>
            <td width="220"><input name="title" type="text" id="title" style="width:220px" value="<?php echo htmlspecialchars($mod['title']);?>"/></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_PARENT_CATEGORY']; ?>: </strong></td>
            <td><select name="parent_id" id="parent_id" style="width:220px">
                <option value="0" <?php if (!isset($mod['parent_id'])||@$mod['parent_id']==0){ echo 'selected'; } ?>>--</option>
            <?php if (isset($mod['parent_id']))
                  {
                        echo $inCore->getListItems('cms_faq_cats', $mod['parent_id']);
                  }	else {
                        echo $inCore->getListItems('cms_faq_cats');
                     }
            ?>
            </select></td>
          </tr>
          <tr>
            <td><strong><?php echo $_LANG['AD_POST_CATEGORY']; ?>?</strong></td>
            <td><label><input name="published" type="radio" value="1" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['YES']; ?> </label>
              <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?> </label></td>
          </tr>
        </table>
        <table width="100%" border="0">
          <tr>
            <?php
            if(!isset($mod['user']) || @$mod['user']==1){
                echo '<td width="52%" valign="top">';
                echo $_LANG['AD_DESCR_CATEGORY'].':<br/>';

                $inCore->insertEditor('description', $mod['description'], '260', '605');

                echo '</td>';
            }
            ?>
          </tr>
        </table>
        <p>
          <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
          <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $_REQUEST['id']; ?>';"/>
          <input name="opt" type="hidden" id="do" <?php if ($opt=='add_cat') { echo 'value="submit_cat"'; } else { echo 'value="update_cat"'; } ?> />
          <?php
            if ($opt=='edit_cat'){
             echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
            }
          ?>
        </p>
</form>
     <?php
}

?>