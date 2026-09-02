<?php
/**
 *    Class for accessing files in engineering VAULTs
 *
 * @package PDM
 * @desc All classes related to the PDM are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Class for accessing engineering vault data from all locations
 *
 * @package PDM
 */
class tldVaultController
{
    public $theVaultDetails = [
        220 => [
            'path' => '/mnt/global.released/powervamp.released/',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Kempston',
        ],
        250 => [
            'path' => '/mnt/global.released/aero.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Boise',
        ],
        300 => [
            'path' => '/mnt/windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Windsor',
        ],
        320 => [
            'path' => '/mnt/windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Windsor',
        ],
        400 => [
            'path' => '/mnt/windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Windsor',
        ],
        410 => [
            'path' => '/mnt/windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Windsor',
        ],
        420 => [
            'path' => '/mnt/global.released/sherbrooke.released',
            'uncPath' => 'pluto/vault',
            'folder' => '2',
            'subfolder' => '3',
            'location' => 'Sherbrooke',
        ],
        500 => [
            'path' => '/mnt/global.released/montlouis.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Montlouis',
        ],
        510 => [
            'path' => '/mnt/global.released/montlouis.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Sorigny',
        ],
        520 => [
            'path' => '/mnt/global.released/montlouis.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'St. Lin',
        ],
        540 => [
            'path' => '/mnt/global.released/montlouis.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Montlouis',
        ],
        560 => [
            'path' => '/mnt/global.released/montlouis.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Dubai',
        ],
        570 => [
            'path' => '/mnt/global.released/lebrun.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Lebrun',
        ],
        600 => [
            'path' => '/mnt/hongkong.eng',
            'folder' => '3',
            'subfolder' => '4',
            'location' => 'Hong Kong',
        ],
        620 => [
            'path' => '/mnt/taiwan.eng',
            'folder' => '3',
            'subfolder' => '4',
            'location' => 'Taiwan',
        ],
        640 => [
            'path' => '/mnt/global.released/shanghai.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Shanghai',
        ],
        660 => [
            'path' => '/mnt/global.released/wuxi.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Wuxi',
        ],
        680 => [
            'path' => '/mnt/global.released/shanghai.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'Shanghai',
        ],
        700 => [
            'path' => '/mnt/global.released/shanghai.released',
            'folder' => '2',
            'subfolder' => '4',
            'location' => 'JST',
        ],
    ];
    /**
     * @var array|basicVault[]
     */
    public $theVaultList = [];

    public function __construct()
    {
        //create array of basicVaults for each vault
        foreach ($this->theVaultDetails as $erpID => $params) {
            $this->theVaultList[$erpID] = new basicVault(
                $params['path'],
                $params['uncPath'],
                $params['folder'],
                $params['subfolder'],
                $params['location']
            );
        }
    }

    /**
     * Returns status of each vault in HTML output
     *
     * @return string
     */
    public function checkHTMLVaultStatus()
    {
        $result = '';
        foreach ($this->theVaultList as $erpID => $aVault) {
            $result .= $aVault->getLocation();
            if ($aVault->fileExists('DO_NOT_DELETE')) {
                $result .= '<img src="/shared/bluesphere/16x16/actions/apply.png">';
            } else {
                $result .= '<img src="/shared/bluesphere/16x16/actions/cancel.png">';
            }
            $result .= '<br>';
        }
        return $result;
    }

    /**
     * Returns true if specified filename exists in specified ERP system
     *
     * @return bool
     */
    public function fileExistsInVault($erp, $file)
    {
        if (!empty($this->theVaultList[$erp])) {
            return $this->theVaultList[$erp]->fileExists($file);
        }

        return false;
    }

    /**
     * Outputs latest version of file given by part number and erp system
     *
     * Uses $pn and $erp to determine the latest drawing version in EDM system
     *
     */
    public function outCurrentFileInVaultByERP_PN($erp, $pn)
    {
        $this->outfileInVault($erp, $this->getCurrentFilenameByERP_PN($erp, $pn));
    }

    /**
     * List files that match criteria
     *
     * Searches for $target in the subfolder structure and then the vault root of the ERP system
     * specified by $erp
     *
     * @return array
     */
    public function getSearchList($erp, $target)
    {
        if (empty($this->theVaultList[$erp])) {
            return [];
        }

        return $this->theVaultList[$erp]->getSearchList($target);
    }

    public function getDirFilelist($erp, $folder)
    {
        if (empty($this->theVaultList[$erp])) {
            return [];
        }

        return $this->theVaultList[$erp]->getAllFilenamesInDir_Array($folder);
    }

    /**
     * List files that match criteria
     *
     * Searches for $target in the subfolder structure and then the vault root of the ERP system
     * specified by $erp
     *
     * @return array
     */
    public function getSearchListUNC($erp, $target)
    {
        if (empty($this->theVaultList[$erp])) {
            return [];
        }

        return $this->theVaultList[$erp]->getSearchListUNC($target);

    }

    /**
     * Get all filenames in all vaults, recursively
     *
     * @return array
     */
    public function getAllFilenames_Array()
    {
        $result = [];
        foreach ($this->theVaultList as $erpID => $aVault) {
            $result[] = $aVault->getAllFilenames_Array();
        }

        return array_merge(...$result);
    }

    /**
     * Output file in vault based on erp and filename
     */
    public function outFileInVault($erp, $file)
    {
        $aVault = $this->theVaultList[$erp];
        if (null !== $aVault && $aVault->fileExists($file)) {
            $aVault->outFile($file);
        } else {
            echo "File $file not found";
        }
    }

    /**
     * Get contents of file in vault based on erp and filename
     */
    public function getFileInVault($erp, $file)
    {
        $aVault = $this->theVaultList[$erp];
        if (null !== $aVault && $aVault->fileExists($file)) {
            return $aVault->getFile($file);
        }
    }

    /**
     * Get path to file based on erp and filename
     *
     * @return string|null
     */
    public function getPathToFile($erp, $file)
    {
        if (!$this->isValidERP($erp)) {
            return null;
        }
        $aVault = $this->theVaultList[$erp];
        if (!$aVault->fileExists($file)) {
            return null;
        }

        return $aVault->getPathToFile($file);
    }

    /**
     * Check if given erp is valid
     *
     * @return bool
     */
    public function isValidERP($erp)
    {
        $erps = array_keys($this->theVaultList);

        return in_array((int)$erp, $erps, true);
    }
}

/**
 * Class for accessing photos of parts
 *
 * @package PDM
 */
class tldPhotoController extends tldVaultController
{
    public function __construct()
    {
        //replace the inherited paths with the released paths.
        $this->theVaultDetails[220]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[250]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[300]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[400]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[410]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[420]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[420]['subfolder'] = '4';
        $this->theVaultDetails[500]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[510]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[520]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[540]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[560]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[570]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[600]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[600]['folder'] = '2';
        $this->theVaultDetails[640]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[660]['path'] = '/mnt/grpfps10.parts_pics';
        $this->theVaultDetails[680]['path'] = '/mnt/grpfps10.parts_pics';

        parent::__construct();
    }

    /**
     * Output file in vault based on erp and filename
     */
    public function outFileInVault($erp, $file, $options = [])
    {
        $aVault = $this->theVaultList[$erp];
        if ($aVault->fileExists($file)) {
            $filepath = $aVault->getPathToFile($file);
            switch ($options['width']) {
                case '1024':
                case '512':
                case '256':
                case '128':
                case '64':
                    $new_width = $options['width'];
                    $jpg = new tldFileJPG($filepath);
                    $jpg->outFile('', '', ['width' => $new_width]);
                    break;
                default:
                    $aVault->outFile($file);
            }
        } else {
            readfile("$GLOBALS[SHARED_PATH]/bluesphere/32x32/filesystems/file_broken.png");
        }
    }
}

/**
 * Class for accessing files in engineering RELEASED vaults
 *
 * @package PDM
 */
class tldReleasedController extends tldVaultController
{
    /**
     * Get filename of latest version of drawing based on erp and part number
     *
     * @return string
     */
    public function getCurrentFileRevByERP_PN($erp, $pn)
    {
        $myEDM = new basicEDM($erp);

        return $myEDM->getCurrentRevLevel($pn);
    }

    /**
     * Get revision of drawing based on erp, part number and date
     *
     * @return string
     */
    public function getFileRevByERP_PN_DATE($erp, $pn, $date)
    {
        $myEDM = new basicEDM($erp);

        return $myEDM->getRevByPN_DATE($pn, $date);
    }

    /**
     * Get filename of latest version of drawing based on erp and part number
     *
     * @return string
     */
    public function getCurrentFilenameByERP_PN($erp, $pn)
    {
        $result = '';
        $myEDM = new basicEDM($erp);
        $result = $myEDM->getCurrentFilename($pn);
        //if nothing found in database try the vault directly
        //looking for partnumber.pdf
        if (empty($result) && $this->fileExistsInVault($erp, $pn . '.pdf')) {
            $result = $pn . '.pdf';
        }

        return $result;
    }

    public function getArchiveFilenameByERP_PN($erp, $pn, $date)
    {
        $myEDM = new basicEDM($erp);

        return $myEDM->getFilenameByPNDate($pn, $date);
    }
}

/**
 * Base class for accessing engineering vault data
 *
 * @package PDM
 */
class basicVault
{
    public $itsVaultPath = 'PLS SET VAULT LOCATION';
    public $itsVaultPathUNC = 'PLS SET VAULT UNC LOCATION';
    public $indexOneNum;
    public $indexTwoNum;
    public $itsLocation;

    public function __construct($path, $uncPath, $indexOne, $indexTwo, $location)
    {
        $this->itsVaultPath = $path;
        $this->itsVaultPathUNC = $uncPath;
        $this->indexOneNum = $indexOne;
        $this->indexTwoNum = $indexTwo;
        $this->itsLocation = $location;
    }

    /**
     * List files that match criteria
     *
     * Searches in the subfolder structure and then the vault root
     * returned paths are in UNC format
     *
     * @return array
     */
    public function getSearchListUNC($target)
    {
        $result = [];
        $myFiles = $this->getSearchList($target);
        foreach ($myFiles as $myFile) {
            $result[] = 'file:' .
                '//' . $this->itsVaultPathUNC .
                '/' . $this->getFolder($myFile) .
                '/' . $this->getSubFolder($myFile) .
                '/' . $myFile;
        }

        return $result;
    }

    /**
     * List files that match criteria
     *
     * Searches in the subfolder structure and then the vault root
     * Just returns a list of the files, NO paths
     *
     * @return array
     */
    public function getSearchList($target)
    {
        $result = [];
        $folder = $this->getFolder($target);
        $subFolder = $this->getSubFolder($target);
        //get files in vault
        $vaultFiles = $this->getAllFilenamesInDirREL_Array("$folder/$subFolder");
        //get files in root
        $rootFiles = $this->getAllFilenamesInDirREL_Array('');
        $allFiles = array_merge($vaultFiles, $rootFiles);
        foreach ($allFiles as $file) {
            if (strpos($file, $target) === 0) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Get all filenames in specified relative path to itsVaultPath
     *
     * @return array
     */
    public function getAllFilenamesInDirREL_Array($dir)
    {
        return $this->getAllFilenamesInDir_Array($this->itsVaultPath . '/' . $dir);
    }

    /**
     * Get all filenames only, in a directory
     *
     * $dir is absolute path
     *
     * @return array
     */
    public function getAllFilenamesInDir_Array($dir)
    {
        $result = [];
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..' && !is_dir("$dir/$file")) {
                    $result[] = $file;
                }
            }
            closedir($handle);
        }
        return $result;
    }

    /**
     * Get the names of any directories in the specified folder
     *
     * @return array
     */
    public function getAllDirNameInDir_Array($dir)
    {
        $result = [];
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (($file !== '..') && $file !== '.' && is_dir("$dir/$file")) {
                    $result[] = $file;
                }
            }
            closedir($handle);
        }
        return $result;
    }

    /**
     * Does file actually exist?
     *
     * @return bool
     */
    public function fileExists($file)
    {
        return $this->getPathToFile($file);
    }

    /**
     * Does file exist in the root folder?
     *
     * @return bool
     */
    public function fileExistsInVault($file)
    {
        $myFile = new basicFile($this->getPathToVault($file));

        return $myFile->fileExists();
    }

    /**
     * Does file exist in the vault structure?
     *
     * @return bool
     */
    public function fileExistsInDefault($file)
    {
        $myFile = new basicFile($this->getPathToDefault($file));

        return $myFile->fileExists();
    }

    /**
     * Output the contents of the specified file to stdout
     */
    public function outFile($file)
    {
        $filepath = $this->getPathToFile($file);
        if ($filepath) {
            $myFile = new basicFile($filepath);
            $myFile->outFile('', true);
        }
    }

    /**
     * Get the contents of specified file from vault
     *
     * @return string|false
     */
    public function getFile($file)
    {
        $filepath = $this->getPathToFile($file);
        if ($filepath) {
            $myFile = new basicFile($filepath);
            return $myFile->getFile();
        }
    }

    /**
     * Get location of file in vault
     *
     * First looks in the vault structure, then root
     * returns null if file is not found in either location
     *
     * @return string|null
     */
    public function getPathToFile($file)
    {
        $filePath = [];
        if (false !== strpos($file, '.')) {
            $parts = explode('.', $file);
            $extension = array_pop($parts);
            $filename = implode('.', $parts);

            $filePath[] = $filename.'.'.strtolower($extension);
            $filePath[] = $filename.'.'.strtoupper($extension);
        } else  {
            // Function is also used to get folders
            $filePath = [$file];
        }

        // Check if we are testing on localhost
        if ($_SERVER['SERVER_NAME'] === 'localhost' && $_SERVER['HTTP_HOST'] === 'localhost') {
            $this->itsVaultPath = $_SERVER['DOCUMENT_ROOT'] . '/legacy/uploads';
        }

        foreach($filePath as $f) {
            if ($this->fileExistsInVault($f)) {
                return $this->getPathToVault($f);
            }

            if ($this->fileExistsInDefault($f)) {
                return $this->getPathToDefault($f);
            }
        }

        return null;
    }

    /**
     * Get path to file based on erp, item part number and revision
     *
     * @return string|null
     */
    public function getPathToItemFile(string $item, string $revision, string $extension = 'jpg')
    {
        $rev = str_replace('.', '', $revision);

        $revName = $rev !== 'REL' && !empty($rev) ? "_$rev" : '';
        $filename = trim($item) . $revName . ".$extension";

        return $this->getPathToFile($filename);
    }

    /**
     * Get the path to the given file in the vault using folder and subfolder
     *
     * @return string
     */
    public function getPathToVault($file)
    {
        return $this->itsVaultPath . '/' . $this->getFolder($file) .
            '/' . $this->getSubFolder($file) . '/' . $file;
    }

    /**
     * Get the path to the given file in the root folder
     *
     * @return string
     */
    public function getPathToDefault($file)
    {
        return $this->itsVaultPath . '/' . $file;
    }

    /**
     * Get name of folder based off the given filename
     *
     * Takes the first number of characters of the filename, the number of characters
     * cut is determined by the object property indexOneNum
     *
     * @return string
     */
    public function getFolder($file)
    {
        return substr($file, 0, $this->indexOneNum);
    }

    /**
     * Get name of subfolder based off the given filename
     *
     * Takes the first number of characters of the filename, the number of characters
     * cut is determined by the object property indexTwoNum
     *
     * @return string
     */
    public function getSubFolder($file)
    {
        return substr($file, 0, $this->indexTwoNum);
    }

    /**
     * Get the location name
     *
     * example windsor, sherbrooke, etc
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->itsLocation;
    }
}

/**
 * Base class for accessing Engineering Data Managed information
 *
 * @package PDM
 */
class basicEDM
{
    public $itsERPID;

    public function __construct($erp)
    {
        $this->itsERPID = $erp;
    }

    /**
     * Get EDM data for a particular part number and date
     *
     * @return array
     */
    public function getEdmByDate_Array($pn, $date)
    {
        $erp = (int)$this->itsERPID;
        $edmerp = 400;
        if (in_array($erp, [220, 250], true)) {
            $edmerp = $erp;
        }

        switch ($erp) {
            case 220:
            case 250:
            case 300:
            case 400:
            case 410:
            case 420:
            case 500:
            case 510:
            case 520:
            case 540:
            case 570:
            case 600:
            case 640:
            case 660:
            case 680:
            case 700:
                $query = <<<EOF
SELECT '$erp' AS erp,
    RTRIM(t_eitm) AS t_eitm,
    RTRIM(t_cdrw) AS t_cdrw,
    t_revi,
    t_dsca,
    SUBSTRING(convert(varchar,t_indt,120), 0, 11) AS t_indt,
    SUBSTRING(convert(varchar,t_exdt,120), 0, 11) AS t_exdt
FROM ttiedm100$edmerp
WHERE t_eitm='$pn' AND t_rele=1
AND (('$date' >= t_indt and '$date' < t_exdt) or ('$date' >= t_indt and t_exdt='1753-01-01'))
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                $query = <<<EOF
select * from bomedm
where
    erp='$this->itsERPID' AND eitm = '$pn' AND
    (('$date' >= bindt and '$date' < bexdt) or ('$date' >= bindt and bexdt='0000-00-00'))
EOF;
        }

        return tldUtils::getSqlRowToAssocArray($query, $opt1, $opt2);
    }

    /**
     * Get latest EDM information based on part number
     *
     * @return array
     */
    public function getCurrentEdm($pn)
    {
        return $this->getEdmByDate_Array($pn, date('Y-m-d'));
    }

    /**
     * Get the current revision level for a particular part number
     *
     * @return string
     */
    public function getCurrentRevLevel($pn)
    {
        $firstRow = $this->getCurrentEdm($pn);

        return $firstRow['t_revi'];
    }

    /**
     * Get the revision level of a drawing based on part number and date
     *
     * @return string
     */
    public function getRevByPN_DATE($pn, $date)
    {
        $firstRow = $this->getEdmByDate_Array($pn, $date);

        return $firstRow['t_revi'];
    }

    /**
     * Get the filename of the latest drawing for a part number
     *
     * @return string
     */
    public function getCurrentFilename($pn)
    {
        $firstRow = $this->getCurrentEdm($pn);

        return $firstRow['t_cdrw'];
    }

    /**
     * Get the filename for a part number and particular date
     *
     * @return string
     */
    public function getFilenameByPNDate($pn, $date)
    {

        $firstRow = $this->getEdmByDate_Array($pn, $date);

        return $firstRow['t_cdrw'];
    }

    /**
     * Get all EDM data for pn in current ERP system
     *
     * @return array
     */
    public function getAllEdmByErpAndPn_Array($pn)
    {
        $erp = (int)$this->itsERPID;
        $opt1 = $opt2 = '';
        $edmerp = 400; //all edm data shared from 400 now
        if ($erp === 250) {
            $edmerp = 250;
        }
        if ($erp === 220) {
            $edmerp = 220;
        }
        switch ($erp) {
            case 250:
            case 300:
            case 400:
            case 410:
            case 420:
            case 500:
            case 510:
            case 520:
            case 540:
            case 570:
            case 600:
            case 640:
            case 660:
            case 680:
            case 700:
                $query = <<<EOF
SELECT
    '$erp' AS erp,
    RTRIM(t_eitm) AS t_eitm,
    RTRIM(t_cdrw) AS t_cdrw,
    t_revi,
    t_dsca,
    SUBSTRING(convert(varchar,t_indt,120), 0, 11) AS t_indt,
    SUBSTRING(convert(varchar,t_exdt,120), 0, 11) AS t_exdt
FROM
    ttiedm100$edmerp
WHERE
    t_eitm='$pn' AND t_rele=1
ORDER BY
    t_indt DESC
EOF;
                $opt1 = 'odbc';
                $opt2 = ['src' => 'baan'];
                break;
            default:
                $query = <<<EOF
select * from bomedm
where
 erp='$this->itsERPID' AND eitm = '$pn'
EOF;
        }
        return tldUtils::getSqlToAssocArray($query, $opt1, $opt2);
    }
}
