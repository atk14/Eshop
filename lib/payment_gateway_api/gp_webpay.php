<?php
namespace PaymentGatewayApi;

definedef("GP_WEBPAY_MERCHANT_NUMBER",""); // "124567890"
definedef("GP_WEBPAY_PROVIDER_CODE",""); // "0100", "0880"
definedef("GP_WEBPAY_PAYMENT_METHOD","CRD"); // "CRD" (card), "APM-BTR"...
definedef("GP_WEBPAY_TESTING",true);
definedef("GP_WEBPAY_PRIVATE_KEY",__DIR__ . "/../../config/gpwebpay-pvk.key"); // It can contain %merchant_number%", e.g. /../../config/gpwebpay-pvk.%merchant_number%.key"
definedef("GP_WEBPAY_PRIVATE_KEY_PASSWORD","secret");

//definedef("GP_WEBPAY_WS_URL","https://test.3dsecure.gpwebpay.com/pay-ws/v1/PaymentService"); // testing: https://test.3dsecure.gpwebpay.com/pay-ws/v1/PaymentService; production: https://3dsecure.gpwebpay.com/pay-ws/v1/PaymentService

class GpWebpay extends PaymentGatewayApi {

	protected $GP_WEBPAY_MERCHANT_NUMBER;
	protected $GP_WEBPAY_PROVIDER_CODE;
	protected $GP_WEBPAY_PAYMENT_METHOD;
	protected $GP_WEBPAY_TESTING;
	protected $GP_WEBPAY_PRIVATE_KEY;
	protected $GP_WEBPAY_PRIVATE_KEY_PASSWORD;

	function __construct($options = []){
		$this->GP_WEBPAY_MERCHANT_NUMBER = GP_WEBPAY_MERCHANT_NUMBER;
		$this->GP_WEBPAY_PROVIDER_CODE = GP_WEBPAY_PROVIDER_CODE;
		$this->GP_WEBPAY_PAYMENT_METHOD = GP_WEBPAY_PAYMENT_METHOD;
		$this->GP_WEBPAY_TESTING = GP_WEBPAY_TESTING;
		$this->GP_WEBPAY_PRIVATE_KEY = GP_WEBPAY_PRIVATE_KEY;
		$this->GP_WEBPAY_PRIVATE_KEY_PASSWORD = GP_WEBPAY_PRIVATE_KEY_PASSWORD;

		parent::__construct($options);
	}

	function prepareForOrder($order){
		$payment_method = $order->getPaymentMethod();
		
		$config = $payment_method->getPaymentGatewayConfig();
		foreach([
			"merchant_number" => "GP_WEBPAY_MERCHANT_NUMBER",
			"provider_code" => "GP_WEBPAY_PROVIDER_CODE",
			"payment_method" => "GP_WEBPAY_PAYMENT_METHOD",
			"testing" => "GP_WEBPAY_TESTING"
		] as $k => $v){
			if(array_key_exists($k,$config)){ $this->$v = $config[$k]; }
		}
	}

	function isProperlyConfigured(){
		foreach(["GP_WEBPAY_MERCHANT_NUMBER","GP_WEBPAY_PROVIDER_CODE","GP_WEBPAY_PRIVATE_KEY","GP_WEBPAY_PRIVATE_KEY_PASSWORD"] as $c_name){
			if(!strlen($this->$c_name)){ return false; }
		}
		return true;
	}

	function testingApi(){
		return $this->GP_WEBPAY_TESTING;
	}

	function _getStartTransactionUrl(&$payment_transaction,&$transaction_id){
		$order = $payment_transaction->getOrder();
		$currency = $payment_transaction->getCurrency();

		$api = $this->_getApi();

		$currency_tr = [
			"CZK" => \AdamStipak\Webpay\PaymentRequest::CZK,
			"EUR" => \AdamStipak\Webpay\PaymentRequest::EUR,
		];
		myAssert(isset($currency_tr["$currency"]),"currency code $currency must be known");

		$paymentNumber = ($payment_transaction->getId() * 100) + rand(0,99); // This reduces the likelihood of generating the same payment number from two or more installations of the application.

		$addInfo = null;

		//*
		$cardholderDetails = [];

		// minLength: 2, maxLength: 45, Text; max. 45 characters ASCII x20-x7E, /^[!-~]+[ -~]*$/
		$name = \String4::ToObject($order->getFirstname()." ".$order->getLastname())->trim()->toAscii()->substr(0,45)->trim()->toString();
		if(strlen($name)>=2 && preg_match('/^[!-~]+[ -~]*$/',$name)){
			$cardholderDetails["name"] = $name;
		}

		// minLength: 6, maxLength: 255, pattern: ([0-9a-zA-Z]([-_\+.\w]*[0-9a-zA-Z_-])*@([0-9a-zA-Z]*[-_\+\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,20})
		$email = (string)$order->getEmail();
		if(strlen($email)>=6 && strlen($email)<=255 && preg_match('/^([0-9a-zA-Z]([-_\+.\w]*[0-9a-zA-Z_-])*@([0-9a-zA-Z]*[-_\+\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,20})$/',$email)){
			$cardholderDetails["email"] = $email;
		}

		$minimalValues = \AdamStipak\Webpay\PaymentRequest\AddInfo::createMinimalValues();
		$values =
			$minimalValues +
			[
				"cardholderInfo" => [
					"cardholderDetails" => $cardholderDetails,
				],
			];
		$addInfo = new \AdamStipak\Webpay\PaymentRequest\AddInfo(file_get_contents(__DIR__ . "/gp_webpay/GPwebpayAdditionalInfoRequest_v.5.xsd"), $values);

		//echo "<pre>", h($addInfo->toXml()); exit;
		// < ?xml version="1.0"? >
		// <additionalInfoRequest xmlns="http://gpe.cz/gpwebpay/additionalInfo/request" version="5.0"><cardholderInfo><cardholderDetails><name>John Doe</name><email>john@doe.cz</email></cardholderDetails></cardholderInfo></additionalInfoRequest>

		// */

		$request = new \AdamStipak\Webpay\PaymentRequest(
			$paymentNumber, // $orderNumber: Číslo platby. Číslo musí být v každém požadavku od obchodníka unikátní.
			$payment_transaction->getPriceToPay(), // $amount
			$currency_tr["$currency"], // $currency
			1, // $depositFlag: 0 = není požadována okamžitá úhrada; 1 = je požadována úhrada
			\Atk14Url::BuildLink(["namespace" => "", "action" => "gp_webpay/finish_transaction", "token" => $payment_transaction->getToken()],["with_hostname" => true]), // $url
			$order->getOrderNo(), // $merOrderNumber: Číslo platby. Zobrazí se na výpisu z banky. V případě, že není zadáno, použije se hodnota ORDERNUMBER.

			null, // $md
			$addInfo,

			$this->GP_WEBPAY_PAYMENT_METHOD
		);

		$transaction_id = (string)$paymentNumber;

		return $api->createPaymentRequestUrl($request);
	}

	// Neuspesny pokus
	//	function _updateStatus(&$payment_transaction){
	//		$order = $payment_transaction->getOrder();
	//		$signer = $this->_getSigner();
	//
	//		$api = $this->_getApi();
	//
	//		$request = $GLOBALS["HTTP_REQUEST"];
	//
	//		var_dump($request->getGetVar("OPERATION"));
	//		var_dump($request->getGetVar("ORDERNUMBER"));
	//		var_dump((string)$request->getGetVar("MERORDERNUM"));
	//		var_dump($request->getGetVar("PRCODE"));
	//		var_dump($request->getGetVar("SRCODE"));
	//		var_dump($request->getGetVar("RESULTTEXT"));
	//		var_dump($request->getGetVar("DIGEST"));
	//		var_dump($request->getGetVar("DIGEST1"));
	//
	//		$response = new \AdamStipak\Webpay\PaymentResponse(
	//			$request->getGetVar("OPERATION"),
	//			$request->getGetVar("ORDERNUMBER"),
	//			(string)$request->getGetVar("MERORDERNUM"),
	//			$request->getGetVar("PRCODE"),
	//			$request->getGetVar("SRCODE"),
	//			$request->getGetVar("RESULTTEXT"),
	//			$request->getGetVar("DIGEST"),
	//			$request->getGetVar("DIGEST1")
	//		);
	//
	//		$result = $api->verifyPaymentResponse($response);
	//		var_dump($result);
	//
	//		exit;
	//	}

	protected function _getCurrentPaymentStatusCode(&$payment_transaction,&$data = null,&$internal_status = null){
		$internal_status = null;
		$data = $this->_getStatus($payment_transaction);
		if(is_null($data)){
			return;
		}

		$status = $data["status"];
		myAssert(strlen((string)$status)>0);

		$substatus = $data["subStatus"];
		myAssert(strlen((string)$substatus)>0);

		$internal_status = "$status/$substatus";

		$tr = [
			"PENDING_AUTHORIZATION" => "pending", 	// Nově založená objednávka příchozí libovolným kanálem, kterou je stále možné úspěšně dokončit. U PUSH plateb je to do konce platnosti nebo vyčerpání pokusů, u ostatních plateb do konce platnosti session.

			"UNPAID" => "pending", 									// Všechno s UNPAID stavem považujeme za pending, pokud to nemá nějaký jasný substatus
			"UNPAID/CANCELED" => "cancelled",				// Toto je bezpecne cancelled
			"UNPAID/DECLINED" => "cancelled",				// Toto je taky bezpecne cancelled
			"UNPAID/TECHNICAL_PROBLEM" => "pending", // Technická chyba znemožňující dokončení platby - pockame, jestli to nekdo neopravi
			"UNPAID/FRAUD" => "pending",						// Potenciální podvod - pockame, jak se to vyresi
			// Nasl. stavy nastavaji, ale nejsou v dokumentaci...
			"UNPAID/PGW_PAGE" => "pending",
			"UNPAID/3DS_REDIRECT" => "pending",
			"UNPAID/3DS_SUBMIT" => "pending",

			"PENDING_CAPTURE" => "pending", 				// Autorizovaná/schválená platba, došlo úspěšně k blokaci finančních prostředků na účtu nakupujícího. Nebyl dosud vytvořen žádný požadavek na stržení blokované částky (capture).
			"REVERSED" => "cancelled", 							// Stornovaná platba – buď manuálně (přes GUI nebo WS) obchodníkem nebo systémem při vypršení doby pro provedení stržení částky (capture) ze stavu PENDING_CAPTURE.
			"CAPTURED" => "paid",										// K objednávce existuje plný deposit bez ohledu na to, zda byl již zpracován nebo na to čeká a lze tudíž ještě zrušit.
			"PENDING_ADJUSTMENT" => "pending",			// Pro použití v oblasti AFD (Automatic fuel dispenser) se bude po úvodní autorizaci čekat na určení přesné částky, na kterou se má ponížit blokace prostředků.
			"PARTIAL_PAYMENT" => "pending",					// Částečně uhrazená platba – nebyla stržena celá blokovaná částka nebo byly částečně vráceny peníze zpět držiteli platební karty.
			"REFUNDED" => "cancelled",							// Kompletně vrácená platba – veškeré stržené peníze byly vráceny držiteli platební karty, bez ohledu, zda byla stržena celková blokovaná částka.
		];
		myAssert(isset($tr[$status]) || isset($tr["$status/$substatus"]),"unknown status/substatus: $status/$substatus");

		if(isset($tr["$status/$substatus"])){
			$code = $tr["$status/$substatus"];
		}else{
			$code = $tr[$status];
		}

		// !!! Toto zlobi !!!
		// Casovy test - prilis stara transakce ve stavu pending je oznacena za cancelled
		//if($code==="pending" && $status==="UNPAID" && $payment_transaction->getPaymentTransactionStartedAt() && strtotime($payment_transaction->getPaymentTransactionStartedAt())<(time() - 23 * 60 * 60)){
		//	$code = "cancelled";
		//}

		return $code;
	}

	function _getStatus($payment_transaction){
		$signer = $this->_getSigner();

		$paymentNumber = $payment_transaction->getPaymentTransactionId() ? $payment_transaction->getPaymentTransactionId() : $payment_transaction->getId();

		$params = [
			"messageId" => $payment_transaction->getId()."x".uniqid().uniqid(),
			"provider" => $this->GP_WEBPAY_PROVIDER_CODE,
			"merchantNumber" => $this->GP_WEBPAY_MERCHANT_NUMBER,
			"paymentNumber" => $paymentNumber,
		];
		$params["signature"] = $signer->sign($params);

		$xml = [];
		$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml[] = '<soapenv:Envelope xmlns:v1="http://gpe.cz/pay/pay-ws/proc/v1" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:type="http://gpe.cz/pay/pay-ws/proc/v1/type">';
		$xml[] = '<soapenv:Header/>';
		$xml[] = '<soapenv:Body>';

		$xml[] = '<v1:getPaymentDetail>';
		$xml[] = '<v1:paymentDetailRequest>';

		foreach($params as $k => $v){
			$xml[] = "<type:$k>".\XMole::ToXml($v)."</type:$k>";
		}

		$xml[] = '</v1:paymentDetailRequest>';
		$xml[] = '</v1:getPaymentDetail>';

		$xml[] = '</soapenv:Body>';
		$xml[] = '</soapenv:Envelope>';
			
		$xml = join("\n",$xml);
		$url = $this->_getGpWebpayWsUrl();

		$uf = new \UrlFetcher($url);
		$uf->post($xml,["content_type" => "text/xml"]);

		$content = $uf->getContent();
		$status_code = $uf->getStatusCode();

		if($status_code==500){
			$this->logger->warn("Status code is 500, skipping the check (content: $content)");
			return;
		}

		if(!$uf->found()){
			throw new \Exception($this->_compileUrlFetcherErrorMessage($uf));
		}
		$xmole = new \XMole();
		$content_cleaned = $this->_cleanXml($content);
		$stat = $xmole->parse($content_cleaned);
		myAssert($stat,"Failed to parse XML (".$xmole->get_error_message()."): $content, cleaned XML: $content_cleaned");

		/*
		 *	<?xml version="1.0" encoding="UTF-8"?>
		 *	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
		 *		<soapenv:Body>
		 *			<ns4:getPaymentDetailResponse xmlns:ns4="http://gpe.cz/pay/pay-ws/proc/v1" xmlns="http://gpe.cz/gpwebpay/additionalInfo/response" xmlns:ns5="http://gpe.cz/gpwebpay/additionalInfo/response/v1" xmlns:ns2="http://gpe.cz/pay/pay-ws/core/type" xmlns:ns3="http://gpe.cz/pay/pay-ws/proc/v1/type">
		 *				<ns4:paymentDetailResponse>
		 *					<ns3:messageId>203723x64511678143906451167814391</ns3:messageId>
		 *					<ns3:state>1</ns3:state>
		 *					<ns3:status>PENDING_AUTHORIZATION</ns3:status>
		 *					<ns3:subStatus>PGW_PAGE</ns3:subStatus>
		 *					<ns3:paymentMethod>PGW</ns3:paymentMethod>
		 *					<ns3:paymentAmount>1178</ns3:paymentAmount>
		 *					<ns3:approveAmount>0</ns3:approveAmount>
		 *					<ns3:captureAmount>0</ns3:captureAmount>
		 *					<ns3:refundAmount>0</ns3:refundAmount>
		 *					<ns3:paymentTime>2023-05-02 15:55:22</ns3:paymentTime>
		 *					<ns3:signature>rF92F7E6TPOnQ/2MSTFDtXxtnE9CfINh8Z13LUHoUBjyRQPKQkWNh9+F0+hefETRzh/IX+kmU2SjVnljXFX5WAsg8ayEhrtI3/3GmvYL7qHWnlqiyzJUrTBzUBJRPBgyXqGFOW5qXuYgPF0VvkXbeH1AlkuWLg/XrMqfYsYoeIHkS283zTlXqsZrHKYheUipi61VHsreoI0cOF6eHa/cIpa+5M1vJ4D4whqrqPL/t90+oLHZgfuBqbQgao81ugzFFDhzpT7/CKgxJYUUmIF1t/vo33E+SBe8GowVCOahpRqLonbOF7o5ylrGl3JoNd1arOm4EaV0Cd56JOPZPYiQlI==</ns3:signature>
		 *				</ns4:paymentDetailResponse>
		 *			</ns4:getPaymentDetailResponse>
		 *		</soapenv:Body>
		 *	</soapenv:Envelope>
		 */


		$branch = $xmole->get_first_matching_branch('/soapenv:Envelope/soapenv:Body/getPaymentDetailResponse/paymentDetailResponse');
		myAssert($branch,"unexpected XML: $content_cleaned");

		$status_ar = [];
		foreach($branch["children"] as $v){
			$key = $v["element"];
			$value = $v["data"];

			$key = preg_replace('/^ns\d+:/','',$key);
			$status_ar[$key] = $value;
		}
		myAssert($status_ar);

		myAssert(isset($status_ar["paymentAmount"]),"paymentAmount must be found in the payment detail response");
		$expected_payment_amount = $payment_transaction->getPriceToPay();
		$received_payment_amount = round($status_ar["paymentAmount"] / 100.0,INTERNAL_PRICE_DECIMALS);
		if($expected_payment_amount!==$received_payment_amount){
			$this->logger->warn("PaymentGatewayApi\\GpWebpay: Expectation failed: expected_payment_amount===received_payment_amount ($expected_payment_amount===$received_payment_amount)");
			return;
		}

		return $status_ar;
	}

	function _cleanXml($xml){
		return preg_replace('/(<\/?)ns\d+:/','\1',$xml);
	}

	protected function _getSigner(){
		$private_key_filename = $this->GP_WEBPAY_PRIVATE_KEY;
		$private_key_filename = str_replace("%merchant_number%",$this->GP_WEBPAY_MERCHANT_NUMBER,$private_key_filename);
		$signer = new \AdamStipak\Webpay\Signer(
			$private_key_filename,										// Path of private key.
			$this->GP_WEBPAY_PRIVATE_KEY_PASSWORD,		// Password for private key.
			$private_key_filename											// Path of public key. Wtf? Ale ja public key nemam! :)
		);
		return $signer;
	}

	protected function _getApi(){
		$signer = $this->_getSigner();
		$api = new \AdamStipak\Webpay\Api(
			$this->GP_WEBPAY_MERCHANT_NUMBER,		// Merchant number.
			$this->_getGpWebpayUrl(),						// URL of webpay.
			$signer												// instance of \AdamStipak\Webpay\Signer.
		);
		return $api;
	}

	function _getGpWebpayUrl(){
		// previously it was constant GP_WEBPAY_URL
		return $this->testingApi() ? "https://test.3dsecure.gpwebpay.com/pgw/order.do" : "https://3dsecure.gpwebpay.com/pgw/order.do";
	}

	function _getGpWebpayWsUrl(){
		// previously it was constant GP_WEBPAY_WS_URL
		return $this->testingApi() ? "https://test.3dsecure.gpwebpay.com/pay-ws/v1/PaymentService" : "https://3dsecure.gpwebpay.com/pay-ws/v1/PaymentService";
	}
}
