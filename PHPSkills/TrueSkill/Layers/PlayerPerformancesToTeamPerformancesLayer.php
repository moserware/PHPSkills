<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Schedule.php");
require_once(dirname(__FILE__) . "/../TrueSkillFactorGraph.php");
require_once(dirname(__FILE__) . "/TrueSkillFactorGraphLayer.php");
require_once(dirname(__FILE__) . "/TeamPerformancesToTeamPerformanceDifferencesLayer.php");
require_once(dirname(__FILE__) . "/TeamDifferencesComparisonLayer.php");

use Moserware\Skills\FactorGraphs\ScheduleLoop;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\ScheduleStep;
use Moserware\Skills\TrueSkill\TrueSkillFactorGraph;

class PlayerPerformancesToTeamPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer()
    {
        foreach ($this->getInputVariablesGroups() as $currentTeam)
        {
            $teamPerformance = $this->createOutputVariable($currentTeam);
            $this->addLayerFactor($this->createPlayerToTeamSumFactor($currentTeam, $teamPerformance));

            // REVIEW: Does it make sense to have groups of one?
            $this->getOutputVariablesGroups() = $teamPerformance;
        }
    }

    public function createPriorSchedule()
    {
        return $this->scheduleSequence(
                array_map(
                        function($weightedSumFactor)
                        {
                            return new ScheduleStep("Perf to Team Perf Step", $weightedSumFactor, 0);
                        },
                        $this->getLocalFactors()),
                "all player perf to team perf schedule");
    }

    protected function createPlayerToTeamSumFactor($teamMembers, $sumVariable)
    {
        return new GaussianWeightedSumFactor(
                $sumVariable,
                $teamMembers,
                array_map(
                        function($v)
                        {
                            return PartialPlay::getPartialPlayPercentage($v->getKey());
                        },
                        $teamMembers));
                                                 
    }

    public function createPosteriorSchedule()
    {
        // BLOG
        $allFactors = array();
        foreach($this->getLocalFactors() as $currentFactor)
        {
            $numberOfMessages = $currentFactor->getNumberOfMessages();
            for($currentIteration = 1; $currentIteration < $numberOfMessages; $currentIteration++)
            {
                $allFactors[] = new ScheduleStep("team sum perf @" + $currentIteration,
                                                 $currentFactor, $currentIteration);
            }
        }
        return $this->scheduleSequence($allFactors, "all of the team's sum iterations");
    }

    private function createOutputVariable($team)
    {
        ///$teamMemberNames = String.Join(", ", team.Select(teamMember => teamMember.Key.ToString()).ToArray());
        $teamMemberNames = "TODO";
        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable("Team[{0}]'s performance", $teamMemberNames);
    }
}

?>
