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
use Context;
use Validate;

class RjCarrierConfiguration extends \ObjectModel
{
    public $id_configuration;
    public $id_shop_group;
    public $id_shop;
    public $id_carrier_company;
    public $name;
    public $value;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'rj_carrier_configuration',
        'primary' => 'id_configuration',
        'fields' => [
            'id_shop_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_carrier_company' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 254],
            'value' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE],
            'date_upd' => ['type' => self::TYPE_DATE],
        ],
    ];

    /**
     * Get a configuration value by name, shop_group, and shop
     *
     * @param string $name
     * @param int|null $id_carrier_company
     * @param int|null $id_shop_group
     * @param int|null $id_shop
     * @return string|null
     */
    public static function get($name, $id_shop_group = null, $id_shop = null)
    {
        $db = Db::getInstance();

        $sql = 'SELECT value FROM ' . _DB_PREFIX_ . 'rj_carrier_configuration
        WHERE name = "' . pSQL($name) . '" ';

        if ($id_shop !== null) {
            $sql .= ' AND id_shop = ' . (int)$id_shop;
        }

        if ($id_shop_group !== null) {
            $sql .= ' AND id_shop_group = ' . (int)$id_shop_group;
        }

        return $db->getValue($sql);
    }

    /**
     * Update or insert a configuration value
     *
     * @param string $name
     * @param string $value
     * @param int|null $id_shop_group
     * @param int|null $id_shop
     * @return bool
     */
    public static function updateValue($name, $value, $id_carrier_company = null, $id_shop_group = null, $id_shop = null)
    {
        $db = Db::getInstance();

        $sql = 'SELECT id_configuration FROM ' . _DB_PREFIX_ . 'rj_carrier_configuration
                WHERE name = "' . pSQL($name) . '" ';

        if ($id_carrier_company !== null) {
            $sql .= ' AND id_carrier_company = ' . (int)$id_carrier_company;
        } else {
            $sql .= ' AND id_carrier_company IS NULL';
        }

        if ($id_shop !== null) {
            $sql .= ' AND id_shop = ' . (int)$id_shop;
        } else {
            $sql .= ' AND id_shop IS NULL';
        }

        if ($id_shop_group !== null) {
            $sql .= ' AND id_shop_group = ' . (int)$id_shop_group;
        } else {
            $sql .= ' AND id_shop_group IS NULL';
        }

        $id_configuration = $db->getValue($sql);

        if ($id_configuration) {
            return $db->execute(
                'UPDATE ' . _DB_PREFIX_ . 'rj_carrier_configuration
                SET value = "' . pSQL($value) . '",
                    id_carrier_company = ' . ($id_carrier_company !== null ? (int)$id_carrier_company : 'NULL') . ',
                    date_upd = NOW()
                WHERE id_configuration = ' . (int)$id_configuration
            );
        } else {
            return $db->insert('rj_carrier_configuration', [
                'name' => pSQL($name),
                'value' => pSQL($value),
                'id_carrier_company' => $id_carrier_company !== null ? (int)$id_carrier_company : null,
                'id_shop_group' => $id_shop_group !== null ? (int)$id_shop_group : null,
                'id_shop' => $id_shop !== null ? (int)$id_shop : null,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Delete a configuration by name, shop_group, and shop
     */
    public static function deleteByName($name, $id_shop_group = null, $id_shop = null)
    {
        $db = Db::getInstance();

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'rj_carrier_configuration
                WHERE name = "' . pSQL($name) . '" ';

        if ($id_shop !== null) {
            $sql .= ' AND id_shop = ' . (int)$id_shop;
        } else {
            $sql .= ' AND id_shop IS NULL';
        }

        if ($id_shop_group !== null) {
            $sql .= ' AND id_shop_group = ' . (int)$id_shop_group;
        } else {
            $sql .= ' AND id_shop_group IS NULL';
        }

        return $db->execute($sql);
    }

    /**
     * Get all configuration records by carrier company ID
     *
     * @param int $id_carrier_company
     * @return array|null
     */
    public static function getAllByCarrierCompany($id_carrier_company)
    {
        if (!Validate::isUnsignedInt($id_carrier_company)) {
            return null;
        }

        $db = Db::getInstance();

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'rj_carrier_configuration
                WHERE id_carrier_company = ' . (int)$id_carrier_company;

        return $db->executeS($sql);
    }
}
