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

class cms_model_clubs{

	private $club_members_ids;
	public $club_total_members = 1;
	public $last_message = '';
	private $clubs = array();

	public function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('clubs');
		cmsCore::loadLanguage('components/clubs');
		cmsCore::loadLib('karma');
    }

/* ========================================================================== */
    /**
     * Настройки по умолчанию для компонента
     * @return array
     */
    public static function getDefaultConfig() {

        return array (
				  'enabled_blogs' => 1,
				  'enabled_photos' => 1,
				  'thumb1' => 48,
				  'thumb2' => 200,
				  'thumbsqr' => 1,
				  'cancreate' => 1,
				  'perpage' => 10,
				  'club_perpage' => 4,
				  'member_perpage' => 10,
				  'club_album_perpage' => 3,
				  'club_posts_perpage' => 5,
				  'posts_perpage' => 10,
				  'photo_perpage' => 18,
				  'wall_perpage' => 10,
				  'photo_watermark' => 1,
				  'photo_thumb_small' => 96,
				  'photo_thumbsqr' => 1,
				  'photo_thumb_medium' => 450,
				  'photo_maxcols' => 6,
				  'create_min_karma' => 0,
				  'create_min_rating' => 0,
				  'is_saveorig' => 0,
				  'notify_in' => 1,
				  'notify_out' => 1,
				  'every_karma' => 100,
                  'meta_keys'=>'',
                  'meta_desc'=>'',
                  'seo_user_access'=>0
				);

    }

/* ========================================================================== */
   //
   // этот метод вызывается компонентом comments при создании нового комментария
   // метод обновляет количество комментариев для поста и для блога в целом
   //
   public function updateCommentsCount($target, $target_id) {

        if ($target != 'club_post') { return false; }

		cmsCore::loadClass('blog');

        return cmsBlogs::updateCommentsCount($target, $target_id);

    }

/* ========================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'club_photo': $photo = $this->inDB->get_fields('cms_photo_files', "id='{$target_id}'", 'id, title');
                           	   if (!$photo) { return false; }
                               $result['link']  = '/clubs/photo'.$photo['id'].'.html';
                               $result['title'] = $photo['title'];
                               break;

            case 'club_post':  $sql = "SELECT p.title as title,
                                        p.seolink as seolink,
                                        b.user_id as bloglink
                                 FROM cms_blog_posts p
								 LEFT JOIN cms_blogs b ON b.id = p.blog_id
                                 WHERE p.id = '{$target_id}' LIMIT 1";

								$res = $this->inDB->query($sql);
								if (!$this->inDB->num_rows($res)){ return false; }
								$post = $this->inDB->fetch_assoc($res);
								$result['link']  = $this->getPostURL($post['bloglink'], $post['seolink']);
								$result['title'] = $post['title'];
                               break;

        }

        return ($result ? $result : false);

    }

   public function getVisibility($target, $target_id) {

        $is_hidden = 0;

        switch($target){
            case 'club_photo':
						$album_id = $this->inDB->get_field('cms_photo_files', "id='{$target_id}'", 'album_id');
					    $club_id  = $this->inDB->get_field('cms_photo_albums', "id='{$album_id}'", 'user_id');
					    $clubtype = $this->inDB->get_field('cms_clubs', "id='{$club_id}'", 'clubtype');
					    if($clubtype == 'private') { $is_hidden = 1; }
					    break;

            case 'club_post':
						$post = $this->inDB->get_fields('cms_blog_posts', "id='$target_id'", 'blog_id, allow_who, published');
						if($post['allow_who'] != 'all' || !$post['published']) { $is_hidden = 1; }
						$club_id = $this->inDB->get_field('cms_blogs', "id='{$post['blog_id']}'", 'user_id');
					    $clubtype = $this->inDB->get_field('cms_clubs', "id='{$club_id}'", 'clubtype');
					    if($clubtype == 'private') { $is_hidden = 1; }
                        break;

        }

        return $is_hidden;

    }

/* ========================================================================== */

    public function updateRatingHook($target, $item_id, $points) {

        if (!$item_id || abs($points)!=1) { return false; }

        switch($target){
            case 'club_photo':
						$sql = "UPDATE cms_photo_files
								SET rating = rating + ({$points})
								WHERE id = '{$item_id}'";
                         break;

            case 'club_post':
						$sql = "UPDATE cms_blogs b, cms_blog_posts p
							SET b.rating = b.rating + ({$points}), p.rating = p.rating + ({$points})
							WHERE p.blog_id = b.id AND p.id = {$item_id}";
                         break;

        }

        $this->inDB->query($sql);

        return true;

    }

/* ========================================================================== */
    /**
     * Методы стены
     */
   public function forWallIsMyProfile($club_id) {

		$club = $this->getClub($club_id);
		if (!$club) { return false; }

        return $club['admin_id'] == cmsUser::getInstance()->id;

   }

   public function forWallIsAdmin($club_id) {

		$club = $this->getClub($club_id);
		if (!$club) { return false; }

		$inUser = cmsUser::getInstance();

		$this->initClubMembers($club['id']);

		$is_admin = $inUser->is_admin || ($inUser->id == $club['admin_id']);
		$is_moder = $this->checkUserRightsInClub('moderator');

        return ($is_admin || $is_moder);

   }

   public function addWall($item) {

		$club = $this->getClub($item['user_id']);
		if (!$club) { return false; }

		// добавляем запись
		$wall_id = $this->inDB->insert('cms_user_wall', cmsCore::callEvent('ADD_WALL', $item));

        if($club['clubtype']=='private') { return $wall_id; }

		$message = strip_tags($item['content']);
		$message = mb_strlen($message)>100 ? mb_substr($message, 0, 100) : $message;

		//регистрируем событие
		cmsActions::log('add_wall_club', array(
					'object' => $club['title'],
					'object_url' => '/clubs/'.$club['id'],
					'object_id' => $wall_id,
					'target' => '',
					'target_url' => '',
					'target_id' => 0,
					'description' => $message
		));

        return $wall_id;

   }

   public function deleteWallRecord($record_id) {

        $this->inDB->delete('cms_user_wall', "id = '$record_id'", 1);
        cmsCore::deleteUploadImages($record_id, 'wall');

        cmsActions::removeObjectLog('add_wall_club', $record_id);

        return true;

   }

/* ========================================================================== */
    /**
     * Возвращает изображение клуба
     * @return str
     */
    public static function getClubImage($imageurl='') {

		return $imageurl ?
				(file_exists(PATH.'/images/clubs/small/'.$imageurl) ? $imageurl : 'nopic.jpg') :
				'nopic.jpg';

    }

/* ========================================================================== */
    /**
     * Условия выборки
     */
    public function whereAdminIs($user_id){
        $this->inDB->where("c.admin_id = '$user_id'");
        return;
    }
/* ========================================================================== */
    /**
     * Список клубов по критериям
     * @return array
     */
    public function getClubs($only_published=true) {

        $clubs = array();

        $published = $only_published ? 'c.published = 1' : '1=1';

        $sql = "SELECT c.* {$this->inDB->select}
                FROM cms_clubs c
				{$this->inDB->join}
                WHERE {$published}
                      {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

        $result = $this->inDB->query($sql);

        $this->inDB->resetConditions();

        if (!$this->inDB->num_rows($result)) { return $clubs; }

        while ($club = $this->inDB->fetch_assoc($result)){

			$club['imageurl'] = self::getClubImage($club['imageurl']);
			$club['fpubdate'] = cmsCore::dateFormat($club['pubdate'], true, true);
            $clubs[$club['id']] = $club;

        }

        $clubs = cmsCore::callEvent('GET_CLUBS', $clubs);

        return $clubs;

    }

/* ========================================================================== */
    /**
     * Количество клубов по критериям
     * @return int
     */
    public function getClubsCount($only_published=true) {

		$published = $only_published ? 'c.published = 1' : '1=1';

        $sql = "SELECT 1

                FROM cms_clubs c
				{$this->inDB->join}
                WHERE {$published}
                      {$this->inDB->where}
                {$this->inDB->group_by}";

        $result = $this->inDB->query($sql);

        return $this->inDB->num_rows($result);

    }

/* ========================================================================== */
    /**
     * Возвращает клуб и его администратора
     * @return array
     */
    public function getClub($club_id) {

		if(isset($this->clubs[$club_id])){ return $this->clubs[$club_id]; }

		$sql =  "SELECT c.*, c.enabled_blogs as orig_enabled_blogs, c.enabled_photos as orig_enabled_photos, u.nickname as nickname, u.login as login, u.status, u.logdate, p.karma, p.gender as gender, p.imageurl as admin_avatar, u.is_deleted
				FROM cms_clubs c
				LEFT JOIN cms_users u ON u.id = c.admin_id
				LEFT JOIN cms_user_profiles p ON p.user_id = u.id
				WHERE c.id = '$club_id' LIMIT 1";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)){ return false;	}

        $club = $this->inDB->fetch_assoc($result);
		$club['f_imageurl'] = self::getClubImage($club['imageurl']);
		$club['fpubdate']   = cmsCore::dateFormat($club['pubdate'], true, true);
		$club['flogdate']   = cmsUser::getOnlineStatus($club['admin_id'], $club['logdate']);
		$club['admin_avatar'] = cmsUser::getUserAvatarUrl($club['admin_id'], 'small', $club['admin_avatar'], $club['is_deleted']);
		$club['enabled_blogs']	= ($club['enabled_blogs'] == -1) ? $this->config['enabled_blogs'] : $club['enabled_blogs'];
		$club['enabled_photos']	= ($club['enabled_photos'] == -1) ? $this->config['enabled_photos'] : $club['enabled_photos'];

        return $this->clubs[$club_id] = cmsCore::callEvent('GET_CLUB', $club);

    }

/* ========================================================================== */
    /**
     * Получает массив членов клуба
     * @return bool
     */
    public function getClubMembers($club_id, $role='') {

		$club_members = array();

		$role_where = $role ? "AND c.role = '{$role}'" : '';

		$sql = "SELECT c.user_id, c.role, u.nickname, u.login, u.status, u.logdate, p.karma, p.gender, p.imageurl as admin_avatar, u.is_deleted
				FROM cms_user_clubs c
				LEFT JOIN cms_users u ON u.id = c.user_id
				LEFT JOIN cms_user_profiles p ON p.user_id = u.id
				WHERE club_id = '$club_id' $role_where
				ORDER BY c.role DESC, u.logdate DESC\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$rs = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if (!$this->inDB->num_rows($rs)){ return $club_members; }

		while($u = $this->inDB->fetch_assoc($rs)){

			$u['admin_avatar'] = cmsUser::getUserAvatarUrl($u['user_id'], 'small', $u['admin_avatar'], $u['is_deleted']);
			$u['is_online']    = cmsUser::isOnline($u['user_id']);
			$u['logdate']      = cmsCore::dateFormat($u['logdate'], true, true);
			$club_members[] = $u;

		}

        return $club_members;

    }

/* ========================================================================== */
    /**
     * Получает id членов клуба и запоминает их по ролям и общее количество участников
     * @return bool
     */
    public function initClubMembers($club_id) {

		// Если уже получали, возвращаемся
		if(isset($this->club_members_ids)) { return true; }

		$club_members_ids = array();
        $club_members_ids['all'] = array();

		$sql = "SELECT user_id, role
				FROM cms_user_clubs
				WHERE club_id = '$club_id'";

		$rs = $this->inDB->query($sql);

		if ($this->inDB->num_rows($rs)){
			while($u = $this->inDB->fetch_assoc($rs)){

				$club_members_ids[$u['role']][] = $u['user_id'];
				$club_members_ids['all'][] = $u['user_id'];

			}
		}

		// запоминаем массив id участников
		$this->club_members_ids = $club_members_ids;
		// запоминаем общее количество участников
		$this->club_total_members = isset($club_members_ids['all']) ? count($club_members_ids['all']) : 0;
		// запоминаем общее количество модераторов
		$this->club_total_moderators = isset($club_members_ids['moderator']) ? count($club_members_ids['moderator']) : 0;

        return true;

    }

/* ========================================================================== */
    /**
     * Возвращает массив id участников клуба
     * @return array
     */
	public function getClubMembersIds($role=''){
		if(!$role){ return $this->club_members_ids['all']; }
		if(!isset($this->club_members_ids[$role])) { return false; }
		return $this->club_members_ids[$role];
	}

/* ========================================================================== */
    /**
     * Проверяет права пользователя в группе
     * @return bool
     */
	public function checkUserRightsInClub($role='', $user_id=0){

		if(!$user_id) { $user_id = cmsUser::getInstance()->id; }
		if(!$user_id) { return false; }

		if(!$role){ return in_array($user_id, $this->club_members_ids['all']); }

		if(!isset($this->club_members_ids[$role])) { return false; }

		return in_array($user_id, $this->club_members_ids[$role]);

	}

/* ========================================================================== */
    /**
     * Устанавливает рейтинг клубу как сумму рейтингов его участников * 5
     * @return bool
     */
	public function setClubRating($club_id){

		$sql = "SELECT SUM( u.rating ) AS rating
				FROM cms_user_clubs c
				LEFT JOIN cms_users u ON u.id = c.user_id
				WHERE c.club_id = '$club_id'";
		$rs = $this->inDB->query($sql);

		if ($this->inDB->num_rows($rs)){
			$data   = $this->inDB->fetch_assoc($rs);
			$rating = $data['rating'] * 5;
		} else {
			$rating = 0;
		}

		return $this->inDB->query("UPDATE cms_clubs SET rating = $rating WHERE id = '$club_id'");

	}
/* ========================================================================== */
    /**
     * Кеширует количество участников клуба
     * @return bool
     */
	public function setClubMembersCount($club_id){

		$members_count = $this->inDB->rows_count('cms_user_clubs', "club_id = '$club_id'")+1; // учитываем администратора

		return $this->inDB->query("UPDATE cms_clubs SET members_count = {$members_count} WHERE id = '$club_id'");

	}
/* ========================================================================== */
    /**
     * Создает клуб
     * @return int
     */
    public function addClub($item){

		global $_LANG;

        $item = cmsCore::callEvent('ADD_CLUB', $item);

        $club_id = $this->inDB->insert('cms_clubs', $item);
		if(!$club_id) { return false; }

        // Создаем блог клуба
		cmsCore::loadClass('blog');
		$inBlog = cmsBlogs::getInstance();

		$inBlog->addBlog(array('user_id'=>$club_id,
							   'title'=>$_LANG['CLUB_BLOG'].' - '.$item['title'],
							   'allow_who'=>$item['clubtype']=='private' ? 'friends' : 'all',
							   'ownertype'=>'multi',
							   'owner'=>'club'));

		// Создаем корневой фотоальбом
		$this->inDB->addRootNsCategory('cms_photo_albums', 'club'.$club_id,
										array('user_id'=>$club_id,
											  'title'=>$_LANG['CLUB_PHOTOALBUMS'].' - '.$item['title'],
											  'orderform'=>0));

		// Устанавливаем рейтинг
		$this->setClubRating($club_id);

        return $club_id;

    }

/* ========================================================================== */
    /**
     * Возвращает id блога клуба
     * @return int
     */
    public function getClubBlogId($club_id) {

        return $this->inDB->get_field('cms_blogs', "owner='club' AND user_id='$club_id'", 'id');

    }

/* ========================================================================== */
    /**
     * Добавляет участника в клуб
     * @return bool
     */
	public function addUserToClub($club_id, $user_id, $role='member'){
		return $this->inDB->query("INSERT IGNORE INTO cms_user_clubs (user_id, club_id, role) VALUES ('$user_id', '$club_id', '$role')");
	}
    /**
     * Удаляет участника из клуба
     * @return bool
     */
	public function removeUserFromClub($club_id, $user_id){
		return $this->inDB->query("DELETE FROM cms_user_clubs WHERE user_id = '$user_id' AND club_id = '$club_id'");
	}
    /**
     * Сохраняет участников клуба
     * @return bool
     */
	public function clubSaveUsers($club_id, $list){

		if(!is_array($list)){ return false; }

		$inUser = cmsUser::getInstance();
		global $_LANG;

		// Все участники
		$members = $this->getClubMembersIds();

		// Название клуба
		$club_title = $this->inDB->get_field('cms_clubs', "id = '{$club_id}'", 'title');

		// Добавляем новых
		foreach ($list as $user_id){

			if (in_array($user_id, $members)){ continue; }

			$this->addUserToClub($club_id, $user_id);

			//Уведомляем
			if($this->config['notify_in'] && ($user_id != $inUser->id)){
				cmsUser::sendMessage(USER_UPDATER, $user_id, sprintf($_LANG['USER_ADD_YOU'], '<a href="'.cmsUser::getProfileURL($inUser->login).'">'.$inUser->nickname.'</a>', '<a href="'.HOST.'/clubs/'.$club_id.'">'.$club_title.'</a>'));
			}

		}

		// удаляем пользователя
		foreach ($members as $member_id){

			if (in_array($member_id, $list)){ continue; }

			$this->removeUserFromClub($club_id, $member_id);
			// Уведомляем пользователя
			if($this->config['notify_out'] && ($member_id != $inUser->id)){
				cmsUser::sendMessage(USER_UPDATER, $member_id, sprintf($_LANG['USER_DELETE_YOU'], '<a href="'.cmsUser::getProfileURL($inUser->login).'">'.$inUser->nickname.'</a>', '<a href="'.HOST.'/clubs/'.$club_id.'">'.$club_title.'</a>'));
			}

		}

	}

    /**
     * Устанавливает роли участников
     * @return bool
     */
	public function clubSetRole($club_id, $list, $role=''){

		if(!is_array($list)){ return false; }

		// Сбрасываем сначала всем роли
		$sql = "UPDATE cms_user_clubs SET role='member'
				 WHERE club_id = '{$club_id}'";
		$this->inDB->query($sql);

		if(!$list){ return true; }

		$list = rtrim(implode(',', $list), ',');

		$sql = "UPDATE cms_user_clubs SET role='{$role}'
				 WHERE club_id = '{$club_id}' AND user_id IN ({$list})";

		$this->inDB->query($sql);

		return true;

	}

/* ========================================================================== */
    /**
     * Загружает изображение клуба
     * @return str
     */
    public function uploadClubImage($old_file='') {

		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/';
		$inUploadPhoto->dir_medium    = 'clubs/';
		$inUploadPhoto->dir_small     = 'clubs/small/';
		$inUploadPhoto->small_size_w  = $this->config['thumb1'];
		$inUploadPhoto->medium_size_w = $this->config['thumb2'];
		$inUploadPhoto->thumbsqr      = $this->config['thumbsqr'];
		$inUploadPhoto->is_watermark  = false;
		$inUploadPhoto->input_name    = 'picture';

		$file = $inUploadPhoto->uploadPhoto($old_file);

		return $file;

    }

/* ========================================================================== */
    /**
     * Возвращает объект класса для загрузки изображений
     * @return obj
     */
    public function initUploadClass() {

		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/photos/';
		$inUploadPhoto->small_size_w  = $this->config['photo_thumb_small'];
		$inUploadPhoto->medium_size_w = $this->config['photo_thumb_medium'];
		$inUploadPhoto->thumbsqr      = $this->config['photo_thumbsqr'];
		$inUploadPhoto->is_watermark  = $this->config['photo_watermark'];
		$inUploadPhoto->is_saveorig   = $this->config['is_saveorig'];

		return $inUploadPhoto;

    }

/* ========================================================================== */
    /**
     * Сохраняет настройки клуба
     * @return bool
     */
    public function updateClub($club_id, $item){

        global $_LANG;

		$blog_allow_who = $item['clubtype']=='private' ? 'friends' : 'all';
		$blog_title     = $_LANG['CLUB_BLOG'].' - '.$item['title'];

        $sql = "UPDATE cms_blogs SET allow_who = '{$blog_allow_who}', title = '{$blog_title}' WHERE user_id = '{$club_id}' AND owner = 'club' LIMIT 1";

        $this->inDB->query($sql);

        $item = cmsCore::callEvent('UPDATE_CLUB', $item);
        return $this->inDB->update('cms_clubs', $item, $club_id);

    }

/* ========================================================================== */
    /**
     * Управляет меткой vip клуба
     * @return bool
     */
    public function setVip($club_id, $is_vip, $join_cost){

        if (!$is_vip) { $is_vip = 0; }
        if (!$join_cost) { $join_cost = 0; }

        $sql = "UPDATE cms_clubs
                SET is_vip = '{$is_vip}',
                    join_cost = '{$join_cost}'
                WHERE id = $club_id";

        $this->inDB->query($sql);

        return true;

    }

/* ========================================================================== */
    /**
     * Удаляет клуб
     * @return bool
     */
    public function deleteClub($club_id) {

        cmsCore::callEvent('DELETE_CLUB', $club_id);

        $club = $this->getClub($club_id);
        if (!$club){ return false; }

		$inBlog = $this->initBlog();

		cmsCore::loadClass('photo');
		$inPhoto = $this->initPhoto();

        //Удаляем логотип клуба
		if($club['imageurl'] != 'nopic.jpg'){
			@unlink(PATH.'/images/clubs/'.$club['imageurl']);
			@unlink(PATH.'/images/clubs/small/'.$club['imageurl']);
		}

        //Удаляем клуб и привязки пользователей
        $this->inDB->query("DELETE FROM cms_clubs WHERE id = '$club_id'");
        $this->inDB->query("DELETE FROM cms_user_clubs WHERE club_id = '$club_id'");

        //Удаляем блог клуба
        $inBlog->deleteBlog($this->getClubBlogId($club_id));

        //Удаляем фотоальбомы клуба
		$inPhoto->deleteAlbum($this->inDB->getNsRootCatId('cms_photo_albums', 'club'.$club_id), 'club'.$club_id, $this->initUploadClass());
        $this->inDB->query("DELETE FROM cms_photo_albums WHERE NSDiffer = 'club{$club_id}'");

		cmsActions::removeObjectLog('add_club', $club_id);

        return true;

    }

/* ========================================================================== */

    public function hasUserClub(){

		$inUser = cmsUser::getInstance();

		$is_has_club = $inUser->id ? $this->inDB->get_fields('cms_clubs', "admin_id = '{$inUser->id}'", 'id, create_karma', 'id DESC') : true;

        return $is_has_club;

    }

/* ========================================================================== */

    public function canCreate(){

		global $_LANG;

		$inUser = cmsUser::getInstance();

		// неавторизованные не могут создавать клубы
		if (!$inUser->id) { return false; }
		// администраторы могут создавать клубы
		if ($inUser->is_admin) { return true; }

		$is_has_club = $this->hasUserClub();

		// клуб можно создавать каждые $cfg['every_karma']
		if ($is_has_club) {

			if (!$this->config['every_karma']) {

				$this->last_message = $_LANG['USER_HAS_ONE_CLUB'];
				return false;

			}
			if (($is_has_club['create_karma'] + $this->config['every_karma']) > $inUser->karma) {

				$message = $_LANG['USER_HAS_CLUB'];
				$message = str_replace('%create_karma%', $is_has_club['create_karma'], $message);
				$message = str_replace('%every_karma%', $this->config['every_karma'], $message);
				$message = str_replace('%karma%', $inUser->karma, $message);
				$message = str_replace('%new_karma%', ($is_has_club['create_karma']+$this->config['every_karma']), $message);

				$this->last_message = $message;
				return false;

			}

		}

		if($this->config['cancreate'] && $inUser->karma >= $this->config['create_min_karma'] && $inUser->rating >= $this->config['create_min_rating']){

			return true;

		} else {

			if($inUser->karma < $this->config['create_min_karma']){
				$message = $_LANG['NEED_KARMA_TEXT_ACCESS'];
				$message = str_replace('%karma%', $inUser->karma, $message);
				$message = str_replace('%min_karma%', $this->config['create_min_karma'], $message);
				$this->last_message = $message;
			}
			if($inUser->rating < $this->config['create_min_rating']){
				$message = $_LANG['NEED_RATING_TEXT_ACCESS'];
				$message = str_replace('%rating%', $inUser->rating, $message);
				$message = str_replace('%min_rating%', $this->config['create_min_rating'], $message);
				$this->last_message = $message;
			}

		}

        return false;

    }

/* ========================================================================== */
    /**
     * Отрабатывает плагины на событие $plugin_title
     * @return array $plugins_list
     */
    public function getPluginsOutput($item, $plugin_title = 'GET_SINGLE_CLUB'){

        $inCore = cmsCore::getInstance();

        $plugins_list = array();

        $plugins = $inCore->getEventPlugins($plugin_title);

        foreach($plugins as $plugin_name){

            $html   = '';
            $plugin = $inCore->loadPlugin($plugin_name);

            if ($plugin!==false){
                $html = $plugin->execute($plugin_title, $item);
            }

            if ($html){

                $p['name']  = $plugin_name;
                $p['html']  = $html;

                $plugins_list[] = $p;

                unset($plugin);

            }

        }

        return $plugins_list;

    }

/* ========================================================================== */
    /**
     * Производит инициализацию класса блогов
     * @return obj
     */
    public function initBlog(){

		cmsCore::loadClass('blog');
		$inBlog = cmsBlogs::getInstance();
		$inBlog->owner = 'club';
		$inBlog->setTargets(array('tags'=>'blogpost',
							 'rating'=>'club_post',
							 'comments'=>'club_post',
							 'actions_post'=>'add_post_club',
							 'actions_blog'=>'add_blog'));

        return $inBlog;

    }

    /**
     * Производит инициализацию класса фото
     * @return obj
     */
    public function initPhoto(){

		cmsCore::loadClass('photo');
		$inPhoto = cmsPhoto::getInstance();
		$inPhoto->setTargets(array('tags'=>'photo',
							 'rating'=>'club_photo',
							 'comments_photo'=>'club_photo',
							 'comments_album'=>'club_album',
							 'actions_photo'=>'add_photo_club'));

        return $inPhoto;

    }

    /**
     * Возвращает ссылку на пост блога клуба /clubs/ID/blog/seolink.html
     * @return str
     */
    public static function getPostURL($club_id, $seolink){

        return '/clubs/'.$club_id.'_'.$seolink.'.html';

    }

    /**
     * Возвращает ссылку на блога клуба /clubs/ID/blog
     * @return str
     */
    public static function getBlogURL($club_id, $page=1, $cat_id=0){

        $cat_section  = ($cat_id >0 ? '/cat-'.$cat_id   : '');
        $page_section = ($page   >1 ? '/page-'.$page    : '');

        return '/clubs/'.$club_id.'_blog'.$cat_section.$page_section;

    }

/* ========================================================================== */

}