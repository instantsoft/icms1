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

function applet_arhive(){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();

	global $_LANG;

	$GLOBALS['cp_page_title'] = $_LANG['AD_ARTICLES_ARCHIVE'];

	$cfg = $inCore->loadComponentConfig('content');
	$cfg_arhive = $inCore->loadComponentConfig('arhive');
    cmsCore::loadModel('content');
    $model = new cms_model_content();

	cpAddPathway($_LANG['AD_ARTICLE_SITE'], 'index.php?view=tree');
	cpAddPathway($_LANG['AD_ARTICLES_ARCHIVE'], 'index.php?view=arhive');

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);

    if ($do=='saveconfig'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }
		$cfg['source'] = cmsCore::request('source', 'str', '');
		$inCore->saveComponentConfig('arhive', $cfg);
        cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'] , 'success');
        cmsCore::redirect('?view=arhive&do=config');

	}

    if ($do=='config'){
		$toolmenu = array();
		$toolmenu[0]['icon'] = 'folders.gif';
		$toolmenu[0]['title'] = $_LANG['AD_LIST_OF_ARTICLES'];
		$toolmenu[0]['link'] = '?view=arhive';

		cpToolMenu($toolmenu);
		cpAddPathway($_LANG['AD_SETTINGS'], 'index.php?view=arhive&do=config');
?>
<form action="index.php?view=arhive&do=saveconfig" method="post" name="optform" target="_self" id="form1">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <table width="609" border="0" cellpadding="10" cellspacing="0" class="proptable">
        <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_SOURCE_MATERIALS']; ?></strong></td>
            <td width="100" valign="top">
                <select name="source" id="source" style="width:285px">
                    <option value="content" <?php if ($cfg_arhive['source']=='content') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_ARTICLE_SITE']; ?></option>
                    <option value="arhive" <?php if ($cfg_arhive['source']=='arhive') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_ARTICLES_ARCHIVE']; ?></option>
                    <option value="both" <?php if ($cfg_arhive['source']=='both') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_CATALOG_AND_ARCHIVE']; ?></option>
                </select>
            </td>
        </tr>
    </table>
    <p>
        <input name="opt" type="hidden" value="saveconfig" />
        <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=arhive';" />
    </p>
</form>
<?php }

	if ($do == 'list'){
		$toolmenu = array();
		$toolmenu[0]['icon'] = 'config.gif';
		$toolmenu[0]['title'] = $_LANG['AD_SETTINGS'];
		$toolmenu[0]['link'] = '?view=arhive&do=config';

		$toolmenu[1]['icon'] = 'delete.gif';
		$toolmenu[1]['title'] = $_LANG['AD_DELETE_SELECTED'] ;
		$toolmenu[1]['link'] = "javascript:checkSel('?view=arhive&do=delete&multiple=1');";

		cpToolMenu($toolmenu);

		//TABLE COLUMNS
		$fields = array();

		$fields[0]['title'] = 'id'; $fields[0]['field'] = 'id'; $fields[0]['width'] = '30';
		$fields[1]['title'] = $_LANG['AD_CREATE']; $fields[1]['field'] = 'pubdate'; $fields[1]['width'] = '80'; $fields[1]['filter'] = 15;
		$fields[1]['fdate'] = '%d/%m/%Y';
		$fields[2]['title'] = $_LANG['TITLE']; $fields[2]['field'] = 'title'; $fields[2]['width'] = ''; $fields[2]['link'] = '?view=content&do=edit&id=%id%';
		$fields[2]['filter'] = 15;
		$fields[3]['title'] = $_LANG['AD_PARTITION']; $fields[3]['field'] = 'category_id'; $fields[3]['width'] = '100';	$fields[3]['filter'] = 1;
		$fields[3]['prc'] = 'cpCatById'; $fields[3]['filterlist'] = cpGetList('cms_category');

		//ACTIONS
		$actions = array();
		$actions[0]['title'] = $_LANG['AD_TO_ARTICLES_CATALOG'];
		$actions[0]['icon']  = 'arhive_off.gif';
		$actions[0]['link']  = '?view=arhive&do=arhive_off&id=%id%';

		$actions[2]['title'] = $_LANG['DELETE'] ;
		$actions[2]['icon']  = 'delete.gif';
		$actions[2]['confirm'] = $_LANG['AD_DELETE_MATERIALS'];
		$actions[2]['link']  = '?view=content&do=delete&id=%id%';

		//Print table
		cpListTable('cms_content', $fields, $actions, 'is_arhive=1');
	}

	if ($do == 'arhive_off'){
		if(isset($_REQUEST['id'])) {
			$sql = "UPDATE cms_content SET is_arhive = 0 WHERE id = '$id'";
			$inDB->query($sql) ;
            cmsCore::redirect('?view=arhive');
		}
	}

	if ($do == 'delete'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){
				$model->deleteArticle($id, $cfg['af_delete']);
			}
		} else {
			$model->deleteArticles($_REQUEST['item'], $cfg['af_delete']);
		}
        cmsCore::redirect('?view=arhive');
	}

}

?>