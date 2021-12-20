<?php

namespace Validators;

use Library\AbstractFormValidator;
use Library\IllegalFormValueException;
use Entities\VirtualMoney;
use Library\DAOException;
use Managers\VirtualMoneyDAOManager;
use Managers\GradeMemberDAOManager;
use Managers\OfficeSizeDAOManager;
use Entities\OfficeBonus;

/**
 *
 * @author Esaie MHS
 *        
 */
class VirtualMoneyFormValidator extends AbstractFormValidator {
	
	const FIELD_AMOUNT = 'amount';
	const FIELD_OFFICE = 'office';
	const FIELD_REQUEST_MONEY = 'request';
	
	/**
	 * @var VirtualMoneyDAOManager
	 */
	private $virtualMoneyDAOManager;
	
	/**
	 * @var GradeMemberDAOManager
	 */
	private $gradeMemberDAOManager;
	
	/**
	 * @var OfficeSizeDAOManager
	 */
	private $officeSizeDAOManager;
	
	/**
	 * @param number $amount
	 * @throws IllegalFormValueException
	 */
	private function validationAmount ($amount) : void {
		if ($amount == null || !preg_match(self::RGX_NUMERIC_POSITIF, $amount)) {
			throw new IllegalFormValueException("must be a numeric value greater than zero");
		}
	}
	
	/**
	 * @param VirtualMoney $money
	 * @param number $amount
	 */
	private function processingAmount (VirtualMoney $money, $amount) : void {
		try {
			$this->validationAmount($amount);
		} catch (IllegalFormValueException $e) {
			$this->addError(self::FIELD_AMOUNT, $e->getMessage());
		}
		$money->setAmount($amount);
		$money->setExpected($amount);
	}
	
	/**
	 * lors de l'envoie d'un nouveau forfait,
	 * -verification dette
	 * -
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::createAfterValidation()
	 * @return VirtualMoney
	 */
	public function createAfterValidation(\Library\HTTPRequest $request) {
		$money = new VirtualMoney();
		$amount = $request->getDataPOST(self::FIELD_AMOUNT);
		
		$this->processingAmount($money, $amount);
		
		if (!$this->hasError()) {
			$money->setOffice($request->getAttribute(self::FIELD_OFFICE));
			$money->setRequest($request->getAttribute(self::FIELD_REQUEST_MONEY));
			try {
			    if ($this->gradeMemberDAOManager->hasOperation($money->getOffice()->getId(), null, false)) {
			        //si l'office a deja effectuer des operations, alors on verifie la dette
    			    $debts = $this->gradeMemberDAOManager->getOperations($money->getOffice()->getId(), null, false);
    			    
    			    foreach ($debts as $d) {//calcul de la dette
    			        if ($money->getAmount() >= $d->getMembership()) {
    			            $money->addDebt($d);//on classe l'operation
    			            $money->setAmount($money->getAmount()-$d->getMembership());//on recalcule le montant
    			        }else{
    			            break;
    			        }
    			    }
			    }
    			
			    
			    $generator = $this->officeSizeDAOManager->getCurrent($money->getOffice()->getId());//le packet actuel de l'office
			    
			    //calcul du %
			    $bonus = new OfficeBonus();
			    $bonus->setGenerator($generator);
			    $amountBonus = ($money->getAmount() / 100) * $generator->getSize()->getPercentage();
			    $bonus->setAmount($amountBonus);
			    //--calcul du %
			    
			    $bonus->setMember($money->getOffice()->getMember());
			    $bonus->setVirtualMoney($money);
			    $money->setBonus($bonus);
			    
			    
				$this->virtualMoneyDAOManager->create($money);
			} catch (DAOException $e) {
				$this->setMessage($e->getMessage());
			}
		}
		
		$this->result = $this->hasError()? "failed to send money":"";
		return $money;
	}

	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::deleteAfterValidation()
	 */
	public function deleteAfterValidation(\Library\HTTPRequest $request) {
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::recycleAfterValidation()
	 */
	public function recycleAfterValidation(\Library\HTTPRequest $request) {
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::removeAfterValidation()
	 */
	public function removeAfterValidation(\Library\HTTPRequest $request) {
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::updateAfterValidation()
	 */
	public function updateAfterValidation(\Library\HTTPRequest $request) {
		// TODO Auto-generated method stub
		
	}



}

