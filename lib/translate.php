<?php/* * Translate PHP library  * http://www.elfimov.ru/php/translate * * Copyright (c) 2012 by Dmitry Elfimov * Released under the MIT License. * http://www.opensource.org/licenses/mit-license.php * * Date: 2012-02-01 */ class Translate{		public $language = '';	public $defaultLanguage = 'en'; // this language will be loaded if we cannot find proper language files	public $available = null; // array('ru', 'en')		private $path = '';	private $messages = array();	private $extra = array();		public $synonyms = array(							'en' => 'uk', 'us' // uk and us are synonyms for en. if HTTP_ACCEPT_LANGUAGE is set to 'uk' or 'us' class will load 'en' instead.					);		/*	$path - path to language files. By default = "messages" subdirectory in the library location.	$userLanguage - user's language. If omitted, then will be trying to find best matching value from Accept-Language header.	$defaultLanguage - default language (language for t() method).	$available - array of available languages.	$extra - extra language files in language directory. By default only messages.php is loading.	*/	public function __construct($path = null, $userLanguage = null, $defaultLanguage = null, $available = null, $extra = null, $synonyms = null) {		$this->path = (isset($path)) ? $path : dirname(__FILE__).'/messages';		$this->available = (isset($available)) ? $available : $this->available;		$this->extra = (isset($extra)) ? $extra : $this->extra;		$this->synonyms = (isset($synonyms)) ? $synonyms : $this->synonyms;		$this->defaultLanguage = (isset($defaultLanguage)) ? $defaultLanguage : $this->defaultLanguage;		$this->setLanguage($userLanguage);		$this->setMessages();	}			// Устанавливаем язык переводов	private function setLanguage($language = null) {		if (isset($language)) {					$this->language = (in_array($language, $this->available) && file_exists($this->path.'/'.$language)) ? $language : $this->defaultLanguage;					} else {						$lang = $this->getLanguage();			$this->language = $lang!==false ? $lang : $language;					}	}		// проверяем разрешен ли выбранный язык и существует ли каталог с сообщениями на выбранном языке	private function getLanguage() {		$langs = explode(';', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));		$max = -1;		foreach ($langs as $key => $lang) {			if ($lang!='') {				$lang = explode(',', $lang);				$short = array();				$long = '';				$q = 1;				foreach ($lang as $value) {					if (strlen($value)==2) {						$short[] = $value;					} else 					if ($value{0}=='q' && $value{1}=='=') {						$q = substr($value, 2);					} else {						$pos = strrchr($value, '_');						if ($pos!==false) {							$short[] = substr($value, 0, -strlen($pos));						} else {							$short[] = $value{0}.$value{1};						}					}				}				foreach ($short as $shortValue) {					if ((!isset($this->available) || in_array($shortValue, $this->available)) && file_exists($this->path.'/'.$shortValue)) {						if ($max<$q) {							$max = $q;							$langMax = $shortValue;						}						$last = $shortValue;						if ($q==1) break 2;					}				}			}		}				$language = isset($langMax) ? $langMax : (isset($last) ? $last : false);		if ($language!==false && ($replace = array_search($language, $this->synonyms))!==false) {			$language = $replace;		}		return $language;				}			// загружаем сообщения из файлов переводов	private function setMessages() {				if (file_exists($this->path.'/'.$this->language.'/messages.php')) {			$this->messages = include($this->path.'/'.$this->language.'/messages.php');		}		foreach ($this->extra as $filename)  {			if (file_exists($this->path.'/'.$this->language.'/'.$filename.'.php')) {				$messages = include($this->path.'/'.$this->language.'/'.$filename.'.php');				$this->messages = $messages + $this->messages;			}		}			}		// перевести строку 	// если заданы $args (массив или несколько переменных), строка форматируется с помощью vsprintf	public function t($string, $args=null) {		if (!empty($this->messages[$string])) {			$string = $this->messages[$string];		}		if (isset($args)) {			if (!is_array($args)) {				$args = func_get_args();				array_shift($args);			}  			$string = vsprintf($string, $args);		}		return $string;	}		// php version>=5.3	public function __invoke($string, $args=null) {		$args = func_get_args();		if (isset($args)) {			array_shift($args);		}		return $this->t($string, $args);	}			// выбрать перевод строки в соответствии с языком пользователя и значения $x	// $string - строка с вариантами, разбитая символом |	// $x - значение от которого зависит выбор варианта	// $args - array, если задано, используется в качестве аргументов, если не задано - единственный аргумент - $x	// $language - язык на котором введено сообщение, указанное в $string. принимается во внимание только если для этого сообщения нет перевода или $string не совпадает с defaultLanguage 	public function choice($string, $x, $args=null, $language=null) {				if (!empty($this->messages[$string])) {			$string = $this->messages[$string];			$language = $this->language;		} else {			$language = isset($language) ? $language : $this->defaultLanguage;		}				$stringsArray = explode('|', $string);				$args = isset($args) ? $args : array($x);				return vsprintf($stringsArray[$this->pluralChoice($x, $language)], $args);	}			// правила 	public function pluralChoice($x, $language) {        /*         * The plural rules are derived from code of the Zend Framework (2010-09-25),         * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).         * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)         */        switch ($language) {            case 'bo':            case 'dz':            case 'id':            case 'ja':            case 'jv':            case 'ka':            case 'km':            case 'kn':            case 'ko':            case 'ms':            case 'th':            case 'tr':            case 'vi':            case 'zh':                return 0;                break;            case 'af':            case 'az':            case 'bn':            case 'bg':            case 'ca':            case 'da':            case 'de':            case 'el':            case 'en':            case 'eo':            case 'es':            case 'et':            case 'eu':            case 'fa':            case 'fi':            case 'fo':            case 'fur':            case 'fy':            case 'gl':            case 'gu':            case 'ha':            case 'he':            case 'hu':            case 'is':            case 'it':            case 'ku':            case 'lb':            case 'ml':            case 'mn':            case 'mr':            case 'nah':            case 'nb':            case 'ne':            case 'nl':            case 'nn':            case 'no':            case 'om':            case 'or':            case 'pa':            case 'pap':            case 'ps':            case 'pt':            case 'so':            case 'sq':            case 'sv':            case 'sw':            case 'ta':            case 'te':            case 'tk':            case 'ur':            case 'zu':                return ($x == 1) ? 0 : 1;            case 'am':            case 'bh':            case 'fil':            case 'fr':            case 'gun':            case 'hi':            case 'ln':            case 'mg':            case 'nso':            case 'xbr':            case 'ti':            case 'wa':                return (($x == 0) || ($x == 1)) ? 0 : 1;            case 'be':            case 'bs':            case 'hr':            case 'ru':            case 'sr':            case 'uk':                return (($x % 10 == 1) && ($x % 100 != 11)) ? 0 : ((($x % 10 >= 2) && ($x % 10 <= 4) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2);            case 'cs':            case 'sk':                return ($x == 1) ? 0 : ((($x >= 2) && ($x <= 4)) ? 1 : 2);            case 'ga':                return ($x == 1) ? 0 : (($x == 2) ? 1 : 2);            case 'lt':                return (($x % 10 == 1) && ($x % 100 != 11)) ? 0 : ((($x % 10 >= 2) && (($x % 100 < 10) || ($x % 100 >= 20))) ? 1 : 2);            case 'sl':                return ($x % 100 == 1) ? 0 : (($x % 100 == 2) ? 1 : ((($x % 100 == 3) || ($x % 100 == 4)) ? 2 : 3));            case 'mk':                return ($x % 10 == 1) ? 0 : 1;            case 'mt':                return ($x == 1) ? 0 : ((($x == 0) || (($x % 100 > 1) && ($x % 100 < 11))) ? 1 : ((($x % 100 > 10) && ($x % 100 < 20)) ? 2 : 3));            case 'lv':                return ($x == 0) ? 0 : ((($x % 10 == 1) && ($x % 100 != 11)) ? 1 : 2);            case 'pl':                return ($x == 1) ? 0 : ((($x % 10 >= 2) && ($x % 10 <= 4) && (($x % 100 < 12) || ($x % 100 > 14))) ? 1 : 2);            case 'cy':                return ($x == 1) ? 0 : (($x == 2) ? 1 : ((($x == 8) || ($x == 11)) ? 2 : 3));            case 'ro':                return ($x == 1) ? 0 : ((($x == 0) || (($x % 100 > 0) && ($x % 100 < 20))) ? 1 : 2);            case 'ar':                return ($x == 0) ? 0 : (($x == 1) ? 1 : (($x == 2) ? 2 : ((($x >= 3) && ($x <= 10)) ? 3 : ((($x >= 11) && ($x <= 99)) ? 4 : 5))));            default:                return 0;        }	}	}