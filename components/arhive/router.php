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

    function routes_arhive(){

        $routes[] = array(
                            '_uri'  => '/^arhive\/([0-9]+)\/([0-9]+)\/([0-9]+)$/i',
                            1       => 'y',
                            2       => 'm',
                            3       => 'd',
                            'do'    => 'ymd'
                         );

        $routes[] = array(
                            '_uri'  => '/^arhive\/([0-9]+)\/([0-9]+)$/i',
                            1       => 'y',
                            2       => 'm',
                            'do'    => 'ym'
                         );

        $routes[] = array(
                            '_uri'  => '/^arhive\/([0-9]+)$/i',
                            1       => 'y',
                            'do'    => 'y'
                         );

        return $routes;

    }

?>
