<?php
/**
 * @fixture delivery_services
 */
class TcDeliveryService extends TcBase {

	function setUp() {
		parent::setUp();

		Cache::Clear();
		SystemParameter::ClearCache();
	}

	function test_get_branches_url() {
		# default parser class is generated by delivery service code
		$this->assertEquals("DeliveryService\BranchParser\Posta", $this->delivery_services["posta"]->getParserClass());
		$this->assertEquals("DeliveryService\BranchParser\Zasilkovna", $this->delivery_services["zasilkovna"]->getParserClass());

		$sys_param = SystemParameter::GetInstanceByCode("delivery_services.zasilkovna.api_key");
		$sys_param->s("content",null);

		# setting own parsing class
		$this->delivery_services["posta"]->setParserClass("DeliveryService\BranchParser\CpBalikNaPostu");
		$this->assertEquals("https://www.zasilkovna.cz/api/v4/{API_KEY}/branch.xml", $this->delivery_services["zasilkovna"]->getBranchesDownloadUrl());
		$this->assertEquals("http://napostu.ceskaposta.cz/vystupy/napostu.xml", $this->delivery_services["posta"]->getBranchesDownloadUrl());

		# bez api key vracime stale url s placeholderem
		$this->assertEquals("https://www.zasilkovna.cz/api/v4/{API_KEY}/branch.xml", $this->delivery_services["zasilkovna"]->getBranchesDownloadUrl());

		# se zadanym api key iz vracime kompletni url
		$sys_param->s("content", "abcdef0123456789");
		Cache::Clear();
		SystemParameter::ClearCache();
		$this->assertEquals("https://www.zasilkovna.cz/api/v4/abcdef0123456789/branch.xml", $this->delivery_services["zasilkovna"]->getBranchesDownloadUrl());
	}

	function test_can_be_used() {
		$this->delivery_services["posta"]->setParserClass("DeliveryService\BranchParser\CpBalikNaPostu");

		$sys_param = SystemParameter::GetInstanceByCode("delivery_services.zasilkovna.api_key");
		$sys_param->s("content",null);

		$this->assertFalse($this->delivery_services["zasilkovna"]->canBeUsed());
		$this->assertTrue($this->delivery_services["posta"]->canBeUsed());

		$this->assertFalse($this->delivery_services["zasilkovna"]->canBeUsed());
		$sys_param->s("content", "abcdef0123456789");

		Cache::Clear();
		SystemParameter::ClearCache();
		$this->assertTrue($this->delivery_services["zasilkovna"]->canBeUsed());
	}
}
