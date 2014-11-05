<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 * @method  int getLowerLeftX()
 * @method  int getLowerLeftY()
 * @method  int getLowerRightX()
 * @method  int getLowerRightY()
 * @method  int getUpperRightX()
 * @method  int getUpperRightY()
 * @method  int getUpperLeftX()
 * @method  int getUpperLeftY()
 */
class BoundingBox extends AbstractDimensions implements \ArrayAccess
{
    const LOWER_LEFT_X  = 0;
    const LOWER_LEFT_Y  = 1;
    const LOWER_RIGHT_X = 2;
    const LOWER_RIGHT_Y = 3;
    const UPPER_RIGHT_X = 4;
    const UPPER_RIGHT_Y = 5;
    const UPPER_LEFT_X  = 6;
    const UPPER_LEFT_Y  = 7;

////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var array
     */
    private $m_aBoundingBox = array();

    /**
     * @var array
     */
    private $m_aMap = array(
          'LowerLeftX'  => self::LOWER_LEFT_X
        , 'LowerLeftY'  => self::LOWER_LEFT_Y
        , 'LowerRightX' => self::LOWER_RIGHT_X
        , 'LowerRightY' => self::LOWER_RIGHT_Y
        , 'UpperRightX' => self::UPPER_RIGHT_X
        , 'UpperRightY' => self::UPPER_RIGHT_Y
        , 'UpperLeftX'  => self::UPPER_LEFT_X
        , 'UpperLeftY'  => self::UPPER_LEFT_Y
    );

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iWidth
     * @expose
     */
    public function setWidth($p_iWidth)
    {
        $this->m_iWidth = (int) $p_iWidth;
    }

    /**
     * @return number
     */
    final public function getWidth()
    {
        return abs($this->getLowerLeftX())+$this->getLowerRightX();
    }

    /**
     * @param $p_iHeight
     */
    private function setHeight($p_iHeight)
    {
        $this->m_iHeight = (int) $p_iHeight;
    }

    /**
     * @return number
     */
    final public function getHeight()
    {
        return abs($this->getLowerRightY()) + abs($this->getUpperRightY());
    }

    /**
     * @param string $p_sMethodName
     * @param array $p_aArguments
     *
     * @throws Exception
     *
     * @return mixed
     */
    final public function __call($p_sMethodName, $p_aArguments)
    {
        $iValue = null;

        if (substr($p_sMethodName, 0, 3) === 'get') {
            $iValue = $this->offsetGet(substr($p_sMethodName, 3));
        } else {
            throw new Exception('Call to undefined method "' . $p_sMethodName . '"');
        }#if

        return $iValue;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iWidth
     * @param $p_iHeight
     */
    final public function __construct($p_iWidth, $p_iHeight)
    {
        $this->setWidth($p_iWidth);
        $this->setHeight($p_iHeight);
    }
    /**
     * @param array $p_aBoundingBox
     *
     * @return BoundingBox
     */
    final public static function fromArray(Array $p_aBoundingBox)
    {
        $oInstance = new self(null, null);
        $oInstance->m_aBoundingBox = $p_aBoundingBox;

        return $oInstance;
    }

    ////////////////////////// ArrayAccess Public API \\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_sOffset
     * @param $p_mValue
     */
    final public function offsetSet($p_sOffset, $p_mValue)
    {
        if (is_null($p_sOffset)) {
            $this->m_aBoundingBox[] = $p_mValue;
        } else {
            $this->m_aBoundingBox[$p_sOffset] = $p_mValue;
        }
    }

    /**
     * @param $p_sOffset
     * @return bool
     */
    final public function offsetExists($p_sOffset)
    {
        return isset($this->m_aBoundingBox[$p_sOffset]);
    }

    /**
     * @param $p_sOffset
     */
    final public function offsetUnset($p_sOffset)
    {
        unset($this->m_aBoundingBox[$p_sOffset]);
    }

    /**
     * @param $p_sOffset
     *
     * @return integer
     */
    final public function offsetGet($p_sOffset)
    {
        $iValue = null;

        if (isset($this->m_aBoundingBox[$p_sOffset])) {
            // Numeric offset, compatible with imagettfbbox() return value
            $iValue = $this->m_aBoundingBox[$p_sOffset];
        } elseif ($this->m_aBoundingBox[$this->m_aMap[$p_sOffset]]) {
            // String offset use bounding box map to find value
            $iValue = $this->m_aBoundingBox[$this->m_aMap[$p_sOffset]];
        } else {
            // Nothing else
        }

        return $iValue;
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}
#EOF
