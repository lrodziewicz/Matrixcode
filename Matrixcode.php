<?php
/**
 * Class for generating matrix codes (2 dimensional scan codes)
 *
 * @package    Matrixcode
 * @copyright  Copyright (c) 2009-2011 Peter Minne <peter@inthepocket.mobi>
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Matrixcode
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
     * @param string | array | Zend_Config $matrixcode
     * @param array | Zend_Config $matrixcodeConfig
     * @param string $renderer OPTIONAL
     * @param array | Zend_Config $rendererConfig OPTIONAL
     * @return Zend_Matrixcode_Abstract
     * @throws Zend_Matrixcode_Exception
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
        
        try {
            $matrixcode = self::getMatrixcode($matrixcode, $matrixcodeConfig);
            $renderer = self::getRenderer($renderer, $rendererConfig);
        } catch (Zend_Exception $e) {
            throw $e;
        }

        $renderer->setMatrixcode($matrixcode);
        return $renderer;
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
     * Renderer Constructor
     *
     * @param mixed $renderer           String name of renderer class, or Zend_Config object.
     * @param mixed $rendererConfig     OPTIONAL; an array or Zend_Config object with renderer parameters.
     * @return Matrixcode_Renderer_Abstract
     */
    public static function getRenderer($renderer = 'image', $rendererConfig = array())
    {
        /*
         * Convert Zend_Config argument to plain string
         * renderer name and separate config object.
         */
        if ($renderer instanceof Zend_Config) {
            if (isset($renderer->rendererParams)) {
                $rendererConfig = $renderer->rendererParams->toArray();
            }
            if (isset($renderer->renderer)) {
                $renderer = (string) $renderer->renderer;
            }
        }
        if ($rendererConfig instanceof Zend_Config) {
            $rendererConfig = $rendererConfig->toArray();
        }

        /*
         * Verify that renderer parameters are in an array.
         */
        if (!is_array($rendererConfig)) {
            require_once 'Matrixcode/Exception.php';
            $e = new Matrixcode_Exception(
                'Renderer parameters must be in an array or a Zend_Config object'
            );
            throw $e;
        }

        /*
         * Verify that a renderer name has been specified.
         */
        if (!is_string($renderer) || empty($renderer)) {
            require_once 'Matrixcode/Exception.php';
            $e = new Matrixcode_Exception(
                'Renderer name must be specified in a string'
            );
            throw $e;
        }

        /*
         * Form full renderer class name
         */
        $rendererNamespace = 'Matrixcode_Renderer';
        if (isset($rendererConfig['rendererNamespace'])) {
            $rendererNamespace = $rendererConfig['rendererNamespace'];
        }
        $rendererName = strtolower($rendererNamespace . '_' . $renderer);
        $rendererName = str_replace(' ', '_', ucwords(str_replace( '_', ' ', $rendererName)));

        /*
         * Load the renderer class.
         */
        if (!class_exists($rendererName)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rendererName);
        }

        /*
         * Create an instance of the renderer class.
         */
        $rdrAdapter = new $rendererName($rendererConfig);

        /*
         * Verify that the object created is a descendent of the abstract renderer type.
         */
        if (!$rdrAdapter instanceof Matrixcode_Renderer_Abstract) {
            require_once 'Matrixcode/Exception.php';
            $e = new Matrixcode_Exception(
                "Renderer class '$rendererName' does not extend Matrixcode_Renderer_Abstract"
            );
            throw $e;
        }
        return $rdrAdapter;
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