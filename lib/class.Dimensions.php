<?php
/**
 *
 */
abstract class Dimensions
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var int
     */
    protected $m_iHeight;
    /**
     * @var int
     */
    protected $m_iWidth;

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iHeight
     */
    protected function setHeight($p_iHeight)
    {
        $this->m_iHeight = (int) $p_iHeight;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->m_iHeight;
    }

    /**
     * @param $p_iWidth
     */
    protected function setWidth($p_iWidth)
    {
        $this->m_iWidth = (int) $p_iWidth;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->m_iWidth;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_iWidth
     * @param $p_iHeight
     */
    public function __construct($p_iWidth, $p_iHeight)
    {
        $this->setWidth($p_iWidth);
        $this->setHeight($p_iHeight);
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

#EOF