<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.7                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/
function smarty_block_flat_js($params, $content, $template, &$repeat)
{
    if ($content) {
        cmsPage::getInstance()
            ->addHeadFlatJS($content);
        $content = '';
    }

    return $content;
}