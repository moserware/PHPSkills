<?php
namespace Moserware\Skills\TrueSkill\Layers;

use Moserware\Skills\Rating;

use Moserware\Skills\Numerics\BasicMath;

use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\FactorGraphs\Variable;

use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianPriorFactor;

// We intentionally have no Posterior schedule since the only purpose here is to
// start the process.
class PlayerPriorValuesToSkillsLayer extends TrueSkillFactorGraphLayer
{
    private $_teams;

    public function __construct(TrueSkillFactorGraph $parentGraph, array $teams)
    {
        parent::__construct($parentGraph);
        $this->_teams = $teams;
    }

    public function buildLayer()
    {
        $teams = $this->_teams;
        foreach ($teams as $currentTeam)
        {
            $localCurrentTeam = $currentTeam;
            $currentTeamSkills = array();

            $currentTeamAllPlayers = $localCurrentTeam->getAllPlayers();
            foreach ($currentTeamAllPlayers as $currentTeamPlayer)
            {
                $localCurrentTeamPlayer = $currentTeamPlayer;
                $currentTeamPlayerRating = $currentTeam->getRating($localCurrentTeamPlayer);
                $playerSkill = $this->createSkillOutputVariable($localCurrentTeamPlayer);
                $priorFactor = $this->createPriorFactor($localCurrentTeamPlayer, $currentTeamPlayerRating, $playerSkill);
                $this->addLayerFactor($priorFactor);
                $currentTeamSkills[] = $playerSkill;
            }

            $outputVariablesGroups = $this->getOutputVariablesGroups();
            $outputVariablesGroups[] = $currentTeamSkills;
        }
    }

    public function createPriorSchedule()
    {
        $localFactors = $this->getLocalFactors();
        return $this->scheduleSequence(
                array_map(
                        function($prior)
                        {
                            return new ScheduleStep("Prior to Skill Step", $prior, 0);
                        },
                        $localFactors),
                 "All priors");
    }

    private function createPriorFactor($player, Rating $priorRating, Variable $skillsVariable)
    {
        return new GaussianPriorFactor($priorRating->getMean(),
                                       BasicMath::square($priorRating->getStandardDeviation()) +
                                       BasicMath::square($this->getParentFactorGraph()->getGameInfo()->getDynamicsFactor()),
                                       $skillsVariable);
    }

    private function createSkillOutputVariable($key)
    {
        $parentFactorGraph = $this->getParentFactorGraph();
        $variableFactory = $parentFactorGraph->getVariableFactory();
        $skillOutputVariable = $variableFactory->createKeyedVariable($key, $key . "'s skill");
        return $skillOutputVariable;
    }
}

?>
