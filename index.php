<?php



$recurso = $_GET['recurso'];
$jsonOutput = "Hola David :D";

if($recurso == 'name'){
	$jsonOutput = 
'{
    "name": "jona.garcia",
    "email": "jona.garcia@privalia.com"
}';
}else if($recurso == 'move'){
	//var_dump(json_decode(file_get_contents('php://input')));

	$body = json_decode(file_get_contents('php://input'));

	$playerX = $body->player->position->x;
	$playerY = $body->player->position->y;

	ob_start();
fwrite('woloasd', ob_get_contents());
	//var_dump($body);
	var_dump(ob_get_contents());
	var_dump($playerX);
 
	//apc_store('map', 'hola k tal', 60);
ob_end_flush();
	$jsonOutput = 
'{
    "move": "up"
}';
}

echo $jsonOutput;



?>