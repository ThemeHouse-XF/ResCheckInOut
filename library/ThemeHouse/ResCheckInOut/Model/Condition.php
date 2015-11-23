<?php

class ThemeHouse_ResCheckInOut_Model_Condition extends XenForo_Model
{

    public function getConditionById($resourceConditionId)
    {
        return $this->_getDb()->fetchRow(
            '
            SELECT *
            FROM xf_resource_condition_th
            WHERE resource_condition_id = ?
        ', $resourceConditionId);
    }

    public function getConditions(array $conditions = array(), array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        
        return $this->fetchAllKeyed(
            $this->limitQueryResults('
            SELECT *
            FROM xf_resource_condition_th
        ', $limitOptions['limit'], $limitOptions['offset']),
            'resource_condition_id');
    }
}