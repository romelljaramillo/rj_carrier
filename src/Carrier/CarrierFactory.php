<?php
namespace Roanja\Module\RjCarrier\Carrier;

use Roanja\Module\RjCarrier\Carrier\Def\CarrierDef;

class CarrierFactory
{
    /**
     * Crea una instancia de transportista basado en el nombre corto.
     *
     * @param string $shortname
     * @return object
     */
    public static function createCarrier($shortname)
    {
        $shortname = strtolower($shortname);
        $className = 'Carrier' . ucfirst($shortname);

        $filePath = _PS_MODULE_DIR_ . "rj_carrier/src/Carrier/" . ucfirst($shortname) . "/{$className}.php";
        $namespaceClass = "\\Roanja\\Module\\RjCarrier\\Carrier\\" . ucfirst($shortname) . "\\{$className}";

        if (file_exists($filePath) && class_exists($namespaceClass)) {
            return new $namespaceClass();
        }

        // Si no se encuentra la clase, devolver el transportista predeterminado.
        return new CarrierDef();
    }
}
