<?php
class IndexForm extends AdminForm {

	function set_up(){
		$this->add_search_field();
		$this->set_hidden_field("category_id",$this->controller->category->getId());
	}
}
