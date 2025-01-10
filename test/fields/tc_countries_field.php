<?php
class TcCountriesField extends TcBase {

	function test(){
		$this->field = $f = new CountriesField([
			"json_encode" => true,
			"required" => false,
		]);

		$value = $this->assertValid("CZ,SK");
		$this->assertEquals('["CZ","SK"]',$value);

		$value = $this->assertValid(" CZ , pl ");
		$this->assertEquals('["CZ","PL"]',$value);

		$value = $this->assertValid("");
		$this->assertEquals('[]',$value);

		$msg = $this->assertInvalid("CZ,XX");
		$this->assertEquals('Country with code XX does not exist.',$msg);

		$msg = $this->assertInvalid("CZ,SK,CZ");
		$this->assertEquals('Country code CZ is given more than once.',$msg);

		$msg = $this->assertInvalid(",");
		$this->assertEquals('Please, write comma-separated list of country codes',$msg);

		//

		$this->field = $f = new CountriesField([
			"json_encode" => false,
		]);

		$value = $this->assertValid("CZ,SK");
		$this->assertEquals(["CZ","SK"],$value);

		//

		$this->field = $f = new CountriesField([
			"required" => true,
		]);

		$mesg = $this->assertInvalid("");
		$this->assertEquals('Please, write comma-separated list of country codes',$msg);
	}

	function test_format_initial_value(){
		$form = new Atk14Form();

		$form["countries"] = new CountriesField([
			"initial" => '["CZ","SK"]'
		]);
		$this->assertStringContains('value="CZ, SK"',$form["countries"]->as_widget());

		$form["countries"] = new CountriesField([
			"initial" => ["PL","HU"]
		]);
		$this->assertStringContains('value="PL, HU"',$form["countries"]->as_widget());

		$form["countries"] = new CountriesField([
			"initial" => "CZ, PL"
		]);
		$this->assertStringContains('value="CZ, PL"',$form["countries"]->as_widget());
	}
}
