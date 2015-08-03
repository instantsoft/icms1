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

function files(){

    $inDB = cmsDatabase::getInstance();

    global $_LANG;

    $do = cmsCore::getInstance()->do;

    $model = new cms_model_files();

//============================================================================//
    // Скачивание
    if ($do=='view'){

        $fileurl = cmsCore::request('fileurl', 'html', '');

        if(mb_strpos($fileurl, '-') === 0){
            $fileurl = htmlspecialchars_decode(base64_decode(ltrim($fileurl, '-')));
        }

        $fileurl = cmsCore::strClear($fileurl);

        if(!$fileurl || mb_strstr($fileurl, '..') || strpos($fileurl, '.') === 0){ cmsCore::error404(); }

        if (strpos($fileurl, 'http') === 0){

            $model->increaseDownloadCount($fileurl);

            cmsCore::redirect($fileurl);

        } elseif(file_exists(PATH.$fileurl)){

            $model->increaseDownloadCount($fileurl);

            header('Content-Disposition: attachment; filename='.basename($fileurl) . "\n");
            header('Content-Type: application/x-force-download; name="'.$fileurl.'"' . "\n");
            header('Location:'.$fileurl);
            cmsCore::halt();

        } else {
            cmsCore::halt($_LANG['FILE_NOT_FOUND']);
        }

    }

//============================================================================//

    if ($do=='redirect'){

    	$url = str_replace(array('--q--',' '), array('?','+'), cmsCore::request('url', 'str', ''));

        if(mb_strpos($url, '-') === 0){
            $url = htmlspecialchars_decode(base64_decode(ltrim($url, '-')));
        }

        $url = cmsCore::strClear($url);

        if(!$url || mb_strstr($url, '..') || strpos($url, '.') === 0){ cmsCore::error404(); }

        // кириллические домены
        $url_host = parse_url($url, PHP_URL_HOST);
        if(preg_match('/^[а-яё]+/iu', $url_host)){

            cmsCore::loadClass('idna_convert');

            $IDN = new idna_convert();

            $host = $IDN->encode($url_host);

            $url = str_ireplace($url_host, $host, $url);

        }
        cmsCore::redirect($url);

    }

//============================================================================//

}