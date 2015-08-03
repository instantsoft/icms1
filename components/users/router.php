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

    function routes_users(){

        $routes[] = array(
                            '_uri'  => '/^users\/change_email\/([a-z0-9]{32})\/(.+)$/i',
                            'do'    => 'change_email',
                            1       => 'token',
                            2       => 'email'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/karma\/plus\/([0-9]+)$/i',
                            'do'    => 'votekarma',
                            'sign'  => 'plus',
                            1       => 'to'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/karma\/minus\/([0-9]+)$/i',
                            'do'    => 'votekarma',
                            'sign'  => 'minus',
                            1       => 'to'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/city\/(.+)$/i',
                            'do'    => 'view',
                            1       => 'city'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/hobby\/(.+)$/i',
                            'do'    => 'view',
                            1       => 'hobby'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/awardslist.html$/i',
                            'do'    => 'awardslist'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/giveaward.html$/i',
                            'do'    => 'giveaward',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/delaward([0-9]+).html$/i',
                            'do'    => 'delaward',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/friendlist.html$/i',
                            'do'    => 'friendlist',
                            1       => 'id',
                            'page'  => '1'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/friendlist([0-9]+).html$/i',
                            'do'    => 'friendlist',
                            1       => 'id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/nofriends.html$/i',
                            'do'    => 'delfriend',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/friendship.html$/i',
                            'do'    => 'addfriend',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/avatar.html$/i',
                            'do'    => 'avatar',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/select\-avatar.html$/i',
                            'do'    => 'select_avatar',
                            1       => 'id',
                            'page'  => '1'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/select\-avatar\-([0-9]+).html$/i',
                            'do'    => 'select_avatar',
                            1       => 'id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/select\-avatar\/([0-9]+)$/i',
                            'do'    => 'select_avatar',
                            1       => 'id',
                            2       => 'avatar_id',
                            'set_avatar' => '1'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/photoalbum.html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'viewphotos',
                            1       => 'id',
                            'page'  => '1'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/delalbum([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'delalbum',
                            1       => 'id',
                            2       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/editalbum([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'editalbum',
                            1       => 'id',
                            2       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/photos\/editlist$/i',
                            'do'    => 'photos',
                            'pdo'   => 'editphotolist',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([a-zA-z0-9\.]+)\/photos\/(public|private)([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'viewalbum',
                            1       => 'login',
                            2       => 'album_type',
                            3       => 'album_id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([a-zA-z0-9\.]+)\/photos\/(public|private)([0-9]+)\-([0-9]+)\.html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'viewalbum',
                            1       => 'login',
                            2       => 'album_type',
                            3       => 'album_id',
                            4       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/photos\/upload$/i',
                            'do'    => 'photos',
                            'pdo'   => 'uploadphotos'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([a-zA-z0-9\.]+)\/photos\/submit$/i',
                            'do'    => 'photos',
                            'pdo'   => 'submitphotos',
                            1       => 'login'
                         );
		$routes[] = array(
                            '_uri'  => '/^users\/([a-zA-z0-9\.]+)\/photos\/submit\-edit$/i',
                            'do'    => 'photos',
                            'pdo'   => 'submitphotos',
                            1       => 'login',
       						'is_edit' => 1
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/photo([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'viewphoto',
                            1       => 'id',
                            2       => 'photoid'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/editphoto([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'editphoto',
                            1       => 'id',
                            2       => 'photoid'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/delphoto([0-9]+).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'delphoto',
                            1       => 'id',
                            2       => 'photoid'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/addphoto(|single).html$/i',
                            'do'    => 'photos',
                            'pdo'   => 'addphoto',
							1       => 'uload_type'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/delprofile.html$/i',
                            'do'    => 'delprofile',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/restoreprofile([0-9]+).html$/i',
                            'do'    => 'restoreprofile',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/editprofile.html$/i',
                            'do'    => 'editprofile',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/sendmessage.html$/i',
                            'do'    => 'sendmessage',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/delmessages-(in|out|notices).html$/i',
                            'do'    => 'delmessages',
                            1       => 'id',
                            2       => 'opt'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages.html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'in'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages([0-9]+).html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'in',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-sent.html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'out'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-sent([0-9]+).html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'out',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-history([0-9]*).html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            2       => 'with_id',
                            'opt'   => 'history'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-history([0-9]+)\-([0-9]+).html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            2       => 'with_id',
                            'opt'   => 'history',
                            3       => 'page'
                         );
        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-notices.html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'notices'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/messages\-notices([0-9]+).html$/i',
                            'do'    => 'messages',
                            1       => 'id',
                            'opt'   => 'notices',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/reply([0-9]+).html$/i',
                            'do'    => 'sendmessage',
                            1       => 'id',
                            2       => 'replyid'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/delmsg([0-9]+).html$/i',
                            'do'    => 'delmessage',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/karma.html$/i',
                            'do'    => 'karma',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/files.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'view',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/files([0-9]+).html$/i',
                            'do'    => 'files',
                            'fdo'   => 'view',
                            1       => 'id',
                            2       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/addfile.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'addfile'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/delfilelist.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'delfilelist',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/showfilelist.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'pubfilelist',
                            1       => 'id',
                            'allow' => 'all'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([0-9]+)\/hidefilelist.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'pubfilelist',
                            1       => 'id',
                            'allow' => 'nobody'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/files\/download.html$/i',
                            'do'    => 'files',
                            'fdo'   => 'download'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/files\/download([0-9]+).html$/i',
                            'do'    => 'files',
                            'fdo'   => 'download',
                            1       => 'fileid'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/latest.html$/i',
                            'orderby' => 'regdate',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/latest([0-9]+).html$/i',
                            1       => 'page',
                            'orderby' => 'regdate',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/positive.html$/i',
                            'orderby' => 'karma',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/positive([0-9]+).html$/i',
                            1       => 'page',
                            'orderby' => 'karma',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/rating.html$/i',
                            'orderby' => 'rating',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/invites.html$/i',
                            'do'    => 'invites'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/rating([0-9]+).html$/i',
                            1       => 'page',
                            'orderby' => 'rating',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/online.html$/i',
                            'online' => '1'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/all.html$/i',
                            'online' => '0',
							'name' => 'all',
							'city' => 'all',
							'hobby' => 'all',
							'gender' => 'all',
							'agefrom' => 'all',
							'ageto' => 'all'
                         );

        $routes[] = array(
                            '_uri'    => '/^users\/group\/([0-9]+)(?:\-([0-9]+))?$/i',
                            1         => 'group_id',
                            2         => 'page',
                            'orderby' => 'regdate',
                            'orderto' => 'desc'
                         );

        $routes[] = array(
                            '_uri'  => '/^users\/([a-zA-z0-9\.]+)$/i',
                            'do'    => 'profile',
                            1       => 'login'
                         );

        return $routes;

    }
