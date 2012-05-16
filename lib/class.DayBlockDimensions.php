<?php

/**
 *
 */
class DayBlockDimensions extends Dimensions
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var Dimensions
     */
    protected static $m_oParent;

    /**
     * @var integer
     */
    static protected $m_iBlockHeight;
    /**
     * @var integer
     */
    static protected $m_iBlockWidth;

    /**
     * @var integer
     */
    static protected $m_iTopOffset;
    /**
     * @var integer
     */
    static protected $m_iLeftOffset;

    /**
     * @var integer
     */
    static protected $m_iLineWidth;
    /**
     * @var integer
     */
    static protected $m_iLineHeight;

    /**
     * @var integer
     */
    protected $m_iRow;
    /**
     * @var integer
     */
    protected $m_iColumn;

    /**
     * @var integer
     */
    protected $m_iX;
    /**
     * @var integer
     */
    protected $m_iY;


////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return int
     */
    public function getHeight()
    {
        return self::getBlockHeight();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return self::getBlockWidth();
    }

    /**
     * @return int
     */
    static public function getBlockHeight()
    {
        return self::$m_iBlockHeight;
    }

    /**
     * @return int
     */
    static public function getBlockWidth()
    {
        return self::$m_iBlockWidth;
    }

    /**
     * @return integer
     */
    static public function getTopOffset()
    {
        return self::$m_iTopOffset;
    }

    /**
     * @return integer
     */
    static public function getLineHeight()
    {
        return self::$m_iLineHeight;
    }

    /**
     * @return integer
     */
    static public function getLeftOffset()
    {
        return self::$m_iLeftOffset;
    }

    /**
     * @return integer
     */
    static public function getLineWidth()
    {
        return self::$m_iLineWidth;
    }

    /**
     * @param $p_iRow
     */
    public function setRow($p_iRow)
    {
        $this->m_iRow = (int) $p_iRow;
    }

    /**
     * @return integer
     */
    public function getRow()
    {
        return $this->m_iRow;
    }

    /**
     * @param $p_iColumn
     */
    public function setColumn($p_iColumn)
    {
        $this->m_iColumn = (int) $p_iColumn;
    }

    /**
     * @return integer
     */
    public function getColumn()
    {
        return $this->m_iColumn;
    }

    /**
     * @param $p_iX
     */
    public function setX($p_iX)
    {
        $this->m_iX = $p_iX;
    }

    /**
     * @return integer
     */
    public function getX()
    {
        return $this->m_iX;
    }

    /**
     * @param $p_iY
     */
    public function setY($p_iY)
    {
        $this->m_iY = $p_iY;
    }

    /**
     * @return integer
     */
    public function getY()
    {
        return $this->m_iY;
    }


////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return DayBlockDimensions
     */
    static public function createFromParentDimensions()
    {
        if(!isset(self::$m_oParent))
        {
            throw new Exception('Can not create from parent: No parent set');
        }
        else
        {
            return new self(self::$m_iBlockWidth, self::$m_iBlockHeight);
        }
    }

    /**
     * @param Dimensions $p_oParent
     */
    static public function setDimensionsFromParent(Dimensions $p_oParent)
    {
        if (!isset(self::$m_oParent))
        {
            self::$m_oParent = $p_oParent;

            // Measurements in comments are pixels in original design
            self::$m_iBlockHeight = $p_oParent->getHeight()/11.5;            // 216
            self::$m_iBlockWidth  = $p_oParent->getWidth() / 8.1203703703705; // 216

            self::$m_iTopOffset  = $p_oParent->getHeight()/ 7.2390670553936;  // 343
            self::$m_iLeftOffset = $p_oParent->getWidth() /11.101265822785;  // 158

            self::$m_iLineHeight = $p_oParent->getHeight()/522.94736842105;   // 4.75
            self::$m_iLineWidth  = $p_oParent->getWidth() /417.61904761905;   // 4.2
        }#if
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

#EOF