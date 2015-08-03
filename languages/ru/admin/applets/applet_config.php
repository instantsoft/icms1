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

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

$_LANG['AD_CONFIG_SITE_ERROR'] 				= 'Файл /includes/config.inc.php недоступен для записи';
$_LANG['AD_SITE'] 							= 'Сайт';
$_LANG['AD_DESIGN'] 						= 'Дизайн';
$_LANG['AD_TIME'] 							= 'Время';
$_LANG['AD_DB'] 							= 'База данных';
$_LANG['AD_POST'] 							= 'Почта';
$_LANG['AD_PATHWAY'] 						= 'Глубиномер';
$_LANG['AD_SECURITY'] 						= 'Безопасность';
$_LANG['AD_SITENAME'] 						= 'Название сайта:';
$_LANG['AD_USE_HEADER'] 					= 'Используется в заголовках страниц';
$_LANG['AD_TAGE_ADD'] 						= 'Добавлять в название страницы (тег title) название сайта:';
$_LANG['AD_TAGE_ADD_PAGINATION'] 			= 'Добавлять в название страницы (тег title) при пагинации номера страниц:';
$_LANG['AD_SITE_LANGUAGE_CHANGE'] 			= 'Смена языка интерфейса на сайте:';
$_LANG['AD_VIEW_FORM_LANGUAGE_CHANGE'] 		= 'Показ формы и возможность смены языка интерфейса на сайте';
$_LANG['AD_SITE_ON'] 						= 'Сайт работает:';
$_LANG['AD_ONLY_ADMINS'] 					= 'Отключенный сайт виден только администраторам';
$_LANG['AD_DEBUG_ON'] 						= 'Включить режим отладки:';
$_LANG['AD_WIEW_DB_ERRORS'] 				= 'Показывает ошибки базы данных и тексты запросов';
$_LANG['AD_WHY_STOP'] 						= 'Причина остановки работы:';
$_LANG['AD_VIEW_WHY_STOP'] 					= 'Отображается на главной странице<br/>при отключении сайта';
$_LANG['AD_WATERMARK'] 						= 'Водяной знак для фотографий:';
$_LANG['AD_WATERMARK_NAME'] 				= 'Название картинки в папке /images/';
$_LANG['AD_QUICK_CONFIG'] 					= 'Быстрая настройка:';
$_LANG['AD_MODULE_CONFIG'] 					= 'Если включено, на сайте заголовки модулей снабжаются ссылками &quot;Настроить&quot;.';
$_LANG['AD_MAIN_PAGE'] 						= 'Заголовок главной страницы:';
$_LANG['AD_MAIN_SITENAME'] 					= 'Если не указан, будет совпадать с названием сайта';
$_LANG['AD_BROWSER_TITLE'] 					= 'Показывается в заголовке окна браузера';
$_LANG['AD_KEY_WORDS'] 						= 'Ключевые слова:';
$_LANG['AD_WHAT_KEY_WORDS'] 				= 'Как подобрать ключевые слова?';
$_LANG['AD_DESCRIPTION'] 					= 'Описание:';
$_LANG['AD_WHAT_DESCRIPTION'] 				= 'Как правильно составить описание?';
$_LANG['AD_MAIN_PAGE_COMPONENT'] 			= 'Компонент на главной странице:';
$_LANG['AD_ONLY_MODULES'] 					= '-- Без компонента, только модули --';
$_LANG['AD_GATE_PAGE'] 						= 'Входная страница:';
$_LANG['AD_FIRST_VISIT'] 					= 'Показывается при первом посещении сайта';
$_LANG['AD_FIRST_VISIT_TEMPLATE'] 			= 'Файл: <strong>/templates/&lt;ваш шаблон&gt;/splash/splash.php</strong>';
$_LANG['AD_TEMPLATE_FOLDER'] 				= 'Содержимое папки &quot;/templates&quot;';
$_LANG['AD_TEMPLATE_INFO'] 				    = 'Автор шаблона: "%s";<br>шаблонизатор - "%s";<br>расширение файлов - "%s"';
$_LANG['AD_SEARCH_RESULT'] 					= 'Подсветка результатов поиска';
$_LANG['AD_TIME_ARREA'] 					= 'Временная зона:';
$_LANG['AD_TIME_SLIP'] 						= 'Смещение в часах:';
$_LANG['AD_MYSQL_CONFIG'] 					= 'Все реквизиты MySQL настраиваются в файле /includes/config.inc.php';
$_LANG['AD_DB_SIZE'] 					    = 'Размер базы данных (примерно)';
$_LANG['AD_DB_SIZE_ERROR'] 				    = 'Невозможно определить';
$_LANG['AD_SITE_EMAIL'] 					= 'E-mail сайта:';
$_LANG['AD_SITE_EMAIL_POST'] 				= 'Адрес от имени которого будут рассылаться уведомления пользователям';
$_LANG['AD_SENDER_EMAIL'] 					= 'Название отправителя:';
$_LANG['AD_IF_NOT_HANDLER'] 				= 'Если не указан, указывается название сайта';
$_LANG['AD_SEND_METHOD'] 					= 'Способ отправки:';
$_LANG['AD_PHP_MAILER'] 					= 'Функция mail в PHP';
$_LANG['AD_SEND_MAILER']					= 'Sendmail';
$_LANG['AD_SMTP_MAILER']					= 'SMTP - сервер';
$_LANG['AD_ENCRYPTING']						= 'Шифрование:';
$_LANG['AD_SMTP_LOGIN']						= 'SMTP авторизация:';
$_LANG['AD_SMTP_USER']						= 'SMTP пользователь:';
$_LANG['AD_IF_CHANGE_USER']					= 'имя пользователя вы можете сменить в файле /includes/config.inc.php';
$_LANG['AD_SMTP_PASS']						= 'SMTP пароль:';
$_LANG['AD_IF_CHANGE_PASS']					= 'пароль вы можете сменить в файле /includes/config.inc.php';
$_LANG['AD_SMTP_HOST']						= 'SMTP хост:';
$_LANG['AD_SOME_HOST']						= 'Можно указать несколько, через точку с запятой, в порядке приоритета';
$_LANG['AD_SMTP_PORT']						= 'SMTP порт:';
$_LANG['AD_VIEW_PATHWAY'] 					= 'Показывать глубиномер?';
$_LANG['AD_PATH_TO_CATEGORY'] 				= 'Отображает путь к разделу, <br/>в котором находится посетитель';
$_LANG['AD_MAINPAGE_PATHWAY'] 				= 'Глубиномер на главной странице:';
$_LANG['AD_PAGE_PATHWAY'] 				 	= 'Текущая страница в глубиномере:';
$_LANG['AD_PAGE_PATHWAY_LINK'] 			    = 'Ссылкой';
$_LANG['AD_PAGE_PATHWAY_TEXT'] 			    = 'Текстом';
$_LANG['AD_IP_ADMIN'] 				 		= 'IP адреса, с которых разрешен доступ в админку:';
$_LANG['AD_IP_COMMA'] 				 		= 'Введите ip адреса через запятую, с которых разрешен доступ в админку. Если не заданы, доступ разрешен всем.';
$_LANG['AD_ONLINESTATS'] 				    = 'Учет online пользователей';
$_LANG['AD_NO_ONLINESTATS'] 			    = 'не вести учет';
$_LANG['AD_YES_ONLINESTATS'] 			    = 'очищать статистику при каждом обновлении страницы';
$_LANG['AD_CRON_ONLINESTATS'] 			    = 'очищать статистику по CRON (задача "clearOnlineUsers")';
$_LANG['AD_SEO_URL_COUNT'] 			        = 'Длина генерируемых SEO URL';
$_LANG['AD_SEO_URL_COUNT_HINT'] 		    = 'Количество символов в генерируемых seo url';
$_LANG['AD_ATTENTION'] 				 		= '<strong>Внимание:</strong> после конфигурирования в целях безопасности необходимо сменить владельца файла /includes/config.inc.php и выставить права доступа на него 644.<br /> Так же обращаем Ваше внимание: после полной настройки сайта на сервере необходимо выставить права доступа <strong>644 для всех файлов</strong> и <strong>755 для всех каталогов,</strong> кроме директорий загрузки файлов. Кроме того, убедитесь, что владелец файлов сайта - пользователь, отличный от того, под которым работает web сервер и интерпретатор php.';