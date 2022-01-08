<?php
namespace Applications\Member\Modules\MyOffice;

use Applications\Member\MemberApplication;
use PHPBackend\Http\HTTPController;
use Core\Shivalik\Managers\GradeMemberDAOManager;
use Core\Shivalik\Managers\VirtualMoneyDAOManager;
use Core\Shivalik\Managers\MemberDAOManager;
use Core\Shivalik\Managers\WithdrawalDAOManager;
use Core\Shivalik\Managers\RequestVirtualMoneyDAOManager;
use Core\Shivalik\Entities\Office;
use Core\Shivalik\Managers\RaportWithdrawalDAOManager;
use PHPBackend\Application;
use PHPBackend\Response;
use PHPBackend\Request;
use PHPBackend\Calendar\Month;
use Core\Shivalik\Validators\RequestVirtualMoneyFormValidator;
use Core\Shivalik\Entities\RaportWithdrawal;
use Core\Shivalik\Entities\Member;

class MyOfficeController extends HTTPController
{
    const ATT_ACTIVE_ITEM_MENU = 'OFFICE_ACTIVE_ITEM_MENU';
    const ATT_ITEM_MENU_DASHBOARD = 'OFFICE_ACTIVE_ITEM_MENU_DASHBOARD';
    const ATT_ITEM_MENU_MEMBERS = 'OFFICE_ACTIVE_ITEM_MENU_MEMBERS';
    const ATT_ITEM_MENU_HISTORY = 'OFFICE_ACTIVE_ITEM_MENU_HISTORY';
    const ATT_ITEM_MENU_OFFICE_ADMIN = 'OFFICE_ACTIVE_ITEM_MENU_OFFICE_ADMIN';
    const ATT_ITEM_MENU_VIRTUAL_MONEY = 'OFFICE_ACTIVE_ITEM_MENU_VIRTUAL_MONEY';
    
    
    const ATT_OFFICE = 'office';
    const ATT_CAN_SEND_RAPORT = 'canSendRaport';
    const ATT_OFFICE_ADMIN = 'officeAdmin';
    
    const ATT_MEMBERS = 'members';
    const ATT_GRADES_MEMBERS = 'gradesMembers';
    
    const ATT_OFFICE_SIZE = 'officeSize';
    const ATT_COUNT_MEMEBERS = 'COUNT_MEMBERS_IN_OFFICE';
    const ATT_WITHDRAWALS = 'WITHDRAWALS';
    const ATT_WITHDRAWALS_AMOUNT = 'WITHDRAWALS_AMOUNT';
    
    //pour les monais virtuels
    const ATT_VIRTUAL_MONEY = 'virtualMoney';
    const ATT_VIRTUAL_MONEYS = 'virtualMoneys';
    
    const ATT_MONTH = 'MONTH';
    const CONFIG_MAX_MEMBER_VIEW_STEP = 'maxMembers';
    const PARAM_MEMBER_COUNT = 'PARAM_MEMBER_COUNT';
    
    /**
     * @var GradeMemberDAOManager
     */
    private $gradeMemberDAOManager;
    
    /**
     * @var VirtualMoneyDAOManager
     */
    private $virtualMoneyDAOManager;
    
    /**
     * @var MemberDAOManager
     */
    private $memberDAOManager;
    
    /**
     * @var WithdrawalDAOManager
     */
    private $withdrawalDAOManager;
    
    /**
     * @var RequestVirtualMoneyDAOManager
     */
    private $requestVirtualMoneyDAOManager;
    
    /**
     * @var RaportWithdrawalDAOManager
     */
    private $raportWithdrawalDAOManager;
    
    /**
     * @var Office
     */
    private $office;
    
    /**
     * {@inheritDoc}
     * @see HTTPController::__construct()
     */
    public function __construct(Application $application, string $action, $module)
    {
        parent::__construct($application, $action, $module);
        if (MemberApplication::getConnectedMember()->getOfficeAccount() == null) {
            $application->getHttpResponse()->sendError();
        }
        $this->office = MemberApplication::getConnectedMember()->getOfficeAccount();
    }
    
    /**
     * 
     * @param Request $request
     * @param Response $response
     */
    public function executeIndex (Request $request, Response $response) : void {
        $request->addAttribute(self::ATT_ACTIVE_ITEM_MENU, self::ATT_ITEM_MENU_DASHBOARD);
        
        $nombreMembre = $this->memberDAOManager->countCreatedBy($this->office->getId());
        if ($this->withdrawalDAOManager->hasRequest($this->office->getId(), null)) {
            $withdrawals = $this->withdrawalDAOManager->getOfficeRequests($this->office->getId(), null);
        }else {
            $withdrawals = array();
        }
        
        if ($this->gradeMemberDAOManager->hasOperation($this->office->getId())) {
            $this->office->setOperations($this->gradeMemberDAOManager->getOperations($this->office->getId()));
        }
        
        if ($this->virtualMoneyDAOManager->hasVirtualMoney($this->office->getId())) {
            $this->office->setVirtualMoneys($this->virtualMoneyDAOManager->forOffice($this->office->getId()));
        }
        
        $this->office->setWithdrawals($withdrawals);
        
        if ($this->withdrawalDAOManager->hasRequest($this->office->getId(), null, false)) {
            $serveds = $this->withdrawalDAOManager->getOfficeRequests($this->office->getId(), null, false);
            $request->addAttribute(self::ATT_WITHDRAWALS, $serveds);
        }else {
            $request->addAttribute(self::ATT_WITHDRAWALS, array());
        }
        $request->addAttribute(self::ATT_CAN_SEND_RAPORT, $this->raportWithdrawalDAOManager->canSendRaport($this->office->getId()));
        $request->addAttribute(self::ATT_COUNT_MEMEBERS, $nombreMembre);
    }
    
    /**
     *
     * @param Request $request
     * @param Response $response
     */
    public function executeVirtualmoney(Request $request, Response $response) : void {
        $request->addAttribute(self::ATT_ACTIVE_ITEM_MENU, self::ATT_ITEM_MENU_VIRTUAL_MONEY);
        
        if ($this->gradeMemberDAOManager->hasOperation($this->office->getId())) {
            $this->office->setOperations($this->gradeMemberDAOManager->getOperations($this->office->getId()));
        }
        
        if ($this->virtualMoneyDAOManager->hasVirtualMoney($this->office->getId())) {
            $this->office->setVirtualMoneys($this->virtualMoneyDAOManager->forOffice($this->office->getId()));
        }
        
        if ($this->requestVirtualMoneyDAOManager->hasWaiting($this->office->getId())) {
            $requests = $this->requestVirtualMoneyDAOManager->getWaiting($this->office->getId());
        }else {
            $requests = array();
        }
        
        $request->addAttribute(self::ATT_VIRTUAL_MONEYS, $requests);
    }
    
    /**
     * Envoie requette demande monais virtuel
     * @param Request $request
     * @param Response $response
     */
    public function executeRequestVirtualmoney(Request $request, Response $response) : void {
        if ($request->getMethod() == Request::HTTP_POST) {
            $form = new RequestVirtualMoneyFormValidator($this->getDaoManager());
            $request->addAttribute($form::FIELD_OFFICE, $this->office);
            $virtual = $form->createAfterValidation($request);
            
            if (!$form->hasError()) {
                $response->sendRedirect("/member/office/virtualmoney/");
            }
            
            $request->addAttribute(self::ATT_VIRTUAL_MONEY, $virtual);
            $form->includeFeedback($request);
        }
    }
    
    /**
     * Envoie du rapport mensuel du matching
     * cette action n'a pas de vue
     * @param Request $request
     * @param Response $response
     */
    public function executeSendRaportWithdrawals(Request $request, Response $response) : void {
        if (!$this->raportWithdrawalDAOManager->canSendRaport($this->office->getId()) || !$this->withdrawalDAOManager->hasRequest($this->office->getId(), true)) {
            $response->sendError("impossible to perform this operation because it is active for a precise time limit.");
        }
        $withdrawals = $this->withdrawalDAOManager->getOfficeRequests($this->office->getId(), true);
        
        $raport = new RaportWithdrawal();
        $raport->setOffice($this->office);
        $raport->setWithdrawals($withdrawals);
        $this->raportWithdrawalDAOManager->create($raport);
        $response->sendRedirect("/member/office/");
    }
    
    
    /**
     * affichage de membres qui se sont adherer, en passant par le bureau
     * @param Request $request
     * @param Response $response
     */
    public function executeMembers (Request $request, Response $response) : void {
        $request->addAttribute(self::ATT_ACTIVE_ITEM_MENU, self::ATT_ITEM_MENU_MEMBERS);
        
        $nombre = $this->memberDAOManager->countCreatedBy($this->office->getId());
        
        if ($nombre>0) {
            if ($request->existGET('limit')) {
                $offset = intval($request->getDataGET('offset'), 10);
                $limit = intval($request->getDataGET('limit'), 10);
            } else {
                $limit = intval($request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)!=null? $request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)->getValue() : 50);
                $offset = 0;
            }
            $members = $this->memberDAOManager->getCreatedBy($this->office->getId(), $limit, $offset);
        }else {
            $members = array();
        }
        
        /**
         * @var Member $member
         */
        foreach ($members as $member) {
            if ($this->gradeMemberDAOManager->hasCurrent($member->getId())) {
                $member->setPacket($this->gradeMemberDAOManager->getCurrent($member->getId()));
            }
        }
        
        $request->addAttribute(self::PARAM_MEMBER_COUNT, $nombre);
        $request->addAttribute(self::ATT_MEMBERS, $members);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     */
    public function executeUpgrades (Request $request, Response $response) : void {
        $request->addAttribute(self::ATT_ACTIVE_ITEM_MENU, self::ATT_ITEM_MENU_MEMBERS);
        //upgrades
        if ($this->gradeMemberDAOManager->hasOperation($this->office->getId(), true)) {
            $packets = $this->gradeMemberDAOManager->getOperations($this->office->getId(), true);
        }else {
            $packets = array();
        }
        
        $request->addAttribute(self::ATT_GRADES_MEMBERS, $packets);
    }
    
    /**
     * consultation de l'istorique des activites d'un office
     * @param Request $request
     * @param Response $response
     */
    public function executeHistory (Request $request, Response $response) : void {
        $request->addAttribute(self::ATT_ACTIVE_ITEM_MENU, self::ATT_ITEM_MENU_HISTORY);
        
        $date = null;
        
        if ($request->existGET('date')) {
            $date = new \DateTime($request->getDataGET('date'));
            $month = new Month(intval($date->format('m'), 10), intval($date->format('Y'), 10));
            $month->addSelectedDate($date);
        }elseif ($request->existGET('month')) {
            $month = new Month(intval($request->getDataGET('month'), 10), intval($request->getDataGET('year'), 10));
        }else {
            $month = new Month();
        }
        $month->setLocal(Month::LOCAL_EN);
        
        $dateMin = ($date!=null? $date : $month->getFirstDay());
        $dateMax = ($date!=null? $date : $month->getLastDay());
        
        //adhesion
        if ($this->memberDAOManager->hasCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()))) {
            $members = $this->memberDAOManager->getCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()));
        }else {
            $members = array();
        }
        
        //Monais virtuel
        if ($this->virtualMoneyDAOManager->hasCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()))) {
            $virtuals = $this->virtualMoneyDAOManager->getCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()));
        }else {
            $virtuals = array();
        }
        
        //upgrades
        if ($this->gradeMemberDAOManager->hasUpgradeHistory($dateMin, $dateMax, array('office' => $this->office->getId()))) {
            $packets = $this->gradeMemberDAOManager->getUpgradeHistory($dateMin, $dateMax, array('office' => $this->office->getId()));
        }else {
            $packets = array();
        }
        
        //retraits
        if ($this->withdrawalDAOManager->hasCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()))) {
            $withdrawals = $this->withdrawalDAOManager->getCreationHistory($dateMin, $dateMax, array('office' => $this->office->getId()));
        }else {
            $withdrawals = array();
        }
        
        $request->addAttribute(self::ATT_MONTH, $month);
        $request->addAttribute(self::ATT_MEMBERS, $members);
        $request->addAttribute(self::ATT_VIRTUAL_MONEYS, $virtuals);
        $request->addAttribute(self::ATT_GRADES_MEMBERS, $packets);
        $request->addAttribute(self::ATT_WITHDRAWALS, $withdrawals);
    }


}

