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

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Polycast_Service_Rackspace_CloudFiles_AllTests::main');
}

/**
 * Polycast_Service_Rackspace_CloudFiles_AllTests
 */
class Polycast_Service_Rackspace_CloudFiles_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Polycast ZF Extensions - Polycast_Service_Rackspace_CloudFiles');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Polycast_Service_Rackspace_CloudFiles_AllTests::main') {
    Polycast_Service_Rackspace_CloudFiles_AllTests::main();
}
