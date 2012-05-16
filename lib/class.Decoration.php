<?php
class Decoration extends DatePeriod
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var DecorationType
     */
    protected $m_oType;
    protected $m_sTitle;
    protected $m_oStartDate;
    protected $m_oInterval;
    protected $m_oEndDate;

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function setType(DecorationType $m_oType)
    {
        $this->m_oType = $m_oType;
    }

    public function getType()
    {
        return $this->m_oType;
    }

    public function setTitle($p_sTitle)
    {
        $this->m_sTitle = (string) $p_sTitle;
    }

    public function getTitle()
    {
        return $this->m_sTitle;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function __construct (DateTime $p_oStartDate, DateInterval $p_oInterval, DateTime $p_oEndDate, $p_iOptions=0) {
        $this->m_oStartDate = $p_oStartDate;
        $this->m_oInterval  = $p_oInterval;
        $this->m_oEndDate   = $p_oEndDate;

        parent::__construct($p_oStartDate, $p_oInterval, $p_oEndDate, $p_iOptions);
    }
    /**
     * @return DateTime
     */
        public function getStartDate()
    {
        return $this->m_oStartDate;
    }

    /**
     * @return DateTime
     */
        public function getEndDate()
    {
        return $this->m_oEndDate;
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