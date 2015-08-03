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

    //
    // ВНИМАНИЕ! Если вы хотите добавить собственное правило, то создайте
    //           файл custom_rewrite.php и объявите в нем функцию
    //           custom_rewrite_rules() по аналогии с текущим файлом!
    //
    // В этом файле определены системные правила для редиректа и подмены адресов
    //
    //      source          : регулярное выражение, для сравнения с текущим URI
    //      target          : URI для перенаправления, при совпадении source
    //      action          : действие при совпадении source
    //
    // Возможные значения для action:
    //
    //      rewrite         : подменить URI перед определением компонента
    //      redirect        : редирект на target с кодом 303 See Other
    //      redirect-301    : редирект на target с кодом 301 Moved Permanently
    //      alias           : заинклудить файл target и остановить скрипт, поддерживаются параметры через file.php?param=value
    //

    function rewrite_rules(){

        $rules[] = array(
                            'source'  => '/^(.+)\/$/ui',
                            'target'  => '/{1}',
                            'action'  => 'redirect-301'
                         );

        //
        // Вход / Выход
        //

        $rules[] = array(
                            'source'  => '/^admin$/ui',
                            'target'  => '/admin/index.php',
                            'action'  => 'redirect'
                         );

        $rules[] = array(
                            'source'  => '/^login$/ui',
                            'target'  => 'registration/login',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^logout$/ui',
                            'target'  => 'registration/logout',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^auth\/error.html$/ui',
                            'target'  => 'registration/autherror',
                            'action'  => 'rewrite'
                         );

        //
        // Регистрация / Активация
        //

        $rules[] = array(
                            'source'  => '/^activate\/(.+)$/ui',
                            'target'  => 'registration/activate/{1}',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^passremind.html$/ui',
                            'target'  => 'registration/passremind',
                            'action'  => 'rewrite'
                         );

        //
        // RSS
        //

        $rules[] = array(
                            'source'  => '/^rss\/([a-z]+)\/(.+)\/feed.rss$/ui',
                            'target'  => 'rssfeed/{1}/{2}',
                            'action'  => 'rewrite'
                         );

        //
        // Внешние ссылки
        //

        $rules[] = array(
                            'source'  => '/^go\/url=(.+)$/ui',
                            'target'  => 'files/go/{1}',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^load\/url=(.+)$/ui',
                            'target'  => 'files/load/{1}',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^r([0-9]+)$/ui',
                            'target'  => 'billing/ref_link/{1}',
                            'action'  => 'rewrite'
                         );

        //
        // Баннеры
        //

        $rules[] = array(
                            'source'  => '/^gobanner([0-9]+)$/ui',
                            'target'  => 'banners/{1}',
                            'action'  => 'rewrite'
                         );

        //
        // Подписка
        //

        $rules[] = array(
                            'source'  => '/^subscribe\/([a-z_]+)\/([0-9]+)$/ui',
                            'target'  => 'subscribes/{1}/{2}/1',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^unsubscribe\/([a-z_]+)\/([0-9]+)$/ui',
                            'target'  => 'subscribes/{1}/{2}/0',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^forum\/subscribe([0-9]+).html$/ui',
                            'target'  => 'subscribes/forum/{1}/1',
                            'action'  => 'rewrite'
                         );

        $rules[] = array(
                            'source'  => '/^forum\/unsubscribe([0-9]+).html$/ui',
                            'target'  => 'subscribes/forum/{1}/0',
                            'action'  => 'rewrite'
                         );

        return $rules;

    }