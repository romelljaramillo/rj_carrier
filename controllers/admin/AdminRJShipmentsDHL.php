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
include_once _PS_MODULE_DIR_ . 'rj_carrier/controllers/admin/AdminRJCarrierController.php';
class AdminRJShipmentsDHLController extends ModuleAdminController
{
    protected $statuses_array = array();
    protected $nameInforme = 'shipments_dhl';

    public function __construct()
    {

        $this->bootstrap = true;
        $this->lang = false;
        $this->addRowAction('view');
        $this->table = 'rj_carrier_shipment';
        $this->className = 'RjcarrierShipment';
        parent::__construct();

        $this->allow_export = true;
        $this->deleted = false;
        $this->identifier = 'id_shipment';
        $this->_defaultOrderBy = 'id_shipment';
        $this->_defaultOrderWay = 'ASC';
        $this->context = Context::getContext();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Modules.Rj_carrier.Admin'),
                'confirm' => $this->trans('Delete selected items?', array(), 'Modules.Rj_carrier.Admin'),
                'icon' => 'icon-trash',
            ),
        );

        $this->getFieldsList();

    }
    /**
     * @param string $token
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public function displayPrintlabelLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_printlabel.tpl');
        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Printlabel'] = $this->trans('Print Label', array(), 'Modules.Rj_carrier.Admin');
        }
        $printed = RjcarrierLabel::isPrintedIdShipment((int)$id);
        $tpl->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&printlabel' . $this->table . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Printlabel'],
            'printed' => $printed
        ));

        return $tpl->fetch();
    }

    public function postProcess()
    {
        // $_GET;
        // $_POST;

        if (Tools::isSubmit('update' . $this->table)) {
            $rjcarrierShipment = new RjcarrierShipment(Tools::getValue('id_shipment'));
            $parameters = ['vieworder' => 1, 'id_order' => (int) $rjcarrierShipment->id_order];
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], $parameters));
        }

        if (Tools::isSubmit('printlabel' . $this->table)) {
            $resp = AdminRJCarrierController::printLabelsShipment(Tools::getValue($this->identifier));
        }

        if (Tools::isSubmit('updatestatus' . $this->table)) {
            $this->module->updateStatus((int) Tools::getValue($this->identifier));
        }

        return parent::postProcess();
    }

    protected function processBulkGenerateLabel()
    {

        if ($shipments = Tools::getValue('rj_carrier_shipmentBox')) {
            foreach ($shipments as $id_shipment) {
                $res = AdminRJCarrierController::downloadLabelsShipment($id_shipment);
                if(!$res)
                    return $res;
                    
            }
        }
    }

    protected function getFieldsList()
    {
        $this->_where = ' AND a.`delete`=0';
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('id_order'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
            ),
            'id_shipment' => array(
                'title' => $this->l('id'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
            ),
            'shipmentid' => array(
                'title' => $this->l('shipmentid'),
                'havingFilter' => true,
            ),
            'product' => array(
                'title' => $this->l('product'),
                'havingFilter' => true,
            ),
            'id_rjcarrier' => array(
                'title' => $this->l('Carrier'),
                'havingFilter' => true,
                'align' => 'text-right',
                'callback' => 'getCarrierShipment',
            ),
            'order_reference' => array(
                'title' => $this->l('order_reference'),
                'havingFilter' => true,
                'align' => 'text-right',
            ),
            'date_add' => array(
                'title' => $this->l('date_add'),
                'havingFilter' => true,
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );
    }

    public function getCarrierShipment($echo, $tr)
    {
        $rjcarrier = new RjCarrier((int)$tr['id_rjcarrier']);
        $carrier = Carrier::getCarrierByReference((int)$rjcarrier->id_reference_carrier);
        return $carrier->name;
    }

    public function renderList()
    {
        // $this->_select = '0 as printed';
        $this->addRowAction('printlabel');
        $this->actions = array('printlabel', 'delete');
        return parent::renderList();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_turnotipo'] = array(
                'href' => self::$currentIndex . '&addmiplanning_turno_tipo&token=' . $this->token,
                'desc' => $this->l('Add new Type of shift'),
                'icon' => 'process-icon-new',
            );
        }

        parent::initPageHeaderToolbar();
    }
}
