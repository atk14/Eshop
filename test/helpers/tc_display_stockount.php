<?php
/**
 *
 * @fixture products
 * @fixture warehouse_items
 * @fixture pricelist_items
 */
class TcDisplayStockount extends TcBase {

	function test(){
		Atk14Require::Helper("function.display_stockcount");

		$smarty = Atk14Utils::GetSmarty();

		$this->assertEquals('<span class="text-success">In stock &gt; 10 pcs</span>',@smarty_function_display_stockcount(array("product" => $this->products["mint_tea"]),$smarty));
		$this->assertEquals('<span class="text-success">In stock 5 pcs</span>',@smarty_function_display_stockcount(array("product" => $this->products["black_tea"]),$smarty));
		$this->assertEquals('',smarty_function_display_stockcount(array("product" => $this->products["black_tea"], "display_nothing_if_can_be_ordered" => true),$smarty));
	}
}
