<?php

class ThemeHouse_ResCheckInOut_CronEntry_Reminders
{

    public static function sendReminderAlerts()
    {
        XenForo_Application::defer('ThemeHouse_ResCheckInOut_Deferred_Reminders', array(), 'ResCheckInOutReminders');
    }
}