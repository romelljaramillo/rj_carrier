<?php
/**
 * 2016-2018 TIPSA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@tip-sa.com>
 *  @copyright 2016-2018 TIPSA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

class AdminTDInfoAgenciaEspController extends ModuleAdminController
{
    protected $statuses_array = array();
    
    public function __construct()
    {
        
        $this->bootstrap = true;
        $this->display = 'list';
        $this->lang = false;
        parent::__construct();
        $this->context = Context::getContext();
        $this->table = 'order';
        $this->className = 'Order';
        $this->allow_export = true;
        $this->deleted = false;
        $this->identifier = 'id_order';
        $this->_defaultOrderBy = 'id_order';
        $this->_defaultOrderWay = 'ASC';

        // if (!$this->module->active) {
        //     Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        // }

        $this->addRowAction('view');

        $this->bulk_actions = array(
            'generateLabel' => array(
                'text' => $this->l('Generate labels'),
                'confirm' => $this->l('Genereate labels for selected items?'),
                'icon' => 'icon-edit'
            ),
            'updateStatus' => array(
                'text' => $this->l('Update Status'),
                'confirm' => $this->l('Update status for selected items?'),
                'icon' => 'icon-edit'
            )            
        );

        $this->getOrders();


    }

    protected function getOrders()
    {

        $this->_select = '
        CONCAT(cu.`firstname`, \' \' , cu.`lastname`) as `customer`,
        os.`paid`,
        osl.`name` AS `osname`,
        cur.`iso_code`,
        cu.`id_customer` IS NULL as `deleted_customer`,
        os.`color`,
        s.`name` AS `shop_name`,
        cu.`company`,
        cl.`name` AS `country_name`,
        ad.`address1`,
        ad.`postcode`,
        ad.`phone`,
        ad.`phone_mobile`,
        es.`name` as `state`,
        cu.`email`,
        DATE_FORMAT(CURDATE(),"%d-%m-%Y") as date,
        IF ((SELECT so.id_order FROM `'._DB_PREFIX_.'orders` so 
        WHERE (so.id_customer = a.id_customer) 
        AND (so.id_order < a.id_order) 
        LIMIT 1 ) > 0, 0, 1 ) AS new';
                    
        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` cu ON a.id_customer = cu.id_customer
        LEFT JOIN `'._DB_PREFIX_.'currency` cur ON a.id_currency = cur.id_currency
        LEFT JOIN `'._DB_PREFIX_.'address` ad ON a.id_address_delivery = ad.id_address
        LEFT JOIN `'._DB_PREFIX_.'state` es ON ad.id_state = es.id_state
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON a.current_state = os.id_order_state
        LEFT JOIN `'._DB_PREFIX_.'shop` s ON a.id_shop = s.id_shop
        LEFT JOIN `'._DB_PREFIX_.'country` c ON ad.id_country = c.id_country
        LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON c.id_country = cl.id_country AND cl.id_lang = '.(int)$this->context->language->id.'
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
        
        $this->_orderBy = 'a.id_order';
        $this->_orderWay = 'DESC';

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);

        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'reference' => array(
                'title' => $this->l('Reference')
            ),
            'new' => array(
                'title' => $this->l('New client'),
                'align' => 'text-center',
                'type' => 'bool',
                'tmpTableFilter' => true,
                'orderby' => false,
                'callback' => 'printNewCustomer'
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            )
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->l('Company'),
                    'filter_key' => 'c!company'
                ),
            ));
        }

        $this->fields_list = array_merge(
            $this->fields_list, array(
                'total_paid_tax_incl' => array(
                    'title' => $this->l('Total'),
                    'align' => 'text-right',
                    'type' => 'price',
                    'currency' => true,
                    'callback' => 'setOrderCurrency',
                    'badge_success' => true
                ),
                'payment' => array(
                    'title' => $this->l('Payment')
                ),
                'osname' => array(
                    'title' => $this->l('Status'),
                    'type' => 'select',
                    'color' => 'color',
                    'list' => $this->statuses_array,
                    'filter_key' => 'os!id_order_state',
                    'filter_type' => 'int',
                    'order_key' => 'osname'
                ),
                'date_add' => array(
                    'title' => $this->l('Date'),
                    'align' => 'text-right',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add'
                ),
                'id_pdf' => array(
                    'title' => $this->l('PDF'),
                    'align' => 'text-center',
                    'callback' => 'printPDFIcons',
                    'orderby' => false,
                    'search' => false,
                    'remove_onclick' => true
                )              
            )
        );
    }

    public function printNewCustomer($id_order, $tr)
    {
        return ($tr['new'] ? $this->l('Yes') : $this->l('No'));
    }

    public static function setOrderCurrency($echo, $tr)
    {
        $order = new Order($tr['id_order']);
        return Tools::displayPrice($echo, (int)$order->id_currency);
    }

    public function printPDFIcons($id_order, $tr)
    {
        static $valid_order_state = array();

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        if (!isset($valid_order_state[$order->current_state])) {
            $valid_order_state[$order->current_state] = Validate::isLoadedObject($order->getCurrentOrderState());
        }

        if (!$valid_order_state[$order->current_state]) {
            return '';
        }

        $this->context->smarty->assign(array(
            'order' => $order,
            'tr' => $tr
        ));

        return $this->createTemplate('_print_pdf_icon.tpl')->fetch();
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function renderView()
    {
        return parent::renderView();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_turnotipo'] = array(
                'href' => self::$currentIndex.'&addmiplanning_turno_tipo&token='.$this->token,
                'desc' => $this->l('Add new Type of shift'),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }   

    public function postProcess()
    {
      return parent::postProcess();
    }


    /**
     * @param string $text_delimiter
     *
     * @throws PrestaShopException
     */
    public function processExport($text_delimiter = '"')
    {
        $this->fields_list = array(
            'date' => array(
                'title' => $this->l('FECH.PREF')
                ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('A COBRAR'),
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true
                ),
            'id_order' => array(
                'title' => $this->l('DOC.VENTA')
                ),
            'customer' => array(
                'title' => $this->l('NOM.CLIENTE.')
                ),
            'address1' => array(
                'title' => $this->l('DIRECCION')
                ),
            'postcode' => array(
                'title' => $this->l('C. POSTAL')
                ),
            'state' => array(
                'title' => $this->l('POBLACION')
                ),
            'reference' => array(
                'title' => $this->l('DESCRIP.')
                ),
            'CANTIDAD' => array(
                'title' => $this->l('CANTIDAD')
                ),
            'centro' => array(
                'title' => $this->l('CENTRO')
                ),
            'phone_mobile' => array(
                'title' => $this->l('TELEFONO1')
                ),
            'phone' => array(
                'title' => $this->l('TELEFONO 2')
                ),
            'OBSERVACIONES' => array(
                'title' => $this->l('OBSERVACIONES')
                ),
            'USADO/RAEE' => array(
                'title' => $this->l('USADO/RAEE')
                ),
            'ARRASTRE' => array(
                'title' => $this->l('ARRASTRE')
                ),
            'VERIFICADO' => array(
                'title' => $this->l('VERIFICADO')
                ),
            'PLANIFICADO' => array(
                'title' => $this->l('PLANIFICADO')
                ),
            'LLAMADO' => array(
                'title' => $this->l('LLAMADO')
                ),
            'CAMION' => array(
                'title' => $this->l('CAMION')
                ),
            'CARGADO' => array(
                'title' => $this->l('CARGADO')
                ),
            'HORA' => array(
                'title' => $this->l('HORA')
                ),
            'INICIO' => array(
                'title' => $this->l('INICIO')
                ),
            'HORA_FIN' => array(
                'title' => $this->l('HORA FIN')
                ),
            'EAN' => array(
                'title' => $this->l('EAN')
                ),
            'PROCEDENCIA' => array(
                'title' => $this->l('PROCEDENCIA')
                ),
            'TIPOSERVICIO' => array(
                'title' => $this->l('TIPOSERVICIO')
                ),
            'BULTOS' => array(
                'title' => $this->l('BULTOS')
                ),
            'VOLUMEN' => array(
                'title' => $this->l('VOLUMEN KILOS')
                ),
            'NUMERO_RESERVA' => array(
                'title' => $this->l('NUMERO_RESERVA')
                ),
            'email' => array(
                'title' => $this->l('EMAIL')
                ),
            'state' => array(
                'title' => $this->l('PROVINCIA')
                ),
            'VALOR' => array(
                'title' => $this->l('VALOR MERNCANCIA')
                )
        );
        
        return parent::processExport();
    }

    // public function renderForm()
    // {  

    //     return parent::renderForm();
    // }
    

}
