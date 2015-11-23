<?php

class ThemeHouse_ResCheckInOut_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_ResCheckInOut' => array(
                'controller' => array(
                    'XenResource_ControllerPublic_Resource'
                ),
                'helper' => array(
                    'XenResource_ControllerHelper_Resource'
                ),
                'model' => array(
                    'XenResource_Model_Resource',
                    'XenForo_Model_User'
                ),
                'datawriter' => array(
                    'XenResource_DataWriter_Resource'
                ),
                'route_prefix' => array(
                    'XenResource_Route_Prefix_Resources'
                ),
                'visitor' => array(
                    'XenForo_Visitor'
                ),
            ),
        );
    }

    public static function loadClassController($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'controller');
    }

    public static function loadClassHelper($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'helper');
    }

    public static function loadClassModel($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'model');
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'datawriter');
    }

    public static function loadClassRoutePrefix($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'route_prefix');
    }

    public static function loadClassVisitor($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_ResCheckInOut_Listener_LoadClass', $class, $extend, 'visitor');
    }
}