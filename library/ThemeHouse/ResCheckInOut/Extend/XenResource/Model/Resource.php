<?php

/**
 *
 * @see XenResource_Model_Resource
 */
class ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource
{

    const FETCH_CHECK_OUT = 0x01;

    public function prepareResourceConditions(array $conditions, array &$fetchOptions)
    {
        $db = $this->_getDb();
        $sqlConditions[] = parent::prepareResourceConditions($conditions, $fetchOptions);

        if (!empty($conditions['check_out_user_id'])) {
            $fetchOptions['th_resCheckInOut_join'] = self::FETCH_CHECK_OUT;

            if (is_array($conditions['check_out_user_id'])) {
                $sqlConditions[] = '(check_out.check_out_user_id IN (' . $db->quote($conditions['check_out_user_id']) .
                     ') OR check_out.check_out_to_user_id IN (' . $db->quote($conditions['check_out_user_id']) .
                     ')) AND check_out.check_in_date = 0';
            } else {
                $sqlConditions[] = '(check_out.check_out_user_id = ' . $db->quote($conditions['check_out_user_id']) .
                     ' OR check_out.check_out_to_user_id = ' . $db->quote($conditions['check_out_user_id']) .
                     ') AND check_out.check_in_date = 0';
            }
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareResourceFetchOptions(array $fetchOptions)
    {
        $resourceFetchOptions = parent::prepareResourceFetchOptions($fetchOptions);

        $selectFields = $resourceFetchOptions['selectFields'];
        $joinTables = $resourceFetchOptions['joinTables'];
        $db = $this->_getDb();

        if (!empty($fetchOptions['th_resCheckInOut_join'])) {
            if ($fetchOptions['th_resCheckInOut_join'] & self::FETCH_CHECK_OUT) {
                $selectFields .= ',
                    resource_condition.title AS condition_title,
                    resource.last_check_out_id_th AS last_check_out_id,
                    check_out.check_out_date,
                    check_out.check_out_location_id,
                    check_out.check_out_condition_id,
                    check_out.check_out_fee_amount,
                    check_out.check_out_fee_payment_method_id,
                    check_out.check_out_deposit_amount,
                    check_out.check_out_deposit_payment_method_id,
                    check_out.check_out_ip_id,
                    check_out.check_out_user_id,
                    IF (check_out_user.username, check_out_user.username, check_out.check_out_username) AS check_out_username,
                    check_out.check_out_to_user_id,
                    IF (check_out_to_user.username, check_out_to_user.username, check_out.check_out_to_username) AS check_out_to_username,
                    check_out.check_in_date,
                    check_out.check_in_location_id,
                    check_out.check_in_condition_id,
                    check_out.check_in_deposit_refund_amount,
                    check_out.check_in_deposit_refund_method_id,
                    check_out.check_in_ip_id,
                    check_out.check_in_user_id,
                    IF (check_in_user.username, check_in_user.username, check_out.check_in_username) AS check_in_username,
                    check_out.check_in_from_user_id,
                    IF (check_in_from_user.username, check_in_from_user.username, check_out.check_in_from_username) AS check_in_from_username';
                $joinTables .= '
					LEFT JOIN xf_resource_condition_th AS resource_condition ON
						(resource.condition_id_th = resource_condition.resource_condition_id)
                    LEFT JOIN xf_resource_check_out_th AS check_out ON
						(resource.last_check_out_id_th = check_out.resource_check_out_id)
                    LEFT JOIN xf_user AS check_out_user ON
                        (check_out.check_out_user_id = check_out_user.user_id)
                    LEFT JOIN xf_user AS check_out_to_user ON
                        (check_out.check_out_to_user_id = check_out_to_user.user_id)
                    LEFT JOIN xf_user AS check_in_user ON
                        (check_out.check_in_user_id = check_in_user.user_id)
                    LEFT JOIN xf_user AS check_in_from_user ON
                        (check_out.check_in_from_user_id = check_in_from_user.user_id)
                    ';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function prepareResource(array $resource, array $category = null, array $viewingUser = null)
    {
        $resource = parent::prepareResource($resource, $category, $viewingUser);

        if ($category) {
            $resource['showCheckOut'] = $this->_getCheckOutModel()->canViewCheckOut($resource, $resource, $category);
            $resource['canCheckOut'] = $this->_getCheckOutModel()->canCheckOutResource($resource, $category);
            $resource['canCheckIn'] = $this->_getCheckOutModel()->canCheckInResource($resource, $category);
        } else {
            $resource['showCheckOut'] = false;
            $resource['canCheckOut'] = false;
            $resource['canCheckIn'] = false;
        }

        return $resource;
    }

    /**
     * Determines if a user can view check outs for a given resource.
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
    public function canViewCheckInsAndOuts(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null,
        array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($categoryPermissions,
            'viewCheckOut'));
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_CheckOut
     */
    protected function _getCheckOutModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_CheckOut');
    }
}