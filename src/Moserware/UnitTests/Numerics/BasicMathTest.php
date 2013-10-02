<?php
namespace Moserware\UnitTests\Numerics;

use Moserware\Skills\Numerics\BasicMath;
use PHPUnit_Framework_TestCase;

/**
 * Class BasicMathTest
 *
 * @todo After switching to PHP internal method pow() this test can be removed entirely.
 *
 * @package Moserware\UnitTests\Numerics
 */
class BasicMathTest extends PHPUnit_Framework_TestCase
{
    public function testSquare()
    {    
        $this->assertEquals( 1, BasicMath::square(1) );
        $this->assertEquals( 1.44, BasicMath::square(1.2) );
        $this->assertEquals( 4, BasicMath::square(2) );
    }
}
?>