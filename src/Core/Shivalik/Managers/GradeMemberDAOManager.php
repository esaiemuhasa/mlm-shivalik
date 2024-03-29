<?php
namespace Core\Shivalik\Managers;

use Core\Shivalik\Entities\GradeMember;
use PHPBackend\Dao\DAOException;
use PHPBackend\Dao\DAOInterface;

/**
 *
 * @author Esaie MHS
 *        
 */
interface GradeMemberDAOManager extends DAOInterface
{

    /**
     * revoie l'actuel packet du membre dont l'id est en paramtres
     * (le packet envoyer est celui qui est actuelement activer)
     * @param int $memberId
     * @return GradeMember
     * @throws DAOException
     */
    public function findCurrentByMember (int $memberId) : GradeMember ;
    
    /**
     * renveoie la demande de mise en jour du packet d'un utilisateur
     * @param int $memberId
     * @return GradeMember
     */
    public function findRequestedByMember (int $memberId) : GradeMember;
    
    /**
     * L'office en parametre a-t-elle deja effectuee aumoin une operation???
     * Lors de la verification, si le parametre virtual veau:
     * <ul>
     * <li>null: alors la colonne de la reference des virtuel est omise dans la close WHERE</li>
     * <li>true: on recupere uniquement les operations dont la retocession a deja eu lieux</li>
     * <li>false: on recupere les operation dont la retrocession n'as pas enore leux lieux</li>
     * </ul>
     * @param int $officeId
     * @param bool $upgrade
     * @param bool $virtual 
     * @return bool
     */
    public function checkByOffice (?int $officeId, ?bool $upgrade = null, ?bool $virtual = null) : bool;
    
    /**
     * revoie une collection d'operation effectuer par un bureau
     * si le parametre virtual veau:
     * <ul>
     * <li>null: alors la colonne de la reference des virtuel est omise dans la close WHERE</li>
     * <li>true: on recupere uniquement les operations dont la retocession a deja eu lieux</li>
     * <li>false: on recupere les operation dont la retrocession n'as pas enore leux lieux</li>
     * </ul>
     * @param int $officeId
     * @param bool $upgrade
     * @param bool $virtual
     * @return GradeMember[]
     */
    public function findByOffice (?int $officeId, ?bool $upgrade = null, ?bool $virtual = null);
    
    /**
     * Verifie s'il a des operation dont la rertrocession n'a pas encore eu lieux
     * @param int $officeId
     * @return bool
     * @deprecated 09/2022
     */
    public function hasUnpaid (?int $officeId) : bool;
    
    /**
     * renvoie une collection des operations dont la retrocession n'as pas encore eu lieux
     * si l'officeId est omise, alors on verfie pour tout les offices dans le systeme
     * @param int $officeId
     * @return GradeMember[]
     * @throws DAOException s'il y a erreur lors de la communication avec la BDD ou aucune operations
     * @deprecated  09/2022
     */
    public function findUnpaid (?int $officeId) : array;
    
    /**
     * y-a-il aumoin une operation pour le virtual en parametre??
     * @param int $virtualId
     * @return bool
     * @deprecated 09/2022
     */
    public function hasDebts (?int $virtualId = null) : bool ;
    
    /**
     * Revoie une collection d'operation en dettes 
     * donc les operation qui ont impacter sur le montant virtual
     * @param int $virtualId
     * @return GradeMember[]
     * @deprecated 09/2022
     */
    public function findDebts (?int $virtualId = null);
    
    
    /**
     * Renvoie la collection des packets en attente d'activation
     * @return GradeMember[]
     * @throws DAOException
     */
    public function findAllRequest ();
    
    /**
     * Verifie s'il y a des packets en attente d'activation.
     * lorsque les packet sont en attente d'activation, les PV et l'argent n'est pas encore dispatcher
     * @return bool
     */
    public function checkRequest () : bool;
    
    /**
     * @param int $memberId
     * @return bool
     */
    public function checkCurrentByMember (int $memberId) : bool;
    
    /**
     * @param int $memberId
     * @return bool
     */
    public function checkRequestedByMember (int $memberId) : bool;
    
    /**
     * @param GradeMember $gm
     * @throws DAOException
     */
    public function upgrade (GradeMember $gm) : void ;
    
    /**
     * comptage des operations d'apgrade de compte??
     * @param int $officeId
     * @return int
     */
    public function countUpgrades (?int $officeId = null) : int ;
    
    /**
     * Activation de packet d'un utilisateur
     * c lors de l'appel a cette methode que les le bonus sont repartie
     * @param GradeMember $gm
     * @throws DAOException s'il y a erreur lors du partage des bonus
     */
    public function enable (GradeMember $gm) : void ;
    
    
    /**
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return bool
     * @throws DAOException
     */
    public function checkUpgradeHistory(\DateTime $dateMin, \DateTime $dateMax = null, ?int $officeId=null, ?int $limit = null, int $offset = 0) : bool;
    
    /**
     * comptage de upgrade de comptes
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param int $officeId
     * @return int
     * @throws DAOException
     */
    public function countUpgradeHistory (\DateTime $dateMin, \DateTime $dateMax = null, ?int $officeId=null) : int;
    
    /**
     * reguperation de l'historique pour Upgrade
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return GradeMember[]
     */
    public function findUpgradeHistory(\DateTime $dateMin, \DateTime $dateMax = null, ?int $officeId=null, ?int $limit = null, int $offset = 0);
    
    
    /**
     * verification de l'historique des operations effectuer pas un office
     * @param int $officeId
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param int $limit
     * @param int $offset
     * @return bool
     */
    public function checkCreationHistoryByOffice (int $officeId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : bool ;
    
    /**
     * recuperation des l'historique des operations effectuer par un office
     * @param int $officeId
     * @param \DateTime $dateMin
     * @param \DateTime $dateMax
     * @param int $limit
     * @param int $offset
     * @return bool
     */
    public function findCreationHistoryByOffice (int $officeId, \DateTime $dateMin, \DateTime $dateMax = null, ?int $limit = null, int $offset= 0) : bool ;
    
    
    /**
     * chargement des donnees du grade d'un membre
     * @param GradeMember|int $gradeMember
     * @return GradeMember
     */
    public function load ($gradeMember) : GradeMember;
    
    /**
     * Comptage des inscriptions d'un compte membre 
     * @param int $member
     * @return int
     * @throws DAOException 
     */
    public function countByMember (int $member) : int;
    
    /**
     * selection de la collection des grades du compte d'un membre
     * @param int $member
     * @return GradeMember[]
     * @throws DAOException aucune inscription, ou une erreur est survenue lors dela communication avec le systeme
     * de gestion de base de donnee
     */
    public function findByMember (int $member) : array;
    
}

