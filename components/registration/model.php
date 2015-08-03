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

class cms_model_registration {

	public function __construct(){
        $this->config = cmsCore::getInstance()->loadComponentConfig('registration');
		cmsCore::loadLanguage('components/registration');
        $this->inDB = cmsDatabase::getInstance();
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    public static function getDefaultConfig() {

        $cfg = array (
                'reg_type' => 'open',
                'inv_count' => 3,
                'inv_karma' => 1,
                'inv_period' => 'WEEK',
                'default_gid' => 1,
                'is_on' => 1,
                'act' => 0,
                'send' => 0,
                'offmsg' => 'Регистрация приостановлена по техническим причинам.',
                'first_auth_redirect' => 'profile',
                'auth_redirect' => 'profile',
                'name_mode' => 'nickname',
                'badnickname' => 'администратор
                                    админ
                                    qwert
                                    qwerty
                                    123
                                    admin
                                    вася пупкин',
                'ask_icq' => 1,
                'ask_birthdate' => 1,
                'ask_city' => 1,
                'send_greetmsg' => 1,
                'greetmsg' => '<h2>Привет!</h2><p>Мы очень <span style="color: rgb(51, 153, 102);">рады</span> что ты зарегистрировался!</p>'
              );

        return $cfg;

    }
/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getBadNickname($nickname){

		return in_array(mb_strtolower($nickname), explode("\n", $this->config['badnickname']));

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function sendGreetsMessage($user_id) {

        return cmsUser::sendMessage(USER_MASSMAIL, $user_id, $this->config['greetmsg']);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function sendRegistrationNotice($send_pass, $user_id){

        global $_LANG;

        $user = cmsUser::getShortUserData($user_id);
        if(!$user_id){ return false; }

        $user['password'] = $send_pass;

        $letter = cmsCore::getLanguageTextFile('registration');

        foreach($user as $key=>$value){
            $letter= str_replace('{'.$key.'}', $value, $letter);
        }
        $letter= str_replace('{sitename}', cmsConfig::getConfig('sitename'), $letter);

        return cmsCore::mailText($user['email'], $_LANG['THANKS_FOR_REGISTERING'].' - '.cmsConfig::getConfig('sitename'), $letter);

    }

    public function sendActivationNotice($send_pass, $user_id){

        global $_LANG;

        $user = cmsUser::getShortUserData($user_id);
        if(!$user_id){ return false; }

        $user['password'] = $send_pass;

        $code = md5($user['email'].uniqid().'-'.microtime());
        $codelink = HOST.'/activate/'.$code;

        $sql = "INSERT cms_users_activate (pubdate, user_id, code)
                VALUES (NOW(), '{$user['id']}', '$code')";
        $this->inDB->query($sql);

        $letter = cmsCore::getLanguageTextFile('activation');

        foreach($user as $key=>$value){
            $letter= str_replace('{'.$key.'}', $value, $letter);
        }
        $letter= str_replace('{sitename}', cmsConfig::getConfig('sitename'), $letter);
        $letter= str_replace('{codelink}', $codelink, $letter);

        return cmsCore::mailText($user['email'], $_LANG['ACTIVATION_ACCOUNT'].' - '.cmsConfig::getConfig('sitename'), $letter);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

}