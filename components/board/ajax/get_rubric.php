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

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

    cmsCore::loadModel('board');
    $model = new cms_model_board();

	$cat_id   = cmsCore::request('value', 'int', 0);	
	$selected = cmsCore::request('obtype', 'str', '');

	$cat = $model->getCategory($cat_id);
	if(!$cat) { cmsCore::halt(); }

	echo $model->getTypesOptions($cat['obtypes'], $selected);

?>