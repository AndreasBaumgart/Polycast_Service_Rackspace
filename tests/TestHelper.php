<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    UnitTests
 * @version    $Id: TestHelper.php 19837 2009-12-21 15:16:14Z matthew $
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/*
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

/*
 * Set error reporting to the highest level.
 */
error_reporting( E_ALL | E_STRICT );

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$pcRoot        = dirname(__FILE__) . '/..';
$pcCoreLibrary = "$pcRoot/library";
$pcCoreTests   = "$pcRoot/tests";

/*
 * Omit from code coverage reports the contents of the tests directory
 */
foreach (array('php', 'phtml', 'csv') as $suffix) {
    PHPUnit_Util_Filter::addDirectoryToFilter($pcCoreTests, ".$suffix");
}

/*
 * Prepend the library/ and tests/ directories to the include_path.
 * This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(
    $pcCoreLibrary,
    $pcCoreTests,
    get_include_path()
    );
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($pcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $pcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $pcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

/*
 * Unset global variables that are no longer needed.
 */
unset($pcRoot, $pcCoreLibrary, $pcCoreTests, $path);
