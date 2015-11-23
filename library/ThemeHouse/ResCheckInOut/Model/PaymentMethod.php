<?php

class ThemeHouse_ResCheckInOut_Model_PaymentMethod extends XenForo_Model
{

    public function getPaymentMethodById($paymentMethodId)
    {
        return $this->_getDb()->fetchRow(
            '
            SELECT *
            FROM xf_check_in_out_payment_method_th
            WHERE payment_method_id = ?
        ', $paymentMethodId);
    }

    public function getPaymentMethods(array $conditions = array(), array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            $this->limitQueryResults('
            SELECT *
            FROM xf_check_in_out_payment_method_th
        ', $limitOptions['limit'], $limitOptions['offset']),
            'payment_method_id');
    }

    public function getPaymentMethodsByIds(array $paymentMethodIds, array $conditions = array(), array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            $this->limitQueryResults('
            SELECT *
            FROM xf_check_in_out_payment_method_th AS refund_method
            WHERE payment_method_id IN (' . $this->_getDb()->quote($paymentMethodIds) . ')
        ', $limitOptions['limit'], $limitOptions['offset']),
            'payment_method_id');
    }

    public function preparePaymentMethod(array $paymentMethod)
    {
        $paymentMethod['refundMethodIds'] = explode(',', $paymentMethod['refund_method_ids']);

        return $paymentMethod;
    }
}