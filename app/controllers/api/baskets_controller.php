<?php
class BasketsController extends ApiController {

	function detail(){
		if($this->params->notEmpty() && ($d = $this->form->validate($this->params))){
			$this->api_data = [
				"header_info" => $this->_render([
					"partial" => "shared/basket_info_content",
					"basket" => $this->_get_basket(),
					"was_changed" => true
				]),
			];
		}
	}
}
