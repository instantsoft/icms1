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
define('USER_UPDATER', -1);
define('USER_MASSMAIL', -2);
define('ONLINE_INTERVAL', 3); // интервал в минутах, когда пользователь считается online

class cmsUser {

    const PROFILE_LINK_PREFIX  = 'users/';
    const MAXIMUM_TOKENS_COUNT = 30;

    private static $csrf_token = '';
    private $friends = array();
    private $new_msg = array();
    private $geo_is_loaded = false;

    private static $instance;

    private static $guest_group_info = array();
    private static $cache = array();

    private $loads_users = array();

    public $id = 0;
    public $is_admin = 0;

    public $online_users;
    public $online_users_ids;

    private function __construct() { }

    private function __clone() {}

// ============================================================================ //
// ============================================================================ //

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Обновляет данные пользователя, если он не забанен
     * заполняя ими свойства объекта
     * @return bool
     */
    public function update() {

        // привязка ip адреса к сессии
        if(!$this->checkSpoofingSession()){
            $this->logout();
            cmsCore::redirectBack();
        }

        $user_id = (int)(isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0);

		// Свойства для гостя
        if (!$user_id){

            self::setUserLogdate();

			$guest_info = self::getGuestInfo();

			foreach($guest_info as $key=>$value){
				$this->{$key} = $value;
			}

        } else {

            // свойства для авторизованного пользователя
            $info = $this->loadUser($user_id);
            if (!$info){ return false; }

            foreach($info as $key=>$value){
                $this->{$key} = $value;
            }

            $this->new_msg       = self::getNewMessages($user_id);
            $this->new_msg_count = $this->new_msg['total'];

            $this->logdate = self::getUserLogdate();

        }

		// проверяем бан
        $this->checkBan();

        return true;

    }

    /**
     * Загружает в свойства данные местоположения пользователя
     */
    public function loadUserGeo() {

        if($this->geo_is_loaded) { return true; }

        $inCore = cmsCore::getInstance();

        $geo_cfg = $inCore->loadComponentConfig('geo');

        if($geo_cfg['component_enabled'] &&
                $geo_cfg['autodetect'] &&
                cmsCore::loadClass($geo_cfg['class']) &&
                class_exists('cms'.$geo_cfg['class'])){

            $geo_data = call_user_func(array('cms'.$geo_cfg['class'], 'getInfo'), $this->ip);
            if($geo_data){

                foreach($geo_data as $k=>$v){
                    $this->geo[$k] = $v;
                }
                // если пользователь город не указал или это гость
                if(!$this->city){
                    $this->city = isset($this->geo['city']) ? $this->geo['city'] : '';
                }

                $this->geo_is_loaded = true;

                return true;

            }
        }

        return false;

    }

// ============================================================================ //
// ============================================================================ //

    private function checkSpoofingSession() {

        // первый раз зашли
        if(!isset($_SESSION['user_net'])) {
            $octets = explode('.', $_SERVER['REMOTE_ADDR']);
            $_SESSION['user_net'] = rtrim($_SERVER['REMOTE_ADDR'], end($octets));
            return true;
        }

		return mb_strstr($_SERVER['REMOTE_ADDR'], $_SESSION['user_net']);

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Загружает данные пользователя из базы
     * @param int $user_id
     * @return array
     */
    public function loadUser($user_id, $where='') {

		if($user_id){
			if(isset($this->loads_users[$user_id])) { return $this->loads_users[$user_id]; }
		}

		$where = $user_id ? "u.id = '$user_id'" : $where;
		if(!$where) { return false; }

        $inDB = cmsDatabase::getInstance();

        $sql  = "SELECT u.*, g.is_admin, g.alias, g.access, p.imageurl, p.imageurl as orig_imageurl, p.karma, p.city
                   FROM cms_users u
				   INNER JOIN cms_user_groups g ON g.id = u.group_id
				   INNER JOIN cms_user_profiles p ON p.user_id = u.id
                   WHERE {$where} AND u.is_deleted = 0 AND u.is_locked = 0 LIMIT 1";

        $result = $inDB->query($sql);

        if($inDB->num_rows($result) !== 1) { return false; }

        $info = $inDB->fetch_assoc($result);

        $info['ip'] = cmsCore::strClear($_SERVER['REMOTE_ADDR']);

		$info['imageurl'] = self::getUserAvatarUrl($info['id'], 'small', $info['imageurl'], $info['is_deleted']);

		$info['access'] = explode(',', str_replace(', ', ',', $info['access']));

        return $this->loads_users[$info['id']] = cmsCore::callEvent('LOAD_USER', $info);

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Проверяет, находится ли текущий посетитель в бан-листе
     * Если да, то показывает сообщение и завершает работу
     */
    private function checkBan(){

        $inDB = cmsDatabase::getInstance();

        $user_where = $this->id ? "(ip = '{$this->ip}' OR user_id = '{$this->id}')" : "ip = '{$this->ip}'";

		// Проверяем бан
		$ban = $inDB->get_fields('cms_banlist', $user_where.' AND status=1', 'int_num, int_period, autodelete, id, status, bandate, user_id, cause');
		if(!$ban) { return; }

        if($this->id){
            $inDB->query("UPDATE cms_banlist SET ip = '{$this->ip}' WHERE user_id = '{$this->id}'");
        }

		$interval = $ban['int_num'] . ' ' .$ban['int_period'];

		// проверяем истек ли срок бана
		if ($inDB->rows_count('cms_banlist', "id = '{$ban['id']}' AND bandate <= DATE_SUB(NOW(), INTERVAL $interval) AND int_num > 0")){
			// если истек и флаг автоудаления есть, удаляем
			if ($ban['autodelete']){
				$inDB->query("DELETE FROM cms_banlist WHERE id='{$ban['id']}'");
			} else {
				$inDB->query("UPDATE cms_banlist SET status=0 WHERE id='{$ban['id']}'");
			}

		} else {
			global $_LANG;
			$ban['bandate'] = cmsCore::dateFormat($ban['bandate']);
			$ban['enddate'] = cmsCore::spellCount($ban['int_num'], $_LANG[$ban['int_period'].'1'], $_LANG[$ban['int_period'].'2'], $_LANG[$ban['int_period'].'10']);
			cmsPage::includeTemplateFile('special/bantext.php', array('ban' => $ban));
            $this->logout();
			cmsCore::halt();
		}

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет наличие кукиса "запомнить меня" и если он найден - авторизует пользователя
     * @return bool
     */
    public function autoLogin(){

        $user_id = (int)(isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0);

        if (cmsCore::getCookie('userid') && !$user_id){

            $cookie_code = (string)cmsCore::getCookie('userid');

            if (!preg_match('/^[0-9a-f]{32}$/i', $cookie_code)){ return false; }

            $user = $this->loadUser(0, "md5(CONCAT(u.id, u.password,'".cmsDatabase::getInstance()->escape_string(PATH)."')) = '$cookie_code'");

            if($user){

                $_SESSION['user'] = $user;
                cmsCore::callEvent('USER_LOGIN', $_SESSION['user']);
                self::setUserLogdate($user['id']);

            } else {
                cmsCore::unsetCookie('user_id');
            }

        }

        return true;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает url аватара пользователя
     * @param int $user_id
     * @param str $size
     * @param str $file_name
     * @param int $usr_is_deleted
     * @return str
     */
	public static function getUserAvatarUrl($user_id, $size='small', $file_name='', $usr_is_deleted=0) {

		// службы обновлений и рассылки
		if ($user_id == -1) { return '/images/messages/update.jpg';	}
		if ($user_id == -2) { return '/images/messages/massmail.jpg'; }

		// пользователь без аватара
		if (!$file_name || !file_exists(PATH.'/images/users/avatars/'.$file_name)){

			if ($size == 'small'){
				return '/images/users/avatars/small/nopic.jpg';
			} else {
				return '/images/users/avatars/nopic.jpg';
			}

		}

		if($usr_is_deleted){

			if ($size == 'small'){
				return '/images/users/avatars/small/noprofile.jpg';
			} else {
				return '/images/users/avatars/noprofile.jpg';
			}

		} else {

			if ($size == 'small'){
				return '/images/users/avatars/small/'.$file_name;
			} else {
				return '/images/users/avatars/'.$file_name;
			}

		}

	}

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает некешированный рейтинг пользователя
     * @param int $user_id ID пользователя
     * @return int
     */
    public static function getRating($user_id) {

        $inDB = cmsDatabase::getInstance();

		$rating = 0;

		$targets = $inDB->get_table('cms_rating_targets', 'is_user_affect = 1 ORDER BY user_weight', 'target, user_weight, target_table');
		if(!$targets) { return $rating; }

        $start_sql = "SELECT SUM( r.total_rating ) AS rating FROM cms_ratings_total r \n";

		foreach($targets as $target){

			$sql = "INNER JOIN {$target['target_table']} {$target['target']} ON
					 r.item_id = {$target['target']}.id AND
					 r.target = '{$target['target']}' AND
					 {$target['target']}.user_id = '{$user_id}' \n";

			$result  = $inDB->query($start_sql . $sql);
			$data    = $inDB->fetch_assoc($result);
			$rating += (int)@$data['rating'] * (int)$target['user_weight'];

		}

        return $rating;

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает некешированное значение кармы пользователя
     * @param int $user_id
     * @return int
     */
    public static function getKarma($user_id){

        $inDB = cmsDatabase::getInstance();

        $sql = "SELECT SUM(points) as karma FROM cms_user_karma WHERE user_id = '$user_id' GROUP BY user_id";
        $result = $inDB->query($sql);

		if (!$inDB->num_rows($result)){ return 0; }

        $data = $inDB->fetch_assoc($result);

        return (int)$data['karma'];

    }

    /**
     * Изменяет карму пользователя
     * @param int $to_user_id Кому изменяем карму
     * @param int $points На сколько
     * @param int $from_user_id Кто изменяет карму
     * @return int
     */
	public static function changeKarmaUser($to_user_id, $points, $from_user_id=0){

		$inDB = cmsDatabase::getInstance();

		$from_user_id = $from_user_id ? $from_user_id : self::getInstance()->id;

		$inDB->query("INSERT INTO cms_user_karma (user_id, sender_id, points, senddate) VALUES ('$to_user_id', '$from_user_id', '$points', NOW())");

		$user_karma = self::getKarma($to_user_id);

		$inDB->query("UPDATE cms_user_profiles SET karma = '$user_karma' WHERE user_id = '$to_user_id'");

		self::checkAwards($to_user_id);

		return $user_karma;

	}

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает ссылки на профили именниников
     * @return html
     */
    public static function getBirthdayUsers() {

        $inDB = cmsDatabase::getInstance();

		$today = date("d-m");

        $sql = "SELECT u.id as id, u.nickname as nickname, u.login as login, u.birthdate, p.gender as gender
                FROM cms_users u
				LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE u.is_locked = 0 AND u.is_deleted = 0 AND p.showbirth = 1 AND DATE_FORMAT(u.birthdate, '%d-%m')='$today'";

        $rs     = $inDB->query($sql);
        $total  = $inDB->num_rows($rs);

        $now=0; $html = '';

        if (!$total){ return false; }

        while($usr = $inDB->fetch_assoc($rs)){
            $html .= self::getGenderLink($usr['id'], $usr['nickname'], $usr['gender'], $usr['login']);
            if ($now < $total-1) { $html .= ', '; }
            $now ++;
        }

        return $html;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает элементы <option> для списка пользователей
     * @param int $selected
     * @param array $exclude
     * @return html
     */
    public static function getUsersList($selected=0, $exclude=array()){

        $inDB   = cmsDatabase::getInstance();

        $html   = '';

        $sql    = "SELECT id, nickname FROM cms_users WHERE is_locked = 0 AND is_deleted = 0 ORDER BY nickname";
        $rs     = $inDB->query($sql);

        if (!$inDB->num_rows($rs)){ return; }

        while($u = $inDB->fetch_assoc($rs)){
            if(!in_array($u['id'], $exclude)){
                if ($selected){
                    if ($u['id'] == $selected){
                        $html .= '<option value="'.$u['id'].'" selected="selected">'.$u['nickname'].'</option>';
                    } else {
                        $html .= '<option value="'.$u['id'].'">'.$u['nickname'].'</option>';
                    }
                } else {
                    $html .= '<option value="'.$u['id'].'">'.$u['nickname'].'</option>';
                }
            }
        }

        return $html;

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает элементы <option> для списка пользователей
     * @param int $selected
     * @param array $exclude
     * @return html
     */
    public static function getAuthorsList($authors, $selected=''){

        if (!$authors) { return; }

        $inDB = cmsDatabase::getInstance();
        $html = '';

        $sql = "SELECT id, nickname FROM cms_users WHERE ";

		$a_list = implode(',', $authors);

        if ($a_list){
            $sql .= "id IN ({$a_list})";
        } else {
            $sql .= '1=0';
        }

        $rs = $inDB->query($sql);

        if ($inDB->num_rows($rs)){
            while($u = $inDB->fetch_assoc($rs)){
                if ($selected){
                    if (in_array($u['id'], $selected)){
                        $html .= '<option value="'.$u['id'].'" selected="selected">'.$u['nickname'].'</option>';
                    } else {
                        $html .= '<option value="'.$u['id'].'">'.$u['nickname'].'</option>';
                    }
                } else {
                    $html .= '<option value="'.$u['id'].'">'.$u['nickname'].'</option>';
                }
            }
        }

        return $html;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив картинок наград
     * @param str $dir
     * @return array
     */
	public static function getAwardsImages($dir = '/images/users/awards'){

		$images = array();

		if ($handle = opendir(PATH.$dir)) {

			while (false !== ($file = readdir($handle))) {

				if ($file != '.' && $file != '..' && mb_strstr($file, '.gif')){

					$tag = str_replace('.gif', '', $file);

					$images[] = htmlspecialchars($tag.'.gif');

				}

			}

			closedir($handle);

		}

		return $images;

	}

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает элементы <option> для списка друзей пользователя
     * @param int $user_id
     * @param int $selected
     * @return html
     */
    public static function getFriendsList($user_id, $selected=0){

        $inDB = cmsDatabase::getInstance();

        $html = '';

		$sql = "SELECT
				CASE
				WHEN f.from_id = $user_id
				THEN f.to_id
				WHEN f.to_id = $user_id
				THEN f.from_id
				END AS id, u.nickname as nickname
                FROM cms_user_friends f
				LEFT JOIN cms_users u ON u.id = CASE WHEN f.from_id = $user_id THEN f.to_id WHEN f.to_id = $user_id THEN f.from_id END
				WHERE (from_id = $user_id OR to_id = $user_id) AND is_accepted =1";

        $result = $inDB->query($sql);

        if ($inDB->num_rows($result)){

            while($friend = $inDB->fetch_assoc($result)){

                if (@$selected==$friend['id']){
                    $s = 'selected';
                } else {
                    $s = '';
                }

                $html .= '<option value="'.$friend['id'].'" '.$s.'>'.$friend['nickname'].'</option>';
            }
        } else {
            $html = '<option value="0" selected>-- Нет друзей --</option>';
        }
        return $html;

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Проверяет дружбу текущего пользователя с $user_id
     * @return bool
     */
    public static function isFriend($user_id=0){

		if (!$user_id) { return false; }

		$my_id = self::getInstance()->id;
		if (!$my_id) { return false; }

		$my_friends = cmsUser::getFriends($my_id);
		if(!$my_friends) { return false; }

		$is_friend = false;

		foreach($my_friends as $friend){

			if($friend['id'] == $user_id){
			   $is_friend = true;
			   break;
			}else{
			   $is_friend = false;
			}

		}

		return $is_friend;

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Создает запрос на добавление текущего пользователя в друзья к $user_id
     * @param int $user_id
     * @return bool
     */
    public static function addFriend($user_id=0){

		if (!$user_id) { return false; }

        cmsCore::callEvent('ADD_FRIEND', $user_id);

		$my_id = self::getInstance()->id;
		if (!$my_id) { return false; }

        return cmsDatabase::getInstance()->insert('cms_user_friends', array('to_id'=>$user_id,
                                                                            'from_id'=>$my_id,
                                                                            'logdate'=>date('Y-m-d H:i:s'),
                                                                            'is_accepted'=>0));

    }
/* ========================================================================== */
/* ========================================================================== */
    /**
     * Удаляет пользователя $user_id из списка друзей текущего пользователя
     * @param int $user_id
     * @return bool
     */
    public static function deleteFriend($user_id=0){

		if (!$user_id) { return false; }

        $friend_field_id = self::getFriendFieldId($user_id);

        if($friend_field_id){

            cmsCore::callEvent('DELETE_FRIEND', $user_id);

			cmsDatabase::getInstance()->query("DELETE FROM cms_user_friends WHERE id = '{$friend_field_id}'");

			cmsActions::removeObjectLog('add_friend', $friend_field_id);
			cmsUser::clearSessionFriends();

            return true;

        }

		return false;

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Возвращает ID записи, где указана ваша дружба с $user_id
     * @param int $user_id
     * @param mixed $acepted Возможные значения: false, 0, 1
     * @return bool
     */
    public static function getFriendFieldId($user_id=0, $accepted=false, $to_from=false){

		if (!$user_id) { return false; }

		$my_id = self::getInstance()->id;
		if (!$my_id) { return false; }

        if($to_from === false){

            $where = "to_id IN ($user_id, $my_id) AND from_id IN ($my_id, $user_id)";

        } else {

            // Если ко мне в друзья просились
            if($to_from == 'to_me'){

                $where = "to_id = '{$my_id}' AND from_id = '{$user_id}'";

            } else { // я просился в друзья

                $where = "to_id = '{$user_id}' AND from_id = '{$my_id}'";

            }

        }

        if($accepted !== false){
            $where .= $accepted ? 'AND is_accepted = 1' : 'AND is_accepted = 0';
        }

		return cmsDatabase::getInstance()->get_field('cms_user_friends', $where, 'id');

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Возвращает количество друзей пользователя
     * @param int $user_id
     * @return int
     */
    public static function getFriendsCount($user_id=0){

		if (!$user_id) { return 0; }

		return cmsDatabase::getInstance()->rows_count('cms_user_friends', "(from_id = '$user_id' OR to_id = '$user_id') AND is_accepted =1");

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает список друзей пользователя
	 * и помещает в текущую сессию
     * @param int $user_id
     * @return array
     */
    public static function getFriends($user_id=0){

		if(!$user_id) { return array(); }

        $friends = array();

		// уже полученных друзей отдаем сразу
		if(isset(self::getInstance()->friends[$user_id])){
			return self::getInstance()->friends[$user_id];
		}

        $is_me = (@$_SESSION['user']['id'] == $user_id);

		//Если список уже в сессии, возвращаем
		if ($is_me && self::sessionGet('friends') !== false) {

            foreach (self::sessionGet('friends') as $key=>$friend) {
                $friend['flogdate'] = self::getOnlineStatus($friend['id'], $friend['logdate']);
                $friends[$key] = $friend;
            }
            return $friends;

        }

		//иначе получаем список из базы, кладем в сессию и возвращаем
        $inDB = cmsDatabase::getInstance();

		$sql = "SELECT
				CASE
				WHEN f.from_id = $user_id
				THEN f.to_id
				WHEN f.to_id = $user_id
				THEN f.from_id
				END AS id, u.nickname as nickname, u.login as login, u.is_deleted, u.status, u.logdate, p.imageurl
                FROM cms_user_friends f
				LEFT JOIN cms_users u ON u.id = CASE WHEN f.from_id = $user_id THEN f.to_id WHEN f.to_id = $user_id THEN f.from_id END
				INNER JOIN cms_user_profiles p ON p.user_id = u.id
				WHERE (from_id = $user_id OR to_id = $user_id) AND is_accepted =1 ORDER BY u.logdate DESC";

        $result = $inDB->query($sql);

        if ($inDB->num_rows($result)){
            while($friend = $inDB->fetch_assoc($result)){
				$friend['avatar']    = self::getUserAvatarUrl($friend['id'], 'small', $friend['imageurl'], $friend['is_deleted']);
				$friend['is_online'] = self::isOnline($friend['id']);
				$friend['flogdate']  = self::getOnlineStatus($friend['id'], $friend['logdate']);
				$friends[$friend['id']] = $friend;
            }
        }

		// своих друзей кладем в сессию
		if ($is_me) { self::sessionPut('friends', $friends); }

		// Запоминаем список друзей пользователя
		self::getInstance()->friends[$user_id] = $friends;

        return $friends;

    }

// ============================================================================ //
// ============================================================================ //
    /*
     * Очищает список друзей в сессии
     */
    public static function clearSessionFriends(){
		self::sessionDel('friends');
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает html стены пользователя
     * @param int $selected
     * @param array $exclude
     * @return html
     */
    public static function getUserWall($target_id, $component='users', $my_profile=0, $is_admin=0){

        $inDB   = cmsDatabase::getInstance();
        $inUser = self::getInstance();

		cmsCore::loadLanguage('components/'.$component);

        if(!$my_profile && !$is_admin) { $my_profile = $inUser->is_admin; }

        $records = array();

        //получаем общее число записей на стене этого пользователя
        $total = $inDB->rows_count('cms_user_wall', "user_id = '$target_id' AND usertype = '$component'");

        if ($total){

            $sql = "SELECT w.*, g.gender, g.imageurl, u.nickname as author, u.login as author_login, u.is_deleted, w.pubdate
                    FROM cms_user_wall w
					INNER JOIN cms_users u ON u.id = w.author_id
					INNER JOIN cms_user_profiles g ON g.user_id = u.id
                    WHERE w.user_id = '$target_id' AND w.usertype = '$component'
                    ORDER BY w.pubdate DESC\n";
			if ($inDB->limit){
				$sql .= "LIMIT {$inDB->limit}";
			}

            $result = $inDB->query($sql);
			$inDB->resetConditions();

            while($record = $inDB->fetch_assoc($result)){
                $record['is_today'] = time() - strtotime($record['pubdate']) < 86400;
				$record['fpubdate'] = $record['is_today'] ? cmsCore::dateDiffNow($record['pubdate']) : cmsCore::dateFormat($record['pubdate']);
                $record['avatar']   = cmsUser::getUserAvatarUrl($record['author_id'], 'small', $record['imageurl'], $record['is_deleted']);
                $records[]          = $record;
            }

            $records = cmsCore::callEvent('GET_WALL_POSTS', $records);

        }

        ob_start();

        cmsPage::initTemplate('components', 'com_users_wall')->
                assign('records', $records)->
                assign('user_id', $inUser->id)->
                assign('target_id', $target_id)->
                assign('my_profile', $my_profile)->
                assign('is_admin', $is_admin)->
                assign('component', $component)->
                assign('total', $total)->
                assign('pagebar', cmsPage::getPagebar($total, $inDB->page, $inDB->perpage, 'javascript:wallPage(%page%)'))->
                display('com_users_wall.tpl');

        return ob_get_clean();

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Проверяет доступ авторизованного пользователя к чему-либо
     * @param str $allow_who
     * @return bool
     */
	public static function checkUserContentAccess($allow_who, $user_id){

		$access = false;

        $inUser = self::getInstance();

		// автору показываем всегда
		if ($inUser->id == $user_id) { return true; }

		// администраторам показываем всегда
		if ($inUser->is_admin) { return true; }

		switch ($allow_who){
			case 'all':        	$access = true; break;
			case 'registered': 	$access = $inUser->id ? true : false; break;
			case 'nobody':      $access = $inUser->id == $user_id ? true : false; break;
			case 'friends':		$access = $inUser->isFriend($user_id); break;
			default: $access = false;
		}

		return $access;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет, голосовал ли пользователь за указанную цель
     * @return int
     */
	public static function isRateUser($target='', $user_id=0, $item_id=0){
		// если на входе одноги из параметров нет, считаем, что пользователь голосовал
		if(!$target || !$user_id || !$item_id) { return true; }
		// если для этого запроса есть кеш, возвращаем значение из кеша
		if(isset(self::$cache[$target][$item_id][$user_id])){ return self::$cache[$target][$item_id][$user_id]; }
		$is_rate = cmsDatabase::getInstance()->rows_count('cms_ratings', "item_id = '$item_id' AND target = '$target' AND user_id = '$user_id'");
		// возвращаем и кешируем значение
		return self::$cache[$target][$item_id][$user_id] = $is_rate;
	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив информации для пользователя "Гость"
     * @return array
     */
    public static function getGuestInfo($field = ''){

        if (!self::$guest_group_info){

			$data = cmsDatabase::getInstance()->get_fields('cms_user_groups', "alias = 'guest'", 'id, access');

            if ($data){

				$data['group_id'] = $data['id'];
				$data['access']   = explode(',', str_replace(', ', ',', $data['access']));
				$data['id']       = 0;
				$data['ip']       = cmsCore::strClear($_SERVER['REMOTE_ADDR']);
				$data['is_admin'] = 0;
				$data['karma']    = -1000000;
                $data['logdate']  = self::getUserLogdate();
                $data['city']     = '';
                self::$guest_group_info = cmsCore::callEvent('GET_GUEST', $data);

            } else {
                self::$guest_group_info = array();
            }

        }

		// если запрашивали конкретное что то, возвращаем если есть
		// иначе возвращаем массив
		if($field){
        	return isset(self::$guest_group_info[$field]) ? self::$guest_group_info[$field] : false;
		} else {
			return self::$guest_group_info;
		}

    }
// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает дату последнего посещения пользователя
     * @return str
     */
    public static function getUserLogdate(){

        // получаем значение из сессии
        $ses_logdate = self::sessionGet('logdate');
        // если есть, возвращаем
        if($ses_logdate) { return date('Y-m-d H:i:s', $ses_logdate); }
        // Получаем время визита из куков
        $cookie_logdate = (int)cmsCore::getCookie('logdate');
        // если есть, возвращаем
        if($cookie_logdate) { return date('Y-m-d H:i:s', $cookie_logdate); }
        // Иначе возвращаем текущую дату
        return date('Y-m-d H:i:s');

    }
    /**
     * Устанавливает дату последнего посещения пользователя
     * @return str
     */
    public static function setUserLogdate($user_id = 0){

        $ses_logdate = self::sessionGet('logdate');
        // Если нет в сессии, кладем в сессию предыдущее значение
        // из куков, если там тоже нет, то текущее
        if(!$ses_logdate){
            $cookie_logdate = (int)cmsCore::getCookie('logdate');
            self::sessionPut('logdate', ($cookie_logdate ? $cookie_logdate : time()));
            cmsCore::setCookie('logdate', time(), time()+60*60*24*30);
        }

        if($user_id){
            cmsDatabase::getInstance()->query("UPDATE cms_users SET logdate = CURRENT_TIMESTAMP WHERE id = '$user_id'");
        }

        return true;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Обновляет статистику посещений сайта
     */
    public function onlineStats(){

        // бот пришел или куки отключены, статистику не обновляем
        if(empty($_COOKIE)){ return; }

        //если учет отключен
        if(!cmsConfig::getConfig('user_stats')){
            return;
        }

        //собираем информацию о текущем пользователе
        $sess_id   = session_id();
        $useragent = cmsCore::strClear((string)$_SERVER['HTTP_USER_AGENT']);
        $page      = cmsCore::isAjax() ? '' : cmsCore::strClear((string)$_SERVER['REQUEST_URI']);

        cmsDatabase::getInstance()->
                query("INSERT IGNORE INTO cms_online (ip, sess_id, user_id) VALUES ('{$this->ip}', '$sess_id', '{$this->id}') ON DUPLICATE KEY UPDATE agent = '$useragent', viewurl = '$page'");

        // удаляем старые записи
        if(cmsConfig::getConfig('user_stats') == 1){
            self::clearOnlineUsers();
        }

        // загружаем всех пользователей, кто онлайн
        $this->loadOnlineUsers();

    }
    /**
     * Удаляет просроченные данные об online пользователях
     */
    public static function clearOnlineUsers() {
        return cmsDatabase::getInstance()->
                query("DELETE FROM cms_online WHERE lastdate <= DATE_SUB(NOW(), INTERVAL ".ONLINE_INTERVAL." MINUTE) LIMIT 5");
    }
    /**
     * Загружает всех пользователей и гостей кто онлайн
     */
    private function loadOnlineUsers() {
        if(!isset($this->online_users)){
            $this->online_users = cmsDatabase::getInstance()->get_table('cms_online');
        }
    }
    /**
     * Возвращает массив с количеством гостей и пользователей онлайн
     * @return array
     */
    public static function getOnlineCount(){

        $ou = self::getInstance()->online_users;

		$guests = 0;
		$online = array();

        foreach ($ou as $o) {
			if ($o['user_id'] == 0 || $o['user_id'] == ''){
				$guests++;
			} else {
				$online[$o['user_id']][] = $o;
			}
        }

		$people['guests'] = $guests;
		$people['users']  = sizeof($online);

        return $people;

    }
    /**
     * Возвращает отформатированную дату последнего визита
     * @param int $user_id ID пользователя
     * @param str $logdate Дата последнего визита
     * @return str
     */
    public static function getOnlineStatus($user_id, $logdate=''){

		global $_LANG;

		if (self::isOnline($user_id)){

			$status = '<span class="online">'.$_LANG['ONLINE'].'</span>';

		} else {

			if ($logdate){

				$status = '<span class="logdate">'.cmsCore::dateDiffNow($logdate).' '.$_LANG['BACK'].'</span>';

			} else {
				$status = '<span class="offline">'.$_LANG['OFFLINE'].'</span>';
			}

		}

        return $status;

    }
    /**
     * Проверяет, на сайте ли указанный пользователь
     * @param int $user_id
     * @return bool
     */
    public static function isOnline($user_id){

		if($user_id<=0) { return false; }

		$inUser = self::getInstance();

		$online_users = array();

		if(!isset($inUser->online_users_ids)){

            if($inUser->online_users){
                foreach ($inUser->online_users as $data) {
                if($data['user_id']){
                    $online_users[] = $data['user_id'];
                }
            }
        }

			$inUser->online_users_ids = $online_users;

		} else {
			$online_users = $inUser->online_users_ids;
		}

        return in_array($user_id, $online_users);

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает массив количества новых сообщений и уведомлений
     * @param int $user_id
     * @return array
     */
    public static function getNewMessages($user_id){

        $inDB   = cmsDatabase::getInstance();

        $sql    = "SELECT from_id FROM cms_user_msg WHERE to_id = '$user_id' AND to_del = 0 AND is_new = 1";
        $result = $inDB->query($sql);

		$messages = 0;
		$notices  = 0;

        while($o = $inDB->fetch_assoc($result)){
			if ($o['from_id'] < 0){
				$notices++;
			} else {
				$messages++;
			}
        }

		$counts['messages'] = $messages;
		$counts['notices']  = $notices;
		$counts['total']    = $notices+$messages;

		return $counts;

    }
    public function getNewMsg() {
        return $this->new_msg;
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает список наград пользователя
     * @param int $user_id
     * @return array
     */
    public static function getAwardsList($user_id){

        if(!$user_id){ return array(); }

        if(isset(self::$cache['users_awards'][$user_id])) { return self::$cache['users_awards'][$user_id]; }

        $aw = cmsDatabase::getInstance()->get_table('cms_user_awards', "user_id = '$user_id'", '*');

        return self::$cache['users_awards'][$user_id] = $aw;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает предустановленные награды
     * @return array
     */
    public static function getAutoAwards(){

        return cmsDatabase::getInstance()->get_table('cms_user_autoawards', "published = 1 ORDER BY title", '*');

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Проверяет условия получения наград и выдает награду пользователю, если нужно
     * @param int $user_id
     * @return bool
     */
    public static function checkAwards($user_id=0){

		if (!$user_id){ return false; }

        $inDB = cmsDatabase::getInstance();

		$awards = self::getAutoAwards();
        if (!$awards){ return false; }

		$p_content   = $inDB->rows_count('cms_content', "user_id='$user_id' AND published = 1");
		$p_comment   = $inDB->rows_count('cms_comments', "user_id='$user_id' AND published = 1");
		$p_blog      = $inDB->rows_count('cms_blog_posts', "user_id='$user_id' AND published = 1");
		$p_forum     = $inDB->rows_count('cms_forum_posts', "user_id='$user_id'");
		$p_photo     = $inDB->rows_count('cms_photo_files', "user_id='$user_id' AND published = 1");
		$p_privphoto = $inDB->rows_count('cms_user_photos', "user_id='$user_id'");
		$p_karma     = $inDB->get_field('cms_user_profiles', "user_id='$user_id'", 'karma');

		foreach ($awards as $award) {

			if ($inDB->rows_count('cms_user_awards', "user_id = '$user_id' AND award_id = '{$award['id']}'")) { continue; }

			$granted = ($award['p_content'] <= $p_content) &&
					   ($award['p_comment'] <= $p_comment) &&
					   ($award['p_blog'] <= $p_blog) &&
					   ($award['p_forum'] <= $p_forum) &&
					   ($award['p_photo'] <= $p_photo) &&
					   ($award['p_privphoto'] <= $p_privphoto) &&
					   ($award['p_karma'] <= $p_karma);

			if (!$granted){ continue; }

			self::giveAward($award, $user_id);

		}

        return true;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Выдает награду
     * @param array $award
     * @param int $user_id
     * @return int $award_id
     */
    public static function giveAward($award, $user_id){

		if(!$award || !$user_id) { return false; }

        global $_LANG;

        $inDB = cmsDatabase::getInstance();

		$user = self::getShortUserData($user_id);
        if(!$user){ return false; }

        if(!file_exists(PATH.'/images/users/awards/'.$award['imageurl'])){ return false; }

        $award = $inDB->escape_string($award);

		$sql = "INSERT INTO cms_user_awards (user_id, pubdate, title, description, imageurl, from_id, award_id)
				VALUES ('$user_id', NOW(), '{$award['title']}', '{$award['description']}', '{$award['imageurl']}', '{$award['from_id']}', '{$award['id']}')";
		$inDB->query($sql);
		$award_id = $inDB->get_last_id('cms_user_awards');

        if(!$award_id){ return false; }

		cmsActions::log('add_award', array(
				'object' => '"'.$award['title'].'"',
				'user_id' => $user_id,
				'object_url' => '',
				'object_id' => $award['id'],
				'target' => '',
				'target_url' => '',
				'target_id' => 0,
				'description' => '<img src="/images/users/awards/'.$award['imageurl'].'" border="0" alt="'.htmlspecialchars($award['description']).'">'
		));
		self::sendMessage(USER_UPDATER, $user_id, '<b>'.$_LANG['RECEIVED_AWARD'].':</b> <a href="'.cmsUser::getProfileURL($user['login']).'#upr_awards">'.$award['title'].'</a>');

        return cmsCore::callEvent('GIVE_AWARD', $award_id);

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Отправляет личное сообщение пользователю
     * @param int $sender_id
     * @param int $receiver_id
     * @param string $message
     * @return bool
     */
    public static function sendMessage($sender_id, $receiver_id, $message){

        $inDB = cmsDatabase::getInstance();

        $message = $inDB->escape_string($message);

        $sql = "INSERT INTO cms_user_msg (to_id, from_id, senddate, is_new, message)
                VALUES ('$receiver_id', '$sender_id', NOW(), 1, '$message')";
        $inDB->query($sql);

        $msg_id = $inDB->get_last_id('cms_user_msg');

        return $msg_id ? $msg_id : false;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Отправляет личное сообщение списку пользователей
     * @param int $sender_id
     * @param array $receiver_ids
     * @param string $message
     * @return int
     */
    public static function sendMessages($sender_id, $receiver_ids, $message){

		if(!is_array($receiver_ids) || !$receiver_ids) { return false; }

		$msg = array();

        foreach ($receiver_ids as $receiver_id){
            $msg[] = self::sendMessage($sender_id, $receiver_id, $message);
        }

        return count($msg); // возвращаем количество отправленных сообщений

    }

    /**
     * Отправляет личное сообщение группе(ам) пользователей
     * @param int $sender_id
     * @param array or int $group
     * @param string $message
     * @return int
     */
    public static function sendMessageToGroup($sender_id, $group, $message){

		// если отсылаем нескольким группам
		if(is_array($group)){

			$count = 0;

			foreach ($group as $group_id){
				$count += self::sendMessageToGroup($sender_id, $group_id, $message);
			}

		} else { // отсылаем одной группе

			// получаем участников групппы
			$user_list = self::getGroupMembers($group);
			if(!$user_list) { return false; }
			// получаем id пользователей
			$user_ids  = array_keys($user_list);

			return self::sendMessages($sender_id, $user_ids, $message);

		}

        return $count;

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Проверяет подписан ли пользователь на обновления контента
     * @param int $user_id
     * @param string $target
     * @param int $target_id
     * @return bool
     */
    public static function isSubscribed($user_id, $target, $target_id){
        if(!$user_id){ return false; }
        return (bool)cmsDatabase::getInstance()->rows_count('cms_subscribe', "user_id = '$user_id' AND target = '$target' AND target_id = '$target_id'");
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Добавляет/удаляет подписку пользователя на обновления контента
     * @param int $user_id
     * @param string $target
     * @param int $target_id
     * @param bool $subscribe
     * @return bool
     */
    public static function subscribe($user_id, $target, $target_id, $subscribe=true){
        $inDB = cmsDatabase::getInstance();
        if ($subscribe){
            if (!$inDB->rows_count('cms_subscribe', "user_id = '$user_id' AND target = '$target' AND target_id = '$target_id'")){
                $sql = "INSERT INTO cms_subscribe (user_id, target, target_id, pubdate)
                        VALUES ('{$user_id}', '{$target}', '{$target_id}', NOW())";
                $inDB->query($sql) ;
            }
        } else {
            $sql = "DELETE FROM cms_subscribe WHERE user_id = '$user_id' AND target = '$target' AND target_id = '$target_id'";
            $inDB->query($sql) ;
        }
        return true;
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Рассылает личные уведомления для подписанных на что-либо пользователей
     * @param string $target Назначение
     * @param int $target_id ID назначения
     * @param array $item Массив того, о чем уведомляем - ключи: array('link','title','author', 'letter_file');
     * @return bool
     */
    public static function sendUpdateNotify($target, $target_id, $item=array()){

        if(!$item){ return false; }

        global $_LANG;

        $inUser = self::getInstance();
        $inDB   = cmsDatabase::getInstance();

        //получаем список подписанных пользователей
        $sql = "SELECT u.id, u.email, p.cm_subscribe as subscribe_type
                FROM cms_subscribe s
                INNER JOIN cms_users u ON u.id = s.user_id
                INNER JOIN cms_user_profiles p ON p.user_id = u.id
                WHERE s.target = '{$target}' AND s.target_id = '{$target_id}'";
        $r = $inDB->query($sql);
        if (!$inDB->num_rows($r)){ return false; }

        $letter = cmsCore::getLanguageTextFile($item['letter_file']);
        if(!$letter){ return false; }

        while ($user = $inDB->fetch_assoc($r)){

            if ($user['id'] == $inUser->id) { continue; }

            if ($user['subscribe_type']=='priv' || $user['subscribe_type']=='both'){
                $message = $_LANG['WAS_UPDATED'].' <a href="'.$item['link'].'">'.$item['title'].'</a>';
                self::sendMessage(USER_UPDATER, $user['id'], $message);
            }

            if ($user['subscribe_type']=='mail' || $user['subscribe_type']=='both'){
                $user_letter = str_replace('{sitename}', cmsConfig::getConfig('sitename'), $letter);
                $user_letter = str_replace('{answerlink}', HOST.$item['link'], $user_letter);
                $user_letter = str_replace('{pagetitle}', $item['title'], $user_letter);
                $user_letter = str_replace('{date}', date('d/m/Y H:i:s'), $user_letter);
                $user_letter = str_replace('{author}', $item['author'], $user_letter);
                cmsCore::mailText($user['email'], '', $user_letter);
                unset($user_letter);
            }

        }

        return true;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает тег ссылки на профиль пользователя с иконкой его пола
     * @param int $user_id
     * @param string $nickname
     * @param char $gender m / f
     * @param string $login
     * @param string $css_style
     * @return html
     */
    public static function getGenderLink($user_id, $nickname='', $gender='', $login='', $css_style=''){

        $inDB = cmsDatabase::getInstance();

        if (!$gender){
            $gender = $inDB->get_field('cms_user_profiles', "user_id = '$user_id'", 'gender');
        }

        if (!$nickname || !$login){
            $user = $inDB->get_fields('cms_users', "id = '$user_id'", 'nickname, login');
            $nickname   = $user['nickname'];
            $login      = $user['login'];
        }

        return '<a href="'.cmsUser::getProfileURL($login).'" class="user_gender_'.$gender.'" style="'.$css_style.'">'.$nickname.'</a>';

    }

// ============================================================================ //
// ============================================================================ //

    public static function getProfileURL($user_login) {
        if(!$user_login){
            global $_LANG;
            return 'javascript:core.alert(\''.$_LANG['USER_IS_DELETE'].'\',\''.$_LANG['ATTENTION'].'\');';
        }
        return '/' . self::PROFILE_LINK_PREFIX . urlencode($user_login);
    }

// ============================================================================ //
// ============================================================================ //

    public static function getProfileLink($user_login, $user_nickname) {
        if(!$user_login){
            global $_LANG;
            return $_LANG['USER_IS_DELETE'];
        }
        return '<a href="'.self::getProfileURL($user_login).'" title="'.htmlspecialchars($user_nickname).'">'.$user_nickname.'</a>';
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Запоминает текущий URI в сессии и перенаправляет пользователя на форму логина
     */
    public static function goToLogin(){

		self::sessionPut('auth_back_url', cmsCore::strClear($_SERVER['REQUEST_URI']));

        cmsCore::redirect('/login');

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Сохраняет переменную в сессии
     * @param str $param Название переменной
     * @param mixed $value Значение
     * @return bool
     */
    public static function sessionPut($param, $value, $box = 'icms'){
        $_SESSION[$box][$param] = $value;
        return $value;
    }

    /**
     * Извлекает переменную из сессии
     * @param str $param Название переменной
     * @return bool
     */
    public static function sessionGet($param, $box = 'icms'){
        if (isset($_SESSION[$box][$param])){
            return $_SESSION[$box][$param];
        } else {
            return false;
        }
    }

    /**
     * Удаляет переменную из сессии
     * @param str $param Название переменной
     */
    public static function sessionDel($param, $box = 'icms'){
        unset($_SESSION[$box][$param]);
    }

    /**
     * Очищает весь массив icms сессии
     */
    public static function sessionClearAll($box = 'icms'){
        unset($_SESSION[$box]);
    }
// ============================================================================ //
// ============================================================================ //
    /**
     * Формирует и возвращает csrf токен
     */
	public static function getCsrfToken(){

        if(!self::$csrf_token){

            $token = md5(uniqid().microtime());

            $tokens = self::sessionGet('csrf_tokens', 'security');
            if(is_array($tokens)){
                $count_tokens = array_push($tokens, $token);
                if($count_tokens > self::MAXIMUM_TOKENS_COUNT){
                    array_shift($tokens);
                }
            } else {
                $tokens = array($token);
            }

            self::sessionPut('csrf_tokens', $tokens, 'security');

            self::$csrf_token = $token;
        }

		return self::$csrf_token;

	}
    /**
     * Проверяет csrf токен
     * Рекомендуется проверять токен в самую последнюю очередь,
     * после всех проверок входных данных
     */
	public static function checkCsrfToken(){

        if(isset($_POST['csrf_token'])) {

            $tokens = self::sessionGet('csrf_tokens', 'security');

            if(is_array($tokens)){

                $key = array_search($_POST['csrf_token'], $tokens, true);

                if($key !== false){
                    unset($tokens[$key]);
                    ksort($tokens);
                    self::sessionPut('csrf_tokens', $tokens, 'security');
                    return true;
                }

            }

        }

        return false;

	}
    /**
     * ====== DEPRECATED =========
     */
	public static function clearCsrfToken(){
		return true;
	}
// ============================================================================ //
// ============================================================================ //

    /**
     * Находит в строке все выжения вида {user.property} и заменяет property
     * на соответствующее свойство объекта cmsUser
     * @param string $string
     * @return string
     */
    public static function stringReplaceUserProperties($string, $is_title=false){

        $matches_count = preg_match_all('/{user.([a-z0-9_]+)}/i', $string, $matches);

        if ($matches_count){

            $user = self::getInstance();

            for($i=0; $i<$matches_count; $i++){

                $tag = $matches[0][$i];
                $property = $matches[1][$i];

                if (isset($user->$property)){
                    if(is_numeric($user->$property) && $is_title){
                        if($user->$property){
                            $string = str_replace($tag, ' ('.$user->$property.')', $string);
                        } else {
                            $string = str_replace($tag, '', $string);
                        }
                    } else {
                        $string = str_replace($tag, $user->$property, $string);
                    }
                }

            }

        }

        return $string;

    }

    /**
     * Возвращает список всех активных пользователей
     * @return array
     */
    public static function getAllUsers(){

        return cmsDatabase::getInstance()->get_table('cms_users', 'id > 0 AND is_locked = 0 AND is_deleted = 0');

    }

    /**
     * Возвращает количество всех активных пользователей
     * @return int
     */
    public static function getCountAllUsers(){

        return cmsDatabase::getInstance()->rows_count('cms_users', 'id > 0 AND is_locked=0 AND is_deleted=0');;

    }

    /**
     * Возвращает данные из cms_users пользователя по логину, email или id
     * @param str $id
     * @return array
     */
    public static function getShortUserData($id='') {

        if(!$id){ return false; }

        $inUser = self::getInstance();

		// для текущего юзера смысла данные из базы брать нет
        if ($inUser->id && in_array($id, array($inUser->login, $inUser->id))) {
            return get_object_vars($inUser);
        }

        if(isset(self::$cache['get_short_user'][$id])){ return self::$cache['get_short_user'][$id]; }

        if(is_numeric($id)){
            $where = "id = '$id'";
        } else {
            if(preg_match("/^(?:[a-z0-9\._\-]+)@(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?\.)+(?:[a-z]{2,6})$/ui", $id)){
                $where = "email = '$id'";
            } else {
                $where = "login = '$id'";
            }
        }

        return self::$cache['get_short_user'][$id] = cmsDatabase::getInstance()->get_fields('cms_users', $where, '*', 'id DESC');

    }

    /**
     * Возвращает массив id групп администраторов
     * @return array
     */
    public static function getAdminGroups(){

		$inDB = cmsDatabase::getInstance();

        $groups = array();

        $result = $inDB->query("SELECT id FROM cms_user_groups WHERE is_admin = 1");

        if ($inDB->num_rows($result)){
            while($group = $inDB->fetch_assoc($result)){
				$groups[] = $group['id'];
            }
        }

        return $groups;

    }

    /**
     * Возвращает id группы пользователя
     * @param int $user_id
     * @return int
     */
    public static function getGroupIdByUserId($user_id){

        return cmsDatabase::getInstance()->get_field('cms_users', "id='{$user_id}'", 'group_id');

    }

    /**
     * Возвращает название группы пользователей
     * @param int $group_id
     * @return str
     */
    public static function getGroupTitle($group_id){

        return cmsDatabase::getInstance()->get_field('cms_user_groups', "id='{$group_id}'", 'title');

    }

    /**
     * Возвращает список групп пользователей
     * @param bool $no_guests Если TRUE, группа "Гости" не выводится
     * @return array
     */
    public static function getGroups($no_guests=false){

        $inDB = cmsDatabase::getInstance();

        $groups = array();

        $sql = "SELECT id, title, alias, is_admin, access
                FROM cms_user_groups\n";

        if ($no_guests){
            $sql .= "WHERE alias <> 'guest'\n";
        }

        $sql .= "ORDER BY is_admin ASC";

        $result = $inDB->query($sql);

        if ($inDB->num_rows($result)){
            while($group = $inDB->fetch_assoc($result)){
				$groups[] = $group;
            }
        }

        return $groups;

    }

    /**
     * Возвращает пользователей в указанной группе
     * @param int $group_id
     * @return array
     */
    public static function getGroupMembers($group_id){

        $inDB = cmsDatabase::getInstance();

        $users = array();

        $sql = "SELECT id, nickname, login
                FROM cms_users
				WHERE group_id='{$group_id}' AND is_deleted=0";

        $result = $inDB->query($sql);

        if ($inDB->num_rows($result)){
            while($user = $inDB->fetch_assoc($result)){
				$users[$user['id']] = $user;
            }
        }

        return $users;

    }

    /**
     * Удаляет тип доступа группы
     * @param str $access_type
     * @return bool
     */
    public static function deleteGroupAccessType($access_type=''){

        return cmsDatabase::getInstance()->query("DELETE FROM cms_user_groups_access WHERE access_type = '{$access_type}'");

    }

    /**
     * Добавляет тип доступа группы
     * @param str $access_type
     * @param str $access_name
     * @return bool
     */
    public static function registerGroupAccessType($access_type='', $access_name='', $hide_for_guest=0){

		if(!$access_type || !$access_name) { return false; }

        $sql  = "INSERT IGNORE INTO cms_user_groups_access (access_type, access_name, hide_for_guest)
                 VALUES ('$access_type', '$access_name', '$hide_for_guest')";

        return cmsDatabase::getInstance()->query($sql);

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Авторизует пользователя
     * возвращает url для редиректа
     * @param str $login
     * @param str $passw
     * @param int $remember_pass
     * @return srt $back_url
     */
    public function signInUser($login = '', $passw = '', $remember_pass = 1, $pass_in_md5 = 0){

        if($this->id) { return cmsCore::getBackURL(); }

		$default_back_url = '/auth/error.html';

		if(!$login || !$passw) { return $default_back_url; }

        $inDB   = cmsDatabase::getInstance();
		$inCore = cmsCore::getInstance();

		// Авторизация по логину или e-mail
		if (!preg_match("/^([a-z0-9\._-]+)@([a-z0-9\._-]+)\.([a-z]{2,4})$/ui", $login)){
			$where_login = "u.login = '{$login}'";
		} else {
			$where_login = "u.email = '{$login}'";
		}
		$where_pass = $pass_in_md5 ? "u.password = '$passw'" : "u.password = md5('$passw')";

		// Проверяем локальную пару логин + пароль
		$user = $this->loadUser(0, "$where_login AND $where_pass");
		// иначе пытаемся авторизоваться через плагины
		if(!$user) {
			$user = cmsCore::callEvent('SIGNIN_USER', array('login'=>$login,'pass'=>$passw));
		}

		if(!$user) { return $default_back_url; }

		$_SESSION['user'] = $user;

		cmsCore::callEvent('USER_LOGIN', $_SESSION['user']);

		if ($remember_pass){
			$cookie_code = md5($user['id'] . $user['password'] . PATH);
			cmsCore::setCookie('userid', $cookie_code, time()+2592000);
		}

		// Флаг первой авторизации
		$first_time_auth = !$user['is_logged_once'];
		// обновляем дату последнего визита, ip
        self::setUserLogdate($user['id']);
		$inDB->query("UPDATE cms_users SET last_ip = '{$this->ip}', is_logged_once = 1 WHERE id = '{$user['id']}'");
        // помечаем, что пользователь онлайн
		$inDB->query("UPDATE cms_online SET user_id = '{$user['id']}' WHERE sess_id = '".session_id()."'");

		//////////////  юзер уже авторизован //////////////////////////

		// Формируем url редиректа после авторизации
		// Получаем настройки что делать после авторизации
		$cfg = $inCore->loadComponentConfig('registration');

		// Получаем URL, предыдущий перед формой логина
		$auth_back_url = cmsUser::sessionGet('auth_back_url');
		$auth_back_url = $auth_back_url ? $auth_back_url : cmsCore::getBackURL();
        if(strpos($auth_back_url, $_SERVER['HTTP_HOST']) === false || strpos($auth_back_url, '/auth/') !== false) {
            $auth_back_url = '/';
        }
		cmsUser::sessionDel('auth_back_url');

		// Авторизация в админку
		if($_SESSION['user']['is_admin'] && cmsCore::inRequest('is_admin')){
            return '/admin/';
		}

		// Остальные пользователи
		if($_SESSION['user']['id']){

			if ($first_time_auth) { $cfg['auth_redirect'] = $cfg['first_auth_redirect']; }

			switch($cfg['auth_redirect']){
				case 'none':        $url = $auth_back_url; break;
				case 'index':       $url = '/'; break;
				case 'profile':     $url = cmsUser::getProfileURL($user['login']); break;
				case 'editprofile': $url = '/users/'.$user['id'].'/editprofile.html'; break;
			}

			return $url;

		}

        return $default_back_url;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Разлогинивает пользователя
     * @return bool
     */
    public function logout(){

		$inDB = cmsDatabase::getInstance();

		cmsCore::unsetCookie('userid');

		$sess_id = session_id();

		cmsCore::callEvent('USER_LOGOUT', $this->id);

        self::setUserLogdate($this->id);
		$inDB->query("DELETE FROM cms_online WHERE user_id = '{$this->id}'");
		$inDB->query("DELETE FROM cms_search WHERE session_id = '$sess_id'");

		session_destroy();

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет, является ли пользователь администратором
     * @param int $userid
     * @return bool
     */
    public static function userIsAdmin($user_id){

        if (!$user_id) { return false; }

        $inUser = self::getInstance();

        if ($user_id == $inUser->id){

            return $inUser->is_admin;

        } else {

			if(isset(self::$cache['is_admin'][$user_id])){ return self::$cache['is_admin'][$user_id]; }
			$is_admin = cmsDatabase::getInstance()->get_field('cms_users u LEFT JOIN cms_user_groups g ON g.id = u.group_id', "u.id = '$user_id'", 'g.is_admin');
			return self::$cache['is_admin'][$user_id] = $is_admin;

        }

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет, является ли пользователь редактором статей
     * Возвращает массив id категорий, где пользователь редактор
     * @param int $userid
     * @return array
     */
    public static function userIsEditor($userid=0){

		$inUser = self::getInstance();

		// если проверяем текущего пользователя
		if(!$userid || $userid == $inUser->id){

			if(!$inUser->id) { return false; }

			$group_id = $inUser->group_id;

		} elseif($userid) {

			$group_id = self::getGroupIdByUserId($userid);

		}

		if(!@$group_id) { return false; }

		$cat = cmsDatabase::getInstance()->get_table('cms_category', "modgrp_id = '{$group_id}'", 'id');

		return $cat ? $cat : false;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив с администраторскими правами доступа текущего пользователя
     * @return array
     */
    public static function getAdminAccess(){

        $inUser = self::getInstance();

		if($inUser->id) { return $inUser->access; }

		$access = cmsDatabase::getInstance()->get_field('cms_user_groups', "id = '{$inUser->group_id}'", 'access');

        if ($access){
            $access = str_replace(', ', ',', $access);
            $access = explode(',', $access);
            return $access;
        }

        return false;
    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет что администратор имеет право на указанное действие
	 * $access_type like "admin/modules" or "admin/users"
     * @param string $access_type
     * @param array $access_list
     * @return bool
     */
    public static function isAdminCan($access_type, $access_list){

        $inUser = self::getInstance();

        if (!$inUser->is_admin){ return false; }

        if($inUser->id==1) { return true; }

        if (in_array($access_type, $access_list)){ return true; }

        return false;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет что пользователь имеет право на указанное действие
	 * $access_type like "comments/delete" or "photo/edit"
     * @param string $access_type
     * @return bool
     */
    public static function isUserCan($access_type){

        $inUser = self::getInstance();

		if($inUser->is_admin) { return true; }

		return in_array($access_type, $inUser->access);

    }

// ============================================================================ //
// ============================================================================ //

}