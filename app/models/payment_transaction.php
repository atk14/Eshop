<?php
class PaymentTransaction extends ApplicationModel {

	static function CreateNewRecord($values,$options = []){
		$values += [
			"secret" => (string)String4::RandomString(8),
		];

		$order = $values["order_id"];
		$order = is_a($order,"Order") ? $order : Order::GetInstanceById($order);

		$values += [
			"price_to_pay" => $order->getPriceToPay(),
			"currency_id" => $order->getCurrency(),
		];

		return parent::CreateNewRecord($values,$options);
	}

	static function GetCurrentPaymentTransactionForOrder($order){
		$dbmole = self::GetDbmole();
		$transaction_id = $dbmole->selectInt("
			SELECT id FROM payment_transactions
			WHERE
				order_id=:order
			ORDER BY
				COALESCE(payment_status_id,0)=:status_paid DESC, -- zaplacena transakce ma absolutni prednost
				COALESCE(payment_status_id,0)=:status_cancelled ASC, -- zrusene transakce budou na konci seznamu
				created_at DESC, id DESC -- dale rozhoduje cerstvost zaznamu, novejsi je vyse
		",[
			":order" => $order,
			":status_paid" => PaymentStatus::GetInstanceByCode("paid"),
			":status_cancelled" => PaymentStatus::GetInstanceByCode("cancelled"),
		]);
		return Cache::Get("PaymentTransaction",$transaction_id);
	}

	function getPaymentTransactionUrl($options = []){
		$options += [
			"prefer_external_url" => true,
		];
		if($options["prefer_external_url"] && $this->g("payment_transaction_url")){
			return $this->g("payment_transaction_url");
		}
		return $this->getOrder()->getPaymentTransactionStartUrl();
	}

	function getToken($options = []){
		if(is_string($options)){
			$options = array("extra_salt" => $options);
		}
		$options += array(
			"salt" => $this->getSecret(),
			"extra_salt" => "",
		);
		return parent::getToken($options);
	}

	function getOrder(){
		return Cache::Get("Order",$this->getOrderId());
	}

	function getPaymentStatus(){
		return Cache::Get("PaymentStatus",$this->getPaymentStatusId());
	}

	function getCurrency(){
		return Cache::Get("Currency",$this->getCurrencyId());
	}

	function getPaymentGatewayApi(){
		$payment_gateway = $this->getPaymentGateway();
		$order = $this->getOrder();
		$api = $payment_gateway->getPaymentGatewayApi();
		$api->prepareForOrder($order);
		return $api;
	}

	/**
	 *
	 * !! Pozor !! Meni to stav objednavky
	 */
	function setNewPaymentStatus($payment_status){
		$order = $this->getOrder();
		$payment_method = $order->getPaymentMethod();
		$current_order_status = $order->getOrderStatus();
		$current_status = $this->getPaymentStatus();

		myAssert(is_null($current_status) || $current_status->getId()!=$payment_status->getId());

		$code = $payment_status->getCode(); // "pending", "paid"...
		
		$tr = [
			"pending" => "waiting_for_online_payment",
			"paid" => "payment_accepted",
			"cancelled" => "payment_failed",
		];

		if(isset($tr[$code]) && $payment_method->isOnlineMethod() && in_array($current_order_status->getCode(),['waiting_for_online_payment','payment_failed'])){
			// zmena stavu online platby meni i stav objednavky, ale pouze za urcitych okolnosti
			$order->setNewOrderStatus($tr[$code]);
			if(!is_null($order->g("updated_by_user_id"))){
				// we don't want to store logged-in user in updated_by_user_id
				$order->s([
					"updated_by_user_id" => null,
					"updated_at" => $order->g("updated_at"),
				]);
			}
			if($code === "paid"){
				// Protoze je metoda Order::increasePricePaid() multi-threaded safe,
				// je tady pojistka, ktera brani ve vicenasobnem navyseni zaplacene castky.
				$current_price_paid = (float)$order->getPricePaid();
				myAssert($current_price_paid === 0.0);
				$order->increasePricePaid($this->getPriceToPay());
				myAssert(round($order->getPricePaid(),INTERNAL_PRICE_DECIMALS)===round(($current_price_paid + $this->getPriceToPay()),INTERNAL_PRICE_DECIMALS));
			}
		}

		$this->s([
			"payment_status_id" => $payment_status,
			"payment_status_updated_at" => now(),
			"payment_status_checked_at" => now(),
		]);
	}
	
	/**
	 * Zacala jiz tato platebni transakce?
	 */
	function started(){
		return !is_null($this->g("payment_transaction_started_at"));
	}

	function isRepeatable(){
		$payment_status = $this->getPaymentStatus();
		if(is_null($payment_status)){
			// platebni transakce jeste ani nezacala
			return true;
		}
		return in_array($payment_status->getCode(),["pending","cancelled"]);
	}

	function paid(){
		$payment_status = $this->getPaymentStatus();
		return $payment_status && $payment_status->getCode()==="paid";
	}

	function pending(){
		$payment_status = $this->getPaymentStatus();
		return $payment_status && $payment_status->getCode()==="pending";
	}

	function copyIntoNewTransaction(){
		$fields = [
			"order_id",
			"payment_gateway_id",
			"testing_payment",
			"payment_transaction_url",
			"currency_id",
			"price_to_pay",
		];

		$values = [];
		foreach($fields as $f){
			$values[$f] = $this->g($f);
		}

		$values["rank"] = $this->g("rank") + 1;

		return self::CreateNewRecord($values);
	}

	/**
	 * * true -> testovaci platba
	 * * false -> produkcni platba
	 * * null -> nevi se
	 */
	function testingPayment(){
		return $this->getTestingPayment();
	}
}
