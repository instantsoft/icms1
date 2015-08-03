<?php

////////////////////////////////////////////////////////////////////////////////
function html_bool_span($value, $condition) {
    if ($condition) {
        return '<span class="positive">' . $value . '</span>';
    } else {
        return '<span class="negative">' . $value . '</span>';
    }
}
////////////////////////////////////////////////////////////////////////////////
function check_requirements() {

    $min_php_version = '5.2.0';
    $extensions = array('json', 'mbstring', 'simplexml', 'iconv', 'mysqli');

    sort($extensions);

    $info = array();

    $info['valid'] = true;

    $info['php'] = array(
        'version' => PHP_VERSION,
        'valid' => (version_compare(PHP_VERSION, $min_php_version) >= 0)
    );

    $info['valid'] = $info['valid'] && $info['php']['valid'];

    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $info['ext'][$ext] = $loaded;
        $info['valid'] = $info['valid'] && $loaded;
    }

    return $info;

}
////////////////////////////////////////////////////////////////////////////////
function check_permissions(){

	$folders     = array('images','upload','includes','cache');
    $permissions = array();

	foreach($folders as $folder){
		$right = true;
        $path  = PATH.'/'.$folder;
		if(!@is_writable($path)){
			if (!@chmod($path, 0777)){
				$right = false;;
			}
		}
        $permissions[$folder] = array('valid'=>$right, 'perm'=>mb_substr(sprintf('%o', @fileperms($path)), -4));
	}

    return $permissions;

}
?>
