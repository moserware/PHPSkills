<?php
namespace Moserware\Skills;

/** 
 * Base class for all skill calculator implementations.
 */
abstract class SkillCalculator
{
    private $_supportedOptions;
    private $_playersPerTeamAllowed;
    private $_totalTeamsAllowed;
    
    protected function __construct($supportedOptions, TeamsRange $totalTeamsAllowed, PlayersRange $playerPerTeamAllowed)
    {
        $this->_supportedOptions = $supportedOptions;
        $this->_totalTeamsAllowed = $totalTeamsAllowed;
        $this->_playersPerTeamAllowed = $playerPerTeamAllowed;
    }

    /// <summary>
    /// Calculates new ratings based on the prior ratings and team ranks.
    /// </summary>
    /// <typeparam name="TPlayer">The underlying type of the player.</typeparam>
    /// <param name="gameInfo">Parameters for the game.</param>
    /// <param name="teams">A mapping of team players and their ratings.</param>
    /// <param name="teamRanks">The ranks of the teams where 1 is first place. For a tie, repeat the number (e.g. 1, 2, 2)</param>
    /// <returns>All the players and their new ratings.</returns>
    public abstract function calculateNewRatings($gameInfo,
                                                 array $teamsOfPlayerToRatings,
                                                 array $teamRanks);

    /// <summary>
    /// Calculates the match quality as the likelihood of all teams drawing.
    /// </summary>
    /// <typeparam name="TPlayer">The underlying type of the player.</typeparam>
    /// <param name="gameInfo">Parameters for the game.</param>
    /// <param name="teams">A mapping of team players and their ratings.</param>
    /// <returns>The quality of the match between the teams as a percentage (0% = bad, 100% = well matched).</returns>
    public abstract function calculateMatchQuality($gameInfo,
                                                   array $teamsOfPlayerToRatings);

    public function isSupported($option)
    {           
        return ($this->_supportedOptions & $option) == $option;             
    }    

    protected function validateTeamCountAndPlayersCountPerTeam(array $teamsOfPlayerToRatings)
    {
        self::validateTeamCountAndPlayersCountPerTeamWithRanges($teamsOfPlayerToRatings, $this->_totalTeamsAllowed, $this->_playersPerTeamAllowed);
    }

    private static function validateTeamCountAndPlayersCountPerTeamWithRanges(
        array $teams,
        TeamsRange $totalTeams, 
        PlayersRange $playersPerTeam)
    {        
        $countOfTeams = 0;
        
        foreach ($teams as $currentTeam)
        {
            if (!$playersPerTeam->isInRange($currentTeam->count()))
            {
                throw new \Exception("Player count is not in range");
            }
            $countOfTeams++;
        }

        if (!$totalTeams->isInRange($countOfTeams))
        {
            throw new Exception("Team range is not in range");
        }
    }
}

class SkillCalculatorSupportedOptions
{
    const NONE = 0x00;
    const PARTIAL_PLAY = 0x01;
    const PARTIAL_UPDATE = 0x02;
}
?>