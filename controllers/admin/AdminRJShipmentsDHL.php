<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the GNU General Public License, version 3 (GPL-3.0).
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* @author    Roanja www.roanja.com <info@roanja.com>
* @copyright 2021 Roanja.com
* @license   https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
*/
include_once _PS_MODULE_DIR_ . 'rj_carrier/controllers/admin/AdminRJLabelController.php';
class AdminRJShipmentsDHLController extends ModuleAdminController
{
    protected $statuses_array = array();
    protected $nameInforme = 'shipments';

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
        if (Tools::isSubmit('update' . $this->table)) {
            $rjcarrierShipment = new RjcarrierShipment(Tools::getValue('id_shipment'));
            $parameters = ['vieworder' => 1, 'id_order' => (int) $rjcarrierShipment->id_order];
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], $parameters));
        }

        if (Tools::isSubmit('printlabel' . $this->table)) {
            $resp = AdminRJLabelController::printLabelsShipment(Tools::getValue($this->identifier));
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
                $res = AdminRJLabelController::downloadLabelsShipment($id_shipment);
                if(!$res)
                    return $res;
                    
            }
        }
    }

    protected function querySql(){
        $this->_select = "a.id_shipment,
                            a.id_order,
                            a.reference_order,
                            a.num_shipment,
                            a.product,
                            a.cash_ondelivery,
                            pk.quantity,
                            pk.weight,
                            a.date_add";
        $this->_join = " INNER JOIN `"._DB_PREFIX_."rj_carrier_infopackage` pk ON a.id_infopackage = pk.id_infopackage";
        $this->_where = ' AND a.delete=0';
    }

    protected function getFieldsList()
    {
        $this->querySql();

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('Nº Order'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'filter_key' => 'a!id_order'
            ),
            'reference_order' => array(
                'title' => $this->l('order reference DHL'),
                'havingFilter' => true,
                'search' =>false,
            ),
            'id_shipment' => array(
                'title' => $this->l('id Envío'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'search' =>false,
            ),
            'num_shipment' => array(
                'title' => $this->l('Id DHL'),
                'havingFilter' => true,
                'search' =>false,
            ),
            'product' => array(
                'title' => $this->l('Product DHL'),
                'havingFilter' => true,
                'search' =>false,
            ),
            'cash_ondelivery' => array(
                'title' => $this->l('Contrareembolso'),
                'havingFilter' => true,
                'type' => 'price',
                'filter_key' => 'pk!cashOndelivery',
            ),
            'quantity' => array(
                'title' => $this->l('Packages'),
                'havingFilter' => true,
                'search' =>false,
            ),
            'weight' => array(
                'title' => $this->l('Weight'),
                'havingFilter' => true,
                'type' => 'decimal',
                'search' =>false,
            ),
            'date_add' => array(
                'title' => $this->l('Fecha'),
                'havingFilter' => true,
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );
    }

    public function getInfopackCashOndelivery($echo, $tr)
    {
        $rjcarrierInfoPackage = new RjcarrierShipment((int)$tr['id_infopackage']);

        return Tools::displayPrice($rjcarrierInfoPackage->cash_ondelivery);
    }

    public function getInfopackPackcage($echo, $tr)
    {
        $rjCarrierInfoPackage = new RjcarrierInfoPackage((int)$tr['id_infopackage']);

        return $rjCarrierInfoPackage->quantity;
    }

    public function renderList()
    {
        // $this->_select = '0 as printed';
        $this->addRowAction('printlabel');
        $this->actions = array('printlabel', 'delete');
        return parent::renderList();
    }

}
