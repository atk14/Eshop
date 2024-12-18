<?php
/**
 *
 * @fixture articles
 */
class TcArticles extends TcBase {

	function test(){
		$this->client->get("articles/detail",array("id" => $this->articles["testing_article"]->getId()));
		$this->assertStringContains(">Testing Article</h1>",$this->client->getContent());
		$this->assertStringContains("<title>Testing Article",$this->client->getContent());
		$this->assertStringContains('<meta name="description" content="Testing teaser">',$this->client->getContent());

		$this->client->get("articles/detail",array("id" => $this->articles["interesting_article"]->getId()));
		$this->assertStringContains(">Interesting Article</h1>",$this->client->getContent());
		$this->assertStringContains("<title>Page title",$this->client->getContent());
		$this->assertStringContains('<meta name="description" content="Page description">',$this->client->getContent());

		$this->_assertOGProperties();
	}

	protected function _assertOGProperties() {
		$this->assertStringContains('<meta property="og:description" content="Interesting teaser">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:title" content="Interesting Article">', $this->client->getContent());
		$this->assertStringContains('<meta property="og:type" content="article">', $this->client->getContent());
		$this->assertStringContains(sprintf('<meta property="og:url" content="http://%s/articles/interesting-article/">', ATK14_HTTP_HOST), $this->client->getContent());
	}
}
