<?php
/**
 * WHMCS SWHosting Registrar Module Test
 *
 *
 * This is by no means intended to be a complete test, and does not exercise any
 * of the actual functionality of the functions within the module. We strongly
 * recommend you implement further tests as appropriate for your module use
 * case.
 */
require_once '_bootstrap.php';

class WHMCSSWHostingRegistrarModuleTest extends PHPUnit\Framework\TestCase
{

    public static function providerCoreFunctionNames()
    {
        return array(
            array('RegisterDomain'),
            array('TransferDomain'),
            array('RenewDomain'),
            array('GetNameservers'),
            array('SaveNameservers'),
            array('GetContactDetails'),
            array('SaveContactDetails'),
        );
    }

    /**
     * Test Core Module Functions Exist
     *
     * This test confirms that the functions we recommend for all registrar
     * modules are defined for the sample module
     *
     * @param $moduleName
     *
     * @dataProvider providerCoreFunctionNames
     */
    public function testCoreModuleFunctionsExist($moduleName)
    {
        $this->assertTrue(function_exists('swhosting_' . $moduleName));
    }
}
