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
use Roanja\Module\RjCarrier\Model\RjcarrierInfoPackage;
use Roanja\Module\RjCarrier\Model\RjcarrierShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;

class AdminRjShipmentGenerateController extends ModuleAdminController
{
    
    private $id_order_pack;

    public function __construct()
    {

        $this->bootstrap = true;
        $this->lang = false;

        $this->table = 'rj_carrier_infopackage';
        $this->className = 'Roanja\Module\RjCarrier\Model\RjcarrierInfoPackage';
        $this->actions = ['generatelabel', 'delete'];

        parent::__construct();

        $this->allow_export = true;
        $this->deleted = false;
        $this->identifier = 'id_infopackage';

        $this->context = \Context::getContext();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->bulk_actions = [
            'generateLabel' => [
                'text' => $this->l('Generate labels'),
                'confirm' => $this->l('Genereate labels for selected items?'),
                'icon' => 'icon-tag'
            ]
        ];

        $this->getFieldsList();
    }
    
    /**
     * @param string $token
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public function displayGeneratelabelLink($token = null, $id_infopackage, $name = null)
    {
        $id_shipmet = RjcarrierShipment::shipmentExistsByIdInfopackage((int)$id_infopackage);
        $printed = RjcarrierLabel::isPrintedIdShipment((int)$id_shipmet);

        if($id_shipmet){
            $action = 'printlabel';
            $action_lang = $this->l('Print Label');
        } else {
            $action = 'generateLabel';
            $action_lang = $this->l('Generate Label');
        }

        $tpl = $this->createTemplate('helpers/list/list_action_generatelabel.tpl');
        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Generatelabel'] = $action_lang;
        }

        $tpl->assign([
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id_infopackage . '&' . $action . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Generatelabel'],
            'printed' => $printed,
            'shipmet_active' => (boolean)$id_shipmet
        ]);

        return $tpl->fetch();
    }

    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = $this->l('You do not have permission to view this.');

            return;
        }

        if ($this->display == 'edit' || $this->display == 'add') {
            if (!$this->loadObject(true)) {
                return;
            }

            $this->content .= $this->renderForm();
        } elseif ($this->display == 'view') {
            // Some controllers use the view action without an object
            if ($this->className) {
                $this->loadObject(true);
            }
            $this->content .= $this->renderView();
        } elseif ($this->display == 'details') {
            $this->content .= $this->renderDetails();
        } elseif (!$this->ajax) {
            // FIXME: Sorry. I'm not very proud of this, but no choice... Please wait sf refactoring to solve this.
            if (get_class($this) != 'AdminCarriersController') {
                $this->content .= $this->renderModulesList();
            }
            $this->content .= $this->renderKpis();

            if ($this->display == 'vieworder'){
                $this->content .= '<div class="row"><div class="col-md-8">';
                $this->content .= $this->renderList();
                $this->content .= '</div>';
                $this->content .= '<div class="col-md-4">';
                $this->content .= $this->renderOrderResumen($this->id_order_pack, $this->id_infopackage);
                $this->content .= '</div></div>';
            } else {
                $this->content .= $this->renderList();
            }

            $this->content .= $this->renderOptions();

            // if we have to display the required fields form
            if ($this->required_database) {
                $this->content .= $this->displayRequiredFields();
            }
        }

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    /**
     * renderiza vista de la información de productos del pedido
     *
     * @param int $id_order
     * @return void
     */
    public function renderOrderResumen($id_order, $id_infopackage)
    {
        $order_detail = OrderDetail::getList($id_order);
        $id_shipmet = RjcarrierShipment::shipmentExistsByIdInfopackage((int)$id_infopackage);
        $printed = RjcarrierLabel::isPrintedIdShipment((int)$id_shipmet);

        if($id_shipmet){
            $action = 'printlabel';
            $action_lang = $this->l('Print Label');
        } else {
            $action = 'generateLabel';
            $action_lang = $this->l('Generate Label');
        }


        $tpl = $this->createTemplate('order-detail.tpl');

        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Generatelabel'] = $action_lang;
        }

        $tpl->assign([
            'link' => $this->context->link,
            'id_infopackage' => (int)$id_infopackage,
            'id_order' => $id_order,
            'order_detail' => $order_detail,
            'printed' => $printed,
            'shipmet_active' => (boolean)$id_shipmet,
            'action' => $action,
            'action_label' => self::$cache_lang['Generatelabel']
        ]);

        return $tpl->fetch();       
    }

    public function postProcess()
    {
        if (Tools::isSubmit('update' . $this->table)) {
            if(Tools::getValue('id_order')){
                $this->id_order_pack = (int)Tools::getValue('id_order');
                $rjcarrier_infopack = RjcarrierInfoPackage::getPackageByIdOrder($this->id_order_pack, $this->context->shop->id);
                $this->id_infopackage = $rjcarrier_infopack['id_infopackage'];
            } else {
                $rjcarrier_infopack = new RjcarrierInfoPackage(Tools::getValue($this->identifier));
                $this->id_infopackage = $rjcarrier_infopack->id;
                $this->id_order_pack = (int)$rjcarrier_infopack->id_order;
            }

            $this->display = 'vieworder';

        } elseif (Tools::isSubmit('generateLabel')) {
            $this->module->generateLabel((int) Tools::getValue($this->identifier));
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminRjShipmentGenerate', true, [], ['conf' => 3]));
        } elseif (Tools::isSubmit('printlabel')) {
            $id_shipmet = RjcarrierShipment::shipmentExistsByIdInfopackage((int)Tools::getValue($this->identifier));
            LabelController::printLabelsShipment((int)$id_shipmet);
        } elseif (Tools::isSubmit('deleterj_carrier_infopackage')){
            $id_shipment = RjcarrierShipment::shipmentExistsByIdInfopackage(Tools::getValue($this->identifier));
            if(!$id_shipment){
                $this->errors[] = $this->l('The label has already been removed, try to generate it again.');
                return false;
            }
            
            $rjcarrier_shipment = new RjcarrierShipment((int)$id_shipment);

            if(!$rjcarrier_shipment->delete()){
                $this->errors[] = $this->l('Something happened trying to remove the label');
                return false;
            }
                
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminRjShipmentGenerate', true, [], ['conf' => 1]));
        }

        return parent::postProcess();
    }

    protected function processBulkGenerateLabel()
    {
        if ($ids_infopackage = Tools::getValue('rj_carrier_infopackageBox')) {
            foreach ($ids_infopackage as $id_infopackage) {
                $id_shipment = RjcarrierShipment::shipmentExistsByIdInfopackage((int)$id_infopackage);
                if(!$id_shipment){
                    $this->module->generateLabel((int)$id_infopackage);
                }
            }
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminRjShipmentGenerate', true, [], ['conf' => 3]));
    }

    protected function querySql(){
        $this->_select = "a.id_order,
                            c.name,
                            a.quantity,
                            a.cash_ondelivery,
                            a.weight";


        $this->_join = " INNER JOIN `"._DB_PREFIX_."carrier` c ON a.id_reference_carrier = c.id_reference AND deleted = 0";
        $this->_join .= " LEFT JOIN `"._DB_PREFIX_."rj_carrier_shipment` b ON a.id_infopackage = b.id_infopackage";
        $this->_where = " AND b.id_infopackage is null 
                        OR (b.delete=1 AND b.id_infopackage NOT IN (
                            SELECT d.id_infopackage                  
                            FROM ps_rj_carrier_shipment d            
                            WHERE d.id_infopackage = a.id_infopackage
                            AND d.`delete` = 0)
                        )                     
                        OR b.`delete` = 0";
        $this->_group = " GROUP BY a.id_infopackage, a.id_order";
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
            'name' => [
                'title' => $this->l('Transport'),
                'havingFilter' => true,
                'filter_key' => 'c!name',
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
                'type' => 'date',
                'filter_key' => 'a!date_add',
            ],
        ];
    }
}
