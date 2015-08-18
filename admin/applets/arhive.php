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

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }
		$cfg['source'] = cmsCore::request('source', 'str', '');
		$inCore->saveComponentConfig('arhive', $cfg);
        cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'] , 'success');
        cmsCore::redirect('?view=arhive&do=config');

	}

    if ($do=='config'){

		cpToolMenu(array(
            array(
                'icon'=>'folders.gif',
                'title'=>$_LANG['AD_LIST_OF_ARTICLES'],
                'link'=>'?view=arhive'
            )
        ));

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

		$toolmenu = array(
            array(
                'icon'=>'config.gif',
                'title'=>$_LANG['AD_SETTINGS'],
                'link'=>'?view=arhive&do=config'
            ),
            array(
                'icon'=>'delete.gif',
                'title'=>$_LANG['AD_DELETE_SELECTED'],
                'link'=>"javascript:checkSel('?view=arhive&do=delete&multiple=1');"
            )
        );

		cpToolMenu($toolmenu);

		$fields = array(
            array(
                'title'=>'id',
                'field'=>'id',
                'width'=>'30'
            ),
            array(
                'title'=>$_LANG['AD_CREATE'],
                'field'=>'pubdate',
                'width'=>'80',
                'filter'=>15,
                'fdate'=>'%d/%m/%Y'
            ),
            array(
                'title'=>$_LANG['TITLE'],
                'field'=>'title',
                'width'=>'',
                'filter'=>15,
                'link'=>'?view=content&do=edit&id=%id%'
            ),
            array(
                'title'=>$_LANG['AD_PARTITION'],
                'field'=>'category_id',
                'width'=>'100',
                'filter'=>1,
                'filterlist'=>cpGetList('cms_category'),
                'prc'=>'cpCatById'
            ),
        );

		$actions = array(
            array(
                'title'=>$_LANG['AD_VIEW_ONLINE'],
                'icon'=>'search.gif',
                'link'=>'/%seolink%.html'
            ),
            array(
                'title'=>$_LANG['AD_TO_ARTICLES_CATALOG'],
                'icon'=>'arhive_off.gif',
                'link'=>'?view=arhive&do=arhive_off&id=%id%'
            ),
            array(
                'title'=>$_LANG['DELETE'],
                'icon'=>'delete.gif',
                'confirm'=>$_LANG['AD_DELETE_MATERIALS'],
                'link'=>'?view=content&do=delete&id=%id%'
            )
        );

		cpListTable('cms_content', $fields, $actions, 'is_arhive=1');

	}

	if ($do == 'arhive_off'){
		if($id) {
			$sql = "UPDATE cms_content SET is_arhive = 0 WHERE id = '$id'";
			$inDB->query($sql) ;
            cmsCore::redirect('?view=arhive');
		}
	}

	if ($do == 'delete'){
		if (!isset($_REQUEST['item'])){
			if ($id){
				$model->deleteArticle($id, $cfg['af_delete']);
			}
		} else {
			$model->deleteArticles(cmsCore::request('item', 'array_int', array()), $cfg['af_delete']);
		}
        cmsCore::redirect('?view=arhive');
	}

}