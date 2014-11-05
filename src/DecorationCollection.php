<?php

namespace Potherca\PrintableCalendarGenerator;


/**
 *
 */
class DecorationCollection extends \Potherca\Base\Data implements \ArrayAccess, \Countable
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var array
     */
    protected $m_aCollection  = array();

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return int
     */
    public function count()
    {
        return count($this->m_aCollection);
    }
    /**
     * @param $p_mOffset
     * @return bool
     */
    public function offsetExists($p_mOffset)
    {
        return isset($this->m_aCollection[$p_mOffset]);
    }

    /**
     * @param $p_mOffset
     * @return mixed
     */
    public function offsetGet($p_mOffset)
    {
        return $this->m_aCollection[$p_mOffset];
    }

    /**
     * @param $p_mOffset
     */
    public function offsetUnset($p_mOffset)
    {
        unset($this->m_aCollection[$p_mOffset]);
    }

    /**
     * @param $p_mOffset
     * @param $p_oValue
     * @throws \InvalidArgumentException
     */
    public function offsetSet($p_mOffset, $p_oValue)
    {
        if (! $p_oValue instanceof Decoration)
        {
            throw new \InvalidArgumentException('Expected object of type "Decoration" instead was given "'.'"');
        }
        else
        {
            $this->m_aCollection[$p_mOffset] = $p_oValue;
        }
    }
    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}
#EOF
