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
 * @package    Polycast_Service_Rackspace_CloudFiles
 * @copyright  Copyright (c) 2010 Andreas Baumgart <andreas@polycast.de>
 * @license    http://polycast.de/license/new-bsd.txt  New BSD License
 */

/**
 * @see Zend_Service_Abstract
 */
require_once 'Zend/Service/Abstract.php';

/**
 * The Polycast_Service_Rackspace_CloudFiles component is an implementation of 
 * the RackspaceCloud CloudFiles API.
 * 
 * @link      http://www.rackspacecloud.com/cloud_hosting_products/files/api
 * @package   Polycast_Service_Rackspace_CloudFiles
 */
class Polycast_Service_Rackspace_CloudFiles extends Zend_Service_Abstract
{
    /**
     * URL pointing to the Storage API.
     * @var string
     */
    protected $_authStorageUrl = null;
    
    /**
     * URL pointing to the CDN API.
     * @var string
     */
    protected $_authCdnUrl = null;
    
    /**
     * Authentication token.
     * @var string
     */
    protected $_authToken = null;
    
    /**
     * Inject authentication headers into the HTTP client.
     * @return Zend_Http_Client
     */
    protected function _prepareHttpClient()
    {
        $client = self::getHttpClient();
        
        if(!is_null($this->_authToken)) {
            $client->setHeaders('X-Auth-Token', $this->_authToken);
        }
        if(!is_null($this->_authToken)) {
            $client->setHeaders('X-Storage-Url', $this->_authStorageUrl);
        }
        if(!is_null($this->_authToken)) {
            $client->setHeaders('X-CDN-Management-Url', $this->_authCdnUrl);
        }
        return $client;
    }
    
    /**
     * Authenticate client and retrieve credentials.
     * 
     * @param string $account Rackspace username.
     * @param string $apiKey Rackspace API key.
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function authenticate($account, $apiKey)
    {
        $client = self::getHttpClient();
        $client->setHeaders('Host', 'auth.api.rackspacecloud.com');
        $client->setHeaders('X-Auth-User', $account);
        $client->setHeaders('X-Auth-Key', $apiKey);
        $client->setUri('https://auth.api.rackspacecloud.com/v1.0');
        $response = $client->request(Zend_Http_Client::GET);
        
        if("204" != $response->getStatus()) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Authentication failed. HTTP response code: ' .
                $response->getStatus()
            );
        }
        
        $this->_authStorageUrl = $response->getHeader('X-storage-url');
        $this->_authCdnUrl = $response->getHeader('X-cdn-management-url');
        $this->_authToken = $response->getHeader('X-auth-token');
        
        return $this;
    }

    /**
     * Check if the client is authenticated.
     * @return boolean
     */
    public function isAuthenticated()
    {
        return !is_null($this->_authToken);
    }
    
    /**
     * Retrieve a list with all container names in your account.
     * 
     * @param int $limit     For an integer value N, limits the number of 
     *                       results to at most N values.
     * @param string $marker Given a string value X, return Object names greater
     *                       in value than the specified marker.
     * @return array
     */
    public function getStorageContainers($limit = null, $marker = null)
    {
        $client = $this->_prepareHttpClient();
        
        if(!is_null($limit)) {
            $client->setParameterGet('limit', $limit);
        }
        if(!is_null($marker)) {
            $client->setParameterGet('marker', $marker);
        }
        
        $client->setParameterGet('format', 'xml');
        $client->setUri($this->_authStorageUrl);
        $response = $client->request(Zend_Http_Client::GET);

        switch($response->getStatus()) {
            
            // Got content. Parse response.
            case '200':
                $containers = array();
                $xml = simplexml_load_string($response->getBody());
                
                foreach($xml->container as $containerNode) {
                    /* @var $containerNode SimpleXMLElement */
                    $containers[(string) $containerNode->name] = array(
                        'count' => (string) $containerNode->count,
                        'bytes' => (string) $containerNode->bytes
                    );
                }
                return $containers;
                
            // No content.
            case '204':
                return array();
            
            // Unknown status code.
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Return array with account statistics. I.e. number of containers
     * and total bytes used.
     * @return array
     */
    public function getStorageStatistics()
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl);
        $response = $client->request(Zend_Http_Client::HEAD);
        
        if('401' == $response->getStatus()) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Not authenticated'
            );
        } elseif('204' != $response->getStatus()) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Unexpected HTTP status code: ' . $response->getStatus()
            );
        }
        return array(
            'bytes' => (int) $response->getHeader('X-Account-Total-Bytes-Used'),
            'count' => (int) $response->getHeader('X-Account-Container-Count'),
        );
    }
    
    public function getStorageContainerStatistics($containerName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName);
        $response = $client->request(Zend_Http_Client::HEAD);
        
        if('401' == $response->getStatus()) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Not authenticated'
            );
        } elseif('204' != $response->getStatus()) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Unexpected HTTP status code: ' . $response->getStatus()
            );
        }
        return array(
            'bytes' => (int) $response->getHeader('X-Container-Bytes-Used'),
            'count' => (int) $response->getHeader('X-Container-Object-Count'),
        );
    }
    
    /**
     * Retrieve a list of contents in a specific container.
     * @param string $name Name of the container.
     * @param array $options
     * @return array
     */
    public function getStorageObjects($containerName, $options = array())
    {
        $client = $this->_prepareHttpClient();
        
        if(isset($options['limit'])) {
            $client->setParameterGet('limit', $options['limit']);
        }
        if(isset($options['marker'])) {
            $client->setParameterGet('marker', $options['marker']);
        }
        if(isset($options['prefix'])) {
            $client->setParameterGet('prefix', $options['prefix']);
        }
        
        $client->setParameterGet('format', 'xml');
        $client->setUri($this->_authStorageUrl . '/' . $containerName);
        $response = $client->request(Zend_Http_Client::GET);

        switch($response->getStatus()) {
            
            // Got content. Parse response.
            case '200':
                $objects = array();
                $xml = simplexml_load_string($response->getBody());
                
                foreach($xml->object as $objectNode) {
                    /* @var $containerNode SimpleXMLElement */
                    $objects[(string) $objectNode->name] = array(
                        'hash' => (string) $objectNode->hash,
                        'bytes' => (int) $objectNode->bytes,
                        'contentType' => (string) $objectNode->content_type,
                        'lastModified' => (string) $objectNode->last_modified,
                    );
                }
                return $objects;
                
            // No content
            case '204';
                return array();
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Create a container.
     * @param string $containerName
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function createStorageContainer($containerName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName);
        $response = $client->request(Zend_Http_Client::PUT);
        
        switch($response->getStatus()) {
        
            // Got content. Parse response.
            case '201': // Created.
            case '202': // Accepted, container already exists.
                return $this;
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Delete a container.
     * 
     * You can only delete empty containers. If the container is not empty a 
     * Polycast_Service_Rackspace_CloudFiles_Exception with error code 409 is 
     * thrown.
     * 
     * If the container does not exist an 
     * Polycast_Service_Rackspace_CloudFiles_Exception is thrown with error
     * code 404. 
     * 
     * @param string $containerName
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function deleteStorageContainer($containerName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName);
        $response = $client->request(Zend_Http_Client::DELETE);
        
        switch($response->getStatus()) {
            case '204': // No content. Success.
                return $this;
            
            case '404': // container not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Container not found: ' . $containerName, 404
                );
                
            case '409': // conflict, container is not empty
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Container is not empty: ' . $containerName, 409
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Get meta data of an object.
     * 
     * @param string $containerName Name of the container the object resides in.
     * @param string $objectName Name of the object.
     * @return array Meta information.
     */
    public function getStorageObjectMetaData($containerName, $objectName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName . '/' . $objectName);
        $response = $client->request(Zend_Http_Client::HEAD);
        
        switch($response->getStatus()) {
            case '204': // No content. Success.
                $metaData = array();
                foreach($response->getHeaders() as $name => $value) {
                    $value = rawurldecode($value);
                    if('x-object-meta-' == strtolower(substr($name, 0, 14))) {
                        if(!isset($metaData[$name])) {
                            $metaData[$name] = $value;
                        } else {
                            $metaData[$name] = (array) $metaData[$name];
                            $metaData[$name][] = $value;
                        }
                    }
                }
                return $metaData;
            
            case '404': // container not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Object not found: ' . $containerName . '/' . $objectName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Load the data of an specific object.
     * 
     * If you pass an optional filename, the object content is written to this
     * file and TRUE is returned instead. 
     * 
     * @param string $containerName Name of the container.
     * @param string $objectName Name of the object.
     * @param string $filename Path of the file to dump the data into.
     * @return mixed
     */
    public function getStorageObjectData($containerName, $objectName, $filename = null)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName . '/' . $objectName);
        $response = $client->request(Zend_Http_Client::GET);
        
        switch($response->getStatus()) {
            case '200': // OK, success.
                if(is_null($filename)) {
                    return $response->getBody();
                } else {
                    file_put_contents($filename, $response->getBody());
                    return true;
                }
            
            case '404': // container not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Object not found: ' . $containerName . '/' . $objectName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }

    /**
     * Create a new object.
     * 
     * @param string $containerName Name of the container.
     * @param string $name Name of the object.
     * @param string $data Data
     * @param array $meta Optional meta data.
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function createStorageObject($containerName, $objectName, $data, $mimeType, $bytes, array $options = array())
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName . '/' . $objectName);
        
        // add meta data
        if(isset($options['meta'])) {
            foreach((array) $options['meta'] as $metaName => $metaValues) {
                foreach((array) $metaValues as $metaVal) {
                    $client->setHeaders('X-Object-Meta-' . $metaName, $metaVal);
                }
            }
        }

        $client->setHeaders('ETag', md5($data));
        $client->setHeaders('Content-Length', $bytes);
        $client->setHeaders('Content-Type', $mimeType);
        $client->setRawData($data, $mimeType);
        
        $response = $client->request(Zend_Http_Client::PUT);
        
        switch($response->getStatus()) {
            case '201': // Created, success.
                return $this;
            
            case '412': // container not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Internal error, length required', 412
                );

            case '422': // ETag doesn't match
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Transfer error: Checksum mismatch detected', 422
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Create a new object.
     * 
     * @param string $containerName Name of the container.
     * @param string $name Name of the object.
     * @param string $data Data
     * @param array $meta Optional meta data.
     * @param array $options
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function createStorageObjectFromFile($containerName, $path, array $options = array())
    {
        if(!file_exists($path)) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'File does not exist: ' . $path
            );
        } elseif(!is_readable($path)) {
            throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                'Can\'t read file, no permission: ' . $path
            );
        }
        
        // determine object name
        if(isset($options['objectName'])) {
            $objectName = $options['objectName'];
            unset($options['objectName']);
        } else {
            $objectName = basename($path);
        }
        
        // determine mime type
        if(isset($options['type'])) {
            $type = $options['type'];
            unset($options['type']);
        } else {
            $type = self::getMimeType($path);
        }
        
        // load data
        $data = @file_get_contents($path);
        $bytes = filesize($path);
        
        // create object
        return $this->createStorageObject(
            $containerName, $objectName, $data, $type, $bytes, $options
        );
    }
    
    /**
     * Set / override meta data. 
     * 
     * Calling setObjectMetaData() will delete all existing meta data added to 
     * the object in a previous POST/PUT.
     * 
     * @param array $meta
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function setStorageObjectMetaData($containerName, $objectName, array $meta)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName . '/' . $objectName);
        
        // add meta data
        foreach($meta as $metaName => $metaValues) {
            foreach((array) $metaValues as $metaVal) {
                $client->setHeaders('X-Object-Meta-' . $metaName, $metaVal);
            }
        }
        $response = $client->request(Zend_Http_Client::POST);
        
        switch($response->getStatus()) {
            case '202': // Accepted
                return $this;
            
            case '404': // object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such object: ' . $containerName . '/' . $objectName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Delete an object.
     * 
     * Deleting an Object is processed immediately at the time of the request. 
     * Any subsequent GET, HEAD, POST, or DELETE operations will return a 404 
     * (Not Found) error.
     * 
     * @param string $containerName Name of the container.
     * @param string $objectName Name of the object.
     * @return Polycast_Service_Rackspace_CloudFiles Fluent interface
     */
    public function deleteStorageObject($containerName, $objectName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authStorageUrl . '/' . $containerName . '/' . $objectName);
        $response = $client->request(Zend_Http_Client::DELETE);
        
        switch($response->getStatus()) {
            case '204': // Accepted
                return $this;
            
            case '404': // object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such object: ' . $containerName . '/' . $objectName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Get the list of CDN-enabled containers.
     * 
     * The $options array allows the following options:
     *  - limit:       For an integer value N, limits the number of results to 
     *                 at most N values.
     *  - marker:      Given a string value X, return Object names greater in 
     *                 value than the specified marker.
     *  - enabledOnly: Set to “true” to return only the CDN-enabled Containers.            
     * 
     * @param array $options
     * @return array List of containers. Key is the container name, the value a 
     *               set of properties.
     */
    public function getCdnContainers(array $options = array())
    {
        $client = $this->_prepareHttpClient();
        
        if(isset($options['limit'])) {
            $client->setParameterGet('limit', $options['limit']);
        }
        if(isset($options['marker'])) {
            $client->setParameterGet('marker', $options['marker']);
        }
        if(isset($options['enabledOnly']) && $options['enabledOnly']) {
            $client->setParameterGet('enabled_only', 'true');
        }
        
        $client->setParameterGet('format', 'xml');
        
        $client->setUri($this->_authCdnUrl);
        $response = $client->request(Zend_Http_Client::GET);
        
        switch($response->getStatus()) {
            case '200': // Got some content, success
                $containers = array();
                $xml = simplexml_load_string($response->getBody());
                foreach($xml->container as $containerNode) {
                    /* @var $containerNode SimpleXMLElement */
                    $containers[(string) $containerNode->name] = array(
                        'cdnEnabled' => 'true' == strtolower((string) $containerNode->cdn_enabled),
                        'ttl' => (int) $containerNode->ttl,
                        'logRetention' => 'true' == strtolower((string) $containerNode->log_retention),
                        'cdnUrl' => (string) $containerNode->cdn_url,
                    );
                }
                return $containers;
            
            case '204': // No content, success
                return array();
                
            case '404': // Object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such container: ' . $containerName, 404
                );
                
            default: // Unknown status code
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Determine the CDN attributes of the Container.
     * 
     * Returns an assiciative array which contains the attribute name as keys 
     * and their respective content as values.
     * 
     * The following attributes are available:
     * - cdnEnabled
     * - cdnUri
     * - ttl
     * - logRetention
     * 
     * @param string $containerName Name of the container.
     * @return array
     */
    public function getCdnContainerAttributes($containerName)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authCdnUrl . '/' . $containerName);
        $response = $client->request(Zend_Http_Client::HEAD);
        
        switch($response->getStatus()) {
            case '204': // No content, success
                return array(
                    'cdnEnabled' => 'true' == strtolower($response->getHeader('X-CDN-Enabled')),
                    'cdnUri' => $response->getHeader('X-CDN-URI'),
                    'ttl' => $response->getHeader('X-TTL'),
                    'logRetention' => $response->getHeader('X-Log-Retention'),
                );
            
            case '404': // object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such container: ' . $containerName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * Initially CDN-enable a container and set its attributes.
     * 
     * Accepted attributes are:
     *  - ttl           Time to live in seconds. This is the time the content 
     *                  will remain in the Limelight CDN cache. 
     *                  Min: 3600; Max: 259200 (=3 days). 
     *   
     * @param string $containerName Name of the container
     * @param array $attributes List of attributes
     * @return string URL of the CDN-enabled container.
     */
    public function createCdnContainer($containerName, array $attributes = array())
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authCdnUrl . '/' . $containerName);
        
        if(isset($attributes['ttl'])) {
            $client->setHeaders('X-TTL', $attributes['ttl']);
        }
        if(isset($attributes['logRetentions'])) {
            $client->setHeaders('X-Log-Retention', ($attributes['logRetentions'] ? 'True' : 'False'));
        }
        
        $response = $client->request(Zend_Http_Client::PUT);
        
        switch($response->getStatus()) {
            case '201': // Created, success.
            case '202': // Accepted, container is already CDN-enabled, TTL adjusted.
                return $response->getHeader('X-CDN-URI');
            
            // 404 isn't mentioned in the docs, but I'd expect such a behaviour.
            // @todo check if 404 is returned if container does not exist.
            case '404': // object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such container: ' . $containerName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }
    }
    
    /**
     * 
     * Keep in mind that if you have content currently cached in the CDN, 
     * setting your Container back to private will NOT purge the CDN cache; 
     * you will have to wait for the TTL to expire.
     * 
     * @param string $containerName Name of the container.
     * @param array $attributes
     * @return string URL of the container.
     */
    public function setCdnContainerAttributes($containerName, array $attributes)
    {
        $client = $this->_prepareHttpClient();
        $client->setUri($this->_authCdnUrl . '/' . $containerName);
        
        if(isset($attributes['ttl'])) {
            $client->setHeaders('X-TTL', $attributes['ttl']);
        }
        if(isset($attributes['logRetentions'])) {
            $client->setHeaders('X-Log-Retention', ($attributes['logRetentions'] ? 'True' : 'False'));
        }
        if(isset($attributes['cdnEnabled'])) {
            $client->setHeaders('X-CDN-Enabled', ($attributes['cdnEnabled'] ? 'True' : 'False'));
        }
        
        $response = $client->request(Zend_Http_Client::PUT);
        
        switch($response->getStatus()) {
            case '202': // Accepted
                return $response->getHeader('X-CDN-URI');
            
            case '404': // object not found
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'No such container: ' . $containerName, 404
                );
                
            // Unknown status code
            default:
                throw new Polycast_Service_Rackspace_CloudFiles_Exception(
                    'Unexpected HTTP status code: ' . $response->getStatus()
                );
        }  
    }
    
    /**
     * Attempt to get the content-type of a file based on the extension
     *
     * @param  string $path
     * @return string
     */
    public static function getMimeType($path)
    {
        $ext = substr(strrchr($path, '.'), 1);

        if(!$ext) {
            // shortcut
            return 'binary/octet-stream';
        }

        switch (strtolower($ext)) {
            case 'xls':
                $content_type = 'application/excel';
                break;
            case 'hqx':
                $content_type = 'application/macbinhex40';
                break;
            case 'doc':
            case 'dot':
            case 'wrd':
                $content_type = 'application/msword';
                break;
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'pgp':
                $content_type = 'application/pgp';
                break;
            case 'ps':
            case 'eps':
            case 'ai':
                $content_type = 'application/postscript';
                break;
            case 'ppt':
                $content_type = 'application/powerpoint';
                break;
            case 'rtf':
                $content_type = 'application/rtf';
                break;
            case 'tgz':
            case 'gtar':
                $content_type = 'application/x-gtar';
                break;
            case 'gz':
                $content_type = 'application/x-gzip';
                break;
            case 'php':
            case 'php3':
            case 'php4':
                $content_type = 'application/x-httpd-php';
                break;
            case 'js':
                $content_type = 'application/x-javascript';
                break;
            case 'ppd':
            case 'psd':
                $content_type = 'application/x-photoshop';
                break;
            case 'swf':
            case 'swc':
            case 'rf':
                $content_type = 'application/x-shockwave-flash';
                break;
            case 'tar':
                $content_type = 'application/x-tar';
                break;
            case 'zip':
                $content_type = 'application/zip';
                break;
            case 'mid':
            case 'midi':
            case 'kar':
                $content_type = 'audio/midi';
                break;
            case 'mp2':
            case 'mp3':
            case 'mpga':
                $content_type = 'audio/mpeg';
                break;
            case 'ra':
                $content_type = 'audio/x-realaudio';
                break;
            case 'wav':
                $content_type = 'audio/wav';
                break;
            case 'bmp':
                $content_type = 'image/bitmap';
                break;
            case 'gif':
                $content_type = 'image/gif';
                break;
            case 'iff':
                $content_type = 'image/iff';
                break;
            case 'jb2':
                $content_type = 'image/jb2';
                break;
            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $content_type = 'image/jpeg';
                break;
            case 'jpx':
                $content_type = 'image/jpx';
                break;
            case 'png':
                $content_type = 'image/png';
                break;
            case 'tif':
            case 'tiff':
                $content_type = 'image/tiff';
                break;
            case 'wbmp':
                $content_type = 'image/vnd.wap.wbmp';
                break;
            case 'xbm':
                $content_type = 'image/xbm';
                break;
            case 'css':
                $content_type = 'text/css';
                break;
            case 'txt':
                $content_type = 'text/plain';
                break;
            case 'htm':
            case 'html':
                $content_type = 'text/html';
                break;
            case 'xml':
                $content_type = 'text/xml';
                break;
            case 'xsl':
                $content_type = 'text/xsl';
                break;
            case 'mpg':
            case 'mpe':
            case 'mpeg':
                $content_type = 'video/mpeg';
                break;
            case 'qt':
            case 'mov':
                $content_type = 'video/quicktime';
                break;
            case 'avi':
                $content_type = 'video/x-ms-video';
                break;
            case 'eml':
                $content_type = 'message/rfc822';
                break;
            default:
                $content_type = 'binary/octet-stream';
                break;
        }

        return $content_type;
    }
}