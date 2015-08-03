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

function files(){
    
    header('X-Frame-Options: DENY');

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

            display_link_template($fileurl, $model, $model->config['file_time']);

        } elseif(is_file(PATH.$fileurl)){

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

        display_link_template($url, $model, $model->config['redirect_time']);

    }

//============================================================================//

}

function display_link_template($link, $model, $time=10) {

    global $_LANG;

    $inPage = cmsPage::getInstance();

    $is_domain_banned = false;

    // проверяем ссылку
    if(function_exists('curl_init') && $model->config['check_link']){

        $link_domain = parse_url($link, PHP_URL_HOST);

        if(($model->config['white_list'] && $link_domain && !in_array($link_domain, $model->config['white_list'])) || !$model->config['white_list']){

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/utils.checkLink?url='.$link);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'InstantCMS/'.CORE_VERSION.' +'.HOST);

            $data = json_decode(curl_exec($ch), true);

            if(!isset($data['error']) && isset($data['response'])){
                $is_domain_banned = ($data['response']['status'] == 'banned');
                $link = $data['response']['link'];
            }

        }

    }


    $inPage->setTitle($_LANG['FILE_EXTERNAL_LINK']);
    $inPage->setDescription($_LANG['FILE_EXTERNAL_LINK']);

    cmsPage::initTemplate('components', 'com_files_redirect')->
            assign('url', htmlspecialchars($link))->
            assign('time', $time)->
            assign('sitename', cmsConfig::getConfig('sitename'))->
            assign('is_domain_banned', $is_domain_banned)->
            display('com_files_redirect.tpl');

}