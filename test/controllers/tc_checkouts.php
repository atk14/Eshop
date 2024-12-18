<?php
/**
 *
 * @fixture products
 * @fixture pricelist_items
 * @fixture warehouse_items
 * @fixture baskets
 * @fixture basket_items
 * @fixture delivery_methods
 * @fixture delivery_services
 * @fixture delivery_service_branches
 * @fixture payment_methods
 * @fixture shipping_combinations
 * @fixture users
 */
class TcCheckouts extends TcBase {

	function _tearDown() {
		# we need to logout user after each test otherwise fixtures setup fails as created_by_user_id is filled with user id of newly created user in one of tests performed in this class;
		$this->client->post("logins/destroy");
		parent::_tearDown();
	}

	/**
	 * Test that user is redirected to the right place when branch is selected.
	 */
	function test_post_set_payment_and_delivery_method_with_correct_branch() {
		global $ATK14_GLOBAL;

		$client = $this->client;
		$lang = $ATK14_GLOBAL->getDefaultLang();

		$this->_setup_basket();

		$controller = $client->post("delivery_service_branches/set_branch", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"delivery_service_branch_id" => $this->delivery_service_branches["zasilkovna_2"]->getExternalBranchId(),
			"_return_uri_" => Atk14Url::BuildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
		]);

		$this->assertEquals(303, $client->getStatusCode());
		$this->assertEmpty($controller->form->get_errors());
		$this->assertEmpty($controller->flash->error());
		$this->assertEquals("/{$lang}/checkouts/set_payment_and_delivery_method/", $client->getLocation());

		$this->_check_delivery_method_data($controller->basket->getDeliveryMethodData(), [
			"delivery_method" => $this->delivery_methods["zasilkovna"],
			"delivery_service_branch" => $this->delivery_service_branches["zasilkovna_2"],
		]);
	}

	function test_post_set_payment_and_delivery_method_with_incorrect_branch() {
		global $ATK14_GLOBAL;

		$client = $this->client;
		$lang = $ATK14_GLOBAL->getDefaultLang();

		$this->_setup_basket();

		DeliveryMethod::_PrereadCache(["force_read" => true]);
		$controller = $client->post("delivery_service_branches/set_branch", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"delivery_service_branch_id" => $this->delivery_service_branches["posta_12000"]->getExternalBranchId(),
			"_return_uri_" => Atk14Url::BuildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
		]);

		$this->assertEquals(303, $client->getStatusCode()); // i pri chybe dochazi k presmerovani zpet
		$this->assertNotEmpty($controller->form->get_errors());
		$this->assertNotEmpty($controller->flash->error());
		$this->assertArrayHasKey("delivery_service_branch_id", $controller->form->errors);
		$this->assertEquals("Dispensing point not found", array_shift($controller->form->errors["delivery_service_branch_id"]));
	}

	/**
	 * Test that a pickup point is required when a delivery method with delivery service is selected
	 */
	function test_post_set_payment_and_delivery_method_without_branch() {
		$client = $this->client;

		$this->_setup_basket();

		# without this DeliveryMethod::GetInstanceById() used in controller would use instance cached by TraitCodebook::GetInstanceById
		# so we need to refresh the cache
		DeliveryMethod::_PrereadCache(["force_read" => true]);
		$controller = $client->post("checkouts/set_payment_and_delivery_method", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"payment_method_id" => $this->payment_methods["bank_transfer"]->getId(),
		]);

		$this->assertEquals(200, $client->getStatusCode());

		$this->assertNotEmpty($errors = $controller->form->errors);
		$this->assertArrayHasKey("delivery_method_id", $errors);
		$this->assertEquals(_("Choose pickup place for selected delivery method"), $errors["delivery_method_id"][0]);
	}

	/**
	 * Test that delivery service branch selected in anonymous basket is remembered when new user is registered.
	 */
	function test_remember_pickup_point_on_create_new_user() {
		global $ATK14_GLOBAL;

		$lang = $ATK14_GLOBAL->getDefaultLang();
		$client = $this->client;
		$this->_setup_basket();

		DeliveryMethod::_PrereadCache(["force_read" => true]);

		$controller = $client->post("delivery_service_branches/set_branch", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"delivery_service_branch_id" => $this->delivery_service_branches["zasilkovna_1"]->getExternalBranchId(),
			"_return_uri_" => Atk14Url::BuildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
		]);

		$controller = $this->client->post("checkouts/set_payment_and_delivery_method", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"payment_method_id" => $this->payment_methods["bank_transfer"]->getId(),
		]);

		$this->assertEquals(303, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/user_identification/", $client->getLocation());

		$controller = $client->post("users/create_new", [
			"login" => "bread.pit",
			"gender_id" => Gender::FindFirst(),
			"firstname" => "Bread",
			"lastname" => "Pit",
			"email" => "bread.pit@pitmail.com",
			"address_street" => "21, Jump Street",
			"address_city" => "Florida",
			"address_zip" => "13000",
			"address_country" => "CZ", // "US" is not allowed invoice country in the region
			"phone" => "+420555000555",
			"password" => "simple.password",
			"password_repeat" => "simple.password",
			"return_uri" => Atk14Url::BuildLink(["action" => "checkouts/user_identification"]),
		]);
		$this->assertEquals([],array_flatten($controller->form->get_errors()));
		$this->assertEquals(303, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/user_identification/", $client->getLocation());

		$controller = $client->get("checkouts/user_identification");
		$this->assertEquals(302, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/set_billing_and_delivery_data/", $client->getLocation());

		$this->_check_delivery_method_data($controller->basket->getDeliveryMethodData(),[
			"delivery_method" => $this->delivery_methods["zasilkovna"],
			"delivery_service_branch" => $this->delivery_service_branches["zasilkovna_1"],
		]);
	}

	/**
	 * Test that delivery service branch selected in anonymous basket is remembered upon user login.
	 */
	function test_remember_pickup_point_on_login_user() {
		global $ATK14_GLOBAL;

		$lang = $ATK14_GLOBAL->getDefaultLang();
		$client = $this->client;
		$this->_setup_basket();

		DeliveryMethod::_PrereadCache(["force_read" => true]);

		$controller = $client->post("delivery_service_branches/set_branch", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"delivery_service_branch_id" => $this->delivery_service_branches["zasilkovna_1"]->getExternalBranchId(),
			"_return_uri_" => Atk14Url::BuildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
		]);

		$controller = $this->client->post("checkouts/set_payment_and_delivery_method", [
			"delivery_method_id" => $this->delivery_methods["zasilkovna"]->getId(),
			"payment_method_id" => $this->payment_methods["bank_transfer"]->getId(),
		]);

		$this->assertEquals(303, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/user_identification/", $client->getLocation());

		$controller = $client->post("logins/create_new", [
			"login" => "rambo",
			"password" => "secret",
			"return_uri" => Atk14Url::BuildLink(["action" => "checkouts/user_identification"]),
		]);
		$this->assertEquals(303, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/user_identification/", $client->getLocation());

		$controller = $client->get("checkouts/user_identification");
		$this->assertEquals(302, $client->getStatusCode());
		$this->assertEquals("/{$lang}/checkouts/set_billing_and_delivery_data/", $client->getLocation());

		$this->_check_delivery_method_data($controller->basket->getDeliveryMethodData(),[
			"delivery_method" => $this->delivery_methods["zasilkovna"],
			"delivery_service_branch" => $this->delivery_service_branches["zasilkovna_1"],
		]);
	}

	protected function _check_delivery_method_data($current_delivery_method_data, $expected=[]) {
		$expected += [
			"delivery_method" => null,
			"delivery_service_branch" => null,
		];

		$delivery_method = $expected["delivery_method"];
		$branch = $expected["delivery_service_branch"];

		$this->assertNotNull($current_delivery_method_data);
		$this->assertEquals($branch->getExternalBranchId(), $current_delivery_method_data["external_branch_id"]);
		$this->assertEquals($delivery_method->getDeliveryServiceId(), $current_delivery_method_data["delivery_service_id"]);
		$this->assertEquals($delivery_method->getCode(), $current_delivery_method_data["delivery_service_code"]);
		$this->assertEquals($branch->getDeliveryService()->getName(), $current_delivery_method_data["delivery_address"]["company"]);
		$this->assertEquals($branch->getStreet(), $current_delivery_method_data["delivery_address"]["street"]);
		$this->assertEquals($branch->getCity(), $current_delivery_method_data["delivery_address"]["city"]);
		$this->assertEquals($branch->getZip(), $current_delivery_method_data["delivery_address"]["zip"]);
		$this->assertEquals($branch->getCountry(), $current_delivery_method_data["delivery_address"]["country"]);
	}

	/**
	 * Instantiates basket for the current user and adds a product into it.
	 */
	protected function _setup_basket() {
		$client = $this->client;
		$controller = $client->get("main/index");
		$basket = $controller->_get_basket(true);

		$basket->addProduct($this->products["mint_tea"],1);

		$controller->permanentSession->s("basket_id", $basket->getId());
		$client->addCookie(new HttpCookie("permanent", $controller->permanentSession->getSecretToken()));
		$controller = $client->get("main/index");
		$this->assertFalse($controller->basket->isEmpty());
	}
}
