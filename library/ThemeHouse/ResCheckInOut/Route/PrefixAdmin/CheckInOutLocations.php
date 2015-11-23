<?php

/**
 * Route prefix handler for check in out locations in the admin control panel.
 */
class ThemeHouse_ResCheckInOut_Route_PrefixAdmin_CheckInOutLocations implements XenForo_Route_Interface
{

    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'location_id');
        $action = $router->resolveActionAsPageNumber($action, $request);
        return $router->getRouteMatch('ThemeHouse_ResCheckInOut_ControllerAdmin_Location', $action, 'checkInOutLocations');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'location_id', 'title');
    }
}