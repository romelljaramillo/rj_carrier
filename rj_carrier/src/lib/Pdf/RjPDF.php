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
namespace Roanja\Module\RjCarrier\lib\Pdf;

use Roanja\Module\RjCarrier\lib\Pdf\TemplateLabel;
use Roanja\Module\RjCarrier\lib\Pdf\RjPDFGenerator;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

class RjPDF extends \Module
{
    public $filename;
    public $pdf_renderer;
    public $shipment;
    public $shortname_company;
    public $template;
    public $num_package;
    public $send_bulk_flag = false;

    //barcodes in page
    public $cb             = 0; //counter barcodes in page
    public $incH           = 88;
    public $offset         = 7;

    const TEMPLATE_LABEL = 'Default';

    /**
     * @param $shipment
     * @param $template
     * @param string $orientation
     */
    public function __construct($shortname_company, $shipment, $template, $num_package, $orientation = 'P')
    {
        $this->pdf_renderer = new RjPDFGenerator((bool)\Configuration::get('PS_PDF_USE_CACHE'), $orientation);
        $this->template = $template;
        $this->num_package = $num_package;
       
        $this->shipment = $shipment;
        $this->shortname_company = $shortname_company;
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

        $this->pdf_renderer->SetHeaderMargin(5);
        $this->pdf_renderer->SetFooterMargin(5);
        $this->pdf_renderer->setMargins(5, 5, 5);
        $this->pdf_renderer->AddPage();
        $this->pdf_renderer->setCellPaddings(1, 1, 1, 1);
        $this->pdf_renderer->setCellMargins(1, 1, 1, 1);

        $template->getContent();

        $this->pdf_renderer->writePage();
        unset($template);
        
        // clean the output buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }

        return $this->pdf_renderer->render($this->filename, $display);
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
        $class_name = 'Roanja\Module\RjCarrier\lib\Pdf\TemplateLabel' . $this->template;

        if (class_exists($class_name)) {
            $class = new $class_name($this->shipment, $this->pdf_renderer, $this->num_package);

            if (!($class instanceof TemplateLabel)) {
                throw new \PrestaShopException('Invalid class. It should be an instance of TemplateLabel');
            }
        }

        return $class;
    }
}
