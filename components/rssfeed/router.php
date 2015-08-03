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

function routes_rssfeed(){

	$routes[] = array(
						'_uri'  => '/^rssfeed\/([a-z]+)\/(.+)$/i',
						1       => 'target',
						2       => 'item_id'
					 );

	return $routes;

}