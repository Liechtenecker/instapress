<?php

require_once ('Instagram.php');

class Instagram_XAuth extends Instagram 
{

	/**
	 * @param $errorCode &string Pointer to a string which will store an error message if authorization was not successful
	 */
	public function getAccessToken($errorMsg = null) 
	{
		// If there is no token yet, try getting an access token via xAuth-Configuration
		if(!$this->_accessToken)
		{
	        $this->_initHttpClient($this->_config['site_url'], Zend_Http_Client::POST);
	        $this->_setHttpClientPostParam('client_id', $this->_config['client_id']);
        	$this->_setHttpClientPostParam('client_secret', $this->_config['client_secret']);
	        $this->_setHttpClientPostParam('username', $this->_config['username']);
	        $this->_setHttpClientPostParam('password', $this->_config['password']);
	        $this->_setHttpClientPostParam('grant_type', $this->_config['grant_type']);
	        
	        // Get HTTP Response
	        $json = $this->_getHttpClientResponse();
	        
	        if($json)
	        {
	        	// Decode Response
	        	$response = json_decode($json);
	        	
	        	// if reponse was successful
	        	if($response->access_token)
	        	{
	        		$this->_accessToken = $response->access_token;
	        	}
	        	// An error occured
	        	else if($response->error_message)
	        	{
	        		$errorMsg = $response->error_message;
	        		$this->_accessToken = null;
	        	}
	        }
		}
        
        return $this->_accessToken;
    }
	
}

?>