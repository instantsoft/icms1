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

    function routes_content(){

        $routes[] = array(
                            '_uri'  => '/^content\/top.html$/i',
                            'do'    => 'best'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/add.html$/i',
                            'do'    => 'addarticle'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/edit([0-9]+).html$/i',
                            'do'    => 'editarticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/delete([0-9]+).html$/i',
                            'do'    => 'deletearticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/publish([0-9]+).html$/i',
                            'do'    => 'publisharticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/my.html$/i',
                            'do'    => 'my'
                         );

        $routes[] = array(
                            '_uri'  => '/^content\/my([0-9]+).html$/i',
                            'do'    => 'my',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^content\/(.+)\/page\-([0-9]+).html$/i',
                            'do'        => 'read',
                            1           => 'seolink',
                            2           => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^content\/(.+).html$/i',
                            'do'        => 'read',
                            1           => 'seolink'
                         );

        $routes[] = array(
                            '_uri'      => '/^content\/(.+)\/page\-([0-9]+)$/i',
                            'do'        => 'view',
                            1           => 'seolink',
                            2           => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^content\/(.*)$/i',
                            'do'        => 'view',
                            1           => 'seolink'
                         );

        return $routes;

    }

?>
