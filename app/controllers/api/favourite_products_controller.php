<?php
class FavouriteProductsController extends ApiController {

	function detail(){
		if($this->params->notEmpty() && ($d = $this->form->validate($this->params))){
			$this->api_data = [
				"header_info" => $this->_render([
					"partial" => "shared/layout/header/header_favourites",
					"basket" => $this->_get_basket(),
				]),
			];
		}
	}
}
