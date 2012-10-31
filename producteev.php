<?php

// ini_set('display_errors','On');

// session_start();

if (! function_exists('json_decode')) 
	{
	echo "You need json_decode() to use this API.";
	exit;
	}

class producteev_api
	{
	
	/***********
	PRODUCTEEV CONFIG
	************/
	
	function __construct()
		{
		// CONFIG your API here
		
		// your API Key
		$this->apiKey 			= '8f7c2bedf50556dc47a1ed6225ed9ec5';	
		
		// your API secret
		$this->apiSecret 		= '1260e3cc4844db33ec55cee5f03dbb7e';	
		
		// API end point URL
		$this->apiUrl 			= 'https://api.producteev.com/';
		
		// GET is not handled by this script so far
		$this->defaultMethod 	= 'POST';	
		
		}
	
	
	/***********
	PRODUCTEEV API METHODS
	************/
	
	function serverTime()
		{
		return $this->fetch('time');
		}
		
	function loginUser($email, $password)
		{
		$params = array('email' => $email, 'password' => $password);
		
		$response = $this->fetch('users/login', $params);
		
		// Token management
		if(is_array($response))
			{
			if($response['body']['login']['token'] != '')
				$this->setToken($response['body']['login']['token']);
			else
				$this->error("The connection attempt has failed ! <br>Error : ".$response['body']['error']['message']);
			}
		else
			$this->error("loginUser() return => Expecting Array, got : $response");
		
		return $response;
		}
		
	function tasksList($params = array())
		{
		if(!$this->getToken())
			return false;
		
		$params['token'] = $this->getToken();
		
		$response = $this->fetch('tasks/show_list', $params);
		
		if($response['body']['error'] != '')
			$this->error("The tasks list has not been donwloaded ! <br>Error : ".$response['body']['error']['message']);
			
		return $response['body']['tasks'];
		}
		
	function getTask($id_task)
		{
		if(!$this->getToken())
			return false;
		
		if(is_array($id_task))
			$params = $id_task;
		else
			{
			$params = array('id_task' => $id_task);
			}
		
		$params['token'] = $this->getToken();
		
		$response = $this->fetch('tasks/view', $params);
		
		// récupération du token
		if($response['body']['error'] != '')
			$this->error("The task has not been retrieved ! <br>Error : ".$response['body']['error']['message']);
			
		return $response['body']['task'];
		}
		
	function createTask($params = array())
		{
		if(!$this->getToken())
			return false;
			
		$params['token'] = $this->getToken();
		
		$response = $this->fetch('tasks/create', $params);
		
		// récupération du token
		if($response['body']['error'] != '')
			$this->error("The task has not been created ! <br>Error : ".$response['body']['error']['message']);
			
		return $response['body']['task'];
		}
		
	function deleteTask($id_task)
		{
		if(!$this->getToken())
			return false;
			
		$params['token'] = $this->getToken();
		$params['id_task'] = $id_task;
		
		$response = $this->fetch('tasks/delete', $params);
		
		// récupération du token
		if($response['body']['error'] != '')
			$this->error("The task has not been deleted ! <br>Error : ".$response['body']['error']['message']);
		
		return $response;
		}
	
	
	/***********
	Token management
	***********/
	
	// Used to store the token
	function setToken($token)
		{
		global $_SESSION;
		
		$this->token = $token;
		
		$_SESSION['producteev_token'] = $token;
		}
		
	// Used to retrieve the token
	function getToken()
		{
		global $_SESSION;
		
		if($this->token != '')
			return $this->token;
		
		if($_SESSION['producteev_token'] != '')
			return $_SESSION['producteev_token'];	

		return false;
		}
		
	
	
	/***********
	REQUEST management
	***********/
	
	function fetch($call, array $additional = array(), $url = false, $method = false)
		{
		if($method === false || $method == '')
			$method = $this->defaultMethod;
			
		if($url === false || $url == '')
			$url = $this->apiUrl;
		
		// Default parameters for each request
		$params_array = array();
		$params_array['api_key'] = $this->apiKey;
		
		if($this->getToken())
			$params_array['token'] = $this->getToken();
		
		$params_array = array_merge($params_array, $additional);
		
		$api_sig = $this->generate_signature($params_array);
		
		$encoded_params = '';
		foreach($params_array as $k => $v)
			{
			if($encoded_params != '')
				$encoded_params .= '&';
				
			$encoded_params .= "$k=".rawurlencode($v);
			}
			
		$url_params = $encoded_params.'&api_sig='.$api_sig;
		
		// construct the url request
		$request = $url.$call.'.json?'.$url_params;
        
		// Initialise and execute a cURL request
        $handle = curl_init();
        
		// Todo : manage GET method
		$options = array(
			CURLOPT_POST => 1, 
			CURLOPT_HEADER => 1, 
			CURLOPT_URL => $request, 
			CURLOPT_FRESH_CONNECT => 1, 
			CURLOPT_RETURNTRANSFER => 1, 
			CURLOPT_FORBID_REUSE => 1, 
			CURLOPT_TIMEOUT => 4,
			CURLOPT_SSL_VERIFYPEER => false
			);
			
		// Set the cURL options at once
        curl_setopt_array($handle, $options);
        
        // Execute and parse the response
       if(!$response = curl_exec($handle)) 
			trigger_error(curl_error($handle)); 
				
		curl_close($handle);
        
		// Parse the response if it is a string
        if(is_string($response)) 
		    $response = $this->parse($response);
			
		return $response;
		}
		
	private function parse($response)
		{
		list($headers, $response) = explode("\r\n\r\n", $response, 2);
		
		$lines = explode("\r\n", $headers);
		
		if (preg_match('#^HTTP/1.1 100#', $lines[0])) 
			{
			list($headers, $response) = explode("\r\n\r\n", $response, 2);
			$lines = explode("\r\n", $headers);
			}
		
		// Get the HTTP response code from the first line
		$first 			= array_shift($lines);
		$pattern 		= '#^HTTP/1.1 ([0-9]{3})#';
		preg_match($pattern, $first, $matches);
		$code = $matches[1];
		
		// Parse the remaining headers into an associative array
		$headers = array();
		foreach ($lines as $line) 
			{
			list($k, $v) = explode(': ', $line, 2);
			$headers[strtolower($k)] = $v;
			}
		
		// If the response body is not a JSON encoded string
		// we'll return the entire response body
		if (!$body = json_decode($response)) 
			$body = $response;
		
		return array('code' => $code, 'body' => $body, 'headers' => $headers);
		}
		
	
	
	
	/*
	* Generate a signature using the application secret key.
	*
	* @param $params_array   an array of parameters,
	*                        NOT INCLUDING the signature itself
	* @param $secret         your app's secret key
	*
	* @return a hash of the signature
	*/
	public function generate_signature($params_array) 
		{
		$str = '';          
		
		//Note: make sure that the signature parameter is not already included in $params_array
		ksort($params_array);     
		
		foreach ($params_array as $k=>$v) 
			if(is_scalar($v)) 
				$str .= "$k$v";                                                                                                                             
			
		$str .= $this->apiSecret;                                                                                                                                                
		
		return md5($str);                                                                                                                                               
		}
	
	/***********
	IHM management
	***********/
	
	// Error management
	function error($error)
		{
		echo $error;
		exit;
		}
		
	// EDIT this function in order to create your own form to ask for email and password to connect the user
	function whoAreYou()
		{
		if($this->getToken())
			return ;
			
		echo "<div id='dialog-connexion-producteev' style='display: none;' title='Producteev log in'>
				<form>
					<table>
						<tr>
							<td>Email address :</td>
							<td><input type='text' id='producteev_login' /></td>
						</tr>
						<tr>
							<td>Password :</td>
							<td><input type='password' id='producteev_password' /></td>
						</tr>
					</table>
				</form>
			</div>";
		}
	
	}
	

?>