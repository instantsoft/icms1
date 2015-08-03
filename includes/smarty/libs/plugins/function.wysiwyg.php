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
    function smarty_function_wysiwyg($params, &$smarty) {
        ob_start();
        cmsCore::insertEditor($params['name'], $params['value'], $params['height'], $params['width'], (!empty($params['toolbar']) ? $params['toolbar'] : 'full'));
        return ob_get_clean();
    }
