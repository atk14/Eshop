<?php
/**
 *
 * @fixture cards
 * @fixture products
 */
class TcCards extends TcBase {

	function test(){
		$client = $this->client;
	
		// Normal product
		$card = $this->cards["coffee"];
		$client->get("cards/detail",["id" => $card]);
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertStringContains($card->getName(),$client->getContent());
		$this->assertStringNotContains("We apologize, but the sale is already over.",$client->getContent());

		$this->_assertOGProperties();

		// Invisible product
		$card->s("visible",false);
		$client->get("cards/detail",["id" => $card]);
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertStringContains($card->getName(),$client->getContent());
		$this->assertStringContains("We apologize, but the sale is already over.",$client->getContent());

		// Deleted product
		$card->s("deleted",true);
		$client->get("cards/detail",["id" => $card]);
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertStringContains($card->getName(),$client->getContent());
		$this->assertStringContains("We apologize, but the sale is already over.",$client->getContent());

		// Special system product
		$product = Product::FindByCode("price_rounding");
		$card = $product->getCard();
		$client->get("cards/detail",["id" => $card]);
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertStringNotContains($card->getName(),$client->getContent());
		$this->assertStringNotContains("We apologize, but the sale is already over.",$client->getContent());

	}

	protected function _assertOGProperties() {
		$this->assertStringContains('<meta property="og:description">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:title" content="Coffee">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:type" content="article">', $this->client->getContent());
		$this->assertStringContains(sprintf('<meta property="og:url" content="http://%s/drink/coffee/">', ATK14_HTTP_HOST), $this->client->getContent());
	}
}
