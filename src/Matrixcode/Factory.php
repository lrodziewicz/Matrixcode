<?php
namespace Matrixcode;

/**
 * Class for generating matrix codes (2 dimensional scan codes)
 *
 * @package    Matrixcode
 * @copyright  Copyright (c) 2009-2011 Peter Minne <peter@inthepocket.mobi>
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Factory
{
    
    /**
     * Factory for Matrixcode_Abstract classes.
     *
     * First argument should be a string containing the base of the adapter class
     * name, e.g. 'qrcode' corresponds to class Matrixcode_Qrcode.  This
     * is case-insensitive.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The matrixcode class base name is read from the 'matrixcode' property.
     * The matrixcode config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the matrixcode constructor.
     *
     * If the first argument is of type Zend_Config and contains a 'params' key, it is assumed to contain
     * all parameters, and the second argument is ignored.
     * 
     * The third parameter specifies the type of renderer, where the fourth parameter is an array or 
     * Zend_Config object containing the renderer parameters
     *
     * @param string | array | ArrayAccess $matrixcode
     * @param array | ArrayAccess $matrixcodeConfig
     * @param string $renderer OPTIONAL
     * @param array | Zend_Config $rendererConfig OPTIONAL
     * @return Matrixcode
     * @throws Matrixcode\Exception
     */
    public static function factory (
    	$matrixcode,
    	$matrixcodeConfig = array(),
    	$renderer = 'image',
    	$rendererConfig = array()
    ) {
        /*
         * Convert Zend_Config argument to plain string
         * matrixcode name and separate config object.
         */
        if ($matrixcode instanceof Zend_Config) {
            if (isset($matrixcode->rendererParams)) {
                $rendererConfig = $matrixcode->rendererParams->toArray();
            }
            if (isset($matrixcode->renderer)) {
                $renderer = (string) $matrixcode->renderer;
            }
            if (isset($matrixcode->matrixcodeParams)) {
                $matrixcodeConfig = $matrixcode->matrixcodeParams->toArray();
            }
            if (isset($matrixcode->matrixcode)) {
                $matrixcode = (string) $matrixcode->matrixcode;
            } else {
                $matrixcode = null;
            }
        }
        
        $matrixcode = new \Matrixcode\QRCode($matrixcode, $matrixcodeConfig);
        $renderer = \Matrixcode\Renderer\Factory::factory($renderer, $rendererConfig);

        $renderer->setMatrixcode($matrixcode);
        
        return $matrixcode;
    }

    
    
	/**
     * Matrixcode Constructor
     *
     * @param mixed $matrixcode        String name of matrixcode class, or Zend_Config object.
     * @param mixed $matrixcodeConfig  OPTIONAL; an array or Zend_Config object with matrixcode parameters
     * @return Matrixcode_Abstract
     */
    public static function getMatrixcode($matrixcode, $matrixcodeConfig = array())
    {
        /*
         * Convert Zend_Config argument to plain string
         * matrixcode name and separate config object.
         */
        if ($matrixcode instanceof Zend_Config) {
            if (isset($matrixcode->matrixcodeParams) && $matrixcode->matrixcodeParams instanceof Zend_Config) {
                $matrixcodeConfig = $matrixcode->matrixcodeParams->toArray();
            }
            if (isset($matrixcode->matrixcode)) {
                $matrixcode = (string) $matrixcode->matrixcode;
            } else {
                $matrixcode = null;
            }
        }
        if ($matrixcodeConfig instanceof Zend_Config) {
            $matrixcodeConfig = $matrixcodeConfig->toArray();
        }

        /*
         * Verify that matrixcode parameters are in an array.
         */
        if (!is_array($matrixcodeConfig)) {
            require_once 'Matrixcode/Exception.php';
            throw new Matrixcode_Exception(
                'Matrixcode parameters must be in an array or a Zend_Config object'
            );
        }

        /*
         * Verify that a matrixcode name has been specified.
         */
        if (!is_string($matrixcode) || empty($matrixcode)) {
            require_once 'Zend/Matrixcode/Exception.php';
            throw new Zend_Matrixcode_Exception(
                'Matrixcode name must be specified in a string'
            );
        }
        
        /*
         * Form full matrixcode class name
         */
        $matrixcodeNamespace = 'Matrixcode';
        if (isset($matrixcodeConfig['matrixcodeNamespace'])) {
            $matrixcodeNamespace = $matrixcodeConfig['matrixcodeNamespace'];
        }
        $matrixcodeName = strtolower($matrixcodeNamespace . '_' . $matrixcode);
        $matrixcodeName = str_replace(' ', '_', ucwords(str_replace('_', ' ', $matrixcodeName)));

        /*
         * Load the matrixcode class.
         */
        if (!class_exists($matrixcodeName)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($matrixcodeName);
        }

        /*
         * Create an instance of the matrixcode class.
         */
        $mcAdapter = new $matrixcodeName($matrixcodeConfig);

        /*
         * Verify that the object created is a descendent of the abstract matrixcode type.
         */
        if (!$mcAdapter instanceof Matrixcode_Abstract) {
            require_once 'Matrixcode/Exception.php';
            throw new Matrixcode_Exception(
                "Matrixcode class '$matrixcodeName' does not extend Matrixcode_Abstract"
            );
        }
        return $mcAdapter;
    }
    
    
    
    /**
     * Proxy to renderer render() method
     * 
     * @param string | array | Zend_Config $matrixcode
     * @param array | Zend_Config $matrixcodeConfig
     * @param string $renderer OPTIONAL
     * @param array | Zend_Config $rendererConfig OPTIONAL
     * @return mixed
     */
    public static function render (
    	$matrixcode,
    	$matrixcodeConfig = array(),
    	$renderer = 'image',
    	$rendererConfig = array()
    ) {
        return self::factory ($matrixcode, $matrixcodeConfig, $renderer, $rendererConfig)->render();
    }

}