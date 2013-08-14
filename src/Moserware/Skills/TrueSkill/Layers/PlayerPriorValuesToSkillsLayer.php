<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../Rating.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../../Numerics/BasicMath.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianPriorFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\Rating;
use Moserware\Skills\FactorGraphs\ScheduleLoop;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\FactorGraphs\Variable;
use Moserware\Numerics\GaussianDistribution;
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
                                       square($priorRating->getStandardDeviation()) +
                                       square($this->getParentFactorGraph()->getGameInfo()->getDynamicsFactor()),
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
