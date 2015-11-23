<?php

class ThemeHouse_ResCheckInOut_DataWriter_PaymentMethod extends XenForo_DataWriter
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
            'xf_check_in_out_payment_method_th' => array(
                'payment_method_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'title' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'refund_method_ids' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
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
        if (!$paymentMethodId = $this->_getExistingPrimaryKey($data, 'payment_method_id')) {
            return false;
        }

        $paymentMethod = $this->_getPaymentMethodModel()->getPaymentMethodById($paymentMethodId);
        if (!$paymentMethod) {
            return false;
        }

        return $this->getTablesDataFromArray($paymentMethod);
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'payment_method_id = ' . $this->_db->quote($this->getExisting('payment_method_id'));
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