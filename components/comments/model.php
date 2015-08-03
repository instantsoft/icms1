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

class cms_model_comments{

    private $childs;

	public $is_can_delete    = false;
	public $is_can_moderate  = false;
	public $is_can_bbcode    = false;
	public $is_can_add       = false;
    public $is_add_published = false;


	public function __construct($labels=array()){
        $this->inDB   = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('comments');
		cmsCore::loadLanguage('components/comments');
		$this->labels = array_merge(self::getDefaultLabels(), $labels);
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public static function getDefaultConfig() {

        return array (
            'email' => '',
            'regcap' => 0,
            'subscribe' => 1,
            'min_karma' => 0,
            'min_karma_show' => 0,
            'min_karma_add' => 0,
            'perpage' => 20,
            'cmm_ajax' => 0,
            'cmm_ip' => 1,
            'max_level' => 5,
            'edit_minutes' => 0,
            'watermark' => 0,
            'meta_keys' => '',
            'meta_desc' => ''
        );

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public static function getDefaultLabels() {

		global $_LANG;

        return array('comments' => $_LANG['COMMENTS'], 'add' => $_LANG['ADD_COMM'], 'rss' => $_LANG['RSS_COMM'], 'not_comments' => $_LANG['NOT_COMMENT_TEXT']);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    public function initAccess() {

		$this->is_can_delete        = cmsUser::isUserCan('comments/delete');
        $this->is_can_moderate      = cmsUser::isUserCan('comments/moderate');
        $this->is_can_bbcode        = cmsUser::isUserCan('comments/bbcode');
        $this->is_can_add           = cmsUser::isUserCan('comments/add');
        $this->is_add_published     = cmsUser::isUserCan('comments/add_published');
        $this->target_author_delete = cmsUser::isUserCan('comments/target_author_delete');

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function addComment($item) {

        $item = cmsCore::callEvent('ADD_COMMENT', $item);

		$item['target_title'] = $this->inDB->escape_string($item['target_title']);

        $comment_id = $this->inDB->insert('cms_comments', $item);

		cmsCore::setIdUploadImage('comment', $comment_id);

        return $comment_id;

    }

    public function updateComment($id, $comment) {

        if (!$id) { return false; }

       $this->inDB->update('cms_comments', $comment, $id);

       return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getTargetAuthor($table, $target_id) {

        $sql = "SELECT u.id, u.email
                FROM cms_users u, {$table} p
                WHERE p.user_id = u.id AND p.id = '{$target_id}' AND u.is_locked = 0 AND u.is_deleted = 0
                LIMIT 1";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)!==1){ return false; }

        return $this->inDB->fetch_assoc($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentAuthorId($comment_id) {

        return $this->inDB->get_field('cms_comments', "id='$comment_id'", 'user_id');

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function isAuthorNeedMail($author_id) {

        return $this->inDB->get_field('cms_user_profiles', "user_id='$author_id'", 'email_newmsg');

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    private function getCommentChilds($comment_id) {

        $sql = "SELECT id FROM cms_comments WHERE parent_id = '$comment_id'";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) { return false; }

        while($child = $this->inDB->fetch_assoc($result)){
            $this->childs[] = $child;
            $this->getCommentChilds($child['id']);
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteComment($comment_id) {

        cmsCore::callEvent('DELETE_COMMENT', $comment_id);

        $this->childs = array();

        $this->getCommentChilds($comment_id);

        $sql = "DELETE FROM cms_comments WHERE id = '$comment_id' LIMIT 1";
        $this->inDB->query($sql);
		cmsCore::deleteRatings('comment', $comment_id);
        cmsActions::removeObjectLog('add_comment', $comment_id);
		cmsCore::deleteUploadImages($comment_id, 'comment');

        if ($this->childs){
            foreach($this->childs as $child){
				cmsCore::callEvent('DELETE_COMMENT', $child['id']);
                $sql = "DELETE FROM cms_comments WHERE id = '{$child['id']}' LIMIT 1";
                $this->inDB->query($sql);
				cmsCore::deleteRatings('comment', $child['id']);
                cmsActions::removeObjectLog('add_comment', $child['id']);
				cmsCore::deleteUploadImages($child['id'], 'comment');
            }
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function isEditable($pubdate) {

		if(cmsUser::getInstance()->is_admin) { return true; }

		if(!$this->config['edit_minutes']) { return false; }

        $now      = time();
        $date     = strtotime($pubdate);
        $diff_sec = $now - $date;
		$end_min  = $this->config['edit_minutes'] - round($diff_sec/60);

        return $end_min > 0 ? $end_min : false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Условия выборки
     */
    public function whereUserIs($user_id) {
        $this->inDB->where("c.user_id = '{$user_id}'");
    }

    public function whereOnlyUsers() {
        $this->inDB->where("c.user_id > 0");
    }

    public function whereRatingOver($rating) {
        $this->inDB->where("c.rating >= {$rating}");
    }

    public function whereTargetIs($target, $target_id) {
        $this->inDB->where("c.target='$target' AND c.target_id = '$target_id'");
    }

    public function whereTargetIn($targets) {
        $t_list = '';
		foreach($targets as $t){
			$t_list .= "'$t',";
        }
		$t_list = rtrim($t_list, ',');
		$this->inDB->where("c.target IN ({$t_list})");
    }

    public function whereIsShow() {
        $this->inDB->where("c.is_hidden=0");
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    /**
     * Получаем комментарии по заданным параметрам
     * @return array
     */
    public function getComments($only_published=true, $is_tree=false, $from_module = false) {

		$inUser = cmsUser::getInstance();

        $comments = array();

		global $_LANG;

        $published = $only_published ? 'c.published = 1' : '1=1';

        $sql = "SELECT c.*,
					   IFNULL(u.nickname, 0) as nickname,
					   IFNULL(u.login, 0) as login,
					   IFNULL(u.is_deleted, 0) as is_deleted,
					   IFNULL(p.imageurl, 0) as imageurl,
					   IFNULL(p.gender, 0) as gender
                FROM cms_comments c
				LEFT JOIN cms_users u ON u.id = c.user_id
				LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE {$published}
					{$this->inDB->where}

                {$this->inDB->group_by}

                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

        $result = $this->inDB->query($sql);

        $this->inDB->resetConditions();

        if (!$this->inDB->num_rows($result)) { return array(); }

        while($comment = $this->inDB->fetch_assoc($result)){

			$comment['level'] = 0;
			$comment['is_editable'] = $this->isEditable($comment['pubdate']);
			$comment['fpubdate']    = cmsCore::dateFormat($comment['pubdate'], true, true);

			if ($comment['guestname']){
				$comment['author']     = $comment['guestname'];
				$comment['is_profile'] = false;
				$comment['ip']  	   = in_array($this->config['cmm_ip'], array(1,2)) ? $comment['ip'] : '';
			} else {
				$comment['author']['nickname'] = $comment['nickname'];
				$comment['author']['login']    = $comment['login'];
				$comment['is_profile'] 	= true;
				$comment['user_image'] 	= cmsUser::getUserAvatarUrl($comment['user_id'], 'small', $comment['imageurl'], $comment['is_deleted']);
				$comment['ip']  		= ($this->config['cmm_ip'] == 2 && $comment['ip']) ? $comment['ip'] : '';
			}

			switch ($comment['gender']){
					case 'm': 	$comment['gender'] = $_LANG['COMMENTS_MALE'];
								break;
					case 'f':	$comment['gender'] = $_LANG['COMMENTS_FEMALE'];
								break;
					default:	$comment['gender'] = $_LANG['COMMENTS_GENDER'];
			}

			$comment['show']  = (!$this->config['min_karma'] || $comment['rating'] >= $this->config['min_karma_show']) || cmsUser::userIsAdmin($comment['user_id']);
			$comment['is_my'] = ($inUser->id == $comment['user_id']);
			if ($inUser->id){
				$comment['is_voted'] = $comment['is_my'] ? true : cmsUser::isRateUser('comment', $inUser->id, $comment['id']);
			} else {
				$comment['is_voted'] = true;
			}

            $comments[] = $comment;

        }

		if($is_tree){
			$comments = $this->buildTree(0, 0, $comments);
		}

        return $from_module ? cmsCore::callEvent('GET_COMMENTS_MODULE', $comments) : cmsCore::callEvent('GET_COMMENTS', $comments);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает количество комментариев по заданным параметрам
     * @return int
     */
    public function getCommentsCount($only_published=true) {

		$published = $only_published ? 'c.published = 1' : '1=1';

        $sql = "SELECT 1
                FROM cms_comments c
				LEFT JOIN cms_users u ON u.id = c.user_id
                WHERE {$published}
                      {$this->inDB->where}
                {$this->inDB->group_by}";

        $result = $this->inDB->query($sql);

        return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    public function getComment($id) {

        $sql = "SELECT c.*,
					   IFNULL(u.nickname, 0) as nickname,
					   IFNULL(u.login, 0) as login,
					   IFNULL(u.is_deleted, 0) as is_deleted,
					   IFNULL(p.imageurl, 0) as imageurl
                FROM cms_comments c
				LEFT JOIN cms_users u ON u.id = c.user_id
				LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE c.id='{$id}'
                LIMIT 1";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) { return false; }

        $comment = $this->inDB->fetch_assoc($result);

		$comment['is_editable'] = $this->isEditable($comment['pubdate']);

        return cmsCore::callEvent('GET_COMMENT', $comment);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    private function buildTree($parent_id, $level, $comments, $tree=array()){
        $level++;
        foreach($comments as $comment){
            if ($comment['parent_id']==$parent_id){
                $comment['level'] = $level-1;
                $tree[] = $comment;
                $tree = $this->buildTree($comment['id'], $level, $comments, $tree);
            }
        }
		return $tree;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

}