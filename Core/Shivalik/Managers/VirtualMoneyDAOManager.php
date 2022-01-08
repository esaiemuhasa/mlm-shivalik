<?php

namespace Core\Shivalik\Managers;

use Core\Shivalik\Entities\VirtualMoney;
use PHPBackend\Dao\DefaultDAOInterface;
use PHPBackend\Dao\UtilitaireSQL;

/**
 *
 * @author Esaie MHS
 *        
 */
abstract class VirtualMoneyDAOManager extends DefaultDAOInterface {
	
	/**
	 * revoie tout le monais virtuel d'un office
	 * @param int $officeId
	 * @return VirtualMoney[]
	 */
	public function findByOffice (int $officeId) {
	    return UtilitaireSQL::findAll($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), self::FIELD_DATE_AJOUT, true, ['office' => $officeId]);
	}
	
	/**
	 * @param int $officeId
	 * @return bool
	 */
	public function ckeckByOffice (int $officeId) : bool {
		return $this->columnValueExist('office', $officeId);
	}
	
	/**
	 * @param int $requestId
	 * @return VirtualMoney
	 */
	public function findByRequest (int $requestId) : VirtualMoney {
		return UtilitaireSQL::findUnique($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), "request", $requestId);
	}
	
	
	/**
	 * La requette a-t-l aumoin une reponse
	 * @param int $requestId
	 * @return bool
	 */
	public function checkByRequest (int $requestId) : bool {
		return $this->columnValueExist('request', $requestId);
	}
	
	
}

