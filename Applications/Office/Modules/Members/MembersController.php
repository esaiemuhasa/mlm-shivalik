<?php

namespace Applications\Office\Modules\Members;

use Applications\Office\OfficeApplication;
use PHPBackend\Http\HTTPController;
use Core\Shivalik\Managers\MemberDAOManager;
use Core\Shivalik\Managers\GradeDAOManager;
use Core\Shivalik\Managers\CountryDAOManager;
use Core\Shivalik\Managers\PointValueDAOManager;
use Core\Shivalik\Managers\GradeMemberDAOManager;
use Core\Shivalik\Managers\BonusGenerationDAOManager;
use Core\Shivalik\Managers\WithdrawalDAOManager;
use Core\Shivalik\Managers\VirtualMoneyDAOManager;
use PHPBackend\Application;
use Core\Shivalik\Entities\Member;
use Core\Shivalik\Entities\Account;
use Core\Shivalik\Validators\GradeMemberFormValidator;
use PHPBackend\Request;
use Core\Shivalik\Validators\MemberFormValidator;
use Core\Shivalik\Validators\LocalisationFormValidator;
use PHPBackend\ToastMessage;

/**
 *
 * @author Esaie MHS
 *        
 */
class MembersController extends HTTPController {
	const CONFIG_MAX_MEMBER_VIEW_STEP = 'maxMembers';
	const PARAM_MEMBER_COUNT = 'countMembers';
	
	const ATT_COUNTRYS = 'countrys';
	const ATT_LOCALISATION = 'localisation';
	const ATT_COMPTE = 'compte';
	const ATT_MEMBERS = 'members';
	const ATT_MEMBER = 'member';
	const ATT_GRADE_MEMBER = 'gradeMember';
	const ATT_REQUESTED_GRADE_MEMBER = 'RequestedGradeMember';
	const ATT_GRADES = 'grades';
	const ATT_SOLDE = 'solde';
	const ATT_SOLDE_WITHDRAWALS = 'soldeWithdrawals';
	const ATT_WITHDRAWALS = 'withdrawals';
	
	const LEFT_CHILDS = 'LEFT';
	const MIDDLE_CHILDS = 'MIDDLE';
	const RIGHT_CHILDS = 'RIGHT';
	
	/**
	 * @var MemberDAOManager
	 */
	private $memberDAOManager;
	
	/**
	 * @var GradeDAOManager
	 */
	private $gradeDAOManager;
	
	/**
	 * @var CountryDAOManager
	 */
	private $countryDAOManager;
	
	/**
	 * @var PointValueDAOManager
	 */
	private $pointValueDAOManager;
	
	/**
	 * @var GradeMemberDAOManager
	 */
	private $gradeMemberDAOManager;
	
	/**
	 * @var BonusGenerationDAOManager
	 */
	private $bonusGenerationDAOManager;
	
	/**
	 * @var WithdrawalDAOManager
	 */
	private $withdrawalDAOManager;
	
	/**
	 * @var VirtualMoneyDAOManager
	 */
	private $virtualMoneyDAOManager;
	
	/**
	 * {@inheritDoc}
	 * @see HTTPController::__construct()
	 */
	public function __construct(Application $application, string $action, string $module)
	{
		parent::__construct($application, $action, $module);
		$nombre = $this->memberDAOManager->countAll();
		$application->getRequest()->addAttribute(self::PARAM_MEMBER_COUNT, $nombre);
		$application->getRequest()->addAttribute(self::ATT_VIEW_TITLE, "Union members");
		
		if ($application->getHttpRequest()->existGET('id')) {//
			$id = intval($application->getRequest()->getDataGET('id'), 10);
			$member = $this->memberDAOManager->getForId($id);
			$account = $this->getAccount($member);
			
			$application->getRequest()->addAttribute(self::ATT_COMPTE, $account);
			$application->getRequest()->addAttribute(self::ATT_MEMBER, $member);
		}
	}
	
	/**
	 * @param Member $member
	 * @return Account
	 */
	public function getAccount (Member $member) : Account {
		return $this->memberDAOManager->getAccount($member);
	}
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeIndex (Request $request, Request $response) : void{
		$withdrawal = 0;

		if ($this->withdrawalDAOManager->hasRequest(OfficeApplication::getConnectedUser()->getOffice()->getId())) {
			$all = $this->withdrawalDAOManager->getOfficeRequests(OfficeApplication::getConnectedUser()->getOffice()->getId());
			foreach ($all as $one) {
				$withdrawal += $one->getAmount();
			}
		}else {
			$all = array();
		}
		
		$request->addAttribute(self::ATT_SOLDE_WITHDRAWALS, $withdrawal);
		$request->addAttribute(self::ATT_WITHDRAWALS, $all);
	}
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeMembers (Request $request, Request $response) : void {
		
		$office = OfficeApplication::getConnectedUser()->getOffice();
		
		if ($request->getMethod() == Request::HTTP_POST) {
		    
		    $matricule = $request->getDataPOST('id');
		    if ($matricule == null ) {
		        $message = new ToastMessage('Error', "Enter user ID to perform shearch operation...", ToastMessage::MESSAGE_ERROR);
		    } else if ($this->memberDAOManager->matriculeExist($matricule)) {
				$member = $this->memberDAOManager->getForMatricule($request->getDataPOST('id'));
				$response->sendRedirect("/office/members/{$member->getId()}/");
			}else {
    			$message = new ToastMessage('Error', "Know user ID in system. ID: {$request->getDataPOST('id')}", ToastMessage::MESSAGE_ERROR);
			}
			
			$request->addAppMessage($message);
			$response->sendRedirect('/office/members/');
		}
		

		$nombre = $this->memberDAOManager->countCreatedBy($office->getId());
		if ($nombre>0) {
			if ($request->existGET('limit')) {
				$offset = intval($request->getDataGET('offset'), 10);
				$limit = intval($request->getDataGET('limit'), 10);
				$members = $this->memberDAOManager->getCreatedBy($office->getId(), $limit, $offset);
			} else {
				$limit = intval($request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)!=null? $request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)->getValue() : 50);
				$members = $this->memberDAOManager->getCreatedBy($office->getId(), $limit, 0);
			}
		}else {
			$members = array();
		}
		
		/**
		* @var Member $member
		*/
		foreach ($members as $member) {
		    $member->setPacket($this->gradeMemberDAOManager->getCurrent($member->getId()));
		}
		$request->addAttribute(self::ATT_MEMBERS, $members);
		$request->addAttribute(self::PARAM_MEMBER_COUNT, $nombre);
	}
	
	
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeAddMember (Request $request, Request $response) : void {
		
		$office = OfficeApplication::getConnectedUser()->getOffice();
		
		if ($this->gradeMemberDAOManager->hasOperation($office->getId())) {
			$office->setOperations($this->gradeMemberDAOManager->getOperations($office->getId()));
		}
		
		if ($this->virtualMoneyDAOManager->hasVirtualMoney($office->getId())) {
			$office->setVirtualMoneys($this->virtualMoneyDAOManager->forOffice($office->getId()));
		}
		
		if ($request->getMethod() == Request::HTTP_POST) {
			$form = new GradeMemberFormValidator($this->getDaoManager());
			$request->addAttribute(GradeMemberFormValidator::FIELD_OFFICE_ADMIN, OfficeApplication::getConnectedUser());
			$gm = $form->createAfterValidation($request);
			
			if (!$form->hasError()) {
				$response->sendRedirect("/office/members/");
			}
			
			$request->addAttribute(LocalisationFormValidator::LOCALISATION_FEEDBACK, $form->getFeedback(LocalisationFormValidator::LOCALISATION_FEEDBACK));
			$request->addAttribute(MemberFormValidator::MEMBER_FEEDBACK, $form->getFeedback(MemberFormValidator::MEMBER_FEEDBACK));
			$form->includeFeedback($request);
			
			$request->addAttribute(self::ATT_GRADE_MEMBER, $gm);
			$request->addAttribute(self::ATT_MEMBER, $gm->getMember());
			$request->addAttribute(self::ATT_LOCALISATION, $gm->getMember()->getLocalisation());
		}
		
		//
		$request->addAttribute(self::ATT_COUNTRYS, $this->countryDAOManager->getAll());
		$grades = $this->gradeDAOManager->getAll();
		$request->addAttribute(self::ATT_GRADES, $grades);
	}
	
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeMember (Request $request, Request $response) : void {
		$id = intval($request->getDataGET('id'), 10);
		if (!$this->memberDAOManager->idExist($id)) {
			$response->sendError();
		}
		
		/**
		 * @var Member $member
		 */
		$member = $this->memberDAOManager->getForId($id);
		
		if ($this->gradeMemberDAOManager->hasCurrent($member->getId())) {
			$gradeMember = $this->gradeMemberDAOManager->getCurrent($id);
			$gradeMember->setMember($member);
			$request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
		} 
		
		if ($this->gradeMemberDAOManager->hasRequested($member->getId())) {
			$requestedGradeMember = $this->gradeMemberDAOManager->getRequested($member->getId());
			$requestedGradeMember->setMember($member);
			$request->addAttribute(self::ATT_REQUESTED_GRADE_MEMBER, $requestedGradeMember);
		}
		
		//Chargement des PV;
		$compte = $this->getAccount($member);
		
		$request->addAttribute(self::ATT_COMPTE, $compte);
		$request->addAttribute(self::ATT_MEMBER, $member);
	}
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeDownlines (Request $request, Request $response) : void {
		
		$id = intval($request->getDataGET('id'), 10);
		if (!$this->memberDAOManager->idExist($id)) {
			$response->sendError();
		}
		
		/**
		 * @var Member $member
		 */
		$member = $this->memberDAOManager->getForId($id);
		
		
		if ($request->existGET('foot')) {
			//chargement des downlines
			switch ($request->getDataGET('foot')){
				case 'left' : {//left
					$members = $this->memberDAOManager->getLeftDownlinesChilds($member->getId());
				}break;
				
				case 'middle' : {//middle
					$members = $this->memberDAOManager->getMiddleDownlinesChilds($member->getId());
				}break;
				
				case 'right' : {//right
					$members = $this->memberDAOManager->getRightDownlinesChilds($member->getId());
				}break;
				
				default : {//all Member
					$members = $this->memberDAOManager->getDownlinesChilds($member->getId());
				}
			}
			
			$request->addAttribute(self::ATT_MEMBERS, $members);
			
		}else {
			
			//comptage des downlines
			$left = $this->memberDAOManager->countLeftChild($member->getId());
			$middle = $this->memberDAOManager->countMiddleChild($member->getId());
			$right = $this->memberDAOManager->countRightChild($member->getId());
			
			$request->addAttribute(self::LEFT_CHILDS, $left);
			$request->addAttribute(self::MIDDLE_CHILDS, $middle);
			$request->addAttribute(self::RIGHT_CHILDS, $right);
		}
		
		$request->addAttribute(self::ATT_MEMBER, $member);
		
		$account = $this->getAccount($member);
		$account->calcul();
		$request->addAttribute(self::ATT_COMPTE, $account);
	}
	
	/**
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeWithdrawalsMember (Request $request, Request $response) : void {
		$id = intval($request->getDataGET('id'), 10);
		if (!$this->memberDAOManager->idExist($id)) {
			$response->sendError();
		}
		
		/**
		 * @var Member $member
		 */
		$member = $this->memberDAOManager->getForId($id);
		
		if ($request->existGET('requestId')) {
			$this->withdrawalDAOManager->validate(intval($request->getDataGET('requestId')), OfficeApplication::getConnectedUser()->getId());
		}
		
		
		if ($this->gradeMemberDAOManager->hasCurrent($member->getId())) {
			$gradeMember = $this->gradeMemberDAOManager->getCurrent($id);
			$gradeMember->setMember($member);
			$request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
		}
		
		if ($this->gradeMemberDAOManager->hasRequested($member->getId())) {
			$requestedGradeMember = $this->gradeMemberDAOManager->getRequested($member->getId());
			$requestedGradeMember->setMember($member);
			$request->addAttribute(self::ATT_REQUESTED_GRADE_MEMBER, $requestedGradeMember);
		}
		
		//Chargement des PV;
		$compte = $this->getAccount($member);
		
		if ($this->withdrawalDAOManager->hasOperation($member->getId())) {
			$withdrawals = $this->withdrawalDAOManager->forMember($member->getId());
		}else {
			$withdrawals = array();
		}
		
		$request->addAttribute(self::ATT_WITHDRAWALS, $withdrawals);
		
		$request->addAttribute(self::ATT_COMPTE, $compte);
		$request->addAttribute(self::ATT_MEMBER, $member);
	}
	
	
	/**
	 *
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeStateMember (Request $request, Request $response) : void {
		$request->addAttribute(self::ATT_VIEW_TITLE, "Union members");
		$id = intval($request->getDataGET('id'), 10);
		if (!$this->memberDAOManager->idExist($id)) {
			$response->sendError();
		}
		
		$state = ($request->getDataGET('state') == 'enable');
		
		/**
		 * @var Member $member
		 */
		$member = $this->memberDAOManager->getForId($id);
		
		if ($state != $member->isEnable()) {
			$this->memberDAOManager->updateState($id, $state);
		}
		
		$response->sendRedirect("/office/members/{$id}/");
		
	}
	
	/**
	 *
	 * @param Request $request
	 * @param Request $response
	 */
	public function executeUpgradeMember (Request $request, Request $response) : void {
		$id = intval($request->getDataGET('id'), 10);
		if (!$this->memberDAOManager->idExist($id)) {
			$response->sendError();
		}
		
		if ($this->gradeMemberDAOManager->hasRequested($id)) {
			$response->sendRedirect("/office/members/{$id}/");
		}
		
		$office = OfficeApplication::getConnectedUser()->getOffice();
		
		if ($this->gradeMemberDAOManager->hasOperation($office->getId())) {
			$office->setOperations($this->gradeMemberDAOManager->getOperations($office->getId()));
		}
		
		if ($this->virtualMoneyDAOManager->hasVirtualMoney($office->getId())) {
			$office->setVirtualMoneys($this->virtualMoneyDAOManager->forOffice($office->getId()));
		}
		
		/**
		 * @var Member $member
		 */
		$member = $this->memberDAOManager->getForId($id);
		$gradeMember = $this->gradeMemberDAOManager->getCurrent($id);
		$gradeMember->setMember($member);
		
		if ($request->getMethod() == Request::HTTP_POST) {
			$form = new GradeMemberFormValidator($this->getDaoManager());
			$request->addAttribute(GradeMemberFormValidator::FIELD_OFFICE_ADMIN, OfficeApplication::getConnectedUser());
			$request->addAttribute($form::FIELD_MEMBER, $member->getId());
			$gradeMember = $form->upgradeAfterValidation($request);
			
			if (!$form->hasError()) {
				$response->sendRedirect("/office/members/{$id}/");
			}
			
			$request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
			$form->includeFeedback($request);
		}
		
		$request->addAttribute(self::ATT_MEMBER, $member);
		$request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
		$grades = $this->gradeDAOManager->getAll();
		$request->addAttribute(self::ATT_GRADES, $grades);
	}
	
}

