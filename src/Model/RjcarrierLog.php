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
use Validate;

class RjcarrierLog extends \ObjectModel
{
    public $name;
    public $id_order;
    public $request;
    public $response;
    public $date_add;
    public $date_upd;

    const TABLE_NAME = _DB_PREFIX_ . 'rj_carrier_log';

    public static $definition = [
        'table' => 'rj_carrier_log',
        'primary' => 'id_carrier_log',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 250, 'required' => true],
            'request' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 65535],
            'response' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 65535],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    /**
     * Agrega un nuevo log al sistema.
     *
     * @param int $id_order
     * @param string $name
     * @param string $request
     * @param string $response
     * @return bool
     */
    public static function addLog($id_order, $name, $request, $response)
    {
        if (!Validate::isUnsignedInt($id_order) || !Validate::isGenericName($name)) {
            return false;
        }

        $log = new self();
        $log->id_order = (int)$id_order;
        $log->name = pSQL($name);
        $log->request = pSQL($request);
        $log->response = pSQL($response);

        return $log->add();
    }

    /**
     * Obtiene logs por ID de pedido.
     *
     * @param int $id_order
     * @return array
     */
    public static function getLogsByOrder($id_order)
    {
        if (!Validate::isUnsignedInt($id_order)) {
            return [];
        }

        $sql = 'SELECT *
                FROM `' . self::TABLE_NAME . '`
                WHERE `id_order` = ' . (int)$id_order . '
                ORDER BY `date_add` DESC';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
}
