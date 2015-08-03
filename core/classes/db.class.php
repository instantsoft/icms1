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

class cmsDatabase {

    private static $instance;

    public $q_count = 0;
    public $q_dump  = array();

    public $join     = '';
    public $select   = '';
    public $where    = '';
    public $group_by = '';
    public $order_by = '';
    public $limit    = '1000';
    public $page     = 1;
    public $perpage  = 10;

	private $cache = array(); // кеш некоторых запросов

    public $db_link;
    private $db_prefix;

// ============================================================================ //
// ============================================================================ //

	protected function __construct(){
		$this->db_link   = static::initConnection();
        $this->db_prefix = cmsConfig::getConfig('db_prefix').'_';
	}
	public function __destruct(){
		mysqli_close($this->db_link);
	}

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
	 * Реинициализирует соединение с базой
	 */
	public static function reinitializedConnection(){

        $db = self::getInstance();

        if (!mysqli_ping($db->db_link)){
            if(!empty($db->db_link)){
                mysqli_close($db->db_link);
            }
            $db->db_link = self::initConnection();
        }

		return true;

	}

// ============================================================================ //
// ============================================================================ //
	/**
	 * Устанавливает соединение с базой
     * @return resource $db_link
	 */
	protected static function initConnection(){

		$inConf = cmsConfig::getInstance();

		$db_link = mysqli_connect($inConf->db_host, $inConf->db_user, $inConf->db_pass, $inConf->db_base);

        if (mysqli_connect_errno()) {
           die('Cannot connect to MySQL server: ' . mysqli_connect_error());
        }

		mysqli_set_charset($db_link, 'utf8');

		return $db_link;

	}

// ============================================================================ //
// ============================================================================ //
	/**
	 * Сбрасывает условия
	 */
    public function resetConditions(){

        $this->where    = '';
		$this->select   = '';
		$this->join     = '';
        $this->group_by = '';
        $this->order_by = '';
        $this->limit    = '1000';

        return $this;

    }

    public function addJoin($join){
        $this->join .= $join . "\n"; return $this;
    }

    public function addSelect($condition){
        $this->select .= ', '.$condition; return $this;
    }

    public function where($condition){
        $this->where .= ' AND ('.$condition.')' . "\n"; return $this;
    }

    public function groupBy($field){
        $this->group_by = 'GROUP BY '.$field; return $this;
    }

    public function orderBy($field, $direction='ASC'){
        $this->order_by = 'ORDER BY '.$field.' '.$direction; return $this;
    }

    public function limit($howmany) {
        return $this->limitIs(0, $howmany);
    }

    public function limitIs($from, $howmany='') {
        $this->limit = (int)$from;
        if ($howmany){
            $this->limit .= ', '.$howmany;
        }
        return $this;
    }

    public function limitPage($page, $perpage){
		$this->page = $page; $this->perpage = $perpage;
        return $this->limitIs(($page-1)*$perpage, $perpage);
    }

// ============================================================================ //
// ============================================================================ //

	protected function replacePrefix($sql, $prefix='cms_'){
        if($prefix == $this->db_prefix){
            return trim($sql);
        }
		return trim(str_replace($prefix, $this->db_prefix, $sql));
	}

// ============================================================================ //
// ============================================================================ //

	public function query($sql, $ignore_errors=false, $replace_prefix = true){

        if (empty($sql)) { return false; }

        $sql = $replace_prefix ? $this->replacePrefix($sql) : $sql;

        $start_time = microtime(true);

		$result = mysqli_query($this->db_link, $sql);

		if (cmsConfig::getConfig('debug')){

            $this->q_count++;

            $trace = debug_backtrace();

            if ((isset($trace[1]['file']) || isset($trace[0]['file'])) && isset($trace[1]['function'])){
                $src = (isset($trace[1]['file']) ? $trace[1]['file'] : $trace[0]['file']) .' => '. $trace[1]['function'] . '()';
                $src = str_replace(PATH, '', $src);
            } else {
                $src = '';
            }

            $this->q_dump[] = array('sql'=>$sql, 'src'=>$src, 'time'=>(microtime(true) - $start_time));

		}

		if (cmsConfig::getConfig('debug') && !$ignore_errors){
            $error = $this->error();
            if($error){
                die('<h3>DATABASE ERROR:</h3><pre>'.$sql.'</pre><p>'.$error.'</p>');
            }
		}

		return $result;

	}

// ============================================================================ //
// ============================================================================ //
	public function num_rows($result){
		return mysqli_num_rows($result);
	}
// ============================================================================ //
// ============================================================================ //
	public function fetch_assoc($result){
		return mysqli_fetch_assoc($result);
	}
// ============================================================================ //
// ============================================================================ //
	public function fetch_row($result){
		return mysqli_fetch_row($result);
	}

// ============================================================================ //
// ============================================================================ //
    public function free_result($result){
		return mysqli_free_result($result);
	}
// ============================================================================ //
// ============================================================================ //
	public function fetch_all($result){
	  $array = array();
	  if ($this->num_rows($result)){
		while ($object = mysqli_fetch_object($result)){
		  $array[] = $object;
		}
	  }
	  return $array;
	}
// ============================================================================ //
// ============================================================================ //
	public function affected_rows(){
		return mysqli_affected_rows($this->db_link);
	}
// ============================================================================ //
// ============================================================================ //

	public function get_last_id($table=''){

        if(!$table){
            return (int)mysqli_insert_id($this->db_link);
        }

		$result = $this->query("SELECT LAST_INSERT_ID() as lastid FROM $table LIMIT 1");

		if ($this->num_rows($result)){
			$data = $this->fetch_assoc($result);
			return $data['lastid'];
		} else {
			return 0;
		}

	}

// ============================================================================ //
// ============================================================================ //

	public function rows_count($table, $where, $limit=0){

		$sql = "SELECT 1 FROM $table WHERE $where";
		if ($limit) { $sql .= " LIMIT ".(int)$limit; }
		$result = $this->query($sql);

		return $this->num_rows($result);

	}

// ============================================================================ //
// ============================================================================ //

	public function get_field($table, $where, $field){

		$sql    = "SELECT $field as getfield FROM $table WHERE $where LIMIT 1";
		$result = $this->query($sql);

		if ($this->num_rows($result)){
			$data = $this->fetch_assoc($result);
			return $data['getfield'];
		} else {
			return false;
		}

	}

// ============================================================================ //
// ============================================================================ //

	public function get_fields($table, $where, $fields='*', $order='id ASC'){

		$sql    = "SELECT $fields FROM $table WHERE $where ORDER BY $order LIMIT 1";
		$result = $this->query($sql);

		if ($this->num_rows($result)){
			$data = $this->fetch_assoc($result);
			return $data;
		} else {
			return false;
		}
	}

// ============================================================================ //
// ============================================================================ //

	public function get_table($table, $where='', $fields='*'){

		$list = array();

		$sql = "SELECT $fields FROM $table";
		if ($where) { $sql .= ' WHERE '.$where; }
		$result = $this->query($sql);

		if ($this->num_rows($result)){
			while($data = $this->fetch_assoc($result)){
				$list[] = $data;
			}
			return $list;
		} else {
			return false;
		}

	}

// ============================================================================ //
// ============================================================================ //
	public function errno() {
		return mysqli_errno($this->db_link);
	}
// ============================================================================ //
// ============================================================================ //
	public function error() {
		return mysqli_error($this->db_link);
	}
// ============================================================================ //
// ============================================================================ //
	public function escape_string($value){

        if(is_array($value)){

            foreach ($value as $key=>$string) {
                $value[$key] = $this->escape_string($string);
            }

            return $value;

        }

		return mysqli_real_escape_string($this->db_link, stripcslashes($value));

	}
// ============================================================================ //
// ============================================================================ //

	public function isFieldExists($table, $field){

		$sql    = "SHOW COLUMNS FROM $table WHERE Field = '$field'";
		$result = $this->query($sql);

		if ($this->errno()) { return false; }

		return (bool)$this->num_rows($result);

	}

// ============================================================================ //
// ============================================================================ //

	public function isFieldType($table, $field, $type){

		$sql    = "SHOW COLUMNS FROM $table WHERE Field = '$field' AND Type = '$type'";
		$result = $this->query($sql);

		if ($this->errno()) { return false; }

		return (bool)$this->num_rows($result);

	}

// ============================================================================ //
// ============================================================================ //

	public function isTableExists($table){

		$this->query("SELECT 1 FROM $table LIMIT 1", true);

		if ($this->errno()){ return false; }

		return true;

	}

// ============================================================================ //
// ============================================================================ //

	public static function optimizeTables($tlist=''){

		$inDB = self::getInstance();

		if(is_array($tlist)) {

			foreach($tlist as $tname) {
				$inDB->query("OPTIMIZE TABLE $tname", true);
				$inDB->query("ANALYZE TABLE $tname", true);
			}

		} else if($inDB->isTableExists('information_schema.tables')) {

            $base = cmsConfig::getConfig('db_base');

			$tlist  = $inDB->get_table('information_schema.tables', "table_schema = '{$base}'", 'table_name');

			if (!is_array($tlist)) { return false; }

			foreach($tlist as $tname) {
				$inDB->query("OPTIMIZE TABLE {$tname['table_name']}", true);
				$inDB->query("ANALYZE TABLE {$tname['table_name']}", true);
			}

		}

		if ($inDB->errno()){ return false; }

		return true;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Добавляет массив записей в таблицу
	 * ключи массива должны совпадать с полями в таблице
     */
	public function insert($table, $insert_array, $ignore=false){

		// убираем из массива ненужные ячейки
		$insert_array = $this->removeTheMissingCell($table, $insert_array);
		$set = '';
		// формируем запрос на вставку в базу
		foreach($insert_array as $field=>$value){
			$set .= "{$field} = '{$value}',";
		}
		// убираем последнюю запятую
		$set = rtrim($set, ',');

        $i = $ignore ? 'IGNORE' : '';

		$this->query("INSERT {$i} INTO {$table} SET {$set}");

		if ($this->errno()) { return false; }

		return $this->get_last_id($table);

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Обновляет данные в таблице
	 * ключи массива должны совпадать с полями в таблице
     */
	public function update($table, $update_array, $id){

        if(isset($update_array['id'])){
            unset($update_array['id']);
        }

        // id или where
        if(is_numeric($id)){
            $where = "id = '{$id}' LIMIT 1";
        } else {
            $where = $id;
        }

		// убираем из массива ненужные ячейки
		$update_array = $this->removeTheMissingCell($table, $update_array);

		$set = '';
		// формируем запрос на вставку в базу
		foreach($update_array as $field=>$value){
			$set .= "{$field} = '{$value}',";
		}
		// убираем последнюю запятую
		$set = rtrim($set, ',');

		$this->query("UPDATE {$table} SET {$set} WHERE $where");

		if ($this->errno()) { return false; }

		return true;

	}

// ============================================================================ //
// ============================================================================ //
    /**
     * Убирает из массива ячейки, которых нет в таблице назначения
	 * используется при вставке/обновлении значений таблицы
     */
	public function removeTheMissingCell($table, $array){

		$result = $this->query("SHOW COLUMNS FROM `{$table}`");
		$list = array();
        while($data = $this->fetch_assoc($result)){
            $list[$data['Field']] = '';
        }
		// убираем ненужные ячейки массива
		foreach($array as $k=>$v){
		   if (!isset($list[$k])) { unset($array[$k]); }
		}

		if(!$array || !is_array($array)) { return array(); }

		return $array;

	}

// ============================================================================ //
// ============================================================================ //

	public function delete($table, $where='', $limit=0) {

		$sql = "DELETE FROM {$table} WHERE {$where}";

		if ($limit) { $sql .= " LIMIT {$limit}"; }

		$this->query($sql, true);

		if ($this->errno()){ return false; }

		return true;

	}

// ============================================================================ //
// ============================================================================ //

    public function setFlag($table, $id, $flag, $value) {
        $this->query("UPDATE {$table} SET {$flag} = '{$value}' WHERE id='{$id}'");
        return $this;
    }

    public function setFlags($table, $items, $flag, $value) {
        foreach($items as $id){
            $this->setFlag($table, $id, $flag, $value);
        }
        return $this;
    }

// ============================================================================ //
// ============================================================================ //

	public function deleteNS($table, $id, $differ='') {

		return cmsCore::getInstance()->nestedSetsInit($table)->DeleteNode($id, $differ);;

	}

// ============================================================================ //
// ============================================================================ //

	public function getNsRootCatId($table, $differ = '') {

		if(isset($this->cache[$table][$differ])) { return $this->cache[$table][$differ]; }

		$root_cat = $this->getNsCategory($table, 0, $differ);

		return $root_cat ? ($this->cache[$table][$differ] = $root_cat['id']) : false;

	}

// ============================================================================ //
// ============================================================================ //

	public function getNsCategory($table, $cat_id_or_link=0, $differ='') {

		if(isset($this->cache[$table][$cat_id_or_link][$differ])) { return $this->cache[$table][$cat_id_or_link][$differ]; }

        if (!$cat_id_or_link){

            $where = 'NSLevel = 0';

        } else {

			if(is_numeric($cat_id_or_link)){ // если пришла цифра, считаем ее cat_id

				$where = "id = '$cat_id_or_link'";

			} else {

				$where = "seolink = '$cat_id_or_link'";

			}

        }

		if(isset($differ)) { $where .= " AND NSDiffer = '{$differ}'"; }

		$cat = $this->get_fields($table, $where, '*');

		return $cat ? $this->cache[$table][$cat_id_or_link][$differ] = $cat : false;

	}

// ============================================================================ //
// ============================================================================ //

    public function moveNsCategory($table, $cat_id, $dir='up') {

        $ns = cmsCore::getInstance()->nestedSetsInit($table);

        if ($dir == 'up'){
            $ns->MoveOrdering($cat_id, -1);
        } else {
            $ns->MoveOrdering($cat_id, 1);
        }

        return true;

    }

// ============================================================================ //
// ============================================================================ //

	public function addNsCategory($table, $cat, $differ=''){

		$cat_id = cmsCore::getInstance()->nestedSetsInit($table)->AddNode($cat['parent_id'], -1, $differ);
		if(!$cat_id) { return false; }

        $this->update($table, $cat, $cat_id);

        return $cat_id;

    }

// ============================================================================ //
// ============================================================================ //

	public function addRootNsCategory($table, $differ='', $cat){

		$cat_id = cmsCore::getInstance()->nestedSetsInit($table)->AddRootNode($differ);
		if(!$cat_id) { return false; }

        $this->update($table, $cat, $cat_id);

        return $cat_id;

    }

// ============================================================================ //
// ============================================================================ /

    public function getNsCategoryPath($table, $left_key, $right_key, $fields='*', $differ='', $only_nested = false) {

		$nested_sql = $only_nested ? '' : '=';

        $path = $this->get_table($table, "NSLeft <$nested_sql $left_key AND NSRight >$nested_sql $right_key AND parent_id > 0 AND NSDiffer = '{$differ}' ORDER BY NSLeft", $fields);

        return $path;

    }

// ============================================================================ //
// ============================================================================ /
    /**
     * Обновляет ссылку на категорию и вложенные в нее
     * Подразумевается, что заголовок категории или поле url изменен заранее
     * @return bool
     */
    public function updateNsCategorySeoLink($table, $cat_id, $is_url_cyrillic = false){

		// получаем изменяемую категорию
		$cat = $this->getNsCategory($table, $cat_id);
		if(!$cat) { return false; }
		// обновляем для нее сеолинк
		$cat_seolink = cmsCore::generateCatSeoLink($cat, $table, $is_url_cyrillic);
		$this->query("UPDATE {$table} SET seolink='{$cat_seolink}' WHERE id = '{$cat['id']}'");

		// Получаем вложенные категории для нее
        $path_list = $this->get_table($table, "NSLeft > {$cat['NSLeft']} AND NSRight < {$cat['NSRight']} AND parent_id > 0 ORDER BY NSLeft");

        if ($path_list){
            foreach($path_list as $pcat){
				$subcat_seolink = cmsCore::generateCatSeoLink($pcat, $table, $is_url_cyrillic);
				$this->query("UPDATE {$table} SET seolink='{$subcat_seolink}' WHERE id = '{$pcat['id']}'");
            }
        }

        return true;

    }
// ============================================================================ //
// ============================================================================ //
    /**
     * Выполняет SQL из файла
     * @param str $sql_file Полный путь к файлу
     * @return bool
     */
    public function importFromFile($sql_file) {

        if (!file_exists($sql_file)){ return false; }

        $lines = file($sql_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $buffer = '';

        foreach ($lines as $line) {

            if (($line = trim($line)) == '') {
                continue;
            }
            if (mb_substr(ltrim($line), 0, 2) == '--') {
                continue;
            }
            // sql в несколько строк
            if (mb_substr($line, -1) != ';') {
                // добавляем в буфер
                $buffer .= $line;
                // считываем следующую строку
                continue;
            } else {
                if ($buffer) {
                    $line = $buffer . $line;
                    // сбрасываем буфер
                    $buffer = '';
                }
            }

            $line = mb_substr($line, 0, -1);

            $result = $this->query(str_replace("#_", cmsConfig::getConfig('db_prefix'), $line), false, false);

            if (!$result) {
                die('DATABASE ERROR: <pre>'.$line.'</pre><br>'.$this->error());
            }

        }

        return true;

    }
// ============================================================================ //
}