<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 *
 */
class CalendarDimensions extends AbstractDimensions
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var integer*/
    private static $m_iBlockWidth;
    /** @var integer*/
    private static $m_iBlockHeight;
    /** @var int*/
    private $m_iHeight;
    /** @var int*/
    private $m_iWidth;
    /** @var integer*/
    private $m_iRow;
    /** @var integer*/
    private $m_iColumn;
    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iHeight
     */
    private function setHeight($p_iHeight)
    {
        $this->m_iHeight = (int) $p_iHeight;
    }

    /**
     * @return int
     */
    final public function getHeight()
    {
        return $this->m_iHeight;
    }

    /**
     * @param $p_iWidth
     */
    private function setWidth($p_iWidth)
    {
        $this->m_iWidth = (int) $p_iWidth;
    }

    /**
     * @return int
     */
    final public function getWidth()
    {
        return $this->m_iWidth;
    }

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
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iWidth
     * @param $p_iHeight
     */
    final public function  __construct($p_iWidth, $p_iHeight)
    {
        $this->setWidth($p_iWidth);
        $this->setHeight($p_iHeight);
    }
    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

#EOF
