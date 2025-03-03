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
     * Encripta y desencripta una cadena
     *
     * @param string $action
     * @param string $pass
     * @return string
     */
    public static function encrypt($action, $pass)
    {
        $salt = base64_decode(_COOKIE_KEY_);
        $salt1 = hash('sha256', $salt, true);
        $salt2 = substr($salt1, 0, 16);

        if ($action === 'encrypt') {
            return base64_encode(openssl_encrypt($pass, 'AES-256-CBC', $salt1, 0, $salt2));
        } elseif ($action === 'decrypt') {
            return openssl_decrypt(base64_decode($pass), 'AES-256-CBC', $salt1, 0, $salt2);
        }

        return false;
    }


    /**
     * Convierte y formatea un precio
     *
     * @param float $price
     * @param Currency|null $currency
     * @param Context|null $context
     * @return string
     */
    public static function convertAndFormatPrice($price, $currency = null, Context $context = null)
    {
        if ($context === null) {
            $context = Context::getContext();
        }
        if ($currency === null) {
            $currency = $context->currency;
        }

        return $context->getCurrentLocale()->formatPrice(Tools::convertPrice($price, $currency), $currency->iso_code);
    }

    /**
     * Convierte y formatea un número
     *
     * @param float $number
     * @return string
     */
    public static function convertAndFormatNumber($number)
    {
        $context = Context::getContext();
        $locale = Tools::getContextLocale($context);

        return $locale->formatNumber($number);
    }

    /**
     * Genera un UUID
     *
     * @return string
     */
    public static function getUUID()
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
    }

    /**
     * Merge multiple PDF files into one
     *
     * @param array $array_pdf Array of PDF file paths
     * @return string Merged PDF content
     */
    public static function mergePdf(array $array_pdf)
    {
        $merger = new Merger();
        $merger->addIterator($array_pdf);
        return $merger->merge();
    }

    /**
     * Crea un archivo PDF
     *
     * @param string $pdf
     * @param string $id_label
     * @return bool
     */
    public static function createFileLabel($pdf, $id_label)
    {
        $filePath = self::getFileLabel($id_label);
        return file_put_contents($filePath, $pdf) !== false;
    }

    /**
     * Obtiene la ruta de un archivo PDF
     *
     * @param string $id_label
     * @return string
     */
    public static function getFileLabel($id_label)
    {
        return _PS_MODULE_DIR_.'rj_carrier/labels/' . $id_label . '.pdf';
    }
}
