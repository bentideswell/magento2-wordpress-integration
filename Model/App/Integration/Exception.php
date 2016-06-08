<?php
/**
 * @var string
**/	
namespace FishPig\WordPress\Model\App\Integration;

/**
 *
**/
class Exception extends \Exception
{
	/**
	 * @var string
	**/
	protected $_error = null;

	/**
	 *
	 *
	 * @return 
	**/
	static public function throwException($message, $error = '')
	{
		$class = get_called_class();
		
		$exception = new $class($message);

		$exception->setRawErrorMessage($error);
		
		throw $exception;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function setRawErrorMessage($error)
	{
		$this->_error = $error;
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getRawErrorMessage()
	{
		return $this->_error;
	}
	
	public function getFullMessage()
	{
		return $this->getMessage() . ($this->getRawErrorMessage() ? 'Error: ' . $this->getRawErrorMessage() : '');
	}
}
