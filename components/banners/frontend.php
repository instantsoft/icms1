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

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function banners(){

    $inCore = cmsCore::getInstance();

    $model = new cms_model_banners();

    $do = $inCore->do;
	$banner_id = cmsCore::request('id', 'int', 0);

//======================================================================================================================//

    if ($do=='view'){

        $banner = $model->getBanner($banner_id);
		if(!$banner || !$banner['published']) { cmsCore::error404(); }

        $model->clickBanner($banner_id);
        cmsCore::redirect($banner['link']);

    }

}
?>