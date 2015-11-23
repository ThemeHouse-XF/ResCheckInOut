<?php

/**
 *
 * @see XenResource_ControllerHelper_Resource
 */
class ThemeHouse_ResCheckInOut_Extend_XenResource_ControllerHelper_Resource extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenResource_ControllerHelper_Resource
{

    /**
     * Checks that a check out is valid and viewable, before returning the check
     * out, resource, and containing category's info.
     *
     * @param integer|null $checkOutId
     * @param array $checkOutFetchOptions Extra data to fetch with the check out
     * @param array $resourceFetchOptions Extra data to fetch with the resource
     * @param array $categoryFetchOptions Extra data to fetch with the category
     *
     * @return array Format: [0] => check_out info, [1] => resource info, [2] =>
     * category info
     */
    public function assertCheckOutValidAndViewable($checkOutId = null, array $checkOutFetchOptions = array(),
        array $resourceFetchOptions = array(), array $categoryFetchOptions = array())
    {
        $resourceFetchOptions['th_resCheckInOut_join'] = ThemeHouse_ResCheckInOut_Extend_XenResource_Model_Resource::FETCH_CHECK_OUT;

        $checkOut = $this->getCheckOutOrError($checkOutId, $checkOutFetchOptions);
        list($resource, $category) = $this->assertResourceValidAndViewable($checkOut['resource_id'],
            $resourceFetchOptions, $categoryFetchOptions);

        /**
         *
         * @var ThemeHouse_ResCheckInOut_Model_CheckOut $checkOutModel
         */
        $checkOutModel = $this->_controller->getModelFromCache('ThemeHouse_ResCheckInOut_Model_CheckOut');

        if (!$checkOutModel->canViewCheckOut($checkOut, $resource, $category, $errorPhraseKey)) {
            throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $checkOut = $checkOutModel->prepareCheckOut($checkOut, $resource, $category);

        return array(
            $checkOut,
            $resource,
            $category
        );
    }

    /**
     * Gets the specified check out or throws an error.
     *
     * @param integer|null $checkOutId
     * @param array $fetchOptions Options that control the data fetched with the
     * check out
     *
     * @return array
     */
    public function getCheckOutOrError($checkOutId = null, array $fetchOptions = array())
    {
        if ($checkOutId === null) {
            $checkOutId = $this->_controller->getInput()->filterSingle('resource_check_out_id', XenForo_Input::UINT);
        }

        $checkOut = $this->_controller->getModelFromCache('ThemeHouse_ResCheckInOut_Model_CheckOut')->getCheckOutById(
            $checkOutId, $fetchOptions);
        if (!$checkOut) {
            throw $this->_controller->responseException(
                $this->_controller->responseError(
                    new XenForo_Phrase('th_requested_check_out_not_found_rescheckinout'), 404));
        }

        return $checkOut;
    }
}