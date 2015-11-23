<?php

/**
 *
 * @see XenResource_Route_Prefix_Resources
 */
class ThemeHouse_ResCheckInOut_Extend_XenResource_Route_Prefix_Resources extends XFCP_ThemeHouse_ResCheckInOut_Extend_XenResource_Route_Prefix_Resources
{

    public function __construct()
    {
        if (is_callable('parent::__construct')) {
            parent::__construct();
        }

        $this->_subComponents['check-outs'] = array(
			'intId' => 'user_id',
			'title' => 'username',
			'controller' => 'ThemeHouse_ResCheckInOut_ControllerPublic_CheckOut'
		);
    }
}