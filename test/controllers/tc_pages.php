<?php
/**
 *
 * @fixture pages
 */
class TcPages extends TcBase {

	function test_visible(){
		$page = $this->pages["testing_subpage"];

		$this->client->get("pages/detail", array("id" => $page));
		$this->assertEquals("200",$this->client->getStatusCode());
		$this->_assertOGProperties(["url" => sprintf("http://%s/testing-page/testing-subpage/", ATK14_HTTP_HOST)]);

		$page->s("visible",false);

		$this->client->get("pages/detail", array("id" => $page));
		$this->assertEquals("404",$this->client->getStatusCode());
	}

	function test_indexable(){
		$page = $this->pages["testing_subpage"];

		$this->client->get("pages/detail", array("id" => $page));
		$this->assertEquals("200",$this->client->getStatusCode());
		$this->assertStringNotContains('<meta name="robots" content="noindex,nofollow,noarchive">',$this->client->getContent());
		$this->_assertOGProperties(["url" => sprintf("http://%s/testing-page/testing-subpage/", ATK14_HTTP_HOST)]);

		$page->s("indexable",false);

		$this->client->get("pages/detail", array("id" => $page));
		$this->assertEquals("200",$this->client->getStatusCode());
		$this->assertStringContains('<meta name="robots" content="noindex,noarchive">',$this->client->getContent());
	}

	protected function _assertOGProperties($options=[]) {
		$options += [
			"url" => "",
		];
		$url = $options["url"];
		$this->assertStringContains('<meta property="og:description">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:title" content="Testing Subpage">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:type" content="article">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:url" content="'.$url.'">', $this->client->getContent());
	}
}
