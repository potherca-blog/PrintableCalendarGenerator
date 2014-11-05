<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 *
 */
class ScratchImage extends AbstractImage
{
    /**
     * @var array
     */
    private $m_aColors;

    /**
     * @param AbstractDimensions $p_oDimensions
     * @param string $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(
        AbstractDimensions $p_oDimensions,
        $p_sType = 'png',
        $p_bAlpha = true
    ) {
        parent::__construct($p_oDimensions, $p_sType, $p_bAlpha);

        parent::create();

        $this->buildColors();
    }

    /**
     * @param array $p_sBackgroundColor
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function buildColors($p_sBackgroundColor = array('0xF1', '0x00', '0xF1'))
    {
        // set background color first
        $this->m_aColors = array(
            'background' => $this->allocateColor(
                $p_sBackgroundColor[0], $p_sBackgroundColor[1],
                $p_sBackgroundColor[2], 127
            )
        );

        $this->defineTransparentColor($this->m_aColors['background']);

        // set common colors
        $this->m_aColors['white'] = $this->allocateColor(0xFF, 0xFF, 0xFF);
        $this->m_aColors['black'] = $this->allocateColor(0x00, 0x00, 0x00);

        $this->m_aColors['red'] = $this->allocateColor(0xFF, 0x00, 0x00);
        $this->m_aColors['blue'] = $this->allocateColor(0x00, 0x00, 0xFF);
        $this->m_aColors['green'] = $this->allocateColor(0x00, 0xFF, 0x00);

        $this->m_aColors['magenta'] = $this->allocateColor(0xFF, 0x00, 0xFF);
        $this->m_aColors['cyan'] = $this->allocateColor(0x00, 0xFF, 0xFF);
        $this->m_aColors['yellow'] = $this->allocateColor(0xFF, 0xFF, 0x00);

        $this->m_aColors['Weekend'] = $this->allocateColor(0xBF, 0xBF, 0xBF);
        $this->m_aColors['Holiday'] = $this->allocateColor(0xAA, 0xAB, 0xAA);

        $this->m_aColors['Week_Nr'] = $this->allocateColor(0xCD, 0xCD, 0xCC);
        $this->m_aColors['Week_Nr_Border'] = $this->allocateColor(0x66, 0x66, 0x66);
        $this->m_aColors['Week_Nr_Divider'] = $this->allocateColor(0xBF, 0xBC, 0xBC);

        $this->m_aColors[DecorationType::BIRTHDAY] = $this->allocateColor( 0xFF, 0xFF, 0x00, 64);
        $this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = $this->allocateColor( 0x00, 0xFF, 0xFF, 64);
        $this->m_aColors[DecorationType::SCHOOL_HOLIDAY] = $this->allocateColor( 0xFF, 0x00, 0xFF, 64);
        $this->m_aColors[DecorationType::SECULAR_HOLIDAY] = $this->allocateColor( 0x00, 0x00, 0xFF, 64);

        return $this->m_aColors;
    }
}

/*EOF*/
