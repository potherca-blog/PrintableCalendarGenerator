<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 *
 */
abstract class AbstractDimensions extends \Potherca\Base\Data
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return int
     */
    abstract function getHeight();

    /**
     * @return int
     */
    abstract public function getWidth();

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iWidth
     * @param $p_iHeight
     */
    abstract public function  __construct($p_iWidth, $p_iHeight);

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

#EOF
