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

$_LANG['INS_DB_HOST_EMPTY']              = 'Set database server!';
$_LANG['INS_DB_BASE_EMPTY']              = 'Set database name!';
$_LANG['INS_DB_USER_EMPTY']              = 'Set database user!';
$_LANG['INS_DB_PREFIX_EMPTY']            = 'Set database prefix!';
$_LANG['INS_ADMIN_LOGIN_EMPTY']          = 'Set administrator login, at least 3 symbols!';
$_LANG['INS_ADMIN_PASS_6']               = 'at least 6 characters!';
$_LANG['INS_ADMIN_PASS_EMPTY']           = 'Set administrator password, '.$_LANG['INS_ADMIN_PASS_6'];
$_LANG['INS_HEADER']                     = 'InstantCMS installation';
$_LANG['INS_START']                      = 'Start';
$_LANG['INS_CHECK_PHP_TITLE']            = 'Checking PHP';
$_LANG['INS_CHECK_PHP']                  = 'Checking PHP extended';
$_LANG['INS_CHECK_FOLDER_TITLE']         = 'Checking pemission';
$_LANG['INS_CHECK_FOLDER']               = 'Checking folders pemission';
$_LANG['INS_INSTALL']                    = 'Installation';
$_LANG['INS_DO_INSTALL']                 = 'Install';
$_LANG['INS_WELCOME']                    = 'Welcome';
$_LANG['INS_WELCOME_NOTES']              = '<p>Installation script will check the server for compliance with technical requirements and makes all the necessary steps to get started with InstantCMS.</p><p> InstantCMS can be installed only in the root directory of the site.</p><p> Before starting the installation create a new MySQL database on your hosting. Collation (COLLATION) must be any of utf8_* according to your needs. In most cases this is utf8_general_ci.</p><p> How to install the system on the local computer with OS Windows&trade; for testing: read <a href="http://www.instantcms.ru/wiki/doku.php/local_installation_denwer" target="_blank">instruction</a> at official site.</p><p>InstantCMS is licensed GNU/GPL version 2. You must accept the terms of the license to install the system.</p>';
$_LANG['INS_ACCEPT_LICENSE']             = ' I accept the terms <a target="_blank" href="/license.rus.txt">of the license GNU/GPL</a> (<a target="_blank" href="http://www.gnu.org/licenses/gpl-2.0.html">original in english</a>).';
$_LANG['INS_CHECKPHP_HINT']              = 'For the correct working of InstantCMS php interpreter is needed, version no older than 5.2.0, web server Apache + mod_rewrite (it is possible to use an empty nginx, however .htaccess must be rewritten according to the specifications), server MySQL database must be version 5 or later.<br> The current version of php is pointed out below, required extensions and their availability status are listed.';
$_LANG['INS_PHP_VERSION']                = 'PHP version';
$_LANG['INS_INSTALL_VERSION']            = 'Installed version';
$_LANG['INS_NEED_EXTENTION']             = 'Required PHP extension';
$_LANG['INS_PHPNET_HINT']                = 'See description on the PHP site';
$_LANG['INS_INSTALL_OK']                 = 'Installed';
$_LANG['INS_INSTALL_NOTFOUND']           = 'Not found';
$_LANG['INS_FOLDERS_NOTES']              = '<p>For the correct working of InstantCMS folders that are pointed out below (and their included folders but with the exception of included in "/includes") must be available for recording. Change the permissions with the help of FTP-client or directly at the server using chmod.</p><p>For successful installation there must be permissions for recording at the directory "/includes". For other directories it is possible to ignore the warnings about inaccessibility of the rights for recording, but only at the time of installation.</p><p> We draw your attention to the fact that immediately after installation the directory "/includes" recording permissions should be removed for security reasons. And after the basic configuration of the site file /includes/config.inc.php must be inaccessible for the recording </p>';

$_LANG['INS_PERMISSION']                 = 'Current access permissions';
$_LANG['INS_PERMISSION_OK']              = 'writable';
$_LANG['INS_PERMISSION_NO']              = 'non writable';
$_LANG['INS_FORM_INSERT']                = 'Fill out form and click "Install" to complete the process.';
$_LANG['INS_FORM_SITE']                  = 'Website name: ';
$_LANG['INS_FORM_LOGIN']                 = 'Website Administrator Login: ';
$_LANG['INS_FORM_PASS']                  = 'Website Administrator Password: ';
$_LANG['INS_FORM_MYSQL']                 = 'MySQL server: ';
$_LANG['INS_FORM_BDNAME']                = 'Database name: ';
$_LANG['INS_FORM_BDUSER']                = 'Database user: ';
$_LANG['INS_BDPASS']                     = 'Database user password: ';
$_LANG['INS_FORM_PREFIX']                = 'Prefix tables in the database: ';
$_LANG['INS_FORM_DEMO']                  = 'Demo data: ';
$_LANG['INS_FORM_NOTES']                 = '<p>When installed with a demo data the same password will be set to all users which coincides with the administrator password. The login details of each user can be obtained from the address profile or from the Control Panel. </p><p> Installation may take from a few seconds to minutes depending on the speed of your server.</p>';
$_LANG['INS_FORM_SUCCESS']               = 'Congratulations, installation is completed! The system is installed and ready to be used.';
$_LANG['INS_CRON_TODO']                  = 'Create a task scheduler';
$_LANG['INS_CRON_NOTES']                 = 'Add file <strong>/cron.php</strong> in task schedule in the panel of your hosting.<br/> The interval is &mdash; 24 hours. This will allow the system to perform periodic service tasks. Possible command added to the CRON, looks like: ';
$_LANG['INS_FEEDBACK_SUPPORT']           = 'In case of difficulty, please contact Hosting technical support';
$_LANG['INS_ATTENTION']                  = 'Attention!';
$_LANG['INS_DELETE_TODO']                = 'Before proceeding you want to remove directories "install" and "migrate" from server with all files inside them!';
$_LANG['INS_GO_SITE']                    = 'Go to site';
$_LANG['INS_GO_CP']                      = 'Control Panel';
$_LANG['INS_GO_HANDBOOK']                = 'Handbook for beginners';
$_LANG['INS_GO_ADDONS']                  = 'Addons';
$_LANG['INS_NEXT']                       = 'Next →';
$_LANG['INS_BACK']                       = '← Back';

$_LANG['INS_INCOMPLETE']                 = 'Installation is not completed';
$_LANG['INS_DELETE_INST_MIGRATE']        = 'If the installation process has been completed,<br/> delete folders "install" and "migrate" on the server and reload page.';
$_LANG['INS_RELOAD_PAGE']                = 'Reload page';
$_LANG['CFG_SITENAME']                   = 'My Social Network';
$_LANG['CFG_OFFTEXT']                    = 'The site is under construction';
$_LANG['CFG_KEYWORDS']                   = 'InstantCMS, management system of the site is a free CMS, site engine, CMS, social network engine';
$_LANG['CFG_METADESC']                   = 'InstantCMS is a free management system with social functions';