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

	function getLink($file){

		$file = preg_replace ('/[^a-zA-Z0-9\-_\.]/i', '', trim($file));
		$file = str_replace ('..', '.', $file);
		$filefull = PATH.'/includes/myphp/'.$file;

        global $_LANG;

		if (file_exists($filefull)){
			ob_start();
			include $filefull;
			$link = ob_get_clean();
		} else {
            $link = $_LANG['FILE'].' "/includes/myphp/'.$file.'" '.$_LANG['NOT_FOUND'];
		}
		return $link;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function f_includes(&$text){

        $phrase = 'ФАЙЛ';

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
				$output = getLink($file);
			} else { $output = ''; }
			$text = str_replace('{'.$phrase.'='.$file.'}', $output, $text );
		}

		return true;

	}
?>