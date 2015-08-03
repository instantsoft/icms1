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

function pluginsList($new_plugins, $action_name, $action){

    $inCore = cmsCore::getInstance();
	global $_LANG;

    echo '<table cellpadding="3" cellspacing="0" border="0" style="margin-left:40px">';
    foreach($new_plugins as $plugin){
        $plugin_obj = $inCore->loadPlugin($plugin);

        if ($action == 'install_plugin') { $version = $plugin_obj->info['version']; }
        if ($action == 'upgrade_plugin') { $version = $inCore->getPluginVersion($plugin) . ' &rarr; '. $plugin_obj->info['version']; }

        echo '<tr>';
            echo '<td width="16"><img src="/admin/images/icons/hmenu/plugins.png" /></td>';
            echo '<td><a style="font-weight:bold;font-size:14px" title="'.$action_name.' '.$plugin_obj->info['title'].'" href="index.php?view=install&do='.$action.'&id='.$plugin.'">'.$plugin_obj->info['title'].'</a> v'.$version.'</td>';
        echo '<tr>';
        echo '<tr>';
            echo '<td width="16">&nbsp;</td>';
            echo '<td>
                        <div style="margin-bottom:6px;">'.$plugin_obj->info['description'].'</div>
                        <div style="color:gray"><strong>'.$_LANG['AD_AUTHOR'].':</strong> '.$plugin_obj->info['author'].'</div>
                        <div style="color:gray"><strong>'.$_LANG['AD_FOLDER'].':</strong> /plugins/'.$plugin_obj->info['plugin'].'</div>
                  </td>';
        echo '<tr>';
    }
    echo '</table>';

    return;

}

function componentsList($new_components, $action_name, $action){

    $inCore = cmsCore::getInstance();
	global $_LANG;

    echo '<table cellpadding="3" cellspacing="0" border="0" style="margin-left:40px">';
    foreach($new_components as $component){
        if ($inCore->loadComponentInstaller($component)) {

            $_component = call_user_func('info_component_'.$component);

            if ($action == 'install_component') { $version = $_component['version']; }
            if ($action == 'upgrade_component') { $version = $inCore->getComponentVersion($component) . ' &rarr; '. $_component['version']; }

            echo '<tr>';
                echo '<td width="16"><img src="/admin/images/icons/hmenu/plugins.png" /></td>';
                echo '<td><a style="font-weight:bold;font-size:14px" title="'.$action_name.' '.$_component['title'].'" href="index.php?view=install&do='.$action.'&id='.$component.'">'.$_component['title'].'</a> v'.$version.'</td>';
            echo '<tr>';
            echo '<tr>';
                echo '<td width="16">&nbsp;</td>';
                echo '<td>
                            <div style="margin-bottom:6px;">'.$_component['description'].'</div>
                            <div style="color:gray"><strong>'.$_LANG['AD_AUTHOR'].':</strong> '.$_component['author'].'</div>
                            <div style="color:gray"><strong>'.$_LANG['AD_FOLDER'].':</strong> /components/'.$_component['link'].'</div>
                      </td>';
            echo '<tr>';

        }
    }
    echo '</table>';

    return;

}

function modulesList($new_modules, $action_name, $action){

    $inCore = cmsCore::getInstance();
	global $_LANG;

    echo '<table cellpadding="3" cellspacing="0" border="0" style="margin-left:40px">';
    foreach($new_modules as $module){
        if ($inCore->loadModuleInstaller($module)) {

            $_module = call_user_func('info_module_'.$module);

            if ($action == 'install_module') { $version = $_module['version']; }
            if ($action == 'upgrade_module') { $version = $inCore->getModuleVersion($module) . ' &rarr; '. $_module['version']; }

            echo '<tr>';
                echo '<td width="16"><img src="/admin/images/icons/hmenu/plugins.png" /></td>';
                echo '<td><a style="font-weight:bold;font-size:14px" title="'.$action_name.' '.$_module['title'].'" href="index.php?view=install&do='.$action.'&id='.$module.'">'.$_module['title'].'</a> v'.$version.'</td>';
            echo '<tr>';
            echo '<tr>';
                echo '<td width="16">&nbsp;</td>';
                echo '<td>
                            <div style="margin-bottom:6px;">'.$_module['description'].'</div>
                            <div style="color:gray"><strong>'.$_LANG['AD_AUTHOR'].':</strong> '.$_module['author'].'</div>
                            <div style="color:gray"><strong>'.$_LANG['AD_FOLDER'].':</strong> /modules/'.$_module['link'].'</div>
                      </td>';
            echo '<tr>';

        }
    }
    echo '</table>';

    return;

}

function applet_install(){

    $inCore = cmsCore::getInstance();

	global $_LANG;

	$GLOBALS['cp_page_title'] = $_LANG['AD_SETUP_EXTENSION'];

    $do = cmsCore::request('do', 'str', 'list');

	global $adminAccess;

// ========================================================================== //

    if ($do == 'module'){

		if (!cmsUser::isAdminCan('admin/modules', $adminAccess)) { cpAccessDenied(); }

      	cpAddPathway($_LANG['AD_SETUP_MODULES'], 'index.php?view=install&do=module');

        $new_modules = $inCore->getNewModules();
        $upd_modules = $inCore->getUpdatedModules();

        echo '<h3>'.$_LANG['AD_SETUP_MODULES'].'</h3>';

        if (!$new_modules && !$upd_modules){

            echo '<p>'.$_LANG['AD_NO_SEARCH_MODULES'].'</p>';
            echo '<p>'.$_LANG['AD_IF_WANT_SETUP_MODULES'].'</p>';
            echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';
            return;

        }

        if ($new_modules){

            echo '<p><strong>'.$_LANG['AD_SEARCH_MODULES'].'</strong></p>';
            modulesList($new_modules, $_LANG['AD_SETUP'], 'install_module');

        }

        if ($upd_modules){

            echo '<p><strong>'.$_LANG['AD_MODULES_UPDATE']	.'</strong></p>';
            modulesList($upd_modules, $_LANG['AD_UPDATE'], 'upgrade_module');

        }

        echo '<p>'.$_LANG['AD_CLICK_TO_CONTINUE_MODULE'].'</p>';

        echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';

    }

// ========================================================================== //

    if ($do == 'install_module'){

		if (!cmsUser::isAdminCan('admin/modules', $adminAccess)) { cpAccessDenied(); }

        $error = '';

        $module_id = cmsCore::request('id', 'str', '');

        if(!$module_id){ cmsCore::redirectBack(); }

        if ($inCore->loadModuleInstaller($module_id)){
            $_module = call_user_func('info_module_'.$module_id);
            //////////////////////////////////////
            $error   = call_user_func('install_module_'.$module_id);
        } else {
            $error = $_LANG['AD_MODULE_WIZARD_FAILURE'];
        }

        if ($error === true) {
            $inCore->installModule($_module, $_module['config']);
            cmsCore::addSessionMessage($_LANG['AD_MODULE'].' <strong>"'.$_module['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_INSTALL'], 'success');
            cmsCore::redirect('/admin/index.php?view=modules');
        } else {
            cmsCore::addSessionMessage($error , 'error');
            cmsCore::redirectBack();
        }

    }

// ========================================================================== //

    if ($do == 'upgrade_module'){

		if (!cmsUser::isAdminCan('admin/modules', $adminAccess)) { cpAccessDenied(); }

        $error = '';

        $module_id = cmsCore::request('id', 'str', '');

        if(!$module_id){ cmsCore::redirectBack(); }

        if ($inCore->loadModuleInstaller($module_id)) {
            $_module = call_user_func('info_module_'.$module_id);
            if (isset($_module['link'])) {
                $_module['content'] = $_module['link'];
            }
            $error   = call_user_func('upgrade_module_'.$module_id);
        } else {
            $error = $_LANG['AD_SETUP_WIZARD_FAILURE'];
        }

        if ($error === true) {
            $inCore->upgradeModule($_module, $_module['config']);
            cmsCore::addSessionMessage($_LANG['AD_MODULE'].' <strong>"'.$_module['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_UPDATE'], 'success');
            cmsCore::redirect('/admin/index.php?view=modules');
        } else {
            cmsCore::addSessionMessage($error , 'error');
            cmsCore::redirectBack();
        }

    }

// ========================================================================== //

    if ($do == 'component'){

		if (!cmsUser::isAdminCan('admin/components', $adminAccess)) { cpAccessDenied(); }

      	cpAddPathway($_LANG['AD_SETUP_COMPONENTS'], 'index.php?view=install&do=component');

        $new_components = $inCore->getNewComponents();
        $upd_components = $inCore->getUpdatedComponents();

        echo '<h3>'.$_LANG['AD_SETUP_COMPONENTS'].'</h3>';

        if (!$new_components && !$upd_components){

            echo '<p>'.$_LANG['AD_NO_SEARCH_COMPONENTS'].'</p>';
            echo '<p>'.$_LANG['AD_IF_WANT_SETUP_COMPONENTS'].'</p>'; ?>
            <h3><?php echo $_LANG['AD_TRY_PREMIUM']; ?></h3>
            <div class="advert_iaudio"><a href="http://www.instantvideo.ru/software/iaudio.html"><strong>iAudio</strong></a> &mdash; <?php echo $_LANG['AD_AUDIO_GALERY']; ?></div>
            <div class="advert_billing"><a href="http://www.instantcms.ru/billing/about.html"><strong><?php echo $_LANG['AD_BILLING']; ?></strong></a> &mdash; <?php echo $_LANG['AD_GAIN']; ?></div>
            <div class="advert_inmaps"><a href="http://www.instantmaps.ru/"><strong>InstantMaps</strong></a> &mdash; <?php echo $_LANG['AD_OBJECT_TO_MAP']; ?></div>
            <div class="advert_inshop"><a href="http://www.instantcms.ru/blogs/InstantSoft/professionalnyi-magazin-dlja-InstantCMS.html"><strong>InstantShop</strong></a> &mdash; <?php echo $_LANG['AD_SHOP']; ?></div>
            <div class="advert_invideo"><a href="http://www.instantvideo.ru/software/instantvideo.html"><strong>InstantVideo</strong></a> &mdash; <?php echo $_LANG['AD_VIDEO_GALERY']; ?></div>
        <?php return;

        }

        if ($new_components){

            echo '<p><strong>'.$_LANG['AD_COMPONENTS_SETUP'].'</strong></p>';
            componentsList($new_components, $_LANG['AD_SETUP'], 'install_component');

        }

        if ($upd_components){

            echo '<p><strong>'.$_LANG['AD_COMPONENTS_UPDATE']	.'</strong></p>';
            componentsList($upd_components, $_LANG['AD_UPDATE'], 'upgrade_component');

        }

        echo '<p>'.$_LANG['AD_CLICK_TO_CONTINUE_COMPONENT'].'</p>';

        echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';

    }

// ========================================================================== //

    if ($do == 'install_component'){

        $error = '';

        $component = cmsCore::request('id', 'str', '');
        if(!$component){ cmsCore::redirectBack(); }

		if (!cmsUser::isAdminCan('admin/components', $adminAccess)) { cpAccessDenied(); }

        if ($inCore->loadComponentInstaller($component)){
            $_component = call_user_func('info_component_'.$component);
            $error      = call_user_func('install_component_'.$component);
        } else {
            $error = $_LANG['AD_COMPONENT_WIZARD_FAILURE'];
        }

        if ($error === true) {
            $inCore->installComponent($_component, $_component['config']);

            $info_text = '<p>'.$_LANG['AD_COMPONENT'].' <strong>"'.$_component['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_INSTALL'].'</p>';
            if (isset($_component['modules'])){
                if(is_array($_component['modules'])){
                    $info_text .= '<p>'.$_LANG['AD_OPT_INSTALL_MODULES'].':</p>';
                    $info_text .= '<ul>';
                        foreach($_component['modules'] as $module=>$title){
                            $info_text .= '<li>'.$title.'</li>';
                        }
                    $info_text .= '</ul>';
                }
            }
            if (isset($_component['plugins'])){
                if(is_array($_component['plugins'])){
                    $info_text .= '<p>'.$_LANG['AD_OPT_INSTALL_PLUGINS'].':</p>';
                    $info_text .= '<ul>';
                        foreach($_component['plugins'] as $module=>$title){
                            $info_text .= '<li>'.$title.'</li>';
                        }
                    $info_text .= '</ul>';
                }
            }

            cmsCore::addSessionMessage($info_text, 'success');
            cmsCore::redirect('/admin/index.php?view=components');
        } else {
            cmsCore::addSessionMessage($error , 'error');
            cmsCore::redirectBack();
        }

    }

// ========================================================================== //

    if ($do == 'upgrade_component'){

        cpAddPathway($_LANG['AD_UPDATE_COMPONENTS'], 'index.php?view=install&do=component');

        $error = '';

        $component = cmsCore::request('id', 'str', '');
        if(!$component){ cmsCore::redirectBack(); }

		if (!cmsUser::isAdminCan('admin/components', $adminAccess)) { cpAccessDenied(); }
		if (!cmsUser::isAdminCan('admin/com_'.$component, $adminAccess)) { cpAccessDenied(); }

        if ($inCore->loadComponentInstaller($component)) {
            $_component = call_user_func('info_component_'.$component);
            $error      = call_user_func('upgrade_component_'.$component);
        } else {
            $error = $_LANG['AD_COMPONENT_WIZARD_FAILURE'];
        }

        if ($error === true) {
            $inCore->upgradeComponent($_component, $_component['config']);
            $info_text = $_LANG['AD_COMPONENT'].' <strong>"'.$_component['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_UPDATE'];
            cmsCore::addSessionMessage($info_text, 'success');
            cmsCore::redirect('/admin/index.php?view=components');
        } else {
            cmsCore::addSessionMessage($error , 'error');
            cmsCore::redirectBack();
        }

    }

// ========================================================================== //

    if ($do == 'remove_component'){

        $component_id = cmsCore::request('id', 'int', '');

        if(!$component_id){ cmsCore::redirectBack(); }

		$com = $inCore->getComponentById($component_id);
		if (!cmsUser::isAdminCan('admin/components', $adminAccess)) { cpAccessDenied(); }
		if (!cmsUser::isAdminCan('admin/com_'.$com, $adminAccess)) { cpAccessDenied(); }

        if ($inCore->loadComponentInstaller($com)){
			if(function_exists('remove_component_'.$com)){
            	call_user_func('remove_component_'.$com);
			}
        }

        $inCore->removeComponent($component_id);

        cmsCore::addSessionMessage($_LANG['AD_COMPONENT_IS_DELETED'], 'success');

        cmsCore::redirect('/admin/index.php?view=components');

    }

// ========================================================================== //

    if ($do == 'plugin'){

		if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }

      	cpAddPathway($_LANG['AD_SETUP_PLUGINS']	, 'index.php?view=install&do=plugin');

        $new_plugins = $inCore->getNewPlugins();
        $upd_plugins = $inCore->getUpdatedPlugins();

        echo '<h3>'.$_LANG['AD_SETUP_PLUGINS'].'</h3>';

        if (!$new_plugins && !$upd_plugins){

            echo '<p>'.$_LANG['AD_NO_SEARCH_PLUGINS'].'</p>';
            echo '<p>'.$_LANG['AD_IF_WANT_SETUP_PLUGINS'].'</p>';
            echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';
            return;

        }

        if ($new_plugins){

            echo '<p><strong>'.$_LANG['AD_PLUGINS_SETUP']	.'</strong></p>';
            pluginsList($new_plugins, $_LANG['AD_SETUP'], 'install_plugin');

        }

        if ($upd_plugins){

            echo '<p><strong>'.$_LANG['AD_PLUGINS_UPDATE']	.'</strong></p>';
            pluginsList($upd_plugins, $_LANG['AD_UPDATE'], 'upgrade_plugin');

        }

        echo '<p>'.$_LANG['AD_CLICK_TO_CONTINUE_PLUGIN'].'</p>';

        echo '<p><a href="javascript:window.history.go(-1);">'.$_LANG['BACK'].'</a></p>';

    }

// ========================================================================== //

    if ($do == 'install_plugin'){

		if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }

        cpAddPathway($_LANG['AD_SETUP_PLUGIN']	, 'index.php?view=install&do=plugin');

        $error = '';

        $plugin_id = cmsCore::request('id', 'str', '');

        if(!$plugin_id){ cmsCore::redirectBack(); }

        $plugin = $inCore->loadPlugin($plugin_id);

        if (!$plugin) { $error = $_LANG['AD_PLUGIN_FAILURE']	; }

        if (!$error && $plugin->install()) {
            cmsCore::addSessionMessage($_LANG['AD_PLUGIN'].' <strong>"'.$plugin->info['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_INSTALL'].'. '.$_LANG['AD_ENABLE_PLUGIN'], 'success');
            cmsCore::redirect('/admin/index.php?view=plugins');
        }

        if ($error){
            echo '<p style="color:red">'.$error.'</p>';
        }

        echo '<p><a href="index.php?view=install&do=plugin">'.$_LANG['BACK'].'</a></p>';

    }

// ========================================================================== //

    if ($do == 'upgrade_plugin'){

		if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }

        cpAddPathway($_LANG['AD_UPDATE_PLUGIN'], 'index.php?view=install&do=plugin');

        $error = '';

        $plugin_id = cmsCore::request('id', 'str', '');

        if(!$plugin_id){ cmsCore::redirectBack(); }

        $plugin = $inCore->loadPlugin($plugin_id);

        if (!$plugin) { $error = $_LANG['AD_PLUGIN_FAILURE']; }

        if (!$error && $plugin->upgrade()) {
            cmsCore::addSessionMessage($_LANG['AD_PLUGIN'].' <strong>"'.$plugin->info['title'].'"</strong> '.$_LANG['AD_SUCCESS'].$_LANG['AD_IS_UPDATE'], 'success');
            cmsCore::redirect('/admin/index.php?view=plugins');
        }

        if ($error){
            echo '<p style="color:red">'.$error.'</p>';
        }

        echo '<p><a href="index.php?view=install&do=plugin">'.$_LANG['BACK'].'</a></p>';

    }

// ========================================================================== //

    if ($do == 'remove_plugin'){

		if (!cmsUser::isAdminCan('admin/plugins', $adminAccess)) { cpAccessDenied(); }

        $plugin_id = cmsCore::request('id', 'str', '');

        if(!$plugin_id){ cmsCore::redirectBack(); }

        $inCore->removePlugin($plugin_id);

        cmsCore::addSessionMessage($_LANG['AD_REMOVE_PLUGIN_OK'], 'success');
        cmsCore::redirect('/admin/index.php?view=plugins');

    }

}