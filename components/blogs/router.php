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

    function routes_blogs(){

        $routes[] = array(
                            '_uri'  => '/^blogs\/my_blog.html$/i',
                            'do'    => 'my_blog'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/createblog.html$/i',
                            'do'    => 'create'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/latest.html$/i',
                            'do'    => 'view'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/latest\-([0-9]+).html$/i',
                            'do'    => 'view',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/popular.html$/i',
                            'do'    => 'best'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/popular\-([0-9]+).html$/i',
                            'do'    => 'best',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^blogs\/all.html$/i',
                            'do'        => 'view_blogs',
                            'ownertype' => 'all'
                         );

        $routes[] = array(
                            '_uri'  	=> '/^blogs\/all\-([0-9]+).html$/i',
                            'do'    	=> 'view_blogs',
                            'ownertype' => 'all',
                            1       	=> 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^blogs\/single.html$/i',
                            'do'        => 'view_blogs',
                            'ownertype' => 'single'
                         );

        $routes[] = array(
                            '_uri'  	=> '/^blogs\/single\-([0-9]+).html$/i',
                            'do'    	=> 'view_blogs',
                            'ownertype' => 'single',
                            1       	=> 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^blogs\/multi.html$/i',
                            'do'        => 'view_blogs',
                            'ownertype' => 'multi'
                         );

        $routes[] = array(
                            '_uri'  	=> '/^blogs\/multi\-([0-9]+).html$/i',
                            'do'    	=> 'view_blogs',
                            'ownertype' => 'multi',
                            1       	=> 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/publishpost([0-9]+).html$/i',
                            'do'    => 'publishpost',
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([0-9]+)\/editblog.html$/i',
                            'do'    => 'config',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([0-9]+)\/delblog.html$/i',
                            'do'    => 'delblog',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/delpost([0-9]+).html$/i',
                            'do'    => 'delpost',
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/delcat([0-9]+).html$/i',
                            'do'    => 'delcat',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([0-9]+)\/newpost.html$/i',
                            'do'    => 'newpost',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([0-9]+)\/newpost([0-9]+).html$/i',
                            'do'    => 'newpost',
                            1       => 'id',
                            2       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([0-9]+)\/newcat.html$/i',
                            'do'    => 'newcat',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/editpost([0-9]+).html$/i',
                            'do'    => 'editpost',
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/editcat([0-9]+).html$/i',
                            'do'    => 'editcat',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)$/i',
                            'do'    => 'blog',
                            1       => 'bloglink'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)\/moderate.html$/i',
                            'do'    => 'blog',
							'on_moderate' => 1,
                            1       => 'bloglink'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)\/page\-([0-9]+)$/i',
                            'do'    => 'blog',
                            1       => 'bloglink',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)\/cat\-([0-9]+)$/i',
                            'do'    => 'blog',
                            1       => 'bloglink',
                            2       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)\/page\-([0-9]+)\/cat\-([0-9]+)$/i',
                            'do'    => 'blog',
                            1       => 'bloglink',
                            2       => 'page',
                            3       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^blogs\/([a-zA-Z0-9\-]+)\/([a-zA-Z0-9\-]+).html$/i',
                            'do'    => 'post',
                            1       => 'bloglink',
                            2       => 'seolink'
                         );

        return $routes;

    }

?>
