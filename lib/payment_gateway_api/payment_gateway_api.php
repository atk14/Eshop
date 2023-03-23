<?php
namespace PaymentGatewayApi;

class PaymentGatewayApi {

	protected $set_new_new_transaction_to_started_state = true;

	function isProperlyConfigured(){
		$class = get_called_class();
		throw new \Exception("Method $class::isProperlyConfigured() needs to be defined");
	}

	function __construct($options = []){
		global $ATK14_GLOBAL, $HTTP_REQUEST;

		$options += [
			"logger" => $ATK14_GLOBAL->getLogger(),
			"request" => $HTTP_REQUEST,
		];

		$this->logger = $options["logger"];
		$this->request = $options["request"];
	}

	function prepareForOrder($order){

	}

	function testingApi(){
		$class = get_class($this);
		throw new \Exception("Method $class::testingApi() needs to be defined");
	}

	final function startTransaction(&$payment_transaction){
		myAssert(!$payment_transaction->started());

		$this->prepareForOrder($payment_transaction->getOrder());
		myAssert($this->isProperlyConfigured(),sprintf("%s is not properly configured",get_class($this)));

		$transaction_id = null;
		$url = $this->_getStartTransactionUrl($payment_transaction,$transaction_id);
		myAssert(strlen($url)>0);
		myAssert(is_bool($this->testingApi()));

		$values = [
			"payment_transaction_url" => $url,
			"payment_transaction_id" => $transaction_id,
			"testing_payment" => $this->testingApi(),
		];

		if($this->set_new_new_transaction_to_started_state){
			$values["payment_transaction_started_at"] = now();
			$values["payment_transaction_started_from_addr"] = $this->request->getRemoteAddr();
		}

		$payment_transaction->s($values);
	}

	final function getCurrentPaymentStatusCode(&$payment_transaction,&$data = null){
		$data = null;

		$this->prepareForOrder($payment_transaction->getOrder());
		myAssert($this->isProperlyConfigured(),sprintf("%s is not properly configured",get_class($this)));

		$code = $this->_getCurrentPaymentStatusCode($payment_transaction,$data);
		return $code;
	}

	final function updateStatus(&$payment_transaction){
		$this->prepareForOrder($payment_transaction->getOrder());
		myAssert($this->isProperlyConfigured(),sprintf("%s is not properly configured",get_class($this)));

		$code = $this->_getCurrentPaymentStatusCode($payment_transaction);
		if(is_null($code)){
			return;
		}

		$status = \PaymentStatus::FindByCode($code);
		myAssert(is_object($status));

		$current_status = $payment_transaction->getPaymentStatus();
		if(!$current_status || $current_status->getId()!=$status->getId()){
			$order = $payment_transaction->getOrder();
			$current_order_status = $order->getOrderStatus();

			$this->logger->info(sprintf("order_no %s, payment_transaction_id %s: payment status updated: %s -> %s",$order->getOrderNo(),$payment_transaction->getId(),$current_status ? $current_status->getCode() : "NULL",$status->getCode()));
			$payment_transaction->setNewPaymentStatus($status);

			$order_status = $order->getOrderStatus();
			if($current_order_status->getId()!=$order_status->getId()){
				$this->logger->info(sprintf("order_no %s, order status updated: %s -> %s",$order->getOrderNo(),$current_order_status->getCode(),$order_status->getCode()));
			}

		}else{
			// nic se nemeni -> pouze se zauktualizuje payment_status_checked_at
			$payment_transaction->s([
				"payment_status_checked_at" => now(),
				"updated_at" => $payment_transaction->g("updated_at"),
			]);
		}
	}

	/**
	 *
	 *	$code = $this->_getCurrentPaymentStatusCode($payment_transaction); // "pending", "paid", "cancelled", null
	 */
	protected function _getCurrentPaymentStatusCode(&$payment_transaction,&$data = null){
		$class = get_class($this);
		throw new \Exception("Method $class::_getCurrentPaymentStatusCode(&\$payment_transaction,&\$data = null) needs to be defined");
	}

	/**
	 *
	 *	$url = $this->_getStartTransactionUrl($payment_transaction,$transaction_id);
	 *	echo $url; // "https://..."	
	 *	echo $transaction_id; // e.g. "A2HW-1WPE-XMG5"
	 */
	protected function _getStartTransactionUrl(&$payment_transaction,&$transaction_id){
		$class = get_class($this);
		throw new \Exception("Method $class::_getStartTransactionUrl(&\$payment_transaction,&\$transaction_id) needs to be defined");
	}
}
