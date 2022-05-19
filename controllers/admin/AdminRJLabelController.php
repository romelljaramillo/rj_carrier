<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// namespace Roanja\Module\RjCarrier\Controller\Admin;

use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Common;

// use Tools;

class AdminRjLabelController extends ModuleAdminController
{
    public function initProcess()
    {
        parent::initProcess();
        $this->checkCacheFolder();
        
        if (Tools::isSubmit('submitCreateLabel')) {
            if(Tools::getValue('id_label')){
                $this->printLabel(Tools::getValue('id_label'));
            }
        } elseif (Tools::isSubmit('submitCreateLabelsShipment')) {
            if(Tools::getValue('id_shipment')){
                self::printLabelsShipment(Tools::getValue('id_shipment'));
            }
        } else {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
        }
    }
    public function updatePrintedLabel($id_label)
    {
        $label = new RjcarrierLabel($id_label);
        $label->print = true;
        $label->update();
    }

    public function printLabel($id_label)
    {
        $label = new RjcarrierLabel($id_label);

        $file = Common::getFileLabel($label->package_id);

        // provicional para generar etiquetas guardadas en base de datos y convertirlas en ficheros ojo borrar con el tiempo
        if(!file_exists($file) && $label->pdf){
            $pdf = base64_decode($label->pdf);
            if(Common::createFileLabel($pdf, $label->package_id)){
                $label->pdf = $label->package_id;
                $label->update();
                $file = Common::getFileLabel($label->package_id);
            }
        }
        // end provicional
        
        if(file_exists($file)){
            header('Content-Type: application/pdf; charset=utf-8');
            header('Cache-Control: no-store, no-cache');
            header('Pragma: public');
            header('Content-Disposition: inline; filename="'.basename($file.".pdf").'"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }

        $this->updatePrintedLabel($id_label);
    }

    public static function printLabelsShipment($id_shipment)
    {
        $labels = RjcarrierLabel::getPDFsByIdShipment((int)$id_shipment);
        $i=0;
        $pdfs=[];

        foreach ($labels as $label) {
            
            $file = Common::getFileLabel($label['package_id']);

            // provicional para generar etiquetas guardadas en base de datos y convertirlas en ficheros ojo borrar con el tiempo
            if(!file_exists($file) && $label['pdf']){
                $rjcarrierlabel = new RjcarrierLabel($label['id_label']);
                $pdf = base64_decode($label['pdf']);
                if(Common::createFileLabel($pdf, $label['package_id'])){
                    $rjcarrierlabel->pdf = $label['package_id'];
                    $rjcarrierlabel->update();
                    $file = Common::getFileLabel($label['package_id']);
                }
            }
            // end provicional

            array_push($pdfs, $file);
            self::updatePrintedLabel($label['id_label']);
            $i++;
        }

        $merge_pdf = Common::mergePdf($pdfs);
        
        header('Content-Type: application/pdf; charset=utf-8');
        header('Cache-Control: no-store, no-cache');

        echo $merge_pdf;
    }

    public function checkCacheFolder()
    {
        if (!is_dir(_PS_CACHE_DIR_.'tcpdf/')) {
            mkdir(_PS_CACHE_DIR_.'tcpdf/');
        }
    }

}