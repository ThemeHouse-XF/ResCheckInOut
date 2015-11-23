<?php

class ThemeHouse_ResCheckInOut_ControllerAdmin_Condition extends XenForo_ControllerAdmin_Abstract
{

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionIndex()
    {
        $conditionModel = $this->_getConditionModel();

        $viewParams = array(
        	'conditions' => $this->_getConditionModel()->getConditions()
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Condition_List',
        	'th_condition_list_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    protected function _getConditionAddEditResponse(array $condition)
    {
        $conditionModel = $this->_getConditionModel();

        $viewParams = array(
        	'condition' => $condition
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Condition_Edit',
        	'th_condition_edit_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionAdd()
    {
        $condition = array();

        return $this->_getConditionAddEditResponse($condition);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEdit()
    {
        $resourceConditionId = $this->_input->filterSingle('resource_condition_id', XenForo_Input::UINT);
        $condition = $this->_getConditionOrError($resourceConditionId);

        return $this->_getConditionAddEditResponse($condition);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionSave()
    {
        $this->_assertPostOnly();

        $resourceConditionId = $this->_input->filterSingle('resource_condition_id', XenForo_Input::UINT);
        $dwData = $this->_input->filter(
        	array(
        		'title' => XenForo_Input::STRING,
        	));

        $dw = XenForo_DataWriter::create('ThemeHouse_ResCheckInOut_DataWriter_Condition');
        if ($resourceConditionId) {
        	$dw->setExistingData($resourceConditionId);
        }
        $dw->bulkSet($dwData);
        $dw->save();

        $resourceConditionId = $dw->get('resource_condition_id');

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
        	XenForo_Link::buildAdminLink('resource-conditions') . $this->getLastHash($resourceConditionId));
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionDelete()
    {
        if ($this->isConfirmedPost()) {
        	return $this->_deleteData('ThemeHouse_ResCheckInOut_DataWriter_Condition', 'resource_condition_id',
        		XenForo_Link::buildAdminLink('resource-conditions'));
        } else {
        	$resourceConditionId = $this->_input->filterSingle('resource_condition_id', XenForo_Input::UINT);
        	$condition = $this->_getConditionOrError($resourceConditionId);

        	$viewParams = array(
        		'condition' => $condition
        	);

        	return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Condition_Delete',
        		'th_condition_delete_rescheckinout', $viewParams);
        }
    }

    /**
     *
     * @return array
     */
    protected function _getConditionOrError($resourceConditionId)
    {
        return $this->getRecordOrError($resourceConditionId, $this->_getConditionModel(), 'getConditionById',
        	'th_condition_not_found_rescheckinout');
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