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

function registration(){

    header('X-Frame-Options: DENY');

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();
    $inConf = cmsConfig::getInstance();

    $model = new cms_model_registration();

    cmsCore::loadModel('users');
    $users_model = new cms_model_users();

    global $_LANG;

	$do = $inCore->do;

//============================================================================//
if ($do=='sendremind'){

    if ($inUser->id) {
        cmsCore::error404();
    }

    $inPage->setTitle($_LANG['REMINDER_PASS']);
    $inPage->addPathway($_LANG['REMINDER_PASS']);

    if (!cmsCore::inRequest('goremind')){

        cmsPage::initTemplate('components', 'com_registration_sendremind')->
                display('com_registration_sendremind.tpl');

    } else {

        if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $email = cmsCore::request('email', 'email', '');
        if(!$email) { cmsCore::addSessionMessage($_LANG['ERR_EMAIL'], 'error'); cmsCore::redirectBack(); }

        $usr = cmsUser::getShortUserData($email);
        if(!$usr || $usr['is_locked'] || $usr['is_deleted']) {
            cmsCore::addSessionMessage($_LANG['ADRESS'].' "'.$email.'" '.$_LANG['NOT_IN_OUR_BASE'], 'error');
            cmsCore::redirectBack();
        }

        if(cmsUser::userIsAdmin($usr['id'])){
            cmsCore::addSessionMessage($_LANG['NOT_ADMIN_SENDREMIND'], 'error');
            cmsCore::redirectBack();
        }

        $usercode = md5($usr['id'] . '-' . uniqid() . '-' . microtime() . '-' . PATH);

        $sql = "INSERT cms_users_activate (pubdate, user_id, code)
                VALUES (NOW(), '{$usr['id']}', '$usercode')";
        $inDB->query($sql);

        $newpass_link = HOST.'/registration/remind/' . $usercode;

        $mail_message = $_LANG['HELLO'].', ' . $usr['nickname'] . '!'. "\n\n";
        $mail_message .= $_LANG['REMINDER_TEXT'].' "'.$inConf->sitename.'".' . "\n\n";
        $mail_message .= $_LANG['YOUR_LOGIN'].': ' .$usr['login']. "\n\n";
        $mail_message .= $_LANG['NEW_PASS_LINK'].":\n" .$newpass_link . "\n\n";
        $mail_message .= $_LANG['LINK_EXPIRES']. "\n\n";
        $mail_message .= $_LANG['SIGNATURE'].', '. $inConf->sitename . ' ('.HOST.').' . "\n";
        $mail_message .= date('d-m-Y (H:i)');

        $inCore->mailText($email, $inConf->sitename.' - '.$_LANG['REMINDER_PASS'], $mail_message);

        cmsCore::addSessionMessage($_LANG['NEW_PAS_SENDED'], 'info');

        cmsCore::redirect('/login');

    }

}

//============================================================================//
if ($do=='remind'){

    if ($inUser->id) {
        cmsCore::error404();
    }

    $usercode = cmsCore::request('code', 'str', '');
    //проверяем формат кода
    if (!preg_match('/^[0-9a-f]{32}$/i', $usercode)){ cmsCore::error404(); }

    // проверяем код
    $user_id = $inDB->get_field('cms_users_activate', "code = '$usercode'", 'user_id');
    if (!$user_id){ cmsCore::error404(); }

    //получаем пользователя
    $user = $inDB->get_fields('cms_users', "id = '{$user_id}'", '*');
    if (!$user){ cmsCore::error404(); }

    if(cmsUser::userIsAdmin($user['id'])){
        cmsCore::error404();
    }

    if (cmsCore::inRequest('submit')){

        if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        $errors = false;

        $pass  = cmsCore::request('pass', 'str', '');
        $pass2 = cmsCore::request('pass2', 'str', '');

        if(!$pass) { cmsCore::addSessionMessage($_LANG['TYPE_PASS'], 'error'); $errors = true; }
        if($pass && !$pass2) { cmsCore::addSessionMessage($_LANG['TYPE_PASS_TWICE'], 'error'); $errors = true; }
        if($pass && $pass2 && mb_strlen($pass)<6) { cmsCore::addSessionMessage($_LANG['PASS_SHORT'], 'error'); $errors = true; }
        if($pass && $pass2 && $pass != $pass2) { cmsCore::addSessionMessage($_LANG['WRONG_PASS'], 'error'); $errors = true; }

        if ($errors){ cmsCore::redirectBack(); }

        $md5_pass = md5($pass);

        $inDB->query("UPDATE cms_users SET password = '{$md5_pass}', logdate = NOW() WHERE id = '{$user['id']}'");

        $inDB->query("DELETE FROM cms_users_activate WHERE code = '$usercode'");

        cmsCore::addSessionMessage($_LANG['CHANGE_PASS_COMPLETED'], 'info');

        $inUser->signInUser($user['login'], $pass, true);

        cmsCore::redirect(cmsUser::getProfileURL($user['login']));

    }

    $inPage->setTitle($_LANG['RECOVER_PASS']);
    $inPage->addPathway($_LANG['RECOVER_PASS']);

    cmsPage::initTemplate('components', 'com_registration_remind')->
            assign('cfg', $model->config)->
            assign('user', $user)->
            display('com_registration_remind.tpl');

}

//============================================================================//
if ($do=='register'){

    if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    if ($inUser->id && !$inUser->is_admin) {
        if ($inCore->menuId() == 1) { return; } else {  cmsCore::error404(); }
    }

    // регистрация закрыта
    if(!$model->config['is_on']){
        cmsCore::error404();
    }
    // регистрация по инвайтам
    if ($model->config['reg_type']=='invite'){
        if (!$users_model->checkInvite(cmsUser::sessionGet('invite_code'))) {
            cmsCore::error404();
        }
    }

    $errors = false;

    // получаем данные
    $item['login'] = cmsCore::request('login', 'str', '');
    $item['email'] = cmsCore::request('email', 'email');
    $item['icq']   = cmsCore::request('icq', 'str', '');
    $item['city']  = cmsCore::request('city', 'str', '');
    $item['nickname']  = cmsCore::request('nickname', 'str', '');
    $item['realname1'] = cmsCore::request('realname1', 'str', '');
    $item['realname2'] = cmsCore::request('realname2', 'str', '');
    $pass  = cmsCore::request('pass', 'str', '');
    $pass2 = cmsCore::request('pass2', 'str', '');

    // проверяем логин
    if(mb_strlen($item['login'])<2 ||
            mb_strlen($item['login'])>15 ||
            is_numeric($item['login']) ||
            !preg_match("/^([a-z0-9])+$/ui", $item['login'])) {

        cmsCore::addSessionMessage($_LANG['ERR_LOGIN'], 'error'); $errors = true;

    }

    // проверяем пароль
    if(!$pass) { cmsCore::addSessionMessage($_LANG['TYPE_PASS'], 'error'); $errors = true; }
    if($pass && !$pass2) { cmsCore::addSessionMessage($_LANG['TYPE_PASS_TWICE'], 'error'); $errors = true; }
    if($pass && $pass2 && mb_strlen($pass)<6) { cmsCore::addSessionMessage($_LANG['PASS_SHORT'], 'error'); $errors = true; }
    if($pass && $pass2 && $pass != $pass2) { cmsCore::addSessionMessage($_LANG['WRONG_PASS'], 'error'); $errors = true; }

    // Проверяем nickname или имя и фамилию
    if($model->config['name_mode']=='nickname'){
        if(!$item['nickname']) { cmsCore::addSessionMessage($_LANG['TYPE_NICKNAME'], 'error'); $errors = true; }
    } else {
        if(!$item['realname1']) { cmsCore::addSessionMessage($_LANG['TYPE_NAME'], 'error'); $errors = true; }
        if(!$item['realname2']) { cmsCore::addSessionMessage($_LANG['TYPE_SONAME'], 'error'); $errors = true; }
        $item['nickname'] = trim($item['realname1']) . ' ' . trim($item['realname2']);
    }
    if (mb_strlen($item['nickname'])<2) { cmsCore::addSessionMessage($_LANG['SHORT_NICKNAME'], 'error'); $errors = true; }
    if($model->getBadNickname($item['nickname'])){
        cmsCore::addSessionMessage($_LANG['ERR_NICK_EXISTS'], 'error'); $errors = true;
    }

    // Проверяем email
    if(!$item['email']) { cmsCore::addSessionMessage($_LANG['ERR_EMAIL'], 'error'); $errors = true; }

    // День рождения
    list($item['bday'], $item['bmonth'], $item['byear']) = array_values(cmsCore::request('birthdate', 'array_int', array()));
    $item['birthdate'] = sprintf('%04d-%02d-%02d', $item['byear'], $item['bmonth'], $item['bday']);

    // получаем данные конструктора форм
    $item['formsdata'] = '';
    if(isset($users_model->config['privforms'])){
        if (is_array($users_model->config['privforms'])){
            foreach($users_model->config['privforms'] as $form_id){
                $form_input  = cmsForm::getFieldsInputValues($form_id);
                $item['formsdata'] .= $inDB->escape_string(cmsCore::arrayToYaml($form_input['values']));
                // Проверяем значения формы
                foreach ($form_input['errors'] as $field_error) {
                    if($field_error){ cmsCore::addSessionMessage($field_error, 'error'); $errors = true; }
                }
            }
        }
    }

    // Проверяем каптчу
    if(!cmsPage::checkCaptchaCode()) { cmsCore::addSessionMessage($_LANG['ERR_CAPTCHA'], 'error'); $errors = true; }

    // проверяем есть ли такой пользователь
    $user_exist = $inDB->get_fields('cms_users', "(login LIKE '{$item['login']}' OR email LIKE '{$item['email']}') AND is_deleted = 0", 'id, login, email');
    if($user_exist){
        if($user_exist['login'] == $item['login']){
            cmsCore::addSessionMessage($_LANG['LOGIN'].' "'.$item['login'].'" '.$_LANG['IS_BUSY'], 'error'); $errors = true;
        } else {
            cmsCore::addSessionMessage($_LANG['EMAIL_IS_BUSY'], 'error'); $errors = true;
        }
    }

    // В случае ошибок, возвращаемся в форму
    if($errors){
        cmsUser::sessionPut('item', $item);
        cmsCore::redirect('/registration');
    }

    //////////////////////////////////////////////
    //////////// РЕГИСТРАЦИЯ /////////////////////
    //////////////////////////////////////////////

    $item['is_locked'] = $model->config['act'];
    $item['password']  = md5($pass);
    $item['orig_password'] = $pass;
    $item['group_id']  = $model->config['default_gid'];
    $item['regdate']   = date('Y-m-d H:i:s');
    $item['logdate']   = date('Y-m-d H:i:s');

    if (cmsUser::sessionGet('invite_code')){

        $invite_code = cmsUser::sessionGet('invite_code');
        $item['invited_by'] = (int)$users_model->getInviteOwner($invite_code);

        if ($item['invited_by']){ $users_model->closeInvite($invite_code); }

        cmsUser::sessionDel('invite_code');

    } else {
        $item['invited_by'] = 0;
    }

    $item = cmsCore::callEvent('USER_BEFORE_REGISTER', $item);

    $item['id'] = $item['user_id'] = $inDB->insert('cms_users', $item);
    if(!$item['id']){ cmsCore::error404(); }

    $inDB->insert('cms_user_profiles', $item);

    cmsCore::callEvent('USER_REGISTER', $item);

    if ($item['is_locked']){

        $model->sendActivationNotice($pass, $item['id']);
        cmsPage::includeTemplateFile('special/regactivate.php');
        cmsCore::halt();

    } else {

        cmsActions::log('add_user', array(
            'object' => '',
            'user_id' => $item['id'],
            'object_url' => '',
            'object_id' => $item['id'],
            'target' => '',
            'target_url' => '',
            'target_id' => 0,
            'description' => ''
        ));

        if ($model->config['send_greetmsg']){ $model->sendGreetsMessage($item['id']); }
        $model->sendRegistrationNotice($pass, $item['id']);

        $back_url = $inUser->signInUser($item['login'], $pass, true);

        cmsCore::redirect($back_url);

    }

}

//============================================================================//
if ($do=='view'){

    $pagetitle = $inCore->getComponentTitle();

    $inPage->setTitle($pagetitle);
    $inPage->addPathway($pagetitle);
    $inPage->addHeadJsLang(array('WRONG_PASS'));

    // Если пользователь авторизован, то не показываем форму регистрации, редирект в профиль.
    if ($inUser->id && !$inUser->is_admin) {
        if ($inCore->menuId() == 1) { return; } else {  cmsCore::redirect(cmsUser::getProfileURL($inUser->login)); }
    }

    $correct_invite = (cmsUser::sessionGet('invite_code') ? true : false);

    if ($model->config['reg_type']=='invite' && cmsCore::inRequest('invite_code')){

        $invite_code    = cmsCore::request('invite_code', 'str', '');
        $correct_invite = $users_model->checkInvite($invite_code);

        if ($correct_invite) {
            cmsUser::sessionPut('invite_code', $invite_code);
        } else {
            cmsCore::addSessionMessage($_LANG['INCORRECT_INVITE'], 'error');
        }

    }

    $item = cmsUser::sessionGet('item');
    if($item){ cmsUser::sessionDel('item'); }

    if(empty($item['birthdate'])){
        $item['birthdate'] = date('Y-m-d');
    }

    $private_forms = array();
    if(isset($users_model->config['privforms'])){
        if (is_array($users_model->config['privforms'])){
            foreach($users_model->config['privforms'] as $form_id){
                $private_forms = array_merge($private_forms, cmsForm::getFieldsHtml($form_id, array(), true));
            }
        }
    }

    cmsPage::initTemplate('components', 'com_registration')->
            assign('cfg', $model->config)->
            assign('item', $item)->
            assign('pagetitle', $pagetitle)->
            assign('correct_invite', $correct_invite)->
            assign('private_forms', $private_forms)->
            display('com_registration.tpl');

}

//============================================================================//
if ($do=='activate'){

    $code = cmsCore::request('code', 'str', '');
    if (!$code) { cmsCore::error404(); }

    $user_id = $inDB->get_field('cms_users_activate', "code = '$code'", 'user_id');
    if (!$user_id){ cmsCore::error404(); }

    $inDB->query("UPDATE cms_users SET is_locked = 0 WHERE id = '$user_id'");
    $inDB->query("DELETE FROM cms_users_activate WHERE code = '$code'");

    cmsCore::callEvent('USER_ACTIVATED', $user_id);

    if ($model->config['send_greetmsg']){ $model->sendGreetsMessage($user_id); }

    // Регистрируем событие
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

    cmsCore::addSessionMessage($_LANG['ACTIVATION_COMPLETE'], 'info');

    cmsUser::goToLogin();

}

//============================================================================//

if ($do=='auth'){

    //====================//
    //==  разлогивание  ==//
    if(cmsCore::inRequest('logout')) {

        $inUser->logout();
        cmsCore::redirect('/');

    }

    //====================//
    //==  авторизация  ==//
    if( !cmsCore::inRequest('logout') ) {

        // флаг неуспешных авторизаций
        $anti_brute_force = cmsUser::sessionGet('anti_brute_force');

        $login = cmsCore::request('login', 'str', '');
        $passw = cmsCore::request('pass', 'str', '');
        $remember_pass = cmsCore::inRequest('remember');

        // если нет логина или пароля, показываем форму входа
        if (!$login || !$passw){

            if($inUser->id && !$inUser->is_admin) { cmsCore::redirect('/'); }

            $inPage->setTitle($_LANG['SITE_LOGIN']);
            $inPage->addPathway($_LANG['SITE_LOGIN']);

            cmsPage::initTemplate('components', 'com_registration_login')->
                    assign('cfg', $model->config)->
                    assign('anti_brute_force', $anti_brute_force)->
                    assign('is_sess_back', cmsUser::sessionGet('auth_back_url'))->
                    display('com_registration_login.tpl');

            if(!mb_strstr(cmsCore::getBackURL(), 'login')){
                cmsUser::sessionPut('auth_back_url', cmsCore::getBackURL());
            }

            return;

        }

        if(!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        // Проверяем каптчу
        if($anti_brute_force && !cmsPage::checkCaptchaCode()) {
            cmsCore::addSessionMessage($_LANG['ERR_CAPTCHA'], 'error');
            cmsCore::redirect('/login');
        }

        cmsUser::sessionDel('anti_brute_force');

        $back_url = $inUser->signInUser($login, $passw, $remember_pass);

        cmsCore::redirect($back_url);

    }

}


//============================================================================//
if ($do=='autherror'){

    cmsUser::sessionPut('anti_brute_force', 1);
    cmsPage::includeTemplateFile('special/autherror.php');
    cmsCore::halt();

}

//============================================================================//

}