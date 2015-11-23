<?php

class ThemeHouse_ResCheckInOut_DataWriter_CheckOut extends XenForo_DataWriter
{
    /**
     * Option that controls whether the data in this discussion should be indexed for
     * search. If this value is set inconsistently for the same discussion (and messages within),
     * data might be orphaned in the search index. Defaults to true.
     *
     * @var string
     */
    const OPTION_INDEX_FOR_SEARCH = 'indexForSearch';
    
    protected $_resource = null;

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_resource_check_out_th' => array(
                'resource_check_out_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'resource_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_date' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'check_out_due_date' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'check_out_location_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckOutLocationId'
                    )
                ),
                'check_out_location_title' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'check_out_condition_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_fee_amount' => array(
                    'type' => self::TYPE_FLOAT,
                    'required' => true
                ),
                'check_out_fee_payment_method_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_deposit_amount' => array(
                    'type' => self::TYPE_FLOAT,
                    'required' => true
                ),
                'check_out_deposit_payment_method_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_ip_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'check_out_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'check_out_to_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'check_out_to_username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'check_in_date' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '0000-00-00'
                ),
                'check_in_location_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckInLocationId'
                    )
                ),
                'check_in_location_title' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
                ),
                'check_in_condition_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
                ),
                'check_in_deposit_refund_amount' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyRefundAmount'
                    )
                ),
                'check_in_deposit_refund_method_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyRefundMethod'
                    )
                ),
                'check_in_ip_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'check_in_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
                ),
                'check_in_username' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
                ),
                'check_in_from_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0,
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
                ),
                'check_in_from_username' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'verification' => array(
                        '$this',
                        '_verifyCheckInData'
                    )
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
     * @return array|bool
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'resource_check_out_id')) {
            return false;
        }

        return array(
            'xf_resource_check_out_th' => $this->_getCheckOutModel()->getCheckOutById($id)
        );
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'resource_check_out_id = ' . $this->_db->quote($this->getExisting('resource_check_out_id'));
    }

    /**
     * @see XenForo_DataWriter_Page::_getDefaultOptions()
     */
    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_INDEX_FOR_SEARCH => true
        );
    }
    
    protected function _verifyCheckOutLocationId(&$value, XenForo_DataWriter $dw, $fieldName, array $fieldData)
    {
        if ($value) {
            $checkInLocation = $this->_getLocationModel()->getLocationById($value);

            if (!$checkInLocation) {
                $this->_triggerRequiredFieldError('xf_resource_check_out_th', $fieldName);
                return false;
            }

            $this->set('check_out_location_title', $checkInLocation['title']);
        }

        return true;
    }

    protected function _verifyCheckInLocationId(&$value, XenForo_DataWriter $dw, $fieldName, array $fieldData)
    {
        if ($this->get('check_in_date')) {
            if ($value) {
                $checkInLocation = $this->_getLocationModel()->getLocationById($value);

                if (!$checkInLocation) {
                    $this->_triggerRequiredFieldError('xf_resource_check_out_th', $fieldName);
                    return false;
                }

                $this->set('check_in_location_title', $checkInLocation['title']);
            }
        } else {
            $value = 0;
        }

        return true;
    }

    protected function _verifyCheckInData(&$value, XenForo_DataWriter $dw, $fieldName, array $fieldData)
    {
        if ($this->get('check_in_date')) {
            if (!$value) {
                $this->_triggerRequiredFieldError('xf_resource_check_out_th', $fieldName);
                return false;
            }
        } else {
            $value = $fieldData['default'];
        }

        return true;
    }

    protected function _verifyRefundAmount(&$value, XenForo_DataWriter $dw, $fieldName)
    {
        if ($this->get('check_in_date')) {
            if (!$value) {
                $this->_triggerRequiredFieldError('xf_resource_check_out_th', $fieldName);
                return false;
            } elseif ($value > $this->get('check_out_deposit_amount')) {
                $this->error(
                    new XenForo_Phrase('th_refund_amount_must_be_no_more_than_deposit_amount_rescheckinout'),
                    $fieldName, false);
                return false;
            }
        } else {
            $value = '0.00';
        }

        return true;
    }

    protected function _verifyRefundMethod(&$value, XenForo_DataWriter $dw, $fieldName)
    {
        if ($this->get('check_in_date')) {
            if (!$value) {
                $this->_triggerRequiredFieldError('xf_resource_check_out_th', $fieldName);
                return false;
            } else {
                /* @var $paymentMethodModel ThemeHouse_ResCheckInOut_Model_PaymentMethod */
                $paymentMethodModel = $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_PaymentMethod');

                if ($value == $this->get('check_out_deposit_payment_method_id')) {
                    return true;
                }

                $paymentMethod = $paymentMethodModel->getPaymentMethodById(
                    $this->get('check_out_deposit_payment_method_id'));
                $refundMethodIds = explode(',', $paymentMethod['refund_method_ids']);

                if (in_array($value, $refundMethodIds)) {
                    return true;
                }

                $this->error(
                    new XenForo_Phrase('th_refund_method_not_allowed_for_this_payment_method_rescheckinout'),
                    $fieldName, false);
                return false;
            }
        } else {
            $value = 0;
        }

        return true;
    }

    /**
     * Post-save handling.
     */
    protected function _postSave()
    {
        $resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
        if ($resourceDw->setExistingData($this->get('resource_id'))) {
            $resourceDw->checkUserName();
            $resourceDw->updateCheckOutCount(1);
            if ($this->isInsert()) {
                $resourceDw->updateLastCheckOut($this->get('resource_check_out_id'));
                $resourceDw->updateResourceCondition($this->get('resource_check_out_condition_id'));
            } else {
                $resourceDw->updateResourceCondition();
            }
            $resourceDw->save();
        }

        $this->_resource = $resourceDw->getMergedData();
        
        if ($this->getOption(self::OPTION_INDEX_FOR_SEARCH)) {
            $this->_insertOrUpdateSearchIndex();
        }
    }
    
    /**
     * Inserts or updates a record in the search index for this check out.
     */
    protected function _insertOrUpdateSearchIndex()
    {
        $dataHandler = new ThemeHouse_ResCheckInOut_Search_DataHandler_CheckOut();
    
        $checkOut = $this->getMergedData();
        
        if ($this->_resource) {
            $checkOut['resource'] = $this->_resource;
        }
        
        $indexer = new XenForo_Search_Indexer();
        $dataHandler->insertIntoIndex($indexer, $checkOut);
    }

    /**
     * Post-save handling, after the transaction is committed.
     */
    protected function _postSaveAfterTransaction()
    {
        if (!$this->_resource && $this->get('resource_id')) {
            $this->_resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'));
        }

        if ($this->_resource) {
            // TODO send alerts?
        }
    }

    /**
     * Post-delete handling.
     */
    protected function _postDelete()
    {
        $this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('resource_check_out',
            $this->get('resource_check_out_id'));

        if ($this->get('message_state') == 'visible') {
            $resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource',
                XenForo_DataWriter::ERROR_SILENT);
            if ($resourceDw->setExistingData($this->get('resource_id'))) {
                $resourceDw->updateCheckOutCount(-1);
                $resourceDw->save();
            }
        }
    }

    public function setResource(array $resource)
    {
        $this->_resource = $resource;
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_CheckOut
     */
    protected function _getCheckOutModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_CheckOut');
    }

    /**
     *
     * @return ThemeHouse_ResCheckInOut_Model_Location
     */
    protected function _getLocationModel()
    {
        return $this->getModelFromCache('ThemeHouse_ResCheckInOut_Model_Location');
    }

    /**
     *
     * @return XenResource_Model_Resource
     */
    protected function _getResourceModel()
    {
        return $this->getModelFromCache('XenResource_Model_Resource');
    }
}