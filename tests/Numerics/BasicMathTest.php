<?php namespace Moserware\Skills\Tests\Numerics;

use Moserware\Skills\Tests\TestCase;

class BasicMathTest extends TestCase
{
    public function testSquare()
    {
        $this->assertEquals(1, Moserware\Numerics\square(1));
        $this->assertEquals(1.44, Moserware\Numerics\square(1.2));
        $this->assertEquals(4, Moserware\Numerics\square(2));
    }
}