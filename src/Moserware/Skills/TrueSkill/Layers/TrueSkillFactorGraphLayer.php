<?php
namespace Moserware\Skills\TrueSkill\Layers;

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
