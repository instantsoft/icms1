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

	function f_pages(&$text){

		if (mb_strpos($text, 'pagebreak') === false){
			return true;
		}

        $seolink = urldecode(cmsCore::request('seolink', 'str', ''));
        $seolink = preg_replace ('/[^a-zа-я-яёіїєґА-ЯЁІЇЄҐ0-9_\/\-]/ui', '', $seolink);

        if (!$seolink) return true;

		$regex = '/{(pagebreak)\s*(.*?)}/iu';

		$pages = preg_split($regex, $text);

		$n = count($pages);

		if ($n<=1){
			return true;
		} else {

			$page = cmsCore::request('page', 'int', 1);
			$text = $pages[$page-1];

            if(!$text){ cmsCore::error404(); }

            cmsCore::loadModel('content');

            $text .= cmsPage::getPagebar($n, $page, 1, cms_model_content::getArticleURL(null, $seolink, '%page%'));

			return true;

		}

	}

?>