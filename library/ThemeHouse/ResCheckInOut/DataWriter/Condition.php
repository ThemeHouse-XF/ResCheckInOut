<?php

class ThemeHouse_ResCheckInOut_DataWriter_Condition extends XenForo_DataWriter
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
            'xf_resource_condition_th' => array(
                'resource_condition_id' => array(
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
        if (!$resourceConditionId = $this->_getExistingPrimaryKey($data, 'resource_condition_id')) {
            return false;
        }

        $condition = $this->_getConditionModel()->getConditionById($resourceConditionId);
        if (!$condition) {
            return false;
        }

        return $this->getTablesDataFromArray($condition);
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'resource_condition_id = ' . $this->_db->quote($this->getExisting('resource_condition_id'));
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_Condition
     */
    protected function _getConditionModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_Condition');
    }
}