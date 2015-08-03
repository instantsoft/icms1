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

function rss_forum($item_id, $cfg){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();

    if(!$inCore->isComponentEnable('forum')) { return false; }

    global $_LANG;

    $channel = array();

    if ($item_id){
        $cat = $inDB->get_fields('cms_forums', "id='$item_id'", 'id, title, description, NSLeft, NSRight');
        if(!$cat){ return; }
        $catsql = "AND cat.NSLeft >= {$cat['NSLeft']} AND cat.NSRight <= {$cat['NSRight']}";
        $channel['title']       = $cat['title'] ;
        $channel['description'] = $cat['description'];
        $channel['link']        = HOST . '/forum/' . $item_id;
    } else {
        $catsql = '';
        $channel['title']       = $_LANG['LAST_THREADS'];
        $channel['description'] = $_LANG['LAST_THREADS'];
        $channel['link']        = HOST . '/forum';
    }

    //ITEMS
    $sql = "SELECT c.*, cat.title as category
            FROM cms_forum_threads c
            INNER JOIN cms_forums cat ON cat.id = c.forum_id
            WHERE c.is_hidden = 0 $catsql
            ORDER by c.pubdate DESC
            LIMIT {$cfg['maxitems']}";

    $rs = $inDB->query($sql);

    $items = array();

    if ($inDB->num_rows($rs)){

        $forumcfg = $inCore->loadComponentConfig('forum');

        while ($item = $inDB->fetch_assoc($rs)){
            $id = $item['id'];
            $item['title'] .= ' '.$item['post_count'];
            $pages = ceil($item['post_count'] / $forumcfg['pp_thread']);
            $items[$id] = $item;
            $items[$id]['link']     = HOST . '/forum/thread'.$id.'-'.$pages.'.html';
            $items[$id]['category'] = $item['category'];
        }

    }

    $rssdata            = array();
    $rssdata['channel'] = $channel;
    $rssdata['items']   = $items;

    return $rssdata;

}