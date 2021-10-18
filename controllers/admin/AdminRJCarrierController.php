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
require_once(_PS_MODULE_DIR_.'rj_carrier/vendor/autoload.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/pdf/RjPDF.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjCarrierInfoPackage.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierLabel.php');

use iio\libmergepdf\Merger;

class AdminRJCarrierController extends ModuleAdminController
{
    const NAME_ETIQUETA = "ETIQUETA_TRANS";
    
    public function postProcess()
    {
        $action = Tools::getValue('submitAction');
        parent::postProcess();

        // We want to be sure that displaying PDF is the last thing this controller will do
        exit;
    }

    public function initProcess()
    {
        // $action = Tools::getValue('submitAction');
        parent::initProcess();
        $this->checkCacheFolder();
        $access = Profile::getProfileAccess($this->context->employee->id_profile, (int)Tab::getIdFromClassName('AdminOrders'));
        $_GET;
        $this->action = Tools::getValue('action');
        
        if ($access['view'] === '1' && $this->action === 'createEtiquetaPDF') {
            $this->processEtiquetaPDF();
        } elseif (Tools::isSubmit('submitCreateLabel')) {
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
        $pdf = base64_decode($label->pdf);

        header('Content-Type: application/pdf; charset=utf-8');
        header('Cache-Control: no-store, no-cache');
        echo $pdf;
        $this->updatePrintedLabel($id_label);
    }

    public static function printLabelsShipment($id_shipment)
    {
        $labels = RjcarrierLabel::getPDFsByIdShipment($id_shipment);
        $i=0;
        $pdfs=[];
        foreach ($labels as $label) {
            file_put_contents(_PS_MODULE_DIR_.'rj_carrier/labels/etiqueta'.$i.'.pdf',base64_decode($label['pdf']));
            array_push($pdfs,_PS_MODULE_DIR_.'rj_carrier/labels/etiqueta'.$i.'.pdf');
            self::updatePrintedLabel($label['id']);
            $i++;
        }

        $mergePDF = self::mergePDF($pdfs);

        header('Content-Type: application/pdf; charset=utf-8');
        header('Cache-Control: no-store, no-cache');
        
        echo $mergePDF;

        foreach ($pdfs as $pdf) {
            unlink($pdf);
        }
    }

    public static function downloadLabelsShipment($id_shipment)
    {
        $labels = RjcarrierLabel::getPDFsByIdShipment($id_shipment);
        $i=0;
        $pdfs=[];
        foreach ($labels as $label) {
            file_put_contents(_PS_MODULE_DIR_.'rj_carrier/labels/etiqueta'.$i.'.pdf',base64_decode($label['pdf']));
            array_push($pdfs,_PS_MODULE_DIR_.'rj_carrier/labels/etiqueta'.$i.'.pdf');
            $i++;
        }
        $fichero = self::mergePDF($pdfs);
        foreach ($pdfs as $pdf) {
            unlink($pdf);
        }
        
        file_put_contents(_PS_MODULE_DIR_.'rj_carrier/labels/etiqueta_'.$id_shipment.'.pdf',$fichero);
        $fichero = _PS_MODULE_DIR_.'rj_carrier/labels/etiqueta_'.$id_shipment.'.pdf';
        if (file_exists($fichero)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($fichero).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fichero));
            readfile($fichero);
            unlink($fichero);
            exit;
        }

        return false;

    }


    public function mergePDF($arrayPDF)
    {
        $merger = new Merger;
        $merger->addIterator($arrayPDF);
        return  $merger->merge();
    }

    public function checkCacheFolder()
    {
        if (!is_dir(_PS_CACHE_DIR_.'tcpdf/')) {
            mkdir(_PS_CACHE_DIR_.'tcpdf/');
        }
    }

    public function processEtiquetaPDF()
    {
        if (Tools::isSubmit('id_infopackage')) {
            $this->generateEtiquetaPDFByIdOrder(Tools::getValue('id_infopackage'));
        } 
    }

    public function generateEtiquetaPDFByIdOrder($id_infopackage)
    {
        $RjCarrierInfoPackage = new RjCarrierInfoPackage((int)$id_infopackage);

        if (!Validate::isLoadedObject($RjCarrierInfoPackage)) {
            die(Tools::displayError('The order cannot be found within your database.'));
        }

        $this->generatePDF($RjCarrierInfoPackage, RjPDF::TEMPLATE_TAG_TD);
    }

    public function generatePDF($object, $template)
    {
        $pdf = new RjPDF($object, $template, Context::getContext()->smarty);
        $pdf->render();
    }

}