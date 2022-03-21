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
/**
 * @since 1.5
 */
namespace Roanja\Module\RjCarrier\Model\Pdf;

use Roanja\Module\RjCarrier\Model\Pdf\HTMLTemplateLabel;
use Roanja\Module\RjCarrier\Model\Pdf\RjPDFGenerator;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

class RjPDF extends \Module
{
    public $filename;
    public $pdf_renderer;
    public $shipment;
    public $template;
    public $num_package;
    public $send_bulk_flag = false;

    //barcodes in page
    public $cb             = 0; //counter barcodes in page
    public $incH           = 88;
    public $offset         = 7;

    const TEMPLATE_TAG_TD = 'Default';

    /**
     * @param $shipment
     * @param $template
     * @param $smarty
     * @param string $orientation
     */
    public function __construct($shipment, $template, $smarty, $num_package, $orientation = 'P')
    {
        $this->pdf_renderer = new RjPDFGenerator((bool)\Configuration::get('PS_PDF_USE_CACHE'), $orientation);
        $this->template = $template;
        $this->num_package = $num_package;
       
        $this->shipment = $shipment;
        $this->smarty = clone $smarty;
        $this->smarty->escape_html = false;
    }

    /**
     * Render PDF
     *
     * @param bool $display
     * @return mixed
     * @throws PrestaShopException
     */
    public function render($display)
    {

        $this->pdf_renderer->setFontForLang(\Context::getContext()->language->iso_code);
        $this->pdf_renderer->startPageGroup();

        $template = $this->getTemplateObject();

        if (empty($this->filename)) {
            $this->filename = $template->getFilename();
        }

        // $template->setCounterPackage($this->num_package);

        $this->pdf_renderer->SetPrintHeader(false);
        $this->pdf_renderer->SetPrintFooter(false);
        $this->pdf_renderer->SetHeaderMargin(5);
        $this->pdf_renderer->SetFooterMargin(5);
        $this->pdf_renderer->setMargins(5, 5, 5);
        $this->pdf_renderer->AddPage();
        $this->pdf_renderer->setCellPaddings(1, 1, 1, 1);
        $this->pdf_renderer->setCellMargins(1, 1, 1, 1);


        $this->labelZoneCarrier();
        $this->labelZoneShipper();
        $this->labelZoneReceiver();
        $this->labelZonePackage();
        $this->labelZoneInfoShipment();
        $this->labelZoneBarcode();

        // $html = $template->getContent();
        // $this->pdf_renderer->createContent($html);
        $this->pdf_renderer->writePage();
        unset($template);
        
        // clean the output buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }

        return $this->pdf_renderer->render($this->filename, $display);
        // return $this->pdf_renderer->Output($this->filename, $display);
    }

    protected function labelZoneCarrier()
    {
        $this->pdf_renderer->SetFillColor(255, 255, 255);
        $this->pdf_renderer->setY(5+($this->incH*$this->cb)+($this->offset*$this->cb));
        $this->pdf_renderer->MultiCell(25, 0, $this->l('Trasnporte'), 0, 'L', 1, 0, '', '', true);
        $this->pdf_renderer->SetFont('dejavusans', '', 15, '', true);
        $this->pdf_renderer->MultiCell(70, 0, $this->shipment['name_carrier'], 0, 'R', 0, 1, '', '', true);
        $this->pdf_renderer->Line(5, 15+($this->incH*$this->cb)+($this->offset*$this->cb), 105, 15+($this->incH*$this->cb)+($this->offset*$this->cb));
        $this->pdf_renderer->ln(4);
    }

    protected function labelZoneShipper()
    {
        $this->pdf_renderer->SetFont('dejavusans', '', 9, '', true);
        $this->pdf_renderer->setY(15);
        $this->pdf_renderer->MultiCell(20, 0, $this->l('From').':', 0, 'L', 1, 0, '', '', true);

        $data_shipper = $this->shipment['info_shop']['company'] . "\n" .
                        $this->shipment['info_shop']['lastname'] . " " . $this->shipment['info_shop']['firstname'] . "\n" .
                        $this->shipment['info_shop']['street'] . " - " . $this->shipment['info_shop']['city'] . "\n" .
                        $this->shipment['info_shop']['state'] . " - " . $this->shipment['info_shop']['country'] . "\n" .
                        $this->shipment['info_shop']['email'] . " - " . $this->shipment['info_shop']['phone'];

        $this->pdf_renderer->MultiCell(80, 0, $data_shipper, 0, 'L', 0, 1, '', '', true);
        $this->pdf_renderer->Line(5, 40, 105, 40);
        $this->pdf_renderer->ln();
    }

    protected function labelZoneReceiver()
    {
        $this->pdf_renderer->setY(45);
        $this->pdf_renderer->SetFont('dejavusans', '', 9, '', true);
        
        $style4 = array('L' => array('width' => 1, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
                        'T' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '30,245', 'phase' => 10, 'color' => array(0, 0, 0)),
                        'R' => array('width' => 1, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
                        'B' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '30,245', 'phase' => 10, 'color' => array(0, 0, 0)));

        $this->pdf_renderer->Rect(5, 45, 100, 40, 'DF', $style4);

        $this->pdf_renderer->MultiCell(10, 0, $this->l('To') . ':', 0, 'L', 1, 0, '', '', true);

        $data_shipper = $this->shipment['info_customer']['company'] . "\n" .
        $this->shipment['info_customer']['lastname'] . " " . $this->shipment['info_customer']['firstname'] . "\n" .
        $this->l('Tel.') . ": " . $this->shipment['info_customer']['phone'] . " - " . $this->shipment['info_customer']['phone_mobile'];

        $this->pdf_renderer->MultiCell(85, 0, $data_shipper, 0, 'L', 0, 1, '', '', true);

        $this->pdf_renderer->setCellPaddings(0, 0, 0, 0);
        $this->pdf_renderer->setCellMargins(14, 0, 0, 0);

        $this->pdf_renderer->SetFont('dejavusans', '', 10, '', true);
        $this->pdf_renderer->Cell(50, 0, $this->shipment['info_customer']['address1'], 0, 1, 'L');
        $this->pdf_renderer->SetFont('dejavusans', '', 20, '', true);
        $this->pdf_renderer->Cell(50, 0, $this->shipment['info_customer']['postcode'] . " - " . $this->shipment['info_customer']['city'], 0, 1, 'L');
        $this->pdf_renderer->SetFont('dejavusans', '', 10, '', true);
        $this->pdf_renderer->Cell(50, 0, $this->shipment['info_customer']['state'] . " - " . $this->shipment['info_customer']['country'], 0, 1, 'L');
    }

    protected function labelZonePackage()
    {
        $this->pdf_renderer->setCellPaddings(1, 1, 1, 1);
        $this->pdf_renderer->setCellMargins(1, 1, 1, 1);
        $style2 = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $this->pdf_renderer->setY(90);
        
        $this->pdf_renderer->Line(5, 90, 105, 90, $style2);
        
        $this->pdf_renderer->SetFont('dejavusans', '', 7, '', true);

        $this->pdf_renderer->Cell(25, 0, $this->l('pedido'), 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(20, 0, $this->l('weight'), 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(20, 0, $this->l('packages'), 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(25, 0, $this->l('cash on delivery'), 0, 0, 'C', 1);

        $this->pdf_renderer->ln();

        $this->pdf_renderer->SetFont('dejavusans', '', 11, '', true);

        $this->pdf_renderer->Cell(25, 0, $this->shipment['info_package']['id_order'], 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(20, 0, $this->shipment['info_package']['weight'], 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(20, 0, $this->num_package .'/'. $this->shipment['info_package']['quantity'], 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(25, 0, $this->shipment['info_package']['cash_ondelivery'], 0, 0, 'C', 1);

        $this->pdf_renderer->Line(30, 92, 30, 103, $style2);
        $this->pdf_renderer->Line(55, 92, 55, 103, $style2);
        $this->pdf_renderer->Line(75, 92, 75, 103, $style2);
 
        $this->pdf_renderer->Line(5, 105, 105, 105,$style2);
        $this->pdf_renderer->ln(4);

    }

    protected function labelZoneInfoShipment()
    {
        $style2 = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $this->pdf_renderer->setY(105);
        
        $this->pdf_renderer->SetFont('dejavusans', '', 9, '', true);
        $this->pdf_renderer->setCellMargins(0, 1, 0, 1);
        $this->pdf_renderer->setCellPaddings(0, 10, 0, 0);

        $type_shipment = new RjcarrierTypeShipment($this->shipment['info_package']['id_type_shipment']);

        $this->pdf_renderer->Cell(10, 0, 'ES', 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(25, 0, $type_shipment->name, 0, 0, 'C', 1);
        $this->pdf_renderer->Cell(60, 0, 'Env.: ' . $this->shipment['response']->datosResultado, 0, 0, 'C', 1);

        $this->pdf_renderer->Line(15, 107, 15, 118, $style2);
        $this->pdf_renderer->Line(45, 107, 45, 118, $style2);
 
        $this->pdf_renderer->Line(5, 120, 105, 120, $style2);
        $this->pdf_renderer->ln();

    }

    protected function labelZoneBarcode()
    {
        $this->pdf_renderer->setCellPaddings(1, 1, 1, 1);

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
        
        $this->pdf_renderer->SetFont('dejavusans', '', 11, '', true);
        $this->pdf_renderer->write1DBarcode($cod_package->codUnico, 'C128', 6, 125, 98, 26, 0.4, $style, 'C');
        $this->pdf_renderer->ln();
        $this->pdf_renderer->setY(145);
        $this->pdf_renderer->Cell(100, 0, 'Pack.: ' . $cod_package->codUnico, 0, 1, 'C');
        $this->pdf_renderer->Line(5, 155, 105, 155, $style2);
    }

    /**
     * Get correct PDF template classes
     *
     * @return HTMLTemplate|false
     * @throws PrestaShopException
     */
    public function getTemplateObject()
    {
        $class = false;
        $class_name = 'Roanja\Module\RjCarrier\Model\Pdf\HTMLTemplate' . $this->template;

        if (class_exists($class_name)) {
            // Some HTMLTemplateXYZ implementations won't use the third param but this is not a problem (no warning in PHP),
            // the third param is then ignored if not added to the method signature.
            $class = new $class_name($this->shipment, $this->smarty, $this->send_bulk_flag);

            if (!($class instanceof HTMLTemplateLabel)) {
                throw new \PrestaShopException('Invalid class. It should be an instance of HTMLTemplate');
            }
        }

        return $class;
    }
}
