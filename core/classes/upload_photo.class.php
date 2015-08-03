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

class cmsUploadPhoto {

    private static $instance;

	public $upload_dir    = '';			// директория загрузки
	public $filename      = '';	        // имя файла
	public $small_size_w  = 96;	    	// ширина миниатюры
	public $small_size_h  = '';			// высота миниатюры
	public $medium_size_w = 480;		// ширина среднего изображения
	public $medium_size_h = '';			// высота среднего изображения
	public $thumbsqr      = true;		// квадратное изображение, да по умолчанию
	public $is_watermark  = true;		// накладывать ватермарк, да по умолчанию
	public $is_saveorig   = 0;			// сохранять оригинал фото, нет по умолчанию
	public $dir_small     = 'small/';	// директория загрузки миниатюры
	public $dir_medium    = 'medium/';	// директория загрузки среднего изображения
	public $only_medium   = false;		// загружать только среднее изображение, нет по умолчанию
	public $input_name    = 'Filedata';	// название поля загрузки файла

// ============================================================================ //
// ============================================================================ //

	private function __construct(){}

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

// ============================================================================ //
    /**
     * Загружает фото файл
     * @return array $file (filename, realfile)
     */
    public function uploadPhoto($old_file=''){

		// если каталог загрузки не определен, возвращаем ложь
		if (!$this->upload_dir) { return false; }

		if (!empty($_FILES[$this->input_name]['name'])){

			cmsCore::includeGraphics();

            $input_name = preg_replace('/[^a-zA-Zа-яёЁА-Я0-9\.\-_ ]/ui', '',
                                    mb_substr(basename(strval($_FILES[$this->input_name]['name'])), 0, 160));
            // расширение
            $ext = mb_strtolower(pathinfo($input_name, PATHINFO_EXTENSION));
            // имя файла без расширения
            $realfile = str_replace('.'.$ext, '', $input_name);

			if (!in_array($ext, array('jpg','jpeg','gif','png','bmp'))) { return false; }

			$this->filename 	   = $this->filename ? $this->filename : md5(time().$realfile).'.'.$ext;

			$uploadphoto 		   = $this->upload_dir . $this->filename;
			$uploadthumb['small']  = $this->upload_dir . $this->dir_small . $this->filename;
			$uploadthumb['medium'] = $this->upload_dir . $this->dir_medium . $this->filename;

			$uploadphoto 		   = $this->upload_dir . $this->filename;

			$source				   = $_FILES[$this->input_name]['tmp_name'];
			$errorCode			   = $_FILES[$this->input_name]['error'];

			if (cmsCore::moveUploadedFile($source, $uploadphoto, $errorCode)) {

				// удаляем предыдущий файл если необходимо
				$this->deletePhotoFile($old_file);

                if (!$this->isImage($uploadphoto)){
                    $this->deletePhotoFile($this->filename);
                    return false;
                }

				if (!$this->small_size_h) { $this->small_size_h = $this->small_size_w; }
				if (!$this->medium_size_h) { $this->medium_size_h = $this->medium_size_w; }

				// Гененрируем маленькое и среднее изображения
				if(!$this->only_medium){
                    if(!is_dir($this->upload_dir . $this->dir_small)) { @mkdir($this->upload_dir . $this->dir_small); }
					@img_resize($uploadphoto, $uploadthumb['small'], $this->small_size_w, $this->small_size_h, $this->thumbsqr);
				}
                if(!is_dir($this->upload_dir . $this->dir_medium)) { @mkdir($this->upload_dir . $this->dir_medium); }
				@img_resize($uploadphoto, $uploadthumb['medium'], $this->medium_size_w, $this->medium_size_h, false, false);

				// Накладывать ватермарк
				if($this->is_watermark) { @img_add_watermark($uploadthumb['medium']); }

				// сохранять оригинал
				if(!$this->is_saveorig) { @unlink($uploadphoto); } elseif($this->is_watermark) { @img_add_watermark($uploadphoto); }

				$file['filename'] = $this->filename;

				$file['realfile'] = $realfile;


			} else {

				return false;

			}


		} else {

			return false;

		}

        return $file;

    }
// ============================================================================ //
    public function isImage($src){

        $size = getimagesize($src);

        if ($size === false) return false;

        return true;

    }
// ============================================================================ //
    /**
     * Удаляет файл фото с папок загрузки
     * @return bool
     */
	public function deletePhotoFile($file=''){

		if (!($file && $this->upload_dir)) { return false; }

		@chmod($this->upload_dir . $file, 0777);
		@unlink($this->upload_dir . $file);
		@chmod($this->upload_dir . $this->dir_small . $file, 0777);
		@unlink($this->upload_dir . $this->dir_small . $file);
		@chmod($this->upload_dir . $this->dir_medium . $file, 0777);
		@unlink($this->upload_dir . $this->dir_medium . $file);

        return true;

    }
// ============================================================================ //

}
?>
