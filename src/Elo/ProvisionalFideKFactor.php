<?php
/**
 * Created by PhpStorm.
 * User: les.peabody
 * Date: 10/13/15
 * Time: 11:01 PM
 */

namespace Skills\Elo;

/**
 * Indicates someone who has played less than 30 games.
 */
class ProvisionalFideKFactor extends FideKFactor
{
  public function getValueForRating($rating)
  {
    return 25;
  }
}
