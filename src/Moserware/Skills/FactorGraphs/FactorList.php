<?php

namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/Factor.php");

/**
 * Helper class for computing the factor graph's normalization constant.
 */
class FactorList
{
    private $_list = array();

    public function getLogNormalization()
    {
        $list = $this->_list;
        foreach($list as $currentFactor)
        {
            $currentFactor->resetMarginals();
        }

        $sumLogZ = 0.0;

        $listCount = count($this->_list);

        for ($i = 0; $i < $listCount; $i++)
        {
            $f = $this->_list[$i];

            $numberOfMessages = $f->getNumberOfMessages();

            for ($j = 0; $j < $numberOfMessages; $j++)
            {
                $sumLogZ += $f->sendMessageIndex($j);
            }
        }

        $sumLogS = 0;

        foreach($list as $currentFactor)
        {
            $sumLogS = $sumLogS + $currentFactor->getLogNormalization();
        }

        return $sumLogZ + $sumLogS;
    }

    public function count()
    {
        return count($this->_list);
    }

    public function addFactor(Factor $factor)
    {
        $this->_list[] = $factor;
        return $factor;
    }
}

?>
