<!-- Modal -->
<div class="modal{if $animation} fade{/if}" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog{if $vertically_centered} modal-dialog-centered{/if}">
		<div class="modal-content">
			<div class="modal-header">
				{if $close_button}
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				{/if}
				{if $title}
				<div class="modal-title" id="myModalLabel">{$title}</div>
				{/if}
			</div>

			<div class="modal-body">
				{!$content}
			</div>

			{if $footer}
			<div class="modal-footer">
				{!$footer}
			</div>
			{/if}

		</div>
	</div>
</div>

{if $open_on_load}
	{content for=js}
		$(window).load(function(){
			$("#{$id}").modal("show");
		});
	{/content}
{/if}
