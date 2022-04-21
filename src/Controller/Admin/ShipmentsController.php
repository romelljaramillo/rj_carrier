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

namespace Roanja\Module\RjCarrier\Controller\Admin;

use Roanja\Module\RjCarrier\Controller\Admin\LabelController;
use Roanja\Module\RjCarrier\Model\RjcarrierShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Tools;

/**
 * Class ShipmentsController.
 *
 * @ModuleActivated(moduleName="rj_carrier", redirectRoute="admin_module_manage")
 */
class ShipmentsController extends FrameworkBundleAdminController
{
    protected $statuses_array = array();
    protected $nameInforme = 'shipments';

    // public function __construct()
    // {

    //     $this->bootstrap = true;
    //     $this->lang = false;
    //     // $this->addRowAction('view');
    //     $this->table = 'rj_carrier_shipment';
    //     $this->className = 'RjcarrierShipment';
    //     parent::__construct();

    //     $this->allow_export = true;
    //     $this->deleted = false;
    //     $this->identifier = 'id_shipment';
    //     $this->_defaultOrderBy = 'id_shipment';
    //     $this->_defaultOrderWay = 'DESC';
    //     $this->context = \Context::getContext();

    //     $this->bulk_actions = array(
    //         'delete' => array(
    //             'text' => $this->trans('Delete selected', 'Modules.Rj_carrier.Admin'),
    //             'confirm' => $this->trans('Delete selected items?', 'Modules.Rj_carrier.Admin'),
    //             'icon' => 'icon-trash',
    //         ),
    //     );

    //     $this->getFieldsList();

    // }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        echo 'test';
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
            self::$cache_lang['Printlabel'] = $this->trans('Print Label', 'Modules.Rj_carrier.Admin');
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
            $resp = LabelController::printLabelsShipment(Tools::getValue($this->identifier));
        }

        if (Tools::isSubmit('updatestatus' . $this->table)) {
            // $this->module->updateStatus((int) Tools::getValue($this->identifier));
        }

        return parent::postProcess();
    }

    protected function processBulkGenerateLabel()
    {

        if ($shipments = Tools::getValue('rj_carrier_shipmentBox')) {
            foreach ($shipments as $id_shipment) {
                $res = LabelController::downloadLabelsShipment($id_shipment);
                if(!$res)
                    return $res;
                    
            }
        }
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
        $this->_where = ' AND a.delete=0 AND a.id_carrier_company = 2';
    }

    protected function getFieldsList()
    {
        $this->querySql();

        $this->fields_list = array(
            'id_shipment' => array(
                'title' => $this->l('id Envío'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'search' =>false,
            ),
            'id_order' => array(
                'title' => $this->l('Nº Order'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'filter_key' => 'a!id_order'
            ),
            'shortname' => array(
                'title' => $this->l('Transport'),
            ),
            'reference_order' => array(
                'title' => $this->l('order reference DHL'),
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
                'type' => 'price'
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

    public function renderList()
    {
        // $this->addRowAction('printlabel');
        $this->actions = array('printlabel', 'delete');
        // return parent::renderList();
    }

}
