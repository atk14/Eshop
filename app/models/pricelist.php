<?php
class Pricelist extends ApplicationModel implements Translatable, Rankable {

	use TraitGetInstanceByCode;

	static function GetTranslatableFields(){ return array("name"); }

	function setRank($rank){
		return $this->_setRank($rank);
	}

	static function GetDefaultPricelist(){
		defined("DEFAULT_PRICELIST") || define("DEFAULT_PRICELIST","default");
		return self::GetInstanceByCode(DEFAULT_PRICELIST);
	}

	/**
	 *	$prices = [
	 *		$product_1->getId() => 123.00,
	 *		$product_2->getId() => 10.20,
	 *		// ...
	 *	];
	 *	$pricelist->setPrices($prices);
	 */
	function setPrices($prices,$options = []){
		$options += [
			"delete_missing_prices" => true,
		];

		$existing = $this->dbmole->selectIntoAssociativeArray("SELECT product_id,price FROM pricelist_items WHERE pricelist_id=:this",[":this" => $this]);
		foreach($prices as $product_id => $price){
			$price = !is_null($price) ? number_format($price,6,'.','') : null;
			$existing_price = isset($existing[$product_id]) ? number_format($existing[$product_id],6,'.','') : null;
			if(!is_null($price) && is_null($existing_price)){
				PricelistItem::CreateNewRecord([
					"pricelist_id" => $this,
					"product_id" => $product_id,
					"price" => $price,
				]);
				continue;
			}
			if(!is_null($price) && $existing_price!==$price){
				$pi = PricelistItem::FindFirst("pricelist_id",$this,"product_id",$product_id);
				$pi->s("price",$price);
			}
			if(is_null($price) && !is_null($existing_price)){
				$pi = PricelistItem::FindFirst("pricelist_id",$this,"product_id",$product_id);
				$pi->destroy();
			}
			unset($existing[$product_id]);
		}

		if($options["delete_missing_prices"] && $existing){
			$this->dbmole->doQuery("DELETE FROM pricelist_items WHERE pricelist_id=:this AND product_id IN :p",[":this" => $this, ":p" => array_keys($existing)]);
		}
	}

	function getPrice($product_id, $amount = 1){
		return $this->dbmole->selectFloat("SELECT price FROM pricelist_items WHERE pricelist_id=:this AND product_id=:p AND minimum_quantity<=:amount ORDER BY price",[":this" => $this, ":p" => $product_id, ":amount" => $amount]);
	}

	function setPrice($product_id, $price){
		$product_id = TableRecord::ObjToId($product_id);
		return $this->setPrices([$product_id => $price], ["delete_missing_prices" => false]);
	}

	function delPrice($product_id){
		$product_id = TableRecord::ObjToId($product_id);
		return $this->setPrices([$product_id => null], ["delete_missing_prices" => false]);
	}

	function getCurrency(){
		return Currency::GetDefaultCurrency();
	}

	function containsPricesWithoutVat(){
		return $this->g("contains_prices_without_vat");
	}

	function isDeletable(){
		return $this->getCode()!=DEFAULT_PRICELIST;
	}

}
