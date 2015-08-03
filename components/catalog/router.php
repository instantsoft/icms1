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

    function routes_catalog(){


        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/tag\/(.+)$/i',
                            'do'    => 'tag',
                            1       => 'cat_id',
                            2       => 'tag',
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\-([0-9]*)\/tag\/(.+)$/i',
                            'do'    => 'tag',
                            1       => 'cat_id',
                            2       => 'tag',
                            3       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/find\-first\/(.+)$/i',
                            'do'    => 'findfirst',
                            1       => 'cat_id',
                            2       => 'text'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\-([0-9]+)\/find\-first\/(.+)$/i',
                            'do'    => 'findfirst',
                            1       => 'cat_id',
                            2       => 'page',
                            3       => 'text'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/find\/(.*)$/i',
                            'do'    => 'find',
                            1       => 'cat_id',
                            2       => 'text'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\-([0-9]+)\/find\/(.+)$/i',
                            'do'    => 'find',
                            1       => 'cat_id',
                            2       => 'page',
                            3       => 'text'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)$/i',
                            'do'    => 'cat',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\-([0-9]+)$/i',
                            'do'    => 'cat',
                            1       => 'cat_id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/add.html$/i',
                            'do'    => 'add_item',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/edit([0-9]+).html$/i',
                            'do'    => 'edit_item',
                            1       => 'item_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/submit.html$/i',
                            'do'    => 'submit_item',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/moderation\/accept([0-9]+).html$/i',
                            'do'    => 'accept_item',
                            1       => 'item_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/moderation\/reject([0-9]+).html$/i',
                            'do'    => 'delete_item',
                            1       => 'item_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/([0-9]+)\/search.html$/i',
                            'do'    => 'search',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/item([0-9]+).html$/i',
                            'do'    => 'item',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/addcart([0-9]+).html$/i',
                            'do'    => 'addcart',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/cartremove([0-9]+).html$/i',
                            'do'    => 'cartremove',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/viewcart.html$/i',
                            'do'    => 'viewcart'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/clearcart.html$/i',
                            'do'    => 'clearcart'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/savecart.html$/i',
                            'do'    => 'savecart'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/order.html$/i',
                            'do'    => 'order'
                         );

        $routes[] = array(
                            '_uri'  => '/^catalog\/finish.html$/i',
                            'do'    => 'finish'
                         );

        return $routes;

    }

?>
