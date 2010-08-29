<?php

require_once(dirname(__FILE__) . "/gaussian.php");


$f = \Moserware\Numerics\GaussianDistribution::cumulativeTo(1.2);
echo $f;
?>