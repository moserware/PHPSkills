<?php

namespace Moserware\Skills\TrueSkill\Layers;

class PlayerSkillsToPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        foreach ($this->getInputVariablesGroups() as $currentTeam)
        {
            $currentTeamPlayerPerformances = array();

            foreach ($currentTeam as $playerSkillVariable)
            {
                $playerPerformance = $this->createOutputVariable($playerSkillVariable->getKey());
                $this->addLayerFactor($this->createLikelihood($playerSkillVariable, $playerPerformance));
                $currentTeamPlayerPerformances[] = $playerPerformance;
            }

            $this->getOutputVariablesGroups()[] = $currentTeamPlayerPerformances;
        }
    }

    private function createLikelihood($playerSkill, $playerPerformance)
    {
        return new GaussianLikelihoodFactor(square($this->getParentFactorGraph()->getGameInfo()->getBeta()), $playerPerformance, $playerSkill);
    }

    private function createOutputVariable($key)
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($key, "{0}'s performance", $key);
    }

    public function createPriorSchedule()
    {
        return $this->scheduleSequence(
                array_map(
                        function($likelihood)
                        {
                            return $this->scheduleStep("Skill to Perf step", $likelihood, 0);
                        },
                        $this->getLocalFactors()),
                "All skill to performance sending");
    }

    public function createPosteriorSchedule()
    {
        return $this->scheduleSequence(
                array_map(
                        function($likelihood)
                        {
                            return new ScheduleStep("name", $likelihood, 1);                    
                        },
                        $this->getLocalFactors()),
                "All skill to performance sending");
    }
}

?>
