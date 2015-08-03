<?php
/******************************************************************************/
//                                                                            //
//                         InstantCMS v1.10.6                                 //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

function mod_tags($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    if(!@$cfg['targets']) { return false; }

    $tl = "'".implode("','", $cfg['targets'])."'";

    if(!isset($cfg['minfreq'])) { $cfg['minfreq']=0; }
    if(!isset($cfg['minlen'])) { $cfg['minlen'] = 0; }
    if(!isset($cfg['maxtags'])) { $cfg['maxtags'] = 20; }
    if(!isset($cfg['colors'])) { $cfg['colors'] = ''; }
    if(!isset($cfg['shuffle'])) { $cfg['shuffle'] = 0; }
    if(!isset($cfg['start_size'])) { $cfg['start_size'] = 10; }
    if(!isset($cfg['step'])) { $cfg['step'] = 4; }
    if(!isset($cfg['end_size'])) { $cfg['end_size'] = 50; }

    $sql  = "SELECT tag, COUNT(tag) as num
             FROM cms_tags WHERE target IN ({$tl}) ";
    $sql .= $cfg['minlen'] ? " AND CHAR_LENGTH(tag) >= {$cfg['minlen']}\n" : "\n";
    $sql .= "GROUP BY tag \n";
    $sql .= $cfg['minfreq'] ? "HAVING num >= {$cfg['minfreq']} \n" : '';
    $sql .= ($cfg['sortby'] == 'tag') ? "ORDER BY tag ASC \n" : "ORDER BY num DESC \n";
    $sql .= 'LIMIT '.$cfg['maxtags'];

    $result = $inDB->query($sql);
    if (!$inDB->num_rows($result)){ return false; }

    // массив возможных значений шрифта
    $sizes    = range($cfg['start_size'], $cfg['end_size'], $cfg['step']);
    $size_prc = ceil((100 / sizeof($sizes)));
    // Общее число тегов
    $summary = 0;
    while($tag = $inDB->fetch_assoc($result)){
        $tag['fontsize'] = '';
        $tags[]   = $tag;
        $summary += $tag['num'];
    }
    // формируем размер шрифта
    foreach($tags as $key=>$value){
        $prc = ceil(($value['num'] / $summary) * 100);
        foreach ($sizes as $k => $v) {
            if ($prc >= ($k*$size_prc)) {
                $tags[$key]['fontsize'] = $sizes[$k];
            }
        }
    }

    // перемешивать теги
    if($cfg['shuffle']){
        shuffle($tags);
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('tags', $tags)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}