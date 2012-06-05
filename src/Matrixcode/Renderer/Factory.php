<?php
namespace Matrixcode\Renderer;

use Matrixcode\Renderer;

class Factory {
	/**
     * Renderer Constructor
     *
     * @param mixed $renderer           String name of renderer class.
     * @param mixed $rendererConfig     OPTIONAL; an array with renderer parameters.
     * @return Matrixcode\Renderer\Abstract
     */
    public static function factory($renderer = 'image', $rendererConfig = array())
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
            $e = new \Matrixcode\Exception(
                'Renderer parameters must be in an array or a Zend_Config object'
            );
            throw $e;
        }

        /*
         * Verify that a renderer name has been specified.
         */
        if (!is_string($renderer) || empty($renderer)) {
            $e = new \Matrixcode\Exception(
                'Renderer name must be specified in a string'
            );
            throw $e;
        }

        /*
         * Form full renderer class name
         */
        /*$rendererNamespace = 'Matrixcode\Renderer';
        if (isset($rendererConfig['rendererNamespace'])) {
            $rendererNamespace = $rendererConfig['rendererNamespace'];
        }
        $rendererName = strtolower($rendererNamespace . '_' . $renderer);
        $rendererName = str_replace(' ', '_', ucwords(str_replace( '_', ' ', $rendererName)));*/
        $rendererName = '\Matrixcode\Renderer\\'.ucwords($renderer);

        /*
         * Create an instance of the renderer class.
         */
        $rdrAdapter = new $rendererName($rendererConfig);

        /*
         * Verify that the object created is a descendent of the abstract renderer type.
         */
        if (!$rdrAdapter instanceof \Matrixcode\Renderer\AbstractRenderer) {
            $e = new \Matrixcode\Exception(
                "Renderer class '$rendererName' does not extend Matrixcode\Renderer\AbstractRenderer"
            );
            throw $e;
        }
        return $rdrAdapter;
    }
}