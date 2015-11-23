<?php

class ThemeHouse_ResCheckInOut_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'https://xenforo.com/community/resources/resource-check-in-and-out.4021/';

    protected function _getPrerequisites()
    {
        return array(
            'XenResource' => '1010000'
        );
    }

    protected function _getTables()
    {
        return array(
            'xf_resource_check_out_th' => array(
                'resource_check_out_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'resource_id' => 'int NOT NULL',
                'check_out_date' => 'date NOT NULL',
                'check_out_due_date' => 'date NOT NULL',
                'check_out_location_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_out_location_title' => 'varchar(255) NOT NULL',
                'check_out_condition_id' => 'int UNSIGNED NOT NULL',
                'check_out_fee_amount' => 'decimal(10,2) UNSIGNED NOT NULL',
                'check_out_fee_payment_method_id' => 'int UNSIGNED NOT NULL',
                'check_out_deposit_amount' => 'decimal(10,2) UNSIGNED NOT NULL',
                'check_out_deposit_payment_method_id' => 'int UNSIGNED NOT NULL',
                'check_out_ip_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_out_user_id' => 'int UNSIGNED NOT NULL',
                'check_out_username' => 'varchar(50) NOT NULL DEFAULT \'\'',
                'check_out_to_user_id' => 'int UNSIGNED NOT NULL',
                'check_out_to_username' => 'varchar(50) NOT NULL DEFAULT \'\'',
                'check_in_date' => 'date NOT NULL DEFAULT 0',
                'check_in_location_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_location_title' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'check_in_condition_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_deposit_refund_amount' => 'decimal(10,2) UNSIGNED NOT NULL DEFAULT 0',
                'check_in_deposit_refund_method_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_ip_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_user_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_username' => 'varchar(50) NOT NULL DEFAULT \'\'',
                'check_in_from_user_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_in_from_username' => 'varchar(50) NOT NULL DEFAULT \'\'',
                'last_alert_date' => 'date NOT NULL DEFAULT 0'
            ),
            'xf_check_in_out_location_th' => array(
                'location_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'title' => 'varchar(255) NOT NULL'
            ),
            'xf_resource_condition_th' => array(
                'resource_condition_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'title' => 'varchar(255) NOT NULL'
            ),
            'xf_check_in_out_payment_method_th' => array(
                'payment_method_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'title' => 'varchar(255) NOT NULL',
                'refund_method_ids' => 'varchar(255) NOT NULL DEFAULT \'\''
            )
        );
    }

    protected function _getTableChanges()
    {
        return array(
            'xf_resource' => array(
                'last_check_out_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'check_out_count_th' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'condition_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0'
            ),
            'xf_user_profile' => array(
                'check_in_out_user_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0'
            )
        );
    }

    protected function _getContentTypes()
    {
        return array(
            'resource_check_out' => array(
                'addon_id' => 'ThemeHouse_ResCheckInOut',
                'fields' => array(
                    'alert_handler_class' => 'ThemeHouse_ResCheckInOut_AlertHandler_CheckOut',
                    'search_handler_class' => 'ThemeHouse_ResCheckInOut_Search_DataHandler_CheckOut'
                )
            )
        );
    }


    protected function _postInstall()
    {
        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('YoYo_');
        if ($addOn) {
            $db->query("
                INSERT INTO xf_resource_check_out_th (resource_check_out_id, resource_id, check_out_date, check_out_due_date, check_out_location_id, check_out_location_title, check_out_condition_id, check_out_fee_amount, check_out_fee_payment_method_id, check_out_deposit_amount, check_out_deposit_payment_method_id, check_out_ip_id, check_out_user_id, check_out_username, check_out_to_user_id, check_out_to_username, check_in_date, check_in_location_id, check_in_location_title, check_in_condition_id, check_in_deposit_refund_amount, check_in_deposit_refund_method_id, check_in_ip_id, check_in_user_id, check_in_username, check_in_from_user_id, check_in_from_username, last_alert_date)
                    SELECT resource_check_out_id, resource_id, check_out_date, check_out_due_date, check_out_location_id, check_out_location_title, check_out_condition_id, check_out_fee_amount, check_out_fee_payment_method_id, check_out_deposit_amount, check_out_deposit_payment_method_id, check_out_ip_id, check_out_user_id, check_out_username, check_out_to_user_id, check_out_to_username, check_in_date, check_in_location_id, check_in_location_title, check_in_condition_id, check_in_deposit_refund_amount, check_in_deposit_refund_method_id, check_in_ip_id, check_in_user_id, check_in_username, check_in_from_user_id, check_in_from_username, last_alert_date
                        FROM xf_resource_check_out_waindigo"); 
            $db->query("
                INSERT INTO xf_check_in_out_location_th (location_id, title)
                    SELECT location_id, title
                        FROM xf_check_in_out_location_waindigo"); 
            $db->query("
                INSERT INTO xf_resource_condition_th (resource_condition_id, title)
                    SELECT resource_condition_id, title
                        FROM xf_resource_condition_waindigo"); 
            $db->query("
                INSERT INTO xf_check_in_out_payment_method_th (payment_method_id, title, refund_method_ids)
                    SELECT payment_method_id, title, refund_method_ids
                        FROM xf_check_in_out_payment_mewaindigood_waindigo"); 
            $db->query("
                UPDATE xf_resource
                    SET last_check_out_id_th=last_check_out_id_waindigo, check_out_count_th=check_out_count_waindigo, condition_id_th=condition_id_waindigo");
            $db->query("
                UPDATE xf_user_profile
                    SET check_in_out_user_id_th=check_in_out_user_id_waindigo");
        }
    }
}