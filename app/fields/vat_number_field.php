<?php
/**
 * Vystupem tohoto pole je objekt typu VatNumber
 */
class VatNumberField extends CharField {

	// https://www.finanz.at/en/taxes/vat-number/
	static protected $Patterns = [
		"AT" => 'ATU\d{8}',
		"BE" => 'BE\d{10}',
		"BG" =>	'BG\d{9,10}',
		"CY" => 'CY\d{9}',
		"CZ" => 'CZ\d{8,10}',
		"DE" => 'DE\d{9}',
		"DK" => 'DK\d{8}',
		"EE" => 'EE\d{9}',
		"ES" => 'ES(\d{9}|[A-Z]\d{8}|\d{8}[A-Z]|[A-Z]\d{7}[A-Z])', // Spain - ES + 9 digits (first and last can be letters)
		"FI" => 'FI\d{8}',
		"FR" => 'FR\d{11}',
		"GB" => 'GB\d{9}',
		"HR" => 'HR\d{11}',
		"HU" => 'HU\d{8}',
		"IE" => 'IE\d{8,9}',
		"IT" => 'IT\d{11}',
		"LT" => 'LT\d{9,12}',
		"LU" => 'LU\d{8}',
		"LV" => 'LV\d{11}',
		"MT" => 'MT\d{8}',
		"NL" => 'NL\d{12}',
		"PL" => 'PL\d{10}',
		"PT" => 'PT\d{9}',
		"RO" => 'RO\d{10}',
		"SE" => 'SE\d{12}',
		"SI" => 'SI\d{8}',
		"SK" => 'SK\d{9,10}',

		"*" => '[A-Z]{2}\d{8,12}', // fallback
	];

	public $enable_validation;

	function __construct($options = []){
		$options += [
			"enable_validation" => false, // zapne validaci DIC v systemu VIES: http://ec.europa.eu/taxation_customs/vies/vatResponse.html
			"trim_value" => true,
			"null_empty_output" => true,
		];
		$this->enable_validation = $options["enable_validation"];
		unset($options["enable_validation"]);

		parent::__construct($options);
		
		$this->update_messages(array(
			"invalid" => _("DIČ není zadáno správně"),
		));
	}

	function clean($value){
		$value = (string)$value;
		$value = trim($value);
		$value = strtoupper($value); // "cz12345678" -> "CZ12345678"

		$value = preg_replace('/\s/','',$value); // "CZ 12345678" -> "CZ12345678"

		list($err,$value) = parent::clean($value);

		if(!strlen((string)$value)){
			if(is_null($err)){
				$value = new VatNumber(["vat_number" => "", "is_valid_for_cross_border_transactions_within_eu" => null]);
			}
			return [$err,$value];
		}

		$country_code = substr($value,0,2);
		$pattern = isset(self::$Patterns[$country_code]) ? self::$Patterns[$country_code] : self::$Patterns["*"];
		if(!preg_match('/^'.$pattern.'$/',$value)){
			$err = $this->messages["invalid"];
			return [$err,null];
		}

		if(!extension_loaded("soap")){
			trigger_error("Exception soap is not loaded");
			$value = new VatNumber(["vat_number" => $value, "is_valid_for_cross_border_transactions_within_eu" => null]);
			return [$err,$value];
		}

		$vies = new MyVies();
		$value = $vies->filterVat($value); // tady je dalsi filtrace podivnych znaku

		if($this->enable_validation){
			//$vies = new \DragonBe\Vies\Vies();

			$is_valid = null;
			$is_alive = null;

			//
			try {
				$is_alive = $vies->getHeartBeat()->isAlive();
			} catch(Exception $e){
				// hmmm... 
			}
			if(!$is_alive){
				trigger_error('\DragonBe\Vies\Vies: service is not available at the moment, failed to validate VAT number '.$value);
			}

			if($is_alive)	{
				$countryCode = substr($value,0,2);
				$vatNumber = substr($value,2);

				try {
					$result = $vies->validateVat($countryCode, $vatNumber); // - Validation routine worked as expected.
					$is_valid = $result->isValid();
				} catch (DragonBe\Vies\ViesServiceException $e) {
					trigger_error('\DragonBe\Vies\Vies: online validation temporarily failed on VAT number '.$value);
					// Temporary error...
					// do nothing
				} catch (\DragonBe\Vies\ViesException $e) {
					// invalid_country_code; just do nothing
				}
			}

			$value = new VatNumber(["vat_number" => $value, "is_valid_for_cross_border_transactions_within_eu" => $is_valid]);

		}else{

			$value = new VatNumber(["vat_number" => $value, "is_valid_for_cross_border_transactions_within_eu" => null]);

		}

		return [$err,$value];
	}
}
