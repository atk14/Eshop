<?php
class WatchedProductsController extends ApplicationController {

	function index(){
		$this->page_title = $this->breadcrumbs[] = _("Uložené položky hlídacího psa");

		$ids = $this->dbmole->selectIntoArray("
			SELECT id FROM
			(
				SELECT id,notified,created_at
				FROM watched_products
				WHERE
					user_id=:user AND
					NOT notified
				UNION
				SELECT id,notified,created_at
				FROM watched_products
				WHERE
					user_id=:user AND
					product_id NOT IN (SELECT product_id FROM watched_products WHERE user_id=:user AND NOT notified) AND
					notified
			)q ORDER BY notified DESC, created_at DESC
		",[":user" => $this->logged_user]);

		$this->tpl_data["watched_products"] = WatchedProduct::GetInstanceById($ids);
	}

	function create_new() {
		$product = $this->_just_find("product","product_id");
		if(!$product || $product->isDeleted()){
			return $this->_execute_action("error404");
		}

		$this->_add_card_to_breadcrumbs($product->getCard());

		$this->page_title = $this->breadcrumbs[] = _("Aktivace hlídacího psa");

		$this->_save_return_uri();

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$d["product_id"] = $product;
			$d["user_id"] = $this->logged_user;

			($wp = WatchedProduct::FindFirst("NOT notified AND product_id=:product AND user_id IS NOT NULL AND user_id=:user",[":product" => $product, ":user" => $this->logged_user])) ||
			($wp = WatchedProduct::FindFirst("NOT notified AND product_id=:product AND email IS NOT NULL AND email=:email",[":product" => $product, ":email" => $d["email"]])) ||
			($wp = WatchedProduct::CreateNewRecord($d));
			
			$this->flash->success(sprintf(_("Hlídací pes byl aktivován. O naskladnění produktu Vás budeme informovat na adrese %s"), h($wp->getEmail())));
			if (!$this->request->xhr()) {
				$this->_redirect_back();
			}
		}
	}

	function _logged_user_required(){
		return in_array($this->action,["index","destroy"]);
	}
}
