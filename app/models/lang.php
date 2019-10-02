<?php
/**
 * Trida pro jazyk z config/locale.yml
 *
 * $langs = Lang::GetInstances();
 */
class Lang {

	/**
	 *
	 * $lang = new Lang("cs", ["LANG" => "cs_CZ.UTF-8", "name" => "česky"]);
	 */
	protected function __construct($lang,$data){
		$this->lang = $lang;
		$this->data = $data;
	}

	static function GetInstances(){
		global $ATK14_GLOBAL;

		$out = [];

		$langs = $ATK14_GLOBAL->getConfig("locale");
		foreach($langs as $lang => $data){
			$out[] = new Lang($lang,$data);
		}

		return $out;
	}

	/**
	 *
	 *	$current_lang = Lang::GetCurrentLang();
	 *	echo $current_lang->getLang(); // "cs"
	 */
	static function GetCurrentLang(){
		global $ATK14_GLOBAL;

		foreach(self::GetInstances() as $l){
			if($l->getLang()==$ATK14_GLOBAL->getLang()){
				return $l;
			}
		}
	}

	/**
	 *
	 *	echo $lang->getId(); // "cs"
	 */
	function getId(){
		return $this->getLang();
	}

	/**
	 * echo $lang->getLang(); // "cs"
	 */
	function getLang(){
		return $this->lang;
	}

	/**
	 * echo $lang->getName(); // "česky"
	 */
	function getName(){
		return $this->data["name"];
	}

	/**
	 * echo $lang->getFallback(); // "en", null
	 */
	function getFallback(){
		return isset($this->data["fallback"]) ? $this->data["fallback"] : null;
	}

	/**
	 *
	 *	$lang = Lang::GetCurrentLang();
	 *	echo "$lang"; // "cs"
	 */
	function toString(){ return $this->getLang(); }
	function __toString(){ return $this->toString(); }

}
