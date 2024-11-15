<?php
class PaymentTransactionsController extends AdminController {

	function index(){
		$this->page_title = _("Online platební transakce");

		($d = $this->form->validate($this->params)) || ($d = $this->form->get_initial());

		$conditions = $bind_ar = array();

		if($d["search"]){
	
			$search = Translate::Upper($d["search"]);

			$_ar = array();
			foreach(array(
				"id::VARCHAR",
				"payment_transaction_id",
				"(SELECT order_no FROM orders WHERE orders.id=payment_transactions.order_id)",
				"(SELECT company FROM orders WHERE orders.id=payment_transactions.order_id)",
				"(SELECT firstname FROM orders WHERE orders.id=payment_transactions.order_id)",
				"(SELECT lastname FROM orders WHERE orders.id=payment_transactions.order_id)",
				"(SELECT email FROM orders WHERE orders.id=payment_transactions.order_id)",
			) as $f){
				$_ar[] = "COALESCE($f,'')";
			}

			$condition = FullTextSearchQueryLike::GetQuery("UPPER(".join("||' '||",$_ar).")",$search,$bind_ar);

			if($condition){
				$conditions[] = $condition;
				$this->sorting->add("search","(SELECT order_no FROM orders WHERE orders.id=payment_transactions.order_id) LIKE :search||'%' DESC, created_at DESC");
				$bind_ar[":search"] = $search;
			}
		}

		$this->sorting->add("created_at","created_at DESC, id DESC");
		$this->sorting->add("payment_status_updated_at","COALESCE(payment_status_updated_at,created_at) DESC, id DESC");

		$this->tpl_data["finder"] = PaymentTransaction::Finder([
			"conditions" => $conditions,
			"bind_ar" => $bind_ar,
			"order_by" => $this->sorting,
			"offset" => $this->params->getInt("offset"),
		]);
	}

	function detail(){
		$this->page_title = sprintf(_("Online platební transakce #%s"),$this->payment_transaction->getId());

		$this->tpl_data["order"] = $order = $this->payment_transaction->getOrder();
		$this->tpl_data["currency"] = $order->getCurrency();
		$this->tpl_data["payment_gateway"] = $this->payment_transaction->getPaymentGateway();
		$this->tpl_data["payment_status"] = $this->payment_transaction->getPaymentStatus();
	}

	function api_dump(){
		$this->page_title = sprintf(_("Výpis platební transakce #%s z API"),$this->payment_transaction->getId());
		$this->breadcrumbs[] = [sprintf(_("Online platební transakce #%s"),$this->payment_transaction->getId()),$this->_link_to(["action" => "detail", "id" => $this->payment_transaction])];

		$pt = $this->payment_transaction;
		$api = $pt->getPaymentGatewayApi();
		$current_status_code = $api->getCurrentPaymentStatusCode($pt,$data,$internal_status);

		$this->tpl_data["current_status_code"] = $current_status_code;
		$this->tpl_data["data"] = $data;
		$this->tpl_data["internal_status"] = $internal_status;
		$this->tpl_data["current_datetime"] = now();
	}

	function _before_filter(){
		if(in_array($this->action,["detail","api_dump"])){
			$this->_find("payment_transaction");
		}
	}
}
