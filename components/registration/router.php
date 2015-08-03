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

    function routes_registration(){

        $routes[] = array(
                            '_uri'  => '/^registration\/login$/i',
                            'do'    => 'auth'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/add$/i',
                            'do'    => 'register'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/logout$/i',
                            'do'    => 'auth',
                            'logout' => 1
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/activate\/([a-z0-9]{32})$/i',
                            'do'    => 'activate',
                            1       => 'code'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/remind\/([a-z0-9]{32})$/i',
                            'do'    => 'remind',
                            1       => 'code'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/passremind$/i',
                            'do'    => 'sendremind'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/autherror$/i',
                            'do'    => 'autherror'
                         );

        $routes[] = array(
                            '_uri'  => '/^registration\/([a-z0-9]{32})/i',
                            'do'    => 'view',
                            1       => 'invite_code'
                         );

        return $routes;

    }

?>
