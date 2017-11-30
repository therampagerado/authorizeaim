<?php
/**
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
 *  @author    Thirty Bees <modules@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017 Thirty Bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

/* will include backward file */
include(dirname(__FILE__).'/authorizeaim.php');

$authorizeaim = new AuthorizeAIM();

/* Does the cart exist and is valid? */
$cart = Context::getContext()->cart;

if (!isset($_POST['x_invoice_num'])) {
    Logger::addLog('Missing x_invoice_num', 4);
    die('An unrecoverable error occured: Missing parameter');
}

if (!Validate::isLoadedObject($cart)) {
    Logger::addLog('Cart loading failed for cart '.(int) $_POST['x_invoice_num'], 4);
    die('An unrecoverable error occured with the cart '.(int) $_POST['x_invoice_num']);
}

if ($cart->id != $_POST['x_invoice_num']) {
    Logger::addLog('Conflict between cart id order and customer cart id');
    die('An unrecoverable conflict error occured with the cart '.(int) $_POST['x_invoice_num']);
}

$customer = new Customer((int) $cart->id_customer);
$invoiceAddress = new Address((int) $cart->id_address_invoice);
$currency = new Currency((int) $cart->id_currency);

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($invoiceAddress) && !Validate::isLoadedObject($currency)) {
    Logger::addLog('Issue loading customer, address and/or currency data');
    die('An unrecoverable error occured while retrieving you data');
}

$params = [
    'x_test_request'   => (bool) Configuration::get('AUTHORIZE_AIM_TEST_MODE'),
    'x_invoice_num'    => (int) $_POST['x_invoice_num'],
    'x_amount'         => number_format((float) $cart->getOrderTotal(true, 3), 2, '.', ''),
    'x_exp_date'       => Tools::safeOutput($_POST['x_exp_date_m'].$_POST['x_exp_date_y']),
    'x_address'        => Tools::safeOutput($invoiceAddress->address1.' '.$invoiceAddress->address2),
    'x_zip'            => Tools::safeOutput($invoiceAddress->postcode),
    'x_first_name'     => Tools::safeOutput($customer->firstname),
    'x_last_name'      => Tools::safeOutput($customer->lastname),
    'x_version'        => '3.1',
    'x_delim_data'     => true,
    'x_delim_char'     => '|',
    'x_relay_response' => false,
    'x_type'           => 'AUTH_CAPTURE',
    'x_currency_code'  => $currency->iso_code,
    'x_method'         => 'CC',
    'x_solution_id'    => 'AAA172566',
    'x_login'          => Tools::safeOutput(Configuration::get('AUTHORIZE_AIM_LOGIN_ID_'.$currency->iso_code)),
    'x_tran_key'       => Tools::safeOutput(Configuration::get('AUTHORIZE_AIM_KEY_'.$currency->iso_code)),
    'x_card_num'       => Tools::safeOutput($_POST['x_card_num']),
    'x_card_code'      => Tools::safeOutput($_POST['x_card_code']),
];

$postString = '';
foreach ($params as $key => $value) {
    $postString .= $key.'='.urlencode($value).'&';
}
$postString = trim($postString, '&');
$url = 'https://'.(Configuration::get('AUTHORIZE_AIM_SANDBOX') ? 'test' : 'secure').'.authorize.net/gateway/transact.dll';

/* Do the CURL request ro Authorize.net */
$request = curl_init($url);
curl_setopt($request, CURLOPT_HEADER, 0);
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($request, CURLOPT_POSTFIELDS, $postString);
curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
$postResponse = curl_exec($request);
curl_close($request);

$response = explode('|', $postResponse);
if (!isset($response[7]) || !isset($response[3]) || !isset($response[9])) {
    $msg = 'Authorize.net returned a malformed response for cart';
    if (isset($response[7])) {
        $msg .= ' '.(int) $response[7];
    }
    Logger::addLog($msg, 4);
    die('Authorize.net returned a malformed response, aborted.');
}

$message = $response[3];
$paymentMethod = 'Authorize.net AIM (Advanced Integration Method)';

switch ($response[0]) // Response code
{
    case 1: // Payment accepted
        $authorizeaim->setTransactionDetail($response);
        $authorizeaim->validateOrder(
            (int) $cart->id,
            Configuration::get('PS_OS_PAYMENT'), (float) $response[9],
            $paymentMethod, $message, null, null, false, $customer->secure_key
        );
        break;

    case 4: // Hold for review
        $authorizeaim->validateOrder(
            (int) $cart->id,
            Configuration::get('AUTHORIZE_AIM_HOLD_REVIEW_OS'), (float) $response[9],
            $authorizeaim->displayName, $response[3], null, null, false, $customer->secure_key
        );
        break;

    default:
        $errorMessage = (isset($response[3]) && !empty($response[3])) ? urlencode(Tools::safeOutput($response[3])) : '';

        $checkoutType = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
        $url = _PS_VERSION_ >= '1.5' ? 'index.php?controller='.$checkoutType.'&' : $checkoutType.'.php?';
        $url .= 'step=3&cgv=1&aimerror=1&message='.$errorMessage;

        if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order')) {
            Tools::redirect($url);
        } else {
            if (strstr($_SERVER['HTTP_REFERER'], '?')) {
                Tools::redirect($_SERVER['HTTP_REFERER'].'&aimerror=1&message='.$errorMessage, '');
            } else {
                Tools::redirect($_SERVER['HTTP_REFERER'].'?aimerror=1&message='.$errorMessage, '');
            }
        }

        exit;
}

$url = 'index.php?controller=order-confirmation&';
if (_PS_VERSION_ < '1.5') {
    $url = 'order-confirmation.php?';
}

$auth_order = new Order($authorizeaim->currentOrder);
Tools::redirect($url.'id_module='.(int) $authorizeaim->id.'&id_cart='.(int) $cart->id.'&key='.$auth_order->secure_key);
