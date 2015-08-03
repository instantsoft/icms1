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

	$toolmenu = array();

	if($opt=='list'){

        $toolmenu[] = array('icon'=>'newaward.gif', 'title'=>$_LANG['AD_NEW_AWARD'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add');
        $toolmenu[] = array('icon'=>'listawards.gif', 'title'=>$_LANG['AD_ALL_AWARDS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=edit&multiple=1');");
        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=show_award&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=hide_award&multiple=1');");

	} else {

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

	}

	cpToolMenu($toolmenu);

	if ($opt == 'show_award'){
		if (!isset($_REQUEST['item'])){
			if (isset($_REQUEST['item_id'])){ dbShow('cms_user_autoawards', $_REQUEST['item_id']);  }
			echo '1'; exit;
		} else {
			dbShowList('cms_user_autoawards', $_REQUEST['item']);
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
			cmsCore::redirectBack();
		}
	}

	if ($opt == 'hide_award'){
		if (!isset($_REQUEST['item'])){
			if (isset($_REQUEST['item_id'])){ dbHide('cms_user_autoawards', $_REQUEST['item_id']);  }
			echo '1'; exit;
		} else {
			dbHideList('cms_user_autoawards', $_REQUEST['item']);
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
			cmsCore::redirectBack();
		}
	}

	if ($opt == 'submit' || $opt == 'update'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

        $title       = cmsCore::request('title', 'str', $_LANG['AD_AWARD']);
		$description = cmsCore::request('description', 'str', '');
		$published   = cmsCore::request('published', 'int', 0);
		$imageurl    = preg_replace('/[^a-zA-Z0-9_\.\-]/iu', '', cmsCore::request('imageurl', 'str', ''));
		$p_comment   = cmsCore::request('p_comment', 'int', 0);
		$p_forum     = cmsCore::request('p_forum', 'int', 0);
		$p_content   = cmsCore::request('p_content', 'int', 0);
		$p_blog      = cmsCore::request('p_blog', 'int', 0);
		$p_karma     = cmsCore::request('p_karma', 'int', 0);
		$p_photo     = cmsCore::request('p_photo', 'int', 0);
		$p_privphoto = cmsCore::request('p_privphoto', 'int', 0);

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');

        if($opt == 'submit'){

            $sql = "INSERT INTO cms_user_autoawards (title, description, imageurl, p_comment, p_blog, p_forum, p_photo, p_privphoto, p_content, p_karma, published)
                    VALUES ('$title', '$description', '$imageurl', $p_comment, $p_blog, $p_forum, $p_photo, $p_privphoto, $p_content, $p_karma, $published)";
            $inDB->query($sql);

            cmsCore::redirect('?view=components&do=config&opt=list&id='.$id);

        } else {

            $item_id = cmsCore::request('item_id', 'int', 0);

            $sql = "UPDATE cms_user_autoawards
                    SET title='$title',
                        description='$description',
                        imageurl='$imageurl',
                        p_comment=$p_comment,
                        p_blog=$p_blog,
                        p_forum=$p_forum,
                        p_photo=$p_photo,
                        p_privphoto=$p_privphoto,
                        p_content=$p_content,
                        p_karma=$p_karma,
                        published=$published
                    WHERE id = '$item_id'";

            $inDB->query($sql);

            if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
                cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list');
            } else {
                cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=edit');
            }

        }

	}

	if($opt == 'delete'){
        $item_id = cmsCore::request('item_id', 'int', 0);
        $sql = "DELETE FROM cms_user_autoawards WHERE id = $item_id";
        $inDB->query($sql);
        $sql = "DELETE FROM cms_user_awards WHERE award_id = $item_id";
        $inDB->query($sql);
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
        cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list');
	}

	if ($opt == 'list'){

		$fields = array();

		$fields[0]['title'] = 'id'; $fields[0]['field'] = 'id'; $fields[0]['width'] = '30';

		$fields[2]['title'] = $_LANG['TITLE'];	$fields[2]['field'] = 'title'; $fields[2]['width'] = '250';
		$fields[2]['filter'] = 15;
		$fields[2]['link'] = '?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%';

		$fields[3]['title'] = $_LANG['DESCRIPTION']; $fields[3]['field'] = 'description'; $fields[3]['width'] = '';
		$fields[3]['filter'] = 15;

		$fields[4]['title'] = $_LANG['AD_GIVING']; $fields[4]['field'] = 'published'; $fields[4]['width'] = '100';
		$fields[4]['do'] = 'opt';  $fields[4]['do_suffix'] = '_award';

		$actions = array();
		$actions[0]['title'] = $_LANG['EDIT'];
		$actions[0]['icon']  = 'edit.gif';
		$actions[0]['link']  = '?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%';

		$actions[1]['title'] = $_LANG['DELETE'];
		$actions[1]['icon']  = 'delete.gif';
		$actions[1]['confirm'] = $_LANG['AD_CONFIRM_DELETING'];
		$actions[1]['link']  = '?view=components&do=config&id='.$id.'&opt=delete&item_id=%id%';

		cpListTable('cms_user_autoawards', $fields, $actions);

	}

	if ($opt == 'add' || $opt == 'edit'){

		if ($opt=='add'){
            cpAddPathway($_LANG['AD_NEW_AWARD']);
            echo '<h3>'.$_LANG['AD_NEW_AWARD'].'</h3>';
            $mod['p_comment']   = 0;
            $mod['p_content']   = 0;
            $mod['p_blog']      = 0;
            $mod['p_karma']     = 0;
            $mod['p_forum']     = 0;
            $mod['p_photo']     = 0;
            $mod['p_privphoto'] = 0;
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

            $mod = $inDB->get_fields('cms_user_autoawards', "id = '$item_id'", '*');
            if(!$mod){ cmsCore::error404(); }

            echo '<h3>'.$mod['title'].' '.$ostatok.'</h3>';
            cpAddPathway($mod['title']);

        }

		?>
		<style type="text/css">
			#p_input{
				border:solid 1px silver;
				text-align:center;
				margin-left:4px;
				margin-right:6px;
			}
			#p_input:hover{
				border:solid 1px gray;
				background-color:#EBEBEB;
				text-align:center;
				margin-left:4px;
				margin-right:6px;
			}
		</style>
		<form action="index.php?view=components&do=config&id=<?php echo $id;?>" method="post" enctype="multipart/form-data" name="addform" id="addform">
            <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
            <table width="625" border="0" cellspacing="5" class="proptable">
              <tr>
                <td width="298" valign="top"><strong><?php echo $_LANG['AD_AWARD_TITLE']; ?></strong><br /></td>
                <td width="308" valign="top"><input name="title" type="text" id="title" size="45" value="<?php echo @$mod['title'];?>"/></td>
              </tr>
              <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_AWARD_DESCRIPTION']; ?></strong><br /></td>
                <td valign="top"><input name="description" type="text" id="description" size="45" value="<?php echo @$mod['description'];?>"/></td>
              </tr>
              <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_ENABLE_AWARD_CONFIRM']; ?></strong><br />
                    <span class="hinttext"><?php echo $_LANG['AD_DISALLOW_TEXT']; ?></span>					</td>
                <td valign="top"><label><input name="published" type="radio" value="1" checked="checked" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                  <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /><?php echo $_LANG['NO']; ?></label></td>
              </tr>
              <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_AWARD_IMAGE']; ?></strong><br />
                <span class="hinttext"><?php echo $_LANG['AD_AWARD_FOLDER']; ?></span></td>
                <td valign="top"><?php
                    $awards_img = cmsUser::getAwardsImages();
                    foreach($awards_img as $img){
                ?>

                    <div style="float:left;margin:4px">
                    <table border="0" cellspacing="0" cellpadding="4"><tr>
                         <?php if ($mod['imageurl'] != $img){ ?>
                            <td align="center" valign="middle"><label><img src="/images/users/awards/<?php echo $img; ?>" /><br/><input type="radio" name="imageurl" value="<?php echo $img; ?>"/></label></td>
                         <?php } else {  ?>
                            <td align="center" valign="middle"><label><img src="/images/users/awards/<?php echo $img; ?>" /><br/><input type="radio" name="imageurl" value="<?php echo $img; ?>" checked="checked"/></label></td>
                         <?php } ?>
                    </tr></table></div>
                <?php } ?>
                </td>
              </tr>
              <tr>
                <td valign="top"><p><strong><?php echo $_LANG['AD_AWARD_CONDITION_TITLE']; ?></strong> <br>
                  <span class="hinttext"><?php echo $_LANG['AD_AWARD']; ?> <?php echo $_LANG['AD_AWARD_CONDITION_TEXT']; ?></span></p></td>
                <td valign="top"><table width="100%" border="0" cellspacing="2" cellpadding="0">
                  <tr>
                    <td width="20"><img src="/admin/components/autoawards/images/p_comment.gif" width="16" height="16" /></td>
                    <td width="14%">
                      <input name="p_comment" type="text" id="p_input" size="5" value="<?php echo @$mod['p_comment'];?>">
                    </td>
                    <td width="86%"><?php echo $_LANG['COMMENT10']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_forum.gif" width="16" height="16" /></td>
                    <td><input name="p_forum" type="text" id="p_input" size="5" value="<?php echo @$mod['p_forum'];?>" /></td>
                    <td><?php echo $_LANG['AD_FORUM_MESSAGES']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_content.gif" width="16" height="16" /></td>
                    <td><input name="p_content" type="text" id="p_input" size="5" value="<?php echo @$mod['p_content'];?>"></td>
                    <td><?php echo $_LANG['AD_PUBLISHED_ARTICLES']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_blog.gif" width="16" height="16" /></td>
                    <td><input name="p_blog" type="text" id="p_input" size="5" value="<?php echo @$mod['p_blog'];?>"></td>
                    <td><?php echo $_LANG['AD_BLOG_POSTS']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_karma.gif" width="16" height="16" /></td>
                    <td><input name="p_karma" type="text" id="p_input" size="5" value="<?php echo @$mod['p_karma'];?>"></td>
                    <td><?php echo $_LANG['AD_KARMA_POINTS']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_photo.gif" width="16" height="16" /></td>
                    <td><input name="p_photo" type="text" id="p_input" size="5" value="<?php echo @$mod['p_photo'];?>" /></td>
                    <td><?php echo $_LANG['AD_PUBLIC_PHOTOS']; ?></td>
                  </tr>
                  <tr>
                    <td><img src="/admin/components/autoawards/images/p_privphoto.gif" width="16" height="16" /></td>
                    <td><input name="p_privphoto" type="text" id="p_input" size="5" value="<?php echo @$mod['p_privphoto'];?>" /></td>
                    <td><?php echo $_LANG['AD_PRIVATE_PHOTOS']; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table>
            <p>
              <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
              <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components&do=config&id=<?php echo $id;?>';" />
              <input name="opt" type="hidden" id="opt" <?php if ($opt=='add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
              <?php
                if ($opt=='edit'){
                 echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
                }
              ?>
            </p>
</form>
	 <?php
	}

?>