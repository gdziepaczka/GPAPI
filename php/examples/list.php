<?php
require('GPAPI.php');

try
{
	$gpapi = new GPAPI(); // inicjalizacja klasy
 
	// tablica parametrow
	$params = array(
		'mode'		=> 'list', // nazwa metody
		'token' 	=> 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEE', // unikalny token
		'output' 	=> 'xml', // typ zwracanej odpowiedzi
	);
 
	// wysyłanie żądania
	$response = $gpapi->request('POST', $params);
 
	// obsługa błędów
	if (in_array($response['status'], array_keys($gpapi->errorCodes)))
	{
		die($gpapi->errorCodes[$response['status']]);
	}
 
	print_r($response); // wyświetlenie tablicy ze szczegółami na temat paczki
}
catch (GPAPIException $e) // przechwytywanie wyjątku
{
	echo $e->getMessage() . '<br />' . $e->getTraceAsString();
}
?>