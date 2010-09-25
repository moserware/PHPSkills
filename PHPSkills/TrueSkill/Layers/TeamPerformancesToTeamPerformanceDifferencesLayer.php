<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianWeightedSumFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\FactorGraphs\Variable;
use Moserware\Skills\TrueSkill\DrawMargin;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianWeightedSumFactor;

class TeamPerformancesToTeamPerformanceDifferencesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph &$parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        $inputVariablesGroup = $this->getInputVariablesGroups();
        $inputVariablesGroupCount = count($inputVariablesGroup);

        for ($i = 0; $i < $inputVariablesGroupCount - 1; $i++)
        {
            $strongerTeam = $inputVariablesGroups[$i][0];
            $weakerTeam = $inputVariablesGroups[$i + 1][0];

            $currentDifference = $this->createOutputVariable();
            $this->addLayerFactor($this->createTeamPerformanceToDifferenceFactor($strongerTeam, $weakerTeam, currentDifference));

            // REVIEW: Does it make sense to have groups of one?
            $outputVariablesGroup = $this->getOutputVariablesGroups();
            $outputVariablesGroup[] = $currentDifference;
        }
    }

    private function createTeamPerformanceToDifferenceFactor(
        Variable &$strongerTeam, Variable &$weakerTeam, Variable &$output)
    {
        return new GaussianWeightedSumFactor($output, array($strongerTeam, $weakerTeam), array(1.0, -1.0));
    }

    private function createOutputVariable()
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable("Team performance difference");
    }
}

?>
