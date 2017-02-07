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

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class AuthorizeAIM
 */
class AuthorizeAIM extends PaymentModule
{
    /** @var array $aimAvailableCurrencies */
    protected $aimAvailableCurrencies;

    /**
     * AuthorizeAIM constructor.
     */
    public function __construct()
    {
        $this->name = 'authorizeaim';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        $this->author = 'thirty bees';
        $this->aimAvailableCurrencies = ['USD', 'AUD', 'CAD', 'EUR', 'GBP', 'NZD'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Authorize.net AIM (Advanced Integration Method)';
        $this->description = $this->l('Receive payment with Authorize.net with updated Akamai endpoint');
    }

    /**
     * Install the module
     *
     * @return bool Indicates whether the module has been successfully installed
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('orderConfirmation') &&
            $this->registerHook('payment') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 1) &&
            Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 0) &&
            Configuration::updateValue('AUTHORIZE_AIM_HOLD_REVIEW_OS', _PS_OS_ERROR_);
    }

    /**
     * Uninstall the module
     *
     * @return bool Indicates whether the module has been successfully uninstalled
     */
    public function uninstall()
    {
        Configuration::deleteByName('AUTHORIZE_AIM_SANDBOX');
        Configuration::deleteByName('AUTHORIZE_AIM_TEST_MODE');
        Configuration::deleteByName('AUTHORIZE_AIM_CARD_VISA');
        Configuration::deleteByName('AUTHORIZE_AIM_CARD_MASTERCARD');
        Configuration::deleteByName('AUTHORIZE_AIM_CARD_DISCOVER');
        Configuration::deleteByName('AUTHORIZE_AIM_CARD_AX');
        Configuration::deleteByName('AUTHORIZE_AIM_HOLD_REVIEW_OS');

        /* Removing credentials configuration variables */
        $currencies = Currency::getCurrencies(false, true);
        foreach ($currencies as $currency) {
            if (in_array($currency['iso_code'], $this->aimAvailableCurrencies)) {
                Configuration::deleteByName('AUTHORIZE_AIM_LOGIN_ID_'.$currency['iso_code']);
                Configuration::deleteByName('AUTHORIZE_AIM_KEY_'.$currency['iso_code']);
            }
        }

        return parent::uninstall();
    }

    /**
     * @param array $params
     *
     * @return string|void
     */
    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder']->module != $this->name) {
            return;
        }

        if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR')) {
            Configuration::updateValue('AUTHORIZEAIM_CONFIGURATION_OK', true);
            $this->context->smarty->assign(['status' => 'ok', 'id_order' => intval($params['objOrder']->id)]);
        } else {
            $this->context->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    /**
     * Hook to back office header
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJQuery();
        $this->context->controller->addJqueryPlugin('fancybox');

        $this->context->controller->addJS($this->_path.'js/authorizeaim.js');
        $this->context->controller->addCSS($this->_path.'css/authorizeaim.css');
    }

    /**
     * Get module configuration page
     *
     * @return string
     */
    public function getContent()
    {
        $html = '';

        if (Tools::isSubmit('submitModule')) {
            $authorizeaimMode = (int) Tools::getvalue('authorizeaim_mode');
            // Sandbox environment
            if ($authorizeaimMode == 2) {
                Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 0);
                Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 1);
            } // Production environment + test mode
            else {
                if ($authorizeaimMode == 1) {
                    Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 1);
                    Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 0);
                } // Production environment
                else {
                    Configuration::updateValue('AUTHORIZE_AIM_TEST_MODE', 0);
                    Configuration::updateValue('AUTHORIZE_AIM_SANDBOX', 0);
                }
            }

            Configuration::updateValue('AUTHORIZE_AIM_CARD_VISA', Tools::getvalue('authorizeaim_card_visa'));
            Configuration::updateValue('AUTHORIZE_AIM_CARD_MASTERCARD', Tools::getvalue('authorizeaim_card_mastercard'));
            Configuration::updateValue('AUTHORIZE_AIM_CARD_DISCOVER', Tools::getvalue('authorizeaim_card_discover'));
            Configuration::updateValue('AUTHORIZE_AIM_CARD_AX', Tools::getvalue('authorizeaim_card_ax'));
            Configuration::updateValue('AUTHORIZE_AIM_HOLD_REVIEW_OS', Tools::getvalue('authorizeaim_hold_review_os'));

            /* Updating credentials for each active currency */
            foreach ($_POST as $key => $value) {
                if (strstr($key, 'authorizeaim_login_id_')) {
                    Configuration::updateValue('AUTHORIZE_AIM_LOGIN_ID_'.str_replace('authorizeaim_login_id_', '', $key), $value);
                } elseif (strstr($key, 'authorizeaim_key_')) {
                    Configuration::updateValue('AUTHORIZE_AIM_KEY_'.str_replace('authorizeaim_key_', '', $key), $value);
                }
            }

            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }

        // For "Hold for Review" order status
        $currencies = Currency::getCurrencies(false, true);
        $orderStates = OrderState::getOrderStates((int) $this->context->cookie->id_lang);

        $this->context->smarty->assign(
            [
                'available_currencies' => $this->aimAvailableCurrencies,
                'currencies'           => $currencies,
                'module_dir'           => $this->_path,
                'order_states'         => $orderStates,

                'AUTHORIZE_AIM_TEST_MODE' => (bool) Configuration::get('AUTHORIZE_AIM_TEST_MODE'),
                'AUTHORIZE_AIM_SANDBOX'   => (bool) Configuration::get('AUTHORIZE_AIM_SANDBOX'),

                'AUTHORIZE_AIM_CARD_VISA'       => Configuration::get('AUTHORIZE_AIM_CARD_VISA'),
                'AUTHORIZE_AIM_CARD_MASTERCARD' => Configuration::get('AUTHORIZE_AIM_CARD_MASTERCARD'),
                'AUTHORIZE_AIM_CARD_DISCOVER'   => Configuration::get('AUTHORIZE_AIM_CARD_DISCOVER'),
                'AUTHORIZE_AIM_CARD_AX'         => Configuration::get('AUTHORIZE_AIM_CARD_AX'),
                'AUTHORIZE_AIM_HOLD_REVIEW_OS'  => (int) Configuration::get('AUTHORIZE_AIM_HOLD_REVIEW_OS'),
                'PS_SSL_ENABLED'                => (int) Configuration::get('PS_SSL_ENABLED'),
            ]
        );

        /* Determine which currencies are enabled on the store and supported by Authorize.net & list one credentials section per available currency */
        foreach ($currencies as $currency) {
            if (in_array($currency['iso_code'], $this->aimAvailableCurrencies)) {
                $configurationIdName = 'AUTHORIZE_AIM_LOGIN_ID_'.$currency['iso_code'];
                $configurationKeyName = 'AUTHORIZE_AIM_KEY_'.$currency['iso_code'];
                $this->context->smarty->assign($configurationIdName, Configuration::get($configurationIdName));
                $this->context->smarty->assign($configurationKeyName, Configuration::get($configurationKeyName));
            }
        }

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
    }

    /**
     * @param array $params
     *
     * @return bool|string
     */
    public function hookPayment($params)
    {
        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);

        if (!Validate::isLoadedObject($currency)) {
            return false;
        }

        //	if (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off'))
        //	{
        $isFailed = Tools::getValue('aimerror');

        $cards = [];
        $cards['visa'] = Configuration::get('AUTHORIZE_AIM_CARD_VISA') == 'on';
        $cards['mastercard'] = Configuration::get('AUTHORIZE_AIM_CARD_MASTERCARD') == 'on';
        $cards['discover'] = Configuration::get('AUTHORIZE_AIM_CARD_DISCOVER') == 'on';
        $cards['ax'] = Configuration::get('AUTHORIZE_AIM_CARD_AX') == 'on';

        if (method_exists('Tools', 'getShopDomainSsl')) {
            $url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$this->name.'/';
        } else {
            $url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';
        }

        $this->context->smarty->assign('x_invoice_num', (int) $params['cart']->id);
        $this->context->smarty->assign('cards', $cards);
        $this->context->smarty->assign('isFailed', $isFailed);
        $this->context->smarty->assign('new_base_dir', $url);
        $this->context->smarty->assign('currency', $currency);

        return $this->display(__FILE__, 'views/templates/hook/authorizeaim.tpl');
    }

    /**
     * Hook to fo header
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'css/aim.css');
        $this->context->controller->addCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css');
        $this->context->controller->addCSS(_THEME_CSS_DIR_.'product.css');
        $this->context->controller->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
        $this->context->controller->addJqueryPlugin('fancybox');
        if (_PS_VERSION_ < '1.5') {
            Tools::addJS(_PS_JS_DIR_.'jquery/jquery.validate.creditcard2-1.0.1.js');
        } else {
            $this->context->controller->addJqueryPlugin('validate-creditcard');
        }
    }

    /**
     * Set the detail of a payment - Call before the validate order init
     * correctly the pcc object
     * See Authorize documentation to know the associated key => value
     *
     * @param array $response
     */
    public function setTransactionDetail($response)
    {
        // If Exist we can store the details
        if (isset($this->pcc)) {
            $this->pcc->transaction_id = (string) $response[6];

            // 50 => Card number (XXXX0000)
            $this->pcc->card_number = (string) substr($response[50], -4);

            // 51 => Card Mark (Visa, Master card)
            $this->pcc->card_brand = (string) $response[51];

            $this->pcc->card_expiration = (string) Tools::getValue('x_exp_date');

            // 68 => Owner name
            $this->pcc->card_holder = (string) $response[68];
        }
    }
}
