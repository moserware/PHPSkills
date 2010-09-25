<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/FactorGraphLayer.php");

use Moserware\Skills\FactorGraphs\FactorGraphLayer;

abstract class TrueSkillFactorGraphLayer extends FactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }
}

?>
