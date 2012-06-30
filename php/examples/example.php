<?php
require('../GPAPI.php');

try
{
	$package = new GdziePaczka(); // inicjalizacja klasy
	
	// tablica parametrow
	$params = array(
		'courier' => 'DHL', // identyfikator kuriera
		'package' => '1234567890', // numer listu przewozowego
		'token' => 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEE', // unikalny token
		'output' => 'xml', // typ zwracanej odpowiedzi
	);
	
	// wysy�anie ��dania
	$response = $package->request('POST', $params);
	
	// obs�uga b��d�w
	if (in_array($response, array_keys($package->errorCodes)))
	{
		die($response);
	}
	
	print_r($response); // wy�wietlenie tablicy ze szczeg�ami na temat paczki
}
catch(GPAPIException $e)
{
	echo $e->getMessage.'<br />'.$e->getTraceAsString();
}