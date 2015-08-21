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

class cmsBlogs {

    private static $instance;
    public $owner;

	private $targets = array('tags'=>'blogpost',
							 'rating'=>'blogpost',
							 'comments'=>'blog',
							 'actions_post'=>'add_post',
							 'actions_blog'=>'add_blog');

// ============================================================================ //
// ============================================================================ //

	private function __construct(){
        $this->inDB = cmsDatabase::getInstance();
		cmsCore::loadLanguage('components/blogs');
		cmsCore::loadLib('tags');
		cmsCore::loadLib('karma');
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Устанавливает таргеты
     * @param array $targets
     * @return bool
     */
    public function setTargets($targets=array()) {
		$this->targets = $targets;
        return true;
    }

    public function getTarget($target='') {
		return isset($this->targets[$target]) ? $this->targets[$target] : '';
    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает категории блога
     * @param int $blog_id
     * @return array $cats
     */
    public function getBlogCats($blog_id){

		$sql = "SELECT cat.*, IFNULL(COUNT(p.id), 0) as post_count
				FROM cms_blog_cats cat
				LEFT JOIN cms_blog_posts p ON p.cat_id = cat.id AND p.published = 1
				WHERE cat.blog_id = '$blog_id'
				GROUP BY cat.id";

		$result = $this->inDB->query($sql);

		if(!$this->inDB->num_rows($result)){ return false; }

        $cats = array();

		while($cat = $this->inDB->fetch_assoc($result)){
			$cats[] = $cat;
		}

        return cmsCore::callEvent('GET_BLOG_CATS', $cats);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает категорию блога
     * @param int $cat_id
     * @return array $cat
     */
    public function getBlogCategory($cat_id){

		$cat = $this->inDB->get_fields('cms_blog_cats', "id = '$cat_id'", '*');

        return $cat ? cmsCore::callEvent('GET_BLOG_CAT', $cat) : false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Добавляет категорию блога
     * @param array $item
     * @return int
     */
    public function addBlogCategory($item){

        return $this->inDB->insert('cms_blog_cats', cmsCore::callEvent('ADD_BLOG_CAT', $item));;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Обновляет данные категории блога
     * @param int $cat_id
     * @param array $item
     * @return bool
     */
    public function updateBlogCategory($cat_id, $item){

        return $this->inDB->update('cms_blog_cats', cmsCore::callEvent('UPDATE_BLOG_CAT', $item), $cat_id);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет категорию
     * @param int $cat_id
     * @return bool
     */
    public function deleteBlogCategory($cat_id){

        cmsCore::callEvent('DELETE_BLOG_CAT', $cat_id);

		$this->inDB->query("UPDATE cms_blog_posts SET cat_id=0 WHERE cat_id = '{$cat_id}'");

        return $this->inDB->delete('cms_blog_cats', "id = '$cat_id'", 1);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает массив авторов блога
     * @param int $blog_id
     * @return array $authors
     */
	public function getBlogAuthors($blog_id){

		$authors = array();

		$rs = $this->inDB->query("SELECT user_id FROM cms_blog_authors WHERE blog_id = '{$blog_id}'");

		if ($this->inDB->num_rows($rs)){
			while ($u = $this->inDB->fetch_assoc($rs)){
				$authors[] = $u['user_id'];
			}
		}

		return cmsCore::callEvent('GET_BLOG_AUTHORS', $authors);

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает массив навигационными постами
     * @param int $post_id
     * @return array $navigation
     */
	public function getPostNavigation($post_id, $blog_id, $ownerModel, $blog_link){

		$blog_where = isset($blog_id) ? "AND blog_id = '$blog_id'" : '';

		$navigation = array();

		$navigation['next'] = $this->inDB->get_fields('cms_blog_posts', "id < '$post_id' {$blog_where}", 'seolink, title', "id DESC");
		if($navigation['next']){
			$navigation['next']['url'] = $ownerModel->getPostURL($blog_link, $navigation['next']['seolink']);
		}

		$navigation['prev'] = $this->inDB->get_fields('cms_blog_posts', "id > '$post_id' {$blog_where}", 'seolink, title', "id ASC");
		if($navigation['prev']){
			$navigation['prev']['url'] = $ownerModel->getPostURL($blog_link, $navigation['prev']['seolink']);
		}

		return $navigation;

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает блог по ссылке или по id
     * @param int or string $id_or_link
     * @return array $blog
     */
    public function getBlog($id_or_link=0){

		if(is_numeric($id_or_link)){
			$where = "id = '$id_or_link'";
		} else {
			$where = "seolink = '$id_or_link'";
		}

		return $this->getBlogItem($where);

    }

    /**
     * Возвращает блог по user_id
     * @param int $user_id
     * @return array $blog
     */
    public function getBlogByUserId($user_id){

		return $this->getBlogItem("user_id = '$user_id'");

    }

    /**
     * Возвращает блог по запросу
     * @param str $where
     * @return array $blog
     */
    private function getBlogItem($where){

		if(isset($this->owner)) { $where .= " AND owner = '{$this->owner}'"; }

		$blog = $this->inDB->get_fields('cms_blogs', $where, '*');
		if(!$blog) { return false; }

		$blog['pubdate'] = cmsCore::dateFormat($blog['pubdate']);

		return cmsCore::callEvent('GET_BLOG', $blog);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает количество блогов по условиям
     * @return int
     */
    public function getBlogsCount(){

		$pub_where = (isset($this->owner) ? "b.owner = '{$this->owner}'" : '1=1');

        $sql = "SELECT 1
                FROM cms_blogs b
				{$this->inDB->join}
                WHERE {$pub_where}
                      {$this->inDB->where}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает блоги
     * @param obj $ownerModel
     * @return array $blogs
     */
    public function getBlogs($ownerModel){

		$pub_where = (isset($this->owner) ? "b.owner = '{$this->owner}'" : '1=1');

        $sql = "SELECT b.*, COUNT(p.id) as records,
                       u.nickname, u.login {$this->inDB->select}
                FROM cms_blogs b
                LEFT JOIN cms_blog_posts p ON p.blog_id = b.id
				LEFT JOIN cms_users u ON u.id = b.user_id
				{$this->inDB->join}
                WHERE {$pub_where}
				      {$this->inDB->where}
                GROUP BY b.id
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

        $blogs = array();

        while($blog = $this->inDB->fetch_assoc($result)){
			$blog['pubdate'] = cmsCore::dateFormat($blog['pubdate']);
			$blog['url']     = $ownerModel->getBlogURL($blog['seolink']);
            $blogs[] = $blog;
        }

        return cmsCore::callEvent('GET_BLOGS', $blogs);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Генерирует ссылку на пост в блоге
     * @param array $post
     * @return str $seolink
     */
    public function generatePostSeoLink($post){

        $seolink = cmsCore::strToURL($post['title']);

        if (@$post['id']){
            $where = ' AND id<>'.$post['id'];
        } else {
            $where = '';
        }

        $is_exists = $this->inDB->rows_count('cms_blog_posts', "seolink='{$seolink}'".$where, 1);

        if ($is_exists) { $seolink .= '-' .(@$post['id'] ? $post['id'] : date("d-i")); }

        return $seolink;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Генерирует ссылку на блог
     * @param array $blog
     * @return str $seolink
     */
    public function generateBlogSeoLink($blog){

        $seolink = cmsCore::strToURL($blog['title']);

        if (@$blog['id']){
            $where = ' AND id<>'.$blog['id'];
        } else {
            $where = '';
        }

        $is_exists = $this->inDB->rows_count('cms_blogs', "seolink='{$seolink}'".$where, 1);
        if ($is_exists) { $seolink .= '-' . (@$blog['id'] ? $blog['id'] : date("d-i")); }

        return $seolink;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает количество постов, ожидающих модерации
     * @param int $blog_id
     * @return int
     */
    public function getModerationCount($blog_id=0){

		$where = $blog_id ? "blog_id = '{$blog_id}'" : '1=1';

        return $this->inDB->rows_count('cms_blog_posts', "{$where} AND published = 0");

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Добавляет блог
     * @param array $item
     * @return int $blog_id
     */
    public function addBlog($item){

        $item['id'] = $this->inDB->insert('cms_blogs', cmsCore::callEvent('ADD_BLOG', $item));

        if ($item['id']){

            $item['seolink'] = $this->generateBlogSeoLink($item);

            $this->inDB->query("UPDATE cms_blogs SET seolink='{$item['seolink']}' WHERE id = '{$item['id']}'");

        }

        return $item['id'] ? $item['id'] : false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Обновляет данные блога
     * @param int $id
     * @param array $item
     * @param bool $update_seo_link
     * @return str $seolink
     */
    public function updateBlog($id, $item, $update_seo_link = false){

        $item['id'] = $id;

		if ($update_seo_link){
        	$item['seolink'] = $this->generateBlogSeoLink($item);
		}

        $item = cmsCore::callEvent('UPDATE_BLOG', $item);

        $this->inDB->update('cms_blogs', $item, $id);

        return @$item['seolink'] ? $item['seolink'] : false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет блог
     * @param int $blog_id
     * @return bool
     */
    public function deleteBlog($blog_id){

        cmsCore::callEvent('DELETE_BLOG', $blog_id);

		// удаляем сам блог
        $this->inDB->query("DELETE FROM cms_blogs WHERE id = '$blog_id'");
		cmsActions::removeObjectLog($this->getTarget('actions_blog'), $blog_id);

		// удаляем посты в нем
        $posts = $this->inDB->get_table('cms_blog_posts', "blog_id = '$blog_id'", 'id');
		if(!$posts) { return true; }

        foreach($posts as $post){
             $this->deletePost($post['id']);
        }

        return true;

    }

    public function deleteBlogs($id_list){

		if(!$id_list || !is_array($id_list)) { return false; }

        foreach($id_list as $key=>$id){
            $this->deleteBlog($id);
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Обновляет авторов блога
     * @param int $id
     * @param array $authors
     * @return bool
     */
    public function updateBlogAuthors($id, $authors){

        //Удаляем прежний набор авторов
        $this->inDB->query("DELETE FROM cms_blog_authors WHERE blog_id = '$id'");

        $authors = cmsCore::callEvent('UPDATE_BLOG_AUTHORS', $authors);

		if($authors){
			foreach ($authors as $key=>$author_id){
				$author_id = (int)$author_id;
				$sql = "INSERT INTO cms_blog_authors (user_id, blog_id, description, startdate)
						VALUES ('$author_id', '$id', '', NOW())";
				$this->inDB->query($sql);
			}
		}

        return true;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Условия выборки
     */
    public function whereOwnerIs($owner){
        $this->inDB->where("b.owner = '$owner'");
        return;
    }

    public function whereNotPublished(){
        $this->inDB->where("p.published = 0");
        return;
    }

    public function whereOnlyPublic(){
        $this->inDB->where("b.allow_who = 'all'");
		$this->inDB->where("p.allow_who = 'all'");
        return;
    }

    public function whereBlogIs($blog_id){
        $this->inDB->where("p.blog_id = '{$blog_id}'");
        return;
    }

    public function whereCatIs($cat_id){
        $this->inDB->where("p.cat_id = '{$cat_id}'");
        return;
    }

    public function whereBlogUserIs($user_id){
        $this->inDB->where("b.user_id = '{$user_id}'");
        return;
    }

    public function whereOwnerTypeIs($ownertype){
        $this->inDB->where("b.ownertype = '{$ownertype}'");
        return;
    }

    public function ratingGreaterThan($rating){
        $this->inDB->where("p.rating > '$rating'");
        return;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает посты по условиям
     * @param bool $show_all
     * @param obj $ownerModel
     * @return array $posts
     */
    public function getPosts($show_all = false, $ownerModel, $is_short = false){

		$pub_where = ($show_all ? '1=1' : 'p.published = 1');

		if(isset($this->owner)) { $pub_where .= " AND b.owner = '{$this->owner}'"; }

        $sql = "SELECT p.*,
                       u.nickname as author, u.login, u.is_deleted,
                       b.allow_who as blog_allow_who,
                       b.seolink as bloglink,
                       b.title as blog_title,
                       b.owner as owner {$this->inDB->select}
                FROM cms_blog_posts p
				INNER JOIN cms_blogs b ON b.id = p.blog_id
				LEFT JOIN cms_users u ON u.id = p.user_id
				{$this->inDB->join}
                WHERE {$pub_where}
				      {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

        $posts = array();

		while($post = $this->inDB->fetch_assoc($result)){
			$post['fpubdate'] = cmsCore::dateFormat($post['pubdate']);
			$post['url']      = $ownerModel->getPostURL($post['bloglink'], $post['seolink']);
			$post['blog_url'] = $ownerModel->getBlogURL($post['bloglink']);
			//Разбиваем текст поста на 2 части по тегу [cut=...] и оставляем только первую из них
			if (mb_strstr($post['content_html'], '[cut')){
				$post['content_html'] = $this->getPostShort($post['content_html'], $post['url']);
			}
			if(!$is_short){
				$post['tagline'] = cmsTagLine($this->getTarget('tags'), $post['id']);
			}
			// если захотим получать аватар владельца
			if(isset($post['imageurl'])){
				$post['author_avatar'] = cmsUser::getUserAvatarUrl($post['user_id'], 'small', $post['imageurl'], $post['is_deleted']);
			}
			$posts[] = $post;
		}

        return cmsCore::callEvent('GET_POSTS', $posts);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает количество постов по условиям
     * @param bool $show_all
     * @return int
     */
    public function getPostsCount($show_all = false){

        $pub_where = ($show_all ? '1=1' : 'p.published = 1');

		if(isset($this->owner)) { $pub_where .= " AND b.owner = '{$this->owner}'"; }

        $sql = "SELECT 1
                FROM cms_blog_posts p
				INNER JOIN cms_blogs b ON b.id = p.blog_id
				{$this->inDB->join}
                WHERE {$pub_where}
                      {$this->inDB->where}
                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getUserBlogId($user_id){

		if(isset($this->owner)) { $where = " AND owner = '{$this->owner}'"; }

        return $this->inDB->get_field('cms_blogs', "user_id = '{$user_id}' {$where}", "id");

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * проверяет, может ли пользователь писать в блог
     * @param array $blog
     * @param int $user_id
     * @return int
     */
    public function isUserBlogWriter($blog, $user_id){

		cmsCore::callEvent('IS_BLOG_WRITER', $blog);

		// в персональные блоги может писать только автор
		if($blog['ownertype']=='single'){ return false; }

		if($blog['ownertype']=='multi' && $blog['forall']) { return true; }

        return $this->inDB->get_field('cms_blog_authors', "blog_id='{$blog['id']}' AND user_id='{$user_id}'", 'id');

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает блог по ссылке или по id
     * @param int or string $id_or_link
     * @return array $blog
     */
    public function getPost($id_or_link=0){

		if(is_numeric($id_or_link)){
			$where = "p.id = '$id_or_link'";
		} else {
			$where = "p.seolink = '$id_or_link'";
		}

		$sql = "SELECT p.*,
					   u.nickname as author_nickname,
					   u.login as author_login,
					   up.imageurl as author_image,
					   u.is_deleted as author_deleted
				FROM cms_blog_posts p
				LEFT JOIN cms_users u ON u.id = p.user_id
				LEFT JOIN cms_user_profiles up ON up.user_id = u.id
				WHERE {$where} LIMIT 1";

		$result = $this->inDB->query($sql);

		if(!$this->inDB->num_rows($result)){ return false; }

		global $_LANG;

		$post = $this->inDB->fetch_assoc($result);

		$post['feditdate'] = cmsCore::dateFormat($post['edit_date']);
		$post['fpubdate']  = cmsCore::dateDiffNow($post['pubdate']).' '.$_LANG['BACK'].' ('.cmsCore::dateFormat($post['pubdate']).')';
		//Убираем тег [cut]
		$post['content_html']  = preg_replace('/\[(cut=)\s*(.*?)\]/ui', '', $post['content_html']);
		$post['author_avatar'] = cmsUser::getUserAvatarUrl($post['user_id'], 'small', $post['author_image'], $post['author_deleted']);

		return cmsCore::callEvent('GET_POST', $post);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Добавляет пост в блоге
     * @param array $item
     * @return array $post_id, $seolink
     */
    public function addPost($item){

        $item = cmsCore::callEvent('ADD_POST', $item);

        //парсим bb-код перед записью в базу
		// Парсим по отдельности части текста, если есть тег [cut
        if (mb_strstr($item['content'], '[cut')){
            $msg_to    = $this->getPostShort($item['content']);
			$msg_to    = cmsCore::parseSmiles($msg_to, true);
			$msg_after = $this->getPostShort($item['content'], false, true);
			$msg_after = cmsCore::parseSmiles($msg_after, true);
			$cut       = $this->getPostCut($item['content']);
			$item['content_html'] = $msg_to . $cut . $msg_after;
        } else {
        	$item['content_html']   = cmsCore::parseSmiles($item['content'], true);
		}
		// Экранируем специальные символы
        $item['content']      = $this->inDB->escape_string($item['content']);
        $item['content_html'] = $this->inDB->escape_string($item['content_html']);

		$post_id = $this->inDB->insert('cms_blog_posts', $item);

		if(!$post_id) { return false; }

        cmsInsertTags($item['tags'], $this->getTarget('tags'), $post_id);

		$item['id']      = $post_id;
		$item['seolink'] = $this->generatePostSeoLink($item);

		$this->inDB->query("UPDATE cms_blog_posts SET seolink='{$item['seolink']}' WHERE id = '{$post_id}'");

		if ($item['published']){
			cmsUser::checkAwards($item['user_id']);
		}

		cmsCore::setIdUploadImage('blog_post', $post_id);

        return array('id'=>$post_id, 'seolink'=>$item['seolink']);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Обновляет пост в блоге
     * @param int $post_id
     * @param array $item
     * @param bool $update_seo_link
     * @return bool
     */
    public function updatePost($post_id, $item, $update_seo_link = false){

        $item['id'] = $post_id;

        $item = cmsCore::callEvent('UPDATE_POST', $item);

		if ($update_seo_link){
        	$item['seolink'] = $this->generatePostSeoLink($item);
		}

        if (mb_strstr($item['content'], '[cut')){
            $msg_to    = $this->getPostShort($item['content']);
			$msg_to    = cmsCore::parseSmiles($msg_to, true);
			$msg_after = $this->getPostShort($item['content'], false, true);
			$msg_after = cmsCore::parseSmiles($msg_after, true);
			$cut       = $this->getPostCut($item['content']);
			$item['content_html'] = $msg_to . $cut . $msg_after;
        } else {
        	$item['content_html'] = cmsCore::parseSmiles($item['content'], true);
		}

        $item['content']      = $this->inDB->escape_string($item['content']);
        $item['content_html'] = $this->inDB->escape_string($item['content_html']);

        $this->inDB->update('cms_blog_posts', $item, $post_id);

        cmsInsertTags($item['tags'], $this->getTarget('tags'), $post_id);

        return isset($item['seolink']) ? $item['seolink'] : true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет пост в блоге
     * @param int $post_id
     * @return bool
     */
    public function deletePost($post_id){

        cmsCore::callEvent('DELETE_POST', $post_id);

		$post = $this->getPost($post_id);
		if (!$post){ return false; }

		// пересчитываем рейтинг блога
		$this->inDB->query("UPDATE cms_blogs SET rating = rating - ({$post['rating']}) WHERE id = '{$post['blog_id']}'");

        $this->inDB->delete('cms_blog_posts', "id = '$post_id'", 1);

        cmsCore::deleteRatings($this->getTarget('rating'), $post_id);
        cmsCore::deleteComments($this->getTarget('comments'), $post_id);

        cmsClearTags($this->getTarget('tags'), $post_id);

        cmsCore::deleteUploadImages($post_id, 'blog_post');
		cmsActions::removeObjectLog($this->getTarget('actions_post'), $post_id);

		return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Публикует пост в блоге
     * @param int $post_id
     * @param int $flag
     * @return bool
     */
    public function publishPost($post_id, $flag=1){

        return $this->inDB->query("UPDATE cms_blog_posts SET published = $flag WHERE id = '$post_id'");

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
   public static function updateCommentsCount($target, $target_id) {

		$inDB = cmsDatabase::getInstance();

		// получаем данные о записи
		$post = $inDB->get_fields('cms_blog_posts', "id='$target_id'", 'id, blog_id');
		if(!$post) { return false; }

		// обновляем количество комментов для поста
		$pc_count = cmsCore::getCommentsCount($target, $post['id']);
		$inDB->query("UPDATE cms_blog_posts SET comments_count = '{$pc_count}' WHERE id = '{$post['id']}'");

		// обновляем общее количество комментов к постам в блоге
		$sql = "SELECT IFNULL(SUM(comments_count), 0) AS blog_comments_count
				FROM cms_blog_posts
				WHERE blog_id = '{$post['blog_id']}' GROUP BY blog_id";

		$result = $inDB->query($sql);

		if ($inDB->num_rows($result)){
			$com = $inDB->fetch_assoc($result);
			$inDB->query("UPDATE cms_blogs SET comments_count = '{$com['blog_comments_count']}' WHERE id = '{$post['blog_id']}'");
		}

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getPostShort($post_content, $post_url = false, $is_after = false){

        $regex      = '/\[(cut=)\s*(.*?)\]/ui';
        $matches    = array();
        preg_match_all( $regex, $post_content, $matches, PREG_SET_ORDER );

        if (is_array($matches)){

            $elm        = $matches[0];
            $elm[0]     = str_replace('[', '', $elm[0]);
            $elm[0]     = str_replace(']', '', $elm[0]);

            mb_parse_str( $elm[0], $args );

            $cut_title  = $args['cut'];

            $pages  = preg_split( $regex, $post_content );

            if ($pages) { $post_content = $is_after ? $pages[1] : $pages[0]; }

			if ($post_url && !$is_after) {
            $post_content .= '<div class="blog_cut_link">
                                    <a href="'.$post_url.'">'.$cut_title.'</a>
                              </div>';
			}

        }

        return $post_content;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getPostCut($post_content){

        $regex      = '/\[(cut=)\s*(.*?)\]/ui';
        $matches    = array();
        preg_match_all( $regex, $post_content, $matches, PREG_SET_ORDER );

        if (is_array($matches)){

            $elm        = $matches[0];
            $elm[0]     = str_replace('[', '', $elm[0]);
            $elm[0]     = str_replace(']', '', $elm[0]);

            mb_parse_str( $elm[0], $args );

			$cut .= '[cut='.$args['cut'].'...]';

        }

        return $cut;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

}
?>
