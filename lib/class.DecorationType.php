<?php
class DecorationType
{
    const BIRTHDAY         = 'birthday';
    const NATIONAL_HOLIDAY = 'national holiday';
    const SCHOOL_HOLIDAY   = 'school holiday';
    const SECULAR_HOLIDAY  = 'secular holiday';

    protected $m_sType;

    public function __construct($p_sType)
    {
        $this->setType($p_sType);
    }

    protected function setType($p_sType)
    {
        if (!defined('self::' . $p_sType)) {
            throw new Exception('There is no type "' . $p_sType . '"');
        }
        else
        {
            $this->m_sType = constant(__CLASS__ . '::' . $p_sType);
        }
        #if
    }

    public function getType()
    {
        return $this->m_sType;
    }

    public function __toString()
    {
        return $this->getType();
    }
}


#EOF