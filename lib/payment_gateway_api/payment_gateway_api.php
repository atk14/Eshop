<?php
namespace PaymentGatewayApi;

class PaymentGatewayApi {

	protected $set_new_new_transaction_to_started_state = true;

	public $logger;
	public $request;

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

	final function getCurrentPaymentStatusCode(&$payment_transaction,&$data = null,&$internal_status = null){
		$data = null;

		$this->prepareForOrder($payment_transaction->getOrder());
		myAssert($this->isProperlyConfigured(),sprintf("%s is not properly configured",get_class($this)));

		$code = $this->_getCurrentPaymentStatusCode($payment_transaction,$data,$internal_status);
		return $code;
	}

	final function updateStatus(&$payment_transaction){
		$this->prepareForOrder($payment_transaction->getOrder());
		myAssert($this->isProperlyConfigured(),sprintf("%s is not properly configured",get_class($this)));

		$code = $this->_getCurrentPaymentStatusCode($payment_transaction,$data,$internal_status);
		if(is_null($code)){
			return;
		}

		if(strlen((string)$internal_status)){
			$this->logger->info(sprintf("internal status of PaymentTransaction#%s: %s",$payment_transaction->getId(),$internal_status));
		}

		$status = \PaymentStatus::FindByCode($code);
		myAssert(is_object($status));

		$current_status = $payment_transaction->getPaymentStatus();
		if(!$current_status || $current_status->getId()!=$status->getId()){
			$order = $payment_transaction->getOrder();
			$current_order_status = $order->getOrderStatus();

			$this->logger->info(strtr(
				"set new payment status on PaymentTransaction#%id%: %current_status% -> %new_status% (Order#%order_id%, order_no=%order_no%), status data dump: %data_dump%",[
					"%id%" => $payment_transaction->getId(),
					"%current_status%" => $current_status ? $current_status->getCode() : "NULL",
					"%new_status%" => $status->getCode(),
					"%order_id%" => $order->getId(),
					"%order_no%" => $order->getOrderNo(),
					"%data_dump%" => var_export($data,true),
				]
			));

			$payment_transaction->setNewPaymentStatus($status);

			$order_status = $order->getOrderStatus();
			if($current_order_status->getId()!=$order_status->getId()){
				$this->logger->info(sprintf("order status updated on Order#%s: %s -> %s (order_no=%s) due to change status on PaymentTransaction#%s",$order->getId(),$current_order_status->getCode(),$order_status->getCode(),$order->getOrderNo(),$payment_transaction->getId()));
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
	protected function _getCurrentPaymentStatusCode(&$payment_transaction,&$data = null,&$internal_status = null){
		$class = get_class($this);
		throw new \Exception("Method $class::_getCurrentPaymentStatusCode(&\$payment_transaction,&\$data = null,&\$internal_status = null) needs to be defined");
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

	/**
	 *
	 *	if(!$uf->found()){
	 *		throw new \Exception($this->_compileUrlFetcherErrorMessage($uf));
	 *	}
	 */
	protected function _compileUrlFetcherErrorMessage($url_fetcher){
		$class = get_class($this);
		$status_code = $url_fetcher->getStatusCode();
		$method = $url_fetcher->getRequestMethod();
		$url = $url_fetcher->getUrl();
		$error_message = $url_fetcher->getErrorMessage();

		$out = [];
		$out[] = "$class:";
		if($status_code){
			$out[] = "Invalid status code ($status_code)";
		}else{
			$out[] = "error occurred";
		}
		$out[] = "on $method $url";
		if($error_message){
			$out[] = "(error_message: $error_message)";
		}
		return join(" ",$out);
	}
}
