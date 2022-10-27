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

namespace Roanja\Module\RjCarrier\lib;

use Ramsey\Uuid\Uuid;
use iio\libmergepdf\Merger;
use Context;
use Tools;

class Common {
    
    /**
     * Devuelve una password encrypt
     *
     * @param string $action 'encrypt' | 'decrypt'
     * @param string $pass
     * @return string
     */
    public static function encrypt($action, $pass)
    {
        $nP = false;
        $salt = base64_decode(_COOKIE_KEY_);
        $salt1 = hash('sha256', $salt);
        $salt2 = substr(hash('sha256', $salt), 0, 16);
        if ($action == 'encrypt') {
            $nP = base64_encode(openssl_encrypt($pass, 'AES-256-CBC', $salt1, 0, $salt2));
        } else if ($action == 'decrypt') {
            $nP = openssl_decrypt(base64_decode($pass), 'AES-256-CBC', $salt1, 0, $salt2);
        }
        return $nP;
    }

    public static function convertAndFormatPrice($price, $currency = false, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$currency) {
            $currency = $context->currency;
        }

        return $context->getCurrentLocale()->formatPrice(Tools::convertPrice($price, $currency), $currency->iso_code);
    }

    public static function convertAndFormatNumber($number)
    {
        $context = Context::getContext();
        $locale = Tools::getContextLocale($context);

        return $locale->formatNumber($number);
    }

    public static function getUUID()
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
    }

    public static function mergePdf($array_pdf)
    {
        $merger = new Merger;
        $merger->addIterator($array_pdf);
        $createdPdf = $merger->merge();
        return $createdPdf;
    }

    public static function createFileLabel($pdf, $id_label)
    {
        header('Content-Type: application/pdf');
        file_put_contents(_PS_MODULE_DIR_.'rj_carrier/labels/'. $id_label .'.pdf', $pdf);
        return true;
    }

    public static function getFileLabel($id_label)
    {
        $file = _PS_MODULE_DIR_.'rj_carrier/labels/' . $id_label . '.pdf';
        return $file;
    }
}