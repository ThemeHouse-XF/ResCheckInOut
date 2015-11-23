<?php

class ThemeHouse_ResCheckInOut_AlertHandler_CheckOut extends XenForo_AlertHandler_Abstract
{

    protected $_checkOutModel;

    /**
     * Fetches the content required by alerts.
     *
     * @param array $contentIds
     * @param XenForo_Model_Alert $model Alert model invoking this
     * @param integer $userId User ID the alerts are for
     * @param array $viewingUser Information about the viewing user (keys:
     * user_id, permission_combination_id, permissions)
     *
     * @return array
     */
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        $checkOutModel = $this->_getCheckOutModel();

        $checkOuts = $checkOutModel->getCheckOutsByIds($contentIds);
        $resourceIds = array();
        foreach ($checkOuts as $checkOut) {
            $resourceIds[$checkOut['resource_id']] = $checkOut['resource_id'];
        }
        $resources = XenForo_Model::create('XenResource_Model_Resource')->getResourcesByIds($resourceIds,
            array(
                'permissionCombinationId' => $viewingUser['permission_combination_id']
            ));

        foreach ($checkOuts as $key => &$checkOut) {
            if (!isset($resources[$checkOut['resource_id']])) {
                unset($checkOuts[$key]);
            } else {
                $checkOut['resource'] = $resources[$checkOut['resource_id']];
                $checkOut['resource']['title'] = XenForo_Helper_String::censorString($checkOut['resource']['title']);
            }
        }

        return $checkOuts;
    }

    protected function _prepareReminder(array $item, array $viewingUser)
    {
        $eventDate = new DateTime(date('Y-m-d', $item['event_date']));
        $dueDate = new DateTime($item['content']['check_out_due_date']);

        $item['days'] = $eventDate->diff($dueDate)->format("%a");

        return $item;
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_CheckOut
     */
    protected function _getCheckOutModel()
    {
        if (!$this->_checkOutModel) {
            $this->_checkOutModel = XenForo_Model::create('ThemeHouse_ResCheckInOut_Model_CheckOut');
        }

        return $this->_checkOutModel;
    }
}