<?php
require_once(__DIR__ . "/payment_gateways_base.php");

class GoPayController extends PaymentGatewaysBaseController {

	function finish_transaction(){
		$this->_finish_transaction([
			"parameter_name" => "id"
		]);
	}

	function update_status(){
		$gateway_code = "go_pay";
		$parameter_name = "id";

		$this->render_template = false;

		$gateway = PaymentGateway::GetInstanceByCode($gateway_code);

		$pt = null;
		if($this->params->defined($parameter_name) && strlen($this->params->getString($parameter_name))){
			$transaction_id = $this->params->getString($parameter_name);
			$pt = PaymentTransaction::FindFirst("payment_transaction_id",$transaction_id,"payment_gateway_id",$gateway,["order_by" => "created_at DESC, id DESC"]);

			// HACK pro GoPay
			// Nekdy nam GoPay notifikuje transakci, kterou nemame v db, ale pritom je to skutecna transakce, kterou napr. zakaznik uz zaplatil.
			if(!$pt){
				$pt = $this->_try_to_create_missing_payment_transaction($transaction_id,$gateway);
			}
		}

		if(!$pt || $pt->getPaymentGateway()->getCode()!==$gateway_code){
			$this->response->setStatusCode(404);
			return;
		}

		$gateway_api = $pt->getPaymentGatewayApi();
		$gateway_api->updateStatus($pt);
	}

	function _try_to_create_missing_payment_transaction($transaction_id,$gateway){
		$gateway_api = $gateway->getPaymentGatewayApi();
		$api = $gateway_api->_getApi();
		$data = $api->get("payments/payment/$transaction_id",[],[
			"acceptable_error_codes" => [404], // error_name: PAYMENT_NOT_FOUND, message: Payment not found
		]);
		if(is_array($data)){
			$this->logger->info("$this->controller/$this->action was notified by a payment-transaction-not-existing-in-database $transaction_id:\n".print_r($data,true));
			if(
				(string)$data["id"]===$transaction_id &&
				($order = Order::FindFirst("order_no",$data["order_number"])) &&
				($order->getCurrency()->getCode()===$data["currency"])
			){
				$most_recent_pt = PaymentTransaction::FindFirst("order_id",$order,["order_by" => "rank DESC"]);
				$testing_payment = $gateway_api->testingApi();

				$this->logger->info("order {$order->getOrderNo()} was found for payment-transaction-not-existing-in-database $transaction_id");

				// sanitizaton
				if($testing_payment){
					myAssert(preg_match('/gw\.sandbox\.gopay/',$data["gw_url"]),"expecting testing URL in ".print_r($data,true));
				}else{
					myAssert(preg_match('/gate\.gopay\.com/',$data["gw_url"]),"expecting production URL in ".print_r($data,true));
				}
				
				$pt = PaymentTransaction::CreateNewRecord([
					"order_id" => $order,
					"payment_gateway_id" => $gateway,
					"testing_payment" => $testing_payment,
					"payment_transaction_id" => (string)$data["id"],
					"payment_transaction_url" => (string)$data["gw_url"],
					"payment_transaction_started_at" => now(),
					"payment_transaction_started_from_addr" => $this->request->getRemoteAddr(),
					"price_to_pay" => $data["amount"] / 100.0,
					"rank" => $most_recent_pt ? ($most_recent_pt->getRank() + 1) : 1,
				]);

				$this->logger->info("PaymentTransaction#{$pt->getId()} was created for payment-transaction-not-existing-in-database $transaction_id");
			}else{
				$this->logger->info("no order was found for payment-transaction-not-existing-in-database $transaction_id");
			}
		}
	}
}
