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
 * @autor    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierCompany;
use Roanja\Module\RjCarrier\Model\RjcarrierInfoPackage;
use Roanja\Module\RjCarrier\Model\RjCarrierConfiguration;

class AdminAjaxRjCarrierController extends ModuleAdminController
{
    public function ajaxProcessContrareembolso()
    {
        $id_order = (int) Tools::getValue('id_order');
        $order = new \Order($id_order);
        $datosOrden = $order->getFields();
        $this->sendJsonResponse($datosOrden['total_paid_tax_incl']);
    }

    public function ajaxProcessTypeShipment()
    {
        $response = $this->initializeResponse();

        try {
            $id_reference_carrier = (int) Tools::getValue('id_reference_carrier');

            if ($id_reference_carrier) {

                $typeShipment = RjcarrierTypeShipment::getTypeShipmentByIdReferenceCarrier($id_reference_carrier);

                $carrierCompany = new RjcarrierCompany((int) $typeShipment['id_carrier_company']);

                $infoTypeShipments = RjcarrierTypeShipment::getTypeShipmentsActiveByIdCarrierCompany($carrierCompany->id);

                $infoPackage = $this->getInfoPackageCarrierConfiguration($carrierCompany->id, $carrierCompany->shortname);

                $response = $this->populateResponse($response, $carrierCompany->shortname, $infoPackage, $infoTypeShipments);

            } else {
                $response['message'] = $this->trans('Missing carrier reference ID.', [], 'Modules.RjCarrier.Admin');
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        $this->sendJsonResponse($response);
    }

    /**
     * Get the configuration values for the carrier company
     *
     * @param int $id_carrier_company
     * @param string $shortname
     * @return array
     */
    private function getInfoPackageCarrierConfiguration($id_carrier_company, $shortname)
    {
        $configurations = RjCarrierConfiguration::getAllByCarrierCompany($id_carrier_company);

        $id_order = (int) Tools::getValue('id_order');
        $infoPackage = RjcarrierInfoPackage::getPackageByIdOrder($id_order);

        if (!$infoPackage) {
            $infoPackage = [];
            foreach ($configurations as $config) {
                $configName = $this->removeShortname($config['name'], $shortname);
                $configName = strtolower($configName);
                if (property_exists(RjcarrierInfoPackage::class, $configName)) {
                    $infoPackage[$configName] = $config['value'];
                }
            }
        }

        return $infoPackage;
    }

    private function removeShortname($string, $shortname) {
        // Verifica si las primeras 4 letras coinciden con la palabra dada
        $shortname = $shortname . '_';

        if (strncmp($string, $shortname, strlen($shortname)) === 0) {
            return substr($string, strlen($shortname)); // Elimina las primeras letras coincidentes
        }
        return $string; // Si no coincide, devuelve el string original
    }

    private function initializeResponse()
    {
        return [
            'success' => false,
            'shortname' => '',
            'info_type_shipment' => [],
            'message' => ''
        ];
    }

    private function populateResponse($response, $shortname, $infoPackage, $infoTypeShipments)
    {
        $response['success'] = true;
        $response['shortname'] = $shortname;
        $response['info_package'] = $infoPackage;
        $response['info_type_shipment'] = $infoTypeShipments;
        return $response;
    }

    private function sendJsonResponse($response)
    {
        header('Content-Type: application/json');
        $this->ajaxRender(json_encode($response));
        exit;
    }
}
