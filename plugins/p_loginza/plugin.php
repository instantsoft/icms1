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

class p_loginza extends cmsPlugin {

    private $reg_model;

    public function __construct(){

        // Информация о плагине
        $this->info['plugin']      = 'p_loginza';
        $this->info['title']       = 'Авторизация Loginza';
        $this->info['description'] = 'Позволяет посетителям авторизоваться на сайте, используя аккаунты популярных социальных сетей';
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.10.4';

        // Настройки по-умолчанию
        $this->config['PL_PROVIDERS'] = 'vkontakte,facebook,mailruapi,google,yandex,openid,twitter,webmoney,rambler,flickr,mailru,loginza,myopenid,lastfm,verisign,aol,steam';
        $this->config['PL_LANG']      = 'ru';

        // События, которые будут отлавливаться плагином
        $this->events[] = 'LOGINZA_BUTTON';
        $this->events[] = 'LOGINZA_AUTH';

        cmsCore::loadModel('registration');
        $this->reg_model = new cms_model_registration();

        parent::__construct();

    }

// ==================================================================== //
    /**
     * Процедура установки плагина
     * @return bool
     */
    public function install(){

        $inDB = cmsDatabase::getInstance();

        if (!$inDB->isFieldExists('cms_users', 'openid')){

            $inDB->query("ALTER TABLE `cms_users` ADD `openid` VARCHAR( 250 ) NULL, ADD INDEX ( `openid` )");

        }

        return parent::install();

    }

// ==================================================================== //

    /**
     * Процедура обновления плагина
     * @return bool
     */
    public function upgrade(){

        cmsDatabase::getInstance()->query("UPDATE `cms_users` SET `openid` = MD5(openid) WHERE `openid` IS NOT NULL");

        return parent::upgrade();

    }

// ==================================================================== //
    /**
     * Обработка событий
     * @param string $event
     * @param mixed $item
     * @return mixed
     */
    public function execute($event='', $item=array()){

        if($this->reg_model->config['reg_type']=='invite'){
            return true;
        }

        switch ($event){
            case 'LOGINZA_BUTTON':  $item = $this->showLoginzaButton(); break;
            case 'LOGINZA_AUTH':    $item = $this->loginzaAuth(); break;
        }

        return true;

    }

// ==================================================================== //

    private function showLoginzaButton() {

        global $_LANG;

        $token_url  = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/plugins/p_loginza/auth.php');

        $html  = '<div class="lf_title">'.$_LANG['PL_LOGIN_LOGINZA'].'</div><p style="margin:15px 0">'.$_LANG['PL_LOGIN_LOGINZA_INFO'].'</p><p><script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
                 <a href="http://loginza.ru/api/widget?token_url='.$token_url.'&providers_set='.$this->config['PL_PROVIDERS'].'&lang='.$this->config['PL_LANG'].'" class="loginza">
                     <img src="http://loginza.ru/img/sign_in_button_gray.gif" alt="'.$_LANG['PL_LOGIN_LOGINZA_DO'].'"/>
                 </a></p>';

        echo $html;

        return;

    }

// ==================================================================== //

    private function loginzaAuth(){

        $inDB   = cmsDatabase::getInstance();
		$inUser = cmsUser::getInstance();

        $token = cmsCore::request('token', 'str', '');
        if (!$token){ cmsCore::error404(); }

        // получение профиля
        $profile = $this->request('http://loginza.ru/api/authinfo?token='.$token);

        // проверка на ошибки
        if (!is_object($profile) || !empty($profile->error_message) || !empty($profile->error_type)) {
            cmsCore::error404();
        }

        // ищем такого пользователя
        $user_id = $this->getUserByIdentity($profile->identity);

        // если пользователя нет, создаем
        if (!$user_id){
            $user_id = $this->createUser($profile);
        }

        // если пользователь уже был или успешно создан, авторизуем
        if ($user_id){
			$user = $inDB->get_fields('cms_users', "id = '{$user_id}'", 'login, password');
			if(!$user) { cmsCore::error404(); }

			$back_url = $inUser->signInUser($user['login'], $user['password'], 1, 1);

			cmsCore::redirect($back_url);

        }

        // если авторизация не удалась, редиректим на сообщение об ошибке
        cmsCore::redirect('/auth/error.html');

    }

// ==================================================================== //

    private function createUser($profile){

        $inCore = cmsCore::getInstance();
        $inDB   = cmsDatabase::getInstance();
        $inUser = cmsUser::getInstance();
		cmsCore::loadClass('actions');

        $nickname = $email = $birthdate = '';
        $advanced = array();

        // для вконтакте поолучаем большой аватар, статус и город
        if(strstr($profile->identity, '//vk.com')){
            $vk = $this->callVk($profile->uid);
            if($vk){
                $advanced = array(
                    'city'=>$vk->city->title,
                    'status'=>$vk->status,
                    'photo'=>$vk->photo_max_orig
                );
            }
        }

        if (!empty($profile->name->full_name)){

            // указано полное имя
            $nickname = $profile->name->full_name;

        } elseif(!empty($profile->name->first_name)) {

            // указано имя и фамилия по-отдельности
            $nickname = $profile->name->first_name;
            if (!empty($profile->name->last_name)){ $nickname .= ' '. $profile->name->last_name; }

        } elseif(preg_match('/^(http:\/\/)([a-zA-Z0-9\-_]+)\.([a-zA-Z0-9\-_]+)\.([a-zA-Z]{2,6})([\/]?)$/i', $profile->identity)) {

            // не указано имя, но передан идентификатор в виде домена
            $nickname = parse_url($profile->identity, PHP_URL_HOST);

        }

		$nickname = cmsCore::strClear($nickname);
        $login    = substr(str_replace('-', '', cmsCore::strToURL($nickname)), 0, 15);

        if (!$nickname || !$login){
            // не указано вообще ничего
            $max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
            $nickname = $login = 'user' . ($max['id'] + 1);
        }

        // генерируем пароль
        $pass = md5(substr(md5(microtime().uniqid()), 0, 8));

        if(!empty($profile->email)){

            $email = cmsCore::strClear($profile->email);

            $already_email = $inDB->get_field('cms_users', "email='{$email}' AND is_deleted=0", 'email');

            if ($already_email == $email){
                cmsCore::redirect('/auth/error.html');
            }

        }

        if(!empty($profile->dob)){
            $birthdate = cmsCore::strClear($profile->dob);
        }

		// проверяем занятость логина
		if ($inDB->get_field('cms_users', "login='{$login}' AND is_deleted=0", 'login') == $login){
			// если логин занят, добавляем к нему ID
			$max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
			$login .= ($max['id']+1);
		}

        $user_array = cmsCore::callEvent('USER_BEFORE_REGISTER', array(
            'status'=>(!empty($advanced['status']) ? $advanced['status'] : ''),
            'status_date'=>date('Y-m-d H:i:s'),
            'login'=>$login,
            'nickname'=>$nickname,
            'password'=>$pass,
            'email'=>$email,
            'birthdate'=>$birthdate,
            'group_id'=>$this->reg_model->config['default_gid'],
            'regdate'=>date('Y-m-d H:i:s'),
            'logdate'=>date('Y-m-d H:i:s'),
            'invited_by'=>0,
            'openid'=>md5($profile->identity),
        ));

        $user_array['id'] = $user_id = $inDB->insert('cms_users', $user_array);

        // создаем профиль пользователя
        if ($user_id){

            $filename = 'nopic.jpg';

            // если есть аватар, пробуем скачать
            if (!empty($profile->photo) || !empty($advanced['photo'])){
                $photo_path = $this->downloadAvatar((!empty($advanced['photo']) ? $advanced['photo'] : $profile->photo));
                if ($photo_path){

                    cmsCore::includeGraphics();

                    $uploaddir 		= PATH.'/images/users/avatars/';
                    $filename 		= md5($photo_path . '-' . $user_id . '-' . time()).'.jpg';
                    $uploadavatar 	= $uploaddir . $filename;
                    $uploadthumb 	= $uploaddir . 'small/' . $filename;

                    $cfg = $inCore->loadComponentConfig('users');

                    @img_resize($photo_path, $uploadavatar, $cfg['medw'], $cfg['medh']);
                    @img_resize($photo_path, $uploadthumb, $cfg['smallw'], $cfg['smallw']);

                    @unlink($photo_path);

                }
            }

            $inUser->loadUserGeo();

            $inDB->insert('cms_user_profiles', array(
                'city'=>(!empty($advanced['city']) ? $advanced['city'] : $inUser->city),
                'user_id'=>$user_id,
                'imageurl'=>$filename,
                'gender'=>(!empty($profile->gender) ? strtolower($profile->gender) : 'm')
            ));

            cmsCore::callEvent('USER_REGISTER', $user_array);

			cmsActions::log('add_user', array(
				'object' => '',
				'user_id' => $user_id,
				'object_url' => '',
				'object_id' => $user_id,
				'target' => '',
				'target_url' => '',
				'target_id' => 0,
				'description' => ''
			));

            if ($this->reg_model->config['send_greetmsg']){ $this->reg_model->sendGreetsMessage($user_id); }

            return $user_id;

        }

        return false;

    }

// ==================================================================== //

    private function downloadAvatar($url){

        $tempfile   = PATH.'/images/users/avatars/'.md5(session_id()).'.jpg';

        if (function_exists('curl_init')){

            $curl = curl_init();
            $user_agent = 'Loginza-API/InstantCMS';

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, false);
            $raw_data = curl_exec($curl);
            curl_close($curl);

        } else {

            $raw_data = file_get_contents($url);

        }

		if($f = @fopen($tempfile, 'w')){

			@fwrite($f, $raw_data);
			@fclose($f);

			return $tempfile;

		} else {

			return false;

		}

    }

// ==================================================================== //

    private function getUserByIdentity($identity){
        return cmsDatabase::getInstance()->get_field('cms_users', "openid='".md5($identity)."'", 'id');
    }

// ==================================================================== //

    private function request($url) {

        if (function_exists('curl_init')){

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, 'InstantCMS/1.10.4 +'.HOST);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $raw_data = curl_exec($curl);
            curl_close($curl);

        } else {

            $raw_data = @file_get_contents($url);

        }

        return $raw_data ? json_decode($raw_data) : false;

    }

// ==================================================================== //

    private function callVk($uid) {

        $r = $this->request('https://api.vk.com/method/users.get?'.http_build_query(array(
            'v'=>'5.21',
            'user_ids'=>$uid,
            'fields'=>'city,photo_max_orig,status'
        )));

        return $r ? current($r->response) : false;

    }

}