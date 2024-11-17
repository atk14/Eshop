<?php
/**
 *
 * @fixture regions
 * @fixture users
 * @fixture vouchers
 * @fixture campaigns
 * @fixture campaign_excluded_for_tags
 * @fixture campaign_designated_for_tags
 * @fixture tags
 * @fixture cards
 * @fixture products
 * @fixture card_tags
 * @fixture pricelist_items
 * @fixture warehouse_items
 * @fixture delivery_services
 * @fixture delivery_service_branches
 * @fixture delivery_methods
 * @fixture delivery_method_country_specifications
 * @fixture payment_methods
 * @fixture discounts
 * @fixture shipping_combinations
 * @fixture currencies
 * @fixture currency_rates
 */
class TcBasket extends TcBase {

	function test_GetDummyBasket(){
		$def_region = Region::GetDefaultRegion();
		$eu = $this->regions["EU"];
		$rambo = $this->users["rambo"];

		$basket = Basket::GetDummyBasket();
		$this->assertTrue($basket->isDummy());
		$this->assertEquals($def_region->getId(),$basket->getRegionId());
		$this->assertNull($basket->getUser());
		$this->assertEquals($def_region->getDefaultCurrency()->getId(),$basket->getCurrencyId());

		$bitcoin = $this->currencies["bitcoin"];
		$basket = Basket::GetDummyBasket($eu,$rambo,$bitcoin);
		$this->assertTrue($basket->isDummy());
		$this->assertEquals($eu->getId(),$basket->getRegionId());
		$this->assertEquals($rambo->getId(),$basket->getUserId());
		$this->assertEquals($bitcoin->getId(),$basket->getCurrencyId());
	}

	function test_CreateNewRecord4UserAndRegion(){
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];

		$kveta = Cache::Get("User",$kveta); // make sure to use the cached instance
	
		// A) Kvetina adresa je ze zeme, kam se dorucuje -> adresa bude nastavena v kosiku jako dorucovaci; fakturacni nebude vyplnena
		$this->assertTrue(in_array($kveta->getAddressCountry(),$czechoslovakia->getDeliveryCountries()));
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$this->assertEquals("Ambrozova 9",$basket->g("delivery_address_street"));
		$this->assertEquals(null,$basket->g("address_street"));
		$basket->destroy();

		// B) Kvetina adresa je ted z jine zeme, nez kam se dorucuje -> v kosiku bude nastavena fakt. adresa, ale dorucovaci musi zustat prazdna
		$kveta->s("address_country","AT"); // Austria
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$this->assertEquals(null,$basket->g("delivery_address_street"));
		$this->assertEquals("Ambrozova 9",$basket->g("address_street"));
		$basket->destroy();

		// C) Kveta ted bude mit posledni pouzitou dorucovaci adresu stejnou jako svoji fakturacni -> je to stejny pripad jako pokus (A)
		$kveta->s("address_country","CZ");
		$delivery_address = DeliveryAddress::CreateNewRecord([
			"user_id" => $kveta,
			"firstname" => $kveta->g("firstname"),
			"lastname" => $kveta->g("lastname"),
			"company" => $kveta->g("company"),
			"address_street" => $kveta->g("address_street"),
			"address_street2" => $kveta->g("address_street2"),
			"address_city" => $kveta->g("address_city"),
			"address_state" => $kveta->g("address_state"),
			"address_zip" => $kveta->g("address_zip"),
			"address_country" => $kveta->g("address_country"),
			"phone" => $kveta->g("phone"),
		]);
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$this->assertEquals("Ambrozova 9",$basket->g("delivery_address_street"));
		$this->assertEquals(null,$basket->g("address_street"));
		$basket->destroy();

		// D) Ted bude posledni dorucovaci adresa jina nez fakturacni -> v kosiku budou obe adresy
		$delivery_address->s("address_street","Biskupcova 19");
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$this->assertEquals("Biskupcova 19",$basket->g("delivery_address_street"));
		$this->assertEquals("Ambrozova 9",$basket->g("address_street"));
		$basket->destroy();
	}

	function test_getting_delivery_address_values(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$basket->s([
			"delivery_address_state" => "Kraj Praha",
			"delivery_address_city" => "Praha 3",
			"delivery_address_note" => "2nd floor"
		]);

		$this->assertEquals("Kveta",$basket->getDeliveryFirstname());
		$this->assertEquals("Latkova",$basket->getDeliveryLastname());
		$this->assertEquals("Ambrozova 9",$basket->getDeliveryAddressStreet());
		$this->assertEquals("u kina Aero",$basket->getDeliveryAddressStreet2());
		$this->assertEquals("Praha 3",$basket->getDeliveryAddressCity());
		$this->assertEquals("Kraj Praha",$basket->getDeliveryAddressState());
		$this->assertEquals("130 00",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());
		$this->assertEquals("2nd floor",$basket->getDeliveryAddressNote());

		$zasilkovna_1 = $this->delivery_service_branches["zasilkovna_1"];
		$basket->s([
			"delivery_method_id" => $this->delivery_methods["zasilkovna"],
			"delivery_method_data" => $zasilkovna_1->getDeliveryMethodData()
		]);

		$this->assertEquals("Kveta",$basket->getDeliveryFirstname());
		$this->assertEquals("Latkova",$basket->getDeliveryLastname());
		$this->assertEquals("Zásilková I.",$basket->getDeliveryAddressStreet());
		$this->assertEquals(null,$basket->getDeliveryAddressStreet2());
		$this->assertEquals("Praha",$basket->getDeliveryAddressCity());
		$this->assertEquals(null,$basket->getDeliveryAddressState());
		$this->assertEquals("123 45",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());
		$this->assertEquals(null,$basket->getDeliveryAddressNote());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["post_cod"],
			"delivery_method_data" => null
		]);

		$this->assertEquals("Kveta",$basket->getDeliveryFirstname());
		$this->assertEquals("Latkova",$basket->getDeliveryLastname());
		$this->assertEquals("Ambrozova 9",$basket->getDeliveryAddressStreet());
		$this->assertEquals("u kina Aero",$basket->getDeliveryAddressStreet2());
		$this->assertEquals("Praha 3",$basket->getDeliveryAddressCity());
		$this->assertEquals("Kraj Praha",$basket->getDeliveryAddressState());
		$this->assertEquals("130 00",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());
		$this->assertEquals("2nd floor",$basket->getDeliveryAddressNote());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["personal"],
		]);

		// Data from the test store, see test/fixtures/stores.yml
		$this->assertEquals("Kveta",$basket->getDeliveryFirstname());
		$this->assertEquals("Latkova",$basket->getDeliveryLastname());
		$this->assertEquals("Testing store",$basket->getDeliveryCompany());
		$this->assertEquals("Street",$basket->getDeliveryAddressStreet());
		$this->assertEquals(null,$basket->getDeliveryAddressStreet2());
		$this->assertEquals("City",$basket->getDeliveryAddressCity());
		$this->assertEquals(null,$basket->getDeliveryAddressState());
		$this->assertEquals("111 00",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());
		$this->assertEquals(null,$basket->getDeliveryAddressNote());
	}

	function test_deliveryAddressEditableByUser(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$this->assertEquals(true,$basket->deliveryAddressEditableByUser());
		$this->assertEquals(false,$basket->deliveryToDeliveryPointSelected());
		$this->assertEquals(false,$basket->personalPickupOnStoreSelected());

		$basket->s("delivery_method_id",$this->delivery_methods["zasilkovna"]);
		$this->assertEquals(false,$basket->deliveryAddressEditableByUser());
		$this->assertEquals(true,$basket->deliveryToDeliveryPointSelected());
		$this->assertEquals(false,$basket->personalPickupOnStoreSelected());

		$basket->s("delivery_method_id",$this->delivery_methods["personal"]);
		$this->assertEquals(false,$basket->deliveryAddressEditableByUser());
		$this->assertEquals(false,$basket->deliveryToDeliveryPointSelected());
		$this->assertEquals(true,$basket->personalPickupOnStoreSelected());

		$basket->s("delivery_method_id",$this->delivery_methods["dpd_test"]);
		$this->assertEquals(true,$basket->deliveryAddressEditableByUser());
		$this->assertEquals(false,$basket->deliveryToDeliveryPointSelected());
		$this->assertEquals(false,$basket->personalPickupOnStoreSelected());
	}

	function test_getting_address_values(){
		$basket = Basket::CreateNewRecord4UserAndRegion(User::GetAnonymousUser(),$this->regions["czechoslovakia"]);

		$this->assertFalse($basket->hasAddressSet());
		$this->assertFalse($basket->hasDeliveryAddressSet());

		$this->assertEquals(null,$basket->getFirstname());
		$this->assertEquals(null,$basket->getLastname());
		$this->assertEquals(null,$basket->getAddressStreet());
		$this->assertEquals(null,$basket->getAddressStreet2());
		$this->assertEquals(null,$basket->getAddressCity());
		$this->assertEquals(null,$basket->getAddressState());
		$this->assertEquals(null,$basket->getAddressZip());
		$this->assertEquals(null,$basket->getAddressCountry());
		
		$basket->s([
			"delivery_firstname" => "Bobina",
			"delivery_lastname" => "Drozdová",
			"delivery_address_street" => "Ambrozova 9",
			"delivery_address_street2" => "u kina Aero",
			"delivery_address_state" => "Kraj Praha",
			"delivery_address_city" => "Praha 3",
			"delivery_address_zip" => "130 00",
			"delivery_address_country" => "CZ",
			"delivery_method_id" => $this->delivery_methods["zasilkovna"],
			"delivery_method_data" => $this->delivery_service_branches["zasilkovna_1"]->getDeliveryMethodData(),
		]);
		$this->assertEquals(true,$basket->hasDeliveryAddressSet());

		$this->assertFalse($basket->hasAddressSet());
		$this->assertTrue($basket->hasDeliveryAddressSet());

		$this->assertEquals("Bobina",$basket->getFirstname());
		$this->assertEquals("Drozdová",$basket->getLastname());
		$this->assertEquals(null,$basket->getAddressStreet());
		$this->assertEquals(null,$basket->getAddressStreet2());
		$this->assertEquals(null,$basket->getAddressCity());
		$this->assertEquals(null,$basket->getAddressState());
		$this->assertEquals(null,$basket->getAddressZip());
		$this->assertEquals(null,$basket->getAddressCountry());

		$this->assertEquals("Bobina",$basket->getDeliveryFirstname());
		$this->assertEquals("Drozdová",$basket->getDeliveryLastname());
		$this->assertEquals("Zásilková I.",$basket->getDeliveryAddressStreet());
		$this->assertEquals(null,$basket->getDeliveryAddressStreet2());
		$this->assertEquals(null,$basket->getDeliveryAddressState());
		$this->assertEquals("Praha",$basket->getDeliveryAddressCity());
		$this->assertEquals("123 45",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["dpd_test"],
		]);

		$this->assertFalse($basket->hasAddressSet());
		$this->assertTrue($basket->hasDeliveryAddressSet());

		$this->assertEquals("Bobina",$basket->getFirstname());
		$this->assertEquals("Drozdová",$basket->getLastname());
		$this->assertEquals("Ambrozova 9",$basket->getAddressStreet());
		$this->assertEquals("u kina Aero",$basket->getAddressStreet2());
		$this->assertEquals("Kraj Praha",$basket->getAddressState());
		$this->assertEquals("Praha 3",$basket->getAddressCity());
		$this->assertEquals("130 00",$basket->getAddressZip());
		$this->assertEquals("CZ",$basket->getAddressCountry());

		$this->assertEquals("Bobina",$basket->getDeliveryFirstname());
		$this->assertEquals("Drozdová",$basket->getDeliveryLastname());
		$this->assertEquals("Ambrozova 9",$basket->getDeliveryAddressStreet());
		$this->assertEquals("u kina Aero",$basket->getDeliveryAddressStreet2());
		$this->assertEquals("Kraj Praha",$basket->getDeliveryAddressState());
		$this->assertEquals("Praha 3",$basket->getDeliveryAddressCity());
		$this->assertEquals("130 00",$basket->getDeliveryAddressZip());
		$this->assertEquals("CZ",$basket->getDeliveryAddressCountry());
	}

	function test_addProduct(){
		$mint_tea = $this->products["mint_tea"];
		$black_tea = $this->products["black_tea"];

		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);

		$this->assertEquals(true,$basket->isEmpty());
		$this->assertEquals(0,sizeof($basket->getItems()));

		$basket->addProduct($mint_tea,2);
		$items = $basket->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals($mint_tea->getId(),$items[0]->getProductId());
		$this->assertEquals(2,$items[0]->getAmount());

		$basket->addProduct($mint_tea,1);
		$items = $basket->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals($mint_tea->getId(),$items[0]->getProductId());
		$this->assertEquals(1,$items[0]->getAmount()); // amount rewritten
		
		$basket->addProduct($mint_tea,3,["rewrite_amount" => false]);
		$items = $basket->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals($mint_tea->getId(),$items[0]->getProductId());
		$this->assertEquals(4,$items[0]->getAmount()); // amount increased

		$basket->addProduct($black_tea);
		$items = $basket->getItems();
		$this->assertEquals(2,sizeof($basket->getItems()));
		$this->assertEquals($black_tea->getId(),$items[0]->getProductId()); // last added product
		$this->assertEquals($mint_tea->getId(),$items[1]->getProductId());

		$basket->addProduct($mint_tea,1,["update_rank" => false]);
		$items = $basket->getItems();
		$this->assertEquals(2,sizeof($basket->getItems()));
		$this->assertEquals($black_tea->getId(),$items[0]->getProductId());
		$this->assertEquals($mint_tea->getId(),$items[1]->getProductId()); // last added product
	}

	function test_createOrder_integrity_key(){
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$basket->s(array(
			"delivery_method_id" => $this->delivery_methods["personal"],
			"payment_method_id" => $this->payment_methods["cash_on_delivery"]
		));

		$order = $basket->createOrder();
		$this->assertEquals((string)$basket->getId(),$order->getIntegrityKey());

		// another try to create new order from the same basket must fail with an exception
		$expcetion_thrown = false;
		try {
			@$order = $basket->createOrder();
		}catch(Exception $e){
			$expcetion_thrown = true;
		}
		$this->assertEquals(true,$expcetion_thrown);
	}

	function test_price(){
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);

		$basket->addProduct($this->products["mint_tea"],2);

		$items = $basket->getItems();
		$item = $items[0];

		$this->assertEquals(20.0,$item->getUnitPrice());
		$this->assertEquals(24.2,$item->getUnitPriceInclVat());
		$this->assertEquals(40.0,$item->getPrice());
		$this->assertEquals(48.4,$item->getPriceInclVat());
	}

	function test_merge(){
		$basket1 = Basket::CreateNewRecord(["note" => "Please deliver quickly"]);
		$basket2 = Basket::CreateNewRecord([]);

		$basket1->getVouchersLister()->add($this->vouchers["free_shipping"]);
		$basket1->getVouchersLister()->add($this->vouchers["percentage_discount"]);

		$basket2->getVouchersLister()->add($this->vouchers["percentage_discount"]);

		$basket2->mergeBasket($basket1);

		$this->assertEquals("Please deliver quickly",$basket2->getNote());

		$vouchers = $basket2->getVouchers();
		$this->assertEquals(2,sizeof($vouchers));

		$this->assertEquals($this->vouchers["percentage_discount"]->getId(),$vouchers[0]->getVoucherId());
		$this->assertEquals($this->vouchers["free_shipping"]->getId(),$vouchers[1]->getVoucherId());

		// --

		$basket1 = Basket::CreateNewRecord([]);
		$basket2 = Basket::CreateNewRecord(["note" => "Original note"]);

		$basket2->mergeBasket($basket1);

		$this->assertEquals("Original note",$basket2->getNote());

		// --

		$basket1 = Basket::CreateNewRecord(["note" => "New note"]);
		$basket2 = Basket::CreateNewRecord(["note" => "Original note"]);

		$basket2->mergeBasket($basket1);

		$this->assertEquals("New note",$basket2->getNote());

		// --

		$basket1 = Basket::CreateNewRecord(["delivery_method_id" => $this->delivery_methods["dpd"]->getId()]);
		$basket2 = Basket::CreateNewRecord(["delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(), "delivery_method_data" => '{"external_branch_id":"10000","delivery_service_id":3,"delivery_service_code":"fake-service"}']);

		$basket2->mergeBasket($basket1);

		$this->assertEquals($this->delivery_methods["dpd"]->getId(),$basket2->g("delivery_method_id"));
		$this->assertEquals(null,$basket2->g("delivery_method_data"));

		// --

		$basket1 = Basket::CreateNewRecord(["delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(), "delivery_method_data" => '{"external_branch_id":"10000","delivery_service_id":3,"delivery_service_code":"fake-service"}']);
		$basket2 = Basket::CreateNewRecord(["delivery_method_id" => $this->delivery_methods["dpd"]->getId()]);

		$basket2->mergeBasket($basket1);

		$this->assertEquals($this->delivery_methods["zasilkovna"]->getId(),$basket2->g("delivery_method_id"));
		$this->assertEquals('{"external_branch_id":"10000","delivery_service_id":3,"delivery_service_code":"fake-service"}',$basket2->g("delivery_method_data"));
	}

	function test_getCampaigns(){
		$basket = Basket::CreateNewRecord([
			"region_id" => $this->regions["czechoslovakia"]
		]);
		$this->assertEquals([],$basket->getCampaigns());
		$this->assertEquals(false,$basket->freeShipping());

		$campaing = Campaign::CreateNewRecord([
			"regions" => json_encode([$this->regions["czechoslovakia"]->getCode() => true]),
			"minimal_items_price_incl_vat" => 0.0,
			"discount_percent" => 0.0,
			"free_shipping" => true,
		]);

		$campaings = $basket->getCampaigns();
		$this->assertEquals(1,sizeof($campaings));
		$this->assertEquals($campaing->getId(),$campaings[0]->getCampaign()->getId());
		$this->assertEquals(true,$basket->freeShipping());

		// podminime kampan dopravou od DPD
		$dpd = $this->delivery_methods["dpd"];
		$parcel_service = $this->delivery_methods["parcel_service"];

		$campaing->s([
			"delivery_method_id" => $dpd,
		]);

		$campaings = $basket->getCampaigns();
		$this->assertEquals(0,sizeof($campaings));
		$this->assertEquals(false,$basket->freeShipping());

		$basket->s([
			"delivery_method_id" => $parcel_service,
		]);
		$this->assertEquals([],$basket->getCampaigns());
		$this->assertEquals(false,$basket->freeShipping());

		$basket->s([
			"delivery_method_id" => $dpd,
		]);
		$campaings = $basket->getCampaigns();
		$this->assertEquals(1,sizeof($campaings));
		$this->assertEquals($campaing->getId(),$campaings[0]->getCampaign()->getId());
		$this->assertEquals(true,$basket->freeShipping());
	}

	function test_contains(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$this->assertEquals(false,$basket->contains($this->products["green_tea"]));
		$this->assertEquals(false,$basket->contains($this->products["black_tea"]));
		$this->assertEquals(false,$basket->contains($this->cards["tea"]));

		$basket->setProductAmount($this->products["green_tea"],1);

		$this->assertEquals(true,$basket->contains($this->products["green_tea"]));
		$this->assertEquals(false,$basket->contains($this->products["black_tea"]));
		$this->assertEquals(true,$basket->contains($this->cards["tea"]));
	}

	function test_hasEveryProductTag_containsProductWithTag(){
		$tag = $this->tags["fun"];

		$tea_card = $this->cards["tea"];
		$book_card = $this->cards["book"];

		$black_tea = $this->products["black_tea"];
		$green_tea = $this->products["green_tea"];
		$book = $this->products["book"];

		//

		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);
		$this->assertFalse($basket->hasEveryProductTag($tag));
		$this->assertFalse($basket->containsProductWithTag($tag));

		$basket->addProduct($black_tea);
		$this->assertFalse($basket->hasEveryProductTag($tag));
		$this->assertFalse($basket->containsProductWithTag($tag));

		$black_tea->addTag($tag);
		$this->assertTrue($basket->hasEveryProductTag($tag));
		$this->assertTrue($basket->containsProductWithTag($tag));

		$basket->addProduct($book);
		$this->assertFalse($basket->hasEveryProductTag($tag));
		$this->assertTrue($basket->containsProductWithTag($tag));

		$book->addTag($tag);
		$this->assertFalse($basket->hasEveryProductTag($tag)); // it's because the book is not variant product
		$this->assertTrue($basket->containsProductWithTag($tag));

		$book_card->addTag($tag);
		$this->assertTrue($basket->hasEveryProductTag($tag));
		$this->assertTrue($basket->containsProductWithTag($tag));

		$basket->addProduct($green_tea);
		$this->assertFalse($basket->hasEveryProductTag($tag));
		$this->assertTrue($basket->containsProductWithTag($tag));

		$tea_card->addTag($tag);
		$this->assertTrue($basket->hasEveryProductTag($tag));
		$this->assertTrue($basket->containsProductWithTag($tag));
	}

	/**
	 * Test, ze s vyberem dorucovaci sluzby s pobockami je zvolena i pobocka
	 */
	function test_delivery_service_needs_delivery_branch() {
		$lang = "en";
		Atk14Locale::Initialize($lang);

		$basket = Basket::CreateNewRecord([
			"region_id" => $this->regions["czechoslovakia"]
		]);
		$basket->addProduct($this->products["mint_tea"], 5);

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"payment_method_id" => $this->payment_methods["bank_transfer"]->getId(),
		]);
		$this->assertFalse($basket->canOrderBeCreated($messages));
		$this->assertCount(1, $messages);
		$this->assertEquals("The dispensing point for order delivery has not been selected", (string)$messages[0]);

		$basket->s("delivery_method_data", $this->delivery_service_branches["zasilkovna_1"]->getDeliveryMethodData());
		$this->assertTrue($basket->canOrderBeCreated($messages));
	}

	function test_getChecksum(){
		$basket = Basket::CreateNewRecord([]);

		$green_tea = $this->products["green_tea"];
		$black_tea = $this->products["black_tea"];

		$checksum_empty = $basket->getChecksum();

		$basket->addProduct($green_tea);

		$checksum_1 = $basket->getChecksum();

		$basket->addProduct($black_tea);

		$checksum_2 = $basket->getChecksum();

		$this->assertTrue(strlen($checksum_empty)>0);
		$this->assertTrue(strlen($checksum_1)>0);
		$this->assertTrue(strlen($checksum_2)>0);

		$this->assertNotEquals($checksum_empty,$checksum_1);
		$this->assertNotEquals($checksum_empty,$checksum_2);
		$this->assertNotEquals($checksum_1,$checksum_2);

		//

		$basket1 = Basket::CreateNewRecord([]);
		$basket2 = Basket::CreateNewRecord([]);

		$this->assertEquals($basket1->getChecksum(),$basket2->getChecksum());

		$basket1->addProduct($black_tea,1);
		$basket2->addProduct($black_tea,2);

		$this->assertNotEquals($basket1->getChecksum(),$basket2->getChecksum());
		$this->assertEquals($basket1->getChecksum(["consider_products_amount" => false]),$basket2->getChecksum(["consider_products_amount" => false]));
	}

	function test_createOrder(){
		$CR = Region::FindByCode("CR");
		$SK = Region::FindByCode("SK");

		$EUR = Currency::GetInstanceByCode("EUR");
		$EUR->s("decimals_summary",1); // v ramci testovani nastavime 1 des. misto pro koncovou cenu, abychom meli zaokrouhlovani i na SK objednavce

		$campaign = Campaign::CreateNewRecord([
			"regions" => json_encode([$CR->getCode() => true]),
			"minimal_items_price_incl_vat" => 41, // je to vice nez 2 knofliky bez DPH, ktere budeme mit v kosiku
			"discount_percent" => 10,
		]);
		$campaign_sk = Campaign::CreateNewRecord([
			"regions" => json_encode([$SK->getCode() => true]),
			"minimal_items_price_incl_vat" => 41, // je to vice nez 2 knofliky bez DPH, ktere budeme mit v kosiku
			"discount_percent" => 10,
		]);
		$voucher = Voucher::CreateNewRecord([
			"voucher_code" => "TEST20",
			"repeatable" => true,
			"discount_amount" => 20,
			"regions" => '{"CR": true, "SK": true, "EU": true}'
		]);

		$basket = $this->_prepareBasketWithWoodenButton();

		$basket->getVouchersLister()->add($voucher);

		$order = $basket->createOrder();

		$this->assertEquals(false,$order->g("without_vat"));

		$this->assertEquals(100,$order->getDeliveryFee());
		$this->assertEquals(121,$order->getDeliveryFeeInclVat());
		$this->assertEquals(75.238095,$order->getPaymentFee());
		$this->assertEquals(79.0,$order->getPaymentFeeInclVat());

		$items = $order->getItems();
		$this->assertEquals(2,sizeof($items)); // wooden_button + price_rounding
		//
		$this->assertEquals(21.0,$items[0]->getVatPercent());
		$this->assertEquals(20,$items[0]->getUnitPrice());
		$this->assertEquals(24.2,$items[0]->getUnitPriceInclVat());
		$this->assertEquals(40,$items[0]->getPrice());
		$this->assertEquals(48.4,$items[0]->getPriceInclVat());
		$this->assertEquals(20,$items[0]->getUnitPriceBeforeDiscount());
		$this->assertEquals(24.2,$items[0]->getUnitPriceBeforeDiscountInclVat());
		//
		$this->assertEquals("price_rounding",$items[1]->getProduct()->getCode());
		$this->assertEquals(21.0,$items[1]->getVatPercent());
		$this->assertEquals(0.4400,round($items[1]->getPriceInclVat(),4));
		$this->assertEquals(0.44,$items[1]->g("unit_price_incl_vat"));
		$this->assertEquals(0.36,$items[1]->getUnitPrice()); // 0.3636
		$this->assertEquals(null,$items[1]->g("unit_price_before_discount_incl_vat"));

		$campaigns = $order->getCampaigns();
		$this->assertEquals(1,sizeof($campaigns));
		$this->assertEquals(4.84,$campaigns[0]->getDiscountAmount());
		$this->assertEquals(4.84,$campaigns[0]->g("discount_amount"));

		$vouchers = $order->getVouchers();
		$this->assertEquals(1,sizeof($vouchers));
		$this->assertEquals(20,$vouchers[0]->getDiscountAmount());
		$this->assertEquals(20,$vouchers[0]->g("discount_amount"));

		$this->assertEquals(round(121 + 79 + 48.4 - 4.84 - 20.0),$order->getPriceToPay());

		// SK objednavka v EUR
		$basket = $this->_prepareEmptyBasket([
			"region_id" => $SK,
		]);
		$basket = $this->_prepareBasketWithWoodenButton($basket);

		$basket->getVouchersLister()->add($voucher);
		$order = $basket->createOrder();

		$this->assertEquals(false,$order->g("without_vat"));

		$this->assertEquals(7.32381,$order->getDeliveryFee());
		$this->assertEquals(7.69,$order->getDeliveryFeeInclVat());
		$this->assertEquals(2.895238,$order->getPaymentFee());
		$this->assertEquals(3.04,$order->getPaymentFeeInclVat());

		$items = $order->getItems();
		$this->assertEquals(2,sizeof($items));
		//
		$this->assertEquals(21.0,$items[0]->getVatPercent());
		$this->assertEquals(0.77,$items[0]->getUnitPrice());
		$this->assertEquals(0.93,$items[0]->getUnitPriceInclVat());
		$this->assertEquals(1.54,$items[0]->getPrice());
		$this->assertEquals(1.86,$items[0]->getPriceInclVat());
		$this->assertEquals(0.77,$items[0]->getUnitPriceBeforeDiscount());
		$this->assertEquals(0.93,$items[0]->getUnitPriceBeforeDiscountInclVat());
		//
		$this->assertEquals("price_rounding",$items[1]->getProduct()->getCode());
		$this->assertEquals(21.0,$items[1]->getVatPercent());
		$this->assertEquals(-0.03,$items[1]->getPriceInclVat());
		$this->assertEquals(0.03,$items[1]->g("unit_price_incl_vat"));
		$this->assertEquals(0.02,$items[1]->getUnitPrice());
		$this->assertEquals(null,$items[1]->g("unit_price_before_discount_incl_vat"));

		$campaigns = $order->getCampaigns();
		$this->assertEquals(1,sizeof($campaigns));
		$this->assertEquals(0.19,$campaigns[0]->getDiscountAmount());
		$this->assertEquals(0.19,$campaigns[0]->g("discount_amount"));

		$vouchers = $order->getVouchers();
		$this->assertEquals(1,sizeof($vouchers));
		$this->assertEquals(0.77,$vouchers[0]->getDiscountAmount());
		$this->assertEquals(0.77,$vouchers[0]->g("discount_amount"));

		$this->assertEquals(round(7.69 + 3.04 + 1.86 - 0.19 - 0.77,1),$order->getPriceToPay());

		// objednavka bez DPH
		$basket = $this->_prepareBasketWithWoodenButton();

		$order = $basket->createOrder(["without_vat" => true]);

		$this->assertEquals(true,$order->g("without_vat"));

		$this->assertEquals(100,$order->getDeliveryFee());
		$this->assertEquals(100,$order->getDeliveryFeeInclVat());
		$this->assertEquals(75.24,$order->getPaymentFee());
		$this->assertEquals(75.24,$order->getPaymentFeeInclVat());

		$items = $order->getItems();
		$this->assertEquals(2,sizeof($items));
		//
		$this->assertEquals(0.0,$items[0]->getVatPercent());
		$this->assertEquals(20,$items[0]->getUnitPrice());
		$this->assertEquals(20,$items[0]->getUnitPriceInclVat());
		$this->assertEquals(40,$items[0]->getPrice());
		$this->assertEquals(40,$items[0]->getPriceInclVat());
		//
		$this->assertEquals("price_rounding",$items[1]->getProduct()->getCode());
		$this->assertEquals(0.0,$items[1]->getVatPercent());
		$this->assertEquals(0.24,$items[1]->g("unit_price_incl_vat"));
		$this->assertEquals(0.24,$items[1]->getUnitPrice());
		$this->assertEquals(-0.24,$items[1]->getPriceInclVat());

		// kampan se pouzije, protoze hypoteticka cena s DPH je vyssi stanoveny nez limit, ale sleva je aplikovana na cenu bez DPH
		$campaigns = $order->getCampaigns();
		$this->assertEquals(1,sizeof($campaigns));
		$this->assertEquals(4.0,$campaigns[0]->getDiscountAmount());

		$this->assertEquals(round(100 + 75.238 + 40 - 4.0),$order->getPriceToPay());

		// SK objednavka bez DPH v EUR
		$basket = $this->_prepareEmptyBasket([
			"region_id" => $SK,
		]);
		$basket = $this->_prepareBasketWithWoodenButton($basket);

		$order = $basket->createOrder(["without_vat" => true]);

		$this->assertEquals(true,$order->g("without_vat"));

		$this->assertEquals(7.33,$order->getDeliveryFee());
		$this->assertEquals(7.33,$order->getDeliveryFeeInclVat());
		$this->assertEquals(2.89,$order->getPaymentFee());
		$this->assertEquals(2.89,$order->getPaymentFeeInclVat());

		$items = $order->getItems();
		$this->assertEquals(2,sizeof($items));
		//
		$this->assertEquals(0.0,$items[0]->getVatPercent());
		$this->assertEquals(0.77,$items[0]->getUnitPrice());
		$this->assertEquals(0.77,$items[0]->getUnitPriceInclVat());
		$this->assertEquals(1.54,$items[0]->getPrice());
		$this->assertEquals(1.54,$items[0]->getPriceInclVat());
		//
		$this->assertEquals("price_rounding",$items[1]->getProduct()->getCode());
		$this->assertEquals(0.0,$items[1]->getVatPercent());
		$this->assertEquals(0.01,$items[1]->g("unit_price_incl_vat"));
		$this->assertEquals(0.01,$items[1]->getUnitPrice());
		$this->assertEquals(-0.01,$items[1]->getPriceInclVat());

		// kampan se pouzije, protoze hypoteticka cena s DPH je vyssi stanoveny nez limit, ale sleva je aplikovana na cenu bez DPH
		$campaigns = $order->getCampaigns();
		$this->assertEquals(1,sizeof($campaigns));
		$this->assertEquals(0.15,$campaigns[0]->getDiscountAmount());

		$this->assertEquals(round(7.33 + 2.89 + 1.54 - 0.15,1),$order->getPriceToPay());

		// otestovani vytvareni objednavky
		// bez DPH s voucherem
		$basket = $this->_prepareBasketWithWoodenButton();
		$basket->getVouchersLister()->add($voucher);

		$order = $basket->createOrder(["without_vat" => true]);
		$vouchers = $order->getVouchers();

		$this->assertEquals(1,sizeof($vouchers));
		$this->assertEquals(16.53,$vouchers[0]->getDiscountAmount()); // 16.53 * 1.21 == 20.0
		$this->assertTrue(null === $vouchers[0]->getVatPercent()); // vat_percent is not stored

		// testovani prenosu cen pred slevou;
		// na kostkovana_latka je sleva 50%
		$basket = $this->_prepareEmptyBasket();
		$basket->addProduct($this->products["kostkovana_latka"],160); // 1.6m
		$order = $basket->createOrder();

		$items = $order->getItems();
		$this->assertEquals(2,sizeof($items));

		$this->assertEquals(1.0,$items[0]->getUnitPrice());
		$this->assertEquals(1.21,$items[0]->getUnitPriceInclVat());
		$this->assertEquals(160.0,$items[0]->getPrice());
		$this->assertEquals(193.6,$items[0]->getPriceInclVat());
		$this->assertEquals(2.0,$items[0]->getUnitPriceBeforeDiscount());
		$this->assertEquals(2.42,$items[0]->getUnitPriceBeforeDiscountInclVat());
		$this->assertEquals(320.0,$items[0]->getPriceBeforeDiscount());
		$this->assertEquals(387.2,$items[0]->getPriceBeforeDiscountInclVat());
	}

	/**
	 * Hodnoty obsahuji:
	 * - pocet kusu produktu 'wooden_button' v kosiku
	 * - pouziti slevoveho poukazu.
	 *
	 * - ocekavany vysledek v campaign_discount_applied u polozky 'wooden_button'
	 */
	function provideItemValues() {
		return [
			# bez slevy za pokukaz
			[ 1000, false, true],
			[ 100, false, false],
			# s procentni slevou za poukaz
			[ 1000, "discount_10", true],
			[ 100, "discount_10", true],
			# s dopravou zdarma za poukaz
			[ 1000, "free_shipping", true],
			[ 100, "free_shipping", false],
			# s celkovou slevou za poukaz
			[ 1000, "supervelikonoce", true],
			[ 100, "supervelikonoce", false],
		];
	}

	/**
	 * Chceme otestovat priznak campaign_discount_applied
	 *
	 * - na polozky ve sleve se neaplikuje sleva z kampane. Toto chceme videt v polozce objednavky.
	 * - u mensich objednavek do 1000 kc se sleva v kampani neaplikuje
	 * -- kostkovana_latka je ve sleve, takze se na ni sleva v kampani neaplikuje nikdy
	 * -- na wooden_button se aplikuje sleva v kampani podle vyse objednavky
	 * @dataProvider provideItemValues
	 */
	function test_create_order_with_discounted_product($amount, $apply_voucher, $expected_campaign_applied) {
		$basket = $this->_prepareBasketWithWoodenButton();
		$basket->s("user_id", $this->users["rambo"]);
		$items = $basket->getItems();
		$this->assertFalse($items[0]->discounted());

		# sleva v kampani se aplikuje pouze na objednavky za minimalni cenu,
		# proto  pridame vic metru do kosiku
		$item2 = $basket->addProduct($this->products["kostkovana_latka"], $amount);
		$this->assertTrue($item2->discounted());

		if ($apply_voucher) {
			$voucher = $this->vouchers[$apply_voucher];
			$lister = $basket->getVouchersLister();
			if(!$lister->contains($voucher)){
				$lister->add($voucher);
			}
		}
		$order = $basket->createOrder();
		$campaigns_applied = [];
		foreach($order->getItems() as $i) {
			$campaigns_applied[$i->getProductId()] = $i->getCampaignDiscountApplied();
		}
		$this->assertEquals($expected_campaign_applied, $campaigns_applied[$this->products["wooden_button"]->getId()]);
		$this->assertFalse($campaigns_applied[$this->products["kostkovana_latka"]->getId()]);
	}

	function test_proper_price_rounding(){
		$basket = $this->_prepareEmptyBasket();
	
		// Pozn. posledni prihozene zbozi je v kosiku na prvnim miste
		$basket->addProduct($this->products["spendlik"],2);
		$basket->addProduct($this->products["zaviraci_spendlik"],3);
		$basket->addProduct($this->products["bavlna_zelena"],52);

		$items = $basket->getItems();

		$this->assertEquals(3,sizeof($items));

		$this->_check_proper_price_rounding_on_items($items);

		$order = $basket->createOrder();

		$order_items = $order->getItems();

		// posledni polozka je zaokrouhleni
		$this->assertEquals(4,sizeof($order_items));

		// pro kontrolu proverime i vysi zaokrouhlovaciho produktu
		// cena polozek je 77.66 + 37.95 + 2.60 = 118.21
		$this->assertEquals(-0.21,$order_items[3]->getPriceInclVat());

		$this->_check_proper_price_rounding_on_items($order_items);
	}

	function test_percentage_discounts_should_not_accumulate(){
		$basket = $this->_prepareEmptyBasket();

		$basket->addProduct($this->products["spendlik"],100);
		$this->assertEquals(130.0,$basket->getItemsPriceInclVat());

		$lister = $basket->getVouchersLister();

		$lister->add($this->vouchers["discount_10"]);
		$lister->add($this->vouchers["discount_15"]);

		$basket_vouchers = $basket->getBasketVouchers();

		$this->assertEquals(2,sizeof($basket_vouchers));
		$this->assertEquals($this->vouchers["discount_10"]->getId(),$basket_vouchers[0]->getVoucherId());
		$this->assertEquals($this->vouchers["discount_15"]->getId(),$basket_vouchers[1]->getVoucherId());

		// nesmi se pocitat sleva z kuponu s mensi slevou
		$this->assertEquals(0.0,$basket_vouchers[0]->getDiscountAmount());
		$this->assertEquals(19.5,$basket_vouchers[1]->getDiscountAmount()); // 15% ze 130

		$lister->remove($this->vouchers["discount_15"]);

		$basket_vouchers = $basket->getBasketVouchers();

		$this->assertEquals(1,sizeof($basket_vouchers));
		$this->assertEquals($this->vouchers["discount_10"]->getId(),$basket_vouchers[0]->getVoucherId());

		$this->assertEquals(13.0,$basket_vouchers[0]->getDiscountAmount()); // 10% ze 130

		// ### Dalsi test s kampani 7% za registraci nad 1000 Kc

		$basket = $this->_prepareEmptyBasket(["user_id" => $this->users["kveta"]]);

		$basket->addProduct($this->products["spendlik"],1000);
		$this->assertEquals(1300.00,$basket->getItemsPriceInclVat());

		$basket_campaigns = $basket->getBasketCampaigns();

		$this->assertEquals(1,sizeof($basket_campaigns));

		$this->assertEquals(91,$basket_campaigns[0]->getDiscountAmount()); // 7% ze 1300

		$lister = $basket->getVouchersLister();
		$lister->add($this->vouchers["discount_10"]);

		$basket_vouchers = $basket->getBasketVouchers();

		$this->assertEquals(1,sizeof($basket_vouchers));

		$this->assertEquals(130.0,$basket_vouchers[0]->getDiscountAmount()); // 10% ze 1300

		$this->assertEquals(0.0,$basket_campaigns[0]->getDiscountAmount()); // 7% je mene nez 10%; sleva se nesmi uplatnit
	}

	function test_cashOnDeliveryEnabled(){
		$basket = $this->_prepareEmptyBasket(["user_id" => $this->users["kveta"]]);

		$this->assertEquals(true,$basket->cashOnDeliveryEnabled());

		$basket->addProduct($this->products["wooden_button"],2);
		$this->assertEquals(true,$basket->cashOnDeliveryEnabled());

		$basket->addProduct($this->products["strih_v_pdf_formatu"],1);
		$this->assertEquals(false,$basket->cashOnDeliveryEnabled());
	}

	function test_canOrderBeCreated(){
		Atk14Locale::Initialize(); // TODO: it is not clear why this is needed, certainly it should be handled on another place

		// empty basket

		$basket = $this->_prepareEmptyBasket();

		$this->assertEquals(false,$basket->canOrderBeCreated($messages));
		$this->assertContains("Shopping basket is empty","$messages[0]");

		// deleted product

		$basket = $this->_prepareEmptyBasket();

		$basket->addProduct($this->products["deleted_product"],1);

		$this->assertEquals(false,$basket->canOrderBeCreated($messages));
		$this->assertContains("has been removed from our offer","$messages[0]");

		 // deleted card

		$basket = $this->_prepareEmptyBasket();

		$basket->addProduct($this->products["product_in_deleted_card"],1);

		$this->assertEquals(false,$basket->canOrderBeCreated($messages));
		$this->assertContains("has been removed from our offer","$messages[0]");

		//

		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$basket->s([
			"delivery_method_id" => $this->delivery_methods["post"],
			"payment_method_id" => $this->payment_methods["bank_transfer"],
		]);

		$this->assertFalse($basket->canOrderBeCreated($messages));
		$this->assertEquals(1,sizeof($messages));
		$this->assertEquals("Shopping basket is empty",(string)$messages[0]);

		$basket->addProduct($this->products["black_tea"]);
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertTrue($stat);
		$this->assertEquals(0,sizeof($messages));

		$basket->addProduct($this->products["herbal_tea"]);
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertFalse($stat);
		$this->assertEquals(1,sizeof($messages));
		$this->assertEquals("Product <em>Herbal tea (HERBAL_TEA)</em> is out of stock",(string)$messages[0]);

		$basket->setProductAmount($this->products["herbal_tea"],0);
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertTrue($stat);

		$basket->addProduct($this->products["green_tea"]);
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertFalse($stat);
		$this->assertEquals(1,sizeof($messages));
		$this->assertEquals("Product <em>Green tea (TEA_GREEN)</em> has been removed from our price list",(string)$messages[0]);

		$basket->setProductAmount($this->products["green_tea"],0);
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertTrue($stat);

		// choosing unsupported shipping combination
		$basket->s([
			"delivery_method_id" => $this->delivery_methods["post_cod"],
			"payment_method_id" => $this->payment_methods["bank_transfer"],
		]);
		$this->assertNotNull($basket->getDeliveryMethod());
		$this->assertNotNull($basket->getPaymentMethod());
		$stat = $basket->canOrderBeCreated($messages);
		$this->assertFalse($stat);
		$this->assertEquals(0,sizeof($messages)); // there is no message
		$this->assertNull($basket->getDeliveryMethod());
		$this->assertNull($basket->getPaymentMethod());
	}

	function test_getDeliveryMethodData(){
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);

		$this->assertNull($basket->getDeliveryMethodData());
		$this->assertNull($basket->getDeliveryMethodData(["as_json" => false]));
		$this->assertNull($basket->getDeliveryMethodData(["as_json" => true]));
		$this->assertNull($basket->getDeliveryMethodData(false));
		$this->assertNull($basket->getDeliveryMethodData(true));

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["zasilkovna"],
			"delivery_method_data" => $this->delivery_service_branches["zasilkovna_1"]->getDeliveryMethodData(["as_json" => true]),
		]);
		//
		$dm_data = $basket->getDeliveryMethodData();
		$this->assertNotNull($dm_data);
		$this->assertEquals("První pražská Zásilkovna",$dm_data["delivery_address"]["place"]);
		//
		$dm_data = $basket->getDeliveryMethodData(["as_json" => false]);
		$this->assertNotNull($dm_data);
		$this->assertEquals("První pražská Zásilkovna",$dm_data["delivery_address"]["place"]);
		//
		$dm_data = $basket->getDeliveryMethodData(false);
		$this->assertNotNull($dm_data);
		$this->assertEquals("První pražská Zásilkovna",$dm_data["delivery_address"]["place"]);
		//
		$dm_data_json = $basket->getDeliveryMethodData(["as_json" => true]);
		$this->assertTrue(is_string($dm_data_json));
		$this->assertTrue(!!preg_match('/{/',$dm_data_json));
		//
		$dm_data_json = $basket->getDeliveryMethodData(true);
		$this->assertTrue(is_string($dm_data_json));
		$this->assertTrue(!!preg_match('/{/',$dm_data_json));

		// setting data for another method
		$basket->s([
			"delivery_method_data" => $this->delivery_service_branches["posta_12000"]->getDeliveryMethodData(["as_json" => true]),
		]);
		//
		$this->assertNull($basket->getDeliveryMethodData());
		$this->assertNull($basket->getDeliveryMethodData(["as_json" => false]));
		$this->assertNull($basket->getDeliveryMethodData(["as_json" => true]));
		$this->assertNotNull($basket->g("delivery_method_data"));
	}

	function test_getDeliveryFee(){
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);

		$this->assertTrue(is_null($basket->getDeliveryFee()));
		$this->assertTrue(is_null($basket->getDeliveryFee(true)));
		$this->assertTrue(is_null($basket->getDeliveryFeeInclVat()));

		// Delivery with Zasilkovna has the same price in different countries
		$basket->s([
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"delivery_address_country" => null,
		]);
		//
		$this->assertEquals(57.85,$basket->getDeliveryFee());
		$this->assertEquals(70.0,$basket->getDeliveryFee(true));
		$this->assertEquals(70.0,$basket->getDeliveryFeeInclVat());

		// Delivery with DPD has different prices in different countries
		$basket->s([
			"delivery_method_id" => $this->delivery_methods["dpd"]->getId(),
			"delivery_address_country" => null,
		]);
		//
		$this->assertEquals(null,$basket->getDeliveryFee());
		$this->assertEquals(null,$basket->getDeliveryFee(true));
		$this->assertEquals(null,$basket->getDeliveryFeeInclVat());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["dpd"]->getId(),
			"delivery_address_country" => "CZ",
		]);
		//
		$this->assertEquals(100.0,$basket->getDeliveryFee());
		$this->assertEquals(121.0,$basket->getDeliveryFee(true));
		$this->assertEquals(121.0,$basket->getDeliveryFeeInclVat());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["dpd"]->getId(),
			"delivery_address_country" => "SK",
		]);
		//
		$this->assertEquals(190.48,$basket->getDeliveryFee());
		$this->assertEquals(200.0,$basket->getDeliveryFee(true));
		$this->assertEquals(200.0,$basket->getDeliveryFeeInclVat());

		// Delivery by agreement has no price set
		$basket->s("delivery_method_id",$this->delivery_methods["by_agreement"]->getId());
		//
		$this->assertTrue(is_null($basket->getDeliveryFee()));
		$this->assertTrue(is_null($basket->getDeliveryFee(true)));
		$this->assertTrue(is_null($basket->getDeliveryFeeInclVat()));
	}

	function test_getAddMoreToGetFreeDelivery(){
		// CZK
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);
		$this->assertEquals("CZK",$basket->getCurrency()->getCode());
		$this->assertEquals(2000.0,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"]);
		$this->assertEquals(1975.8,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"],10);
		$this->assertEquals(1758.0,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"],1000);
		$this->assertEquals(0.0,$basket->getAddMoreToGetFreeDelivery());
		// oversized_product
		$basket->setProductAmount($this->products["mint_tea"],0);
		$basket->addProduct($this->products["fridge"]);
		$this->assertEquals(8001.0,$basket->getAddMoreToGetFreeDelivery());

		// EUR
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["SK"]);
		$this->assertEquals("EUR",$basket->getCurrency()->getCode());
		$this->assertEquals(76.92,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"]);
		$this->assertEquals(75.99,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"],10);
		$this->assertEquals(67.62,$basket->getAddMoreToGetFreeDelivery());
		$basket->addProduct($this->products["mint_tea"],1000);
		$this->assertEquals(0.0,$basket->getAddMoreToGetFreeDelivery());
		// oversized_product
		$basket->setProductAmount($this->products["mint_tea"],0);
		$basket->addProduct($this->products["fridge"]);
		$this->assertEquals(307.73,$basket->getAddMoreToGetFreeDelivery());
	}

	function test_getShippingFee(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$this->assertEquals(null,$basket->getShippingFee());
		$this->assertEquals(null,$basket->getShippingFee(true));
		$this->assertEquals(null,$basket->getShippingFeeInclVat(true));

		$basket->s(array(
			"delivery_method_id" => $this->delivery_methods["personal"],
			"payment_method_id" => $this->payment_methods["cash_on_delivery"]
		));

		$this->assertEquals(75.24,$basket->getShippingFee());
		$this->assertEquals(79.0,$basket->getShippingFee(true));
		$this->assertEquals(79.0,$basket->getShippingFeeInclVat());
	}

	function test_getAveragedItemsVatPercent(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$this->assertTrue(null == $basket->getAveragedItemsVatPercent());

		$basket->setProductAmount($this->products["mint_tea"],1);
		$this->assertEquals(21.0,$basket->getAveragedItemsVatPercent());

		$basket->setProductAmount($this->products["black_tea"],2);
		$this->assertEquals(21.0,$basket->getAveragedItemsVatPercent());

		$basket->setProductAmount($this->products["book"],1);
		$this->assertEquals(12.05,$basket->getAveragedItemsVatPercent());

		$basket->setProductAmount($this->products["book"],2);
		$this->assertEquals(11.13,$basket->getAveragedItemsVatPercent());

		$basket->setProductAmount($this->products["mint_tea"],0);
		$basket->setProductAmount($this->products["black_tea"],0);

		$this->assertEquals(10.0,$basket->getAveragedItemsVatPercent());
	}

	function test_GetAddressFields(){
		$this->assertEquals([
			"firstname" => true,
			"lastname" => true,
			"company" => false,
			"address_street" => true,
			"address_street2" => false,
			"address_city" => true,
			"address_state" => false,
			"address_zip" => true,
			"address_country" => true,
		],Basket::GetAddressFields());

		$this->assertEquals([
			"company" => false,
			"address_street" => true,
			"address_street2" => false,
			"address_city" => true,
			"address_state" => false,
			"address_zip" => true,
			"address_country" => true,
			"company_number" => false,
			"vat_id" => false,
			"address_note" => false,
		],Basket::GetAddressFields(["company_data" => true, "note" => true, "name" => false, "street2" => false, "state" => false]));

		$this->assertEquals([
			"delivery_company" => false,
			"delivery_address_street" => true,
			"delivery_address_street2" => false,
			"delivery_address_city" => true,
			"delivery_address_state" => false,
			"delivery_address_zip" => true,
			"delivery_address_country" => true,
			"delivery_address_note" => false,
		],Basket::GetAddressFields(["name" => false, "note" => true, "prefix" => "delivery_"]));
	}

	function test_hasDeliveryAddressSet(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);
		$this->assertEquals(true,$basket->hasDeliveryAddressSet());

		$basket = Basket::CreateNewRecord4UserAndRegion(User::GetAnonymousUser(),$this->regions["czechoslovakia"]);
		$this->assertEquals(false,$basket->hasDeliveryAddressSet());

		$basket->s([
			"delivery_firstname" => "Bobina",
			"delivery_lastname" => "Drozdová",
			"delivery_method_id" => $this->delivery_methods["zasilkovna"],
			"delivery_method_data" => $this->delivery_service_branches["zasilkovna_1"]->getDeliveryMethodData(),
		]);
		$this->assertEquals(true,$basket->hasDeliveryAddressSet());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["dpd_test"],
		]);
		$this->assertEquals(false,$basket->hasDeliveryAddressSet());

		$basket->s([
			"delivery_address_street" => "Ambryho 9",
			"delivery_address_city" => "Praha 3",
			//"delivery_address_zip" => "130 00",
			"delivery_address_country" => "CZ",
		]);
		$this->assertEquals(false,$basket->hasDeliveryAddressSet()); // zip is missing

		$basket->s([
			"delivery_address_zip" => "130 00",
		]);
		$this->assertEquals(true,$basket->hasDeliveryAddressSet());
	}

	function test_setBasketItemsVirtually(){
		$basket = Basket::GetDummyBasket();
		$basket_item = new BasketItem();
		$basket_item->setValuesVirtually([
			"product_id" => $this->products["black_tea"]->getId(),
			"amount" => 2,
		]);
		$basket->setBasketItemsVirtually([$basket_item]);
		$basket_items = $basket->getBasketItems();
		$this->assertEquals(1,sizeof($basket_items));
		$this->assertEquals($this->products["black_tea"]->getId(),$basket_items[0]->getProduct()->getId());

		// Basket::setBasketItemsVirtually() can't be called on a regular (not dummy) basket
		$kveta = $this->users["kveta"];
		$czechoslovakia = $this->regions["czechoslovakia"];
	
		$basket = Basket::CreateNewRecord4UserAndRegion($kveta,$czechoslovakia);
		$expcetion_thrown = false;
		try {
			$basket->setBasketItemsVirtually([$basket_item]);
		}catch(Exception $e){
			$expcetion_thrown = true;
		}
		$this->assertEquals(true,$expcetion_thrown);
	}

	function test__getDeliveryCountry(){
		$basket = Basket::CreateNewRecord4UserAndRegion($this->users["kveta"],$this->regions["czechoslovakia"]);

		$basket->s("address_country",null);
		$basket->s("delivery_address_country",null);
		$this->assertEquals(null,$basket->_getDeliveryCountry());

		$basket->s("address_country","CZ");
		$this->assertEquals("CZ",$basket->_getDeliveryCountry());

		$basket->s("delivery_address_country","SK");
		$this->assertEquals("SK",$basket->_getDeliveryCountry());

		$basket->s([
			"delivery_firstname" => "Bobina",
			"delivery_lastname" => "Drozdová",
			"delivery_method_id" => $this->delivery_methods["zasilkovna"],
			"delivery_method_data" => $this->delivery_service_branches["zasilkovna_1"]->getDeliveryMethodData(),
		]);
		$this->assertEquals("CZ",$basket->_getDeliveryCountry());

		$basket->s([
			"delivery_method_id" => $this->delivery_methods["personal_sk"],
		]);
		$this->assertEquals("SK",$basket->_getDeliveryCountry());
	}

	function _check_proper_price_rounding_on_items($items){
		// bavlna_zelena: latky v cm se zaokrouhluji na 4 desetiny - v ceniku je 1.2342
		$this->assertEquals(1.2342,$items[0]->getUnitPrice());
		$this->assertEquals(1.4934,$items[0]->getUnitPriceInclVat());
		// celk. cena pak na 2
		$this->assertEquals(64.18,$items[0]->getPrice()); // round(1.2342 * 52, 2)
		$this->assertEquals(77.66,$items[0]->getPriceInclVat()); // round(1.4934 * 52, 2)

		// zaviraci_spendlik: kusove zbozi se zaokrouhluje na 2 desetiny - v ceniku je cena 10.4567
		$this->assertEquals(10.45,$items[1]->getUnitPrice());
		$this->assertEquals(12.65,$items[1]->getUnitPriceInclVat()); // round(10.4567 * 1.21, 2)
		// celk. cena je taky na 2
		$this->assertEquals(31.36,$items[1]->getPrice()); // (12.65 * 3) / 1.21
		$this->assertEquals(37.95,$items[1]->getPriceInclVat()); // 12.65 * 3

		// spendlik: kusove zbozi se zaokrouhluje na 2 desetiny - v ceniku je cena 1.0744
		$this->assertEquals(1.07,$items[2]->getUnitPrice());
		$this->assertEquals(1.30,$items[2]->getUnitPriceInclVat());
		// celk. celk je taky na 2
		$this->assertEquals(2.15,$items[2]->getPrice());
		$this->assertEquals(2.60,$items[2]->getPriceInclVat());
	}
}
