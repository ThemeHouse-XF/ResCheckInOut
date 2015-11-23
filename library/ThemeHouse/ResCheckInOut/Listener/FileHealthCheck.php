<?php

class ThemeHouse_ResCheckInOut_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/ResCheckInOut/AlertHandler/CheckOut.php' => '0710d2fa7a1073cbec5ae8d0d5e287d1',
                'library/ThemeHouse/ResCheckInOut/ControllerAdmin/Condition.php' => 'a7193831aed8aaf3160716d4002aad88',
                'library/ThemeHouse/ResCheckInOut/ControllerAdmin/Location.php' => '3c8d5fc785af73e183b933d7b3e1b7ae',
                'library/ThemeHouse/ResCheckInOut/ControllerAdmin/PaymentMethod.php' => '89750d239d54f7257f17c3c76943b7b1',
                'library/ThemeHouse/ResCheckInOut/ControllerPublic/CheckOut.php' => '16714a4af0cc71029fa2ef2e2e3b3cc7',
                'library/ThemeHouse/ResCheckInOut/CronEntry/Reminders.php' => 'f219c5cb37f0c5d754c873dd9083c1a8',
                'library/ThemeHouse/ResCheckInOut/DataWriter/CheckOut.php' => '42c964f84e96e6344d42fc2f0c66f569',
                'library/ThemeHouse/ResCheckInOut/DataWriter/Condition.php' => '7f7c1070276df114e66f7883e0330c08',
                'library/ThemeHouse/ResCheckInOut/DataWriter/Location.php' => '92da89f55c0f0b0b93073dfabededb42',
                'library/ThemeHouse/ResCheckInOut/DataWriter/PaymentMethod.php' => '15450dc154fae5ff5e8d687a0db1105c',
                'library/ThemeHouse/ResCheckInOut/Deferred/Reminders.php' => 'c4558b8b35a956941612fc3cc8476758',
                'library/ThemeHouse/ResCheckInOut/Extend/XenForo/Model/User.php' => '23434288f2dbbf82a4702667641a9873',
                'library/ThemeHouse/ResCheckInOut/Extend/XenForo/Visitor.php' => '8dfe808192bda8f8c105cac923c2845c',
                'library/ThemeHouse/ResCheckInOut/Extend/XenResource/ControllerHelper/Resource.php' => '9592a18c472f262e96e85e46a60812c9',
                'library/ThemeHouse/ResCheckInOut/Extend/XenResource/ControllerPublic/Resource.php' => 'fe357ebc3c267c508df5f133c93778db',
                'library/ThemeHouse/ResCheckInOut/Extend/XenResource/DataWriter/Resource.php' => '7c8b6544f47a81423c7e2929645489df',
                'library/ThemeHouse/ResCheckInOut/Extend/XenResource/Model/Resource.php' => '17731680d214e9a543469bba2b772073',
                'library/ThemeHouse/ResCheckInOut/Extend/XenResource/Route/Prefix/Resources.php' => '3c8e76d59b64695095c0b668ec642f63',
                'library/ThemeHouse/ResCheckInOut/Install/Controller.php' => '5a4a3e750824ef43118b8e761dc4f612',
                'library/ThemeHouse/ResCheckInOut/Listener/ContainerPublicParams.php' => '766d5bc5e47330f1a67063df6eabf119',
                'library/ThemeHouse/ResCheckInOut/Listener/LoadClass.php' => 'c1452ace4f065f46807a331f43af7893',
                'library/ThemeHouse/ResCheckInOut/Model/CheckOut.php' => 'bf2c1e8eb0449509f46c21ef6ad02cf4',
                'library/ThemeHouse/ResCheckInOut/Model/Condition.php' => 'bb85f4f1b25c13d74454622d2a32b37a',
                'library/ThemeHouse/ResCheckInOut/Model/Location.php' => 'bc83d579a9a46b9385f1c86bc2e52775',
                'library/ThemeHouse/ResCheckInOut/Model/PaymentMethod.php' => 'e791671bc851419ff95ca7f417c3312e',
                'library/ThemeHouse/ResCheckInOut/Route/PrefixAdmin/CheckInOutLocations.php' => 'c6175005b00710dcefc285584e96d5af',
                'library/ThemeHouse/ResCheckInOut/Route/PrefixAdmin/CheckInOutPaymentMethods.php' => '28bd4bbf3dc6b9768b4ed81c70535be5',
                'library/ThemeHouse/ResCheckInOut/Route/PrefixAdmin/ResourceConditions.php' => '116d73ec0ac74e14ac537b8fe4a5aefe',
                'library/ThemeHouse/ResCheckInOut/Search/DataHandler/CheckOut.php' => 'da75743df832d2959a7029fb577d3c85',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/ContainerParams.php' => '43bf59af9f140f58f665be373ac07320',
                'library/ThemeHouse/Listener/ContainerParams/20150106.php' => '36fa6f85128a9a9b2b88210c9abe33bd',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
            ));
    }
}