<?php
class Basket extends BasketOrOrder {

	const ID_DUMMY = 1;

	protected $basket_items;

	static function CreateNewRecord($values,$options = []){
		$values += [
			"user_id" => null,
			"region_id" => Region::GetDefaultRegion(),
			"currency_id" => null,
		];

		$region = is_object($values["region_id"]) ? $values["region_id"] : Cache::Get("Region",$values["region_id"]);

		if(is_null($values["currency_id"])){
			$values["currency_id"] = $region->getDefaultCurrency();
		}

		return parent::CreateNewRecord($values,$options);
	}

	/**
	 *
	 *	$basket = Basket::CreateNewRecord4UserAndRegion();
	 */
	static function CreateNewRecord4UserAndRegion($user,$region,$options = array()){
		$basket = self::CreateNewRecord([
			"user_id" => $user,
			"region_id" => $region
		],$options);

		$update_ar = [];
		$delivery_countries_allowed = $basket->getDeliveryCountriesAllowed();
		$user = $basket->getUser();

		if($user){
			$update_ar["email"] = $user->g("email");

			// Dorucovaci adresa
			if($da = DeliveryAddress::GetMostRecentRecord($user,$delivery_countries_allowed)){
				// pouzita jest posledni dorucovaci adresa
				foreach(self::GetAddressFields(["phone" => true, "note" => true]) as $k => $req){
					$update_ar["delivery_$k"] = $da->g("$k");
				}
			}elseif(in_array($user->getAddressCountry(),$delivery_countries_allowed)){
				// pouzita jest fakturacni adresa
				foreach(self::GetAddressFields(["phone" => true, "note" => false]) as $k => $req){
					$update_ar["delivery_$k"] = $user->g("$k");
				}
			}

			// !! Fakturacni adresu nastavime, kdyz ma uzivatel vyplnene IC nebo DIC nebo je v dorucovaci adrese neco jineho nez ve fakturacni adrese
			$addresses_differ = false;
			foreach(self::GetAddressFields(["company_data" => false, "phone" => false, "note" => false]) as $k => $req){
				if(!array_key_exists("delivery_$k",$update_ar) || (string)$update_ar["delivery_$k"]!==(string)$user->g("$k")){
					$addresses_differ = true;
					break;
				}
			}
			if($user->g("vat_id") || $user->g("company_number") || $addresses_differ){
				foreach(self::GetAddressFields(["company_data" => true, "phone" => false]) as $k => $req){
					$update_ar["$k"] = $user->g("$k");
				}
			}
		}

		if($update_ar){
			$basket->s($update_ar);
		}

		return $basket;
	}

	static function GetDummyBasket($region = null,$user = null, $currency = null){
		if(!$region){ $region = Region::GetDefaultRegion(); }
		$basket = Cache::Get("Basket",self::ID_DUMMY);

		$out = clone($basket);
		$out->setValuesVirtually([
			"region_id" => $region->getId(),
			"currency_id" => isset($currency) ? TableRecord::ObjToId($currency) : $region->getDefaultCurrency()->getId(),
			"user_id" => $user ? $user->getId() : null,
		]);

		return $out;
	}

	function isDummy(){
		return $this->getId()==self::ID_DUMMY;
	}

	function getUser(){
		return Cache::Get("User",$this->getUserId());
	}

	function getRegion(){
		return Cache::Get("Region",$this->getRegionId());
	}

	/**
	 * Nastavi do kosiku dany region
	 *
	 * Neudela nic, pokud ma kosik stejny region.
	 *
	 *	$something_updated = $basket->setRegion($region);
	 */
	function setRegion($region){
		$allowed_currencies = $region->getCurrencies();
		$allowed_currencies = array_map(function($c){ return $c->getCode(); },$allowed_currencies); // ["CZK","EUR"]
		$allowed_countries = $region->getDeliveryCountries(); // ["CZ","SK"]

		$update_ar = [];
		if($this->getRegionId()!=$region->getId()){
			$update_ar["region_id"] = $region->getId();
			// meni se region - v tichosti zmenime i delivery_method_id a payment_method_id - nema asi cenu slozite overovat, zda tam pripadne mohou zustat
			$update_ar["delivery_method_id"] = null;
			$update_ar["delivery_method_data"] = null;
			$update_ar["payment_method_id"] = null;
		}
		if($this->g("delivery_address_country") && !in_array($this->g("delivery_address_country"),$allowed_countries)){
			$update_ar["delivery_address_country"] = null;
		}
		if(!in_array($this->getCurrency()->getCode(),$allowed_currencies)){
			$update_ar["currency_id"] = $region->getDefaultCurrency()->getId();
		}

		if($update_ar){
			if($this->isDummy()){
				$this->setValuesVirtually($update_ar);
			}else{
				$this->s($update_ar);
			}
			return true;
		}

		return false;
	}

	function getBasketItems(){
		if(is_null($this->basket_items)){
			$this->basket_items = new BasketItems($this);
		}
		return $this->basket_items;
	}

	function setBasketItemsVirtually($basket_items){
		myAssert($this->isDummy(),"Basket::setBasketItemsVirtually() can only be called on a dummy basket");
		$this->basket_items = $basket_items;
	}

	function getItems(){
		return $this->getBasketItems();
	}

	function isEmpty(){
		return sizeof($this->getBasketItems())==0;
	}

	function getPriceFinder(){
		return PriceFinder::GetInstance($this->getUser(),$this->getCurrency());
	}

	function setProductAmount($product,$amount = 1,$options = array()){
		$options += [ 'update_rank' => false ];
		return $this->addProduct($product, $amount, $options);
	}

	/**
	 * Add the given amount of the product to the basket
	 *
	 * If the product already exists in the basket, it's amount will be rewritten.
	 * This default behaviour can be changed by the option "rewrite_amount".
	 *
	 * By default, the newly created or updated basket item will be the first in line.
	 *
	 * $basket->addProduct($product); // 1ks
	 * $basket->addProduct($product,10);
	 * $basket->addProduct($product,0); // smazani produktu z kosiku
	 */
	function addProduct($product,$amount = 1,$options = array()){
		if($this->isDummy()){ return; }

		$bi = $this->basket_items;
		$this->basket_items = null; // vynulovani cache

		$options += array(
			"update_rank" => true, // private stuff
			"rewrite_amount" => true,
		);

		$rewrite_amount = $options["rewrite_amount"];

		if(!$amount) {
			if(!$rewrite_amount){
				return;
			}
			$item = BasketItem::FindFirst("basket_id",$this,"product_id",$product,array("use_cache" => true));
			if($item) {
				$item->destroy();
			}
			return;
		}
		if($bi) {
			$bi = array_map(function($v) {return $v->getId();}, iterator_to_array($bi));
		}
		Cache::Clear("BasketItem", $bi);

		$bind_ar = array(
			":basket_id" => $this->getId(),
			":product_id" => $product->getId(),
			":amount" => $amount,
		);

		$update_sql="UPDATE basket_items SET amount = ".($rewrite_amount ? "" : "amount + ").":amount WHERE basket_id = :basket_id AND product_id = :product_id ";
		$sql = "DO $$ BEGIN
			WITH updated AS ($update_sql RETURNING id)
			INSERT INTO basket_items(id, basket_id, product_id, amount)
				 SELECT NEXTVAL('seq_basket_items'), :basket_id, :product_id, :amount
				 WHERE (SELECT COUNT(*) FROM updated) = 0;
			EXCEPTION WHEN unique_violation THEN
			   $update_sql;
			END $$;
		";
		$this->dbmole->doQuery($sql, $bind_ar);
		$item = BasketItem::FindFirst("basket_id",$this,"product_id",$product);

		if($options["update_rank"]){
			$item->setRank(0);
		}

		return $item;
	}

	function getProductAmount($product){
		$product = self::ObjToId($product);
		foreach($this->getItems() as $item){
			if($item->getProductId()==$product){
				return $item->getAmount();
			}
		}
		return 0;
	}

	function containsProduct($product){
		return $this->getProductAmount($product)>0;
	}

	/**
	 *
	 *	$basket->contains($product); // true or false
	 *	$basket->contains($card); // true or false
	 */
	function contains($product_or_card){
		if(is_a($product_or_card,"Card")){
			foreach($product_or_card->getProducts() as $prod){
				if($this->containsProduct($prod)){ return true; }
			}
			return false;
		}
		return $this->containsProduct($product_or_card);
	}

	function getDeliveryMethod(){
		return Cache::Get("DeliveryMethod",$this->getDeliveryMethodId());
	}

	/**
	 * Vraci cenu v dane mene
	 */
	function getDeliveryFee($incl_vat = false,$options = []){
		$options += [
			"check_for_free_shipping_campaign_or_voucher" => true,
		];
		if($options["check_for_free_shipping_campaign_or_voucher"] && $this->freeShipping()){ return 0.0; }
		if($delivery = $this->getDeliveryMethod()){
			$currency = $this->getCurrency();
			$country = $this->_getDeliveryCountry();
			if(is_null($country) && $delivery->getLowestPriceInclVat()!=$delivery->getHighestPriceInclVat()){
				return null;
			}
			$price = $incl_vat ? $delivery->getPriceInclVat($country) : $delivery->getPrice($country);
			if(is_null($price)){ return null; }
			$price = $price / $currency->getRate();
			return $currency->roundPrice($price);
		}
	}

	/**
	 * Vraci cenu v dane mene
	 */
	function getDeliveryFeeInclVat($options = []){
		return $this->getDeliveryFee(true,$options);
	}

	/**
	 * Vrati seznam vsech moznych zemi, do kterych bude mozne dorucit objednavku vytvorenou z tohoto kosiku
	 *
	 *	$basket->getDeliveryCountriesAllowed(); // e.g. ["CZ","SK"]
	 */
	function getDeliveryCountriesAllowed(){
		$region = $this->getRegion();
		$delivery_method = $this->getDeliveryMethod();

		if($delivery_method){
			return $delivery_method->getDeliveryCountriesAllowed($region);
		}

		return $region->getDeliveryCountries();
	}


	function getPaymentMethod(){
		return Cache::Get("PaymentMethod",$this->getPaymentMethodId());
	}

	/**
	 * Vraci cenu v dane mene
	 */
	function getPaymentFee($incl_vat = false, $options = []){
		if($incl_vat){
			return $this->getPaymentFeeInclVat($options);
		}
		$options += [
			"check_for_free_shipping_campaign_or_voucher" => true,
		];
		if($options["check_for_free_shipping_campaign_or_voucher"] && $this->freeShipping()){ return 0.0; }
		if($payment = $this->getPaymentMethod()){
			$currency = $this->getCurrency();
			$price = $payment->getPrice() / $currency->getRate();
			return $currency->roundPrice($price);
		}
	}

	/**
	 * Vraci cenu v dane mene
	 */
	function getPaymentFeeInclVat($options = []){
		$options += [
			"check_for_free_shipping_campaign_or_voucher" => true,
		];
		if($options["check_for_free_shipping_campaign_or_voucher"] && $this->freeShipping()){ return 0.0; }
		if($payment = $this->getPaymentMethod()){
			$currency = $this->getCurrency();
			$price = $payment->getPriceInclVat() / $currency->getRate();
			return $currency->roundPrice($price);
		}
	}

	/**
	 * Je vyloucena platba dobirkou?
	 *
	 * Pokud nakupni kosik obsahuje darkovy poukaz, nelze platit dobirkou
	 */
	function cashOnDeliveryEnabled(){
		if(0<$this->dbmole->selectInt("
			SELECT COUNT(*) FROM
				basket_items,
				products,
				category_cards,
				categories
			WHERE
				basket_items.basket_id=:basket AND
				products.id=basket_items.product_id AND
				category_cards.card_id=products.card_id AND
				categories.id=category_cards.category_id AND
				categories.code=:code
		",[":basket" => $this, ":code" => "gift_vouchers"])){
			return false;
		}

		$digital_product = Tag::GetInstanceByCode("digital_product");
		foreach($this->getItems() as $item){
			$product = $item->getProduct();
			$card = $product->getCard();
			if($card->containsTag($digital_product)){
				return false;
			}
		}

		return true;
	}

	/**
	 * Je vyzadovana pouze online platba?
	 */
	function onlinePaymentMethodRequired(){
		return 0<$this->dbmole->selectInt("
			SELECT COUNT(*) FROM
				basket_items,
				products,
				category_cards,
				categories
			WHERE
				basket_items.basket_id=:basket AND
				products.id=basket_items.product_id AND
				category_cards.card_id=products.card_id AND
				categories.id=category_cards.category_id AND
				categories.code=:code
		",[":basket" => $this, ":code" => "gift_vouchers"]);
	}

	function getPriceToPay($incl_vat = true, &$price_without_rounding = null){

		$price = $this->getItemsPrice($incl_vat);

		$price += $this->getDeliveryFee($incl_vat);
		$price += $this->getPaymentFee($incl_vat);
		$price -= $this->getVouchersDiscountAmount($incl_vat);
		$price -= $this->getCampaignsDiscountAmount($incl_vat);

		$price_without_rounding = $price;

		$price = round($price,$this->getCurrencyDecimalsSummary());
		return $price;
	}

	/**
	 * Castka, za kterou je nutne jeste nakoupit, aby byla doprava zdarma.
 	 *
	 *	$add_more = $basket->getAddMoreToGetFreeDelivery();
	 *	// 99.0 - jeste 99.0 penez v dane mene pro dopravu zdarma!
	 *	// 0.0 - pro dopravu zdarma uz neni treba nic do kosiku pridavat
	 *	// null - kampan pro dopravu zdarma neexistuje
	 */
	function getAddMoreToGetFreeDelivery(){
		list($delivery_methods,$payment_methods) = ShippingCombination::GetAvailableMethods4Basket($this);
		$filter = function($item){ return $item->getPrice()>0.0; };
		$delivery_methods = array_filter($delivery_methods,$filter);
		$payment_methods = array_filter($payment_methods,$filter);
		if(sizeof($delivery_methods)==0 && sizeof($payment_methods)==0){
			// There is no delivery method or payment method with some price
			return 0.0;
		}

		/*
		foreach($this->getBasketCampaigns() as $bs){
			if(!$bs->freeShipping()){ continue; }
			$required_price = $bs->getMinimalItemsPriceInclVat();
			$current_price = $this->getItemsPriceInclVat();
			$add_more = $required_price - $current_price;
			if($add_more<0.0){
				return 0.0;
			}
			return $add_more;
		}
		return;
		*/

		$region = $this->getRegion();
		($user = $this->getUser()) || ($user = User::GetAnonymousUser());
		$current_price = $this->getItemsPriceInclVat();

		$conditions = [
			"active",
			"free_shipping",
			"(regions->>:region_code)::BOOLEAN",
			"valid_from IS NULL OR valid_from<=:now",
			"valid_to IS NULL OR valid_to>=:now",
			"required_customer_group_id IS NULL OR required_customer_group_id IN :required_customer_groups",
			"required_delivery_method_id IS NULL OR required_delivery_method_id=:required_delivery_method",
			"required_payment_method_id IS NULL OR required_payment_method_id=:required_payment_method"
		];
		$bind_ar = [
			":region_code" => $region->getCode(),
			":now" => now(),
			":required_customer_groups" => $user->getCustomerGroups(),
			":required_delivery_method" => $this->getDeliveryMethod(),
			":required_payment_method" => $this->getPaymentMethod(),
		];

		$campaigns = Campaign::FindAll([
			"conditions" => $conditions,
			"bind_ar" => $bind_ar,
			"order_by" => "minimal_items_price_incl_vat",
		]);
		$campaigns = $this->_filterOutInappropriateCampaigns($campaigns);
		if($campaigns){
			$campaign = $campaigns[0];
			$currency = $this->getCurrency();
			$required_price = $campaign->getMinimalItemsPriceInclVat() / $currency->getRate();

			$add_more = $required_price - $current_price;
			if($add_more<0.0){
				return 0.0;
			}
			return $currency->roundPrice($add_more);
		}
	}

	/**
	 * Ma kosik nastavenou fakturacni adresu?
	 */
	function hasAddressSet(){
		foreach(self::GetAddressFields() as $k => $req){
			if($req && strlen((string)$this->g("$k"))==0){ return false; }
		}
		return true;
	}

	/**
	 * Ma kosik nastavenou dorucovaci adresu?
	 */
	function hasDeliveryAddressSet(){
		$fields = self::GetAddressFields(["prefix" => "delivery_"]);
		if(!$this->deliveryAddressEditableByUser()){
			$fields = [
				"delivery_firstname" => true,
				"delivery_lastname" => true,
			];
		}
		foreach($fields as $k => $req){
			$method = String4::ToObject($k)->camelize()->prepend("get")->toString(); // "delivery_address_zip" -> "getDeliveryAddressZip"
			if($req && strlen((string)$this->$method())==0){ return false; }
		}
		return true;
	}

	function getVouchersLister(){
		return $this->getLister("Vouchers");
	}

	/**
	 * !! Pozor !! Toto nevraci Voucher[], ale BasketVoucher[]
	 */
	function getVouchers(){
		return $this->getBasketVouchers();
		//return $this->getVouchersLister()->getRecords();
	}

	/**
	 * Pozor!! Toto nevraci Voucher[], ale BasketItem[]
	 *
	 * @return BasketVoucher[]
	 */
	function getBasketVouchers(){
		return Cache::Get("BasketVoucher",$this->getVouchersLister()->getIds());
	}

	/**
	 * Alias pro getBasketCampaigns()
	 *
	 * !! Pozor !! Toto nevraci Campaigns[], ale BasketCampaign[]
	 */
	function getCampaigns(){
		return $this->getBasketCampaigns();
	}

	/**
	 *
	 *	$campaigns = $basket->getBasketCampaigns();
	 *	$campaigns = $basket->getBasketCampaigns($delivery_method); // tady lze ziskat kampane, ktere by se tykaly kosiku, pokud by v nem byla nastavena jina $delivery_method
	 */
	function getBasketCampaigns($considered_delivery_method = null){
		$out = [];

		$currency = $this->getCurrency();
		($user = $this->getUser()) || ($user = User::GetAnonymousUser());
		$region = $this->getRegion();
		$delivery_method = $considered_delivery_method ? $considered_delivery_method : $this->getDeliveryMethod();
		$items_price_incl_vat = $this->getItemsPriceInclVat() * $currency->getRate();
		$now = now();

		// Zakladni podminky
		$conditions = $bind_ar = [];
		$conditions = [
			"(regions->>:region_code)::BOOLEAN",
			"active",
			"valid_from IS NULL OR valid_from<:now",
			"valid_to IS NULL OR valid_to>:now",
			"required_customer_group_id IS NULL OR required_customer_group_id IN :required_customer_groups",
			"required_delivery_method_id IS NULL OR required_delivery_method_id=:required_delivery_method",
			"required_payment_method_id IS NULL OR required_payment_method_id=:required_payment_method",
			"minimal_items_price_incl_vat<=:items_price_incl_vat",
		];
		$bind_ar[":region_code"] = $region->getCode();
		$bind_ar[":now"] = $now;
		$bind_ar[":required_customer_groups"] = $user->getCustomerGroups();
		$bind_ar[":required_delivery_method"] = $this->getDeliveryMethod();
		$bind_ar[":required_payment_method"] = $this->getPaymentMethod();
		$bind_ar[":items_price_incl_vat"] = $items_price_incl_vat;
		if($delivery_method){
			$conditions[] = "delivery_method_id IS NULL OR delivery_method_id=:delivery_method";
			$bind_ar[":delivery_method"] = $delivery_method;
		}else{
			$conditions[] = "delivery_method_id IS NULL";
		}

		// Napred hledame darky
		$_conditions = $conditions;
		$_bind_ar = $bind_ar;
		$_conditions[] = "gift_product_id IS NOT NULL";
		$campaigns = Campaign::FindAll([
			"conditions" => $_conditions,
			"bind_ar" => $_bind_ar,
		],[
			"use_cache" => true,
		]);
		$campaigns = $this->_filterOutInappropriateCampaigns($campaigns);
		if($campaigns){
			$out[] = new BasketCampaign($this,$campaigns[0]);
		}

		// Pak hledame kampan s nejvyhodnejsi procentrni slevou
		$_conditions = $conditions;
		$_bind_ar = $bind_ar;
		$_conditions[] = "discount_percent>0.0";
		$campaigns = Campaign::FindAll([
			"conditions" => $_conditions,
			"bind_ar" => $_bind_ar,
			"order_by" => "discount_percent DESC", // nejvyssi sleva jako prvni
		],[
			"use_cache" => true,
		]);
		$campaigns = $this->_filterOutInappropriateCampaigns($campaigns);
		if($campaigns){
			$out[] = new BasketCampaign($this,$campaigns[0]);
		}

		// Ted kampan s dopravou zdarma
		$_conditions = $conditions;
		$_bind_ar = $bind_ar;
		$_conditions[] = "free_shipping";
		$campaigns = Campaign::FindAll([
			"conditions" => $_conditions,
			"bind_ar" => $_bind_ar,
		],[
			"use_cache" => true,
		]);
		$campaigns = $this->_filterOutInappropriateCampaigns($campaigns);
		if($campaigns){
			$out[] = new BasketCampaign($this,$campaigns[0]);
		}

		return $out;
	}

	protected function _filterOutInappropriateCampaigns($campaigns){
		$out = [];
		foreach($campaigns as $campaign){
			foreach($campaign->getDesignatedForTags() as $t){
				if(!$this->containsProductWithTag($t)){
					continue 2;
				}
			}
			foreach($campaign->getExcludedForTags() as $t){
				if($this->containsProductWithTag($t)){
					continue 2;
				}
			}
			$out[] = $campaign;
		}
		return $out;
	}

	/**
	 * Ma tento kosik dopravu zdarma?
	 *
	 * Tj. uz je aktivni kampan na dopravu zdarma nebo je v kosiku voucher na dopravu zdarma.
	 */
	function freeShipping($considered_delivery_method = null){

		if(is_null($considered_delivery_method)){ $considered_delivery_method = $this->getDeliveryMethod(); }

		foreach($this->getBasketCampaigns($considered_delivery_method) as $bc){
			if($bc->freeShipping()){
				return true;
			}
		}

		foreach($this->getBasketVouchers() as $bv){
			$voucher = $bv->getVoucher();
			if($voucher->freeShipping()){
				return true;
			}
		}

		return false;
	}

	/**
	 * Zmena obsahu kosiku zmenu jeho checksum
	 *
	 * Pouziva se pri vytvareni objednavky.
	 * Chceme mit totiz jistotu, ze uzivatel vytvari objednavku podle toho, co vidi v sumarizaci kosiku.
	 */
	function getChecksum($options = []){
		$options += [
			"consider_products_amount" => true,
		];

		$ary = [];
		foreach($this->getItems() as $item){
			if($options["consider_products_amount"]){
				$ary[] = [$item->getProductId(),$item->getAmount()];
			}else{
				$ary[] = [$item->getProductId()];
			}
		}

		return md5(serialize($ary));
	}

	function canOrderBeCreated(&$messages = [],$options = []){
		$messages = [];

		if($this->isEmpty()){
			$messages[] = new BasketErrorMessage(_("Košík je prázdný"));
			return false;
		}

		foreach($this->getItems() as $item){
			$product = $item->getProduct();
			$card = $product->getCard();

			if($product->isDeleted() || !$product->isVisible() || $card->isDeleted() || !$card->isVisible()){
				$messages[] = new BasketErrorMessage(
					sprintf(_("Produkt <em>%s (%s)</em> byl vyřazen z nabídky"),h($product->getName()),h($product->getCatalogId())),
					[
						"correction_text" => _("odebrat z košíku"),
						"correction_url" => $this->_buildLink(["action" => "basket_items/destroy", "id" => $item->getId()]),
					]
				);
				continue;
			}

			$price = $item->getProductPrice();
			if(!$price->priceExists()){
				$messages[] = new BasketErrorMessage(
					sprintf(_("Produkt <em>%s (%s)</em> byl vyřazen z našeho ceníku"),h($product->getName()),h($product->getCatalogId())),
					[
						"correction_text" => _("odebrat z košíku"),
						"correction_url" => $this->_buildLink(["action" => "basket_items/destroy", "id" => $item->getId()]),
					]
				);
				continue;
			}

			$max_quantity = $product->getCalculatedMaximumQuantityToOrder();
			$min_quantity = $product->getCalculatedMinimumQuantityToOrder();
			$step = $product->getOrderQuantityStep();
			$amount = $item->getAmount();

			if($max_quantity<=0){
				$messages[] = new BasketErrorMessage(sprintf(_("Produkt <em>%s (%s)</em> není skladem"),h($product->getName()),h($product->getCatalogId())),
					[
						"correction_text" => _("odebrat z košíku"),
						"correction_url" => $this->_buildLink(["action" => "basket_items/destroy", "id" => $item->getId()]),
					]
				);
				continue;
			}

			if($amount>$max_quantity){
				$messages[] = new BasketErrorMessage(sprintf(_("Produkt <em>%s (%s)</em> lze objednat v počtu nejvýše: %s"),h($product->getName()),h($product->getCatalogId()),$max_quantity),
					[
						"correction_text" => sprintf(_("změnit množství na %s"),$max_quantity),
						"correction_url" => $this->_buildLink(["action" => "basket_items/edit", "id" => $item->getId(), "amount" => $max_quantity]),
					]
				);
				continue;
			}

			if($amount<$min_quantity){
				$messages[] = new BasketErrorMessage(sprintf(_("Produkt <em>%s (%s)</em> lze objednat v počtu nejméně: %s"),h($product->getName()),h($product->getCatalogId()),$min_quantity),
					[
						"correction_text" => sprintf(_("změnit množství na %s"),$min_quantity),
						"correction_url" => $this->_buildLink(["action" => "basket_items/edit", "id" => $item->getId(), "amount" => $min_quantity]),
					]
				);
				continue;
			}

			if($amount%$step!==0){
				$new_quantity = ceil($amount / $step) * $step;
				$messages[] = new BasketErrorMessage(sprintf(_("Množství u produktu <em>%s (%s)</em> musí být násobkem čísla %s"),h($product->getName()),h($product->getCatalogId()),$step),
					[
						"correction_text" => sprintf(_("změnit množství na %s"),$new_quantity),
						"correction_url" => $this->_buildLink(["action" => "basket_items/edit", "id" => $item->getId(), "amount" => $new_quantity]),
					]
				);
				continue;
			}
		}

		foreach($this->getBasketVouchers() as $b_voucher){
			$voucher = $b_voucher->getVoucher();
			if(!$voucher->isApplicable($this,$msg)){
				$messages[] = new BasketErrorMessage($msg,[
					"correction_text" => sprintf(_("odstranit voucher")),
					"correction_url" => $this->_buildLink(["action" => "basket_vouchers/destroy", "id" => $b_voucher->getId()])
				]);
			}
		}

		$currency = $this->getCurrency();
		if($this->getPriceToPay()<0.0){
			$messages[] = new BasketErrorMessage(_("Celková cena nesmí být záporná. Přihoďte do košíku ještě něco malého :)"),[
				"correction_text" => sprintf(_("přejít do katalogu")),
				"correction_url" => $this->_buildLink(["action" => "categories/index"])
			]);
		}elseif($this->getPriceToPay()!==0.0 && $this->getPriceToPay()<$currency->getLowestOrderPrice()){
			Atk14Require::Helper("modifier.display_price");
			$messages[] = new BasketErrorMessage(sprintf(_("Celková cena musí být alespoň %s. Přihoďte do košíku ještě něco malého :)"),smarty_modifier_display_price($currency->getLowestOrderPrice(),["currency" => $currency, "format" => "plain", "summary" => true])),[
				"correction_text" => sprintf(_("přejít do katalogu")),
				"correction_url" => $this->_buildLink(["action" => "categories/index"])
			]);
		}

		$delivery_method = $this->getDeliveryMethod();
		$payment_method = $this->getPaymentMethod();

		list($delivery_methods,$payment_methods) = ShippingCombination::GetAvailableMethods4Basket($this);
		$delivery_method_ids = array_map(function($o){ return $o->getId(); },$delivery_methods);
		$payment_method_ids = array_map(function($o){ return $o->getId(); },$payment_methods);

		if(
			($delivery_method && !in_array($delivery_method->getId(),$delivery_method_ids)) ||
			($payment_method && !in_array($payment_method->getId(),$payment_method_ids)) ||
			($delivery_method && !$payment_method) ||
			(!$delivery_method && $payment_method) ||
			(!ShippingCombination::IsAllowed($delivery_method,$payment_method))
		){
			// Beware! Here, the basket state is changing.
			$this->s([
				"delivery_method_id" => null,
				"delivery_method_data" => null,
				"payment_method_id" => null,
			]);
			$delivery_method = $payment_method = null;
		}

		if($delivery_method && !in_array($delivery_method->getId(),$delivery_method_ids)){
			$messages[] = new  BasketErrorMessage(sprintf(_("Doručovací metoda <em>%s</em> nemůže být vybrána"),h($delivery_method->getLabel())),[
				"correction_text" => _("vyberte jinou metodu"),
				"correction_url" => $this->_buildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
			]);
		}

		if($payment_method && !in_array($payment_method->getId(),$payment_method_ids)){
			$messages[] = new  BasketErrorMessage(sprintf(_("Platební metoda <em>%s</em> nemůže být vybrána"),h($payment_method->getLabel())),[
				"correction_text" => _("vyberte jinou metodu"),
				"correction_url" => $this->_buildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
			]);
		}

		# Pokud je zvolena dorucovaci sluzba (napr. Zasilkovna), musi byt zvolena i pobocka
		if ($delivery_method && !is_null($delivery_method->getDeliveryServiceId()) && is_null($this->getDeliveryMethodData())) {
			$messages[] = new BasketErrorMessage(_("Výdejní místo pro doručení objednávky nebylo vybráno"),[
				"correction_text" => _("vyberte výdejní místo"),
				"correction_url" => $this->_buildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
				"request_method" => "GET",
			]);
		}

		if(
			$delivery_method &&
			!$this->personalPickupOnStoreSelected() && // tady pripadne umoznime os. vyzvednuti na prodejne, i je prodejna v nepodporovane zemi
			$this->getDeliveryAddressCountry() &&
			!in_array($this->getDeliveryAddressCountry(),$this->getDeliveryCountriesAllowed())
		){
			$messages[] = new BasketErrorMessage(_("Objednávku nelze doručit na danou doručovací adresu"),[
				"correction_text" => _("upravte doručovací adresu"),
				"correction_url" => $this->_buildLink(["action" => "checkouts/set_billing_and_delivery_data"]),
				"request_method" => "GET",
			]);
		}

		if(($delivery_service_branch = $this->getDeliveryServiceBranch()) && !$delivery_service_branch->isActive()){
			$messages[] = new BasketErrorMessage(_("Vybrané výdejní místo pro doručení objednávky již není aktivní"),[
				"correction_text" => _("vyberte jiné výdejní místo"),
				"correction_url" => $this->_buildLink(["action" => "checkouts/set_payment_and_delivery_method"]),
				"request_method" => "GET",
			]);
		}

		if($messages){
			return false;
		}

		if(!$delivery_method || !$payment_method){
			return false;
		}

		return true;
	}

	protected function _buildLink($params){
		$params += [
			"namespace" => "",
		];
		return Atk14Url::BuildLink($params);
	}

	/**
	 * Vytvori objednavku z tohoto kosiku
	 *
	 *	$order = $basket->createOrder();
	 *
	 * Vytvoreni objednavky bez DPH
	 *
	 *	$order = $basket->createOrder([
	 *		"without_vat" => true
	 *	]);
	 */
	function createOrder($options = []){
		$options += [
			"without_vat" => false,
			"send_notification" => true,
			"mailer" => null,
		];

		$currency = $this->getCurrency();

		$without_vat = !!$options["without_vat"];
		$incl_vat = !$without_vat;
		$decimals = $without_vat ? $currency->getDecimals() : INTERNAL_PRICE_DECIMALS;
		$delivery_method = $this->getDeliveryMethod();
		$delivery_country = $this->_getDeliveryCountry();
		$payment_method = $this->getPaymentMethod();

		$values = $this->toArray();

		foreach([
			"id",
			"created_from_addr",
			"updated_from_addr",
			"created_at",
			"updated_at",
			"subscribe_to_newsletter",
		] as $key){
			unset($values[$key]);
		}

		$values["without_vat"] = $without_vat;

		// Toto zajisti, ze pokud neni nastavena fakturacni adresa, pouzije se dorucovaci.
		// Osetreno to je to v jednotlivych funkcich getAddressStreet(), getAddressCity().
		// Naopak u dorucovaci adresy metody getDeliveryAddress*() mohou nacitat hodnoty z vybraneho mista odberu.
		$address_fields = array_keys(Basket::GetAddressFields());
		foreach(array_keys(Basket::GetAddressFields(["note" => true, "prefix" => "delivery_"])) as $_k){
			$address_fields[] = $_k;
		}
		foreach($address_fields as $key){
			$method = String4::ToObject($key)->camelize()->prepend("get")->toString(); // "address_zip" -> "getAddressZip"
			$values[$key] = $this->$method();
		}

		foreach($address_fields as $key){
			$method = String4::ToObject($key)->camelize()->prepend("get")->toString(); // "address_zip" -> "getAddressZip"
			$values[$key] = $this->$method();
		}

		$payment_method = $this->getPaymentMethod();
		$payment_gateway = $payment_method->getPaymentGateway();

		$values["delivery_method_data"] = $this->getDeliveryMethodData(["as_json" => true]);

		$delivery_fee_incl_vat = $this->getDeliveryFee($incl_vat,["check_for_free_shipping_campaign_or_voucher" => false]);
		$delivery_fee_vat_percent = $incl_vat ? $delivery_method->getVatPercent($delivery_country) : 0.0;
		$delivery_fee = $this->_delVat($delivery_fee_incl_vat,$delivery_fee_vat_percent);
		$values["delivery_fee_incl_vat"] = $delivery_fee_incl_vat;
		$values["delivery_fee_vat_percent"] = $delivery_fee_vat_percent;

		$payment_fee_incl_vat = $this->getPaymentFee($incl_vat,["check_for_free_shipping_campaign_or_voucher" => false]);
		$payment_fee_vat_percent = $incl_vat ? $payment_method->getVatPercent() : 0.0;
		$payment_fee = $this->_delVat($payment_fee_incl_vat,$payment_fee_vat_percent);
		$values["payment_fee_incl_vat"] = $payment_fee_incl_vat;
		$values["payment_fee_vat_percent"] = $payment_fee_vat_percent;

		$free_shipping = $this->freeShipping(); // false, true
		$shipping_fee_incl_vat = $delivery_fee_incl_vat; // Pozor! Zjednoduseni. Tady se neuvazuje poplatek za platbu. Ten byva obvykle 0. A nize by to delalo problem se stanovenim sazby DPH, kdyby byly sazby za dopravu a platbu jine.

		$values["price_to_pay"] = $price_to_pay = $this->getPriceToPay($incl_vat,$price_to_pay_without_rounding);

		if(!$options["send_notification"]){
			// Notification delayed
			$values["creation_notified"] = false;
		}

		$values["integrity_key"] = (string)$this->getId();

		$order = Order::CreateNewRecord($values);
		//$order->setNewOrderStatus(OrderStatus::DetermineInitialStatus($values["payment_method_id"])->getCode());

		foreach($this->getItems() as $item){
			$p_price = $item->getProductPrice();
			$vat_percent = $incl_vat ? $item->getVatPercent() : 0.0;
			$unit_price_incl_vat = $p_price->getUnitPrice($incl_vat); // pekne zaokrouhlena cena na 2 (resp. 4 u cm) des. mista
			$unit_price = $this->_delVat($unit_price_incl_vat,$vat_percent); // toto bude zaokrouhleno na INTERNAL_PRICE_DECIMALS mist
			$unit_price_before_discount_incl_vat = $p_price->getUnitPriceBeforeDiscount($incl_vat); // pekne zaokrouhleno
			$unit_price_before_discount = $this->_delVat($unit_price_before_discount_incl_vat,$vat_percent); // zaokrouhleno na INTERNAL_PRICE_DECIMALS mist

			OrderItem::CreateNewRecord([
				"order_id" => $order,
				"product_id" => $item->getProductId(),
				"amount" => $item->getAmount(),
				//"unit_price" => $unit_price,
				"unit_price_incl_vat" => $unit_price_incl_vat,
				//"unit_price_before_discount" => $unit_price_before_discount,
				"unit_price_before_discount_incl_vat" => $unit_price_before_discount_incl_vat,
				"vat_percent" => $vat_percent,
				# zda byla poskytnuta sleva v kampani nebo pri pouziti poukazu (vouchers)
				# napr. u zlevneneho zbozi se neposkytuje
				"campaign_discount_applied" => (($this->getCampaignsDiscountAmount()>0) || ($this->getVouchersDiscountAmount(null, ["free_shipping" => false, "discount_amount" => false])>0)) && !$item->discounted(),
			]);

			$product = $item->getProduct();

			// Blokace skladove zasoby
			// Blokace se uz vybira z view v_stockcount_blocations
			/*
			if($product->considerStockcount()){
				StockcountBlocation::CreateNewRecord([
					"order_id" => $order,
					"product_id" => $item->getProductId(),
					"stockcount" => $item->getAmount(),
				]);
			}
			*/
		}

		// Pridani polozky "Zaokrouhlení"
		$delta = $price_to_pay - $price_to_pay_without_rounding;
		$delta = $currency->roundPrice($delta);
		if(abs($delta)>=$currency->getLowestPrice()){
			$amount = $delta > 0.0 ? 1 : -1;
			$delta_product = Product::FindByCode("price_rounding");
			$delta_vat_percent = $incl_vat ? $delta_product->getVatPercent() : 0.0;
			$delta_price = abs($delta);
			//$delta_price_with_no_vat = ($delta_price / (100.0 + $delta_vat_percent)) * 100.0;

			OrderItem::CreateNewRecord([
				"order_id" => $order,
				"product_id" => $delta_product,
				"amount" => $amount,
				"unit_price_incl_vat" => $delta_price,
				"vat_percent" => $delta_vat_percent,
				"rank" => 9999, // chceme, aby to bylo za prip. darkama, viz nize
			]);
		}

		$free_shipping_treated = false;

		// Vouchery
		foreach($this->getBasketVouchers() as $b_voucher){
			$voucher_obj = $b_voucher->getVoucher();
			$discount_amount = $b_voucher->getDiscountAmount($incl_vat); // tady castka za pripadnou dopravu zdarma neni

			if($voucher_obj->freeShipping() && $free_shipping && !$free_shipping_treated && $shipping_fee_incl_vat!==0.0){
				OrderVoucher::CreateNewRecord([
					"order_id" => $order,
					"voucher_id" => $b_voucher->getVoucherId(),
					"discount_amount" => $shipping_fee_incl_vat,
					"free_shipping" => true,
					"vat_percent" => $delivery_fee_vat_percent,
				]);
				$free_shipping_treated = true;

				if($discount_amount===0.0){
					// uz neni treba ukladat stejny voucher s nulovou slevou
					continue;
				}
			}

			OrderVoucher::CreateNewRecord([
				"order_id" => $order,
				"voucher_id" => $b_voucher->getVoucherId(),
				"discount_amount" => $discount_amount,
				"vat_percent" => $voucher_obj->getVatPercent(), // Koupeny slev. kupon ma DPH 21%, jiny slevovy kupon tady ma NULL.
			]);
		}

		// Kampane
		foreach($this->getBasketCampaigns() as $b_campaign){
			$campaign_obj = $b_campaign->getCampaign();
			$discount_amount = $b_campaign->getDiscountAmount($incl_vat); // tady castka za pripadnou dopravu zdarma neni
			$gift_product = $b_campaign->getGiftProduct();

			// gift
			if($gift_product){
				$gift_product_vat_percent = $incl_vat ? $gift_product->getVatPercent() : 0.0;
				$gift_order_item = OrderItem::CreateNewRecord([
					"order_id" => $order,
					"product_id" => $gift_product,
					"amount" => $b_campaign->getGiftAmount(),
					"unit_price_incl_vat" => 0.0,
					"vat_percent" => $gift_product_vat_percent,
					"rank" => 9999, // chceme, aby to bylo za prip. darkama, viz nize
				]);
				OrderCampaign::CreateNewRecord([
					"order_id" => $order,
					"campaign_id" => $b_campaign->getCampaignId(),
					"discount_amount" => 0.0,
					"free_shipping" => false,
					"vat_percent" => $gift_product_vat_percent,
					"gift_order_item_id" => $gift_order_item,
				]);
				continue;
			}

			if($campaign_obj->freeShipping() && $free_shipping && !$free_shipping_treated && $shipping_fee_incl_vat!==0.0){
				OrderCampaign::CreateNewRecord([
					"order_id" => $order,
					"campaign_id" => $b_campaign->getCampaignId(),
					"discount_amount" => $shipping_fee_incl_vat,
					"free_shipping" => true,
					"vat_percent" => $delivery_fee_vat_percent,
				]);
				$free_shipping_treated = true;

				if($discount_amount===0.0){
					// uz neni treba ukladat stejnou kampan s nulovou slevou
					continue;
				}
			}

			OrderCampaign::CreateNewRecord([
				"order_id" => $order,
				"campaign_id" => $b_campaign->getCampaignId(),
				"discount_amount" => $discount_amount,
				"vat_percent" => null,
			]);
		}

		// Toto je pro pripad, ze by Basket a Order pocitaly price_to_pay jinak...
		if($order->recalculatePriceToPay()){
			trigger_error(sprintf("Basket::createOrder(): price_to_pay mismatch on Order#%s; corrected: %s -> %s",$order->getId(),$price_to_pay,$order->getPriceToPay()));
		}

		// Vytvoreni platebni transakce
		if($payment_gateway){
			PaymentTransaction::CreateNewRecord([
				"order_id" => $order,
				"payment_gateway_id" => $payment_gateway,
			]);
		}

		// Order creation notification
		if($options["send_notification"]){
			$mailer = $options["mailer"] ? $options["mailer"] : Atk14Mailer::GetInstance();
			$mailer->notify_order_creation($order);
		}

		// Next automatic status
		$next_status = OrderStatus::DetermineNextAutomaticStatus($order);
		if($next_status){
			$next_status->notificationEnabled() && $options["send_notification"] && sleep(1); // Sleep for 1 sec to ensure the payment request email is to be sent later than order creation notification
			$order->setNewOrderStatus([
				"order_status_id" => $next_status->getId(),
				"order_status_set_at" => now(),
				"order_status_set_by_user_id" => null,
			]);
		}

		# v ApplicationModel se automaticky vyplni prihlaseny uzivatel,
		# kdyz dojde ke zmene stavu diky predchozimu kodu.
		# pri nove vytvorene objednavce to ale nechceme.
		$order->s([
			"updated_at" => null,
			"updated_by_user_id" => null
		]);
		return $order;
	}

	function _delVat($price,$vat_percent){
		if(is_null($price)){ return null; }

		$vat_percent = (float)$vat_percent;
		$out = ($price / (100.0 + $vat_percent)) * 100.0;
		$out = round($out,INTERNAL_PRICE_DECIMALS);
		return $out;
	}

	/**
	 * Slouceni dvou kosiku
	 *
	 *	$new_basket->mergeBasket($current_basket);
	 *	// a dale se uz pouziva $new_basket
	 */
	function mergeBasket($basket){

		$update_ar = array();
		foreach(array(
			"payment_method_id",
			"delivery_method_id",
			//"delivery_method_data",
			"note",
		) as $f){
			$v = $basket->g($f);
			if(!is_null($v)){
				$update_ar[$f] = $v;
			}
		}

		if(isset($update_ar["delivery_method_id"])){
			$update_ar["delivery_method_data"] = $basket->g("delivery_method_data");
		}

		$update_ar && $this->s($update_ar);
		$this->_mergeBasketItems($basket);

		$lister = $this->getVouchersLister();
		foreach($basket->getBasketVouchers() as $b_voucher){
			$voucher = $b_voucher->getVoucher();
			if(!$lister->contains($voucher)){
				$lister->add($voucher);
			}
		}
	}

	function getFirstname(){
		return $this->_getAddressOrDelivery("firstname");
	}

	function getLastname(){
		return $this->_getAddressOrDelivery("lastname");
	}

	function getCompany(){
		return $this->_getAddressOrDelivery("company");
	}

	function getAddressStreet(){
		return $this->_getAddressOrDelivery("address_street");
	}

	function getAddressStreet2(){
		return $this->_getAddressOrDelivery("address_street2");
	}

	function getAddressCity(){
		return $this->_getAddressOrDelivery("address_city");
	}

	function getAddressState(){
		return $this->_getAddressOrDelivery("address_state");
	}

	function getAddressZip(){
		return $this->_getAddressOrDelivery("address_zip");
	}

	function getAddressCountry(){
		return $this->_getAddressOrDelivery("address_country");
	}

	function _getAddressOrDelivery($key){
		if($this->hasAddressSet()){
			return $this->g($key);
		}
		if($this->deliveryAddressEditableByUser() || in_array($key,["firstname","lastname"])){
			return $this->g("delivery_$key");
		}
		$user = $this->getUser();
		if($user && !$user->isAnonymous() && !$this->deliveryAddressEditableByUser()){
			$method = String4::ToObject("get_$key")->camelize(["lower" => true])->toString(); // address_city -> getAddressCity
			return $user->$method();
		}
	}

	/**
	 * Ma kazdy produkt v kosiku dane klicove slovo?
	 */
	function hasEveryProductTag($tag){
		if($this->isEmpty()){ return false; }
		foreach($this->getBasketItems() as $item){
			$product = $item->getProduct();
			if(!$product->containsTag($tag,["consider_categories" => true])){ return false; }
		}
		return true;
	}

	function containsProductWithTag($tag){
		static $CATEGORIES_PREFETCHED = [];

		// We should prefetch all categories in all paths.
		// It helps Product::containsProductWithTag($tag,["consider_categories" => true]) to be faster.
		$checksum = $this->getChecksum(["consider_products_amount" => false]);
		if(!isset($CATEGORIES_PREFETCHED[$checksum])){
			foreach($this->getItems() as $item){
				$card = $item->getProduct()->getCard();
				$categories = $card->getCategories();
				Category::PrecacheParentsForCategories($categories);
			}
			$CATEGORIES_PREFETCHED[$checksum] = true;
		}

		foreach($this->getBasketItems() as $item){
			$product = $item->getProduct();
			if($product->containsTag($tag,["consider_categories" => true])){ return true; }
		}
		return false;
	}

	/**
	 *
	 *	$basket1->addProduct("1111/11111111",2);
	 *	$basket1->addProduct("2222/22222222",3);
	 *
	 *	$basket2->addProduct("1111/11111111",3);
	 *	$basket2->addProduct("3333/33333333",5);
	 *
	 *	$basket1->mergeBasketItems($basket2);
	 *	// 1111/11111111 3
	 *	// 2222/22222222 3
	 *	// 3333/33333333 5
	 */
	protected function _mergeBasketItems($basket){
		$dbmole = $this->getDbmole();

		$dbmole->doQuery("
			DELETE FROM basket_items WHERE basket_id=:to_id AND product_id IN (SELECT product_id FROM basket_items WHERE basket_id=:from_id);
			CREATE TEMPORARY TABLE temp_basket_items AS SELECT * FROM basket_items WHERE basket_id=:from_id;
			UPDATE temp_basket_items SET basket_id=:to_id, id=NEXTVAL('seq_basket_items'),updated_at=:now;
			INSERT INTO basket_items SELECT * FROM temp_basket_items;
			DROP TABLE temp_basket_items;
		",[
			":from_id" => $basket->getId(),
			":to_id" => $this->getId(),
			":now" => now(),
		]);

		$this->basket_items = null;
	}

	function destroy($options = []){
		if($this->isDummy()){
			throw new Exception("Dummy basket cannot be erased!");
		}

		return parent::destroy($options);
	}

	function setValues($values,$options = []){
		if($this->isDummy()){
			throw new Exception("Dummy basket cannot be updated!");
		}
		return parent::setValues($values,$options);
	}

	function _getDeliveryCountry(){
		$country = $this->getDeliveryAddressCountry();
		$country = is_null($country) ? $this->g("address_country") : $country;
		if(is_null($country)){
			$region = $this->getRegion();
			$countries = $region->getDeliveryCountries();
			if(sizeof($countries)==1){
				// There is no way to deliver order to another country
				$country = $countries[0];
			}
		}
		return $country;
	}

	function getDeliveryMethodData($options = []) {
		if(!is_array($options)){
			$options = ["as_json" => $options];
		}
		$options += [
			"as_json" => false,
		];

		$json = $this->g("delivery_method_data");
		if(!$json){ return null; }

		$data = json_decode($json,true);
		if(!$data){ return null; }

		$d_method = $this->getDeliveryMethod();
		if(!$d_method){ return null; }

		if($data["delivery_service_id"]!==$d_method->getDeliveryServiceId()){ return null; }

		return $options["as_json"] ? $json : $data;
	}

	/**
	 * Vrati nastavenou pobocku dorucovaci sluzby
	 *
	 * @return DeliveryServiceBranch
	 */
	function getDeliveryServiceBranch() {
		static $CACHE = [];
		$method_data = $this->getDeliveryMethodData();
		if (is_null($this->getDeliveryMethod()) || is_null($method_data)) {
			return null;
		}
		$cache_key = $this->getDeliveryMethod()->getDeliveryServiceId()."_".$method_data["external_branch_id"];
		if(!array_key_exists($cache_key,$CACHE)){
			$CACHE[$cache_key] = DeliveryServiceBranch::FindFirst("delivery_service_id", $this->getDeliveryMethod()->getDeliveryServiceId(), "external_branch_id", $method_data["external_branch_id"]);
		}
		return $CACHE[$cache_key];
	}

	/**
	 * Bylo vybrano doruceni do dorucovaciho mista?
	 *
	 */
	function deliveryToDeliveryPointSelected(){
		$d_method = $this->getDeliveryMethod();
		return $d_method && $d_method->getDeliveryService();
	}

	/**
	 * Is in-store pickup selected?
	 */
	function personalPickupOnStoreSelected(){
		$d_method = $this->getDeliveryMethod();
		return $d_method && $d_method->getPersonalPickupOnStore();
	}

	function deliveryAddressEditableByUser(){
		return
			!$this->deliveryToDeliveryPointSelected() &&
			!$this->personalPickupOnStoreSelected();
	}

	/**
	 * Are prices without VAT important for the given user (i.e. this basket)?
	 */
	function displayPricesWithoutVat(){
		global $ATK14_GLOBAL;

		if(!SystemParameter::ContentOn("merchant.vat_payer")){
			return false;
		}

		if(!is_null($ATK14_GLOBAL->getConfig("display_prices_without_vat"))){
			// This is mainly for development purposes!
			//
			//	File local_config/display_prices_without_vat.php may contain
			//	return true; // true or false
			return $ATK14_GLOBAL->getConfig("display_prices_without_vat");
		}

		// Here is a place for your implementation...

		return false;
	}

	/**
	 * Opposite method to displayPricesWithoutVat()
	 */
	function displayPricesInclVat(){
		return !$this->displayPricesWithoutVat();
	}

	function getDeliveryCompany(){ return $this->_getDeliveryAddress("delivery_company"); }
	function getDeliveryAddressStreet(){ return $this->_getDeliveryAddress("delivery_address_street"); }
	function getDeliveryAddressStreet2(){ return $this->_getDeliveryAddress("delivery_address_street2"); }
	function getDeliveryAddressCity(){ return $this->_getDeliveryAddress("delivery_address_city"); }
	function getDeliveryAddressState(){ return $this->_getDeliveryAddress("delivery_address_state"); }
	function getDeliveryAddressZip(){ return $this->_getDeliveryAddress("delivery_address_zip"); }
	function getDeliveryAddressCountry(){ return $this->_getDeliveryAddress("delivery_address_country"); }
	function getDeliveryAddressNote(){ return $this->_getDeliveryAddress("delivery_address_note"); }

	protected function _getDeliveryAddress($key){
		if($delivery_service_branch = $this->getDeliveryServiceBranch()){
			$delivery_address = $delivery_service_branch->getDeliveryAddressAr();
			return $delivery_address[$key];
		}
		$delivery_method = $this->getDeliveryMethod();
		$store = $delivery_method ? $delivery_method->getPersonalPickupOnStore() : null;
		if($store){
			$_key = String4::ToObject($key)->gsub('/^delivery_/','')->toString();
			if($_key === "company"){
				return $store->getName();
			}
			if($_key === "address_note"){
				return null;
			}
			return $store->g($_key);
		}
		return $this->g($key);
	}
}
