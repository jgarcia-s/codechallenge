<?php

namespace JMGQ\AStar;

class SequencePrinter
{
    //private $terrainCost;
    private $map;
    private $sequence;
    private $emptyTileToken = '-';
    private $tileSize = 2;
    private $padToken = ' ';

    /**
     * @param Array $map
     * @param MyNode[] $sequence
     */
    public function __construct(Array $map, array $sequence)
    {
        //$this->terrainCost = $terrainCost;
      $this->map = $map;
        $this->sequence = $sequence;
    }

    /**
     * @return string
     */
    public function getEmptyTileToken()
    {
        return $this->emptyTileToken;
    }

    /**
     * @param string $emptyTileToken
     */
    public function setEmptyTileToken($emptyTileToken)
    {
        if (!is_string($emptyTileToken)) {
            throw new \InvalidArgumentException('Invalid empty tile token: ' . print_r($emptyTileToken, true));
        }

      
      
        $this->emptyTileToken = $emptyTileToken;
    }

    /**
     * @return int
     */
    public function getTileSize()
    {
        return $this->tileSize;
    }

    /**
     * @param int $tileSize
     */
    public function setTileSize($tileSize)
    {
        $naturalNumber = filter_var($tileSize, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

        if ($naturalNumber === false) {
            throw new \InvalidArgumentException('Invalid tile size: ' . print_r($tileSize, true));
        }

        $this->tileSize = $naturalNumber;
    }

    /**
     * @return string
     */
    public function getPadToken()
    {
        return $this->padToken;
    }

    /**
     * @param string $padToken
     */
    public function setPadToken($padToken)
    {
        if (!is_string($padToken)) {
            throw new \InvalidArgumentException('Invalid pad token: ' . print_r($padToken, true));
        }

        $this->padToken = $padToken;
    }

    public function printSequence()
    {
        $board = $this->generateEmptyBoard();

        $step = 1;
        foreach ($this->sequence as $node) {
            $board[$node->getRow()][$node->getColumn()] = $this->getTile($step);

            $step++;
        }

        $stringBoard = array();

        for($i = 0; $i<count($this->map); $i++){
            for($j = 0; $j<count($this->map[0]); $j++){
                if ($this->map[$i][$j] == '999'){
                    $board[$i][$j] = $this->getTile('X');
                }else if ($this->map[$i][$j] == '300'){
                    $board[$i][$j] = $this->getTile('A');
                }else if ($this->map[$i][$j] == '2000'){
                    $board[$i][$j] = $this->getTile('O');
                }

          }
        }
      
        foreach ($board as $row) {
            $stringBoard[] = implode('', $row);
        }

        //echo implode("\n", $stringBoard);
      return implode("\n", $stringBoard);
    }

    private function generateEmptyBoard()
    {
        $emptyTile = $this->getTile($this->getEmptyTileToken());

        //$emptyRow = array_fill(0, $this->terrainCost->getTotalColumns(), $emptyTile);
      
        $emptyRow = array_fill(0, count($this->map[0]), $emptyTile);

        //$board = array_fill(0, $this->terrainCost->getTotalRows(), $emptyRow);
      $board = array_fill(0, count($this->map), $emptyRow);

        return $board;
    }

    private function getTile($value)
    {
        return str_pad($value, $this->getTileSize(), $this->getPadToken(), STR_PAD_LEFT);
    }
}



