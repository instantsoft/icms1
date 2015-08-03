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

function mod_search($mod, $cfg){

    cmsCore::loadModel('search');
    cmsCore::loadLanguage('components/search');
    $model = cms_model_search::initModel();

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('enable_components', $model->getEnableComponentsWithSupportSearch())->
            display($cfg['tpl']);

    return true;

}