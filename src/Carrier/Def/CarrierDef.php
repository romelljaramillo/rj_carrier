<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Roanja\Module\RjCarrier\Carrier\Def;

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\CarrierInterface;

use Db;
use Shop;

/**
 * Class CarrierDef
 * Clase predeterminada para transportistas genéricos.
 */
class CarrierDef extends CarrierCompany implements CarrierInterface
{
    public function __construct()
    {
        $this->carrier_name = 'Default Carrier';
        $this->shortname = 'DEF';
        $this->show_create_label = false;
        parent::__construct();
    }

    public function setFieldsConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('Account ID'),
                'name' => $this->shortname . '_ACCOUNTID',
                'required' => true,
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('User ID'),
                'name' => $this->shortname . '_USERID',
                'required' => true,
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Password'),
                'name' => $this->shortname . '_PASSWORD',
                'required' => true,
                'class' => 'fixed-width-lg',
            ],
        ];
    }

    public function setFieldsAdditionalConfig()
    {
        $this->fields_additional_config = [
            [
                'type' => 'text',
                'label' => $this->l('Prefix etiqueta'),
                'name' => 'RJ_ETIQUETA_TRANSP_PREFIX',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'select',
                'label' => $this->l('Module contrareembolso'),
                'name' => 'RJ_MODULE_CONTRAREEMBOLSO',
                'options' => [
                    'query' => self::getModulesPay(),
                    'id' => 'id',
                    'name' => 'name'
                ]
            ]
        ];
    }

    public function getModulesPay()
    {
        $id_shop = Shop::getContextShopID();

        $modulesPay = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT m.`name`  FROM `' . _DB_PREFIX_ . 'module` m
        INNER JOIN `' . _DB_PREFIX_ . 'module_carrier` mc ON m.`id_module` = mc.`id_module`
        WHERE mc.`id_shop` = ' . $id_shop . '
        GROUP BY m.`id_module`');

        $modules_array[] = [
            'id' => '',
            'name' => ''
        ];

        foreach ($modulesPay as $module) {
            $modules_array[] =  array(
                'id' => $module['name'],
                'name' => $module['name']
            );
        }

        return $modules_array;
    }

    /**
     * Crea un envío genérico.
     *
     * @param array $shipment
     * @return array|bool
     */
    public function createShipment($shipment)
    {
        // Puedes extender esta lógica para agregar más detalles si es necesario.
        $this->logInfo('Default carrier: creating shipment.');
        return parent::createShipment($shipment);
    }

    /**
     * Crea etiquetas de manera predeterminada (no hace nada en este caso).
     *
     * @param int $id_shipment
     * @param int $id_order
     * @return bool
     */
    public function createLabel($id_shipment, $id_order)
    {

        $this->logInfo('Default carrier: labels are not implemented.');
        return false; // No se implementa la generación de etiquetas en este transportista predeterminado.
    }

    /**
     * Log de información para el transportista por defecto.
     *
     * @param string $message
     */
    private function logInfo(string $message): void
    {
        error_log('[CarrierDef] ' . $message);
    }
}
