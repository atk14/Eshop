<?php
class RegionsField extends MultipleChoiceField {

	var $json_encode;

	/**
	 * Constructor
	 *
	 * @param array $options For options {@see Field class} or {@link Field class}
	 */
	function __construct($options=array())
	{
		$all_regions = Region::GetAllInstances();

		$choices = [];
		foreach($all_regions as $r){
			$choices[$r->getCode()] = $r->getName();
		}

		$options += array(
			"choices" => $choices,
			"widget" => new CheckboxSelectMultiple(),
			"json_encode" => false,
			"initial" => [], // "__all__"
			"required" => true,
		);

		$this->json_encode = $options["json_encode"];
		unset($options["json_encode"]);

		if((!is_array($options["initial"]) && $options["initial"]=="__all__") || ($options["required"] && sizeof($all_regions)==1)){
			$initial = [];
			foreach($all_regions as $r){
				$initial[$r->getCode()] = true;
			}
			$options["initial"] = $initial;
		}

		parent::__construct($options);
	}

	/**
	 * Validating method
	 *
	 * @param mixed $value
	 */
	function clean($value)
	{
		list($error, $values) = parent::clean($value);
		if($error) {
			return [$error, null];
		}
		$out = array_fill_keys($values, true) + array_fill_keys(array_keys($this->choices), false);

		if($this->json_encode){
			$out = json_encode($out);
		}

		return [ null, $out ];
	}

	function format_initial_data($value) {
		if(is_string($value)) {
			$value = json_decode($value);
		}
		if(is_object($value)) {
			$value = (array) $value;
		}
		if(!$value) { return []; };
		return array_keys(array_filter($value));
	}
}
