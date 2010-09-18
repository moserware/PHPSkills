<?php
namespace Moserware\Skills\TrueSkill;

require_once(dirname(__FILE__) . '../Rating.php');
require_once(dirname(__FILE__) . '../FactorGraphs/FactorList.php');
require_once(dirname(__FILE__) . '../FactorGraphs/Schedule.php');
require_once(dirname(__FILE__) . '../FactorGraphs/VariableFactory.php');
require_once(dirname(__FILE__) . '../Numerics/GaussianDistribution.php');
require_once(dirname(__FILE__) . '/Layers/PlayerPriorValuesToSkillsLayer.php');
require_once(dirname(__FILE__) . '/Layers/PlayerSkillsToPerformancesLayer.php');
require_once(dirname(__FILE__) . '/Layers/IteratedTeamDifferencesInnerLayer.php');
require_once(dirname(__FILE__) . '/Layers/TeamPerformancesToTeamPerformanceDifferencesLayer.php');
require_once(dirname(__FILE__) . '/Layers/TeamDifferencesComparisonLayer.php');

use Moserware\Skills\Rating;
use Moserware\Skills\FactorGraphs\FactorList;
use Moserware\Skills\FactorGraphs\ScheduleSequence;
use Moserware\Skills\FactorGraphs\VariableFactory;
use Moserware\Skills\TrueSkill\Layers\PlayerPriorValuesToSkillsLayer;
use Moserware\Skills\TrueSkill\Layers\PlayerSkillsToPerformancesLayer;
use Moserware\Skills\TrueSkill\Layers\IteratedTeamDifferencesInnerLayer;
use Moserware\Skills\TrueSkill\Layers\TeamPerformancesToTeamPerformanceDifferencesLayer;
use Moserware\Skills\TrueSkill\Layers\TeamDifferencesComparisonLayer;

class TrueSkillFactorGraph extends FactorGraph
{
    private $_gameInfo;
    private $_layers;
    private $_priorLayer;
    private $_variableFactory;

    public function __construct(GameInfo $gameInfo, $teams, array $teamRanks)
    {
        $this->_priorLayer = new PlayerPriorValuesToSkillsLayer($this, $teams);
        $this->_gameInfo = $gameInfo;
        $this->_variableFactory = new VariableFactory(
                                        function()
                                        {
                                            return GaussianDistribution::fromPrecisionMean(0, 0);
                                        });

        $this->_layers = array(
                              $this->_priorLayer,
                              new PlayerSkillsToPerformancesLayer($this),
                              new PlayerPerformancesToTeamPerformancesLayer($this),
                              new IteratedTeamDifferencesInnerLayer(
                                  $this,
                                  new TeamPerformancesToTeamPerformanceDifferencesLayer($this),
                                  new TeamDifferencesComparisonLayer($this, $teamRanks))
                              );
    }

    public function getGameInfo()
    {
        return $this->_gameInfo;
    }

    public function buildGraph()
    {
        $lastOutput = null;

        foreach ($this->_layers as $currentLayer)
        {
            if ($lastOutput != null)
            {
                $currentLayer->setInputVariablesGroups($lastOutput);
            }

            $currentLayer->buildLayer();

            $lastOutput = $currentLayer->getOutputVariablesGroups();
        }
    }

    public function runSchedule()
    {
        $fullSchedule = $this->createFullSchedule();
        $fullScheduleDelta = $fullSchedule->visit();
    }

    public function getProbabilityOfRanking()
    {
        $factorList = new FactorList();

        foreach ($this->_layers as $currentLayer)
        {
            foreach ($currentLayer->getFactors() as $currentFactor)
            {
                $factorList->addFactor($currentFactor);
            }
        }

        $logZ = $factorList->getLogNormalization();
        return exp($logZ);
    }

    private function createFullSchedule()
    {
        $fullSchedule = array();

        foreach ($this->_layers as $currentLayer)
        {
            $currentPriorSchedule = $currentLayer->createPriorSchedule();
            if ($currentPriorSchedule != null)
            {
                $fullSchedule->add($currentPriorSchedule);
            }
        }
        
        $allLayersReverse = \array_reverse($this->_layers);

        foreach ($allLayersReverse as $currentLayer)
        {
            $currentPosteriorSchedule = $currentLayer->createPosteriorSchedule();
            if ($currentPosteriorSchedule != null)
            {
                $fullSchedule->add($currentPosteriorSchedule);
            }
        }

        return new ScheduleSequence("Full schedule", $fullSchedule);
    }

    public function getUpdatedRatings()
    {
        $result = array();

        foreach ($this->_priorLayer->getOutputVariablesGroups() as $currentTeam)
        {
            foreach ($currentTeam as $currentPlayer)
            {
                $result[$currentPlayer->getKey()] = new Rating($currentPlayer->getValue()->getMean(),
                                                               $currentPlayer->getValue()->getStandardDeviation());
            }
        }

        return $result;
    }
}

?>
