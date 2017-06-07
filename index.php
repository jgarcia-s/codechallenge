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

  $body = json_decode(file_get_contents('php://input'));
    $maze = $body->maze;

  $playerX = $body->player->position->x;
  $playerY = $body->player->position->y;
  $goalX = $maze->goal->x;
  $goalY = $maze->goal->y;


  if (apcu_exists('map-'.$body->game->id)) {
      $map = apcu_fetch('map-'.$body->game->id);
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
  
  if (apcu_exists('g-'.$body->game->id)) {
    $logG = apcu_fetch('g-'.$body->game->id);
    $areaG = apcu_fetch('area-'.$body->game->id);
  }else{
    $logG = array();
    $areaG = null;
  }

  foreach($maze->walls as $wall){
      $map[$wall->y][$wall->x] = 999;
  }

  //goal
  $map[$maze->goal->y][$maze->goal->x] = 1;

  apcu_store('map-'.$body->game->id, $map);
  
  //Marcamos los fantasmas
  $ghosts = $body->ghosts;

  //Este será el mapa que guardaremos para la siguiente iteración
  $tmpLogG = array();
  for ($i = $body->player->area->y1; $i <= $body->player->area->y2; $i++){
        $row = array();
        for ($j = $body->player->area->x1; $j <= $body->player->area->x2; $j++){
            $row[$j] = 0;
        }
        $tmpLogG[$i] = $row;
  }
  
  //var_dump($tmpLogG);
  
  foreach($ghosts as $ghost){
    //var_dump($ghost);

    //Comprobamos si es un fantasma nuevo
    /*for ($i = 1; $i < count($logG); $i++){
        $row = array();
        for ($j = 1; $j < count($logG[0]); $j++){
            $nuevo = true;
            if($logG[][])
        }
      }*/
    
    //var_dump($logG);
    
    if(!is_null($areaG)){
    
      if(($ghost->y > $areaG->y1) && ($ghost->y < $areaG->y2)){

        if(($ghost->x > $areaG->x1) && ($ghost->x < $areaG->x2)){
//var_dump($ghost);
          //Miramos si lo tenemos fichado
          $nuevo = max(
                  $logG[$ghost->y-1][$ghost->x],
                  $logG[$ghost->y+1][$ghost->x],
                  $logG[$ghost->y][$ghost->x-1],
                  $logG[$ghost->y][$ghost->x+1]
          );
          
          $tmpLogG[$ghost->y][$ghost->x] = $nuevo + 1;
          
          //Este muerde
          if($nuevo > 1){
            $map[$ghost->y][$ghost->x] = 300;
            $map[$ghost->y-1][$ghost->x] = 300;
            $map[$ghost->y][$ghost->x-1] = 300;
            $map[$ghost->y+1][$ghost->x] = 300;
            $map[$ghost->y][$ghost->x+1] = 300;
          }
        }
      }
    }
  }

  //Comprobamos que no tenemos fantasmas cerca
    /*for($i = $playerY-1; $i <= $playerY+1; $i++){
          for($j = $playerX-1; $j <= $playerX+1; $j++){
              if($map[$i][$j] > 299){
                  $map[$i][$j] = 999;
              }
          }
    }*/

    //Cambiar los 2000 si metemos porcentajes
    if($map[$playerY-1][$playerX] == 300) $map[$playerY-1][$playerX] = 2000;
    if($map[$playerY][$playerX-1] == 300) $map[$playerY][$playerX-1] = 2000;
    if($map[$playerY+1][$playerX] == 300) $map[$playerY+1][$playerX] = 2000;
    if($map[$playerY][$playerX+1] == 300) $map[$playerY][$playerX+1] = 2000;

  $terrainCost = new TerrainCost(
  $map
  );


  $start = new MyNode($playerY, $playerX);
  $goal = new MyNode($goalY, $goalX);

  $aStar = new MyAStar($terrainCost);

  $solution = $aStar->run($start, $goal);

  $nextPositionY = $solution[1]->getRow();
  $nextPositionX = $solution[1]->getColumn();

  function calcularDistanciaMuro($map, $actualY, $nextY, $actualX, $nextX, $goalX, $goalY){

      $countY = 0;
      $countX = 0;
      $tmpY = $actualY;
      $tmpX = $actualX;
      $notFound = true;
      while($notFound){
          $tmpY += $nextY;
          $tmpX += $nextX;

          $mapY = $map[$tmpY][$actualX];
          $mapX = $map[$actualY][$tmpX];

          if(($mapY == 999) && ($mapX == 999)){
            //echo "entramos 1";
            //exit();
              return distToGoal($actualY, $actualX, $goalY, $goalX);
          }else if($mapY == 999){
            //echo "entramos 2";
              return 'X';
          }else if($mapX == 999){
            //echo "entramos 3";
              return 'Y';
          }
      }
  }

  function distToGoal($actualY, $actualX, $goalY, $goalX){
      if (abs($actualY - $goalY) > abs($actualX - $goalX)){
          return 'Y';
      }else{
          return 'X';
      }
  }

  // Arriba
  if(($nextPositionY < $playerY) && ($nextPositionX == $playerX)){
    $move = 'up';

  // Abajo
  }else if(($nextPositionY > $playerY) && ($nextPositionX == $playerX)){
    $move = 'down';

  // Izquierda
  }else if(($nextPositionX < $playerX) && ($nextPositionY == $playerY)){
    $move = 'left';

  // Derecha
  }else if(($nextPositionX > $playerX) && ($nextPositionY == $playerY)){
    $move = 'right';




  // Arriba Der
  }else if(($nextPositionY < $playerY) && ($nextPositionX > $playerX)){

      //Si no hay fantasmas
      if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY][$playerX+1] != 2000)){
          $res = calcularDistanciaMuro($map, $playerY, -1, $playerX, +1, $maze->goal->y, $maze->goal->x);

          if($res == 'Y'){
              $move = 'up';
          }else{
              $move = 'right';
          }
        //Si hay fantasmas o muros en ambas direcciones
        }else if(($map[$playerY-1][$playerX] > 998) && ($map[$playerY][$playerX+1] > 998)){
          $res = distToGoal($playerY, $playerX, $maze->goal->y, $maze->goal->x);
          if($res == 'Y'){
              //Si no es muro ni fantasma
              if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY+1][$playerX] != 999)){
                $move = 'down';
              }else{
                if(($map[$playerY][$playerX-1] != 2000) && ($map[$playerY][$playerX-1] != 999)){
                  $move = 'left';
                }else{
                  $move = 'no';
                }
              }
          }else{
              //Si no es muro ni fantasma
              if(($map[$playerY][$playerX-1] != 2000) && ($map[$playerY][$playerX-1] != 999)){
                $move = 'left';
              }else{
                if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY+1][$playerX] != 999)){
                  $move = 'down';
                }else{
                  $move = 'no';
                }
              }
          }
          
        //Fantasma Arriba?
        }else if($map[$playerY-1][$playerX] > 998){
          $move = 'right';
        //Fantasma Derecha?
        }else if($map[$playerY][$playerX+1] > 998){
            $move = 'up';
        }else{
            //Nunca debería de llegar aquí
      $move = 'no2';
        }

  // Abajo Izq
  }else if(($nextPositionY > $playerY) && ($nextPositionX < $playerX)){


      //Si no hay fantasmas
      if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY][$playerX-1] != 2000)){
          $res = calcularDistanciaMuro($map, $playerY, +1, $playerX, -1, $maze->goal->y, $maze->goal->x);

          if($res == 'Y'){
              $move = 'down';
          }else{
              $move = 'left';
          }
        //Si hay fantasmas o muros en ambas direcciones
        }else if(($map[$playerY+1][$playerX] > 998) && ($map[$playerY][$playerX-1] > 998)){
          $res = distToGoal($playerY, $playerX, $maze->goal->y, $maze->goal->x);
          if($res == 'Y'){
              //Si no es muro ni fantasma
              if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY-1][$playerX] != 999)){
                $move = 'up';
              }else{
                if(($map[$playerY][$playerX+1] != 2000) && ($map[$playerY][$playerX+1] != 999)){
                  $move = 'right';
                }else{
                  $move = 'no';
                }
              }
          }else{
              //Si no es muro ni fantasma
              if(($map[$playerY][$playerX+1] != 2000) && ($map[$playerY][$playerX+1] != 999)){
                $move = 'right';
              }else{
                if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY-1][$playerX] != 999)){
                  $move = 'up';
                }else{
                  $move = 'no';
                }
              }
          }
          
        //Fantasma Abajo?
        }else if($map[$playerY+1][$playerX] > 998){
          $move = 'left';
        //Fantasma Izq?
        }else if($map[$playerY][$playerX-1] > 998){
            $move = 'down';
        }else{
            //Nunca debería de llegar aquí
      $move = 'no2';
        }

  // Arriba Izq
  }else if(($nextPositionX < $playerX) && ($nextPositionY < $playerY)){
    
          //Si no hay fantasmas
      if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY][$playerX-1] != 2000)){
          $res = calcularDistanciaMuro($map, $playerY, -1, $playerX, -1, $maze->goal->y, $maze->goal->x);

          if($res == 'Y'){
              $move = 'up';
          }else{
              $move = 'left';
          }
        //Si hay fantasmas o muros en ambas direcciones
        }else if(($map[$playerY-1][$playerX] > 998) && ($map[$playerY][$playerX-1] > 998)){
          $res = distToGoal($playerY, $playerX, $maze->goal->y, $maze->goal->x);
          if($res == 'Y'){
              //Si no es muro ni fantasma
              if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY+1][$playerX] != 999)){
                $move = 'down';
              }else{
                if(($map[$playerY][$playerX+1] != 2000) && ($map[$playerY][$playerX+1] != 999)){
                  $move = 'right';
                }else{
                  $move = 'no';
                }
              }
          }else{
              //Si no es muro ni fantasma
              if(($map[$playerY][$playerX+1] != 2000) && ($map[$playerY][$playerX+1] != 999)){
                $move = 'right';
              }else{
                if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY+1][$playerX] != 999)){
                  $move = 'down';
                }else{
                  $move = 'no';
                }
              }
          }
          
        //Fantasma Arriba?
        }else if($map[$playerY-1][$playerX] > 998){
          $move = 'left';
        //Fantasma Izq?
        }else if($map[$playerY][$playerX-1] > 998){
            $move = 'up';
        }else{
            //Nunca debería de llegar aquí
      $move = 'no2';
        }

  // Abajo Der
  }else if(($nextPositionX > $playerX) && ($nextPositionY > $playerY)){

          //Si no hay fantasmas
      if(($map[$playerY+1][$playerX] != 2000) && ($map[$playerY][$playerX+1] != 2000)){
          $res = calcularDistanciaMuro($map, $playerY, +1, $playerX, +1, $maze->goal->y, $maze->goal->x);

          if($res == 'Y'){
              $move = 'down';
          }else{
              $move = 'right';
          }
        //Si hay fantasmas o muros en ambas direcciones
        }else if(($map[$playerY+1][$playerX] > 998) && ($map[$playerY][$playerX+1] > 998)){
          $res = distToGoal($playerY, $playerX, $maze->goal->y, $maze->goal->x);
          if($res == 'Y'){
              //Si no es muro ni fantasma
              if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY-1][$playerX] != 999)){
                $move = 'up';
              }else{
                if(($map[$playerY][$playerX-1] != 2000) && ($map[$playerY][$playerX-1] != 999)){
                  $move = 'left';
                }else{
                  $move = 'no';
                }
              }
          }else{
              //Si no es muro ni fantasma
              if(($map[$playerY][$playerX-1] != 2000) && ($map[$playerY][$playerX-1] != 999)){
                $move = 'left';
              }else{
                if(($map[$playerY-1][$playerX] != 2000) && ($map[$playerY-1][$playerX] != 999)){
                  $move = 'up';
                }else{
                  $move = 'no';
                }
              }
          }
          
        //Fantasma Abajo?
        }else if($map[$playerY+1][$playerX] > 998){
          $move = 'right';
        //Fantasma Derecha?
        }else if($map[$playerY][$playerX+1] > 998){
            $move = 'down';
        }else{
            //Nunca debería de llegar aquí
      $move = 'no2';
        }
  }
       
  //Guardamos los fantasmas dentro del area
  apcu_store('g-'.$body->game->id, $tmpLogG);
       
  //Guardamos el area
  apcu_store('area-'.$body->game->id, $body->player->area);

  $printer = new SequencePrinter($map, $solution);

  $file = 'partida/map.txt';
  // Write the contents back to the file
  $wololo = $printer->printSequence();

  file_put_contents($file, $wololo);



  //echo "\n";
    $jsonOutput = 
  '{
    "move": "'.$move.'"
}';
}



echo $jsonOutput;



?>