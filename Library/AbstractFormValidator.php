<?php
namespace Library;
/**
 *
 * @author Esaie MHS
 *        
 */
abstract class AbstractFormValidator extends AbstractCrypteur
{
    
    /*
     * les expression regulieres
     * ************************************************
     */
    const RGX_INT= '#^(\-?[0-9]+)$#';//Validation d'un nombre entier
    const RGX_INT_POSITIF= '#^([0-9]+)$#';//Validation d'un nombre entier positif
    const RGX_NUMERIC = '#^(((\-?[0-9]+)(\.[0-9]+)?){1,64})$#';//Validation d'une valeur numerique
    const RGX_NUMERIC_POSITIF = '#^((([0-9]+)(\.[0-9]+)?){1,64})$#';//Validation d'une valeure numerique positif
    
    /**
     * Expression reguliere d'un e-mail valide
     * @var string
     */
    const RGX_EMAIL='#^([a-zA-Z0-9._-]+)@([a-z0-9._-]{2,})\.[a-z]{2,6}$#';
    
    /**
     * Expression reguliere de la forme du nom du dommaine d'un site web
     * @var string
     */
    const RGX_WEB_SITE = '#^(http(s?)://)?([a-z0-9._-]{2,})\.[a-z]{2,6}$#';
    
    /**
     * Expression d'un numero de telephone en RD Congo.
     * Cette expression est vraiment efficace, mains uniquement pour la 
     * telephonie mobile en RD Congo
     * @var string
     */
    const RGX_TELEPHONE_RDC = '#^(\+243|0)(9|8)([0-9]{8})$#';
    
    /**
     * Expression reguliere d'un numero de telephone modibile de maniere generale.
     * cette expression a de failles, car il autorise certains numero invalide
     * @var string
     */
    const RGX_TELEPHONE = '#^(\+[0-9]{1,3})([0-9]{6,13})$#';
    /**
     * Expression reguliere de verification de la validite du'une date dans une chaine de caractere
     * La date doite une date simple
     * @var string
     */
    const RGX_DATE = '#^([0-9]{4})-([0-9]{2})-([0-9]{2})$#';
    
    /**
     * Expression reguliere de validation de la date et l'heure.
     * pour l'heure les 3 parametres doivent etre renseigner (HH:MM:SS)
     * @var string
     */
    const RGX_DATE_FULL_TIME = '#^([0-9]{4})((-([0-9]{2})){2})T([0-9]{2}):([0-9]{2})$#';
    
    /**
     * Expression reguleire de validation de la date et heure
     * pour l'heure, HH:MM sont uniquement requisent 
     * @var string
     */
    const RGX_DATE_TIME = '#^([0-9]{4})((-([0-9]{2})){2})T([0-9]{2}):([0-9]{2})$#';
    
    /**
     * Expression reguliere du format d'envoie de l'heures dans une chaine de caracetere
     * @var string
     */
    const RGX_TIME = '#^([0-9]{2}):([0-9]{2})(:[0-9]{2})?$#';
    
    
    const IMAGE_MAX_FILE_SIZE = 1024 * 1024 * 15;// environ 15 Mo
    
    /*
     * Les noms des messages apres validation dans la portee $_REQUEST
     * *******************************************************************/
    const ATT_MESSAGE = 'message';
    const ATT_RESULT = 'result';
    const ATT_ERRORS = 'errors';
    const ATT_MESSAGES = 'messages';
    const ATT_FEEDBACK = 'feedback';
    const ATT_FEEDBACKS = 'feedbacks';
    
    /*
     * Le champs des formulaire
     *****************************************/
    const CHAMP_ID = 'id';
    const CHAMP_DELETED = 'deleted';
    
    /**
     * Collection des messages d'errors lors de la validation d'un formulaire
     * nom/message: chaque message doit avoir une clee unique
     * @var array
     */
    private $errors=array();
    
    /**
     * Collection des message specifique. Surtout lors de la communication avec la base de donnee
     * lors de la validation d'un formulaire
     * @var array
     */
    private $messages=array();
    
    /**
     * Le message de result final lors de la validation du formulaire
     * @var string
     */
    protected $result;
    
    /**
     * Message special. si l'errerur surviens la la fin de la validation d'un
     * formulaire, lors de la communication avec la base de donnees
     * @var string
     */
    protected $message;
    
    /**
     * Une collection des feedback lors des valdation
     * @var ValidationFeedback[]
     */
    protected $validationFeedbacks;
    
    use DAOAutoload;
    
    /**
     * @var DAOManager
     */
    private $daoManager;
    
    /**
     * Constructeur d'initialisation et hydratation de DAOs
     * @param DAOManager $daoManager
     */
    public function __construct(DAOManager $daoManager)
    {
        $this->autoHydrate($daoManager);
        $this->validationFeeedbacks = array();
        $this->daoManager = $daoManager;
        $this->message = null;
    }
    
    /**
     * @return \Library\ValidationFeedback[]
     */
    public function getFeedbacks()
    {
        return $this->validationFeedbacks;
    }
    
    /**
     * @return \Library\DAOManager
     */
    public function getDaoManager() : DAOManager
    {
        return $this->daoManager;
    }

    /**
     * Creation d'un nouveau feedback soit ecrasement de l'encience si la cle est deja utiliser
     * @param string $key
     * @return \Library\ValidationFeedback
     */
    public function createFeedback($key) : ValidationFeedback{
        $feed = new ValidationFeedback($this);
        $this->validationFeedbacks[$key] = $feed;
        return $feed;
    }
    
    
    /**
     * Ajout d'u feedback a la collection des feedback
     * @param string $key
     * @param ValidationFeedback $feedback
     * @return void
     */
    public function addFeedback(string $key, ValidationFeedback $feedback) : void{
        $this->validationFeedbacks[$key] = $feedback;
    }
    
    /**
     * Supression d'un feedback a la collection des feedbacks
     * @param string $key
     */
    public function removeFeedback(string $key) : void{
        if ($this->hasFeedback()) {
            unset($this->validationFeedbacks[$key]);
        }
    }
    
    /**
     * Ajout d'un message d'erreur a l'une des feedback de la collection des feedbacks
     * Si le feedback n'existe pas, alors il sera creer
     * @param string $feedbackKey la cle du feedback
     * @param string $errorKey la clee du message dans la collection des message d'erreurs
     * @param string $message les message d'erreur
     * @return \Library\ValidationFeedback
     */
    public function putError(string $feedbackKey, string $errorKey, string $message) : ValidationFeedback{
        if (!array_key_exists($feedbackKey, $this->validationFeedbacks)) {
            $this->validationFeedbacks[$feedbackKey] = new ValidationFeedback($this);
        }
        $this->validationFeedbacks[$feedbackKey]->addError($errorKey, $message);        
        return $this->validationFeedbacks[$feedbackKey];
    }
    
    /**
     * Ajout d'un message informatif dans un feedback
     * s le feedback n'exite pas, il sera automaiquement creer
     * @param string $feedbackKey
     * @param string $messageKey
     * @param string $message
     * @return \Library\ValidationFeedback
     */
    public function putMessage(string $feedbackKey, string $messageKey, string $message) : ValidationFeedback{
        if (!array_key_exists($feedbackKey, $this->validationFeedbacks)) {
            $this->validationFeedbacks[$feedbackKey] = new ValidationFeedback($this);
        }
        $this->validationFeedbacks[$feedbackKey]->addMessage($messageKey, $message);
        return $this->validationFeedbacks[$feedbackKey];
    }
    
    /**
     * Verification s'il y a aumoin un feedback, soit si le feedback dont la cle est en parametre exite
     * @param string $key
     * @return boolean
     */
    public function hasFeedback(?string $key=null) : bool{
        if($key!=null){
            return isset($this->validationFeedbacks[$key]);
        }
        return !(empty($this->validationFeedbacks));
    }
    
    
    /**
     * Recuperation d'un feedback dont la cle est en parametre
     * @param string $key
     * @return \Library\ValidationFeedback|NULL
     */
    public function getFeedback(string $key) : ?ValidationFeedback{
        if ($this->hasFeedback($key)){
            return $this->validationFeedbacks[$key];
        }
        return null;
    }

    /**
     * Ajout d'un message d'erreur lors de la validation
     * @param string $name
     * @param string $message
     * @return void
     */
    protected function addError(string $name, string $message) : void
    {
        $this->errors[$name] = $message;
    }
    
    /**
     * Pour verifier s'il y a eu erreur lors de la validation d'un formulaire
     * @return boolean
     */
    public function hasError(?string $key = null) : bool
    {
        if ($key != null) {
            return array_key_exists($key, $this->errors);
        } 
        
        if (!empty($this->getFeedbacks())) {
            foreach ($this->validationFeedbacks as $feed) {
                if ($feed->hasError()) {
                    return true;
                }
            }
        }
        
        return !(empty($this->errors));
    }
    
    /**
     * Pour verifier s'il y a eu aumoin un message d'erreur special
     * @return boolean
     */
    public function hasMessage(?string $key = null) : bool{
        if ($key != null) {
            return array_key_exists($key, $this->messages);
        }
        return !(empty($this->messages));
    }
    
    /**
     * Ajout d'un message quelconque a  la collection des messages
     * @param string $name
     * @param string $message
     * @return void
     */
    public function addMessage(string $name, string $message) : void
    {
        $this->messages[$name] = $message;
    }
    
    /**
     * Retoyage des messages informatifs sur le traitements
     * @return void
     */
    public function clear() : void{
        $this->errors = array();
        $this->messages = array();
        $this->message = null;
        $this->result = null;
    }
    
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return string
     */
    public function getResult() : ?string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages) : void
    {
        $this->messages = $messages;
    }

    /**
     * @param string $result
     */
    public function setResult($result) : void
    {
        $this->result = $result;
    }

    /**
     * @param string $message
     * @param boolean $inErrors
     */
    public function setMessage($message, $inErrors=true) : void
    {
        $this->message = $message;
        if ($inErrors) {
            $this->addError(self::ATT_MESSAGE, $message);
        }
    }
    
    /**
     * Validation d'un identifiant d'un entite
     * @param string|int $id
     * @throws IllegalFormValueException
     */
    protected function validationId($id) : void
    {
        if($id!=null)
        {
            if ((is_string($id) && !preg_match(self::RGX_INT_POSITIF, $id))) {
                throw new IllegalFormValueException('Il se pourait que vous avez modifie les données d\'un champ de configuration. Le données du champ en question sont uniquement des entier possitifs.');
            }
        }else throw new IllegalFormValueException('Les données de ce champ sont toujours obligratoire.');
    }
    
    /**
     * Lancement de la vaidation d'un identifiant
     * @param DBEntity $entity
     * @param int|string $id
     * @return void
     */
    protected function traitementId(DBEntity $entity, $id) : void
    {
        try {
            $this->validationId($id);
        } catch (IllegalFormValueException $e) {
            $this->addError(self::CHAMP_ID, $e->getMessage());
        }
        $entity->setId($id);
    }
    
    /**
     * Pour inclure les message de feedback dans la requette
     * @param HTTPRequest $request
     * @return void
     */
    public function includeFeedback(HTTPRequest $request) : void
    {
        $request->addAttribute(self::ATT_ERRORS, $this->getErrors());
        $request->addAttribute(self::ATT_RESULT, $this->result);
        $request->addAttribute(self::ATT_MESSAGES, $this->getMessages());
        if (!array_key_exists(self::ATT_MESSAGE, $this->messages) && $this->message != null) {
            $request->addAttribute(self::ATT_MESSAGE, $this->message);
        }
        
        if ($this->hasFeedback()){
            $request->addAttribute(self::ATT_FEEDBACKS, $this->getFeedbacks());
        }
    }
    
    /**
     * Validation d'un image
     * @param File $file
     * @throws IllegalFormValueException
     */
    protected function validationImage(File $file) : void{
        if ($file->isFile()) {
            if ($file->hasError()) {
                throw new IllegalFormValueException('Une erreur est survenue sur le serveur avant la reception du fichier');
            }elseif (!$file->isImage()) {
                throw new IllegalFormValueException('Uniquement les images de type png, jpd end jpeg sont prise en charge.');
            }elseif ($file->getSize()>self::IMAGE_MAX_FILE_SIZE){
                throw new IllegalFormValueException('image trop lourd. utiliser un logiciel de combression pour reduire le poid de l\'image');
            }
        }
    }
    
    /**
     * Pour convertir les message d'erreur en un AppMessage
     * @return AppMessage
     */
    public function buildAppMessage() : AppMessage{
        $title = $this->getResult();
        $desctiption = '';
        
        foreach ($this->getErrors() as $value) {
            $desctiption.= $value.'\n';
        }
        
        foreach ($this->getMessages() as $value) {
            $desctiption.= $value.'\n';
        }
        
        if ($this->hasFeedback()) {            
            foreach ($this->getFeedbacks() as $feed) {
                foreach ($feed->getErrors() as $err) {
                    $desctiption.= $err.'\n';
                }
                
                foreach ($feed->getMessages() as $msg) {
                    $desctiption.= $msg.'\n';
                }
            }
        }
        $appMessage  = new AppMessage($title, $desctiption, $this->hasError()? AppMessage::MESSAGE_ERROR : AppMessage::MESSAGE_SUCCESS);
        return $appMessage;
    }
    
    /**
     * Validation d'un entite et ajout dans la bdd si les donnees sont valide
     * @param HTTPRequest $request
     * @return DBEntity
     */
    public abstract function createAfterValidation(HTTPRequest $request);
    
    /**
     * Validation de la mise ajour d'une entite
     * @param HTTPRequest $request
     * @return DBEntity l'objet deja mise ajour
     */
    public abstract function updateAfterValidation(HTTPRequest $request);
    
    /**
     * Demande de supresion d'une occurence dans la base de donnee
     * @param HTTPRequest $request
     * @return DBEntity l'occurence suprimer dansla base de donnees
     * 
     * @tutorial Il est probat que la supression soit impossible. Dans ce cas, si 
     * les autres occurence font reference a l'occurence encours de supression, alors ceux-ci 
     * vont etre integrer dans le $_REQUEST...
     */
    public abstract function deleteAfterValidation(HTTPRequest $request);
    
    /**
     * Demande de mise en corbeille d'une occurence
     * @param HTTPRequest $request
     * @return DBEntity
     */
    public abstract function removeAfterValidation(HTTPRequest $request);
    
    /**
     * Demande de recuperation d'une occurence deja mis en corbeil
     * @param HTTPRequest $request
     * @return DBEntity
     */
    public abstract function recycleAfterValidation(HTTPRequest $request);
    
    /**
     * Demande de supression definitive d'une collection d'occurence
     * @param HTTPRequest $request
     * @throws LibException
     * @return DBEntity[]
     */
    public function deleteAllAfterValidation(HTTPRequest $request){
        throw new LibException('Assurez-vous d\'avoir re-definei la metode aqui permet de superter la supression multiple');
    }
    
    
    /**
     * Demande de mise en corbeil d'une collection d'occurences
     * @param HTTPRequest $request
     * @throws LibException
     * @return DBEntity[]
     */
    public function removeAllAfterValidation(HTTPRequest $request){
        throw new LibException('La multiple n\'est pas supporter. assurez-vous d\'avoir la dite methode');
    }
    
    /**
     * Recyclage d'une collection d'occurence
     * @param HTTPRequest $request
     * @throws LibException
     * @return DBEntity
     */
    public function recycleAllAfterValidation(HTTPRequest $request){
        throw new LibException('Recyclage muliple non prise en charge');
    }
    
    /**
     * Pour lacer une requette de type LIKE vers la base de donnee pour une valeur biens precis
     * @param HTTPRequest $request
     * @return DBEntity[] une collection des DBEntity contiens la valeur voulue
     */
    public function likeAfterValidation(HTTPRequest $request)
    {
        throw new LibException('Assez-vous d\'avoir redefinie la methode likeAferValidation');
    }
    
    /**
     * Convesion des resultats de validation en un resultat global de validation
     * @return \Library\ValidationFeedback
     */
    public function toFeedback() : ValidationFeedback{
        $feed = new ValidationFeedback($this);
        $feed->setResult($this->getResult());
        $feed->setMessage($this->getMessage());
        $feed->setMessages($this->getMessages());
        foreach ($this->getErrors() as $key => $valeu) {
            $feed->addError($key, $valeu);
        }
        return $feed;
    }
    
    /**
     * Conversion du result de validation au format XML
     * @return string
     */
    public function toXML() : string{
        $refClass = new \ReflectionClass($this);
        $xml = '<validator className="'.$refClass->getShortName().'" result="'.$this->getResult().'" message="'.$this->getMessage().'">';
        if ($this->hasError()){
            $xml .= '<errors>';
            foreach ($this->getErrors() as $name => $message) {
                $xml .= '<error name="'.$name.'" value="'.$message.'"/>';
            }
            $xml .= '</errors>';
        }
        if (!empty($this->getMessages())) {
            $xml .= '<messages>';
            foreach ($this->getMessages() as $name => $message) {
                $xml .= '<message name="'.$name.'" value="'.$message.'"/>';
            }
            $xml .= '</messages>';
        }
        
        if ($this->hasFeedback()) {//Coversion des feedback en XML
            $xml .= '<feedbacks>';
            foreach ($this->getFeedbacks() as $name => $feedback) {
                $feedRef = new \ReflectionClass($feedback);
                $xml = '<feedback name="'.$name.'" className="'.$feedRef->getShortName().'" namespace="'.$feedRef->getNamespaceName().'">';
                $xml .= '<result>'.$feedback->getResult().'</result>';
                $xml .= '<message>'.$feedback->getMessage().'</message>';
                if ($feedback->hasError()){
                    $xml .= '<errors>';
                    foreach ($feedback->getErrors() as $errName => $message) {
                        $xml .= '<error name="'.$errName.'" value="'.$message.'"/>';
                    }
                    $xml .= '</errors>';
                }
                if (!empty($feedback->getMessages())) {
                    $xml .= '<messages>';
                    foreach ($feedback->getMessages() as $msgName => $message) {
                        $xml .= '<message name="'.$msgName.'" value="'.$message.'"/>';
                    }
                    $xml .= '</messages>';
                }
                $xml .= '</feedback>';
            }
            $xml .= '</feedbacks>';
        }
        $xml .= '</validator>';
        return $xml;
    }
    
    /**
     * Conversion du result de validation au format JSON
     * @return string
     */
    public function toJSON() : string{
        $refClass = new \ReflectionClass($this);
        $json = '{';
        $json .= '"className": "'.$refClass->getShortName().'",';
        $json .= '"result":'.($this->getResult()!=null? '"'.$this->getResult().'"': 'null').', ';
        $json .= '"message":'.($this->getMessage()!=null? '"'.$this->getMessage().'"': 'null').'';
        if ($this->hasError()){
            $json .= ', "errors" : [';
            $nombreError = count($this->getErrors());
            $numError = 1;
            foreach ($this->getErrors() as $name => $message) {
                $json .= '{"'.$name.'" : "'.$message.'"}'.($numError==$nombreError? '' : ', ');
                $numError++;
            }
            $json .= ']';
        }
        if (!empty($this->getMessages())) {
            $json .= ', "messages" : [';
            $nombreError = count($this->getMessages());
            $numError = 1;
            foreach ($this->getMessages() as $name => $message) {
                $json .= '{"'.$name.'" : "'.$message.'"}'.($numError==$nombreError? '' : ', ');
                $numError++;
            }
            $json .= ']';
        }
        
        if($this->hasFeedback()){
            $json .= ', "feedbacks" : {';
            $nombreFeedbacks = count($this->getFeedbacks());
            $numFeedback = 1;
            foreach ($this->getFeedbacks() as $name => $feedback) {
                $json .= '"'.$name.'" : '.$feedback->toJSON().''.($numFeedback==$nombreFeedbacks? '' : ', ');
                $numFeedback++;
            }
            $json .= '}';
        }
        $json .= '}';
        return $json;
    }
}
