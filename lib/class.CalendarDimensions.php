<?php

/**
 *
 */
class CalendarDimensions extends Dimensions
{
    /**
     * @var integer
     */
    protected static $m_iBlockWidth;
    /**
     * @var integer
     */
    protected static $m_iBlockHeight;

    /**
     * @var integer
     */
    protected $m_iRow;
    /**
     * @var integer
     */
    protected $m_iColumn;


    /**
     * @return float
     */
    public function getBlockHeight()
    {
        if(!isset(self::$m_iBlockHeight))
        {
            self::$m_iBlockHeight = $this->getHeight()/11.5;         // 216
        }
        return self::$m_iBlockHeight;
    }

    /**
     * @return float
     */
    public function getBlockWidth()
    {
        if(!isset(self::$m_iBlockWidth))
        {
            self::$m_iBlockWidth = $this->getWidth()/8.1203703703705;// 216
        }
        return self::$m_iBlockWidth;
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
}

#EOF