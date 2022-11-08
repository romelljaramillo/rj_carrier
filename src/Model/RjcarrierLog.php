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

class RjcarrierLog extends \ObjectModel
{
    public $name;
    public $id_order;
    public $request;
    public $response;
    public $date_add;
	public $date_upd;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'rj_carrier_log',
        'primary' => 'id_carrier_log',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'name'     => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 250, 'required' => true],
            'request'  => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'response' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];
}