<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../PartialPlay.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../Factors/GaussianWeightedSumFactor.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");
require_once(dirname(__FILE__) . "/TeamPerformancesToTeamPerformanceDifferencesLayer.php");
require_once(dirname(__FILE__) . "/TeamDifferencesComparisonLayer.php");

use Moserware\Skills\PartialPlay;
use Moserware\Skills\FactorGraphs\ScheduleLoop;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\TrueSkill\Factors\GaussianWeightedSumFactor;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;

class PlayerPerformancesToTeamPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        foreach ($inputVariablesGroups as $currentTeam)
        {
            $localCurrentTeam = $currentTeam;
            $teamPerformance = $this->createOutputVariable($localCurrentTeam);
            $newSumFactor = $this->createPlayerToTeamSumFactor($localCurrentTeam, $teamPerformance);
            
            $this->addLayerFactor($newSumFactor);

            // REVIEW: Does it make sense to have groups of one?
            $outputVariablesGroups = $this->getOutputVariablesGroups();
            $outputVariablesGroups[] = array($teamPerformance);
        }        
    }

    public function createPriorSchedule()
    {
        $localFactors = $this->getLocalFactors();

        $sequence = $this->scheduleSequence(
                                            array_map(
                                                    function($weightedSumFactor)
                                                    {
                                                        return new ScheduleStep("Perf to Team Perf Step", $weightedSumFactor, 0);
                                                    },
                                                    $localFactors),
                                            "all player perf to team perf schedule");
        return $sequence;
    }

    protected function createPlayerToTeamSumFactor($teamMembers, $sumVariable)
    {
        $weights = array_map(
                        function($v)
                        {
                            $player = $v->getKey();
                            return PartialPlay::getPartialPlayPercentage($player);
                        },
                        $teamMembers);

        return new GaussianWeightedSumFactor(
                $sumVariable,
                $teamMembers,
                $weights);
                                                 
    }

    public function createPosteriorSchedule()
    {        
        $allFactors = array();
        $localFactors = $this->getLocalFactors();
        foreach($localFactors as $currentFactor)
        {
            $localCurrentFactor = $currentFactor;
            $numberOfMessages = $localCurrentFactor->getNumberOfMessages();
            for($currentIteration = 1; $currentIteration < $numberOfMessages; $currentIteration++)
            {
                $allFactors[] = new ScheduleStep("team sum perf @" . $currentIteration,
                                                 $localCurrentFactor, $currentIteration);
            }
        }
        return $this->scheduleSequence($allFactors, "all of the team's sum iterations");
    }

    private function createOutputVariable($team)
    {
        $memberNames = \array_map(function ($currentPlayer)
                                  {
                                        return (string)($currentPlayer->getKey());
                                  },
                                  $team);

        $teamMemberNames = \join(", ", $memberNames);
        $outputVariable = $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable("Team[" . $teamMemberNames . "]'s performance");
        return $outputVariable;
    }
}

?>
