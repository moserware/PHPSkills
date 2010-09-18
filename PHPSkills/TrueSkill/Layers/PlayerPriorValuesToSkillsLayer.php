<?php

namespace Moserware\Skills\TrueSkill\Layers;

// We intentionally have no Posterior schedule since the only purpose here is to
class PlayerPriorValuesToSkillsLayer extends TrueSkillFactorGraphLayer
{
    private $_teams;

    public function __construct(TrueSkillFactorGraph $parentGraph, $teams)
    {
        parent::__construct($parentGraph);
        $this->_teams = $teams;
    }

    public function buildLayer()
    {
        foreach ($this->_teams as $currentTeam)
        {
            $currentTeamSkills = array();

            foreach ($currentTeam as $currentTeamPlayer)
            {
                $playerSkill = $this->createSkillOutputVariable($currentTeamPlayer.Key);
                $this->addLayerFactor($this->createPriorFactor($currentTeamPlayer.Key, $currentTeamPlayer.Value, $playerSkill));
                $currentTeamSkills[] = $playerSkill;
            }

            OutputVariablesGroups.Add(currentTeamSkills);
        }
    }

    public function createPriorSchedule()
    {
        return $this->scheduleSequence(
                array_map(
                        function($prior)
                        {
                            return new ScheduleStep("Prior to Skill Step", $prior, 0);
                        },
                        $this->getLocalFactors()),
                 "All priors");
    }

    private function createPriorFactor($player, $priorRating, $skillsVariable)
    {
        return new GaussianPriorFactor($priorRating->getMean(),
                                       square($priorRating->getStandardDeviation()) +
                                       square($this->getParentFactorGraph()->getGameInfo()->getDynamicsFactor()),
                                       $skillsVariable);
    }

    private function createSkillOutputVariable($key)
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($key, "{0}'s skill", $key);
    }
}

?>
