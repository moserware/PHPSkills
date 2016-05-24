<?php namespace Moserware\Skills\Tests\Numerics;

use Moserware\Skills\Numerics\BasicMath;
use Moserware\Skills\Tests\TestCase;

class BasicMathTest extends TestCase
{
    public function testSquare()
    {
        $this->assertEquals(1, BasicMath::square(1));
        $this->assertEquals(1.44, BasicMath::square(1.2));
        $this->assertEquals(4, BasicMath::square(2));
    }
}