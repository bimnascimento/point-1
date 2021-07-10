<?php
class Client{
	const OLD_API_URL = 'https://apis.live.net/v5.0/';
	const API_URL = 'https://api.onedrive.com/v1.0/';
    const AUTH_URL = 'https://login.live.com/oauth20_authorize.srf';
    const TOKEN_URL = 'https://login.live.com/oauth20_token.srf';
    private $_clientId;
    private $_state;
    private $_httpStatus;
    private $_contentType;
    private $_sslVerify;
    private $_sslCaPath;
    private $size_of_chunk = 4194304;// send 4 mb at one time
    
    /**
     * Creates a base cURL object which is compatible with the OneDrive API.
     *
     * @param string $path    The path of the API call (eg. me/skydrive).
     * @param array  $options Extra cURL options to apply.
     *
     * @return resource A compatible cURL object.
     */
    private function _createCurl($path, $options = array()){
        $curl = curl_init();

        $defaultOptions = array(
            // General options.
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,

            // SSL options.
            // The value 2 checks the existence of a common name and also
            // verifies that it matches the hostname provided.
            CURLOPT_SSL_VERIFYHOST => ($this->_sslVerify ? 2 : false),

            CURLOPT_SSL_VERIFYPEER => $this->_sslVerify
        );

        if ($this->_sslVerify && $this->_sslCaPath) {
            $default_options[CURLOPT_CAINFO] = $this->_sslCaPath;
        }

        // See http://php.net/manual/en/function.array-merge.php for a
        // description of the + operator (and why array_merge() would be wrong).
        $finalOptions = $options + $defaultOptions;

        curl_setopt_array($curl, $finalOptions);
        return $curl;
    }

    /**
     * Processes a result returned by the OneDrive API call using a cURL object.
     *
     * @param resource $curl The cURL object used to perform the call.
     *
     * @return object|string The content returned, as an object instance if
     *                       served a JSON, or as a string if served as anything
     *                       else.
     *
     * @throws \Exception Thrown if curl_exec() fails.
     */
    
    private function _processResult($curl){
        $result = curl_exec($curl);
        if (false === $result) {
            throw new \Exception('curl_exec() failed: ' . curl_error($curl));
        }
        $info = curl_getinfo($curl);
        
        $this->_httpStatus = array_key_exists('http_code', $info) ?
            (int) $info['http_code'] : null;

        $this->_contentType = array_key_exists('content_type', $info) ?
            (string) $info['content_type'] : null;

        // Parse nothing but JSON.
        if (1 !== preg_match('|^application/json|', $this->_contentType)) {
            return $result;
        }

        // Empty JSON string is returned as an empty object.
        if ('' == $result) {
            return (object) array();
        }

        $decoded = json_decode($result);
        $vars    = get_object_vars($decoded);
        
        if (array_key_exists('error', $vars)) {
            throw new \Exception($decoded->error->message, (int) $decoded->error->code);
        }
        return $decoded;
    }
    
    

    /**
     * Constructor.
     *
     * @param array $options The options to use while creating this object.
     *                       Valid supported keys are:
     *                         'state'      (object)      When defined, it
     *                                                    should contain a valid
     *                                                    OneDrive client state,
     *                                                    as returned by
     *                                                    getState(). Default:
     *                                                    array().
     *                         'ssl_verify' (bool)        Whether to verify SSL
     *                                                    hosts and peers.
     *                                                    Default: false.
     *                         'ssl_capath' (bool|string) CA path to use for
     *                                                    verifying SSL
     *                                                    certificate chain.
     *                                                    Default: false.
     */
    public function __construct(array $options = array()){
    	
        $this->_clientId = array_key_exists('client_id', $options)
            ? (string) $options['client_id'] : null;

        $this->_state = array_key_exists('state', $options)
            ? $options['state'] : (object) array(
                'redirect_uri' => null,
                'token'        => null
            );
        $this->_sslVerify = array_key_exists('ssl_verify', $options)
            ? $options['ssl_verify'] : false;

        $this->_sslCaPath = array_key_exists('ssl_capath', $options)
            ? $options['ssl_capath'] : false;
    }

    /**
     * Gets the current state of this Client instance. Typically saved in the
     * session and passed back to the Client constructor for further requests.
     *
     * @return object The state of this Client instance.
     */
    public function getState(){
        return $this->_state;
    }

    // TODO: support $options.
    /**
     * Gets the URL of the log in form. After login, the browser is redirected to
     * the redirect URL, and a code is passed as a GET parameter to this URL.
     *
     * The browser is also redirected to this URL if the user is already logged
     * in.
     *
     * @param array $scopes       The OneDrive scopes requested by the
     *                            application. Supported values:
     *                              'wl.signin'
     *                              'wl.basic'
     *                              'wl.contacts_skydrive'
     *                              'wl.skydrive_update'
     * @param string $redirectUri The URI to which to redirect to upon
     *                            successful log in.
     * @param array  $options     Reserved for future use. Default: array().
     *
     * @return string The login URL.
     *
     * @throws \Exception Thrown if this Client instance's clientId is not set.
     */
    public function getLogInUrl(array $scopes, $redirectUri, array $options = array()){
        if (null === $this->_clientId) {
            throw new \Exception('The client ID must be set to call getLoginUrl()');
        }

        $imploded    = implode(',', $scopes);
        $redirectUri = (string) $redirectUri;
        if (isset($this->_state->redirect_uri)){
        	$this->_state->redirect_uri = $redirectUri;        	
        }
        
        // When using this URL, the browser will eventually be redirected to the
        // callback URL with a code passed in the URL query string (the name of the
        // variable is "code"). This is suitable for PHP.
        $url = self::AUTH_URL
            . '?client_id=' . urlencode($this->_clientId)
            . '&scope=' . urlencode($imploded)
            . '&response_type=code'
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&display=popup'
            . '&locale=en';

        return $url;
    }

    /**
     * Gets the access token expiration delay.
     *
     * @return int The token expiration delay, in seconds.
     */
    public function getTokenExpire(){
        return $this->_state->token->obtained
            + $this->_state->token->data->expires_in - time();
    }

    /**
     * Gets the status of the current access token.
     *
     * @return int The status of the current access token:
     *                0 No access token.
     *               -1 Access token will expire soon (1 minute or less).
     *               -2 Access token is expired.
     *                1 Access token is valid.
     */
    public function getAccessTokenStatus(){
        if (null === $this->_state->token) {
            return 0;
        }

        $remaining = $this->getTokenExpire();

        if (0 >= $remaining) {
            return -2;
        }

        if (60 >= $remaining) {
            return -1;
        }

        return 1;
    }

    /**
     * Obtains a new access token from OAuth. This token is valid for one hour.
     *
     * @param string $clientSecret The OneDrive client secret.
     * @param string $code         The code returned by OneDrive after
     *                             successful log in.
     * @param string $redirectUri  Must be the same as the redirect URI passed
     *                             to getLoginUrl().
     *
     * @throws \Exception Thrown if this Client instance's clientId is not set.
     * @throws \Exception Thrown if the redirect URI of this Client instance's
     *                    state is not set.
     */
    public function obtainAccessToken($clientSecret, $code, $redirect_uri){
        if (null === $this->_clientId) {
            throw new \Exception('The client ID must be set to call obtainAccessToken()');
        }
        
        if (!empty($this->_state) && !empty($this->_state->redirect_uri)) {
       		$redirect_uri = $this->_state->redirect_uri;    
        }

        $url = self::TOKEN_URL
            . '?client_id=' . urlencode($this->_clientId)
            . '&redirect_uri=' . urlencode($redirect_uri)
            . '&client_secret=' . urlencode($clientSecret)
            . '&grant_type=authorization_code'
            . '&code=' . urlencode($code);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            // General options.
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,

            // SSL options.
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $url
        ));

        $result = curl_exec($curl);

        if (false === $result) {
            if (curl_errno($curl)) {
                throw new \Exception('curl_setopt_array() failed: ' . curl_error($curl));
            } else {
                throw new \Exception('curl_setopt_array(): empty response');
            }
        }

        $decoded = json_decode($result);

        if (null === $decoded) {
            throw new \Exception('json_decode() failed');
        }

        if (isset($this->_state->redirect_uri)){
        	$this->_state->redirect_uri = null;        	
        }

        if (empty($this->_state)){
        	$this->_state = new stdClass();
        }
        $this->_state->token = (object) array(
            'obtained' => time(),
            'data'     => $decoded
        );
        //return $this->_state->token;
    }

    /**
     * Renews the access token from OAuth. This token is valid for one hour.
     *
     * @param string $clientSecret The client secret.
     * @param string $redirectUri  The redirect URI.
     */
    public function renewAccessToken($clientSecret, $redirectUri){
        $url = self::TOKEN_URL
            . '?client_id=' . $this->_clientId
            . '&redirect_uri=' . (string) $redirectUri
            . '&client_secret=' . (string) $clientSecret
            . '&grant_type=' . 'refresh_token'
            . '&refresh_token=' . (string)$this->_state->token->data->refresh_token; // ADDED BY AZZAROCO
            //. '&code=' . (string) $this->_state->token->data->refresh_token; //not working this param

        ///ADDED BY AZZAROCO
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        		// General options.
        		CURLOPT_RETURNTRANSFER => true,
        		//CURLOPT_FOLLOWLOCATION => true,
        		CURLOPT_AUTOREFERER    => true,
        
        		// SSL options.
        		CURLOPT_SSL_VERIFYHOST => false,
        		CURLOPT_SSL_VERIFYPEER => false,
        		CURLOPT_URL            => $url
        ));
        
        $result = curl_exec($curl);
        
        if (false === $result) {
        	if (curl_errno($curl)) {
        		throw new \Exception('curl_setopt_array() failed: ' . curl_error($curl));
        	} else {
        		throw new \Exception('curl_setopt_array(): empty response');
        	}
        }
        $decoded = json_decode($result);
        
        if (null === $decoded) {
        	throw new \Exception('json_decode() failed');
        }
        
        $this->_state->redirect_uri = null;
        $this->_state->token = (object) array(
        		'obtained' => time(),
        		'data'     => $decoded
        );
        return $this->_state->token;
        ///ADDED BY AZZAROCO
    }

    /**
     * Performs a call to the OneDrive API using the GET method.
     *
     * @param string $path    The path of the API call (eg. me/skydrive).
     * @param array  $options Further curl options to set.
     *
     * @return object|string The response body, if any.
     */
    public function apiGet($path, $options = array()){
        //$url = self::API_URL . $path . '?access_token=' . urlencode($this->_state->token->data->access_token);
    	$url = self::OLD_API_URL . $path . '?access_token=' . urlencode($this->_state->token->data->access_token);
    	
        $curl = self::_createCurl($path, $options);

        curl_setopt($curl, CURLOPT_URL, $url);
        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the POST method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiPost($path, $data=null){
    	/*
    	 * @param string, object
    	 * @return array
    	 */
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL        => $url,
            CURLOPT_POST       => true,

            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the PUT method.
     *
     * @param string   $path        The path of the API call (eg. me/skydrive).
     * @param resource $stream      The data stream to upload.
     * @param string   $contentType The MIME type of the data stream, or null if
     *                              unknown. Default: null.
     * @param number $upload_size
     * @param array $headers
     * @return object|string The response body, if any.
     */
    public function apiPut($path, $stream, $contentType=null, $upload_size=-1, $headers=array(), $create=FALSE) {
    	if (strpos($path, 'https://')!==0){
    		if ($create){
    			$url = self::OLD_API_URL . $path;
    		} else {
    			$url = self::API_URL . $path;
    		}
  			
  		} else {
  			$url = $path;
  		}
    	$curl  = $this->_createCurl($path);
    	
    	if ($upload_size===-1) {
    		$stats = fstat($stream);
    		$upload_size = $stats[7];
    	}
       
    	$headers[] = 'Authorization: Bearer ' . $this->_state->token->data->access_token;
    	if ($contentType!==null && $contentType) {
    		$headers[] = 'Content-Type: ' . $contentType;
    	}
    	
    	$options = array(
    			CURLOPT_URL        => $url,
    			CURLOPT_HTTPHEADER => $headers,
    			CURLOPT_PUT        => true,
    			CURLOPT_INFILE     => $stream,
    			CURLOPT_INFILESIZE => $upload_size
    	);
    
    	curl_setopt_array($curl, $options);
    	return $this->_processResult($curl);
    }
    
    
    /**
     * Performs a call to the OneDrive API using the DELETE method.
     *
     * @param string $path The path of the API call (eg. me/skydrive).
     *
     * @return object|string The response body, if any.
     */
    public function apiDelete($path){
        $url = self::OLD_API_URL . $path;

        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        	CURLOPT_HTTPHEADER	=> array('Authorization: Bearer ' . $this->_state->token->data->access_token)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the MOVE method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiMove($path, $data){
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'MOVE',

            CURLOPT_HTTPHEADER    => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS    => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the COPY method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiCopy($path, $data){
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'COPY',

            CURLOPT_HTTPHEADER    => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS    => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Creates a folder in the current OneDrive account.
     *
     * @param string      $name        The name of the OneDrive folder to be
     *                                 created.
     * @param null|string $parentId    The ID of the OneDrive folder into which
     *                                 to create the OneDrive folder, or null to
     *                                 create it in the OneDrive root folder.
     *                                 Default: null.
     * @param null|string $description The description of the OneDrive folder to
     *                                 be created, or null to create it without
     *                                 a description. Default: null.
     *
     * @return Folder The folder created, as a Folder instance referencing to
     *                the OneDrive folder created.
     */
    public function createFolder($name, $parentId = null, $description = null){
        if (null === $parentId) {
            $parentId = 'me/skydrive';
        }

        $properties = array(
            'name' => (string) $name
        );

        if (null !== $description) {
            $properties['description'] = (string) $description;
        }

        $folder = $this->apiPost($parentId, (object) $properties);
        return new Folder($this, $folder->id, $folder);
    }

    /**
     * Creates a file in the current OneDrive account.
     *
     * @param string      $name        The name of the OneDrive file to be
     *                                 created.
     * @param null|string $parentId    The ID of the OneDrive folder into which
     *                                 to create the OneDrive file, or null to
     *                                 create it in the OneDrive root folder.
     *                                 Default: null.
     * @param string|resource $content The content of the OneDrive file to be
     *                                 created, as a string or as a resource to
     *                                 an already opened file. In the latter
     *                                 case, the responsibility to close the
     *                                 handle is left to the calling function.
     *                                 Default: ''.
     *
     * @return File The file created, as File instance referencing to the
     *              OneDrive file created.
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function createFile($name, $parentId = null, $content = ''){
        if (null === $parentId) {
            $parentId = 'me/skydrive';
        }

        if (is_resource($content)) {
            $stream = $content;
        } else {
            $stream = fopen('php://memory', 'w+b');

            if (false === $stream) {
                throw new \Exception('fopen() failed');
            }

            if (false === fwrite($stream, $content)) {
                fclose($stream);
                throw new \Exception('fwrite() failed');
            }

            if (!rewind($stream)) {
                fclose($stream);
                throw new \Exception('rewind() failed');
            }
        }

        // TODO: some versions of cURL cannot PUT memory streams? See here for a
        // workaround: https://bugs.php.net/bug.php?id=43468
        $file = $this->apiPut($parentId . '/files/' . urlencode($name), $stream, false, -1, array(), TRUE );
        
        // Close the handle only if we opened it within this function.
        if (!is_resource($content)) {
            fclose($stream);
        }
        
        return new File($this, $file->id, $file);
    }

    /**
     * Fetches an object from the current OneDrive account.
     *
     * @param null|string The unique ID of the OneDrive object to fetch, or null
     *                    to fetch the OneDrive root folder. Default: null.
     *
     * @return object The object fetched, as an Object instance referencing to
     *                the OneDrive object fetched.
     */
    public function fetchObject($objectId = null){
        $objectId = null !== $objectId ? $objectId : 'me/skydrive';
        $result   = $this->apiGet($objectId);

        if (in_array($result->type, array('folder', 'album'))) {
            return new Folder($this, $objectId, $result);
        }

        return new File($this, $objectId, $result);
    }

    /**
     * Fetches the root folder from the current OneDrive account.
     *
     * @return Folder The root folder, as a Folder instance referencing to the
     *         OneDrive root folder.
     */
    public function fetchRoot(){
        return $this->fetchObject();
    }

    /**
     * Fetches the "Camera Roll" folder from the current OneDrive account.
     *
     * @return Folder The "Camera Roll" folder, as a Folder instance referencing
     *                to the OneDrive "Camera Roll" folder.
     */
    public function fetchCameraRoll(){
        return $this->fetchObject('me/skydrive/camera_roll');
    }

    /**
     * Fetches the "Documents" folder from the current OneDrive account.
     *
     * @return Folder The "Documents" folder, as a Folder instance referencing
     *                to the OneDrive "Documents" folder.
     */
    public function fetchDocs(){
        return $this->fetchObject('me/skydrive/my_documents');
    }

    /**
     * Fetches the "Pictures" folder from the current OneDrive account.
     *
     * @return Folder The "Pictures" folder, as a Folder instance referencing to
     *                the OneDrive "Pictures" folder.
     */
    public function fetchPics(){
        return $this->fetchObject('me/skydrive/my_photos');
    }

    /**
     * Fetches the "Public" folder from the current OneDrive account.
     *
     * @return Folder The "Public" folder, as a Folder instance referencing to
     *                the OneDrive "Public" folder.
     */
    public function fetchPublicDocs(){
        return $this->fetchObject('me/skydrive/public_documents');
    }

    /**
     * Fetches the properties of an object in the current OneDrive account.
     *
     * @return object The properties of the object fetched.
     */
    public function fetchProperties($objectId){
        if (null === $objectId) {
            $objectId = 'me/skydrive';
        }

        return $this->apiGet($objectId);
    }

    /**
     * Fetches the objects in a folder in the current OneDrive account.
     *
     * @return array The objects in the folder fetched, as Object instances
     *               referencing OneDrive objects.
     */
    public function fetchObjects($objectId){
        if (null === $objectId) {
            $objectId = 'me/skydrive';
        }

        $result   = $this->apiGet($objectId . '/files');
        $objects  = array();

        foreach ($result->data as $data) {
            $object = in_array($data->type, array('folder', 'album')) ?
                new Folder($this, $data->id, $data)
                : new File($this, $data->id, $data);

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Updates the properties of an object in the current OneDrive account.
     *
     * @param string       $objectId   The unique ID of the object to update.
     * @param array|object $properties The properties to update. Default:
     *                                 array().
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function updateObject($objectId, $properties = array()){
        $properties = (object) $properties;
        $encoded    = json_encode($properties);
        $stream     = fopen('php://memory', 'w+b');

        if (false === $stream) {
            throw new \Exception('fopen() failed');
        }

        if (false === fwrite($stream, $encoded)) {
            throw new \Exception('fwrite() failed');
        }

        if (!rewind($stream)) {
            throw new \Exception('rewind() failed');
        }

        $this->apiPut($objectId, $stream, 'application/json');
    }

    /**
     * Moves an object into another folder.
     *
     * @param string      The unique ID of the object to move.
     * @param null|string The unique ID of the folder into which to move the
     *                    object, or null to move it to the OneDrive root
     *                    folder. Default: null.
     */
    public function moveObject($objectId, $destinationId = null){
        if (null === $destinationId) {
            $destinationId = 'me/skydrive';
        }

        $this->apiMove($objectId, array(
            'destination' => $destinationId
        ));
    }

    /**
     * Copies a file into another folder. OneDrive does not support copying
     * folders.
     *
     * @param string      $objectId      The unique ID of the file to copy.
     * @param null|string $destinationId The unique ID of the folder into which
     *                                   to copy the file, or null to copy it to
     *                                   the OneDrive root folder. Default:
     *                                   null.
     */
    public function copyFile($objectId, $destinationId = null){
        if (null === $destinationId) {
            $destinationId = 'me/skydrive';
        }

        $this->apiCopy($objectId, array(
            'destination' => $destinationId
        ));
    }

    /**
     * Deletes an object in the current OneDrive account.
     *
     * @param string $objectId The unique ID of the object to delete.
     */
    public function deleteObject($objectId){
        $this->apiDelete($objectId);
    }

    /**
     * Fetches the quota of the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'quota'     (int) The total space, in bytes.
     *                  'available' (int) The available space, in bytes.
     */
    public function fetchQuota(){
        return $this->apiGet('me/skydrive/quota');
    }
    
    /**
     * Fetches the account info of the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'id'         (string) OneDrive account ID.
     *                  'first_name' (string) Account owner's first name.
     *                  'last_name'  (string) Account owner's last name.
     *                  'name'       (string) Account owner's full name.
     *                  'gender'     (string) Account owner's gender.
     *                  'locale'     (string) Account owner's locale.
     */
    public function fetchAccountInfo(){
        return $this->apiGet('me');
    }

    /**
     * Fetches the recent documents uploaded to the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'data' (array) The list of the recent documents uploaded.
     */
    public function fetchRecentDocs(){
        return $this->apiGet('me/skydrive/recent_docs');
    }

    /**
     * Fetches the objects shared with the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                    'data' (array) The list of the shared objects.
     */
    public function fetchShared(){
        return $this->apiGet('me/skydrive/shared');
    }
    
    
    public function upload_file($source_file, $target_file_path){
    	/*
    	 * @param string, string
    	 * @return none
    	 */
   		if (filesize($source_file)>$this->size_of_chunk){
   			//send piece by piece
   			$this->send_file_chunked($source_file, $target_file_path);
   		} else {
   			//send one time ll data
   			$handle_file = fopen($source_file, 'rb');
   			$this->createFile($target_file_path, null, $handle_file);
   		}
    } 

   public function send_file_chunked($source_file, $target_file_path){
   		/*
   		 * @param string, string
   		 * @return none
   		 */
   		//Create OneDrive Session Upload
		$one_session_data = $this->apiPost("drive/root:/" . urlencode($target_file_path) . ":/upload.createSession");
		$upload_url = $one_session_data->uploadUrl;
		
		$handle_file = fopen($source_file, 'rb');
		$file_size = filesize($source_file);
		$number_of_pieces = ceil($file_size / $this->size_of_chunk);
		
		for ($i = 1 ; $i <= $number_of_pieces; $i++){
			$start_byte = ($i-1) * $this->size_of_chunk;
			$end_byte = $i * $this->size_of_chunk - 1;
			if ($end_byte>$file_size-1){
				$end_byte = $file_size-1;
			}

			$upload_size = $end_byte - $start_byte + 1;
			fseek($handle_file, $start_byte);
			$headers = array(
				"Content-Length: $upload_size",
				"Content-Range: bytes $start_byte-$end_byte/".$file_size
			);	
			$this->apiPut($upload_url, $handle_file, FALSE, $upload_size, $headers);
		}
		fclose($handle_file);
	}
	
	public function return_all_files($objectId=-1){
		/*
		 * @param int
		 * @return array
		 */
		if (-1 === $objectId) {
			$objectId = 'me/skydrive';
		}
	
		$files = $this->apiGet($objectId . '/files');
	
		$i = 0;
		foreach ($files->data as $file) {
			$arr[$i]['id'] = $file->id;
			$arr[$i]['name'] = $file->name;
			$arr[$i]['size'] = $file->size;
			$i++;
		}
	
		return $arr;
	}
	
	public function get_file_meta_by_id($id){
		/*
		 * @param int
		 * @return array
		 */
		$files = $this->apiGet('me/skydrive/files');
		foreach ($files->data as $file) {
			//$file['source'] , direct download link
			//$file['upload_location']
			//$file['size']
			if (strcmp($file->id, $id)===0){
				return (array)$file;
			}
		}
		return array();
	}
	
	public function get_file_meta_by_name($name){
		/*
		 * @param string
		 * @return array
		 */
		$files = $this->apiGet('me/skydrive/files');
		foreach ($files->data as $file) {
			if (strcmp($file->name, $name)===0){
				return (array)$file;
			}
		}
		return array();
	}
	
	public function download_file($source='', $target='', $expected_size=0){
		/*
		 * @param string, string, float
		 * @return none
		 */
	
		if (file_exists($target)){
			unlink($target);
		}
		$file = new File($this, $source);
		$file_handler = fopen($target, 'a');
	
		if ($expected_size==0){
			$file_meta = $this->get_file_meta($source);
			$expected_size = $file_meta['size'];
		}
		$start_byte = 0;
		$end_byte = $start_byte + $this->size_of_chunk;
		if ($end_byte>$expected_size){
			$end_byte = $expected_size;
		}
	
		while ($start_byte < $expected_size){
			$options = array();
			$options[] = 'CURLOPT_RANGE: '.$start_byte . '-' . $end_byte;
				
			$chunk_of_data = $file->fetchContent($options);//get data from onedrive
			fwrite($file_handler, $chunk_of_data);//put data in file
				
			$start_byte = ftell($file_handler);//set the first byte of data
			$end_byte = $start_byte + $this->size_of_chunk;
			if ($end_byte>$expected_size){
				$end_byte = $expected_size;
			}
				
		}
		fclose($file_handler);
		return $target;
	}	
	
	public function generate_download_url($name){
		/*
		 * @param string
		 * @return string
		 */
		$file_meta = $this->get_file_meta_by_name($name);
		if (!empty($file_meta['upload_location'])){
			return $file_meta['upload_location'] . '?access_token=' . $this->_state->token->data->access_token;
		}
		return '';
	}
	
    
}
