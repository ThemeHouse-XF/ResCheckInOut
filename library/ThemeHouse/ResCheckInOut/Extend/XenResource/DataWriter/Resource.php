<?php

/**
 *
 * @see XenResource_DataWriter_Resource
 */
class ThemeHouse_ResCheckInOut_Extend_XenResource_DataWriter_Resource extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenResource_DataWriter_Resource
{

    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_resource']['last_check_out_id_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );

        $fields['xf_resource']['check_out_count_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );

        $fields['xf_resource']['condition_id_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );

        return $fields;
    }

    protected function _preSave()
    {
        if (!empty($GLOBALS['XenResource_ControllerPublic_Resource'])) {
            /* @var $controller XenResource_ControllerPublic_Resource */
            $controller = $GLOBALS['XenResource_ControllerPublic_Resource'];

            $input = $controller->getInput()->filter(array(
                'resource_condition_id' => XenForo_Input::UINT,
                'resource_condition_id_shown' => XenForo_Input::UINT
            ));

            if ($input['resource_condition_id_shown']) {
                $this->set('condition_id_th', $input['resource_condition_id']);
            }
        }

        parent::_preSave();
    }

    public function updateCheckOutCount($adjust = null)
    {
        if ($adjust === null) {
            $this->set('check_out_count',
                $this->_db->fetchOne(
                    '
        				SELECT COUNT(*)
        				FROM xf_resource_check_out_th
        				WHERE resource_id = ?
        			', $this->get('resource_id')));
        } else {
            $this->set('check_out_count_th', $this->get('check_out_count_th') + $adjust);
        }
    }

    public function updateLastCheckOut($lastCheckOutId = null)
    {
        if ($lastCheckOutId === null) {
            // do a recalculation from the DB
            $lastCheckOutId = intval(
                $this->_db->fetchOne(
                    $this->_db->limit(
                        '
        					SELECT resource_check_out_id
        					FROM xf_resource_check_out_th
        					WHERE resource_id = ?
        					ORDER BY check_out_date DESC, resource_check_out_id DESC
        				', 1), $this->get('resource_id')));

            $this->set('last_check_out_id_th', $lastCheckOutId);
        }

        $this->set('last_check_out_id_th', $lastCheckOutId);
    }

    public function updateResourceCondition($resourceConditionId = null)
    {
        if ($resourceConditionId === null) {
            // do a recalculation from the DB
            $resourceConditionId = intval(
                $this->_db->fetchOne(
                    $this->_db->limit(
                        '
        					SELECT IF (check_in_condition_id, check_in_condition_id, check_out_condition_id) AS resource_condition_id
        					FROM xf_resource_check_out_th
        					WHERE resource_id = ?
        					ORDER BY check_out_date DESC, resource_check_out_id DESC
        				', 1), $this->get('resource_id')));

            $this->set('condition_id_th', $resourceConditionId);
        }

        $this->set('condition_id_th', $resourceConditionId);
    }
}