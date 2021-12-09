<?php
namespace Entities;

use Library\LibException;

/**
 *
 * @author Esaie MHS
 *        
 */
abstract class AbstractBonus extends Operation
{
    
    /**
     * on garde la reference vers l'actuel generator du membre
     * dont pour la classe d'association pour le membre et le generator
     * @var GradeMember
     */
    protected $generator;
    
    
    /**
     * @return \Entities\GradeMember
     */
    public function getGenerator() : ?GradeMember
    {
        return $this->generator;
    }
    
    /**
     * @param \Entities\GradeMember $generator
     */
    public function setGenerator($generator) : void
    {
        if ($generator == null || $generator instanceof GradeMember) {
            $this->generator = $generator;
        }elseif ($this->isInt($generator)){
            $this->old = new GradeMember(array('id' => $generator));
        }else {
            throw new LibException("Invalid value in param of method setGenerator()");
        }
    }
}

