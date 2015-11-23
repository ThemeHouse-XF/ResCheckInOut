<?php

class ThemeHouse_ResCheckInOut_DataWriter_Location extends XenForo_DataWriter
{

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_check_in_out_location_th' => array(
                'location_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'title' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                )
            )
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in.
     * See parent for explanation.
     *
     * @param mixed
     *
     * @return array|false
     */
    protected function _getExistingData($data)
    {
        if (!$locationId = $this->_getExistingPrimaryKey($data, 'location_id')) {
            return false;
        }

        $location = $this->_getLocationModel()->getLocationById($locationId);
        if (!$location) {
            return false;
        }

        return $this->getTablesDataFromArray($location);
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'location_id = ' . $this->_db->quote($this->getExisting('location_id'));
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_Location
     */
    protected function _getLocationModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_Location');
    }
}