<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/FactorGraphLayer.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");

use Moserware\Skills\FactorGraphs\FactorGraphLayer;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;

abstract class TrueSkillFactorGraphLayer extends FactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }
}

?>
