<?php

/******************************************************************
Projectname:   Automatic Keyword Generator
Version:       0.2
Author:        Ver Pangonilo <smp_AT_itsp.info>
Last modified: 11 July 2013 for InstantCMS
Copyright (C): 2006 Ver Pangonilo, All Rights Reserved

* GNU General Public License (Version 2, June 1991)
*
* This program is free software; you can redistribute
* it and/or modify it under the terms of the GNU
* General Public License as published by the Free
* Software Foundation; either version 2 of the License,
* or (at your option) any later version.
*
* This program is distributed in the hope that it will
* be useful, but WITHOUT ANY WARRANTY; without even the
* implied warranty of MERCHANTABILITY or FITNESS FOR A
* PARTICULAR PURPOSE. See the GNU General Public License
* for more details.

Description:
This class can generates automatically META Keywords for your
web pages based on the contents of your articles. This will
eliminate the tedious process of thinking what will be the best
keywords that suits your article. The basis of the keyword
generation is the number of iterations any word or phrase
occured within an article.

This automatic keyword generator will create single words,
two word phrase and three word phrases. Single words will be
filtered from a common words list.

Change Log:
===========
0.2 Ver Pangonilo - 22 July 2005
================================
Added user configurable parameters and commented codes
for easier end user understanding.

0.3 Vasilich  (vasilich_AT_grafin.kiev.ua) - 26 July 2006
=========================================================
Added encoding parameter to work with UTF texts, min number
of the word/phrase occurrences,

0.4 Peter Kahl, B.S.E.E. (www.dezzignz.com) - 24 May 2009
=========================================================
To strip the punctuations CORRECTLY, moved the ';' to the
end.

Also added '&nbsp;', '&trade;', '&reg;'.
******************************************************************/

class autokeyword {

	//declare variables
	//the site contents
	var $contents;
	var $encoding;
	//the generated keywords
	var $keywords;
	//minimum word length for inclusion into the single word
	//metakeys
	var $wordLengthMin;
	var $wordOccuredMin;
	//minimum word length for inclusion into the 2 word
	//phrase metakeys
	var $word2WordPhraseLengthMin;
	var $phrase2WordLengthMinOccur;
	//minimum word length for inclusion into the 3 word
	//phrase metakeys
	var $word3WordPhraseLengthMin;
	//minimum phrase length for inclusion into the 2 word
	//phrase metakeys
	var $phrase2WordLengthMin;
	var $phrase3WordLengthMinOccur;
	//minimum phrase length for inclusion into the 3 word
	//phrase metakeys
	var $phrase3WordLengthMin;

	function autokeyword($params, $encoding)
	{
		//get parameters
		$this->encoding = $encoding;
		$this->contents = $this->replace_chars($params['content']);

		// single word
		$this->wordLengthMin = $params['min_word_length'];
		$this->wordOccuredMin = $params['min_word_occur'];

		// 2 word phrase
		$this->word2WordPhraseLengthMin = $params['min_2words_length'];
		$this->phrase2WordLengthMin = $params['min_2words_phrase_length'];
		$this->phrase2WordLengthMinOccur = $params['min_2words_phrase_occur'];

		// 3 word phrase
		$this->word3WordPhraseLengthMin = $params['min_3words_length'];
		$this->phrase3WordLengthMin = $params['min_3words_phrase_length'];
		$this->phrase3WordLengthMinOccur = $params['min_3words_phrase_occur'];

		//parse single, two words and three words

	}

	function get_keywords()
	{
		$keywords = $this->parse_words().$this->parse_2words().$this->parse_3words();
		return mb_substr($keywords, 0, -2);
	}

	//turn the site contents into an array
	//then replace common html tags.
	function replace_chars($content)
	{
		//convert all characters to lower case
		$content = mb_strtolower($content);
		//$content = mb_strtolower($content, "UTF-8");
		$content = strip_tags($content);

		$punctuations = array(',', ')', '(', '.', "'", '"',
		'<', '>', '!', '?', '/', '-',
		'_', '[', ']', ':', '+', '=', '#',
		'$', '&quot;', '&copy;', '&gt;', '&lt;',
		'&laquo;', '&laquo', '&raquo', ';','&nbsp;',
		chr(10), chr(13), chr(9));

		$content = str_replace($punctuations, " ", $content);
		// replace multiple gaps
		$content = preg_replace('/ {2,}/sui', " ", $content);

		return $content;
	}

	//single words META KEYWORDS
	function parse_words()
	{
		//list of commonly used words
		// this can be edited to suit your needs
		$common = array("способный", "около", "сверху", "акт", "добавить", "боюсь", "после", "раз", "против", "возраст", "своей", "согласен", "все", "почти", "только", "вместе", "уже", "также", "хотя", "всегда", "пpежде", "сумма", "и", "гнев", "злые", "другой", "ответ", "любой", "появляются", "котоpому", "есть", "прибыли", "рука", "оружие", "около", "спросить", "попытка", "тетя", "в сторону", "Назад ", "плохой", "мешок", "залив", "быть", "стал", "потому что", "стать", "был", "перед", "начал", "начать", "бытие", "чтобы", "принадлежать", "ниже", "рядом", "лучший", "между", "большой", "тело", "кость", "род", "брать", "дно", "котоpое", "мальчик", "перерыв", "принести", "принес", "ошибка", "построил", "занято", "но", "купить", "вызов", "пришли", "может", "причина", "выбрать", "закрыть", "рассмотреть", "прийти", "рассмотреть", "снова", "содержать", "продолжать", "этого", "вырезать", "смел", "темный", "дело", "милая", "решить", "глубокий", "сделал", "умереть", "делать", "не", "сделали", "сомнение", "вниз", "во время", "ничего", "раннее", "усилие", "либо", "другого", "конец", "пользоваться", "достаточно", "Enter", "даже", "никогда", "каждый", "кроме", "ожидать", "объяснить", "падение", "далеко", "толстый", "за", "страх", "чувствовать", "ноги", "упал", "чувствовал", "некоторые", "заполнить", "котоpые", "вписывается", "летать", "следить", "навсегда", "забыли", "от", "хоpошо", "дал", "получить", "дает", "идет", "нет", "хорошо", "получил", "своего", "великий", "котоpая", "вырос", "расти", "угадать", "половина", "повесить", "случилось", "есть", "шляпа", "иметь", "он", "услышать", "слышали", "состоялась", "привет", "помощь", "здесь", "высокая", "тогда", "такой", "держать", "горячий", "однако", "если", "плохо", "действительно", "своим", "есть", "его", "ее", "справедливый", "держать", "знал", "знать", "известным", "поздно", "как минимум", "привели", "слева", "одолжить", "меньше", "пусть", "как", "вероятно", "одинокий", "длинный", "смотреть", "делать", "много" , "возможно", "средняя", "встретились", "вероятно", "никакие", "своему", "больше", "самый", "двигаться", "должен", "мое", "рядом", "почти", "необходимо", "ни", "никогда", "следующий", "к сведению", "ничто", "сейчас", "номер", "о", "выключено", "зачастую", "ах", "котоpый", "один раз", "или", "другие", "наш", "вне", "пожалуйста", "подготовить", "вероятно", "тянуть", "чистый", "толчок", "путь", "поднимать", "бежал", "достижения", "понимать", "ответить", "требовать", "собой", "бежать", "сказал", "то же", "видел", "говорить", "видеть", "кажется", "продать", "послал", "отдельный", "множество", "она", "сторона", "знак", "так", "продал", "некоторые", "скоро", "извините", "остановиться", "всего", "палка", "до сих пор", "стояли", "такие", "внезапная", "Предположим", "взять", "брать", "вашего", "высокий", "сказать", "чем", "спасибо", "что", "своих", "их", "затем", "там", "поэтому", "эти", "они", "это", "те", "хотя", "посредством", "до", "к", "сегодня", "сказал", "завтра", "слишком", "взял", "сорвал", "научили", "пытался", "пытается", "доверие", "попробовать", "очередь", "под", "пока", "вверх", "после", "нас", "использование", "обычный", "разные", "должны", "очень", "самой", "хочу", "мы", "хорошо", "пошел", "были", "когда", "где", "будь то", "который", "а", "какой", "кто", "кого", "чья", "почему", "воля", "с", "в", "без", "будет", "да", "ты", "молодой", "Ваш", "br", "img", "p","lt", "gt", "quot", "copy", "&laquo");
		//create an array out of the site contents
		$s = explode(" ", $this->contents);
		//initialize array
		$k = array();
		//iterate inside the array
		foreach( $s as $key=>$val ) {
			//delete single or two letter words and
			//Add it to the list if the word is not
			//contained in the common words list.
			if(mb_strlen(trim($val)) >= $this->wordLengthMin  && !in_array(trim($val), $common)  && !is_numeric(trim($val))) {
				$k[] = trim($val);
			}
		}
		//count the words
		$k = array_count_values($k);
		//sort the words from
		//highest count to the
		//lowest.
		$occur_filtered = $this->occure_filter($k, $this->wordOccuredMin);
		arsort($occur_filtered);

		$imploded = $this->implode(", ", $occur_filtered);
		//release unused variables
		unset($k);
		unset($s);

		return $imploded;
	}

	function parse_2words()
	{
		//create an array out of the site contents
		$x = explode(" ", $this->contents);
		//initilize array

		//$y = array();
		for ($i=0; $i < count($x)-1; $i++) {
			//delete phrases lesser than 5 characters
			if( (mb_strlen(trim($x[$i])) >= $this->word2WordPhraseLengthMin ) && (mb_strlen(trim($x[$i+1])) >= $this->word2WordPhraseLengthMin) )
			{
				$y[] = trim($x[$i])." ".trim($x[$i+1]);
			}
		}

		//count the 2 word phrases
		$y = array_count_values($y);

		$occur_filtered = $this->occure_filter($y, $this->phrase2WordLengthMinOccur);
		//sort the words from highest count to the lowest.
		arsort($occur_filtered);

		$imploded = $this->implode(", ", $occur_filtered);
		//release unused variables
		unset($y);
		unset($x);

		return $imploded;
	}

	function parse_3words()
	{
		//create an array out of the site contents
		$a = explode(" ", $this->contents);
		//initilize array
		$b = array();

		for ($i=0; $i < count($a)-2; $i++) {
			//delete phrases lesser than 5 characters
			if( (mb_strlen(trim($a[$i])) >= $this->word3WordPhraseLengthMin) && (mb_strlen(trim($a[$i+1])) > $this->word3WordPhraseLengthMin) && (mb_strlen(trim($a[$i+2])) > $this->word3WordPhraseLengthMin) && (mb_strlen(trim($a[$i]).trim($a[$i+1]).trim($a[$i+2])) > $this->phrase3WordLengthMin) )
			{
				$b[] = trim($a[$i])." ".trim($a[$i+1])." ".trim($a[$i+2]);
			}
		}

		//count the 3 word phrases
		$b = array_count_values($b);
		//sort the words from
		//highest count to the
		//lowest.
		$occur_filtered = $this->occure_filter($b, $this->phrase3WordLengthMinOccur);
		arsort($occur_filtered);

		$imploded = $this->implode(", ", $occur_filtered);
		//release unused variables
		unset($a);
		unset($b);

		return $imploded;
	}

	function occure_filter($array_count_values, $min_occur)
	{
		$occur_filtered = array();
		foreach ($array_count_values as $word => $occured) {
			if ($occured >= $min_occur) {
				$occur_filtered[$word] = $occured;
			}
		}

		return $occur_filtered;
	}

	function implode($gule, $array)
	{
		$c = "";
		foreach($array as $key=>$val) {
			@$c .= $key.$gule;
		}
		return $c;
	}
}
?>
