<?php
/**
 * @package ENG
 * @desc All classes related to engineering are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Need these functions
 */
include_once 'common.inc.php';
include_once 'vault.inc.php';
include_once 'fpdf/fpdf.php';

/**
 * Bill of materials class
 *
 * @package ENG
 */
class tldBOM
{
    /**
     * ERP company number
     *
     * @var integer
     */
    public $itsERP;
    /**
     * Header
     *
     * @var array
     */
    public $itsHeader;
    /**
     * Part number
     *
     * @var string
     */
    public $itsID;
    /**
     * Effectivity date
     *
     * @var string
     */
    public $itsDate;
    /**
     * BOM as array
     *
     * @var mixed array or arrays, one array per part
     */
    public $itsBOMAsArray;
    /**
     * EDM Object for getting
     *
     * provides edm functions
     *
     * @var tldEDM
     */
    public $itsTLDEDM;
    /**
     * Flag designator for multilevel BOM
     *
     * @var boolean
     */
    public $itsML;
    /** @var string */
    private $itsLang;

    /**
     * Constructor
     *
     * @param int $erp ERP company number
     * @param string $id PN of bom
     * @param string $date string rep of effectivity date
     * @param boolean $isML specifies if bom is multi level
     * @param array|string $options Array of options
     * @param array|null $data
     */
    public function __construct(int $erp, string $id, string $date = '', bool $isML = true, array $options = [], array $data = null)
    {
        $this->itsERP = $erp;
        $this->itsID = strtoupper($id);
        $this->itsLang = $options['lang'] ?? 'EN';

        $this->itsDate = empty($date) ? date('Y-m-d') : $date;
        $this->itsTLDEDM = new basicEDM($erp);
        $this->itsML = $isML; //true if multi level

        if (null === $data) {
            throw new Exception('This is no longer used');
        }

        $this->transformItem($data);
        $bomData[] = $data;
        $this->transformLnToBann($data['items'], $bomData);
        $bomData[0]['items'] = [];
        $this->itsBOMAsArray = $bomData;
    }

    public function transformLnToBann(&$data, &$bomData): void
    {
        foreach ($data as &$child) {
            $this->transformItem($child);
            $bomData[] = $child;

            if (isset($child['items'])) {
                $child['children'] = $child['items'];
            }

            if (!empty($child['children']) && \is_array($child['children'] ?? null)) {
                $this->transformLnToBann($child['children'], $bomData);
            }

            $child['children'] = [];
        }
    }

    private function transformItem(array &$data): void
    {
        $effectiveDate = null;
        $expiryDate = null;

        if (array_key_exists('engineeringRevisionEffectiveDate', $data)) {
            $effectiveDate = new \DateTime($data['engineeringRevisionEffectiveDate']);
            $effectiveDate = $effectiveDate->format('Y-m-d');
        }

        if (array_key_exists('engineeringRevisionExpiryDate', $data)) {
            $expiryDate = new \DateTime($data['engineeringRevisionExpiryDate']);
            $expiryDate = $expiryDate->format('Y-m-d');
        }

        $data['erp'] = $this->itsERP;
        $data['t_cprj'] = $this->itsID;
        $data['t_pono'] = $data['position'] ?? null;
        $data['t_sitm'] = $data['partNumber'] ?? $data['product'];
        $data['t_mitm'] = $data['standardItem'] ?? null;
        $data['t_csig'] = $data['itemSignalCode'] ?? null;
        $data['t_dsca'] = $data['itemDescription'] ?? null;
        $data['altdsca'] =  isset($data['itemOtherDescription']) ? mb_convert_encoding($data['itemOtherDescription'], 'HTML-ENTITIES', 'UTF-8') : null;
        $data['t_qana'] = $data['quantity'] ?? null;
        $data['itm_dsca'] = $data['itemDescription'] ?? null;
        $data['rev_desc'] = $data['engineeringDescription'] ?? null;
        $data['t_csel'] = $data['itemSelectionCode'] ?? null;
        $data['edm_csel'] = $data['engineeringSelectionCode'] ?? null;
        $data['t_csig_edm'] = $data['engineeringSignalCode'] ?? null;
        $data['t_opol'] = $data['customized'] ?? null;
        $data['t_opno'] = $data['operation'] ?? null;
        $data['t_exin'] = $data['extraInformation'] ?? null;
        $data['mitm_title'] = $data['itemDescription'] ?? null;
        $data['t_csig_fullname'] = $data['signalCodeDescription'] ?? null;
        $data['P'] = (isset($data['preventive']) && $data['preventive']) ? 'P' : null;
        $data['M'] = (isset($data['maintenance']) && $data['maintenance']) ? 'M' : null;
        $data['O'] = (isset($data['overhaul']) && $data['overhaul']) ? 'O' : null;
        $data['C'] = (isset($data['critical']) && $data['critical']) ? 'C' : null;
        $data['t_cuni'] = $data['unitOfMeasure'] ?? null;
        $data['t_revi'] = $data['engineeringRevision'] ?? null;
        $data['t_kitm'] = $data['itemType'] ?? null;
        $data['t_citg'] = $data['itemGroup'] ?? null;
        $data['t_suno'] = $data['buyFromBusinessPartner'] ?? null;
        $data['t_nama'] = $data['buyFromBusinessPartnerName'] ?? null;
        $data['t_indt'] = $effectiveDate;
        $data['t_exdt'] = $expiryDate;
        $data['t_oltm'] = $data['supplyTime'] ?? null;
        $data['t_cwar'] = $data['warehouse'] ?? null;
        $data['t_bfcp'] = $data['backflushIfMaterial'] ?? null;
        $data['t_cpha'] = $data['phantom'] ?? null;
        $data['rev_desc'] = $data['engineeringRevisionDescription'] ?? null;
    }

    public function __toString()
    {
        return $this->itsID;
    }

    /**
     * Return single level bom as an array list
     *
     * @return array
     */
    public function getBOMList()
    {
        return $this->itsBOMAsArray;
    }

    /**
     * @deprecated
     */
    public function getBOM_Array()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get the jpg file object
     *
     * Returns a basicFile object if the file exists, otherwise false
     * @return basicFile|bool
     */
    public function getJPGFile()
    {
        return $this->getFile('jpg');
    }

    public function getPDFFile()
    {
        return $this->getFile('pdf');
    }

    public function getFile($ext = 'jpg')
    {
        if (!in_array($ext, ['pdf', 'jpg'], true)) {
            return 'ERROR: only jpg or pdf allowed';
        }

        $rev = str_replace('.', '', $this->itsBOMAsArray['t_revi']);

        $revName = $rev !== 'REL' && !empty($rev) ? "_$rev" : '';
        $filename = trim($this->itsID) . $revName . ".$ext";
        $vault = new tldReleasedController();
        $filepath = $vault->getPathToFile($this->itsERP, $filename);
        $file = new basicFile($filepath);

        return $file->fileExists() ? $file : false;
    }

    /**
     * @deprecated
     */
    public function getHeader()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    private function getBOM_ArrayRec(&$result, $mitm, $level)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    private function generateBomKey(array $sitm, ?string $parentKey = null): string
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    private function getSubItems_Array($pns)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getMaterialList(&$result)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getMaterialListREC($a, &$result)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function outZippedFiles()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getERPID()
    {
        return $this->itsERP;
    }
}

/**
 *
 * Engineering Bill of materials class
 *
 * @package ENG
 */
class tldEDMBOM
{
    /**
     * Header
     *
     * @var array
     */
    public $itsHeader;
    /**
     * Part number
     *
     * @var string
     */
    public $itsID;

    /**
     * Effectivity date
     *
     * @var string
     */
    public $itsDate;
    /**
     * BOM as array
     *
     * @var mixed array or arrays, one array per part
     */
    public $itsBOMAsArray;

    private $itsLang;

    /**
     * Constructor
     *
     * @param integer $id PN of bom
     * @param string $date string rep of effectivity date
     * @param array $options Array of options
     */
    public function __construct($id, $date = '', $options = [], ?array $data = null)
    {
        $this->itsID = strtoupper($id);
        //define the language to use for the description shown in the BOM
        $this->itsLang = 'EN';
        if (is_array($options) && isset($options['lang']) && $options['lang']) {
            $this->itsLang = $options['lang'];
        }

        $this->itsDate = empty($date) ? date('Y-m-d') : $date;

        if (null === $data) {
            throw new Exception('This is no longer used');
        }

        $this->transformItem($data);
        $bomData[] = $data;
        $this->transformLnToBann($data['items'], $bomData);
        $bomData[0]['items'] = [];
        $this->itsBOMAsArray = $bomData;
    }

    public function transformLnToBann(&$data, &$bomData): void
    {
        foreach ($data as &$child) {
            $this->transformItem($child);

            if (!empty($child['children']) && \is_array($child['children'] ?? null)) {
                $this->transformLnToBann($child['children'], $bomData);
            }

            $child['children'] = [];
            $bomData[] = $child;
        }
    }

    private function transformItem(array &$data): void
    {
        $effectiveDate = null;
        $expiryDate = null;

        if (array_key_exists('engineeringRevisionEffectiveDate', $data)) {
            $effectiveDate = new \DateTime($data['engineeringRevisionEffectiveDate']);
            $effectiveDate = $effectiveDate->format('Y-m-d');
        }

        if (array_key_exists('engineeringRevisionExpiryDate', $data)) {
            $expiryDate = new \DateTime($data['engineeringRevisionExpiryDate']);
            $expiryDate = $expiryDate->format('Y-m-d');
        }

        $data['erp'] = $this->itsERP;
        $data['t_cprj'] = $this->itsID;
        $data['t_pono'] = $data['position'];
        $data['t_sitm'] = $data['partNumber']?? $data['product'];
        $data['t_mitm'] = $data['standardItem'];
        $data['t_csig'] = $data['itemSignalCode'];
        $data['t_dsca'] = $data['engineeringDescription'];
        $data['altdsca'] = mb_convert_encoding($data['engineeringOtherDescription'], 'HTML-ENTITIES', 'UTF-8');
        $data['t_qana'] = $data['quantity'];
        $data['itm_dsca'] = $data['engineeringDescription'];
        $data['rev_desc'] = $data['engineeringDescription'];
        $data['t_csel'] = $data['engineeringSelectionCode'];
        $data['edm_csel'] = $data['engineeringSelectionCode'];
        $data['t_csig_edm'] = $data['engineeringSignalCode'];
        $data['t_opol'] = $data['customized'];
        $data['t_opno'] = $data['operation'];
        $data['t_exin'] = $data['extraInformation'];
        $data['mitm_title'] = $data['engineeringDescription'];
        $data['t_csig_fullname'] = $data['signalCodeDescription'];
        $data['P'] = (bool) $data['preventive'] ? 'P' : null;
        $data['M'] = (bool) $data['maintenance'] ? 'M' : null;
        $data['O'] = (bool) $data['overhaul'] ? 'O' : null;
        $data['C'] = (bool) $data['critical'] ? 'C' : null;
        $data['t_cuni'] = $data['unitOfMeasure'];
        $data['t_revi'] = $data['engineeringRevision'];
        $data['t_kitm'] = $data['itemType'];
        $data['t_citg'] = $data['itemGroup'];
        $data['t_indt'] = $effectiveDate;
        $data['t_exdt'] = $expiryDate;
        $data['rev_desc'] = $data['engineeringRevisionDescription'];
    }

    /**
     * Return single level bom as an array list
     *
     * @return array
     */
    public function getBOMList()
    {
        return $this->itsBOMAsArray;
    }

    public function getJPGFile()
    {
        // This will allow to use the BOM PDF class to input only the BOM and not the JPG
        return false;
    }

    /**
     * @deprecated
     */
    public function getBOM_Array()
    {
        throw new Exception('This is no longer used');
    }


    /**
     * @deprecated
     */
    public function getHeader()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    private function getSubItems_Array($pn)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getERPID()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function aeroBOMAndEDMReport($begin, $end)
    {
        throw new Exception('This is no longer used');
    }
}

class tldBOMPDF extends FPDF
{
    public $itsLMargin = 15;
    public $itsRMargin = 15;
    public $itsTMargin = 15;
    public $itsBMargin = 20;
    public $itsWidth;
    public $itsHeight;
    public $itsERP;
    public $itsPN;
    public $itsDate;
    public $itsBOM;
    private $itsPreviousPageType;
    protected $hidePageNumbers = false;

    public function __construct($erp, $pn, $date, ?string $orientation = 'P', ?bool $hidePageNumbers = false, ?tldBOM $bom = null)
    {
        if (null === $bom) {
            throw new Exception('This is no longer used');
        }

        $this->itsERP = $erp;
        $this->itsPN = $pn;
        $this->itsDate = $date;
        $this->itsBOM = $bom;
        $this->hidePageNumbers = (bool) $hidePageNumbers;
        $this->doPageSetup($orientation);
        $this->doBooklet($orientation);
    }


    /**
     * Create the booklet
     *
     * @return integer page number of start of booklet
     */
    public function doBooklet(?string $orientation = 'P')
    {
        $this->SetTitle("BOM Booklet $this->itsERP-$this->itsPN-$this->itsDate");
        //JPG page
        $this->doDiagramPage();
        // setup the page number that will be used in the TOC for this Part
        $result = $this->PageNo();

        //table pages
        $this->doPartsTablePage('L' !== ($orientation ?? 'P'));
        if ($this->PageNo() % 2 <> 0) {
            $this->doIntentionallyPage();
        }
        return $result;
    }

    /**
     * Setup the page params
     *
     */
    public function doPageSetup(?string $orientation = 'P')
    {
        parent::__construct($orientation ?? 'P', 'mm', 'Letter');
        $this->SetAutoPageBreak(true, $this->itsBMargin);
        $this->itsWidth = $this->getPrintWidth();
        $this->itsHeight = $this->getPrintHeight();
        $this->AliasNbPages();
        $this->SetMargins($this->itsLMargin,
            $this->itsTMargin,
            $this->itsRMargin);
        $this->SetFont('Arial', 'B', 10);
        $this->SetAuthor('Graham FONG');
        $this->SetDisplayMode('fullpage', 'TwoColumnRight');
    }

    /**
     * Create the cover page
     *
     * @return integer page number of coverpage
     */
    public function doCoverPage()
    {
        //Cover page
        $this->AddPage();
        $result = $this->PageNo();
        //Go to 1.5 cm from bottom
        $this->SetY(50);
        $header = $this->itsBOM->itsHeader;

        $t = 'Part Number: ' . $header['t_sitm'] . "\n" .
            'Revision: ' . $header['t_revi'] . "\n" .
            'Description: ' . $header['t_dsca'] . "\n" .
            'Effective Date: ' . $header['t_indt'] . "\n";
        if ($header['t_exdt'] <> '1753-01-01') {
            $t .= 'Expiration Date: ' . $header['t_exdt'];
        }
        $this->SetFont('Arial', 'B', 24);
        $this->MultiCell(0, 24,
            $t,
            0,
            'L');
        return $result;
    }

    public function doIntentionallyPage()
    {
        //Cover page
        $this->AddPage();
        $this->SetY(50);
        $this->SetFont('Arial', 'B', 24);
        $this->MultiCell(0, 24,
            'This page left intentionally blank',
            0, 'C');
    }

    public function doDiagramPage()
    {
        $file = $this->itsBOM->getJPGFile();
        if ($file <> false) {
            [$width, $height, $type, $attr] = getimagesize($file->itsFilepath);
            //set orientation
            if ($width > $height) {
                $pFilename = 'p_' . $file->getBasename();
                $pPath = '/tmp/' . $pFilename;
                $src = imagecreatefromjpeg($file->itsFilepath);
                $dest = imagerotate($src, 90, 0);
                imagejpeg($dest, $pPath);
                $file = new basicFile($pPath);
                $temp = $width;
                $width = $height;
                $height = $temp;
            }
            //resize
            $MAX_HEIGHT = 210;
            $MAX_WIDTH = 180;
            if ($width / $MAX_WIDTH > $height / $MAX_HEIGHT) {
                $ratio = $MAX_WIDTH / $width;
            } else {
                $ratio = $MAX_HEIGHT / $height;
            }

            $newW = $width * $ratio;
            $newH = $height * $ratio;
            //center on page
            $x = ($this->itsWidth - $newW) / 2;
            $y = ($this->itsHeight - $newH) / 2;

            $this->AddPage();
            $this->Image($file->itsFilepath,
                $this->itsLMargin + $x,
                $this->itsTMargin + $y,
                $newW, $newH);
        }
    }

    /**
     * Do the parts table
     *
     */
    public function doPartsTablePage(bool $withPMOC = true)
    {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 10);
        //table header
        $y = $this->GetY();
        $this->Line($this->itsLMargin, $y,
            $this->getPrintWidth() + $this->itsLMargin, $y);
        $this->doPartsTableTitles($withPMOC);
        $y = $this->GetY();
        $this->Line($this->itsLMargin, $y,
            $this->getPrintWidth() + $this->itsLMargin, $y);

        $rows = $this->itsBOM->itsBOMAsArray;
        foreach ($rows as $row) {

            $this->Cell(12, 6, $row['t_pono'], 0, 0, 'L');
            $this->Cell(28, 6, $row['t_sitm'], 0, 0, 'L');
            $this->Cell(20, 6, $row['t_qana'], 0, 0, 'C');
            $this->Cell(10, 6, $row['t_cuni'], 0, 0, 'C');

            // Mark start coords for the multicell (description)
            $x = $this->GetX();
            $y1 = $this->GetY();

            //MultiCell use for the description (due to French case, description for Europe display En description and FR is available)
            $this->MultiCell(95, 6, $row['t_dsca'], 0, 'L');
            // Mark End Y coord for the multicell (description)
            $y2 = $this->GetY();
//			$yH = $y2 - $y1;
            // set the height of the PMOC cells
            $yH = 6;
            // set the start coords of the PMOC cells
            $this->SetXY($x + 95, $y1);

            if ($withPMOC) {
                if ($row['P'] <> 0 || $row['P'] === 'P') {
                    $this->Cell(5, $yH, $row['P'], 0, 0, 'C');
                } else {
                    $this->Cell(5, $yH, '', 0, 0, 'C');
                }
                if ($row['M'] <> 0 || $row['M'] === 'M') {
                    $this->Cell(5, $yH, $row['M'], 0, 0, 'C');
                } else {
                    $this->Cell(5, $yH, '', 0, 0, 'C');
                }
                if ($row['O'] <> 0 || $row['O'] === 'O') {
                    $this->Cell(5, $yH, $row['O'], 0, 0, 'C');
                } else {
                    $this->Cell(5, $yH, '', 0, 0, 'C');
                }
                if ($row['C'] <> 0 || $row['C'] === 'C') {
                    $this->Cell(5, $yH, $row['C'], 0, 0, 'C');
                } else {
                    $this->Cell(5, $yH, '', 0, 0, 'C');
                }
            }

            // set the End coords of the line to the same height of the last line of the Description's MultiCell
            // $y2-6 was used because there seems to be an extra line when using the $y2 (the -6 was used to fix this problem).
            $this->SetXY($this->GetX(), $y2 - 6);
            if ($this->GetY() + 20 > $this->itsHeight) {
                $this->doDiagramPage();
                $this->AddPage();
                $this->doPartsTableTitles($withPMOC);
            } else {
                $this->ln();
            }
        }
    }

    /**
     * Put the titles on the parts table
     *
     */
    public function doPartsTableTitles(bool $withPMOC = true)
    {
        //do the table titles
        $this->Cell(10, 6, 'Item', 0, 0, 'C');
        $this->Cell(30, 6, 'PN', 0, 0, 'C');
        $this->Cell(20, 6, 'Qty', 0, 0, 'C');
        $this->Cell(10, 6, 'UM', 0, 0, 'C');
        $this->Cell(95, 6, 'Description', 0, 0, 'C');
        if ($withPMOC) {
            $this->Cell(5, 6, 'P', 0, 0, 'C');
            $this->Cell(5, 6, 'M', 0, 0, 'C');
            $this->Cell(5, 6, 'O', 0, 0, 'C');
            $this->Cell(5, 6, 'C', 0, 0, 'C');
        }

        $this->ln();
    }
    //find dimensions of page

    /**
     * Get the page width
     */
    public function getPrintWidth()
    {
        $x = $this->GetX();
        $this->SetX(-1);
        $result = $this->GetX() + 1 - $this->itsLMargin - $this->itsRMargin;
        $this->SetX($x);
        return $result;
    }

    /**
     * Get the page height
     */
    public function getPrintHeight()
    {
        $y = $this->GetY();
        $this->SetY(-1);
        $result = $this->GetY() + 1 - $this->itsTMargin;
        $this->SetY($y);
        return $result;
    }

    /**
     * Page level Header
     *
     * by default put the logo at top right of page
     */
    public function Header()
    {
        $header = $this->itsBOM->itsHeader;
        switch ($this->itsPageType) {
            case 'blankPage':
                break;
            case 'logoOnly':
                if (250 === (int) $this->itsBOM->itsERP) {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/emails/aero/aero-logo.png', $this->itsWidth - 15, $this->itsTMargin, 35);
                } else {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/icons/tld-icon.jpg', $this->itsWidth - 5, $this->itsTMargin);
                }
                break;

            default:
                if (250 === (int) $this->itsBOM->itsERP) {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/emails/aero/aero-logo.png', $this->itsWidth - 15, $this->itsTMargin, 35);
                } else {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/icons/tld-icon.jpg', $this->itsWidth - 5, $this->itsTMargin);
                }
                $this->SetFont('Arial', 'B', 14);
                $this->MultiCell($this->itsWidth - 5, 6,
                    $header['t_sitm'] . '_' . $header['t_revi'] . "\n" .
                    $header['t_dsca'],
                    0, 'L');
        }
        $this->SetY(40);
    }

    /**
     * Page level Footer
     */
    public function Footer()
    {
        if (true !== $this->hidePageNumbers) {
            switch ($this->itsPreviousPageType) {
                case 'blankPage':
                case 'logoOnly':
                case 'noFooter':
                    break;
                default:
                    //Go to 1.5 cm from bottom
                    $this->SetY(-15);
                    //Select Arial italic 8
                    $this->SetFont('Arial', 'B', 10);
                    //Print centered page number
                    $this->Cell(0, 10, 'Page ' . $this->PageNo(),
                        0, 0, 'C');
            }
        }

        $this->itsPreviousPageType = $this->itsPageType;
    }

    public function setPageType($type)
    {
        $this->itsPreviousPageType = $this->itsPageType;
        $this->itsPageType = $type;
    }

}

class tldEDMBOMPDF extends tldBOMPDF
{
    public function __construct($pn, $date, ?tldEDMBOM $bom = null)
    {
        if (null === $bom) {
            throw new Exception('This is no longer used');
        }

        $this->itsERP = 400;
        $this->itsPN = $pn;
        $this->itsDate = $date;
        $this->itsBOM = $bom;
        $this->doPageSetup();
        $this->doBooklet();
    }

    /**
     * Do the parts table
     *
     */
    public function doPartsTablePage(bool $withPMOC = true)
    {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 10);
        //table header
        $this->doPartsTableTitles($withPMOC);

        foreach ($this->itsBOM->itsBOMAsArray as $row) {
            $this->Cell(12, 6, $row['t_pono'], 0, 0, 'L');
            $this->Cell(28, 6, $row['t_comp'], 0, 0, 'L');

            // Mark start coords for the multicell (description)
            $x = $this->GetX();
            $y1 = $this->GetY();

            //MultiCell use for the description (due to French case, description for Europe display En description and FR is available)
            $this->MultiCell(95, 6, $row['t_dsca'], 0, 'L');
            // Mark End Y coord for the multicell (description)
            $y2 = $this->GetY();
            $this->SetXY($x + 95, $y1);
            $this->Cell(10, 6, $row['t_revi'], 0, 0, 'C');
            $this->Cell(20, 6, $row['t_nqan'], 0, 0, 'C');
            $this->Cell(10, 6, $row['t_cuni'], 0, 0, 'C');

            // set the End coords of the line to the same height of the last line of the Description's MultiCell
            // $y2-6 was used because there seems to be an extra line when using the $y2 (the -6 was used to fix this problem).
            $this->SetXY($this->GetX(), $y2 - 6);
            if ($this->GetY() + 20 > $this->itsHeight) {
                $this->doDiagramPage();
                $this->AddPage();
                $this->doPartsTableTitles($withPMOC);
            } else {
                $this->ln();
            }
        }
    }

    /**
     * Page level Header
     *
     * by default put the logo at top right of page
     *
     */
    public function Header()
    {
        $header = $this->itsBOM->itsHeader;
        switch ($this->itsPageType) {
            case 'blankPage':
                ;
                break;
            case 'logoOnly':
                if (250 === (int) $this->itsBOM->itsERP) {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/emails/aero/aero-logo.png', $this->itsWidth - 15, $this->itsTMargin, 35);
                } else {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/icons/tld-icon.jpg', $this->itsWidth - 5, $this->itsTMargin);
                }
                break;
            default:
                if (250 === (int) $this->itsBOM->itsERP) {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/emails/aero/aero-logo.png', $this->itsWidth - 15, $this->itsTMargin, 35);
                } else {
                    $this->Image($GLOBALS['SHARED_PATH'] . '/icons/tld-icon.jpg', $this->itsWidth - 5, $this->itsTMargin);
                }
                $this->SetFont('Arial', 'B', 14);
                $this->MultiCell($this->itsWidth - 5, 6,
                    $header['t_sitm'] . '_' . $header['t_revi'] . " (EDM)\n" .
                    $header['t_dsca'],
                    0, 'L');
        }
        $this->SetY(40);
    }

    /**
     * Put the titles on the parts table
     *
     */
    public function doPartsTableTitles(bool $withPMOC = true)
    {
        //table header
        $y = $this->GetY();
        $this->Line($this->itsLMargin, $y, $this->getPrintWidth() + $this->itsLMargin, $y);

        //do the table titles
        $this->Cell(10, 6, 'Item', 0, 0, 'C');
        $this->Cell(30, 6, 'PN', 0, 0, 'C');
        $this->Cell(95, 6, 'Description', 0, 0, 'C');
        $this->Cell(10, 6, 'Rev', 0, 0, 'C');
        $this->Cell(20, 6, 'Qty', 0, 0, 'C');
        $this->Cell(10, 6, 'UM', 0, 0, 'C');
        $this->ln();
        $y = $this->GetY();
        $this->Line($this->itsLMargin, $y, $this->getPrintWidth() + $this->itsLMargin, $y);
    }

    public function doIntentionallyPage()
    {
        // We don't want that...obviously
    }

}

/**
 * @deprecated
 */
class tldCBOMPDF extends tldBOMPDF
{
    public $itsCBOM;
    public $itsSN;

    public function __construct($erp, $sn, $date, ?string $orientation, ?bool $hidePageNumbers = false, ?tldCBOM $cbom = null)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function doPartsbook(?string $orientation = 'P')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function doTableOfContentsPage($rows)
    {
        throw new Exception('This is no longer used');
    }

}


/**
 * contains basic information from parts table
 *
 * @package ENG
 */
class tldPart
{
    /**
     * ERP company number
     *
     * @var integer
     */
    public $itsERP;

    /**
     * Part number
     *
     * @var string
     */
    public $itsID;

    /**
     * Table row information for this part
     *
     * @var array
     */
    public $itsDetails;

    /**
     * returns possibly multiple rows with different warehouses
     *
     * @param integer $erp erp company number
     * @param string $id part number
     */
    public function __construct($erp, $id)
    {
        $this->itsID = $id;
        $query = "select * from parts where ERP='$erp' AND ITEM='$id'";
        $this->itsDetails = tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get the part summary
     *
     * @return string
     */
    public function getText()
    {
        return '<b>' . $this->itsDetails['ITEM'] . '</b>, ' . $this->getDescription();
    }

    /**
     * Get the part description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->itsDetails[0]['t_dsca'] ?: $this->itsDetails[0]['FR'];
    }

    /**
     * Get the part number
     *
     * @return string
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get the French description
     *
     * @return string
     */
    public function getFR()
    {
        return $this->itsDetails['FR'];
    }

    /**
     * Search database by erp and part number
     *
     * @param integer $erp company number to search in
     * @param string $id part number to search for
     * @return array
     */
    public static function search($erp, $id)
    {
        $query = <<<EOF
		SELECT * FROM parts
		WHERE ERP='$erp' AND (ITEM like '__$id' OR ITEM like '$id')
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search database by part number only
     *
     * @param string $id part number
     * @return array
     */
    public static function searchByPN($id)
    {
        $query = <<<EOF
		SELECT * FROM parts
		WHERE ITEM like '__$id' OR ITEM like '$id'
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Customised BOM
 *
 * @package ENG
 */
class tldCBOM
{
    public $itsID;
    /** @var string */
    public $itsDate;
    public $itsERP;
    /** @var string */
    public $itsLang;
    /** @var array */
    public $itsBOMAsArray;

    public function __construct($erp, $date, $cbomNumber, $options = '', $data = null)
    {
        $this->itsID = strtoupper($cbomNumber);
        $this->itsERP = $erp;
        $this->itsDate = $date;
        //define the language to use for the description shown in the CBOM
        $this->itsLang = !empty($options['lang']) ? $options['lang'] : 'EN';

        if (null === $data) {
            throw new \Exception('This is no longer used');
        }

        $this->transformLnToBann($data);
        $this->itsBOMAsArray = $data;
    }

    public function transformLnToBann(&$data)
    {
        foreach ($data as &$child) {
            $effectiveDate = null;
            $expiryDate = null;

            if (array_key_exists('engineeringRevisionEffectiveDate', $child)) {
                $effectiveDate = new \DateTime($child['engineeringRevisionEffectiveDate']);
                $effectiveDate = $effectiveDate->format('Y-m-d');
            }

            if (array_key_exists('engineeringRevisionExpiryDate', $child)) {
                $expiryDate = new \DateTime($child['engineeringRevisionExpiryDate']);
                $expiryDate = $expiryDate->format('Y-m-d');
            }

            $child['erp'] = $this->itsERP;
            $child['t_cprj'] = $this->itsID;
            $child['t_pono'] = $child['position'] ?? null;
            $child['t_sitm'] = $child['partNumber'] ?? null;
            $child['t_mitm'] = $child['standardItem'] ?? null;
            $child['t_csig'] = $child['itemSignalCode'] ?? null;
            $child['t_dsca'] = $child['itemDescription'] ?? null;
            $child['altdsca'] = mb_convert_encoding($child['itemOtherDescription'], 'HTML-ENTITIES', 'UTF-8');
            $child['t_qana'] = $child['quantity'] ?? null;
            $child['itm_dsca'] = $child['itemDescription'] ?? null;
            $child['rev_desc'] = $child['engineeringDescription'] ?? null;
            $child['t_csel'] = $child['itemSelectionCode'] ?? null;
            $child['edm_csel'] = $child['engineeringSelectionCode'] ?? null;
            $child['t_csig_edm'] = $child['engineeringSignalCode'] ?? null;
            $child['t_opol'] = $child['customized'] ?? null;
            $child['t_opno'] = $child['operation'] ?? null;
            $child['t_exin'] = $child['extraInformation'] ?? null;
            $child['mitm_title'] = $child['itemDescription'] ?? null;
            $child['t_csig_fullname'] = $child['signalCodeDescription'] ?? null;
            $child['p'] = (isset($child['preventive']) && $child['preventive']) ? 'P' : null;
            $child['m'] = (isset($child['maintenance']) && $child['maintenance']) ? 'M' : null;
            $child['o'] = (isset($child['overhaul']) && $child['overhaul']) ? 'O' : null;
            $child['c'] = (isset($child['critical']) && $child['critical']) ? 'C' : null;
            $child['t_cuni'] = $child['unitOfMeasure'] ?? null;
            $child['t_revi'] = $child['engineeringRevision'] ?? null;
            $child['t_kitm'] = $child['itemType'] ?? null;
            $child['t_citg'] = $child['itemGroup'] ?? null;
            $child['t_suno'] = $child['buyFromBusinessPartner'] ?? null;
            $child['t_nama'] = $child['buyFromBusinessPartnerName'] ?? null;
            $child['t_oltm'] = $child['supplyTime'] ?? null;
            $child['t_csgp'] = $child['purchaseStatisticsGroup'] ?? null;
            $child['t_oqmf'] = $child['orderQuantityIncrement'] ?? null;
            $child['t_mioq'] = $child['minimumOrderQuantity'] ?? null;
            $child['t_sfst'] = $child['safetyStock'] ?? null;
            $child['t_oltm'] = $child['supplyTime'] ?? null;
            $child['t_buyr'] = $child['buyer'] ?? null;
            $child['t_copr'] = $child['estimatedStandardCost'] ?? null;
            $child['t_cpgs'] = $child['salesPriceGroup'] ?? null;
            $child['t_indt'] = $effectiveDate;
            $child['t_exdt'] = $expiryDate;

            if (!empty($child['children']) && \is_array($child['children'] ?? null)) {
                $this->transformLnToBann($child['children']);
            }
        }
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function getDATE()
    {
        return $this->itsDate;
    }

    public function getCPRJ()
    {
        return $this->itsID;
    }

    /**
     * @deprecated
     */
    public function isEmpty()
    {
        return count($this->itsBOMAsArray) === 0;
    }

    /**
     * @deprecated
     */
    public function getTopLevel_Array()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getCBOMTree()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLiveMaterialList($WHERE = '')
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getMaterialListByConstraints($a)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getMaterialList($options = '')
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getDescLang($itm, $lang)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPartsbook()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getAssemblyInstructions()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getSchematics()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getChapters()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getSignalCodes()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function getBAANProductTypeList($format = '')
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getCategories()
    {
        return [
            'A' => 'Body-Chassis',
            'B' => 'Covers and Panels',
            'C' => 'Boom',
            'D' => 'Bridge',
            'E' => 'Elevator',
            'F' => 'Lifting-Scissors System',
            'G' => 'Power Plant',
            'H' => 'Hydraulic System',
            'I' => 'Pneumatic System',
            'J' => 'Refrigeration System',
            'K' => 'Electrical System',
            'L' => 'Suspension, Tires and Brakes',
            'M' => 'User Interfaces and Cab',
            'N' => 'Accessories and Options',
        ];
    }

    /**
     * @deprecated
     */
    public function getDocumentList()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPMOC($code = '')
    {
        throw new \Exception('This is no longer used');
    }
}

class tldESL
{
    /**
     * Unit project number
     *
     * @var integer
     */
    public $itsID;
    /**
     * Effectivity date
     *
     * @var string
     */
    public $itsDate;
    /**
     * ERP Company number
     *
     * @var integer
     */
    public $itsERP;
    /**
     * ESL as array
     *
     * @var array
     */
    public $itsESLAsArray;

    /**
     * Constructor
     *
     * @param integer $erp
     * @param string $date
     * @param integer $sn
     * @param array $options optional set optional parameters such as lang
     * @return tldESL
     */
    public function __construct($erp, $date, $sn, $options = '')
    {
        $this->itsID = strtoupper($sn);
        $this->itsERP = $erp;
        $this->itsDate = $date;
        //define the language to use for the description shown in the CBOM
        if (isset($options['lang'])) {
            $this->itsLang = $options['lang'];
        } else {
            $this->itsLang = 'EN';
        }
        $this->itsESLAsArray = $this->getESL_Array();
    }

    /**
     * Get multilevel ESL
     *
     * @return array
     */
    public function getESL_Array()
    {
        //clear cache
        $this->itsESLAsArray = [];
        $array = [];
        $parent = $this->getTopLevel_Array();
        foreach ($parent AS $child) {
            $this->getESL_ArrayRec($array, $child['t_sitm']);
        }
        return $this->filterESLArray($array);
    }

    /**
     * Filter and sort data array
     *
     * @param array $array
     * @return array
     */
    public function filterESLArray($array)
    {
        $result = array_filter($array);
        usort($result, [$this, 'sortESL']);

        return $result;
    }

    /**
     * Sorting method used by method tldESL::filterESLArray() usort callback
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortESL($a, $b)
    {
        $lcidA = ['s' => preg_replace('/[^A-Z]/i', '', $a['t_lcid']), 'd' => (int)preg_replace('/[^0-9]/', '', $a['t_lcid'])];
        $lcidB = ['s' => preg_replace('/[^A-Z]/i', '', $b['t_lcid']), 'd' => (int)preg_replace('/[^0-9]/', '', $b['t_lcid'])];
        if ($lcidA['s'] == $lcidB['s']) {
            if ($lcidA['d'] < $lcidB['d']) {
                return -1;
            }

            if ($lcidA['d'] > $lcidB['d']) {
                return 1;
            }

            return 0;
        }
        return strcmp($lcidA['s'], $lcidB['s']);
    }

    /**
     * Recursive version of multi level esl routine
     *
     * @param array &$array
     * @param string $mitm Part number
     * @return array
     */
    private function getESL_ArrayRec(&$array, $mitm)
    {
        $erp = $this->itsERP;
        switch ($erp) {
            case '400':
            case '410':
            case '420':
            case '500':
            case '510':
            case '520':
            case '540':
            case '640':
            case '660':
            case '700':
                $sitms = $this->getSubItems_Array($mitm);
                if (!count($sitms)) {
                    return;
                }
                foreach ($sitms as $sitm) {
                    $array[] = $sitm;
                    $this->getESL_ArrayRec($array, $sitm['t_sitm']);
                }
                break;
        }
    }

    /**
     * Get only the top level part numbers
     *
     * @return array
     */
    public function getTopLevel_Array()
    {
        $erp = $this->itsERP;
        $sn = $this->itsID;
        $query = <<<EOF
		SELECT RTRIM(PCS.t_sitm) AS t_sitm
		FROM ttipcs022$erp AS PCS
		WHERE PCS.t_cprj='$sn'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get sub items of a PN as an array
     *
     * @param string $pn Part number
     * @return array
     */
    private function getSubItems_Array($pn)
    {
        $date = $this->itsDate;
        $erp = $this->itsERP;

        $query = <<<EOF
		SELECT
		  LTRIM(RTRIM(BOM2.t_lcid)) AS t_lcid,
		  LTRIM(RTRIM(BOM1.t_sitm)) AS t_sitm,
		  ITM.t_dsca
		FROM ttibom010$erp AS BOM1
		  LEFT JOIN ttibom020$erp AS BOM2 ON (BOM1.t_mitm=BOM2.t_mitm AND BOM1.t_pono=BOM2.t_pono AND BOM1.t_seqn=BOM2.t_seqn)
		  LEFT JOIN ttiitm001$erp AS ITM ON BOM1.t_sitm=ITM.t_item
		WHERE BOM1.t_mitm='$pn'
		  AND (
        	('$date' >= BOM1.t_indt AND '$date' < BOM1.t_exdt)
        	OR
        	('$date' >= BOM1.t_indt AND BOM1.t_exdt='1753-01-01')
      	  )
EOF;
        $result = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
        if (count($result) < 1) {
            return [];
        }
        return $result;
    }
}

/**
 * Class for accessing and managing cached material lists from ERP
 *
 *
 */
class tldML
{
    public $itsID; //material list id
    /**
     * @var array
     */
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get row from table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM erp_ml
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getLines()
    {
        return tldMLL::byParent($this->itsID);
    }

    /**
     * Insert a new ML into table
     *
     * @param array $p
     */
    public static function insert($p)
    {
        $fields = ['erp', 't_prno', 'dsca'];
        //set defaults
        $query = <<<EOF
        INSERT INTO erp_ml
        SET dt_entered=NOW(),
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }

    /**
     * Add a MLL to this ML
     *
     * @param array $p
     * @return mixed integer on success, string on error
     */
    public function addLine($p)
    {
        $p['parent_id'] = $this->itsID;
        $e = tldMLL::insert($p);
        if (!is_numeric($e)) {
            return 'ERROR: Problem adding line to Material List';
        }
        return $e;
    }

}

/**
 * Class for accessing and managing cached material list lines from ERP
 *
 *
 */
class tldMLL
{
    public $itsID; //material list line id

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     *  Get header information from database
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM erp_mll
		WHERE id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get MLL by parent id
     *
     * @param integer $pid
     * @return array
     */
    public static function byParent($pid)
    {
        $query = <<<EOF
		SELECT *
		FROM erp_mll
		WHERE parent_id=$pid
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Insert a single MLL into the table
     *
     * @param array $p
     * @return mixed integer of new row id on success, string text on error
     */
    public static function insert($p)
    {
        $fields = [
            'parent_id', 'tsec', 't_prno', 't_mitm', 't_pono', 't_sitm', 't_qana',
            't_cuni', 't_dsca', 't_csig', 't_revi', 't_indt', 't_exdt', 'changed',
            'p', 'm', 'o', 'c', 'lev',
        ];
        //set defaults
        $query = <<<EOF
        INSERT INTO erp_mll
        SET
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }
}


/**
 * Baan product variant/config
 *
 * @package ENG
 */
class tldPVA
{
    /**
     * Baan Variant number
     *
     * @var integer
     */
    public $itsID;

    /**
     * ERP Company number
     *
     * @var integer
     */
    public $itsERP;

    /**
     * Constructor
     *
     * @param string $id
     * @param integer $erp ERP Company number
     * @return tldPVA
     */
    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
    }

    /**
     * Get header info
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM ttipcf500$this->itsERP
		WHERE t_cpva=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get the selected config options
     *
     * @return array
     */
    public function getDetails()
    {
        //all companies share 300 for configurator!!!
        $query = <<<EOF
	SELECT T2.*,
		(SELECT t_dsca
		FROM ttipcf110300
		WHERE t_item=T1.t_item AND t_sern=T2.t_sern
			AND t_copt=T2.t_copt AND t_exdt='1753-01-01 00:00:00.000') AS t_dscb
	FROM ttipcf500$this->itsERP AS T1, ttipcf520$this->itsERP AS T2
	WHERE T1.t_cpva=$this->itsID AND T1.t_cpva=T2.t_cpva AND T2.t_copt<>''
		ORDER BY T2.t_sern
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * get variants by project number
     *
     * Should normally only be one row!
     *
     * @param integer $prj project number
     * @param integer $erp company number to retrieve from
     * @return array array of db rows
     */
    public static function byProjectERP($prj, $erp)
    {
        $query = <<<EOF
		SELECT *
		FROM ttipcf500$erp
		WHERE t_reft='4' AND t_refo='$prj'
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

}


/**
 * Production orders
 *
 * @package ENG
 */
class tldPOR
{
    public $itsID;
    public $itsERP;

    public function __construct($id, $erp)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
    }

    /**
     * Get row information for this production order
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM ttisfc001$this->itsERP
		WHERE t_pdno=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get the detail rows associated with this production order
     *
     * @return array
     */
    public function getDetail()
    {
        $query = <<<EOF
		select t1.*,
		t3.t_dsca
		(select sum(t2.t_hrem)
		from tticst002$this->itsERP as t2
		where t2.t_pdno=t1.t_pdno and t2.t_opno=t1.t_opno and t2.t_rono<>0
		group by t2.t_opno) as hrem_tot
		FROM tticst002$this->itsERP as t1 left join ttirou003 as t3 ON t1.t_tano=t3.t_tano
		where t1.t_pdno=$this->itsID and t1.t_rono=0
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get production orders by project number
     *
     * @param integer $id project number
     * @param integer $erp company number
     */
    public static function byPRJ($id, $erp)
    {
        $query = <<<EOF
		SELECT *
		FROM ttisfc001$erp
		WHERE t_cprj=$id
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}

/**
 * Class for production, operations
 *
 * Gets the actual hours booked to operation ie t_rono=1
 *
 * t_rono=0 gives estimated hours
 *
 * @package ENG
 */
class tldOP
{
    public $itsERP;
    public $itsOPNO;
    public $itsPDNO;
    public $itsHeader;

    public function __construct($erp, $pdno, $opno)
    {
        $this->itsERP = $erp;
        $this->itsOPNO = $opno;
        $this->itsPDNO = $pdno;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * get the estimated op cost
     *
     * ie rono=0
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM tticst002$this->itsERP
		WHERE t_pdno=$this->itsPDNO
		AND t_opno=$this->itsOPNO
		AND t_rono=0
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Get man hours
     */
    public function getHREM()
    {
        return $this->itsHeader['t_hrem'];
    }

    /**
     * Get machine hours
     */
    public function getHRMC()
    {
        return $this->itsHeader['t_hrmc'];
    }

    /**
     * Get labour costs
     */
    public function getWGCS()
    {
        return $this->itsHeader['t_wgcs'];
    }

    /**
     * get machine costs
     */
    public function getMCCS()
    {
        return $this->itsHeader['t_mccs'];
    }

    /**
     * get overhead costs
     */
    public function getOHCS()
    {
        return $this->itsHeader['t_ohcs'];
    }


    /**
     * get estimated tasks
     */
    public static function byPDNOERP($pdno, $erp)
    {
        $query = <<<EOF
		select t1.*,
		(select sum(t2.t_hrem)
		from tticst002$erp as t2
		where t2.t_pdno=t1.t_pdno and t2.t_opno=t1.t_opno and t2.t_rono<>0
		group by t2.t_opno) AS hrem_tot
		FROM tticst002$erp as t1
		where t1.t_pdno=$pdno and t1.t_rono=0
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}

/**
 * Class for creating and manipulating EAPs
 *
 * @package ENG
 */
class tldEAP
{
    public $itsID;
    /** @var array */
    public $itsHeader;
    /** @var tldMEAP|tldEAP|null */
    private $familyParentModule = null;

    /**
     * constructor
     *
     * @param integer $id ID number of EAP
     */
    public function __construct($id, $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    /**
     * Get header information from database
     *
     * @return array
     */
    public function getHeader()
    {
        if (!$this->itsID || !is_numeric($this->itsID)) {
            return [];
        }

        $query = <<<SQL
		SELECT eap.*,
			'{$this->getModule()}' AS module,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=poster
			) AS poster_fullname,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=reporter
			) AS reporter_fullname,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=assignee
			) AS assignee_fullname,
			locations.location,
			locations.erp,
			IF(assignee=0, 'N', 'Y') AS overnight,
			(SELECT SUM(tasks.hours) FROM tasks WHERE tasks.parent_id = eap.id AND tasks.module = "EAP") AS total_est_hours
		FROM eap LEFT JOIN locations ON eap.factory=locations.id
		WHERE eap.id=$this->itsID
SQL;
        if ([] !== $row = tldUtils::getSqlRowToAssocArray($query)) {
            $hoursByEAP = tldTimekeeping::getActualHoursByEAP([$this->itsID]);
            $row['total_hours_actual'] = $hoursByEAP[$row['id']] ?? null;
        }

        return $row;
    }

    public static function getDisciplineList(): array
    {
        return [
            'Mechanic Design' => 'Mechanic Design',
            'Hydraulic Design' => 'Hydraulic Design',
            'Cooling Design' => 'Cooling Design',
            'Electric Design' => 'Electric Design',
            'Electronic Design' => 'Electronic Design',
            'Software' => 'Software',
            'Bill of Material' => 'Bill of Material',
            'Commercial document' => 'Commercial document',
            'Test' => 'Test',
        ];
    }

    public static function getSELECT(): string
    {
        return <<<SQL
SELECT
    eap.*,
    YEAR(eap.dt_opened) AS eap_year,
    locations.location,
    IF(assignee=0, 'N', 'Y') AS overnight,
    (SELECT COUNT(tasks.id)
        FROM tasks
        JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id AND tasks.module LIKE 'BP' AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (SELECT COUNT(*) FROM tasks
	    WHERE module='EAP' AND tasks.parent_id=eap.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
	) AS models,
	(SELECT SUM(tasks.hours) FROM tasks
        WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
    ) AS total_est_hours,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
        WHERE people.id=eap.poster
	) AS poster_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
        WHERE people.id=eap.reporter
	) AS reporter_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
        WHERE people.id=eap.assignee
	) AS assignee_fullname
SQL;
    }

    public static function getFROM(): string
    {
        return <<<SQL
FROM eap
LEFT JOIN locations ON eap.factory=locations.id
SQL;
    }

    /**
     * is the EAP empty/valid
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Answers whether this EAP is an overnight one
     *
     * @return boolean
     */
    public function isOvernight()
    {
        return $this->itsHeader['overnight'] === 'Y';
    }

    /**
     * Get the status field of the EAP
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get EAP id number
     *
     * @return integer
     */
    public function getID()
    {
        return $this->itsHeader['id'];
    }

    /**
     * Get the short description field
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    /**
     * Get the initiator field
     *
     * Returns the id of the initiator from the peoples table
     *
     * @return integer
     */
    public function getInitiator()
    {
        return $this->itsHeader['reporter'];
    }

    /**
     * Get the reported_by field
     *
     * Returns the id of the person reporting EAP from the peoples table
     *
     * @return integer
     */
    public function getReportedBy()
    {
        return $this->itsHeader['reported_by'];
    }

    /**
     * Get related tasks to this EAP
     *
     * @return array
     */
    public function getTasks($mode = '')
    {
        return tldTask::byParent($this->itsID, 'EAP', $mode);
    }

    public function getFileByTasks($mode = '')
    {
        return tldTask::byFileParent($this->itsID, 'EAP', $mode);
    }

    /**
     * Get related models to this EAP
     *
     * @return array
     */
    public function getModels($mode = '')
    {
        return tldModModel::byParent($this->itsID, 'EAP', $mode);
    }

    public function getPartsList(): array
    {
        if (empty($this->itsID)) {
            return [];
        }
        $query = "SELECT * FROM eap_parts WHERE parent_id=$this->itsID";
        return tldUtils::getSqlToAssocArray($query);
    }

    public function setPartsListDescription(array $partsList, array $items): array
    {
        foreach ($partsList as $key => $part) {
            $item = array_filter($items, static function($item) use ($part) {
                return $item['item'] === $part['pn'];
            });

            $item = array_shift($item);
            if (empty($item)) {
                $partsList[$key]['description'] = '';
                continue;
            }

            $partsList[$key]['description'] = $item['description'];
        }

        return $partsList;
    }

    /**
     * Get related BPs to this EAP
     *
     * @return array
     */
    public function getBPS($mode = '')
    {
        return tldBP::byParent($this->itsID, 'EAP', $mode);
    }

    /**
     * Change status of the EAP
     *
     * If $status is empty then a list of all possible statuses will be returned. List
     * changes according to the current status
     *
     * @return array
     */
    public function changeStatus($status = '')
    {
        $allowed = [];
        $SET = '';
        if (empty($this->itsID)) {
            return 'ERROR: can not change status, EAP ID not set in object.';
        }
        $currentStatus = strtoupper($this->getStatus());
        if ($currentStatus === 'REJECTED') {
            return $allowed;
        }
        switch ($currentStatus) {
            case 'PENDING':
                //if this is an overnighter then allow by passing to notification stage
                if ($this->isOvernight()) {
                    $allowed = ['REJECTED' => 'REJECTED', 'NOTIFICATION' => 'NOTIFICATION'];
                } else {
                    $allowed = ['REJECTED' => 'REJECTED', 'IN QUEUE' => 'IN QUEUE', 'IN PROGRESS' => 'IN PROGRESS'];
                }
                break;
            case 'IN QUEUE':
                $allowed = ['IN PROGRESS' => 'IN PROGRESS'];
                break;
            case 'IN PROGRESS':
                $allowed = ['PROPOSED' => 'PROPOSED'];
                if (0 === count($this->getTasks()) && 0 === count($this->getBPS())) {
                    $allowed['IN QUEUE'] = 'IN QUEUE';
                }
                break;
            case 'PROPOSED':
                $allowed = ['IN PROGRESS' => 'IN PROGRESS', 'PROPOSED' => 'PROPOSED', 'NOTIFICATION' => 'NOTIFICATION'];
                if (!count($this->getBPS())) {
                    $allowed['NOTIFICATION'] = 'NOTIFICATION';
                }
                break;
            case 'NOTIFICATION':
                $allowed = ['PROPOSED' => 'PROPOSED', 'NOTIFICATION' => 'NOTIFICATION', 'CLOSED' => 'CLOSED'];
                break;
        }
        if ($status) {
            switch ($status) {
                case 'CLOSED':
                case 'REJECTED':
                    $SET = ' , dt_closed=NOW()';
                    break;
            }
        } else {
            return $allowed;
        }

        if (!in_array($status, $allowed, true)) {
            return "ERROR: $status is not an allowed status for EAP at {$this->getStatus()}";
        }
        $query = <<<SQL
			UPDATE eap
			SET status=UCASE('$status')
			$SET
			WHERE id=$this->itsID
SQL;
        $e = tldUtils::sqlQuery($query);
        $this->refresh();

        return $e;
    }

    /**
     * Refresh the cached header information
     *
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get list of related files from mod_files system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'EAP');
    }

    public function getFollowers()
    {
        return tldModMember::byParent($this->itsID, 'EAP');
    }

    public function getFollowersRecipients()
    {
        $recipients = [];
        foreach ($this->getFollowers() as $follower) {
            if (empty($follower['email'])) {
                continue;
            }
            $recipients[] = $follower['email'];
        }
        return $recipients;
    }

    /**
     * Get the factory number
     *
     * @return integer
     */
    public function getFactory()
    {
        return $this->itsHeader['factory'];
    }

    /**
     * Get the ERP number
     *
     * @return integer
     */
    public function getERP()
    {
        return $this->itsHeader['erp'];
    }

    /**
     * Get related files
     */
    public function getLinks($type = ''): array
    {
        $allowed = ['ALL', 'SB', 'PN', 'WC', 'NCR', 'EAP', 'PDC'];
        $type = strtoupper($type);
        if (!in_array($type, $allowed, true)) {
            return [];
        }

        $WHERE = " WHERE parent_id={$this->itsID} ";
        if ('ALL' !== $type) {
            $WHERE .= " AND type='$type' ";
        }

        return tldUtils::getSqlToAssocArray("SELECT * FROM eap_links $WHERE");
    }

    public static function getLatest(int $num = 10): array
    {
        $query = <<<EOF
		SELECT eap.*, YEAR(eap.dt_opened) AS eap_year, locations.location,
			IF(assignee=0, 'N', 'Y') AS overnight,
			( 	SELECT count(tasks.id)
				FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
				WHERE cal_bp.parent_id=eap.id
				AND tasks.module LIKE 'BP'
				AND tasks.status NOT LIKE 'CLOSED'
			) AS nb_task,
		    (
		    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
				FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
			) AS models,
			(
                SELECT SUM(tasks.hours)
                FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
            ) AS total_est_hours
		FROM eap LEFT JOIN locations ON eap.factory=locations.id
		ORDER BY id DESC
		LIMIT $num
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getEAPLinkNCR($a)
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        $query = <<<EOF
SELECT GROUP_CONCAT(ml.parent_id) as ncr_id, eap.*,
       GROUP_CONCAT(mod_models.model) as mod_model
FROM eap
         INNER JOIN mod_links ml ON eap.id=ml.item AND ml.module='NCR' AND ml.type='EAP'
         LEFT JOIN mod_models ON mod_models.module='EAP' AND mod_models.parent_id=eap.id
WHERE $WHERE
GROUP BY eap.id
UNION
SELECT GROUP_CONCAT(ml.item) as ncr_id, eap.*,
       GROUP_CONCAT(mod_models.model) as mod_model
FROM eap
         INNER JOIN mod_links ml ON eap.id=ml.parent_id AND ml.module='EAP' AND ml.type='NCR'
         LEFT JOIN mod_models ON mod_models.module='EAP' AND mod_models.parent_id=eap.id
WHERE $WHERE
GROUP BY eap.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get average number of days for all open eaps
     *
     * @return double
     */
    public static function getADO()
    {
        $query = <<<EOF
		select round(AVG(to_days(now())-to_days(dt_opened)), 2) AS ado
		from eap
		WHERE status NOT IN ('CLOSED','REJECTED')
			AND assignee=0
			AND category IN ('Engineering Change','Engineering Information Request')
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);
        return $row['ado'];
    }

    /**
     * get average number of days for all open eaps grouped by factory
     *
     * @return array
     */
    public static function getStatsByFactory($erp = '')
    {
        $WHERE = $erp ? " AND locations.erp=$erp" : '';
        $query = <<<EOF
		select
			(SELECT location FROM locations WHERE locations.id=t1.factory)
			 AS location,
		  round(AVG(to_days(now())-to_days(t1.dt_opened)), 2)
		   AS ado,
    		(SELECT COUNT(*) FROM eap AS t2
		   	WHERE status='CLOSED'
		   	AND t2.factory=t1.factory
		   	AND DATE_FORMAT(t2.dt_closed, '%Y-%m')=DATE_FORMAT(NOW(), '%Y-%m')
		   	)
    		 AS ccm,
    		 (SELECT ROUND(AVG(UNIX_TIMESTAMP(t3.dt_closed)-UNIX_TIMESTAMP(t3.dt_opened))/3600, 0)
			FROM eap as t3
			WHERE t3.assignee<>0
				AND t3.status='CLOSED'
		   		AND t3.factory=t1.factory
		   		AND DATE_FORMAT(t3.dt_closed, '%Y-%m')=DATE_FORMAT(NOW(), '%Y-%m')
		   	) AS ovrn_avg_hrs
		from eap AS t1
		WHERE t1.status NOT IN ('CLOSED','REJECTED')
			AND t1.assignee=0
			AND t1.category IN ('Engineering Change','Engineering Information Request')
		$WHERE
		GROUP BY t1.factory
		ORDER BY ado DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get average days open stats by year and month
     *
     * @return array
     */
    public function getADOClosed()
    {
        $query = <<<EOF
	select year(dt_closed) as y, month(dt_closed) as m, avg(datediff(dt_closed, dt_opened)) as avgDaysOpen
	from eap as t1
	where t1.status='CLOSED'
			AND assignee=0
			AND category IN ('Engineering Change','Engineering Information Request')
	group by y, m
	order by dt_closed
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }


    /**
     * Create a new EAP
     *
     * Taks an input array from a htmlQuickform
     *
     * @return integer id of new EAP
     */
    public static function insert($p)
    {
        //get the employee fullname if t_emno exists
        if ($p['t_emno']) {
            $loc = new tldLocation($p['factory']);
            $erpid = $loc->getERP();
            if ($erpid) {
                $erp = tldERP::getERPOb($erpid);
                $em = $erp->getEmployeeData($p['t_emno']);
                $p['reported_by'] = $em['t_nama'] . ' ' . $em['t_namb'];
            }
        }
        //process file, if any
        if (!empty($p['filename_info']['tmp_name'])) {
            $file = new basicFile($p['filename_info']['tmp_name']);
            $p['filename1'] = date('Gis') . basicFile::cleanupName($p['filename_info']['name']);
            echo $file->copyFile(tldUtils::getPathToUploadFile('eap', $p['filename1']));
        }
        $fields = ['t_emno', 'reported_by', 'reporter', 'poster', 'short_desc', 'description', 'category',
            'ifactor', 'factory', 'assignee', 'info', 'pvt', 'expected_hours', 'discipline', 'action_plan'];
        //set defaults
        if ($p['newfile'] === 'N' && !empty($p['newfile'])) {
            $query = <<<EOF
            INSERT INTO eap
            SET dt_opened=NOW(), status='PENDING', filename ='{$p['filename']}',
EOF;
        } else {
            $query = <<<EOF
            INSERT INTO eap
            SET dt_opened=NOW(), status='PENDING', filename ='{$p['filename1']}',
EOF;
        }
        $query .= tldUtils::getSqlSet($p, $fields);
        $error = tldUtils::sqlInsert($query);
        //if insert was good then add pns

        if (is_numeric($error) && count($p['pn'] ?? []) > 0) {
            $eap = new tldEAP($error);
            foreach ($p['pn'] as $pn) {
                if ($pn) {
                    $eap->addPart($pn);
                }
            }
        }
        //if header was inserted correctly, add models
        if (is_numeric($error) && count($p['models'] ?? []) > 0) {
            foreach ($p['models'] as $model) {
                if ($model === 'OTHER') {
                    // Manually insert OTHER in ModModel list
                    $query = <<<EOF
					INSERT INTO mod_models
					SET parent_id=$error, module='EAP', type='Other / Discontinued', model='OTHER'
EOF;
                    tldUtils::sqlInsert($query);
                } else {
                    tldModModel::insert('EAP', $error, $model);
                }
            }
        }
        // Set members
        if (is_numeric($error) && count($p['members'] ?? [])) {
            $eap = new tldEAP($error);
            $eap->addMember($p['members']);
        }
        return $error;
    }

    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE eap SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public function addMember($members)
    {
        $members = (array)$members;
        foreach ($members AS $member) {
            tldModMember::insert('EAP', $this->itsID, $member);
        }
    }

    /**
     * Add a comment to the log
     *
     * @param array $a Array structure containing 'poster' and 'comment'
     *
     * @return boolean
     */
    public function addComment($a)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'EAP';
        return tldModLog::insert($a);
    }

    public function addPart($pn)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No id set for EAP';
        }
        if (empty($pn)) {
            return 'ERROR: No PN given';
        }
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $pn)) {
            return 'ERROR: Part Number must be alphanumeric';
        }
        $query = <<<EOF
        INSERT INTO eap_parts
        SET parent_id=$this->itsID, pn='$pn'
EOF;
        return tldUtils::sqlInsert($query);
    }

    public function removePart($pn)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No id set for EAP';
        }
        if (empty($pn)) {
            return 'ERROR: No PN given';
        }
        $pn = TldDatabase::escape($pn);
        $query = <<<EOF
        DELETE FROM eap_parts
        WHERE eap_parts.parent_id = $this->itsID AND eap_parts.pn = '$pn';
EOF;
        return TldUtils::sqlExecute($query);
    }

    public function addModel($model)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No id set for EAP';
        }
        foreach ($this->getModels() as $key => $link) {
            if (isset($link['model']) && $link['model'] === $model) {
                return 'ERROR: This model is already link to the EAP';
            }
        }
        if ($model === 'OTHER') {
            // Manually insert OTHER in ModModel list
            $query = <<<SQL
                INSERT INTO mod_models
                SET parent_id={$this->itsID}, module='EAP', type='Other / Discontinued', model='OTHER'
SQL;
            return tldUtils::sqlInsert($query);
        }

        return tldModModel::insert('EAP', $this->itsID, $model);
    }

    /**
     * Get log of comments/history
     *
     * @return array
     */
    public function getLog($log_num = 0)
    {
        return tldModLog::byParent($this->itsID, 'EAP', $log_num);
    }

    /**
     * NOTIFICATION FUNCTIONS
     *
     * @return boolean
     */
    public function notifyInitiator($message, $subject = '', $cc = '')
    {
        $message .= "<br><br>{$this->getShortDesc()}";
        $reporter = new tldUser($this->getInitiator());
        if (empty($subject)) {
            $subject = 'EAP# ' . $this->itsID . ' updated';
        }
        $subject = "[{$this->itsHeader['location']}] $subject";
        return tldUtils::emailAttachment($reporter->getEmail(), 'noreply@tld-gse.com', $subject, $message, null, $cc);
    }

    public function getEMSList()
    {
        $ems = new tldGroup('gg_mod_eap_admin', ((int)$this->itsHeader['factory'] === 37) ? 500 : $this->itsHeader['erp']);
        $addresses = $ems->getEmailList();
        // Include Linked MEAP Project Leaders
        $mods = tldModLink::byItem($this->itsID, 'EAP');
        foreach ($mods AS $mod) {
            if ($mod['module'] === 'MEAP') {
                $meap = new tldMEAP($mod['parent_id']);
                if ($meap->itsHeader['proj_leader']) {
                    $u = new tldUser($meap->itsHeader['proj_leader']);
                    $addresses[] = $u->getEmail();
                }
            }
        }
        return array_unique($addresses);
    }

    /**
     *    Returns a 2 dim array of counts by erp and status
     */
    public static function countByFactoryStatus($year = '')
    {
        $query = <<<EOF
		SELECT eap.status, locations.location, count(*) as num
		FROM eap LEFT JOIN locations ON eap.factory=locations.id
		GROUP BY location, status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactoryOpenStatusWithNoOpenTasks()
    {
        $a = [
            "status NOT IN('REJECTED','CLOSED')",
            "(SELECT COUNT(*) FROM tasks WHERE status<>'CLOSED' AND module LIKE 'EAP' AND eap.id=tasks.parent_id)=0",
            "(SELECT COUNT(*) FROM cal_bp WHERE parent_id=eap.id AND module='EAP' AND status<>'CLOSED' ) =0",
        ];

        return self::countByFactoryStatusByConstraints(implode(' AND ', $a));
    }

    /**
     * @param string|array $constraints
     * @return array
     */
    public static function countByFactoryStatusByConstraints($constraints = '1=1')
    {
        $WHERE = '';
        if (is_array($constraints)) {
            $WHERE = tldUtils::constructWhere($constraints);
        } elseif (!empty($constraints)) {
            $WHERE = $constraints;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    eap.status,
    eap.factory,
    locations.location AS factory_fullname,
    COUNT(*) AS num
FROM
    eap
    LEFT JOIN locations ON eap.factory=locations.id
$WHERE
GROUP BY
    factory_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryOpenStatusWithNoOpenTasks($factory, $status)
    {
        $a = [
            "status NOT IN('REJECTED','CLOSED')",
            "(SELECT COUNT(*) FROM tasks WHERE status<>'CLOSED' AND module LIKE 'EAP' AND eap.id=tasks.parent_id)=0",
            "(SELECT COUNT(*) FROM cal_bp WHERE parent_id=eap.id AND module='EAP' AND status<>'CLOSED' ) =0",
        ];
        if ($factory !== 'ALL') {
            $a[] = "location LIKE '$factory'";
        }
        if ($status !== 'ALL') {
            $a[] = "status LIKE '$status'";
        }

        return self::byConstraints(implode(' AND ', $a));
    }

    /**
     *    Get EAPs by ERP and status
     *
     * @param string $erp
     * @param string $status
     * @param string|array $sort
     */
    public static function byERPStatus($erp = '', $status = '', $sort = ''): array
    {
        $where = '';
        if ($erp !== 'ALL' && $status !== 'ALL') {
            if (!empty($erp) && empty($status)) {
                $where = " WHERE locations.location='$erp'";
            } elseif (empty($erp) && !empty($status)) {
                $where = " WHERE eap.status='$status'";
            } elseif (!empty($erp) && !empty($status)) {
                $where = " WHERE eap.status='$status' AND locations.location='$erp'";
            }
        } elseif ($erp !== 'ALL') {
            if (!empty($erp)) {
                $where = " WHERE locations.location='$erp'";
            }
        } elseif ($status !== 'ALL') {
            if (!empty($status)) {
                $where = " WHERE eap.status='$status' ";
            }
        }
        $sortBy = ' ORDER BY factory, status';
        if (is_array($sort)) {
            $sortBy = " ORDER BY {$sort['field']}" . (in_array($sort['dir'], ['ASC', 'DESC']) ? " {$sort['dir']}" : ' ASC');
        }

        $query = <<<EOF
SELECT eap.*, YEAR(eap.dt_opened) AS eap_year, locations.location,
    IF(assignee=0, 'N', 'Y') AS overnight,
    (
        SELECT count(tasks.id)
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (
        SELECT tasks.due_date
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
        ORDER by tasks.due_date DESC
        LIMIT 1
    ) AS latest_bp_duedate,
    (
	    SELECT COUNT(*) FROM tasks
	    WHERE module='EAP' AND tasks.parent_id=eap.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (
    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
	) AS models,
	(
        SELECT SUM(tasks.hours)
        FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
    ) AS total_est_hours,
	(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.poster
	) AS poster_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.reporter
	) AS reporter_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.assignee
	) AS assignee_fullname
FROM eap LEFT JOIN locations ON eap.factory=locations.id
$where
$sortBy
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count stats by Model and Status
     *
     * Optional by year also
     *
     * @return array
     */
    public static function countByModelStatus()
    {
        $query = <<<EOF
		SELECT eap.status, mod_models.model, count(*) as num
		FROM eap LEFT JOIN mod_models ON eap.id=mod_models.parent_id AND mod_models.module='EAP'
		GROUP BY mod_models.model, status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByPartByStatus($id, $pn, $status = [])
    {
        if (is_string($status) && status) {
            $status = (array) $status;
        }
        $WHERE = $status ? sprintf("AND t1.status IN ('%s')", implode("','", $status)) : '';
        $id = (int) $id;
        $pn = TldDatabase::escape($pn);

        $query = <<<EOF
		SELECT count(*) AS qty
			FROM eap AS t1
			JOIN eap_parts AS t2 ON t1.id=t2.parent_id
		WHERE t2.pn LIKE '$pn' AND t1.id!=$id
		$WHERE
EOF;
        $rows = tldUtils::getSqlRowToAssocArray($query);

        return $rows ? $rows['qty'] : '0';
    }

    public static function byPartByStatus($pn, $status): array
    {
        if (is_string($status) && $status) {
            $status = (array) $status;
        }
        $WHERE = $status ? sprintf("AND t1.status IN ('%s')", implode("','", $status)) : '';

        $query = <<<EOF
		SELECT t1.*,
		    YEAR(t1.dt_opened) AS eap_year,
		    IF(assignee=0, 'N', 'Y') AS overnight,
	    (
	        SELECT count(tasks.id)
	        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
	        WHERE cal_bp.parent_id=t1.id
	        AND tasks.module LIKE 'BP'
	        AND tasks.status NOT LIKE 'CLOSED'
	    ) AS nb_bptasks,
	    (
		    SELECT COUNT(*) FROM tasks
		    WHERE module='EAP' AND tasks.parent_id=t1.id
		    AND tasks.status<>'CLOSED'
	    ) AS nb_tasks,
	    (
	    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
			FROM mod_models	WHERE parent_id=t1.id AND module='EAP'
		) AS models,
		(
	        SELECT SUM(tasks.hours)
	        FROM tasks WHERE tasks.parent_id=t1.id AND tasks.module='EAP'
	    ) AS total_est_hours
		FROM eap AS t1
		JOIN eap_parts AS t2 ON t1.id=t2.parent_id
		WHERE t2.pn LIKE '$pn'
		$WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);

    }

    /**
     * Get Lis of EAP by Employee ID
     *
     * @param $uid int employee id
     * @param $options array
     */
    public static function byEmno($uid, $options = '')
    {
        if (!is_numeric($uid)) {
            return;
        }
        if (!empty($options)) {
            $options = tldUtils::cleanupFormInput($options);
        }
        if (isset($options['mode']) && $options['mode'] === 'byOpenStatusERP') {
            $a = "t_emno=$uid AND status NOT IN('CLOSED','REJECTED') AND locations.erp={$options['erp']}";
        } else {
            $a['t_emno'] = $uid;
        }
        return self::byQuery($a);
    }

    /**
     * Get list of EAP by model and status
     *
     * @param string $model
     * @param string $status
     */
    public static function byModelStatus(?string $model = '', ?string $status = ''): array
    {
        $where = '';
        if ($model !== 'ALL' && $status !== 'ALL') {
            if (!empty($model) && empty($status)) {
                $where = " WHERE mod_models.model='$model'";
            } elseif (empty($model) && !empty($status)) {
                $where = " WHERE eap.status='$status'";
            } elseif (!empty($model) && !empty($status)) {
                $where = " WHERE eap.status='$status' AND mod_models.model='$model'";
            }
        }

        $query = <<<EOF
SELECT eap.*,
    YEAR(eap.dt_opened) AS eap_year, mod_models.model,
    IF(assignee=0, 'N', 'Y') AS overnight,
    (
        SELECT count(tasks.id)
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (
	    SELECT COUNT(*) FROM tasks
	    WHERE module='EAP' AND tasks.parent_id=eap.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (
    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
	) AS models,
	(
        SELECT SUM(tasks.hours)
        FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
    ) AS total_est_hours
FROM eap LEFT JOIN mod_models ON eap.id=mod_models.parent_id AND mod_models.module='EAP'
		$where
		ORDER BY mod_models.model, status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search EAPS for string
     *
     * Only searches in particular fields
     *
     * @param string $data
     * @return array
     */
    public static function search($data)
    {
        if (empty($data)) {
            return 'Empty parameter';
        }
        $WHERE = [];
        if (!empty($data['target'])) {
            $targetFields = ['pn', 'short_desc', 'description', 'category', 'type', 'model'];
            $keywords = explode(' ', $data['target']);
            foreach ($keywords AS $keyword) {
                if ($keyword == '') {
                    continue;
                }
                $group = [];
                foreach ($targetFields AS $field) {
                    $group[$field] = "%$keyword%";
                }
                $WHERE[] = '(' . tldUtils::constructWhere($group, 'OR') . ')';
            }
        }
        if (!empty($data['status'])) {
            $WHERE[] = "status='{$data['status']}'";
        }
        if (!empty($data['location'])) {
            $WHERE[] = "location='{$data['location']}'";
        }
        if (!empty($data['models']) AND is_array($data['models'])) {
            $WHERE[] = "EXISTS(SELECT * FROM mod_models mm WHERE mm.parent_id=eap.id AND mm.module='EAP' AND mm.model IN('" . implode("','", $data['models']) . "'))";
        }
        $WHERE = implode(' AND ', $WHERE);
        if (!empty($WHERE)) {
            $WHERE = "WHERE {$WHERE}";
        }
        // Make query
        $query = <<<EOF
SELECT
	eap.*,
    YEAR(eap.dt_opened) AS eap_year,
    locations.location,
    IF(assignee=0, 'N', 'Y') AS overnight,
    (
        SELECT count(tasks.id)
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (
	    SELECT COUNT(*) FROM tasks
	    WHERE module='EAP' AND tasks.parent_id=eap.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (
    	SELECT SUM(tasks.hours)
    	FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
    ) AS total_est_hours,
    (
    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
	) AS models
FROM eap
	LEFT JOIN locations ON eap.factory=locations.id
{$WHERE}
ORDER BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search by pn
     *
     * @param string $pn
     * @return array
     */
    public static function byPartNumber($pn)
    {
        if (empty($pn)) {
            return [];
        }
        $query = <<<EOF
SELECT t1.*,
    YEAR(t1.dt_opened) AS eap_year,
    (SELECT location FROM locations where locations.id=t1.factory),
    IF(t1.assignee=0, 'N', 'Y') AS overnight,
    (
        SELECT count(tasks.id)
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=t1.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (
	    SELECT COUNT(*) FROM tasks
	    WHERE
	    module='EAP' AND tasks.parent_id=t1.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (
    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=t1.id AND module='EAP'
	) AS models
FROM eap AS t1
	JOIN eap_parts AS t2 ON t1.id=t2.parent_id
WHERE t2.pn LIKE '$pn'
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byOpenPartNumber($pn)
    {
        $SELECT = self::getSELECT();
        $WHERE = "t2.pn LIKE '$pn'";
        if (is_array($pn)) {
            $SELECT .= ', t2.pn';
            $pns = implode("','", $pn);
            $WHERE = "t2.pn IN ('$pns')";
        }
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
	JOIN eap_parts AS t2 ON eap.id=t2.parent_id
WHERE
    $WHERE
    AND eap.status NOT IN ('CLOSED','REJECTED')
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get EAPs by query
     *
     * @param string $m mode to use
     * @return array array of db rows
     */
    public static function byQuery($m, $options = '')
    {
        if (empty($m)) {
            return [];
        }
        if (!is_array($m)) {
            switch ($m) {
                case 'byNoParent':
                    $WHERE = <<<EOF
WHERE eap.status NOT IN('CLOSED','REJECTED') AND eap.parent_id = 0
EOF;
                    break;
                case 'byNoTasks':
                    $WHERE = <<<EOF
WHERE eap.status NOT IN('CLOSED','REJECTED')
    AND (SELECT COUNT(*) FROM tasks
    WHERE
    module='EAP' AND tasks.parent_id=eap.id
    AND tasks.status<>'CLOSED'
    )=0
EOF;
                    break;
                default:
                    $WHERE = " WHERE $m ";
                    break;
            }
        } else {
            $WHERE = ' WHERE ' . tldUtils::constructWhere($m, $options);
        }
        $query = <<<EOF
SELECT eap.*,
    YEAR(eap.dt_opened) AS eap_year,
    locations.location,
    IF(assignee=0, 'N', 'Y') AS overnight,
    (
        SELECT count(tasks.id)
        FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
        WHERE cal_bp.parent_id=eap.id
        AND tasks.module LIKE 'BP'
        AND tasks.status NOT LIKE 'CLOSED'
    ) AS nb_bptasks,
    (
	    SELECT COUNT(*) FROM tasks
	    WHERE module='EAP' AND tasks.parent_id=eap.id
	    AND tasks.status<>'CLOSED'
    ) AS nb_tasks,
    (
    	SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
	) AS models,
	(
	   SELECT CONCAT(firstname, ' ', lastname)
	   FROM people WHERE people.id = eap.reporter
	) AS reporter_fullname
FROM eap
    LEFT JOIN locations ON eap.factory=locations.id
$WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byPosterID($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [];
        }
        return self::byConstraints(['poster' => $id]);
    }

    /**
     * Get EAP by constraints
     * WARNING: DO NOT DELETE, SHOULD BE THE GENERIC ONE TO USE
     * @param mixed array or string
     * @return array
     */
    public static function byConstraints($a, array $options = []): array
    {
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $WHERE = !empty($a) ? " WHERE $a" : '';
        $SELECT = $options['select'] ?? '';
        $JOIN = $options['join'] ?? '';

        $query = <<<EOF
        SELECT eap.*,
        	'EAP' AS module,
            YEAR(eap.dt_opened) AS eap_year,
            locations.location,
            IF(assignee=0, 'N', 'Y') AS overnight,
            (SELECT count(tasks.id)
                FROM tasks JOIN cal_bp ON cal_bp.id=tasks.parent_id
                WHERE cal_bp.parent_id=eap.id
                AND tasks.module LIKE 'BP'
                AND tasks.status NOT LIKE 'CLOSED'
            ) AS nb_bptasks,
            (SELECT COUNT(*) FROM tasks
        	    WHERE module='EAP' AND tasks.parent_id=eap.id
        	    AND tasks.status<>'CLOSED'
            ) AS nb_tasks,
            (SELECT GROUP_CONCAT(DISTINCT model SEPARATOR ',')
        		FROM mod_models	WHERE parent_id=eap.id AND module='EAP'
        	) AS models,
            (SELECT SUM(tasks.hours)
                FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP'
    )        AS total_est_hours,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.poster
			) AS poster_fullname,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.reporter
			) AS reporter_fullname,
			(SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=eap.assignee
			) AS assignee_fullname
            $SELECT
    FROM eap
            LEFT JOIN locations ON eap.factory=locations.id
            $JOIN
        $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getParentModule()
    {
        return $this->itsHeader['parent_module'];
    }

    public function getModule()
    {
        return 'EAP';
    }

    public function addChild($cid, $module)
    {
        if (empty($cid) || empty($this->itsID)) {
            return 'Child# invalid or not object context';
        }
        $table = ($module === 'MEAP') ? 'meap' : 'eap';
        $query = <<<EOF
        UPDATE {$table}
        SET parent_id={$this->itsID}, parent_module='{$this->getModule()}'
        WHERE id={$cid}
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function deleteChild($cid, $module)
    {
        if (empty($cid) || empty($this->itsID)) {
            return 'Child# invalid or not object context';
        }
        $table = ($module === 'MEAP') ? 'meap' : 'eap';
        $query = <<<EOF
        UPDATE {$table}
        SET parent_id=0, parent_module=''
        WHERE id={$cid}
        AND parent_id={$this->itsID}
        AND parent_module='{$this->getModule()}'
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function getChildList()
    {
        if (empty($this->itsID)) {
            return [];
        }
        $eap = self::byConstraints(['eap.parent_id' => $this->itsID, 'parent_module' => $this->getModule()]);
        $meap = tldMEAP::byConstraints(['meap.parent_id' => $this->itsID, 'parent_module' => $this->getModule()]);
        return array_merge($eap, $meap);
    }

    public function getSumOfHoursOfChildren($children, $level, $field)
    {
        if ($level > 6){
            return;
        }
        $level++;
        $total = 0;
        foreach ($children as $child) {
            $eap = new tldEAP($child['id']);
            $total += $eap->itsHeader[$field] + $eap->getSumOfHoursOfChildren($eap->getChildList(), $level, $field);
        }
        return $total;
    }

    /**
     * @param array $meapIds
     * @param array $eapIds
     * @return array
     */
    public static function getChildListByBatch($meapIds = [], $eapIds = [])
    {
        if (!$meapIds && !$eapIds) {
            return [];
        }
        $filter = '';
        if ($eapIds) {
            $filter .= sprintf(" (%%1\$s.parent_id IN (%s) AND parent_module = 'EAP')", implode(",", $eapIds));
        }
        if ($meapIds) {
            if ($eapIds) {
                $filter .= ' OR ';
            }
            $filter .= sprintf("(%%1\$s.parent_id IN (%s) AND parent_module = 'MEAP') ", implode(",", $meapIds));
        }

        $eap = self::byConstraints('('.sprintf($filter, 'eap').')');
        $meap = tldMEAP::byConstraints('('.sprintf($filter, 'meap').')');

        return array_merge($eap, $meap);
    }

    /**
     * @return void
     */
    public function getChildListRec(&$result, $level = 0, $hideChildMEAPDetail = false)
    {
        if (empty($this->itsID)) {
            throw new DomainException('Not object context');
        }

        self::getChildListRecursive($result, $level, $hideChildMEAPDetail, [], [$this->itsID], $this->itsID);
    }

    public static function getChildListRecursive(&$result, $level = 0, $hideChildMEAPDetail = false, $meapIds = [], $eapIds = [], $initialMeap = null)
    {
        $level++;
        if (!($rows = self::getChildListByBatch($meapIds, $eapIds))) {
            return;
        }
        $meapIds = $eapIds = [];
        foreach ($rows as $row) {
            // Skipping the MEAP content if it is not the one from where we start
            if ($hideChildMEAPDetail && $row['module'] === 'MEAP' && null !== $initialMeap && $row['id'] !== $initialMeap) {
                continue;
            }
            $result[] = ['id' => $row['id'], 'module' => $row['module'], 'parent_id' => $row['parent_id'], 'parent_module' => $row['parent_module'], 'level' => $level, 'data' => $row];
            if ($row['module'] === 'MEAP') {
                $meapIds[] = $row['id'];
            } else {
                $eapIds[] = $row['id'];
            }
        }

        self::getChildListRecursive($result, $level, false, $meapIds, $eapIds, $initialMeap);
    }

    public function getParentModuleIDFamily($returnObject = false)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }

        if (0 === (int)($parentId = $this->getParentID())) {
            return ($returnObject) ? $this : ['module' => $this->getModule(), 'id' => $this->getID()];
        }
        // Make this method a one-time use - save overhead
        if (null !== $this->familyParentModule) {
            return ($returnObject) ? $this->familyParentModule : ['module' => $this->familyParentModule->getModule(), 'id' => $this->familyParentModule->getID()];
        }
        // Get first parent
        $parentModule = $this->getParentModule();
        $prev = $this;
        // if first parent found, loop and search parent until we found the last one
        while ($parentId > 0) {
            $this->familyParentModule = ($parentModule === 'MEAP') ? new tldMEAP($parentId) : new tldEAP($parentId);
            if ($this->familyParentModule->isEmpty()) {
                $this->familyParentModule = $prev;
                break;
            }
            $parentId = $this->familyParentModule->getParentID();
            $parentModule = $this->familyParentModule->getParentModule();
            $prev = $this->familyParentModule;
        }
        return ($returnObject) ? $this->familyParentModule : ['module' => $this->familyParentModule->getModule(), 'id' => $this->familyParentModule->getID()];
    }

    /**
     * @return array
     */
    public function getFamilyTree()
    {
        if (empty($this->itsID)) {
            throw new DomainException('Not object context');
        }

        $topParent = $this->getParentModuleIDFamily(true);
        $topParentModule = $topParent->getModule();
        $topParentId = $topParent->getID();
        $result[] = ['id' => $topParentId, 'module' => $topParentModule, 'parent_id' => '0', 'parent_module' => '', 'level' => 0, 'data' => $topParent->itsHeader];

        $meaps = 'MEAP' === $topParentModule ? [$topParentId] : [];
        $eaps = 'EAP' === $topParentModule ? [$topParentId] : [];

        self::getChildListRecursive($result, 0, false, $meaps, $eaps, 'MEAP' === $this->getParentModule() ? $this->getParentID() : null);

        return $result;
    }

    public function getEAPSummary()
    {
        $query = <<<SQL
        SELECT id
        FROM  eap
        WHERE parent_id=$this->itsID AND parent_module='EAP'
SQL;

        if (!$ids = array_column(tldUtils::getSqlToAssocArray($query), 'id')) {
            return [];
        }

        $ids = implode(',', array_unique($ids));

        $query = <<<EOF
        SELECT eap.*,
            (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people WHERE people.id=eap.poster) AS poster_fullname,
            (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people WHERE people.id=eap.reporter) AS reporter_fullname,
            (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people WHERE people.id=eap.assignee) AS assignee_fullname,
            locations.location,
            locations.erp,
            IF (eap.assignee=0, 'N', 'Y') AS overnight,
            (SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' ) AS tasks_all,
            (SELECT SUM(tasks.hours) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' ) AS total_est_hours,
            (SELECT SUM(tasks.hours) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status='OPEN' ) AS total_open_est_hours,
            (SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status<>'CLOSED') AS tasks_open,
            (SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status='CLOSED') AS tasks_closed,
            (SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.due_date<CURDATE() AND tasks.status<>'CLOSED') AS tasks_late,
            (SELECT COUNT(*) FROM cal_bp WHERE cal_bp.parent_id=eap.id AND cal_bp.module='EAP' AND cal_bp.status<>'CLOSED') AS bp_open,
            (SELECT COUNT(*) FROM cal_bp WHERE cal_bp.parent_id=eap.id AND cal_bp.module='EAP' AND cal_bp.status='CLOSED') AS bp_closed,
            CASE eap.status
                WHEN 'PENDING' THEN 10
                WHEN 'IN PROGRESS' THEN 100
                WHEN 'PROPOSED' THEN 1000
                WHEN 'NOTIFICATION' THEN 10000
                WHEN 'IN QUEUE' THEN 100000
                WHEN 'CLOSED' THEN 1000000
                WHEN 'REJECTED' THEN 10000000
                ELSE 100000000
            END AS status_order
        FROM eap
            LEFT JOIN locations ON eap.factory=locations.id
        WHERE eap.id IN ($ids)
        ORDER BY status_order, location, ifactor DESC, dt_opened, id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        $meap = $this->getParentModuleIDFamily(true);
        if ($meap->getModule() !== 'MEAP') {
            return false;
        }

        return $meap->isPrivate();
    }

    public function getMembers()
    {
        $meap = $this->getParentModuleIDFamily(true);
        if ($meap->getModule() !== 'MEAP') {
            return [];
        }

        return $meap->getMembers();
    }

    public function isMember($uid)
    {
        $meap = $this->getParentModuleIDFamily(true);
        if ($meap->getModule() !== 'MEAP') {
            return false;
        }
        return $meap->isMember($uid);
    }

    public function getMembersList()
    {
        $meap = $this->getParentModuleIDFamily(true);
        if ($meap->getModule() !== 'MEAP') {
            return [];
        }
        return $meap->getMembersList();
    }

    public function getCCList()
    {
        return array_unique(array_merge($this->getEMSList(), $this->getMembersList(), $this->getFollowersRecipients()));
    }

    public static function countEAPClosedByModel($models, $from, $to, $factory)
    {
        $WHERE = $INNER_WHERE = '';
        if ($models) {
            $INNER_WHERE = ' AND mods.model IN ("'.implode('","', $models).'") ';
        }

        if ('ALL' !== $factory) {
            $WHERE = " AND locations.id=$factory ";
        }

        $query = <<<SQL
SELECT
    (SELECT mods.model
     FROM mod_models AS mods
     WHERE mods.parent_id=eap.id AND mods.module='EAP' 
     $INNER_WHERE
     ORDER BY id
     LIMIT 1
    ) AS models,
    GROUP_CONCAT(DISTINCT eap.id ORDER BY eap.id) as eaps,
    date_format(eap.dt_closed, '%Y-%m') AS xval,
    COUNT(DISTINCT(eap.id)) AS yval,
    locations.location
FROM eap
LEFT JOIN locations ON eap.factory=locations.id
WHERE dt_closed BETWEEN '$from' AND '$to' AND status='CLOSED'
$WHERE
GROUP BY models, xval, locations.location
HAVING models IS NOT NULL
ORDER BY xval, models
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countEAPClosedByFamily($models, $from, $to, $factory)
    {
        $WHERE = $INNER_WHERE = '';
        if ($models) {
            $INNER_WHERE = ' AND mods.model IN ("'.implode('","', $models).'") ';
        }

        if ('ALL' !== $factory) {
            $WHERE = " AND locations.id=$factory ";
        }

        $query = <<<SQL
SELECT
    (SELECT models.family
     FROM mod_models AS mods
     LEFT JOIN models ON mods.model = models.model
     WHERE mods.parent_id=eap.id AND mods.module='EAP' 
     $INNER_WHERE 
     ORDER BY eap.id
     LIMIT 1
    ) AS family,
    GROUP_CONCAT(DISTINCT eap.id ORDER BY eap.id) as eaps,
    date_format(eap.dt_closed, '%Y-%m') AS xval,
    COUNT(DISTINCT(eap.id)) AS yval,
    locations.location
FROM eap
LEFT JOIN locations ON eap.factory=locations.id
WHERE dt_closed BETWEEN '$from' AND '$to' AND status='CLOSED'
$WHERE
GROUP BY family, xval, locations.location
HAVING family IS NOT NULL
ORDER BY xval, family
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }


    public static function countEAPNotifiedByType($from, $to, $factory)
    {
        $WHERE = '';
        if ('ALL' !== $factory) {
            $WHERE = " AND locations.id='$factory' ";
        }
       $query = <<<SQL
SELECT
	COALESCE ((SELECT mods.type
		FROM mod_models AS mods
		WHERE mods.parent_id=eap.id AND mods.module='EAP'
		LIMIT 1
	), 'NO_TYPE') AS zval,
	date_format(log.date, '%Y-%m') AS xval,
	COUNT(DISTINCT(eap.id)) AS yval,
	locations.location
FROM fin_periods as p
LEFT JOIN eap ON date_format(eap.dt_opened, '%Y%m')=p.nam_period
INNER JOIN locations ON eap.factory=locations.id
INNER JOIN (
             SELECT parent_id, MIN(id), date
             FROM mod_logs
             WHERE module = 'EAP' AND comment = 'NOTIFICATION'
             group by parent_id
         ) log ON log.parent_id = eap.id
WHERE log.date BETWEEN '$from' AND '$to'
$WHERE
GROUP BY zval, xval, locations.location
ORDER BY locations.location, xval
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldTimekeeping
{
    private $itsID;

    private $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader(): array
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE timekeeping.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function perEngineer($user_id, $date_start, $date_end, $location = ''): array
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $OPTION = "timekeeping.user_id = $user_id";
        if ($user_id === 'ALL' && !empty($location)) {
            $OPTION = "timekeeping.location=$location";
        }
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE $OPTION AND timekeeping.dt_open BETWEEN '$date_start' AND '$date_end' AND timekeeping.tld_dpt = 'ENG'
            ORDER BY timekeeping.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function perLastXDaysBySubordinates($factory, $userid, $dept, $nbDays = null): array
    {
        $inClause = implode(',', self::getErpUsingActualHours());
        # Default to current month only
        $WHERE = " AND DATE_FORMAT(timekeeping.dt_open, '%Y%m') =DATE_FORMAT(CURDATE(), '%Y%m')";
        if ($nbDays) {
            $today = date('Y-m-d');
            $lastday = date('Y-m-d', strtotime("-$nbDays days"));
            $WHERE = " AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$lastday' AND '$today'";
        }

        $query = <<<SQL
            SELECT
          timekeeping.*,
          DATE_FORMAT(dt_open,'%m/%d') AS dt_open,
          ROUND(SUM(IF(timekeeping.location IN ($inClause), ROUND(timekeeping.hours_actual,1), ROUND(timekeeping.time_actual*8/100,1))), 1) as hours_actual,
          CONCAT(people.id,': ',people.firstname,' ',people.lastname) AS user_fullname
        FROM timekeeping
        LEFT JOIN people ON timekeeping.user_id=people.id
        WHERE people.reports_to=$userid AND timekeeping.location=$factory AND timekeeping.tld_dpt = '$dept' $WHERE
        GROUP BY timekeeping.dt_open,timekeeping.user_id
        ORDER BY timekeeping.dt_open
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method by constraints
     * @param mixed string or array $constraints
     * @return array
     */
    public static function byConstraints($constraints): array
    {
        $HAVING = is_array($constraints) ? tldUtils::constructWhere($constraints) : $constraints;
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        $ORDERBY = 'dt_open DESC';
        if (!empty($opt['orderBy'])) {
            $ORDERBY = TldDatabase::escape($opt['orderBy']);
        }
        $SELECT_extra = !empty($opt['select']) ? ", {$opt['select']}" : '';

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();

        $query = <<<EOF
            $SELECT
            $SELECT_extra
            $FROM
            WHERE 1=1
            $HAVING
            ORDER BY $ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getSELECT(): string
    {
        $inClause = implode(',', self::getErpUsingActualHours());
        return <<<EOF
SELECT
    timekeeping.*,
    IF(timekeeping.location IN ($inClause) OR timekeeping.tld_dpt='MIS', ROUND(timekeeping.hours_actual,1), ROUND(timekeeping.time_actual*8/100,1)) as hours_actual,
    CONCAT(people.id,': ',people.firstname,' ',people.lastname) AS user_fullname,
    locations.location AS man_location,
    IF(tasks.module ='EAP',tasks.parent_id,'') AS eap,
    IF(timekeeping.link ='Task',timekeeping.module_id,'') AS tasks_id,
    COALESCE(tasks.parent_id) AS tasks_parent_id,
    IF(tasks.module = 'EAP', tasks.hours, '') AS est_hours
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM timekeeping
    LEFT JOIN locations ON timekeeping.location=locations.erp
    LEFT JOIN people ON timekeeping.user_id=people.id
    LEFT JOIN tasks ON timekeeping.module_id=tasks.id AND timekeeping.link = 'Task' AND tasks.module = 'EAP'
EOF;
    }

    public function isEmpty(): bool
    {
        return empty($this->itsHeader);
    }

    /**
     * @return int[]
     */
    public static function getErpUsingActualHours(): array
    {
        return [400, 410, 420, 640, 660, 250, 430];
    }

    /**
     * @param array|string $if
     */
    public static function getMEAPHours(string $factory, $hours, array $if, string $status, string $start, string $end, $meapId = null, bool $productiveOnly = false, bool $direct = false): array
    {
        $erps = self::getErpUsingActualHours();
        // Hours formula based on Factory and Hours per day
        $time = 'ROUND(SUM(tk_hours_actual), 1) AS hours_actual';
        if (!in_array((int)$factory, $erps, true)) {
            $hours = empty($hours) ? 8 : $hours;
            $time = "ROUND(SUM( tk_time_actual ) * $hours / 100, 1) AS hours_actual";
        }
        // Special Case "All" Factory
        $LOCATION = " AND tk_location = $factory";
        if ($factory === 'All') {
            $inClause = implode(', ', $erps);
            $time = "ROUND(SUM(IF(tk_location IN ($inClause), tk_hours_actual, tk_time_actual*$hours/100)),1) AS hours_actual";
            $LOCATION = '';
        }
        $IFACTOR = '';
        $if = array_filter($if);
        if (!empty($if)) {
            $IFACTOR .= ' AND ifactor IN (' . implode(',', $if) . ') ';
        }

        if ($status === 'ALL') {
            $STATUS = '';
        } elseif ($status === 'ALL (OPENED)') {
            $STATUS = "AND status NOT IN('CLOSED', 'REJECTED')";
        } else {
            $STATUS = "AND status = '$status' ";
        }

        $CATEGORY = $productiveOnly ? " AND tk_category NOT IN ('Vacation-Sick Leave', 'Non Productive Hours', 'Other') " : '';
        $MEAPID = $meapId !== null ? " AND meap_id =  $meapId " : '';

        $SUBQUERY = <<<SQL
            SELECT * FROM tk_in_meap
            UNION
            SELECT * FROM tk_in_eap_linked_to_meap
            UNION
            SELECT * FROM tk_in_subeap_lvl1
            UNION
            SELECT * FROM tk_in_subeap_lvl2
            UNION
            SELECT * FROM tk_in_subeap_lvl3
            UNION
            SELECT * FROM tk_in_subeap_lvl4
            UNION
            SELECT * FROM tk_tasks_in_meap
            UNION
            SELECT * FROM tk_tasks_in_eap_linked_to_meap
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl1
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl2
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl3
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl4
SQL;

        if ($direct) {
            $SUBQUERY = <<<SQL
                SELECT * FROM tk_in_meap
                UNION
                SELECT * FROM tk_tasks_in_eap_linked_to_meap
SQL;
        }

        $query = <<<SQL
SELECT
  meap.id               AS meap_id,
  meap.status           AS status,
  locations.location    AS factory,
  meap.short_desc       AS meap_short_desc,
  meap.ifactor          AS ifactor,
  $time
FROM ($SUBQUERY) AS tk_views
  LEFT JOIN meap ON meap.id = tk_views.meap_id
  LEFT JOIN locations ON locations.erp = tk_views.tk_location
WHERE
  tk_tld_dpt = 'ENG'
  $LOCATION
  $STATUS
  $MEAPID
  AND DATE_FORMAT(tk_dt_open, '%Y-%m-%d') BETWEEN '$start' AND '$end'
  $IFACTOR
  $CATEGORY
GROUP BY tk_views.meap_id;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getMEAPHoursDetail(string $start, string $end, int $meapId, string $phase): array
    {
        $erps = self::getErpUsingActualHours();
        $inClause = implode(', ', $erps);
        $time = "ROUND(SUM(IF(tk_location IN ($inClause), tk_hours_actual, tk_time_actual*8/100)),1) AS hours_actual";

        $SUBQUERY = <<<SQL
            SELECT * FROM tk_in_meap
            UNION
            SELECT * FROM tk_in_eap_linked_to_meap
            UNION
            SELECT * FROM tk_in_subeap_lvl1
            UNION
            SELECT * FROM tk_in_subeap_lvl2
            UNION
            SELECT * FROM tk_in_subeap_lvl3
            UNION
            SELECT * FROM tk_in_subeap_lvl4
            UNION
            SELECT * FROM tk_tasks_in_meap
            UNION
            SELECT * FROM tk_tasks_in_eap_linked_to_meap
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl1
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl2
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl3
            UNION
            SELECT * FROM tk_tasks_in_subeap_lvl4
SQL;

        $query = <<<SQL
SELECT
  meap.id AS meap_id,
  '$phase' AS phase,
  DATE_FORMAT(tk_dt_open, '%Y-%m') as OpenDate,
  CONCAT(DATE_FORMAT(tk_dt_open, '%Y-%m'), '-01') as startDate,
  DATE_FORMAT(DATE_ADD(CONCAT(DATE_FORMAT(tk_dt_open, '%Y-%m'), '-01'), INTERVAL 1 MONTH), '%Y-%m-%d') as closedDate,
  $time
FROM ($SUBQUERY) AS tk_views
  LEFT JOIN meap ON meap.id = tk_views.meap_id
  LEFT JOIN locations ON locations.erp = tk_views.tk_location
WHERE
  tk_tld_dpt = 'ENG'
  AND meap_id =  $meapId
  AND DATE_FORMAT(tk_dt_open, '%Y-%m-%d') BETWEEN '$start' AND '$end'
GROUP BY tk_views.meap_id, OpenDate;
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }


    public static function getMEAPTimesheets($factory, $meap, $start, $end): array
    {
        $LOCATION = " AND tk_location = $factory";
        // Special Case "All" Factory
        if ($factory === 'All') {
            $LOCATION = '';
        }
        $query = <<<SQL
SELECT
  timekeeping.*,
  CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
  locations.location AS man_location,
  IF(tasks.module ='EAP',tasks.parent_id,'') AS eap,
  IF(timekeeping.link ='Task',timekeeping.module_id,'') AS tasks_id,
  COALESCE(tasks.parent_id) AS tasks_parent_id,
  IF(tasks.module = 'EAP', tasks.hours, '') AS est_hours
FROM (
       SELECT * FROM tk_in_meap
       UNION
       SELECT * FROM tk_in_eap_linked_to_meap
       UNION
       SELECT * FROM tk_in_subeap_lvl1
       UNION
       SELECT * FROM tk_in_subeap_lvl2
       UNION
       SELECT * FROM tk_in_subeap_lvl3
       UNION
       SELECT * FROM tk_in_subeap_lvl4
       UNION
       SELECT * FROM tk_tasks_in_meap
       UNION
       SELECT * FROM tk_tasks_in_eap_linked_to_meap
       UNION
       SELECT * FROM tk_tasks_in_subeap_lvl1
       UNION
       SELECT * FROM tk_tasks_in_subeap_lvl2
       UNION
       SELECT * FROM tk_tasks_in_subeap_lvl3
       UNION
       SELECT * FROM tk_tasks_in_subeap_lvl4
     ) AS tk_views
  LEFT JOIN meap ON meap.id = tk_views.meap_id
  LEFT JOIN timekeeping ON timekeeping.id = tk_views.tk_id
  LEFT JOIN locations ON locations.erp = tk_location
  LEFT JOIN people ON timekeeping.user_id=people.id
  LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module IN('EAP', 'MEAP')
WHERE
  tk_tld_dpt = 'ENG'
  $LOCATION
  AND tk_views.meap_id = $meap
  AND DATE_FORMAT(tk_dt_open, '%Y-%m-%d') BETWEEN '$start' AND '$end'
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getHoursAmount($factory, $hours, $module, $module_id, $user_id, $start, $end): array
    {
        $erps = self::getErpUsingActualHours();
        // Variables
        $WHERE = '';
        $USER = '';
        $LEFT_JOIN = '';
        $LOCATION = " AND timekeeping.location = $factory";
        // User
        if (!empty($user_id)) {
            $USER = " AND timekeeping.user_id = $user_id";
        }
        // Hours formula based on Factory and Hours per day
        if (!in_array((int)$factory, $erps, true)) {
            if (empty($hours)) {
                $hours = '8';
            }
            $time = "ROUND(SUM( time_actual )*$hours/100, 1) AS hours_actual";
        } else {
            $time = 'ROUND(SUM( hours_actual ), 1) AS hours_actual';
        }
        // Special Case "All" Factory
        if ($factory === 'All') {
            $inClause = implode(', ', $erps);
            $time = "ROUND(SUM(IF(timekeeping.location IN ($inClause),hours_actual,time_actual*$hours/100)),1) AS hours_actual";
            $LOCATION = '';
        }
        // Module
        if (!empty($module)) {
            $WHERE = " AND timekeeping.project = '$module'";
        }
        // Module ID
        if (!empty($module_id)) {
            switch ($module) {
                case 'EAP':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'EAP'
                    LEFT JOIN eap ON eap.id = tasks.parent_id OR (eap.id = timekeeping.module_id AND timekeeping.link = 'EAP')
EOF;

                    $WHERE = " AND eap.id = $module_id";
                    break;
                case 'MEAP':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'EAP'
                    LEFT JOIN eap ON eap.id = tasks.parent_id OR (eap.id = timekeeping.module_id AND timekeeping.link = 'EAP')
                    LEFT JOIN meap ON meap.id = eap.parent_id
EOF;

                    $WHERE = " AND meap.id = $module_id";
                    break;
                case 'GWF':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'GWF'
                    LEFT JOIN gwf ON gwf.id = tasks.parent_id OR (gwf.id = timekeeping.module_id AND timekeeping.link = 'GWF')
EOF;

                    $WHERE = " AND gwf.id = $module_id";
                    break;
                case 'PDC':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'PDC'
                    LEFT JOIN demerit ON demerit.id = tasks.parent_id OR (demerit.id = timekeeping.module_id AND timekeeping.link = 'PDC')
EOF;

                    $WHERE = " AND demerit.id = $module_id";
                    break;
                case 'NCR':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'NCR'
                    LEFT JOIN ncr ON ncr.id = tasks.parent_id
EOF;

                    $WHERE = " AND ncr.id = $module_id";
                    break;
                case 'SB':
                    $LEFT_JOIN = <<<EOF
                    LEFT JOIN tasks ON tasks.id = timekeeping.module_id AND timekeeping.link = 'Task' AND tasks.module = 'SB'
                    LEFT JOIN sb ON sb.id = tasks.parent_id
EOF;

                    $WHERE = " AND sb.id = $module_id";
                    break;
            }
        }

        $query = <<<EOF
SELECT
locations.location AS factory,
CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
$time
FROM timekeeping
LEFT JOIN locations ON locations.erp = timekeeping.location
LEFT JOIN people ON people.id = timekeeping.user_id
$LEFT_JOIN
WHERE tld_dpt = 'ENG' $LOCATION $USER AND DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' $WHERE

EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function fullReportENG($start, $end, $location, $hours, $opt = []): array
    {
        $WHERE = '';
        if (!empty($location)) {
            $WHERE .= " AND timekeeping.location = $location ";
        }
        if (isset($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . TldDatabase::escape($opt['orderBy']);
        } else {
            $ORDERBY = 'ORDER BY dt_open';
        }
        if (empty($hours)) {
            $hours = 8;
        }

        $query = <<<EOF
          SELECT
                timekeeping.id,
                locations.location AS man_location,
                dt_open,
                CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
                timekeeping.category,
                product_type,
                project,
                CASE
                WHEN tasks.module = 'PDC' THEN tasks.parent_id
                WHEN tasks.module = 'EAP' THEN tasks.parent_id
                WHEN tasks.module = 'MEAP' THEN tasks.parent_id
                WHEN tasks.module = 'GWF' THEN tasks.parent_id
                WHEN tasks.module = 'SOL' THEN tasks.parent_id
                ELSE module_id
                END AS module,
                IF(link = 'Task', timekeeping.module_id, '') AS task_id,
                link,
                ROUND(time_actual*$hours/100, 2) AS time_actual_calcul,
                ROUND(time_forecast*$hours/100, 2) AS time_forecast_calcul,
                hours_actual,
                hours_forecast,
                comment,
                timekeeping.link AS timekeeping_link,
                timekeeping.module_id AS timekeeping_module_id,
                tasks.module AS tasks_module,
                tasks.parent_id AS tasks_parent_id,
                tasks.task as tasks_desc
            FROM timekeeping
                LEFT JOIN locations ON timekeeping.location=locations.erp
                LEFT JOIN people ON timekeeping.user_id=people.id
                LEFT JOIN tasks ON timekeeping.module_id=tasks.id AND timekeeping.link = 'Task' AND (tasks.module IN ('EAP', 'MEAP', 'PDC'))
            WHERE tld_dpt = 'ENG' AND dt_open BETWEEN '$start' AND '$end' $WHERE
            GROUP BY timekeeping.id
            $ORDERBY
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        $cache = [];
        // Get EAP information:
        foreach ($rows AS &$row) {
            $row['meap'] = '';
            $row['meap_model'] = '';
            if ($row['timekeeping_link'] === 'Task' && $row['tasks_module'] === 'EAP') {
                if (!array_key_exists("EAP_{$row['tasks_parent_id']}", $cache)) {
                    $id = (int)$row['tasks_parent_id'];
                    $eap = new tldEAP($id);
                    $root = $eap->getParentModuleIDFamily();
                    if (($root['module'] === 'MEAP')) {
                        $query = <<<EOF
                        SELECT model
                        FROM meap
                        WHERE id={$root['id']}
    EOF;
                        $meap = tldUtils::getSqlRowToAssocArray($query);

                    }
                    $cache["EAP_{$row['tasks_parent_id']}"]['tasks_desc'] = $eap->getShortDesc();
                    $cache["EAP_{$row['tasks_parent_id']}"]['meap'] = $root['id'] ?? '';
                    $cache["EAP_{$row['tasks_parent_id']}"]['meap_model'] = $meap['model'] ?? '';
                }

                $row['meap'] = $cache["EAP_{$row['tasks_parent_id']}"]['meap'];
                $row['meap_model'] = $cache["EAP_{$row['tasks_parent_id']}"]['meap_model'];
            } elseif ($row['timekeeping_link'] === 'EAP' && $row['timekeeping_module_id'] > 0) {
                if (!array_key_exists("EAP_{$row['timekeeping_module_id']}", $cache)) {
                    $id = (int)$row['timekeeping_module_id'];
                    $eap = new tldEAP($id);
                    $root = $eap->getParentModuleIDFamily();
                    if (($root['module'] === 'MEAP')) {
                        $query = <<<EOF
        			SELECT model
        			FROM meap
        			WHERE id={$root['id']}
EOF;
                        $meap = tldUtils::getSqlRowToAssocArray($query);
                    }
                    $cache["EAP_{$row['timekeeping_module_id']}"]['tasks_desc'] = $eap->getShortDesc();
                    $cache["EAP_{$row['timekeeping_module_id']}"]['meap'] = $root['id'] ?? '';
                    $cache["EAP_{$row['timekeeping_module_id']}"]['meap_model'] = $meap['model'] ?? '';
                }

                $row['tasks_desc'] = $cache["EAP_{$row['timekeeping_module_id']}"]['tasks_desc'];
                $row['meap'] = $cache["EAP_{$row['timekeeping_module_id']}"]['meap'];
                $row['meap_model'] = $cache["EAP_{$row['timekeeping_module_id']}"]['meap_model'];

            } elseif ($row['timekeeping_link'] === 'MEAP' && $row['timekeeping_module_id'] > 0) {
                if (!array_key_exists("MEAP_{$row['timekeeping_module_id']}", $cache)) {
                    $id = (int)$row['timekeeping_module_id'];
                    $query = <<<EOF
        			SELECT model, short_desc
        			FROM meap
        			WHERE id=$id
EOF;
                    $meap = tldUtils::getSqlRowToAssocArray($query);
                    $cache["MEAP_{$row['timekeeping_module_id']}"]['meap'] = $id;
                    $cache["MEAP_{$row['timekeeping_module_id']}"]['meap_model'] = $meap['model'];
                    $cache["MEAP_{$row['timekeeping_module_id']}"]['short_desc'] = $meap['short_desc'];
                }
                $row['meap'] = $cache["MEAP_{$row['timekeeping_module_id']}"]['meap'];
                $row['meap_model'] =  $cache["MEAP_{$row['timekeeping_module_id']}"]['meap_model'];
                $row['tasks_desc'] = $cache["MEAP_{$row['timekeeping_module_id']}"]['short_desc'];

            } elseif ($row['timekeeping_link'] === 'Task' && $row['tasks_module'] === 'MEAP') {
                if (!array_key_exists("MEAP_{$row['tasks_parent_id']}", $cache)) {
                    $id = (int)$row['tasks_parent_id'];
                    $query = <<<EOF
        			SELECT model, short_desc
        			FROM meap
        			WHERE id=$id
EOF;
                    $meap = tldUtils::getSqlRowToAssocArray($query);
                    $cache["MEAP_{$row['tasks_parent_id']}"]['meap'] = $id;
                    $cache["MEAP_{$row['tasks_parent_id']}"]['meap_model'] = $meap['model'];
                    $cache["MEAP_{$row['tasks_parent_id']}"]['short_desc'] = $meap['short_desc'];
                }

                $row['meap'] = $cache["MEAP_{$row['tasks_parent_id']}"]['meap'];
                $row['meap_model'] =  $cache["MEAP_{$row['tasks_parent_id']}"]['meap_model'];
            }
        }

        return $rows;
    }

    public static function delete($id)
    {
        $id = (int)$id;
        $query = <<<EOF
        DELETE FROM timekeeping
        WHERE id = $id
EOF;
        tldUtils::sqlQuery($query);
    }


    public static function perLastXDays($location, $hours, $id, $tld_dpt = '', $nbDays = 20): array
    {
        $erps = self::getErpUsingActualHours();
        $WHERE = '';
        $time = '';
        // Construct constraints
        if (!empty($location)) {
            $WHERE .= " AND timekeeping.location = $location ";
            if (!in_array((int)$location, $erps, true)) {
                if (empty($hours)) {
                    $hours = '8';
                }
                $time = " ROUND(SUM( time_actual )*$hours/100, 1) AS hours_actual, ";
            } else {
                $time = ' ROUND(SUM( hours_actual ), 1) AS hours_actual, ';
            }
        }

        $today = date('Y-m-d');
        $lastday = date('Y-m-d', strtotime("-$nbDays days"));

        if (!empty($id)) {
            $WHERE .= " AND timekeeping.user_id = $id";
        }

        if ($tld_dpt === 'ENG') {
            $tld_dpt = " AND tld_dpt = 'ENG'";
        }
        if ($tld_dpt === 'MIS') {
            $tld_dpt = " AND tld_dpt = 'MIS'";
        }

        $query = <<<EOF
          SELECT
                locations.location AS man_location,
                CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
                $time
                DATE_FORMAT(dt_open,'%m/%d') AS dt_open
            FROM timekeeping
                LEFT JOIN locations ON timekeeping.location=locations.erp
                LEFT JOIN people ON timekeeping.user_id=people.id
            WHERE DATE_FORMAT(dt_open,'%Y-%m-%d') BETWEEN '$lastday' AND '$today' $WHERE $tld_dpt
            GROUP BY user_id, dt_open
            ORDER BY timekeeping.dt_open
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }


    public static function perDay($date, $location, $hours, $tld_dpt = '', $userid = null): array
    {
        $erps = self::getErpUsingActualHours();
        $WHERE = '';
        $time = '';
        // Construct constraints
        if (!empty($location)) {
            $WHERE .= " AND timekeeping.location = $location ";
            if (!in_array((int)$location, $erps, true)) {
                if (empty($hours)) {
                    $hours = '8';
                }
                $time = " ROUND(SUM( time_actual )*$hours/100, 1) AS hours_actual, ";
            } else {
                $time = ' ROUND(SUM( hours_actual ), 1) AS hours_actual, ';
            }
        }
        if ($tld_dpt === 'ENG') {
            $tld_dpt = " AND tld_dpt = 'ENG'";
        }
        if ($tld_dpt === 'MIS') {
            $tld_dpt = " AND tld_dpt = 'MIS'";
        }
        if (!empty($userid)) {
            $WHERE .= " AND people.id = $userid ";
        }
        $query = <<<EOF
          SELECT
                locations.location AS man_location,
                CONCAT(people.id,': ', people.firstname,' ',people.lastname) AS user_fullname,
                $time
                DATE_FORMAT(dt_open,'%m/%d') AS dt_open
            FROM timekeeping
                LEFT JOIN locations ON timekeeping.location=locations.erp
                LEFT JOIN people ON timekeeping.user_id=people.id
            WHERE DATE_FORMAT(dt_open,'%Y-%m') LIKE '$date' $WHERE $tld_dpt
            GROUP BY user_id, dt_open
            ORDER BY dt_open
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }


    /**
     * Get latest personal timesheets, defaults to last 5
     *
     * @param integer $id
     * @param integer $qty
     * @return array
     */
    public static function byLatest($id, $qty = 5): array
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE timekeeping.user_id=$id
ORDER BY dt_open DESC, id DESC
LIMIT $qty
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Return hours scheduled in % for the given id and date
     *
     * @param integer $id
     * @param string $date
     * @return string
     */
    public static function getDailyHours($id, $date, $option)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE timekeeping.user_id=$id AND dt_open='$date'
ORDER BY dt_open DESC, id DESC
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if (!$rows) {
            return '0';
        }

        $hours_actual = 0;
        foreach ($rows as $row) {
            $hours_actual += $row[$option];
        }

        return $hours_actual;
    }

    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'Timekeep';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;
        return tldModLog::insert($a);
    }

    public function getFullLog(): array
    {
        $query = <<<EOF
        SELECT
            mod_logs.*, concat(a.lastname,', ',a.firstname) as poster_fullname
        FROM mod_logs
            LEFT JOIN people AS a ON mod_logs.poster=a.id
        WHERE
            (mod_logs.parent_id=$this->itsID
            AND mod_logs.module LIKE 'Timekeep' AND log_num<>10)
EOF;

        $query .= ' ORDER BY id DESC';
        return tldUtils::getSqlToAssocArray($query);
    }

    public function editPermission($id): bool
    {
        return $this->itsHeader['user_id'] == $id && strtotime($this->itsHeader['dt_open']) > strtotime('-15 days');
    }

    /**
     * Return timesheets for the given id and date
     *
     * @param integer $id
     * @param string $date
     */
    public static function getDailyTimesheets($id, $date, $tld_dpt): array
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE timekeeping.user_id=$id AND dt_open='$date' AND tld_dpt ='$tld_dpt'
ORDER BY dt_open DESC, id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getTimesheetsByMISProjectByConstraints($a): array
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $query = <<<EOF
SELECT
    timekeeping.*,
    CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
    locations.location AS man_location,
    tasks.task AS task,
    mis_tts.id AS tts_id
FROM timekeeping
    LEFT JOIN locations ON timekeeping.location=locations.erp
    LEFT JOIN people ON timekeeping.user_id=people.id
    LEFT JOIN tasks ON timekeeping.module_id=tasks.id AND timekeeping.link = 'Task' AND tasks.module = 'TTS'
    LEFT JOIN mis_tts ON mis_tts.id = tasks.parent_id
WHERE
    tld_dpt LIKE 'MIS'
    $WHERE
ORDER BY
    dt_open DESC,
    id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getTimesheetsByMISProject($project, $start, $end)
    {
        $a = "dt_open BETWEEN '$start' AND '$end' AND mis_tts.id = $project";
        return self::getTimesheetsByMISProjectByConstraints($a);
    }

    public static function getHoursByMISProject($project)
    {
        $query = <<<EOF
SELECT
    timekeeping.*,
    CONCAT(people.firstname,' ',people.lastname) AS user_fullname,
    locations.location AS man_location,
    mis_tts.id AS tts_id,
    ROUND(SUM( hours_actual ), 1) AS hours_actual
FROM timekeeping
    LEFT JOIN locations ON timekeeping.location=locations.erp
    LEFT JOIN people ON timekeeping.user_id=people.id
    LEFT JOIN tasks ON timekeeping.module_id=tasks.id AND timekeeping.link = 'Task' AND tasks.module = 'TTS'
    LEFT JOIN mis_tts ON mis_tts.id = tasks.parent_id
WHERE tld_dpt ='MIS' AND mis_tts.id = $project
GROUP BY tts_id
EOF;

        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['hours_actual'];
    }


    /**
     * Create a new Timesheet row in the database
     * @param array $p
     */
    public static function insert($p, $option = '')
    {
        $fields = ['location', 'user_id', 'dt_open', 'category', 'product_type', 'project', 'link', 'module_id', 'time_actual', 'time_forecast', 'comment', 'hours_actual', 'hours_forecast'];
        //Check if EAP not CLOSED or REJECTED
        if($p['link'] === 'EAP') {
            $eap = new tldEAP($p['module_id']);
            if (empty($eap->itsHeader)) {
                return sprintf('EAP#%s not found', $p['module_id']);
            }
            if (in_array($eap->getStatus(), ['CLOSED', 'REJECTED']) && new DateTime($p['dt_open']) > new DateTime($eap->itsHeader['dt_closed'])) {
                return sprintf('You cannot submit Timesheet on EAP#%s because it is %s', $eap->itsID, $eap->getStatus());
            }
        }
        //Check if MEAP not CLOSED or REJECTED
        if($p['link'] === 'MEAP') {
            $meap = new tldMEAP($p['module_id']);
            if (empty($meap->itsHeader)) {
                return sprintf('MEAP#%s not found', $p['module_id']);
            }
            if (in_array($meap->getStatus(), ['CLOSED', 'REJECTED']) && new DateTime($p['dt_open']) > new DateTime($eap->itsHeader['dt_closed'])) {
                return sprintf('You cannot submit Timesheet on MEAP#%s because it is %s', $meap->itsID, $meap->getStatus());
            }
        }
        if ($option === 'ENG') {
            $option = "tld_dpt = 'ENG',";
        }
        if ($option === 'MIS') {
            $option = "tld_dpt = 'MIS',";
        }
        if (!empty($p['dt_to'])) {
            $begin = new DateTime($p['dt_open']);
            $end = new DateTime($p['dt_to']);
            $end = $end->modify('+1 day');
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
            $insertedDaysCount = 0;
            $ret = null;
            foreach ($period as $v) {
                if ($v->format('N') <= 5) {
                    $p['dt_open'] = $v->format('Y-m-d');
                    $query = "INSERT INTO timekeeping SET $option ";
                    $query .= tldUtils::getSqlSet($p, $fields);
                    $ret = tldUtils::sqlInsert($query);
                    $insertedDaysCount++;
                }
            }
            if ($insertedDaysCount === 0) {
                return "You can't enter a timesheet on a saturday or a sunday";
            }
            return $ret;
        }

        $query = "INSERT INTO timekeeping SET $option ";
        $query .= tldUtils::getSqlSet($p, $fields);
        return tldUtils::sqlInsert($query);
    }


    /**
     * Generic Timekeeping update method
     * @param $data array of esr datas
     * @param $fields array of timekeeping fields to update
     * @return string on error
     */
    public function update($data, $fields = [])
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE timekeeping SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    /**
     * Get list of project where status is set to 'ACTIVE'
     */
    public static function getProjectList($format = ''): array
    {
        $query = "SELECT * FROM timekeeping_lists WHERE status='ACTIVE' AND type='PROJECT' ORDER BY name";
        if ($format === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['name', 'name']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of names for the 'OTHER' category where status is set to 'ACTIVE'
     */
    public static function getOtherList($format = ''): array
    {
        $query = "SELECT * FROM timekeeping_lists WHERE status='ACTIVE' AND type='OTHER' ORDER BY name";
        if ($format === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['name', 'name']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Category based on Task number
     */
    public static function getTaskCategory($id, $format = ''): array
    {
        $query = "SELECT module FROM tasks WHERE id=$id ";
        if ($format === 'smartyOptions') {
            return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['module', 'module']);
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Model based on Task number
     */
    public static function getTaskModel(int $id): array
    {
        $result = [];
        $mod = tldUtils::getSqlRowToAssocArray("SELECT module, parent_id FROM tasks WHERE id=$id");
        if (!$mod['parent_id'] || !is_numeric($mod['parent_id'])) {
            return $result;
        }
        if ($mod['module'] === 'EAP') {
            $query = "SELECT model FROM mod_models WHERE module='EAP' AND parent_id=" . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'PDC') {
            $query = 'SELECT model FROM demerit WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'SOL') {
            $query = 'SELECT model FROM sor_lines WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'GWF') {
            $query = 'SELECT model FROM gwf WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'ER') {
            $query = 'SELECT model FROM service WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'WC') {
            $query = 'SELECT model FROM warranty WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'TOC') {
            $query = 'SELECT service.model AS model FROM toc LEFT JOIN service ON service.id = toc.erid WHERE toc.id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        } elseif ($mod['module'] === 'NCR') {
            $query = 'SELECT model FROM ncr WHERE id=' . $mod['parent_id'];
            $result = tldUtils::getSqlToAssocArray($query);
        }

        return $result;
    }

    public static function getModel($id, $link): array
    {
        $result = [];
        if (!$id || !is_numeric($id)) {
            return [];
        }
        if ($link === 'EAP') {
            $query = "SELECT model FROM mod_models WHERE module='EAP' AND parent_id=$id";
            $result = tldUtils::getSqlToAssocArray($query);
        }
        if ($link === 'PDC') {
            $query = "SELECT model FROM demerit WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'ER') {
            $query = "SELECT model FROM service WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'SB') {
            $query = "SELECT sbs_lines.model FROM sbs LEFT JOIN sbs_lines ON sbs_lines.parent_id=sbs.id WHERE sbs.id=$id";
            $result = tldUtils::getSqlToAssocArray($query);
        }
        if ($link === 'SB3') {
            $query = "SELECT service.model FROM sb
            LEFT JOIN sb_lines ON sb_lines.parent_id=sb.id
            LEFT JOIN service ON service.id=sb_lines.er_id
            WHERE sb.id=$id";
            $result = tldUtils::getSqlToAssocArray($query);
        }
        if ($link === 'SOL') {
            $query = "SELECT model FROM sor_lines WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'GWF') {
            $query = "SELECT model FROM gwf WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'WC') {
            $query = "SELECT model FROM warranty WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'TOC') {
            $query = "SELECT service.model AS model FROM toc LEFT JOIN service ON service.id = toc.erid WHERE toc.id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'PIP') {
            $query = "SELECT TRIM(model) FROM pip WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'MANUAL') {
            $query = "SELECT TRIM(model) FROM manuals WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        if ($link === 'MEAP') {
            $query = "SELECT TRIM(model) FROM meap WHERE id=$id";
            $result = tldUtils::getSqlRowToAssocArray($query);
        }
        return $result;
    }

    public static function getLinkList(): array
    {
        $linkList = ['PDC', 'SB3', 'SOL', 'GWF', 'TOC', 'CPA', 'NCR', 'PIP', 'MEAP', 'EAP', 'FAQ', 'AGILE', 'CRAB'];
        sort($linkList);
        return array_merge(['N/A'], $linkList);
    }

    public static function getCategoryList(): array
    {
        $categoryList = ['Design Task', 'Training', 'Supplier / Customer Visit', 'Production Support', 'Field / SPR Support', 'Team Meeting', 'Non Productive Hours', 'Vacation', 'Sick Leave'];
        sort($categoryList);
        return $categoryList;
    }

    public static function getActualHoursByEAP(array $eapIds): array {

        $eapIds = implode(',', $eapIds);
        $inClause = implode(',', tldTimekeeping::getErpUsingActualHours());

        $hoursByEAPData = <<<SQL
SELECT ROUND(SUM(hours),1) AS hours, eap FROM
(
        (SELECT ROUND(SUM(IF(timekeeping.location IN ($inClause), timekeeping.hours_actual, timekeeping.time_actual*8/100)),1) hours, tasks.parent_id AS eap
    FROM timekeeping
    LEFT JOIN tasks ON tasks.id = timekeeping.module_id
    WHERE  link = 'Task'  AND tasks.module = 'EAP' AND tasks.parent_id IN ($eapIds)
    GROUP BY tasks.parent_id)
UNION
    (SELECT ROUND(SUM(IF(timekeeping.location IN ($inClause), timekeeping.hours_actual, timekeeping.time_actual*8/100)),1) hours, module_id AS eap
    FROM timekeeping
    WHERE link = 'EAP' AND  module_id IN ($eapIds)
    GROUP BY module_id))
    tmp
GROUP BY eap
SQL;

        return array_column(tldUtils::getSqlToAssocArray($hoursByEAPData), 'hours', 'eap');
    }

}

class tldMEAP
{
    public $itsID;
    /** @var array */
    public $itsHeader;

    /** @var tldMEAP|tldEAP|null */
    private $familyParentModule = null;
    /** @var array */
    private $acoo;
    /** @var array */
    private $gcoo;
    /** @var array */
    private $gceo;

    public function __construct($id, $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    public static function getFileUploadDirectory()
    {
        return $GLOBALS['UPLOADS_PATH'] . '/meap';
    }

    public function getHeader()
    {
        $query = <<<EOF
        SELECT meap.*,
        	'{$this->getModule()}' AS module,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=meap.poster
            ) AS poster_fullname,
            CONCAT(init.lastname,', ', init.firstname) AS proj_leader_fullname,
            init.email AS proj_leader_email,
            erp.location AS factory_fullname,
            erp.erp AS factory_erp
        FROM meap
            LEFT JOIN people AS init ON meap.proj_leader=init.id
            LEFT JOIN locations AS erp ON meap.factory=erp.id
        WHERE meap.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getID()
    {
        return $this->itsHeader['id'];
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    /**
     * @deprecated
     */
    public function notifyPL($message, $subject = '', $cc = '')
    {
        return $this->notifyProjectLeader($message, $subject, $cc);
    }

    public function notifyProjectLeader($message, $subject = '', $cc = '')
    {
        $reporter = new tldUser($this->getProjectLeader());
        if (empty($subject)) {
            $subject = 'Master EAP# ' . $this->itsID . ' updated';
        }
        return tldUtils::emailAttachment($reporter->getEmail(), 'noreply@tld-gse.com', $subject, $message, null, $cc);
    }

    public function getEMSList()
    {
        $ems = new tldGroup('gg_mod_eap_admin', ((int)$this->itsHeader['factory'] === 37) ? 500 : $this->itsHeader['factory_erp']);
        return $ems->getEmailList();
    }

    public static function getEconCurrencyList(): array
    {
        return ['USD' => 'USD', 'EUR' => 'EUR', 'CAD' => 'CAD', 'RMB' => 'RMB', 'GBP' => 'GBP', 'INR' => 'INR'];
    }

    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'proj_leader', 'factory', 'product_type', 'ifactor', 'model',
            'econ_capitalized', 'econ_currency', 'type', 'purpose', 'short_desc',
            'parent_id', 'parent_module', 'description', 'filename', 'pvt', 'cost_calculation_method', 'poster',
        ];
        $query = <<<EOF
            INSERT INTO meap
    		SET date=NOW(), status='PROPOSAL',
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);
        $return = tldUtils::sqlInsert($query);

        if (is_int($return)) {
            $meap = new tldMEAP($return);
            $meap->insertPCD('MEAP PHASE', 'PHASE 0 CLOSURE');
            $meap->insertPCD('MEAP PHASE', 'PHASE 1 CLOSURE');
            $meap->insertPCD('MEAP PHASE', 'PHASE 2 CLOSURE');
            $meap->insertPCD('MEAP PHASE', 'PHASE 3 CLOSURE');
            $meap->insertPCD('MEAP PHASE', 'PHASE 4 CLOSURE');

            if (\in_array((int) $meap->getImportanceFactor(), [100, 1000, 10000], true)) {
                $meap->createChildsEAP();
            }

            if (count($p['members'] ?? [])) {
                $meap->addMember($p['members']);
            }

           foreach ($p['factories'] as $factory) {
                $meap->insertInvolvedFactory((int) $factory);
            }
        }

        return $return;
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function byLatest($num = 10)
    {
        $query = <<<EOF
            SELECT * 
            FROM meap
            ORDER BY id DESC
            LIMIT $num
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Refresh the cached header information
     *
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Add a comment to the log
     *
     * @param array $a Array structure containing 'poster' and 'comment'
     *
     * @return boolean
     */
    public function addComment($a)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'MEAP';
        return tldModLog::insert($a);
    }

    /**
     * Get related BPs to this MEAP
     *
     * @return array
     */
    public function getBPS($mode = '')
    {
        return tldBP::byParent($this->itsID, 'MEAP', $mode);
    }

    public function isClosed()
    {
        return in_array($this->getStatus(), ['REJECTED', 'CLOSED'], true);
    }

    public function changeStatus($status = '', $bpCancelled = false)
    {
        if (empty($this->itsID)) {
            return 'MEAP ID not set in object';
        }
        if ($this->isClosed()) {
            if (!empty($status)) {
                return 'Can not change status, MEAP is already closed';
            }

            return [];
        }
        $current_status = $this->getStatus();
        if (!empty($status) && $status === $current_status) {
            return true;
        }
        // Get allowed
        $allowed = [];
        $message = [];
        switch ($current_status) {
            case 'PROPOSAL':
                $allowed = ['GATE_PROPOSAL', 'REJECTED', 'SUSPENDED'];
                break;
            case 'SUSPENDED':
                $allowed = ['REJECTED', 'PROPOSAL'];
                break;
            case 'GATE_PROPOSAL':
                if ($bpCancelled) {
                    $allowed[] = 'PROPOSAL';
                }
                if (0 === count($this->getBPS())) {
                    $allowed[] = 'PHASE_0';
                } else {
                    $message['message'] = "This MEAP has BPs, you cannot change to the next status";
                }
                break;
            case 'PHASE_0':
                if (!$this->isReadyForGate0()) {
                    $allowed[] = $current_status;
                    $message['message'] = "Missing prerequisites for GATE_0. Check presence of files for Goal Sheet, DTC, or project schedule. Check if PCD has an original target date defined. Check all economic fields are filled. Check if there is no BP linked to the MEAP";
                    break;
                }
            case 'PHASE_1':
            case 'PHASE_2':
            case 'PHASE_3':
            case 'PHASE_4':
                $allowed[] = $current_status;
                $allowed[] = 'GATE_' . substr($current_status, -1);
                if (($children = $this->getFirstLevelMEAPChildren()) && array_diff(array_unique(array_column($children, 'status')), ['CLOSED'])) {
                    $message['message'] = "All children are not closed";
                    break;
                }
                if (!count($this->getTasks())) {
                    $allowed[] = 'CLOSED';
                }
                break;
            case 'GATE_0':
            case 'GATE_1':
            case 'GATE_2':
            case 'GATE_3':
                $allowed[] = $current_status;
                if (!count($this->getBPS())) {
                    $allowed[] = 'PHASE_' . ($bpCancelled ? substr($current_status, -1) : (substr($current_status, -1) + 1));
                } else {
                    $message['message'] = "This MEAP has BPs, you cannot change to the next status";
                }
                if (($children = $this->getFirstLevelMEAPChildren()) && array_diff(array_unique(array_column($children, 'status')), ['CLOSED'])) {
                    $message['message'] = "All children are not closed";
                    break;
                }
                if (!count($this->getBPS()) && !count($this->getTasks())) {
                    $allowed[] = 'CLOSED';
                }
                break;
            case 'GATE_4':
                $allowed[] = $current_status;
                // Check for MEAP 10000 that every children are closed
                if (($children = $this->getFirstLevelMEAPChildren()) && array_diff(array_unique(array_column($children, 'status')), ['CLOSED'])) {
                    $message['message'] = "All children are not closed";
                    break;
                }
                if ($bpCancelled) {
                    $allowed[] = 'PHASE_4';
                }
                if (!count($this->getBPS()) && !count($this->getTasks())) {
                    $allowed[] = 'CLOSED';
                }
                break;
        }
        $allowed = array_combine($allowed, $allowed);
        // Return allowed
        if (empty($status)) {
            return array_merge(['allowed' => $allowed], $message);
        }
        // Set status
        if (!in_array($status, $allowed, true)) {
            return "{$status} is not an allowed status for MEAP at {$current_status}";
        }
        $SET = '';
        switch ($status) {
            case 'REJECTED':
            case 'CLOSED':
                $SET = ', date_closed=NOW()';
                break;
            case 'SUSPENDED':
                $SET = ', date_suspended=NOW()';
                break;
            // Set actual close date for current PHASE_X statuses
            case 'GATE_0':
            case 'GATE_1':
            case 'GATE_2':
            case 'GATE_3':
            case 'GATE_4':
                $ph = (int)substr($status, -1);
                $query = <<<EOF
	    	UPDATE meap_pcd
	    	SET actual_date=NOW()
	    	WHERE type='MEAP PHASE' AND description='PHASE {$ph} CLOSURE' AND parent_id={$this->itsID}
EOF;
                if ($error = tldUtils::sqlQuery($query)) {
                    return $error;
                };
                break;

            case 'PHASE_0':
            case 'PHASE_1':
            case 'PHASE_2':
            case 'PHASE_3':
            case 'PHASE_4':
                if (!$bpCancelled) {
                    break;
                }
                $ph = substr($status, -1);
                $query = <<<EOF
	    	UPDATE meap_pcd
	    	SET actual_date=NULL
	    	WHERE type='MEAP PHASE' AND description='PHASE {$ph} CLOSURE' AND parent_id={$this->itsID}
EOF;
                if ($error = tldUtils::sqlQuery($query)) {
                    return $error;
                };
                break;
        }
        $query = <<<EOF
		UPDATE meap
		SET status=UCASE('{$status}') {$SET}
		WHERE id={$this->itsID}
EOF;
        $e = tldUtils::sqlQuery($query);
        $this->refresh();

        return $e;
    }

    /**
     * it should return Antoine as per spec
     * @return array
     */
    private function getACOO()
    {
        if (null === $this->acoo) {
            $this->acoo = tldGroup::inGroup('role_GCEO');
        }

        return $this->acoo;
    }

    /**
     * it should return Erwan as per spec
     * @return array
     */
    private function getGCOO()
    {
        if (null === $this->gcoo) {
            $this->gcoo = tldGroup::inGroup('role_TCOO');
        }

        return $this->gcoo;
    }

    /**
     * it should return Valentin as per spec
     * @return array
     */
    private function getGCEO()
    {
        if (null === $this->gceo) {
            $acoo = array_column($this->getACOO(), 'id');
            // Only way to separate Valentin/Antoine via groups
            $this->gceo = array_filter(
                tldGroup::inGroup('role_GCOO', 900),
                static function (array $user) use ($acoo) {
                    return !in_array($user['id'], $acoo);
                }
            );
        }

        return $this->gceo;
    }

    /**
     * @param string $status
     *
     * @return array
     */
    public function getApprovers($status) {
        $erp = $this->getERP();
        $involvedApprovers = [];
        if (10000 === $importanceFactor = (int) $this->getImportanceFactor()) {
            $involvedErps = array_column($this->getInvolvedFactories(), 'erp');
            foreach ($involvedErps as $involvedErp) {
                $involvedApprovers[] = tldGroup::inGroup('role_COO', $involvedErp);
                $involvedApprovers[] = tldGroup::inGroup('role_RCOO', $involvedErp);
                $involvedApprovers[] = tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($involvedErp));
            }
        }
        $involvedApprovers = array_merge(...$involvedApprovers);

        $approvers = array_merge(
            tldGroup::inGroup('role_EM', $erp),
            tldGroup::inGroup('role_COO', $erp)
        );
        switch ($status) {
            case 'GATE_PROPOSAL':
                if ($importanceFactor >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_GPID')
                    );
                }
                break;
            case 'GATE_0':
                $parent = $this->getParentModuleIDFamily(true);
                if (($parent !== $this) && (10000 === (int)$parent->getImportanceFactor())) {
                    $approvers = array_merge(
                        $approvers,
                        [(new tldUser($this->getProjectLeader()))->itsDetails]
                    );
                }

                if ((int)$this->getImportanceFactor() >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCOO', $erp),
                        tldGroup::inGroup('role_RCEO', $erp),
                        tldGroup::inGroup('role_GPID'),
                    );
                }

                if ((int)$this->getImportanceFactor() >= 1000) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_GCOO'),
                        tldGroup::inGroup('role_GCEO'),
                        tldGroup::inGroup('ROLE_CHAIRMAN'),
                        tldGroup::inGroup('role_GCTO'),

                    );
                }

                if ((int)$this->getImportanceFactor() >= 10000) {
                    foreach ($involvedErps as $involvedErp) {
                        $approvers = array_merge(
                            $approvers,
                            tldGroup::inGroup('role_EM', $involvedErp),
                            tldGroup::inGroup('role_COO', $involvedErp),
                            tldGroup::inGroup('role_RCOO', $involvedErp),
                            tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($involvedErp)),
                            [(new tldUser($this->getProjectLeader()))->itsDetails]
                        );
                    }
                }
                break;

            case 'GATE_1':
                if ($importanceFactor >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCOO', $erp),
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        tldGroup::inGroup('role_GPID'),
                        tldGroup::inGroup('role_GCTO')
                    );
                }
                if ($importanceFactor >= 1000) {
                    $approvers = array_merge(
                        $approvers,
                        $this->getGCOO(),
                        $this->getGCEO(),
                        $this->getACOO()
                    );
                }
                if ($importanceFactor >= 10000) {
                    $approvers = array_merge($approvers, $involvedApprovers);
                }
                break;
            case 'GATE_2':
                if ($importanceFactor >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCOO', $erp),
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        tldGroup::inGroup('role_GCTO')
                    );
                }
                if ($importanceFactor >= 1000) {
                    $approvers = array_merge(
                        $approvers,
                        $this->getGCOO(),
                        $this->getGCEO()
                    );
                }
                if ($importanceFactor >= 10000) {
                    $approvers = array_merge($approvers, $involvedApprovers);
                }
                break;
            case 'GATE_3':
                if ($importanceFactor >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCOO', $erp),
                        tldGroup::inGroup('role_GCTO')
                    );
                }
                if ($importanceFactor >= 1000) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp))
                    );
                }
                if ($importanceFactor >= 10000) {
                    $approvers = array_merge($approvers, $involvedApprovers);
                }
                break;
            case 'GATE_4':
                if ($importanceFactor >= 100) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCOO', $erp),
                        tldGroup::inGroup('role_GPID'),
                        tldGroup::inGroup('role_GCTO')
                    );
                }
                if ($importanceFactor >= 1000) {
                    $approvers = array_merge(
                        $approvers,
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp))
                    );
                }
                if ($importanceFactor >= 10000) {
                    $approvers = array_merge(
                        $approvers,
                        $involvedApprovers,
                        $this->getGCOO()
                    );

                }
                break;
        }

        return $approvers;
    }
    /**
     * @param string $status
     *
     * @return array
     */
    public function getNotificationMembers($status) {
        $erp = $this->getERP();
        $importanceFactor = (int) $this->getImportanceFactor();
        $notified = [];
        switch ($status) {
            case 'GATE_PROPOSAL':
                $notified = tldGroup::inGroup('role_RCOO', $erp);
                if ($importanceFactor >= 100) {
                    $notified = array_merge(
                        $notified,
                        tldGroup::inGroup('role_RCOO', $erp),
                        [(new tldUser($this->itsHeader['poster']))->itsDetails],
                        tldGroup::inGroup('role_GCTO'),
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        $this->getGCOO()
                    );
                }
                if ($importanceFactor >= 1000) {
                    $notified = array_merge(
                        $notified,
                        $this->getGCEO(),
                        $this->getACOO()
                    );
                }
                return $notified;
            case 'GATE_0':
                if (10 === $importanceFactor) {
                    return tldGroup::inGroup('role_RCOO', $erp);
                }
                if (100 === $importanceFactor) {
                    return $this->getGCOO();
                }

                return array_merge(
                    tldGroup::inGroup('role_CFO', tldLocation::getFactorySSO($erp)),
                    tldGroup::inGroup('role_FC', 0), // Anne Claire BREGERON
                    tldGroup::inGroup('role_GCFO')
                );
            case 'GATE_1':
                if (10 === $importanceFactor) {
                    return tldGroup::inGroup('role_RCOO', $erp);
                }
                if (100 === $importanceFactor) {
                    return $this->getGCOO();
                }
                break;
            case 'GATE_2':
                if (10 === $importanceFactor) {
                    return tldGroup::inGroup('role_RCOO', $erp);
                }

                if (100 === $importanceFactor) {
                    return array_merge(
                        tldGroup::inGroup('role_GPID'),
                        $this->getGCOO()
                    );
                }

                return array_merge(
                    tldGroup::inGroup('role_GPID'),
                    $this->getACOO()
                );
            case 'GATE_3':
                if (10 === $importanceFactor) {
                    return array_merge(
                        tldGroup::inGroup('role_QAM', $erp),
                        tldGroup::inGroup('role_RCOO', $erp)
                    );
                }

                if (100 === $importanceFactor) {
                   return array_merge(
                       tldGroup::inGroup('role_QAM', $erp),
                       tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        tldGroup::inGroup('role_GPID'),
                        $this->getGCOO()
                    );
                }

                return array_merge(
                    tldGroup::inGroup('role_QAM', $erp),
                    tldGroup::inGroup('role_GPID'),
                    $this->getGCOO(),
                    $this->getGCEO(),
                    $this->getACOO()
                );
            case 'GATE_4':
                if (10 === $importanceFactor) {
                    return array_merge(
                        tldGroup::inGroup('role_QAM', $erp),
                        tldGroup::inGroup('role_RCOO', $erp)
                    );
                }

                if (100 === $importanceFactor) {
                   return array_merge(
                       tldGroup::inGroup('role_QAM', $erp),
                       tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        $this->getGCOO()
                    );
                }

                if (1000 === $importanceFactor) {
                    return array_merge(
                        tldGroup::inGroup('role_QAM', $erp),
                        tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                        $this->getGCOO(),
                        $this->getGCEO(),
                        $this->getACOO()
                    );
                }

                return array_merge(
                    tldGroup::inGroup('role_QAM', $erp),
                    tldGroup::inGroup('role_RCEO', tldLocation::getFactorySSO($erp)),
                    $this->getGCEO(),
                    $this->getACOO()
                );
        }

        return $notified;
    }

    public function notifyGate($message, $subject = null)
    {
        $status = $this->getStatus();
        $to = array_unique(array_column($this->getNotificationMembers($status), 'email'));
        if (null === $subject) {
            $subject = "Master EAP#{$this->itsID} updated to $status";
        }
        return tldUtils::emailAttachment($to, 'noreply@tld-gse.com', $subject, nl2br($message));
    }
    public function runBPClosingAction($cancel = false)
    {
        $status = $gate = $this->getStatus();
        $status = explode('_', $status);
        if ($status[0] !== 'GATE') {
            return;
        }

        if ($status[1] === 'PROPOSAL') {
            $nextStatus = $cancel ? 'PROPOSAL' : 'PHASE_0';
        }
        elseif ($status[1] === '4'){
            $nextStatus = $cancel ? 'PHASE_4' : 'CLOSED';
        }
        else {
            $nextStatus = sprintf('PHASE_%s', $cancel ? $status[1] : $status[1] + 1);
        }

        switch ($status[1]) {
            case 'PROPOSAL':
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
                $this->changeStatus($nextStatus, $cancel);
                $meapInfo = $this->getHeader();
                $parent_id = $meapInfo['id'];
                $module = 'MEAP';
                $action = $cancel ? 'rejected' : 'approved';
                //setup the description
                $taskDesc = "All BP has been $action in GATE_{$status[1]}, please take action in $nextStatus from MEAP#$parent_id";

                $vals = [
                    'module' => $module,
                    'assignor' => $meapInfo['proj_leader'],
                    'assignee' => $meapInfo['proj_leader'],
                    'task' => $taskDesc,
                    'bu_id' => $meapInfo['factory'],
                    'escalation_trigger' => '30',
                ];
                // setup the due date depending on the leadtime
                $vals['due_date'] = ['value' => '0', 'unit' => 'DAY'];

                //insert new task
                $taskId = tldTask::insert($parent_id, tldUtils::cleanupFormInput($vals), $module);
                if (!is_numeric($taskId)) {
                    error_log("Could not create new task. There was an error processing. The error returned is '$taskId'");
                    return;
                }

                $task = new tldTask($taskId);

                // built the email notification for the newly created task
                $assignee = new tldUser($task->getAssignee());
                $assignor = new tldUser($task->getAssignor());
                $message = <<<EOF
                            Task #$taskId has been assigned to {$assignee->getFullname()}.\n<br>
    
                            Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
                            <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskId">
                            Click here to go to Task.
                            </a>
                            <br>
                            Task:<br>
EOF;
                $message .= $task->getTask();
                $subject = "Tasks, New: #$taskId opened for {$assignee->getFullname()} by {$assignor->getFullname()}";
                $task->notifyAssignee($message, $subject);
                break;
        }

        $this->addLog(0, sprintf('MEAP status automatically moved%s to %s', $cancel ? ' back' : '', $this->getStatus()));
    }

    public function getTasks($mode = '')
    {
        return tldTask::byParent($this->itsID, 'MEAP', $mode);
    }

    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'MEAP');
    }

    public function getFiles($level = 0)
    {
        return tldModFile::byParent($this->itsID, 'MEAP', $level);
    }

    public function getDTCFiles(): array
    {
        return tldModFile::byParent($this->itsID, 'MEAP', 1);
    }

    public function getGoalSheetFiles(): array
    {
        return tldModFile::byParent($this->itsID, 'MEAP', 2);
    }

    public function getPlanningFiles(): array
    {
        return tldModFile::byParent($this->itsID, 'MEAP', 3);
    }

    public function getFactory()
    {
        return $this->itsHeader['factory'];
    }

    public function getERP()
    {
        return $this->itsHeader['factory_erp'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getParentModule()
    {
        return $this->itsHeader['parent_module'];
    }

    public function getImportanceFactor()
    {
        return $this->itsHeader['ifactor'];
    }

    public function getModule()
    {
        return 'MEAP';
    }

    public function addChild($cid, $module)
    {
        if (empty($cid) || empty($this->itsID)) {
            return 'Child# invalid or not object context';
        }
        $table = ($module === 'MEAP') ? 'meap' : 'eap';
        $query = <<<EOF
        UPDATE {$table}
        SET parent_id={$this->itsID}, parent_module='{$this->getModule()}'
        WHERE id={$cid}
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function deleteChild($cid, $module)
    {
        if (empty($cid) || empty($this->itsID)) {
            return 'Child# invalid or not object context';
        }
        $table = ($module === 'MEAP') ? 'meap' : 'eap';
        $query = <<<EOF
        UPDATE {$table}
        SET parent_id=0, parent_module=''
        WHERE id={$cid}
        AND parent_id={$this->itsID}
        AND parent_module='{$this->getModule()}'
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function getChildList()
    {
        if (empty($this->itsID)) {
            return [];
        }

        $eap = tldEAP::byConstraints(['eap.parent_id' => $this->itsID, 'parent_module' => 'MEAP']);
        $meap = self::byConstraints(['meap.parent_id' => $this->itsID, 'parent_module' =>  'MEAP']);

        return array_merge($eap, $meap);
    }

    public function getChildrenAndGrandchildrenList()
    {
        if (empty($this->itsID)) {
            return [];
        }

        $childrenEaps = $this->getChildList();
        $grandchildrenEapsList = [];
        foreach ($childrenEaps as $childrenEap) {
            $grandchildrenEaps = (tldEAP::byConstraints(['eap.parent_id' => $childrenEap['id'], 'parent_module' => 'EAP']) );
            foreach ($grandchildrenEaps as $grandchildrenEap){
                $grandchildrenEapsList[] = $grandchildrenEap;
            }
        }

        return array_merge($childrenEaps, $grandchildrenEapsList);
    }

    public static function getOpenMEAPListByFactory($factory)
    {
        if (empty($factory)) {
            return 'Factory is missing';
        }
        $meaps = self::byConstraints("meap.factory=$factory AND meap.status <>'CLOSED'");
        $data = [];
        foreach ($meaps as $meap) {
            $data[$meap['id']] = "MEAP# {$meap['id']} - {$meap['short_desc']}";
        }
        return $data;
    }

    /**
     * @param $result
     * @param int $level
     * @param bool $hideChildMEAPDetail
     * @return void
     */
    public function getChildListRec(&$result, $level = 0, $hideChildMEAPDetail = false)
    {
        if (empty($this->itsID)) {
            throw new DomainException('Not object context');
        }
        tldEAP::getChildListRecursive($result, $level, $hideChildMEAPDetail, [$this->itsID], [], $this->itsID);
    }

    /**
     * @param bool $infantsOnly Flag to indicate if we want the to fetch the complete tree or only the infants
     * @param bool $hideChildMEAPDetail Flag to indicate if we should include MEAP infants and their child in the tree
     *
     * @return array
     */
    public function getFamilyTree($infantsOnly, $hideChildMEAPDetail = false)
    {
        if (empty($this->itsID)) {
            throw new DomainException('Not object context');
        }

        $topParent = $infantsOnly ? $this : $this->getParentModuleIDFamily(true);
        $topParentModule = $topParent->getModule();
        $topParentId = $topParent->getID();
        $result[] = ['id' => $topParentId, 'module' => $topParentModule, 'parent_id' => '0', 'parent_module' => '', 'level' => 0, 'data' => $topParent->itsHeader];

        $meaps = 'MEAP' === $topParentModule ? [$topParentId] : [];
        $eaps = 'EAP' === $topParentModule ? [$topParentId] : [];

        tldEAP::getChildListRecursive($result, 0, $hideChildMEAPDetail, $meaps, $eaps, $this->itsID);

        return $result;
    }

    public function getParentModuleIDFamily($returnObject = false)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }

        if (0 === (int)($parentId = $this->getParentID())) {
            return ($returnObject) ? $this : ['module' => $this->getModule(), 'id' => $this->getID()];
        }
        // Make this method a one-time use - save overhead
        if (null !== $this->familyParentModule) {
            return ($returnObject) ? $this->familyParentModule : ['module' => $this->familyParentModule->getModule(), 'id' => $this->familyParentModule->getID()];
        }
        // Get first parent
        $parentModule = $this->getParentModule();
        $prev = $this;
        // if first parent found, loop and search parent until we found the last one
        while ($parentId > 0) {
            $this->familyParentModule = ($parentModule === 'MEAP') ? new tldMEAP($parentId) : new tldEAP($parentId);
            if ($this->familyParentModule->isEmpty()) {
                $this->familyParentModule = $prev;
                break;
            }
            $parentId = $this->familyParentModule->getParentID();
            $parentModule = $this->familyParentModule->getParentModule();
            $prev = $this->familyParentModule;
        }
        return ($returnObject) ? $this->familyParentModule : ['module' => $this->familyParentModule->getModule(), 'id' => $this->familyParentModule->getID()];
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'MEAP');
    }

    public function insertPCD($type, $description)
    {
        $query = <<<EOF
    	INSERT INTO meap_pcd
    	SET parent_id={$this->itsID}, type='{$type}', description='{$description}'
EOF;
        return tldUtils::sqlInsert($query);
    }

    public function createChildsEAP()
    {
        $eap = [
            'poster' => $this->itsHeader['poster'],
            'short_desc' => '',
            'description' => '',
            'category' => 'Engineering Development',
            'ifactor' => '10+',
            'factory' => $this->itsHeader['factory'],
            'pvt' => $this->itsHeader['pvt'],
            'models' => [$this->itsHeader['model']],
            // members are not copies on purpose
        ];
        foreach (range(0, 4) as $phase) {
            $eap['short_desc'] = "{$this->itsHeader['short_desc']} - Phase $phase";
            $result = tldEAP::insert($eap);
            $this->addChild($result, 'EAP');
            if ($phase === 2) {
                $subEap = $eap;
                $subEap['short_desc'] = "LINK System Installation";
                $subEap['description'] = <<<EOF
    Electric drawing installation and DBC creation
    Factory Acceptance Test
    The LINK system should be installed on the 1st prototype and the x followers unit (the x to be defined with the COO). this is on TOP of DMS#4739.        
EOF;
                $res = tldEAP::insert($subEap);
                (new tldEAP($result))->addChild($res, 'EAP');
            }
        }
    }

    public function setPCDDate($pcd_id, $target, $date)
    {
        $setdate = (empty($date)) ? 'NULL' : "'$date'";
        switch ($target) {
            case 'original':
                $set = "original_target={$setdate}";
                break;
            case 'management':
                $set = "management_target={$setdate}";
                break;
            case 'current':
                $set = "current_target={$setdate}";
                break;
            case 'actual':
                $set = "actual_date={$setdate}";
                break;
            default:
                return 'Not a valid target type';
                break;
        }
        $query = <<<EOF
    	UPDATE meap_pcd
    	SET {$set}
    	WHERE id=$pcd_id AND parent_id={$this->itsID}
EOF;
        return tldUtils::sqlQuery($query);
    }

    /**
     * @deprecated
     */
    public function PCD($id = null, $type = null)
    {
        return $this->getPCD($id, $type);
    }

    /**
     * @param null|int|string $id
     * @param null|string $type The type of event
     * @return array
     */
    public function getPCD($id = null, $type = null)
    {
        $id = ($id !== null) ? (int)$id : $this->itsID;
        $WHERE = "WHERE parent_id={$id}";
        if (null !== $type) {
            $WHERE .= " AND type='$type' ";
        }
        $query = <<<EOF
    	SELECT *
    	FROM meap_pcd
    	$WHERE
    	ORDER BY id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function isPCDComplete()
    {
        foreach ($this->getPCD() as $pcd) {
            if (empty($pcd['original_target']) || '0000-00-00' === $pcd['original_target']) {
                return false;
            }
        }

        return true;
    }

    public function isReadyForGate0(): bool
    {
        if ((int) $this->getImportanceFactor() >= 100 && empty($this->getDTCFiles()) && empty($this->getGoalSheetFiles()) && empty($this->getPlanningFiles())) {
            return false;
        }

        return $this->isPCDComplete() && $this->isEconComplete() && empty($this->getBPS());
    }

    public function setEcon($type, $field, $value)
    {
        if (!in_array($field, ['dh', 'sea', 'maoc', 'pmc', 'plh', 'material', 'hours'], true)) {
            return 'Not a valid field';
        }
        $value = ($value == '') ? 'NULL' : "'$value'";
        switch ($type) {
            case 'actual':
                $set = "econ_actual_{$field}={$value}, econ_actual_{$field}_dt=NOW()";
                break;
            case 'eac':
                $set = "econ_eac_{$field}={$value}";
                break;
            case 'target':
                $set = "econ_target_{$field}={$value}";
                break;
            default:
                return 'Not a valid type';
        }
        $query = <<<EOF
    	UPDATE meap
    	SET {$set}
    	WHERE id={$this->itsID}
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function isEconComplete()
    {
        if (trim($this->itsHeader['econ_target_dh']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_sea']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_maoc']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_pmc']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_plh']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_material']) === '') {
            return false;
        }
        if (trim($this->itsHeader['econ_target_hours']) === '') {
            return false;
        }

        return true;
    }

    public function getTaskSummary()
    {
        $parent = $this->getFamilyTree(true, true);
        foreach ($parent AS $link) {
            if ($link['module'] === 'EAP') {
                $ids[] = $link['id'];
            }
        }
        if (empty($ids)) {
            return [];
        }
        $ids = implode(',', array_unique($ids));

        $inClause = implode(',', tldTimekeeping::getErpUsingActualHours());

        $query = <<<SQL
        SELECT
            eap.id AS eap_id,
            eap.status AS eap_status,
            eap.short_desc AS eap_desc,
            tasks.id as task_id,
            DATE_FORMAT(tasks.date, '%Y-%m-%d') as task_dt_open,
            tasks.status as task_status,
            tasks.task as task_desc,
            tasks.hours as est_time,
            tasks.due_date as due_date,
            (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
            WHERE people.id=tasks.assignor) AS assignor_fullname,
            (SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
            WHERE people.id=tasks.assignee) AS assignee_fullname,
            locations.location as factory,
            locations.erp as erp,
            (
                SELECT
                IF(timekeeping.location IN ($inClause), ROUND(SUM(timekeeping.hours_actual),1), ROUND(SUM(timekeeping.time_actual)*8/100,1))
                FROM timekeeping
                WHERE timekeeping.module_id = tasks.id AND timekeeping.link = 'Task' AND timekeeping.tld_dpt = 'ENG'
                GROUP BY timekeeping.module_id
            ) as timekeeping
        FROM eap
        LEFT JOIN tasks ON tasks.parent_id = eap.id AND tasks.module = 'EAP'
        LEFT JOIN locations ON eap.factory=locations.id
        WHERE eap.id IN($ids)
        ORDER BY eap_id, task_status, task_id
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getEAPSummary()
    {
        $ids = [];
        $parent = $this->getFamilyTree(true, true);
        foreach ($parent AS $link) {
            if ($link['module'] === 'EAP') {
                $ids[] = $link['id'];
            }
        }
        if (empty($ids)) {
            return [];
        }
        $eapId = $ids;
        $ids = implode(',', array_unique($ids));

        $query = <<<SQL
    	SELECT eap.*,
			(SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
			WHERE people.id=eap.poster) AS poster_fullname,
			(SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
			WHERE people.id=eap.reporter) AS reporter_fullname,
			(SELECT CONCAT(people.lastname,', ',people.firstname) FROM people
			WHERE people.id=eap.assignee) AS assignee_fullname,
			locations.location,
			locations.erp,
			IF(eap.assignee=0, 'N', 'Y') AS overnight,
    		(SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' ) AS tasks_all,
    		(SELECT SUM(tasks.hours) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' ) AS total_est_hours,
    		(SELECT SUM(tasks.hours) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status='OPEN' ) AS total_open_est_hours,
    		(SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status<>'CLOSED') AS tasks_open,
    		(SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.status='CLOSED') AS tasks_closed,
    		(SELECT COUNT(*) FROM tasks WHERE tasks.parent_id=eap.id AND tasks.module='EAP' AND tasks.due_date<CURDATE() AND tasks.status<>'CLOSED') AS tasks_late,
    		(SELECT COUNT(*) FROM cal_bp WHERE cal_bp.parent_id=eap.id AND cal_bp.module='EAP' AND cal_bp.status<>'CLOSED') AS bp_open,
    		(SELECT COUNT(*) FROM cal_bp WHERE cal_bp.parent_id=eap.id AND cal_bp.module='EAP' AND cal_bp.status='CLOSED') AS bp_closed,
        		CASE eap.status
                WHEN 'PENDING' THEN 10
                WHEN 'IN PROGRESS' THEN 100
                WHEN 'PROPOSED' THEN 1000
                WHEN 'NOTIFICATION' THEN 10000
                WHEN 'IN QUEUE' THEN 100000
                WHEN 'CLOSED' THEN 1000000
                WHEN 'REJECTED' THEN 10000000
                ELSE 100000000
    		END AS status_order
    	FROM eap
    	LEFT JOIN locations ON eap.factory=locations.id
    	WHERE eap.id IN ($ids)
    	ORDER BY status_order, location, ifactor DESC, dt_opened, id
SQL;
        $rows = tldUtils::getSqlToAssocArray($query);
        $hoursByEAP = tldTimekeeping::getActualHoursByEAP($eapId);
        foreach ($rows as &$row) {
            $row['total_hours_actual'] = $hoursByEAP[$row['id']] ?? null;
        }
        return $rows;
    }

    public static function countByFactoryStatus($status = '')
    {
        $WHERE = $status === 'OPENED' ? " WHERE meap.status NOT IN ('CLOSED' , 'REJECTED') " : '';

        $query = <<<SQL
SELECT status, location, COUNT(distinct id) as num
FROM (
        SELECT meap.id, meap.status, meapl.location as location
        FROM meap
        INNER JOIN locations meapl ON meap.factory=meapl.id
        $WHERE
    UNION ALL
        SELECT meap.id, meap.status, l.location as location
        FROM meap
        INNER JOIN meap_factories mf on meap.id = mf.parent_id
        INNER JOIN locations l on mf.factory_id = l.id
        $WHERE
) as temp
GROUP BY location, status
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byERPStatus($erp = '', $status = '')
    {
        $where = [];
        if ($erp !== 'ALL') {
            $where[] = " locations.location='$erp' ";
        }
        if ($status !== 'ALL') {
            $where[] = " meaps.status='$status' ";
        }
        $WHERE = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $query = <<<EOF
        SELECT meaps.*,
            locations.location,
        	ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0) AS dfactor
        FROM meap AS meaps LEFT JOIN locations ON meaps.factory=locations.id
        $WHERE
        ORDER BY ifactor DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search MEAPS for string
     *
     * Only searches in particular fields
     *
     * @param array $data
     * @return array
     */
    public static function search(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        $targetFields = ['product_type', 'model', 'short_desc', 'description'];
        $WHERE = [];
        $keywords = explode(' ', $data['target']);
        foreach ($keywords AS $keyword) {
            if ($keyword === '') {
                continue;
            }
            $group = [];
            foreach ($targetFields AS $field) {
                $group[$field] = "%$keyword%";
            }
            $WHERE[] = '(' . tldUtils::constructWhere($group, 'OR') . ')';
        }
        foreach (['status', 'location', 'ifactor'] as $field) {
            if ('ALL' !== $data[$field]) {
                $WHERE[] = "$field = '{$data[$field]}'";
            }
        }
        if (!empty($data['date_closed_until'])) {
            $WHERE[] = "'{$data['date_closed_until']}' >= date_closed";
        }
        if (!empty($data['date_closed_from'])) {
            $WHERE[] = "'{$data['date_closed_from']}' <= date_closed";
        }
        $WHERE = implode(' AND ', $WHERE);

        $sortBy = !empty($data['sort']) ? "ORDER BY {$data['sort']}" : 'ORDER BY ifactor DESC';
        // Make query
        $query = <<<EOF
        SELECT meaps.*,
            locations.location,
        	ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0) AS dfactor
        FROM meap AS meaps LEFT JOIN locations ON meaps.factory=locations.id
        WHERE $WHERE
        $sortBy
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getProjectLeader()
    {
        return $this->itsHeader['proj_leader'];
    }

    /**
     * @deprecated
     */
    public function getPL()
    {
        return $this->getProjectLeader();
    }

    public function addLog($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'MEAP';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    /**
     * Get MEAP by constraints
     * WARNING: DO NOT DELETE, SHOULD BE THE GENERIC ONE TO USE
     * @param mixed array or string
     * @return array
     */
    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $WHERE = '';
        if (!empty($a)) {
            $WHERE = " WHERE $a";
        }

        $query = <<<EOF
        SELECT meap.*,
        	'MEAP' AS module,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=meap.poster
            ) AS poster_fullname,
            CONCAT(init.lastname,', ', init.firstname) AS proj_leader_fullname,
            init.email AS proj_leader_email,
            erp.location AS factory_fullname,
            erp.erp AS factory_erp
        FROM meap
            LEFT JOIN people AS init ON meap.proj_leader=init.id
            LEFT JOIN locations AS erp ON meap.factory=erp.id
        $WHERE
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     *@return bool
     */
    public function isPrivate()
    {
        return $this->itsHeader['pvt'] === 'Y';
    }

    public function setPrivate($isPrivate)
    {
        $pvt = ($isPrivate) ? 'Y' : 'N';
        $query = <<<EOF
			UPDATE meap
			SET pvt='{$pvt}'
			WHERE id={$this->itsID}
EOF;
        $e = tldUtils::sqlQuery($query);
        $this->refresh();
        return $e;
    }

    public function getMembers()
    {
        $members = tldModMember::byParent($this->itsID, 'MEAP');
        usort($members, static function ($a, $b) {
            return strcmp($a['lastname'], $b['lastname']);
        });

        return $members;
    }

    public function isMember($uid)
    {
        if ((int)$this->getProjectLeader() === (int)$uid) {
            return true;
        }
        return tldModMember::isMember('MEAP', $this->itsID, $uid);
    }

    public function addMember($members)
    {
        if (is_array($members)) {
            foreach ($members AS $member) {
                tldModMember::insert('MEAP', $this->itsID, $member);
            }
        } elseif (!empty($members)) {
            tldModMember::insert('MEAP', $this->itsID, $members);
        }
    }

    public function getMembersList($field = 'email')
    {
        $list = $this->getMembers();
        $members = [];
        foreach ($list AS $member) {
            $members[] = $member[$field];
        }
        return array_unique($members);
    }

    public function getCCList()
    {
        return $this->getMembersList();
    }

    public function refreshActualDevHours()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }

        $hours = tldTimekeeping::getMEAPHours('All', 0, [], 'ALL', '2013-01-01', (new \DateTime('tomorrow'))->format('Y-m-d'), $this->itsID);

        if (empty($hours)) {
            return 'No timesheets';
        }
        $hours = current($hours);
        $hours = (int)$hours['hours_actual'];
        $this->setEcon('actual', 'dh', $hours);
        return $hours;
    }

    public function getFirstLevelMEAPChildren()
    {
        if  (10000 !== (int) $this->getImportanceFactor()) {
            return [];
        }

        return self::byConstraints(['meap.parent_id' => $this->itsID, 'parent_module' =>  'MEAP']);
    }
    /**
     * @return array
     */
    public function getInfantEconomicsDashboard()
    {
        if (!$children = $this->getFirstLevelMEAPChildren()) {
            return  [];
        }
        $ids = implode(',', array_column($children, 'id'));

        // Get next Key event (based on management_target for each MEAP
        $query = <<<SQL
SELECT meap_pcd.*
FROM meap_pcd
LEFT JOIN meap_pcd AS pcd ON meap_pcd.parent_id = pcd.parent_id AND pcd.management_target < meap_pcd.management_target AND meap_pcd.type=pcd.type AND pcd.actual_date IS NULL
WHERE meap_pcd.type='KEY EVENT' AND meap_pcd.actual_date IS NULL AND pcd.id IS NULL AND meap_pcd.parent_id IN ($ids)
GROUP BY meap_pcd.parent_id
SQL;
        $keyEvents = [];
        foreach(tldUtils::getSqlToAssocArray($query) as $keyEvent) {
            $keyEvents[$keyEvent['parent_id']] = $keyEvent;
        }

        foreach($children as &$child) {
            $child['key_event_management_target'] = isset($keyEvents[$child['id']]) ? $keyEvents[$child['id']]['management_target'] : null;
            $child['key_event_description'] = isset($keyEvents[$child['id']]) ? $keyEvents[$child['id']]['description'] : null;
        }

        return $children;
    }

    /**
     * @return array
     */
    public function getInvolvedFactories()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }

        $query = <<<SQL
    	SELECT locations.*
    	FROM meap_factories
    	LEFT JOIN locations ON locations.id=meap_factories.factory_id
    	WHERE meap_factories.parent_id={$this->itsID}
    	ORDER BY id
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param int $locationId
     */
    public function insertInvolvedFactory(/* int */ $locationId)
    {
        if (!is_int($locationId)) {
            return 'Invalid location Id';
        }

        if (empty($this->itsID)) {
            return 'Not object context';
        }

        $query = <<<EOF
    	INSERT INTO meap_factories
    	SET parent_id={$this->itsID}, factory_id=$locationId
EOF;
        return tldUtils::sqlInsert($query);
    }

    /**
     * @param int $locationId
     */
    public function deleteInvolvedFactory($locationId)
    {
        if (!is_int($locationId)) {
            return 'Invalid location Id';
        }

        if (empty($this->itsID)) {
            return 'Not object context';
        }

        $query = <<<EOF
    	DELETE FROM meap_factories
    	WHERE parent_id={$this->itsID} AND factory_id=$locationId
    	LIMIT 1
EOF;
        return tldUtils::sqlInsert($query);
    }

    public static function getTypes(): array
    {
        return [
            'ADMIN', 'CDEV', 'CRED', 'CUST', 'IMPR', 'UPDA',
        ];
    }

    public static function getPurposes(): array
    {
        return [
            'New Product', 'New System', 'New feature', 'Evolution of existing',
        ];
    }

    public static function getCostsCalculationMethods(): array
    {
        return [
            'Non Applicable (non E-product)', 'Batteries are included in cost', 'Batteries are not included in cost',
        ];
    }

    public static function getOTE($factory)
    {
        $WHERE = '';
        if ('ALL' !== $factory) {
            $WHERE = " AND meap.factory=$factory ";
        }

        $query = <<<SQL
SELECT
    COUNT(DISTINCT meap_pcd.id) AS total,
    SUM(IF(actual_date IS NULL, 0, DATEDIFF(actual_date, original_target) <= 15)) AS onTime,
    ROUND(SUM(IF(actual_date IS NULL, 0, DATEDIFF(actual_date, original_target) <= 15))/COUNT(DISTINCT meap_pcd.id)*100, 1) AS yval,
    DATE_FORMAT(meap_pcd.original_target,'%Y-%m') AS xval,
    locations.location,
    GROUP_CONCAT(DISTINCT
                 CONCAT(
                         'MEAP#', meap.id, ' - ',
                         meap_pcd.description,
                         ' : ',
                         IF(DATEDIFF(actual_date, original_target) <= 15, 'OK', 'NOK'),
                         ''
        )
                 ORDER BY meap_pcd.id ASC SEPARATOR '<br>') AS details
FROM meap_pcd
INNER JOIN meap ON meap_pcd.parent_id=meap.id
INNER JOIN locations ON meap.factory=locations.id
WHERE
    meap_pcd.original_target IS NOT NULL
    AND meap_pcd.type = 'MEAP PHASE'
    AND meap.ifactor > 10
    AND PERIOD_DIFF(DATE_FORMAT(CURRENT_DATE,'%Y%m'),DATE_FORMAT(meap_pcd.original_target,'%Y%m')) BETWEEN 0 AND 12
    AND meap_pcd.original_target < CURRENT_DATE
    AND (meap.date_closed IS NULL OR meap.date_closed >= meap_pcd.original_target)
    $WHERE
GROUP BY xval, locations.location
ORDER BY locations.location, xval
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getOnBudgetProgramDelivery($factory)
    {
        $WHERE = '';
        if ('ALL' !== $factory) {
            $WHERE = " AND meap.factory=$factory ";
        }

        $query = <<<SQL
SELECT
    meap.id,
    meap.ifactor,
    meap.status,
    meap.short_desc,
    meap.econ_target_dh,
    IF(status = 'CLOSED', COALESCE(meap.econ_actual_dh, 0), COALESCE(meap.econ_eac_dh, 0)) AS econ_actual_dh,
    locations.location
FROM meap
INNER JOIN locations ON meap.factory=locations.id
WHERE ifactor IN (100, 1000, 10000)
AND (meap.date_closed IS NULL OR PERIOD_DIFF(DATE_FORMAT(CURRENT_DATE,'%Y%m'),DATE_FORMAT(meap.date_closed,'%Y%m')) BETWEEN 0 AND 3)
AND econ_target_dh IS NOT NULL
AND meap.status NOT IN ('PROPOSAL', 'GATE_PROPOSAL', 'PHASE_0', 'GATE_0', 'SUSPENDED', 'REJECTED')
$WHERE
ORDER BY locations.location
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getAllBPs(array $eapIds, array $meapIds): array {

        $eapIds = implode(',', $eapIds);
        $meapIds = implode(',', $meapIds);

        $query = <<<SQL
        SELECT *
        FROM cal_bp
        WHERE(parent_id IN ($meapIds) AND module = 'MEAP') OR (parent_id IN ($eapIds) AND module = 'EAP')
        ORDER BY id, module
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getEconCapitalized(): string
    {
        if ('S' === $this->itsHeader['econ_capitalized']) {
            return 'Sold Program';
        }

        return $this->itsHeader['econ_capitalized'];
    }
}

class tldPIP
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public static function getFileUploadDirectory(): string
    {
        return $GLOBALS['UPLOADS_PATH'] . '/pip';
    }

    public function getStatusAllowed(): array
    {
        switch ($this->getStatus()) {
            case 'PENDING':
                return ['IN PROGRESS'];
            case 'IN PROGRESS':
                return ['SUSPENDED', 'REJECTED', 'CLOSED'];
                break;
            case 'SUSPENDED':
                return ['IN PROGRESS', 'REJECTED', 'CLOSED'];
        }

        return [];
    }

    public function getHeader()
    {
        $query = <<<EOF
        SELECT pip.*,
            (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=pip.poster
            ) AS poster_fullname,
            CONCAT(init.lastname,', ', init.firstname) AS initiator_fullname,
            init.email AS initiator_email,
            erp.location AS factory_fullname,
            erp.erp AS factory_erp
        FROM pip
            LEFT JOIN people AS init ON pip.initiator=init.id
            LEFT JOIN locations AS erp ON pip.factory=erp.id
        WHERE pip.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function notifyInitiator($message, $subject = '', $cc = '')
    {
        $to = $this->itsHeader['initiator_email'];
        //Get GTD list, should only be one!
        $g = new tldGroup('role_GTD', 900);
        $gtds = $g->getEmailList();
        if (count($gtds)) {
            $gtd_emails = implode(',', $gtds);
            if ($cc) {
                $cc .= ',' . $gtd_emails;
            } else {
                $cc = $gtd_emails;
            }
        }

        if ($this->itsHeader['factory_erp']) {
            //email factory coo
            $g = new tldGroup('role_COO', $this->itsHeader['factory_erp']);
            $coos = $g->getEmailList();
            if (count($coos)) {
                $coo_emails = implode(',', $coos);
                if ($cc) {
                    $cc .= ',' . $coo_emails;
                } else {
                    $cc = $coo_emails;
                }
            }

            //email factory em
            $g = new tldGroup('role_EM', $this->itsHeader['factory_erp']);
            $ems = $g->getEmailList();
            if (count($ems)) {
                $em_emails = implode(',', $ems);
                if ($cc) {
                    $cc .= ',' . $em_emails;
                } else {
                    $cc = $em_emails;
                }
            }
        }

        if (empty($subject)) {
            $subject = 'PIP# ' . $this->itsID . ' updated';
        }
        return tldUtils::emailAttachment($to, 'pip@tld-gse.com', $subject, $message, null, $cc);
    }

    public static function insert($p)
    {
        $query = <<<EOF
        INSERT INTO pip
        SET
            product_type        = '{$p['product_type']}',
            model		= '{$p['model']}',
            date		= now(),
            short_desc		= '{$p['short_desc']}',
            description		= '{$p['description']}',
            picture_filename    = '{$p['picture_filename']}',
            poster		= '{$p['poster']}',
            initiator		= '{$p['initiator']}'
EOF;
        return tldUtils::sqlInsert($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public static function byLatest($num = 10)
    {
        $query = <<<EOF
            SELECT *
            FROM pip
            ORDER BY id DESC
            LIMIT $num
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'PIP', 'ALL');
    }

    public function getLinks()
    {
        return tldModLink::byParent($this->itsID, 'PIP');
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'PIP');
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'PIP');
    }

    public static function countByFactoryStatus()
    {
        $query = <<<EOF
SELECT pips.status,
  erps.location,
  count(*) AS num
FROM pip AS pips
  LEFT JOIN locations AS erps ON pips.factory=erps.id
GROUP BY erps.location, pips.status
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byERPStatus($erp = '', $status = '')
    {
        $where = [];
        if ($erp !== 'ALL' && $erp !== '') {
            $where[] = " locations.location='$erp' ";
        }
        if ($status !== 'ALL') {
            $where[] = " pips.status='$status' ";
        }
        $WHERE = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $query = <<<EOF
        SELECT pips.*,
            locations.location,
        IF(pips.status='SUSPENDED',
                PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m')),
                PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'), DATE_FORMAT(date, '%Y%m'))
                ) AS monthsOpen,
        IF(pips.status='SUSPENDED',
                ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0),
                ROUND(POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
                ) AS dfactor,
        CASE
                WHEN pips.status='CLOSED'
                        THEN final_fweight
                WHEN pips.status='SUSPENDED'
                        THEN ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(date_suspended, INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
                ELSE
                        ROUND(ifactor * POW(1.4678, PERIOD_DIFF(DATE_FORMAT(DATE_SUB(now(), INTERVAL days_suspended DAY), '%Y%m'),DATE_FORMAT(date, '%Y%m'))), 0)
                END
                 AS fweight
        FROM pip AS pips LEFT JOIN locations ON pips.factory=locations.id
        $WHERE
        ORDER BY fweight DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);

    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function changeStatus($status, $opts = '')
    {
        if (in_array($this->getStatus(), ['CLOSED', 'REJECTED'])) {
            return 'ERROR: Cannot change status of a closed or rejected PIP...';
        }
        $SET = '';
        if (empty($this->itsID)) {
            return false;
        }
        //if changing to CLOSED
        switch ($status) {
            case 'IN PROGRESS':
                if (!empty($opts['factory'])) {
                    $SET .= ", factory={$opts['factory']}";
                }
                if (!empty($opts['ifactor'])) {
                    $SET .= ", ifactor={$opts['ifactor']}";
                }
                $SET .= ", date_closed='0000-00-00', date_suspended='0000-00-00'";
                break;
            case 'CLOSED':
            case 'REJECTED':
                $SET = ", date_closed=NOW(), date_suspended='0000-00-00'";
                break;
            case 'SUSPENDED':
                $SET = ", date_closed='0000-00-00', date_suspended=NOW()";
                break;
        }

        $query = <<<EOF
        UPDATE pip
        SET status=UCASE('$status')
        $SET
        WHERE id=$this->itsID
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function getInitiator()
    {
        return $this->itsHeader['initiator'];
    }

    public function addLog($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'PIP';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        return tldModLog::insert($a);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }
}

/**
 * Class to access Production Order or Production Work Order in BAAN
 * @package ENG
 */
class tldWO
{

    public $itsID;
    public $itsERP;
    public $itsHeader;

    public function __construct($erp, $id)
    {
        $this->itsID = $id;
        $this->itsERP = $erp;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
SELECT * FROM ttisfc001$this->itsERP
WHERE t_pdno=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    public function getERP()
    {
        return $this->itsERP;
    }

    public function getCPRJ()
    {
        return $this->itsHeader['t_cprj'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader) ? true : false;
    }


    public function getMaterialList()
    {
        $query = <<<EOF
SELECT mtl.*,itm.t_dsca
FROM tticst001$this->itsERP AS mtl
LEFT JOIN ttiitm001$this->itsERP AS itm ON mtl.t_sitm=itm.t_item
WHERE t_pdno=$this->itsID
ORDER BY t_pdno
EOF;
        return tldUtils::getSqlToAssocArray($query, 'obdc', ['src' => 'baan']);
    }

    public function getMaterialListByERPConstraints($erp, $a, $option = null)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    mtl.*,
    (mtl.t_issu + mtl.t_subd) AS t_tbi,
    itm.t_dsca,
    itm.t_stoc,
    CASE itm.t_bfcp WHEN 1 THEN 'Yes' WHEN 2 THEN 'No' END AS 't_bfcp',
    itm.t_cwar,
    itm.t_csig,
    mtl.t_opno
FROM
    tticst001$erp AS mtl
    LEFT JOIN ttiitm001$erp AS itm ON mtl.t_sitm=itm.t_item
WHERE
    $WHERE
    AND mtl.t_bfls = 2
ORDER BY
    t_pdno, t_sitm
EOF;
        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }
}
