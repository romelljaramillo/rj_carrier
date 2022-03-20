<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Roanja\Module\RjCarrier\Model\Pdf;

use Roanja\Module\RjCarrier\Model\Pdf\HTMLTemplateLabel;

use Shop;
use Configuration;

class HTMLTemplateDefault extends HTMLTemplateLabel
{
    public $available_in_your_account = false;
    public $shipment;
    public $num_package;
    /** @var Smarty */
    public $smarty;
    public $id_shop_group;
    public $id_shop;
    public $id_lang;
    public $context;

    public function __construct($shipment, $smarty, $bulk_mode = false)
    {
        $this->shipment = $shipment;
        $this->smarty = $smarty;
    }

    public function setCounterPackage($num_package){
        $this->num_package = $num_package;
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $this->shipment['count'] = $this->num_package;
        $this->smarty->assign($this->shipment);
        return $this->smarty->fetch($this->getTemplate('label-defauld-pdf'));
    }

    /**
     * Returns the template filename when using bulk rendering
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'rjcarrier.pdf';
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $format = '%1$s%2$06d';

        if (Configuration::get('PS_INVOICE_USE_YEAR')) {
            $format = Configuration::get('PS_INVOICE_YEAR_POS') ? '%1$s%3$s-%2$06d' : '%1$s%2$06d-%3$s';
        }

        return sprintf(
            $format,
            Configuration::get('RJ_ETIQUETA_TRANSP_PREFIX', null, $id_shop_group, $id_shop),
           $this->shipment['id_order'],
            date('Y')
        ).'.pdf';
    }
}
