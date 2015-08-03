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

class cms_model_search{

	protected static $instance;

	public $query      = ''; // поисковый запрос
	public $look       = ''; // тип поиска
	protected $against = ''; // подготовленная строка для запроса
	public $words      = ''; // подготовленные слова запроса
	public $parametrs_array = array(); // массив параметров запроса

	public $page          = 1; // текущая страница
	public $order_by_date = 0; // сортировка по дате публикации
	public $from_pubdate  = ''; // интервал вывода результатов
	public $from_component = array(); // массив линков компонентов, в которых проводить поиск

	public $components = array(); // массив компонентов, в которых есть поддержка поиска фултекст
	public $config     = array(); // конфигурация компонента

	private $default_array_result = array('session_id'=>'','pubdate'=>'','title'=>'','description'=>'','link'=>'','place'=>'','placelink'=>'','imageurl'=>''); // поля результатов поиска

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function __construct(){
		$this->inDB   = cmsDatabase::getInstance();
		$this->inCore = cmsCore::getInstance();
		$this->query  = self::getQuery();
		$this->look   = cmsCore::request('look', 'str', 'allwords');
		$this->page   = cmsCore::request('page', 'int', 1);
		$this->from_pubdate = cmsCore::request('from_pubdate', 'str', '');
		$this->order_by_date = $this->query ? cmsCore::request('order_by_date', 'int', 0) : 1;
		$this->from_component = self::getComponentsArrayForSearch();
		$this->config = $this->inCore->loadComponentConfig('search');
		cmsCore::loadLib('tags');
		$this->getComponentsWithSupportSearch();
		$this->parametrs_array = $this->gatherAnArrayOfQueryParametrs();
	}

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Инициализирует и возвращает объект модели
	 * Singleton
     * @return obj $instance
     */
    public static function initModel() {

        if (self::$instance === null) {

			// Загружаем конфигурацию компонента
			$config = cmsCore::getInstance()->loadComponentConfig('search');;
			// Инициализируем класс провайдера
			if (self::loadProviderClass($config['search_engine'])){
				self::$instance = new $config['search_engine']();
			} else {
				self::$instance = new self;
			}

        }
        return self::$instance;

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Загружает класс провайдера
     * @return bool
     */
    public static function loadProviderClass($provider) {

		if(!$provider) { return false; }

		$class_file = PATH.'/components/search/providers/'.$provider.'.php';

		if (file_exists($class_file)){
			require_once($class_file);
			return true;
		} else {
			return false;
		}

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Возврящает конфигурацию по умолчанию для компонента
     * @return array $cfg
     */
    public static function getDefaultConfig() {

        $cfg = array(
                     'search_engine'=>'',
                     'perpage'=>15,
                     'comp'=>array()
               );

        return $cfg;

    }

/* ========================================================================== */
/* ========================================================================== */
    /**
     * Получает строку запроса из формы
	 * убирает ненужное из нее
     * @return str $query
     */
    private static function getQuery() {

		$query  = strip_tags(urldecode(mb_strtolower(cmsCore::request('query', 'html', ''))));
		// убираем двойные пробелы
		$query  = preg_replace('/\s+/u', ' ', $query);
		// ограничиваем строку запроса
		return cmsDatabase::getInstance()->escape_string(mb_substr($query, 0, 100));

    }
/* ========================================================================== */
/* ========================================================================== */
    /**
     * Формирует массив параметров запроса
     * @return array $parametrs
     */
    private function gatherAnArrayOfQueryParametrs() {

		$parametrs = array(
					 'query'=>$this->query,
					 'look'=>$this->look,
					 'from_component'=>$this->from_component
		);

		return $parametrs;

    }
/* ========================================================================== */
/* ========================================================================== */
    /**
     * Получает массив компонентов для поиска
     * @return array
     */
    protected static function getComponentsArrayForSearch() {

		// получаем саму строку запроса из GET
		$from_component = cmsCore::request('from_component', 'array_str');
		if(!$from_component || !is_array($from_component)) { return array(); }
		// убираем повторяющииеся элементы
		$from_component = @array_unique($from_component);
		// удаляем пустые элементы массива
		// линки массива должны содержать только буквы
		if($from_component){
			foreach ($from_component as $key=>$val){
			   if(!$from_component[$key]){
				  unset($from_component[$key]);
			   } else {
				   $from_component[$key] = preg_replace('/[^a-z_]/ui', '', $from_component[$key]);
			   }
			}
		}

		return $from_component;

    }

/* ========================================================================== */
/* ========================================================================== */

    public function wherePeriodIs(){

		$today = date("d-m-Y");

		switch ($this->from_pubdate){

			case 'd': $this->inDB->where("DATE_FORMAT(pubdate, '%d-%m-%Y')='$today'"); break;
			case 'w': $this->inDB->where("DATEDIFF(NOW(), pubdate) <= 7"); break;
			case 'm': $this->inDB->where("DATE_SUB(NOW(), INTERVAL 1 MONTH) < pubdate"); break;
			case 'y': $this->inDB->where("DATE_SUB(NOW(), INTERVAL 1 YEAR) < pubdate"); break;
			default: return;

		}
        return;
    }

    public function whereSessionIs($session_id){

		$this->inDB->where("session_id = '{$session_id}'");

        return;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает массив результатов поиска (постранично)
	 * выполняя подсветку искомых слов
     * @return array $rs
     */
    public function getResults() {

        $sql = "SELECT pubdate, title, description, link, place, placelink, imageurl
                FROM cms_search
				WHERE 1=1 {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by}
				LIMIT ".(($this->page-1)*$this->config['perpage']).", {$this->config['perpage']}";

		$result = $this->inDB->query($sql);

		$this->inDB->resetConditions();

		if(!$this->inDB->num_rows($result)){ return false; }

        $rs = array();

		while ($res = $this->inDB->fetch_assoc($result)){

			$res['pubdate'] = $res['pubdate'] ? cmsCore::dateFormat($res['pubdate'], true, false, false) : '';
			// заголовок с учетом подсветки слов поиска
			$res['s_title'] = $this->getHighlightedText($res['title']);
			// описание с учетом подсветки слов поиска
			$res['description'] = $this->getHighlightedText($res['description']);

			$rs[] = $res;

		}

        return cmsCore::callEvent('GET_SEARCH_RESULT', $rs);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает общее количество результатов поиска
     * @return int
     */
    public function getCountResults() {

        $sql = "SELECT 1
                FROM cms_search
                WHERE 1=1 {$this->inDB->where}
                {$this->inDB->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }
// ============================================================================ //
// ============================================================================ //
    protected function checkArrayResults($input_array = array()) {

		// убираем ненужные ячейки массива
		foreach($input_array as $k=>$v){
		   if (!isset($this->default_array_result[$k])) { unset($input_array[$k]); }
		}

		if(!$input_array || !is_array($input_array)) { return array(); }

		return $input_array;

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Добавляет результат поиска в таблицу
     * @return bool
     */
    public function addResult($item = array()) {

		// проверяем правильность полей для добавления
		$item = $this->checkArrayResults($item);
		if(!$item) { return false; }
		// если такая запись уже есть, возвращаемся
		if ($this->inDB->rows_count('cms_search', "session_id='{$item['session_id']}' AND link='{$item['link']}'")){
			 return false;
		}

        $set = '';
		foreach($item as $field=>$value){
			$set .= "{$field} = '{$this->inDB->escape_string($value)}',";
		}

		$set = rtrim($set, ',');

		$this->inDB->query("INSERT INTO cms_search SET {$set}");

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает общее количество результатов поиска по тегу
     * @return int
     */
    public function getCountTags() {

		return $this->inDB->rows_count('cms_tags', "tag = '{$this->query}'");

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает массив результатов поиска (постранично) для тегов
     * @return array $rs
     */
    public function searchByTag() {

        $sql = "SELECT *
                FROM cms_tags
                WHERE tag = '{$this->query}' ORDER BY tag DESC LIMIT ".(($this->page-1)*$this->config['perpage']).", {$this->config['perpage']}";

		$result = $this->inDB->query($sql);

		if(!$this->inDB->num_rows($result)){ return false; }

        $rs = array();

		$row = 1;
		while ($res = $this->inDB->fetch_assoc($result)){
			if ($itemlink = cmsTagItemLink($res['target'], $res['item_id'])){
				$res['itemlink'] = $itemlink;
				$res['tag_bar']  = cmsTagBar($res['target'], $res['item_id'], $this->query);
				if($row % 2 == 0) { $res['class'] = 'search_row2'; } else { $res['class'] = 'search_row1'; }
				$rs[] = $res;
				$row++;
			}
		}

        return $rs;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Проверяет, изменились ли параметры запроса
     * @return bool
     */
    public function isChangedParams() {

		return $this->parametrs_array == cmsUser::sessionGet('query_params');

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Подсвечивает искомые слова в тексте
     * @return str $text
     */
    protected function getHighlightedText($text) {

		if (!cmsUser::sessionGet('searchquery')){ return $text; }

		$q = cmsUser::sessionGet('searchquery');

		if ($this->look == 'phrase'){
            $text = preg_replace('/('.$q.')/iu','<strong class="search_match">$1</strong>', $text);
		} elseif($this->look == 'allwords') {
			$text = preg_replace('/('.$q.')/iu','<strong class="search_match">$1</strong>', $text);
			$words = explode(' ', $q);
			foreach($words as $w){
                if (mb_strlen($w) > 0){
                    $text = preg_replace('/('.$w.')/iu','<strong class="search_match">$1</strong>', $text);
                }
			}
		} else {
			$words = explode(' ', $q);
			foreach($words as $w){
                if (mb_strlen($w) > 0){
                    $text = preg_replace('/('.$w.')/iu','<strong class="search_match">$1</strong>', $text);
                }
			}
		}

		return $text;

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Находит в тексте предложение с искомым словом
	 * и возвращает первое найденное
     * @return str $proposal
     */
    public function getProposalWithSearchWord($text) {

		if(!$text) { return ''; }

		// убираем ненужное с текста
		$text = strip_tags($text);
		$text = preg_replace('/\s+/u', ' ', $text);

		// формируем массив предложений из текста
		$text_array = explode('.', $text);
		// формируем массив слов запроса
		$words_array = explode(' ', $this->words);

		$result_array = array();
		foreach($words_array as $w){
			$result_array = preg_grep('/'.$w.'/iu', $text_array);
			if($result_array) { break; }
		}

		$proposal = implode('. ', $result_array);

		return $proposal;

    }

/* ========================================================================================================= */
/* ========================================================================================================= */
    /**
     * Процессор поиска
     * @return bool
     */
    public function prepareSearch() {

		// формируем запрос
		$this->getAgainst();

		// получаем массив разрешенных к поиску компонентов
		$enable_components = $this->getEnableComponentsWithSupportSearch();

		// если кол-во компонентов в которых нужно искать по запросу
		// совпадает с количеством компонентов, разрешенных к поиску
		// считаем, что запроса $this->from_component не было и ищем
		// по всем разрешенным компонентам
		if(count($this->from_component) == count($enable_components)){ $this->from_component = array(); }

		// если пришел запрос на поиск в определенных компонентах
		if($this->from_component){

			foreach($this->from_component as $component){
				if (in_array($component, $this->config['comp'])){
					$reply = $this->callComponentSearch($component);
				}
			}

		} else { // выполняем поиск по все включенным компонентам

			foreach($enable_components as $component){
				$reply = $this->callComponentSearch($component['link']);
			}

		}

        return $reply;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Вызывает функцию поиска для компонента
     * @return bool
     */
    protected function callComponentSearch($component) {

		if (file_exists(PATH.'/components/'.$component.'/psearch.php')){
			include_once PATH.'/components/'.$component.'/psearch.php';
			$search_func = 'search_' . $component;
			if(function_exists($search_func)){
				call_user_func($search_func, $this->against, $this->look);
				return true;
			}
		}

        return false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет результаты поиска для текущей сессии
     * @return bool
     */
    public function deleteResultsFromThisSession() {

        $this->inDB->query("DELETE FROM cms_search WHERE session_id = '".session_id()."'");

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет записи в кеше поиска старее 1 дня
     * @return bool
     */
    public function deleteOldResults() {

        $this->inDB->query("DELETE FROM cms_search WHERE DATEDIFF(NOW(), date) > 1");

        return true;

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Полностью очищает таблицу cms_search
     * @return bool
     */
    public function truncateResults() {

        $this->inDB->query("TRUNCATE TABLE `cms_search`");

        return true;

    }
/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Подготавливает строку поиска для sql запроса
	 * формирует $this->against и $this->words
     * @return bool
     */
    protected function getAgainst() {

		// если уже получали, возвращаемся
		if($this->against && $this->words) { return true; }

		// Подключаем стеммер Портера
		$this->inCore->includeFile('includes/stemmer/stemmer.php');
		$stemmer = new Lingua_Stem_Ru();
		// формируем массив слов
		$words = explode(' ', $this->query);
		// любое слово
		if ($this->look == 'anyword'){
			foreach($words as $w){
				$w = trim($w);
				if(mb_strlen($w)>3){
					if(mb_strlen($w)==4){
						$this->against .= $w.'* ';
						$this->words   .= $w.' ';
					} else {
						$this->against .= $stemmer->stem_word($w).'* ';
						$this->words   .= $stemmer->stem_word($w).' ';

					}
				}
			}
		}
		// все слова
		if ($this->look == 'allwords'){
			$this->against  = '>\"'.$this->query.'\" ';
			$this->against .= '<(';
			foreach($words as $w){
				$w = trim($w);
				if(mb_strlen($w)>3){
					if(mb_strlen($w)==4){
						$this->against .= '+'.$w.'* ';
						$this->words   .= $w.' ';
					} else {
						$this->against .= '+'.$stemmer->stem_word($w).'* ';
						$this->words   .= $stemmer->stem_word($w).' ';

					}
				}
			}
			$this->against .= ')';
		}
		// фраза целиком
		if ($this->look == 'phrase'){
			$this->against = '\"'.$this->query.'\"';
			$this->words   = $this->query;
		}

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Формирует массив компонентов, у которых есть поддержка поиска фултекст
	 * формирует $this->components
     * @return bool
     */
    private function getComponentsWithSupportSearch() {

		// если уже получали, возвращаемся
		if($this->components && is_array($this->components)) { return true; }

		// Получаем список компонентов
		// в каждой директории компонента ищем файл psearch.php
		$rs = $this->inDB->query('SELECT link, title FROM cms_components WHERE internal = 0 AND published = 1') ;
		if (!$this->inDB->num_rows($rs)){ return false; }

		while ($component = $this->inDB->fetch_assoc($rs)){
			if (file_exists(PATH.'/components/'.$component['link'].'/psearch.php')){
				$component['title'] = str_replace('InstantShop', 'Каталог товаров', $component['title']);
				$component['title'] = str_replace('InstantMaps', 'Каталог объектов на карте', $component['title']);
				$this->components[] = $component;
			}
		}

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает массив компонентов, включенных для участия в поиске
     * @return array $enable_components
     */
    public function getEnableComponentsWithSupportSearch() {

		$enable_components = array();

		foreach($this->components as $component){
			if (in_array($component['link'], $this->config['comp'])){
				$enable_components[] = $component;
			}
		}

        return $enable_components;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

}