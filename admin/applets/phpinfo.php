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

function applet_phpinfo(){

    global $_LANG;
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/config', $adminAccess)) { cpAccessDenied(); }

	$GLOBALS['cp_page_title'] = $_LANG['AD_PHP_INFO'];

	cpAddPathway($_LANG['AD_SITE_SETTING'], 'index.php?view=config');
	cpAddPathway($_LANG['AD_PHP_INFO'], 'index.php?view=phpinfo');

?>
<div>

	<h3><?php echo $_LANG['AD_PHP_INFO']; ?></h3>

    <iframe src="/admin/includes/phpinfo.php" style="border:none;width:100%;height:600px" />

</div>
<?php } ?>
