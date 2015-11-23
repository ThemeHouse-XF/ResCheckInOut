<?php

class ThemeHouse_ResCheckInOut_Deferred_Reminders extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 100
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        $xenOptions = XenForo_Application::get('options');

        $reminderDays = explode(',', $xenOptions->th_resCheckInOut_reminderAlertsUser);
        $reminderDays = array_unique(array_map('trim', $reminderDays));

        if (!$reminderDays) {
            return true;
        }

        $dueDates = array();
        foreach ($reminderDays as $reminderDay) {
            $dueDates[] = date('Y-m-d', XenForo_Application::$time - $reminderDay * 24 * 60 * 60);
        }

        /* @var $checkOutModel ThemeHouse_ResCheckInOut_Model_CheckOut */
        $checkOutModel = XenForo_Model::create('ThemeHouse_ResCheckInOut_Model_CheckOut');

        $checkOutIds = $checkOutModel->getCheckOutIdsInRange($data['position'], $data['batch']);

        if (sizeof($checkOutIds) == 0) {
            return true;
        }

        $checkOuts = $checkOutModel->getCheckOuts(
            array(
                'resource_check_out_id' => $checkOutIds,
                'check_out_due_date' => $dueDates,
                'checkedIn' => false,
                'last_alert_date' => array('<', date('Y-m-d', XenForo_Application::$time))
            ),
            array(
                'join' => ThemeHouse_ResCheckInOut_Model_CheckOut::FETCH_CHECK_OUT_TO_USER
            ));

        if (!$checkOuts) {
            $data['position'] = end($checkOutIds);
        }

        $s = microtime(true);
        foreach ($checkOuts as $checkOutId => $checkOut) {
            if ($targetRunTime && (microtime(true) - $s) >= $targetRunTime) {
                break;
            }

            $data['position'] = $checkOutId;

            $checkOutModel->sendAlertsForCheckOut($checkOut);
        }

        return $data;
    }
}