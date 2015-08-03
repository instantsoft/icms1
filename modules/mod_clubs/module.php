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

function mod_clubs($mod, $cfg){

	$inDB = cmsDatabase::getInstance();

	if (!isset($cfg['count'])) { $cfg['count'] = 5; }
	if (!isset($cfg['type'])) { $cfg['type'] = 'id'; }
	if (!isset($cfg['vip_on_top'])) { $cfg['vip_on_top'] = 1; }

    cmsCore::loadModel('clubs');
    $model = new cms_model_clubs();

	if($cfg['vip_on_top']){
		$inDB->orderBy('is_vip', 'DESC, c.'.$cfg['type'].' DESC');
	} else {
		$inDB->orderBy('c.'.$cfg['type'], 'DESC');
	}
	$inDB->limit($cfg['count']);

    $clubs = $model->getClubs();
	if(!$clubs){ return false; }

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('clubs', $clubs)->
            display($cfg['tpl']);

	return true;

}