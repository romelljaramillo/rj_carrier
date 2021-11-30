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

/**
 * @since 1.5
 */
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/pdf/HTMLTemplate.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/rj_carrier.php');

class HTMLTemplateDefault extends HTMLTemplate
{
    public $order;
    public $order_invoice;
    public $available_in_your_account = false;
    public $rjtransport;
    public $count;


    /**
     * @param OrderInvoice $order_invoice
     * @param $smarty
     * @throws PrestaShopException
     */
    public function __construct($rjtransport, $smarty, $bulk_mode = false)
    {
        $this->rjtransport = $rjtransport;
        $this->order = new Order((int)$this->rjtransport->id_order);
        $this->smarty = $smarty;

        $id_lang = Context::getContext()->language->id;
        // $this->title = $order_invoice->getInvoiceNumberFormatted($id_lang,(int)$this->order->id_shop);

        $this->shop = new Shop((int)$this->order->id_shop);
    }

    public function setCounter($count){
        $this->count = $count;
    }
    /**
     * Returns the template's HTML header
     *
     * @return string HTML header
     */
    public function getHeader()
    {
        $this->assignCommonHeaderData();
        $this->smarty->assign(array('header' => HTMLTemplateInvoice::l('Invoice')));

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    /**
     * Compute layout elements size
     *
     * @param $params Array Layout elements
     *
     * @return Array Layout elements columns size
     */
    protected function computeLayout($params)
    {
        $layout = array(
            'reference' => array(
                'width' => 15,
            ),
            'product' => array(
                'width' => 40,
            ),
            'quantity' => array(
                'width' => 8,
            ),
            'tax_code' => array(
                'width' => 8,
            ),
            'unit_price_tax_excl' => array(
                'width' => 0,
            ),
            'total_tax_excl' => array(
                'width' => 0,
            )
        );

        if (isset($params['has_discount']) && $params['has_discount']) {
            $layout['before_discount'] = array('width' => 0);
            $layout['product']['width'] -= 7;
            $layout['reference']['width'] -= 3;
        }

        $total_width = 0;
        $free_columns_count = 0;
        foreach ($layout as $data) {
            if ($data['width'] === 0) {
                ++$free_columns_count;
            }

            $total_width += $data['width'];
        }

        $delta = 100 - $total_width;

        foreach ($layout as $row => $data) {
            if ($data['width'] === 0) {
                $layout[$row]['width'] = $delta / $free_columns_count;
            }
        }

        $layout['_colCount'] = count($layout);

        return $layout;
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $this->context = Context::getContext();
        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $id_lang = $this->context->language->id;

        $rj_carrier = new Rj_Carrier();

        $activeDHL = false;
        $order = new Order($this->order->id);
        $info_customer = new Address($order->id_address_delivery);
        $info_shop = InfoShop::getShopData();
        $info_package = RjCarrierInfoPackage::getDataPackage($this->order->id);
        $carriers = Carrier::getCarriers((int) $id_lang);

        if($info_package['id_reference_carrier']){
            $name_carrier = Carrier::getCarrierByReference((int)$info_package['id_reference_carrier'], $id_lang);
            if($info_package['id_reference_carrier'] == Configuration::get('RJ_DHL_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop)){
                $activeDHL = true;
            }
        }

        $infoOrder = array(
            'link' => $this->context->link,
            'order_id' => $this->order->id,
            'info_package' => $info_package,
            'count' => $this->count,
            'info_customer' => (array)$info_customer,
            'info_shop' => $info_shop,
            'carriers' => $carriers,
            'name_carrier' => $name_carrier->name,
            'activeDHL' => $activeDHL
        );

        $this->context->smarty->assign($infoOrder);
        return $this->smarty->fetch($this->getTemplate('tag-defauld-pdf'));
    }

    /**
     * Returns the template filename when using bulk rendering
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'invoices.pdf';
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = (int)$this->order->id_shop;
        // $orderId = (int)$this->order->id;
        $format = '%1$s%2$06d';

        return sprintf(
            $format,
            Configuration::get('RJ_ETIQUETA_TRANSP_PREFIX', null, $id_shop_group, $id_shop),
            $this->order->id,
            date('Y')
        ).'.pdf';
    }
}
