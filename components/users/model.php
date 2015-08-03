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

class cms_model_users{

	function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
		$this->inCore = cmsCore::getInstance();
		$this->config = $this->inCore->loadComponentConfig('users');
		cmsCore::loadLanguage('components/users');
        cmsCore::loadClass('form');
    }

/* ========================================================================== */
/* ========================================================================== */

    public static function getDefaultConfig() {

        $cfg = array(
				'sw_comm'=>1,
				'sw_search'=>1,
				'sw_forum'=>1,
				'sw_photo'=>1,
				'sw_wall'=>1,
				'sw_blogs'=>1,
				'sw_clubs'=>1,
				'sw_feed'=>1,
				'sw_awards'=>1,
				'sw_board'=>1,
				'sw_msg'=>1,
				'sw_guest'=>1,
				'sw_gifts'=>1,
				'karmatime'=>1,
				'karmaint'=>'DAY',
				'photosize'=>0,
				'watermark'=>1,
				'smallw'=>64,
				'medw'=>200,
				'medh'=>500,
				'sw_files'=>1,
				'filessize'=>100,
				'users_perpage'=>10,
				'wall_perpage'=>10,
				'filestype'=>'jpeg,gif,png,jpg,bmp,zip,rar,tar',
				'privforms'=>array(),
				'deltime'=>6
        	);

        return $cfg;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'userphoto': $photo = $this->inDB->get_fields('cms_user_photos', "id='{$target_id}'", 'user_id, title');
                              if (!$photo) { return false; }
                              $result['link']  = '/users/'.$photo['user_id'].'/photo'.$target_id.'.html';
                              $result['title'] = $photo['title'];
                              break;

        }

        return ($result ? $result : false);

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
   //
   // этот метод вызывается компонентом comments при создании нового комментария
   //
   // метод должен вернуть 0 или 1
   //
   public function getVisibility($target, $target_id) {

        $is_hidden = 0;

        switch($target){

            case 'userphoto': 	$photo = $this->inDB->get_fields('cms_user_photos', "id='{$target_id}'", 'album_id, allow_who');
								if($photo['allow_who'] != 'all') { $is_hidden = 1; }
								$album = $this->getPhotoAlbum('private', $photo['album_id']);
								if($album['allow_who'] != 'all') { $is_hidden = 1; }
                              	break;

        }

        return $is_hidden;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
	/**
	 * Методы стены
	 */
   public function forWallIsMyProfile($user_id) {

        return $user_id == cmsUser::getInstance()->id;

   }

   public function forWallIsAdmin($user_id) {

        return cmsUser::getInstance()->is_admin;

   }

   public function addWall($item) {

		// проверяем есть ли пользователь, которому добавляем на стену
		$usr = cmsUser::getShortUserData($item['user_id']);
		if (!$usr) { return false; }

		// добавляем запись
		$wall_id = $this->inDB->insert('cms_user_wall', cmsCore::callEvent('ADD_WALL', $item));

		$message = strip_tags($item['content']);
		$message = mb_strlen($message)>100 ? mb_substr($message, 0, 100) : $message;

		if ($item['user_id'] == $item['author_id']){

			cmsActions::log('add_wall_my', array(
					'object' => '',
					'object_url' => '',
					'object_id' => $wall_id,
					'target' => '',
					'target_url' => '',
					'target_id' => 0,
					'description' => $message
			));

		} else {

			cmsActions::log('add_wall', array(
					'object' => $usr['nickname'],
					'object_url' => cmsUser::getProfileURL($usr['login']),
					'object_id' => $wall_id,
					'target' => '',
					'target_url' => '',
					'target_id' => 0,
					'description' => $message
			));

		}

		$usr['email_newmsg'] = $this->inDB->get_field('cms_user_profiles', "user_id='{$item['user_id']}'", 'email_newmsg');

		if ($usr['email_newmsg'] && $item['user_id'] != $item['author_id']){

			global $_LANG;

			$letter = cmsCore::getLanguageTextFile('newwallpost');
			$letter = str_replace('{sitename}', cmsConfig::getConfig('sitename'), $letter);
			$letter = str_replace('{profilelink}', HOST . cmsUser::getProfileURL($usr['login']), $letter);
			$letter = str_replace('{date}', date('d/m/Y H:i:s'), $letter);
			$letter = str_replace('{from}', $item['nickname'], $letter);
			cmsCore::getInstance()->mailText($usr['email'], $_LANG['NEW_POST_ON_WALL'].'! - '.cmsConfig::getConfig('sitename'), $letter);

		}

        return $wall_id;

   }

   public function deleteWallRecord($record_id) {

        $this->inDB->delete('cms_user_wall', "id = '$record_id'", 1);
        cmsCore::deleteUploadImages($record_id, 'wall');

        cmsActions::removeObjectLog('add_wall_my', $record_id);
        cmsActions::removeObjectLog('add_wall', $record_id);

        return true;

   }

// ============================================================================ //
// ============================================================================ //
    public function whereUserGroupIs($group_id) {
        $this->inDB->where("u.group_id = '{$group_id}'");
    }
    public function whereNameIs($name) {
        $this->inDB->where("LOWER(u.nickname) LIKE '%$name%'");
    }

    public function whereCityIs($city) {
        $this->inDB->where("LOWER(p.city) LIKE '%$city%'");
    }

    public function whereHobbyIs($hobby) {
        $this->inDB->where("LOWER(p.description) LIKE '%$hobby%' OR LOWER(p.formsdata) LIKE '%$hobby%'");
    }

    public function whereGenderIs($gender) {
        $this->inDB->where("p.gender = '$gender'");
    }

    public function whereAgeTo($year) {
        $this->inDB->where('DATEDIFF(NOW(), u.birthdate) <= '.($year*365));
    }

    public function whereAgeFrom($year) {
        $this->inDB->where('DATEDIFF(NOW(), u.birthdate) >= '.($year*365));
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function getUsers($is_online = false){

        //подготовим условия
        $r_join = $is_online ? "INNER JOIN cms_online o ON o.user_id = u.id" : '';

        $sql = "SELECT
				u.id,
				u.login,
				u.nickname,
				u.icq,
				u.logdate as flogdate,
				u.rating,
		        u.is_deleted as is_deleted,
                u.birthdate, u.rating,
				u.status as microstatus,
                p.city, p.karma, p.imageurl,
				p.gender as gender

                FROM cms_users u
				INNER JOIN cms_user_profiles p ON p.user_id = u.id
				{$r_join}
                WHERE u.is_locked = 0 AND u.is_deleted = 0
                      {$this->inDB->where}

                {$this->inDB->group_by}

                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

		$users = array();

		while ($user = $this->inDB->fetch_assoc($result)){

			$user['avatar']    = cmsUser::getUserAvatarUrl($user['id'], 'small', $user['imageurl'], $user['is_deleted']);
			$user['user_link'] = cmsUser::getProfileLink($user['login'], $user['nickname']);
			$user['flogdate']  = cmsCore::dateFormat($user['flogdate']);
			$user['is_online'] = $is_online ? true : cmsUser::isOnline($user['id']);

			$users[] = $user;

		}

		return $users;

	}
/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getUsersCount($is_online = false){

        //подготовим условия
        $r_join  = $is_online ? "INNER JOIN cms_online o ON o.user_id = u.id" : '';

        $sql = "SELECT u.id

                FROM cms_users u
				INNER JOIN cms_user_profiles p ON p.user_id = u.id
				{$r_join}
                WHERE u.is_locked = 0 AND u.is_deleted = 0
					  {$this->inDB->where}

                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getUser($login){

		if(is_numeric($login)){
			$where = "u.id = '{$login}'";
		} else {
			$where = "u.login = '{$login}'";
		}

		$sql = "SELECT
				u.*,
                u.status as status_text,
				u.rating as user_rating,
                p.id as pid, p.city, p.description, p.showmail, p.showbirth, p.showicq, p.showphone,
				p.karma, p.imageurl, p.allow_who,
				p.gender as gender,	p.formsdata, p.signature,
				p.email_newmsg, p.cm_subscribe,
				g.title as grp,
				g.alias as group_alias,
				b.user_id as banned,
                IFNULL(ui.login, '') as inv_login,
                IFNULL(ui.nickname, '') as inv_nickname
                FROM cms_users u
				INNER JOIN cms_user_profiles p ON p.user_id = u.id
				INNER JOIN cms_user_groups g ON g.id = u.group_id
				LEFT JOIN cms_banlist b ON b.user_id = u.id AND b.status = 1
                LEFT JOIN cms_users ui ON ui.id = u.invited_by
                WHERE u.is_locked = 0 AND {$where}
                ORDER BY id DESC LIMIT 1";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)){ return false; }

        $user = $this->inDB->fetch_assoc($result);

		global $_LANG;

		$user['avatar'] = cmsUser::getUserAvatarUrl($user['id'], 'big', $user['imageurl'], $user['is_deleted']);
		$user['status_date'] = cmsCore::dateDiffNow($user['status_date']);
		$user['flogdate']    = cmsUser::getOnlineStatus($user['id'], $user['logdate']);
		$user['fregdate']    = cmsCore::dateFormat($user['regdate']);
		$user['fbirthdate']  = cmsCore::dateFormat($user['birthdate']);
		$user['cityurl']     = urlencode($user['city']);
		$user['profile_link'] = HOST . cmsUser::getProfileURL($user['login']);
		$user['fdescription'] = cmsPage::getMetaSearchLink('/users/hobby/', $user['description']);
        $user['formsdata']    = cmsCore::yamlToArray($user['formsdata']);
		if ($user['gender']) {
			switch ($user['gender']){
				case 'm': $user['fgender'] = $_LANG['MALES']; break;
				case 'f': $user['fgender'] = $_LANG['FEMALES']; break;
				default:  $user['fgender'] = '';
			}
		}

        return cmsCore::callEvent('GET_USER', $user);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteUser($user_id, $is_delete = false){

        cmsCore::callEvent('DELETE_USER', $user_id);

        if ($user_id == 1) { return false; }

		if ($is_delete) {

			$avatar = $this->inDB->get_field('cms_user_profiles', "user_id = '$user_id'", 'imageurl');
            if ($avatar && $avatar != 'nopic.jpg'){
                 @unlink(PATH.'/images/users/avatars/'.$avatar);
                 @unlink(PATH.'/images/users/avatars/small/'.$avatar);
            }

			$this->inDB->query("DELETE FROM cms_users WHERE id = '$user_id' LIMIT 1");
			$this->inDB->query("DELETE FROM cms_user_profiles WHERE user_id = '$user_id' LIMIT 1");
			$this->inDB->query("DELETE FROM cms_user_wall WHERE user_id = '$user_id' AND usertype = 'user'");
			$this->inDB->query("DELETE FROM cms_user_friends WHERE to_id = '$user_id' OR from_id = '$user_id'");
			$this->inDB->query("DELETE FROM cms_user_clubs WHERE user_id = '$user_id'");

			cmsCore::loadClass('blog');
			$inBlog = cmsBlogs::getInstance();
			$inBlog->owner = 'user';

			$user_blog = $inBlog->getBlogByUserId($user_id);
			if($user_blog){
				$inBlog->deleteBlog($user_blog['id']);
			}


		} else {
        	$this->inDB->query("UPDATE cms_users SET is_deleted = 1 WHERE id = '$user_id'");
		}

		$this->inDB->query("DELETE FROM cms_user_awards WHERE user_id = '$user_id'");
		$this->inDB->query("DELETE FROM cms_subscribe WHERE user_id = '$user_id'");

		cmsActions::removeUserLog($user_id);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteUsers($id_list){

        foreach($id_list as $key=>$id){
            $this->deleteUser($id);
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteGroup($group_id){

        cmsCore::callEvent('DELETE_USER_GROUP', $group_id);

        $sql = "SELECT id FROM cms_users WHERE group_id = '$group_id'";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)){
            while($user = $this->inDB->fetch_assoc($result)){
                $this->deleteUser($user['id']);
            }
        }

        $this->inDB->query("DELETE FROM cms_user_groups WHERE id = '$group_id'");

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteGroups($id_list){

        foreach($id_list as $key=>$id){
            $this->deleteGroup($id);
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getPluginsOutput($user){

        $inCore       = cmsCore::getInstance();

        $plugins_list = array();

        $plugins      = $inCore->getEventPlugins('USER_PROFILE');

        foreach($plugins as $plugin_name){

            $html   = '';

            $plugin = cmsCore::loadPlugin( $plugin_name );

            if ($plugin!==false){
                $html = $plugin->execute('USER_PROFILE', $user);
            }

            if ($html !== false){

                $p['name']      = $plugin_name;
                $p['title']     = !empty($plugin->info['tab']) ? $plugin->info['tab'] : $plugin->info['title'];
                $p['ajax_link'] = !empty($plugin->info['ajax_link']) ? $plugin->info['ajax_link'] : '';
                $p['html']      = $html;

                $plugins_list[] = $p;

            }

        }

        return $plugins_list;

    }

/* ==================================================================================================== */
/* ===================================          ИНВАЙТЫ           ===================================== */
/* ==================================================================================================== */

    public function addInvite($invite) {

        $sql = "INSERT INTO cms_user_invites (code, owner_id, createdate, is_used, is_sended)
                VALUES ('{$invite['code']}', '{$invite['owner_id']}', NOW(), 0, 0)";

        $this->inDB->query($sql);

        return true;

    }

    public function giveInvites($count, $has_karma, $inv_period=false) {

        if (!$inv_period) { $sql_period = 'DAY'; } else { $sql_period = $inv_period; }

        $sql = "SELECT  u.id as id,
                        IFNULL((u.invdate < DATE_SUB(NOW(), INTERVAL 1 {$sql_period})) OR u.invdate is NULL, 0) as is_time,
                        IFNULL(SUM(k.points), 0) as karma
                FROM cms_users u
                LEFT JOIN cms_user_karma k ON k.user_id = u.id
                WHERE is_deleted = 0
                GROUP BY u.id
                ";

        $res = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($res)) { return false; }

        $given = 0;

        while($user = $this->inDB->fetch_assoc($res)){

            if ($user['karma'] < $has_karma){ continue; }
            if ($inv_period && !$user['is_time']){ continue; }

            for($c=1; $c<=$count; $c++){

                $invite['code']     = md5($user['id'] .'$'. microtime() . '$' . uniqid() . '$' . $c);
                $invite['owner_id'] = $user['id'];

                $this->addInvite($invite);

                $given++;

            }

            $this->inDB->query("UPDATE cms_users SET invdate = NOW() WHERE id = '{$user['id']}'");

        }

        return $given;

    }

    public function giveInvitesCron() {

        $inCore = cmsCore::getInstance();

        $cfg = $inCore->loadComponentConfig('registration');

        if (!isset($cfg['reg_type'])) { $cfg['reg_type'] = 'open'; }
        if (!isset($cfg['inv_count'])) { $cfg['inv_count'] = 5; }
        if (!isset($cfg['inv_karma'])) { $cfg['inv_karma'] = 50; }
        if (!isset($cfg['inv_period'])) { $cfg['inv_period'] = 'WEEK'; }

        if ($cfg['reg_type'] != 'invite') { return false; }

        $this->giveInvites($cfg['inv_count'], $cfg['inv_karma'], $cfg['inv_period']);

        return true;

    }

    public function checkInvite($code) {

        if (!preg_match('/^([a-z0-9]{32})$/ui', $code)) { return false; }

        $correct = $this->inDB->get_field('cms_user_invites', "code='{$code}' AND is_used = 0", 'id');

        return (bool)$correct;

    }

    public function getInviteOwner($code) {

        if (!preg_match('/^([a-z0-9]{32})$/ui', $code)) { return false; }

        $owner_id = $this->inDB->get_field('cms_user_invites', "code='{$code}' AND is_used = 0", 'owner_id');

        return $owner_id;

    }

    public function getInvite($owner_id) {

        $invite = $this->inDB->get_fields('cms_user_invites', "owner_id='{$owner_id}' AND is_used = 0 AND is_sended=0", '*');

        return $invite;

    }

    public function getUserInvitesCount($owner_id) {

        $count = $this->inDB->rows_count('cms_user_invites', "owner_id='{$owner_id}' AND is_used = 0 AND is_sended = 0");

        return $count;

    }

    public function sendInvite($owner_id, $email) {

        $inCore = cmsCore::getInstance();
        $inConf = cmsConfig::getInstance();

        global $_LANG;

        $user = cmsUser::getShortUserData($owner_id);

        if (!$user) { return false; }

        $invite = $this->getInvite($owner_id);

        if (!$invite) { return false; }

        $letter = cmsCore::getLanguageTextFile('invite');
        $letter = str_replace('{sitename}', $inConf->sitename, $letter);
        $letter = str_replace('{site_url}', HOST, $letter);
        $letter = str_replace('{invite_code}', $invite['code'], $letter);
        $letter = str_replace('{username}', $user['nickname'], $letter);

        $inCore->mailText($email, sprintf($_LANG['INVITE_SUBJECT'], $user['nickname']), $letter);

        $this->inDB->query("UPDATE cms_user_invites SET is_sended=1 WHERE id='{$invite['id']}'");

        return true;

    }

    public function closeInvite($code){

        if (!preg_match('/^([a-z0-9]{32})$/ui', $code)) { return false; }

        $this->inDB->query("UPDATE cms_user_invites SET is_used = 1 WHERE code='{$code}'");

        return true;

    }

    public function deleteInvites() {

        $this->inDB->query('DELETE FROM cms_user_invites WHERE is_used = 0');

        return true;

    }

    public function clearInvites() {

        $this->inDB->query('DELETE FROM cms_user_invites WHERE is_used = 1 AND is_sended = 1');

        return true;

    }

/* ==================================================================================================== */
/* ===================================   Пользовательские фото    ===================================== */
/* ==================================================================================================== */

    public function addPhotoAlbum($album) {

        $album = cmsCore::callEvent('ADD_USER_PHOTO_ALBUM', $album);

        if (!$album['allow_who']) { $album['allow_who'] = 'all'; }

        $sql = "INSERT INTO cms_user_albums (user_id, title, pubdate, allow_who, description)
                VALUES ({$album['user_id']}, '{$album['title']}', NOW(), '{$album['allow_who']}', '{$album['description']}')";

        $this->inDB->query($sql);

        $album_id = $this->inDB->get_last_id('cms_user_albums');

        return $album_id;

    }

    public function updatePhotoAlbum($album) {

        if (!$album['allow_who']) { $album['allow_who'] = 'all'; }

		$sql = "UPDATE cms_user_albums
						SET title = '{$album['title']}',
							description = '{$album['description']}',
							allow_who = '{$album['allow_who']}'
						WHERE id = '{$album['id']}'
						LIMIT 1";

        $this->inDB->query($sql);

        return true;

    }

    public function getPhotoAlbum($type, $id) {

        $album = array();

        if ($type == 'private'){
            $album = $this->inDB->get_fields('cms_user_albums', "id='{$id}'", 'id, user_id, title, allow_who, description');
        }

        if ($type == 'public'){
            $album = $this->inDB->get_fields('cms_photo_albums', "id='{$id}'", 'id, user_id, title, NSDiffer');
        }

        return $album ? $album : false;

    }

    public function getPhoto($id) {

        $photo = $this->inDB->get_fields('cms_user_photos', "id='{$id}'", 'id, user_id, title');

        return $photo ? $photo : false;

    }

    public function getUserPhotoCount($user_id) {

        return $this->inDB->rows_count('cms_user_photos', "user_id='{$user_id}'");

    }

    public function getAlbumPhotos($user_id, $album_type, $album_id, $is_friends=false) {

        $inUser     = cmsUser::getInstance();
        $is_my      = $inUser->id == $user_id;
        $is_friends = (int)$is_friends;
        $filter     = '';
        $photos     = array();

        if ($album_type == 'private'){

            if (!$is_my){
                $filter = "AND (
                                    allow_who='all'
                                    OR
                                    (allow_who='registered' AND ({$inUser->id}>0))
                                    OR
                                    (allow_who='friends' AND ({$is_friends}=1))
                                )";
            }

            //Получаем личные фотографии
            $private_sql = "SELECT id, pubdate, imageurl as file, hits, title
                            FROM cms_user_photos
                            WHERE user_id = '{$user_id}' AND album_id = '{$album_id}' $filter
                            ORDER BY id DESC";

            $private_res = $this->inDB->query($private_sql);

            if ($this->inDB->num_rows($private_res)) {
                while($photo = $this->inDB->fetch_assoc($private_res)){
                    $photo['file']  = '/images/users/photos/small/'.$photo['file'];
                    $photo['url']   = '/users/'.$user_id.'/photo'.$photo['id'].'.html';
                    $photo['fpubdate'] = cmsCore::dateFormat($photo['pubdate']);
                    $photos[]       = $photo;
                }
            }

        }

        if ($album_type == 'public'){

            //Получаем фотографии из галереи
            $public_sql = "SELECT f.id, f.pubdate, f.file, f.hits, f.title, f.owner, a.NSDiffer
                            FROM cms_photo_files f
                            INNER JOIN cms_photo_albums a ON a.id = f.album_id AND a.published = 1
                            WHERE f.user_id = '{$user_id}' AND f.album_id = '{$album_id}' AND f.published = 1";

            $public_res = $this->inDB->query($public_sql);

            if ($this->inDB->num_rows($public_res)) {
                while($photo = $this->inDB->fetch_assoc($public_res)){
                    $photo['file']  = '/images/photos/small/'.$photo['file'];
                    $photo['url']   = $photo['NSDiffer'] == '' ? '/photos/photo'.$photo['id'].'.html' : '/clubs/photo'.$photo['id'].'.html';
                    $photo['fpubdate'] = cmsCore::dateFormat($photo['pubdate']);
                    $photos[]       = $photo;
                }
            }

        }

        return $photos;

    }

    public function getPhotoAlbums($user_id, $is_friends=false, $only_private=false) {

        $inUser     = cmsUser::getInstance();
        $is_my      = $inUser->id == $user_id || $inUser->is_admin;
        $is_friends = (int)$is_friends;
        $filter     = '';
        $albums     = array();

        if (!$is_my){
            $filter = "AND (
                                a.allow_who='all'
                                OR
                                (a.allow_who='registered' AND ({$inUser->id}>0))
                                OR
                                (a.allow_who='friends' AND ({$is_friends}=1))
                            )";
        }

        $sql = "SELECT a.id as id,
                       a.title as title,
                       a.pubdate as pubdate,
                       a.allow_who as allow_who,
                       'private' as type,
                       p.imageurl as imageurl,
                       COUNT(p.id) as photos_count
                FROM cms_user_photos p
				INNER JOIN cms_user_albums a ON a.id = p.album_id
                WHERE p.user_id='{$user_id}' {$filter}
                GROUP BY p.album_id";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)) {
            while($album = $this->inDB->fetch_assoc($result)){
                $album['imageurl'] = "/images/users/photos/small/{$album['imageurl']}";
                $album['pubdate']  = cmsCore::dateFormat($album['pubdate']);
                $albums[] = $album;
            }
        }

        if ($only_private){
            $albums = cmsCore::callEvent('GET_USER_ALBUMS', $albums);
            return $albums;
        }

        $sql = "SELECT  a.id as id,
                        a.title as title,
                        a.pubdate as pubdate,
                        'all' as allow_who,
                        'public' as type,
                        f.file as imageurl,
                        COUNT(f.id) as photos_count
                FROM cms_photo_files f
				LEFT JOIN cms_photo_albums a ON a.id = f.album_id
                WHERE f.user_id='{$user_id}' AND f.published = 1
                GROUP BY f.album_id";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)) {
            while($album = $this->inDB->fetch_assoc($result)){
                $album['imageurl'] = "/images/photos/small/{$album['imageurl']}";
                $album['pubdate']  = cmsCore::dateFormat($album['pubdate']);
                $albums[] = $album;
            }
        }

        $albums = cmsCore::callEvent('GET_USER_ALBUMS', $albums);

        return $albums;

    }

    public function addUploadedPhoto($user_id, $photo) {

        $sql = "INSERT INTO cms_user_photos (user_id, album_id, pubdate, title, description, allow_who, hits, imageurl)
                VALUES('{$user_id}', '0', NOW(), '{$photo['filename']}', '', 'none', 0, '{$photo['imageurl']}')";

        $this->inDB->query($sql);

        return true;

    }

    public function getUploadedPhotos($user_id) {

        $photos = array();

        if (cmsUser::sessionGet('photos_list')){
            $sess_ids = 'id IN ('.rtrim(implode(',', cmsUser::sessionGet('photos_list')), ',').')';
        } else {
            $sess_ids = '1=0';
        }

        $sql = "SELECT id, user_id, album_id, title, description, allow_who, imageurl
                FROM cms_user_photos
                WHERE user_id='{$user_id}' AND (album_id = 0 OR ({$sess_ids}))";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)) {
            while($photo = $this->inDB->fetch_assoc($result)){
                $photos[$photo['id']] = $photo;
            }
        }

        $photos = cmsCore::callEvent('GET_USER_UPLOADED_PHOTOS', $photos);

        return $photos ? $photos : false;

    }

    public function deletePhoto($photo_id) {

        cmsCore::loadLib('tags');

        $sql = "SELECT imageurl FROM cms_user_photos WHERE id = '{$photo_id}'";
        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)){
            $photo = $this->inDB->fetch_assoc($result);
            @unlink(PATH.'/images/users/photos/'.$photo['imageurl']);
            @unlink(PATH.'/images/users/photos/small/'.$photo['imageurl']);
            @unlink(PATH.'/images/users/photos/medium/'.$photo['imageurl']);
            $this->inDB->query("DELETE FROM cms_user_photos WHERE id = $photo_id") ;
            cmsCore::deleteComments('userphoto', $photo_id);
			cmsActions::removeObjectLog('add_user_photo', $photo_id);
            cmsClearTags('userphoto', $photo_id);
        }

        return true;

    }

    public function deletePhotoAlbum($user_id, $album_id) {

        $photos = $this->getAlbumPhotos($user_id, 'private', $album_id);

        if ($photos){
            foreach($photos as $photo){
                $this->deletePhoto($photo['id']);
            }
        }

		cmsActions::removeTargetLog('add_user_photo_multi', $album_id);

        $this->inDB->query("DELETE FROM cms_user_albums WHERE id = '$album_id'") ;

        return true;

    }

    public function clearUploadedPhotos() {

        $sql = "SELECT id
                FROM cms_user_photos
                WHERE album_id = 0 OR allow_who = 'none'
                ORDER BY id ASC";

        $result = $this->inDB->query($sql);

        if ($this->inDB->num_rows($result)) {
            while($photo = $this->inDB->fetch_assoc($result)){
                $this->deletePhoto($photo['id']);
            }
        }

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteInactiveUsers() {

		cmsCore::loadClass('actions');

        $users_list = $this->inDB->get_table('cms_users', "DATE_SUB(NOW(), INTERVAL ".$this->config['deltime']." MONTH) > logdate", 'id');

		if(!$users_list) { return false; }

        foreach($users_list as $usr){
            $this->deleteUser($usr['id']);
        }

        return true;

    }

/* ==================================================================================================== */
/* ============================================= Сообщения ============================================ */
/* ==================================================================================================== */

    public function deleteOldNotification() {

		$this->inDB->query("DELETE FROM cms_user_msg WHERE from_id IN (-1, -2) AND is_new =0 AND DATE_SUB(NOW(), INTERVAL 1 MONTH) > senddate");

        return true;

    }

    public function markAsReadMessage($to_id, $limit=15, $is_user=true) {

        $user_where = $is_user ? 'from_id > 0' : 'from_id < 0';

		return $this->inDB->query("UPDATE cms_user_msg SET is_new = 0
                                   WHERE is_new = 1 AND to_id = '{$to_id}' AND {$user_where}
                                   ORDER BY id LIMIT {$limit}");

    }

    public function getReplyMessage($msg_id, $user_id) {

		$sql = "SELECT m.id as id,
					   m.senddate, m.message, u.login, u.nickname
				FROM cms_user_msg m
				LEFT JOIN cms_users u ON u.id = m.from_id
				WHERE m.id = '$msg_id' AND m.to_id = '$user_id'";

		$result = $this->inDB->query($sql);

		if (!$this->inDB->num_rows($result)){ return false; }

		$msg = $this->inDB->fetch_assoc($result);

		$msg['senddate'] = cmsCore::dateFormat($msg['senddate'], true, true);

		return $msg;

    }

	public function getMessages($show_notice = false){

        if($show_notice){ return $this->getNotices(); }

        $sql = "SELECT m.*, u.id as user_id, u.nickname as author,
				u.login as author_login, u.logdate,
				m.from_id as sender_id, u.is_deleted,
				p.imageurl {$this->inDB->select}
                FROM cms_users u
				INNER JOIN cms_user_profiles p ON p.user_id = u.id
				{$this->inDB->join}
                WHERE 1=1
                      {$this->inDB->where}

                {$this->inDB->group_by}

                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

		while ($msg = $this->inDB->fetch_assoc($result)){

			$msg['authorlink'] = cmsUser::getProfileLink($msg['author_login'], $msg['author']);
			$msg['fpubdate'] = cmsCore::dateFormat($msg['senddate'], true, true, true);
			$msg['user_img']  = cmsUser::getUserAvatarUrl($msg['sender_id'], 'small', $msg['imageurl'], $msg['is_deleted']);
			$msg['online_status'] = cmsUser::getOnlineStatus($msg['user_id'], $msg['logdate']);

			$msgs[] = $msg;

		}

		return $msgs;

	}

	private function getNotices(){

		global $_LANG;

        $sql = "SELECT m.*, m.from_id as sender_id {$this->inDB->select}
                FROM cms_user_msg m
                WHERE m.from_id < 0
                {$this->inDB->where}
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

		while ($msg = $this->inDB->fetch_assoc($result)){

            if ($msg['sender_id'] == USER_UPDATER){
                $msg['authorlink'] = $_LANG['SERVICE_UPDATE'];
            }
            if ($msg['sender_id'] == USER_MASSMAIL){
                $msg['authorlink'] = $_LANG['SERVICE_MAILING'];
            }
			$msg['fpubdate'] = cmsCore::dateFormat($msg['senddate'], true, true, true);
			$msg['user_img'] = cmsUser::getUserAvatarUrl($msg['sender_id'], 'small', '', '');

			$msgs[] = $msg;

		}

		return $msgs;

	}

    public function getMessagesCount($show_notice = false){

        if($show_notice){

            return $this->inDB->rows_count('cms_user_msg m', 'm.from_id < 0 '.$this->inDB->where);

        }

        $sql = "SELECT u.id
                FROM cms_users u
				{$this->inDB->join}
                WHERE 1=1
					  {$this->inDB->where}
                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function sendNotificationByEmail($to_id=0, $from_id=0, $msg_id=0) {

		if(!$from_id || !$to_id || !$msg_id) { return false; }

		$inUser = cmsUser::getInstance();
		$inCore = cmsCore::getInstance();
		$inConf = cmsConfig::getInstance();

		global $_LANG;

		//проверяем подписку на уведомления
		$needmail = $this->inDB->get_field('cms_user_profiles', "user_id='{$to_id}'", 'email_newmsg');

		//если подписан и не онлайн, отправляем уведомление на email
		if (!$inUser->isOnline($to_id) && $needmail){

			$postdate   = date('d/m/Y H:i:s');
			$to_email   = $this->inDB->get_field('cms_users', "id='{$to_id}'", 'email');
			$from_nick  = $inUser->nickname;
			$answerlink = HOST.'/users/'.$to_id.'/messages.html';

			$letter = cmsCore::getLanguageTextFile('newmessage');
			$letter = str_replace('{sitename}', $inConf->sitename, $letter);
			$letter = str_replace('{answerlink}', $answerlink, $letter);
			$letter = str_replace('{date}', $postdate, $letter);
			$letter = str_replace('{from}', $from_nick, $letter);
			$inCore->mailText($to_email, $_LANG['YOU_HAVE_NEW_MESS'].'! - '.$inConf->sitename, $letter);

			return true;
		}

        return false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function isUserCanChangeKarma($user_id){

		$inUser = cmsUser::getInstance();

		if($user_id == $inUser->id) { return false; }

		$sql = "SELECT id FROM cms_user_karma WHERE user_id = '$user_id' AND sender_id = '".$inUser->id."' AND senddate >= DATE_SUB(NOW(), INTERVAL ".$this->config['karmatime']." ".$this->config['karmaint'].")";
		$result = $this->inDB->query($sql) ;

		return !$this->inDB->num_rows($result);

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function getUserKarma($user_id){

        $sql = "SELECT k.*, k.points as kpoints, u.nickname, u.login
                     FROM cms_user_karma k
                     LEFT JOIN cms_users u ON u.id = k.sender_id
                     WHERE k.user_id = '{$user_id}'
                     ORDER BY k.senddate DESC
                     LIMIT 50";
        $result = $this->inDB->query($sql);

        $karma = array();

        if ($this->inDB->num_rows($result)){
            while($k = $this->inDB->fetch_assoc($result)){
                $k['fsenddate'] = cmsCore::dateFormat($k['senddate'], true, true);
                $karma[] = $k;
            }
        }

		return $karma;

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function getUserAwards($user_id){

		$sql = "SELECT * FROM cms_user_awards
				WHERE user_id = '$user_id'
				ORDER BY pubdate DESC";

		$result = $this->inDB->query($sql) ;

		if (!$this->inDB->num_rows($result)){ return array(); }

		while ($aw = $this->inDB->fetch_assoc($result)){
			$aw['pubdate'] = cmsCore::dateFormat($aw['pubdate']);
			$aws[] = $aw;
		}

		return $aws;

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function getUserFilesSize($user_id){

		$sql    = "SELECT SUM(filesize) as totalsize FROM cms_user_files WHERE user_id = '$user_id' GROUP BY user_id";
		$result = $this->inDB->query($sql) ;

		if ($this->inDB->num_rows($result)){
			$data = $this->inDB->fetch_assoc($result);
			$size = $data['totalsize'];
		} else {
			$size = 0;
		}

		return $size;

	}

/* ==================================================================================================== */
/* ==================================================================================================== */
	public function getUserFiles($for_all=false){

        if (!$for_all){
            $allowsql = "allow_who='all'";
        } else {
            $allowsql = '1=1';
        }

        $sql = "SELECT *
                FROM cms_user_files
                WHERE {$allowsql}
                {$this->inDB->where}
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if (!$this->inDB->num_rows($result)){ return array(); }

        $rownum = 0;

		while($file = $this->inDB->fetch_assoc($result)){

			$file['pubdate']  = cmsCore::dateFormat($file['pubdate']);
            $file['filelink'] = HOST.'/users/files/download'.$file['id'].'.html';
            $file['fileicon'] = cmsCore::fileIcon($file['filename']);
            $file['mb'] 	  = round(($file['filesize']/1024)/1024, 2); if ($file['mb'] == '0') { $file['mb'] = '~ 0'; }
            $file['rownum']   = $rownum; $rownum++;

			$files[] = $file;
		}

		return $files;

	}

	public function getUserFilesCount($for_all=false){

        if (!$for_all){
            $allowsql = "allow_who='all'";
        } else {
            $allowsql = '1=1';
        }

        $sql = "SELECT 1
                FROM cms_user_files
                WHERE {$allowsql}
                {$this->inDB->where}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

	}

/* ==================================================================================================== */
/* ==================================================================================================== */

}