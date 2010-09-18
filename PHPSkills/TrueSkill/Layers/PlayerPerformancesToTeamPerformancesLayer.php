<?php

namespace Moserware\Skills\TrueSkill\Layers;

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
        return $this->scheduleSequence(
                from currentFactor in LocalFactors
                                from currentIteration in
                                    Enumerable.Range(1, currentFactor.NumberOfMessages - 1)
                                select new ScheduleStep<GaussianDistribution>(
                                    "team sum perf @" + currentIteration,
                                    currentFactor,
                                    currentIteration),
                                "all of the team's sum iterations");
    }

    private function createOutputVariable($team)
    {
        $teamMemberNames = String.Join(", ", team.Select(teamMember => teamMember.Key.ToString()).ToArray());
        return ParentFactorGraph.VariableFactory.CreateBasicVariable("Team[{0}]'s performance", teamMemberNames);
    }
}

?>
