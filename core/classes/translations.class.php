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

class translations {

    private $inDB;

    private $fieldsset_id = 0;
    private $fields       = array();
    private $lang         = '';
    private $id_field;
    private $field_compliance;

    private static $instances = array();

// ========================================================================== //

    private function __construct($target){

        $this->inDB = cmsDatabase::getInstance();
        $this->loadFieldSet($target);

    }

    private static function init($target, $lang='', $id_field = 'id', $field_compliance=array()) {
        if (!isset(self::$instances[$target])) {
            self::$instances[$target] = new self($target, $id_field, $field_compliance);
        }
        self::$instances[$target]->lang = $lang;
        self::$instances[$target]->id_field = $id_field;
        self::$instances[$target]->field_compliance = $field_compliance;
        return self::$instances[$target];
    }

    private function loadFieldSet($target) {

        $f = $this->inDB->get_fields('cms_translations_fields', "target = '$target'");

        if($f){

            $this->fieldsset_id = $f['id'];
            $this->fields       = cmsCore::yamlToArray($f['fields']);

        }

    }

    /**
     * Регистрирует поля подлежащие переводу
     * @param str $target Цель
     * @param array $fields массив подлежащих переводу полей и их типов 'поле'=>'str|html'
     */
    public static function registerFields($target, $fields) {

        cmsDatabase::getInstance()->insert('cms_translations_fields', array(
            'target'=>$target,
            'fields'=>cmsCore::arrayToYaml($fields)
        ), true);

    }

    public static function getFields($target) {

        return self::init($target)->fields;

    }

    public static function getFieldsetId($target) {

        return self::init($target)->fieldsset_id;

    }

    /**
     * Ищет и применяет перевод для возможных полей
     * @param str $lang Язык возвращаемого перевода
     * @param str $target Для какой цели ищем
     * @param array $item Массив, в котором нужно заменить значения полей
     * @return array
     */
    public static function process($lang, $target, $item, $id_field = 'id', $field_compliance=array()) {

        if(!cmsConfig::getConfig('is_change_lang')){
            return $item;
        }

        $thisObj = self::init($target, $lang, $id_field, $field_compliance);

        if(!$thisObj->fieldsset_id || !$item || !is_array($item)){
            return $item;
        }

        return $thisObj->get($item);

    }

    /**
     *
     * @param str $lang  Язык перевода
     * @param str $target Для какой цели ищем
     * @param int $target_id ID цели
     * @return mixed
     */
    public static function getTranslation($lang, $target, $target_id) {

        $thisObj = self::init($target, $lang);

        if(!$thisObj->fieldsset_id || !$target_id){
            return false;
        }

        $sql    = "SELECT * FROM cms_translations WHERE target_id = '{$target_id}' AND fieldsset_id = '{$thisObj->fieldsset_id}' AND lang = '{$thisObj->lang}' LIMIT 1";
		$result = $thisObj->inDB->query($sql);

		if (!$thisObj->inDB->num_rows($result)){
            return false;
        }

        $d = $thisObj->inDB->fetch_assoc($result);
        $d['data'] = cmsCore::yamlToArray($d['data']);

        return $d;

    }

    public static function deleteTargetTranslation($target, $target_id) {

        $thisObj = self::init($target);

        if(!$thisObj->fieldsset_id || !$target_id){
            return false;
        }

        $thisObj->inDB->query("DELETE FROM cms_translations WHERE target_id = '{$target_id}' AND fieldsset_id = '{$thisObj->fieldsset_id}'");

    }

    private function get($item) {

        // флаг, что массив многомерный
        $first = current($item);
        $is_multidimensional = is_array($first);

        // простой массив
        if(!$is_multidimensional && !empty($item[$this->id_field])){
            $where = "target_id = '{$item[$this->id_field]}'";
        }

        // многомерный массив
        else {
            foreach ($item as $k=>$i) {
                if(!empty($i[$this->id_field])){
                    $ids[$k] = $i[$this->id_field];
                }
            }
            if(!empty($ids)){
                $target_ids = array_unique($ids);
                $where = 'target_id IN('.implode(',', $target_ids).')';
            }
        }

        if(empty($where)){
            return $item;
        }

        $sql    = "SELECT target_id, data FROM cms_translations WHERE {$where} AND fieldsset_id = '{$this->fieldsset_id}' AND lang = '{$this->lang}'";
		$result = $this->inDB->query($sql);

		if (!$this->inDB->num_rows($result)){
            return $item;
        }

        // простой массив
        if(!$is_multidimensional){

            $d    = $this->inDB->fetch_assoc($result);
            $data = $this->processData($d['data']);

            return array_merge($item, $data);

        }
        // многомерный массив
        else {

            while($d = $this->inDB->fetch_assoc($result)){
                $translations[$d['target_id']] = $this->processData($d['data']);
            }

            foreach ($ids as $key=>$value) {
                if(isset($item[$key]) && isset($translations[$value])){
                    // меняем поля
                    $item[$key] = array_merge($item[$key], $translations[$value]);
                }
            }

            return $item;

        }


    }

    private function processData($data) {

        $data = cmsCore::yamlToArray($data);

        // меняем названия полей при необходимости
        if($this->field_compliance){
            $intersect = array_intersect_key($data, $this->field_compliance);
            $data = array_combine(array_values($this->field_compliance), array_values($intersect));
        }

        return $data;

    }

}