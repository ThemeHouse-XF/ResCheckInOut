<?php

class ThemeHouse_ResCheckInOut_ControllerPublic_CheckOut extends XenForo_ControllerPublic_Abstract
{

    protected function _preDispatch($action)
    {
        if (!$this->_getResourceModel()->canViewResources($error)) {
            throw $this->getErrorOrNoPermissionResponseException($error);
        }
    }

    public function actionIndex()
    {
        if ($this->_input->filterSingle('user_id', XenForo_Input::UINT)) {
            return $this->responseReroute(__CLASS__, 'view');
        }

        $resourceModel = $this->_getResourceModel();

        $checkOuts = $resourceModel->getMostActiveCheckOuts(20);
        if (!$checkOuts) {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                XenForo_Link::buildPublicLink('resources'));
        }

        $viewParams = array(
            'checkOuts' => $checkOuts
        );
        return $this->responseView('XenResource_ViewPublic_CheckOut_List', 'resource_checkOut_list', $viewParams);
    }

    public function actionView()
    {
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);

        if (!$user = $this->_getUserModel()->getUserById($userId)) {
            return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
        }

        $resourceModel = $this->_getResourceModel();

        $conditions = array(
            'check_out_user_id' => $userId
        );
        $conditions += $this->_getCategoryModel()->getPermissionBasedFetchConditions();

        $categories = $this->_getCategoryModel()->getViewableCategories();
        $conditions['resource_category_id'] = array_keys($categories);

        $aggregate = $resourceModel->getAggregateResourceData($conditions);

        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
        $perPage = XenForo_Application::get('options')->resourcesPerPage;

        $visitor = XenForo_Visitor::getInstance();

        if (!$aggregate['total_resources']) {
            $resources = array();
        } else {
            $resources = $resourceModel->getResources($conditions,
                array(
                    'join' => XenResource_Model_Resource::FETCH_CATEGORY | XenResource_Model_Resource::FETCH_VERSION |
                         XenResource_Model_Resource::FETCH_USER,
                        'permissionCombinationId' => $visitor['permission_combination_id'],
                        'order' => 'last_update',
                        'direction' => 'desc',
                        'page' => $page,
                        'perPage' => $perPage
                ));
        }

        $this->_getCategoryModel()->bulkSetCategoryPermCache($visitor['permission_combination_id'], $resources,
            'category_permission_cache');

        foreach ($resources as $key => $resource) {
            if (!$resourceModel->canViewResourceAndContainer($resource, $resource)) {
                unset($resources[$key]);
            }
        }

        $resources = $resourceModel->prepareResources($resources);
        $inlineModOptions = $resourceModel->getInlineModOptionsForResources($resources);

        $viewParams = array(
            'canChangeCheckInOutUser' => $this->_getUserModel()->canChangeCheckInOutUser(),

            'resources' => $resources,
            'inlineModOptions' => $inlineModOptions,

            'page' => $page,
            'perPage' => $perPage,

            'user' => $user,
            'aggregate' => $aggregate,

            'fromProfile' => $this->_input->filterSingle('profile', XenForo_Input::UINT)
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewPublic_CheckOuts_View',
            'th_resource_check_outs_view_rescheckinout', $viewParams);
    }

    public function actionCheckInOutAsUser()
    {
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $username = $this->_input->filterSingle('username', XenForo_Input::STRING);

        if ($userId && !$user = $this->_getUserModel()->getUserById($userId)) {
            return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
        } elseif ($username && !$user = $this->_getUserModel()->getUserByName($username)) {
            return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
        }

        if (!$this->_getUserModel()->canChangeCheckInOutUser()) {
            return $this->responseNoPermission();
        }

        if ($this->isConfirmedPost()) {
            if (!empty($user)) {
                $this->_getUserModel()->setCheckInOutUser($user['user_id']);

                return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $this->getDynamicRedirect());
            }

            return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
        }

        $visitor = XenForo_Visitor::getUserId();

        $username = '';
        if ($visitor['check_in_out_user_id_th'] && !$userId) {
            $username = $visitor['username'];
        } elseif (!empty($user)) {
            $username = $user['username'];
        }

        $viewParams = array(
            'username' => $username,
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewPublic_CheckInOutAsUser',
            'th_check_in_out_as_user_rescheckinout', $viewParams);
    }

    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('th_viewing_resource_check_outs_rescheckinout');
    }

    /**
     *
     * @return XenResource_Model_Resource
     */
    protected function _getResourceModel()
    {
        return $this->getModelFromCache('XenResource_Model_Resource');
    }

    /**
     *
     * @return XenResource_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('XenResource_Model_Category');
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