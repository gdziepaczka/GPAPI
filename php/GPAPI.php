<?php
/************
Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This licence is there: http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS /FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.
*************/

/**
* @author       GdziePaczka.pl
* @copyright    Copyright © 2012, GdziePaczka.pl
* @license      Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt
*/
class GPAPI
{
	/**
	* @desc Wersja
	*/
	const VERSION = '1.0';
	
	/**
	* @desc Zasób API
	*/
	protected $api = 'http://api.gdziepaczka.pl/package.php';
	
	/**
	* @desc Czas odpowiedzi
	*/
	protected $requestTimeout = 3;
	
	/**
	* @desc Możliwe kody błędu
	*/
	public $errorCodes = array(
		'COURIER_INVALID' => 'Niepoprawny identyfikator kuriera',
		'COURIER_MISSING' => 'Brak kuriera',
		'TOKEN_MISSING' => 'Brak tokena',
		'TOKEN_INVALID' => 'Niepoprawny token',
		'PACKAGE_MISSING' => 'Brak numeru paczki',
		'PACKAGE_INVALID' => 'Niepoprawny numer paczki',
		'PACKAGE_NOT_FOUND' => 'Paczka nie została znaleziona',
		'NO_PERMISSION' => 'Brak uprawnień do wykonania żądania',
	);
	
	/**
	* @desc Odpowiedź
	*/
	protected $response;
	
	/**
	* @desc Informacje o odpowiedzi
	*/
	protected $info;
	
	/**
	* @desc Inicjalizacja
	*/
	public function __construct()
	{
		if (!function_exists('curl_init'))
		{
			throw new GPAPIException('No CURL extension.');
		}

		if (!function_exists('json_decode'))
		{
			throw new GPAPIException('No JSON extension.');
		}
	}
	
	/**
	* @desc Zapytanie HTTP do API
	*
	* @param string 	$method 		Nazwa metody HTTP: 'GET', 'POST'
	* @param array 		$params			Parametry zapytania
	*
	* @return mixed
	*/
	public function request($method, $params = array())
	{
		$headers = array();
		$headers[] = 'User-Agent: GdziePaczkaAPI v'.self::VERSION;
		$headers[] = 'Accept-Charset: ISO-8859-2,utf-8;q=0.7,*;q=0.7';
		$headers[] = 'X-Request-Server: '.$_SERVER['SERVER_NAME'];
		$headers[] = 'X-Request-URI: '.$_SERVER['REQUEST_URI'];
		
		if (!in_array($method, array('GET', 'POST')))
		{
			throw new GPAPIException('Nieprawidłowa metoda');
		}
		
		if (empty($params))
		{
			throw new GPAPIException('Nieprawidłowe parametry');
		}
		
		$ch = curl_init();
		
		if ($method == 'POST')
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		}
		
		if ($method != 'POST')
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->requestTimeout);
		
		$this->url = ($method == 'POST') ? $this->api : $this->api.'?'.http_build_query($params);
		
		curl_setopt($ch, CURLOPT_URL, $this->url);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		
		if (defined('CURL_ENCODING'))
		{
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		}
		
		$this->response = curl_exec($ch);
		$this->info = curl_getinfo($ch);
		
		if ($this->response === false)
		{
			throw new GPAPIException('Nieprawidłowa odpowiedź na zapytanie');
		}
		
		$this->response = substr($this->response, $this->info['header_size']);
		
		return $this->parseResponse($this->response, $this->info['content_type']);
	}
	
	/**
	* @desc Translacja odpowiedzi do tablicy PHP w zależności od formatu
	*
	* @param string $response	Odpowiedź serwera
	* @param string $type		Format odpowiedzi serwera
	*
	* @return mixed
	*/
	private function parseResponse($response, $type)
	{
		switch ($type)
		{
			case 'text/xml':
				$parsedResponse = $this->parseXML($response);
				break;
			
			case 'application/json':
				$parsedResponse = $this->parseJSON($response);
				break;
		}
		
		return $parsedResponse;
	}
	
	/**
	* @desc Translacja XML do PHP
	*
	* @param string $input
	*/
	protected function parseXML($input)
	{
		$xml = @simplexml_load_string($input);
		
		if ($xml === false)
		{
			throw new GPAPIException('Nieprawidłowa odpowiedź w formacie XML');
		}
		
		return $this->xml2array($xml);
	}
	
	/**
	* @desc Translacja JSON do PHP
	*
	* @param string $input
	*/
	protected function parseJSON($input)
	{
		return json_decode($input, true);
	}
	
	/**
	* @desc Translacja obiektów SimpleXML do tablicy
	*
	* @param object $xml
	*/
	private function xml2array($xml)
	{
		if (is_object($xml))
		{
			if (get_class($xml) == 'SimpleXMLElement')
			{
				$attributes = $xml->attributes();
				
				foreach($attributes as $k => $v)
				{
					if ($v)
					{
						$a[$k] = (string) $v;
					}
				}
				
				$x = $xml;
				$xml = get_object_vars($xml);
			}
		}
		
		if (is_array($xml))
		{
			if (count($xml) == 0)
			{
				return (string)$x;
			}
			
			foreach($xml as $key => $value)
			{
				$r[$key] = $this->xml2array($value);
			}
			
			if (isset($a))
			{
				$r['@attributes'] = $a;
			}
			
			return $r;
		}
	
		return (string) $xml;
	}
}

class GPAPIException extends Exception {}
?>