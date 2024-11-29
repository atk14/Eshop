<h1>{$page_title}</h1>

{if $cleaned_data}

	{dump var=$cleaned_data}

	<p>{a action="js_validation"}{t}Retry?{/t}{/a}</p>

{else}

{form _novalidate=novalidate}

{render partial="shared/form_error"}

<fieldset>

	{render partial="shared/form_field" fields=$form->get_field_keys()}

	{render partial="shared/form_button"}

</fieldset>

{/form}

{/if}
