<?php

/**
 *
 */
class ScratchImage extends Image
{
    /**
     * @var bool
     */
    protected $m_bDebug = false;

    /**
     * @var array
     */
    public $m_aColors;

    /**
     * @param array $p_aColorSets
     * @param array $p_sBackgroundColor
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function buildColors($p_aColorSets = array(), $p_sBackgroundColor=array('0xF1', '0x00','0xF1'))
    {

        if(!isset($this->m_rImage))
        {
            throw new Exception('Cannot allocate colors, Image has not yet been created. Please invoke the "create" or "loadFromFile" method before trying to build colors.');
        }
        else
        {
            // set background color first
            $this->m_aColors = array(
                'background' => imagecolorallocatealpha($this->m_rImage, $p_sBackgroundColor[0], $p_sBackgroundColor[1], $p_sBackgroundColor[2], 127),
            );

            imagecolortransparent($this->m_rImage, $this->m_aColors['background']);

            // set common colors
            $this->m_aColors['white'] = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0xFF);
            $this->m_aColors['black'] = imagecolorallocate($this->m_rImage, 0x00, 0x00,0x00);

            $this->m_aColors['red']   = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0x00);
            $this->m_aColors['blue']  = imagecolorallocate($this->m_rImage, 0x00, 0x00,0xFF);
            $this->m_aColors['green'] = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0x00);

            $this->m_aColors['magenta'] = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0xFF);
            $this->m_aColors['cyan']    = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0xFF);
            $this->m_aColors['yellow']  = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0x00);

            $this->m_aColors['Weekend'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBF, 0xBF);
            $this->m_aColors['Holiday'] = imagecolorallocate($this->m_rImage, 0xAA, 0xAB, 0xAA);

            $this->m_aColors['Week_Nr']         = imagecolorallocate($this->m_rImage, 0xCD, 0xCD, 0xCC);
            $this->m_aColors['Week_Nr_Border']  = imagecolorallocate($this->m_rImage, 0x66, 0x66, 0x66);
            $this->m_aColors['Week_Nr_Divider'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBC, 0xBC);

            $this->m_aColors[DecorationType::BIRTHDAY]         = imagecolorallocatealpha($this->m_rImage, 0xFF, 0xFF, 0x00, 64);
            $this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = imagecolorallocatealpha($this->m_rImage, 0x00, 0xFF, 0xFF, 64);
            $this->m_aColors[DecorationType::SCHOOL_HOLIDAY]   = imagecolorallocatealpha($this->m_rImage, 0xFF, 0x00, 0xFF, 64);
            $this->m_aColors[DecorationType::SECULAR_HOLIDAY]  = imagecolorallocatealpha($this->m_rImage, 0x00, 0x00, 0xFF, 64);

            return $this->m_aColors;
        }
    }

    /**
     * @param Dimensions $p_oDimensions
     * @param string $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(Dimensions $p_oDimensions, $p_sType='png', $p_bAlpha=true)
    {
        parent::__construct($p_oDimensions, $p_sType, $p_bAlpha);

        parent::create();

        $this->m_sFontDirectory = './fonts';
        $this->m_sFontPath      = '/erasblkb.pfb';

        $this->buildColors();
    }
}
#EOF