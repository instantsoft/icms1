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

    function routes_actions(){

        $routes[] = array(
                            '_uri'  => '/^actions\/delete\/([0-9]+)$/i',
                            'do'    => 'delete',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^actions\/my_friends$/i',
                            'do'    => 'view_user_feed'
                         );

        $routes[] = array(
                            '_uri'  => '/^actions\/page\-([0-9]+)$/i',
                            'do'    => 'view',
                            1       => 'page'
                         );

        return $routes;

    }

?>
