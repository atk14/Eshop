<?php
class SystemParametersController extends AdminController {

	function index(){
		$this->page_title = _("System preferences");

		$this->tpl_data["system_parameters"] = SystemParameter::FindAll(["order_by" => "code ASC"]);
	}

	function edit(){
		$this->page_title = sprintf(_("Editing parameter %s"),$this->system_parameter->getCode());

		$this->form->prepare_for($this->system_parameter);
		$this->form->set_initial($this->system_parameter);
		$this->_save_return_uri();

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			if($d==$this->form->get_initial() || $this->system_parameter->isReadOnly()){
				$this->flash->notice(_("Nothing has been changed"));
			}else{
				$this->system_parameter->s($d);
				$this->flash->success(_("Parameter value has been updated"));
			}
			$this->_redirect_back();
		}
	}

	function _before_filter(){
		if($this->action=="edit"){
			$this->_find("system_parameter");
		}
	}
}
