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

class cmsForm {

    private $kinds       = array('text','link','textarea','checkbox','radiogroup','list','menu','file');
    public  $values      = array();
    public  $form        = array();
    public  $form_fields = array();
    private $form_id;
	public  $is_admin;

	private static $cached_form_data   = array();
	private static $cached_form_fields = array();

    private function __construct($form_id, $values = array(), $is_admin = false) {

        $this->form_id  = cmsDatabase::getInstance()->escape_string($form_id);
        $this->values   = $values;
        $this->is_admin = $is_admin;

		cmsCore::loadLanguage('components/forms');

		$this->loadFormData();
		$this->form_fields = $this->getFormFields($this->form_id);

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Проверяет значения формы
     * @param int $form_id ID формы
     * @return array
     */
	public static function getFieldsInputValues($form_id){

		// Получаем данные без mysqli_real_escape_string
		$form_array = cmsCore::request('field', 'array');
		if(!$form_array) { return array(); }

		$formObj = new self($form_id, $form_array);

		if(!$formObj->form || !$formObj->form_fields) { return array(); }

		global $_LANG;
        $inUser = cmsUser::getInstance();

		$output = array();

		// Заполняем выходной массив значений
		// $output['values'] массив значений полей
		// $output['errors'] массив ошибок полей
        foreach ($formObj->form_fields as $field) {

			// Значение поля
			$field_value = array_key_exists($field['id'], $formObj->values) ? $formObj->values[$field['id']] : '';

			$error = '';

			// проверяем заполненность текстового поля если нужно
			if($field['mustbe'] && !$field_value && $field['kind'] != 'file'){

				$error = $_LANG['FIELD'].' "'.$field['title'].'" '.$_LANG['MUST_BE_FILLED'];

			} else {
				cmsUser::sessionPut('form_last_'.$formObj->form_id.'_'.$field['id'], htmlspecialchars($field_value));
			}

            // Загружаем файл если он есть в форме
            if($field['kind'] == 'file'){

                // старые значения если есть
                $old_url   = $field_value['url'];
                $old_name  = $field_value['name'];

                $output['values'][$field['id']] = array();

                if(!$_FILES['field']['name'][$field['id']]){
                    // Поле обязательно для заполнения?
                    if($field['mustbe'] && !$old_url){
                        $error = $_LANG['FIELD'].' "'.$field['title'].'" '.$_LANG['MUST_BE_FILLED'];
                    } else {
                        // если пришел флаг на удаление
                        if(isset($field_value['delete'])){
                            $formObj->deleteFile($old_url);
                            $old_url  = '';
                            $old_name = '';
                        }
                        $output['values'][$field['id']] = array('name'=>$old_name,
                                                                'url'=>$old_url);
                    }
                } else {

                    // директория загрузки
                    $upload_dir = PATH.'/upload/forms/'.$form_id.'/'.$inUser->id;
                    @mkdir($upload_dir, 0777, true);
                    @chmod($upload_dir, 0777);

                    // проверяем расширение файла
                    $maytypes = explode(',', $field['config']['ext']);
                    $ext = mb_strtolower(pathinfo($_FILES['field']['name'][$field['id']], PATHINFO_EXTENSION));
                    if(!in_array($ext, $maytypes)) {
                        $error = $_LANG['ERROR_TYPE_FILE'].$field['config']['ext'];
                    }

                    // имя файла
                    $file_name = cmsCore::strToURL(pathinfo($_FILES['field']['name'][$field['id']], PATHINFO_FILENAME));

                    $dest = $upload_dir.'/'.$file_name.uniqid().'.'.$ext;

                    if (!$error) {
                        if(!cmsCore::moveUploadedFile($_FILES['field']['tmp_name'][$field['id']],
                            $dest, $_FILES['field']['error'][$field['id']])){
                            $error = cmsCore::uploadError();
                        } else {
                            $formObj->deleteFile($old_url);
                            $output['values'][$field['id']] = array('name'=>$file_name,
                                                                    'url'=>str_replace(PATH, '', $dest));
                        }
                    }

                }

            } else {
                // Заполняем массив значений полей, ключи массива id поля
                $output['values'][$field['id']] = $field['config']['max'] ?
                                                    mb_substr(strip_tags($field_value), 0, $field['config']['max']) :
                                                    strip_tags($field_value);
            }

			// Заполняем массив ошибок
			$output['errors'][$field['id']] = $error;

        }

        // если были ошибки, удаляем загруженный файл, если он есть
        if(!empty($dest)){
            foreach ($output['errors'] as $field_error) {
                if($field_error){
                    @unlink($dest); break;
                }
            }
        }

		return $output;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Удаляет файл, загруженный через форму
     * @param str $url путь к файлу от корня сайта
     * @return bool
     */
    private function deleteFile($url){

        $inUser = cmsUser::getInstance();

        // администратор удаляет без проверки
        if($inUser->is_admin){
            @unlink(PATH.$url); return;
        }

        // имя файла
        $file_name  = pathinfo($url, PATHINFO_BASENAME);
        // полный путь
        $path = PATH.'/upload/forms/'.$this->form_id.'/'.$inUser->id.'/'.$file_name;

        @unlink($path); return;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает html формы
     * @param int $form_id ID формы
     * @param array $values Значения полей
     * @param bool $is_admin
     * @return str HTML
     */
	public static function displayForm($form_id, $values = array(), $is_admin = false){

		$formObj = new self($form_id, $values, $is_admin);

		if(!$formObj->form || !$formObj->form_fields) { return ''; }

        // Формируем поля формы
        foreach ($formObj->form_fields as $key => $field) {
            $formObj->form_fields[$key]['field'] = $formObj->getFormField($field);
        }

		ob_start();

		cmsPage::includeTemplateFile('special/'.$formObj->form['tpl'].'.php', array('formObj'=>$formObj));

		return ob_get_clean();

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив значений полей формы
     * @param int $form_id ID формы
     * @param array $values Значения полей
     * @return array
     */
	public static function getFieldsValues($form_id, $values = array()){

		$formObj = new self($form_id,$values);

		if(!$formObj->form || !$formObj->form_fields) { return array(); }

        // Формируем значения полей формы
        foreach ($formObj->form_fields as $key => $field) {
            $formObj->form_fields[$key]['field'] = $formObj->getFormFieldValue($field);
        }

		return $formObj->form_fields;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает массив полей формы
     * @param int $form_id ID формы
     * @param array $values Значения полей
     * @param bool $only_mustbe Показывать только обязательные поля
     * @return array
     */
	public static function getFieldsHtml($form_id, $values = array(), $only_mustbe = false){

		$formObj = new self($form_id,$values);

		if(!$formObj->form || !$formObj->form_fields) { return ''; }

        // Формируем значения полей формы
        foreach ($formObj->form_fields as $key => $field) {
            if($only_mustbe && !$field['mustbe']){ unset($formObj->form_fields[$key]); continue; }
            $formObj->form_fields[$key]['field'] = $formObj->getFormField($field);
        }

		return $formObj->form_fields;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Загружает данные формы в свойство объекта
     */
    private function loadFormData(){

        $this->form = $this->getFormData($this->form_id);

		// если форму получили по имени
		$this->form_id = $this->form ? $this->form['id'] : 0;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает данные формы
     * @param int $form_id ID формы
     * @return array
     */
    public static function getFormData($form_id){

		if(isset(self::$cached_form_data[$form_id])) { return self::$cached_form_data[$form_id]; }

		if(is_numeric($form_id)){
			$where = "id = '{$form_id}'";
		} else {
			$where = "title LIKE '{$form_id}'";
		}

        return cmsCore::callEvent('GET_FORM', cmsDatabase::getInstance()->get_fields('cms_forms', $where, '*'));

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает поля формы
     * @param int $form_id ID формы
     * @return array
     */
    public static function getFormFields($form_id){

		if(isset(self::$cached_form_fields[$form_id])) { return self::$cached_form_fields[$form_id]; }

        $form_fields = array();

        $inDB = cmsDatabase::getInstance();

		$sql = "SELECT * FROM cms_form_fields WHERE form_id = '{$form_id}' ORDER BY ordering ASC";
		$res = $inDB->query($sql);

		if ($inDB->num_rows($res)){

			while($form_field = $inDB->fetch_assoc($res)){

                $form_field['config'] = cmsCore::yamlToArray($form_field['config']);
				$form_fields[] = $form_field;

            }

        }

        return cmsCore::callEvent('GET_FORM_FIELDS', $form_fields);

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает значение поля формы из сессии
     * @param int $field_id ID поля формы
     * @return string
     */
    private function getLastEnteredValue($field_id){

		$ses_value = cmsUser::sessionGet('form_last_'.$this->form_id.'_'.$field_id);

		if ($ses_value){
			cmsUser::sessionDel('form_last_'.$this->form_id.'_'.$field_id);
		}

        return (string)$ses_value;

    }

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает значение поля формы
     * @param int $field_id ID поля формы
     * @return string
     */
	private function getFieldValue($field_id){

		$field_value = array_key_exists($field_id, $this->values) ? htmlspecialchars($this->values[$field_id]) : '';

		return $field_value ? $field_value : $this->getLastEnteredValue($field_id);

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Возвращает html поля формы
     * @param array $form_field Массив поля формы
     * @return string html
     */
	public function getFormField($form_field){

        if(in_array($form_field['kind'], $this->kinds)){
			$method_name = 'render'.icms_ucfirst($form_field['kind']);
            if(method_exists($this, $method_name)){

                return call_user_func_array(array($this, $method_name), array($form_field));

            }
        }

	}
    /**
     * Возвращает значение поля формы
     * @param array $form_field Массив поля формы
     * @return string html
     */
	public function getFormFieldValue($form_field){

        if(in_array($form_field['kind'], $this->kinds)){
			$method_name = 'get'.icms_ucfirst($form_field['kind']).'Value';
            if(method_exists($this, $method_name)){

                return call_user_func_array(array($this, $method_name), array($form_field));

            }

            return (!empty($form_field['config']['text_is_link']) && $form_field['config']['text_link_prefix']) ?
                   cmsPage::getMetaSearchLink($form_field['config']['text_link_prefix'], $this->getFieldValue($form_field['id'])) :
                   $this->getFieldValue($form_field['id']);

        }

	}

// ============================================================================ //
// ========================   Методы полей формы   ============================ //
// ============================================================================ //
	private function getLinkValue($form_field){

        $value = preg_replace ('/[^a-zA-ZА-Яа-я0-9\-_\.\/\:]/ui', '', $this->getFieldValue($form_field['id']));
		$value = htmlspecialchars(str_replace ('..', '.', $value));

		return $value ? '<a href="/go/url=-'.base64_encode($value).'" target="_blank">'.$value.'</a>' : '';

	}

	private function getTextareaValue($form_field){

		return nl2br($this->getFieldValue($form_field['id']));

	}

	private function getFileValue($form_field){

        $link = '';

        if(array_key_exists($form_field['id'], $this->values)){

            $field_value = $this->values[$form_field['id']];

            global $_LANG;

            if (is_file(PATH.$field_value['url'])){

                $downloaded = cmsCore::fileDownloadCount($field_value['url']);

                $filesize = round(filesize(PATH.$field_value['url'])/1024, 2);

                $link = '<span class="filelink">';
                    $link .= '<a href="/load/url=-'.base64_encode($field_value['url']).'" alt="'.$_LANG['FILE_DOWNLOAD'].'">'.$field_value['name'].'</a> ';
                    $link .= '<span>| '.$filesize.' '.$_LANG['SIZE_KB'].'</span> ';
                    $link .= '<span>| '.$_LANG['FILE_DOWNLOADED'].': '.cmsCore::spellCount($downloaded, $_LANG['TIME1'], $_LANG['TIME2'], $_LANG['TIME1']).'</span>';
                $link .= '</span>';

            }

        }

		return $link;

	}

    private function renderText($form_field){

		return '<input type="text"
								  name="field['.$form_field['id'].']"
								  maxlength="'.(int)$form_field['config']['max'].'"
								  value="'.$this->getFieldValue($form_field['id']).'"
								  placeholder="'.htmlspecialchars($form_field['description']).'"
								  style="width: '.(int)$form_field['config']['size'].'px"
								  class="text-input form_text" />';

	}

	private function renderLink($form_field){

		return $this->renderText($form_field);

	}

	private function renderTextarea($form_field){

		return '<textarea name="field['.$form_field['id'].']"
						 class="text-input form_textarea"
						 maxlength="'.(int)$form_field['config']['max'].'"
						 style="width: '.(int)$form_field['config']['size'].'px"
                         placeholder="'.htmlspecialchars($form_field['description']).'"
						 rows="'.(int)$form_field['config']['rows'].'">'.$this->getFieldValue($form_field['id']).'</textarea>';

	}

	private function renderFile($form_field){

        global $_LANG;

        $field_value = $this->values[$form_field['id']];

        if(!empty($field_value['url'])){

            return '<div class="text-input city_block" style="width:'.(int)$form_field['config']['size'].'px">
                <input type="hidden" value="'.htmlspecialchars($field_value['url']).'" name="field['.$form_field['id'].'][url]">
                <input type="hidden" value="'.htmlspecialchars($field_value['name']).'" name="field['.$form_field['id'].'][name]">
                <input type="file" style="width: 73%" class="city_view" name="field['.$form_field['id'].']">
                <label>'.$_LANG['DELETE'].' <input type="checkbox" name="field['.$form_field['id'].'][delete]" value="1"></label>
              </div>';

        } else {

            return '<input type="hidden" value="" name="field['.$form_field['id'].'][url]"><input type="file" style="width: '.(int)$form_field['config']['size'].'px" class="text-input form_file" name="field['.$form_field['id'].']">';

        }

	}

	private function renderCheckbox($form_field){

		global $_LANG;

		$default = $this->getFieldValue($form_field['id']);
        $default = $default ? $default : $form_field['config']['checked'];

		$field  = '<label><input type="radio" name="field['.$form_field['id'].']" value="'.$_LANG['YES'].'" ';
		$field .= ($default || $default == $_LANG['YES']) ? 'checked="checked"' : '';
		$field .= '/>'.$_LANG['YES'].'</label> ';
		$field .= '<label><input type="radio" name="field['.$form_field['id'].']" value="'.$_LANG['NO'].'" ';
		$field .= (!$default || $default == $_LANG['NO']) ? 'checked="checked"' : '';
		$field .= '/>'.$_LANG['NO'].'</label> ';

		return $field;

	}

	private function renderRadiogroup($form_field){

		$field = '';

		$items   = explode('/', trim($form_field['config']['items']));
		$default = $this->getFieldValue($form_field['id']);

		if($items){
			foreach($items as $i){

				$i = trim(htmlspecialchars($i));

				$field .= '<label><input type="radio" name="field['.$form_field['id'].']" value="'.$i.'" ';
				if($i == $default) { $field .= 'checked="checked"'; }
				$field .= ' />'.$i.'</label><br/>';

			}
		}

		return $field;

	}

	private function renderList($form_field){

        $field = '';

		$items   = explode('/', trim($form_field['config']['items']));
		$default = $this->getFieldValue($form_field['id']);

		if($items){

            $field .= '<select class="text-input form_list" style="width: '.(int)$form_field['config']['size'].'px" name="field['.$form_field['id'].']">';

			foreach($items as $i){

				$i = trim(htmlspecialchars($i));

                $field .= '<option value="'.$i.'"';
                if($i == $default) { $field .= 'selected="selected"'; }
                $field .= ' >'.$i.'</option>';

			}

            $field .= '</select>';

		}

		return $field;

	}

	private function renderMenu($form_field){

        $field = '';

		$items   = explode('/', trim($form_field['config']['items']));
		$default = $this->getFieldValue($form_field['id']);

		if($items){

            $field .= '<select class="text-input form_menu" style="width: '.(int)$form_field['config']['size'].'px" name="field['.$form_field['id'].']" size="5">';

			foreach($items as $i){

				$i = trim(htmlspecialchars($i));

                $field .= '<option value="'.$i.'"';
                if($i == $default) { $field .= 'selected="selected"'; }
                $field .= ' >'.$i.'</option>';

			}

            $field .= '</select>';

		}

		return $field;

	}
// ============================================================================ //
// ============================================================================ //

}

?>