<?php
namespace Skills\Tests\Numerics;

use Skills\Numerics\BasicMath;
use PHPUnit_Framework_TestCase;

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
