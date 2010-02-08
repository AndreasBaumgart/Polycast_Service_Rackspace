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

/** @see Polycast_Service_Rackspace_CloudFiles */
require_once 'Polycast/Service/Rackspace/CloudFiles.php';

/**
 * Polycast_Service_Rackspace_CloudFilesTest
 */
class Polycast_Service_Rackspace_CloudFilesTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var Polycast_Service_Rackspace_CloudFiles
     */
    protected $_service = null;

    public function setUp()
    {
        $this->_service = new Polycast_Service_Rackspace_CloudFiles();
        $this->_service->authenticate(
            TEST_POLYCAST_SERVICE_RACKSPACE_CLOUDFILES_ACCOUNT,
            TEST_POLYCAST_SERVICE_RACKSPACE_CLOUDFILES_APIKEY
        );
    }

    public function testAuthenticationSucceedsWithProperCredentials()
    {
        $service = new Polycast_Service_Rackspace_CloudFiles();
        $result = $service->authenticate(
            TEST_POLYCAST_SERVICE_RACKSPACE_CLOUDFILES_ACCOUNT,
            TEST_POLYCAST_SERVICE_RACKSPACE_CLOUDFILES_APIKEY
        );
        $this->assertEquals($service, $result);
        $this->assertTrue($service->isAuthenticated());
    }

    public function testAuthenticationThrowsExceptionForWrongCredentials()
    {
        $service = new Polycast_Service_Rackspace_CloudFiles();
        try {
            $service->authenticate('usernamenooneshouldnevereveruse', 'bleh');
        } catch(Polycast_Service_Rackspace_CloudFiles_Exception $e) {
            $caught = true;
        }
        if(!isset($caught)) {
            $this->fail('Expected Polycast_Service_Rackspace_CloudFiles_Exception');
        }
        $this->assertFalse($service->isAuthenticated());
    }

    public function testGetStorageContainers()
    {
        try {
            $this->_service->deleteStorageContainer('TestGetStorageContainers');
        } catch (Polycast_Service_Rackspace_CloudFiles_Exception $e) { /* it's ok. */ }
        
        $initContainers = $this->_service->getStorageContainers();
        $this->assertType('array', $initContainers);

        $this->_service->createStorageContainer('TestGetStorageContainers');
        $result = $this->_service->getStorageContainers();
        $this->assertEquals(count($initContainers) + 1, count($result));
        $this->assertEquals('TestGetStorageContainers', $result[0]);
        
        $this->_service->deleteStorageContainer('TestGetStorageContainers');
    }

    public function testGetStorageStatistics()
    {
        $stats = $this->_service->getStorageStatistics();
        $this->assertType('array', $stats);
        $this->assertArrayHasKey('bytes', $stats);
        $this->assertArrayHasKey('count', $stats);
    }

    public function testGetStorageContainerStatistics()
    {
        $this->_service->createStorageContainer('TestGetStorageContainerStatistics');

        $stats = $this->_service->getStorageStatistics();
        $this->assertType('array', $stats);
        $this->assertArrayHasKey('bytes', $stats);
        $this->assertArrayHasKey('count', $stats);
    }

    /**
     * @todo Implement testGetStorageObjects().
     */
    public function testGetStorageObjects() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCreateStorageContainer().
     */
    public function testCreateStorageContainer() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDeleteStorageContainer().
     */
    public function testDeleteStorageContainer() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetStorageObjectMetaData().
     */
    public function testGetStorageObjectMetaData() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetStorageObjectData().
     */
    public function testGetStorageObjectData() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCreateStorageObject().
     */
    public function testCreateStorageObject() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCreateStorageObjectFromFile().
     */
    public function testCreateStorageObjectFromFile() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSetStorageObjectMetaData().
     */
    public function testSetStorageObjectMetaData() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDeleteStorageObject().
     */
    public function testDeleteStorageObject() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetCdnContainers().
     */
    public function testGetCdnContainers() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetCdnContainerAttributes().
     */
    public function testGetCdnContainerAttributes() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCreateCdnContainer().
     */
    public function testCreateCdnContainer() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSetCdnContainerAttributes().
     */
    public function testSetCdnContainerAttributes() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetMimeType().
     */
    public function testGetMimeType() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}

