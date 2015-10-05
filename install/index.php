<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.7                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2016                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

session_start();
setlocale(LC_ALL, "ru_RU.UTF-8");
header('Content-Type: text/html; charset=utf-8');

define('VALID_CMS', 1);
define('PATH', $_SERVER['DOCUMENT_ROOT']);
include(PATH . '/core/cms.php');
cmsCore::includeFile('install/function.php');
cmsCore::loadClass('config');
cmsCore::loadClass('db');
cmsCore::loadClass('user');
cmsCore::loadClass('page');
cmsCore::loadClass('actions');
$inConf = cmsConfig::getInstance();

// Мультиязычная установка
$inConf->lang = isset($_SESSION['inst_lang']) ? $_SESSION['inst_lang'] : $inConf->lang;
$langs        = cmsCore::getDirsList('/languages');
// запрос на смену языка
if(cmsCore::inRequest('lang')){
    $inst_lang = cmsCore::request('lang', 'html', 'ru');
    if(in_array($inst_lang, $langs)){
        $_SESSION['inst_lang'] = $inst_lang;
        $inConf->lang          = $inst_lang;
    }
}

cmsCore::loadLanguage('lang');
cmsCore::loadLanguage('install');

$installed = false;

// Можно делать мультиязычные дампы
$sqldumpdemo  = 'sqldumpdemo.sql';
$sqldumpempty = 'sqldumpempty.sql';
if($inConf->lang != 'ru'){
    $sqldumpempty = (file_exists(PATH . '/install/sqldumpempty_'.$inConf->lang.'.sql')) ?
            'sqldumpempty_'.$inConf->lang.'.sql' : 'sqldumpempty.sql';
    $sqldumpdemo  = (file_exists(PATH . '/install/sqldumpdemo_'.$inConf->lang.'.sql')) ?
            'sqldumpdemo_'.$inConf->lang.'.sql' : $sqldumpempty;
}

////////////////////// процесс установки ////////////////////////////////////////
if (cmsCore::inRequest('install')) {

    $errors = false;

    $_CFG['offtext']   = $_LANG['CFG_OFFTEXT'];
    $_CFG['keywords']  = $_LANG['CFG_KEYWORDS'];
    $_CFG['metadesc']  = $_LANG['CFG_METADESC'];

    $_CFG['sitename']  = cmsCore::request('sitename', 'html', $_LANG['CFG_SITENAME']);
    $_CFG['db_host']   = cmsCore::request('db_server', 'html', '');
    $_CFG['db_base']   = cmsCore::request('db_base', 'html', '');
    $_CFG['db_user']   = cmsCore::request('db_user', 'html', '');
    $_CFG['db_pass']   = cmsCore::request('db_password', 'html', '');
    $_CFG['db_prefix'] = cmsCore::request('db_prefix', 'html', '');
    $_CFG['lang']      = $inConf->lang; // Какой язык выбрали при установке, тот и будет сохранен в конфигурации
    $sql_file = PATH . '/install/' . (cmsCore::request('demodata', 'int') ? $sqldumpdemo : $sqldumpempty);

    $admin_login    = cmsCore::request('admin_login', 'html', '');
    $admin_password = cmsCore::request('admin_password', 'html', '');

    if (!$_CFG['db_host']) {
        cmsCore::addSessionMessage($_LANG['INS_DB_HOST_EMPTY'], 'error');
        $errors = true;
    }
    if (!$_CFG['db_base']) {
        cmsCore::addSessionMessage($_LANG['INS_DB_BASE_EMPTY'], 'error');
        $errors = true;
    }
    if (!$_CFG['db_user']) {
        cmsCore::addSessionMessage($_LANG['INS_DB_USER_EMPTY'], 'error');
        $errors = true;
    }
    if (!$_CFG['db_prefix']) {
        cmsCore::addSessionMessage($_LANG['INS_DB_PREFIX_EMPTY'], 'error');
        $errors = true;
    }
    if (mb_strlen($admin_login) < 3) {
        cmsCore::addSessionMessage($_LANG['INS_ADMIN_LOGIN_EMPTY'], 'error');
        $errors = true;
    }
    if (mb_strlen($admin_password) < 6) {
        cmsCore::addSessionMessage($_LANG['INS_ADMIN_PASS_EMPTY'], 'error');
        $errors = true;
    }

    if ($errors) {
        cmsCore::redirect('/install/');
    }

    $inConf->db_host   = $_CFG['db_host'];
    $inConf->db_user   = $_CFG['db_user'];
    $inConf->db_pass   = $_CFG['db_pass'];
    $inConf->db_base   = $_CFG['db_base'];
    $inConf->db_prefix = $_CFG['db_prefix'];

    $inDB = cmsDatabase::getInstance();

    $inDB->importFromFile($sql_file);

    $d_cfg = $inConf->getDefaultConfig();
    $_CFG = array_merge($d_cfg, $_CFG);
    $inConf->saveToFile($_CFG);

    $sql = "UPDATE cms_users SET password = md5('{$admin_password}'), login = '{$admin_login}' WHERE id = 1";
    $inDB->query($sql);
    $sql = "UPDATE cms_users SET password = md5('{$admin_password}') WHERE id > 1";
    $inDB->query($sql);

    $installed = true;

    cmsCore::getInstance();
    $inUser = cmsUser::getInstance();
    $inUser->update();
    $inUser->signInUser($admin_login, $admin_password, true);

}
// =================================================================================================== //

$info        = check_requirements();
$permissions = check_permissions();
$php_path    = get_program_path('php');
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $_LANG['INS_HEADER'] .' '. CORE_VERSION; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script src='/includes/jquery/jquery.js' type='text/javascript'></script>
        <script src='/install/js/jquery.wizard.js' type='text/javascript'></script>
        <script src='/install/js/install.js' type='text/javascript'></script>
        <link type='text/css' href='/install/css/styles.css' rel='stylesheet' media='screen' />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>

    <body>
        <div class="wrap">
        <?php if(sizeof($langs)>1) { ?>
        <div title="<?php echo $_LANG['TEMPLATE_INTERFACE_LANG']; ?>" id="langs" style="background-image:  url(/templates/_default_/images/icons/langs/<?php echo $inConf->lang; ?>.png);">
            <span><?php echo $inConf->lang; ?></span>
            <ul id="langs-select">
                <?php foreach ($langs as $lng) {
                    if($lng == $inConf->lang){ continue; } ?>
                <li data-lang="<?php echo $lng; ?>" style="background-image:  url(/templates/_default_/images/icons/langs/<?php echo $lng; ?>.png);"><?php echo $lng; ?></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <h1 id="header">
            <?php echo $_LANG['INS_HEADER'] .' '. CORE_VERSION; ?>
        </h1>
        <?php if (!$installed) { ?>
            <!-- ================================================================ -->
            <form class="wizard" action="#" method="post" >
                <div class="wizard-nav">
                    <a href="#start"><?php echo $_LANG['INS_START']; ?></a>
                    <a href="#php"><?php echo $_LANG['INS_CHECK_PHP_TITLE']; ?></a>
                    <a href="#folders"><?php echo $_LANG['INS_CHECK_FOLDER_TITLE']; ?></a>
                    <a href="#install"><?php echo $_LANG['INS_INSTALL']; ?></a>
                </div>
                <?php $messages = cmsCore::getSessionMessages(); ?>
                <?php if ($messages) { ?>
                    <div class="sess_messages">
                        <?php foreach ($messages as $message) { ?>
                            <?php echo $message; ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div id="start" class="wizardpage">
                    <h2><?php echo $_LANG['INS_WELCOME']; ?></h2>
                    <?php echo $_LANG['INS_WELCOME_NOTES']; ?>
                    <p>
                        <label><input type="checkbox" id="license_agree" onClick="checkAgree()" /><?php echo $_LANG['INS_ACCEPT_LICENSE']; ?></label>
                    </p>
                </div>
                <!-- ================================================================ -->
                <div id="php" class="wizardpage">
                    <h2><?php echo $_LANG['INS_CHECK_PHP']; ?></h2>
                    <p><?php echo $_LANG['INS_CHECKPHP_HINT']; ?></p>
                    <h3><?php echo $_LANG['INS_PHP_VERSION']; ?></h3>
                    <table class="grid">
                        <tr>
                            <td><?php echo $_LANG['INS_INSTALL_VERSION']; ?></td>
                            <td class="value">
                                <?php echo html_bool_span($info['php']['version'], $info['php']['valid']); ?>
                            </td>
                        </tr>
                    </table>
                    <h3><?php echo $_LANG['INS_NEED_EXTENTION']; ?></h3>
                    <table class="grid">
                    <?php foreach ($info['ext'] as $name => $valid) { ?>
                        <tr>
                            <td><a href="http://ru2.php.net/manual/ru/book.<?php echo str_replace('math', '', $name) ; ?>.php" target="_blank" title="<?php echo $_LANG['INS_PHPNET_HINT']; ?>"><?php echo $name; ?></a></td>
                            <td class="value">
                            <?php if ($valid) { ?>
                                <?php echo html_bool_span($_LANG['INS_INSTALL_OK'], $valid); ?>
                            <?php } else { ?>
                                <?php echo html_bool_span($_LANG['INS_INSTALL_NOTFOUND'], $valid); ?>
                            <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </table>
                </div>
                <!-- ================================================================ -->
                <div id="folders" class="wizardpage">
                    <h2><?php echo $_LANG['INS_CHECK_FOLDER']; ?></h2>
                    <?php echo $_LANG['INS_FOLDERS_NOTES']; ?>
                    <table class="grid">
                    <?php foreach ($permissions as $name => $permission) { ?>
                        <tr>
                            <td>/<?php echo $name;
                                echo $permission['perm'] ? ' | '.$_LANG['INS_PERMISSION'] .' '. $permission['perm'] : ''; ?></td>
                            <td class="value">
                            <?php if ($permission['valid']) { ?>
                                <?php echo html_bool_span($_LANG['INS_PERMISSION_OK'], $permission['valid']); ?>
                            <?php } else { ?>
                                <?php echo html_bool_span($_LANG['INS_PERMISSION_NO'], $permission['valid']); ?>
                            <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </table>
                </div>
                <!-- ================================================================ -->
                <div id="install" class="wizardpage">
                    <h2><?php echo $_LANG['INS_INSTALL']; ?></h2>
                    <p><?php echo $_LANG['INS_FORM_INSERT']; ?></p>
                    <table class="instal_data">
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_SITE']; ?></td>
                            <td><input name="sitename" type="text" class="txt" value="<?php echo $_LANG['CFG_SITENAME']; ?>"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_LOGIN']; ?></td>
                            <td><input name="admin_login" type="text" class="txt" value="admin"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_PASS']; ?></td>
                            <td><input name="admin_password" type="password" placeholder="<?php echo $_LANG['INS_ADMIN_PASS_6']; ?>" class="txt"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_MYSQL']; ?></td>
                            <td align="center"><input name="db_server" type="text" class="txt" value="localhost"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_BDNAME']; ?></td>
                            <td><input name="db_base" type="text" class="txt"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_BDUSER']; ?></td>
                            <td><input name="db_user" type="text" class="txt" value=""></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_BDPASS']; ?> </td>
                            <td><input name="db_password" type="password" class="txt"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_PREFIX']; ?></td>
                            <td><input name="db_prefix" type="text" class="txt" value="cms"></td>
                        </tr>
                        <tr>
                            <td><?php echo $_LANG['INS_FORM_DEMO']; ?></td>
                            <td align="center" valign="top">
                                <?php if($sqldumpdemo == $sqldumpempty){ ?>
                                    <label><input disabled="true" name="demodata" type="radio" value="1" /><?php echo $_LANG['YES']; ?></label>
                                    <label><input disabled="true" name="demodata" type="radio" value="0" checked="true" /> <?php echo $_LANG['NO']; ?></label>
                                <?php } else { ?>
                                    <label><input name="demodata" type="radio" value="1" checked /><?php echo $_LANG['YES']; ?></label>
                                    <label><input name="demodata" type="radio" value="0" /> <?php echo $_LANG['NO']; ?></label>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                    <div class="hint_text"><?php echo $_LANG['INS_FORM_NOTES']; ?></div>
                </div>
            </form>
<?php } else { ?>
            <div class="result_link">
                <a href="/"><?php echo $_LANG['INS_GO_SITE']; ?></a>
                <a href="/admin"><?php echo $_LANG['INS_GO_CP']; ?></a>
                <a id="tutorial" target="_blank" href="http://www.instantcms.ru/wiki/doku.php"><?php echo $_LANG['INS_GO_HANDBOOK']; ?></a>
                <a id="tutorial" target="_blank" href="http://addons.instantcms.ru/"><?php echo $_LANG['INS_GO_ADDONS']; ?></a>
                <a id="tutorial" target="_blank" href="https://github.com/instantsoft/icms1">GitHub</a>
            </div>
            <div class="sess_messages">
                <div class="message_success"><?php echo $_LANG['INS_FORM_SUCCESS']; ?></div>
            </div>
            <div class="wizardpage">
                <h2><?php echo $_LANG['INS_CRON_TODO']; ?></h2>
                <p>
                    <?php echo $_LANG['INS_CRON_NOTES']; ?>
                </p>
                <pre><?php if($php_path) { echo $php_path; } else { ?>php<?php } ?> -f <?php echo PATH; ?>/cron.php <?php echo $_SERVER['HTTP_HOST']; ?> > /dev/null</pre>
                <p>
                    <?php echo $_LANG['INS_FEEDBACK_SUPPORT']; ?>
                </p>
                <h2><?php echo $_LANG['INS_ATTENTION']; ?></h2>
                <p><?php echo $_LANG['INS_DELETE_TODO']; ?></p>
            </div>
<?php } ?>
        </div>
        <div id="footer">
            <div>
                <a href="http://www.instantcms.ru/" target="_blank">InstantCMS</a>, <a href="http://instantsoft.ru/" target="_blank">InstantSoft</a> &copy; 2007-<?php echo date('Y'); ?>
            </div>
        </div>
        <script>
            <?php echo cmsPage::getLangJS('INS_DO_INSTALL'); ?>
            <?php echo cmsPage::getLangJS('INS_NEXT'); ?>
            <?php echo cmsPage::getLangJS('INS_BACK'); ?>
        </script>
    </body>
</html>