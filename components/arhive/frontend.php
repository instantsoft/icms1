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

function arhive(){

    global $_LANG;

    $model = new cms_model_arhive();

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();

    $pagetitle = $inCore->getComponentTitle();

    $do = $inCore->do;

	$inPage->setTitle($pagetitle);
	$inPage->addPathway($pagetitle, '/arhive');

 //======================================================================================================//

	if ($do == 'view' || $do == 'y'){

        if($do == 'y'){
            $pagetitle = $_LANG['ARCHIVE_MATERIALS_FROM'].$model->year.$_LANG['ARHIVE_YEAR'];
            $inPage->addPathway($model->year, '/arhive/'.$model->year);
            $inPage->setTitle($pagetitle);
            $model->whereYearIs();
        }

        $items = $model->getArhiveContent();

        cmsPage::initTemplate('components', 'com_arhive_dates')->
                assign('pagetitle', $pagetitle)->
                assign('items', $items)->
                assign('do', $do)->
                display('com_arhive_dates.tpl');

	}

//======================================================================================================//

	if ($do == 'ymd' || $do == 'ym'){

        $month_name = cmsCore::intMonthToStr($model->month);
        $inPage->addPathway($model->year, '/arhive/'.$model->year);
        $inPage->addPathway($month_name, '/arhive/'.$model->year.'/'.$model->month);

        if($do == 'ymd'){
            $inPage->addPathway($model->day, '/arhive/'.$model->year.'/'.$model->month.'/'.$model->day);
            $pagetitle = $_LANG['ARCHIVE_MATERIALS_FROM'].$model->day.' '
                            .$_LANG['MONTH_'.$model->month].' '.$model->year.$_LANG['ARHIVE_YEARS'];
            $model->whereDayIs();
        } else {
            $pagetitle = $_LANG['ARCHIVE_MATERIALS_FROM'].$month_name.' '.$model->year.$_LANG['ARHIVE_YEARS'];
            $model->whereMonthIs();
        }

        $inPage->setTitle($pagetitle);

        $model->setArtticleSql();

        $items = $model->getArhiveContent();

        cmsPage::initTemplate('components', 'com_arhive_list')->
                assign('pagetitle', $pagetitle)->
                assign('items', $items)->
                display('com_arhive_list.tpl');

    }

}
?>