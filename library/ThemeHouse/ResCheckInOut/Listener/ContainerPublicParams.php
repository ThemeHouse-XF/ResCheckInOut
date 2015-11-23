<?php

class ThemeHouse_ResCheckInOut_Listener_ContainerPublicParams extends ThemeHouse_Listener_ContainerParams
{

    protected function _run()
    {
        $userModel = XenForo_Model::create('XenForo_Model_User');

        if ($userModel->canChangeCheckInOutUser()) {
            $this->_params['canChangeCheckInOutUser'] = true;
        }
    }

    public static function containerPublicParams(array &$params, XenForo_Dependencies_Abstract $dependencies)
    {
        $params = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_ContainerPublicParams', $params, $dependencies);
    }
}