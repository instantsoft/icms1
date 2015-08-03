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

$_LANG['BOARD']                   ='Доска объявлений';
$_LANG['ADD_ADV']                 ='Добавить объявление';
$_LANG['CAT_NOT_FOUND']           ='Рубрика не найдена';
$_LANG['CAT_BOARD']               ='Рубрика';
$_LANG['MAX_VALUE_OF_ADD_ADV']    ='Достигнут предел добавлений в сутки. Вы сможете добавить объявление в эту рубрику через 24 часа.';
$_LANG['YOU_CANT_ADD_ADV']        ='Вы не можете добавлять объявления в эту рубрику';
$_LANG['YOU_CANT_ADD_ADV_ANY']    ='Вы не можете добавлять объявления';

$_LANG['NEED_TITLE']              ='Необходимо указать заголовок объявления!';
$_LANG['NEED_TEXT_ADV']           ='Необходимо указать текст объявления!';
$_LANG['NEED_CAT_ADV']            ='Необходимо выбрать рубрику!';
$_LANG['NEED_CITY']               ='Необходимо указать город!';
$_LANG['ERR_CAPTCHA']             ='Неправильно указан код с картинки!';
$_LANG['INFO_CAT_NO_PHOTO']       ='К объявлениям этой рубрики запрещено прикреплять фотоматериалы';

$_LANG['ADV_IS_ADDED']            ='Объявление успешно добавлено.';
$_LANG['ADV_PREMODER_TEXT']       ='Объявление будет опубликовано после проверки администратором.';

$_LANG['MSG_ADV_SUBMIT']          ='Пользователь %user% добавил объявление "<b>%link%</b>".'."\n".'Необходима модерация.';
$_LANG['MSG_ADV_EDITED']          ='Пользователь %user% изменил объявление "<b>%link%</b>".'."\n".'Необходима модерация.';
$_LANG['MSG_ADV_ACCEPTED']        ='Ваше объявление "%link%" прошло модерацию и было опубликовано.';

$_LANG['ADV_IS_MODER']            ='Объявление находится на модерации';
$_LANG['ADV_IS_EXTEND']           ='Объявление просрочено';

$_LANG['ADV_IS_ACCEPTED']         ='Объявление опубликовано';
$_LANG['WAIT_MODER']              ='Ожидает модерации';
$_LANG['ADV_EXTEND_INFO']         ='Просрочено';

$_LANG['EDIT_ADV']                ='Редактировать объявление';
$_LANG['REPEAT_EDIT']             ='Повторить редактирование';
$_LANG['ADV_MODIFIED']            ='Объявление изменено.';
$_LANG['ADV_EXTEND']              ='Вы можете продлить объявление на';
$_LANG['ADV_EXTEND_SROK']         ='Нажав "сохранить объявление", срок Вашего объявления продлится на';
$_LANG['ADV_EDIT_PREMODER_TEXT']  ='Объявление скрыто и будет вновь опубликовано после проверки администратором.';

$_LANG['DELETE_ADV']              ='Удалить объявление';
$_LANG['DELETING_ADV']            ='Удаление объявления';
$_LANG['YOU_SURE_DELETE_ADV']     ='Вы действительно желаете удалить объявление';
$_LANG['ADV_IS_DELETED']          ='Объявление успешно удалено.';
//Template
$_LANG['BOARD_GUEST']             ='Гость';
$_LANG['TITLE']                   ='Заголовок';
$_LANG['CITY']                    ='Город';
$_LANG['TEXT_ADV']                ='Текст объявления';
$_LANG['PERIOD_PUBL']             ='Срок публикации';
$_LANG['DAYS']                    ='дней';
$_LANG['DAYS_TO']                 ='начиная с';
$_LANG['PHOTO']                   ='Фотография';
$_LANG['DEL_PHOTO']               ='Удалить фотографию';
$_LANG['SELECT_CAT']              ='выберите рубрику';
$_LANG['SAVE_ADV']                ='Сохранить объявление';
$_LANG['WRITE_MESS_TO_AVTOR']     ='Написать сообщение автору';
$_LANG['ALL_AVTOR_ADVS']          ='Все объявления автора';
$_LANG['ADVS_NOT_FOUND']          ='Объявления не найдены.';
$_LANG['ADD_ADV_TO_CAT']          ='Добавить объявление в эту рубрику';
$_LANG['TYPE']                    ='Тип';
$_LANG['ALL_TYPE']                ='Все типы';
$_LANG['ALL_CITY']                ='Все города';
$_LANG['ORDER']                   ='Сортировать';
$_LANG['ORDERBY_TITLE']           ='По алфавиту';
$_LANG['ORDERBY_DATE']            ='По дате';
$_LANG['ORDERBY_HITS']            ='По просмотрам';
$_LANG['ORDERBY_TYPE']            ='По типу';
$_LANG['ORDERBY_AVTOR']           ='По автору';
$_LANG['ORDERBY_DESC']            ='по убыванию';
$_LANG['ORDERBY_ASC']             ='по возрастанию';
$_LANG['FILTER']                  ='Фильтр';
$_LANG['MARK_AS_VIP']             ='Сделать VIP на';
$_LANG['EXTEND_MARK_AS_VIP']      ='Продлить VIP на';
$_LANG['DELETE_MARK_AS_VIP']      ='удалить VIP-статус';
$_LANG['VIP_STATUS']              ='VIP-статус';
$_LANG['VIP_STATUS_HINT']         ='VIP-объявления выделяются цветом и всегда находятся в начале списка';
$_LANG['VIP_BUY_ERROR']           ='На вашем балансе не достаточно средств для покупки VIP-статуса на указанный срок';
$_LANG['VIP_ITEM']                ='VIP-объявление';
$_LANG['SECUR_SPAM']              ='Защита от спама';
$_LANG['SECUR_SPAM_TEXT']         ='Введите символы, изображенные на картинке';
$_LANG['DO_NOT_DO']               ='не делать';
$_LANG['LEAVE_AS_IS']             ='оставить как есть';
