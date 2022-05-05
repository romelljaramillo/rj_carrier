<?php
/**
 * 2016-2018 ROANJA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <info@roanja.com>
 *  @copyright 2016-2018 ROANJA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

use Roanja\Module\RjCarrier\Controller\Admin\LabelController;
use Roanja\Module\RjCarrier\Model\RjcarrierShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;

class AdminRjShipmentsController extends ModuleAdminController
{
    protected $statuses_array = [];
    protected $nameInforme = 'shipments';

    public function __construct()
    {

        $this->bootstrap = true;
        $this->lang = false;
        
        $this->table = 'rj_carrier_shipment';
        $this->className = 'Roanja\Module\RjCarrier\Model\RjcarrierShipment';
        $this->actions = ['printlabel', 'delete'];

        parent::__construct();

        $this->allow_export = true;
        $this->deleted = false;
        $this->identifier = 'id_shipment';

        $this->context = \Context::getContext();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];

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
            self::$cache_lang['Printlabel'] = $this->l('Print Label');
        }
        $printed = RjcarrierLabel::isPrintedIdShipment((int)$id);
        $tpl->assign([
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&printlabel' . $this->table . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Printlabel'],
            'printed' => $printed
        ]);

        return $tpl->fetch();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('update' . $this->table)) {
            $rjcarrierShipment = new RjcarrierShipment(Tools::getValue('id_shipment'));
            $parameters = ['vieworder' => 1, 'id_order' => (int) $rjcarrierShipment->id_order];
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], $parameters));
        } elseif (Tools::isSubmit('printlabel' . $this->table)) {
            LabelController::printLabelsShipment(Tools::getValue($this->identifier));
        }

        return parent::postProcess();
    }

    protected function querySql(){
        $this->_select = "a.id_shipment,
                            a.id_order,
                            cc.shortname,
                            a.reference_order,
                            a.num_shipment,
                            a.product,
                            ip.cash_ondelivery,
                            ip.quantity,
                            ip.weight,
                            a.date_add";

        $this->_join = " INNER JOIN `"._DB_PREFIX_."rj_carrier_infopackage` ip ON a.id_infopackage = ip.id_infopackage";
        $this->_join .= " INNER JOIN `"._DB_PREFIX_."rj_carrier_company` cc ON a.id_carrier_company = cc.id_carrier_company";
        $this->_where = ' AND a.delete=0';
        $this->_defaultOrderBy = 'id_order';
        $this->_defaultOrderWay = 'DESC';
    }

    protected function getFieldsList()
    {
        $this->querySql();

        $this->fields_list = [
            'id_order' => [
                'title' => $this->l('Nº Order'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'filter_key' => 'a!id_order'
            ],
            'shortname' => [
                'title' => $this->l('Transport'),
                'havingFilter' => true,
                'filter_key' => 'cc!shortname',
            ],
            'reference_order' => [
                'title' => $this->l('order reference DHL'),
                'havingFilter' => true,
                'filter_key' => 'a!reference_order'
            ],
            'num_shipment' => [
                'title' => $this->l('Shipment number'),
                'havingFilter' => true,
                'filter_key' => 'a!num_shipment'
            ],
            'product' => [
                'title' => $this->l('Carrier'),
                'havingFilter' => true,
                'filter_key' => 'a!product',
            ],
            'cash_ondelivery' => [
                'title' => $this->l('Cash ondelivery'),
                'havingFilter' => true,
                'type' => 'price',
                'filter_key' => 'ip!cash_ondelivery',
            ],
            'quantity' => [
                'title' => $this->l('Packages'),
                'havingFilter' => true,
                'search' =>false,
            ],
            'weight' => [
                'title' => $this->l('Weight'),
                'havingFilter' => true,
                'type' => 'decimal',
                'search' =>false,
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'havingFilter' => true,
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];
    }
}
