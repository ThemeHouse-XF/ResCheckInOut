<?php

class ThemeHouse_ResCheckInOut_Model_Location extends XenForo_Model
{

    public function getLocationById($locationId)
    {
        return $this->_getDb()->fetchRow(
            '
            SELECT *
            FROM xf_check_in_out_location_th
            WHERE location_id = ?
        ', $locationId);
    }

    public function getLocations(array $conditions = array(), array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        
        return $this->fetchAllKeyed(
            $this->limitQueryResults('
            SELECT *
            FROM xf_check_in_out_location_th
        ', $limitOptions['limit'], $limitOptions['offset']),
            'location_id');
    }
}