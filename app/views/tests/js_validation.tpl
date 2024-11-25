{assign js_validator $form->js_validator()}

<h1>{$page_title}</h1>

{if $cleaned_data}

	{dump var=$cleaned_data}

	<p>{a action="js_validation"}{t}Retry?{/t}{/a}</p>

{else}

{form _novalidate=novalidate _data-validation-messages="{!$js_validator->get_messages()|to_json}" _data-validation-rules="{!$js_validator->get_rules()|to_json}"}

{render partial="shared/form_error"}

<fieldset>

	{render partial="shared/form_field" fields=$form->get_field_keys()}

	{render partial="shared/form_button"}

</fieldset>

{/form}

{/if}
