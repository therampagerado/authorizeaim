{*
 * 2007-2016 PrestaShop
 *
 * Thirty Bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017 Thirty Bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    Thirty Bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017 Thirty Bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
*}
{if $status == 'ok'}
	<p>{l s='Your order on' mod='authorizeaim'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='authorizeaim'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='authorizeaim'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='authorizeaim'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='authorizeaim'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='authorizeaim'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='authorizeaim'}</a>.
	</p>
{/if}
