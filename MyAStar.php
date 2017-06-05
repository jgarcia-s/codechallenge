<?php

namespace JMGQ\AStar;

include 'Algo/AStar.php';
include 'Algo/Node.php';

class MyAStar extends AStar
{
    private $terrainCost;

    public function __construct(TerrainCost $terrainCost)
    {
        $this->terrainCost = $terrainCost;
    }

    /**
     * @inheritdoc
     */
    public function generateAdjacentNodes(Node $node)
    {
        $adjacentNodes = array();

        $myNode = MyNode::fromNode($node);

        if ($myNode->getRow() == 0) {
            $startingRow = 0;
        } else {
            $startingRow = $myNode->getRow() - 1;
        }

        if ($myNode->getRow() == $this->terrainCost->getTotalRows() - 1) {
            $endingRow = $myNode->getRow();
        } else {
            $endingRow = $myNode->getRow() + 1;
        }

        if ($myNode->getColumn() == 0) {
            $startingColumn = 0;
        } else {
            $startingColumn = $myNode->getColumn() - 1;
        }

        if ($myNode->getColumn() == $this->terrainCost->getTotalColumns() - 1) {
            $endingColumn = $myNode->getColumn();
        } else {
            $endingColumn = $myNode->getColumn() + 1;
        }

        for ($row = $startingRow; $row <= $endingRow; $row++) {
            for ($column = $startingColumn; $column <= $endingColumn; $column++) {
                $adjacentNode = new MyNode($row, $column);

                if ($adjacentNode->getID() !== $myNode->getID()) {
                    $adjacentNodes[] = $adjacentNode;
                }
            }
        }

        return $adjacentNodes;
    }

    /**
     * @inheritdoc
     */
    public function calculateRealCost(Node $node, Node $adjacent)
    {
        $myStartNode = MyNode::fromNode($node);
        $myEndNode = MyNode::fromNode($adjacent);

        switch($this->areAdjacent($myStartNode, $myEndNode)){
            case 1:
                $cost = $this->terrainCost->getCost($myEndNode->getRow(), $myEndNode->getColumn());
                return $cost - 0.01;
                break;
            case 2:
                $cost = $this->terrainCost->getCost($myEndNode->getRow(), $myEndNode->getColumn());
                return $cost;
                break;
            case 3:
                return TerrainCost::INFINITE;
                break;
        }


            /*if((abs($myStartNode->getRow() - $myEndNode->getRow()) == 1) && (abs($myStartNode->getColumn() - $myEndNode->getColumn()) == 1))
            {
              return ($cost - 0.01);
              //TerrainCost::INFINITE;
            }else{
              return $cost;
            }*/
        
    }

    /**
     * @inheritdoc
     */
    public function calculateEstimatedCost(Node $start, Node $end)
    {
        $myStartNode = MyNode::fromNode($start);
        $myEndNode = MyNode::fromNode($end);

        $rowFactor = pow($myStartNode->getRow() - $myEndNode->getRow(), 2);
        $columnFactor = pow($myStartNode->getColumn() - $myEndNode->getColumn(), 2);

        $euclideanDistance = sqrt($rowFactor + $columnFactor);

        return $euclideanDistance;
    }

    private function areAdjacent(MyNode $a, MyNode $b)
    {
        $diffRow = abs($a->getRow() - $b->getRow());
        $diffColumn = abs($a->getColumn() - $b->getColumn());
      
        if(($diffRow == 1 && $diffColumn == 0) || ($diffRow == 0 && $diffColumn == 1)){
            return 1;
        }else if($diffRow == 1 && $diffColumn == 1){
            return 2;
        }else{
            return 3;
        }
      
        //return abs($a->getRow() - $b->getRow()) <= 1 && abs($a->getColumn() - $b->getColumn()) <= 1;
    }
}


