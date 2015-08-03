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

function cpModuleHasConfig($item){
    if (file_exists('modules/'.$item['content'].'/backend.php')){ return true; }
    if (file_exists('modules/'.$item['content'].'/backend.xml')){ return true; }
	return false;
}

function applet_modules(){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();

	global $_LANG;

	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/modules', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_MODULES'];
	cpAddPathway($_LANG['AD_MODULES'], 'index.php?view=modules');
	$GLOBALS['cp_page_head'][] = '<script language="JavaScript" type="text/javascript" src="js/modules.js"></script>';

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);
	$co = cmsCore::request('co', 'int', -1);

//============================================================================//
//============================================================================//

	if ($do == 'config'){

		$module_name    = cpModuleById($id);
		$module_title   = cpModuleTitleById($id);

		if (!$module_name) { cmsCore::redirect('index.php?view=modules&do=edit&id='.$id); }

        $xml_file = PATH.'/admin/modules/'.$module_name.'/backend.xml';
        $php_file = 'modules/'.$module_name.'/backend.php';

        if (!file_exists($xml_file)){
            if (file_exists($php_file)){ include $php_file; return; }
            cmsCore::halt();
        }

        $cfg = $inCore->loadModuleConfig($id);

        cmsCore::loadClass('formgen');

        $formGen = new cmsFormGen($xml_file, $cfg);

        cpAddPathway($module_title, '?view=modules&do=edit&id='.$id);
    	cpAddPathway($_LANG['AD_SETTINGS']);

        echo '<h3>'.$module_title.'</h3>';

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:submitModuleConfig();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'index.php?view=modules');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_MODULE_VIEW'], 'link'=>'?view=modules&do=edit&id='.$id);

        cpToolMenu($toolmenu);

        echo '<form action="index.php?view=modules&do=save_auto_config&id='.$id.'" method="post" name="optform" target="_self" id="optform">';
        echo $formGen->getHTML();
        echo '</form>';

        return;

	}

//============================================================================//
//============================================================================//

    if ($do == 'save_auto_config'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $module_name = cpModuleById($id);

        $is_ajax = cmsCore::inRequest('ajax');

        if ($is_ajax){
            $title      = cmsCore::request('title', 'str', '');
            $published  = cmsCore::request('published', 'int', 0);
            $inDB->query("UPDATE cms_modules SET title='{$title}', published='{$published}' WHERE id={$id}");
            if(cmsCore::inRequest('content')){
                $content = $inDB->escape_string(cmsCore::request('content', 'html'));
                $inDB->query("UPDATE cms_modules SET content='{$content}' WHERE id={$id}");
            }
        }

        if (cmsCore::inRequest('title_only')){
            cmsCore::redirectBack();
        }

        $xml_file = PATH.'/admin/modules/'.$module_name.'/backend.xml';
        if (!file_exists($xml_file)){ cmsCore::halt(); }

        $cfg = array();

        $backend = simplexml_load_file($xml_file);

        foreach($backend->params->param as $param){

            $name       = (string)$param['name'];
            $type       = (string)$param['type'];
            $default    = (string)$param['default'];

            switch($param['type']){

                case 'number':  $value = cmsCore::request($name, 'int', $default); break;
                case 'string':  $value = cmsCore::request($name, 'str', $default); break;
                case 'html':    $value = cmsCore::badTagClear(cmsCore::request($name, 'html', $default)); break;
                case 'flag':    $value = cmsCore::request($name, 'int', 0); break;
                case 'list':    $value = (is_array($_POST[$name]) ? cmsCore::request($name, 'array_str', $default) : cmsCore::request($name, 'str', $default)); break;
                case 'list_function': $value = cmsCore::request($name, 'str', $default); break;
                case 'list_db': $value = (is_array($_POST[$name]) ? cmsCore::request($name, 'array_str', $default) : cmsCore::request($name, 'str', $default)); break;

            }

            $cfg[$name] = $value;

        }

        $inCore->saveModuleConfig($id, $cfg);

        if (!$is_ajax){
            cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
        }

        cmsCore::redirectBack();

    }

//============================================================================//
//============================================================================//

    if ($do == 'list'){

        $toolmenu[] = array('icon'=>'new.gif', 'title'=>$_LANG['AD_MODULE_ADD'], 'link'=>'?view=modules&do=add');
        $toolmenu[] = array('icon'=>'install.gif', 'title'=>$_LANG['AD_MODULES_SETUP'], 'link'=>'?view=install&do=module');
        $toolmenu[] = array('icon'=>'edit.gif', 'title'=>$_LANG['AD_EDIT_SELECTED'], 'link'=>"javascript:checkSel('?view=modules&do=edit&multiple=1');");
        $toolmenu[] = array('icon'=>'delete.gif', 'title'=>$_LANG['AD_DELETE_SELECTED'], 'link'=>"javascript:checkSel('?view=modules&do=delete&multiple=1');");
        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=modules&do=show&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=modules&do=hide&multiple=1');");
        $toolmenu[] = array('icon'=>'autoorder.gif', 'title'=>$_LANG['AD_MODULE_ORDER'], 'link'=>'?view=modules&do=autoorder');
        $toolmenu[] = array('icon'=>'reorder.gif', 'title'=>$_LANG['AD_SAVE_ORDER'], 'link'=>"javascript:checkSel('?view=modules&do=saveorder');");
        $toolmenu[] = array('icon'=>'help.gif', 'title'=>$_LANG['AD_HELP'], 'link'=>'?view=help&topic=modules');

		cpToolMenu($toolmenu);

		$fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
        $fields[] = array(
            'title'=>$_LANG['AD_TITLE'],
            'field'=>array('title','titles'), 'width'=>'',
            'link'=>'?view=modules&do=edit&id=%id%',
            'prc'=>  function ($i){
                $i['titles'] = cmsCore::yamlToArray($i['titles']);
                // переопределяем название пункта меню в зависимости от языка
                if(!empty($i['titles'][cmsConfig::getConfig('lang')])){
                    $i['title'] = $i['titles'][cmsConfig::getConfig('lang')];
                }
                return $i['title'];
            }
        );
		$fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'name', 'width'=>'220', 'filter'=>'15');
        $fields[] = array('title'=>$_LANG['AD_VERSION'], 'field'=>'version', 'width'=>'55');
		$fields[] = array('title'=>$_LANG['AD_AUTHOR'], 'field'=>'author', 'width'=>'110');
		$fields[] = array('title'=>$_LANG['SHOW'], 'field'=>'published', 'width'=>'65');
		$fields[] = array('title'=>$_LANG['AD_ORDER'], 'field'=>'ordering', 'width'=>'75');
		$fields[] = array('title'=>$_LANG['AD_POSITION'], 'field'=>'position', 'width'=>'70', 'filter'=>'10', 'filterlist'=>cpGetList('positions'));

        $actions[] = array('title'=>$_LANG['AD_CONFIG'], 'icon'=>'config.gif', 'link'=>'?view=modules&do=config&id=%id%', 'condition'=>'cpModuleHasConfig');
        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=modules&do=edit&id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_MODULE_DELETE'], 'link'=>'?view=modules&do=delete&id=%id%');

		cpListTable('cms_modules', $fields, $actions, '', 'published DESC, position, ordering ASC');

	}

//============================================================================//
//============================================================================//

	if ($do == 'autoorder'){

		$rs = $inDB->query("SELECT id, position FROM cms_modules ORDER BY position") ;

		if ($inDB->num_rows($rs)){
			$ord = 1;
			while ($item = $inDB->fetch_assoc($rs)){
                if(isset($latest_pos)){
                    if($latest_pos != $item['position']){
                        $ord = 1;
                    }
                }
				$inDB->query("UPDATE cms_modules SET ordering = ".$ord." WHERE id=".$item['id']) ;
				$ord += 1;
                $latest_pos = $item['position'];
			}
		}

		cmsCore::redirect('index.php?view=modules');
	}

//============================================================================//
//============================================================================//

	if ($do == 'move_up'){
		if ($id >= 0){ dbMoveUp('cms_modules', $id, $co); }
		cmsCore::redirectBack();
	}

	if ($do == 'move_down'){
		if ($id >= 0){ dbMoveDown('cms_modules', $id, $co); }
		cmsCore::redirectBack();
	}

//============================================================================//
//============================================================================//

	if ($do == 'saveorder'){
		if(isset($_REQUEST['ordering'])) {
			$ord = $_REQUEST['ordering'];
			$ids = $_REQUEST['ids'];

			foreach ($ord as $id=>$ordering){
				$inDB->query("UPDATE cms_modules SET ordering = ".(int)$ordering." WHERE id = ".(int)$ids[$id]);
			}
			cmsCore::redirect('index.php?view=modules');
		}
	}

//============================================================================//
//============================================================================//

	if ($do == 'show'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbShow('cms_modules', $id);  }
			echo '1'; exit;
		} else {
			dbShowList('cms_modules', $_REQUEST['item']);
			cmsCore::redirectBack();
		}

	}

	if ($do == 'hide'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbHide('cms_modules', $id);  }
			echo '1'; exit;
		} else {
			dbHideList('cms_modules', $_REQUEST['item']);
			cmsCore::redirectBack();
		}
	}

	if ($do == 'delete'){
		if (!isset($_REQUEST['item'])){
            $inCore->removeModule($id);
		} else {
            $inCore->removeModule(cmsCore::request('item', 'array_int', array()));
		}
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
		cmsCore::redirect('index.php?view=modules');
	}

//============================================================================//
//============================================================================//

	if ($do == 'update'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $id             = cmsCore::request('id', 'int', 0);
        $name           = cmsCore::request('name', 'str', '');
        $title          = cmsCore::request('title', 'str', '');
        $titles         = cmsCore::arrayToYaml(cmsCore::request('titles', 'array_str', array()));
        $position       = cmsCore::request('position', 'str', '');
        $showtitle      = cmsCore::request('showtitle', 'int', 0);
        $content    	= $inDB->escape_string(cmsCore::request('content', 'html', ''));
        $published      = cmsCore::request('published', 'int', 0);
        $css_prefix     = cmsCore::request('css_prefix', 'str', '');
        $is_strict_bind = cmsCore::request('is_strict_bind', 'int', 0);
        $is_strict_bind_hidden = cmsCore::request('is_strict_bind_hidden', 'int', 0);

        $is_public      = cmsCore::request('is_public', 'int', '');
        if (!$is_public){
            $access_list = cmsCore::arrayToYaml(cmsCore::request('allow_group', 'array_int', array()));
        }

        $template       = cmsCore::request('template', 'str', '');
        $cache          = cmsCore::request('cache', 'int', 0);
        $cachetime      = cmsCore::request('cachetime', 'int', 0);
        $cacheint       = cmsCore::request('cacheint', 'str', '');

        $sql = "UPDATE cms_modules
                SET name='$name',
                    title='$title',
                    titles='$titles',
                    position='$position',
                    template='$template',
                    showtitle=$showtitle,";

                if ($content){
                    $sql .= "content='$content',";
                }

        $sql .=	"
                    published=$published,
                    css_prefix='$css_prefix',
                    access_list='$access_list',
                    hidden_menu_ids='',
                    cachetime = '$cachetime',
                    cacheint = '$cacheint',
                    cache = '$cache',
                    is_strict_bind = '$is_strict_bind',
                    is_strict_bind_hidden = '$is_strict_bind_hidden'
                WHERE id = '$id'
                LIMIT 1";
        $inDB->query($sql) ;

        $sql = "DELETE FROM cms_modules_bind WHERE module_id = $id";
        $inDB->query($sql);

        if (cmsCore::request('show_all', 'int', 0)){

            $sql = "INSERT INTO cms_modules_bind (module_id, menu_id, position)
                    VALUES ($id, 0, '{$position}')";
            $inDB->query($sql);

            $hidden_menu_ids = cmsCore::request('hidden_menu_ids', 'array_int', array());
            if($hidden_menu_ids){
                $hidden_menu_ids = cmsCore::arrayToYaml($hidden_menu_ids);
                $inDB->query("UPDATE cms_modules SET hidden_menu_ids='$hidden_menu_ids' WHERE id = '$id' LIMIT 1");
            }

        } else {

            $showin  = cmsCore::request('showin', 'array_int', array());
            $showpos = cmsCore::request('showpos', 'array_str', array());
            if ($showin){
                foreach ($showin as $key=>$value){
                    $sql = "INSERT INTO cms_modules_bind (module_id, menu_id, position)
                            VALUES ($id, $value, '{$showpos[$value]}')";
                    $inDB->query($sql);
                }
            }

        }

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'] , 'success');

        if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
            cmsCore::redirect('index.php?view=modules');
        } else {
            cmsCore::redirect('index.php?view=modules&do=edit');
        }

	}

//============================================================================//
//============================================================================//

	if ($do == 'submit'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$sql        = "SELECT ordering as max_o FROM cms_menu ORDER BY ordering DESC LIMIT 1";
		$result     = $inDB->query($sql) ;
		$row        = $inDB->fetch_assoc($result);
		$maxorder   = $row['max_o'] + 1;

        $name           = cmsCore::request('name', 'str', '');
        $title          = cmsCore::request('title', 'str', '');
        $titles         = cmsCore::arrayToYaml(cmsCore::request('titles', 'array_str', array()));
        $position       = cmsCore::request('position', 'str', '');
        $showtitle      = cmsCore::request('showtitle', 'int', 0);
		$content    	= $inDB->escape_string(cmsCore::request('content', 'html', ''));
        $published      = cmsCore::request('published', 'int', 0);
        $css_prefix     = cmsCore::request('css_prefix', 'str', '');

		$is_public = cmsCore::request('is_public', 'int', '');
		if (!$is_public){
			$access_list = cmsCore::arrayToYaml(cmsCore::request('allow_group', 'array_int', array()));
		}

        $template       = cmsCore::request('template', 'str', '');
        $cache          = cmsCore::request('cache', 'int', 0);
        $cachetime      = cmsCore::request('cachetime', 'int', 0);
        $cacheint       = cmsCore::request('cacheint', 'str', '');
		$operate        = cmsCore::request('operate', 'str', '');

        $is_strict_bind = cmsCore::request('is_strict_bind', 'int', 0);
        $is_strict_bind_hidden = cmsCore::request('is_strict_bind_hidden', 'int', 0);

		if ($operate == 'user'){ //USER MODULE
			$sql = "INSERT INTO cms_modules (position, name, title, titles, is_external, content, ordering, showtitle, published, user, original, css_prefix, access_list, template, is_strict_bind, is_strict_bind_hidden)
					VALUES ('$position', '$name', '$title', '$titles', 0, '$content', '$maxorder', '$showtitle', '$published', 1, 1, '$css_prefix', '$access_list', '$template', '$is_strict_bind', '$is_strict_bind_hidden')";
			$inDB->query($sql) ;
		}

		if ($operate == 'clone'){ //DUPLICATE MODULE

			$mod_id     = cmsCore::request('clone_id', 'int', 0);

			$sql        = "SELECT * FROM cms_modules WHERE id = $mod_id LIMIT 1";
			$result     = $inDB->query($sql) ;
			$original   = $inDB->escape_string($inDB->fetch_assoc($result));
            $is_original = cmsCore::request('del_orig', 'int', 0) ? 1 : 0;

			$sql = "INSERT INTO cms_modules (position, name, title, titles, is_external,
                                             content, ordering, showtitle, published,
                                             original, user, config, css_prefix, template,
                                             access_list, is_strict_bind, is_strict_bind_hidden,
                                             cache, cachetime, cacheint, version)
					VALUES (
							'{$position}',
							'{$original['name']}',
							'{$title}',
							'{$titles}',
							'{$original['is_external']}',
							'{$original['content']}',
							'{$maxorder}',
							'{$showtitle}',
							'{$published}',
							'{$is_original}',
							'{$original['user']}',
							'{$original['config']}',
							'$css_prefix',
                            '{$template}',
                            '{$access_list}',
                            '{$is_strict_bind}',
                            '{$is_strict_bind_hidden}',
                            '{$cache}', '{$cachetime}', '{$cacheint}', '{$original['version']}'
                            )";
			$inDB->query($sql);

			if (cmsCore::request('del_orig', 'int', 0)){
				$sql = "DELETE FROM cms_modules WHERE id = $mod_id";
				$inDB->query($sql) ;
			}
		}

		$lastid  = $inDB->get_last_id('cms_modules');

		if (cmsCore::request('show_all', 'int', 0)){

			$sql = "INSERT INTO cms_modules_bind (module_id, menu_id, position)
					VALUES ($lastid, 0, '{$position}')";
			$inDB->query($sql) ;

            $hidden_menu_ids = cmsCore::request('hidden_menu_ids', 'array_int', array());
            if($hidden_menu_ids){
                $hidden_menu_ids = cmsCore::arrayToYaml($hidden_menu_ids);
                $inDB->query("UPDATE cms_modules SET hidden_menu_ids='$hidden_menu_ids' WHERE id = '$lastid' LIMIT 1");
            }

		} else {
            $showin  = cmsCore::request('showin', 'array_int', array());
            $showpos = cmsCore::request('showpos', 'array_str', array());
			if ($showin){
				foreach ($showin as $key=>$value){
					$sql = "INSERT INTO cms_modules_bind (module_id, menu_id, position)
							VALUES ($lastid, $value, '{$showpos[$value]}')";
					$inDB->query($sql) ;
				}
			}
		}

		cmsCore::addSessionMessage($_LANG['AD_MODULE_ADD_SITE'] , 'success');
		cmsCore::redirect('index.php?view=modules');

	}

//============================================================================//
//============================================================================//

   if ($do == 'add' || $do == 'edit'){

    require('../includes/jwtabs.php');
    $GLOBALS['cp_page_head'][] = jwHeader();

    $langs = cmsCore::getDirsList('/languages');

    if ($do=='add'){
        cpAddPathway($_LANG['AD_MODULE_ADD']);
        echo '<h3>'.$_LANG['AD_MODULE_ADD'].'</h3>';
        $show_all = false;
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

        $mod = $inDB->get_fields('cms_modules', "id = '$item_id'", '*');
        if(!$mod){ cmsCore::error404(); }
        $mod['hidden_menu_ids'] = cmsCore::yamlToArray($mod['hidden_menu_ids']);
        $mod['titles'] = cmsCore::yamlToArray($mod['titles']);

        $sql = "SELECT id FROM cms_modules_bind WHERE module_id = $id AND menu_id = 0 LIMIT 1";
        $result = $inDB->query($sql) ;
        if($inDB->num_rows($result)) { $show_all = true; } else { $show_all = false; }

        echo '<h3>'.$_LANG['AD_EDIT_MODULE'].$ostatok.'</h3>';
        cpAddPathway($mod['name']);

    }

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'javascript:history.go(-1);');

    if(@$mod['is_external']){
        $php_file = 'modules/'.$mod['content'].'/backend.php';
        $xml_file = 'modules/'.$mod['content'].'/backend.xml';
        if (file_exists($php_file) || file_exists($xml_file)){
            $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['CONFIG_MODULE'], 'link'=>'?view=modules&do=config&id='.$mod['id']);
        }
    }

    cpToolMenu($toolmenu);

	?>
    <form id="addform" name="addform" method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <input type="hidden" name="view" value="modules" />

        <table class="proptable" width="100%" cellpadding="15" cellspacing="2">
            <tr>

                <!-- главная ячейка -->
                <td valign="top">

                    <div><strong><?php echo $_LANG['AD_MODULE_TITLE'];?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_VIEW_IN_SITE'];?></span></div>
                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td><input name="title" type="text" id="title" style="width:100%" value="<?php echo htmlspecialchars($mod['title']);?>" /></td>
                                <td style="width:15px;padding-left:10px;padding-right:0px;">
                                    <input type="checkbox" title="<?php echo $_LANG['AD_VIEW_TITLE'];?>" name="showtitle" <?php if ($mod['showtitle'] || $do=='add') { echo 'checked="checked"'; } ?> value="1">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php if(count($langs)>1) { ?>
                    <div><strong><?php echo $_LANG['AD_LANG_TITLES']; ?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_LANG_TITLES_HINT']; ?></span></div>
                    <?php foreach ($langs as $lang) { ?>

                    <div><strong><?php echo $lang; ?>:</strong> <input name="titles[<?php echo $lang; ?>]" type="text" style="width:97%" value="<?php echo htmlspecialchars(@$mod['titles'][$lang]);?>" placeholder="<?php echo $_LANG['AD_HINT_DEFAULT']; ?>" /></div>
                    <?php } ?>
                    <?php } ?>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:10px;">
                        <tr>
                            <td valign="top">
                                <div>
                                    <strong><?php echo $_LANG['AD_MODULE_NAME'];?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_SHOW_ADMIN'];?></span>
                                </div>
                                <div>
                                    <?php if (!isset($mod['user']) || @$mod['user']==1) { ?>
                                        <input name="name" type="text" id="name" style="width:99%" value="<?php echo htmlspecialchars($mod['name']);?>" />
                                    <?php } else { ?>
                                        <input name="" type="text" id="name" style="width:99%" value="<?php echo @$mod['name'];?>" disabled="disabled" />
                                        <input name="name" type="hidden" value="<?php echo htmlspecialchars($mod['name']);?>" />
                                    <?php } ?>
                                </div>
                            </td>
                            <td valign="top" width="160" style="padding-left:10px;">
                                <div>
                                    <strong><?php echo $_LANG['AD_PREFIX_CSS'] ;?></strong>
                                </div>
                                <div>
                                    <input name="css_prefix" type="text" id="css_prefix" value="<?php echo @$mod['css_prefix'];?>" style="width:154px" />
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top:8px">
                        <strong><?php echo $_LANG['AD_DEFOLT_VIEW'];?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_POSITION_MUST_BE'];?></span>
                    </div>
                    <div>
                        <?php
                            $pos = cpModulePositions(cmsConfig::getConfig('template'));
                        ?>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:5px;">
                            <tr>
                                <td valign="top">
                                    <select name="position" id="position" style="width:100%">
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
                                </td>
                                <?php if(file_exists(PATH.'/templates/'.TEMPLATE.'/positions.jpg')){ ?>
                                <td valign="top" width="160" style="padding-left:10px;">
                                    <script>
                                    $(function() {
                                        $('#pos').dialog({modal: true, autoOpen: false, closeText: LANG_CLOSE, width: 'auto'});
                                    });
                                    </script>
                                    <a onclick="$('#pos').dialog('open');return false;" href="#" class="ajaxlink"><?php echo $_LANG['AD_SEE_VISUALLY']; ?></a>
                                    <div id="pos" title="<?php echo $_LANG['AD_TPL_POS']; ?>"><img src="/templates/<?php echo TEMPLATE; ?>/positions.jpg" alt="<?php echo $_LANG['AD_TPL_POS']; ?>" /></div>
                                </td>
                                <?php } ?>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-top:15px">
                        <strong><?php echo $_LANG['AD_MODULE_TEMPLATE'];?></strong> <span class="hinttext">&mdash; <?php echo $_LANG['AD_FOLDER_MODULES'];?></span>
                    </div>
                    <div>
                        <?php
                            $tpls = cmsAdmin::getModuleTemplates();
                        ?>
                        <select name="template" id="template" style="width:100%">
                            <?php
                                foreach($tpls as $tpl){
                                    $selected = ($mod['template']==$tpl || (!$mod['template'] && $tpl=='module.tpl' )) ? 'selected="selected"' : '';
                                    echo '<option value="'.$tpl.'" '.$selected.'>'.$tpl.'</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <?php if ($do=='add'){ ?>
                    <div style="margin-top:15px">
                        <strong><?php echo $_LANG['AD_MODULE_TYPE'];?></strong>
                    </div>
                    <div>
                        <select name="operate" id="operate" onchange="checkDiv()" style="width:100%">
                            <option value="user" selected="selected"><?php echo $_LANG['AD_MODULE_TYPE_NEW'];?></option>
                            <option value="clone"><?php echo $_LANG['AD_MODULE_TYPE_COPY'];?></option>
                        </select>
                    </div>
                    <?php } ?>

                    <?php if(!isset($mod['user']) || $mod['user']==1 || $do=='add'){ ?>
                        <div id="user_div">
                            <div style="margin-top:15px">
                                <strong><?php echo $_LANG['AD_MODULE_CONTENT'];?></strong>
                            </div>
                            <div><?php insertPanel(); ?></div>
                            <div>
                                <?php $inCore->insertEditor('content', $mod['content'], '250', '100%'); ?>
                            </div>
                        </div>
                    <?php } ?>

                <div id="clone_div" style="display:none;">
                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['AD_MODULE_COPY'];?></strong>
                        </div>
                        <div>
                            <select name="clone_id" id="clone_id" style="width:100%">
                                <?php
                                    echo $inCore->getListItems('cms_modules');
                                ?>
                            </select>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:6px">
                                <tr>
                                    <td width="20"><input type="checkbox" name="del_orig" id="del_orig" value="1" /></td>
                                    <td><label for="del_orig"><?php echo $_LANG['AD_ORIGINAL_MODULE_DELETE'];?></label></td>
                                </tr>
                            </table>
                        </div>
                </div>

                </td>

                <!-- боковая ячейка -->
                <td width="300" valign="top" style="background:#ECECEC;">

                    <?php ob_start(); ?>

                    {tab=<?php echo $_LANG['AD_TAB_PUBLISH'];?>}

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="published" id="published" value="1" <?php if ($mod['published'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="published"><strong><?php echo $_LANG['AD_MODULE_PUBLIC'];?></strong></label></td>
                        </tr>
                        <tr>
                            <td width="20"><input name="show_all" id="show_all" type="checkbox" value="1" onclick="checkGroupList()" <?php if ($show_all) { echo 'checked'; } ?> /></td>
                            <td><label for="show_all"><strong><?php echo $_LANG['AD_VIEW_ALL_PAGES'];?></strong></label></td>
                        </tr>
                    </table>

                    <?php
                        if ($do=='edit'){
                            $bind_sql = "SELECT * FROM cms_modules_bind WHERE module_id = " . $mod['id'];
                            $bind_res = $inDB->query($bind_sql);
                            $bind     = array();
                            $bind_pos = array();
                            while ($r = $inDB->fetch_assoc($bind_res)){
                                $bind[] = $r['menu_id'];
                                $bind_pos[$r['menu_id']] = $r['position'];
                            }
                        }

                        $menu_sql = "SELECT * FROM cms_menu ORDER BY NSLeft, ordering";
                        $menu_res = $inDB->query($menu_sql) ;

                        $menu_items = array();

                        if ($inDB->num_rows($menu_res)){
                            while ($item = $inDB->fetch_assoc($menu_res)){
                                if ($do=='edit'){
                                    if (in_array($item['id'], $bind)){
                                        $item['selected'] = true;
                                        $item['position'] = $bind_pos[$item['id']];
                                    }
                                }
                                $item['titles'] = cmsCore::yamlToArray($item['titles']);
                                // переопределяем название пункта меню в зависимости от языка
                                if(!empty($item['titles'][cmsConfig::getConfig('lang')])){
                                    $item['title'] = $item['titles'][cmsConfig::getConfig('lang')];
                                }
								$item['title'] = str_replace($_LANG['AD_ROOT_PAGES'], $_LANG['AD_MAIN'] , $item['title']);
                                $menu_items[] = $item;
                            }
                        }

                    ?>

                    <div id="grp">

                        <div style="margin-top:13px">
                            <strong class="show_list"><?php echo $_LANG['AD_WHERE_MODULE_VIEW'];?></strong>
                            <strong class="hide_list"><?php echo $_LANG['AD_WHERE_MODULE_NOT_VIEW'];?></strong>
                        </div>

                        <div style="height:300px;overflow: auto;border: solid 1px #999; padding:5px 10px; background: #FFF;">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
                            <tr>
                                <td colspan="2" height="25"><strong><?php echo $_LANG['AD_MENU'];?></strong></td>
                                <td class="show_list" align="center" width="50"><strong><?php echo $_LANG['AD_POSITION'];?></strong></td>
                            </tr>
                            <?php foreach($menu_items as $i){ ?>
                            <tr class="show_list">
                                <td width="20" height="25">
                                    <input type="checkbox" name="showin[]" id="mid<?php echo $i['id']; ?>" value="<?php echo $i['id']; ?>" <?php if ($i['selected']){ ?>checked="checked"<?php } ?> onclick="$('#p<?php echo $i['id']; ?>').toggle()"/>
                                </td>
                                <td style="padding-left:<?php echo ($i['NSLevel'])*6-6; ?>px"><label for="mid<?php echo $i['id']; ?>"><?php echo $i['title']; ?></label></td>
                                <td align="center">
                                    <select id="p<?php echo $i['id']; ?>" name="showpos[<?php echo $i['id']; ?>]" style="<?php if (!$i['selected']) { ?>display:none<?php } ?>">
                                        <?php foreach($pos as $position){ ?>
                                            <option value="<?php echo $position; ?>" <?php if ($i['position']==$position){ ?>selected="selected"<?php } ?>><?php echo $position; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php foreach($menu_items as $it){ ?>
                            <tr class="hide_list">
                                <td width="20" height="25">
                                    <input type="checkbox" name="hidden_menu_ids[]" id="hmid<?php echo $it['id']; ?>" value="<?php echo $it['id']; ?>" <?php if (in_array($it['id'], $mod['hidden_menu_ids'])){ ?>checked="checked"<?php } ?> />
                                </td>
                                <td style="padding-left:<?php echo ($it['NSLevel'])*6-6; ?>px"><label for="hmid<?php echo $it['id']; ?>"><?php echo $it['title']; ?></label></td>
                            </tr>
                            <?php } ?>
                        </table>
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist show_list">
                            <tr>
                                <td width="20"><input type="checkbox" name="is_strict_bind" id="is_strict_bind" value="1" <?php if ($mod['is_strict_bind']) { echo 'checked="checked"'; } ?>/></td>
                                <td><label for="is_strict_bind"><strong><?php echo $_LANG['AD_DONT_VIEW']; ?></strong></label></td>
                            </tr>
                        </table>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist hide_list">
                            <tr>
                                <td width="20"><input type="checkbox" name="is_strict_bind_hidden" id="is_strict_bind_hidden" value="1" <?php if ($mod['is_strict_bind_hidden']) { echo 'checked="checked"'; } ?>/></td>
                                <td><label for="is_strict_bind_hidden"><strong><?php echo $_LANG['AD_EXCEPT_NESTED']; ?></strong></label></td>
                            </tr>
                        </table>

                    </div>

					<?php if(($mod['is_external'] && $do=='edit') || $do=='add') { ?>

                    {tab=<?php echo $_LANG['AD_MODULE_CACHE'] ; ?>}

                        <div style="margin-top:4px">
                            <strong><?php echo $_LANG['AD_DO_MODULE_CACHE']; ?></strong>
                        </div>
                        <div>
                            <select name="cache" id="cache" style="width:100%">
                                <option value="0" <?php if (@!$mod['cache']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['NO']; ?></option>
                                <option value="1" <?php if (@$mod['cache']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['YES']; ?></option>
                            </select>
                        </div>

                        <div style="margin-top:15px">
                            <strong><?php echo $_LANG['AD_MODULE_CACHE_PERIOD']; ?></strong>
                        </div>
                        <div>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:5px;">
                                <tr>
                                    <td valign="top"  width="100">
                                        <input name="cachetime" type="text" id="int_1" style="width:99%" value="<?php echo @(int)$mod['cachetime']?>"/>
                                    </td>
                                    <td valign="top" style="padding-left:5px">
                                        <select name="cacheint" id="int_2" style="width:100%">
                                            <option value="MINUTE"  <?php if(@mb_strstr($mod['cacheint'], 'MINUTE')) { echo 'selected="selected"'; } ?>><?php echo cmsCore::spellCount((int)@$mod['cachetime'], $_LANG['MINUTE1'], $_LANG['MINUTE2'], $_LANG['MINUTE10'], false); ?></option>
                                            <option value="HOUR"  <?php if(@mb_strstr($mod['cacheint'], 'HOUR')) { echo 'selected="selected"'; } ?>><?php echo cmsCore::spellCount((int)@$mod['cachetime'], $_LANG['HOUR1'], $_LANG['HOUR2'], $_LANG['HOUR10'], false); ?></option>
                                            <option value="DAY" <?php if(@mb_strstr($mod['cacheint'], 'DAY')) { echo 'selected="selected"'; } ?>><?php echo cmsCore::spellCount((int)@$mod['cachetime'], $_LANG['DAY1'], $_LANG['DAY2'], $_LANG['DAY10'], false); ?></option>
                                            <option value="MONTH" <?php if(@mb_strstr($mod['cacheint'], 'MONTH')) { echo 'selected="selected"'; } ?>><?php echo cmsCore::spellCount((int)@$mod['cachetime'], $_LANG['MONTH1'], $_LANG['MONTH2'], $_LANG['MONTH10'], false); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin-top:15px">
                            <?php
                                if ($do=='edit'){
                                    if ($inCore->isCached('module', $mod['id'], $mod['cachetime'], $mod['cacheint'])){
                                        $t = 'module'.$mod['id'];
                                        $cfile = PATH.'/cache/'.md5($t).'.html';
                                        if (file_exists($cfile)){
                                            $kb = round(filesize($cfile)/1024, 2);
                                            echo '<a href="index.php?view=cache&do=delcache&target=module&id='.$mod['id'].'">'.$_LANG['AD_MODULE_CACHE_DELETE'].'</a> ('.$kb.$_LANG['SIZE_KB'].')';
                                        }
                                    } else {
                                        echo '<span style="color:gray">'.$_LANG['AD_NO_CACHE'].'</span>';
                                    }
                                }
                            ?>
                        </div>
					<?php } ?>

                    {tab=<?php echo $_LANG['AD_TAB_ACCESS']; ?>}
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
                            <?php echo $_LANG['AD_IF_CHECKED']; ?>
                        </span>
                    </div>

                    <div style="margin-top:10px;padding:5px;padding-right:0px;">
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

                    {/tabs}

                    <?php echo jwTabs(ob_get_clean()); ?>

                </td>

            </tr>
        </table>
        <p>
            <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.history.back();" />
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

//============================================================================//
//============================================================================//

}