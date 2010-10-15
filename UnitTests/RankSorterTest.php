<?php
namespace Moserware\Skills;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once(dirname(__FILE__) . '/../Skills/RankSorter.php');


use \PHPUnit_Framework_TestCase;
 
class RankSorterTest extends PHPUnit_Framework_TestCase
{
    public function testSort()
    {
        $team1 = array( "a" => 1, "b" => 2 );
        $team2 = array( "c" => 3, "d" => 4 );
        $team3 = array( "e" => 5, "f" => 6 );
        
        $teams = array($team1, $team2, $team3);
        
        $teamRanks = array(3, 1, 2);
        
        $sortedRanks = RankSorter::sort($teams, $teamRanks);
        
        $this->assertEquals($team2, $sortedRanks[0]);        
        $this->assertEquals($team3, $sortedRanks[1]);
        $this->assertEquals($team1, $sortedRanks[2]);
        
    }
}

?>