<?php

/* * *************************************************************************** */
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/* * *************************************************************************** */

if (!defined('VALID_CMS')) {
    die('ACCESS DENIED');
}
/*
 * Created by Firs Yuriy
 * e-mail: firs.yura@gmail.com
 * site: firs.org.ua
 */
$_LANG['ERR_UNKNOWN_TARGET']       = 'Ошибка определения объекта комментирования!';
$_LANG['ERR_DEFINE_USER']          = 'Ошибка определения пользователя!';
$_LANG['ERR_USER_NAME']            = 'Вы не указали свое имя!';
$_LANG['ERR_COMMENT_TEXT']         = 'Введите текст комментария!';
$_LANG['ERR_CAPTCHA']              = 'Неправильно указан код с картинки! Попробуйте нажать "обновить картинку".';
$_LANG['ERR_COMMENT_ADD']          = 'Ошибка добавления комментария!';
$_LANG['COMM_PREMODER_TEXT']       = 'Спасибо! Ваш комментарий будет добавлен после проверки администратором!';
$_LANG['COMM_PREMODER_ADMIN_TEXT'] = 'Пользователь %user% добавил комментарий <a href="%targetlink%">%targetlink%</a>.<br>Необходима <a href="/admin/index.php?view=components&do=config&link=comments&show_hidden=1">модерация.</a>.';
$_LANG['NEW_COMMENT']              = 'Новый комментарий';
$_LANG['COMM_SUC_DELETE']          = 'Комментарий успешно удален';
$_LANG['COMMENTS']                 = 'Комментарии';
$_LANG['COMMENT']                  = 'комментарий';
$_LANG['COMMENTS_ON_SITE']         = 'Комментарии на сайте';
$_LANG['WAIT_MODERING']            = 'Ожидает модерации';

$_LANG['COMMENTS_MALE']   = 'прокомментировал';
$_LANG['COMMENTS_FEMALE'] = 'прокомментировала';
$_LANG['COMMENTS_GENDER'] = 'комментирует';

// Template
$_LANG['COMMENTS_CAN_ADD_ONLY']  = 'Комментарии могут добавлять только';
$_LANG['REGISTERED']             = 'зарегистрированные';
$_LANG['USERS']                  = 'пользователи';
$_LANG['YOUR_NAME']              = 'Ваше имя';
$_LANG['INSERT_SMILE']           = 'Вставить смайл';
$_LANG['NOTIFY_NEW_COMM']        = 'Уведомлять о новых комментариях';
$_LANG['CONFIG_NOTIFY']          = 'Настройка уведомлений';
$_LANG['YOU_NEED']               = 'У вас не хватает';
$_LANG['KARMS']                  = 'кармы';
$_LANG['TO_ADD_COMM']            = 'для добавления комментария';
$_LANG['NEED']                   = 'Требуется';
$_LANG['HAS']                    = 'имеется';
$_LANG['YOU_HAVENT_ACCESS_TEXT'] = 'У вас нет прав на добавление комментариев. Обратитесь к администрации сайта.';
$_LANG['LINK_TO_COMMENT']        = 'Ссылка на комментарий';
$_LANG['BAD_COMMENT']            = 'Плохой комментарий';
$_LANG['GOOD_COMMENT']           = 'Хороший комментарий';
$_LANG['SHOW_COMMENT']           = 'показать комментарий';
$_LANG['REPLY']                  = 'Ответить';
$_LANG['DELETE_BRANCH']          = 'Удалить ветвь';
$_LANG['NOT_COMMENT_TEXT']       = 'Нет комментариев. Ваш будет первым!';
$_LANG['RSS']                    = 'RSS-лента';
$_LANG['RSS_COMM']               = 'RSS-лента комментариев';
$_LANG['LOADING_COMM']           = 'Загрузка комментариев';
$_LANG['ADD_COMM']               = 'Добавить комментарий';
$_LANG['SUBSCRIBE_TO_NEW']       = 'Подписаться на новые';
$_LANG['UNSUBSCRIBE']            = 'Прекратить подписку';
$_LANG['EDIT_COMMENT']           = 'Редактировать комментарий';
$_LANG['CONFIRM_DEL_COMMENT']    = 'Удалить комментарий?';
$_LANG['COMMENT_IN_LINK']        = 'Вы пришли на страницу по этой ссылке';
?>
