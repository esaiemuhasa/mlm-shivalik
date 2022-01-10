<?php
namespace Core\Shivalik\Managers\Implementation;

use Core\Shivalik\Managers\LocalisationDAOManager;
use PHPBackend\DAOException;
use PHPBackend\Dao\DAOEvent;
use PHPBackend\Dao\UtilitaireSQL;

/**
 *
 * @author Esaie MHS
 *        
 */
class LocalisationDAOManagerImplementation1 extends LocalisationDAOManager
{

    /**
     * {@inheritDoc}
     * @see \PHPBackend\Dao\DAOInterface::update()
     */
    public function update($entity, $id) : void
    {
        try {
            $pdo = $this->getConnection();
            if ($pdo->beginTransaction()) {
                $this->updateInTransaction($entity, $id, $pdo);
                $pdo->commit();
            }else {
                throw new DAOException("An error occurred while creating the transaction");
            }
        } catch (\PDOException $e) {
            throw new DAOException($e->getMessage(), DAOException::ERROR_CODE, $e);
        }
        $event = new DAOEvent($this, DAOEvent::TYPE_UPDATION, $entity);
        $this->dispatchEvent($event);
    }
    
    /**
     * {@inheritDoc}
     * @see \PHPBackend\Dao\DAOInterface::createInTransaction()
     */
    public function createInTransaction($entity, \PDO $pdo): void
    {
        $id = UtilitaireSQL::insert($pdo, $this->getTableName(), [
            'country' => $entity->getCountry()->getId(),
            'city' => $entity->getCity(),
            'district' => $entity->getDistrict()            
        ]);
        $entity->setId($id);
    }

    /**
     * {@inheritDoc}
     * @see \PHPBackend\Dao\DefaultDAOInterface::updateInTransaction()
     */
    public function updateInTransaction($entity, $id, \PDO $pdo): void
    {
        UtilitaireSQL::update($pdo, $this->getTableName(), [            
            'country' => $entity->getCountry()->getId(),
            'city' => $entity->getCity(),
            'district' => $entity->getDistrict()
        ], $id);
        
    }

}
