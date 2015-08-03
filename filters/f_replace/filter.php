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

function insertForm($form_title){

    cmsCore::loadClass('form');

    return cmsForm::displayForm(trim($form_title), array(), false);

}

function PhotoLink($photo_title){

    $photo_title = cmsCore::strClear($photo_title);

    $photo = cmsDatabase::getInstance()->get_fields('cms_photo_files', "title LIKE '{$photo_title}'", 'id, title');

    if($photo){
        $link = '<a href="/photos/photo'.$photo['id'].'.html" title="'.htmlspecialchars($photo['title']).'">'.$photo['title'].'</a>';
    } else { $link = ''; }

    return $link;
}

function AlbumLink($album_title){

    $album_title = cmsCore::strClear($album_title);

    $album = cmsDatabase::getInstance()->get_fields('cms_photo_albums', "title LIKE '{$album_title}'", 'id, title');

    if($album){
        $link = '<a href="/photos/'.$album['id'].'" title="'.htmlspecialchars($album['title']).'">'.$album['title'].'</a>';
    } else { $link = ''; }

    return $link;
}

function ContentLink($content_title){

    $content_title = cmsCore::strClear($content_title);

    $content = cmsDatabase::getInstance()->get_fields('cms_content', "title LIKE '{$content_title}'", 'seolink, title');

    if($content){
        $link = '<a href="/'.$content['seolink'].'.html" title="'.htmlspecialchars($content['title']).'">'.$content['title'].'</a>';
    } else { $link = ''; }

    return $link;
}

function replace_text($text, $phrase) {

    $regex   = '/{('.$phrase['title'].'=)\s*(.*?)}/ui';
    $matches = array();

    preg_match_all($regex, $text, $matches, PREG_SET_ORDER );
    foreach ($matches as $elm) {
        $elm[0] = str_replace(array('{','}'), '', $elm[0]);
        mb_parse_str($elm[0], $args);
        $arg=@$args[$phrase['title']];
        if ($arg){
            $output = call_user_func($phrase['function'], $arg);
        } else { $output = ''; }
        $text = str_replace('{'.$phrase['title'].'='.$arg.'}', $output, $text);
    }

    return $text;

}

////////////////////////////////////////////////////////////////////////////////
function f_replace(&$text){

    $phrases = array('photo'=>array('title'=>'ФОТО', 'function'=>'PhotoLink'),
                    'album'=>array('title'=>'АЛЬБОМ', 'function'=>'AlbumLink'),
                    'content'=>array('title'=>'МАТЕРИАЛ', 'function'=>'ContentLink'),
                    'form'=>array('title'=>'ФОРМА', 'function'=>'insertForm'),
                    'blank'=>array('title'=>'БЛАНК', 'function'=>'insertForm'));

    foreach ($phrases as $phrase) {
        if (mb_strpos($text, $phrase['title']) !== false){
            $text = replace_text($text, $phrase);
        }
    }

    return true;

}