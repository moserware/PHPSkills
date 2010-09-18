<?php
namespace Moserware\Skills\TrueSkill\Layers;

require_once(dirname(__FILE__) . "TrueSkillFactorGraphLayer.php");
require_once(dirname(__FILE__) . "TeamPerformancesToTeamPerformanceDifferencesLayer.php");
require_once(dirname(__FILE__) . "TeamDifferencesComparisonLayer.php");
require_once(dirname(__FILE__) . "../../FactorGraphs/Schedule.php");

use Moserware\Skills\FactorGraphs\ScheduleLoop;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\ScheduleStep;

// The whole purpose of this is to do a loop on the bottom
class IteratedTeamDifferencesInnerLayer extends TrueSkillFactorGraphLayer
{
    private $_TeamDifferencesComparisonLayer;
    private $_TeamPerformancesToTeamPerformanceDifferencesLayer;

    public function __construct(TrueSkillFactorGraph $parentGraph,
                                TeamPerformancesToTeamPerformanceDifferencesLayer $teamPerformancesToPerformanceDifferences,
                                TeamDifferencesComparisonLayer $teamDifferencesComparisonLayer)
    {
        parent::__construct($parentGraph);
        $this->_TeamPerformancesToTeamPerformanceDifferencesLayer = $teamPerformancesToPerformanceDifferences;
        $this->_TeamDifferencesComparisonLayer = $teamDifferencesComparisonLayer;
    }

    public function buildLayer()
    {
        $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->setRawInputVariablesGroups($this->getInputVariablesGroups());
        $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->buildLayer();

        $this->_TeamDifferencesComparisonLayer->setRawInputVariablesGroups(
            $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getRawOutputVariablesGroups());
        $this->_TeamDifferencesComparisonLayer->buildLayer();
    }

    public function createPriorSchedule()
    {
        // BLOG about $loop
        switch (count($this->getInputVariablesGroups()))
        {
            case 0:
            case 1:
                throw new InvalidOperationException();
            case 2:
                $loop = $this->createTwoTeamInnerPriorLoopSchedule();
                break;
            default:
                $loop = $this->createMultipleTeamInnerPriorLoopSchedule();
                break;
        }

        // When dealing with differences, there are always (n-1) differences, so add in the 1
        $totalTeamDifferences = count($this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors());
        $totalTeams = $totalTeamDifferences + 1;

        $localFactors = $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
        $innerSchedule = new ScheduleSequence(
            "inner schedule",
            array(
                    $loop,
                    new ScheduleStep(
                        "teamPerformanceToPerformanceDifferenceFactors[0] @ 1",
                        $localFactors[0], 1),
                    new ScheduleStep(
                        "teamPerformanceToPerformanceDifferenceFactors[teamTeamDifferences = {0} - 1] @ 2",                                      
                        $localFactors[$totalTeamDifferences - 1], 2)
                )
            );

        return innerSchedule;
    }

    private function createTwoTeamInnerPriorLoopSchedule()
    {
        $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors = $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
        $teamDifferencesComparisonLayerLocalFactors = $this->_TeamDifferencesComparisonLayer->getLocalFactors();

        return $this->scheduleSequence(
            array(
                    new ScheduleStep(
                        "send team perf to perf differences",
                        $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors[0],
                        0),
                    new ScheduleStep(
                        "send to greater than or within factor",
                        $teamDifferencesComparisonLayerLocalFactors[0],
                        0)
                ),
            "loop of just two teams inner sequence");
    }

    private function createMultipleTeamInnerPriorLoopSchedule()
    {
        $totalTeamDifferences = count($this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors());

        $forwardScheduleList = array();

        for ($i = 0; $i < $totalTeamDifferences - 1; $i++)
        {
            $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors = $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
            $teamDifferencesComparisonLayerLocalFactors = $this->_TeamDifferencesComparisonLayer->getLocalFactors();

            $currentForwardSchedulePiece =
                $this->scheduleSequence(
                    array(
                            new ScheduleStep(
                                sprintf("team perf to perf diff %d", $i),
                                $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors[$i], 0),
                            new ScheduleStep(
                                sprintf("greater than or within result factor %d", $i),
                                $teamDifferencesComparisonLayerLocalFactors[$i], 0),
                            new ScheduleStep(
                                sprintf("team perf to perf diff factors [%d], 2", $i),
                                $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors[$i], 2)
                        ), sprintf("current forward schedule piece %d", $i));

            $forwardScheduleList[] = $currentForwardSchedulePiece;
        }

        $forwardSchedule = new ScheduleSequence("forward schedule", $forwardScheduleList);

        $backwardScheduleList = array();

        for ($i = 0; $i < $totalTeamDifferences - 1; $i++)
        {
            $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors = $this->_TeamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
            $teamDifferencesComparisonLayerLocalFactors = $this->_TeamDifferencesComparisonLayer->getLocalFactors();

            $currentBackwardSchedulePiece = new ScheduleSequence(
                "current backward schedule piece",
                array(
                        new ScheduleStep(
                            sprintf("teamPerformanceToPerformanceDifferenceFactors[totalTeamDifferences - 1 - %d] @ 0", $i),
                            $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors[$totalTeamDifferences - 1 - $i], 0),
                        new ScheduleStep(
                            sprintf("greaterThanOrWithinResultFactors[totalTeamDifferences - 1 - %d] @ 0", $i),
                            $teamDifferencesComparisonLayerLocalFactors[$totalTeamDifferences - 1 - $i], 0),
                        new ScheduleStep(
                            sprintf("teamPerformanceToPerformanceDifferenceFactors[totalTeamDifferences - 1 - %d] @ 1", $i),
                            $teamPerformancesToTeamPerformanceDifferencesLayerLocalFactors[$totalTeamDifferences - 1 - $i], 1)
                ));
            $backwardScheduleList[] = $currentBackwardSchedulePiece;
        }

        $backwardSchedule = new ScheduleSequence("backward schedule", $backwardScheduleList);

        $forwardBackwardScheduleToLoop =
            new ScheduleSequence(
                "forward Backward Schedule To Loop",
                array($forwardSchedule, $backwardSchedule));

        $initialMaxDelta = 0.0001;

        $loop = new ScheduleLoop(
            sprintf("loop with max delta of %f", $initialMaxDelta),
            $forwardBackwardScheduleToLoop,
            $initialMaxDelta);

        return $loop;
    }
}

?>
