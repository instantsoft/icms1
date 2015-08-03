<?php
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

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

 function getCountUsers($id) {

     $inDB = cmsDatabase::getInstance();
     $count = $inDB->rows_count('cms_users', "group_id = '$id'");
     return '<a href="?view=users&filter[group_id]='.$id.'">'.$count.'</a>';
}

function applet_usergroups(){

	$inDB = cmsDatabase::getInstance();

	global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/users', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_USERS_GROUP'];
 	cpAddPathway($_LANG['AD_USERS'], 'index.php?view=users');
 	cpAddPathway($_LANG['AD_USERS_GROUP'], 'index.php?view=usergroups');

    $do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);

    cmsCore::loadModel('users');
    $model = new cms_model_users();

	if ($do == 'list'){

        $toolmenu[] = array('icon'=>'usergroupadd.gif', 'title'=>$_LANG['AD_CREATE_GROUP'], 'link'=>'?view=usergroups&do=add');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=usergroups&do=edit&multiple=1');");
        $toolmenu[] = array('icon'=>'delete.gif', 'title'=>$_LANG['AD_DELETE_SELECTED'], 'link'=>"javascript:if(confirm('{$_LANG['AD_REMOVE_GROUP']}')) { checkSel('?view=users&do=delete&multiple=1'); }");

		cpToolMenu($toolmenu);

		$fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
		$fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=usergroups&do=edit&id=%id%', 'filter'=>'12');
        $fields[] = array('title'=>$_LANG['AD_FROM_USERS'], 'field'=>'id', 'width'=>'100', 'prc'=>'getCountUsers');
        $fields[] = array('title'=>$_LANG['AD_IF_ADMIN'], 'field'=>'is_admin', 'width'=>'110', 'prc'=>'cpYesNo');
		$fields[] = array('title'=>$_LANG['AD_ALIAS'], 'field'=>'alias', 'width'=>'75', 'filter'=>'12');

        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=usergroups&do=edit&id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_REMOVE_GROUP'], 'link'=>'?view=usergroups&do=delete&id=%id%');

		cpListTable('cms_user_groups', $fields, $actions);

	}

	if ($do == 'delete'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){
				$model->deleteGroup($id);
			}
		} else {
			$model->deleteGroups(cmsCore::request('item', 'array_int', array()));
		}
		cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
		cmsCore::redirect('index.php?view=usergroups');
	}

    if ($do == 'submit' || $do == 'update'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $types = array('title'=>array('title', 'str', ''),
                       'alias'=>array('alias', 'str', ''),
                       'is_admin'=>array('is_admin', 'int', 0),
                       'access'=>array('access', 'array_str', array(),
                                        create_function('$a_list', 'return implode(\',\', $a_list);')));

        $items = cmsCore::getArrayFromRequest($types);

        if($do == 'submit') {

            $inDB->insert('cms_user_groups', $items);
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
            cmsCore::redirect('index.php?view=usergroups');

        } else {

            $inDB->update('cms_user_groups', $items, $id);
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
            if (empty($_SESSION['editlist'])){
                cmsCore::redirect('index.php?view=usergroups');
            } else {
                cmsCore::redirect('index.php?view=usergroups&do=edit');
            }

        }

    }

    if ($do == 'add' || $do == 'edit'){

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'javascript:history.go(-1);');

        cpToolMenu($toolmenu);

        if ($do=='add'){
            cpAddPathway($_LANG['AD_CREATE_GROUP']);
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
            } else { $item_id = cmsCore::request('id', 'int', 0); }

            $mod = $inDB->get_fields('cms_user_groups', "id = '$item_id'", '*');
            if(!$mod){ cmsCore::error404(); }

            echo '<h3>'.$_LANG['AD_EDIT_GROUP'].' '.$ostatok.'</h3>';

            cpAddPathway($_LANG['AD_EDIT_GROUP'].' '.$mod['title']);
        }

        if(isset($mod['access'])){
            $mod['access'] = str_replace(', ', ',', $mod['access']);
            $mod['access'] = explode(',', $mod['access']);
        }

	?>
	<form id="addform" name="addform" method="post" action="index.php?view=usergroups">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
		<table width="660" border="0" cellspacing="5" class="proptable">
			<tr>
				<td width="198" valign="top"><div><strong><?php echo $_LANG['AD_GROUP_NAME'];?>: </strong></div><span class="hinttext"><?php echo $_LANG['AD_VIEW_SITE'];?></span></td>
				<td width="475" valign="top"><input name="title" type="text" id="title" size="30" value="<?php echo htmlspecialchars($mod['title']);?>"/></td>
			</tr>
			<tr>
				<td valign="top"><div><strong><?php echo $_LANG['AD_ALIAS'];?>:</strong></div><?php if($do=='edit'){ ?><span class="hinttext"><?php echo $_LANG['AD_DONT_CHANGE'];?></span><?php } ?></td>
    <td valign="top"><input name="alias" type="text" id="title3" <?php if (@$mod['alias']=='guest') {?>readonly="readonly"<?php } ?> size="30" value="<?php echo @$mod['alias'];?>"/></td>
			</tr>
			<tr>
				<td><strong><?php echo $_LANG['AD_IF_ADMIN'];?></strong></td>
				<td>
					<label><input name="is_admin" type="radio" value="1" <?php if (@$mod['is_admin']) { echo 'checked="checked"'; } ?> onclick="$('#accesstable').hide();$('#admin_accesstable').show();"/> <?php echo $_LANG['YES'];?> </label>
					<label><input name="is_admin" type="radio" value="0"  <?php if (@!$mod['is_admin']) { echo 'checked="checked"'; } ?> onclick="$('#accesstable').show();$('#admin_accesstable').hide();"/> <?php echo $_LANG['NO'];?></label>
				</td>
			</tr>
		</table>

		<!--------------------------------------------------------------------------------------------------------------------------------------------->

		<table width="660" border="0" cellspacing="5" class="proptable" id="admin_accesstable" style="<?php if(@!$mod['is_admin']){echo 'display:none;'; }?>">
			<tr>
				<td width="191" valign="top">
					<div><strong><?php echo $_LANG['AD_AVAILABLE_SECTIONS'];?> </strong></div>
					<span class="hinttext"><?php echo $_LANG['AD_ALL_SECTIONS'];?></span>
				</td>
				<td width="475" valign="top">
					<table width="100%" border="0" cellspacing="2" cellpadding="0">
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_menu" value="admin/menu" <?php if (isset($mod['access'])) { if (in_array('admin/menu', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_menu"><?php echo $_LANG['AD_MENU_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_modules" value="admin/modules" <?php if (isset($mod['access'])) { if (in_array('admin/modules', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_modules"><?php echo $_LANG['AD_MODULES_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_content" value="admin/content" <?php if (isset($mod['access'])) { if (in_array('admin/content', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_content"><?php echo $_LANG['AD_CONTENTS_CONTROL'];?></label></td>
						</tr>
                        <tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_plugins" value="admin/plugins" <?php if (isset($mod['access'])) { if (in_array('admin/filters', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_plugins"><?php echo $_LANG['AD_PLUGINS_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_filters" value="admin/filters" <?php if (isset($mod['access'])) { if (in_array('admin/filters', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_filters"><?php echo $_LANG['AD_FILTERS_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_components" value="admin/components" <?php if (isset($mod['access'])) { if (in_array('admin/components', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_components"><?php echo $_LANG['AD_COMPONENTS_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_users" value="admin/users" <?php if (isset($mod['access'])) { if (in_array('admin/users', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_users"><?php echo $_LANG['AD_USERS_CONTROL'];?></label></td>
						</tr>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_config" value="admin/config" <?php if (isset($mod['access'])) { if (in_array('admin/config', $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="admin_config"><?php echo $_LANG['AD_SETTINGS_CONTROL'];?></label></td>
						</tr>
					</table>
                </td>
			</tr>
			<tr>
			  <td valign="top">
			  	<div><strong><?php echo $_LANG['AD_COMPONENTS_SETTINGS_FREE'];?> </strong></div>
				<span class="hinttext"><?php echo $_LANG['AD_COMPONENTS_SETTINGS_ON'];?></span>
			  </td>
			  <td valign="top">
				  <table width="100%" border="0" cellspacing="2" cellpadding="0">

						<?php
							$coms = cmsCore::getInstance()->getAllComponents();
                            foreach ($coms as $com) {
                                if (!file_exists(PATH.'/admin/components/'.$com['link'].'/backend.php')){
                                    continue;
                                }
						?>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="admin_com_<?php echo $com['link']; ?>" value="admin/com_<?php echo $com['link']; ?>" <?php if (isset($mod['access'])) { if (in_array('admin/com_'.$com['link'], $mod['access'])) { echo 'checked="checked"'; } }?> /></td>
							<td><label for="admin_com_<?php echo $com['link']; ?>"><?php echo $com['title']; ?></label></td>
						</tr>
						<?php } ?>

				  </table>
			  </td>
		  </tr>
		</table>

		<!--------------------------------------------------------------------------------------------------------------------------------------------->

		<table width="660" border="0" cellspacing="5" class="proptable" id="accesstable" style="<?php if(@$mod['is_admin']){echo 'display:none;'; } ?>">
			<tr>
				<td width="191" valign="top"><strong><?php echo $_LANG['AD_GROUP_RULE'];?> </strong></td>
				<td width="475" valign="top">
					<table width="100%" border="0" cellspacing="2" cellpadding="0">

					<?php
                        $sql = "SELECT * FROM cms_user_groups_access ORDER BY access_type";
                        $res = $inDB->query($sql);

                        while ($ga = $inDB->fetch_assoc($res)) {

                            if($mod['alias']=='guest' && $ga['hide_for_guest']){
                                continue;
                            }
                    ?>
						<tr>
							<td width="16"><input type="checkbox" name="access[]" id="<?php echo str_replace('/', '_', $ga['access_type']); ?>" value="<?php echo $ga['access_type']; ?>" <?php if (isset($mod['access'])) { if (in_array($ga['access_type'], $mod['access'])) { echo 'checked="checked"'; } }?>></td>
							<td><label for="<?php echo str_replace('/', '_', $ga['access_type']); ?>"><?php echo $ga['access_name']; ?></label></td>
						</tr>
                    <?php } ?>
					</table>
				</td>
			</tr>
		</table>

		<!--------------------------------------------------------------------------------------------------------------------------------------------->

		<p>
			<input name="add_mod" type="submit" id="add_mod" <?php if ($do=='add') { echo 'value="'.$_LANG['AD_CREATE_GROUP'].'"'; } else { echo 'value="'.$_LANG['SAVE'].'"'; } ?> />
			<span style="margin-top:15px"><input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.history.back();"/></span>
			<input name="do" type="hidden" id="do" <?php if ($do=='add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
			<?php
			if ($do=='edit'){
			 echo '<input name="id" type="hidden" value="'.$mod['id'].'" />';
			}
			?>
		</p>
	</form>
	<?php
   }
}