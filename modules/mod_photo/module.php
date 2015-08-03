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

function mod_photo($mod, $cfg){

	cmsCore::loadClass('photo');
	$inPhoto = cmsPhoto::getInstance();
	$inDB    = cmsDatabase::getInstance();

	$default_cfg = array (
        'is_full' => 1,
        'showmore' => 1,
        'album_id' => 0,
        'whatphoto' => 'all',
        'shownum' => 5,
        'maxcols' => 2,
        'sort' => 'pubdate',
        'showclubs' => 0,
        'is_subs' => 1,
        'is_lightbox' => 1
    );
	$cfg = array_merge($default_cfg, $cfg);

    // выбираем категории фото
    $inDB->addJoin('INNER JOIN cms_photo_albums a ON a.id = f.album_id AND a.published = 1');
    $inDB->addSelect('a.title as cat_title, a.NSDiffer');

	// если категория задана, выбираем из нее
	if($cfg['album_id']){

		// Если выбирать нужно включая вложенные
		if($cfg['is_subs']){

			// получаем категорию
			$album = $inDB->getNsCategory('cms_photo_albums', $cfg['album_id']);
			if (!$album) { return false; }

			$inPhoto->whereThisAndNestedCats($album['NSLeft'], $album['NSRight']);

		} else {

			$inPhoto->whereAlbumIs($cfg['album_id']);

		}

	}

	// если фото клубов не нужны
	if(!$cfg['showclubs']){
		$inDB->where("f.owner = 'photos'");
	}

	// Задаем период
	$inPhoto->wherePeriodIs($cfg['whatphoto']);

    //устанавливаем сортировку
    $inDB->orderBy('f.'.$cfg['sort'], 'DESC');

    //устанавливаем номер текущей страницы и кол-во фото на странице
    $inDB->limit($cfg['shownum']);

	// получаем фото
	$photos = $inPhoto->getPhotos(false, $cfg['is_full']);
	if(!$photos) { return false; }

	cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('photos', $photos)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

	return true;

}