<?php

class ThemeHouse_ResCheckInOut_Model_CheckOut extends XenForo_Model
{

    const FETCH_CHECK_OUT_CONDITION = 0x01;

    const FETCH_CHECK_IN_CONDITION = 0x02;

    const FETCH_CHECK_OUT_LOCATION = 0x04;

    const FETCH_CHECK_IN_LOCATION = 0x08;

    const FETCH_CHECK_OUT_USER = 0x10;

    const FETCH_CHECK_IN_USER = 0x20;

    const FETCH_CHECK_OUT_TO_USER = 0x40;

    const FETCH_CHECK_IN_FROM_USER = 0x80;

    const FETCH_FEE_PAYMENT_METHOD = 0x100;

    const FETCH_DEPOSIT_PAYMENT_METHOD = 0x200;

    const FETCH_DEPOSIT_REFUND_METHOD = 0x400;

    const FETCH_CHECK_OUT_FULL = 0x7FF;

    public function getCheckOutById($checkOutId)
    {
        return $this->_getDb()->fetchRow(
            '
                SELECT *
                FROM xf_resource_check_out_th AS check_out
                WHERE resource_check_out_id = ?
            ', $checkOutId);
    }

    public function getCheckOuts(array $conditions = array(), array $fetchOptions = array())
    {
        $whereClause = $this->prepareCheckOutConditions($conditions, $fetchOptions);
        
        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);
        
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        
        return $this->fetchAllKeyed(
            $this->limitQueryResults(
                '
                    SELECT check_out.*
        				' . $joinOptions['selectFields'] . '
                    FROM xf_resource_check_out_th AS check_out
        			' . $joinOptions['joinTables'] . '
                    WHERE ' . $whereClause . '
                    ORDER BY check_out_date DESC, resource_check_out_id DESC
                ', $limitOptions['limit'], $limitOptions['offset']), 
            'resource_check_out_id');
    }

    public function getCheckOutsByIds(array $checkOutIds, array $fetchOptions = array())
    {
        if (empty($checkOutIds)) {
            return array();
        }
        
        return $this->getCheckOuts(array(
            'resource_check_out_id' => $checkOutIds
        ), $fetchOptions);
    }

    public function getCheckOutIdsInRange($start, $limit)
    {
        $db = $this->_getDb();
        
        return $db->fetchCol(
            $db->limit(
                '
			SELECT resource_check_out_id
			FROM xf_resource_check_out_th
			WHERE resource_check_out_id > ?
			ORDER BY resource_check_out_id
		', $limit), $start);
    }

    public function countCheckOuts(array $conditions = array())
    {
        $fetchOptions = array();
        $whereClause = $this->prepareCheckOutConditions($conditions, $fetchOptions);
        
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        
        return $this->_getDb()->fetchOne(
            '
                SELECT COUNT(*)
                FROM xf_resource_check_out_th AS check_out
                WHERE ' . $whereClause . '
            ');
    }

    public function prepareCheckOutConditions(array $conditions, array &$fetchOptions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();
        
        if (!empty($conditions['resource_id'])) {
            if (is_array($conditions['resource_id'])) {
                $sqlConditions[] = 'check_out.resource_id IN(' . $db->quote($conditions['resource_id']) . ')';
            } else {
                $sqlConditions[] = 'check_out.resource_id = ' . $db->quote($conditions['resource_id']);
            }
        }
        
        if (!empty($conditions['resource_check_out_id'])) {
            if (is_array($conditions['resource_check_out_id'])) {
                $sqlConditions[] = 'check_out.resource_check_out_id IN(' .
                     $db->quote($conditions['resource_check_out_id']) . ')';
            } else {
                $sqlConditions[] = 'check_out.resource_check_out_id = ' .
                     $db->quote($conditions['resource_check_out_id']);
            }
        }
        
        if (!empty($conditions['check_out_due_date'])) {
            if (is_array($conditions['check_out_due_date'])) {
                $sqlConditions[] = 'check_out.check_out_due_date IN(' . $db->quote($conditions['check_out_due_date']) .
                     ')';
            } else {
                $sqlConditions[] = 'check_out.check_out_due_date = ' . $db->quote($conditions['check_out_due_date']);
            }
        }
        
        if (!empty($conditions['last_alert_date']) && is_array($conditions['last_alert_date'])) {
            $sqlConditions[] = $this->getCutOffCondition("check_out.last_alert_date", $conditions['last_alert_date']);
        }
        
        if (isset($conditions['checkedIn'])) {
            if (!$conditions['checkedIn']) {
                $sqlConditions[] = 'check_out.check_in_date = 0';
            }
        }
        
        if (!empty($conditions['search_type']) && !empty($conditions['search_user_id'])) {
            switch ($conditions['search_type']) {
                case 'checked_out_by':
                    $sqlConditions[] = 'check_out.check_out_user_id = ' . $db->quote($conditions['search_user_id']);
                    break;
                
                case 'checked_out_to':
                    $sqlConditions[] = 'check_out.check_out_to_user_id = ' . $db->quote($conditions['search_user_id']);
                    break;
                
                case 'checked_in_by':
                    $sqlConditions[] = 'check_out.check_in_user_id = ' . $db->quote($conditions['search_user_id']);
                    break;
                
                case 'checked_in_from':
                    $sqlConditions[] = 'check_out.check_in_from_user_id = ' . $db->quote(
                        $conditions['search_user_id']);
                    break;
            }
        }
        
        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareUserFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        
        if (!empty($fetchOptions['join'])) {
            if ($fetchOptions['join'] & self::FETCH_CHECK_OUT_CONDITION) {
                $selectFields .= ',
					check_out_condition.title AS check_out_condition_title';
                $joinTables .= '
					LEFT JOIN xf_resource_condition_th AS check_out_condition ON
						(check_out_condition.resource_condition_id = check_out.check_out_condition_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_IN_CONDITION) {
                $selectFields .= ',
					check_in_condition.title AS check_in_condition_title';
                $joinTables .= '
					LEFT JOIN xf_resource_condition_th AS check_in_condition ON
						(check_in_condition.resource_condition_id = check_out.check_in_condition_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_OUT_LOCATION) {
                $selectFields .= ',
					IF (check_out_location.title, check_out_location.title, check_out.check_out_location_title) AS check_out_location_title';
                $joinTables .= '
					LEFT JOIN xf_check_in_out_location_th AS check_out_location ON
						(check_out_location.location_id = check_out.check_out_location_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_IN_LOCATION) {
                $selectFields .= ',
					IF (check_in_location.title, check_in_location.title, check_out.check_in_location_title) AS check_in_location_title';
                $joinTables .= '
					LEFT JOIN xf_check_in_out_location_th AS check_in_location ON
						(check_in_location.location_id = check_out.check_in_location_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_OUT_USER) {
                $selectFields .= ',
					check_out_user.username AS check_out_username, check_out_user.gender AS check_out_user_gender,
                    check_out_user.avatar_date AS check_out_user_avatar_date, check_out_user.gravatar AS check_out_user_gravatar,
                    check_out_user.avatar_width AS check_out_user_avatar_width, check_out_user.avatar_height AS check_out_user_avatar_height';
                $joinTables .= '
					LEFT JOIN xf_user AS check_out_user ON
						(check_out_user.user_id = check_out.check_out_user_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_IN_USER) {
                $selectFields .= ',
					check_in_user.username AS check_in_username, check_out_user.gender AS check_out_user_gender,
                    check_in_user.avatar_date AS check_in_user_avatar_date, check_in_user.gravatar AS check_in_user_gravatar,
                    check_in_user.avatar_width AS check_in_user_avatar_width, check_in_user.avatar_height AS check_in_user_avatar_height';
                $joinTables .= '
					LEFT JOIN xf_user AS check_in_user ON
						(check_in_user.user_id = check_out.check_in_user_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_OUT_TO_USER) {
                $selectFields .= ',
					check_out_to_user.username AS check_out_to_username, check_out_to_user.gender AS check_out_to_user_gender,
                    check_out_to_user.avatar_date AS check_out_to_user_avatar_date, check_out_to_user.gravatar AS check_out_to_user_gravatar,
                    check_out_to_user.avatar_width AS check_out_to_user_avatar_width, check_out_to_user.avatar_height AS check_out_to_user_avatar_height';
                $joinTables .= '
					LEFT JOIN xf_user AS check_out_to_user ON
						(check_out_to_user.user_id = check_out.check_out_to_user_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_CHECK_IN_USER) {
                $selectFields .= ',
					check_in_from_user.username AS check_in_from_username, check_in_from_user.gender AS check_out_user_gender,
                    check_in_from_user.avatar_date AS check_in_from_user_avatar_date, check_in_from_user.gravatar AS check_in_from_user_gravatar,
                    check_in_from_user.avatar_width AS check_in_from_user_avatar_width, check_in_from_user.avatar_height AS check_in_from_user_avatar_height';
                $joinTables .= '
					LEFT JOIN xf_user AS check_in_from_user ON
						(check_in_from_user.user_id = check_out.check_in_from_user_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_FEE_PAYMENT_METHOD) {
                $selectFields .= ',
					fee_payment_method.title AS check_out_fee_payment_method_title';
                $joinTables .= '
					LEFT JOIN xf_check_in_out_payment_method_th AS fee_payment_method ON
						(fee_payment_method.payment_method_id = check_out.check_out_fee_payment_method_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_DEPOSIT_PAYMENT_METHOD) {
                $selectFields .= ',
					deposit_payment_method.title AS check_out_deposit_payment_method_title';
                $joinTables .= '
					LEFT JOIN xf_check_in_out_payment_method_th AS deposit_payment_method ON
						(deposit_payment_method.payment_method_id = check_out.check_out_deposit_payment_method_id)';
            }
            
            if ($fetchOptions['join'] & self::FETCH_DEPOSIT_REFUND_METHOD) {
                $selectFields .= ',
					deposit_refund_method.title AS check_in_deposit_refund_method_title';
                $joinTables .= '
					LEFT JOIN xf_check_in_out_payment_method_th AS deposit_refund_method ON
						(deposit_refund_method.payment_method_id = check_out.check_in_deposit_refund_method_id)';
            }
        }
        
        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function getDefaultCheckOut(array $resource, array $category, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser);
        
        $todayDate = XenForo_Locale::date(XenForo_Application::$time, 'picker');
        
        $xenOptions = XenForo_Application::get('options');
        
        $dueTime = XenForo_Application::$time + $xenOptions->th_resCheckInOut_defaultDueDays * 24 * 60 * 60;
        $dueDate = XenForo_Locale::date($dueTime, 'picker');
        
        return array(
            'check_out_date' => $todayDate,
            'check_out_due_date' => $dueDate,
            'check_out_username' => $viewingUser['check_in_out_username'] ? $viewingUser['check_in_out_username'] : $viewingUser['username'],
            'check_out_condition_id' => $resource['condition_id_th'],
            'check_out_fee_amount' => 5,
            'check_out_deposit_amount' => 5
        );
    }

    public function prepareCheckOut(array $checkOut, array $resource, array $category)
    {
        if (!$checkOut['check_in_user_id']) {
            $checkOut['checkedIn'] = false;
            
            $todayDate = XenForo_Locale::date(XenForo_Application::$time, 'picker');
            
            $visitor = XenForo_Visitor::getInstance();
            
            $checkOut['check_in_deposit_refund_amount'] = $checkOut['check_out_deposit_amount'];
            $checkOut['check_in_deposit_refund_method_id'] = $checkOut['check_out_deposit_payment_method_id'];
            $checkOut['check_in_condition_id'] = $resource['condition_id_th'];
            $checkOut['check_in_location_id'] = $checkOut['check_out_location_id'];
            $checkOut['check_in_date'] = $todayDate;
            $checkOut['check_in_username'] = $visitor['check_in_out_username'] ? $visitor['check_in_out_username'] : $visitor['username'];
            $checkOut['check_in_from_username'] = $checkOut['check_out_to_username'];
        } else {
            $checkOut['checkedIn'] = true;
        }
        
        return $checkOut;
    }

    public function prepareCheckOuts(array $checkOuts, $resource, $category)
    {
        foreach ($checkOuts as &$checkOut) {
            $checkOut = $this->prepareCheckOut($checkOut, $resource, $category);
        }
        
        return $checkOuts;
    }

    public function sendAlertsForCheckOut(array $checkOut)
    {
        $db = $this->_getDb();
        
        $updated = $db->update('xf_resource_check_out_th',
            array(
                'last_alert_date' => date('Y-m-d', XenForo_Application::$time)
            ), 'resource_check_out_id = ' . $db->quote($checkOut['resource_check_out_id']));
        
        if ($updated) {
            // if (XenForo_Model_Alert::userReceivesAlert($checkOut,
            // 'resource_check_out', 'reminder')) {
            XenForo_Model_Alert::alert($checkOut['check_out_to_user_id'], $checkOut['check_out_to_user_id'], 
                $checkOut['check_out_to_username'], 'resource_check_out', $checkOut['resource_check_out_id'], 'reminder');
            // }
        }
    }

    /**
     * Return a list of users that the specified resource has been checked out
     * by, where the user the resource has been checked out by matches the given
     * username search string
     *
     * @param integer $resourceId
     * @param string $searchString
     *
     * @return array
     */
    public function findCheckedOutByUsersForResource($resourceId, $searchString)
    {
        $userIds = $this->_getDb()->fetchCol(
            '
			SELECT DISTINCT resource_check_out.check_out_user_id
			FROM xf_resource_check_out_th AS resource_check_out
            WHERE resource_check_out.resource_id = ?
		', $resourceId);
        
        return $this->_getUsersMatchingCriteria($userIds, $searchString);
    }

    /**
     * Return a list of users that the specified resource has been checked out
     * to, where the user the resource has been checked out to matches the given
     * username search string
     *
     * @param integer $resourceId
     * @param string $searchString
     *
     * @return array
     */
    public function findCheckedOutToUsersForResource($resourceId, $searchString)
    {
        $userIds = $this->_getDb()->fetchCol(
            '
			SELECT DISTINCT resource_check_out.check_out_to_user_id
			FROM xf_resource_check_out_th AS resource_check_out
            WHERE resource_check_out.resource_id = ?
		', $resourceId);
        
        return $this->_getUsersMatchingCriteria($userIds, $searchString);
    }

    /**
     * Return a list of users that the specified resource has been checked in
     * by, where the user the resource has been checked in by matches the given
     * username search string
     *
     * @param integer $resourceId
     * @param string $searchString
     *
     * @return array
     */
    public function findCheckedInByUsersForResource($resourceId, $searchString)
    {
        $userIds = $this->_getDb()->fetchCol(
            '
			SELECT DISTINCT resource_check_out.check_in_user_id
			FROM xf_resource_check_out_th AS resource_check_out
            WHERE resource_check_out.resource_id = ?
		', $resourceId);
        
        return $this->_getUsersMatchingCriteria($userIds, $searchString);
    }

    /**
     * Return a list of users that the specified resource has been checked in
     * from, where the user the resource has been checked in from matches the
     * given username search string
     *
     * @param integer $resourceId
     * @param string $searchString
     *
     * @return array
     */
    public function findCheckedInFromUsersForResource($resourceId, $searchString)
    {
        $userIds = $this->_getDb()->fetchCol(
            '
			SELECT DISTINCT resource_check_out.check_in_from_user_id
			FROM xf_resource_check_out_th AS resource_check_out
            WHERE resource_check_out.resource_id = ?
		', $resourceId);
        
        return $this->_getUsersMatchingCriteria($userIds, $searchString);
    }

    /**
     * Fetches a list of users matching the user ID and user name search
     * criteria.
     * Used in conjunction with this class's findChecked[x]UsersForResource()
     * methods.
     *
     * @param array $userIds
     * @param string $searchString
     *
     * @return array
     */
    protected function _getUsersMatchingCriteria(array $userIds, $searchString)
    {
        if ($userIds) {
            $userModel = $this->getModelFromCache('XenForo_Model_User');
            
            return $userModel->getUsers(
                array(
                    'user_id' => $userIds,
                    'username' => array(
                        $searchString,
                        'r'
                    )
                ));
        }
        
        return array();
    }

    /**
     * Determines if a user can check out a given resource.
     * Does not check viewing perms.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canCheckOutResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, 
        array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        
        if (empty($resource['check_in_user_id']) && !empty($resource['check_out_user_id'])) {
            return false;
        }
        
        return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($categoryPermissions, 'checkOut'));
    }

    /**
     * Determines if a user can check in a given resource.
     * Does not check viewing perms.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canCheckInResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, 
        array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        
        if (!empty($resource['check_in_user_id']) || empty($resource['check_out_user_id'])) {
            return false;
        }
        
        return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($categoryPermissions, 'checkIn'));
    }

    /**
     * Determines if a user can view a check out for a given resource.
     * Does not check viewing perms.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canViewCheckOut(array $checkOut, array $resource, array $category, &$errorPhraseKey = '', 
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        
        return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($categoryPermissions, 
            'viewCheckOut'));
    }

    public function getCheckOutViewableCategories(array $fetchOptions = array(), array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        
        if (empty($fetchOptions['permissionCombinationId'])) {
            $fetchOptions['permissionCombinationId'] = $viewingUser['permission_combination_id'];
        }
        
        $categories = $this->_getCategoryModel()->getAllCategories($fetchOptions);
        if (!$categories) {
            return array();
        }
        
        if (!empty($fetchOptions['permissionCombinationId'])) {
            $this->bulkSetCategoryPermCache($fetchOptions['permissionCombinationId'], $categories, 
                'category_permission_cache');
        }
        
        foreach ($categories as $key => $category) {
            if (!$this->canViewCheckOut(array(), array(), $category, $null, $viewingUser)) {
                unset($categories[$key]);
            }
        }
        
        return $categories;
    }

    /**
     * Standardizes the viewing user reference for the specific resource
     * category.
     *
     * @param integer|array $categoryId
     * @param array|null $viewingUser Viewing user; if null, use visitor
     * @param array|null $categoryPermissions Permissions for this category; if
     * null, use visitor's
     */
    public function standardizeViewingUserReferenceForCategory($categoryId, array &$viewingUser = null, 
        array &$categoryPermissions = null)
    {
        $this->_getCategoryModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, 
            $categoryPermissions);
    }

    /**
     *
     * @return XenResource_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('XenResource_Model_Category');
    }
}