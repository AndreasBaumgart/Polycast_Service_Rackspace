<?php

/**
 * Polycast Zend Framework Extensions
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * It is also available through the world-wide-web at this URL:
 * http://polycast.de/license/new-bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to andreas@polycast.de so I can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2010 Andreas Baumgart <andreas@polycast.de>
 * @license    http://polycast.de/license/new-bsd.txt  New BSD License
 */

require_once dirname(__FILE__) . '/../../TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Polycast_Service_AllTests::main');
}

require_once 'Polycast/Service/Rackspace/AllTests.php';

/**
 * Polycast_Service_AllTests
 */
class Polycast_Service_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Regular suite
     *
     * All tests except those that require output buffering.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Polycast ZF Extensions - Polycast_Service');

//        $suite->addTestSuite('Zend_Acl_AclTest');
        $suite->addTest(Polycast_Service_Rackspace_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Polycast_Service_AllTests::main') {
    Polycast_Service_AllTests::main();
}
