<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../DrawMargin.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianGreaterThanFactor.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianWithinFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\TrueSkill\DrawMargin;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianGreaterThanFactor;
use Moserware\Skills\TrueSkill\Factors\GaussianWithinFactor;

class TeamDifferencesComparisonLayer extends TrueSkillFactorGraphLayer
{
    private $_epsilon;
    private $_teamRanks;

    public function __construct(TrueSkillFactorGraph $parentGraph, array $teamRanks)
    {
        parent::__construct($parentGraph);
        $this->_teamRanks = $teamRanks;
        $gameInfo = $this->getParentFactorGraph()->getGameInfo();
        $this->_epsilon = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(), $gameInfo->getBeta());
    }

    public function buildLayer()
    {
        $inputVarGroups = $this->getInputVariablesGroups();
        $inputVarGroupsCount = count($inputVarGroups);

        for ($i = 0; $i < $inputVarGroupsCount; $i++)
        {
            $isDraw = ($this->_teamRanks[$i] == $this->_teamRanks[$i + 1]);
            $teamDifference = $inputVarGroups[$i][0];

            $factor =
                $isDraw
                    ? new GaussianWithinFactor($this->_epsilon, $teamDifference)
                    : new GaussianGreaterThanFactor($this->_epsilon, $teamDifference);

            $this->addLayerFactor($factor);
        }
    }
}

?>
