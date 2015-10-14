<?php
namespace Skills\Tests\Numerics;

class BasicMathTest extends PHPUnit_Framework_TestCase
{    
    public function testSquare()
    {    
        $this->assertEquals( 1, Moserware\Numerics\square(1) );
        $this->assertEquals( 1.44, Moserware\Numerics\square(1.2) );
        $this->assertEquals( 4, Moserware\Numerics\square(2) );
    }
}
?>
