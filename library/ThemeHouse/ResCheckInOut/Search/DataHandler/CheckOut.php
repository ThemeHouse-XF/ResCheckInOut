<?php

/**
 * Handles searching of check outs.
 */
class ThemeHouse_ResCheckInOut_Search_DataHandler_CheckOut extends XenForo_Search_DataHandler_Abstract
{

    /**
     *
     * @var ThemeHouse_ResCheckInOut_Model_CheckOut
     */
    protected $_checkOutModel = null;

    /**
     *
     * @var XenForo_Model_Template
     */
    protected $_templateModel = null;

    /**
     * Inserts into (or replaces a record) in the index.
     *
     * @see XenForo_Search_DataHandler_Abstract::_insertIntoIndex()
     */
    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        $metadata = array();
        
        $indexer->insertIntoIndex('resource_check_out', $data['resource_check_out_id'], $data['resource']['title'], '', 
            strtotime($data['check_out_date']), $data['check_out_user_id'], $data['resource_id']);
    }

    /**
     * Updates a record in the index.
     *
     * @see XenForo_Search_DataHandler_Abstract::_updateIndex()
     */
    protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
    {
        $indexer->updateIndex('resource_check_out', $data['resource_check_out_id'], $fieldUpdates);
    }

    /**
     * Deletes one or more records from the index.
     *
     * @see XenForo_Search_DataHandler_Abstract::_deleteFromIndex()
     */
    protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
    {
        $checkOutIds = array();
        foreach ($dataList as $data) {
            $checkOutIds[] = $data['resource_check_out_id'];
        }
        
        $indexer->deleteFromIndex('resource_check_out', $checkOutIds);
    }

    /**
     * Rebuilds the index for a batch.
     *
     * @see XenForo_Search_DataHandler_Abstract::rebuildIndex()
     */
    public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
    {
        $checkOutModel = $this->_getCheckOutModel();
        
        $checkOutIds = $checkOutModel->getCheckOutIdsInRange($lastId, $batchSize);
        if (!$checkOutIds) {
            return false;
        }
        
        $this->quickIndex($indexer, $checkOutIds);
        
        return max($checkOutIds);
    }

    /**
     * Rebuilds the index for the specified content.
     *
     * @see XenForo_Search_DataHandler_Abstract::quickIndex()
     */
    public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
    {
        $checkOutModel = $this->_getCheckOutModel();
        
        $checkOuts = $checkOutModel->getCheckOutsByIds($contentIds);
        
        $resourceIds = XenForo_Application::arrayColumn($checkOuts, 'resource_id');
        $resourceIds = array_unique($resourceIds);
        
        /* @var $resourceModel XenResource_Model_Resource */
        $resourceModel = XenForo_Model::create('XenResource_Model_Resource');
        
        $resources = $resourceModel->getResourcesByIds($resourceIds);
        
        foreach ($checkOuts as &$checkOut) {
            if (empty($resources[$checkOut['resource_id']])) {
                continue;
            }
            
            $checkOut['resource'] = $resources[$checkOut['resource_id']];
            
            $this->insertIntoIndex($indexer, $checkOut);
        }
        
        return true;
    }

    /**
     * Gets the type-specific data for a collection of results of this content
     * type.
     *
     * @see XenForo_Search_DataHandler_Abstract::getDataForResults()
     */
    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $checkOutModel = $this->_getCheckOutModel();
        
        $checkOuts = $checkOutModel->getCheckOutsByIds($ids, 
            array(
                'permissionCombinationId' => $viewingUser['permission_combination_id'],
                'join' => ThemeHouse_ResCheckInOut_Model_CheckOut::FETCH_CHECK_OUT_TO_USER
            ));
        
        $resourceIds = XenForo_Application::arrayColumn($checkOuts, 'resource_id');
        $resourceIds = array_unique($resourceIds);
        
        /* @var $resourceModel XenResource_Model_Resource */
        $resourceModel = XenForo_Model::create('XenResource_Model_Resource');
        
        $resources = $resourceModel->getResourcesByIds($resourceIds, array(
            'join' => XenResource_Model_Resource::FETCH_CATEGORY
        ));
        
        foreach ($checkOuts as &$checkOut) {
            if (empty($resources[$checkOut['resource_id']])) {
                continue;
            }
            
            $checkOut['resource'] = $resources[$checkOut['resource_id']];
        }
        
        return $checkOuts;
    }

    /**
     * Determines if this result is viewable.
     *
     * @see XenForo_Search_DataHandler_Abstract::canViewResult()
     */
    public function canViewResult(array $result, array $viewingUser)
    {
        return true;
        $checkOutModel = $this->_getCheckOutModel();
        
        return $checkOutModel->canViewCheckOut($result, $result['resource'], $result['resource']);
    }

    /**
     * Prepares a result for display.
     *
     * @see XenForo_Search_DataHandler_Abstract::prepareResult()
     */
    public function prepareResult(array $result, array $viewingUser)
    {
        $checkOutModel = $this->_getCheckOutModel();
        
        return $result;
    }

    /**
     * Gets the date of the result (from the result's content).
     *
     * @see XenForo_Search_DataHandler_Abstract::getResultDate()
     */
    public function getResultDate(array $result)
    {
        return strtotime($result['check_out_date']);
    }

    /**
     * Renders a result to HTML.
     *
     * @see XenForo_Search_DataHandler_Abstract::renderResult()
     */
    public function renderResult(XenForo_View $view, array $result, array $search)
    {
        $checkOutUser = array(
            'user_id' => $result['check_out_user_id'],
            'username' => $result['check_out_username']
        );
        $checkOutToUser = array(
            'user_id' => $result['check_out_to_user_id'],
            'username' => $result['check_out_to_username'],
            'gender' => $result['check_out_to_user_gender'],
            'avatar_date' => $result['check_out_to_user_avatar_date'],
            'avatar_width' => $result['check_out_to_user_avatar_width'],
            'avatar_height' => $result['check_out_to_user_avatar_height'],
            'gravatar' => $result['check_out_to_user_gravatar'],
        );
        $checkInUser = array(
            'user_id' => $result['check_in_user_id'],
            'username' => $result['check_in_username']
        );
        $checkInFromUser = array(
            'user_id' => $result['check_in_from_user_id'],
            'username' => $result['check_in_from_username']
        );
        
        return $view->createTemplateObject('search_result_resource_check_out', 
            array(
                'checkOut' => $result,
                'resource' => $result['resource'],
                'checkOutUser' => $checkOutUser,
                'checkOutToUser' => $checkOutToUser,
                'checkInUser' => $checkInUser,
                'checkInFromUser' => $checkInFromUser,
                'search' => $search
            ));
    }

    /**
     * Returns an array of content types handled by this class
     *
     * @see XenForo_Search_DataHandler_Abstract::getSearchContentTypes()
     */
    public function getSearchContentTypes()
    {
        return array(
            'resource_check_out'
        );
    }

    /**
     * Get type-specific constrints from input.
     *
     * @param XenForo_Input $input
     *
     * @return array
     */
    public function getTypeConstraintsFromInput(XenForo_Input $input)
    {
        $constraints = array();
        
        return $constraints;
    }

    /**
     * Process a type-specific constraint.
     *
     * @see XenForo_Search_DataHandler_Abstract::processConstraint()
     */
    public function processConstraint(XenForo_Search_SourceHandler_Abstract $sourceHandler, $constraint, $constraintInfo, 
        array $constraints)
    {
        return false;
    }

    /**
     * Gets the search form controller response for this type.
     *
     * @see XenForo_Search_DataHandler_Abstract::getSearchFormControllerResponse()
     */
    public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, 
        array $viewParams)
    {
        return $controller->responseView('ThemeHouse_CheckOutSearch_ViewPublic_Search_Form_CheckOut',
            'search_form_resource_check_out', $viewParams);
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