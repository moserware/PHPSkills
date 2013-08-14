<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../../Numerics/BasicMath.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianLikelihoodFactor.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");

use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\FactorGraphs\KeyedVariable;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;
use Moserware\Skills\TrueSkill\Factors\GaussianLikelihoodFactor;

class PlayerSkillsToPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $outputVariablesGroups = $this->getOutputVariablesGroups();

        foreach ($inputVariablesGroups as $currentTeam)
        {
            $currentTeamPlayerPerformances = array();

            foreach ($currentTeam as $playerSkillVariable)
            {
                $localPlayerSkillVariable = $playerSkillVariable;
                $currentPlayer = $localPlayerSkillVariable->getKey();
                $playerPerformance = $this->createOutputVariable($currentPlayer);
                $newLikelihoodFactor = $this->createLikelihood($localPlayerSkillVariable, $playerPerformance);
                $this->addLayerFactor($newLikelihoodFactor);
                $currentTeamPlayerPerformances[] = $playerPerformance;
            }            
            
            $outputVariablesGroups[] = $currentTeamPlayerPerformances;
        }
    }

    private function createLikelihood(KeyedVariable $playerSkill, KeyedVariable $playerPerformance)
    {
        return new GaussianLikelihoodFactor(square($this->getParentFactorGraph()->getGameInfo()->getBeta()), $playerPerformance, $playerSkill);
    }

    private function createOutputVariable($key)
    {
        $outputVariable = $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($key, $key . "'s performance");
        return $outputVariable;
    }

    public function createPriorSchedule()
    {
        $localFactors = $this->getLocalFactors();
        return $this->scheduleSequence(
                array_map(
                        function($likelihood)
                        {
                            return new ScheduleStep("Skill to Perf step", $likelihood, 0);
                        },
                        $localFactors),
                "All skill to performance sending");
    }

    public function createPosteriorSchedule()
    {
        $localFactors = $this->getLocalFactors();
        return $this->scheduleSequence(
                array_map(
                        function($likelihood)
                        {
                            return new ScheduleStep("name", $likelihood, 1);                    
                        },
                        $localFactors),
                "All skill to performance sending");
    }
}

?>
