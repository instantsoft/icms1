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

function applet_userbanlist(){

    $inCore = cmsCore::getInstance();
	$inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

	global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/users', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_BANLIST'];
 	cpAddPathway($_LANG['AD_USERS'], 'index.php?view=users');
 	cpAddPathway($_LANG['AD_BANLIST'], 'index.php?view=userbanlist');

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);
	$to = cmsCore::request('to', 'int', 0);
    // для редиректа обратно в профиль на сайт
    if($to){
        cmsUser::sessionPut('back_url', cmsCore::getBackURL());
    }

	if ($do == 'list'){

        $toolmenu[] = array('icon'=>'useradd.gif', 'title'=>$_LANG['AD_TO_BANLIST_ADD'], 'link'=>'?view=userbanlist&do=add');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=userbanlist&do=edit&multiple=1');");
        $toolmenu[] = array('icon'=>'delete.gif', 'title'=>$_LANG['AD_DELETE_SELECTED'], 'link'=>"javascript:checkSel('?view=userbanlist&do=delete&multiple=1');");

		cpToolMenu($toolmenu);

        $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
        $fields[] = array('title'=>$_LANG['AD_IS_ACTIVE'], 'field'=>'status', 'width'=>'55', 'prc'=>'cpYesNo');
        $fields[] = array('title'=>$_LANG['AD_BANLIST_USER'], 'field'=>'user_id', 'width'=>'120', 'filter'=>'12', 'prc'=>'cpUserNick');
        $fields[] = array('title'=>$_LANG['AD_BANLIST_IP'], 'field'=>'ip', 'width'=>'100', 'link'=>'?view=userbanlist&do=edit&id=%id%', 'filter'=>'12');
        $fields[] = array('title'=>$_LANG['DATE'], 'field'=>'bandate', 'width'=>'', 'fdate'=>'%d/%m/%Y %H:%i:%s', 'filter'=>'12');
        $fields[] = array('title'=>$_LANG['AD_BANLIST_TIME'], 'field'=>'int_num', 'width'=>'55');
        $fields[] = array('title'=>'', 'field'=>'int_period', 'width'=>'70');
        $fields[] = array('title'=>$_LANG['AD_AUTOREMOVE'], 'field'=>'autodelete', 'width'=>'90', 'prc'=>'cpYesNo');

        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=userbanlist&do=edit&id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_REMOVE_RULE'], 'link'=>'?view=userbanlist&do=delete&id=%id%');

		cpListTable('cms_banlist', $fields, $actions, '1=1', 'ip DESC');

	}

    if ($do == 'delete'){
        if (!isset($_REQUEST['item'])){
            if ($id >= 0){ dbDelete('cms_banlist', $id);  }
        } else {
            dbDeleteList('cms_banlist', $_REQUEST['item']);
        }
        cmsCore::redirect('?view=userbanlist');
    }

	if ($do == 'submit' || $do == 'update'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

        $types = array('user_id'=>array('user_id', 'int', 0),
                       'ip'=>array('ip', 'str', ''),
                       'cause'=>array('cause', 'str', ''),
                       'autodelete'=>array('autodelete', 'int', 0),
                       'int_num'=>array('int_num', 'int', 0),
                       'int_period'=>array('int_period', 'str', '',
                                        create_function('$p', 'if(!in_array($p, array("MONTH","DAY","HOUR","MINUTE"))){ $p = "MINUTE"; } return $p;')));

        $items = cmsCore::getArrayFromRequest($types);

        $error = false;

        if (!$items['ip']){
            $error = true;
            cmsCore::addSessionMessage($_LANG['AD_NEED_IP'], 'error');
        }
        if ($items['ip'] == $_SERVER['REMOTE_ADDR'] ||
            $items['user_id'] == $inUser->id){
            $error = true;
            cmsCore::addSessionMessage($_LANG['AD_ITS_YOUR_IP'], 'error');
        }

        if(cmsUser::userIsAdmin($items['user_id'])){
            $error = true;
            cmsCore::addSessionMessage($_LANG['AD_ITS_ADMIN'], 'error');
        }

        if ($error){
            cmsCore::redirectBack();
        }

        if($do == 'update'){

            $inDB->update('cms_banlist', $items, $id);

            if (empty($_SESSION['editlist'])){
                cmsCore::redirect('?view=userbanlist');
            } else {
                cmsCore::redirect('?view=userbanlist&do=edit');
            }

        }

        $inDB->insert('cms_banlist', $items);
        $back_url = cmsUser::sessionGet('back_url'); cmsUser::sessionDel('back_url');
        cmsCore::redirect($back_url ? $back_url : '?view=userbanlist');

	}

    if ($do == 'add' || $do == 'edit'){

        $GLOBALS['cp_page_head'][] = '<script language="JavaScript" type="text/javascript" src="/admin/js/banlist.js"></script>';

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'javascript:history.go(-1);');

        cpToolMenu($toolmenu);

        if ($do=='add'){
            echo '<h3>'.$_LANG['AD_TO_BANLIST_ADD'].'</h3>';
            cpAddPathway($_LANG['AD_TO_BANLIST_ADD']);
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

            $mod = $inDB->get_fields('cms_banlist', "id = '$item_id'", '*');
            if(!$mod){ cmsCore::error404(); }

            echo '<h3>'.$_LANG['AD_EDIT_RULE'].' '.$ostatok.'</h3>';

            cpAddPathway($_LANG['AD_EDIT_RULE']);

        }

	?>
	  <div style="margin-top:2px;padding:10px;border:dotted 1px silver; width:508px;background:#FFFFCC">
	  	<div style="font-weight:bold"><?php echo $_LANG['ATTENTION'];?>!</div>
		<div><?php echo $_LANG['AD_CAUTION_INFO_0'];?></div>
		<div><?php echo $_LANG['AD_CAUTION_INFO_1'];?></div>
	  </div>
      <form id="addform" name="addform" method="post" action="index.php?view=userbanlist">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="530" border="0" cellspacing="5" class="proptable">
          <tr>
            <td width="150" valign="top"><div><strong><?php echo $_LANG['AD_BANLIST_USER'];?>: </strong></div></td>
			<?php if($do=='add' && $to) { $mod['user_id'] = $to; $mod['ip'] = $inDB->get_field('cms_users', 'id='.$to, 'last_ip'); } ?>
            <td valign="top">
				<select name="user_id" id="user_id" onchange="loadUserIp()" style="width: 250px;">
                    <option value="0" <?php if (@!$mod['user_id']){ echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_WHITHOUT_USER'];?></option>
                    <?php
                        if (isset($mod['user_id'])) {
                            echo $inCore->getListItems('cms_users', $mod['user_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                        } else {
                            echo $inCore->getListItems('cms_users', 0, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                        }
                    ?>
				</select>
            </td>
          </tr>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_BANLIST_IP'];?>:</strong></td>
            <td valign="top"><input name="ip" type="text" id="ip" style="width: 244px;" value="<?php echo @$mod['ip'];?>"/></td>
          </tr>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_BANLIST_CAUSE'];?>:</strong></td>
            <td valign="top">
                <textarea name="cause" style="width:240px" rows="5"><?php echo @$mod['cause'];?></textarea>
            </td>
          </tr>
		  <?php $forever=false; if (!@$mod['int_num']){ $forever = true; } ?>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_BAN_FOREVER'];?></strong></td>
            <td valign="top"><input type="checkbox" name="forever" value="1" <?php if ($forever){ echo 'checked="checked"'; } ?> onclick="$('tr.bantime').toggle();"/></td>
          </tr>
          <tr class="bantime">
            <td valign="top"><strong><?php echo $_LANG['AD_BAN_FOR_TIME'];?></strong> </td>

            <td valign="top"><p>
            <input name="int_num" type="text" id="int_num" size="5" value="<?php echo @(int)$mod['int_num']?>"/>
              <select name="int_period" id="int_period">
                <option value="MINUTE"  <?php if (@mb_strstr($mod['int_period'], 'MINUTE')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['MINUTE10'];?></option>]
                <option value="HOUR"  <?php if (@mb_strstr($mod['int_period'], 'HOUR')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['HOUR10'];?></option>
                <option value="DAY" <?php if (@mb_strstr($mod['int_period'], 'DAY')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['DAY10'];?></option>
                <option value="MONTH" <?php if (@mb_strstr($mod['int_period'], 'MONTH')) { echo 'selected="selected"'; } ?>><?php echo $_LANG['MONTH10'];?></option>
              </select>
            </p>
            <p><label><input name="autodelete" type="checkbox" id="autodelete" value="1" <?php if($mod['autodelete']){ echo 'checked="checked"'; } ?> /> <?php echo $_LANG['AD_REMOVE_BAN'];?></label></p>
            </td>
          </tr>
		  <?php if ($forever) { ?><script type="text/javascript">$('tr.bantime').hide();</script><?php } ?>
        </table>
        <p>
          <label>
          <input name="add_mod" type="submit" id="add_mod" <?php if ($do=='add') { echo 'value="'.$_LANG['AD_TO_BANLIST_ADD'].'"'; } else { echo 'value="'.$_LANG['SAVE'].'"'; } ?> />
          </label>
          <label><span style="margin-top:15px">
          <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.history.back();"/>
          </span></label>
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

?>