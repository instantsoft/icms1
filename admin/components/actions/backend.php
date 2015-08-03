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

    cmsCore::loadModel('actions');
    $model = new cms_model_actions();

    cmsCore::loadClass('actions');
    $inActions = cmsActions::getInstance();

    $opt = cmsCore::request('opt', 'str', 'list');

	$act_components = cmsActions::getActionsComponents();
    $act_component  = cmsCore::request('act_component', 'str', '');

//=================================================================================================//
//=================================================================================================//

	$toolmenu = array();

	if($opt != 'config'){
?>
		<table width="100%" cellpadding="2" border="0" class="toolmenu" style="margin:0px">
		  <tbody>
			<tr>
			  <td width="45px">
				<a class="toolmenuitem" href="?view=components&do=config&id=<?php echo $id; ?>&opt=config" title="<?php echo $_LANG['AD_CONFIG']; ?>">
				  <img src="images/toolmenu/config.gif" border="0">
				</a>
			  </td>
			  <td>
              <form action="?view=components&do=config&id=<?php echo $id; ?>" method="post" id="filter_form">
				<?php echo $_LANG['AD_ACTIONS_FROM']; ?>:
                <select name="act_component" style="width:215px" onchange="$('#filter_form').submit()">
                    <option value="" <?php if(!$act_component){ ?>selected="selected"<?php } ?>><?php echo $_LANG['AD_ACTIONS_FROM_ALL_COM']; ?></option>
                    <?php foreach($act_components as $act_com) {
                            if($act_com['link'] == $act_component){
                                echo '<option value="'.$act_com['link'].'" selected="selected">'.$act_com['title'].'</option>';
                            } else {
                                echo '<option value="'.$act_com['link'].'">'.$act_com['title'].'</option>';
                            }
                          }
					?>
                </select>
              </form>
			  </td>
			</tr>
		  </tbody>
		</table><br>

<?php
	}

	if($opt == 'config'){

        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.optform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

		cpToolMenu($toolmenu);

	}

//=================================================================================================//
//=================================================================================================//

    if ($opt == 'list'){

        $page    = cmsCore::request('page', 'int', 1);
        $perpage = 15;

        $inActions->showTargets(true);

		if ($act_component){
			$inDB->where("a.component = '$act_component'");
		}

		$total = $inActions->getCountActions();

        $inDB->limitPage($page, $perpage);

        $actions = $inActions->getActionsLog();

		$pagebar = cmsPage::getPagebar($total, $page, $perpage, '?view=components&do=config&id='.$id.'&opt=list&page=%page%');

		$tpl_file   = 'admin/actions.php';
		$tpl_dir    = file_exists(TEMPLATE_DIR.$tpl_file) ? TEMPLATE_DIR : DEFAULT_TEMPLATE_DIR;

		include($tpl_dir.$tpl_file);

    }

//=================================================================================================//
//=================================================================================================//
	if($opt=='saveconfig'){

		if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

		$cfg = array();

        $cfg['show_target'] = cmsCore::request('show_target', 'int', 1);
        $cfg['perpage']     = cmsCore::request('perpage', 'int', 10);
        $cfg['perpage_tab'] = cmsCore::request('perpage_tab', 'int', 15);
        $cfg['is_all']      = cmsCore::request('is_all', 'int', 0);
        $cfg['act_type']    = cmsCore::request('act_type', 'array_str', array());
        $cfg['meta_keys']   = cmsCore::request('meta_keys', 'str', '');
        $cfg['meta_desc']   = cmsCore::request('meta_desc', 'str', '');

        $inCore->saveComponentConfig('actions', $cfg);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

		cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=config');

	}
//=================================================================================================//
//=================================================================================================//
	if ($opt=='config') {

		cpAddPathway($_LANG['AD_SETTINGS'], '?view=components&do=config&id='.$id.'&opt=config');

        $sql        = "SELECT *
                       FROM cms_actions
                       ORDER BY title
                       LIMIT 100";

        $result = $inDB->query($sql);

		?>

	<form action="index.php?view=components&do=config&id=<?php echo $id;?>&opt=saveconfig" method="post" name="optform" target="_self" id="form1">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
		<table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
			<tr>
				<td>
					<strong><?php echo $_LANG['AD_SHOW_TARGET']; ?>:</strong><br />
				</td>
				<td valign="top">
					<label><input name="show_target" type="radio" value="1"  <?php if ($model->config['show_target']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
					<label><input name="show_target" type="radio" value="0"  <?php if (!$model->config['show_target']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG['AD_COUNT_ACTIONS_PAGE']; ?>:</strong><br />
				</td>
				<td valign="top">
					<input name="perpage" size=5 value="<?php echo $model->config['perpage'];?>"/>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG['AD_COUNT_ACTIONS_TAB']; ?>:</strong><br />
				</td>
				<td valign="top">
					<input name="perpage_tab" size=5 value="<?php echo $model->config['perpage_tab'];?>"/>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG['AD_ACTIONS_TYPE']; ?>:</strong><br />
                    <div class="param-links">
                        <label for="is_all"><input type="checkbox" id="is_all" name="is_all" value="1" <?php if($model->config['is_all']) {?>checked="checked" <?php }?> /> <a href="javascript:void(0);" onclick="$('td input:checkbox, #is_all').prop('checked', true);"><?php echo $_LANG['SELECT_ALL']; ?></a></label> |
                        <a href="javascript:void(0);" onclick="$('td input:checkbox, #is_all').prop('checked', false);"><?php echo $_LANG['REMOVE_ALL']; ?></a>
                    </div>
				</td>
				<td valign="top">
					<?php

						$html = '<table cellpadding="0" cellspacing="0">' . "\n";

						if ($inDB->num_rows($result)){
							while($option = $inDB->fetch_assoc($result)){

								$html .= '<tr>' . "\n" .
											"\t" . '<td><input type="checkbox" id="act_type_'.$option['name'].'" name="act_type['.$option['name'].']" value="'.$option['id'].'" '.(@in_array($option['id'], $model->config['act_type']) ? 'checked="checked"' : '').' />' . "\n" .
											"\t" . '<td><label for="act_type_'.$option['name'].'">'.$option['title'].'</label></td>' . "\n" .
										 '</tr>';
							}
						}

						$html .= '</table>' . "\n";
						echo $html;

					?>
				</td>
			</tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METAKEYS']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:99%"><?php echo $model->config['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METADESC']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:99%"><?php echo $model->config['meta_desc'] ?></textarea>
                </td>
            </tr>
		</table>
		<p>
			<input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
			<input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
		</p>
	</form>

<?php }