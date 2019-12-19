<div id="formAddPaymentPanel" class="panel">
	<div class="panel-heading">
		<i class="icon-money"></i>
		{l s='ATM payment details:' mod='wfxcomprafacil'}
	</div>

	<div class="table-responsive">
		<table border="1" cellpadding="5" cellspacing="5" style="border:1px solid #ccc;">
			<tr>
				<td style="margin:2px;">
					<img src="{$this_path}multibanco.jpg" alt="{l s='Comprafacil' mod='wfxcomprafacil'}" border="0" />
				</td>
				<td align="left" valign="top" style="padding:4px;margin:2px;">
						{l s='Entity:' mod='wfxcomprafacil'} <b>{$entity}</b>
						<br /><br />{l s='Reference:' mod='wfxcomprafacil'} <b>{$reference}</b>
						<br /><br />{l s='Value:' mod='wfxcomprafacil'} <b>{$value}</b>
				</td>
			</tr>
		</table>

	</div>
</div>