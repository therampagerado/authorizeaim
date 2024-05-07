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
<div class="authorizeaim-wrapper panel">
	<a href="http://reseller.authorize.net/application/prestashop/" class="authorizeaim-logo" target="_blank"><img src="{$module_dir}img/logo_authorize.png" alt="Authorize.net" border="0"/></a>
	<p class="authorizeaim-intro">{l s='Start accepting payments through your PrestaShop store with Authorize.Net, the pioneering provider of ecommerce payment services.  Authorize.Net makes accepting payments safe, easy and affordable.' mod='authorizeaim'}</p>
	<div class="authorizeaim-content">
		<div class="authorizeaim-leftCol">
			<h3>&nbsp;&nbsp;&nbsp;{l s='Why Choose Authorize.Net?' mod='authorizeaim'}</h3>
			<ul>
				<li>{l s='Leading payment gateway since 1996 with 400,000+ active merchants' mod='authorizeaim'}</li>
				<li>{l s='Multiple currency acceptance' mod='authorizeaim'}</li>
				<li>{l s='FREE award-winning customer support via telephone, email and online chat' mod='authorizeaim'}</li>
				<li>{l s='FREE Virtual Terminal for mail order/telephone order transactions' mod='authorizeaim'}</li>
				<li>{l s='No Contracts or long term commitments ' mod='authorizeaim'}</li>
				<li>{l s='Additional services include: ' mod='authorizeaim'}
					<ul class="none">
						<li>{l s='- Advanced Fraud Detection Suite™' mod='authorizeaim'}</li>
						<li>{l s='- Automated Recurring Billing ™' mod='authorizeaim'}</li>
						<li>{l s='- Customer Information Manager' mod='authorizeaim'}</li>
					</ul>
				</li>
				<li>{l s='Gateway and merchant account set up available' mod='authorizeaim'}</li>
				<li>{l s='Simple setup process' mod='authorizeaim'}
				</li>
			</ul>
			<ul class="none" style = "display: inline; font-size: 13px;">
				<li><a href="http://reseller.authorize.net/application/prestashop/" target="_blank" class="authorizeaim-link">{l s='Sign up Now' mod='authorizeaim'}</a></li>
			</ul>
		</div>
		<div class="authorizeaim-video">
			<p>{l s='Have you ever wondered how credit card payments work? Connecting a payment application to the credit card processing networks is difficult, expensive and beyond the resources of most businesses. Authorize.Net provides the complex infrastructure and security necessary to ensure secure, fast and reliable transactions. See How:' mod='authorizeaim'}</p>
			<a href="http://www.youtube.com/watch?v=8SQ3qst0_Pk" class="authorizeaim-video-btn">
				<img src="{$module_dir}img/video-screen.jpg" alt="Merchant Warehouse screencast" />
				<img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" />
			</a>
		</div>
	</div>

	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
		<div>
			<h3>{l s='Configure your existing Authorize.Net Accounts' mod='authorizeaim'}{if !$PS_SSL_ENABLED}<p class="authwarn">{l s='NO SSL Active, This module requires SSL and will not be visible until SSL is enabled' mod='authorizeaim'}</p>{/if}</h3>

			{* Determine which currencies are enabled on the store and supported by Authorize.net & list one credentials section per available currency *}
			{foreach from=$currencies item='currency'}
				{if (in_array($currency.iso_code, $available_currencies))}
					{assign var='configuration_id_name' value="AUTHORIZE_AIM_LOGIN_ID_"|cat:$currency.iso_code}
					{assign var='configuration_key_name' value="AUTHORIZE_AIM_KEY_"|cat:$currency.iso_code}
					<table>
						<tr>
							<td>
								<p>{l s='Credentials for' mod='authorizeaim'}<b> {$currency.iso_code}</b> {l s='currency' mod='authorizeaim'}</p>
								<label for="authorizeaim_login_id">{l s='Login ID' mod='authorizeaim'}:</label>
								<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_login_id_{$currency.iso_code}" name="authorizeaim_login_id_{$currency.iso_code}" value="{${$configuration_id_name}}"/></div>
								<label for="authorizeaim_key">{l s='Key' mod='authorizeaim'}:</label>
								<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_key_{$currency.iso_code}" name="authorizeaim_key_{$currency.iso_code}" value="{${$configuration_key_name}}"/></div>
							</td>
						</tr>
					</table>
					<br/>
					<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade/>
					<br/>
				{/if}
			{/foreach}

			<label for="authorizeaim_mode"><a class="authorizeaim-sign-up" target="_blank" href="https://developer.authorize.net/guides/AIM/wwhelp/wwhimpl/js/html/wwhelp.htm"><img src="{$module_dir}img/help.png" alt=""/></a> {l s='Environment:' mod='authorizeaim'}</label>
			<div class="margin-form" id="authorizeaim_mode">
				<input type="radio" name="authorizeaim_mode" value="0" style="vertical-align: middle;" {if !$AUTHORIZE_AIM_SANDBOX && !$AUTHORIZE_AIM_TEST_MODE}checked="checked"{/if} />
				<span>{l s='Live mode' mod='authorizeaim'}</span><br/>
				<input type="radio" name="authorizeaim_mode" value="1" style="vertical-align: middle;" {if !$AUTHORIZE_AIM_SANDBOX && $AUTHORIZE_AIM_TEST_MODE}checked="checked"{/if} />
				<span>{l s='Test mode (in production server)' mod='authorizeaim'}</span><br/>
				<input type="radio" name="authorizeaim_mode" value="2" style="vertical-align: middle;" {if $AUTHORIZE_AIM_SANDBOX}checked="checked"{/if} />
				<span>{l s='Test mode' mod='authorizeaim'}</span><br/>
			</div>
			<label for="authorizeaim_cards">{l s='Cards* :' mod='authorizeaim'}</label>
			<div class="margin-form" id="authorizeaim_cards">
				<input type="checkbox" name="authorizeaim_card_visa" {if $AUTHORIZE_AIM_CARD_VISA}checked="checked"{/if} />
				<img src="{$module_dir}/views/img/visa.png" alt="visa" height="32"/>
				<input type="checkbox" name="authorizeaim_card_mastercard" {if $AUTHORIZE_AIM_CARD_MASTERCARD}checked="checked"{/if} />
				<img src="{$module_dir}/views/img/mastercard.png" alt="visa" height="32"/>
				<input type="checkbox" name="authorizeaim_card_discover" {if $AUTHORIZE_AIM_CARD_DISCOVER}checked="checked"{/if} />
				<img src="{$module_dir}/views/img/discover.png" alt="visa" height="32"/>
				<input type="checkbox" name="authorizeaim_card_ax" {if $AUTHORIZE_AIM_CARD_AX}checked="checked"{/if} />
				<img src="{$module_dir}/views/img/amex.png" alt="visa" height="32"/>
			</div>

			<label for="authorizeaim_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='authorizeaim'}</label>
			<div class="margin-form">
				<select id="authorizeaim_hold_review_os" class="col-md-4 col-lg-3" name="authorizeaim_hold_review_os">';
					// Hold for Review order state selection
					{foreach from=$order_states item='os'}
						<option value="{if $os.id_order_state|intval}" {((int)$os.id_order_state == $AUTHORIZE_AIM_HOLD_REVIEW_OS)} selected{/if}>
							{$os.name|stripslashes}
						</option>
					{/foreach}
				</select>
			</div>
			<br/>
			<sub>{l s='* Subject to region' mod='authorizeaim'}</sub>
		</div>
		<div class="panel-footer">
			<button type="submit" class="btn btn-default pull-right" name="submitModule"><i class="process-icon-save"></i> {l s='Save'}</button>
		</div>
	</form>
</div>
