<?php

class CalendarDimensions extends Dimensions
{
    protected static $m_iBlockWidth;
    protected static $m_iBlockHeight;

    protected $m_iRow;
    protected $m_iColumn;


    public function getBlockHeight()
    {
        if(!isset(self::$m_iBlockHeight))
        {
            self::$m_iBlockHeight = $this->getHeight()/11.5;         // 216
        }
        return self::$m_iBlockHeight;
    }

    public function getBlockWidth()
    {
        if(!isset(self::$m_iBlockWidth))
        {
            self::$m_iBlockWidth = $this->getWidth()/8.1203703703705;// 216
        }
        return self::$m_iBlockWidth;
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
}

#EOF