<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../../Numerics/BasicMath.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianPriorFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\FactorGraphs\ScheduleLoop;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianPriorFactor;

// We intentionally have no Posterior schedule since the only purpose here is to
// start the process.
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

            $outputVariablesGroups = $this->getOutputVariablesGroups();
            $outputVariablesGroups[] = $currentTeamSkills;
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
