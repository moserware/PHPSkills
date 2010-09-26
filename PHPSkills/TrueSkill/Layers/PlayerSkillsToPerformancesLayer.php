<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../../Numerics/BasicMath.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianLikelihoodFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianLikelihoodFactor;

class PlayerSkillsToPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph &$parentGraph)
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
                $newLikelihoodFactor = $this->createLikelihood($playerSkillVariable, $playerPerformance);
                $this->addLayerFactor($newLikelihoodFactor);
                $currentTeamPlayerPerformances[] = &$playerPerformance;
            }
            
            $outputVariablesGroups = &$this->getOutputVariablesGroups();
            $outputVariablesGroups[] = &$currentTeamPlayerPerformances;
        }
    }

    private function createLikelihood(&$playerSkill, &$playerPerformance)
    {
        return new GaussianLikelihoodFactor(square($this->getParentFactorGraph()->getGameInfo()->getBeta()), $playerPerformance, $playerSkill);
    }

    private function createOutputVariable(&$key)
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($key, "{0}'s performance", $key);
    }

    public function createPriorSchedule()
    {
        return $this->scheduleSequence(
                array_map(
                        function($likelihood)
                        {
                            return new ScheduleStep("Skill to Perf step", $likelihood, 0);
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
