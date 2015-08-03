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

    function routes_clubs(){

        $routes[] = array(
                            '_uri'  => '/^clubs\/by_user_([0-9]+)$/i',
                            'do'    => 'user_clubs',
							1       => 'user_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/members\-([0-9]+)$/i',
                            'do'    => 'members',
							1       => 'id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/page\-([0-9]+)$/i',
                            'do'    => 'view',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/create.html$/i',
                            'do'    => 'create'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/message\-members.html$/i',
                            'do'    => 'send_message',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/join_member.html$/i',
                            'do'    => 'join_member',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)$/i',
                            'do'    => 'club',
                            1       => 'id'
                         );


        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/leave.html$/i',
                            'do'    => 'leave',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/join.html$/i',
                            'do'    => 'join',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/config.html$/i',
                            'do'    => 'config',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/photoalbums$/i',
                            'do'    => 'view_albums',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/photoalbum([0-9]+)$/i',
                            'do'    => 'view_album',
                            1       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/photoalbum([0-9]+)\/page\-([0-9]+)$/i',
                            'do'    => 'view_album',
                            1       => 'album_id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/delalbum([0-9]+).html$/i',
                            'do'    => 'delete_album',
                            1       => 'album_id'
                         );
						 
        $routes[] = array(
                            '_uri'  => '/^clubs\/photo([0-9]+).html$/i',
                            'do'    => 'view_photo',
                            1       => 'photo_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/addphoto([0-9]+).html$/i',
                            'do'    => 'add_photo',
							'do_photo' => 'addphoto',
                            1       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/photoalbum([0-9]+)\/submit_photo.html$/i',
							'do'    => 'add_photo',
                            'do_photo' => 'submit_photo',
                            1       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/uploaded([0-9]+).html$/i',
							'do'    => 'add_photo',
                            'do_photo'    => 'uploaded',
                            1       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/delphoto([0-9]+).html$/i',
                            'do'    => 'delete_photo',
                            1       => 'photo_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/editphoto([0-9]+).html$/i',
                            'do'    => 'edit_photo',
                            1       => 'photo_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/publish([0-9]+).html$/i',
                            'do'    => 'publish_photo',
                            1       => 'photo_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/newpost.html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'newpost',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/newpost([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'newpost',
                            1       => 'id',
                            2       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)\/newcat.html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'newcat',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/editpost([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'editpost', 
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/editcat([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'editcat',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/delcat([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'delcat',
                            1       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/delpost([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'delpost',
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/publishpost([0-9]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'publishpost',
                            1       => 'post_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_blog$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'blog',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_blog\/moderate.html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'blog',
							'on_moderate' => 1,
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_blog\/page\-([0-9]+)$/i',
                            'do'    => 'club_blogs',
							'bdo'   => 'blog',
                            1       => 'id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_blog\/cat\-([0-9]+)$/i',
                            'do'    => 'club_blogs',
							'bdo'   => 'blog',
                            1       => 'id',
                            2       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_blog\/page\-([0-9]+)\/cat\-([0-9]+)$/i',
                            'do'    => 'club_blogs',
							'bdo'   => 'blog',
                            1       => 'id',
                            2       => 'page',
                            3       => 'cat_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^clubs\/([0-9]+)_([a-zA-Z0-9\-]+).html$/i',
							'do'    => 'club_blogs',
                            'bdo'   => 'post',
                            1       => 'id',
                            2       => 'seolink'
                         );

        return $routes;

    }

?>
