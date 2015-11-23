<?php

/**
 *
 * @see XenForo_Model_User
 */
class ThemeHouse_ResCheckInOut_Extend_XenForo_Model_User extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenForo_Model_User
{

    public function getVisitingUserById($userId)
    {
        $visitor = parent::getVisitingUserById($userId);

        if (!empty($visitor['check_in_out_user_id_th'])) {
            $checkInOutUserId = $visitor['check_in_out_user_id_th'];

            if ($checkInOutUserId != $visitor['user_id']) {
                $user = $this->getUserById($checkInOutUserId);
                if ($user) {
                    $visitor['check_in_out_username'] = $user['username'];
                }
            }
        }

        if (empty($visitor['check_in_out_username'])) {
            $visitor['check_in_out_user_id_th'] = $visitor['user_id'];
            $visitor['check_in_out_username'] = $visitor['username'];
        }

        return $visitor;
    }

    public function canChangeCheckInOutUser(&$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'checkInOutAsUser')) {
            return true;
        }

        return false;
    }

    public function setCheckInOutUser($userId, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if ($userId == $viewingUser['user_id']) {
            $userId = 0;
        }

        $this->_getDb()->update('xf_user_profile',
            array(
                'check_in_out_user_id_th' => $userId
            ), 'user_id = ' . $this->_getDb()
                ->quote($viewingUser['user_id']));
    }
}