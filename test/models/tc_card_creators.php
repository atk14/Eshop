<?php
/**
 *
 * @fixture cards
 * @fixture card_creators
 */
class TcCardCreators extends TcBase {

	function test(){
		$book = $this->cards["book"];

		//

		$creators = CardCreator::GetCreatorsForCard($book);
		$this->assertEquals(2,sizeof($creators));
		$this->assertEquals("John Doe",(string)$creators[0]);
		$this->assertEquals("Author",(string)$creators[0]->getCreatorRole());
		$this->assertEquals("Samantha Doe",(string)$creators[1]);
		$this->assertEquals("Illustration",(string)$creators[1]->getCreatorRole());

		//

		$creators = CardCreator::GetMainCreatorsForCard($book);
		$this->assertEquals(1,sizeof($creators));
		$this->assertEquals("John Doe",(string)$creators[0]);
		$this->assertEquals("Author",(string)$creators[0]->getCreatorRole());
	}
}
