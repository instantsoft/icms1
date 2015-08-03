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

function applet_plugins(){

    global $_LANG;

    $inCore = cmsCore::getInstance();

    $GLOBALS['cp_page_title'] = $_LANG['AD_PLUGINS'];
    cpAddPathway($_LANG['AD_PLUGINS'], 'index.php?view=plugins');

	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }

	$do = cmsCore::request('do', 'str', 'list');
	$id = cmsCore::request('id', 'int', -1);

// ===================================================================================== //

	if ($do == 'hide'){
		dbHide('cms_plugins', $id);
		echo '1'; exit;
	}

// ===================================================================================== //

	if ($do == 'show'){
		dbShow('cms_plugins', $id);
		echo '1'; exit;
	}

// ===================================================================================== //

	if ($do == 'list'){

		$toolmenu[] = array('icon'=>'install.gif', 'title'=>$_LANG['AD_INSTALL_PLUGINS'], 'link'=>'?view=install&do=plugin');

		cpToolMenu($toolmenu);

        $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'20');
        $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title','link'=>'?view=plugins&do=config&id=%id%', 'width'=>'250');
        $fields[] = array('title'=>$_LANG['DESCRIPTION'], 'field'=>'description', 'width'=>'');
        $fields[] = array('title'=>$_LANG['AD_AUTHOR'], 'field'=>'author', 'width'=>'160');
        $fields[] = array('title'=>$_LANG['AD_VERSION'], 'field'=>'version', 'width'=>'50');
        $fields[] = array('title'=>$_LANG['AD_FOLDER'], 'field'=>'plugin', 'width'=>'100');
        $fields[] = array('title'=>$_LANG['AD_ENABLE'], 'field'=>'published', 'width'=>'60');

        $actions[] = array('title'=>$_LANG['AD_CONFIG'], 'icon'=>'config.gif', 'link'=>'?view=plugins&do=config&id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'link'=>'?view=install&do=remove_plugin&id=%id%', 'confirm'=>$_LANG['AD_REMOVE_PLUGIN_FROM']);

		cpListTable('cms_plugins', $fields, $actions);

	}

// ===================================================================================== //

    if ($do == 'save_config'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $plugin_name = cmsCore::request('plugin', 'str', 0);
        $config      = cmsCore::request('config', 'array_str');

        if (!$config || !$plugin_name) { cmsCore::redirectBack(); }

        $inCore->savePluginConfig($plugin_name, $config);

		cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

        cmsCore::redirect('index.php?view=plugins');

    }

    if ($do == 'save_auto_config'){

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $plugin_name = cmsCore::request('plugin', 'str', '');

        $xml_file = PATH.'/plugins/'.$plugin_name.'/backend.xml';
        if(!file_exists($xml_file)){ cmsCore::error404(); }

        $cfg = array();

        $backend = simplexml_load_file($xml_file);

        foreach($backend->params->param as $param){

            $name    = (string) $param['name'];
            $type    = (string) $param['type'];
            $default = (string) $param['default'];

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

        if (!$cfg || !$plugin_name) { cmsCore::redirectBack(); }

        $inCore->savePluginConfig($plugin_name, $cfg);

        cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

        cmsCore::redirect('index.php?view=plugins');

    }

// ===================================================================================== //

    if ($do == 'config'){

        $plugin_name = $inCore->getPluginById($id);
        if(!$plugin_name){ cmsCore::error404(); }

        $plugin = $inCore->loadPlugin($plugin_name);
        $config = $inCore->loadPluginConfig($plugin_name);

        $GLOBALS['cp_page_title'] = $plugin->info['title'];
        cpAddPathway($plugin->info['title'], 'index.php?view=plugins&do=config&id='.$id);

        echo '<h3>'.$plugin->info['title'].'</h3>';

        $xml_file = PATH.'/plugins/'.$plugin_name.'/backend.xml';

        if (!$config && !file_exists($xml_file)) {
            echo '<p>'.$_LANG['AD_PLUGIN_DISABLE'].'.</p>';
            echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';
            return;
        }

        echo '<form name="addform" action="index.php?view=plugins&plugin='.$plugin_name.'" method="POST">';

        if(file_exists($xml_file)){

            $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
            $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'index.php?view=modules');
            cpToolMenu($toolmenu);

            cmsCore::loadClass('formgen');
            $formGen = new cmsFormGen($xml_file, $config);
            echo $formGen->getHTML();

        } else {
            echo '<input type="hidden" name="csrf_token" value="'.cmsUser::getCsrfToken().'" />';
            echo '<input type="hidden" name="do" value="save_config" />';
            echo '<table class="proptable" width="605" cellpadding="8" cellspacing="0" border="0">';
                foreach ($config as $field=>$value){
                    echo '<tr>';
                        echo '<td width="150"><strong>'.(isset($_LANG[mb_strtoupper($field)]) ? $_LANG[mb_strtoupper($field)] : $field).':</strong></td>';
                        echo '<td><input type="text" style="width:90%" name="config['.$field.']" value="'.htmlspecialchars($value).'" /></td>';
                    echo '</tr>';
                }
            echo '</table>';

            echo '<div style="margin-top:6px;">';
                echo '<input type="submit" name="save" value="'.$_LANG['SAVE'].'" /> ';
                echo '<input type="button" name="back" value="'.$_LANG['CANCEL'].'" onclick="window.history.go(-1)" />';
            echo '</div>';
        }
        echo '</form>';

    }

// ===================================================================================== //

}