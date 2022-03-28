<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfe664b36c32c26407e3ea2324e30cdd0
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        'e39a8b23c42d4e1452234d762b03835a' => __DIR__ . '/..' . '/ramsey/uuid/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
        'i' => 
        array (
            'iio\\libmergepdf\\' => 16,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'R' => 
        array (
            'Roanja\\Module\\RjCarrier\\' => 24,
            'Ramsey\\Uuid\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
        'iio\\libmergepdf\\' => 
        array (
            0 => __DIR__ . '/..' . '/iio/libmergepdf/src',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Roanja\\Module\\RjCarrier\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Ramsey\\Uuid\\' => 
        array (
            0 => __DIR__ . '/..' . '/ramsey/uuid/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Datamatrix' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/barcodes/datamatrix.php',
        'FPDF' => __DIR__ . '/..' . '/iio/libmergepdf/tcpdi/tcpdi.php',
        'FPDF_TPL' => __DIR__ . '/..' . '/iio/libmergepdf/tcpdi/fpdf_tpl.php',
        'PDF417' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/barcodes/pdf417.php',
        'QRcode' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/barcodes/qrcode.php',
        'Ramsey\\Uuid\\BinaryUtils' => __DIR__ . '/..' . '/ramsey/uuid/src/BinaryUtils.php',
        'Ramsey\\Uuid\\Builder\\DefaultUuidBuilder' => __DIR__ . '/..' . '/ramsey/uuid/src/Builder/DefaultUuidBuilder.php',
        'Ramsey\\Uuid\\Builder\\DegradedUuidBuilder' => __DIR__ . '/..' . '/ramsey/uuid/src/Builder/DegradedUuidBuilder.php',
        'Ramsey\\Uuid\\Builder\\UuidBuilderInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Builder/UuidBuilderInterface.php',
        'Ramsey\\Uuid\\Codec\\CodecInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/CodecInterface.php',
        'Ramsey\\Uuid\\Codec\\GuidStringCodec' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/GuidStringCodec.php',
        'Ramsey\\Uuid\\Codec\\OrderedTimeCodec' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/OrderedTimeCodec.php',
        'Ramsey\\Uuid\\Codec\\StringCodec' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/StringCodec.php',
        'Ramsey\\Uuid\\Codec\\TimestampFirstCombCodec' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/TimestampFirstCombCodec.php',
        'Ramsey\\Uuid\\Codec\\TimestampLastCombCodec' => __DIR__ . '/..' . '/ramsey/uuid/src/Codec/TimestampLastCombCodec.php',
        'Ramsey\\Uuid\\Converter\\NumberConverterInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/NumberConverterInterface.php',
        'Ramsey\\Uuid\\Converter\\Number\\BigNumberConverter' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/Number/BigNumberConverter.php',
        'Ramsey\\Uuid\\Converter\\Number\\DegradedNumberConverter' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/Number/DegradedNumberConverter.php',
        'Ramsey\\Uuid\\Converter\\TimeConverterInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/TimeConverterInterface.php',
        'Ramsey\\Uuid\\Converter\\Time\\BigNumberTimeConverter' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/Time/BigNumberTimeConverter.php',
        'Ramsey\\Uuid\\Converter\\Time\\DegradedTimeConverter' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/Time/DegradedTimeConverter.php',
        'Ramsey\\Uuid\\Converter\\Time\\PhpTimeConverter' => __DIR__ . '/..' . '/ramsey/uuid/src/Converter/Time/PhpTimeConverter.php',
        'Ramsey\\Uuid\\DegradedUuid' => __DIR__ . '/..' . '/ramsey/uuid/src/DegradedUuid.php',
        'Ramsey\\Uuid\\Exception\\InvalidUuidStringException' => __DIR__ . '/..' . '/ramsey/uuid/src/Exception/InvalidUuidStringException.php',
        'Ramsey\\Uuid\\Exception\\UnsatisfiedDependencyException' => __DIR__ . '/..' . '/ramsey/uuid/src/Exception/UnsatisfiedDependencyException.php',
        'Ramsey\\Uuid\\Exception\\UnsupportedOperationException' => __DIR__ . '/..' . '/ramsey/uuid/src/Exception/UnsupportedOperationException.php',
        'Ramsey\\Uuid\\FeatureSet' => __DIR__ . '/..' . '/ramsey/uuid/src/FeatureSet.php',
        'Ramsey\\Uuid\\Generator\\CombGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/CombGenerator.php',
        'Ramsey\\Uuid\\Generator\\DefaultTimeGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/DefaultTimeGenerator.php',
        'Ramsey\\Uuid\\Generator\\MtRandGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/MtRandGenerator.php',
        'Ramsey\\Uuid\\Generator\\OpenSslGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/OpenSslGenerator.php',
        'Ramsey\\Uuid\\Generator\\PeclUuidRandomGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/PeclUuidRandomGenerator.php',
        'Ramsey\\Uuid\\Generator\\PeclUuidTimeGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/PeclUuidTimeGenerator.php',
        'Ramsey\\Uuid\\Generator\\RandomBytesGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/RandomBytesGenerator.php',
        'Ramsey\\Uuid\\Generator\\RandomGeneratorFactory' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/RandomGeneratorFactory.php',
        'Ramsey\\Uuid\\Generator\\RandomGeneratorInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/RandomGeneratorInterface.php',
        'Ramsey\\Uuid\\Generator\\RandomLibAdapter' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/RandomLibAdapter.php',
        'Ramsey\\Uuid\\Generator\\SodiumRandomGenerator' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/SodiumRandomGenerator.php',
        'Ramsey\\Uuid\\Generator\\TimeGeneratorFactory' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/TimeGeneratorFactory.php',
        'Ramsey\\Uuid\\Generator\\TimeGeneratorInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Generator/TimeGeneratorInterface.php',
        'Ramsey\\Uuid\\Provider\\NodeProviderInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/NodeProviderInterface.php',
        'Ramsey\\Uuid\\Provider\\Node\\FallbackNodeProvider' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/Node/FallbackNodeProvider.php',
        'Ramsey\\Uuid\\Provider\\Node\\RandomNodeProvider' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/Node/RandomNodeProvider.php',
        'Ramsey\\Uuid\\Provider\\Node\\SystemNodeProvider' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/Node/SystemNodeProvider.php',
        'Ramsey\\Uuid\\Provider\\TimeProviderInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/TimeProviderInterface.php',
        'Ramsey\\Uuid\\Provider\\Time\\FixedTimeProvider' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/Time/FixedTimeProvider.php',
        'Ramsey\\Uuid\\Provider\\Time\\SystemTimeProvider' => __DIR__ . '/..' . '/ramsey/uuid/src/Provider/Time/SystemTimeProvider.php',
        'Ramsey\\Uuid\\Uuid' => __DIR__ . '/..' . '/ramsey/uuid/src/Uuid.php',
        'Ramsey\\Uuid\\UuidFactory' => __DIR__ . '/..' . '/ramsey/uuid/src/UuidFactory.php',
        'Ramsey\\Uuid\\UuidFactoryInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/UuidFactoryInterface.php',
        'Ramsey\\Uuid\\UuidInterface' => __DIR__ . '/..' . '/ramsey/uuid/src/UuidInterface.php',
        'Rj_Carrier' => __DIR__ . '/../..' . '/rj_carrier.php',
        'Roanja\\Module\\RjCarrier\\Carrier\\CarrierCompany' => __DIR__ . '/../..' . '/src/Carrier/CarrierCompany.php',
        'Roanja\\Module\\RjCarrier\\Carrier\\Cex\\CarrierCex' => __DIR__ . '/../..' . '/src/Carrier/Cex/CarrierCex.php',
        'Roanja\\Module\\RjCarrier\\Carrier\\Cex\\ServiceCex' => __DIR__ . '/../..' . '/src/Carrier/Cex/ServiceCex.php',
        'Roanja\\Module\\RjCarrier\\Carrier\\Dhl\\CarrierDhl' => __DIR__ . '/../..' . '/src/Carrier/Dhl/CarrierDhl.php',
        'Roanja\\Module\\RjCarrier\\Carrier\\Dhl\\ServiceDhl' => __DIR__ . '/../..' . '/src/Carrier/Dhl/ServiceDhl.php',
        'Roanja\\Module\\RjCarrier\\Controller\\Admin\\LabelController' => __DIR__ . '/../..' . '/src/Controller/Admin/LabelController.php',
        'Roanja\\Module\\RjCarrier\\Controller\\Admin\\RjCarrierModuleController' => __DIR__ . '/../..' . '/src/Controller/Admin/RjCarrierModuleController.php',
        'Roanja\\Module\\RjCarrier\\Controller\\Admin\\ShipmentsController' => __DIR__ . '/../..' . '/src/Controller/Admin/ShipmentsController.php',
        'Roanja\\Module\\RjCarrier\\Model\\Pdf\\HTMLTemplateDefault' => __DIR__ . '/../..' . '/src/Model/Pdf/HTMLTemplateDefault.php',
        'Roanja\\Module\\RjCarrier\\Model\\Pdf\\HTMLTemplateLabel' => __DIR__ . '/../..' . '/src/Model/Pdf/HTMLTemplateLabel.php',
        'Roanja\\Module\\RjCarrier\\Model\\Pdf\\RjPDF' => __DIR__ . '/../..' . '/src/Model/Pdf/RjPDF.php',
        'Roanja\\Module\\RjCarrier\\Model\\Pdf\\RjPDFGenerator' => __DIR__ . '/../..' . '/src/Model/Pdf/RjPDFGenerator.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierCompany' => __DIR__ . '/../..' . '/src/Model/RjcarrierCompany.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierInfoPackage' => __DIR__ . '/../..' . '/src/Model/RjcarrierInfoPackage.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierInfoshop' => __DIR__ . '/../..' . '/src/Model/RjcarrierInfoshop.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierLabel' => __DIR__ . '/../..' . '/src/Model/RjcarrierLabel.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierShipment' => __DIR__ . '/../..' . '/src/Model/RjcarrierShipment.php',
        'Roanja\\Module\\RjCarrier\\Model\\RjcarrierTypeShipment' => __DIR__ . '/../..' . '/src/Model/RjcarrierTypeShipment.php',
        'Symfony\\Polyfill\\Ctype\\Ctype' => __DIR__ . '/..' . '/symfony/polyfill-ctype/Ctype.php',
        'TCPDF' => __DIR__ . '/..' . '/tecnickcom/tcpdf/tcpdf.php',
        'TCPDF2DBarcode' => __DIR__ . '/..' . '/tecnickcom/tcpdf/tcpdf_barcodes_2d.php',
        'TCPDFBarcode' => __DIR__ . '/..' . '/tecnickcom/tcpdf/tcpdf_barcodes_1d.php',
        'TCPDF_COLORS' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_colors.php',
        'TCPDF_FILTERS' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_filters.php',
        'TCPDF_FONTS' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_fonts.php',
        'TCPDF_FONT_DATA' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_font_data.php',
        'TCPDF_IMAGES' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_images.php',
        'TCPDF_IMPORT' => __DIR__ . '/..' . '/tecnickcom/tcpdf/tcpdf_import.php',
        'TCPDF_PARSER' => __DIR__ . '/..' . '/tecnickcom/tcpdf/tcpdf_parser.php',
        'TCPDF_STATIC' => __DIR__ . '/..' . '/tecnickcom/tcpdf/include/tcpdf_static.php',
        'TCPDI' => __DIR__ . '/..' . '/iio/libmergepdf/tcpdi/tcpdi.php',
        'iio\\libmergepdf\\Driver\\DefaultDriver' => __DIR__ . '/..' . '/iio/libmergepdf/src/Driver/DefaultDriver.php',
        'iio\\libmergepdf\\Driver\\DriverInterface' => __DIR__ . '/..' . '/iio/libmergepdf/src/Driver/DriverInterface.php',
        'iio\\libmergepdf\\Driver\\Fpdi2Driver' => __DIR__ . '/..' . '/iio/libmergepdf/src/Driver/Fpdi2Driver.php',
        'iio\\libmergepdf\\Driver\\TcpdiDriver' => __DIR__ . '/..' . '/iio/libmergepdf/src/Driver/TcpdiDriver.php',
        'iio\\libmergepdf\\Exception' => __DIR__ . '/..' . '/iio/libmergepdf/src/Exception.php',
        'iio\\libmergepdf\\Merger' => __DIR__ . '/..' . '/iio/libmergepdf/src/Merger.php',
        'iio\\libmergepdf\\Pages' => __DIR__ . '/..' . '/iio/libmergepdf/src/Pages.php',
        'iio\\libmergepdf\\PagesInterface' => __DIR__ . '/..' . '/iio/libmergepdf/src/PagesInterface.php',
        'iio\\libmergepdf\\Source\\FileSource' => __DIR__ . '/..' . '/iio/libmergepdf/src/Source/FileSource.php',
        'iio\\libmergepdf\\Source\\RawSource' => __DIR__ . '/..' . '/iio/libmergepdf/src/Source/RawSource.php',
        'iio\\libmergepdf\\Source\\SourceInterface' => __DIR__ . '/..' . '/iio/libmergepdf/src/Source/SourceInterface.php',
        'setasign\\Fpdi\\FpdfTpl' => __DIR__ . '/..' . '/setasign/fpdi/src/FpdfTpl.php',
        'setasign\\Fpdi\\FpdfTplTrait' => __DIR__ . '/..' . '/setasign/fpdi/src/FpdfTplTrait.php',
        'setasign\\Fpdi\\Fpdi' => __DIR__ . '/..' . '/setasign/fpdi/src/Fpdi.php',
        'setasign\\Fpdi\\FpdiException' => __DIR__ . '/..' . '/setasign/fpdi/src/FpdiException.php',
        'setasign\\Fpdi\\FpdiTrait' => __DIR__ . '/..' . '/setasign/fpdi/src/FpdiTrait.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\AbstractReader' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/AbstractReader.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\CrossReference' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/CrossReference.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\CrossReferenceException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/CrossReferenceException.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\FixedReader' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/FixedReader.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\LineReader' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/LineReader.php',
        'setasign\\Fpdi\\PdfParser\\CrossReference\\ReaderInterface' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/CrossReference/ReaderInterface.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\Ascii85' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/Ascii85.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\Ascii85Exception' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/Ascii85Exception.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\AsciiHex' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/AsciiHex.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\FilterException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/FilterException.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\FilterInterface' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/FilterInterface.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\Flate' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/Flate.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\FlateException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/FlateException.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\Lzw' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/Lzw.php',
        'setasign\\Fpdi\\PdfParser\\Filter\\LzwException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Filter/LzwException.php',
        'setasign\\Fpdi\\PdfParser\\PdfParser' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/PdfParser.php',
        'setasign\\Fpdi\\PdfParser\\PdfParserException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/PdfParserException.php',
        'setasign\\Fpdi\\PdfParser\\StreamReader' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/StreamReader.php',
        'setasign\\Fpdi\\PdfParser\\Tokenizer' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Tokenizer.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfArray' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfArray.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfBoolean' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfBoolean.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfDictionary' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfDictionary.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfHexString' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfHexString.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfIndirectObject' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfIndirectObject.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfIndirectObjectReference' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfIndirectObjectReference.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfName' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfName.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfNull' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfNull.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfNumeric' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfNumeric.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfStream' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfStream.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfString' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfString.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfToken' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfToken.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfType' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfType.php',
        'setasign\\Fpdi\\PdfParser\\Type\\PdfTypeException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfParser/Type/PdfTypeException.php',
        'setasign\\Fpdi\\PdfReader\\DataStructure\\Rectangle' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfReader/DataStructure/Rectangle.php',
        'setasign\\Fpdi\\PdfReader\\Page' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfReader/Page.php',
        'setasign\\Fpdi\\PdfReader\\PageBoundaries' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfReader/PageBoundaries.php',
        'setasign\\Fpdi\\PdfReader\\PdfReader' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfReader/PdfReader.php',
        'setasign\\Fpdi\\PdfReader\\PdfReaderException' => __DIR__ . '/..' . '/setasign/fpdi/src/PdfReader/PdfReaderException.php',
        'setasign\\Fpdi\\TcpdfFpdi' => __DIR__ . '/..' . '/setasign/fpdi/src/TcpdfFpdi.php',
        'setasign\\Fpdi\\Tcpdf\\Fpdi' => __DIR__ . '/..' . '/setasign/fpdi/src/Tcpdf/Fpdi.php',
        'setasign\\Fpdi\\Tfpdf\\FpdfTpl' => __DIR__ . '/..' . '/setasign/fpdi/src/Tfpdf/FpdfTpl.php',
        'setasign\\Fpdi\\Tfpdf\\Fpdi' => __DIR__ . '/..' . '/setasign/fpdi/src/Tfpdf/Fpdi.php',
        'tcpdi_parser' => __DIR__ . '/..' . '/iio/libmergepdf/tcpdi/tcpdi_parser.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfe664b36c32c26407e3ea2324e30cdd0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfe664b36c32c26407e3ea2324e30cdd0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfe664b36c32c26407e3ea2324e30cdd0::$classMap;

        }, null, ClassLoader::class);
    }
}
