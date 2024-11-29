<?php
class TestsController extends ApplicationController {

	function index(){
		$this->page_title = "Testování komponent";
		$this->head_tags->setMetaTag("robots", "noindex,noarchive");
		$this->head_tags->setMetaTag("googlebot", "noindex");
	}

	function modal(){
		$this->page_title = "Testování block.modal";
	}

	function remote_modal(){

	}

	function remote_modal_vertically_centered(){

	}

	function remote_bootbox_modal(){

	}
	
	function colors(){
		$this->page_title = "Color manipulation";
	}
	
	function swiper_custom_config(){
		$this->page_title = "Swiper custom configuration";
	}

	function notify_order_creation(){
		$order = Order::FindFirst(["order_by" => "created_at DESC"]);
		$this->mailer->notify_order_creation($order);
		$this->_dump_email();
	}

	function notify_order_status_update(){
		$order = Order::FindFirst(["order_by" => "created_at DESC"]);
		$this->mailer->notify_order_status_update($order);
		$this->_dump_email();
	}

	function notify_user_registration(){
		$user = User::FindFirst(["order_by" => "created_at DESC"]);
		$this->mailer->notify_user_registration($user);
		$this->_dump_email();
	}

	// testovani vzhledu stranky HTTP 500 Internal Server Error
	function e500(){
		$this->render_template = false;
		ob_start();
		$logged = true;
		include(ATK14_DOCUMENT_ROOT . "/config/error_pages/error500.phtml");
		$content = ob_get_contents();
		ob_end_clean();
		$this->response->write($content);
	}

	function e503(){
		$this->render_template = false;
		ob_start();
		$logged = true;
		include(ATK14_DOCUMENT_ROOT . "/config/error_pages/error503.phtml");
		$content = ob_get_contents();
		ob_end_clean();
		$this->response->write($content);
	}

	function js_validation(){
		$this->page_title = _("Form with JS validation");

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->tpl_data["cleaned_data"] = $d;
		}
	}

	function no_js_validation(){
		$this->page_title = _("Form without JS validation");

		$this->form = $this->_get_form("JsValidationForm");

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->tpl_data["cleaned_data"] = $d;
		}
	}

	function check_login_availability(){
		$this->response->write(User::FindByLogin($this->params->getString("login")) ? "false" : "true");
		$this->render_template = false;
	}

	function _dump_email(){
		$this->render_template = false;
		$this->response->write(sprintf('From: "%s" &lt;%s&gt;<br>',$this->mailer->from_name,$this->mailer->from));
		$this->response->write("To: ".$this->mailer->to."<br>");
		$this->response->write("Subject: ".$this->mailer->subject);
		$this->response->write("<hr>");
		$this->response->write($this->mailer->body_html);
		$this->response->write("<hr><pre>".h($this->mailer->body)."</pre>");
	}

	function _before_filter(){
		// neni zadouci posilani emailu v produkci!
		if(PRODUCTION && preg_match('/^notify_/',$this->action)){
			$this->_execute_action("error403");
		}
	}

	function _before_render(){
		parent::_before_render();

		$this->breadcrumbs[] = ["Testování",$this->_link_to("tests/index")];
		if($this->action!="index"){
			$this->breadcrumbs[] = $this->page_title;
		}
	}
}
