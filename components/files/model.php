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

class cms_model_files {

	public function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
        $this->config = cmsCore::getInstance()->loadComponentConfig('files');
        $this->config['white_list'] = explode(',', $this->config['white_list']);
    }

    public static function getDefaultConfig() {
        return array (
            'check_link' => 1,
            'redirect_time' => 10,
            'file_time' => 0,
            'white_list' => ''
        );
    }

    public function increaseDownloadCount($fileurl) {

        $downloads = cmsCore::fileDownloadCount($fileurl);

        if ($downloads == 0){
            $sql = "INSERT INTO cms_downloads (fileurl, hits) VALUES ('$fileurl', '1')";
        } else {
            $sql = "UPDATE cms_downloads SET hits = hits + 1 WHERE fileurl = '$fileurl'";
        }

        $this->inDB->query($sql);

        return true;

    }

}