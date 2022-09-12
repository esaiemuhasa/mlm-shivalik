<?php
namespace Core\Shivalik\Managers\Implementation;

use Core\Shivalik\Managers\OperationDAOManager;
use PHPBackend\Dao\DAOException;
use PHPBackend\Dao\DefaultDAOInterface;
use PHPBackend\Dao\UtilitaireSQL;

/**
 *
 * @author Esaie MUHASA
 *        
 */
abstract class AbstractOperationDAOManager extends DefaultDAOInterface implements OperationDAOManager
{
    
    /**
     * {@inheritDoc}
     * @see \PHPBackend\Dao\DAOInterface::update()
     */
    public function update($entity, $id) : void
    {
        throw new DAOException("no subsequent update of the benefit is authorized");
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\OperationDAOManager::checkByMember()
     */
    public function checkByMember (int $memberId) :bool {
        return $this->columnValueExist('member', $memberId);
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\OperationDAOManager::countByMember()
     */
    public function countByMember (int $memberId) : int {
        return UtilitaireSQL::count($this->getConnection(), $this->getTableName(), array("member" => $memberId));
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\OperationDAOManager::findByMember()
     */
    public function findByMember (int $memberId, ?int $limit = null, int $offset = 0) : array {
        return UtilitaireSQL::findAll($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), self::FIELD_DATE_AJOUT, true, array("member"=> $memberId), $limit, $offset);
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\OperationDAOManager::checkHistoryByMember()
     */
    public function checkHistoryByMember (int $memberId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : bool {
        return UtilitaireSQL::hasCreationHistory($this->getConnection(), $this->getTableName(), self::FIELD_DATE_AJOUT, true, $dateMin, $dateMax, ['member' => $memberId], $limit, $offset);
    }
    
    /**
     * {@inheritDoc}
     * @see \Core\Shivalik\Managers\OperationDAOManager::findHistoryByMember()
     */
    public function findHistoryByMember (int $memberId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : array {
        return UtilitaireSQL::findCreationHistory($this->getConnection(), $this->getTableName(), $this->getMetadata()->getName(), self::FIELD_DATE_AJOUT, true, $dateMin, $dateMax, ['member' => $memberId], $limit, $offset);
    }
}
