<?php

class ThemeHouse_ResCheckInOut_ControllerAdmin_Location extends XenForo_ControllerAdmin_Abstract
{

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionIndex()
    {
        $locationModel = $this->_getLocationModel();

        $viewParams = array(
        	'locations' => $this->_getLocationModel()->getLocations()
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Location_List',
        	'th_location_list_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    protected function _getLocationAddEditResponse(array $location)
    {
        $locationModel = $this->_getLocationModel();

        $viewParams = array(
        	'location' => $location
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Location_Edit',
        	'th_location_edit_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionAdd()
    {
        $location = array();

        return $this->_getLocationAddEditResponse($location);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEdit()
    {
        $locationId = $this->_input->filterSingle('location_id', XenForo_Input::UINT);
        $location = $this->_getLocationOrError($locationId);

        return $this->_getLocationAddEditResponse($location);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionSave()
    {
        $this->_assertPostOnly();

        $locationId = $this->_input->filterSingle('location_id', XenForo_Input::UINT);
        $dwData = $this->_input->filter(
        	array(
        		'title' => XenForo_Input::STRING,
        	));

        $dw = XenForo_DataWriter::create('ThemeHouse_ResCheckInOut_DataWriter_Location');
        if ($locationId) {
        	$dw->setExistingData($locationId);
        }
        $dw->bulkSet($dwData);
        $dw->save();

        $locationId = $dw->get('location_id');

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
        	XenForo_Link::buildAdminLink('check-in-out-locations') . $this->getLastHash($locationId));
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionDelete()
    {
        if ($this->isConfirmedPost()) {
        	return $this->_deleteData('ThemeHouse_ResCheckInOut_DataWriter_Location', 'location_id',
        		XenForo_Link::buildAdminLink('check-in-out-locations'));
        } else {
        	$locationId = $this->_input->filterSingle('location_id', XenForo_Input::UINT);
        	$location = $this->_getLocationOrError($locationId);

        	$viewParams = array(
        		'location' => $location
        	);

        	return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_Location_Delete',
        		'th_location_delete_rescheckinout', $viewParams);
        }
    }

    /**
     *
     * @return array
     */
    protected function _getLocationOrError($locationId)
    {
        return $this->getRecordOrError($locationId, $this->_getLocationModel(), 'getLocationById',
        	'th_location_not_found_rescheckinout');
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