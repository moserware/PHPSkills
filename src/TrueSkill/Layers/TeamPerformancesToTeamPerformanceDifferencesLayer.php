<?php
namespace Skills\TrueSkill\Layers;

use Skills\FactorGraphs\Variable;
use Skills\TrueSkill\DrawMargin;
use Skills\TrueSkill\TrueSkillFactorGraph;
use Skills\TrueSkill\Factors\GaussianWeightedSumFactor;

class TeamPerformancesToTeamPerformanceDifferencesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $inputVariablesGroupsCount = count($inputVariablesGroups);
        $outputVariablesGroup = $this->getOutputVariablesGroups();

        for ($i = 0; $i < $inputVariablesGroupsCount - 1; $i++)
        {
            $strongerTeam = $inputVariablesGroups[$i][0];
            $weakerTeam = $inputVariablesGroups[$i + 1][0];

            $currentDifference = $this->createOutputVariable();
            $newDifferencesFactor = $this->createTeamPerformanceToDifferenceFactor($strongerTeam, $weakerTeam, $currentDifference);
            $this->addLayerFactor($newDifferencesFactor);

            // REVIEW: Does it make sense to have groups of one?            
            $outputVariablesGroup[] = array($currentDifference);
        }
    }

    private function createTeamPerformanceToDifferenceFactor(
        Variable $strongerTeam, Variable $weakerTeam, Variable $output)
    {
        $teams = array($strongerTeam, $weakerTeam);
        $weights = array(1.0, -1.0);
        return new GaussianWeightedSumFactor($output, $teams, $weights);
    }

    private function createOutputVariable()
    {
        $outputVariable = $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable("Team performance difference");
        return $outputVariable;
    }
}

?>
