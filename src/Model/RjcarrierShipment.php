<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
namespace Roanja\Module\RjCarrier\Model;

use Db;
use Shop;
use Order;
use Context;
use Validate;

class RjcarrierShipment extends \ObjectModel
{
    public $id_order;
    public $num_shipment;
    public $reference_order;
    public $id_carrier_company;
    public $id_infopackage;
    public $account;
    public $product;
    public $request;
    public $response;
    public $delete;
    public $date_add;
    public $date_upd;
    public $id_shop;

    const TABLE_NAME = _DB_PREFIX_ . 'rj_carrier_shipment';
    const TABLE_SHOP = _DB_PREFIX_ . 'rj_carrier_shipment_shop';

    public static $definition = [
        'table' => 'rj_carrier_shipment',
        'primary' => 'id_shipment',
        'multishop' => true,
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'reference_order' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100],
            'num_shipment' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100],
            'id_carrier_company' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_infopackage' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'account' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'product' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'request' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 65535],
            'response' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 65535],
            'delete' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    public function __construct($id_infopackage = null, $id_lang = null, $id_shop = null, Context $context = null)
    {
        Shop::addTableAssociation('rj_carrier_shipment', ['type' => 'shop']);
        parent::__construct($id_infopackage, $id_lang, $id_shop);
    }


    public static function getIdByIdOrder($id_order)
    {
        if (!Validate::isUnsignedInt($id_order)) {
            return null;
        }

        $sql = 'SELECT c.id_shipment
                FROM `' . self::TABLE_NAME . '` c
                WHERE c.`id_order` = ' . (int)$id_order . ' AND c.`delete` = 0';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Obtiene el id_shipment a partir del id_order
     *
     * @param int $id_order
     * @return int|null
     */
    public static function getShipmentByIdOrder($id_order)
    {
        if (!Validate::isUnsignedInt($id_order)) {
            return null;
        }

        $sql = 'SELECT cs.*
		FROM `' . self::TABLE_NAME . '` cs
		WHERE cs.`id_order` = ' . (int)$id_order . '
        AND cs.`delete` = 0';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public static function shipmentExistsByIdInfopackage($id_infopackage)
    {
        $sql = 'SELECT c.id_shipment
                FROM `' . self::TABLE_NAME . '` c
                WHERE c.`id_infopackage` = ' . (int)$id_infopackage . ' AND c.`delete` = 0';

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $row ? (int)$row['id_shipment'] : null;
    }

    public function delete()
    {
        $this->delete = true;

        if (!$this->update()) {
            throw new \PrestaShopException('Failed to delete shipment.');
        }

        return true;
    }

    public static function getOrCreateShipment(int $id_order): RjcarrierShipment
    {
        // Intenta obtener el envío existente
        $id_shipment = self::getIdByIdOrder($id_order);

        if ($id_shipment) {
            // Si ya existe, devuelve el envío cargado
            return new self((int) $id_shipment);
        }

        // Si no existe, crea un nuevo objeto de envío
        $shipment = new self();
        $shipment->id_order = $id_order;

        // Agrega cualquier lógica de inicialización adicional si es necesario
        $shipment->reference_order = (new Order($id_order))->reference;

        // Guarda el nuevo envío en la base de datos (opcional, dependiendo de tus necesidades)
        if (!$shipment->add()) {
            throw new \Exception('Failed to create a new shipment for order ID ' . $id_order);
        }

        return $shipment;
    }

}
