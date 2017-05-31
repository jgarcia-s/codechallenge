<?php
//phpinfo();
//exit();
namespace JMGQ\AStar;

include 'TerrainCost.php';
include 'MyAStar.php';
include 'MyNode.php';
include 'SequencePrinter.php';

$recurso = $_GET['recurso'];
$jsonOutput = "Hola David :D";

if($recurso == 'name'){
  $jsonOutput = 
'{
    "name": "jona.garcia",
    "email": "jona.garcia@privalia.com"
}';
}else if($recurso == 'move'){
/*phpinfo();
  exit();*/
//$mem  = new Memcached();
//'10.100.16.178'
//$mem->addServer('127.0.0.1',11211);


  
  $body = json_decode(file_get_contents('php://input'));
    $maze = $body->maze;

  $playerX = $body->player->position->x;
  $playerY = $body->player->position->y;
  $goalX = $maze->goal->x;
  $goalY = $maze->goal->y;


if (apcu_exists($body->game->id)) {
    $map = apcu_fetch($body->game->id);
    //var_dump($map);
} else {
    $map = array();
  
    for ($i = 0; $i < $maze->size->height; $i++){
      $row = array();
    for ($j = 0; $j < $maze->size->width; $j++){
          if(($j == 0) || ($j == ($maze->size->width - 1)) || ($i == 0) || ($i == ($maze->size->height - 1))){
              $row[] = 999;
          }else{
              $row[] = 1;
          }
    }
    $map[] = $row;
  }
}

foreach($maze->walls as $wall){
    $map[$wall->y][$wall->x] = 999;
}

//goal
$map[$maze->goal->y][$maze->goal->x] = 1;
//$map[6][3] = 999;
//$map[6][2] = 999;

//terrainCost[$row][$column] = 99
/*var_dump($map);
var_dump($goalX);
var_dump($goalY);
exit();*/

apcu_store($body->game->id, $map);

$terrainCost = new TerrainCost(
$map
);
  

$start = new MyNode($playerY, $playerX);
$goal = new MyNode($goalY, $goalX);

$aStar = new MyAStar($terrainCost);

$solution = $aStar->run($start, $goal);

$nextPositionY = $solution[1]->getRow();
$nextPositionX = $solution[1]->getColumn();


  
// Arriba + Arriba Der
if((($nextPositionY < $playerY) && ($nextPositionX == $playerX)) || (($nextPositionY < $playerY) && ($nextPositionX > $playerX))){
  if($map[$playerY-1][$playerX] == 999){
        $move = 'right';
    }else{
        $move = 'up';
    }

// Abajo + Abajo Izq
}else if((($nextPositionY > $playerY) && ($nextPositionX == $playerX)) || (($nextPositionY > $playerY) && ($nextPositionX < $playerX))){
    if($map[$playerY+1][$playerX] == 999){
        $move = 'left';
    }else{
    $move = 'down';
    }
// Izquierda + Arriba Izq
}else if((($nextPositionX < $playerX) && ($nextPositionY == $playerY)) || (($nextPositionX < $playerX) && ($nextPositionY < $playerY))){
    if($map[$playerY][$playerX-1] == 999){
        $move = 'up';
    }else{
    $move = 'left';
    }
// Derecha + Abajo Der
}else if((($nextPositionX > $playerX) && ($nextPositionY == $playerY)) || (($nextPositionX > $playerX) && ($nextPositionY > $playerY))){
    if($map[$playerY][$playerX+1] == 999){
        $move = 'down';
    }else{
    $move = 'right';
    }
}

$printer = new SequencePrinter($map, $solution);

$file = 'partida/map.txt';
// Write the contents back to the file
$wololo = $printer->printSequence();
  
file_put_contents($file, $wololo);



echo "\n";
  $jsonOutput = 
'{
    "move": "'.$move.'"
}';
}



echo $jsonOutput;



?>

