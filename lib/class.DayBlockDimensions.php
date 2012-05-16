<?php

class DayBlockDimensions extends Dimensions
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var Dimensions
     */
    protected static $m_oParent;

    static protected $m_iBlockHeight;
    static protected $m_iBlockWidth;

    static protected $m_iTopOffset;
    static protected $m_iLeftOffset;

    static protected $m_iLineWidth;
    static protected $m_iLineHeight;

    protected $m_iRow;
    protected $m_iColumn;

    protected $m_iX;
    protected $m_iY;


////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function getHeight()
    {
        return self::getBlockHeight();
    }

    public function getWidth()
    {
        return self::getBlockWidth();
    }

    static public function getBlockHeight()
    {
        return self::$m_iBlockHeight;
    }

    static public function getBlockWidth()
    {
        return self::$m_iBlockWidth;
    }

    static public function getTopOffset()
    {
        return self::$m_iTopOffset;
    }

    static public function getLineHeight()
    {
        return self::$m_iLineHeight;
    }

    static public function getLeftOffset()
    {
        return self::$m_iLeftOffset;
    }

    static public function getLineWidth()
    {
        return self::$m_iLineWidth;
    }

    public function setRow($p_iRow)
    {
        $this->m_iRow = (int) $p_iRow;
    }

    public function getRow()
    {
        return $this->m_iRow;
    }

    public function setColumn($p_iColumn)
    {
        $this->m_iColumn = (int) $p_iColumn;
    }

    public function getColumn()
    {
        return $this->m_iColumn;
    }

    public function setX($p_iX)
    {
        $this->m_iX = $p_iX;
    }

    public function getX()
    {
        return $this->m_iX;
    }

    public function setY($p_iY)
    {
        $this->m_iY = $p_iY;
    }

    public function getY()
    {
        return $this->m_iY;
    }


////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
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