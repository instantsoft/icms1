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

class cms_model_photos{

	public function __construct(){
        $this->inDB = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('photos');
		cmsCore::loadLib('karma');
		cmsCore::loadLanguage('components/photos');
    }

/* ========================================================================== */

    public static function getDefaultConfig() {

        return array (
				  'link' => 0,
				  'saveorig' => 0,
				  'maxcols' => 2,
				  'orderby' => 'title',
				  'orderto' => 'desc',
				  'meta_keys' => '',
				  'meta_desc' => '',
				  'seo_user_access' => 0,
				  'showlat' => 1,
				  'best_latest_perpage' => 25,
				  'best_latest_maxcols' => 5,
				  'watermark' => 1
				);

    }

/* ========================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'palbum': $album = $this->inDB->get_fields('cms_photo_albums', "id='{$target_id}'", 'title');
                           if (!$album) { return false; }
                           $result['link']  = '/photos/'.$target_id;
                           $result['title'] = $album['title'];
                           break;

            case 'photo':  $photo = $this->inDB->get_fields('cms_photo_files', "id='{$target_id}'", 'title');
                           if (!$photo) { return false; }
                           $result['link']  = '/photos/photo'.$target_id.'.html';
                           $result['title'] = $photo['title'];
                           break;

        }

        return ($result ? $result : false);

    }

    public function updateRatingHook($target, $item_id, $points) {

        if (!$item_id || abs($points)!=1) { return false; }

        switch($target){
            case 'photo':
						$sql = "UPDATE cms_photo_files
								SET rating = rating + ({$points})
								WHERE id = '{$item_id}'";
                         break;
        }

        $this->inDB->query($sql);

        return true;

    }

/* ========================================================================== */
    /**
     * Возвращает объект класса для загрузки изображений
     * @return obj
     */
    public function initUploadClass($album) {

		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/photos/';
		$inUploadPhoto->small_size_w  = $album['thumb1'];
		$inUploadPhoto->medium_size_w = $album['thumb2'];
		$inUploadPhoto->thumbsqr      = $album['thumbsqr'];
		$inUploadPhoto->is_watermark  = $this->config['watermark'];
		$inUploadPhoto->is_saveorig   = $this->config['saveorig'];

		return $inUploadPhoto;

    }

/* ========================================================================== */

    public function loadedByUser24h($user_id, $album_id) {

		return $this->inDB->rows_count('cms_photo_files', "user_id = '$user_id' AND album_id = '$album_id' AND pubdate >= DATE_SUB(NOW(), INTERVAL 1 DAY)");

    }

/* ========================================================================== */

}