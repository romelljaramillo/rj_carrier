<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

class AdminAjaxRJCarrierController extends ModuleAdminController
{

    public function ajaxProcessContrareembolso()
    {
        $order_id = (int) Tools::getValue('order_id');
        $order          = new Order($order_id);
        $datosOrden     = $order->getFields();
        header('Content-Type: application/json');
        $this->ajaxRender(json_encode($datosOrden['total_paid_tax_incl']));
        exit;
    }
}