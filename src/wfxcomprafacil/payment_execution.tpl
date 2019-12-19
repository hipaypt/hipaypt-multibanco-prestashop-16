{capture name=path}{l s='Comprafacil payment' mod='wfxcomprafacil'}{/capture}

<h2>{l s='Order summary' mod='wfxcomprafacil'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='Comprafacil payment' mod='wfxcomprafacil'}</h3>
<form action="{$this_path}validation.php" method="post">
    <p>
        <img src="{$this_path}cards.png" alt="{l s='Comprafacil' mod='wfxcomprafacil'}" style="float:left; margin: 0px 20px 45px 0px;" />
        {l s='You have choosen to pay with Comprafacil.' mod='wfxcomprafacil'}
    </p>
    <p style="margin-top:20px;">
        - {l s='The total amount of your order is' mod='wfxcomprafacil'}
        <span id="amount" class="price">{displayPrice price=$total}</span>
        {if $use_taxes == 1}
            {l s='(tax incl.)' mod='wfxcomprafacil'}
        {/if}
        <br><br>
    </p>
    <p>
        {l s='ATM payment details will be displayed on the next page.' mod='wfxcomprafacil'}
        <br /><br />
        <b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='wfxcomprafacil'}.</b>
        <br><br>
    </p>
    <p class="cart_navigation">
        <a href="{$link->getPageLink('order.php', true)}?step=3" class="button_large hideOnSubmit">{l s='Other payment methods' mod='wfxcomprafacil'}</a>
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='wfxcomprafacil'}" class="exclusive exclusive_large hideOnSubmit" />
    </p>
</form>
{/if}
