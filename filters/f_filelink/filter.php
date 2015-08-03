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

	function getDownLoadLink($file){

		$file     = preg_replace('/\.+\//', '', trim($file));
		$file     = htmlspecialchars($file);
		$filefull = PATH.$file;

        global $_LANG;

        if (file_exists($filefull)){

			$downloaded = cmsCore::fileDownloadCount($file);

			$filesize = round(filesize($filefull)/1024, 2);

			$link = '<span class="filelink">';
				$link .= '<a href="/load/url=-'.base64_encode($file).'" alt="'.$_LANG['FILE_DOWNLOAD'].'">'.basename($file).'</a> ';
				$link .= '<span>| '.$filesize.' '.$_LANG['SIZE_KB'].'</span> ';
				$link .= '<span>| '.$_LANG['FILE_DOWNLOADED'].': '.cmsCore::spellCount($downloaded, $_LANG['TIME1'], $_LANG['TIME2'], $_LANG['TIME1']).'</span>';
			$link .= '</span>';

		} else {
			$link = $_LANG['FILE'].' "'.$file.'" '.$_LANG['NOT_FOUND'];
		}

		return $link;

	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function f_filelink(&$text){

        $phrase = 'СКАЧАТЬ';

		if (mb_strpos($text, $phrase) === false){
			return true;
		}

 		$regex = '/{('.$phrase.'=)\s*(.*?)}/i';
		$matches = array();
		preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
		foreach ($matches as $elm) {
			$elm[0] = str_replace('{', '', $elm[0]);
			$elm[0] = str_replace('}', '', $elm[0]);
			mb_parse_str( $elm[0], $args );
			$file=@$args[$phrase];
			if ($file){
				$output = getDownLoadLink($file);
			} else { $output = ''; }
			$text = str_replace('{'.$phrase.'='.$file.'}', $output, $text );
		}

		return true;

	}
?>