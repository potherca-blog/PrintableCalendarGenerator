<?php
abstract class Dimensions
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected $m_iHeight;
    protected $m_iWidth;

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected function setHeight($p_iHeight)
    {
        $this->m_iHeight = (int) $p_iHeight;
    }

    public function getHeight()
    {
        return $this->m_iHeight;
    }

    protected function setWidth($p_iWidth)
    {
        $this->m_iWidth = (int) $p_iWidth;
    }

    public function getWidth()
    {
        return $this->m_iWidth;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function __construct($p_iWidth, $p_iHeight)
    {
        $this->setWidth($p_iWidth);
        $this->setHeight($p_iHeight);
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

#EOF