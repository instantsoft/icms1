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
	function getProvidersList() {
		$pdir = @opendir(PATH.'/components/search/providers/');
		if(!$pdir){ return false; }
		$provider_array = array();
		while ($provider = readdir($pdir)){
			if (($provider != '.') && ($provider != '..') && !is_dir(PATH.'/components/search/providers/'.$provider)) {
				$provider = mb_substr($provider, 0, mb_strrpos($provider, '.'));
				$provider_array[] = $provider;
			}
		}
		closedir($pdir);
		return $provider_array;
	}

    cmsCore::loadModel('search');
    $model = cms_model_search::initModel();

    $opt = cmsCore::request('opt', 'str', '');

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.optform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components');

	cpToolMenu($toolmenu);

    if ($opt=='save'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

		$cfg = array();
		$cfg['perpage'] = cmsCore::request('perpage', 'int', 15);
		$cfg['comp']    = cmsCore::request('comp', 'array_str');
		$cfg['search_engine'] = preg_replace('/[^a-z_]/i', '', cmsCore::request('search_engine', 'str', ''));

		if($model->config['search_engine'] && class_exists($model->config['search_engine']) && method_exists($model->config['search_engine'], 'getProviderConfig')){
			foreach($model->getProviderConfig() as $key=>$value){
				$cfg[$model->config['search_engine']][$value] = cmsCore::request($value, 'str', '');
			}
		}

		$inCore->saveComponentConfig('search', $cfg);
		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
		cmsCore::redirectBack();
	}

	if ($opt=='dropcache'){
		$model->truncateResults();
	}

?>
<form action="index.php?view=components&do=config&id=<?php echo $id;?>" name="optform" method="post" target="_self">
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table border="0" cellpadding="10" cellspacing="0" class="proptable">
          <tr>
            <td width="215"><strong><?php echo $_LANG['AD_RESULTS_PAGE']; ?>: </strong></td>
            <td width="289"><input class="uispin" name="perpage" type="text" id="perpage" value="<?php echo $model->config['perpage'];?>" size="6" /></td>
          </tr>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_SEARCH_PROVIDER']; ?>: </strong></td>
            <td valign="top">
                <select name="search_engine" style="width:245px">
                    <option value="" <?php if (!$model->config['search_engine']){?>selected="selected"<?php } ?>><?php echo $_LANG['AD_NATIVE']; ?></option>
                    <?php $provider_array = getProvidersList();
					if($provider_array){
						foreach($provider_array as $provider){
					?>
                    	<option value="<?php echo $provider; ?>" <?php if ($model->config['search_engine']==$provider){?>selected="selected"<?php } ?>><?php echo $provider; ?></option>
                    <?php
						}
					}
					?>
                </select>
            </td>
          </tr>
          <?php if($model->config['search_engine'] && class_exists($model->config['search_engine']) && method_exists($model->config['search_engine'], 'getProviderConfig')){
		  foreach($model->getProviderConfig() as $key=>$value){
		  ?>
              <tr>
                <td width="215"><strong><?php echo $key; ?>: </strong></td>
                <td width="289"><input name="<?php echo $value; ?>" type="text" value="<?php echo $model->config[$model->config['search_engine']][$value]; ?>" style="width:245px" /></td>
              </tr>
		  <?php } } ?>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_SEARCH_COMPONENTS']; ?>:</strong> </td>
            <td valign="top">
			<?php
				echo '<table border="0" cellpadding="2" cellspacing="0">';
				foreach($model->components as $component){
					echo '<tr>';
					$checked = '';
					if (in_array($component['link'], $model->config['comp'])){
						$checked = 'checked="checked"';
					}
					echo '<td><input name="comp[]" id="'.$component['link'].'" type="checkbox" value="'.$component['link'].'" '.$checked.'/></td><td><label for="'.$component['link'].'">'.$component['title'].'</label></td>';
					echo '</tr>';
				}
				echo '</table>';
			?></td>
          </tr>
          <tr>
            <td valign="top"><strong><?php echo $_LANG['AD_SEARCH_CASH']; ?>:</strong> </td>
            <td valign="top">
			<?php
				$records = $inDB->rows_count('cms_search', "1=1");
				echo $records .' '.$_LANG['AD_PIECES'];
				if ($records) {
					echo ' | <a href="?view=components&do=config&id='.$id.'&opt=dropcache">'.$_LANG['AD_CLEAN'].'</a>';
				}
			?></td>
          </tr>
        </table>
        <p>
          <input name="opt" type="hidden" id="do" value="save" />
          <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
          <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
        </p>
</form>