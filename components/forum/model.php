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

class cms_model_forum{

    private $abstract_array = array();
    private $last_addpoll_error = false;

    public function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('forum');
		cmsCore::loadLanguage('components/forum');
    }

////////////////////////////////////////////////////////////////////////////////
    public static function getDefaultConfig() {

        $cfg = array (
                'is_rss' => 1,
                'pp_thread' => 15,
                'pp_forum' => 15,
                'showimg' => 1,
                'img_on' => 1,
                'img_max' => 5,
                'fast_on' => 1,
                'fast_bb' => 1,
                'fa_on' => 1,
                'group_access' => '',
                'meta_keys' => '',
                'meta_desc' => '',
                'fa_max' => 25,
                'fa_ext' => 'txt doc zip rar arj png gif jpg jpeg bmp',
                'fa_size' => 1024,
                'edit_minutes' => 0,
                'watermark' => 0,
                'min_karma_add' => 0,
                'ranks' => array(),
                'modrank' => 0
              );

        return $cfg;

    }
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    public function isForumModerator($moder_list){

        if(cmsUser::isUserCan('forum/moderate')) { return true; }

        if (!$moder_list) { return false; }

        $moder_list = cmsCore::yamlToArray($moder_list);

		if (!is_array($moder_list)) { return false; }

		return in_array(cmsUser::getInstance()->id, $moder_list);

    }

    public function getForumModerators($moder_list){

        if (!$moder_list) { return array(); }

        $moder_list = cmsCore::yamlToArray($moder_list);
		if (!is_array($moder_list)) { return array(); }

        $ids_list = rtrim(implode(',', $moder_list), ',');

		return $this->inDB->get_table('cms_users', "id IN ({$ids_list})", 'id, login, nickname');

    }

////////////////////////////////////////////////////////////////////////////////
////////////// методы для кеширования статистических даных /////////////////////
////////////////////////////////////////////////////////////////////////////////
    /**
     * Кеширует количество постов тем форума
     * в таблицу cms_forums
     * @param int $forum_left_key
     * @param int $forum_right_key
     * @return bool
     */
    public function updateForumPostsCount($forum_left_key, $forum_right_key) {

		$sql = "SELECT IFNULL(SUM(t.post_count), 0) AS post_count
				FROM cms_forum_threads t
				INNER JOIN cms_forums f ON f.id = t.forum_id AND f.NSLeft >= '$forum_left_key' AND f.NSRight <= '$forum_right_key' AND f.published = 1";
        $result = $this->inDB->query($sql);
        $post = $this->inDB->fetch_assoc($result);

        return $this->inDB->query("UPDATE cms_forums SET post_count = '{$post['post_count']}' WHERE NSLeft = '$forum_left_key' AND NSRight = '$forum_right_key' LIMIT 1");

    }
    /**
     * Кеширует количество постов темы
     * в таблицу cms_forum_threads
     * @param int $thread_id
     * @return bool
     */
    public function updateThreadPostCount($thread_id) {

		$sql = "SELECT p.id
				FROM cms_forum_posts p
				INNER JOIN cms_forum_threads t ON t.id = '{$thread_id}' AND t.id = p.thread_id";
        $result = $this->inDB->query($sql);
        $post_count = $this->inDB->num_rows($result);

        $this->inDB->query("UPDATE cms_forum_threads SET post_count = '{$post_count}' WHERE id = '{$thread_id}' LIMIT 1");

        return $post_count;

    }
    /**
     * Кеширует последний пост в теме
     * в таблицу cms_forum_threads
     * @param int $thread_id
     * @return bool
     */
    public function cacheThreadLastPost($thread_id) {

        $sql = "SELECT p.pubdate, p.id, p.content_html,
                       u.nickname as author_nickname,
                       u.login as author_login,
                       t.title as thread_title, t.id as thread_id, t.post_count
                FROM cms_forum_posts p
                INNER JOIN cms_forum_threads t ON t.id = p.thread_id AND t.id = '{$thread_id}'
                LEFT JOIN cms_users u ON u.id = p.user_id
                ORDER BY p.pubdate DESC
                LIMIT 1";

        $result = $this->inDB->query($sql);
        if (!$this->inDB->num_rows($result)){ return true; }

        $post = $this->formLastPostArray($this->inDB->fetch_assoc($result));

        $yaml_post = $this->inDB->escape_string(cmsCore::arrayToYaml($post));

        return $this->inDB->query("UPDATE cms_forum_threads SET last_msg = '{$yaml_post}', pubdate = '{$post['pubdate']}' WHERE id = '{$post['thread_id']}' LIMIT 1");

    }
    /**
     * Кеширует количество тем форума
     * в таблицу cms_forums
     * @param int $forum_left_key
     * @param int $forum_right_key
     * @return bool
     */
    public function updateForumThreadsCount($forum_left_key, $forum_right_key) {

		$sql = "SELECT t.id
				FROM cms_forum_threads t
				INNER JOIN cms_forums f ON f.id = t.forum_id AND f.NSLeft >= '$forum_left_key' AND f.NSRight <= '$forum_right_key' AND f.published = 1";
        $result = $this->inDB->query($sql);
        $thread_count = $this->inDB->num_rows($result);

        return $this->inDB->query("UPDATE cms_forums SET thread_count = '{$thread_count}' WHERE NSLeft = '$forum_left_key' AND NSRight = '$forum_right_key' LIMIT 1");

    }
    /**
     * Кеширует массив последнего сообщения темы форума
     * в таблицу cms_forums и cms_forum_threads
     * @param int $forum_left_key
     * @param int $forum_right_key
     * @return bool
     */
    public function cacheLastPost($forum_left_key, $forum_right_key) {

        $post = $this->getForumLastPost($forum_left_key, $forum_right_key);

        $yaml_post = $this->inDB->escape_string(cmsCore::arrayToYaml($post));

        $this->inDB->query("UPDATE cms_forums SET last_msg = '{$yaml_post}' WHERE NSLeft = '$forum_left_key' AND NSRight = '$forum_right_key' LIMIT 1");

        if(isset($post['thread_id'])){

            $this->inDB->query("UPDATE cms_forum_threads SET last_msg = '{$yaml_post}', pubdate = '{$post['pubdate']}' WHERE id = '{$post['thread_id']}' LIMIT 1");

        }

        return true;

    }
    /**
     * Кеширует данные форума
     * используя методы выше
     * @param int $forum_left_key
     * @param int $forum_right_key
     * @param bool $and_parent Метка обновления текущего форума и выше него
     * @return bool
     */
    public function updateForumCache($forum_left_key, $forum_right_key, $and_parent = false) {

        if(!$and_parent){
            $this->updateForumPostsCount($forum_left_key, $forum_right_key);
            $this->updateForumThreadsCount($forum_left_key, $forum_right_key);
            $this->cacheLastPost($forum_left_key, $forum_right_key);
            return true;
        }

        $path_list = $this->inDB->getNsCategoryPath('cms_forums', $forum_left_key, $forum_right_key, 'NSLeft, NSRight');
        if ($path_list){
            $path_list = array_reverse($path_list);
            foreach($path_list as $pcat){
                $this->updateForumCache($pcat['NSLeft'], $pcat['NSRight']);
            }
        }

    }


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает тему форума
     * @param int $id ID темы
     * @return array
     */
    public function getThread($id){

        $thread = $this->inDB->get_fields('cms_forum_threads t
                                           INNER JOIN cms_forums f ON f.id = t.forum_id',
                                           "t.id = '$id'",
                                           't.*, f.NSLeft, f.NSRight');
        if(!$thread){ return false; }

        $thread['fpubdate'] = cmsCore::dateFormat($thread['pubdate']);
        $thread['is_mythread'] = $thread['user_id'] == cmsUser::getInstance()->id;

        return cmsCore::callEvent('GET_FORUM_THREAD', $thread);

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает категорию форума
     * @param mixed $id
     * @return array
     */
    public function getForumCat($id){

        if(is_numeric($id)){
            $where = "id = '$id'";
        } else {
            $where = "seolink = '$id'";
        }

        $cat = $this->inDB->get_fields('cms_forum_cats', $where, '*');
        if(!$cat){ return false; }

        return cmsCore::callEvent('GET_FORUM_CAT', $cat);

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает форум
     * @param int $id ID форума
     * @return array
     */
    public function getForum($id){

        $forum = $this->inDB->get_fields('cms_forums', "id = '$id'", '*');
        if(!$forum){ return false; }

        $forum['last_msg_array'] = cmsCore::yamlToArray($forum['last_msg']);
        if($forum['last_msg_array']){
            $forum['last_msg_array']['fpubdate'] = cmsCore::dateFormat($forum['last_msg_array']['pubdate']);
        }

        return cmsCore::callEvent('GET_FORUM', $forum);

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает пост темы
     * @param int $id
     * @return array
     */
    public function getPost($id){

        $post = $this->inDB->get_fields('cms_forum_posts', "id = '$id'", '*');
        if(!$post){ return false; }

        return cmsCore::callEvent('GET_FORUM_POST', $post);

    }

////////////////////////////////////////////////////////////////////////////////
    /**
     * Возвращает последний пост форума
     * @param int $forum_left_key
     * @param int $forum_right_key
     * @return array
     */
    public function getForumLastPost($left_key, $right_key){

        $sql = "SELECT p.pubdate, p.id, p.content_html,
                       u.nickname as author_nickname,
                       u.login as author_login,
                       t.title as thread_title, t.id as thread_id, t.post_count
                FROM cms_forum_posts p
                INNER JOIN cms_forum_threads t ON t.id = p.thread_id
                INNER JOIN cms_forums f ON f.id = t.forum_id AND f.NSLeft >= '{$left_key}' AND f.NSRight <= '{$right_key}'
                LEFT JOIN cms_users u ON u.id = p.user_id
                ORDER BY p.pubdate DESC
                LIMIT 1";

        $result = $this->inDB->query($sql) ;
        if (!$this->inDB->num_rows($result)){ return array(); }

        return $this->formLastPostArray($this->inDB->fetch_assoc($result));

    }

    private function formLastPostArray($post) {

        $post['lastpage'] = ceil($post['post_count'] / $this->config['pp_thread']);
        $link = '/forum/thread'.$post['thread_id'].'-'.$post['lastpage'].'.html#'.$post['id'];
        $post['thread_link'] = '<a href="'.$link.'">'.$post['thread_title'].'</a>';
        $post['user_link'] = cmsUser::getProfileLink($post['author_login'], $post['author_nickname']);

        return $post;

    }

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    public function whereForumCatIs($cat_id) {
        $this->inDB->where("f.category_id = '{$cat_id}'");
    }

    public function whereNestedForum($left_key, $right_key) {
        $this->inDB->where("f.NSLeft > '{$left_key}' AND f.NSRight < '{$right_key}'");
    }

    public function whereThisAndNestedForum($left_key, $right_key) {
        $this->inDB->where("f.NSLeft >= '{$left_key}' AND f.NSRight <= '{$right_key}'");
    }

    public function getForums($is_admin=false){

        $pub_sql = $is_admin ? '' : ' AND f.published = 1';

        $sql = "SELECT f.*, cat.title as cat_title, cat.seolink as cat_seolink, cat.id as cat_id
                FROM cms_forums f
                LEFT JOIN cms_forum_cats cat ON cat.id = f.category_id
                WHERE f.parent_id > 0 AND cat.published = 1 {$pub_sql}
                {$this->inDB->where}
                ORDER BY cat.ordering, f.NSLeft \n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return array(); }

        $forums = array();

		while ($forum = $this->inDB->fetch_assoc($result)){

            // проверяем доступ к форуму
            if(!cmsCore::checkContentAccess($forum['access_list'])) { continue; }
            // получаем массив последнего сообщения
            $forum['last_msg_array'] = cmsCore::yamlToArray($forum['last_msg']);
            if($forum['last_msg_array']){
                $forum['last_msg_array']['fpubdate'] = cmsCore::dateFormat($forum['last_msg_array']['pubdate']);
            }
            // Путь до иконки форума
            $forum['icon_url'] = '/upload/forum/cat_icons/'.($forum['icon'] ? $forum['icon'] : 'forum.gif');

            $forums[] = $forum;

        }

        if($forums){

            $forums = translations::process(cmsConfig::getConfig('lang'), 'forum_forums', $forums);
            $forums = translations::process(cmsConfig::getConfig('lang'), 'forum_forum_cats', $forums, 'cat_id', array(
                'title'=>'cat_title'
            ));

            foreach ($forums as $f) {

                // Уровень первого элемента
                $first_level = isset($first_level) ? $first_level : $f['NSLevel'];

                // Формируем корневой уровень
                if($f['NSLevel'] == $first_level){
                    $nested_forums[] = $f;
                } else { // формируем подфорумы
                    $k = array_keys($nested_forums);
                    $nested_forums[end($k)]['sub_forums'][] = $f;
                }

            }
        }

        return cmsCore::callEvent('GET_FORUMS', $nested_forums);

    }

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    public function whereDayIntervalIs($day) {
        $this->inDB->where("DATEDIFF(NOW(), t.pubdate) <= {$day}");
    }

    public function whereForumIs($forum_id) {
        $this->inDB->where("t.forum_id = '{$forum_id}'");
    }

    public function whereThreadUserIs($user_id) {
        $this->inDB->where("t.user_id = '{$user_id}'");
    }

    public function wherePublicThreads() {
        $this->inDB->where("t.is_hidden = 0");
    }

    public function wherePinnedThreads() {
        $this->inDB->where("t.pinned = 1");
    }

    public function getThreads(){

        $sql = "SELECT t.*, u.nickname, u.login {$this->inDB->select}
                FROM cms_forum_threads t
                LEFT JOIN cms_users u ON u.id = t.user_id
                {$this->inDB->join}
                WHERE 1=1
                {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by} \n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return array(); }

		while ($thread = $this->inDB->fetch_assoc($result)){

            $thread['last_msg_array'] = cmsCore::yamlToArray($thread['last_msg']);
            if($thread['last_msg_array']){
                $thread['last_msg_array']['fpubdate'] = cmsCore::dateFormat($thread['last_msg_array']['pubdate']);
            }
            $thread['fpubdate']       = cmsCore::dateFormat($thread['pubdate']);
            $thread['is_new']         = (bool)(strtotime($thread['pubdate']) > strtotime(cmsUser::getInstance()->logdate));
            $thread['answers']        = $thread['post_count']-1;
            $thread['pages']          = ceil($thread['post_count'] / $this->config['pp_thread']);

            $threads[] = $thread;

        }

        return cmsCore::callEvent('GET_THREADS', $threads);

    }

    public function getThreadsCount() {

        $sql = "SELECT 1
                FROM cms_forum_threads t
                WHERE 1=1
                {$this->inDB->where}
                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    public function whereThreadIs($thread_id) {
        $this->inDB->where("p.thread_id = '{$thread_id}'");
    }

    public function wherePostUserIs($user_id) {
        $this->inDB->where("p.user_id = '{$user_id}'");
    }

    public function getPosts(){

        $inUser = cmsUser::getInstance();

        $sql = "SELECT p.id, p.thread_id, p.user_id, p.pubdate, p.editdate, p.edittimes, p.rating, p.attach_count, p.content_html, p.pinned,
                       u.nickname, u.login, u.is_deleted, u.logdate, u.status,
                       up.imageurl, up.signature_html, up.city, up.karma, g.access as group_access, g.is_admin {$this->inDB->select}
                FROM cms_forum_posts p
                {$this->inDB->join}
                LEFT JOIN cms_users u ON u.id = p.user_id
                LEFT JOIN cms_user_profiles up ON up.user_id = u.id
                LEFT JOIN cms_user_groups g ON u.group_id = g.id
                WHERE 1=1
                {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by} \n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return array(); }

		while ($post = $this->inDB->fetch_assoc($result)){

            $post['fpubdate']    = cmsCore::dateFormat($post['pubdate']);
            $post['wday']        = cmsCore::dateToWday($post['pubdate']);
            $post['peditdate']   = cmsCore::dateFormat($post['editdate'], true, true);
            $post['post_count']  = $this->getUserPostsCount($post['user_id']);
            $post['avatar_url']  = cmsUser::getUserAvatarUrl($post['user_id'], 'small', $post['imageurl'], $post['is_deleted']);
            $post['flogdate']    = cmsUser::getOnlineStatus($post['user_id'], $post['logdate']);
            $post['userrank']    = $this->getForumUserRank($post);
            $post['user_awards'] = cmsUser::getAwardsList($post['user_id']);
            $post['attached_files'] = $this->config['fa_on'] && $post['attach_count'] ?
                                            $this->getPostAttachments($post['id']) : array();
            $end_min = $this->checkEditTime($post['pubdate']);
            $post['is_author'] = ($post['user_id'] == cmsUser::getInstance()->id);
            $post['is_author_can_edit'] = (is_bool($end_min) ? $end_min : $end_min > 0) && $post['is_author'];
			if ($inUser->id){
				$post['is_voted'] = $post['is_author'] ? true : cmsUser::isRateUser('forum_post', $inUser->id, $post['id']);
			} else {
				$post['is_voted'] = true;
			}

            $posts[] = $post;

        }

        $this->resetAbstractArray();

        return cmsCore::callEvent('GET_FORUM_POSTS', $posts);

    }

    public function getPostsCount() {

        $sql = "SELECT 1
                FROM cms_forum_posts p
                {$this->inDB->join}
                WHERE 1=1
                {$this->inDB->where}
                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    public function resetAbstractArray($array_param=0){

		if(!$array_param){
			$this->abstract_array = array();
		} else {
        	$this->abstract_array[$array_param] = array();
		}

    }

	public function getUserPostsCount($user_id){

		if(!isset($this->abstract_array['users_post_count'][$user_id])){
			$this->abstract_array['users_post_count'][$user_id] = $this->inDB->rows_count('cms_forum_posts', "user_id = '$user_id'");
		}

		return $this->abstract_array['users_post_count'][$user_id];

	}

	public function getForumUserRank($post){

		global $_LANG;

        // Значения по умолчанию
        $output = array('group'=>'','rank'=>$_LANG['USER'], 'class'=>'user_rank');

        // Получаем звания
        if(is_array($this->config['ranks'])){
            foreach($this->config['ranks'] as $rank){
                if ($post['post_count'] >= $rank['msg'] && $rank['msg']){
                    $user_rank = $rank['title'];
                }
            }
        }

        // Звания для админов
        if($post['is_admin']){
            $output['class'] = 'admin_rank';
            $output['group'] = $_LANG['ADMINISTRATOR'];
            $output['rank']  = $this->config['modrank'] && isset($user_rank) ? $user_rank : '';

            return $output;

        }
        // Звания для модераторов
        if (mb_strstr($post['group_access'], 'forum/moderate')){
            $output['group'] = $_LANG['MODER'];
            $output['rank']  = $this->config['modrank'] && isset($user_rank) ? $user_rank : '';
            $output['class'] = 'moder_rank';

            return $output;

        }
        // Для остальных пользователей
        $output['rank'] = @$user_rank ? $user_rank : $output['rank'];

		return $output;

	}

    public function checkEditTime($pubdate) {

        if($this->config['edit_minutes'] == 0) { return true; }
        if($this->config['edit_minutes'] == -1) { return false; }

		return $this->config['edit_minutes'] - round((time() - strtotime($pubdate))/60);

    }

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    public function addPost($post){

        // Получаем последний пост темы
        $last_post = $this->inDB->get_fields('cms_forum_posts', "thread_id = '{$post['thread_id']}'", '*', 'pubdate DESC');
        // Если он этого же автора и прошло не более 20 минут, склеиваем, иначе просто добавляем
        $minutes_passed = round((time() - strtotime($last_post['pubdate']))/60);
        if($last_post['user_id'] == $post['user_id'] && $minutes_passed < 20){

            global $_LANG;

            $u_post['content']      = $this->inDB->escape_string($last_post['content']."\r\n".$post['content']);
            $u_post['content_html'] = $this->inDB->escape_string($last_post['content_html'].'<em class="added_later">'.
                                        $_LANG['ADDED_LATER'].
                                        ($minutes_passed ?
                                        cmsCore::spellCount($minutes_passed, $_LANG['MINUTU1'], $_LANG['MINUTE2'], $_LANG['MINUTE10']) :
                                        $_LANG['FEW_SECONDS']).
                                        '</em>'.$post['content_html']);

            $this->updatePost($u_post, $last_post['id']);

            return $last_post['id'];

        }

        $post_id = $this->inDB->insert('cms_forum_posts', cmsCore::callEvent('ADD_FORUM_POST', $post));

        // регистрируем загруженные изображения
        cmsCore::setIdUploadImage('post', $post_id);

        return $post_id;

    }

    public function updatePost($post, $post_id){

        cmsCore::callEvent('UPDATE_FORUM_POST', $post_id);

        cmsCore::setIdUploadImage('post', $post_id);

        return $this->inDB->update('cms_forum_posts', $post, $post_id);

    }

    public function deletePost($post_id){

        cmsCore::callEvent('DELETE_POST', $post_id);

        $this->deletePostAttachments($post_id);
        cmsCore::deleteUploadImages($post_id, 'post');
        $this->inDB->delete('cms_forum_posts', "id = '{$post_id}'", 1);
        cmsActions::removeObjectLog('add_fpost', $post_id);

        return true;

    }

    public function isBelongsToPostTopic($post_id, $thread_id){

        return $this->inDB->get_field('cms_forum_posts', "id = '$post_id' AND thread_id = '$thread_id'", 'id');

    }

    public function addThread($thread){

        return $this->inDB->insert('cms_forum_threads', cmsCore::callEvent('ADD_THREAD', $thread));

    }

    public function closeThread($thread_id){

        cmsCore::callEvent('CLOSE_THREAD', $thread_id);

        global $_LANG;

        return $this->inDB->query("UPDATE cms_forum_threads
                                   SET title = CONCAT('{$_LANG['TOPIC_FIXED_PREFIX']} ', title), closed = 1
                                   WHERE id = '$thread_id'");

    }

    public function openThread($thread_id){

        cmsCore::callEvent('OPEN_THREAD', $thread_id);

        global $_LANG;

        return $this->inDB->query("UPDATE cms_forum_threads
                                   SET title = REPLACE(title, '{$_LANG['TOPIC_FIXED_PREFIX']} ', ''), closed = 0
                                   WHERE id = '$thread_id'");

    }

    public function deleteThread($thread_id){

        cmsActions::removeObjectLog('add_thread', $thread_id);

        $this->whereThreadIs($thread_id);
        $posts = $this->getPosts();

        if ($posts){
            foreach ($posts as $post) {
                $this->deletePost($post['id']);
            }
        }

        $this->inDB->query("DELETE FROM cms_forum_threads WHERE id = '{$thread_id}'");

        $poll = $this->getThreadPoll($thread_id);
        if($poll){
            $this->deletePoll($poll['id']);
        }

    }

    public function updateThread($thread, $thread_id){

        cmsCore::callEvent('UPDATE_THREAD', $thread_id);

        return $this->inDB->update('cms_forum_threads', $thread, $thread_id);

    }

    public function deleteAllUserPosts($user_id){

        $this->inDB->query("DELETE FROM cms_forum_threads WHERE user_id = '{$user_id}'");
        $this->inDB->query("DELETE FROM cms_forum_posts WHERE user_id = '{$user_id}'");
        $this->inDB->query("DELETE FROM cms_forum_votes WHERE user_id = '{$user_id}'");

        $action_add_thread_id = (int)$this->inDB->get_field('cms_actions', "name='add_thread'", 'id');
        $action_add_fpost_id  = (int)$this->inDB->get_field('cms_actions', "name='add_fpost'", 'id');

        $this->inDB->query("DELETE FROM cms_actions_log WHERE action_id IN({$action_add_thread_id},{$action_add_fpost_id}) AND user_id = '{$user_id}'");

        return true;

    }

////////////////////////////////////////////////////////////////////////////////
/////////////////////// методы для прикрепленных файлов ////////////////////////
////////////////////////////////////////////////////////////////////////////////
    /**
     * Загружает и добавляет/обновляет файл(ы)
     * @param int $post_id
     * @param array $input_file
     * @return str имя файла
     */
    public function addUpdatePostAttachments($post_id, $input_file=array(), $count_files = 0) {

        if(!@$_FILES['fa']['name'][0]) { return 0; }

        $success = true;

        foreach ($_FILES['fa']['error'] as $key => $error) {

            if($count_files > $this->config['fa_max']) { $success = false; break; }
            if($_FILES['fa']['size'][$key] > $this->config['fa_size']*1024) { $success = false; continue; }

            $file = $_FILES['fa']['name'][$key];

            $pp  = pathinfo($file);
            $ext = mb_strtolower($pp['extension']);

            if(in_array($ext, array('php','htm','html','htaccess'))) { $success = false; continue; }
            if (!mb_stristr($this->config['fa_ext'], $ext)){ $success = false; continue; }

            $file = cmsCore::strToURL(mb_substr($file, 0, mb_strrpos($file, '.'))) . '_' . uniqid() . '.' . $ext;

            @mkdir(PATH.'/upload/forum/post'.$post_id);
            $destination = PATH.'/upload/forum/post'.$post_id.'/'.$file;

            // Формируем массив
            $result['filesize'] = $this->inDB->escape_string($_FILES['fa']['size'][$key]);
            $result['post_id']  = $post_id;
            $result['hits']     = 0;
            $result['pubdate']  = date("Y-m-d H:i:s");
            $result['filename'] = $this->inDB->escape_string($file);

            if (cmsCore::moveUploadedFile($_FILES['fa']['tmp_name'][$key], $destination, $error)) {

                if(!$input_file){ //Если новый файл

                    $this->inDB->insert('cms_forum_files', $result);

                } else { // Если обновляем файл

                    $this->deletePostAttachment($input_file, false);

                    $this->inDB->update('cms_forum_files', $result, $input_file['id']);

                    break; // подразумевается, что обновляем 1 файл

                }

            } else {

                $success = false;
                @rmdir(PATH.'/upload/forum/post'.$post_id);

            }

            $count_files++;

        }

        $this->updatePostAttachmentsCount($post_id);

        return $success;

    }
    /**
     * Кеширует кол-во вложений к посту
     * @param int $post_id
     * @return bool
     */
    public function updatePostAttachmentsCount($post_id) {

        $post_file_count = $this->inDB->rows_count('cms_forum_files', "post_id = '$post_id'");

        return $this->inDB->update('cms_forum_posts', array('attach_count'=>$post_file_count), $post_id);

    }
    /**
     * Удаляет прикрепленные файлы к посту темы
     * @param int $post_id
     * @return bool
     */
    public function deletePostAttachments($post_id) {

        $files = $this->getPostAttachments($post_id);
        if(!$files) { return false; }

        foreach ($files as $file) {
            $this->deletePostAttachment($file);
        }

        return true;

    }
    /**
     * Удаляет файл
     * @param array $file
     * @return bool
     */
    public function deletePostAttachment($file, $whith_db = true) {

        cmsCore::callEvent('DELETE_POST_FILE', $file);

        @unlink(PATH.'/upload/forum/post'.$file['post_id'].'/'.$file['filename']);
        @rmdir(PATH.'/upload/forum/post'.$file['post_id']);
        if($whith_db){
            $this->inDB->delete('cms_forum_files', "id = '{$file['id']}'", 1);
            $this->updatePostAttachmentsCount($file['post_id']);
        }

        return true;

    }
    /**
     * Получает прикрепленный файл
     * @param array $file
     * @return bool
     */
    public function getPostAttachment($file_id) {

        $file = $this->inDB->get_fields('cms_forum_files', "id = '{$file_id}'", '*');
        if(!$file){ return false; }

        return cmsCore::callEvent('GET_POST_FILE', $file);

    }
    /**
     * Возвращает файлы поста
     * @param int $post_id
     * @return bool
     */
	public function getPostAttachments($post_id){

		$sql = "SELECT *
				FROM cms_forum_files
				WHERE post_id = '$post_id'";
		$result = $this->inDB->query($sql) ;

		if (!$this->inDB->num_rows($result)){ return array(); }

        $img_ext = array('jpg','jpeg','gif','png','bmp');

		while($file = $this->inDB->fetch_assoc($result)){

			$path_parts = pathinfo($file['filename']);
			$ext = $path_parts['extension'];

            $file['is_img'] = in_array($ext, $img_ext) && $this->config['showimg'];

            $file['icon'] = cmsCore::fileIcon($file['filename']);
            $file['filesize_kb'] = round(($file['filesize']/1024),2);

            $files[] = $file;

        }

        return cmsCore::callEvent('GET_POST_FILES', $files);

	}

////////////////////////////////////////////////////////////////////////////////
/////////////////////////// методы для опросов /////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    /**
     * Создает опрос
     * @param array $poll
     * @param int $thread_id
     * @return int
     */
    public function addPoll($poll, $thread_id){

        global $_LANG;

        // если заголовка опроса нет, считаем что опрос не нужен
        $t_poll['title'] = cmsCore::strClear($poll['title']);
        if(!$t_poll['title']) { return false; }

        $t_poll['description'] = cmsCore::strClear($poll['desc']);
        $t_poll['enddate'] = date("Y-m-d H:i:s", (((int)$poll['days']*86400) + time()));

        $options['result'] = (int)$poll['result'];
        $options['change'] = (int)$poll['change'];
        $t_poll['options'] = cmsCore::arrayToYaml($options);

        $answers = array();
        foreach($poll['answers'] as $answer){
            if ($answer) {
                $answers[strip_tags($answer)] = 0;
            }
        }
        if (sizeof($answers)<2){ $this->last_addpoll_error = $_LANG['ERR_POLL_VARIANT']; return false; }

        $t_poll['answers']   = $this->inDB->escape_string(cmsCore::arrayToYaml($answers));
        $t_poll['thread_id'] = $thread_id;

        return $this->inDB->insert('cms_forum_polls', cmsCore::callEvent('ADD_FORUM_POLL', $t_poll));

    }
    /**
     * Обновляет опрос
     * @param array $poll
     * @param array $latest_poll
     * @return bool
     */
    public function updatePoll($poll, $latest_poll){

        global $_LANG;

        $t_poll['title'] = cmsCore::strClear($poll['title']);
        if(!$t_poll['title']) { return false; }

        $t_poll['description'] = cmsCore::strClear($poll['desc']);
        $t_poll['enddate'] = date("Y-m-d H:i:s", (((int)$poll['days']*86400) + time()));

        $options['result'] = (int)$poll['result'];
        $options['change'] = (int)$poll['change'];
        $t_poll['options'] = cmsCore::arrayToYaml($options);

        // Формируем массив голосов
        foreach ($latest_poll['answers'] as $vote_count) {
            $vote_counts[] = $vote_count;
        }
        // Новый массив вопросов
        foreach($poll['answers'] as $new_answer){
            $new_answers[] = strip_tags($new_answer);
        }
        // Результирующий массив
        foreach($new_answers as $key=>$answer){
            if($answer) {
                $answers[$answer] = isset($vote_counts[$key]) ? $vote_counts[$key] : 0;
            }
        }

        if (sizeof($answers)<2){ $this->last_addpoll_error = $_LANG['ERR_POLL_VARIANT']; return false; }

        $t_poll['answers'] = $this->inDB->escape_string(cmsCore::arrayToYaml($answers));

        return $this->inDB->update('cms_forum_polls', cmsCore::callEvent('UPDATE_FORUM_POLL', $t_poll), $latest_poll['id']);

    }

    public function getLastAddPollError() {
        return $this->last_addpoll_error;
    }

    public function getThreadPoll($thread_id) {

        return $this->getPoll("thread_id = '{$thread_id}'");

    }

    public function getPollById($id) {

        return $this->getPoll("id = '{$id}'");

    }
    /**
     * Возвращает опрос
     * @param str $where условия запроса
     * @return int
     */
    public function getPoll($where) {

        $poll = $this->inDB->get_fields('cms_forum_polls', $where, '*');
        if(!$poll){ return false; }

        global $_LANG;

        $poll['answers'] = cmsCore::yamlToArray($poll['answers']);
        $num = 1;
        foreach ($poll['answers'] as $key => $value) {
            $poll['answers_key'][$num] = $key;
            $num++;
        }
        $poll['options'] = cmsCore::yamlToArray($poll['options']);
        switch ($poll['options']['result']) {
            case 0:
                $poll['options']['result_text'] = $_LANG['AVAILABLE_FOR_ALL'];
                break;
            case 1:
                $poll['options']['result_text'] = $_LANG['AVAILABLE_FOR_VOTERS'];
                break;
            case 2:
                $poll['options']['result_text'] = $_LANG['AVAILABLE_AFTER_VOTE'];
                break;
        }
        switch ($poll['options']['change']) {
            case 0:
                $poll['options']['change_text'] = $_LANG['PROHIBITED'];
                break;
            case 1:
                $poll['options']['change_text'] = $_LANG['ALLOW'];
                break;
        }
        $poll['days_left'] = round((strtotime($poll['enddate']) - time())/86400);
        $poll['days_left'] = $poll['days_left'] < 0 ? 0 : $poll['days_left'];
        $poll['is_closed'] = $poll['days_left'] <= 0;
        $poll['is_user_vote'] = $this->isUserVoted($poll['id']);
        // совместимость старых типов ответов, по id
        // если пользователь проголосовал
        if(!is_bool($poll['is_user_vote'])){
            if(is_numeric($poll['is_user_vote'])){
                $poll['is_user_vote'] = $poll['answers_key'][$poll['is_user_vote']];
            }
        }
        $poll['vote_count']   = $this->getVoteCount($poll['answers']);
        $poll['fenddate']     = cmsCore::dateFormat($poll['enddate']);
        $poll['show_result']  = false;

        return cmsCore::callEvent('GET_THREAD_POLL', $poll);

    }

    public function deletePoll($poll_id){

        cmsCore::callEvent('DELETE_FORUM_POLL', $poll_id);

        $this->inDB->delete('cms_forum_polls', "id = '$poll_id'", 1);
        $this->inDB->delete('cms_forum_votes', "poll_id = '$poll_id'", 1);

        return true;

    }

    private function getVoteCount($poll_answers = array()){

        $count = 0;

        foreach($poll_answers as $num){
            $count += (int)$num;
        }

        return $count;

    }

    public function isUserVoted($poll_id){

        $inUser = cmsUser::getInstance();

        // считаем что гости голосовали
        if(!$inUser->id) { return true; }

        return $this->inDB->get_field('cms_forum_votes', "user_id='{$inUser->id}' AND poll_id = '$poll_id'", 'answer');

    }

    public function votePoll($poll, $answer){

        if(!$poll['answers']){ return false; }

        $inUser = cmsUser::getInstance();

        //Прибавляем голос к переданному нам варианту ответа
        foreach($poll['answers'] as $key=>$value){
            if ($key == stripslashes($answer)){
                $poll['answers'][$key] += 1;
            }
        }

        $answers = $this->inDB->escape_string(cmsCore::arrayToYaml($poll['answers']));

        //Сохраняем результаты опроса
        $sql = "UPDATE cms_forum_polls SET answers = '{$answers}' WHERE id = '{$poll['id']}'";
        $this->inDB->query($sql);

        // помечаем кто за что проголосовал
        $sql = "INSERT cms_forum_votes (poll_id, answer, user_id, pubdate)
                VALUES ('{$poll['id']}', '$answer', '{$inUser->id}', NOW())";
        $this->inDB->query($sql);

        return true;

    }

    public function deleteVote($poll){

        if(!$poll['answers']){ return false; }

        $inUser = cmsUser::getInstance();

        // Убираем голос пользователя
        foreach($poll['answers'] as $key=>$value){
            if ($key == $poll['is_user_vote']){
                $poll['answers'][$key] -= 1;
            }
        }

        $answers = $this->inDB->escape_string(cmsCore::arrayToYaml($poll['answers']));

        $this->inDB->query("UPDATE cms_forum_polls SET answers = '{$answers}' WHERE id = '{$poll['id']}'");

        $this->inDB->delete('cms_forum_votes', "poll_id = '{$poll['id']}' AND user_id = '{$inUser->id}'", 1);

        return true;

    }

    public function getCatSeoLink($title = '', $id = 0){

        $seolink = cmsCore::strToURL($title);

        if ($id){
            $where = ' AND id<>'.$id;
        } else {
            $where = '';
        }

        $is_exists = $this->inDB->rows_count('cms_forum_cats', "seolink='{$seolink}'".$where, 1);

        if ($is_exists) { $seolink .= '-' . $id; }

        return $seolink;

    }

}