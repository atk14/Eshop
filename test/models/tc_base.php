<?php
/**
 * The base class of every model`s test case class.
 *
 * Notice that TcBase is descendant of TcAtk14Model
 * so there are a couple of special member variables in advance.
 */
class TcBase extends TcAtk14Model{

	function _setUp(){
		$this->dbmole->begin();
		$this->setUpFixtures();
	}

	function _tearDown(){
		$this->dbmole->rollback();
	}

	function callForData($data, $function, $arguments = []) {
		end($data);
		$name = key($data);
		$vals = array_pop($data);
		if($data) {
			foreach($vals as $v) {
				$this->callForData($data, $function, $arguments + [ $name => $v ]);
			}
		}	else {
			foreach($vals as $v) {
				$function($arguments + [$name => $v]);
			}
		}
	}

	function _prepareEmptyBasket($values = []){
		$values += [
			"region_id" => Region::FindByCode("CR"),
			"delivery_method_id" => isset($this->delivery_methods) ? $this->delivery_methods["dpd"] : null, // from fixture delivery_methods
			"payment_method_id" => isset($this->payment_methods) ? $this->payment_methods["cash_on_delivery"] : null, // from fixture payment_methods
		];
		$basket = Basket::CreateNewRecord($values);
		return $basket;
	}

	function _prepareBasketWithWoodenButton($basket = null){
		if(is_null($basket)){
			$basket = $this->_prepareEmptyBasket();
		}
		$basket->addProduct($this->products["wooden_button"],2);
		return $basket;
	}
}
