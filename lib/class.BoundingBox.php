<?php
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
class BoundingBox extends Dimensions implements ArrayAccess
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
    protected $m_aBoundingBox = array();

    /**
     * @var array
     */
    protected $m_aMap = array(
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
     * @param string $p_sMethodName
     * @param array $p_aArguments
     *
     * @return null
     */
    public function __call($p_sMethodName, $p_aArguments)
    {
        $iValue = null;

        if(substr($p_sMethodName, 0, 3) === 'get')
        {
            $iValue = $this->offsetGet(substr($p_sMethodName,3));
        }
        else
        {
            throw new Exception('Call to undefined method "' . $p_sMethodName . '"');
        }#if

        return $iValue;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param array $p_aBoundingBox
     *
     * @return BoundingBox
     */
    static public function fromArray(Array $p_aBoundingBox)
    {
        $oInstance = new self(null, null);
        $oInstance->m_aBoundingBox = $p_aBoundingBox;

        return $oInstance;
    }

    /**
     * @return number
     */
    public function getWidth()
    {
        return abs($this->getLowerLeftX())+$this->getLowerRightX();
    }


    /**
     * @return number
     */
    public function getHeight()
    {
        return abs($this->getLowerRightY()) + abs($this->getUpperRightY());
    }

    ////////////////////////// ArrayAccess Public API \\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_sOffset
     * @param $p_mValue
     */
    public function offsetSet($p_sOffset, $p_mValue) {
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
    public function offsetExists($p_sOffset) {
        return isset($this->m_aBoundingBox[$p_sOffset]);
    }

    /**
     * @param $p_sOffset
     */
    public function offsetUnset($p_sOffset) {
        unset($this->m_aBoundingBox[$p_sOffset]);
    }

    /**
     * @param $p_sOffset
     *
     * @return integer
     */
    public function offsetGet($p_sOffset) {
        $iValue = null;

        if(isset($this->m_aBoundingBox[$p_sOffset]))
        {
            // Numeric offset, compatible with imagettfbbox() return value
            $iValue = $this->m_aBoundingBox[$p_sOffset];
        }
        elseif($this->m_aBoundingBox[$this->m_aMap[$p_sOffset]])
        {
            // String offset use bounding box map to find value
            $iValue = $this->m_aBoundingBox[$this->m_aMap[$p_sOffset]];
        }

        return $iValue;
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}
#EOF