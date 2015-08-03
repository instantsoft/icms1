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

function applet_config(){

    // получаем оригинальный конфиг
    $config = cmsConfig::getDefaultConfig();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();

	global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/config', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_SITE_SETTING'];

	cpAddPathway($_LANG['AD_SITE_SETTING'], 'index.php?view=config');

	$do = cmsCore::request('do', 'str', 'list');

	if ($do == 'save'){

        if (!cmsCore::validateForm()) { cmsCore::error404(); }

		$newCFG = array();
		$newCFG['sitename'] 	= stripslashes(cmsCore::request('sitename', 'str', ''));
		$newCFG['title_and_sitename'] = cmsCore::request('title_and_sitename', 'int', 0);
		$newCFG['title_and_page'] = cmsCore::request('title_and_page', 'int', 0);

        $newCFG['hometitle'] 	= stripslashes(cmsCore::request('hometitle', 'str', ''));
        $newCFG['homecom']      = cmsCore::request('homecom', 'str', '');

		$newCFG['siteoff'] 		= cmsCore::request('siteoff', 'int', 0);
		$newCFG['debug'] 		= cmsCore::request('debug', 'int', 0);
		$newCFG['offtext'] 		= htmlspecialchars(cmsCore::request('offtext', 'str', ''), ENT_QUOTES);
		$newCFG['keywords'] 	= cmsCore::request('keywords', 'str', '');
		$newCFG['metadesc'] 	= cmsCore::request('metadesc', 'str', '');
		$newCFG['seourl']       = cmsCore::request('seourl', 'int', 0);
		$newCFG['lang']         = cmsCore::request('lang', 'str', 'ru');
		$newCFG['is_change_lang'] = cmsCore::request('is_change_lang', 'int', 0);

		$newCFG['sitemail'] 	= cmsCore::request('sitemail', 'str', '');
		$newCFG['sitemail_name'] = cmsCore::request('sitemail_name', 'str', '');
		$newCFG['wmark']        = cmsCore::request('wmark', 'str', '');
		$newCFG['template'] 	= cmsCore::request('template', 'str', '');
		$newCFG['splash'] 		= cmsCore::request('splash', 'int', 0);
		$newCFG['slight'] 		= cmsCore::request('slight', 'int', 0);
		$newCFG['db_host'] 		= $config['db_host'];
		$newCFG['db_base'] 		= $config['db_base'];
		$newCFG['db_user'] 		= $config['db_user'];
		$newCFG['db_pass'] 		= $config['db_pass'];
		$newCFG['db_prefix']	= $config['db_prefix'];
		$newCFG['show_pw']		= cmsCore::request('show_pw', 'int', 0);
		$newCFG['last_item_pw'] = cmsCore::request('last_item_pw', 'int', 0);
		$newCFG['index_pw']		= cmsCore::request('index_pw', 'int', 0);
		$newCFG['fastcfg']		= cmsCore::request('fastcfg', 'int', 0);

		$newCFG['mailer'] 		= cmsCore::request('mailer', 'str', '');
		$newCFG['smtpsecure']   = cmsCore::request('smtpsecure', 'str', '');
		$newCFG['smtpauth']		= cmsCore::request('smtpauth', 'int', 0);
		$newCFG['smtpuser']		= cmsCore::inRequest('smtpuser') ?
                                    cmsCore::request('smtpuser', 'str', '') :
                                    $config['smtpuser'];
		$newCFG['smtppass']		= cmsCore::inRequest('smtppass') ?
                                    cmsCore::request('smtppass', 'str', '') :
                                    $config['smtppass'];
		$newCFG['smtphost']		= cmsCore::request('smtphost', 'str', '');
		$newCFG['smtpport']		= cmsCore::request('smtpport', 'int', '25');

        $newCFG['timezone']		= cmsCore::request('timezone', 'str', '');
        $newCFG['timediff']		= cmsCore::request('timediff', 'str', '');
        $newCFG['user_stats']	= cmsCore::request('user_stats', 'int', 0);

        $newCFG['seo_url_count'] = cmsCore::request('seo_url_count', 'int', 0);
		$newCFG['allow_ip']		 = cmsCore::request('allow_ip', 'str', '');

		if (cmsConfig::saveToFile($newCFG)){
			cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'] , 'success');
        } else {
			cmsCore::addSessionMessage($_LANG['AD_CONFIG_SITE_ERROR'], 'error');
        }

        cmsCore::clearCache();

		cmsCore::redirect('index.php?view=config');

	}

?>
<div>

      <?php cpCheckWritable('/includes/config.inc.php'); ?>

<div id="config_tabs" class="uitabs">

  <ul id="tabs">
	  	<li><a href="#basic"><span><?php echo $_LANG['AD_SITE']; ?></span></a></li>
	  	<li><a href="#home"><span><?php echo $_LANG['AD_MAIN']; ?></span></a></li>
		<li><a href="#design"><span><?php echo $_LANG['AD_DESIGN']; ?></span></a></li>
		<li><a href="#time"><span><?php echo $_LANG['AD_TIME'] ; ?></span></a></li>
		<li><a href="#database"><span><?php echo $_LANG['AD_DB'] ; ?></span></a></li>
		<li><a href="#mail"><span><?php echo $_LANG['AD_POST']; ?></span></a></li>
		<li><a href="#other"><span><?php echo $_LANG['AD_PATHWAY']; ?></span></a></li>
		<li><a href="#seq"><span><?php echo $_LANG['AD_SECURITY']; ?></span></a></li>
  </ul>

	<form action="/admin/index.php?view=config" method="post" name="CFGform" target="_self" id="CFGform" style="margin-bottom:30px">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <div id="basic">
			<table width="720" border="0" cellpadding="5">
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_SITENAME']; ?></strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_USE_HEADER']; ?></span>
                    </td>
					<td width="350" valign="top">
                        <input name="sitename" type="text" id="sitename" value="<?php echo htmlspecialchars($config['sitename']);?>" style="width:358px" />
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_TAGE_ADD']; ?></strong>
                    </td>
					<td valign="top">
						<label><input name="title_and_sitename" type="radio" value="1" <?php if ($config['title_and_sitename']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="title_and_sitename" type="radio" value="0" <?php if (!$config['title_and_sitename']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_TAGE_ADD_PAGINATION'] ; ?></strong>
                    </td>
					<td valign="top">
						<label><input name="title_and_page" type="radio" value="1" <?php if ($config['title_and_page']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="title_and_page" type="radio" value="0" <?php if (!$config['title_and_page']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['TEMPLATE_INTERFACE_LANG']; ?>:</strong>
                    </td>
					<td width="350" valign="top">
                        <select name="lang" id="lang" style="width:364px">
                            <?php $langs = cmsCore::getDirsList('/languages');
                            foreach ($langs as $lng) {
                                echo '<option value="'.$lng.'" '.($config['lang'] == $lng ? 'selected="selected"': '').'>'.$lng.'</option>';
                            }
                            ?>
                        </select>
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_SITE_LANGUAGE_CHANGE']; ?></strong><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_VIEW_FORM_LANGUAGE_CHANGE']; ?></span>
                    </td>
					<td valign="top">
						<label><input name="is_change_lang" type="radio" value="1" <?php if ($config['is_change_lang']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="is_change_lang" type="radio" value="0" <?php if (!$config['is_change_lang']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_SITE_ON']; ?></strong><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_ONLY_ADMINS'] ; ?></span>
                    </td>
					<td valign="top">
                        <label><input name="siteoff" type="radio" value="0" <?php if (!$config['siteoff']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
                        <label><input name="siteoff" type="radio" value="1" <?php if ($config['siteoff']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
                </tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_DEBUG_ON']; ?></strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_WIEW_DB_ERRORS']; ?></span>
                    </td>
					<td valign="top">
						<label><input name="debug" type="radio" value="1" <?php if ($config['debug']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="debug" type="radio" value="0" <?php if (!$config['debug']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
				</tr>
				<tr>
					<td valign="middle">
                        <strong><?php echo $_LANG['AD_WHY_STOP'] ; ?></strong><br />
						<span class="hinttext"><?php echo $_LANG['AD_VIEW_WHY_STOP']; ?></span>

                    </td>
					<td valign="top"><input name="offtext" type="text" id="offtext" value="<?php echo htmlspecialchars($config['offtext']);?>" style="width:358px" /></td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_WATERMARK']; ?> </strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_WATERMARK_NAME']; ?></span>
                    </td>
					<td>
						<input name="wmark" type="text" id="wmark" value="<?php echo $config['wmark'];?>" style="width:358px" />
                    </td>
				</tr>
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_QUICK_CONFIG'] ; ?></strong> <br />
						<span class="hinttext"><?php echo $_LANG['AD_MODULE_CONFIG'] ; ?></span>
                    </td>
                    <td valign="top">
                        <label><input name="fastcfg" type="radio" value="1" <?php if ($config['fastcfg']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
                        <label><input name="fastcfg" type="radio" value="0" <?php if (!$config['fastcfg']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
                    </td>
				</tr>
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_ONLINESTATS'] ; ?></strong>
                    </td>
                    <td valign="top">
                        <label><input name="user_stats" type="radio" value="0" <?php if (!$config['user_stats']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_NO_ONLINESTATS']; ?></label><br>
                        <label><input name="user_stats" type="radio" value="1" <?php if ($config['user_stats']==1) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_YES_ONLINESTATS']; ?></label><br>
                        <label><input name="user_stats" type="radio" value="2" <?php if ($config['user_stats']==2) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_CRON_ONLINESTATS']; ?></label>
                    </td>
				</tr>
				<tr>
					<td>
                        <strong><?php echo $_LANG['AD_SEO_URL_COUNT']; ?> </strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_SEO_URL_COUNT_HINT']; ?></span>
                    </td>
					<td>
						<input name="seo_url_count" type="text" class="uispin" value="<?php echo $config['seo_url_count'];?>" style="width:50px" />
                    </td>
				</tr>
			</table>
        </div>
        <div id="home">
			<table width="720" border="0" cellpadding="5">
                <tr>
    				<td>
                        <strong><?php echo $_LANG['AD_MAIN_PAGE']; ?></strong><br />
						<span class="hinttext"><?php echo $_LANG['AD_MAIN_SITENAME']; ?></span><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_BROWSER_TITLE']; ?></span>
                    </td>
                    <td width="350" valign="top">
                        <input name="hometitle" type="text" id="hometitle" value="<?php echo htmlspecialchars($config['hometitle']);?>" style="width:358px" />
                    </td>
			    </tr>
				<tr>
					<td valign="top">
						<strong><?php echo $_LANG['AD_KEY_WORDS']; ?></strong><br />
						<span class="hinttext"><?php echo $_LANG['AD_FROM_COMMA']; ?></span>
						<div class="hinttext" style="margin-top:4px"><a style="color:#09C" href="http://tutorial.semonitor.ru/#5" target="_blank"><?php echo $_LANG['AD_WHAT_KEY_WORDS']; ?></a></div>
                    </td>
					<td>
						<textarea name="keywords" style="width:350px" rows="3" id="keywords"><?php echo $config['keywords'];?></textarea>					</td>
				</tr>
				<tr>
					<td valign="top">
						<strong><?php echo $_LANG['AD_DESCRIPTION']; ?></strong><br />
						<span class="hinttext"><?php echo $_LANG['AD_LESS_THAN']; ?></span>
						<div class="hinttext" style="margin-top:4px"><a style="color:#09C" href="http://tutorial.semonitor.ru/#219" target="_blank"><?php echo $_LANG['AD_WHAT_DESCRIPTION']; ?></a></div>
                    </td>
					<td>
						<textarea name="metadesc" style="width:350px" rows="3" id="metadesc"><?php echo $config['metadesc'];?></textarea>
                    </td>
				</tr>
                <tr>
    				<td>
                        <strong><?php echo $_LANG['AD_MAIN_PAGE_COMPONENT']; ?></strong>
                    </td>
                    <td width="350" valign="top">
                        <select name="homecom" style="width:358px">
                            <option value="" <?php if(!$config['homecom']){ ?>selected="selected"<?php } ?>><?php echo $_LANG['AD_ONLY_MODULES']; ?></option>
                            <?php echo cmsCore::getListItems('cms_components', $config['homecom'], 'title', 'ASC', 'internal=0', 'link'); ?>
                        </select>
                    </td>
			    </tr>
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_GATE_PAGE']; ?></strong> <br/>
						<span class="hinttext"><?php echo $_LANG['AD_FIRST_VISIT']; ?></span> <br/>
                        <span class="hinttext"><?php echo $_LANG['AD_FIRST_VISIT_TEMPLATE']; ?></strong></span>
					</td>
					<td valign="top">
						<label><input name="splash" type="radio" value="0" <?php if (!$config['splash']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['HIDE']; ?></label>
						<label><input name="splash" type="radio" value="1" <?php if ($config['splash']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['SHOW']; ?></label>
					</td>
				</tr>
			</table>
        </div>
		<div id="design">
			<table width="720" border="0" cellpadding="5">
				<tr>
					<td valign="top">
                        <div style="margin-top:2px">
                            <strong><?php echo $_LANG['TEMPLATE']; ?>:</strong><br />
                            <span class="hinttext"><?php echo $_LANG['AD_TEMPLATE_FOLDER'] ; ?> </span>
                        </div>
					</td>
					<td>
                        <select name="template" id="template" style="width:350px" onchange="document.CFGform.submit();">
                            <?php $templates = cmsCore::getDirsList('/templates');
                            foreach ($templates as $template) {
                                echo '<option value="'.$template.'" '.($config['template'] == $template ? 'selected="selected"': '').'>'.$template.'</option>';
                            }
                            $tpl_info = $inPage->getCurrentTplInfo();
                            ?>
                        </select>
                            <?php if(file_exists(PATH.'/templates/'.TEMPLATE.'/positions.jpg')){ ?>
                            <script>
                            $(function() {
                                $('#pos').dialog({modal: true, autoOpen: false, closeText: LANG_CLOSE, width: 'auto'});
                            });
                            </script>
                            <a onclick="$('#pos').dialog('open');return false;" href="#" class="ajaxlink"><?php echo $_LANG['AD_TPL_POS']; ?></a>
                                <div id="pos" title="<?php echo $_LANG['AD_TPL_POS']; ?>"><img src="/templates/<?php echo TEMPLATE; ?>/positions.jpg" alt="<?php echo $_LANG['AD_TPL_POS']; ?>" /></div>
                            <?php } ?>
                        <div style="margin-top:5px" class="hinttext">
                            <?php echo sprintf($_LANG['AD_TEMPLATE_INFO'], $tpl_info['author'], $tpl_info['renderer'], $tpl_info['ext']); ?>
                        </div>
					</td>
				</tr>
				<tr>
					<td><strong><?php echo $_LANG['AD_SEARCH_RESULT']; ?></strong></td>
					<td valign="top">
						<label><input name="slight" type="radio" value="1" <?php if ($config['slight']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="slight" type="radio" value="0" <?php if (!$config['slight']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
					</td>
				</tr>
			</table>
		</div>
		<div id="time">
			<table width="720" border="0" cellpadding="5">
				<tr>
					<td valign="top" width="100">
                        <div style="margin-top:2px">
                            <strong><?php echo $_LANG['AD_TIME_ARREA']; ?></strong>
                        </div>
					</td>
					<td>
                        <select name="timezone" id="timezone" style="width:350px">
                            <?php include(PATH.'/admin/includes/timezones.php'); ?>
                            <?php foreach($timezones as $tz) { ?>
                            <option value="<?php echo $tz; ?>" <?php if ($tz == $config['timezone']) { ?>selected="selected"<?php } ?>><?php echo $tz; ?></option>
                            <?php } ?>
                        </select>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_TIME_SLIP']; ?></strong>
					</td>
					<td width="350">
                        <select name="timediff" id="timediff" style="width:60px">
                            <?php for($h=-12; $h<=12; $h++) { ?>
                                <option value="<?php echo $h; ?>" <?php if ($h == $config['timediff']) { ?>selected="selected"<?php } ?>><?php echo ($h > 0 ? '+'.$h : $h); ?></option>
                            <?php } ?>
                        </select>
					</td>
				</tr>
			</table>
		</div>
		<div id="database">
			<table width="720" border="0" cellpadding="5" style="margin-top:15px;">
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_DB_SIZE']; ?></strong>
					</td>
					<td width="350">
                        <?php
                        $result = $inDB->query("SELECT (sum(data_length)+sum(index_length))/1024/1024 as size FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '{$config['db_base']}'", true);
                        if(!$inDB->error()){
                            $s = $inDB->fetch_assoc($result);
                            echo round($s['size'], 2).' '.$_LANG['SIZE_MB'];
                        } else {
                            echo $_LANG['AD_DB_SIZE_ERROR'];
                        }
                        ?>
					</td>
				</tr>
				<tr>
					<td colspan="2"><span class="hinttext"><?php echo $_LANG['AD_MYSQL_CONFIG']; ?></span></td>
				</tr>
			</table>
        </div>
		<div id="mail">
			<table width="720" border="0" cellpadding="5" style="margin-top:15px;">
				<tr>
					<td width="250">
                        <strong><?php echo $_LANG['AD_SITE_EMAIL']; ?> </strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_SITE_EMAIL_POST']; ?></span>
                    </td>
					<td>
						<input name="sitemail" type="text" id="sitemail" value="<?php echo $config['sitemail'];?>" style="width:358px" />
                    </td>
				</tr>
				<tr>
					<td width="250">
                        <strong><?php echo $_LANG['AD_SENDER_EMAIL']; ?></strong><br/>
						<span class="hinttext"><?php echo $_LANG['AD_IF_NOT_HANDLER']; ?></span>
                    </td>
					<td>
						<input name="sitemail_name" type="text" id="sitemail_name" value="<?php echo $config['sitemail_name'];?>" style="width:358px" />
                    </td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_SEND_METHOD']; ?></strong>
					</td>
					<td>
						<select name="mailer" style="width:354px">
							<option value="mail" <?php if ($config['mailer']=='mail') { echo 'selected="selected"'; } ?>><?php echo  $_LANG['AD_PHP_MAILER']; ?></option>
							<option value="sendmail" <?php if ($config['mailer']=='sendmail') { echo 'selected="selected"'; } ?>><?php echo  $_LANG['AD_SEND_MAILER']; ?></option>
							<option value="smtp" <?php if ($config['mailer']=='smtp') { echo 'selected="selected"'; } ?>><?php echo  $_LANG['AD_SMTP_MAILER']; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_ENCRYPTING']; ?></strong>
					</td>
					<td>
						<label><input name="smtpsecure" type="radio" value="" <?php if (!$config['smtpsecure']) { echo 'checked="checked"'; } ?>/><?php echo  $_LANG['NO']; ?></label>
						<label><input name="smtpsecure" type="radio" value="tls" <?php if ($config['smtpsecure']=='tls') { echo 'checked="checked"'; } ?>/> tls</label>
						<label><input name="smtpsecure" type="radio" value="ssl" <?php if ($config['smtpsecure']=='ssl') { echo 'checked="checked"'; } ?>/> ssl</label>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_SMTP_LOGIN']; ?></strong>
					</td>
					<td>
						<label><input name="smtpauth" type="radio" value="1" <?php if ($config['smtpauth']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="smtpauth" type="radio" value="0" <?php if (!$config['smtpauth']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_SMTP_USER']; ?></strong>
					</td>
					<td>
                        <?php if(!$config['smtpuser']){ ?>
                            <input name="smtpuser" type="text" id="smtpuser" value="<?php echo $config['smtpuser'];?>" style="width:350px" />
                        <?php } else { ?>
                            <span class="hinttext"><?php echo  $_LANG['AD_IF_CHANGE_USER']; ?></span>
                        <?php } ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_SMTP_PASS']; ?></strong>
					</td>
					<td>
                        <?php if(!$config['smtppass']){ ?>
                            <input name="smtppass" type="password" id="smtppass" value="<?php echo $config['smtppass'];?>" style="width:350px" />
                        <?php } else { ?>
                            <span class="hinttext"><?php echo  $_LANG['AD_IF_CHANGE_PASS']; ?></span>
                        <?php } ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo  $_LANG['AD_SMTP_HOST']; ?></strong><br>
                        <span class="hinttext"><?php echo  $_LANG['AD_SOME_HOST']; ?></span>
					</td>
					<td>
						<input name="smtphost" type="text" id="smtphost" value="<?php echo $config['smtphost'];?>" style="width:350px" />
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_SMTP_PORT']; ?></strong>
					</td>
					<td>
						<input name="smtpport" type="text" id="smtpport" value="<?php echo $config['smtpport'];?>" style="width:350px" />
					</td>
				</tr>
			</table>
		</div>
		<div id="other">
			<table width="720" border="0" cellpadding="5">
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_VIEW_PATHWAY']; ?></strong><br />
						<span class="hinttext">
                            <?php echo $_LANG['AD_PATH_TO_CATEGORY']; ?>
                        </span>
					</td>
					<td>
						<label><input name="show_pw" type="radio" value="1" <?php if ($config['show_pw']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="show_pw" type="radio" value="0" <?php if (!$config['show_pw']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
					</td>
				</tr>
				<tr>
					<td><strong><?php echo $_LANG['AD_MAINPAGE_PATHWAY']; ?></strong></td>
					<td>
						<label><input name="index_pw" type="radio" value="1" <?php if ($config['index_pw']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['YES']; ?></label>
						<label><input name="index_pw" type="radio" value="0" <?php if (!$config['index_pw']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['NO']; ?></label>
					</td>
				</tr>
				<tr>
					<td><strong><?php echo $_LANG['AD_PAGE_PATHWAY']; ?></strong></td>
					<td>
						<label><input name="last_item_pw" type="radio" value="0" <?php if (!$config['last_item_pw']) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['HIDE']; ?></label>
						<label><input name="last_item_pw" type="radio" value="1" <?php if ($config['last_item_pw'] == 1) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_PAGE_PATHWAY_LINK']; ?></label>
						<label><input name="last_item_pw" type="radio" value="2" <?php if ($config['last_item_pw'] == 2) { echo 'checked="checked"'; } ?>/><?php echo $_LANG['AD_PAGE_PATHWAY_TEXT']; ?></label>
					</td>
				</tr>
			</table>
        </div>
        <div id="seq">
			<table width="720" border="0" cellpadding="5">
				<tr>
					<td>
						<strong><?php echo $_LANG['AD_IP_ADMIN']; ?></strong> <br />
						<span class="hinttext"><?php echo $_LANG['AD_IP_COMMA']; ?></span></td>
				<td valign="top">
					<input name="allow_ip" type="text" id="allow_ip" value="<?php echo htmlspecialchars($config['allow_ip']);?>" style="width:358px" /></td>
				</tr>
			</table>
    <p style="color:#900"><?php echo $_LANG['AD_ATTENTION']; ?></p>
        </div>

	<div align="left">
		<input name="do" type="hidden" id="do" value="save" />
		<input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.history.back();" />
	</div>
</form>
</div></div>
<?php }