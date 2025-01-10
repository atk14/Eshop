<?php
/**
 *
 * @fixture stores
 * @fixture delivery_methods
 */
class TcStore extends TcBase {

	function test(){
		
		$store = $this->stores["test"];

		$this->assertEquals("Street\n111 00 City",$store->getAddress());
		$this->assertStringContains("Street\n111 00 City\nCzech",$store->getAddress(array("with_country" => true))); // "Czechia" or "Czech Republic"

		$this->assertEquals("Street, 111 00 City",$store->getAddress(["connector" => ", "]));
		$this->assertEquals("Testing store, Street, 111 00 City",$store->getAddress(["connector" => ", ", "with_name" => true]));

		$store->s("address_en","\n\nTESTING STORE as Street in City\n\n");
		$this->assertEquals("TESTING STORE as Street in City",$store->getAddress());

		// pouziti SpecialOpeningHour

		$groceries = $this->stores["groceries"];

		$this->assertTrue($groceries->isOpen("2019-01-15 08:30:00"));
		SpecialOpeningHour::CreateNewRecord([
			"store_id" => $groceries,
			"date" => "2019-01-15",
 			// zavreno
			"opening_hours1" => null,
			"opening_hours2" => null,
		]);
		$this->assertFalse($groceries->isOpen("2019-01-15 08:30:00"));

		$this->assertFalse($groceries->isOpen("2019-07-10 09:00:00")); // ve stredu je zavreno
		SpecialOpeningHour::CreateNewRecord([
			"store_id" => $groceries,
			"date" => "2019-07-10",
 			// zavreno
			"opening_hours1" => 8.0,
			"opening_hours2" => 12.0,
		]);
		$this->assertTrue($groceries->isOpen("2019-07-10 09:00:00"));
	}

	function test_isDeletable(){
		$store = $this->stores["test"];
		$eshop = Store::GetInstanceByCode("eshop");
		$just_store = Store::CreateNewRecord(array());

		$this->assertFalse($store->isDeletable()); // there is a delivery method with personal pickup on this store
		$this->assertFalse($eshop->isDeletable()); // code=eshop
		$this->assertTrue($just_store->isDeletable());
	}
}
