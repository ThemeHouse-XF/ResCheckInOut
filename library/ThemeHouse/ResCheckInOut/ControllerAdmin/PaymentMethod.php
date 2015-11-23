<?php

class ThemeHouse_ResCheckInOut_ControllerAdmin_PaymentMethod extends XenForo_ControllerAdmin_Abstract
{

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionIndex()
    {
        $paymentMethodModel = $this->_getPaymentMethodModel();

        $viewParams = array(
        	'paymentMethods' => $this->_getPaymentMethodModel()->getPaymentMethods()
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_PaymentMethod_List',
        	'th_payment_method_list_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    protected function _getPaymentMethodAddEditResponse(array $paymentMethod)
    {
        $paymentMethodModel = $this->_getPaymentMethodModel();

        $paymentMethod = $paymentMethodModel->preparePaymentMethod($paymentMethod);

        $refundMethods = $this->_getPaymentMethodModel()->getPaymentMethods();
        if (!empty($paymentMethod['payment_method_id'])) {
            unset($refundMethods[$paymentMethod['payment_method_id']]);
        }

        $viewParams = array(
            'refundMethods' => $refundMethods,
            'paymentMethod' => $paymentMethod
        );

        return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_PaymentMethod_Edit',
        	'th_payment_method_edit_rescheckinout', $viewParams);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionAdd()
    {
        $paymentMethod = array(
            'refund_method_ids' => ''
        );

        return $this->_getPaymentMethodAddEditResponse($paymentMethod);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEdit()
    {
        $paymentMethodId = $this->_input->filterSingle('payment_method_id', XenForo_Input::UINT);
        $paymentMethod = $this->_getPaymentMethodOrError($paymentMethodId);

        return $this->_getPaymentMethodAddEditResponse($paymentMethod);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionSave()
    {
        $this->_assertPostOnly();

        $paymentMethodId = $this->_input->filterSingle('payment_method_id', XenForo_Input::UINT);
        $dwData = $this->_input->filter(
        	array(
        		'title' => XenForo_Input::STRING,
        		'refund_method_ids' => XenForo_Input::ARRAY_SIMPLE,
        	));
        $dwData['refund_method_ids'] = array_filter($dwData['refund_method_ids']);
        $dwData['refund_method_ids'] = implode(',', $dwData['refund_method_ids']);

        $dw = XenForo_DataWriter::create('ThemeHouse_ResCheckInOut_DataWriter_PaymentMethod');
        if ($paymentMethodId) {
        	$dw->setExistingData($paymentMethodId);
        }
        $dw->bulkSet($dwData);
        $dw->save();

        $paymentMethodId = $dw->get('payment_method_id');

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
        	XenForo_Link::buildAdminLink('check-in-out-pay-methods') . $this->getLastHash($paymentMethodId));
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionDelete()
    {
        if ($this->isConfirmedPost()) {
        	return $this->_deleteData('ThemeHouse_ResCheckInOut_DataWriter_PaymentMethod', 'payment_method_id',
        		XenForo_Link::buildAdminLink('check-in-out-pay-methods'));
        } else {
        	$paymentMethodId = $this->_input->filterSingle('payment_method_id', XenForo_Input::UINT);
        	$paymentMethod = $this->_getPaymentMethodOrError($paymentMethodId);

        	$viewParams = array(
        		'paymentMethod' => $paymentMethod
        	);

        	return $this->responseView('ThemeHouse_ResCheckInOut_ViewAdmin_PaymentMethod_Delete',
        		'th_payment_method_delete_rescheckinout', $viewParams);
        }
    }

    /**
     *
     * @return array
     */
    protected function _getPaymentMethodOrError($paymentMethodId)
    {
        return $this->getRecordOrError($paymentMethodId, $this->_getPaymentMethodModel(), 'getPaymentMethodById',
        	'th_payment_method_not_found_rescheckinout');
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_PaymentMethod
     */
    protected function _getPaymentMethodModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_PaymentMethod');
    }
}