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

include_once(_PS_MODULE_DIR_.'rj_carrier/classes/pdf/RjPDFGenerator.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/pdf/HTMLTemplateDefault.php');

class RjPDF
{
    public $filename;
    public $pdf_renderer;
    public $object;
    public $template;
    public $send_bulk_flag = false;
    public $num_print;

    const TEMPLATE_TAG_TD = 'Default';

    /**
     * @param $objects
     * @param $template
     * @param $smarty
     * @param string $orientation
     */
    public function __construct($object, $template, $smarty, $orientation = 'P')
    {
        $this->pdf_renderer = new RjPDFGenerator((bool)Configuration::get('PS_PDF_USE_CACHE'), $orientation);
        $this->template = $template;
        $this->smarty = $smarty;

        $this->object = $object;
        $this->num_print = (int)$object->packages;
    }

    /**
     * Render PDF
     *
     * @param bool $display
     * @return mixed
     * @throws PrestaShopException
     */
    public function render($display = true)
    {
        $render = false;
        $this->pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);
        $this->pdf_renderer->startPageGroup();
        $template = $this->getTemplateObject();

        if (empty($this->filename)) {
            $this->filename = $template->getFilename();
        }

        for ($i=0; $i < $this->num_print; $i++) { 
            $template->setCounter($i+1);
            $this->pdf_renderer->createContent($template->getContent());
            $this->pdf_renderer->writePage();
        }

        $render = true;

        unset($template);
        $this->object->print = true;
        $this->object->update();

        if ($render) {
            // clean the output buffer
            if (ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }
            return $this->pdf_renderer->render($this->filename, $display);
        }
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
        $class_name = 'HTMLTemplate' . $this->template;

        if (class_exists($class_name)) {
            // Some HTMLTemplateXYZ implementations won't use the third param but this is not a problem (no warning in PHP),
            // the third param is then ignored if not added to the method signature.
            $class = new $class_name($this->object, $this->smarty, $this->send_bulk_flag);

            if (!($class instanceof HTMLTemplate)) {
                throw new PrestaShopException('Invalid class. It should be an instance of HTMLTemplate');
            }
        }

        return $class;
    }
}
