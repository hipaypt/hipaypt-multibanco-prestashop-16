{if !$error}
<p>{l s='Your order on' mod='wfxcomprafacil'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='wfxcomprafacil'}
	<br /><br />
	{l s='ATM payment details:' mod='wfxcomprafacil'}
	<br /><br />
	<p>
		<table border="1" cellpadding="5" cellspacing="5" style="border:1px solid #ccc;">
			<tr>
				<td>
					<img src="{$this_path}multibanco.jpg" alt="{l s='Comprafacil' mod='wfxcomprafacil'}" border="0" />
				</td>
				<td align="left" valign="top">
						{l s='Entity:' mod='wfxcomprafacil'} <span>{$entity}</span>
						<br /><br />{l s='Reference:' mod='wfxcomprafacil'} <span>{$reference}</span>
						<br /><br />{l s='Value:' mod='wfxcomprafacil'} <span>{$value}</span>
				</td>
			</tr>
		</table>
	</p>

	<br /><br />{l s='An e-mail has been sent to you with this information.' mod='wfxcomprafacil'}
	<br /><br />{l s='Your order will be sent as soon as we receive your payment.' mod='wfxcomprafacil'}
	<br /><br />{l s='For any questions or for further information, please contact our' mod='wfxcomprafacil'} <a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='wfxcomprafacil'}</a>.
</p>
{else}
    <p class="warning">{$error}</p>
{/if}
