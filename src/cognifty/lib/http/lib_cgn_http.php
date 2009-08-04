<?php

class Cgn_Http_Connection {

	var $headers = array();
	var $method = 'GET';
	var $body =  '';
	var $host = 'example.com';
	var $url = '/test.html';
	var $scheme = '';
	var $port = 80;
	var $_version = 'HTTP/1.0';

	var $_response;
	var $responseHeaders = array();
	var $responseBody = '';
	var $responseStatus = -1;


	function Cgn_Http_Connection($host, $url, $scheme='http', $port=80) {
		$this->host = $host;
		$this->url = $url;
		//only http and https are supported
		//take https:// or ssl://
		if (strstr($scheme,'s') !== false) {
			$this->useSsl(true);
		}
		$this->port = $port;
		$this->setHeader('User-Agent','php '.phpversion());
	}


	function setHeader($k,$v) {
		$this->headers[$k] = $v;
	}


	function setHeaders($a) {
		$this->headers = array_merge($this->headers,$a);
	}


	function setUrl($u) {
		$this->url = $u;
	}


	function setBody($b) {
		$this->body = $b;
	}


	function addToBody($b) {
		$this->body .= $b;
	}


	function setMethod($m) {
		if (strtolower($m) == 'post') {
			$this->method = 'POST';
			return true;
		} else if (strtolower($m) == 'get') {
			$this->method = 'GET';
			return true;
		}
		return false;
	}


	function useSsl($ssl=true) {
		if ($ssl) {
			$this->scheme = 'ssl://';
		} else {
			$this->scheme = '';
		}
	}


	/**
	 * do post or get
	 */
	function fetch() {
		if ($this->method == 'POST' ) { 
			return $this->doPost();
		} else {
			return $this->doGet();
		}
	}


	/**
	 * perform a post
	 */
	function doPost() {
		$errno = -1;
		$errstr = '';
		$fp = fsockopen($this->scheme.$this->host, $this->port, $errno, $errstr, 15);

		if (!$fp) {
			return false;
		}
//$d = fopen('/tmp/http_ouput.txt','w+');
		// posting to (ssl://testefsnet.concordebiz.com/EFSnet.dll)
		//XXX FIXME use proper version string for HTTP
		fputs($fp, "POST ".$this->url." HTTP/1.0\n");
//fputs($d, "\n\nPOST ".$this->url." HTTP/1.0\n");
		foreach($this->headers as $k =>$v) {
			fputs($fp, $k.": ".$v."\n");
//fputs($d, $k.": ".$v."\n");
		}
		fputs($fp, "Host: ".$this->host."\n");
		fputs($fp, "Content-Length: ".strlen($this->body)."\n");
		fputs($fp, "\n");
		fwrite($fp, $this->body);
//fputs($d, "Host: ".$this->host."\n");
//fputs($d, "Content-Length: ".strlen($this->body)."\n");
//fputs($d, "\n");
//fwrite($d, $this->body);
//fputs($d, "\n***response***\n");



		//IIS servers do not do https correctly (surprised?)
		//turn off warnings for the time being
		$olderr = ini_get('error_reporting');
		ini_set('error_reporting',E_ALL &~ E_NOTICE &~ E_WARNING);

		$finishedReading = false;
		$finishedHeaders = false;
		while (!$finishedReading) {
			$meta = stream_get_meta_data($fp);
			if ($meta['eof'] == 1) { break; }
		// save the response from efsnet.
			$buffer = fgets($fp, 4096);
//fputs($d, " *** READ 4096 bytes\n");
//fputs($d, "finished reading = ".$finishedReading."\n");
//fputs($d, $buffer);
			if (trim($buffer) == '') {
				$finishedHeaders = true;
				continue;
			}
			if ($finishedHeaders) {
				$this->responseBody .= $buffer;
			} else {
				//find the response code
				$header = explode(': ',$buffer);
				if (strstr($header[0],'HTTP') !== false ) {
					$this->responseStatus = $header[0];
				} else {
					$this->responseHeaders[$header[0]] = $header[1];
				}
			}
			$status = socket_get_status($fp);
			$finishedReading = $status['eof'] > 0 ? true:false;
		}
//fclose($d);
		fclose($fp);
		ini_set('error_reporting',$olderr);
		return true;
	}


	/**
	 * perform a get
	 */
	function doGet() {
		$errno = -1;
		$errstr = '';
		$fp = fsockopen($this->scheme.$this->host, $this->port, $errno, $errstr, 15);
		stream_set_timeout($fp,7);
		stream_set_blocking($fp, TRUE );

		if (!$fp) {
			return false;
		}
//$d = fopen('/tmp/http_ouput.txt','a+');
		// posting to (ssl://testefsnet.concordebiz.com/EFSnet.dll)
		//XXX FIXME use proper version string for HTTP
		fputs($fp, "GET ".$this->url." HTTP/1.0\n");
//fputs($d, "\n\nGET ".$this->url." HTTP/1.0\n");
		foreach($this->headers as $k =>$v) {
			fputs($fp, $k.": ".$v."\n");
//fputs($d, $k.": ".$v."\n");
		}
		fputs($fp, "Host: ".$this->host."\n");
		fputs($fp, "Content-Length: ".strlen($this->body)."\n");
		fputs($fp, "\n");
		/*
fputs($d, "Host: ".$this->host."\n");
fputs($d, "Content-Length: ".strlen($this->body)."\n");
fputs($d, "\n");
fputs($d, "\n***response***\n");
		 */


		//IIS servers do not do https correctly (surprised?)
		//turn off warnings for the time being
		$olderr = ini_get('error_reporting');
		ini_set('error_reporting',E_ALL &~ E_NOTICE &~ E_WARNING);

		$finishedReading = false;
		$finishedHeaders = false;
		$bytesToRead = 1024;
		while (!$finishedReading) {
		// save the response from efsnet.
			if ($bytesToRead == 0 ) { $bytesToRead = 1024; }
			$meta = stream_get_meta_data($fp);
			if ($meta['eof'] == 1) { break; }
			if ($finishedHeaders) {
				$buffer = fread($fp, $bytesToRead);
			} else {
				$buffer = fgets($fp, 4096);
			}

			if (trim($buffer) == '') {
				$finishedHeaders = true;
				continue;
			}
			if ($finishedHeaders) {
				$this->responseBody .= $buffer;
			} else {
				//find the response code
				$header = explode(': ',$buffer);
				if (strstr($header[0],'HTTP') !== false ) {
					$this->responseStatus = $header[0];
				} else {
					$this->responseHeaders[$header[0]] = $header[1];
				}
			}
			$status = socket_get_status($fp);
			$finishedReading = $status['eof'] > 0 ? true:false;
			//this helps to shorten the time it takes to realize that there's no body
			// with this HTTP connection.  fread() doesn't wait to time out like fgets()
			 /*
			if ($bytesToRead < 10 && isset($this->responseHeaders['Content-Length']) 
				&& intval($this->responseHeaders['Content-Length']) == 0 ){
					$finishedHeaders = true;
				}
			  */
		}
//fclose($d);
		fclose($fp);
		ini_set('error_reporting',$olderr);
		return true;
	}
}
?>
