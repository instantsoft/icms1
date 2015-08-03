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

function applet_cron(){

    cmsCore::loadClass('cron');

	global $_LANG;

	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/config', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_CRON_MISSION'];
 	cpAddPathway($_LANG['AD_SITE_SETTING'], 'index.php?view=config');
 	cpAddPathway($_LANG['AD_CRON_MISSION'], 'index.php?view=cron');

    $do = cmsCore::request('do', 'str', 'list');
    $id = cmsCore::request('id', 'int', '0');

	if ($do == 'list'){
		$toolmenu = array();
		$toolmenu[0]['icon'] = 'new.gif';
		$toolmenu[0]['title'] = $_LANG['AD_CREATE_CRON_MISSION'];
		$toolmenu[0]['link'] = "?view=cron&do=add";

		cpToolMenu($toolmenu);

        $items = cmsCron::getJobs(false);

        $tpl_file   = 'admin/cron.php';
        $tpl_dir    = file_exists(TEMPLATE_DIR.$tpl_file) ? TEMPLATE_DIR : DEFAULT_TEMPLATE_DIR;

        include($tpl_dir.$tpl_file);
	}

    if ($do == 'show'){

        if ($id){ cmsCron::jobEnabled($id, true);  }
        echo '1'; exit;

	}

	if ($do == 'hide'){

        if ($id){ cmsCron::jobEnabled($id, false);  }
        echo '1'; exit;

	}

	if ($do == 'delete'){

        if ($id) { cmsCron::removeJobById($id); }

        cmsCore::redirect('index.php?view=cron');

	}

	if ($do == 'execute'){

        if ($id) { $job_result = cmsCron::executeJobById($id); }

        if($job_result){
            cmsCore::addSessionMessage($_LANG['AD_MISSION_SUCCESS'], 'success');
        } else {
            cmsCore::addSessionMessage($_LANG['AD_MISSION_ERROR'], 'error');
        }

        cmsCore::redirect('index.php?view=cron');

	}

	if ($do == 'submit'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

        $job_name       = cmsCore::request('job_name', 'str');
        $comment        = cmsCore::request('comment', 'str');
        $job_interval   = cmsCore::request('job_interval', 'int');
        $enabled        = cmsCore::request('enabled', 'int');
        $component      = cmsCore::request('component', 'str');
        $model_method   = cmsCore::request('model_method', 'str');
        $custom_file    = cmsCore::request('custom_file', 'str');
        $custom_file    = (mb_stripos($custom_file, 'image') || mb_stripos($custom_file, 'upload') || mb_stripos($custom_file, 'cache')) ? '' : $custom_file;
        $custom_file    = preg_replace('/\.+\//', '', $custom_file);
        $class_name     = cmsCore::request('class_name', 'str');
        $class_method   = cmsCore::request('class_method', 'str');

        cmsCron::registerJob($job_name, array(
                                        'interval' => $job_interval,
                                        'component' => $component,
                                        'model_method' => $model_method,
                                        'comment' => $comment,
                                        'custom_file' => $custom_file,
                                        'enabled' => $enabled,
                                        'class_name' => $class_name,
                                        'class_method' => $class_method
                                  ));

        cmsCore::redirect('index.php?view=cron');

	}

	if ($do == 'update'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

        if (!$id) { cmsCore::halt(); }

        $job_name       = cmsCore::request('job_name', 'str');
        $comment        = cmsCore::request('comment', 'str');
        $job_interval   = cmsCore::request('job_interval', 'int');
        $enabled        = cmsCore::request('enabled', 'int');
        $component      = cmsCore::request('component', 'str');
        $model_method   = cmsCore::request('model_method', 'str');
        $custom_file    = cmsCore::request('custom_file', 'str');
        $custom_file    = (mb_stripos($custom_file, 'image') || mb_stripos($custom_file, 'upload') || mb_stripos($custom_file, 'cache')) ? '' : $custom_file;
        $custom_file    = preg_replace('/\.+\//', '', $custom_file);
        $class_name     = cmsCore::request('class_name', 'str');
        $class_method   = cmsCore::request('class_method', 'str');

        cmsCron::updateJob($id, array(
                                        'job_name' => $job_name,
                                        'job_interval' => $job_interval,
                                        'component' => $component,
                                        'model_method' => $model_method,
                                        'comment' => $comment,
                                        'custom_file' => $custom_file,
                                        'is_enabled' => $enabled,
                                        'class_name' => $class_name,
                                        'class_method' => $class_method
                                  ));

        cmsCore::redirect('index.php?view=cron');

	}

   if ($do == 'edit' || $do== 'add'){

 		$toolmenu = array();
		$toolmenu[0]['icon'] = 'save.gif';
		$toolmenu[0]['title'] = $_LANG['SAVE'];
		$toolmenu[0]['link'] = 'javascript:document.addform.submit();';

		$toolmenu[1]['icon'] = 'cancel.gif';
		$toolmenu[1]['title'] = $_LANG['CANCEL'];
		$toolmenu[1]['link'] = 'javascript:history.go(-1);';

		cpToolMenu($toolmenu);

		if ($do=='edit'){

            $mod = cmsCron::getJobById($id);

             echo '<h3>'.$_LANG['AD_EDIT_MISSION'].'</h3>';
             cpAddPathway($mod['job_name'], 'index.php?view=cron&do=edit&id='.$mod['id']);

		} else {
            echo '<h3>'.$_LANG['AD_CREATE_CRON_MISSION'].'</h3>';
            cpAddPathway($_LANG['AD_CREATE_CRON_MISSION'], 'index.php?view=cron&do=add');
		}
	?>

    <form action="index.php?view=cron" method="post" enctype="multipart/form-data" name="addform" id="addform">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="750" border="0" cellpadding="0" cellspacing="10" class="proptable">
            <tr>
                <td width="300" valign="middle">
                    <strong><?php echo $_LANG['TITLE']; ?>:</strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_ONLY_LATIN']; ?></span>
                </td>
                <td width="" valign="middle">
                    <input name="job_name" type="text" style="width:220px" value="<?php echo @$mod['job_name'];?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['DESCRIPTION']; ?>: </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_ONLY_200_SIMBOLS']; ?></span>
                </td>
                <td valign="middle">
                    <input name="comment" type="text" maxlength="200" style="width:400px" value="<?php echo htmlspecialchars($mod['comment']);?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_MISSION_ON']; ?> </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_MISSION_OFF'] ; ?></span>
                </td>
                <td valign="middle">
                    <label>
                        <input name="enabled" type="radio" value="1" <?php if ($mod['is_enabled']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?>
                    </label>
                    <label>
                        <input name="enabled" type="radio" value="0"  <?php if (!$mod['is_enabled']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_MISSION_INTERVAL']; ?>:</strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_MISSION_PERIOD']; ?></span>
                </td>
                <td valign="middle">
                    <input name="job_interval" type="text" maxlength="4" style="width:50px" value="<?php echo @$mod['job_interval'];?>" /> <?php echo $_LANG['HOUR1'].'.'; ?>
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_PHP_FILE']; ?> </strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_EXAMPLE'] ; ?>: <strong>includes/myphp/test.php</strong></span><br/>
                </td>
                <td valign="middle">
                    <input name="custom_file" type="text" maxlength="250" style="width:220px" value="<?php echo @$mod['custom_file'];?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_COMPONENT']; ?>: </strong><br/>
                </td>
                <td valign="middle">
                    <input name="component" type="text" maxlength="250" style="width:220px" value="<?php echo @$mod['component'];?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_METHOD']; ?> </strong><br/>
                </td>
                <td valign="middle">
                    <input name="model_method" type="text" maxlength="250" style="width:220px" value="<?php echo @$mod['model_method'];?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo icms_ucfirst($_LANG['AD_CLASS']); ?></strong><br/>
                    <span class="hinttext">
                        <span style="color:#666;font-family: mono"><?php echo $_LANG['AD_FILE_CLASS']; ?></span>, <?php echo $_LANG['AD_EXAMPLE']; ?> <strong>actions|cmsActions</strong>&nbsp;<?php echo $_LANG['OR']; ?><br/>
                        <span style="color:#666;font-family: mono"><?php echo $_LANG['AD_CLASS']; ?></span>, <?php echo $_LANG['AD_EXAMPLE']; ?> <strong>cmsDatabase</strong>
                    </span>
                </td>
                <td valign="top">
                    <input name="class_name" type="text" maxlength="50" style="width:220px" value="<?php echo @$mod['class_name'];?>" />
                </td>
            </tr>
            <tr>
                <td width="" valign="middle">
                    <strong><?php echo $_LANG['AD_CLASS_METHOD']; ?> </strong><br/>
                </td>
                <td valign="middle">
                    <input name="class_method" type="text" maxlength="50" style="width:220px" value="<?php echo @$mod['class_method'];?>" />
                </td>
            </tr>
        </table>
        <p>
		  <?php if($do=='edit'){ ?>
	          <input name="do" type="hidden" id="do" value="update" />
	          <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['AD_SAVE_CRON_MISSION']; ?>" />
		  <?php } else { ?>
	          <input name="do" type="hidden" id="do" value="submit" />
	          <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['AD_CREATE_CRON_MISSION'] ; ?>" />
		  <?php } ?>
          <span style="margin-top:15px">
          <input name="back2" type="button" id="back2" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.history.back();" />
          </span>
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

?>