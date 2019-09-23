<?php
class ArticlesForm extends AdminForm {

	function set_up(){
		$this->add_translatable_field("title",new CharField(array(
			"label" => _("Title"),
			"max_length" => 255,
		)));

		$this->add_field("image_url",new PupiqImageField([
			"label" => _("Image"),
			"required" => false,
			"help_text" => _("Image used in article overview")
		]));

		$this->add_translatable_field("teaser",new TextField(array(
			"label" => _("Teaser"),
			"required" => false,
		)));

		$this->add_translatable_field("body",new MarkdownField(array(
			"label" => _("Body"),
			"help_text" => _("Markdown format is expected"),
		)));

		$this->add_field("published_at",new DateTimeField(array(
			"label" => _("Published At"),
			"initial" => time(),
		)));

		$this->add_field("tags", new TagsField(array(
			"label" => _("Tags"),
			"required" => false,
			"hints" => array("news", "news , webdesign"),
			"create_tag_if_not_found" => true,
		)));
	}
}
