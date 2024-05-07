{*
 * 2007-2016 PrestaShop
 *
 * Thirty Bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    Thirty Bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
*}
{literal}
	<script type="text/javascript">
		$(document).ready(function () {
			$("a.fancybox").fancybox();
		});
	</script>
{/literal}
<link rel="shortcut icon" type="image/x-icon" href="{$module_dir}img/secure.png"/>
<div class="row">
	<div class="col-xs-12">
		<div class="auth_wrapper">
			<p>    {if $isFailed == 1}
			<p style="color: red;">
				{if !empty($smarty.get.message)}
					{l s='Error detail from AuthorizeAIM : ' mod='authorizeaim'}
					{$smarty.get.message|htmlentities}
				{else}
					{l s='Error, please verify the card information' mod='authorizeaim'}
				{/if}
			</p>
			{/if}
			<form name="authorizeaim_form" id="authorizeaim_form" action="{$module_dir}validation.php" method="post">
				<a id="click_authorizeaim" href="#" title="{l s='Pay with AuthorizeAIM' mod='authorizeaim'}">
					{if $cards.visa == 1}<i class="fa fa-cc-visa auth_icon"></i>{/if}
					{if $cards.mastercard == 1}<i class="fa fa-cc-mastercard auth_icon"></i>{/if}
					{if $cards.discover == 1}<i class="fa fa-cc-discover auth_icon"></i>{/if}
					{if $cards.ax == 1}<i class="fa fa-cc-amex auth_icon"></i>{/if}
					&nbsp;&nbsp;{l s='Secured card payment' mod='authorizeaim'}</a>
				{if $isFailed == 0}
				<div id="aut2" style="display:none">
					{else}
					<div id="aut2">
						{/if}

						<input type="hidden" name="x_solution_ID" value="A1000006"/>
						<input type="hidden" name="x_invoice_num" value="{$x_invoice_num|escape:'htmlall':'UTF-8'}"/>
						<input type="hidden" name="x_currency_code" value="{$currency->iso_code|escape:'htmlall':'UTF-8'}"/>
						<div class="col-xs-12 clearfix mt25">
							<div class="col-xs-6">
								<label class="auth_label">{l s='Full name' mod='authorizeaim'}</label>
							</div>
							<div class="col-xs-6">
								<input type="text" name="name" id="fullname" size="30" maxlength="25S"/>
							</div>
						</div>
						<div class="col-xs-12 clearfix mt10">
							<div class="col-xs-6">
								<label class="auth_label">{l s='Card Type' mod='authorizeaim'}</label>
							</div>
							<div class="col-xs-6">
								<select id="cardType">
									{if $cards.ax == 1}
										<option value="AmEx">American Express</option>
									{/if}
									{if $cards.visa == 1}
										<option value="Visa">Visa</option>
									{/if}
									{if $cards.mastercard == 1}
										<option value="MasterCard">MasterCard</option>
									{/if}
									{if $cards.discover == 1}
										<option value="Discover">Discover</option>
									{/if}
								</select>
							</div>
						</div>
						<div class="col-xs-12 clearfix mt10">
							<div class="col-xs-6">
								<label class="auth_label">{l s='Card number' mod='authorizeaim'}</label>
							</div>
							<div class="col-xs-6">
								<input type="text" name="x_card_num" id="cardnum" size="30" maxlength="16" autocomplete="Off"/>
							</div>
						</div>
						<div class="col-xs-12 clearfix mt10">
							<div class="col-xs-6">
								<label class="auth_label">{l s='Expiration date' mod='authorizeaim'}</label>
							</div>
							<div class="col-xs-6">
								<select id="x_exp_date_m" name="x_exp_date_m" style="width:60px;">
									{section name=date_m start=01 loop=13}
										<option value="{$smarty.section.date_m.index}">{$smarty.section.date_m.index}</option>
									{/section}
								</select>/<select name="x_exp_date_y">
									{assign var="startYear" value=$smarty.now|date_format:"%Y"}
									{section name=date_y start=$startYear loop=$startYear+10}
										<option value="{$smarty.section.date_y.index}">{$smarty.section.date_y.index}</option>
									{/section}
								</select>
							</div>
						</div>
						<div class="col-xs-12 clearfix mt10">
							<div class="col-xs-6">
								<label class="auth_label">{l s='CVV' mod='authorizeaim'}</label>
							</div>
							<div class="col-xs-6">
								<input type="text" name="x_card_code" id="x_card_code" size="4" maxlength="4"/>
								<a href="{$module_dir}img/cvvImage.jpg" class="fancybox">
									<img src="{$module_dir}img/help.png" id="cvv_help" title="{l s='the 3 last digits on the back of your credit card' mod='authorizeaim'}" alt=""/>
								</a>
							</div>
						</div>
						<div class="col-xs-12 setHeight mt10">
							<div class="col-xs-3">
							</div>
							<div class="col-xs-3">
								<input type="button" id="asubmit" value="{l s='Complete Purchase' mod='authorizeaim'}" class="button btn btn-default standard-checkout button-medium p10"/>
								<br class="clear"/>
							</div>
							<div class="col-xs-3">
							</div>
						</div>
					</div>
			</form>
			</p>
			<script type="text/javascript">
				var mess_error = "{l s='Please check your credit card information (Credit card type, number and expiration date)' mod='authorizeaim' js=1}";
				var mess_error2 = "{l s='Please specify your Full Name' mod='authorizeaim' js=1}";
				{literal}        $(document).ready(function () {
					$('#x_exp_date_m').children('option').each(function () {
						if ($(this).val() < 10) {
							$(this).val('0' + $(this).val());
							$(this).html($(this).val())
						}
					});
					$('#click_authorizeaim').click(function (e) {
						e.preventDefault();
						$('#click_authorizeaim').fadeOut("fast", function () {
							$("#aut2").show();
							$('#click_authorizeaim').fadeIn('fast');
						});
						$('#click_authorizeaim').unbind();
						$('#click_authorizeaim').click(function (e) {
							e.preventDefault();
						});
					});
					$('#asubmit').click(function () {
						if ($('#fullname').val() == '') {
							alert(mess_error2);
						} else if (!validateCC($('#cardnum').val(), $('#cardType').val()) || $('#x_card_code').val() == '') {
							alert(mess_error);
						} else {
							$('#authorizeaim_form').submit();
							$('#asubmit').prop("disabled", true);
						}
						return false;
					});
				});{/literal}</script>
		</div>
	</div>
</div>
