<?php

namespace Moserware\Skills\TrueSkill\Layers;

class TeamPerformancesToTeamPerformanceDifferencesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
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
            $this->getOutputVariablesGroups()[] = $currentDifference;
        }
    }

    private function createTeamPerformanceToDifferenceFactor(
        Variable $strongerTeam, Variable $weakerTeam, Variable $output)
    {
        return new GaussianWeightedSumFactor($output, array($strongerTeam, $weakerTeam), array(1.0, -1.0));
    }

    private function createOutputVariable()
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable("Team performance difference");
    }
}

?>
