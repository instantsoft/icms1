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

function iconList(){
	global $_LANG;
	if ($handle = opendir(PATH.'/images/menuicons')) {
		$n = 0;
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && mb_strstr($file, '.gif')){
				$tag = str_replace('.gif', '', $file);
				$dir = '/images/menuicons/';
				echo '<a style="width:20px;height:20px;display:block; float:left; padding:2px" href="javascript:selectIcon(\''.$file.'\')"><img alt="'.$file.'"src="'.$dir.$file.'" border="0" /></a>';
 				$n++;
			}
		}
		closedir($handle);
	}

	if (!$n) { echo '<p>'.$_LANG['AD_EMPTY_FOLDER'] .'</p>'; }

	echo '<div align="right" style="clear:both">[<a href="javascript:selectIcon(\'\')">'.$_LANG['AD_NO_ICON'].'</a>] [<a href="javascript:hideIcons()">'.$_LANG['CLOSE'].'</a>]</div>';

	return;
}
function list_menu($menu) {
    $m = cmsCore::yamlToArray($menu);
    return implode(', ', $m);
}

function cpMenutypeById($item){
    global $_LANG;
    $inDB   = cmsDatabase::getInstance();

	$html   = '';
	$maxlen = 35;

	switch($item['linktype']){
        case 'link': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_LINK'].'</a></span> - '.$item['linkid'];
				     break;
        case 'component': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_COMPONENT'].'</a></span> - '.$inDB->get_field('cms_components', "link='".$item['linkid']."'", 'title');
					 	  break;
        case 'content': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_ARTICLE'].'</a></span> - '.$inDB->get_field('cms_content', 'id='.$item['linkid'], 'title');
					 	break;
        case 'category': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_PARTITION'].'</a></span> - '.$inDB->get_field('cms_category', 'id='.$item['linkid'], 'title');
					 	 break;
        case 'video_cat':
            if(cmsCore::getInstance()->isComponentInstalled('video')){
                $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_VIDEO_PARTITION'].'</a></span> - '.$inDB->get_field('cms_video_category', 'id='.$item['linkid'], 'title');
            }
            break;
        case 'uccat': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_CATEGORY'].'</a></span> - '.$inDB->get_field('cms_uc_cats', 'id='.$item['linkid'], 'title');
					  break;
        case 'blog': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_BLOG'].'</a></span> - '.$inDB->get_field('cms_blogs', 'id='.$item['linkid'], 'title');
				     break;
        case 'photoalbum': $html = '<span id="menutype"><a target="_blank" href="'.$item['link'].'">'.$_LANG['AD_TYPE_ALBUM'].'</a></span> - '.$inDB->get_field('cms_photo_albums', 'id='.$item['linkid'], 'title');
					 	   break;
	}
	$clear = strip_tags($html);
	$r = mb_strlen($html) - mb_strlen($clear);
	if (mb_strlen($clear)>$maxlen) { $html = mb_substr($html, 0, $maxlen+$r).'...'; }
	return $html;
}

function applet_menu(){

    $inCore = cmsCore::getInstance();
	$inDB   = cmsDatabase::getInstance();

	global $_LANG;
	global $adminAccess;

	if (!cmsUser::isAdminCan('admin/menu', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_MENU'];
 	cpAddPathway($_LANG['AD_MENU'], 'index.php?view=menu');

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);

	if ($do == 'list'){

        $toolmenu[] = array('icon'=>'new.gif', 'title'=>$_LANG['AD_MENU_POINT_ADD'], 'link'=>'?view=menu&do=add');
        $toolmenu[] = array('icon'=>'newmenu.gif', 'title'=>$_LANG['AD_MENU_ADD'], 'link'=>'?view=menu&do=addmenu');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=menu&do=edit&multiple=1');");
        $toolmenu[] = array('icon'=>'delete.gif', 'title'=>$_LANG['AD_DELETE_SELECTED'], 'link'=>"javascript:checkSel('?view=menu&do=delete&multiple=1');");
        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=menu&do=show&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=menu&do=hide&multiple=1');");
        $toolmenu[] = array('icon'=>'help.gif', 'title'=>$_LANG['AD_HELP'], 'link'=>'?view=help&topic=menu');

		cpToolMenu($toolmenu);

        $fields[] = array('title'=>'Lt', 'field'=>'NSLeft', 'width'=>'30');
        $fields[] = array(
            'title'=>$_LANG['TITLE'],
            'field'=>array('title','titles'), 'width'=>'',
            'link'=>'?view=menu&do=edit&id=%id%',
            'prc'=>  function ($i){
                $i['titles'] = cmsCore::yamlToArray($i['titles']);
                // переопределяем название пункта меню в зависимости от языка
                if(!empty($i['titles'][cmsConfig::getConfig('lang')])){
                    $i['title'] = $i['titles'][cmsConfig::getConfig('lang')];
                }
                return $i['title'];
            }
        );
        $fields[] = array('title'=>$_LANG['SHOW'], 'field'=>'published', 'width'=>'60');
        $fields[] = array('title'=>$_LANG['AD_ORDER'], 'field'=>'ordering', 'width'=>'100');
        $fields[] = array('title'=>$_LANG['AD_LINK'], 'field'=>array('linktype', 'linkid', 'link'), 'width'=>'240', 'prc'=>'cpMenutypeById');
        $fields[] = array('title'=>$_LANG['AD_MENU'], 'field'=>'menu', 'width'=>'70', 'filter'=>'10', 'filterlist'=>cpGetList('menu'), 'prc'=>'list_menu');
        $fields[] = array('title'=>$_LANG['TEMPLATE'], 'field'=>'template', 'width'=>'70', 'prc'=>'cpTemplateById');

        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=menu&do=edit&id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_MENU_POINT_CONFIRM'], 'link'=>'?view=menu&do=delete&id=%id%');

		cpListTable('cms_menu', $fields, $actions, 'parent_id>0', 'NSLeft, ordering');

	} else {

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'index.php?view=menu');

        cpToolMenu($toolmenu);

    }

	if ($do == 'move_up'){
        $inDB->moveNsCategory('cms_menu', $id, 'up');
		cmsCore::redirectBack();
	}

	if ($do == 'move_down'){
		$inDB->moveNsCategory('cms_menu', $id, 'down');
		cmsCore::redirectBack();
	}

	if ($do == 'show'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbShow('cms_menu', $id);  }
			echo '1'; exit;
		} else {
			dbShowList('cms_menu', $_REQUEST['item']);
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');
			cmsCore::redirectBack();
		}
	}

	if ($do == 'hide'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbHide('cms_menu', $id);  }
			echo '1'; exit;
		} else {
			dbHideList('cms_menu', cmsCore::request('item', 'array_int', array()));
            cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');
			cmsCore::redirectBack();
		}
	}

	if ($do == 'delete'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ $inDB->deleteNS('cms_menu', (int)$id);  }
		} else {
            $items = cmsCore::request('item', 'array_int', array());
            foreach($items as $item_id){
                $inDB->deleteNS('cms_menu', $item_id);
            }
		}
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');
		cmsCore::redirectBack();
	}

	if ($do == 'update'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $id = cmsCore::request('id', 'int', 0);
        if(!$id){ cmsCore::redirectBack(); }

        $title     = cmsCore::request('title', 'str', '');
        $titles    = cmsCore::arrayToYaml(cmsCore::request('titles', 'array_str', array()));
        $menu      = cmsCore::arrayToYaml(cmsCore::request('menu', 'array_str', ''));
        $linktype  = cmsCore::request('mode', 'str', '');
        $linkid    = cmsCore::request($linktype, 'str', '');
        $link      = $inCore->getMenuLink($linktype, $linkid);
        $target    = cmsCore::request('target', 'str', '');
        $published = cmsCore::request('published', 'int', 0);
        $template  = cmsCore::request('template', 'str', '');
        $iconurl   = cmsCore::request('iconurl', 'str', '');
        $parent_id = cmsCore::request('parent_id', 'int', 0);
        $oldparent = cmsCore::request('oldparent', 'int', 0);
		$is_lax    = cmsCore::request('is_lax', 'int', 0);
		$css_class = cmsCore::request('css_class', 'str', '');

        $is_public = cmsCore::request('is_public', 'int', '');
        if (!$is_public){
            $access_list = cmsCore::arrayToYaml(cmsCore::request('allow_group', 'array_int'));
        }

        $ns = $inCore->nestedSetsInit('cms_menu');

        if ($oldparent!=$parent_id){
            $ns->MoveNode($id, $parent_id);
        }

        $sql = "UPDATE cms_menu
                SET title='$title',
                    titles='$titles',
                    css_class='$css_class',
                    menu='$menu',
                    link='$link',
                    linktype='$linktype',
                    linkid='$linkid',
                    target='$target',
                    published='$published',
                    template='$template',
                    access_list='$access_list',
                    is_lax='$is_lax',
                    iconurl='$iconurl'
                WHERE id = '$id'
                LIMIT 1";
        $inDB->query($sql) ;

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');

        if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
            cmsCore::redirect('?view=menu');
        } else {
            cmsCore::redirect('?view=menu&do=edit');
        }

	}

	if ($do == 'submit'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $title     = cmsCore::request('title', 'str', '');
        $titles    = cmsCore::arrayToYaml(cmsCore::request('titles', 'array_str', array()));
        $menu      = cmsCore::arrayToYaml(cmsCore::request('menu', 'array_str', ''));
        $linktype  = cmsCore::request('mode', 'str', '');
        $linkid    = cmsCore::request($linktype, 'str', '');
        $link      = $inCore->getMenuLink($linktype, $linkid);
        $target    = cmsCore::request('target', 'str', '');
        $published = cmsCore::request('published', 'int', 0);
        $template  = cmsCore::request('template', 'str', '');
        $iconurl   = cmsCore::request('iconurl', 'str', '');
        $parent_id = cmsCore::request('parent_id', 'int', 0);
        $css_class = cmsCore::request('css_class', 'str', '');

        $is_public = cmsCore::request('is_public', 'int', '');
		$is_lax    = cmsCore::request('is_lax', 'int', 0);
        if (!$is_public){
            $access_list = cmsCore::arrayToYaml(cmsCore::request('allow_group', 'array_int'));
        }

		$ns = $inCore->nestedSetsInit('cms_menu');
		$myid = $ns->AddNode($parent_id);

		$sql = "UPDATE cms_menu
				SET menu='$menu',
					title='$title',
                    titles='$titles',
                    css_class='$css_class',
					link='$link',
					linktype='$linktype',
					linkid='$linkid',
					target='$target',
					published='$published',
					template='$template',
					access_list='$access_list',
					is_lax='$is_lax',
					iconurl='$iconurl'
				WHERE id = '$myid'";

		$inDB->query($sql);

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');

        cmsCore::redirect('?view=menu');

	}

	if ($do == 'submitmenu'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$sql = "SELECT ordering as max_o FROM cms_modules ORDER BY ordering DESC LIMIT 1";
		$result = $inDB->query($sql) ;
		$row = $inDB->fetch_assoc($result);
		$maxorder = $row['max_o'] + 1;

        $menu       = cmsCore::request('menu', 'str', '');
		$title      = cmsCore::request('title', 'str', '');
		$position   = cmsCore::request('position', 'str', '');
		$published  = cmsCore::request('published', 'int', 0);
		$css_prefix = cmsCore::request('css_prefix', 'str', '');
		$is_public  = cmsCore::request('is_public', 'int', '');
		if (!$is_public){
			$access_list = $inCore->arrayToYaml(cmsCore::request('allow_group', 'array_int'));
		}

		$cfg['menu'] = $menu;
		$cfg_str = cmsCore::arrayToYaml($cfg);

		$sql = "INSERT INTO cms_modules (position, name, title, is_external, content, ordering, showtitle, published, user, config, css_prefix, access_list)
                VALUES ('$position', '{$_LANG['AD_MENU']}', '$title', 1, 'mod_menu', $maxorder, 1, $published, 0, '$cfg_str', '$css_prefix', '$access_list')";

		$inDB->query($sql) ;

		$newid = $inDB->get_last_id('cms_modules');

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');

        cmsCore::redirect('?view=modules&do=edit&id='.$newid);

	}

    if ($do == 'addmenu' || $do == 'add' || $do == 'edit'){
        $GLOBALS['cp_page_head'][] = '<script language="JavaScript" type="text/javascript" src="js/menu.js"></script>';
        echo '<script>';
        echo cmsPage::getLangJS('AD_SPECIFY_LINK_MENU');
        echo '</script>';
    }

    if ($do == 'addmenu'){

        $GLOBALS['cp_page_title'] = $_LANG['AD_MENU_ADD'];
        cpAddPathway($_LANG['AD_MENU_ADD']);

        $menu_list = cpGetList('menu');

         ?>
         <form id="addform" name="addform" action="index.php?view=menu&do=submitmenu" method="post">
             <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
             <table class="proptable" width="650" cellspacing="10" cellpadding="10">
                 <tr>
                     <td width="300" valign="top">
                         <strong><?php echo $_LANG['AD_MODULE_MENU_TITLE']; ?></strong>
                     </td>
                     <td valign="top">
                         <input name="title" type="text" id="title2" style="width:99%" value=""/>
                     </td>
                 </tr>
                 <tr>
                     <td valign="top">
                         <strong><?php echo $_LANG['AD_MENU_TO_VIEW'] ; ?></strong><br/>
                         <span class="hinttext"><?php echo $_LANG['AD_TO_CREATE_NEW_POINT']; ?></span>
                     </td>
                     <td valign="top">
                         <select name="menu" id="menu" style="width:99%">
                             <?php foreach ($menu_list as $menu) { ?>
                                 <option value="<?php echo $menu['id']; ?>">
                                     <?php echo $menu['title']; ?>
                                 </option>
                             <?php } ?>
                         </select>
                     </td>
                 </tr>
                 <tr>
                     <td valign="top">
                         <strong><?php echo $_LANG['AD_POSITION_TO_VIEW']; ?></strong><br />
                         <span class="hinttext"><?php echo $_LANG['AD_POSITION_MUST_BE']; ?></span>
                     </td>
                     <td valign="top">
                         <?php
                             $pos = cpModulePositions(cmsConfig::getConfig('template'));
                         ?>
                         <select name="position" id="position" style="width:99%">
                             <?php
                                 if ($pos){
                                     foreach($pos as $key=>$position){
                                         if (@$mod['position']==$position){
                                             echo '<option value="'.$position.'" selected>'.$position.'</option>';
                                         } else {
                                             echo '<option value="'.$position.'">'.$position.'</option>';
                                         }
                                     }
                                 }
                             ?>
                         </select>
                         <input name="is_external" type="hidden" id="is_external" value="0" />
                     </td>
                 </tr>
                 <tr>
                     <td valign="top"><strong><?php echo $_LANG['AD_MENU_PUBLIC']; ?></strong></td>
                     <td valign="top">
                         <label><input name="published" type="radio" value="1" checked="checked" <?php if (@$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?></label>
                         <label><input name="published" type="radio" value="0"  <?php if (@!$mod['published']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?></label>
                     </td>
                 </tr>
                 <tr>
                     <td valign="top"><strong><?php echo $_LANG['AD_PREFIX_CSS']; ?></strong></td>
                     <td valign="top">
                         <input name="css_prefix" type="text" id="css_prefix" value="<?php echo @$mod['css_prefix'];?>" style="width:99%" />
                     </td>
                 </tr>
                 <tr>
                     <td valign="top">
                         <strong><?php echo $_LANG['AD_TAB_ACCESS']; ?>:</strong><br />
                         <span class="hinttext"><?php echo $_LANG['AD_GROUP_ACCESS'] ; ?></span>
                     </td>
                     <td valign="top">
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                         <tr>
                             <td width="20">
                                 <?php

                                     $groups = cmsUser::getGroups();
                                     $style  = 'disabled="disabled"';
                                     $public = 'checked="checked"';

                                     if ($do == 'edit'){

                                         if ($mod['access_list']){
                                             $public = '';
                                             $style  = '';

                                             $access_list = $inCore->yamlToArray($mod['access_list']);

                                         }
                                     }
                                 ?>
                                 <input name="is_public" type="checkbox" id="is_public" onclick="checkAccesList()" value="1" <?php echo $public?> />
                             </td>
                             <td><label for="is_public"><strong><?php echo $_LANG['AD_SHARE']; ?></strong></label></td>
                         </tr>
                     </table>
                     <div style="padding:5px">
                         <span class="hinttext">
                             <?php echo $_LANG['AD_VIEW_IF_CHECK']; ?>
                         </span>
                     </div>

                     <div style="margin-top:10px;padding:5px;padding-right:0px;" id="grp">
                         <div>
                             <strong><?php echo $_LANG['AD_GROUPS_VIEW']; ?></strong><br />
                             <span class="hinttext">
                                  <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL']; ?>
                             </span>
                         </div>
                         <div>
                             <?php
                                 echo '<select style="width: 99%" name="allow_group[]" id="allow_group" size="6" multiple="multiple" '.$style.'>';

                                 if ($groups){
                                     foreach($groups as $group){
                                         echo '<option value="'.$group['id'].'"';
                                         if ($do=='edit'){
                                             if (inArray($access_list, $group['id'])){
                                                 echo 'selected="selected"';
                                             }
                                         }

                                         echo '>';
                                         echo $group['title'].'</option>';
                                     }

                                 }

                                 echo '</select>';
                             ?>
                         </div>
                     </div>
                     </td>
                 </tr>
                 <tr>
                     <td colspan="2" valign="top">
                         <div style="padding:10px;margin:4px;background-color:#EBEBEB;border:solid 1px gray">
                             <?php echo $_LANG['AD_NEW_MENU_NEW_MODULE']; ?>
                         </div>
                     </td>
                 </tr>
             </table>
             <div style="margin-top:5px">
                 <input name="save" type="submit" id="save" value="<?php echo $_LANG['AD_MENU_ADD']; ?>" />
                 <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=menu';" />
             </div>
         </form>
         <?php

    }

    if ($do == 'add' || $do == 'edit'){

        require('../includes/jwtabs.php');
        $GLOBALS['cp_page_head'][] = jwHeader();

        $menu_list = cpGetList('menu');

        $langs = cmsCore::getDirsList('/languages');

        if ($do=='add'){
             cpAddPathway($_LANG['AD_MENU_POINT_ADD']);
             $mod['menu'] = array('mainmenu');
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

            $mod = $inDB->get_fields('cms_menu', "id = '$item_id'", '*');
            if(!$mod){ cmsCore::error404(); }
            $mod['menu']   = cmsCore::yamlToArray($mod['menu']);
            $mod['titles'] = cmsCore::yamlToArray($mod['titles']);

            cpAddPathway($_LANG['AD_MENU_POINT_EDIT'].$ostatok.' "'.$mod['title'].'"');

        }
	?>
    <form id="addform" name="addform" method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <input type="hidden" name="view" value="menu" />

        <table class="proptable" width="100%" cellpadding="15" cellspacing="2">
            <tr>

                <td valign="top">

                    <div><strong><?php echo $_LANG['AD_MENU_POINT_TITLE']; ?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_VIEW_IN_SITE']; ?></span></div>
                    <div><input name="title" type="text" id="title" style="width:100%" value="<?php echo htmlspecialchars($mod['title']);?>" /></div>
                    <?php if(count($langs)>1) { ?>
                    <div><strong><?php echo $_LANG['AD_LANG_TITLES']; ?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_LANG_TITLES_HINT']; ?></span></div>
                    <?php foreach ($langs as $lang) { ?>

                    <div><strong><?php echo $lang; ?>:</strong> <input name="titles[<?php echo $lang; ?>]" type="text" style="width:97%" value="<?php echo htmlspecialchars(@$mod['titles'][$lang]);?>" placeholder="<?php echo $_LANG['AD_HINT_DEFAULT']; ?>" /></div>
                    <?php } ?>
                    <?php } ?>
                    <div><strong><?php echo $_LANG['AD_PARENT_POINT']; ?></strong></div>
                    <div>
                        <?php
                            $rootid = $inDB->get_field('cms_menu', 'parent_id=0', 'id');
                        ?>
                        <select name="parent_id" size="10" id="parent_id" style="width:100%">
                            <option value="<?php echo $rootid?>" <?php if (@$mod['parent_id']==$rootid || !isset($mod['parent_id'])) { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_MENU_ROOT']; ?></option>
                            <?php
                                if (isset($mod['parent_id'])){
                                    echo $inCore->getListItemsNS('cms_menu', $mod['parent_id']);
                                } else {
                                    echo $inCore->getListItemsNS('cms_menu');
                                }
                            ?>
                        </select>
                        <input type="hidden" name="oldparent" value="<?php echo @$mod['parent_id'];?>" />
                    </div>

                    <div><strong><?php echo $_LANG['AD_MENU_POINT_ACTION']; ?></strong></div>
                    <div>
                        <select name="mode" id="linktype" style="width:100%" onchange="showMenuTarget()">
                            <option value="link" <?php if (@$mod['linktype']=='link' || !isset($mod['mode'])) { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_LINK']; ?></option>
                            <option value="content" <?php if (@$mod['linktype']=='content') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_ARTICLE']; ?></option>
                            <option value="category" <?php if (@$mod['linktype']=='category') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_PARTITION']; ?></option>
                            <?php if($inCore->isComponentInstalled('video')){ ?>
                            <option value="video_cat" <?php if (@$mod['linktype']=='video_cat') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_VIDEO_PARTITION']; ?></option>
                            <?php } ?>
                            <option value="component" <?php if (@$mod['linktype']=='component') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_COMPONENT']; ?></option>
                            <option value="blog" <?php if (@$mod['linktype']=='blog') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_BLOG']; ?></option>
                            <option value="uccat" <?php if (@$mod['linktype']=='uccat') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_CATEGORY']; ?></option>
                            <option value="photoalbum" <?php if (@$mod['linktype']=='photoalbum') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_OPEN_ALBUM']; ?></option>
                        </select>
                    </div>

                    <div id="t_link" class="menu_target" style="display:<?php if ($mod['linktype']=='link'||$mod['linktype']=='ext'||!$mod['linktype']) { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_LINK']; ?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_LINK_HINT']; ?> <b>http://</b></span>
                        </div>
                        <div>
                            <input name="link" type="text" id="link" size="50" style="width:100%" <?php if (@$mod['linktype']=='link'||@$mod['linktype']=='ext') { echo  'value="'.$mod['link'].'"'; } ?>/>
                        </div>
                    </div>

                    <div id="t_content" class="menu_target" style="display:<?php if ($mod['linktype']=='content') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_ARTICLE'] ; ?></strong>
                        </div>
                        <div>
                            <select name="content" id="content" style="width:100%">
                                <?php
                                    if (@$mod['linktype']=='content') {
                                        echo $inCore->getListItems('cms_content', $mod['linkid']);
                                    } else {
                                        echo $inCore->getListItems('cms_content');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <?php if($inCore->isComponentInstalled('video')){ ?>
                    <div id="t_video_cat" class="menu_target" style="display:<?php if ($mod['linktype']=='video_cat') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_PARTITION']; ?></strong>
                        </div>
                        <div>
                            <select name="video_cat" id="video_cat" style="width:100%">
                                    <?php
                                    if (@$mod['linktype']=='video_cat') {
                                        echo $inCore->getListItemsNS('cms_video_category', $mod['linkid']);
                                    } else {
                                        echo $inCore->getListItemsNS('cms_video_category');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

                    <div id="t_category" class="menu_target" style="display:<?php if ($mod['linktype']=='category') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_PARTITION']; ?></strong>
                        </div>
                        <div>
                            <select name="category" id="category" style="width:100%">
                                    <?php
                                    if (@$mod['linktype']=='category') {
                                        echo $inCore->getListItemsNS('cms_category', $mod['linkid']);
                                    } else {
                                        echo $inCore->getListItemsNS('cms_category');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="t_component" class="menu_target" style="display:<?php if ($mod['linktype']=='component') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_COMPONENT']; ?></strong>
                        </div>
                        <div>
                           <select name="component" id="component" style="width:100%">
                                <?php
                                    if (@$mod['linktype']=='component') {
                                        echo $inCore->getListItems('cms_components', $mod['linkid'], 'title', 'asc', 'internal=0', 'link');
                                    } else {
                                        echo $inCore->getListItems('cms_components', 0, 'title', 'asc', 'internal=0', 'link');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="t_blog" class="menu_target" style="display:<?php if ($mod['linktype']=='blog') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_BLOG']; ?></strong>
                        </div>
                        <div>
                           <select name="blog" id="blog" style="width:100%">
                                <?php
                                    if (@$mod['linktype']=='blog') {
                                        echo $inCore->getListItems('cms_blogs', $mod['linkid'], 'title', 'asc', "owner='user'");
                                    } else {
                                        echo $inCore->getListItems('cms_blogs', 0, 'title', 'asc', "owner='user'");
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="t_uccat" class="menu_target" style="display:<?php if ($mod['linktype']=='uccat') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_CATEGORY']; ?></strong>
                        </div>
                        <div>
                           <select name="uccat" id="uccat" style="width:100%">
                                <?php
                                    if (@$mod['linktype']=='uccat') {
                                        echo $inCore->getListItems('cms_uc_cats', $mod['linkid']);
                                    } else {
                                        echo $inCore->getListItems('cms_uc_cats');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="t_photoalbum" class="menu_target" style="display:<?php if ($mod['linktype']=='photoalbum') { echo  'block'; } else { echo 'none'; } ?>">
                        <div>
                            <strong><?php echo $_LANG['AD_CHECK_ALBUM'] ; ?></strong>
                        </div>
                        <div>
                           <select name="photoalbum" id="photoalbum" style="width:100%">
                                <?php
                                    if (@$mod['linktype']=='photoalbum') {
                                        echo $inCore->getListItems('cms_photo_albums', $mod['linkid'], 'id', 'ASC', 'NSDiffer = ""');
                                    } else {
                                        echo $inCore->getListItems('cms_photo_albums', 0, 'id', 'ASC', 'NSDiffer = ""');
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                </td>

                <td width="300" valign="top" style="background:#ECECEC;">

                    <?php ob_start(); ?>

                    {tab=<?php echo $_LANG['AD_TAB_PUBLISH']; ?>}

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                            <tr>
                                <td width="20"><input type="checkbox" name="published" id="published" value="1" <?php if ($mod['published'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                                <td><label for="published"><strong><?php echo $_LANG['AD_MENU_POINT_PUBLIC']; ?></strong></label></td>
                            </tr>
                        </table>

                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['AD_OPEN_POINT'];?></strong>
                        </div>
                        <div>
                            <select name="target" id="target" style="width:100%">
                                <option value="_self" <?php if (@$mod['target']=='_self') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_SELF']; ?></option>
                                <option value="_parent"><?php echo $_LANG['AD_PARENT'];?></option>
                                <option value="_blank" <?php if (@$mod['target']=='_blank') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_BLANK']; ?></option>
                                <option value="_top" <?php if (@$mod['target']=='_top') { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_TOP']; ?></option>
                            </select>
                        </div>

                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['TEMPLATE'];?></strong><br/>
                            <span class="hinttext"><?php echo $_LANG['AD_DESIGN_CHANGE'] ;?></span>
                        </div>
                        <div>
                            <select name="template" id="template" style="width:100%">
                                <option value="0" <?php if (@$mod['template']==0 || !$mod['template']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_BY_DEFAULT'];?></option>
                                <?php
                                $templates = cmsCore::getDirsList('/templates');
                                foreach ($templates as $template) {
                                    echo '<option value="'.$template.'" '.(@$mod['template'] == $template ? 'selected="selected"': '').'>'.$template.'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['AD_ICON_PICTURE'];?></strong><br/>
                            <span class="hinttext"><?php echo $_LANG['AD_ICON_FILENAME'];?></span>
                        </div>
                        <div>
                            <input name="iconurl" type="text" id="iconurl" size="30" value="<?php echo @$mod['iconurl'];?>" style="width:100%"/>
                            <div>
                                <a id="iconlink" style="display:block;" href="javascript:showIcons()"><?php echo $_LANG['AD_CHECK_ICON'];?></a>
                                <div id="icondiv" style="display:none; padding:6px;border:solid 1px gray;background:#FFF">
                                    <div><?php iconList(); ?></div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['AD_CSS_CLASS'];?></strong>
                        </div>
                        <div>
                            <input name="css_class" type="text" size="30" value="<?php echo @$mod['css_class'];?>" style="width:100%"/>
                        </div>

                    {tab=<?php echo $_LANG['AD_TAB_ACCESS'] ;?>}
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                        <tr>
                            <td width="20">
                                <?php

									$groups = cmsUser::getGroups();

                                    $style  = 'disabled="disabled"';
                                    $public = 'checked="checked"';

                                    if ($do == 'edit'){

                                        if ($mod['access_list']){
                                            $public = '';
                                            $style  = '';

											$access_list = $inCore->yamlToArray($mod['access_list']);

                                        }
                                    }
                                ?>
                                <input name="is_public" type="checkbox" id="is_public" onclick="checkAccesList()" value="1" <?php echo $public?> />
                            </td>
                            <td><label for="is_public"><strong><?php echo $_LANG['AD_SHARE'];?></strong></label></td>
                        </tr>
                    </table>
                    <div style="padding:5px">
                        <span class="hinttext">
                            <?php echo $_LANG['AD_VIEW_IF_CHECK'];?>
                        </span>
                    </div>

                    <div style="margin-top:10px;padding:5px;padding-right:0px;" id="grp">
                        <div>
                            <strong><?php echo $_LANG['AD_GROUPS_VIEW'];?></strong><br />
                            <span class="hinttext">
                                <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL'];?>
                            </span>
                        </div>
                        <div>
                            <?php
                                echo '<select style="width: 99%" name="allow_group[]" id="allow_group" size="6" multiple="multiple" '.$style.'>';

                                if ($groups){
									foreach($groups as $group){
                                        echo '<option value="'.$group['id'].'"';
                                        if ($do=='edit' && $mod['access_list']){
                                            if (inArray($access_list, $group['id'])){
                                                echo 'selected="selected"';
                                            }
                                        }

                                        echo '>';
                                        echo $group['title'].'</option>';
									}

                                }

                                echo '</select>';
                            ?>
                        </div>
                    </div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                        <tr>
                            <td width="20">
                                <input name="is_lax" type="checkbox" id="is_lax" value="1" <?php if(@$mod['is_lax']) {?>checked="checked"<?php } ?> />
                            </td>
                            <td><label for="is_lax"><strong><?php echo $_LANG['AD_ONLY_CHILD_ITEM']; ?></strong></label></td>
                        </tr>
                    </table>
                    {tab=<?php echo $_LANG['AD_MENU']; ?>}
                    <div style="padding:5px;padding-right:0px;">
                        <div>
                            <strong><?php echo $_LANG['AD_MENU_TO_VIEW'];?></strong><br />
                            <span class="hinttext">
                                <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL'];?>
                            </span>
                        </div>
                        <div>
                        <select style="width: 99%" name="menu[]" size="9" multiple="multiple">
                            <?php foreach ($menu_list as $menu) { ?>
                                <option value="<?php echo $menu['id']; ?>" <?php if (@in_array($menu['id'], @$mod['menu'])) { echo 'selected="selected"'; }?>>
                                    <?php echo $menu['title']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        </div>
                    </div>
                    {/tabs}

                    <?php echo jwTabs(ob_get_clean()); ?>

                </td>

            </tr>
        </table>

        <p>
            <input name="add_mod" type="button" onclick="submitItem()" id="add_mod" value="<?php echo $_LANG['SAVE']; ?> " />
            <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.location.href='index.php?view=menu';" />
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