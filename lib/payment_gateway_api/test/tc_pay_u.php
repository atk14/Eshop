<?php
class TcPayU extends TcBase {

	function test_PaymentStatus(){
		$xml = trim('
			<?xml version="1.0" encoding="UTF-8"?>
			<response>
				<status>OK</status>
				<trans>
					<id>2174878146</id>
					<pos_id>4116988</pos_id>
					<session_id>39</session_id>
					<order_id>12205</order_id>
					<amount>33900</amount>
					<status>2</status>
					<pay_type>cb</pay_type>
					<pay_gw_name>csob</pay_gw_name>
					<desc>Objednávka v e-shopu ATK14 Eshop</desc>
					<desc2>Objednávka v e-shopu ATK14 Eshop</desc2>
					<create>2021-06-06 23:39:52</create>
					<init>2021-06-07 00:12:00</init>
					<sent></sent>
					<recv></recv>
					<cancel>2021-06-07 00:12:00</cancel>
					<auth_fraud>0</auth_fraud>
					<ts>1625149489487</ts>
					<sig>70f65d58d72e3214b6cec1e09fe6ed8b</sig>
				</trans>
			</response>
		');
		$ps = PaymentGatewayApi\PayU\PaymentStatus::GetInstance($xml,$err_code,$err_message);
		$this->assertNotNull($ps);
		$this->assertNull($err_code);
		$this->assertNull($err_message);
		$this->assertEquals("2",$ps->getStatus());
		$this->assertEquals("cancelled",$ps->getStatusDescription());
		$this->assertEquals(33900.0,$ps->getAmount());

		$xml = trim('
			<?xml version="1.0" encoding="UTF-8"?>
			<response>
				<status>ERROR</status>
				<error>
					<nr>500</nr>
					<message>Kod błędu: 500</message>
				</error>
			</response>
		');
		$ps = PaymentGatewayApi\PayU\PaymentStatus::GetInstance($xml,$err_code,$err_message);
		$this->assertNull($ps);
		$this->assertEquals("500",$err_code);
		$this->assertEquals("Error code: 500",$err_message);
	}
}
