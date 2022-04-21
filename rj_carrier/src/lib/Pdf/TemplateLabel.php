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
 *  @author 	PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Roanja\Module\RjCarrier\lib\Pdf;

use Roanja\Module\RjCarrier\lib\Common;
use Roanja\Module\RjCarrier\Model\RjcarrierCompany;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;
use Configuration;
use Translate;
use Validate;
use Context;
use Shop;
use Tools;
use Hook;

/**
 * @since 1.5
 */
abstract class TemplateLabel
{
    public $title;
    public $date;
    public $available_in_your_account = true;

    /** @var Shop */
    public $shop;

    public $shipment;

    public $company_shortname;
    public $pdf_class;
    public $num_package;

    //barcodes in page
    public $cb             = 0; //counter barcodes in page
    public $incH           = 88;
    public $offset         = 7;

    public function __construct($shipment, $pdf_class, $num_package = '')
    {
        $this->shipment = $shipment;
        $this->pdf_class = $pdf_class;
        $this->num_package = $num_package;
    }

    /**
     * Returns the template's HTML header
     *
     * @return string HTML header
     */
    public function getHeader()
    {
        // $this->getLogo(),
        $this->pdf_class->SetHeaderData(
            PDF_HEADER_LOGO,
            PDF_HEADER_LOGO_WIDTH,
            PDF_HEADER_TITLE,
            PDF_HEADER_STRING,
            array(0,64,255), 
            array(0,64,128)
        );
        $this->pdf_class->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    }

    /**
     * Returns the template's HTML footer
     *
     * @return string HTML footer
     */
    public function getFooter()
    {
        $this->pdf_class->setFooterData(array(0,64,0), array(0,64,128));
        $this->pdf_class->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    }

    protected function zoneCarrier()
    {
        $this->pdf_class->SetFillColor(255, 255, 255);
        $this->pdf_class->setY(5+($this->incH*$this->cb)+($this->offset*$this->cb));
        $this->pdf_class->MultiCell(25, 0, $this->l('Trasnporte'), 0, 'L', 1, 0, '', '', true);
        $this->pdf_class->SetFont('dejavusans', '', 15, '', true);
        $this->pdf_class->MultiCell(70, 0, $this->shipment['name_carrier'], 0, 'R', 0, 1, '', '', true);
        $this->pdf_class->Line(5, 15+($this->incH*$this->cb)+($this->offset*$this->cb), 105, 15+($this->incH*$this->cb)+($this->offset*$this->cb));
        $this->pdf_class->ln(4);
    }

    protected function zoneShipper()
    {
        $this->pdf_class->SetFont('dejavusans', '', 9, '', true);
        $this->pdf_class->setY(15);
        $this->pdf_class->MultiCell(20, 0, $this->l('From').':', 0, 'L', 1, 0, '', '', true);

        $data_shipper = $this->shipment['info_shop']['company'] . "\n" .
                        $this->shipment['info_shop']['lastname'] . " " . $this->shipment['info_shop']['firstname'] . "\n" .
                        $this->shipment['info_shop']['street'] . " - " . $this->shipment['info_shop']['city'] . "\n" .
                        $this->shipment['info_shop']['state'] . " - " . $this->shipment['info_shop']['country'] . "\n" .
                        $this->shipment['info_shop']['email'] . " - " . $this->shipment['info_shop']['phone'];

        $this->pdf_class->MultiCell(80, 0, $data_shipper, 0, 'L', 0, 1, '', '', true);
        $this->pdf_class->Line(5, 40, 105, 40);
        $this->pdf_class->ln();
    }

    protected function zoneReceiver()
    {
        $this->pdf_class->setY(45);
        $this->pdf_class->SetFont('dejavusans', '', 9, '', true);
        
        $style4 = array('L' => array('width' => 1, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
                        'T' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '30,245', 'phase' => 10, 'color' => array(0, 0, 0)),
                        'R' => array('width' => 1, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
                        'B' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '30,245', 'phase' => 10, 'color' => array(0, 0, 0)));

        $this->pdf_class->Rect(5, 45, 100, 40, 'DF', $style4);

        $this->pdf_class->MultiCell(10, 0, $this->l('To') . ':', 0, 'L', 1, 0, '', '', true);

        $data_shipper = $this->shipment['info_customer']['company'] . "\n" .
        $this->shipment['info_customer']['lastname'] . " " . $this->shipment['info_customer']['firstname'] . "\n" .
        $this->l('Tel.') . ": " . $this->shipment['info_customer']['phone'] . " - " . $this->shipment['info_customer']['phone_mobile'];

        $this->pdf_class->MultiCell(85, 0, $data_shipper, 0, 'L', 0, 1, '', '', true);

        $this->pdf_class->setCellPaddings(0, 0, 0, 0);
        $this->pdf_class->setCellMargins(14, 0, 0, 0);

        $this->pdf_class->SetFont('dejavusans', '', 10, '', true);
        $this->pdf_class->Cell(50, 0, $this->shipment['info_customer']['address1'], 0, 1, 'L');
        $this->pdf_class->SetFont('dejavusans', '', 20, '', true);
        $this->pdf_class->Cell(50, 0, $this->shipment['info_customer']['postcode'] . " - " . $this->shipment['info_customer']['city'], 0, 1, 'L');
        $this->pdf_class->SetFont('dejavusans', '', 10, '', true);
        $this->pdf_class->Cell(50, 0, $this->shipment['info_customer']['state'] . " - " . $this->shipment['info_customer']['country'], 0, 1, 'L');
    }
    
    protected function zonePackage()
    {
        $this->pdf_class->setCellPaddings(1, 1, 1, 1);
        $this->pdf_class->setCellMargins(1, 1, 1, 1);
        $style2 = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $this->pdf_class->setY(90);
        
        $this->pdf_class->Line(5, 90, 105, 90, $style2);
        
        $this->pdf_class->SetFont('dejavusans', '', 7, '', true);

        $this->pdf_class->Cell(25, 0, $this->l('pedido'), 0, 0, 'C', 1);
        $this->pdf_class->Cell(20, 0, $this->l('weight'), 0, 0, 'C', 1);
        $this->pdf_class->Cell(20, 0, $this->l('packages'), 0, 0, 'C', 1);
        $this->pdf_class->Cell(25, 0, $this->l('cash on delivery'), 0, 0, 'C', 1);

        $this->pdf_class->ln();

        $this->pdf_class->SetFont('dejavusans', '', 11, '', true);

        $id_order = $this->shipment['info_package']['id_order'];
        $cash_ondelivery = Common::convertAndFormatPrice($this->shipment['info_package']['cash_ondelivery']);
        $quantity = $this->shipment['info_package']['quantity'];
        $weight = Common::convertAndFormatNumber($this->shipment['info_package']['weight']) / $quantity;

        $this->pdf_class->Cell(25, 0, $id_order, 0, 0, 'C', 1);
        $this->pdf_class->Cell(20, 0, $weight, 0, 0, 'C', 1);
        $this->pdf_class->Cell(20, 0, $this->num_package .'/'. $quantity, 0, 0, 'C', 1);
        $this->pdf_class->Cell(25, 0, $cash_ondelivery, 0, 0, 'C', 1);

        $this->pdf_class->Line(30, 92, 30, 103, $style2);
        $this->pdf_class->Line(55, 92, 55, 103, $style2);
        $this->pdf_class->Line(75, 92, 75, 103, $style2);
 
        $this->pdf_class->Line(5, 105, 105, 105,$style2);
        $this->pdf_class->ln(4);

    }

    protected function zoneInfoShipment()
    {
        $style2 = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $this->pdf_class->setY(105);
        
        $this->pdf_class->SetFont('dejavusans', '', 9, '', true);
        $this->pdf_class->setCellMargins(0, 1, 0, 1);
        $this->pdf_class->setCellPaddings(0, 10, 0, 0);

        $type_shipment = new RjcarrierTypeShipment($this->shipment['info_package']['id_type_shipment']);

        $this->pdf_class->Cell(10, 0, 'ES', 0, 0, 'C', 1);
        $this->pdf_class->Cell(25, 0, $type_shipment->name, 0, 0, 'C', 1);
        $this->pdf_class->Cell(60, 0, 'Env.: ' . $this->shipment['response']->datosResultado, 0, 0, 'C', 1);

        $this->pdf_class->Line(15, 107, 15, 118, $style2);
        $this->pdf_class->Line(45, 107, 45, 118, $style2);
 
        $this->pdf_class->Line(5, 120, 105, 120, $style2);
        $this->pdf_class->ln();

    }

    protected function zoneBarcode()
    {
        $this->pdf_class->setCellPaddings(1, 1, 1, 1);

        $style      = array(
            'position' => '',
            'align' => 'C',
            'stretch' => true,
            'fitwidth' => false,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'freeserif',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        $style2 = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $cod_package = $this->shipment['response']->listaBultos[$this->num_package - 1];
        
        $this->pdf_class->SetFont('dejavusans', '', 11, '', true);
        $this->pdf_class->write1DBarcode($cod_package->codUnico, 'C128', 6, 125, 98, 26, 0.4, $style, 'C');
        $this->pdf_class->ln();
        $this->pdf_class->setY(145);
        $this->pdf_class->Cell(100, 0, 'Pack.: ' . $cod_package->codUnico, 0, 1, 'C');
        $this->pdf_class->Line(5, 155, 105, 155, $style2);
    }

    /**
     * Returns the shop address
     *
     * @return string
     */
    protected function getShopAddress()
    {
        $shop_address = '';

        $shop_address_obj = $this->shop->getAddress();
        if (isset($shop_address_obj) && $shop_address_obj instanceof \Address) {
            $shop_address = \AddressFormat::generateAddress($shop_address_obj, array(), ' - ', ' ');
        }

        return $shop_address;
    }

    /**
     * Returns the invoice logo
     */
    protected function getLogo()
    {
        $logo = '';

        $icon = RjcarrierCompany::getIconCompanyByShortname($this->company_shortname);

        if ($icon != false && file_exists(IMG_ICON_COMPANY_DIR. $this->company_shortname . '/' . $icon)) {
            $logo = IMG_ICON_COMPANY_DIR. $this->company_shortname . '/' . $icon;
        } elseif ($icon != false && file_exists(IMG_ICON_COMPANY_DIR . $icon)) {
            $logo = IMG_ICON_COMPANY_DIR . $icon;
        }

        return $logo;
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent(){
        $this->zoneCarrier();
        $this->zoneShipper();
        $this->zoneReceiver();
        $this->zonePackage();
        $this->zoneInfoShipment();
        $this->zoneBarcode();
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        $format = '%1$s%2$06d';

        return sprintf(
            $format,
            Configuration::get('RJ_ETIQUETA_TRANSP_PREFIX', null, Shop::getContextShopGroupID(), Shop::getContextShopID()),
           $this->shipment['id_order'],
            date('Y')
        ).'.pdf';
    }

    /**
     * Translation method
     *
     * @param string $string
     *
     * @return string translated text
     */
    protected static function l($string)
    {
        return Translate::getPdfTranslation($string);
    }
    
    /**
     * Returns the template's HTML pagination block
     *
     * @return string HTML pagination block
     */
    public function getPagination()
    {
        // return $this->smarty->fetch($this->getTemplate('pagination'));
    }
}
