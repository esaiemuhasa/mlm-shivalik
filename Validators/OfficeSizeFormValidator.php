<?php

namespace Validators;

use Library\AbstractFormValidator;
use Library\IllegalFormValueException;
use Managers\SizeDAOManager;
use Library\DAOException;
use Entities\OfficeSize;
use Managers\OfficeSizeDAOManager;
use Managers\OfficeDAOManager;

/**
 *
 * @author Esaie MHS
 *        
 */
class OfficeSizeFormValidator extends AbstractFormValidator {
	
	const FIELD_OFFICE = 'office';
	const FIELD_SIZE = 'size';
	
	/**
	 * @var SizeDAOManager
	 */
	private $sizeDAOManager;
	
	/**
	 * @var OfficeSizeDAOManager
	 */
	private $officeSizeDAOManager;
	
	/**
	 * @var OfficeDAOManager
	 */
	private $officeDAOManager;
	
	/**
	 * @param number $size
	 */
	private function validationSize ($size) : void {
		if ($size == null) {
			throw new IllegalFormValueException("Office size is required");
		}else if (!preg_match(self::RGX_INT_POSITIF, $size)) {
			throw new IllegalFormValueException("Reference of office must be a positive numeric value");
		}
		
		try {
			if (!$this->sizeDAOManager->idExist($size)) {
				throw new IllegalFormValueException("unknown reference in the system");
			}
		} catch (DAOException $e) {
			throw new IllegalFormValueException($e->getMessage(),$e->getCode(), $e);
		}
	}
	
	/**
	 * @param OfficeSize $os
	 * @param number $size
	 */
	private function processingSize (OfficeSize $os, $size) : void {
		try {
			$this->validationSize($size);
			$os->setSize($size);
		} catch (IllegalFormValueException $e) {
			$this->addError(self::FIELD_SIZE, $e->getMessage());
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::createAfterValidation()
	 */
	public function createAfterValidation(\Library\HTTPRequest $request) {
		$os = new OfficeSize();
		
		$size = $request->getDataPOST(self::FIELD_SIZE);
		
		$form = new OfficeFormValidator($this->getDaoManager());
		$office = $form->processingOffice($request);
		
		$os->setOffice($office);
		$this->processingSize($os, $size);
		
		if (!$this->hasError() && !$form->hasError()) {
			try {
				$os->setInitDate(new \DateTime());
				$this->officeSizeDAOManager->create($os);
				
				$form->processingPhoto($office, $form->getPhoto(), true);
				$this->officeDAOManager->updatePhoto($office->getId(), $office->getPhoto());
			} catch (DAOException $e) {
				$this->setMessage($e->getMessage());
			}
		}else {
			foreach ($form->getErrors() as $key => $error) {
				$this->addError($key, $error);
			}
		}
		
		$this->result = $this->hasError()? "failure to execute operations" : "successful execution of the operation";
		
		return $os;
	}

	/**
	 * {@inheritDoc}
	 * @see \Library\AbstractFormValidator::updateAfterValidation()
	 */
	public function updateAfterValidation(\Library\HTTPRequest $request) {
		// TODO Auto-generated method stub
		
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


}

