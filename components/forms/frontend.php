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

function forms(){

    cmsCore::loadClass('form');

    $do = cmsCore::getInstance()->do;

    global $_LANG;
//========================================================================================================================//
//========================================================================================================================//
    if ($do=='view'){

        // Получаем форму
        $form = cmsForm::getFormData(cmsCore::request('form_id', 'int'));
        if(!$form) { cmsCore::error404(); }

        // Получаем данные полей формы
        $form_fields = cmsForm::getFormFields($form['id']);
        // Если полей нет, 404
        if(!$form_fields) { cmsCore::error404(); }

		$errors     = array();
        $attachment = array();

        // Получаем данные формы
        // Если не переданы, назад
		$form_input = cmsForm::getFieldsInputValues($form['id']);
		if(!$form_input) {
            $errors[] = $_LANG['FORM_ERROR'];
        }
		// Проверяем значения формы
		foreach ($form_input['errors'] as $field_error) {
			if($field_error){
                $errors[] = $field_error;
            }
		}
		// проверяем каптчу
		if(!cmsPage::checkCaptchaCode()) {
            $errors[] = $_LANG['ERR_CAPTCHA'];
        }

		if($errors){
            if(cmsCore::isAjax()){
                cmsCore::jsonOutput(array('error' => true,
                                          'text' => end($errors)));
            } else {
                foreach ($errors as $error) {
                    cmsCore::addSessionMessage($error, 'error');
                }
                cmsCore::redirectBack();
            }
        }

        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

        // Подготовим начало письма
        $mail_message  = '<h3>'.$_LANG['FORM'].': ' . $form['title'] . '</h3>';
		// Добавляем заполненные поля в письмо
        foreach ($form_fields as $field) {

            // Значение поля
            $value = $form_input['values'][$field['id']];
            if(!$value){ continue; }

			if(is_string($value)){
				$mail_message .= '<h5>'.$field['title'] . '</h5><p>'.$value.'</p>';
			} elseif(is_array($value)) { // если массив, значит к форме прикреплен файл

                if ($form['sendto']=='mail'){
                    $attachment[] = !empty($value['url']) ? PATH.$value['url'] : '';
                } elseif(!empty($value['url'])) {
                    $mail_message .= '<h5>'.$field['title'] . '</h5><p><a href="'.$value['url'].'">'.$value['name'].'</a></p>';
                }

            }

        }

        // Отправляем форму
		if ($form['sendto']=='mail'){
            $emails = explode(',', $form['email']);
            if($emails){
                foreach ($emails as $email) {
                    cmsCore::mailText(trim($email), cmsConfig::getConfig('sitename').': '.$form['title'], $mail_message, $attachment);
                }
            }
            // удаляем прикрепленные файлы
			foreach($attachment as $attach){
				@unlink($attach);
			}
		} else {
			cmsUser::sendMessage(-2, $form['user_id'], $mail_message);
		}

		cmsUser::sessionClearAll();

        if(cmsCore::isAjax()){
            cmsCore::jsonOutput(array('error' => false,
                                      'text' => $_LANG['FORM_IS_SEND']));
        } else {
            cmsCore::addSessionMessage($_LANG['FORM_IS_SEND'], 'info');
            cmsCore::redirectBack();
        }

    }

//========================================================================================================================//

}