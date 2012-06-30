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
	
	// wysy³anie ¿¹dania
	$response = $package->request('POST', $params);
	
	// obs³uga b³êdów
	if (in_array($response, array_keys($package->errorCodes)))
	{
		die($response);
	}
	
	print_r($response); // wyœwietlenie tablicy ze szczegó³ami na temat paczki
}
catch(GPAPIException $e)
{
	echo $e->getMessage.'<br />'.$e->getTraceAsString();
}