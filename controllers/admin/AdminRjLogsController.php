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
use Roanja\Module\RjCarrier\Model\RjcarrierLog;

class AdminRjLogsController extends ModuleAdminController
{
    protected $statuses_array = [];
    protected $nameInforme = 'logs';

    public function __construct()
    {

        $this->bootstrap = true;
        $this->lang = false;
        
        $this->table = 'rj_carrier_log';
        $this->className = 'Roanja\Module\RjCarrier\Model\RjcarrierLog';
        $this->actions = ['view','delete'];

        parent::__construct();

        $this->allow_export = true;
        $this->deleted = false;
        $this->identifier = 'id_carrier_log';

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
    
    public function postProcess()
    {
        if (Tools::isSubmit('view' . $this->table)) {
            $this->content = $this->renderViewLog(Tools::getValue('id_carrier_log'));
        }

        return parent::postProcess();
    }

    protected function querySql()
    {
        $this->_select = "a.id_carrier_log,
                            a.id_order,
                            a.name,
                            a.request,
                            a.response,
                            a.date_add,
                            a.date_upd";

        $this->_defaultOrderBy = 'id_carrier_log';
        $this->_defaultOrderWay = 'DESC';
    }

    protected function getFieldsList()
    {
        $this->querySql();

        $this->fields_list = [
            'id_carrier_log' => [
                'title' => $this->l('id_carrier_log'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'filter_key' => 'a!id_carrier_log'
            ],
            'id_order' => [
                'title' => $this->l('id_order'),
                'havingFilter' => true,
                'filter_key' => 'cc!id_order',
            ],
            'name' => [
                'title' => $this->l('name'),
                'havingFilter' => true,
                'filter_key' => 'a!name'
            ],
            'response' => [
                'title' => $this->l('response'),
                'havingFilter' => true,
                'filter_key' => 'a!response',
            ],
            'date_add' => [
                'title' => $this->l('date_add'),
                'havingFilter' => true,
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => $this->l('date_upd'),
                'havingFilter' => true,
                'type' => 'datetime',
                'filter_key' => 'a!date_upd',
            ],
        ];
    }

    public function renderViewLog($id_carrier_log)
    {
        $rjcarrierLog = new RjcarrierLog($id_carrier_log);
        $tpl = $this->createTemplate('order-detail.tpl');
        $tpl = $this->createTemplate('view-log.tpl');
        $tpl->assign([
            'link' => $this->context->link,
            'log' => $rjcarrierLog->getFields(),
        ]);

        return  $tpl->fetch(); 
    }
}
