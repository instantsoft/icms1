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

@set_time_limit(0);

session_start();

header('Content-Type: text/html; charset=utf-8');
define('VALID_CMS', 1);

define('PATH', $_SERVER['DOCUMENT_ROOT']);

include(PATH.'/core/cms.php');
$inCore = cmsCore::getInstance(false, true);

cmsCore::loadClass('user');
cmsCore::loadClass('cron');
cmsCore::loadClass('actions');
cmsCore::loadClass('page');

$inConf = cmsConfig::getInstance();
$inDB   = cmsDatabase::getInstance();

// принудительно включаем дебаг
$inConf->debug = 1;

$version_prev = '1.10.5';
$version_next = '1.10.6';

// ========================================================================== //
// ========================================================================== //
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>InstantCMS - Миграция базы данных</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<style type="text/css">
	body { font-family:Arial; font-size:14px; }

	a { color: #0099CC; }
	a:hover { color: #375E93; }
	h2 { color: #375E93; }

	#wrapper { padding:10px 30px; }
	#wrapper p{ line-height: 20px; }

	.migrate p {
				   line-height:16px;
				   padding-left:20px;
				   margin:2px;
				   margin-left:20px;
				   background:url(/admin/images/actions/on.gif) no-repeat;
			   }
	.migrate p.info {
                   font-size: 16px;
				   background: none;
                   color: #C00;
			   }
	.important {
				   margin:20px;
				   margin-left:0px;
				   border:solid 1px silver;
				   padding:15px;
				   padding-left:65px;
				   background:url(important.png) no-repeat 15px 15px;
			   }
	 .nextlink {
				   margin-top:15px;
				   font-size:18px;
	 }
  </style>
<div id="wrapper" class="migrate">
<?php
    echo "<h2>Миграция базы данных InstantCMS {$version_prev} &rarr; {$version_next}</h2>";

	if(!cmsCore::inRequest('go')){
		echo '<h3><a href="/migrate/index.php?go=1">начать миграцию...</a></h3>';
		exit;
	}

// ========================================================================== //
// ========================================================================== //
	$step = cmsCore::request('go', 'int', 0);

    echo '<h3>Шаг № '.$step.'</h3>';

// ========================================================================== //
// ========================================================================== //

	if($step == 1){

        // ========================================================================== //
        // ========================================================================== //
        if (!$inDB->isFieldExists('cms_menu', 'titles')){
            $inDB->query("ALTER TABLE `cms_menu` ADD `titles` TINYTEXT NOT NULL DEFAULT '' AFTER `title`");
            echo '<p>Поле titles добавлено в таблицу cms_menu.</p>';
        }
        if (!$inDB->isFieldExists('cms_modules', 'titles')){
            $inDB->query("ALTER TABLE `cms_modules` ADD `titles` TINYTEXT NOT NULL DEFAULT '' AFTER `title`");
            echo '<p>Поле titles добавлено в таблицу cms_modules.</p>';
        }
        if (!$inDB->isFieldExists('cms_user_profiles', 'showphone')){
            $inDB->query("ALTER TABLE `cms_user_profiles` ADD `showphone` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `showicq`");
            echo '<p>Поле showphone добавлено в таблицу cms_user_profiles.</p>';
        }
        if (!$inDB->isFieldExists('cms_users', 'phone')){
            $inDB->query("ALTER TABLE `cms_users` ADD `phone` VARCHAR( 12 ) NOT NULL DEFAULT '' AFTER `email`");
            echo '<p>Поле phone добавлено в таблицу cms_users.</p>';
        }

        $inDB->query("ALTER TABLE `cms_event_hooks` DROP INDEX `event`");
        $inDB->query("ALTER TABLE `cms_event_hooks` ADD INDEX(`plugin_id`)");

        cmsUser::registerGroupAccessType('comments/target_author_delete', 'Удаление неугодных комментариев к своим публикациям', 1);
        echo '<p>В права доступа добавлено новое правило "Удаление неугодных комментариев к своим публикациям"</p>';

        if(!$inDB->rows_count('cms_plugins', "plugin='p_related_posts'", 1)){
            $plugin = $inCore->loadPlugin('p_related_posts');
            $plugin->install();
            echo '<p>Плагин "Похожие записи в блогах" установлен.</p>';
        }

        $inDB->query("CREATE TABLE IF NOT EXISTS `cms_translations_fields` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `target` varchar(50) NOT NULL DEFAULT '',
                        `fields` varchar(500) NOT NULL DEFAULT '',
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `target` (`target`)
                      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

        $inDB->query("CREATE TABLE IF NOT EXISTS `cms_translations` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `lang` varchar(10) NOT NULL DEFAULT '',
                        `data` longtext NOT NULL,
                        `fieldsset_id` int(11) unsigned NOT NULL,
                        `target_id` int(11) NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `target_id` (`target_id`,`fieldsset_id`,`lang`)
                      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

        translations::registerFields('content_content', array(
            'title'=>'str',
            'description'=>'html',
            'content'=>'html',
            'pagetitle'=>'str',
            'meta_desc'=>'str',
            'meta_keys'=> 'str'
        ));
        translations::registerFields('content_category', array(
            'title'=>'str',
            'description'=>'html',
            'pagetitle'=>'str',
            'meta_desc'=>'str',
            'meta_keys'=> 'str'
        ));
        translations::registerFields('forum_forums', array(
            'title'=>'str',
            'description'=>'str',
            'pagetitle'=>'str',
            'meta_desc'=>'str',
            'meta_keys'=> 'str'
        ));
        translations::registerFields('forum_forum_cats', array(
            'title'=>'str',
            'pagetitle'=>'str',
            'meta_keys'=>'str',
            'meta_desc'=>'str'
        ));

        echo '<p>Поддержка мультиязычности компонентов контент и форум выполнена</p>';

		echo '<div style="margin:15px 0px;font-weight:bold">Миграция завершена. Удалите папку /migrate/ прежде чем продолжить!</div>';
		echo '<div class="nextlink"><a href="/">Перейти на сайт</a></div>';

	}
// ========================================================================== //
// ========================================================================== //

    echo '</div></body></html>';