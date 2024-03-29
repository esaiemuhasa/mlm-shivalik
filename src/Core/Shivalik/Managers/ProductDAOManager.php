<?php
namespace Core\Shivalik\Managers;

use Core\Shivalik\Entities\Product;
use PHPBackend\Dao\DAOException;
use PHPBackend\Dao\DAOInterface;

/**
 *
 * @author Esaie MUHASA
 *        
 */
interface ProductDAOManager extends DAOInterface
{
    
    /**
     * verification du nom d'un produit dans la BDD.
     * dans le cas où le parametre id est different de null, alors la verification est faite en faisant 
     * abstraction à l'occurence proprietaire de l'id en deuxième parametre
     * @param string $name
     * @param int $id
     * @return bool
     * @throws DAOException
     */
    public function checkByName (string $name, ?int $id = null) : bool ;
    
    /**
     * renvoie le produit dont le nom est en parametre
     * @param string $name
     * @return Product
     * @throws DAOException
     */
    public function findByName (string $name) : Product;
    
    /**
     * mis en jour de la photo d'un produit
     * @param int $id
     * @param string $path
     * @throws DAOException s'il y a erreur lors de la communication avec la BDD
     */
    public function updatePicture (int $id, string $path) : void;
    
    /**
     * Verification de l'existance des produit dans la categie pour l'intervale de selection en parametre
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return bool
     * @throws DAOException
     */
    public function checkByCategory (int $categoryId, ?int $limit = null, int $offset = 0) : bool;
    
    /**
     * Renvoie une collection des produit appartenant a la categie en premier parametre.
     * <br>La limite dans la selection est pris en compte lors que $limit != null. et doit etre une valeur superieur a 0
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws DAOException dans le cas où il y a une erreur lors de la communication avec le SGBD, 
     * ou s'il n'y a aucun resultat pour la requette de selection
     */
    public function findByCategory (int $categoryId, ?int $limit = null, int $offset = 0) : array;
    
    /**
     * Compteage des produit appartenant a une categie
     * @param int $categoryId
     * @return int
     * @throws DAOException s'il ya erreur lors de la communication avec le SGBD
     */
    public function countByCategory (int $categoryId) : int;
    
    /**
     * Comptage du nombre des produits dont leurs stocks ne sont pas vide pour l'office en parametre
     * @param int $officeId
     * @return int
     * @return DAOException s'il y a erreur lors de la communication avec le SGBD
     */
    public function countVailableByOffice (int $officeId) : int;
    
    /**
     * verifie l'existance de produit ayant des stocks non vide pour l'office en premier parametre
     * @param int $officeId
     * @param int $limit
     * @param int $offset
     * @return bool
     * @throws DAOException s'il y a erreur losrs de la communication avec le SGBD
     */
    public function checkVailableByOffice (int $officeId, ?int $limit = null, int $offset = 0) : bool;
    
    /**
     * renvoie la collection des produits disponible dans l'office en premier parametre.
     * (sock auxiliaire non vide)
     * @param int $officeId
     * @param int $limit
     * @param int $offset
     * @return Product[]
     * @throws DAOException s'il y a erreur lors de la communication avec le SGBD, ou aucun resultat
     */
    public function findVailableByOffice (int $officeId, ?int $limit = null, int $offset = 0) : array;

    /**
     * renvoie la colection des produits trier en ordre aphabetique
     *
     * @param int|null $limit
     * @param int $offset
     * @return Product[]
     * @throws DAOException s'il y erreur lors dela communication avec le SGBD, soit aucun resultat returner par la requette de selection
     */
    public function findSortedByName (?int $limit = null, int $offset = 0) : array;
}

