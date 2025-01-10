<?php
class TcGpWebpay extends TcBase {

	function test__cleanXml(){
		$gp_webpay = new PaymentGatewayApi\GpWebpay();
		$xml = trim('
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Body>
		<ns4:getPaymentDetailResponse xmlns:ns4="http://gpe.cz/pay/pay-ws/proc/v1" xmlns="http://gpe.cz/gpwebpay/additionalInfo/response" xmlns:ns5="http://gpe.cz/gpwebpay/additionalInfo/response/v1" xmlns:ns2="http://gpe.cz/pay/pay-ws/core/type" xmlns:ns3="http://gpe.cz/pay/pay-ws/proc/v1/type">
			<ns4:paymentDetailResponse>
				<ns3:messageId>203723x64511678143906451167814391</ns3:messageId>
				<ns3:state>1</ns3:state>
				<ns3:status>PENDING_AUTHORIZATION</ns3:status>
				<ns3:subStatus>PGW_PAGE</ns3:subStatus>
				<ns3:paymentMethod>PGW</ns3:paymentMethod>
				<ns3:paymentAmount>1178</ns3:paymentAmount>
				<ns3:approveAmount>0</ns3:approveAmount>
				<ns3:captureAmount>0</ns3:captureAmount>
				<ns3:refundAmount>0</ns3:refundAmount>
				<ns3:paymentTime>2023-05-02 15:55:22</ns3:paymentTime>
				<ns3:signature>rF92F7E6TPOnQ/2MSTFDtXxtnE9CfINh8Z13LUHoUBjyRQPKQkWNh9+F0+hefETRzh/IX+kmUHkS283zTlXqsZrHKYheUipi61VHsreoI0cOF6eHa/cIpa+5M1vJ4D4whqrqPL/t90+oLHZgfuBqbQgao81ugzFFDhzpT7/CKgxJYUUmIF1t/vo33E+SBe8GowVCOahpRqLonbOF7o5ylrGl3JoNd1arOm4EaV0Cd56JOPZPYiQlI==</ns3:signature>
			</ns4:paymentDetailResponse>
		</ns4:getPaymentDetailResponse>
	</soapenv:Body>
</soapenv:Envelope>
		');

		$cleaned_xml = $gp_webpay->_cleanXml($xml);
		
		$this->assertEquals(trim('
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Body>
		<getPaymentDetailResponse xmlns:ns4="http://gpe.cz/pay/pay-ws/proc/v1" xmlns="http://gpe.cz/gpwebpay/additionalInfo/response" xmlns:ns5="http://gpe.cz/gpwebpay/additionalInfo/response/v1" xmlns:ns2="http://gpe.cz/pay/pay-ws/core/type" xmlns:ns3="http://gpe.cz/pay/pay-ws/proc/v1/type">
			<paymentDetailResponse>
				<messageId>203723x64511678143906451167814391</messageId>
				<state>1</state>
				<status>PENDING_AUTHORIZATION</status>
				<subStatus>PGW_PAGE</subStatus>
				<paymentMethod>PGW</paymentMethod>
				<paymentAmount>1178</paymentAmount>
				<approveAmount>0</approveAmount>
				<captureAmount>0</captureAmount>
				<refundAmount>0</refundAmount>
				<paymentTime>2023-05-02 15:55:22</paymentTime>
				<signature>rF92F7E6TPOnQ/2MSTFDtXxtnE9CfINh8Z13LUHoUBjyRQPKQkWNh9+F0+hefETRzh/IX+kmUHkS283zTlXqsZrHKYheUipi61VHsreoI0cOF6eHa/cIpa+5M1vJ4D4whqrqPL/t90+oLHZgfuBqbQgao81ugzFFDhzpT7/CKgxJYUUmIF1t/vo33E+SBe8GowVCOahpRqLonbOF7o5ylrGl3JoNd1arOm4EaV0Cd56JOPZPYiQlI==</signature>
			</paymentDetailResponse>
		</getPaymentDetailResponse>
	</soapenv:Body>
</soapenv:Envelope>
		'),$cleaned_xml);

	}
}
