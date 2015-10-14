<?php

namespace Skills\TrueSkill\Layers;

use Skills\FactorGraphs\FactorGraphLayer;
use Skills\TrueSkill\TrueSkillFactorGraph;

abstract class TrueSkillFactorGraphLayer extends FactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }
}

?>
