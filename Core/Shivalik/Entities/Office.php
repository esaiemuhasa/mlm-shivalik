<?php
namespace Core\Shivalik\Entities;

use PHPBackend\DBEntity;
use PHPBackend\PHPBackendException;

/**
 *
 * @author Esaie MHS
 *        
 */
class Office extends DBEntity
{
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $photo;
    
    /**
     * @var boolean
     */
    private $central;
    
    /**
     * @var Localisation
     */
    private $localisation;
    
    /**
     * @var Member
     */
    private $member;
    
    
    /**
     * collection des opperations qui touche la monais virtuel
     * @var GradeMember[]
     */
    private $operations = [];
    
    /**
     * collection des stock virtuel de l'office
     * @var VirtualMoney[]
     */
    private $virtualMoneys = [];
    
    /**
     * @var RequestVirtualMoney[]
     */
    private $requests = [];
    
    
    /**
     * collection des straits effectuer dans un bureau
     * @var Withdrawal[]
     */
    private $withdrawals = [];
    
    
    /**
     * L'actuel size de l'office
     * @var OfficeSize
     */
    private $officeSize;
    
    
    /**
     * @return string
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isCentral() : ?bool
    {
        return $this->central;
    }

    /**
     * @param boolean $central
     */
    public function setCentral($central) : void
    {
        $this->central = $central==true || $central >= 1;
    }

    /**
     * @return string
     */
    public function getPhoto() : ?string
    {
        return $this->photo;
    }

    /**
     * @return Localisation
     */
    public function getLocalisation() : ?Localisation
    {
        return $this->localisation;
    }

    /**
     * @param string $name
     */
    public function setName($name) : void
    {
        $this->name = $name;
    }

    /**
     * @param string $photo
     */
    public function setPhoto($photo) : void
    {
        $this->photo = $photo;
    }

    /**
     * @param Localisation $localisation
     */
    public function setLocalisation($localisation) : void
    {
        if ($this->isInt($localisation)) {
            $this->localisation = new Localisation(array('id' => $localisation));
        }elseif ($localisation  instanceof Localisation || $localisation == null){
            $this->localisation = $localisation;
        }else {
            throw new PHPBackendException("invalid value in setLocation method maparam");
        }
            
    }
    
	/**
	 * @return Member
	 */
	public function getMember() :?Member {
		return $this->member;
	}

	/**
	 * @param Member $member
	 */
	public function setMember($member) : void {
		if ($member == null || $member instanceof Member) {
			$this->member = $member;
		}elseif ($this->isInt($member)){
			$this->member = new Member(array('id' => $member));
		}else {
			throw new PHPBackendException("invalid value in method parameter setMember()");
		}
	}
	
	/**
	 * @return multitype:Withdrawal 
	 */
	public function getWithdrawals() {
		return $this->withdrawals;
	}

	/**
	 * @param multitype:Withdrawal  $withdrawals
	 */
	public function setWithdrawals(array $withdrawals) : void{
		$this->withdrawals = $withdrawals;
	}

	/**
	 * @return GradeMember[]
	 */
	public function getOperations() {
		return $this->operations;
	}
	
	/**
	 * Revoie le solde de operation deja fait par l'office
	 * @return int
	 */
	public function getSoldOperations () : int {
		$solde = 0;
		foreach ($this->operations as $operation) {
			$solde += ($operation->getMembership() + $operation->getProduct());
		}
		return $solde;
	}
	
	/**
	 * renvoie le solde des produits
	 * lorse montant allouer pour l'adhesion a un packet
	 * @return int
	 */
	public function getSoldProduct() : int {
	    $solde = 0;
	    foreach ($this->operations as $operation) {
	        $solde += $operation->getProduct();
	    }
	    return $solde;
	}
	
	/**
	 * Revoie le solde de retro-commission, pour tout les operations deja fait
	 * en ignorant les retrocommissions deja payer
	 * @return int
	 */
	public function getSoldRetroCommission() : int {
	    $solde = 0;
	    foreach ($this->operations as $operation) {
	        if ($operation->getVirtualMoney() == null) {
    	        $solde += $operation->getMembership();
	        }
	    }
	    return $solde;
	}
	
	
	/**
	 * Revoei la somme des dettes de retro-commisssion pour l'ensemble des operations
	 * qu'a deja effecctuer un office, sans que ceux-ci ne soient deja payer
	 * @return int
	 */
	public function getSoldDebt () : int {
	    $debt = 0;
	    foreach ($this->getOperations() as $operation) {
	        if ($operation->getVirtualMoney() == null) {//pour les operations dont la retrocomission est deja au rendez-vous
    	        $debt += $operation->getMembership();
	        }
	        
	    }
	    return $debt;
	}
	
	/**
	 * renvoie le solde du monai virtual deja utiliser
	 * @return int
	 */
	public function getSoldTrashVirtualMoney () : int {
	    return ($this->getSoldProduct() + $this->getSoldRetroCommission());
	}
	
	/**
	 * @return number
	 */
	public function getSoldWithdrawals () {
		$solde = 0;
		foreach ($this->withdrawals as $withdrawal) {
			$solde += $withdrawal->getAmount();
		}
		return $solde;
	}
	
	/**
	 * @return number
	 */
	public function getSoldRequestWithdrawals () {
		$solde = 0;
		foreach ($this->withdrawals as $withdrawal) {
			if ($withdrawal->getAdmin() == null) {
				$solde += $withdrawal->getAmount();
			}
		}
		return $solde;
	}
	
	/**
	 * @return number
	 */
	public function getSoldAcceptWithdrawals () {
		$solde = 0;
		foreach ($this->withdrawals as $withdrawal) {
			if ($withdrawal->getAdmin() != null && $withdrawal->getRaport() == null) {
				$solde += $withdrawal->getAmount();
			}
		}
		return $solde;
	}
	

	/**
	 * @return multitype:VirtualMoney 
	 */
	public function getVirtualMoneys() {
		return $this->virtualMoneys;
	}
	
	/**
	 * revoie le solde disponible de la monais virtuel d'un office
	 * @return int
	 */
	public function getAvailableVirtualMoney () : int {
		$money = 0;
		foreach ($this->getVirtualMoneys() as $virtual) {
			$money += $virtual->getAmount();
		}
		
		$return = ($money- $this->getUsedVirtualMoney());
		
		return $return < 0? 0 : $return;
	}
	
	/**
	 * Aliance de la methode getSoldOperation()
	 * @return int
	 */
	public function getUsedVirtualMoney () : int {
		return $this->getSoldTrashVirtualMoney();
	}
	
	/**
	 * l'office est en dete??
	 * @return bool
	 */
	public function hasDebts () : bool {
		if ($this->getDebts() != 0 ) {
			return true;
		}
		return false;
	}
	
	/**
	 * @return bool
	 */
	public function hasWaiting () : bool {
		$return = false;
		foreach ($this->requests as $request) {
			if (empty($request->getResponses())) {
				return true;
			}
		}
		return $return;
	}
	
	/**
	 * renvoie la dette de l'office
	 * @return int
	 */
	public function getDebts () : int {
		$money = 0;
		foreach ($this->virtualMoneys as $virtual) {
			$money += $virtual->getAmount();
		}
		$return = ($money - $this->getSoldOperations());
		
		return $return < 0? abs($return) : 0;
	}

	/**
	 * @return multitype:RequestVirtualMoney 
	 */
	public function getRequests() {
		return $this->requests;
	}

	/**
	 * @param multitype:RequestVirtualMoney  $requests
	 */
	public function setRequests(array $requests) {
		$this->requests = $requests;
	}

	/**
	 * @param multitype:GradeMember  $operations
	 */
	public function setOperations($operations) {
		$this->operations = $operations;
	}
	
	/**
	 * comptage des upgrade des comptes
	 * @return int
	 */
	public function countUpgrades () : int {
		$nombre = 0;
		foreach ($this->operations as $op) {
			if ($op->getOld() != null) {
				$nombre++;
			}
		}
		return $nombre;
	}
	
	/**
	 * @param multitype:VirtualMoney  $virtualMoneys
	 */
	public function setVirtualMoneys($virtualMoneys) {
		$this->virtualMoneys = $virtualMoneys;
	}
	
    /**
     * @return OfficeSize
     */
    public function getOfficeSize() : ?OfficeSize
    {
        return $this->officeSize;
    }

    /**
     * @param OfficeSize $officeSize
     */
    public function setOfficeSize($officeSize)
    {
        if ($officeSize instanceof OfficeSize || $officeSize == null) {
            $this->officeSize = $officeSize;
        } elseif (self::isInt($officeSize)) {
            $this->officeSize = new OfficeSize(array('id' => $officeSize));
        } else {
            throw new PHPBackendException("invalid param value in setOfficeSize() method");
        }
    }


}
