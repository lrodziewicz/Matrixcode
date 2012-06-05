<?php
/**
 * Matrixcode_Renderer_AbstractRenderer
 *
 * @package    Matrixcode
 * @copyright  Copyright (c) 2009-2011 Peter Minne <peter@inthepocket.mobi>
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Matrixcode_Renderer_AbstractRenderer
{
	
	/**
     * Namespace of the renderer for autoloading
     * @var string
     */
    protected $_rendererNamespace = 'Matrixcode_Renderer';
    
    /**
     * Renderer type
     * @var string
     */
    protected $_type;
    
    /**
     * Matrixcode object
     * @var Matrixcode_Abstract
     */
	protected $_matrixcode;
	
	/**
	 * Whether to return the result or send it to the client
	 * An array can be used to specify additional headers that should be sent along
	 * (f.i. a Content-Disposition header to send output as attachment)
	 * @var boolean | array
	 */
	protected $_send_result = true;
	
	
	
	/**
     * Constructor
     * @param array | Zend_Config $options 
     * @return void
     */
    public function __construct ($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }
        $this->_type = strtolower(substr(get_class($this), strlen($this->_rendererNamespace) + 1));
    }

    
    /**
     * Set matrixcode state from options array
     * @param Zend_Config $config
     * @return Matrixcode_Abstract
     */
    public function setOptions($options)
    {
    	foreach ($options as $key => $value) {
    		$normalized = ucfirst($key);
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
   
	/**
     * Set matrixcode state from config object
     * @param Zend_Config $config
     * @return Matrixcode_Abstract
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }
	
    
    /**
     * Retrieve renderer type
     * @return string
     */
    public function getType()
    {
    	return $this->_type;
    }
    
    
    /**
     * Set the 'send result' flag
     * @param bool|array $bool
     */
    public function setSendResult($value)
    {
    	$this->_send_result = $value;
    	return $this;
    }
    
    /**
     * Retrieve the 'send result' flag
     * @return bool
     */
    public function getSendResult()
    {
    	return $this->_send_result;
    }
	
	
    /**
     * Set the matrix code
     * @param Matrixcode_Abstract $matrixcode
     */
	public function setMatrixcode(Matrixcode_AbstractMatrixcode $matrixcode)
	{
		$this->_matrixcode = $matrixcode;
		return $this;
	}
	
	
	/**
	 * Converts decimal color into HTML notation
	 * @param int $color
	 */
	protected function _decimalToHTMLColor($color)
	{
		return str_pad(dechex($color),6,'0',STR_PAD_LEFT);
	}
	
	
	/**
	 * Render method
	 */
	public function render()
	{
		$this->_checkParams();
		return $this->_renderMatrixcode();
	}
	
	
	/**
     * Checking of parameters after all settings
     *
     * @return void
     */
    abstract protected function _checkParams();
    
    
    /**
     * Method that prepares the matrix
     * @return array
     */
    abstract protected function _renderMatrixcode();
	
}