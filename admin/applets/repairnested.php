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

function checkNestedSet($table){

	$inDB   = cmsDatabase::getInstance();
	$differ = $table['differ'];
	$table	= $table['name'];
	$errors = array();

	//step 1
		$sql = "SELECT id FROM $table WHERE NSLeft >= NSRight AND NSDiffer = '$differ'";
		$res = $inDB->query($sql);
		if (!$inDB->errno()) { $errors[] = ($inDB->num_rows($res)>0); } else { $errors[] = true; }

	//step 2 and 3
		$sql = "SELECT COUNT(id) as rows, MIN(NSLeft) as min_left, MAX(NSRight) as max_right FROM $table WHERE NSDiffer = '$differ'";
		$res = $inDB->query($sql);
		if (!$inDB->errno()) {
			$data = $inDB->fetch_assoc($res);
			$errors[] = ($data['min_left'] != 1);
			$errors[] = ($data['max_right'] != 2*$data['rows']);
		} else { $errors[] = true; }

	//step 4
		$sql = "SELECT id, NSRight, NSLeft
				FROM $table
				WHERE MOD((NSRight-NSLeft), 2) = 0 AND NSDiffer = '$differ'";
		$res = $inDB->query($sql);
		if (!$inDB->errno()) { $errors[] = ($inDB->num_rows($res)>0); } else { $errors[] = true; }

	//step 5
		$sql = "SELECT id
				FROM $table
				WHERE MOD((NSLeft-NSLevel+2), 2) = 0 AND NSDiffer = '$differ'";
		$res = $inDB->query($sql);
		if (!$inDB->errno()) { $errors[] = ($inDB->num_rows($res)>0); } else { $errors[] = true; }

	//step 6
		$sql = "SELECT 	t1.id,
						COUNT(t1.id) AS rep,
						MAX(t3.NSRight) AS max_right
				FROM $table AS t1, $table AS t2, $table AS t3
				WHERE t1.NSLeft <> t2.NSLeft AND t1.NSLeft <> t2.NSRight AND t1.NSRight <> t2.NSLeft AND t1.NSRight <> t2.NSRight
						AND t1.NSDiffer = '$differ' AND t2.NSDiffer = '$differ' AND t3.NSDiffer = '$differ'
				GROUP BY t1.id
				HAVING max_right <> SQRT(4 * rep + 1) + 1";
		$res = $inDB->query($sql);
		if (!$inDB->errno()) { $errors[] = ($inDB->num_rows($res)>0); } else { $errors[] = true; }

		return (in_array(true, $errors));

}
// ========================================================================== //

function tree_all_ns($s_table, $i_value = 1, $k_parent = 0, $lvl = 0){

    $inDB = cmsDatabase::getInstance();

    if(!is_numeric($k_parent) || !is_numeric($i_value)) { return false; }

    $r = $inDB->query("SELECT id FROM {$s_table} WHERE parent_id='{$k_parent}' ORDER BY NSLeft ASC, ordering ASC");
    if(!$r){ return false; }

    $o = 1;

    while ($f = $inDB->fetch_assoc($r)){

        $k_item  = $f['id'];
        $i_right = tree_all_ns($s_table, $i_value + 1, $k_item, $lvl+1);

        if($i_right === false){ return false; }

        if(!$inDB->query("UPDATE ".$s_table." SET NSLeft='".$i_value."', NSRight='".$i_right."', ordering = '".$o++."', NSLevel = '{$lvl}' where id='".$k_item."'")){ return false; }

        $i_value = $i_right + 1;

    }

    return $i_value;

}

// ========================================================================== //

function repairNestedSet($table){

	$inDB   = cmsDatabase::getInstance();

	global $_LANG;

	$differ = $table['differ'];
	$title  = $table['title'];
	$table	= $table['name'];

	$root_id = $inDB->getNsRootCatId($table, $differ);

	$sql = "SELECT id
			FROM $table
			WHERE NSDiffer = '$differ' AND NSLevel > 0
			ORDER BY NSLeft";
	$res = $inDB->query($sql);

	if (!$inDB->errno()){

		$items_count = $inDB->num_rows($res);
		$max_right	 = ($items_count+1) * 2;
		//fix root node
		$sql = "UPDATE $table
				SET NSLeft = 1,
					NSRight = $max_right,
					parent_id = 0,
					NSLevel = 0,
					ordering = 1
				WHERE id = $root_id";
		$inDB->query($sql);
		//fix child nodes
		$pos = 0;
		$ord = 1;
		while ($item = $inDB->fetch_assoc($res)){

			$level = 1;
			$left  = $pos + 2;
			$right = $pos + 3;

			$sql = "UPDATE $table
					SET NSLeft=$left,
						NSRight=$right,
						parent_id = $root_id,
						NSLevel = $level,
						ordering = $ord
					WHERE id=".$item['id'];
			$inDB->query($sql);
			$pos+=2; $ord++;

		}

		cmsCore::addSessionMessage($title.' '.$_LANG['AD_RESTORED'], 'success');
	}
}

// ===================================================================================================================================== //

function applet_repairnested(){

    $inCore = cmsCore::getInstance();

	global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/config', $adminAccess)) { cpAccessDenied(); }

	$tables = array();

	$tables[0]['name']	 	= 'cms_category';
	$tables[0]['title']		= $_LANG['AD_ARTICLES_TREE'];
	$tables[0]['differ']	= '';

	$tables[1]['name']	 	= 'cms_photo_albums';
	$tables[1]['title']		= $_LANG['AD_ALBUMS_TREE'];
	$tables[1]['differ']	= '';

	$tables[3]['name']	 	= 'cms_board_cats';
	$tables[3]['title']		= $_LANG['AD_DESK_TREE'] ;
	$tables[3]['differ']	= '';

	$tables[4]['name']	 	= 'cms_uc_cats';
	$tables[4]['title']		= $_LANG['AD_CATALOG_TREE'];
	$tables[4]['differ']	= '';

	$tables[5]['name']	    = 'cms_menu';
	$tables[5]['title']	    = $_LANG['AD_MENU_TREE'];
	$tables[5]['differ']	= '';

	$tables[6]['name']	    = 'cms_forums';
	$tables[6]['title']	    = $_LANG['AD_FORUMS_TREE'];
	$tables[6]['differ']	= '';

	if($inCore->isComponentInstalled('maps')){
		$tables[7]['name']	    = 'cms_map_cats';
		$tables[7]['title']	    = $_LANG['AD_MAPS_TREE'];
		$tables[7]['differ']	= '';
	}

	if($inCore->isComponentInstalled('video')){
		$tables[8]['name']	    = 'cms_video_category';
		$tables[8]['title']	    = $_LANG['AD_VIDEO_TREE'];
		$tables[8]['differ']	= '';
	}

	if($inCore->isComponentInstalled('shop')){
		$tables[9]['name']	    = 'cms_shop_cats';
		$tables[9]['title']	    = $_LANG['AD_SHOP_TREE'];
		$tables[9]['differ']	= '';
	}

    if(cmsCore::inRequest('tables')){
		if (is_array($_POST['tables'])){
			foreach ($_POST['tables'] as $table_id){
                if(cmsCore::request('go_repair', 'int', 0)){
                    repairNestedSet($tables[(int)$table_id]);
                } else
                if(cmsCore::request('go_repair_tree', 'int', 0)){
                    if(tree_all_ns($tables[(int)$table_id]['name']) !== false) {
                        cmsCore::addSessionMessage($tables[(int)$table_id]['title'].' '.$_LANG['AD_RESTORED'], 'success');
                    }
                }
			}
		}
    }

	$GLOBALS['cp_page_title'] = $_LANG['AD_CHECKING_TREES'];

	cpAddPathway($_LANG['AD_SITE_SETTING'], 'index.php?view=config');
	cpAddPathway($_LANG['AD_CHECKING_TREES'], 'index.php?view=repairnested');

	$GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/admin/js/repair.js"></script>';

	$errors_found = false;

	echo '<h3>'.$_LANG['AD_TREE_FULL'].'</h3>';

	echo '<div style="margin:20px; margin-top:0px;">';
	echo '<form method="post" action="" id="repairform">';
		echo '<input id="go_repair" type="hidden" name="go_repair" value="0">';
		echo '<input id="go_repair_tree" type="hidden" name="go_repair_tree" value="0">';
		echo '<table cellpadding="2">';
			foreach($tables as $id=>$table){
				$errors = checkNestedSet($table);
				echo '<tr>';
					echo '<td width="15">'.($errors ? '<input type="checkbox" name="tables[]" value="'.$id.'" checked="checked"/>' : '').'</td>';
					echo '<td><div>';
						echo '<span>'.$table['title'].'</span> &mdash; ' . ($errors ? '<span style="color:red">'.$_LANG['AD_ERROR_FOUND'].'</span>' : '<span style="color:green">'.$_LANG['AD_NO_ERROR_FOUND'] .'</span>');
					echo '</div></td>';
				echo '</tr>';
				if ($errors) { $errors_found = true; }
			}
		echo '</table>';
	echo '</div>';

	if ($errors_found){
		echo '<div style="margin-bottom:20px">';
			echo '<input type="button"  onclick="repairTreesRoot()" value="'.$_LANG['AD_REPAIR'].'"> ';
			echo '<input type="button"  onclick="repairTrees()" value="'.$_LANG['AD_REPAIR_TOTREE'].'">';
		echo '</div>';
	}

    cmsPage::displayLangJS(array('AD_REPAIR_CONFIRM','AD_REPAIR_TOTREE_CONFIRM'));

}

