<?php
/**
 * Matrixcode_Renderer_Eps
 *
 * @package    Matrixcode
 * @copyright  Copyright (c) 2009-2011 Peter Minne <peter@inthepocket.mobi>
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * Thanks to Paul Bourke http://local.wasp.uwa.edu.au/~pbourke/dataformats/postscript/
 */
class Matrixcode_Renderer_Eps extends Matrixcode_Renderer_AbstractRenderer
{

    /**
     * Retrieve the scale of the code
     * @return int
     * @throws Matrixcode_Renderer_Exception
     */
    public function getScale()
    {
        $module_size = $this->_matrixcode->getModuleSize();
        if ($module_size[0] != $module_size[1]) {
            throw new Matrixcode_Renderer_Exception(
                'So far only square modules are supported. The current module size settings of '.$module_size[0].'x'.$module_size[1].' indicate a different rectangular shape.'
            );
        }

        return $module_size[0];
    }

    /**
     * @see Matrixcode_Renderer_Abstract::_checkParams()
     */
    protected function _checkParams() {}

    /**
     * @see Matrixcode_Renderer_Abstract::_renderMatrixcode()
     */
    protected function _renderMatrixcode()
    {
        $padding = $this->_matrixcode->getPadding();

        $this->_matrixcode->draw();
        $matrix_dimension = count($this->_matrixcode->getMatrix());

        $matrix_dim_with_padding_x = $matrix_dimension + $padding[1] + $padding[3];
        $matrix_dim_with_padding_y = $matrix_dimension + $padding[0] + $padding[2];

        // Scaling
        $scale = $this->getScale();
        $output_size_width = $matrix_dim_with_padding_x * $scale;
        $output_size_height = $matrix_dim_with_padding_y * $scale;

        // Set colors/transparency
        $fore_color = $this->_matrixcode->getForeColor();
        $back_color = $this->_matrixcode->getBackgroundColor();
        // convert a hexadecimal color code into decimal eps format (green = 0 1 0, blue = 0 0 1, ...)
        $r = round((($fore_color & 0xFF0000) >> 16) / 255, 5);
        $b = round((($fore_color & 0x00FF00) >> 8) / 255, 5);
        $g = round(($fore_color & 0x0000FF) / 255, 5);
          $fore_color = $r.' '.$g.' '.$b;

          $output =
        '%!PS-Adobe EPSF-3.0'."\n".
        '%%Creator: Matrixcode_Qrcode'."\n".
        '%%Title: QRcode'."\n".
        '%%CreationDate: '.date('Y-m-d')."\n".
        '%%DocumentData: Clean7Bit'."\n".
        '%%LanguageLevel: 2'."\n".
        '%%Pages: 1'."\n".
        '%%BoundingBox: 0 0 '.$output_size_width.' '.$output_size_height."\n";

        // set the scale
        $output .= $scale.' '.$scale.' scale'."\n";
        // position the center of the coordinate system
        $output .= $padding[3].' '.$padding[2].' translate'."\n";

        // redefine the 'rectfill' operator to shorten the syntax
        $output .= '/F { rectfill } def'."\n";
        // set the symbol color
        $output .= $fore_color.' setrgbcolor'."\n";

        // Convert the matrix into pixels
        $matrix = $this->_matrixcode->getMatrix();
        for ($i=0; $i<$matrix_dimension; $i++) {
            for ($j=0; $j<$matrix_dimension; $j++) {
                if ($matrix[$i][$j]) {
                    $x = $i;
                    $y = $matrix_dimension - 1 - $j;
                    $output .= $x.' '.$y.' 1 1 F'."\n";
                }
            }
        }

        $output .=
        '%%EOF';

        if ($this->_send_result) {
            $this->_sendOutput($output);
        } else {
            return $output;
        }

        return;
    }

    protected function _sendOutput($output)
    {
        if (is_array($this->_send_result)) {
            foreach ($this->_send_result as $header) {
                header($header);
            }
        }

        header("Content-Type: application/postscript");
        echo $output;

        exit();
    }

}
