<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 *
 */
class DecorationType extends \Potherca\Base\Data //@TODO: Create an ENUM type? Maybe in base? Or use ARC?
{
    const BIRTHDAY         = 'birthday';
    const NATIONAL_HOLIDAY = 'national holiday';
    const SCHOOL_HOLIDAY   = 'school holiday';
    const SECULAR_HOLIDAY  = 'secular holiday';
    const CUSTOM           = 'custom decoration';

    /**
     * @var string
     */
    protected $m_sType;

    /**
     * @param $p_sType
     */
    public function __construct($p_sType)
    {
        $this->setType($p_sType);
    }

    /**
     * @param $p_sType
     *
     * @throws Exception
     */
    protected function setType($p_sType)
    {
        $sType = trim($p_sType);
        if (!defined('self::' . $sType)) {
            throw new Exception('There is no type "' . $sType . '"');
        } else {
            $this->m_sType = constant(__CLASS__ . '::' . $sType);
        }
        #if
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->m_sType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getType();
    }
}


#EOF
