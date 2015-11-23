<?php

/**
 * Route prefix handler for payment methods in the public system.
 */
class ThemeHouse_ResCheckInOut_Route_PrefixAdmin_CheckInOutPaymentMethods implements XenForo_Route_Interface
{

    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'payment_method_id');
        $action = $router->resolveActionAsPageNumber($action, $request);
        return $router->getRouteMatch('ThemeHouse_ResCheckInOut_ControllerAdmin_PaymentMethod', $action, 'checkInOutPaymentMethods');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'payment_method_id', 'title');
    }
}