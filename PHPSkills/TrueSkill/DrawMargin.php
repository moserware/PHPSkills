<?php
namespace Moserware\Skills\TrueSkill;

require_once(dirname(__FILE__) . "/../Numerics/GaussianDistribution.php");

use Moserware\Numerics\GaussianDistribution;

final class DrawMargin
{
    public static function getDrawMarginFromDrawProbability($drawProbability, $beta)
    {
        // Derived from TrueSkill technical report (MSR-TR-2006-80), page 6

        // draw probability = 2 * CDF(margin/(sqrt(n1+n2)*beta)) -1

        // implies
        //
        // margin = inversecdf((draw probability + 1)/2) * sqrt(n1+n2) * beta
        // n1 and n2 are the number of players on each team
        $margin = GaussianDistribution::inverseCumulativeTo(.5*($drawProbability + 1), 0, 1)*sqrt(1 + 1)*
                        $beta;
        return $margin;
    }
}

?>