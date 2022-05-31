<?php
/**
 * @fixture currencies
 */
class TcDisplayPrice extends TcBase {

	function test(){
		Atk14Require::Helper("modifier.display_price");

		$this->assertEquals("123 CZK",smarty_modifier_display_price(123.45,"CZK,show_decimals_on_czk=false,format=plain"));
		$this->assertEquals("123.45 EUR",smarty_modifier_display_price(123.45,"EUR,show_decimals_on_czk=false,format=plain"));
		$this->assertEquals("123.45000000 BTC",smarty_modifier_display_price(123.45,"BTC,show_decimals_on_czk=false,format=plain"));

		$this->assertEquals("123.45000000 BTC",smarty_modifier_display_price(123.45,["currency" => $this->currencies["bitcoin"], "show_decimals_on_czk" => false, "format" => "plain"]));
		$this->assertEquals("123 BTC",smarty_modifier_display_price(123.45,["currency" => $this->currencies["bitcoin"], "show_decimals_on_btc" => false, "format" => "plain"]));

		// summary CZK
		$this->assertEquals("123.45 CZK",smarty_modifier_display_price(123.45,"CZK,format=plain"));
		$this->assertEquals("123 CZK",smarty_modifier_display_price(123.45,"CZK,summary,format=plain"));

		// summary EUR
		$this->assertEquals("123.45 EUR",smarty_modifier_display_price(123.45,"EUR,format=plain"));
		$this->assertEquals("123.45 EUR",smarty_modifier_display_price(123.45,"EUR,summary,format=plain"));

		// locale
		$lang = "cs";
		Atk14Locale::Initialize($lang);
		$this->assertEquals("123,45 Kč",smarty_modifier_display_price(123.45,"CZK,format=plain"));
	}
}
