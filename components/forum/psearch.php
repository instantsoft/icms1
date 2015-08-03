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

function search_forum($query, $look){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $searchModel = cms_model_search::initModel();

    global $_LANG;
    cmsCore::loadLanguage('components/forum');
    $config = $inCore->loadComponentConfig('forum');

    // Ищем в названиях тем
    $sql = "SELECT t.*, f.title as forum, f.id as forum_id, f.access_list
            FROM cms_forum_threads t
            INNER JOIN cms_forums f ON f.id = t.forum_id
            WHERE MATCH(t.title) AGAINST ('$query' IN BOOLEAN MODE) AND t.is_hidden=0 LIMIT 50";

    $result = $inDB->query($sql);

    if ($inDB->num_rows($result)){
        while($item = $inDB->fetch_assoc($result)){

            if(!cmsCore::checkContentAccess($item['access_list'])) { continue; }

            $result_array = array();
            $pages = ceil($item['post_count'] / $config['pp_thread']);
            $result_array['link']        = "/forum/thread{$item['id']}-{$pages}.html";
            $result_array['place']       = $item['forum'];
            $result_array['placelink']   = "/forum/".$item['forum_id'];
            $result_array['title']       = $item['title'];
            $result_array['pubdate']     = $item['pubdate'];
            $result_array['description'] = $searchModel->getProposalWithSearchWord($item['description']);
            $result_array['session_id']  = session_id();

            $searchModel->addResult($result_array);
        }
    }

    // Ищем в тексте постов
    $sql = "SELECT p.*, t.title as thread, t.id as thread_id, t.post_count, img.fileurl
            FROM cms_forum_posts p
            INNER JOIN cms_forum_threads t ON t.id = p.thread_id AND t.is_hidden=0
            LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'post' AND img.component = 'forum'
            WHERE MATCH(p.content) AGAINST ('$query' IN BOOLEAN MODE) LIMIT 50";

    $result = $inDB->query($sql);

    if ($inDB->num_rows($result)){
        while($item = $inDB->fetch_assoc($result)){

            $result_array = array();

            $pages = ceil($item['post_count'] / $config['pp_thread']);
            $post_page = ($pages > 1) ? postPage::getPage($item['thread_id'], $item['id'], $config['pp_thread']) : 1;

            $result_array['link']        = "/forum/thread{$item['thread_id']}-{$post_page}.html#{$item['id']}";
            $result_array['place']       = $_LANG['FORUM_POST'];
            $result_array['placelink']   = $result_array['link'];
            $result_array['description'] = $searchModel->getProposalWithSearchWord($item['content_html']);
            $result_array['title']       = $item['thread'];
            $result_array['pubdate']     = $item['pubdate'];
            $result_array['imageurl']    = $item['fileurl'];
            $result_array['session_id']  = session_id();

            $searchModel->addResult($result_array);
        }
    }

    return;

}

class postPage {

    private static $post_ids = array();

    private function __construct() { }

    public static function getPage($thread_id, $post_id, $perpage) {

        $ids = self::getThreadPostIds($thread_id);

        $post_num = array_search($post_id, $ids);

        return ceil($post_num / $perpage);

    }

  private static function getThreadPostIds($thread_id) {

        if(isset(self::$post_ids[$thread_id])) { return self::$post_ids[$thread_id]; }

        $inDB = cmsDatabase::getInstance();

        $ids = array();

        $result = $inDB->query("SELECT id FROM cms_forum_posts WHERE thread_id = '$thread_id' ORDER BY pubdate ASC");

        if (!$inDB->num_rows($result)){ return array(); }

        $num = 1;

        while($data = $inDB->fetch_assoc($result)){

            $ids[$num] = $data['id'];

            $num++;

        }

        return $ids;

    }

}