<?php

/**
 *
 */
class Decoration extends DatePeriod
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var DecorationType
     */
    protected $m_oType;
    /**
     * @var string
     */
    protected $m_sTitle;
    /**
     * @var DateTime
     */
    protected $m_oStartDate;
    /**
     * @var DateInterval
     */
    protected $m_oInterval;
    /**
     * @var DateTime
     */
    protected $m_oEndDate;

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DecorationType $m_oType
     */
    public function setType(DecorationType $m_oType)
    {
        $this->m_oType = $m_oType;
    }

    /**
     * @return DecorationType
     */
    public function getType()
    {
        return $this->m_oType;
    }

    /**
     * @param $p_sTitle
     */
    public function setTitle($p_sTitle)
    {
        $this->m_sTitle = (string) $p_sTitle;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->m_sTitle;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oStartDate
     * @param DateInterval $p_oInterval
     * @param DateTime $p_oEndDate
     * @param int $p_iOptions
     */
    public function __construct (DateTime $p_oStartDate, DateInterval $p_oInterval, DateTime $p_oEndDate, $p_iOptions=0) {
        $this->m_oStartDate = $p_oStartDate;
        $this->m_oInterval  = $p_oInterval;
        $this->m_oEndDate   = $p_oEndDate;

        parent::__construct($p_oStartDate, $p_oInterval, $p_oEndDate, $p_iOptions);
    }
    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->m_oStartDate;
    }

    /**
     * @param \DateTime
     */
    public function setStartDate(\DateTime $p_oStartDate)
    {
        $this->m_oStartDate = $p_oStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->m_oEndDate;
    }

    /**
     * @param \DateTime
     */
    public function setEndDate(\DateTime $p_oEndDate)
    {
        $this->m_oEndDate = $p_oEndDate;
    }

    /**
     * @return int The duration of the decoration in days
     */
    public function getDuration()
    {
        //@TODO: Calculate the actual duration (using DateInterval)?
        $iSeconds = (int) $this->getEndDate()->format('U') - (int) $this->getStartDate()->format('U');
        return (int) ($iSeconds/60/60/24);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getStartDate()->format('c')
            . ' '
            . $this->getEndDate()->format('c')
        ;
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

}


#EOF