<?php

// Fill in your Rackspace credentials here
define('CLOUDFILES_ACCOUNT', 'you');
define('CLOUDFILES_APIKEY', 'xyz');

require_once dirname(__FILE__) . '/../library/Polycast/Service/Rackspace/CloudFiles.php';

define('DEMO_LOCAL_FILE', dirname(__FILE__) . '/rick.jpg');
define('DEMO_CONTAINER_NAME', 'MyContainer');

$service = new Polycast_Service_Rackspace_CloudFiles();

// Authenticate with your account name and API key.
try {
    $service->authenticate(CLOUDFILES_ACCOUNT, CLOUDFILES_APIKEY);
} catch (Polycast_Service_Rackspace_CloudFiles_Exception $e) {
    echo 'Authentication failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Create a new storage container.
$service->createStorageContainer(DEMO_CONTAINER_NAME);

// Enable CDN facilities of the storage container we created before.
$containerUrl = $service->createCdnContainer(DEMO_CONTAINER_NAME);

// Set the time the container will remain in CDN cache to 2 hours.
// Note: You could also have passed this as third parameter of
// createCdnContainer().
$service->setCdnContainerAttributes(DEMO_CONTAINER_NAME, array(
    'ttl' => 7200 // 2 hours
));

// Create a new object in the CDN-enabled container.
try {
    $service->createStorageObjectFromFile(
            DEMO_CONTAINER_NAME,
            DEMO_LOCAL_FILE,
            array('objectName' => 'astley.jpg')
    );
} catch (Polycast_Service_Rackspace_CloudFiles_Exception $e ) {
    echo 'Failed to create object from file: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Set some meta data on the object.
$service->setStorageObjectMetaData(DEMO_CONTAINER_NAME, 'astley.jpg', array(
    'OriginalFilename' => basename(DEMO_LOCAL_FILE),
    'Client' => 'Polycast_Service_Rackspace_CloudFiles'
));

// The object URL is the concatenated string of the container URL and the
// object name.
echo 'CDN-URL: ' . $containerUrl . '/astley.jpg' . PHP_EOL;
