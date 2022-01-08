<?php
namespace Core\Shivalik\Validators;

use Core\Shivalik\Entities\Country;
use Core\Shivalik\Managers\CountryDAOManager;
use PHPBackend\DAOException;
use PHPBackend\Request;
use PHPBackend\Validator\DefaultFormValidator;
use PHPBackend\Validator\IllegalFormValueException;

/**
 *
 * @author Esaie MHS
 *        
 */
class CountryFormValidator extends DefaultFormValidator
{
    const FIELD_NAME = 'name';
    const FIELD_ABBREVIATION = 'abbreviation';
    
    /**
     * @var CountryDAOManager
     */
    private $countryDAOManager;
    
    private function validationName ($name, $id = null) : void {
        if ($name == null) {
            throw new IllegalFormValueException("country name is required");
        }
        
        try {
            if ($this->countryDAOManager->checkByName($name, $id)) {
                throw new IllegalFormValueException("This name is used by oder country");
            }
        } catch (DAOException $e) {
            throw new IllegalFormValueException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    private function validationAbbreviation ($abbreviation, $id = null) : void {
        if ($abbreviation == null) {
            throw new IllegalFormValueException("country abbreviation is required");
        }
        
        try {
            if ($this->countryDAOManager->checkByAbreviation($abbreviation, $id)) {
                throw new IllegalFormValueException("This abbreviation are used by oder country");
            }
        } catch (DAOException $e) {
            throw new IllegalFormValueException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    private function processingName (Country $country, $name, $id=-1) : void {
        try {
            $this->validationName($name, $id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_NAME, $e->getMessage());
        }
        $country->setName($name);
    }
    
    private function processingAbbreviation (Country $country, $abbreviation, $id=-1) : void {
        try {
            $this->validationAbbreviation($abbreviation, $id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_ABBREVIATION, $e->getMessage());
        }
        $country->setAbbreviation($abbreviation);
    }
    
    /**
     * {@inheritDoc}
     * @see \PHPBackend\Validator\FormValidator::createAfterValidation()
     */
    public function createAfterValidation(Request $request)
    {
        $country = new Country();
        $name = $request->getDataPOST(self::FIELD_NAME);
        $abbreviation = $request->getDataPOST(self::FIELD_ABBREVIATION);
        
        $this->processingAbbreviation($country, $abbreviation);
        $this->processingName($country, $name);
        
        if (!$this->hasError()) {
            try {
                $this->countryDAOManager->create($country);
            } catch (DAOException $e) {
                $this->setMessage($e->getMessage());
            }
        }
        
        $this->result = $this->hasError()? "registration failure":"registration success";
        
        return $country;
        
    }

    /**
     * {@inheritDoc}
     * @see \PHPBackend\Validator\FormValidator::updateAfterValidation()
     */
    public function updateAfterValidation(Request $request)
    {
        $country = new Country();
        $id = intval($request->getDataGET('id'), 10);
        $name = $request->getDataPOST(self::FIELD_NAME);
        $abbreviation = $request->getDataPOST(self::FIELD_ABBREVIATION);
        
        $this->traitementId($country, $id);
        $this->processingAbbreviation($country, $abbreviation, $country->getId());
        $this->processingName($country, $name, $country->getId());
        
        if (!$this->hasError()) {
            try {
                $this->countryDAOManager->update($country, $country->getId());
            } catch (DAOException $e) {
                $this->setMessage($e->getMessage());
            }
        }
        
        $this->result = $this->hasError()? "registration failure":"registration success";
        
        return $country;
        
    }


}

