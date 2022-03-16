<?php

namespace Core\Shivalik\Managers\Implementation;

use Core\Shivalik\Entities\OfficeBonus;
use Core\Shivalik\Entities\VirtualMoney;
use Core\Shivalik\Managers\VirtualMoneyDAOManager;
use PHPBackend\Dao\DAOException;
use PHPBackend\Dao\UtilitaireSQL;
use PHPBackend\Dao\DefaultDAOInterface;

/**
 *
 * @author Esaie MHS
 *        
 */
class VirtualMoneyDAOManagerImplementation1 extends DefaultDAOInterface implements VirtualMoneyDAOManager {

	/**
	 * {@inheritDoc}
	 * @see \PHPBackend\Dao\DAOInterface::update()
	 */
	public function update($entity, $id) : void {
		throw new DAOException("unsupported operation");
	}
	
    /**
     * {@inheritDoc}
     * @see \PHPBackend\Dao\DAOInterface::createInTransaction()
	 * @param VirtualMoney $entity
     */
    public function createInTransaction($entity, \PDO $pdo): void
    {
        $id = UtilitaireSQL::insert($pdo, $this->getTableName(), [
			'amount' => $entity->getAmount(),
		    'expected' => $entity->getExpected(),
		    'request' => $entity->getRequest()!=null? $entity->getRequest()->getId() : null,
			'office' => $entity->getOffice()->getId(),
            self::FIELD_DATE_AJOUT => $entity->getFormatedDateAjout()            
        ]);
		$entity->setId($id); 
		
		foreach ($entity->getDebts() as $d) {
		    UtilitaireSQL::update($pdo, "GradeMember", [
		        'virtualMoney' => $id		        
		    ], $d->getId());
		}
		
		//EVOIE DU BONUS
		if ($entity->getBonus()->getAmount()>0) {//ssi suppieur a zero
		    $this->getDaoManager()->getManagerOf(OfficeBonus::class)->createInTransaction($entity->getBonus(), $pdo);
		}
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\VirtualMoneyDAOManager::findByOffice()
     */
    public function findByOffice (int $officeId) {
        return UtilitaireSQL::findAll($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), self::FIELD_DATE_AJOUT, true, ['office' => $officeId]);
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\VirtualMoneyDAOManager::checkByOffice()
     */
    public function checkByOffice (int $officeId) : bool {
        return $this->columnValueExist('office', $officeId);
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\VirtualMoneyDAOManager::findByRequest()
     */
    public function findByRequest (int $requestId) : VirtualMoney {
        return UtilitaireSQL::findUnique($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), "request", $requestId);
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\VirtualMoneyDAOManager::checkByRequest()
     */
    public function checkByRequest (int $requestId) : bool {
        return $this->columnValueExist('request', $requestId);
    }
    
    
    /**
     * verification de l'historique des operations effectuer pas un office
     * @param int $officeId
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param int $limit
     * @param int $offset
     * @return bool
     */
    public function checkCreationHistoryByOffice (int $officeId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : bool {
        return UtilitaireSQL::hasCreationHistory($this->getConnection(), $this->getTableName(), self::FIELD_DATE_AJOUT, true, $dateMin, $dateMax, ['office' => $officeId], $limit, $offset);
    }
    
    /**
     * recuperation des l'historique des operations effectuer par un office
     * @param int $officeId
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param int $limit
     * @param int $offset
     * @return VirtualMoney[]
     */
    public function findCreationHistoryByOffice (int $officeId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : array {
        return UtilitaireSQL::findCreationHistory($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), self::FIELD_DATE_AJOUT, true, $dateMin, $dateMax, ['office' => $officeId], $limit, $offset);
    }

}

