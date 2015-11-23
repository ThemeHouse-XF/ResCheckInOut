<?php

/**
 *
 * @see XenResource_ControllerPublic_Resource
 */
class ThemeHouse_ResCheckInOut_Extend_XenResource_ControllerPublic_Resource extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenResource_ControllerPublic_Resource
{

    protected function _getResourceAddOrEditResponse(array $resource, array $category, array $attachments = array())
    {
        $response = parent::_getResourceAddOrEditResponse($resource, $category, $attachments);
        
        if ($response instanceof XenForo_ControllerResponse_View) {
            $response->params['conditions'] = $this->_getConditionModel()->getConditions();
        }
        
        return $response;
    }

    public function actionSave()
    {
        $GLOBALS['XenResource_ControllerPublic_Resource'] = $this;
        
        return parent::actionSave();
    }

    protected function _getResourceViewInfo(array $fetchOptions = array())
    {
        $fetchOptions += array(
            'th_resCheckInOut_join' => ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource::FETCH_CHECK_OUT
        );
        
        return parent::_getResourceViewInfo($fetchOptions);
    }

    protected function _getResourceViewWrapper($selectedTab, array $resource, array $category, 
        XenForo_ControllerResponse_View $subView)
    {
        $response = parent::_getResourceViewWrapper($selectedTab, $resource, $category, $subView);
        
        if ($response instanceof XenForo_ControllerResponse_View) {
            $resource = $response->params['resource'];
            $category = $response->params['category'];
            
            $response->params['canViewCheckInsAndOuts'] = $this->_getResourceModel()->canViewCheckInsAndOuts($resource, 
                $category);
        }
        
        return $response;
    }

    public function actionCheckInsAndOuts()
    {
        list($resource, $category) = $this->_getResourceViewInfo();
        
        if (!$this->_getResourceModel()->canViewCheckInsAndOuts($resource, $category, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }
        
        $this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/check-ins-and-outs', $resource));
        
        $viewParams = $this->_getCheckInsAndOutsListData($resource, $category);
        
        return $this->_getResourceViewWrapper('check_ins_and_outs', $resource, $category, 
            $this->responseView('ThemeHouse_ResCheckInOut_ViewPublic_Resource_CheckInsAndOuts',
                'th_resource_check_ins_and_outs_rescheckinout', $viewParams));
    }

    protected function _getCheckInsAndOutsListData(array $resource, array $category, array $extraConditions = array())
    {
        $checkOutModel = $this->_getCheckOutModel();
        
        $conditions = $this->_getCheckInsAndOutsListConditions($resource, $category);
        $originalConditions = $conditions;
        $conditions = array_merge($conditions, $extraConditions);

        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
        $perPage = XenForo_Application::getOptions()->th_resCheckInOut_resourceCheckOutsPerPage;
        
        $totalCheckOuts = $checkOutModel->countCheckOuts($conditions);
        $checkOuts = array();
        if ($totalCheckOuts) {
            $fetchOptions = array(
                'join' => ThemeHouse_ResCheckInOut_Model_CheckOut::FETCH_CHECK_OUT_FULL,
                'setUserId' => $resource['user_id'],
                'order' => 'check_in_date',
                'direction' => 'desc',
                'page' => $page,
                'perPage' => $perPage
            );
            
            $checkOuts = $checkOutModel->getCheckOuts($conditions, $fetchOptions);
        }
        
        return array(
            'resource' => $resource,
            'category' => $category,
            
            'checkOuts' => $checkOutModel->prepareCheckOuts($checkOuts, $resource, $category),
            'totalCheckOuts' => $totalCheckOuts,
            
            'page' => $page,
            'perPage' => $perPage,
            
            'search_type' => $conditions['search_type'],
            'search_user' => $conditions['search_user'],
            
            'pageNavParams' => array(
                'search_type' => ($originalConditions['search_type'] ? $originalConditions['search_type'] : false),
                'search_user' => ($originalConditions['search_user'] ? $originalConditions['search_user'] : false)
            )
        );
    }

    protected function _getCheckInsAndOutsListConditions(array $resource, array $category)
    {
        $conditions = array(
            'resource_id' => $resource['resource_id']
        ) + $this->_getCategoryModel()->getPermissionBasedFetchConditions($category);
        
        $searchType = $this->_input->filterSingle('search_type', XenForo_Input::STRING);
        $searchUser = $this->_input->filterSingle('search_user', XenForo_Input::STRING);
        
        if ($searchUser && $user = $this->getModelFromCache('XenForo_Model_User')->getUserByName($searchUser)) {
            $conditions = array(
                'search_type' => $searchType,
                'search_user' => $user['username'],
                'search_user_id' => $user['user_id']
            );
        } else {
            $conditions = array(
                'search_type' => '',
                'search_user' => ''
            );
        }
        
        return $conditions;
    }

    public function actionCheckOut()
    {
        list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, 
            array(
                'th_resCheckInOut_join' => ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource::FETCH_CHECK_OUT
            ));
        
        if (!$this->_getCheckOutModel()->canCheckOutResource($resource, $category, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }
        
        $locations = $this->_getLocationModel()->getLocations();
        $conditions = $this->_getConditionModel()->getConditions();
        $paymentMethods = $this->_getPaymentMethodModel()->getPaymentMethods();
        
        $checkOut = $this->_getCheckOutModel()->getDefaultCheckOut($resource, $category);
        
        $viewParams = array(
            'resource' => $resource,
            'category' => $category,
            'checkOut' => $checkOut,
            'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
            'locations' => $locations,
            'conditions' => $conditions,
            'paymentMethods' => $paymentMethods,
            'canChangeCheckInOutUser' => $this->_getUserModel()->canChangeCheckInOutUser()
        );
        
        return $this->responseView('ThemeHouse_ResCheckInOut_ViewPublic_CheckOut',
            'th_resource_check_out_rescheckinout', $viewParams);
    }

    public function actionSaveCheckOut()
    {
        $this->_assertPostOnly();
        
        if ($checkOutId = $this->_input->filterSingle('resource_check_out_id', XenForo_Input::UINT)) {
            list($checkOut, $resource, $category) = $this->_getResourceHelper()->assertCheckOutValidAndViewable();
            if (!$this->_getCheckOutModel()->canEditCheckOut($checkOut, $resource, $category, $errorPhraseKey)) {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        } else {
            list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, 
                array(
                    'th_resCheckInOut_join' => ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource::FETCH_CHECK_OUT
                ));
            if (!$this->_getCheckOutModel()->canCheckOutResource($resource, $category, $errorPhraseKey)) {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
            $checkOut = false;
        }
        
        $dwInput = $this->_input->filter(
            array(
                'check_out_username' => XenForo_Input::STRING,
                'check_out_to_username' => XenForo_Input::STRING,
                'check_out_date' => XenForo_Input::STRING,
                'check_out_due_date' => XenForo_Input::STRING,
                'check_out_location_id' => XenForo_Input::UINT,
                'check_out_location_title' => XenForo_Input::STRING,
                'check_out_condition_id' => XenForo_Input::UINT,
                'check_out_fee_amount' => XenForo_Input::STRING,
                'check_out_fee_payment_method_id' => XenForo_Input::UINT,
                'check_out_deposit_amount' => XenForo_Input::STRING,
                'check_out_deposit_payment_method_id' => XenForo_Input::UINT
            ));
        
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->_getUserModel();
        
        $visitor = XenForo_Visitor::getInstance();
        
        if (!$userModel->canChangeCheckInOutUser()) {
            $dwInput['check_out_username'] = $visitor['username'];
        }
        
        if (!$dwInput['check_out_username'] || !$dwInput['check_out_to_username']) {
            return $this->responseError(new XenForo_Phrase('please_enter_value_for_all_required_fields'));
        }
        
        if ($dwInput['check_out_location_id']) {
            unset($dwInput['check_out_location_title']);
        }
        
        $user = $userModel->getUserByName($dwInput['check_out_username']);
        if (!$user) {
            return $this->responseError(
                new XenForo_Phrase('requested_user_x_not_found', 
                    array(
                        'name' => $dwInput['check_out_username']
                    )));
        }
        $dwInput['check_out_username'] = $user['username'];
        $dwInput['check_out_user_id'] = $user['user_id'];
        
        $toUser = $userModel->getUserByName($dwInput['check_out_to_username']);
        if (!$toUser) {
            return $this->responseError(
                new XenForo_Phrase('requested_user_x_not_found', 
                    array(
                        'name' => $dwInput['check_out_to_username']
                    )));
        }
        $dwInput['check_out_to_username'] = $toUser['username'];
        $dwInput['check_out_to_user_id'] = $toUser['user_id'];
        
        $dw = XenForo_DataWriter::create('ThemeHouse_ResCheckInOut_DataWriter_CheckOut');
        if ($checkOutId) {
            $dw->setExistingData($checkOutId);
        } else {
            $dw->set('resource_id', $resource['resource_id']);
        }
        
        $dw->bulkSet($dwInput);
        
        $dw->save();
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
            XenForo_Link::buildPublicLink('resources', $resource));
    }

    public function actionCheckIn()
    {
        list($checkOut, $resource, $category) = $this->_getResourceHelper()->assertCheckOutValidAndViewable();
        
        if (!$this->_getCheckOutModel()->canCheckInResource($resource, $category, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }
        
        $locations = $this->_getLocationModel()->getLocations();
        $conditions = $this->_getConditionModel()->getConditions();
        
        $paymentMethodModel = $this->_getPaymentMethodModel();
        $paymentMethod = $paymentMethodModel->getPaymentMethodById($checkOut['check_out_deposit_payment_method_id']);
        $refundMethodIds = explode(',', $paymentMethod['refund_method_ids']);
        $refundMethodIds[] = $checkOut['check_out_deposit_payment_method_id'];
        $refundMethods = $paymentMethodModel->getPaymentMethodsByIds($refundMethodIds);
        
        $viewParams = array(
            'resource' => $resource,
            'category' => $category,
            'checkOut' => $checkOut,
            'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
            'locations' => $locations,
            'conditions' => $conditions,
            'refundMethods' => $refundMethods,
            'canChangeCheckInOutUser' => $this->_getUserModel()->canChangeCheckInOutUser()
        );
        
        return $this->responseView('ThemeHouse_ResCheckInOut_ViewPublic_CheckIn',
            'th_resource_check_in_rescheckinout', $viewParams);
    }

    public function actionSaveCheckIn()
    {
        $this->_assertPostOnly();
        
        $checkOutId = $this->_input->filterSingle('resource_check_out_id', XenForo_Input::UINT);
        
        list($checkOut, $resource, $category) = $this->_getResourceHelper()->assertCheckOutValidAndViewable();
        if ($checkOut['check_in_user_id']) {
            if (!$this->_getCheckOutModel()->canEditCheckIn($checkOut, $resource, $category, $errorPhraseKey)) {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        } else {
            if (!$this->_getCheckOutModel()->canCheckInResource($resource, $category, $errorPhraseKey)) {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        }
        
        $dwInput = $this->_input->filter(
            array(
                'check_in_username' => XenForo_Input::STRING,
                'check_in_from_username' => XenForo_Input::STRING,
                'check_in_date' => XenForo_Input::STRING,
                'check_in_location_id' => XenForo_Input::UINT,
                'check_in_condition_id' => XenForo_Input::UINT,
                'check_in_deposit_refund_amount' => XenForo_Input::STRING,
                'check_in_deposit_refund_method_id' => XenForo_Input::UINT
            ));
        
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->_getUserModel();
        
        $visitor = XenForo_Visitor::getInstance();
        
        if (!$userModel->canChangeCheckInOutUser()) {
            $dwInput['check_in_username'] = $visitor['username'];
        }
        
        if (!$dwInput['check_in_username'] || !$dwInput['check_in_from_username']) {
            return $this->responseError(new XenForo_Phrase('please_enter_value_for_all_required_fields'));
        }
        
        if ($dwInput['check_in_location_id']) {
            unset($dwInput['check_in_location_title']);
        }
        
        $user = $userModel->getUserByName($dwInput['check_in_username']);
        if (!$user) {
            return $this->responseError(
                new XenForo_Phrase('requested_user_x_not_found', 
                    array(
                        'name' => $dwInput['check_in_username']
                    )));
        }
        $dwInput['check_in_username'] = $user['username'];
        $dwInput['check_in_user_id'] = $user['user_id'];
        
        $fromUser = $userModel->getUserByName($dwInput['check_in_from_username']);
        if (!$fromUser) {
            return $this->responseError(
                new XenForo_Phrase('requested_user_x_not_found', 
                    array(
                        'name' => $dwInput['check_out_from_username']
                    )));
        }
        $dwInput['check_in_from_username'] = $fromUser['username'];
        $dwInput['check_in_from_user_id'] = $fromUser['user_id'];
        
        $dw = XenForo_DataWriter::create('ThemeHouse_ResCheckInOut_DataWriter_CheckOut');
        $dw->setExistingData($checkOutId);
        
        $dw->bulkSet($dwInput);
        
        $dw->save();
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
            XenForo_Link::buildPublicLink('resources', $resource));
    }

    public function actionFindCheckInsAndOutsUser()
    {
        $this->_assertPostOnly();
        
        list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();
        
        $checkOutModel = $this->_getCheckOutModel();
        $resourceId = $resource['resource_id'];
        $q = $this->_input->filterSingle('q', XenForo_Input::STRING);
        
        switch ($this->_input->filterSingle('search_type', XenForo_Input::STRING)) {
            case 'checked_out_by':
                $users = $checkOutModel->findCheckedOutByUsersForResource($resourceId, $q);
                break;
            
            case 'checked_out_to':
                $users = $checkOutModel->findCheckedOutToUsersForResource($resourceId, $q);
                break;
            
            case 'checked_in_by':
                $users = $checkOutModel->findCheckedInByUsersForResource($resourceId, $q);
                break;
            
            case 'checked_in_from':
                $users = $checkOutModel->findCheckedInFromUsersForResource($resourceId, $q);
                break;
            
            default:
                $users = array();
        }
        
        $viewParams = array(
            'users' => $users
        );
        
        return $this->responseView('XenForo_ViewPublic_Member_Find', 'member_autocomplete', $viewParams);
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_CheckOut
     */
    protected function _getCheckOutModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_CheckOut');
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_Condition
     */
    protected function _getConditionModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_Condition');
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_Location
     */
    protected function _getLocationModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_Location');
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_PaymentMethod
     */
    protected function _getPaymentMethodModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_PaymentMethod');
    }

    /**
     *
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }
}