<?php
class TcRemoveIfContainsNoText extends TcBase {

	function test(){
		Atk14Require::Helper("block.remove_if_contains_no_text");

		$repeat = false;
		$params = [];
		$template = null;

		$content = "<p>Hello World!</p>";
		$this->assertEquals($content,smarty_block_remove_if_contains_no_text($params,$content,$template,$repeat));

		$content = '<p><img src="image.jpg" alt="Sunset"></p>';
		$this->assertEquals("",smarty_block_remove_if_contains_no_text($params,$content,$template,$repeat));

		$content = "";
		$this->assertEquals("",smarty_block_remove_if_contains_no_text($params,$content,$template,$repeat));

		$content = " ";
		$this->assertEquals("",smarty_block_remove_if_contains_no_text($params,$content,$template,$repeat));
	}
}
