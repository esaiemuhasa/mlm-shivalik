<?php
namespace Core\Shivalik\Validators;

use Core\Shivalik\Entities\Generation;
use Core\Shivalik\Managers\GenerationDAOManager;
use PHPBackend\DAOException;
use PHPBackend\Request;
use PHPBackend\Validator\DefaultFormValidator;
use PHPBackend\Validator\IllegalFormValueException;

/**
 *
 * @author Esaie MHS
 *        
 */
class GenerationFormValidator extends DefaultFormValidator
{
    const FIELD_NAME = 'name';
    const FIELD_ABBREVIATION = 'abbreviation';
    const FIELD_NUMBER = 'number';
    const FIELD_PERCENTAGE = 'percentage';
    
    /**
     * @var GenerationDAOManager
     */
    private $generationDAOManager;
    
    private function validationName ($name, $id = null) : void {
        if ($name == null) {
            throw new IllegalFormValueException("generation name is required");
        }
        
        try {
            if ($this->generationDAOManager->checkByName($name, $id)) {
                throw new IllegalFormValueException("This name is used by oder generation");
            }
        } catch (DAOException $e) {
            throw new IllegalFormValueException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    private function validationAbbreviation ($abbreviation, $id = null) : void {
        if ($abbreviation == null) {
            throw new IllegalFormValueException("generation abbreviation is required");
        }
        
        try {
            if ($this->generationDAOManager->checkByAbreviation($abbreviation, $id)) {
                throw new IllegalFormValueException("This abbreviation are used by oder generation");
            }
        } catch (DAOException $e) {
            throw new IllegalFormValueException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    private function validationNumber ($number, $id = -1) : void {
        if ($number == null) {
            throw new IllegalFormValueException("generation number is required");
        }else if (!preg_match(self::RGX_INT_POSITIF, $number)) {
            throw new IllegalFormValueException("the generation number must be a positive value");
        }
        
        try {
            if ($this->generationDAOManager->checkByNumber($number, $id)) {
                throw new IllegalFormValueException("This number are used by oder generation");
            }
        } catch (DAOException $e) {
            throw new IllegalFormValueException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    private function validationPercentage ($percentage) : void {
        if ($percentage == null) {
            throw new IllegalFormValueException("generation percentage is required");
        }else if (!preg_match(self::RGX_NUMERIC_POSITIF, $percentage)) {
            throw new IllegalFormValueException("percentage profit must be positive");
        }
    }
    
    
    private function processingName (Generation $generation, $name, $id=-1) : void {
        try {
            $this->validationName($name, $id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_NAME, $e->getMessage());
        }
        $generation->setName($name);
    }
    
    private function processingAbbreviation (Generation $generation, $abbreviation, $id=-1) : void {
        try {
            $this->validationAbbreviation($abbreviation, $id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_ABBREVIATION, $e->getMessage());
        }
        $generation->setAbbreviation($abbreviation);
    }
    
    private function processingNumber (Generation $generation, $number, $id=-1) : void {
        try {
            $this->validationNumber($number, $id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_NUMBER, $e->getMessage());
        }
        $generation->setNumber($number);
    }
    
    private function processingPercentage (Generation $generation, $percentage) : void {
        try {
            $this->validationPercentage($percentage);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::FIELD_PERCENTAGE, $e->getMessage());
        }
        $generation->setPercentage($percentage);
    }
    
    /**
     * {@inheritDoc}
     * @see \PHPBackend\Validator\FormValidator::createAfterValidation()
     * @return Generation
     */
    public function createAfterValidation(Request $request)
    {
        $generation =  new Generation();
        $name = $request->getDataPOST(self::FIELD_NAME);
        $abbreviation = $request->getDataPOST(self::FIELD_ABBREVIATION);
        $number = $request->getDataPOST(self::FIELD_NUMBER);
        $percentage = $request->getDataPOST(self::FIELD_PERCENTAGE);
        
        $this->processingName($generation, $name);
        $this->processingAbbreviation($generation, $abbreviation);
        $this->processingNumber($generation, $number);
        $this->processingPercentage($generation, $percentage);
        
        if (!$this->hasError()) {
            try {
                $this->generationDAOManager->create($generation);
            } catch (DAOException $e) {
                $this->setMessage($e->getMessage());
            }
        }
        
        $this->result = $this->hasError()? "registration failure":"registration success";
        
        return $generation;
        
    }

    /**
     * {@inheritDoc}
     * @see \PHPBackend\Validator\FormValidator::updateAfterValidation()
     * @return Generation
     */
    public function updateAfterValidation(Request $request)
    {
        $generation =  new Generation();
        $id = $request->getDataGET(self::CHAMP_ID);
        $name = $request->getDataPOST(self::FIELD_NAME);
        $abbreviation = $request->getDataPOST(self::FIELD_ABBREVIATION);
        $number = $request->getDataPOST(self::FIELD_NUMBER);
        $percentage = $request->getDataPOST(self::FIELD_PERCENTAGE);
        
        $this->traitementId($generation, $id);
        $this->processingName($generation, $name, $id);
        $this->processingAbbreviation($generation, $abbreviation, $id);
        $this->processingNumber($generation, $number, $id);
        $this->processingPercentage($generation, $percentage);
        
        if (!$this->hasError()) {
            try {
                $this->generationDAOManager->update($generation, $generation->getId());
            } catch (DAOException $e) {
                $this->setMessage($e->getMessage());
            }
        }
        
        $this->result = $this->hasError()? "failure to save changes":"successful registration of changes";
        return $generation;
    }


    
}

