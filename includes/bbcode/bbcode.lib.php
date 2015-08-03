<?php

/******************************************************************************
 *                                                                            *
 *   bbcode.lib.php, v 0.1 2012/09/12 - Handling of a BBCode                  *
 *   Copyright (C) 2006  Dmitriy Skorobogatov  dima@pc.uz                     *
 *                 2012  InstantCMS Team, (www.instantsoft.ru)                *
 *                                                                            *
 *   This program is free software; you can redistribute it and/or modify     *
 *   it under the terms of the GNU General Public License as published by     *
 *   the Free Software Foundation; either version 2 of the License, or        *
 *   (at your option) any later version.                                      *
 *                                                                            *
 *   This program is distributed in the hope that it will be useful,          *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of           *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            *
 *   GNU General Public License for more details.                             *
 *                                                                            *
 *   You should have received a copy of the GNU General Public License        *
 *   along with this program; if not, write to the Free Software              *
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA *
 *                                                                            *
 ******************************************************************************/

class bbcode {
    /*
    Описания тегов. Каждое описание - масив свойств:
        'handler'  - название функции - обработчика тегов.
        'is_close' - true, если тег всегда считается закрытым (например [hr]).
        'lbr'       - число переводов строк, которые следует игнорировать перед
                     элементом.
        'rbr'      - число переводов строк, которые следует игнорировать после
                     элемента.
        'ends'     - список тегов, начало которых обязательно закрывает данный.
        'permission_top_level' - true, если тегу разрешено находиться в корне
                     дерева элементов.
        'children' - список тегов, которым разрешено быть вложенными в данный.
    */
    private $info_about_tags = array(
            'align' => array(
                    'handler' => 'align_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 1,
                    'ends' => array('*','tr','td','th'),
                    'permission_top_level' => true,
                    'children' => array('align','b','code', 'video', 'audio', 'color','email',
                        'font','google','h1','h2','h3','hr','i','img','list',
                        'nobb','php','quote','s','size','sub','sup','table','tt','u','url')
                ),
            'b' => array(
                    'handler' => 'b_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code', 'video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'code' => array(
                    'handler' => 'code_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 2,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array()
                ),
			'video' => array(
                    'handler' => 'video_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 2,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array()
               ),
			'audio' => array(
                    'handler' => 'audio_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 2,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array()
               ),
            'spoiler' => array(
                    'handler' => 'spoiler_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 2,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','i','img', 'video',
                    'nobb','s','size','sub','sup','tt','u', 'audio', 'quote', 'url')
                ),
            'color' => array(
                    'handler' => 'color_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'email' => array(
                    'handler' => 'email_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','i','img',
                        'nobb','s','size','sub','sup','tt','u')
                ),
            'font' => array(
                    'handler' => 'font_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','font','google','i',
                        'img','nobb','s','size','sub','sup','tt','u','url')
                ),
            'h1' => array(
                    'handler' => 'h1_2html',
                    'is_close' => false,
                    'lbr' => 1,
                    'rbr' => 2,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'h2' => array(
                    'handler' => 'h2_2html',
                    'is_close' => false,
                    'lbr' => 1,
                    'rbr' => 2,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'h3' => array(
                    'handler' => 'h3_2html',
                    'is_close' => false,
                    'lbr' => 1,
                    'rbr' => 2,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'hr' => array(
                    'handler' => 'hr_2html',
                    'is_close' => true,
                    'lbr' => 0,
                    'rbr' => 1,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array()
                ),
            'i' => array(
                    'handler' => 'i_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            's' => array(
                    'handler' => 's_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'img' => array(
                    'handler' => 'img_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array()
                ),
            'quote' => array(
                    'handler' => 'quote_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 1,
                    'ends' => array(),
                    'permission_top_level' => true,
                    'children' => array('*','align','b','code','video', 'audio', 'color','email',
                        'font','google','h1','h2','h3','hr','i','img','list','spoiler',
                        'nobb','php','quote','s','size','sub','sup','table','tt','u','url')
                ),
            'u' => array(
                    'handler' => 'u_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','email','font','google','i','img',
                        'nobb','s','size','sub','sup','tt','u','url')
                ),
            'url' => array(
                    'handler' => 'url_2html',
                    'is_close' => false,
                    'lbr' => 0,
                    'rbr' => 0,
                    'ends' => array('*','align','code','video', 'audio', 'h1','h2','h3','hr',
                        'list','php','quote','table','td','th','tr'),
                    'permission_top_level' => true,
                    'children' => array('b','color','font','i','img','nobb',
                        's','size','sub','sup','tt','u')
                ),
        );
    // При инициализации объекта положим сюда синтаксический разбор ббкода
    private $syntax = array();

	private $smiles_img = array();
    /*
    Функция парсит BBCode и возвращает масив пар
    "число (тип лексемы) - лексема", где типы лексем могут быть следующие:
    0 - открывющая квадратная скобка ("[")
    1 - закрывающая квадратная cкобка ("]")
    2 - двойная кавычка ('"')
    3 - апостроф ("'")
    4 - равенство ("=")
    5 - прямой слэш ("/")
    6 - последовательность пробельных символов
        (" ", "\t", "\n", "\r", "\0" или "\x0B")
    7 - последовательность прочих символов, не являющаяся именем тега
    8 - имя тега
    */
    private function get_array_of_tokens($code) {

        $length    = mb_strlen($code);
        $tokens    = array();
        $token_key = -1;
        $type_of_char = null;

        for ( $i=0; $i<$length; ++$i ) {
            $previous_type = $type_of_char;
            switch (mb_substr($code, $i, 1)) {
                case '[':
                    $type_of_char = 0;
                    break;
                case ']':
                    $type_of_char = 1;
                    break;
                case '"':
                    $type_of_char = 2;
                    break;
                case "'":
                    $type_of_char = 3;
                    break;
                case "=":
                    $type_of_char = 4;
                    break;
                case '/':
                    $type_of_char = 5;
                    break;
                case ' ':
                    $type_of_char = 6;
                    break;
                case "\t":
                    $type_of_char = 6;
                    break;
                case "\n":
                    $type_of_char = 6;
                    break;
                case "\r":
                    $type_of_char = 6;
                    break;
                case "\0":
                    $type_of_char = 6;
                    break;
                case "\x0B":
                    $type_of_char = 6;
                    break;
                default:
                    $type_of_char = 7;
            }
            if ( 7 == $previous_type && $type_of_char != $previous_type ) {
                $word = mb_strtolower($tokens[$token_key][1]);
                if ( isset($this -> info_about_tags[$word]) ) {
                    $tokens[$token_key][0] = 8;
                }
            }
            switch ( $type_of_char ) {
                case 6:
                    if ( 6 == $previous_type ) {
                        $tokens[$token_key][1] .= mb_substr($code, $i, 1);
                    } else { $tokens[++$token_key] = array( 6, mb_substr($code, $i, 1) ); }
                    break;
                case 7:
                    if ( 7 == $previous_type ) {
                        $tokens[$token_key][1] .= mb_substr($code, $i, 1);
                    } else { $tokens[++$token_key] = array( 7, mb_substr($code, $i, 1) ); }
                    break;
                default:
                    $tokens[++$token_key] = array( $type_of_char, mb_substr($code, $i, 1) );
            }
        }
        return $tokens;
    }
    /*
    Конструктор класса. Совершает синтаксический разбор BBCode и инициализирует
    свойство $this -> syntax - массив следующей структуры:
    Array
    (
        ...
        [i] => Array  // [i] - целочисленный ключ начиная с 0
            (
                [type] => тип элемента: 'text', 'open', 'close' или 'open/close'
                          'text'  - элемент соответствует тексту между тегами
                          'open'  - элемент соответствует открывающему тегу
                          'close' - элемент соответствует закрывающему тегу
                          'open/close' - элемент соответствует закрытому тегу
                                         (например такому: [img="..." /])
                [str]  => строковое представление элемента: текст между тегами
                          или тег (например: '[FONT color=red size=+1]')
                [name] => имя тега. Всегда в нижнем регистре. Например: 'color'.
                          Значение [name] отсутствует для элементов типа 'text'
                          и может быть пустой строкой для элементов типа
                          'close'. В последнем случае элемент будет
                          соответствовать тегу '[/]', который будет считаться
                          закрывающим для последнего незакрытого перед ним.
                [attrib] => Array         // Это значение существует только для
                    (                     // элементов типов 'open' и
                        ...               // 'open/close'
                        ...
                        [имя атрибута] => значение атрибута. Например:
                        ...               [color] => red
                                          Имя атрибута всегда в нижнем регистре.
                                          Значение атрибута может быть пустой
                                          строкой. Имя тега тоже присутствует в
                                          списке атрибутов. Это для того, чтобы
                                          можно было работать, например, с
                                          такими тегами - [color="#555555"]
                    )
                [layout] => Array                 // Это значение несуществует
                    (                             // для элементов типа 'text'.
                        [0] => Array              // Массив содержит пары
                            (                     // ( тип строки , строка )
                                [0] => 0          // Типы могут быть следующие:
                                [1] => [          // 0 - скобка ('[' или ']')
                            )                     // 1 - слэш '/'
                        ...                       // 2 - имя тега
                        [i] => Array              //     (например - 'FONT')
                            (                     // 3 - знак '='
                                [0] => тип строки // 4 - строка из пробельных
                                [1] => строка     //     символов
                            )                     // 5 - кавычка или апостроф,
                        ...                       //     ограничивающая значение
                                                  //     атрибута
                    )                             // 6 - имя атрибута
            )                                     // 7 - значение атрибута
        ...
    )
    */
    public function __construct($code) {
        /*
        Используем метод конечных автоматов
        Список возможных состояний автомата:
        0  - Начало сканирования или находимся вне тега. Ожидаем что угодно.
        1  - Встретили символ "[", который считаем началом тега. Ожидаем имя
             тега, или символ "/".
        2  - Нашли в теге неожидавшийся символ "[". Считаем предыдущую строку
             ошибкой. Ожидаем имя тега, или символ "/".
        3  - Нашли в теге синтаксическую ошибку. Текущий символ не является "[".
             Ожидаем что угодно.
        4  - Сразу после "[" нашли символ "/". Предполагаем, что попали в
             закрывающий тег. Ожидаем имя тега или символ "]".
        5  - Сразу после "[" нашли имя тега. Считаем, что находимся в
             открывающем теге. Ожидаем пробел или "=" или "/" или "]".
        6  - Нашли завершение тега "]". Ожидаем что угодно.
        7  - Сразу после "[/" нашли имя тега. Ожидаем "]".
        8  - В открывающем теге нашли "=". Ожидаем пробел или значение атрибута.
        9  - В открывающем теге нашли "/", означающий закрытие тега. Ожидаем
             "]".
        10 - В открывающем теге нашли пробел после имени тега или имени
             атрибута. Ожидаем "=" или имя другого атрибута или "/" или "]".
        11 - Нашли '"' начинающую значение атрибута, ограниченное кавычками.
             Ожидаем что угодно.
        12 - Нашли "'" начинающий значение атрибута, ограниченное апострофами.
             Ожидаем что угодно.
        13 - Нашли начало незакавыченного значения атрибута. Ожидаем что угодно.
        14 - В открывающем теге после "=" нашли пробел. Ожидаем значение
             атрибута.
        15 - Нашли имя атрибута. Ожидаем пробел или "=" или "/" или "]".
        16 - Находимся внутри значения атрибута, ограниченного кавычками.
             Ожидаем что угодно.
        17 - Завершение значения атрибута. Ожидаем пробел или имя следующего
             атрибута или "/" или "]".
        18 - Находимся внутри значения атрибута, ограниченного апострофами.
             Ожидаем что угодно.
        19 - Находимся внутри незакавыченного значения атрибута. Ожидаем что
             угодно.
        20 - Нашли пробел после значения атрибута. Ожидаем имя следующего
             атрибута или "/" или "]".

        Описание конечного автомата:
        */
        $finite_automaton = array(
               // Предыдущие |   Состояния для текущих событий (лексем)   |
               //  состояния |  0 |  1 |  2 |  3 |  4 |  5 |  6 |  7 |  8 |
                   0 => array(  1 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 )
                ,  1 => array(  2 ,  3 ,  3 ,  3 ,  3 ,  4 ,  3 ,  3 ,  5 )
                ,  2 => array(  2 ,  3 ,  3 ,  3 ,  3 ,  4 ,  3 ,  3 ,  5 )
                ,  3 => array(  1 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 )
                ,  4 => array(  2 ,  6 ,  3 ,  3 ,  3 ,  3 ,  3 ,  3 ,  7 )
                ,  5 => array(  2 ,  6 ,  3 ,  3 ,  8 ,  9 , 10 ,  3 ,  3 )
                ,  6 => array(  1 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 ,  0 )
                ,  7 => array(  2 ,  6 ,  3 ,  3 ,  3 ,  3 ,  3 ,  3 ,  3 )
                ,  8 => array( 13 , 13 , 11 , 12 , 13 , 13 , 14 , 13 , 13 )
                ,  9 => array(  2 ,  6 ,  3 ,  3 ,  3 ,  3 ,  3 ,  3 ,  3 )
                , 10 => array(  2 ,  6 ,  3 ,  3 ,  8 ,  9 ,  3 , 15 , 15 )
                , 11 => array( 16 , 16 , 17 , 16 , 16 , 16 , 16 , 16 , 16 )
                , 12 => array( 18 , 18 , 18 , 17 , 18 , 18 , 18 , 18 , 18 )
                , 13 => array( 19 ,  6 , 19 , 19 , 19 , 19 , 17 , 19 , 19 )
                , 14 => array(  2 ,  3 , 11 , 12 , 13 , 13 ,  3 , 13 , 13 )
                , 15 => array(  2 ,  6 ,  3 ,  3 ,  8 ,  9 , 10 ,  3 ,  3 )
                , 16 => array( 16 , 16 , 17 , 16 , 16 , 16 , 16 , 16 , 16 )
                , 17 => array(  2 ,  6 ,  3 ,  3 ,  3 ,  9 , 20 , 15 , 15 )
                , 18 => array( 18 , 18 , 18 , 17 , 18 , 18 , 18 , 18 , 18 )
                , 19 => array( 19 ,  6 , 19 , 19 , 19 , 19 , 20 , 19 , 19 )
                , 20 => array(  2 ,  6 ,  3 ,  3 ,  3 ,  9 ,  3 , 15 , 15 )
            );
        // Получаем массив лексем:
        $array_of_tokens = $this->get_array_of_tokens($code);
        // Сканируем его с помощью построенного автомата:
        $mode = 0;
        $result = array();
        $tag_decomposition = array();
        $token_key = -1;
        foreach ( $array_of_tokens as $token ) {
            $previous_mode = $mode;
            $mode = $finite_automaton[$previous_mode][$token[0]];
            switch ( $mode ) {
                case 0:
                    if (-1<$token_key && 'text'==$result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $token[1];
                    } else {
                        $result[++$token_key] = array(
                                'type' => 'text',
                                'str' => $token[1]
                            );
                    }
                    break;
                case 1:
                    $tag_decomposition['name']     = '';
                    $tag_decomposition['type']     = '';
                    $tag_decomposition['str']      = '[';
                    $tag_decomposition['layout'][] = array( 0, '[' );
                    break;
                case 2:
                    if (-1<$token_key && 'text'==$result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $tag_decomposition['str'];
                    } else {
                        $result[++$token_key] = array(
                                'type' => 'text',
                                'str' => $tag_decomposition['str']
                            );
                    }
                    $tag_decomposition = array();
                    $tag_decomposition['name']     = '';
                    $tag_decomposition['type']     = '';
                    $tag_decomposition['str']      = '[';
                    $tag_decomposition['layout'][] = array( 0, '[' );
                    break;
                case 3:
                    if (-1<$token_key && 'text'==$result[$token_key]['type']) {
                        $result[$token_key]['str'] .= $tag_decomposition['str'];
                        $result[$token_key]['str'] .= $token[1];
                    } else {
                        $result[++$token_key] = array(
                                'type' => 'text',
                                'str' => $tag_decomposition['str'].$token[1]
                            );
                    }
                    $tag_decomposition = array();
                    break;
                case 4:
                    $tag_decomposition['type'] = 'close';
                    $tag_decomposition['str'] .= '/';
                    $tag_decomposition['layout'][] = array( 1, '/' );
                    break;
                case 5:
                    $tag_decomposition['type'] = 'open';
                    $name = mb_strtolower($token[1]);
                    $tag_decomposition['name'] = $name;
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 2, $token[1] );
                    $tag_decomposition['attrib'][$name] = '';
                    break;
                case 6:
                    if ( ! isset($tag_decomposition['name']) ) {
                        $tag_decomposition['name'] = '';
                    }
                    if ( 13 == $previous_mode || 19 == $previous_mode ) {
                        $tag_decomposition['layout'][] = array( 7, $value );
                    }
                    $tag_decomposition['str'] .= ']';
                    $tag_decomposition['layout'][] = array( 0, ']' );
                    $result[++$token_key] = $tag_decomposition;
                    $tag_decomposition = array();
                    break;
                case 7:
                    $tag_decomposition['name'] = mb_strtolower($token[1]);
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 2, $token[1] );
                    break;
                case 8:
                    $tag_decomposition['str'] .= '=';
                    $tag_decomposition['layout'][] = array( 3, '=' );
                    break;
                case 9:
                    $tag_decomposition['type'] = 'open/close';
                    $tag_decomposition['str'] .= '/';
                    $tag_decomposition['layout'][] = array( 1, '/' );
                    break;
                case 10:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 4, $token[1] );
                    break;
                case 11:
                    $tag_decomposition['str'] .= '"';
                    $tag_decomposition['layout'][] = array( 5, '"' );
                    break;
                case 12:
                    $tag_decomposition['str'] .= "'";
                    $tag_decomposition['layout'][] = array( 5, "'" );
                    break;
                case 13:
                    $tag_decomposition['attrib'][$name] = $token[1];
                    $value = $token[1];
                    $tag_decomposition['str'] .= $token[1];
                    break;
                case 14:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 4, $token[1] );
                    break;
                case 15:
                    $name = $token[1];
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 6, $token[1] );
                    $tag_decomposition['attrib'][$name] = '';
                    break;
                case 16:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    $value .= $token[1];
                    break;
                case 17:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['layout'][] = array( 7, $value );
                    $value = '';
                    $tag_decomposition['layout'][] = array( 5, $token[1] );
                    break;
                case 18:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    $value .= $token[1];
                    break;
                case 19:
                    $tag_decomposition['str'] .= $token[1];
                    $tag_decomposition['attrib'][$name] .= $token[1];
                    $value .= $token[1];
                    break;
                case 20:
                    $tag_decomposition['str'] .= $token[1];
                    if ( 13 == $previous_mode || 19 == $previous_mode ) {
                        $tag_decomposition['layout'][] = array( 7, $value );
                    }
                    $value = '';
                    $tag_decomposition['layout'][] = array( 4, $token[1] );
                    break;
            }
        }
        if ( count($tag_decomposition) ) {
            if ( -1 < $token_key && 'text' == $result[$token_key]['type'] ) {
                $result[$token_key]['str'] .= $tag_decomposition['str'];
            } else {
                $result[++$token_key] = array(
                        'type' => 'text',
                        'str' => $tag_decomposition['str']
                    );
            }
        }
        $this->syntax = $result;
    }
    // Функция возвращает нормализует и возвращает дерево элементов
    private function get_tree_of_elems() {
        /* Первый этап нормализации: превращаем $this -> syntax в правильную
           скобочную структуру */
        $structure = array();

        $structure_key = -1;
        $level = 0;
        $open_tags = array();

        foreach ( $this->syntax as $syntax_key => $val ) {
            unset($val['layout']);
            switch ( $val['type'] ) {
                case 'text':
                    $type = (-1 < $structure_key)
                        ? $structure[$structure_key]['type'] : false;
                    if ( 'text' == $type ) {
                        $structure[$structure_key]['str'] .= $val['str'];
                    } else {
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level;
                    }
                    break;
                case 'open/close':
                    foreach (array_reverse($open_tags,true) as $ult_key => $ultimate) {
                        $ends = $this->info_about_tags[$ultimate]['ends'];
                        if ( in_array($val['name'],$ends) ) {
                            $structure[++$structure_key] = array(
                                    'type'  => 'close',
                                    'name'  => $ultimate,
                                    'str'   => '',
                                    'level' => --$level
                                );
                            unset($open_tags[$ult_key]);
                        } else { break; }
                    }
                    $structure[++$structure_key] = $val;
                    $structure[$structure_key]['level'] = $level;
                    break;
                case 'open':
                    foreach (array_reverse($open_tags,true) as $ult_key => $ultimate) {
                        $ends = $this->info_about_tags[$ultimate]['ends'];
                        if ( in_array($val['name'],$ends) ) {
                            $structure[++$structure_key] = array(
                                    'type'  => 'close',
                                    'name'  => $ultimate,
                                    'str'   => '',
                                    'level' => --$level
                                );
                            unset($open_tags[$ult_key]);
                        } else { break; }
                    }
                    if ( $this->info_about_tags[$val['name']]['is_close'] ) {
                        $val['type'] = 'open/close';
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level;
                    } else {
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = $level++;
                        $open_tags[] = $val['name'];
                    }
                    break;
                case 'close':
                    if ( !count($open_tags) ) {
                        $type = (-1 < $structure_key)
                            ? $structure[$structure_key]['type'] : false;
                        if ( 'text' == $type ) {
                            $structure[$structure_key]['str'] .= $val['str'];
                        } else {
                            $structure[++$structure_key] = array(
                                    'type'  => 'text',
                                    'str'   => $val['str'],
                                    'level' => 0
                                );
                        }
                        break;
                    }
                    if ( !$val['name'] ) {
                        end($open_tags);
                        list($ult_key, $ultimate) = each($open_tags);
                        $val['name'] = $ultimate;
                        $structure[++$structure_key] = $val;
                        $structure[$structure_key]['level'] = --$level;
                        unset($open_tags[$ult_key]);
                        break;
                    }
                    if ( !in_array($val['name'],$open_tags) ) {
                        $type = (-1 < $structure_key)
                            ? $structure[$structure_key]['type'] : false;
                        if ( 'text' == $type ) {
                            $structure[$structure_key]['str'] .= $val['str'];
                        } else {
                            $structure[++$structure_key] = array(
                                    'type'  => 'text',
                                    'str'   => $val['str'],
                                    'level' => $level
                                );
                        }
                        break;
                    }
                    foreach (array_reverse($open_tags,true) as $ult_key => $ultimate) {
                        if ( $ultimate != $val['name'] ) {
                            $structure[++$structure_key] = array(
                                    'type'  => 'close',
                                    'name'  => $ultimate,
                                    'str'   => '',
                                    'level' => --$level
                                );
                            unset($open_tags[$ult_key]);
                        } else { break; }
                    }
                    $structure[++$structure_key] = $val;
                    $structure[$structure_key]['level'] = --$level;
                    unset($open_tags[$ult_key]);
            }
        }
        foreach (array_reverse($open_tags,true) as $ult_key => $ultimate) {
            $structure[++$structure_key] = array(
                    'type'  => 'close',
                    'name'  => $ultimate,
                    'str'   => '',
                    'level' => --$level
                );
            unset($open_tags[$ult_key]);
        }
        /* Второй этап нормализации: Отслеживаем, имеют ли элементы
           неразрешенные подэлементы. Соответственно этому исправляем
           $structure. */
        $normalized = array();
        $normal_key = -1;
        $level = 0;
        $open_tags = array();
        $not_tags = array();
        foreach ( $structure as $structure_key => $val ) {
            switch ( $val['type'] ) {
                case 'text':
                    $type = (-1 < $normal_key)
                        ? $normalized[$normal_key]['type'] : false;
                    if ( 'text' == $type ) {
                        $normalized[$normal_key]['str'] .= $val['str'];
                    } else {
                        $normalized[++$normal_key] = $val;
                        $normalized[$normal_key]['level'] = $level;
                    }
                    break;
                case 'open/close':
                    $is_open = count($open_tags);
                    end($open_tags);
                    $info = $this->info_about_tags[$val['name']];
                    $children = $is_open
                        ? $this->info_about_tags[current($open_tags)]['children']
                        : array();
                    $not_normal = ! $level && ! $info['permission_top_level']
                        || $is_open && ! in_array($val['name'],$children);
                    if ( $not_normal ) {
                        $type = (-1 < $normal_key)
                            ? $normalized[$normal_key]['type'] : false;
                        if ( 'text' == $type ) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                    'type'  => 'text',
                                    'str'   => $val['str'],
                                    'level' => $level
                                );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = $level;
                    break;
                case 'open':
                    $is_open = count($open_tags);
                    end($open_tags);
                    $info = $this->info_about_tags[$val['name']];
                    $children = $is_open
                        ? $this->info_about_tags[current($open_tags)]['children']
                        : array();
                    $not_normal = ! $level && ! $info['permission_top_level']
                        || $is_open && ! in_array($val['name'],$children);
                    if ( $not_normal ) {
                        $not_tags[$val['level']] = $val['name'];
                        $type = (-1 < $normal_key)
                            ? $normalized[$normal_key]['type'] : false;
                        if ( 'text' == $type ) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                    'type'  => 'text',
                                    'str'   => $val['str'],
                                    'level' => $level
                                );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = $level++;
                    $ult_key = count($open_tags);
                    $open_tags[$ult_key] = $val['name'];
                    break;
                case 'close':
                    $not_normal = isset($not_tags[$val['level']])
                        && $not_tags[$val['level']] = $val['name'];
                    if ( $not_normal ) {
                        unset($not_tags[$val['level']]);
                        $type = (-1 < $normal_key)
                            ? $normalized[$normal_key]['type'] : false;
                        if ( 'text' == $type ) {
                            $normalized[$normal_key]['str'] .= $val['str'];
                        } else {
                            $normalized[++$normal_key] = array(
                                    'type'  => 'text',
                                    'str'   => $val['str'],
                                    'level' => $level
                                );
                        }
                        break;
                    }
                    $normalized[++$normal_key] = $val;
                    $normalized[$normal_key]['level'] = --$level;
                    $ult_key = count($open_tags) - 1;
                    unset($open_tags[$ult_key]);
                    break;
            }
        }
        // Формируем дерево элементов
        $result = array();
        $result_key = -1;
        $open_tags = array();
        $val_key = -1;
        foreach ( $normalized as $normal_key => $val ) {
            switch ( $val['type'] ) {
                case 'text':
                    if ( ! $val['level'] ) {
                        $result[++$result_key] = array(
                                'type' => 'text',
                                'str' => $val['str']
                            );
                        break;
                    }
                    $open_tags[$val['level']-1]['val'][] = array(
                            'type' => 'text',
                            'str' => $val['str']
                        );
                    break;
                case 'open/close':
                    if ( ! $val['level'] ) {
                        $result[++$result_key] = array(
                                'type'   => 'item',
                                'name'   => $val['name'],
                                'attrib' => $val['attrib'],
                                'val'    => array()
                            );
                        break;
                    }
                    $open_tags[$val['level']-1]['val'][] = array(
                            'type'   => 'item',
                            'name'   => $val['name'],
                            'attrib' => $val['attrib'],
                            'val'    => array()
                        );
                    break;
                case 'open':
                    $open_tags[$val['level']] = array(
                            'type'   => 'item',
                            'name'   => $val['name'],
                            'attrib' => $val['attrib'],
                            'val'    => array()
                        );
                    break;
                case 'close':
                    if ( ! $val['level'] ) {
                        $result[++$result_key] = $open_tags[0];
                        unset($open_tags[0]);
                        break;
                    }
                    $open_tags[$val['level']-1]['val'][] = $open_tags[$val['level']];
                    unset($open_tags[$val['level']]);
                    break;
            }
        }
        return $result;
    }
    /**
     * Автоссылки в тексте
     * @param string $text
     * @return str
     */
    public static function autoLink($text){

		$text = preg_replace('/\s+/u', ' ', $text);

        $search = array(
                "~(\s|^)((?:http|https|ftp)://[^<\s]+[^<\.,:;?!\"»'+\-\s])~uim",
                "~(\s|^)(www\.[^<\s]+[^<\.,:;?!\"»'+\-\s])~uim",
                "'([^\w\d-\.]|^)([\w\d-\.]+@[\w\d-\.]+\.[\w]+[^.,;\s<\"\'\)]+)'usi"
            );
        $replace = array(
                '$1<a href="/go/url=$2">$2</a>',
                '$1<a href="/go/url=http://$2">$2</a>',
                '$1<a href="mailto:$2">$2</a>'
            );

        $text = preg_replace($search, $replace, $text);

        return preg_replace_callback(
          '#<a href="/go/url=([^"]+)"#',
          create_function(
              '$matches',
              'if (!strstr($matches[1], $_SERVER[\'HTTP_HOST\'])){ return "<a target=\"_blank\" title=\"".htmlspecialchars($matches[1])."\" href=\"/go/url=-".base64_encode($matches[1])."\""; } else { return "<a href=\"".htmlspecialchars($matches[1])."\""; }'
          ),
          $text
        );

    }
    /*
    Функция мнемонизирует HTML-код, вставляет в текст разрывы <br /> и
    "автоматические ссылки".
    */
    public function insert_smiles($text) {

		return self::autoLink(nl2br(htmlspecialchars($text)));

    }
    /*
    Функция преобразует смайлы в их изображения
    */
	public function replaceEmotionToSmile($text, $smile_dir='/images/smilies/', $ext='gif') {

        //convert emoticons to smileys
        $smilefix = array();
        $smilefix[' :) '] = 'smile';
        $smilefix[' =) '] = 'smile';
        $smilefix[':-)']  = 'smile';
        $smilefix[' :( '] = 'sad';
        $smilefix[':-(']  = 'sad';
        $smilefix[';-)']  = 'joke';
        $smilefix[' ;) '] = 'joke';
        $smilefix[' =0 '] = 'shock';
        $smilefix['=-0']  = 'shock';
        $smilefix[' Oo '] = 'shock';
        $smilefix[':-0']  = 'shock';
        $smilefix[' :D '] = 'laugh';
        $smilefix[':-D']  = 'laugh';

        foreach($smilefix as $find=>$tag){
            $text = str_replace($find, ':'.$tag.':', $text);
        }

		$smiles_img = $this->getSmilesImg($smile_dir);

		if($smiles_img){
			foreach($smiles_img as $smile){
				$file = $smile_dir . $smile .'.'. $ext;
				if (@file_exists(PATH.$file)){
					$text = str_replace(':'.$smile.':', ' <img src="'.$file.'" alt="'.$smile.'" border="0"/> ', $text);
				}
			}
		}

		return $text;
	}
    /*
    Функция возвращает массив названий файлов смайлов без расширения.
    */
	private function getSmilesImg($path) {

		if($this->smiles_img) { return $this->smiles_img; }

		$pdir = @opendir(PATH.$path);
		if(!$pdir){ return false; }

		$smiles = array();

		while ($smile = @readdir($pdir)){
			if (($smile != '.') && ($smile != '..') && !is_dir(PATH.$path.$smile)) {
				$smiles[] = mb_substr($smile, 0, mb_strrpos($smile, '.'));
			}
		}

		@closedir($pdir);

		$this->smiles_img = $smiles;

		return $smiles;
	}

    private function cleanAttrValue($value) {
        if(preg_match('/javascript:/ui', $value)) {
            return '';
        }
        return htmlspecialchars(strip_tags($value));
    }

    // Функция конвертит дерево элементов BBCode в HTML и возвращает результат
    public function get_html($tree_of_elems=false) {
        if (! is_array($tree_of_elems)) {
            $tree_of_elems = $this -> get_tree_of_elems();
        }
        $result = '';
        $lbr = 0;
        $rbr = 0;
        foreach ( $tree_of_elems as $elem ) {
            if ('text'==$elem['type']) {
                $elem['str'] = $this -> insert_smiles($elem['str']);
                for ($i=0; $i<$rbr; ++$i) {
                    $elem['str'] = ltrim($elem['str']);
                    if ('<br />' == mb_substr($elem['str'], 0, 6)) {
                        $elem['str'] = icms_substr_replace($elem['str'], '', 0, 6);
                    }
                }
                $result .= $elem['str'];
            } else {
                $lbr = $this -> info_about_tags[$elem['name']]['lbr'];
                $rbr = $this -> info_about_tags[$elem['name']]['rbr'];
                for ($i=0; $i<$lbr; ++$i) {
                    $result = rtrim($result);
                    if ('<br />' == mb_substr($result, -6)) {
                        $result = icms_substr_replace($result, '', -6, 6);
                    }
                }
                $func_name = $this -> info_about_tags[$elem['name']]['handler'];
                $result .= call_user_func(array(&$this,$func_name), $elem);
            }
        }
        return $result;
    }
    // Функция - обработчик тега [align]
    function align_2html($elem) {
        $align = $this->cleanAttrValue($elem['attrib']['align']);
        return '<div align="'.$align.'">'.$this -> get_html($elem['val']).'</div>';
    }
    // Функция - обработчик тега [b]
    function b_2html($elem) {
        return '<strong>'.$this -> get_html($elem['val']).'</strong>';
    }
    // Функция - обработчик тега [code]
    function code_2html($elem) {
        global $_LANG;
        $lang = $elem['attrib']['code'];
        if(!$lang){ $lang = 'php'; }

        $str  = '<div class="bb_tag_code">';
        $str .= '<strong>'.$_LANG['CODE'].' '.mb_strtoupper($this->cleanAttrValue($lang)).':</strong><br/>';

        cmsCore::includeFile('includes/geshi/geshi.php');

        foreach ($elem['val'] as $item) {
            if ('item'==$item['type']) { continue; }
            $item['str'] = str_replace('&#8217;', "'", $item['str']);
            $item['str'] = str_replace('’', "'", $item['str']);
        }

        $geshi = new GeSHi($item['str'], $lang);
        $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);

        $str .= $geshi->parse_code();

        $str .= '</div>';

        return $str;

    }
    // Функция - обработчик тега [video]
    function video_2html($elem) {
        $str = '<div class="bb_tag_video">';
        foreach ($elem['val'] as $item) {

            if ('item'==$item['type']) { continue; }

			$my_domen_regexp = str_replace('.', '\.', HOST);
			$my_domen_regexp = str_replace('/', '\/', $my_domen_regexp);

            $iframe_regexp      = '/<iframe.*?src=(?!"\/\/www\.youtube\.com\/embed\/|"http:\/\/vk\.com\/video_ext\.php\?|"'.$my_domen_regexp.').*?><\/iframe>/iu';
            $iframe_regexp2     = '/<iframe.*>.+<\/iframe>/iu';
            $item['str']        = preg_replace($iframe_regexp, '', $item['str']);
            $item['str']        = preg_replace($iframe_regexp2, '', $item['str']);

            $str .= strip_tags($item['str'], '<iframe><object><param><embed>');

        }
        $str .= '</div>';
        return cmsCore::htmlCleanUp($str);
    }
    // Функция - обработчик тега [audio]
    function audio_2html($elem) {
        $str = '<div class="bb_tag_audio">';
        $str .= '<object type="application/x-shockwave-flash" data="/includes/bbcode/player_mp3_mini.swf" width="200" height="20">
                     <param name="movie" value="/includes/bbcode/player_mp3_mini.swf"></param>
                     <param name="bgcolor" value="#666666"></param>
                     <param name="loadingcolor" value="#FFFFFF"></param>
                     <param name="buttoncolor" value="#000000"></param>
                     <param name="slidercolor" value="#333333"></param>
                     <param name="FlashVars" value="mp3='.$this->cleanAttrValue($elem['val'][0]['str']).'"></param>
                </object>';
        $str .= '</div>';
        return $str;
    }

    function spoiler_2html($elem) {

        global $_LANG;

        $title  = $elem['attrib']['spoiler'];
        if ($elem['attrib']){
            unset($elem['attrib']['spoiler']);
            $keys = array_keys($elem['attrib']);
            foreach($keys as $key){
                if ($key != 'spoiler'){
                    $title .= ' '.$key;
                }
            }
        }
        $title = trim($title);
        if (!$title) { $title = $_LANG['SPOILER']; }
        $str .= '<div class="bb_tag_spoiler">';
            $str .= '<div class="spoiler_title">
                        <strong>'.$this->cleanAttrValue($title).'</strong>
                        <input style="margin-left:10px" type="button" onclick="$(this).parent(\'div\').parent(\'div\').find(\'.spoiler_body\').slideToggle();   " value="'.$_LANG['SHOW'].'" />
                     </div>';
            $str .= '<div class="spoiler_body" style="display:none">';
                $str .= $this -> get_html($elem['val']);
            $str .= '</div>';
        $str .= '</div>';
        return $str;
    }

    // Функция - обработчик тега [color]
    function color_2html($elem) {
        $color = $this->cleanAttrValue($elem['attrib']['color']);
        return '<font color="'.$color.'">'.$this -> get_html($elem['val'])
            .'</font>';
    }
    // Функция - обработчик тега [font]
    function font_2html($elem) {
        $face = $elem['attrib']['font'];
        $attr = ' face="'.$this->cleanAttrValue($face).'"';
        $color = isset($elem['attrib']['color']) ? $elem['attrib']['color'] : '';
        if ($color) { $attr .= ' color="'.$this->cleanAttrValue($color).'"'; }
        $size = isset($elem['attrib']['size']) ? $elem['attrib']['size'] : '';
        if ($size) { $attr .= ' size="'.$this->cleanAttrValue($size).'"'; }
        return '<font'.$attr.'>'.$this -> get_html($elem['val']).'</font>';
    }
    // Функция - обработчик тега [h1]
    function h1_2html($elem) {
        $attr = ' class="bb_tag_h1"';
        $align = isset($elem['attrib']['align']) ? $elem['attrib']['align'] : '';
        if ( $align ) { $attr .= ' align="'.$this->cleanAttrValue($align).'"'; }
        return '<h1'.$attr.'>'.$this -> get_html($elem['val']).'</h1>';
    }
    // Функция - обработчик тега [h2]
    function h2_2html($elem) {
        $attr = ' class="bb_tag_h2"';
        $align = isset($elem['attrib']['align']) ? $elem['attrib']['align'] : '';
        if ( $align ) { $attr .= ' align="'.$this->cleanAttrValue($align).'"'; }
        return '<h2'.$attr.'>'.$this -> get_html($elem['val']).'</h2>';
    }
    // Функция - обработчик тега [h3]
    function h3_2html($elem) {
        $attr = ' class="bb_tag_h3"';
        $align = isset($elem['attrib']['align']) ? $elem['attrib']['align'] : '';
        if ( $align ) { $attr .= ' align="'.$this->cleanAttrValue($align).'"'; }
        return '<h3'.$attr.'>'.$this -> get_html($elem['val']).'</h3>';
    }
    // Функция - обработчик тега [hr]
    function hr_2html($elem) {
        return '<hr class="bb" />';
    }
    // Функция - обработчик тега [i]
    function i_2html($elem) {
        return '<i>'.$this -> get_html($elem['val']).'</i>';
    }
    // Функция - обработчик тега [img]
    function img_2html($elem) {
        $attr = 'alt="'.$this->cleanAttrValue(cmsCore::request('title')).'"';
        $src = '';
        foreach ($elem['val'] as $text) {
            if ('text'==$text['type']) { $src .= $text['str']; }
        }
        if (isset($elem['attrib']['align'])){
            if(in_array($elem['attrib']['align'], array('left','right'))){
                $div_style  = "float:{$elem['attrib']['align']};overflow:hidden;";
                $div_style .= "margin-" .($elem['attrib']['align'] == 'left' ? 'right' : 'left'). ":15px; margin-bottom:15px; ";
            }
        }

		$width  = '';
        $height = '';
        $zoom   = false;

        $src = preg_replace ('/[^a-zA-ZА-Яф-я0-9\-_\.\/\:]/ui', '', $src);
		$src = $this->cleanAttrValue(str_replace ('..', '.', $src));

		if (mb_strpos($src, 'http') === false){

            global $_LANG;

			if(file_exists(PATH.$src)){
				if (function_exists('getimagesize')){
					$size = getimagesize(PATH.$src);
					$width = $size[0];
					$height = $size[1];
					while ($width > 340 || $height > 340){
						$width  = round($width*0.9);
						$height = round($height*0.9);
						$zoom   = true;
					}
				}
				if (!$zoom){
					return '<div class="bb_img" style="'.$div_style.'"><img src="'.$src.'" '.$attr.' /></div>';
				} else {
					$html = '<div class="forum_zoom" style="width:'.$width.'px">'."\n";
						$html .= '<div style="'.$div_style.'"><a href="'.$src.'" target="_blank"><img src="'.$src.'" '.$attr.' width="'.$width.'" height="'.$height.'" /></a></div>'."\n";
					$html .= '</div>';
					return $html;
				}
			} else {
				return '<div class="forum_lostimg">'.$_LANG['FILE'].' "'.$src.'" '.$_LANG['NOT_FOUND'].'!</div>';
			}
		} else {
			return '<div class="bb_img" style="'.$div_style.'"><img src="'.$src.'" '.$attr.' /></div>';
		}
    }
    // Функция - обработчик тега [quote]
    function quote_2html($elem) {
        $author = $elem['attrib']['quote'];
        if ($elem['attrib']){
            unset($elem['attrib']['quote']);
            $keys = array_keys($elem['attrib']);
            foreach($keys as $key){
                if ($key != 'quote'){
                    $author .= ' '.$key;
                }
            }
        }
        $author = $this->cleanAttrValue(trim($author));

        $author = $author
            ? '<strong>'.$author.':</strong><br>'
            : '';
        return '<div class="bb_quote">'
            .$author.'<div class="quote">'.$this -> get_html($elem['val'])
            .'</div></div>';
    }
    // Функция - обработчик тега [s]
    function s_2html($elem) {
        return '<s>'.$this -> get_html($elem['val']).'</s>';
    }
    // Функция - обработчик тега [u]
    function u_2html($elem) {
        return '<u>'.$this -> get_html($elem['val']).'</u>';
    }
	// Функция - обработчик тега [email]
	function email_2html($elem) {
		return $this -> get_html($elem['val']);
	}
    // Функция - обработчик тега [url]
    function url_2html($elem) {
        $attr = '';
        $href = $elem['attrib']['url'];
        if (!$href) {
            return $this->get_html($elem['val']);
        }
        $protocols = array(
            'http://','https://','ftp://','file://','#','/','?','./','../'
        );
        $is_http = false;
        foreach ($protocols as $val) {
            if ($val==substr($href,0,strlen($val))) {
                $is_http = true;
                break;
            }
        }
        if (!$is_http) { $href = 'http://'.$href; }

        if (strstr($href, $_SERVER['HTTP_HOST']) || substr($href,0,1)=='/'){
            $url = $href;
            $local = true;
        } else {
            $url = '/go/url=-'.base64_encode($href);
            $local = false;
        }
        $attr .= ' href="'.$this->cleanAttrValue($url).'"';

        $attr .= ' title="'.$this->cleanAttrValue($href).'"';
        $name = isset($elem['attrib']['name']) ? $elem['attrib']['name'] : '';
        if ($name) { $attr .= ' name="'.$this->cleanAttrValue($name).'"'; }
        $target = isset($elem['attrib']['target']) ? $elem['attrib']['target'] : '';
        if ($target) { $attr .= ' target="'.$this->cleanAttrValue($target).'"'; }
        //Если аттрибут target не указан, считать, что ссылки надо открывать в новом окне.
        	elseif (!$local)
        	{ $attr .= ' target="_blank"'; }
        return '<a'.$attr.'>'.$this -> get_html($elem['val']).'</a>';
    }

}
