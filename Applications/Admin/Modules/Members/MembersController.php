<?php
namespace Applications\Admin\Modules\Members;

use Library\HTTPRequest;
use Library\HTTPResponse;
use Managers\GradeDAOManager;
use Validators\GradeMemberFormValidator;
use Validators\LocalisationFormValidator;
use Validators\MemberFormValidator;
use Managers\CountryDAOManager;
use Applications\Admin\AdminApplication;
use Managers\GradeMemberDAOManager;
use Library\AppMessage;
use Applications\Admin\AdminController;
use Entities\Member;
use Library\Image2D\Mlm\TreeFormatter;
use Library\Image2D\Mlm\Ternary\TernaryTreeBuilder;
use Library\Image2D\Mlm\Ternary\TernaryTreeRender;

/**
 *
 * @author Esaie MHS
 *        
 */
class MembersController extends AdminController
{
    const ATT_SELECTED_ITEM_MENU = 'SELECTED_ITEM_MENU';
    const ATT_ITEM_MENU_DASHBORAD = 'ITEM_MENU_DASHBORAD';
    const ATT_ITEM_MENU_WITHDRAWALS = 'ITEM_MENU_WITHDRAWALS';
    const ATT_ITEM_MENU_DOWNLINES = 'ITEM_MENU_DOWNLINES';
    
    const CONFIG_MAX_MEMBER_VIEW_STEP = 'maxMembers';
    
    
    const ATT_COUNTRYS = 'countrys';    
    const ATT_LOCALISATION = 'localisation';
    const ATT_COMPTE = 'compte';
    const ATT_MEMBERS = 'members';
    const ATT_MEMBERS_REQUEST = 'membersRequest';
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
    
    const ATT_TREE_FORMATER = 'TREE_FORMATTER';
    
    
    /**
     * @var GradeDAOManager
     */
    private $gradeDAOManager;
    
    /**
     * @var CountryDAOManager
     */
    private $countryDAOManager;
    
    
    /**
     * @var GradeMemberDAOManager
     */
    private $gradeMemberDAOManager;
    
    
    /**
     * {@inheritDoc}
     * @see \Library\Controller::__construct()
     */
    public function __construct(\Library\Application $application, $action, $module)
    {
        parent::__construct($application, $action, $module);
        $nombre = $this->memberDAOManager->countAll();
        $application->getHttpRequest()->addAttribute(self::PARAM_MEMBER_COUNT, $nombre);
        $application->getHttpRequest()->addAttribute(self::ATT_VIEW_TITLE, "Union members");
    }

    /**
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeMembers (HTTPRequest $request, HTTPResponse $response) : void {
        
        if ($request->getMethod() == HTTPRequest::HTTP_POST) {//RECHERCHE D'UN MEMBRE EN FONCTION DES SON ID
            if ($this->memberDAOManager->matriculeExist($request->getDataPOST('id'))) {
                $member = $this->memberDAOManager->getForMatricule($request->getDataPOST('id'));
                $response->sendRedirect("/admin/members/{$member->getId()}/");
            }
            
            $message = new AppMessage('Error', "Know user ID in system => {$request->getDataPOST('id')}", AppMessage::MESSAGE_ERROR);
            $request->addAppMessage($message);
            $response->sendRedirect('/admin/');
        }
        
        if ($this->gradeMemberDAOManager->hasRequest()) {
        	$requestMembers = $this->gradeMemberDAOManager->getAllRequest();
        }else {
        	$requestMembers = array();
        }
        
        $nombre = $this->memberDAOManager->countAll();
        if ($nombre>0) {
            if ($request->existGET('limit')) {
                $offset = intval($request->getDataGET('offset'), 10);
                $limit = intval($request->getDataGET('limit'), 10);
                $members = $this->memberDAOManager->getAll($limit, $offset);
            } else {
                $limit = intval($request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)!=null? $request->getApplication()->getConfig()->get(self::CONFIG_MAX_MEMBER_VIEW_STEP)->getValue() : 50);
                $members = $this->memberDAOManager->getAll($limit, 0);
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
        $request->addAttribute(self::ATT_MEMBERS_REQUEST, $requestMembers);
    }
    
    
    /**
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeAddMember (HTTPRequest $request, HTTPResponse $response) : void {
        
        if ($request->getMethod() == HTTPRequest::HTTP_POST) {
            $form = new GradeMemberFormValidator($this->getDaoManager());
            $request->addAttribute(GradeMemberFormValidator::FIELD_OFFICE_ADMIN, AdminApplication::getConnectedUser());
            $gm = $form->createAfterValidation($request);
            
            if (!$form->hasError()) {
                $response->sendRedirect("/admin/members/");
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
     * Dashoard du compte d'un membre
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeMember (HTTPRequest $request, HTTPResponse $response) : void {
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $request->addAttribute(self::ATT_SELECTED_ITEM_MENU, self::ATT_ITEM_MENU_DASHBORAD);
        
        /**
         * @var \Entities\Member $member
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
     * 
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeUpdateMember (HTTPRequest $request, HTTPResponse $response) : void {
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        
        if ($request->getMethod() == HTTPRequest::HTTP_POST) {
        	$form = new MemberFormValidator($this->getDaoManager());
        	$request->addAttribute($form::CHAMP_ID, $id);
        	$member = $form->updateAfterValidation($request);
        	if (!$form->hasError()) {
        		$response->sendRedirect("/admin/members/{$id}/");
        	}
        	$form->includeFeedback($request);
        	$request->addAttribute($form::MEMBER_FEEDBACK, $form->toFeedback());
        }
        
        $compte = $this->getAccount($member);
        
        $request->addAttribute(self::ATT_COMPTE, $compte);
        $request->addAttribute(self::ATT_MEMBER, $member);
    }
    
    /**
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeResetPassword (HTTPRequest $request, HTTPResponse $response) : void {
    	$id = intval($request->getDataGET('id'), 10);
    	if (!$this->memberDAOManager->idExist($id)) {
    		$response->sendError();
    	}
    	
    	/**
    	 * @var \Entities\Member $member
    	 */
    	$member = $this->memberDAOManager->getForId($id);
    	
    	if ($request->getMethod() == HTTPRequest::HTTP_POST) {
    		$id = intval($request->getDataGET('id'), 10);
    		
    		$form = new MemberFormValidator($this->getDaoManager());
    		$request->addAttribute($form::CHAMP_ID, $id);
    		$form->resetPasswordAfterValidation($request);
    		
    		if (!$form->hasError()) {
    			$response->sendRedirect("/admin/members/{$id}/");
    		}
    		
    		$form->includeFeedback($request);
    	}
    	
    	$compte = $this->getAccount($member);
    	
    	
    	
    	$request->addAttribute(self::ATT_COMPTE, $compte);
    	$request->addAttribute(self::ATT_MEMBER, $member);
    }
    
    /**
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeDownlines (HTTPRequest $request, HTTPResponse $response) : void {
        
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $request->addAttribute(self::ATT_SELECTED_ITEM_MENU, self::ATT_ITEM_MENU_DOWNLINES);
        
        /**
         * @var \Entities\Member $member
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
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeDownlinesHierarchy (HTTPRequest $request, HTTPResponse $response) : void {
        
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $request->addAttribute(self::ATT_SELECTED_ITEM_MENU, self::ATT_ITEM_MENU_DOWNLINES);
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        
        
        if ($request->existGET('foot')) {
            //chargement des downlines
            switch ($request->getDataGET('foot')){
                case 'left' : {//left
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::LEFT_FOOT);
                }break;
                
                case 'middle' : {//middle
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::MIDDEL_FOOT);
                }break;
                
                case 'right' : {//right
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::RIGHT_FOOT);
                }break;
                
                default : {//all Member
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId());
                }
            }
            
        }else {
            $childs = $this->memberDAOManager->getDownlinesStacks($member->getId());
        }
        
        $member->setChilds($childs);
        $member->setParent(null);
        
        $formater = new TreeFormatter($member);
        $account = $this->getAccount($member);
        $account->calcul();
        $request->addAttribute(self::ATT_TREE_FORMATER, $formater);
        $request->addAttribute(self::ATT_COMPTE, $account);
        $request->addAttribute(self::ATT_MEMBER, $member);
    }
    
    
    /**
     * generation de l'arbre genealogique d'un membre
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeTree (HTTPRequest $request, HTTPResponse $response) : void {
        
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $response->sendRedirect("/admin/members/{$id}/");
        
        $request->addAttribute(self::ATT_SELECTED_ITEM_MENU, self::ATT_ITEM_MENU_DOWNLINES);
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        
        
        if ($request->existGET('foot')) {
            //chargement des downlines
            switch ($request->getDataGET('foot')){
                case 'left' : {//left
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::LEFT_FOOT);
                }break;
                
                case 'middle' : {//middle
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::MIDDEL_FOOT);
                }break;
                
                case 'right' : {//right
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId(), Member::RIGHT_FOOT);
                }break;
                
                default : {//all Member
                    $childs = $this->memberDAOManager->getDownlinesStacks($member->getId());
                }
            }
            
        }else {
            $childs = $this->memberDAOManager->getDownlinesStacks($member->getId());
        }
        
        $member->setChilds($childs);
        $member->setParent(null);
        
        $builder = new TernaryTreeBuilder($member, 100);
        $render = new TernaryTreeRender($builder);
        
        $render->render();
        $account = $this->getAccount($member);
        $account->calcul();
        $request->addAttribute(self::ATT_COMPTE, $account);
        $request->addAttribute(self::ATT_MEMBER, $member);
    }
    
    
    
    /**
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeWithdrawalsMember (HTTPRequest $request, HTTPResponse $response) : void {
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $request->addAttribute(self::ATT_SELECTED_ITEM_MENU, self::ATT_ITEM_MENU_WITHDRAWALS);
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        
        if ($request->existGET('requestId')) {
            $this->withdrawalDAOManager->validate(intval($request->getDataGET('requestId')), AdminApplication::getConnectedUser()->getId());
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
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeStateMember (HTTPRequest $request, HTTPResponse $response) : void {
        $request->addAttribute(self::ATT_VIEW_TITLE, "Union members");
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        $state = ($request->getDataGET('state') == 'enable');
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        
        if ($state != $member->isEnable()) {
            $this->memberDAOManager->updateState($id, $state);
        }
        
        $response->sendRedirect("/admin/members/{$id}/");
        
    }
    
    /**
     * 
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeUpgradeMember (HTTPRequest $request, HTTPResponse $response) : void {
        $id = intval($request->getDataGET('id'), 10);
        if (!$this->memberDAOManager->idExist($id)) {
            $response->sendError();
        }
        
        if ($this->gradeMemberDAOManager->hasRequested($id)) {
            $response->sendRedirect("/admin/members/{$id}/");
        }
        
        /**
         * @var \Entities\Member $member
         */
        $member = $this->memberDAOManager->getForId($id);
        $gradeMember = $this->gradeMemberDAOManager->getCurrent($id);
        $gradeMember->setMember($member);
        
        if ($request->getMethod() == HTTPRequest::HTTP_POST) {
            $form = new GradeMemberFormValidator($this->getDaoManager());
            $request->addAttribute(GradeMemberFormValidator::FIELD_OFFICE_ADMIN, AdminApplication::getConnectedUser());
            $request->addAttribute($form::FIELD_MEMBER, $member->getId());
            $gradeMember = $form->upgradeAfterValidation($request);
            
            if (!$form->hasError()) {
                $response->sendRedirect("/admin/members/{$id}/");
            }
            
            $request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
            $form->includeFeedback($request);
        }
        
        $request->addAttribute(self::ATT_MEMBER, $member);
        $request->addAttribute(self::ATT_GRADE_MEMBER, $gradeMember);
        $grades = $this->gradeDAOManager->getAll();
        $request->addAttribute(self::ATT_GRADES, $grades);
    }
    
    
    /**
     *
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function executeCertifyMember (HTTPRequest $request, HTTPResponse $response) : void {
        
        $id = intval($request->getDataGET('id'), 10);
        $gmId = intval($request->getDataGET('idGradeMember'), 10);
        
        if (!$this->memberDAOManager->idExist($id) || !$this->gradeMemberDAOManager->idExist($gmId)) {
            $response->sendError();
        }
        
        /**
         * @var \Entities\GradeMember $gradeMember
         */
        $gradeMember = $this->gradeMemberDAOManager->getForId($gmId);
        $member = $gradeMember->getMember();
        
        if ($gradeMember->isEnable()) {
            $response->sendError("impossible to share the packs because the operation is already done, and this operation is irreversible");
        }
        
        //Activation du comote
        $form = new GradeMemberFormValidator($this->getDaoManager());
        $request->addAttribute($form::CHAMP_ID, $gmId);
        $form->enableAfterValidation($request);
        
        $request->addAppMessage($form->buildAppMessage());
        $response->sendRedirect("/admin/members/");
    }
}

