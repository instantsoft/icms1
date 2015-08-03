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
/*
 * Created by Firs Yuriy
 * e-mail: firs.yura@gmail.com
 * site: firs.org.ua
 */

$_LANG['CATALOG']                 ='Универсальный каталог';

$_LANG['ARTICLES']                ='Записей';
$_LANG['RATING']                  ='Рейтинг';
$_LANG['VOTES']                   ='Голосов';
$_LANG['YOUR_VOTE']               ='Ваша оценка';
$_LANG['ORDER_ARTICLES']          ='Сортировка записей';
$_LANG['ORDERBY_PRICE']           ='По цене';
$_LANG['ORDERBY_TITLE']           ='По алфавиту';
$_LANG['ORDERBY_DATE']            ='По дате';
$_LANG['ORDERBY_RATING']          ='По рейтингу';
$_LANG['ORDERBY_HITS']            ='По просмотрам';
$_LANG['ORDERBY_DESC']            ='по убыванию';
$_LANG['ORDERBY_ASC']             ='по возрастанию';

$_LANG['NEW_RECORDS']             ='Новое в каталоге';

$_LANG['NO_MATCHING_FOUND']       ='Совпадений не найдено.';
$_LANG['SEARCH_IN_CAT']           ='Поиск в каталоге';
$_LANG['NO_CAT_IN_CATALOG']       ='В каталоге нет рубрик или все рубрики пусты.';
$_LANG['SEARCH_RESULT']           ='Результаты поиска';
$_LANG['FOUNDED']                 ='Найдено';
$_LANG['CANCEL_SEARCH']           ='Отменить поиск';
$_LANG['SEARCH_BY_TAG']           ='Поиск по ключевому слову';
$_LANG['MATCHES']                 ='совпадений';
$_LANG['MATCHE']                  ='совпадение';
$_LANG['CAT_NOT_FOUND']           ='Рубрика каталога не найдена.';

$_LANG['ADD_ITEM']                ='Добавить запись';
$_LANG['EDIT_ITEM']               ='Редактирование записи';
$_LANG['ADDED_BY']                ='Автор записи';
$_LANG['COMMA_SEPARATE']          ='Через запятую, если несколько';
$_LANG['CAN_MANY']                ='Разрешить выбор количества';
$_LANG['TYPE_LINK']               ='Введите ссылку';
$_LANG['ITEM_PREMOD_NOTICE']      ='Запись будет опубликована в каталоге после проверки администратором.';
$_LANG['WAIT_MODERATION']         ='Запись ожидает модерации';
$_LANG['MODERATION_ACCEPT']       ='Разрешить';
$_LANG['MODERATION_REJECT']       ='Удалить';
$_LANG['MSG_ITEM_SUBMIT']         ='Пользователь %user% добавил в каталог запись "<b>%link%</b>".'."\n".'Необходима модерация.';
$_LANG['MSG_ITEM_EDITED']         ='Пользователь %user% изменил запись "<b>%link%</b>" в каталоге.'."\n".'Необходима модерация.';
$_LANG['MSG_ITEM_REJECTED']       ='Ваша запись "%item%" была не принята в каталог и удалена';
$_LANG['MSG_ITEM_ACCEPTED']       ='Ваша запись "%link%" была опубликована в каталоге';

$_LANG['PRICE']                   ='Цена';
$_LANG['ADD_TO_CART']             ='Добавить в корзину';

$_LANG['CART']                    ='Корзина';
$_LANG['DEL_POSITION_FROM_CART']  ='Удалить позицию из корзины?';
$_LANG['CLEAR_CART']              ='Очистить корзину';
$_LANG['ITEM']                    ='Позиция';
$_LANG['CAT']                     ='Рубрика';
$_LANG['QTY']                     ='Кол-во';
$_LANG['CART_TOTAL']              ='Стоимость заказа';
$_LANG['BACK_TO_SHOP']            ='Вернуться в магазин';
$_LANG['CART_ORDER']              ='Оформить заказ';
$_LANG['NOITEMS_IN_CART']         ='В корзине нет товаров.';
$_LANG['CART_ORDERING']           ='Оформление заказа';
$_LANG['TOTAL_PRICE']             ='Итоговая стоимость заказа';

$_LANG['INFO_CUSTOMER']           ='Информация покупателя';
$_LANG['FIO_CUSTOMER']            ='Ф. И. О. покупателя';
$_LANG['ORGANIZATION']            ='Организация';
$_LANG['CONTACT_PHONE']           ='Контактный телефон';
$_LANG['ADRESS_EMAIL']            ='Адрес e-mail';
$_LANG['CUSTOMER_COMMENT']        ='Дополнительные сведения';
$_LANG['SUBMIT_ORDER']            ='Отправить заказ';

$_LANG['EMPTY_NAME']              ='Укажите свое имя!';
$_LANG['EMPTY_PHONE']             ='Укажите контактный телефон!';
$_LANG['ERR_CAPTCHA']             ='Неправильно указан защитный код с картинки!';

$_LANG['GET_ORDER_FROM_CATALOG']  ='Получен заказ из каталога';
$_LANG['CUSTOMER']                ='ПОКУПАТЕЛЬ';
$_LANG['FIO']                     ='Ф. И. О.';
$_LANG['COMPANY']                 ='КОМПАНИЯ';
$_LANG['PHONE']                   ='ТЕЛЕФОН';
$_LANG['ORDER_COMMENT']           ='ДОПОЛНИТЕЛЬНО';
$_LANG['ORDER']                   ='ЗАКАЗ';
$_LANG['ORDER_COMPLETE']          ='Заказ принят';
$_LANG['TOTAL_ORDER_PRICE']       ='Общая сумма заказа';
$_LANG['EMAIL_SUBJECT']           ='{sitename}: ЗАКАЗ ИЗ КАТАЛОГА';
$_LANG['CUSTOMER_EMAIL_SUBJECT']  ='Ваш заказ поступил в обработку';
$_LANG['ADMIN_EMAIL_SUBJECT']     ='Новый заказ';
$_LANG['CUSTOMER_EMAIL_TEXT']     ='Наши менеджеры свяжутся с вами по указанному телефону в ближайшее время.';
$_LANG['NEED_TITLE']              ='Необходимо указать название';

//Template
$_LANG['ADVICE']                  ='Совет';
$_LANG['ADVICE_TEXT']             ='Вы можете использовать символы подстановки';
$_LANG['ANY_SEQ_LETTERS']         ='любая последовательность символов';
$_LANG['ANY_ONE_LETTER']          ='один любой символ';
$_LANG['FILL_FIELDS']             ='Заполните поля целиком или частично';
$_LANG['SEARCH_IN_CAT']           ='Найти в каталоге';
$_LANG['SEARCH_BY_CAT']           ='Поиск по рубрике';
$_LANG['DETAILS']                 ='Подробнее';
$_LANG['SEO_KEYWORDS']            ='SEO: Ключевые слова';
$_LANG['SEO_KEYWORDS_HINT']       ='Если не указано, будет браться из тегов';
$_LANG['SEO_DESCRIPTION']         ='SEO: Описание';
$_LANG['IS_COMMENTS']             ='Разрешить комментарии';
?>