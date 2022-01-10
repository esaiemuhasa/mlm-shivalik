<?php

namespace Core\Shivalik\Entities;

use PHPBackend\DBEntity;
use PHPBackend\PHPBackendException;

/**
 *
 * @author Esaie MHS
 *        
 */
class RequestVirtualMoney extends DBEntity {
	
	/**
	 * @var number
	 */
	private $amount;
	
	/**
	 * @var Office
	 */
	private $office;
	
	/**
	 * @var VirtualMoney
	 */
	private $response;
	
	/**
	 * @var boolean
	 */
	private $waiting;
	
	/**
	 * @return number
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return Office
	 */
	public function getOffice() : ?Office {
		return $this->office;
	}

	/**
	 * @return VirtualMoney 
	 */
	public function getResponse() : ?VirtualMoney{
		return $this->response;
	}

	/**
	 * @param number $amount
	 */
	public function setAmount($amount) : void{
		$this->amount = $amount;
	}

	/**
	 * @param Office $office
	 */
	public function setOffice($office) : void {
		
		if ($office == null || $office instanceof Office) {
			$this->office = $office;
		}else if (self::isInt($office)) {
			$this->office = new Office(array('id' => $office));
		}else {
		    throw new PHPBackendException("invalid value in setOffice param method");
		}
	}

	/**
	 * @param VirtualMoney  $response
	 */
	public function setResponse($response) : void {
		if ($response == null || $response instanceof VirtualMoney) {
			$this->response = $response;
		}else if (self::isInt($response)) {
			$this->response = new VirtualMoney(array('id' => $response));
		} else {
		    throw new PHPBackendException("Invalid param value in setResponse Method");
		}
	}

}
