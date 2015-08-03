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
    function routes_geo(){

        $routes[] = array(
                            '_uri'      => '/^geo\/city\/(.*)$/i',
                            'do'        => 'view',
                            1           => 'city_id'
                         );

        $routes[] = array(
                            '_uri'      => '/^geo\/get$/i',
                            'do'        => 'get'
                         );

        return $routes;

    }
?>
