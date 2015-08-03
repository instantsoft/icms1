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

defined('VALID_CMS_ADMIN') or die();

function printLangPanel($target, $target_id, $field) {

    if(!$target_id){
        return;
    }

    $langs = cmsCore::getDirsList('/languages');

    if(count($langs)>1) {

        foreach ($langs as $lang) { ?>

            &nbsp;<a class="ajaxlink editfieldlang" href="#" onclick="return editFieldLang('<?php echo $lang;?>','<?php echo $target;?>','<?php echo $target_id;?>','<?php echo $field; ?>', this);"><strong><?php echo mb_strtoupper($lang);?></strong></a>&nbsp;

        <?php }

    }

}

function cpAccessDenied(){
	cmsCore::redirect('/admin/index.php?view=noaccess');
}

function cpWarning($text){
    global $_LANG;
    return '<div id="warning"><span>'.$_LANG['ATTENTION'].': </span>'.$text.'</div>';
}

function cpWritable($file){ //relative path with starting "/"
	if (is_writable(PATH.$file)){
		return true;
	} else {
		return @chmod(PATH.$file, 0777);
	}
}

function cpCheckWritable($file, $type='file'){
    global $_LANG;
	if (!cpWritable($file)){
		if ($type=='file'){
			echo cpWarning(sprintf($_LANG['FILE_NOT_WRITABLE'], $file));
		} else {
			echo cpWarning(sprintf($_LANG['DIR_NOT_WRITABLE'], $file));
		}
	}
}

function cpWhoOnline(){
    global $_LANG;
	$people = cmsUser::getOnlineCount();

	$html .= '<div>';

		$html .= '<table width="100%" cellpadding="2" cellspacing="2"><tr>';

			$html .= '<td width="24" valign="top">';
				$html .= '<img src="images/user.gif"/>';
			$html .= '</td>';

			$html .= '<td width="" valign="top">';
				$html .= '<div><strong>'.$_LANG['AD_FROM_USERS'].': </strong>'.$people['users'].'</div>';
				$html .= '<div><strong>'.$_LANG['AD_FROM_GUESTS'].': </strong>'.$people['guests'].'</div>';
			$html .= '</td>';

		$html .= '</tr></table>';
	$html .= '</div>';

	return $html;

}

/////////////////////////// PAGE GENERATION ////////////////////////////////////////////////////////////////
function cpHead(){

    global $_LANG;

    $inPage = cmsPage::getInstance();

    $inPage->title = !empty($GLOBALS['cp_page_title']) ?
                $GLOBALS['cp_page_title'].' - '.$_LANG['AD_ADMIN_PANEL'].' v '.CORE_VERSION :
                $_LANG['AD_ADMIN_PANEL'];

    array_unshift($inPage->page_head, '<script type="text/javascript" src="js/common.js"></script>');

    $inPage->addHeadJsLang(array('AD_NO_SELECT_OBJECTS','AD_SWITCH_EDITOR','CANCEL','CONTINUE','CLOSE','ATTENTION'));

    $inPage->addHeadJS('includes/jquery/colorbox/jquery.colorbox.js');
    $inPage->addHeadCSS('includes/jquery/colorbox/colorbox.css');
    $inPage->addHeadJsLang(array('CBOX_IMAGE','CBOX_FROM','CBOX_PREVIOUS','CBOX_NEXT','CBOX_CLOSE','CBOX_XHR_ERROR','CBOX_IMG_ERROR', 'CBOX_SLIDESHOWSTOP', 'CBOX_SLIDESHOWSTART'));

    if (!empty($GLOBALS['cp_jquery'])){
        array_unshift($inPage->page_head, '<script type="text/javascript" src="'.$GLOBALS['cp_jquery'].'"></script>');
    } else {
        array_unshift($inPage->page_head, '<script type="text/javascript" src="/includes/jquery/jquery.js"></script>');
    }

	foreach($GLOBALS['cp_page_head'] as $key=>$value) {
        $inPage->addHead($value);
		unset ($GLOBALS['cp_page_head'][$key]);
	}

    $inPage->printHead();

	return;

}

function cpMenu(){
    global $_LANG;
	global $adminAccess;

    $inCore = cmsCore::getInstance();
    $inUser = cmsUser::getInstance();

    ob_start(); ?>
	<div id="hmenu">
		<ul id="nav">
			<?php if (cmsUser::isAdminCan('admin/menu', $adminAccess)){ ?>
			<li>
				<a href="index.php?view=menu" class="menu"><?php echo $_LANG['AD_MENU']; ?></a>
				<ul>
					<li><a class="add" href="index.php?view=menu&do=add"><?php echo $_LANG['AD_MENU_POINT_ADD']; ?></a></li>
					<li><a class="add" href="index.php?view=menu&do=addmenu"><?php echo $_LANG['AD_MENU_ADD']; ?></a></li>
					<li><a class="list" href="index.php?view=menu"><?php echo $_LANG['AD_SHOW_ALL']; ?></a></li>
				</ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/modules', $adminAccess)){ ?>
			<li>
				<a href="index.php?view=modules" class="modules"><?php echo $_LANG['AD_MODULES']; ?></a>
				<ul>
                	<li><a class="install" href="index.php?view=install&do=module"><?php echo $_LANG['AD_MODULES_SETUP']; ?></a></li>
					<li><a class="add" href="index.php?view=modules&do=add"><?php echo $_LANG['AD_MODULE_ADD']; ?></a></li>
					<li><a class="list" href="index.php?view=modules"><?php echo $_LANG['AD_SHOW_ALL']; ?></a></li>
				</ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/content', $adminAccess)){ ?>
			<li>
				<a class="content" href="index.php?view=tree"><?php echo $_LANG['AD_ARTICLE_SITE']; ?></a>
				<ul>
					<li><a class="content" href="index.php?view=tree"><?php echo $_LANG['AD_ARTICLES']; ?></a></li>
					<li><a class="arhive" href="index.php?view=arhive"><?php echo $_LANG['AD_ARTICLES_ARCHIVE']; ?></a></li>
					<li><a class="add" href="index.php?view=cats&do=add"><?php echo $_LANG['AD_CREATE_SECTION']; ?></a></li>
					<li><a class="add" href="index.php?view=content&do=add"><?php echo $_LANG['AD_CREATE_ARTICLE']; ?></a></li>
				</ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/components', $adminAccess)){ ?>
			<li>
				<a href="index.php?view=components" class="components"><?php echo $_LANG['AD_COMPONENTS']; ?></a>
				<ul>
                <li><a class="install" href="index.php?view=install&do=component"><?php echo $_LANG['AD_INSTALL_COMPONENTS']; ?></a></li>
                    <?php

                        $components   = $inCore->getAllComponents();
                        $showed_count = 0;
                        $total_count  = count($components);

                        if ($total_count){

                            foreach ($components as $com){

                                if ($com['published'] && (file_exists('components/'.$com['link'].'/backend.php')) && cmsUser::isAdminCan('admin/com_'.$com['link'], $adminAccess)){ ?>

                                    <li>
                                        <a style="margin-left:5px; background:url(/admin/images/components/<?php echo $com['link']; ?>.png) no-repeat 6px 6px;" href="index.php?view=components&do=config&link=<?php echo $com['link']; ?>">
                                            <?php echo $com['title']; ?>
                                        </a>
                                    </li>

                                <?php

                                    $showed_count++;

                                }

                            }

                        }

                        if ($total_count != $showed_count && $inUser->id == 1){

                    ?>
                        <li><a class="list" href="index.php?view=components"><?php echo $_LANG['AD_SHOW_ALL']; ?>...</a></li>
                    <?php

                        }

                    ?>

				</ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/plugins', $adminAccess)){ ?>
			<li>
				<a class="plugins"><?php echo $_LANG['AD_ADDITIONS']; ?></a>
				<ul>
                	<li><a class="install" href="index.php?view=install&do=plugin"><?php echo $_LANG['AD_INSTALL_PLUGINS']; ?></a></li>
                    <li><a href="index.php?view=plugins" class="plugins"><?php echo $_LANG['AD_PLUGINS']; ?></a></li>
                    <?php if (cmsUser::isAdminCan('admin/filters', $adminAccess)){ ?>
                        <li><a href="index.php?view=filters" class="filters"><?php echo $_LANG['AD_FILTERS']; ?></a></li>
                    <?php } ?>
				</ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/users', $adminAccess)){ ?>
			<li>
                <a href="index.php?view=users" class="users"><?php echo $_LANG['AD_USERS']; ?></a>
                <ul>
                    <li><a href="index.php?view=users" class="user"><?php echo $_LANG['AD_USERS']; ?></a></li>
                    <li><a href="index.php?view=userbanlist" class="banlist"><?php echo $_LANG['AD_BANLIST']; ?></a></li>
                    <li><a class="users" href="index.php?view=usergroups"><?php echo $_LANG['AD_USERS_GROUP']; ?></a></li>
                    <li><a class="add" href="index.php?view=users&do=add"><?php echo $_LANG['AD_USER_ADD']; ?></a></li>
                    <li><a class="add" href="index.php?view=usergroups&do=add"><?php echo $_LANG['AD_CREATE_GROUP']; ?></a></li>
                    <li><a class="config" href="index.php?view=components&do=config&link=users"><?php echo $_LANG['AD_PROFILE_SETTINGS']; ?></a></li>
                </ul>
			</li>
			<?php } ?>
			<?php if (cmsUser::isAdminCan('admin/config', $adminAccess)){ ?>
			<li>
				<a href="index.php?view=config" class="config"><?php echo $_LANG['AD_SETTINGS']; ?></a>
				<ul>
					<li><a class="config" href="index.php?view=config"><?php echo $_LANG['AD_SITE_SETTING']; ?></a></li>
					<li><a class="repairnested" href="index.php?view=repairnested"><?php echo $_LANG['AD_CHECKING_TREES']; ?></a></li>
                    <li><a class="cron" href="index.php?view=cron"><?php echo $_LANG['AD_CRON_MISSION']; ?></a></li>
                    <li><a class="phpinfo" href="index.php?view=phpinfo"><?php echo $_LANG['AD_PHP_INFO']; ?></a></li>
          			<li><a class="clearcache" href="index.php?view=clearcache"><?php echo $_LANG['AD_CLEAR_SYS_CACHE']; ?></a></li>
				</ul>
			</li>
			<?php } ?>
			<li>
				<a href="http://www.instantcms.ru/wiki" target="_blank" class="help"><?php echo $_LANG['AD_DOCS']; ?></a>
			</li>
		</ul>
	</div>

	<?php echo ob_get_clean();

	return;
}

function cpToolMenu($toolmenu_list){

	if ($toolmenu_list){
		echo '<table width="100%" cellpadding="2" border="0" class="toolmenu" style="margin:0px"><tr><td>';
		foreach($toolmenu_list as $toolmenu){

            if(!$toolmenu){
                echo '<div class="toolmenuseparator"></div>'; continue;
            }

            $class_selected = ('?'.$_SERVER['QUERY_STRING'] == $toolmenu['link']) ? 'toolmenuitem_sel' : '';
            $target = isset($toolmenu['target']) ? 'target="'.$toolmenu['target'].'"' : '';
			echo '<a class="'.$class_selected.' toolmenuitem uittip" href="'.$toolmenu['link'].'" title="'.$toolmenu['title'].'" '.$target.'><img src="images/toolmenu/'.$toolmenu['icon'].'" border="0" /></a>';
		}
		echo '</td></tr></table>';
	}

	return;
}

function cpProceedBody(){

	ob_start();

	$file = $GLOBALS['applet'] . '.php';

    if (!file_exists(PATH.'/admin/applets/'.$file)){
        cmsCore::error404();
    }

    cmsCore::loadLanguage('admin/applets/applet_'.$GLOBALS['applet']);
	include('applets/'.$file);

	call_user_func('applet_'.$GLOBALS['applet']);

	$GLOBALS['cp_page_body'] = ob_get_clean();

}

function cpBody(){
	echo $GLOBALS['cp_page_body'];
	return;
}

//////////////////////////////////////////////// PATHWAY ///////////////////////////////////////////////////////
function cpPathway($separator='&raquo;'){

    if(sizeof($GLOBALS['cp_pathway']) <= 1){
        echo '<div class="pathway"></div>';
        return;
    }

	echo '<div class="pathway">';
	foreach($GLOBALS['cp_pathway'] as $key => $value){

		echo '<a href="'.$GLOBALS['cp_pathway'][$key]['link'].'" class="pathwaylink">'.$GLOBALS['cp_pathway'][$key]['title'].'</a> ';

		if ($key<sizeof($GLOBALS['cp_pathway'])-1) {
			echo ' '.$separator.' ';
		}

	}
	echo '</div>';

}

function cpAddPathway($title, $link){
	$already = false;
    if (empty($link)) { $link = htmlspecialchars($_SERVER['REQUEST_URI']); }

	foreach($GLOBALS['cp_pathway'] as $key => $val){
	 if ($GLOBALS['cp_pathway'][$key]['title'] == $title || $GLOBALS['cp_pathway'][$key]['link'] == $link){
	 	$already = true;
	 }
	}

	if(!$already){
		$next = sizeof($GLOBALS['cp_pathway']);
		$GLOBALS['cp_pathway'][$next]['title'] = $title;
		$GLOBALS['cp_pathway'][$next]['link'] = $link;
	}

	return true;
}

function cpModulePositions($template){

	$pos = array();

	$posfile = PATH.'/templates/'.$template.'/positions.txt';

	if(file_exists($posfile)){
		$file = fopen($posfile, 'r');
		while(!feof($file)){
			$str = fgets($file);
			$str = str_replace("\n", '', $str);
			$str = str_replace("\r", '', $str);
			if (!mb_strstr($str, '#') && mb_strlen($str)>1){
				$pos[] = $str;
			}
		}
		fclose($file);
		return $pos;
	} else {
		return false;
	}

}

function cpAddParam($query, $param, $value){
	$new_query = '';
	mb_parse_str($query, $params);
	$l = 0; $added= false;
	foreach($params as $key => $val){
		$l ++;
		if ($key != $param && $key!='nofilter'){ $new_query .= $key .'='.$val; } else {	$new_query .= $key .'='.$value; $added = true;	}
		if ($l<sizeof($params)) { $new_query .= '&'; }
	}
	if (!$added) {
		if (mb_strlen($new_query)>1){ $new_query .= '&'.$param . '=' . $value; } else {$new_query .= $param . '=' . $value; }
	}
	return $new_query;
}

function cpListTable($table, $_fields, $_actions, $where='', $orderby='title'){

    global $_LANG;
    $inDB = cmsDatabase::getInstance();

	$perpage = 60;

	$sql = 'SELECT *';
	$is_actions = sizeof($_actions);

	foreach($_fields as $key => $value){
		if (isset($_fields[$key]['fdate'])){
			$sql .= ", DATE_FORMAT(".$_fields[$key]['field'].", '".$_fields[$key]['fdate']."') as `".$_fields[$key]['field']."`" ;
		}
	}

	$sql .= ' FROM '.$table;

	if(isset($_SESSION['filter_table']) && $_SESSION['filter_table']!=$table){
		unset($_SESSION['filter']);
	}

	if (cmsCore::inRequest('nofilter')){
		unset($_SESSION['filter']);
		cmsCore::redirect('/admin/index.php?'.str_replace('&nofilter', '', $_SERVER['QUERY_STRING']));
	}

	$filter = false;

	if (cmsCore::inRequest('filter')) {
		$filter = cmsCore::request('filter', 'array_str', '');
		$_SESSION['filter'] = $filter;
	} elseif (isset($_SESSION['filter'])) {
		$filter = $_SESSION['filter'];
	}

	if ($filter){
		$f = 0;
		$sql .= ' WHERE 1=1';
		foreach($filter as $key => $value){
			if($filter[$key] && $filter[$key]!=-100){
                $sql .= ' AND ';
				if (!is_numeric($filter[$key])){
                    $sql .= $key . " LIKE '%" . $filter[$key] . "%'";
				} else {
					$sql .= $key . " = '" . $filter[$key] . "'";
				}
				$f++;
			}
		}
		if (!isset($_SESSION['filter'])) { $_SESSION['filter'] = $filter; }
	}

	if (mb_strlen($where)>3) {
		if (mb_strstr($sql, 'WHERE')){ $sql .= ' AND '.$where; }
		else { $sql .= ' WHERE '.$where; }
	}

    $sort = cmsCore::request('sort', 'str', '');

	if ($sort == false){
		if ($orderby) { $sort = $orderby; } else {
			foreach($_fields as $key => $value){
				if ($_fields[$key]['field'] == 'ordering' && $sort!='NSLeft'){ $sort = 'ordering'; $so = 'asc';}
			}
		}
	}

	if ($sort) {
		$sql .= ' ORDER BY '.$sort;
		if (cmsCore::inRequest('so')) { $sql .= ' '. cmsCore::request('so', 'str', ''); }
	}

    $page = cmsCore::request('page', 'int', 1);

	$total_rs = $inDB->query($sql);
	$total = $inDB->num_rows($total_rs);

	$sql .= " LIMIT ".($page-1)*$perpage.", $perpage";

	$result = $inDB->query($sql);

	$_SESSION['filter_table'] = $table;

	if ($inDB->error()) {
		unset($_SESSION['filter']);
        cmsCore::redirect('/admin/index.php?'.$_SERVER['QUERY_STRING']);
	}

	$filters = 0; $f_html = '';
	//Find and render filters
	foreach($_fields as $key => $value){
		 if (isset($_fields[$key]['filter'])){
				$f_html .= '<td width="">'.$_fields[$key]['title'].': </td>';
				if(!isset($filter[$_fields[$key]['field']])) { $initval = ''; }
				else { $initval =  $filter[$_fields[$key]['field']]; }
				$f_html .= '<td width="">';
					$inputname = 'filter['.$_fields[$key]['field'].']';
					if(!isset($_fields[$key]['filterlist'])){
						$f_html .= '<input name="'.$inputname.'" type="text" size="'.$_fields[$key]['filter'].'" class="filter_input" value="'.$initval.'"/></td>';
					} else {
						$f_html .= cpBuildList($inputname, $_fields[$key]['filterlist'], $initval);
					}
				$f_html .= '</td>';
				$filters += 1;
                $_SERVER['QUERY_STRING'] = str_replace('filter['.$_fields[$key]['field'].']=', '', $_SERVER['QUERY_STRING']);
		 }
	}
	//draw filters
	if ($filters>0){
		echo '<div class="filter">';
		echo '<form name="filterform" action="index.php?'.$_SERVER['QUERY_STRING'].'" method="POST">';
		echo '<table width="250"><tr>';
		echo $f_html;
		echo '<td width="80"><input type="submit" class="filter_submit" value="'.$_LANG['AD_FILTER'].'" /></td>';
		if (@$f>0){
			echo '<td width="80"><input type="button" onclick="window.location.href=\'index.php?'.$_SERVER['QUERY_STRING'].'&nofilter\'" class="filter_submit" value="'.$_LANG['AD_ALL'].'" /></td>';
		}
		echo '</tr></table>';
		echo '</form>';
		echo '</div>';
	}

	if ($inDB->num_rows($result)){

		//DRAW LIST TABLE
		echo '<form name="selform" action="index.php?view='.$GLOBALS['applet'].'&do=saveorder" method="post">';
		echo '<table id="listTable" border="0" class="tablesorter" width="100%" cellpadding="0" cellspacing="0">';
			//TABLE HEADING
			echo '<thead>'."\n";
				echo '<tr>'."\n";
					echo '<th width="20" class="lt_header" align="center"><a class="lt_header_link" href="javascript:invert();" title="'.$_LANG['AD_INVERT_SELECTION'].'">#</a></th>'. "\n";
					foreach($_fields as $key => $value){
						echo '<th width="'.$_fields[$key]['width'].'" class="lt_header">';
							echo $_fields[$key]['title'];
						echo '</th>'. "\n";
					}
					if ($is_actions){
						echo '<th width="80" class="lt_header" align="center">'.$_LANG['AD_ACTIONS'].'</th>'. "\n";
					}
				echo '</tr>'."\n";
			echo '</thead><tbody>'."\n";
			//TABLE BODY
			$r = 0;
			while ($item = $inDB->fetch_assoc($result)){
				$r++;
				if ($r % 2) { $row_class = 'lt_row1'; } else { $row_class = 'lt_row2'; }
				echo '<tr id="lt_row2">'."\n";
					echo '<td class="'.$row_class.'" align="center" valign="middle"><input type="checkbox" name="item[]" value="'.$item['id'].'" /></td>'. "\n";
					foreach($_fields as $key => $value){
						if (isset($_fields[$key]['link'])){
                            $link = str_replace('%id%', $item['id'], $_fields[$key]['link']);
                            if (isset($_fields[$key]['prc'])) {
                                // функция обработки под названием $_fields[$key]['prc']
                                // какие параметры передать функции - один ключ или произвольный массив ключей
                                if(is_array($_fields[$key]['field'])){
                                    foreach ($_fields[$key]['field'] as $func_field) {
                                        $in_func_array[$func_field] = $item[$func_field];
                                    }
                                    $data = call_user_func($_fields[$key]['prc'], $in_func_array);
                                } else {
                                    $data = call_user_func($_fields[$key]['prc'], $item[$_fields[$key]['field']]);
                                }
                            } else {
                                $data = $item[$_fields[$key]['field']];
                                 if (isset($_fields[$key]['maxlen'])){
                                    if (mb_strlen($data)>$_fields[$key]['maxlen']){
                                        $data = mb_substr($data, 0, $_fields[$key]['maxlen']).'...';
                                    }
                                 }
                            }
							 //nested sets otstup
							if (isset($item['NSLevel']) && ($_fields[$key]['field']=='title' || (is_array($_fields[$key]['field']) && in_array('title', $_fields[$key]['field'])))){
								$otstup = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($item['NSLevel']-1));
								if ($item['NSLevel']-1 > 0){ $otstup .=  ' &raquo; '; }
							} else { $otstup = ''; }
                            if ($table != 'cms_components'){
                                echo '<td class="'.$row_class.'" valign="middle">'.$otstup.'<a class="lt_link" href="'.$link.'">'.$data.'</a></td>'. "\n";
                            } else {
                                $data = function_exists('cpComponentHasConfig') && cpComponentHasConfig($item['link']) ?
                                        '<a class="lt_link" href="'.$link.'">'.$data.'</a>' :
                                        $data;
                                echo '<td class="'.$row_class.'" valign="middle">
                                            <span class="lt_link" style="padding:1px; padding-left:24px; background:url(/admin/images/components/'.$item['link'].'.png) no-repeat">'.$data.'</span>
                                      </td>'. "\n";
                            }
						} else {
							if ($_fields[$key]['field'] != 'ordering'){
								if ($_fields[$key]['field'] == 'published'){
									if (isset($_fields[$key]['do'])) { $do = $_fields[$key]['do']; } else { $do = 'do'; }
									if (isset($_fields[$key]['do_suffix'])) { $dos = $_fields[$key]['do_suffix']; $ids = 'item_id'; } else { $dos = ''; $ids = 'id'; }
									if ($item['published']){
										$qs = cpAddParam($_SERVER['QUERY_STRING'], $do, 'hide'.$dos);
										$qs = cpAddParam($qs, $ids, $item['id']);
											$qs2 = cpAddParam($_SERVER['QUERY_STRING'], $do, 'show'.$dos);
											$qs2 = cpAddParam($qs2, $ids, $item['id']);
										$qs = "pub(".$item['id'].", '".$qs."', '".$qs2."', 'off', 'on');";
										echo '<td class="'.$row_class.'" valign="middle">
												<a title="'.$_LANG['HIDE'].'" class="uittip" id="publink'.$item['id'].'" href="javascript:'.$qs.'"><img id="pub'.$item['id'].'" src="images/actions/on.gif" border="0"/></a>
											 </td>'. "\n";
									} else {
										$qs = cpAddParam($_SERVER['QUERY_STRING'], $do, 'show'.$dos);
										$qs = cpAddParam($qs, $ids, $item['id']);
											$qs2 = cpAddParam($_SERVER['QUERY_STRING'], $do, 'hide'.$dos);
											$qs2 = cpAddParam($qs2, $ids, $item['id']);
										$qs = "pub(".$item['id'].", '".$qs."', '".$qs2."', 'on', 'off');";
										echo '<td class="'.$row_class.'" valign="middle">
												<a title="'.$_LANG['SHOW'].'" class="uittip" id="publink'.$item['id'].'" href="javascript:'.$qs.'"><img id="pub'.$item['id'].'" src="images/actions/off.gif" border="0"/></a>
											 </td>'. "\n";
									}
								} else {
                                    if (isset($_fields[$key]['prc'])) {
                                        // функция обработки под названием $_fields[$key]['prc']
                                        // какие параметры передать функции - один ключ или произвольный массив ключей
                                        if(is_array($_fields[$key]['field'])){
                                            foreach ($_fields[$key]['field'] as $func_field) {
                                                $in_func_array[$func_field] = $item[$func_field];
                                            }
                                            $data = call_user_func($_fields[$key]['prc'], $in_func_array);
                                        } else {
                                            $data = call_user_func($_fields[$key]['prc'], $item[$_fields[$key]['field']]);
                                        }
                                    } else {
                                        $data = $item[$_fields[$key]['field']];
                                         if (isset($_fields[$key]['maxlen'])){
                                            if (mb_strlen($data)>$_fields[$key]['maxlen']){
                                                $data = mb_substr($data, 0, $_fields[$key]['maxlen']).'...';
                                            }
                                         }
                                    }
                                     //nested sets otstup
                                    if (isset($item['NSLevel']) && ($_fields[$key]['field']=='title' || (is_array($_fields[$key]['field']) && in_array('title', $_fields[$key]['field'])))){
                                        $otstup = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($item['NSLevel']-1));
                                        if ($item['NSLevel']-1 > 0){ $otstup .=  ' &raquo; '; }
                                    } else { $otstup = ''; }
                                    echo '<td class="'.$row_class.'" valign="middle">'.$otstup.$data.'</td>'. "\n";
                               }
							} else {
                                if (isset($_fields[$key]['do'])) { $do = 'do=config&id='.(int)$_REQUEST['id'].'&'.$_fields[$key]['do']; } else { $do = 'do'; }
								if (isset($_fields[$key]['do_suffix'])) { $dos = $_fields[$key]['do_suffix']; $ids = 'item_id'; } else { $dos = ''; $ids = 'id'; }
								echo '<td class="'.$row_class.'" valign="middle">
									<a title="'.$_LANG['AD_DOWN'].'" href="?view='.$GLOBALS['applet'].'&'.$do.'=move_down&co='.$item[$_fields[$key]['field']].'&'.$ids.'='.$item['id'].'"><img src="images/actions/down.gif" border="0"/></a>';
									if ($table != 'cms_menu' && $table != 'cms_category'){
										echo '<input class="lt_input" type="text" size="4" name="ordering[]" value="'.$item['ordering'].'" />';
										echo '<input name="ids[]" type="hidden" value="'.$item['id'].'" />';
									} else {
										echo '<input class="lt_input" type="text" size="4" name="ordering[]" value="'.$item['ordering'].'" disabled/>';
									}
									echo '<a title="'.$_LANG['AD_UP'].'" href="?view='.$GLOBALS['applet'].'&'.$do.'=move_up&co='.$item[$_fields[$key]['field']].'&'.$ids.'='.$item['id'].'"><img src="images/actions/top.gif" border="0"/></a>'.
								'</td>'. "\n";
							}
						}
					}
					if ($is_actions){
						echo '<td width="110" class="'.$row_class.'" align="right" valign="middle"><div style="padding-right:8px">';
						foreach($_actions as $key => $value){
							if (isset($_actions[$key]['condition'])){
                                $print = $_actions[$key]['condition']($item);
                            } else {
                                $print = true;
                            }
							if ($print){
								$icon   = $_actions[$key]['icon'];
								$title  = $_actions[$key]['title'];
                                $link   = $_actions[$key]['link'];

                                foreach($item as $f=>$v){
                                    $link = str_replace('%'.$f.'%', $v, $link);
                                }

								if (!isset($_actions[$key]['confirm'])){
									echo '<a href="'.$link.'" class="uittip" title="'.$title.'"><img hspace="2" src="images/actions/'.$icon.'" border="0" alt="'.$title.'"/></a>';
								} else {
									echo '<a href="#" class="uittip" onclick="jsmsg(\''.$_actions[$key]['confirm'].'\', \''.$link.'\')" title="'.$title.'"><img hspace="2" src="images/actions/'.$icon.'" border="0" alt="'.$title.'"/></a>';
								}
							}
						}
						echo '</div></td>'. "\n";
					}
				echo '</tr>'."\n";
			}

		echo '</tbody></table></form>';

		echo '<script type="text/javascript">highlightTableRows("listTable","hoverRow","clickedRow");</script>';
		echo '<script type="text/javascript">activateListTable("listTable");</script>';

		$link = '?view='.$GLOBALS['applet'];

		if ($sort){
			$link .= '&sort='.$sort;
			if (cmsCore::inRequest('so')) { $link .= '&so='.cmsCore::request('so'); }
		}

        echo cmsPage::getPagebar($total, $page, $perpage, $_SERVER['PHP_SELF'].'?'.cpAddParam($_SERVER['QUERY_STRING'], 'page', '%page%'));

	} else {
		echo '<p class="cp_message">'.$_LANG['OBJECTS_NOT_FOUND'].'</p>';
	}
}

//////////////////////////////////////// LIST TABLE PROCESSORS ///////////////////////////////////////////////////////////////////

function cpForumCatById($id){

    $inDB = cmsDatabase::getInstance();

	$result = $inDB->query("SELECT title FROM cms_forum_cats WHERE id = $id") ;

	if ($inDB->num_rows($result)) {
		$cat = $inDB->fetch_assoc($result);
		return '<a href="index.php?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id='.$id.'">'.$cat['title'].'</a> ('.$id.')';
	} else { return '--'; }

}

function cpFaqCatById($id){

    $inDB = cmsDatabase::getInstance();

	$result = $inDB->query("SELECT title FROM cms_faq_cats WHERE id = $id") ;

	if ($inDB->num_rows($result)) {
		$cat = $inDB->fetch_assoc($result);
		return '<a href="index.php?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id='.$id.'">'.$cat['title'].'</a>';
	} else { return '--'; }

}

function cpCatalogCatById($id){

    $inDB = cmsDatabase::getInstance();

	$result = $inDB->query("SELECT title, parent_id FROM cms_uc_cats WHERE id = $id") ;

	if ($inDB->num_rows($result)) {
		$cat = $inDB->fetch_assoc($result);
        if ($cat['parent_id']){
            return '<a href="index.php?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id='.$id.'">'.$cat['title'].'</a> ('.$id.')';
        } else {
            return $cat['title'];
        }
	} else { return '--'; }

}

function cpBoardCatById($id){

    $inDB = cmsDatabase::getInstance();

	$result = $inDB->query("SELECT title FROM cms_board_cats WHERE id = $id") ;

	if ($inDB->num_rows($result)) {
		$cat = $inDB->fetch_assoc($result);
		return '<a href="index.php?view=components&do=config&id='.(int)$_REQUEST['id'].'&opt=edit_cat&item_id='.$id.'">'.$cat['title'].'</a> ('.$id.')';
	} else { return '--'; }

}

function cpGroupById($id){

    if(isset($GLOBALS['groups'][$id])){
        $title = $GLOBALS['groups'][$id];
    } else {
        $title = cmsUser::getGroupTitle($id);
        $GLOBALS['groups'][$id] = $title;
    }

	return '<a href="index.php?view=usergroups&do=edit&id='.$id.'">'.$title.'</a>';

}

function cpCatById($id){

    $inDB = cmsDatabase::getInstance();

	$result = $inDB->query("SELECT title, parent_id FROM cms_category WHERE id = $id") ;

	if ($inDB->num_rows($result)) {
		$cat = $inDB->fetch_assoc($result);
        if ($cat['parent_id']){
            return '<a href="index.php?view=cats&do=edit&id='.$id.'">'.$cat['title'].'</a> ('.$id.')';
        } else {
            return $cat['title'];
        }
	} else { return '--'; }

}

function cpModuleById($id){
    $inDB = cmsDatabase::getInstance();
	$sql = "SELECT content FROM cms_modules WHERE id = $id AND is_external = 1";
	$result = $inDB->query($sql);
	if ($inDB->num_rows($result)) { $mod = $inDB->fetch_assoc($result); return $mod['content']; }
	else { return false; }
}

function cpModuleTitleById($id){
    $inDB = cmsDatabase::getInstance();
	$sql = "SELECT name FROM cms_modules WHERE id = $id";
	$result = $inDB->query($sql);
	if ($inDB->num_rows($result)) { $mod = $inDB->fetch_assoc($result); return $mod['name']; }
	else { return false; }
}

function cpTemplateById($template_id){
    global $_LANG;
	if ($template_id) { return $template_id; } else { return '<span style="color:silver">'.$_LANG['AD_AS_SITE'].'</span>'; }

}

function cpUserNick($user_id=0){
    global $_LANG;
    $inDB = cmsDatabase::getInstance();
	if ($user_id){
		$sql = "SELECT nickname FROM cms_users WHERE id = $user_id";
		$result = $inDB->query($sql);
		if ($inDB->num_rows($result)) { $usr = $inDB->fetch_assoc($result); return $usr['nickname']; }
		else { return false; }
	} else {
		return '<em style="color:gray">'.$_LANG['AD_NOT_DEFINED'].'</em>';
	}
}

function cpYesNo($option){
    global $_LANG;
	if ($option) { return '<span style="color:green">'.$_LANG['YES'].'</span>'; } else { return '<span style="color:red">'.$_LANG['NO'].'</span>'; }
}

//////////////////////////////////////////////// DATABASE //////////////////////////////////////////////////////////
function dbMoveUp($table, $id, $current_ord){
    $inDB = cmsDatabase::getInstance();
    $id = (int)$id;
    $current_ord = (int)$current_ord;
	$sql = "UPDATE $table SET ordering = ordering + 1 WHERE ordering = ($current_ord-1) LIMIT 1";
	$inDB->query($sql) ;
	$sql = "UPDATE $table SET ordering = ordering - 1 WHERE id = $id LIMIT 1";
	$inDB->query($sql) ;
}
function dbMoveDown($table, $id, $current_ord){
    $inDB = cmsDatabase::getInstance();
    $id = (int)$id;
    $current_ord = (int)$current_ord;
	$sql = "UPDATE $table SET ordering = ordering - 1 WHERE ordering = ($current_ord+1) LIMIT 1";
	$inDB->query($sql) ;
	$sql = "UPDATE $table SET ordering = ordering + 1 WHERE id = $id LIMIT 1";
	$inDB->query($sql) ;
}

function dbShow($table, $id){
    $inDB = cmsDatabase::getInstance();
    $id = (int)$id;
	$sql = "UPDATE $table SET published = 1 WHERE id = $id";
	$inDB->query($sql) ;
}
function dbShowList($table, $list){
    $inDB = cmsDatabase::getInstance();
	if (is_array($list)){
		$sql = "UPDATE $table SET published = 1 WHERE ";
		$item = 0;
		foreach($list as $key => $value){
			$item ++;
			$sql .= 'id = '.(int)$value;
			if ($item<sizeof($list)) { $sql .= ' OR '; }
		}
		$sql .= ' LIMIT '.sizeof($list);
		$inDB->query($sql) ;
	}
}

function dbHide($table, $id){
    $inDB = cmsDatabase::getInstance();
    $id = (int)$id;
	$sql = "UPDATE $table SET published = 0 WHERE id = $id";
	$inDB->query($sql) ;
}
function dbHideList($table, $list){
    $inDB = cmsDatabase::getInstance();
	if (is_array($list)){
		$sql = "UPDATE $table SET published = 0 WHERE ";
		$item = 0;
		foreach($list as $key => $value){
			$item ++;
			$sql .= 'id = '.(int)$value;
			if ($item<sizeof($list)) { $sql .= ' OR '; }
		}
		$sql .= ' LIMIT '.sizeof($list);
		$inDB->query($sql) ;
	}
}

function dbDelete($table, $id){
    $inCore = cmsCore::getInstance();
    $inDB = cmsDatabase::getInstance();
    $id = (int)$id;
	$sql = "DELETE FROM $table WHERE id = $id LIMIT 1";
	$inDB->query($sql) ;
	if ($table=='cms_content'){
		cmsClearTags('content', $id);
        $inCore->deleteRatings('content', $id);
        $inCore->deleteComments('article', $id);
		$inDB->query("DELETE FROM cms_tags WHERE target='content' AND item_id=$id");
	}
	if ($table=='cms_modules'){
		$inDB->query("DELETE FROM cms_modules_bind WHERE module_id=$id");
	}
}
function dbDeleteList($table, $list){
    $inDB = cmsDatabase::getInstance();
	if (is_array($list)){
		$sql = "DELETE FROM $table WHERE ";
		$item = 0;
		foreach($list as $key => $value){
			$item ++;
            $value = (int)$value;
			$sql .= 'id = '.$value;
			if ($item<sizeof($list)) { $sql .= ' OR '; }
			if ($table=='cms_content'){
				cmsClearTags('content', $value);
				$inDB->query("DELETE FROM cms_comments WHERE target='article' AND target_id=$value");
				$inDB->query("DELETE FROM cms_ratings WHERE target='content' AND item_id=$value");
				$inDB->query("DELETE FROM cms_tags WHERE target='content' AND item_id=$value");
			}
			if ($table=='cms_modules'){
				$inDB->query("DELETE FROM cms_modules_bind WHERE module_id=$value");
			}
		}
		$sql .= ' LIMIT '.sizeof($list);
		$inDB->query($sql) ;
	}
}

///////////////////////////////////////////// HTML GENERATORS ////////////////////////////////////////////////
function insertPanel(){
    global $_LANG;
    $p_html = cmsCore::callEvent('REPLACE_PANEL', array('html' => ''));

    if($p_html['html']){ return $p_html['html']; }

    $inCore=cmsCore::getInstance();

    $submit_btn = '<input type="button" value="'.$_LANG['AD_INSERT'].'" style="width:100px" onClick="insertTag(document.addform.ins.options[document.addform.ins.selectedIndex].value)">';

echo '<table width="100%" border="0" cellspacing="0" cellpadding="8" class="proptable"><tr><td>';
	echo '<table width="100%" border="0" cellspacing="0" cellpadding="2">';
	echo '<tr>';
		echo '<td width="120">';
			echo '<strong>'.$_LANG['AD_INSERT'].':</strong> ';
		echo '</td>';
		echo '<td width="">';
			echo '<select name="ins" id="ins" style="width:99%" onChange="showIns()">
					<option value="frm" selected="selected">'.$_LANG['AD_FORM'].'</option>
					<option value="include">'.$_LANG['FILE'].'</option>
					<option value="filelink">'.$_LANG['AD_LINK_DOWNLOAD_FILE'].'</option>';
                    if ($inCore->isComponentInstalled('banners')){
                        echo '<option value="banpos">'.$_LANG['AD_BANNER_POSITION'].'</option>';
                    }
		    echo   '<option value="pagebreak">-- '.$_LANG['AD_PAGEBREAK'].' --</option>
					<option value="pagetitle">-- '.$_LANG['AD_PAGETITLE'].' --</option>
				  </select>';
		echo '</td>';
        echo '<td width="100">&nbsp;</td>';
	echo '</tr>';
	echo '<tr id="frm">';
		echo '<td width="120">
                    <strong>'.$_LANG['AD_FORM'].':</strong>
              </td>';
        echo '<td>
                    <select name="fm" style="width:99%">'.$inCore->getListItems('cms_forms').'</select>
              </td>';
        echo '<td width="100">'.$submit_btn.'</td>';
    echo '</tr>';
	echo '<tr id="include">';
		echo '<td width="120">
                    <strong>'.$_LANG['FILE'].':</strong>
              </td>';
        echo '<td>
                    /includes/myphp/<input name="i" type="text" value="myscript.php" />
              </td>';
        echo '<td width="100">'.$submit_btn.'</td>';
    echo '</tr>';
	echo '<tr id="filelink">';
		echo '<td width="120">
                    <strong>'.$_LANG['FILE'].':</strong>
              </td>';
        echo '<td>
                    <input name="fl" type="text" value="/files/myfile.rar" />
              </td>';
        echo '<td width="100">'.$submit_btn.'</td>';
    echo '</tr>';
    if ($inCore->isComponentInstalled('banners')){
        $inCore->loadModel('banners');
        echo '<tr id="banpos">';
            echo '<td width="120">
                        <strong>'.$_LANG['AD_POSITION'].':</strong>
                  </td>';
            echo '<td>
                        <select name="ban" style="width:99%">'.cms_model_banners::getBannersListHTML().'</select>
                  </td>';
            echo '<td width="100">'.$submit_btn.'</td>';
        echo '</tr>';
    }
	echo '<tr id="pagebreak">';
		echo '<td width="120">
                    <strong>'.$_LANG['TAG'].':</strong>
              </td>';
        echo '<td>
                    {pagebreak}
              </td>';
        echo '<td width="100">'.$submit_btn.'</td>';
    echo '</tr>';
	echo '<tr id="pagetitle">';
		echo '<td width="120">
                    <strong>'.$_LANG['AD_TITLE'].':</strong>
              </td>';
        echo '<td>
                    <input type="text" name="ptitle" style="width:99%" />
              </td>';
        echo '<td width="100">'.$submit_btn.'</td>';
    echo '</tr>';


	echo '</table>';

   echo '</td></tr></table>';

   echo '<script type="text/javascript">showIns();</script>';

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function cpBuildList($attr_name, $list, $selected_id=false){
    global $_LANG;
	$html = '';

	$html .= '<select name="'.$attr_name.'" id="'.$attr_name.'">' . "\n";

	$html .= '<option value="-100">-- '.$_LANG['AD_ALL'].' --</option>'."\n";

	foreach($list as $key=>$value){
		if ($selected_id == $list[$key]['id']) { $sel = 'selected'; } else { $sel = ''; }
		$html .= '<option value="'.$list[$key]['id'].'" '.$sel.'>'.$list[$key]['title'].'</option>' . "\n";
	}

	$html .= '</select>' . "\n";

	return $html;
}

function cpGetList($listtype, $field_name='title'){

    global $_LANG;
	$list = array();

	// Позиции для модулей
	if ($listtype == 'positions'){

        $pos = cpModulePositions(cmsConfig::getConfig('template'));

        foreach($pos as $p){
            $list[] = array('title'=>$p, 'id'=>$p);
        }

		return $list;

	}
	// Типы меню
	if ($listtype == 'menu'){

        $list[] = array('title'=>$_LANG['AD_MAIN_MENU'], 'id'=>'mainmenu');
        $list[] = array('title'=>$_LANG['AD_USER_MENU'], 'id'=>'usermenu');
        $list[] = array('title'=>$_LANG['AD_AUTH_MENU'], 'id'=>'authmenu');

        for ($m=1; $m<=20; $m++){
            $list[] = array('title'=>"{$_LANG['AD_SUBMENU']} {$m}", 'id'=>'menu'.$m);
        }

		return $list;

	}

	//...или записи из таблицы
    $inDB = cmsDatabase::getInstance();
	$sql  = "SELECT id, {$field_name} FROM $listtype ORDER BY {$field_name} ASC";
	$result = $inDB->query($sql) ;

	if ($inDB->num_rows($result)>0) {
		while($item = $inDB->fetch_assoc($result)){
			$next = sizeof($list);
			$list[$next]['title'] = $item[$field_name];
			$list[$next]['id'] = $item['id'];
		}
	}

	return $list;

}

function getFullAwardsList(){

    $inDB = cmsDatabase::getInstance();

    $awards = array();

    $rs = $inDB->query("SELECT title FROM cms_user_awards GROUP BY title");

    if ($inDB->num_rows($rs)){
        while($aw = $inDB->fetch_assoc($rs)){
            $awards[] = $aw;
        }
    }

    $rs = $inDB->query("SELECT title FROM cms_user_autoawards GROUP BY title");

    if ($inDB->num_rows($rs)){
        while($aw = $inDB->fetch_assoc($rs)){
            if (!in_array(array('title' => $aw['title']), $awards)) {
                $awards[] = $aw;
            }
        }
    }

    return $awards;

}
/**
 * Рекурсивно удаляет директорию
 * @param string $directory
 * @param bool $is_clear Если TRUE, то директория будет очищена, но не удалена
 * @return bool
 */
function files_remove_directory($directory, $is_clear=false){

    if(substr($directory,-1) == '/'){
        $directory = substr($directory,0,-1);
    }

    if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)){
        return false;
    }

    $handle = opendir($directory);

    while (false !== ($node = readdir($handle))){

        if($node != '.' && $node != '..'){

            $path = $directory.'/'.$node;

            if(is_dir($path)){
                if (!files_remove_directory($path)) { return false; }
            } else {
                if(!@unlink($path)) { return false; }
            }

        }

    }

    closedir($handle);

    if ($is_clear == false){
        if(!@rmdir($directory)){
            return false;
        }
    }

    return true;

}
?>