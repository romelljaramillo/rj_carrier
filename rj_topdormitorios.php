<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}


class Rj_TopDormitorios extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'rj_topdormitorios';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Roanja';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Informe agencia especializadas');
        $this->description = $this->l('Informe de agencia especializadas Top Dormitorios');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        // Configuration::updateValue('rj_topdormitorios_LIVE_MODE', false);

        // include(dirname(__FILE__).'/sql/install.php');
        
        if (parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminOrderTabOrder')
        ) {

            $this->installTab('AdminParentTabTopDormitorios', 'Top Dormitorio'); 
            $this->installTab('AdminTDModule', 'Configuration', 'AdminParentTabTopDormitorios');
            $this->installTab('AdminTDInfoAgenciaEsp', 'Informe Agencias', 'AdminParentTabTopDormitorios');

            return true;
        }

        return false;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }
        
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstall()
    {
        // Configuration::deleteByName('rj_topdormitorios_LIVE_MODE');

        // include(dirname(__FILE__).'/sql/uninstall.php');
        $res = $this->uninstallTab('AdminParentTabTopDormitorios');
        $res &= $this->uninstallTab('AdminTDModule');
        $res &= $this->uninstallTab('AdminTDInfoAgenciaEsp');

        return parent::uninstall();
    }

    public function uninstallTab($tabName = '')
    {
        //$tab_class = Tools::ucfirst($this->name) . Tools::ucfirst($class_sfx);
        $id_tab    = Tab::getIdFromClassName($tabName);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitModulePay')) {
            $this->_postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        // $output .= $this->renderForm();

        // $output .= $this->renderList();

        return $output;
    }
    protected function _postProcess()
    {

    }



    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    /* protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitrj_topdormitoriosModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    } */

    /**
     * Create the structure of your form.
     */
    /* protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'rj_topdormitorios_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'rj_topdormitorios_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'rj_topdormitorios_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    } */

    /**
     * Set values for the inputs.
     */
    /* protected function getConfigFormValues()
    {
        return array(
            'rj_topdormitorios_LIVE_MODE' => Configuration::get('rj_topdormitorios_LIVE_MODE', true),
            'rj_topdormitorios_ACCOUNT_EMAIL' => Configuration::get('rj_topdormitorios_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'rj_topdormitorios_ACCOUNT_PASSWORD' => Configuration::get('rj_topdormitorios_ACCOUNT_PASSWORD', null),
        );
    } */

    /* protected function getOrders()
    {
        // obtengo multi-tienda
        $context = Context::getContext();
		$id_shop = $context->shop->id;

        // obtengo lenguaje
        $id_lang = (int)Context::getContext()->language->id;
        
        $dbquery = new DbQuery();
        $dbquery->select('CONCAT(cu.`firstname`, " " , cu.`lastname`) as `customer`,o.`id_order`,o.`reference`,o.`total_paid_tax_incl`,os.`paid`,osl.`name` AS `osname`,o.`id_currency`,cur.`iso_code`,o.`current_state`,o.`id_customer`,cu.`id_customer` IS NULL as `deleted_customer`,os.`color`,o.`payment`,s.`name` AS `shop_name`,o.`date_add`,cu.`company`,cl.`name` AS `country_name`,o.`invoice_number`,o.`delivery_number`,
        IF ((SELECT so.id_order FROM '._DB_PREFIX_.'orders so WHERE (so.id_customer = o.id_customer) AND (so.id_order < o.id_order) LIMIT 1 ) > 0, 0, 1 ) AS new');
        $dbquery->from('orders', 'o');
        $dbquery->leftJoin('customer', 'cu','o.id_customer = cu.id_customer');
        $dbquery->leftJoin('currency', 'cur' , 'o.id_currency = cur.id_currency');
        $dbquery->innerJoin('address', 'a', 'o.id_address_delivery = a.id_address');
        $dbquery->leftJoin('order_state', 'os', 'o.current_state = os.id_order_state');
        $dbquery->leftJoin('shop', 's', 'o.id_shop = s.id_shop');
        $dbquery->innerJoin('country', 'c', 'a.id_country = c.id_country');
        $dbquery->innerJoin('country_lang', 'cl', 'c.id_country = cl.id_country AND cl.id_lang = ' . (int) $id_lang);
        $dbquery->leftJoin('order_state_lang', 'osl', 'os.id_order_state = osl.id_order_state AND osl.id_lang = '.(int)$id_lang);
        $dbquery->where('o.`id_shop` = ' . (int) $id_shop);
        $dbquery->orderBy('o.`id_order` DESC');
        $dbquery->limit('50');
    
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        // dump($row);
        return ($row);
    } */

    /* public function renderList()
    {

        $fields_list = array(
            'id_order' => array(
                'title' => $this->l('Id'),
                'width' => 50,
                'search' => true
            ),
            'customer' => array(
                'title' => $this->l('customer'),
                'width' => 'auto',
                'search' => true
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('total'),
                'width' => 'auto',
                'search' => true
            ),
            'reference' => array(
                'title' => $this->l('reference'),
                'width' => 'auto',
                'search' => true
            ),
        );

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Informe Agencia Especializada');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->title_icon = 'icon-folder';
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_order';
        $helper_list->table = 'order';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name; 
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('viewOrder');

        $ordes = $this->getOrders();
        $helper_list->listTotal = count($ordes);


        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;
        $ordes = $this->paginateOrdes($ordes, $page, $pagination);

        return $helper_list->generateList($ordes, $fields_list);
    } */


    /**
     * Save form data.
     */
    /* protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    } */

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayAdminOrderTabOrder()
    {
        /* Place your code here. */
    }
}
