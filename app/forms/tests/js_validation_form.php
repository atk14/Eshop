<?php
class JsValidationForm extends ApplicationForm {

	function set_up(){
		$this->add_field("login", new LoginField([
			"label" => "login",
		]));

		$this->add_field("firstname", new CharField([
			"label" => "Firstname",
			"min_length" => 2,
			"max_length" => 30,
		]));

		$this->add_field("lastname", new CharField([
			"label" => "Lastname",
			"min_length" => 2,
			"max_length" => 60,
		]));

		$this->add_field("age", new IntegerField([
			"label" => "Your age",
			"min_value" => 10,
			"max_value" => 123, 
		]));

		$this->add_field("totem_animal", new ChoiceField([	
			"label" => "Totem animal",
			"choices" => [
				"" => "",
				"mouse" => "Mouse",
				"lion" => "Lion",
				"squirrel" => "Squirrel",
			],
		]))->update_messages([
			"required" => "Please choose your totem animal."
		]);

		$this->add_field("password", new PasswordField([
			"label" => "Password",
		]));

		$this->add_field("password_confirmation", new PasswordField([
			"label" => "Password confirmation",
		]));

		$this->add_field("captcha", new RegexField('/^\s*aho[jy]\s*$/i', [
			"label" => "Captcha",
			"help_text" => "Write the word AHOY",
		]));

		$this->set_button_text("Continue");
	}

	function js_validator(){
		$js_v = parent::js_validator();

		$js_v->validators["login"]->add_rule("remote",Atk14Url::BuildLink(array("controller" => "tests", "action" => "check_login_availability")));
		$js_v->validators["login"]->add_message("remote","The login has been already taken");

		$js_v->validators["password_confirmation"]->add_rule("equalTo","#id_password");
		$js_v->validators["password_confirmation"]->add_message("equalTo",_("Please, enter the same password"));
	
		return $js_v;
	}
}
