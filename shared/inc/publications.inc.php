<?php
/**
 *    Publications related classes
 *
 * @package Publications
 * @desc All classes related to Publications are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

/**
 * Need these functions
 */
include_once('common.inc.php');
include_once('fpdf/fpdf.php');
include_once('fpdf/chinese.php');

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '120');

/**
 *
 *Creates a document object from data already stored in database
 *
 *    Table structure for table `manuals_parts`
 *CREATE TABLE `manuals_diag` (
 *  `id` int(20) NOT NULL auto_increment,
 *  `parent_id` int(11) NOT NULL default '0',
 *  `erp` int(3) NOT NULL default '0',
 *  `factory_num` varchar(50) NOT NULL default '',
 *  `rev` varchar(11) NOT NULL default '',
 *  `category` varchar(20) NOT NULL default '',
 *  `doc_type` varchar(20) NOT NULL default '',
 *  `endescription` text NOT NULL,
 *  `frdescription` text NOT NULL,
 *  `diagram_filename` varchar(100) NOT NULL default '',
 *  `ennotes` text NOT NULL,
 *  `frnotes` text NOT NULL,
 *  PRIMARY KEY  (`id`)
 *) TYPE=MyISAM;
 *
 *
 * @package Publications
 */
class document
{
	public $itsErp;
	public $itsId;
	public $itsDiagramUploadDir;
	public $itsDocAsArray;
	public $itsLang;
	public $itsHeader;

	public function __construct($id, $erp = '', $lang = 'en')
	{
		global $HOME_DIR;
		$this->itsDiagramUploadDir = "$GLOBALS[UPLOADS_PATH]/manuals_diagrams/";
		$this->itsId = $id;
		$this->itsLang = $lang;
		$this->itsHeader = $this->getHeader();
		$this->itsDocAsArray = $this->getDetailArray();
		//set the erp var from database, if not set explicitly in params
		if (!isset($erp)) {
			$erp = $this->itsHeader['erp'];
		}
		$this->itsErp = $erp;
	}

//DOCUMENT FUNCTIONS
	public function deleteSelf()
	{
		$id = $this->itsId;
		if (empty($id)) {
			return 'Doc ID was not set in delDocument...';
		}
		//Delete header
		$query = <<< EOF
		DELETE FROM manuals_diag
		WHERE id=$id
		LIMIT 1
EOF;
		$result = tldUtils::sqlQuery($query);
		//Delete detail
		$query = <<< EOF
		DELETE FROM manuals_parts
		WHERE parent_id=$id
EOF;
		$result .= tldUtils::sqlQuery($query);
		return $result;
	}

	public function outFile()
	{
		$header = $this->getHeader();
		if ($header['diagram_filename']) {
            $filenamepath = self::getFilePath($GLOBALS['UPLOADS_PATH'], $header['diagram_filename']);
            $myFile = new basicFile($filenamepath);
			if (strtolower(substr($header['diagram_filename_ext'], -3)) === 'jpg') {
				$myFile->outFile('', true);
			} else {
				$myFile->outFile();
			}
		}
	}

	public function outPDF()
	{
		$pdf = new html2pdf($this->getDoc(), '');
		$pdf->outFile('document_' . $this->itsId . '.pdf');
	}

	public function getDoc()
	{
		$smarty = tldUtils::getSmarty('common');
		$smarty->assign('header', $this->getHeader());
		$smarty->assign('body', $this->getBody());
		return $smarty->fetch('publications/documents/document.tpl');
	}

	public function getType()
	{
		return $this->itsHeader['doc_type'];
	}

	public function getBody()
	{
		$MAX_WIDTH = 640;
		$MAX_HEIGHT = 820;
		$MAX_LINES_PER_PAGE = 16;

		$smarty = tldUtils::getSmarty('common');
		//get header
		$header = $this->getHeader();
		if ($header['diagram_filename'] && strtolower(substr($header['diagram_filename'], -3)) === 'jpg') {
			$filename = $this->itsDiagramUploadDir . '/' . $header['diagram_filename'];
			if (file_exists($filename)) {
				[$width, $height, $type, $attr] = getimagesize($filename);
				//if it is landscape then rotate to portrait
				if ($width > $height) {
					$pFilename = 'p_' . $header['diagram_filename'];
					$pPath = $this->itsDiagramUploadDir . '/' . $pFilename;
					if (!file_exists($pPath)) {
						$src = imagecreatefromjpeg($filename);
						$dest = imagerotate($src, 90, 0);
						imagejpeg($dest, $pPath);
					}
					$header['diagram_filename'] = $pFilename;
					$temp = $width;
					$width = $height;
					$height = $temp;
				}
				$smarty->assign('diagram_path', $this->itsDiagramUploadDir);
				if ($width / $MAX_WIDTH > $height / $MAX_HEIGHT) {
					$ratio = $MAX_WIDTH / $width;
				} else {
					$ratio = $MAX_HEIGHT / $height;
				}
				$smarty->assign('pic_width', $width * $ratio);
				$smarty->assign('pic_height', $height * $ratio);
			}
		}
		$smarty->assign('header', $header);
		$rows = $this->getDetailArray();
		$charsLine = 50; //num of characters per line in detail field
		$numLines = 0;
		foreach ($rows as $row) {
			$height = ceil(strlen($row['en'] . $row['fr'] . $row['note']) / $charsLine);
			if ($height < 2) {
				$height = 2;
			}
			if ($numLines + $height > 30) {
				//add page
				$pages[] = $page ?? null;
				//reset page to empty
				$page = [];
				//reset line count
				$numLines = 0;
			} else {
				$numLines += $height;
			}
			$page[] = $row;
		}
		if (count($page)) {
			$pages[] = $page;
		}
		$smarty->assign('pages', $pages);
		$smarty->assign('MAX_LINES_PER_PAGE', $MAX_LINES_PER_PAGE);
		return $smarty->fetch('publications/documents/document.body.tpl');
	}

	public function isLandscape()
	{
		$header = $this->getHeader();
		if ($header['diagram_filename']) {
			$filename = $this->itsDiagramUploadDir . '/' . $header['diagram_filename'];
			if (file_exists($filename)) {
				list($width, $height, $type, $attr) = getimagesize($filename);
				if ($width > $height) {
					return true;
				}
			}
		}
	}

	public static function saveFile($filename, $tempFilename)
	{
		$timestamp = time();
		$bf = new basicFile($tempFilename);
		$cleanFilename = "$timestamp-" . basicFile::cleanupName($filename);
		$dest = tldUtils::getPathToUploadFile('manuals_diagrams', $cleanFilename);
		$res = $bf->copyFile($dest);
		if ($res !== true) {
			$retry = $bf->copyFile($dest);
			if ($retry !== true) {
				return false;
			}
		}

		return $cleanFilename;
	}

	public function duplicateFile()
	{
		$header = $this->getHeader();
		$filename = $header['diagram_filename'];
		$timestamp = time();
		$upload_dir = $this->itsDiagramUploadDir;
		list($oldTimestamp, $oldFilename) = explode('-', $filename);
		$cleanFilename = "$timestamp-" . basicFile::cleanupName($oldFilename);
		if (copy("$upload_dir/$filename", "$upload_dir/$cleanFilename")) {
			//echo "COPY WAS SUCCESSFUL. $cleanFilename";
			return $cleanFilename;
		}
	}

	public static function search($erp, $target, $offset = 0, $limit = 10, $lang = 'en')
	{
		$target = TldDatabase::escape($target);

		//Get total number of records
		$query = <<<EOF
		SELECT
			diag.id,
			diag.parent_id,
			diag.dt_created,
			diag.erp,
			diag.factory_num,
			diag.rev,
			diag.category,
			diag.doc_type,
			diag.endescription,
			diag.diagram_filename,
			diag.ennotes,
			diag.frnotes,
			trans.t_dsca AS frdescription
		FROM
			manuals_diag AS diag
			LEFT JOIN parts_trans AS trans ON trans.t_eitm=diag.factory_num
				AND trans.t_clan='$lang'
		WHERE
			diag.erp='$erp'
			AND (
				diag.id					like '%$target%'
				OR diag.factory_num		like '%$target%'
				OR diag.rev				like '%$target%'
				OR diag.category		like '%$target%'
				OR diag.doc_type		like '%$target%'
				OR diag.endescription	like '%$target%'
				OR trans.t_dsca			like '%$target%'
				OR diag.ennotes			like '%$target%'
				OR diag.frnotes			like '%$target%'
			)
		ORDER BY diag.id DESC
EOF;

		$result['count'] = count(tldUtils::getSqlToAssocArray($query));

		//get page
		$query .= " LIMIT $offset, $limit";

		$result['rows'] = tldUtils::getSqlToAssocArray($query);
		return $result;
	}

	public static function insertHeader($REQUEST_VARS)
	{
//		$erp = $this->itsErp;
		$vars = tldUtils::cleanupFormInput($REQUEST_VARS);
		$query = <<< EOF
			INSERT INTO manuals_diag
			SET
			erp				='0',
			factory_num		='${vars['factory_num']}',
			rev				='${vars['rev']}',
			category		='${vars['category']}',
			doc_type		='${vars['doc_type']}',
			endescription	='${vars['endescription']}',
			frdescription	='${vars['frdescription']}',
			ennotes			='${vars['ennotes']}',
			frnotes			='${vars['frnotes']}',
			diagram_filename='${vars['diagram_filename']}'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function newDocument($erp, $REQUEST_VARS, $FILES_VAR)
	{
		$newID = self::insertHeader($REQUEST_VARS);
		if (!is_int($newID)) {
			return "ERROR: $newID";
		}
		$fileDetails = $FILES_VAR['diagram_filename'];
		$filename = $fileDetails['name'];
		//save the file
		if ($filename !== 'none' && $filename <> '') {
			$cleanFilename = self::saveFile($filename, $fileDetails['tmp_name']);
			$myDoc = new document($erp, $newID);
			$myDoc->updateDBFilename($cleanFilename);
		}
		return $newID;
	}

	public function updateDBFilename($filename)
	{
		$id = $this->itsId;
		$erp = $this->itsErp;
		$query = <<< EOF
		UPDATE manuals_diag
		SET
		diagram_filename='$filename'
		WHERE id=$id
		LIMIT 1
EOF;
		tldUtils::sqlQuery($query);

	}

	public function duplicate()
	{
		//duplicate header first
		$header = $this->getHeader();
		$newID = self::insertHeader($header);

		$myDoc = new document($newID, $this->itsErp);
		//duplicate details
		$myDoc->insertMultipleDetails($this->getDetailArray());

		//duplicate file
		$cleanFilename = $this->duplicateFile();
		$myDoc->updateDBFilename($cleanFilename);

		return $newID;
	}

//HEADER FUNCTIONS

	/**
	 *    Returns header information for parts diagram as an array
	 *
	 */
	public function getHeader()
	{
		$id = $this->itsId;
		$lang = $this->itsLang;
		$headerQuery = <<<EOF
SELECT
	diag.id,
	diag.parent_id,
	diag.dt_created,
	diag.erp,
	diag.factory_num,
	diag.rev,
	diag.category,
	diag.doc_type,
	diag.endescription,
	diag.diagram_filename,
	diag.ennotes,
	diag.frnotes,
	CASE '$lang'
		WHEN 'CH' THEN trans.t_dsca
		ELSE COALESCE(trans.t_dsca, '')
	END AS frdescription
FROM
	manuals_diag AS diag
	LEFT JOIN parts_trans AS trans ON trans.t_eitm=diag.factory_num
		AND trans.t_clan='$lang'
WHERE
	diag.id='$id'
EOF;
		$row = tldUtils::getSqlRowToAssocArray($headerQuery);
		if ($row) {
			$row['diagram_filename_ext'] = strtolower(substr($row['diagram_filename'], -3));
		}
		return $row;
	}

	public function updateHeader($REQUEST_VARS, $FILES_VAR)
	{
		$id = $this->itsId;
		$erp = $this->itsErp;
		$filename = $FILES_VAR['diagram_filename']['name'];

		$query = <<< EOF
	UPDATE manuals_diag
	SET
	factory_num		='${REQUEST_VARS['factory_num']}',
	rev				='${REQUEST_VARS['rev']}',
	category		='${REQUEST_VARS['category']}',
	doc_type		='${REQUEST_VARS['doc_type']}',
	endescription	='${REQUEST_VARS['endescription']}',
	frdescription	='${REQUEST_VARS['frdescription']}',
	ennotes			='${REQUEST_VARS['ennotes']}',
	frnotes			='${REQUEST_VARS['frnotes']}'
	WHERE erp=$erp AND id=$id
	LIMIT 1
EOF;
		tldUtils::sqlQuery($query);

		//save the file
		if ($filename !== 'none' && $filename <> '') {
			$upload_dir = $this->itsDiagramUploadDir;
			//check if there's already a file there
			$headerArray = $this->getHeader($id);
			if (!empty($headerArray['diagram_filename'])) {
				unlink($upload_dir . '/' . $headerArray['diagram_filename']);
			}
			$newFilename = self::saveFile($filename, $FILES_VAR['diagram_filename']['tmp_name']);
			$this->updateDBFilename($newFilename);
		}
	}


//DETAIL FUNCTIONS
	public function insertMultipleDetails($n)
	{
		if (count($n) == 0) {
			return;
		}
		foreach ($n as $line) {
			$error .= $this->insertDetail($line);
		}
		return $error;
	}

	public function insertDetail($line)
	{
		$parent_id = $this->itsId;
		$line = tldUtils::cleanupFormInput($line);
		$query = <<< EOF
		INSERT INTO manuals_parts
		SET
		parent_id		='$parent_id',
		item			='${line['item']}',
		pn				='${line['pn']}',
		vendor_pn		='${line['vendor_pn']}',
		ocm_pn			='${line['ocm_pn']}',
		qty				='${line['qty']}',
		um				='${line['um']}',
		en				='${line['en']}',
		fr				='${line['fr']}',
		note			='${line['note']}',
		group_p			='${line['group_p']}',
		group_m			='${line['group_m']}',
		group_o			='${line['group_o']}',
		group_c			='${line['group_c']}'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function updateMultipleDetails($n)
	{
		if (count($n) == 0) {
			return;
		}
		foreach ($n as $id => $line) {
			$error .= $this->updateDetail($id, $line);
		}
		return $error;
	}

	public function updateDetail($id, $line)
	{
		$parent_id = $this->itsId;

		$query = <<< EOF
		UPDATE manuals_parts
		SET
			item			='${line['item']}',
			pn				='${line['pn']}',
			vendor_pn		='${line['vendor_pn']}',
			ocm_pn			='${line['ocm_pn']}',
			qty				='${line['qty']}',
			um				='${line['um']}',
			en				='${line['en']}',
			fr				='${line['fr']}',
			note			='${line['note']}'
		WHERE
			parent_id = $parent_id
			AND id		='$id'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function getDetailArray()
	{
		$id = $this->itsId;
		$lang = $this->itsLang;
		$query = <<<EOF
SELECT
	parts.id,
	parts.parent_id,
	parts.item,
	parts.pn,
	parts.vendor_pn,
	parts.ocm_pn,
	parts.qty,
	parts.um,
	parts.en,
	CASE '$lang'
		WHEN 'CH' THEN trans.t_dsca
		ELSE IF(trans.t_dsca,trans.t_dsca,IF(parts.fr=parts.en,'',parts.fr))
	END AS fr,
	parts.dscu,
	parts.note,
	parts.group_p,
	parts.group_m,
	parts.group_o,
	parts.group_c
FROM manuals_parts AS parts
	LEFT JOIN parts_trans AS trans ON trans.t_eitm=parts.pn
		AND trans.t_clan LIKE '$lang'
WHERE
	parts.parent_id='$id'
ORDER BY
	CAST(parts.item AS SIGNED)
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function deleteDetail($id)
	{
		$doc_id = $this->itsId;
		$query = <<< EOF
		DELETE FROM manuals_parts
		WHERE parent_id=$doc_id AND id=$id
EOF;

		return tldUtils::sqlQuery($query);
	}

	//return doc# containing part
	public static function findDocsContainingPN($pn)
	{
		$query = <<<EOF
			SELECT
				manuals_diag.id,
				manuals_diag.parent_id,
				manuals_diag.dt_created,
				manuals_diag.erp,
				manuals_diag.factory_num,
				manuals_diag.rev,
				manuals_diag.category,
				manuals_diag.doc_type,
				manuals_diag.endescription,
				manuals_diag.diagram_filename,
				manuals_diag.ennotes,
				manuals_diag.frnotes,
				trans.t_dsca AS frdescription,
				manuals_docs.parent_id AS man_id,
				manuals.brand,
				manuals.model,
				manuals_diag.id AS doc_id
			FROM
				manuals_parts LEFT JOIN manuals_diag ON manuals_parts.parent_id=manuals_diag.id
				LEFT JOIN manuals_docs ON manuals_diag.id=manuals_docs.doc_num
				LEFT JOIN manuals ON manuals_docs.parent_id=manuals.id
				LEFT JOIN parts_trans AS trans ON trans.t_eitm=manuals_diag.factory_num
					AND trans.t_clan=manuals.lang
			WHERE
				pn LIKE '$pn%'
				OR vendor_pn LIKE '$pn%'
				OR ocm_pn LIKE '$pn%'
			ORDER BY man_id, doc_id
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

    //return doc# containing part
	public static function findDocsContainingPNbyER($pn)
	{
		$query = <<<EOF
            SELECT
                manuals_diag.id,
                manuals_diag.parent_id,
                manuals_diag.dt_created,
                manuals_diag.erp,
                manuals_diag.factory_num,
                manuals_diag.rev,
                manuals_diag.category,
                manuals_diag.doc_type,
                manuals_diag.endescription,
                manuals_diag.diagram_filename,
                manuals_diag.ennotes,
                manuals_diag.frnotes,
                trans.t_dsca AS frdescription,
                manuals_docs.parent_id AS man_id,
                manuals.brand,
                manuals.model,
                manuals_diag.id AS doc_id,
                service.sn,
                service.customer_name AS cusname
            FROM
                manuals_parts
                LEFT JOIN manuals_diag ON manuals_parts.parent_id=manuals_diag.id
                LEFT JOIN manuals_docs ON manuals_diag.id=manuals_docs.doc_num
                LEFT JOIN manuals ON manuals_docs.parent_id=manuals.id
                LEFT JOIN parts_trans AS trans ON trans.t_eitm=manuals_diag.factory_num AND trans.t_clan=manuals.lang
                LEFT JOIN service_serials ON manuals.id=service_serials.serial 
                LEFT JOIN service ON service_serials.parent_id=service.id
            WHERE
                service_serials.component='MANUAL' AND (
                    pn LIKE '$pn%'
                    OR vendor_pn LIKE '$pn%'
                    OR ocm_pn LIKE '$pn%'
                )
            ORDER BY
                man_id, doc_id
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

    public static function getFilePath(string $directory, string $filename): ?string
    {
        $filePath = sprintf('%s/%s', $directory, $filename);
        if (is_file($filePath)) {
            return $filePath;
        }

        $filePath = sprintf('%s/manuals_diagrams/%s', $directory, $filename);
        return is_file($filePath) ? $filePath : null;
    }
}

/**
 * Class for handling manuals
 *
 * @package Publications
 */
class manual
{
	public $itsId;
	public $itsErp;
	public $itsHeader;
	public $itsLang;

	public function __construct($id, $erp = '', $lang = 'en')
	{
		$this->itsId = $id;
		$this->itsHeader = $this->getHeader();
		$this->itsLang = $lang;
		$this->itsErp = empty($erp) ? $this->itsHeader['erp'] : $erp;
	}

	public function getChapterFour()
	{
		$docs = $this->getDetails();
		$result = '';
		$toc = [];
		foreach ($docs as $doc) {
			if ('PARTS DIAGRAM' === $doc['doc_type']) {
				$toc[$doc['category']][] = $doc;
				$doc = new document($doc['id']);
				$result .= $doc->getBody();
			}
		}
		return $this->getTOC($toc) . $result;
	}

	public function outPNRefIndex($file = '')
	{
		$query = <<<EOF
		SELECT
			T3.pn AS t,
			CONCAT(T3.parent_id,'-', T3.item) AS p
		FROM
			manuals_docs AS T1,
			manuals_diag AS T2,
			manuals_parts AS T3
		WHERE
			T1.parent_id=$this->itsId
			AND T1.doc_num=T2.id
			AND T2.id=T3.parent_id
		ORDER BY
			t, p
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		$pdf = new PDF_Ref();
		$pdf->Open();
		$pdf->SetFont('Arial', '', 15);
		//Creation of index
		$pdf->CreateReference(4, $rows);
		$pdf->Output($file);
	}

	public function getTOC($toc)
	{
		if (empty($toc)) {
			return;
		}
		$text = [
			'BOOM' => ['fr' => 'FLECHE'],
			'BODY' => ['fr' => 'CORPS'],
			'BRAKING' => ['fr' => 'FREINAGE'],
			'BRIDGE' => ['fr' => 'PONT'],
			'CHASSIS' => ['fr' => 'EQUIPEMENT CHASSIS'],
			'COMPRESSOR' => ['fr' => 'COMPRESSEUR'],
			'ELEC CONTROL' => ['fr' => 'ELECTRICITE'],
			'ELEVATOR' => ['fr' => 'ASCENSEUR'],
			'ENGINE' => ['fr' => 'EQUIPEMENT MOTEUR'],
			'GENERATOR' => ['fr' => 'GENERATEUR'],
			'HYDRAULIC' => ['fr' => 'HYDRAULIQUE '],
			'PNEUMATIC' => ['fr' => 'PNEUMATIQUE'],
			'REFRIGERATION' => ['fr' => 'REFRIGERATION'],
			'SUSPENSION' => ['fr' => 'SUSPENSION'],
			'STRUCTURAL' => ['fr' => 'STRUCTURE'],
			'TRANSMISSION' => ['fr' => 'TRANSMISSION'],
			'LABELS' => ['fr' => 'LABELS'],
			'DRIVER CAB' => ['fr' => 'DRIVER CAB'],
			'OPTIONS' => ['fr' => 'OPTIONS'],
		];
		$i = 0;
		$pageHeight = 88;
		$result = '<h1>Table of Contents</h1><ul>';
		foreach ($toc as $category => $docs) {
			if ($i % $pageHeight > $pageHeight - 5) {
				$result .= '<!-- NEW PAGE --><h1>Table of Contents</h1><ul>';
				$i = (floor($i / $pageHeight) + 1) * $pageHeight;
			}
			$result .= '<li>';
			$result .= $this->itsLang !== 'en' ? $text[$category][$this->itsLang] : $category;
			$result .= '<ul>';
			$i += 4;
			foreach ($docs as $doc) {
				$result .= '<li><a href="#' . $doc['id'] . '">' . $doc['factory_num'] .
					', ' . $doc[$this->itsLang . 'description'] . '</a></li>';
				$i += 2;
				//detect full page
				if ($pageHeight - 3 < $i % $pageHeight && $i % $pageHeight < $pageHeight) {
					$result .= '<ul></li></ul><!-- NEW PAGE --><h1>Table of Contents</h1><ul>';
					$result .= '<li>';
					$result .= $this->itsLang !== 'en' ? $text[$category][$this->itsLang] : $category;
					$result .= '<ul>';
					$i += 2;
				}
			}
			$result .= '</ul></li>';
		}
		$result .= '</ul>' .
			'<!-- NEW PAGE -->';
		if ((floor($i / $pageHeight) + 1) % 2 == 0) {
			$result .= '<br><br><br><br><br><br><br><br><br><br><br><br>' .
				'<p align="center">Page Intentionally Left Blank</p><!-- NEW PAGE -->';
		}
		return $result;
	}

	public function getManual()
	{
		$smarty = tldUtils::getSmarty('common');
		$smarty->assign('header', $this->getHeader());
		$smarty->assign('body', $this->getChapterFour());
		return $smarty->fetch('publications/manuals/manual.tpl');
	}

	public function outPDF()
	{
		$pdf = new html2pdf($this->getManual(), '');
		$pdf->outFile('manual_' . $this->itsId . '.pdf');
	}

	public function getPDF($filename = false)
	{
		$pdf = new html2pdf($this->getManual(), '');
		if ($filename) {
			return $pdf->getFile();
		}

		readfile($pdf->getFile());
	}

	/**
	 * Get RSPL for particular group
	 *
	 * @param string
	 */
	public function getRSPL($grp = ''): array
	{
		if (in_array($grp, ['p', 'm', 'o', 'c',])) {
			$WHERE = " AND manuals_parts.group_$grp <> ''";
		} else {
			$WHERE = <<<EOF
			 AND (manuals_parts.group_p="P" OR manuals_parts.group_m="M" OR
			manuals_parts.group_o="O" OR manuals_parts.group_c="C")
EOF;
		}
		$query = <<<EOF
		SELECT
			manuals_diag.category,
			manuals_parts.id,
			manuals_parts.parent_id,
			manuals_parts.item,
			manuals_parts.pn,
			manuals_parts.vendor_pn,
			manuals_parts.ocm_pn,
			manuals_parts.qty,
			manuals_parts.um,
			manuals_parts.en,
			parts_trans.t_dsca AS fr,
			manuals_parts.dscu,
			manuals_parts.note,
			manuals_parts.group_p,
			manuals_parts.group_m,
			manuals_parts.group_o,
			manuals_parts.group_c
		FROM
			manuals_docs,
			manuals_diag,
			manuals_parts,
			parts_trans
		WHERE
			manuals_docs.doc_num=manuals_diag.id
			AND parts_trans.t_eitm=manuals_diag.factory_num
			AND manuals_diag.id=manuals_parts.parent_id
			AND manuals_docs.parent_id=$this->itsId
		$WHERE
		ORDER BY manuals_diag.category, manuals_diag.id, manuals_parts.item
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public function deleteSelf()
	{
		$id = $this->itsId;
		if (empty($id)) {
			return 'Doc ID was not set in delManual...';
		}
		//Delete header
		$query = <<< EOF
		DELETE FROM manuals
		WHERE id=$id
		LIMIT 1
EOF;
		$result = tldUtils::sqlQuery($query);
		//Delete detail
		$query = <<< EOF
		DELETE FROM manuals_docs
		WHERE parent_id=$id
EOF;
		$result .= tldUtils::sqlQuery($query);
		return $result;
	}

	/**
	 * Get List of docs linked to the manual
	 * @return mixed string error or array
	 */
	public function getDoc()
	{
		if (empty($this->itsId)) {
			return 'Not in object context';
		}
		$query = <<< EOF
			SELECT
				manuals_docs.item,
				manuals_docs.id,
				manuals_diag.id AS doc_id,
				manuals_diag.factory_num,
				manuals_diag.endescription,
				parts_trans.t_dsca AS frdescription
			FROM manuals
				LEFT JOIN manuals_docs ON manuals.id=manuals_docs.parent_id
				LEFT JOIN manuals_diag ON manuals_docs.doc_num=manuals_diag.id
				LEFT JOIN parts_trans ON parts_trans.t_eitm=manuals_diag.factory_num
					AND parts_trans.t_clan=manuals.lang
			WHERE manuals.id=$this->itsId
			GROUP BY manuals_docs.id
			ORDER BY manuals_docs.item
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

    /**
     * Return ISO2 version of a language
     * @return string language
     */
    public static function getIso2Language($language)
    {
        if (strlen($language) === 2){
            return $language;
        }

        switch ($language){
            case 'FRENCH':
                return 'FR';
            case 'CHINESE':
                return 'CH';
            default :
                return 'EN';
        }
    }

	/**
	 * Search for a docs
	 * @return mixed string error or array
	 */
	public static function searchDoc($target, $limit = 50)
	{
		$limit = TldDatabase::escape($limit);
		$target = TldDatabase::escape($target);
		$query = <<<EOF
			SELECT
				manuals_diag.*
			FROM
				manuals_diag
			WHERE
				id like '%$target%'
				OR factory_num like '%$target%'
				OR endescription like '%$target%'
				OR category like '%$target%'
			ORDER BY rev DESC
			LIMIT 0,$limit
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function addDoc($doc_id, $item)
	{
		$manual_id = $this->itsId;
		$query = <<< EOF
				INSERT INTO manuals_docs
				SET
				parent_id		='$manual_id',
				item			='$item',
				doc_num			='$doc_id'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function removeDoc($id)
	{
		$manual_id = $this->itsId;
		$query = <<< EOF
		DELETE FROM manuals_docs
		WHERE parent_id=$manual_id
		AND id=$id
		LIMIT 1
EOF;
		return tldUtils::sqlQuery($query);
	}

	//Positions is associative array, key is document id, value is new position number
	public function updateDocPositions($positions)
	{
		$manual_id = $this->itsId;
		foreach ($positions as $id => $position) {
			$query = <<<EOF
				UPDATE manuals_docs
				SET item='$position'
				WHERE parent_id=$manual_id AND id=$id
				LIMIT 1
EOF;
			$e = tldUtils::sqlQuery($query);
			if (is_string($e)) {
				$error[] = $e;
			}
		}
		return $error;
	}

	public static function search($erp, $target)
	{
//		$MAX_LINES_PER_PAGE = 150;
		$query = <<< EOF
		SELECT *
		FROM manuals
		WHERE
		erp = $erp AND (
EOF;
		if (is_array($target)) {
			$num = count($target);
			if ($num == 0) {
				return;
			}
			$fields = ['brand', 'model', 'description', 'features'];
			foreach ($target as $key => $value) {
				if (in_array($key, $fields, true)) {
					$query .= "$key='$value'";
					$num--;
				}
				if ($num > 0) {
					$query .= ' OR ';
				}
			}
		} else {
			$query .= <<< EOF
			brand			like '%$target%'
			OR model		like '%$target%'
			OR description	like '%$target%'
			OR features		like '%$target%'
EOF;
		}
		$query .= <<< EOF
		)
		ORDER BY id DESC
EOF;

//		LIMIT $MAX_LINES_PER_PAGE
		return tldUtils::getSqlToAssocArray($query);
	}

	public static function insertHeader($erp, $header)
	{
		$vars = tldUtils::cleanupFormInput($header);
		$query = <<< EOF
	INSERT INTO manuals
	SET
	erp				='$erp',
	brand			='${vars['brand']}',
	model			='${vars['model']}',
	date			=now(),
	description		='${vars['description']}',
	features		='${vars['features']}',
	lang			='${vars['lang']}',
	status			='${vars['status']}'
EOF;
		return tldUtils::sqlInsert($query);
	}

	public function updateHeader($header)
	{
		$vars = tldUtils::cleanupFormInput($header);
		$erp = $this->itsErp;
		$id = $this->itsId;
		$query = <<< EOF
	UPDATE manuals
	SET
	erp				='$erp',
	brand			='${vars['brand']}',
	model			='${vars['model']}',
	date			='${vars['date']}',
	description		='${vars['description']}',
	features		='${vars['features']}',
	lang			='${vars['lang']}',
	status			='${vars['status']}'
	WHERE
		id=$id
EOF;
		return tldUtils::sqlInsert($query);
	}

	public static function emailNotifyLateDownloads()
	{
		$query = <<<EOF
        SELECT vendors.*,
        CONCAT(firstname, ' ',lastname) AS vendor_fullname
        FROM vendors
        LEFT JOIN vendors_suno ON vendors.id=vendors_suno.parent_id
        WHERE vendors_suno.type = 'manual_dwl'
        GROUP BY vendors.id
EOF;
		$vendors = tldUtils::getSqlToAssocArray($query);
		foreach ($vendors as $vendor) {
			$vendorEmail = $vendor['email'];
			$vendorFullname = $vendor['vendor_fullname'];
			$vendorID = $vendor['id'];
			$query = <<<EOF
SELECT
manuals_downloads.*,
(SELECT email FROM people WHERE people.id = manuals_downloads.poster_id) AS poster_email,
service.sn AS er_sn,
service.model AS model,
(SELECT customer_name FROM customers WHERE customers.id=service.customer_id) AS customer,
manuals_downloads.parent_id AS manual_id
FROM manuals_downloads
LEFT JOIN service ON service.id=manuals_downloads.er_id
WHERE manuals_downloads.vendor_id = $vendorID AND manuals_downloads.downloaded = 0
AND DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 DAY), '%Y-%m-%d') = DATE_FORMAT(dt_entered, '%Y-%m-%d')
ORDER BY manuals_downloads.id
EOF;

			$manuals = tldUtils::getSqlToAssocArray($query);
			if (!empty($manuals)) {
				$poster_emails = [];
				foreach ($manuals as $manual) {
					$poster_emails[] = $manual['poster_email'];
				}
				//Email Notification
				$message = "Dear $vendorFullname,<br><br>This is a reminder that you have pending manual(s) ready for printing.<br>";
				$form = new tldReportColumnar(
					$manuals,
					[
						'xItems' => [
							'er_sn' => 'Unit SN',
							'model' => 'Unit Model',
							'customer' => 'Customer',
							'manual_id' => 'Manual ID#',
							'dt_delivery' => 'Expected Delivery Date',
							'std_manual' => 'Standard',
							'full_manual' => 'Full',
							'extra_cd' => 'Extra CD',
							'chapter_5' => 'Chapter 5',
						],
					]
				);
				$report = $form->fetch();
				$message .= $report;
				$message .= "<br><a href='https://www.tld-gse.com/evendors/evendors.php?m[0]=manuals'>Please click here to access the manual(s)</a>";
				tldUtils::emailAttachment(
					$vendorEmail,
					'noreply@tld-gse.com',
					'Pending Manual Printing',
					$message,
					null,
					array_unique($poster_emails)
				);
			}
		}
	}


	public function setDownloaded($id)
	{
		if (empty($id) || !is_numeric($id)) {
			return 'Manual Download ID is not set or not valid!';
		}
		$query = "UPDATE manuals_downloads SET downloaded = 1 WHERE id = $id LIMIT 1";
		return tldUtils::sqlExecute($query);
	}

	public static function hideDownload($id)
	{
		if (empty($id) || !is_numeric($id)) {
			return 'Manual Download ID is not set or not valid!';
		}
		$query = "UPDATE manuals_downloads SET hide = 1 WHERE id = $id LIMIT 1";
		return tldUtils::sqlExecute($query);
	}

	public function insertDownload($p)
	{
		if (empty($this->itsId)) {
			return 'Not object context';
		}
		$fields = ['poster_id', 'vendor_id', 'er_id', 'erp', 'std_manual', 'full_manual', 'extra_cd', 'chapter_5', 'dt_delivery', 'comment'];
		$query = <<<EOF
INSERT INTO manuals_downloads
SET parent_id = $this->itsId, dt_entered = NOW(),
EOF;
		$query .= tldUtils::getSqlSet($p, $fields);
		return tldUtils::sqlInsert($query);
	}

	public function getDownloads()
	{
		if (empty($this->itsId)) {
			return 'Not object context';
		}
		$query = <<<EOF
SELECT
manuals_downloads.*,
manuals_downloads.id AS dwl_id,
(SELECT CONCAT(firstname,' ',lastname,' - #',id) FROM vendors WHERE vendors.id=manuals_downloads.vendor_id) AS vendor,
service.sn AS er_sn,
service.model AS model,
(SELECT customer_name FROM customers WHERE customers.id=service.customer_id) AS customer,
manuals_downloads.parent_id AS manual_id,
IF(manuals_downloads.downloaded = 0, 'No', 'Yes') AS downloaded,
IF(manuals_downloads.hide = 0, 'No', 'Yes') AS hidden
FROM manuals_downloads
LEFT JOIN service ON service.id=manuals_downloads.er_id
WHERE manuals_downloads.parent_id = $this->itsId
ORDER BY manuals_downloads.id
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function deleteDownload($id)
	{
		if (empty($id) || !is_numeric($id)) {
			return 'Manual Download ID is not set or not valid!';
		}
		$query = "DELETE FROM manuals_downloads WHERE id = $id LIMIT 1";
		return tldUtils::sqlExecute($query);
	}

	public static function resetDownload($id)
	{
		if (empty($id) || !is_numeric($id)) {
			return 'Manual Download ID is not set or not valid!';
		}
		$query = "UPDATE manuals_downloads SET downloaded = 0, hide = 0 WHERE id = $id LIMIT 1";
		return tldUtils::sqlExecute($query);
	}

	public function addLogEntry($id, $comment)
	{
		$a['parent_id'] = $this->itsId;
		$a['module'] = 'MANUAL';
		$a['poster'] = $id;
		$a['comment'] = $comment;
		return tldModLog::insert($a);
	}

	public function getLog()
	{
		return tldModLog::byParent($this->itsId, 'MANUAL');
	}

	public function getHeader()
	{
		$id = $this->itsId;
		$query = <<<EOF
			SELECT	*
			FROM manuals
			WHERE id=$id
EOF;
		return tldUtils::getSqlRowToAssocArray($query);
	}

	public function getLang()
	{
		return $this->itsHeader['lang'];
	}

	/**
	 * get associated documents
	 * optionally filter by pn
	 *
	 * @return array array of db rows
	 */
	public function getDetails($search = '')
	{
		$id = $this->itsId;
		$lang = $this->itsLang;
		$WHERE = '';
		if ($search) {
			$WHERE = <<<EOF
			AND (T2.endescription like '%$search%'
				OR T2.frdescription like '%$search%'
				OR T3.pn='$search' OR T3.en like '%$search%'
				OR T3.fr like '%$search%'
			)
EOF;
		}
		// Create the query
		$query = <<<EOF
		SELECT
			DISTINCT(T1.id) AS doc_id,
			T1.item,
			T2.id,
			T2.parent_id,
			T2.dt_created,
			T2.erp,
			T2.factory_num,
			T2.rev,
			T2.category,
			T2.doc_type,
			T2.endescription,
			IF(T2.frdescription<>'',T2.frdescription,IF(T4.t_dsca,T4.t_dsca,IF(T3.fr=T2.endescription,'',T3.fr))) AS frdescription,
			T2.diagram_filename,
			T2.ennotes,
			T2.frnotes
		FROM
			manuals_docs AS T1
			LEFT JOIN manuals_diag AS T2 ON T1.doc_num=T2.id
			LEFT JOIN manuals_parts AS T3 ON T2.id=T3.parent_id
			LEFT JOIN parts_trans AS T4 ON T4.t_eitm=T2.factory_num
				AND T4.t_clan LIKE '$lang'
		WHERE
			T1.parent_id=$id
			$WHERE
		GROUP BY
			T1.id,
			T1.item,
			T2.id,
			T2.parent_id,
			T2.dt_created,
			T2.erp,
			T2.factory_num,
			T2.rev,
			T2.category,
			T2.doc_type,
			T2.endescription,
			T4.t_dsca,
			T2.diagram_filename,
			T2.ennotes,
			T2.frnotes
		ORDER BY
			T2.doc_type,
			T2.category,
			CAST(T1.item AS SIGNED)
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public function getCategorizedDetails($search = '')
	{
		$docs = $this->getDetails($search);
		$result = [];
		foreach ($docs as $doc) {
			$result[$doc['doc_type']][$doc['category']][] = $doc;
		}

		return $result;
	}

	public function duplicate()
	{
		$erp = $this->itsErp;

		//copy header
		$header = $this->getHeader();
		$newID = self::insertHeader($erp, $header);

		$myDoc = new manual($newID, $erp);
		//copy details
		$details = $this->getDetails();

		foreach ($details as $line) {
			$myDoc->addDoc($line['id']);
		}

		return $newID;
	}

	//STATIC FUNCTIONS
	public static function byCustomer($customer_id, $options = '')
	{
		$query = <<<EOF
			SELECT
				DISTINCT(manuals.id),
				manuals.*
			FROM
				service,
				service_serials,
				manuals
			WHERE
				service.id=service_serials.parent_id
				AND service_serials.serial=manuals.id
				AND service_serials.component='MANUAL'
				AND service.buyer_customer_id=$customer_id
			ORDER BY
				manuals.id
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get count of manual by language and model
	 *
	 * @return array returns a 2 dim array of counts by language and model
	 */
	public static function countByModelLang($mode, $a = '')
	{
		$CONSTRAINTS = ['model' => '%'];
		if ($mode === 'byFamily' && $a !== ' ALL_MODELS') {
			$CONSTRAINTS = ['model' => $a . '%'];
		}
		$WHERE = tldUtils::constructWhere($CONSTRAINTS);


		$query = <<<EOF
			SELECT id, model, count(*) as num, lang FROM manuals
			WHERE $WHERE
			group by model, lang
			order by model, lang
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	/**
	 * Get count of manual publishable from CBOM by model
	 *
	 * @return array returns a 2 dim array of counts by model by publishable status
	 */
	public static function countByModelPublishable($mode, $a = '')
	{
		$CONSTRAINTS = ['model' => '%'];
		if ($mode === 'byFamily' && $a !== ' ALL_MODELS') {
			$CONSTRAINTS = ['model' => $a . '%'];
		}
		$WHERE = tldUtils::constructWhere($CONSTRAINTS);

		$query = <<<EOF
			SELECT id, model, count(*) as num, publishable FROM service
			WHERE $WHERE
			group by model, publishable
			order by model, publishable
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function byQuery($params, $sort = '')
	{
		if ($sort) {
			$sortBy = "ORDER BY $sort";
		}
		$parms = tldUtils::constructWhere($params);
		if ($parms) {
			$WHERE = " WHERE $parms";
		}
		$query = <<<EOF
		SELECT * FROM manuals
		$WHERE
		$sortBy
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	public static function byCustomerPN($cu_nama, $pn)
	{
		$cu_nama = str_replace(' ', '%', $cu_nama);
		$query = <<<EOF
		SELECT
			DISTINCT t3.id,
			t5.id,
			t4.parent_id AS man_id,
			t3.brand,
			t3.model,
			t5.id AS doc_id,
			t5.parent_id,
			t5.dt_created,
			t5.erp,
			t5.factory_num,
			t5.rev,
			t5.category,
			t5.doc_type,
			t5.endescription,
			t7.t_dsca AS frdescription,
			t5.diagram_filename,
			t5.ennotes,
			t5.frnotes
		FROM
			service as t1,
			service_serials as t2,
			manuals as t3,
			manuals_docs as t4,
			manuals_diag as t5,
			manuals_parts as t6,
			parts.trans AS t7
		WHERE
			t1.id=t2.parent_id
			and t2.component='MANUAL'
			and t2.serial=t3.id
			and t3.id=t4.parent_id
			and t4.doc_num=t5.id
			and t5.id=t6.parent_id
			AND t7.t_eitm=t5.factory_num
			and t1.customer_name like '%$cu_nama%'
			and t6.pn='$pn'
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	//returns an array of brands in system
	public static function getManuals($where = '')
	{
		if ($where) {
			$where = " WHERE $where";
		}
		$query = <<<EOF
			SELECT	*
			FROM manuals
			$where
			ORDER BY id
EOF;

		return tldUtils::getSqlToAssocArray($query);
	}

	//returns an array of brands in system
	public static function getBrands($where = '')
	{
		if ($where) {
			$where = " AND $where";
		}
		$query = <<<EOF
			SELECT	DISTINCT(brand)
			FROM manuals
			WHERE brand<>''
			$where
			ORDER BY brand
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	//returns an array of models in system
	public static function getModels($where = '')
	{
		if ($where) {
			$where = " AND $where";
		}
		$query = <<<EOF
			SELECT	DISTINCT(model)
			FROM manuals
			WHERE model<>''
			$where
			ORDER BY model
EOF;
		return tldUtils::getSqlToAssocArray($query);
	}

	public function getEquipmentIds()
	{
		$query = <<<SQL
SELECT serial as service_id
FROM service_serials
WHERE serial = $this->itsId AND component = 'MANUAL'
ORDER BY serial
SQL;

		return tldUtils::getSqlToAssocArray($query);
	}
}

/**
 * Sublass of FPDF to handle barcodes
 *
 * @package Publications
 */
class tldFPDF extends PDF_Chinese
{

	public function EAN13($x, $y, $barcode, $h = 16, $w = .35)
	{
		$this->Barcode($x, $y, $barcode, $h, $w, 13);
	}

	public function UPC_A($x, $y, $barcode, $h = 16, $w = .35)
	{
		$this->Barcode($x, $y, $barcode, $h, $w, 12);
	}

	public function GetCheckDigit($barcode)
	{
		//Compute the check digit
		$sum = 0;
		for ($i = 1; $i <= 11; $i += 2) {
			$sum += 3 * $barcode[$i];
		}
		for ($i = 0; $i <= 10; $i += 2) {
			$sum += $barcode[$i];
		}
		$r = $sum % 10;
		if ($r > 0) {
			$r = 10 - $r;
		}
		return $r;
	}

	public function TestCheckDigit($barcode)
	{
		//Test validity of check digit
		$sum = 0;
		for ($i = 1; $i <= 11; $i += 2) {
			$sum += 3 * $barcode[$i];
		}
		for ($i = 0; $i <= 10; $i += 2) {
			$sum += $barcode[$i];
		}
		return ($sum + $barcode[12]) % 10 == 0;
	}

	public function Barcode($x, $y, $text, $h, $w, $len)
	{
		//Padding
		$barcode = str_pad($text, $len - 1, '0', STR_PAD_LEFT);
		if ((int)$len === 12) {
			$barcode = '0' . $barcode;
		}
		//Add or control the check digit
		if (strlen($barcode) === 12) {
			$barcode .= $this->GetCheckDigit($barcode);
		} elseif (!$this->TestCheckDigit($barcode)) {
			$this->Error('Incorrect check digit');
		}
		//Convert digits to bars
		$codes = [
			'A' => [
				'0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101', '4' => '0100011',
				'5' => '0110001', '6' => '0101111', '7' => '0111011', '8' => '0110111', '9' => '0001011'],
			'B' => [
				'0' => '0100111', '1' => '0110011', '2' => '0011011', '3' => '0100001', '4' => '0011101',
				'5' => '0111001', '6' => '0000101', '7' => '0010001', '8' => '0001001', '9' => '0010111'],
			'C' => [
				'0' => '1110010', '1' => '1100110', '2' => '1101100', '3' => '1000010', '4' => '1011100',
				'5' => '1001110', '6' => '1010000', '7' => '1000100', '8' => '1001000', '9' => '1110100'],
		];
		$parities = [
			'0' => ['A', 'A', 'A', 'A', 'A', 'A'],
			'1' => ['A', 'A', 'B', 'A', 'B', 'B'],
			'2' => ['A', 'A', 'B', 'B', 'A', 'B'],
			'3' => ['A', 'A', 'B', 'B', 'B', 'A'],
			'4' => ['A', 'B', 'A', 'A', 'B', 'B'],
			'5' => ['A', 'B', 'B', 'A', 'A', 'B'],
			'6' => ['A', 'B', 'B', 'B', 'A', 'A'],
			'7' => ['A', 'B', 'A', 'B', 'A', 'B'],
			'8' => ['A', 'B', 'A', 'B', 'B', 'A'],
			'9' => ['A', 'B', 'B', 'A', 'B', 'A'],
		];
		$code = '101';
		$p = $parities[$barcode[0]];
		for ($i = 1; $i <= 6; $i++) {
			$code .= $codes[$p[$i - 1]][$barcode[$i]];
		}
		$code .= '01010';
		for ($i = 7; $i <= 12; $i++) {
			$code .= $codes['C'][$barcode[$i]];
		}
		$code .= '101';
		//Draw bars
		for ($i = 0, $iMax = strlen($code); $i < $iMax; $i++) {
			if ($code[$i] == '1') {
				$this->Rect($x + $i * $w, $y, $w, $h, 'F');
			}
		}
		//Print text uder barcode
//	    $this->Text($x+9,$y+$h+9/$this->k,substr($text,-$len));
	}
}

//require('fpdf.php');

/**
 * fpdf reference class
 *
 * Author: Pierre-André Vullioud
 * License: Freeware
 * @package Publications
 */
class PDF_Ref extends FPDF
{
	public $RefActive = 0;        //Flag indicating that the index is being processed
	public $ChangePage = 0;       //Flag indicating that a page break has occurred
	public $Reference = [];  //Array containing the references
	public $col = 0;              //Current column number
	public $NbCol;              //Total number of columns
	public $y0;                 //Top ordinate of columns
	public $itsTitle = 'Index';

	public function Header()
	{
		if ((int) $this->RefActive === 1) {
			//Title of index pages
			$this->SetFont('Arial', '', 15);
			$this->Cell(0, 5, $this->itsTitle, 0, 1, 'C');
			$this->Ln();
		}
	}

	public function Reference($txt)
	{
		$present = 0;
		$size = sizeof($this->Reference);

		//Search the reference in the array
		for ($i = 0; $i < $size; $i++) {
			if ($this->Reference[$i]['t'] == $txt) {
				$present = 1;
				$this->Reference[$i]['p'] .= ',' . $this->PageNo();
			}
		}

		//If not found, add it
		if ($present === 0) {
			$this->Reference[] = ['t' => $txt, 'p' => $this->PageNo()];
		}
	}

	public function CreateReference($NbCol, $refs = '')
	{
		if (count($refs)) {
			$this->Reference = $refs;
		}

		//Initialization
		$this->RefActive = 1;
		$this->SetFontSize(8);

		//New page
		$this->AddPage();

		//Save the ordinate
		$this->y0 = $this->GetY();
		$this->NbCol = $NbCol;
		$size = sizeof($this->Reference);
		$PageWidth = $this->w - $this->lMargin - $this->rMargin;

		for ($i = 0; $i < $size; $i++) {

			//Handles page break and new position
			if ($this->ChangePage == 1) {
				$this->ChangePage = 0;
				$this->y0 = $this->GetY() - $this->FontSize - 1;
			}

			//LibellLabel
			$str = $this->Reference[$i]['t'];
			$strsize = $this->GetStringWidth($str);
			$this->Cell($strsize + 2, $this->FontSize + 2, $str, 0, 0, 'R');

			//Dots
			//Computes the widths
			$ColWidth = ($PageWidth / $NbCol) - 2;
			$w = $ColWidth - $this->GetStringWidth($this->Reference[$i]['p']) - ($strsize + 4);
			if ($w < 6) {
				$w = 6;
			}
			$nb = $w / $this->GetStringWidth('.');
			$dots = str_repeat('.', $nb - 2);
			$this->Cell($w, $this->FontSize + 2, $dots, 0, 0, 'L');

			//Page number
			$this->SetFillColor(211, 211, 211);
			$Largeur = $ColWidth - $strsize - $w;
			$this->MultiCell($Largeur, $this->FontSize + 1, $this->Reference[$i]['p'], 0, 'R', 1);
		}
		$this->RefActive = 0;
	}

	public function SetCol($col)
	{
		//Set position on a column
		$this->col = $col;
		$x = $this->rMargin + $col * ($this->w - $this->rMargin - $this->rMargin) / $this->NbCol;
		$this->SetLeftMargin($x);
		$this->SetX($x);
	}

	public function AcceptPageBreak()
	{
		if ((int)$this->RefActive !== 1) {
			return true;
		}

		if ($this->col < $this->NbCol - 1) {
			//Go to the next column
			$this->SetCol($this->col + 1);
			$this->SetY($this->y0);
			//Stay on the page
			return false;
		}
		//Go back to the first column
		$this->SetCol(0);
		$this->ChangePage = 1;
		//Page break
		return true;
	}
}

/**
 * Class for converting html to pdf presentation
 *
 * @package Publications
 */
class html2pdf
{
	public $itsHtml;
	public $itsBaseDir;
	public $itsBaseUrl;

	public function __construct($string, $baseUrl = '', $baseDir = '')
	{
		$this->itsHtml = $string;
		$this->itsBaseUrl = $baseUrl;
		$this->itsBaseDir = $baseDir;
	}

	/**
	 * returns outfile filename
	 *
	 * @return string
	 */
	public function convert($in, $options = '')
	{
		$infile = escapeshellcmd($in);
		$outfile = escapeshellcmd(tempnam('/tmp', 'pdf')) . '.pdf';
		exec("htmldoc -t pdf --quiet --jpeg --continuous --pagelayout tworight --pagemode document --webpage --size Letter --left 10mm --right 10mm -f $outfile $options '$infile'");
		return $outfile;
	}

	/**
	 * Save html to a temp file
	 *
	 * @return string The temp filename
	 */
	public function saveHtmlFile()
	{
		$filename = tempnam('/tmp', 'pdf');
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, 'a')) {
				echo "Cannot open file ($filename)";
				exit;
			}
			//convert http references to local
			$html = str_replace($this->itsBaseUrl, $this->itsBaseDir, $this->itsHtml);
			if (fwrite($handle, $html) === false) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);
			$result = $filename;
		} else {
			echo "The file $filename is not writable";
		}
		return $result;
	}

	/**
	 * returns filename converted pdf file
	 *
	 * @return string
	 */
	public function getFile()
	{
		$htmlFilename = $this->saveHtmlFile();
		return $this->convert($htmlFilename);
	}

	/**
	 * Send converted file to stdout
	 *
	 * @param string $file Final filename to use at client side
	 */
	public function outFile($file = '')
	{
		$pdfFilepath = $this->getFile();
		$pdfFile = new basicFile($pdfFilepath);
		if (empty($file)) {
			$file = basename($pdfFilepath);
		}
		//false param forces pdf to be downloaded
		$pdfFile->outFile($file, false);
	}
}

/**
 * Class for extracting vendor cross reference info
 * REPLACED BY FUNCTIONS IN ERP.INC.PHP
 *
 * @package Publications
 */
class xref
{
	public $itsId;
	public $itsBrandFrom;
	public $itsPnFrom;
	public $itsBrandTo;
	public $itsPnTo;
	public $theMAX_TREE_DEPTH = 5;
	public $itsLevel;

	public function __construct($brandFrom, $pnFrom, $level = 0)
	{
		$this->itsBrandFrom = $brandFrom;
		$this->itsPnFrom = $pnFrom;
		$this->itsLevel = $level;
	}

	public function getDetails()
	{
		$id = $this->itsId;
		$query = <<<EOF
		SELECT *
		FROM parts_xref
		WHERE id=$id
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
	}

	public function setTreeLevel($level, $direction = '')
	{
		if ($direction === 'rev') {
			$this->itsLevel = -$level;
		} else {
			$this->itsLevel = $level;
		}
	}

	public function getLowestLevel()
	{
		$result = $this->getXrefTree('rev');
		$lowest = 0;
		//find lowest level
		foreach ($result as $xref) {
			if ($xref->itsLevel < $lowest) {
				$lowest = $xref->itsLevel;
			}
		}
		return $lowest;
	}

	public function getRoot()
	{
		$fwdRoots = $this->findRoot($this->getXrefTree());
		$revRoots = $this->findRoot($this->getXrefTree('rev'));
		return array_merge($fwdRoots, $revRoots);
	}

	public function findRoot($xrefs)
	{
		if (count($xrefs) == 0) {
			return;
		}
		foreach ($xrefs as $xref) {
			echo $xref->toBrand;
			if (strtolower($xref->itsBrandFrom) === 'root') {
				$result[] = $xref;
			}
		}
		return is_array($result) ? $result : [];
	}

	//returns array of xref
	public function getXrefTree($direction = '')
	{
		$myXref = new xref($this->itsBrandFrom, $this->itsPnFrom);
		$myXref->setTreeLevel(0);
//		$result[] = $myXref;
		$result = [];
		$xrefs = $myXref->getRelatedXrefs($direction);
		if (count($xrefs) > 0) {
			foreach ($xrefs as $xref) {
				$xref->setTreeLevel(1, $direction);
				$this->addToResults($result, $xref, $direction);
				$history = [];
				$history[] = $myXref;
				$history[] = $xref;
				$xref->getXrefTreeRec($result, $history, $direction);
			}
		}
		return $result;
	}

	public function getRelatedXrefs($direction = '')
	{
		if ($direction === 'rev') {
			return $this->getParentXrefs();
		}

		return $this->getChildrenXrefs();
	}

	//need to call with $history as an array. initial call as empty array
	public function getXrefTreeRec(&$results, $history, $direction = '')
	{
		$xrefs = $this->getRelatedXrefs($direction);
		if (!$xrefs) {
			return;
		}

		$currentLevel = count($history);
		if ($currentLevel > $this->theMAX_TREE_DEPTH) {
			$error = new xref('ERROR',
				'Max tree depth reached at ' . $xref->itsBrandFrom . $xref->itsPnFrom,
				$currentLevel);
			$error->setTreeLevel($currentLevel, $direction);
			$this->addToResults($results, $error, $direction);
			return;
		}

		foreach ($xrefs as $xref) {
			foreach ($history as $step) {
				if ($step->isSame($xref)) {
					$error = new xref('ERROR',
						'Circular Reference to ' . $xref->itsBrandFrom . $xref->itsPnFrom,
						$currentLevel);
					$error->setTreeLevel($currentLevel, $direction);
					$this->addToResults($results, $error, $direction);
					return;
				}
			}
			$xref->setTreeLevel($currentLevel, $direction);
			$this->addToResults($results, $xref, $direction);
			$history[] = $xref;
			$xref->getXrefTreeRec($results, $history, $direction);
		}
		return;
	}

	public function exists()
	{
		return array_merge($this->getParentXrefs(), $this->getChildrenXrefs());
	}

	public function getParentRows($brandTo, $pnTo)
	{
		$query = <<<EOF
		SELECT *
		FROM parts_xref
		WHERE brand_to='$brandTo'
		AND pn_to='$pnTo'
EOF;
		$row = tldUtils::getSqlToAssocArray($query);
		return is_array($row) ? $row : [];
	}

	public function getChildrenRows($brandFrom, $pnFrom)
	{
		$query = <<<EOF
		SELECT *
		FROM parts_xref
		WHERE brand_from='$brandFrom'
		AND pn_from='$pnFrom'
EOF;
		$row = tldUtils::getSqlToAssocArray($query);
		return is_array($row) ? $row : [];
	}

	public function getParentXrefs()
	{
		$xrefRows = $this->getParentRows($this->itsBrandFrom, $this->itsPnFrom);
		if (count($xrefRows) == 0) {
			return;
		}
		foreach ($xrefRows as $xrefRow) {
			$result[] = new xref($xrefRow['brand_from'], $xrefRow['pn_from']);
		}
		return $result;
	}

	public function getChildrenXrefs()
	{
		$xrefRows = $this->getChildrenRows($this->itsBrandFrom, $this->itsPnFrom);
		if (count($xrefRows) == 0) {
			return;
		}
		foreach ($xrefRows as $xrefRow) {
			$result[] = new xref($xrefRow['brand_to'], $xrefRow['pn_to']);
		}
		return $result;
	}

	public function addToResults(&$results, $item, $direction = '')
	{
		if ($direction === 'rev') {
			array_unshift($results, $item);
		} else {
			$results[] = $item;
		}
	}

	public function isSame($xref)
	{

		return (strtoupper($xref->itsBrandFrom) == strtoupper($this->itsBrandFrom)
			&& strtoupper($xref->itsPnFrom) == strtoupper($this->itsPnFrom));
	}
}

/**
 * Class for handling PDF Documents
 *
 * @package Publications
 */
class tldOnlineDocumentPDF extends PDF_Chinese
{
	public $itsLMargin = 15;
	public $itsRMargin = 15;
	public $itsTMargin = 15;
	public $itsBMargin = 20;
	public $itsWidth;
	public $itsHeight;
	public $itsLang;
	/**
	 * Header
	 * @var array
	 */
	public $itsHeader;

	/**
	 * Log for debugging
	 * @var array
	 */
	public $itsLog;

    public $itsERP;

    public $itsOptions;
    public $itsId;
    public $itsDiagramUploadDir;
    public $itsDoc;
    public $itsStatus;
    public $itsPageType;
    public $itsPreviousPageType;

    /**
	 * tldOnlineDocumentPDF constructor
	 */
	public function __construct($id, $erp = '', $lang = 'en', $options = '')
	{
		global $HOME_DIR;
		$this->itsERP = $erp;
		$this->itsId = $id;
		$this->itsLang = strtoupper($lang);
		$this->itsOptions = $options;
		$this->itsLog = [];
		$this->itsDiagramUploadDir = "$GLOBALS[UPLOADS_PATH]/manuals_diagrams/";
		$this->itsDoc = new document($id, $erp, $lang);
		$this->doPageSetup();
		$this->doBooklet();
	}

	public function debug($email = null)
	{
		$this->itsLog['Diagrams']['Total File Size'] = $this->format_bytes($this->itsLog['Diagrams']['Total File Size']);
		$message = "Logged problems in class: tldOnlineDocument (Manual ID#{$this->itsId} ERP# {$this->itsERP} LANG: {$this->itsLang})\r\n\r\n" . print_r($this->itsLog, true);
		return error_log($message, (isset($email) AND !empty($email)) ? 1 : 0, $email);
	}

	/**
	 * Create the booklet
	 *
	 * @return integer page number of start of booklet
	 */
	public function doBooklet()
	{
		$this->SetTitle($this->translate('Booklet') . ' ' . $this->itsERP . '-' . $this->itsId);
		//JPG page
		$this->doDiagramPage();
		// setup the page number that will be used in the TOC for this Part
		$result = $this->PageNo();

		//table pages
		$this->doPartsTablePage();
		if ($this->PageNo() % 2 <> 0) {
			$this->doIntentionallyPage();
		}
		return $result;
	}

	/**
	 * Setup the page params
	 *
	 */
	public function doPageSetup()
	{
		parent::__construct('P', 'mm', 'Letter');

		if ($this->itsLang === 'CH') {
			parent::AddGBFont();
		}
		$this->SetAutoPageBreak(true, $this->itsBMargin);
		$this->itsWidth = $this->getPrintWidth();
		$this->itsHeight = $this->getPrintHeight();
		$this->AliasNbPages();
		$this->SetMargins($this->itsLMargin,
			$this->itsTMargin,
			$this->itsRMargin);
		$this->SetFont('', 'B', 10);
		$this->SetAuthor('Graham FONG');
		$this->SetDisplayMode('fullpage', 'TwoColumnRight');
	}

	public function translate($str, $show_en = true)
	{
		if ('EN' === $this->itsLang) {
			return $str;
		}
		static $translate;
		if (!$translate) {
			$charset = $this->itsLang === 'CH' ? 'GB2312' :  'ISO-8859-1';
			$translate = new tldTranslate($this->itsLang, $charset);
		}
		$text = $translate->getDictionary($str);
		if ($str === $text) {
			return $str;
		}
		if (!$show_en) {
			return $text;
		}
		return "$str/$text";
	}


	/**
	 * rotation function used to rotate the watermarks
	 *
	 * @param integer $angle
	 * @param integer $x
	 * @param integer $y
	 *
	 */
	public function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1) {
			$x = $this->x;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		if (($this->angle ?? null) != 0) {
			$this->_out('Q');
		}
		$this->angle = $angle;
		if ($angle != 0) {
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}


	/**
	 * doWatermark function used to add watermarks
	 *
	 * @param string $text
	 *
	 */
	public function doWatermark($text)
	{
		$y = 180;
		$rotate = 55;
		// Find X
		$len = strlen($text);
		$offset = ceil(($len * 7) / 2);
		$x = (-30) - $offset;
		$this->SetFont('', 'B', 50);
		$this->SetTextColor(230, 230, 230);
		$this->Rotate($rotate);
		$this->Text($x, $y, $text);
		$this->Rotate(0);
		$this->SetTextColor(0, 0, 0);
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
		if ($this->itsStatus === 'PRELIMINARY') {
			$text = $this->translate('PRELIMINARY', false);
			$text = $this->_spaceText($text);
			$this->doWatermark($text);
		}
		$result = $this->PageNo();
		//Go to 1.5 cm from bottom
		$this->SetY(50);
		$header = $this->itsDoc->itsHeader;
		$t = 'Part Number: ' . $header['factory_num'] . "\n" .
			$this->translate('Revision') . ': ' . $header['rev'] . "\n" .
			$this->translate('Description') . ': ' . $header['endescription'] . "\n" .
			$this->translate('Effective Date') . ': ' . $header['dt_created'] . "\n";

		$this->SetFont('', 'B', 24);
		$this->MultiCell(0, 24, $t);

		return $result;
	}

	/**
	 * Create blank intermediate page
	 *
	 */
	public function doIntentionallyPage()
	{
		//Cover page
		$this->AddPage();
		if ($this->itsStatus === 'PRELIMINARY') {
			$text = $this->translate('PRELIMINARY', false);
			$text = $this->_spaceText($text);
			$this->doWatermark($text);
		}
		$this->SetY(50);
		$this->SetFont('', 'B', 24);
		$this->MultiCell(0, 24,
			$this->translate('This page left intentionally blank'),
			0, 'C');
	}

	/**
	 * Create the diagram page
	 *
	 */
	public function doDiagramPage()
	{
		global $HOME_DIR;
		$header = $this->itsDoc->itsHeader;
		$filenamepath = document::getFilePath($GLOBALS['UPLOADS_PATH'], $header['diagram_filename']);
		if ($header['diagram_filename'] && null !== $filenamepath) {
			[$width, $height, $type, $attr] = $img = getimagesize($filenamepath);
			//set orientation
			//if $width > $height teh rotate the picture
			if ($width > $height) {
                $pathinfo = pathinfo($filenamepath);
				$pFilename = 'p_' . $pathinfo['basename'];
				$pPath = '/tmp/' . $pFilename;
				$src = imagecreatefromjpeg($filenamepath);
				$dest = imagerotate($src, 90, 0);
				imagejpeg($dest, $pPath);
				$file = new basicFile($pPath);
				$temp = $width;
				$width = $height;
				$height = $temp;
			} else {
				// else process as this.
				$file = new basicFile($filenamepath);
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
			if ($this->itsStatus === 'PRELIMINARY') {
				$text = $this->translate('PRELIMINARY', false);
				$text = $this->_spaceText($text);
				$this->doWatermark($text);
			}
			$size = filesize($filenamepath);
			$this->itsLog['Diagrams']['Total File Size'] += $size;
			$this->itsLog['Diagrams'][] = [
				'File Name' => $header['diagram_filename'],
				'File Size' => $this->format_bytes($size),
				'URL' => str_replace("$HOME_DIR/www.tld-gse.com", 'https://www.tld-gse.com', $filenamepath),
				'Doc#' => $header['id'],
				'Factory#' => $header['factory_num'],
				'Category' => $header['category'],
				'Description' => $header['endescription'],
				'Width' => "$width px",
				'Height' => "$height px",
				'Mime Type' => $img['mime'],
				'Channels' => $img['channels'],
			];
		} else {
			// if no pictures available, do empty page with watermark
			$this->AddPage();
			$result = $this->PageNo();
			$this->doWatermark($this->translate('SORRY, NO PICTURE', false));
		}
		return $result;
	}

	public function format_bytes($size)
	{
		$units = [' B', ' KB', ' MB', ' GB', ' TB'];
		for ($i = 0; $size >= 1024 && $i < 4; $i++) {
			$size /= 1024;
		}
		return round($size, 2) . $units[$i];
	}

	/**
	 * Do the parts table page
	 *
	 */
	public function doPartsTablePage()
	{
		$this->AddPage();
		if ($this->itsStatus === 'PRELIMINARY') {
			$text = $this->translate('PRELIMINARY', false);
			$text = $this->_spaceText($text);
			$this->doWatermark($text);
		}
		$this->SetFont('', 'B', 10);
		//table header
		$this->doPartsTableTitles();
		// Get Documents
		$rows = $this->itsDoc->itsDocAsArray;
		$done = [];
		foreach ($rows as $row) {
			// Check if doc already done
			if (in_array($row['pn'], $done)) {
				continue;
			}
			$done[] = $row['pn'];
			// Mark start coords for the first multicell (item)
			$x = $this->GetX();
			$y = $this->GetY();
			// set the variable $y2 to $y, this variable will be used to keep the biggest Y value, this value will be
			// used when starting a new row a multicell (next item line of the PDF)
			$y2 = $y;

			// do the 'Item' multicell
			$this->MultiCell(16, 6, $row['item'], 0, 'L');
			// get the end Y position of the multicell, if it is superior to the previous one them set $y2 to this one
			$y1 = $this->GetY();
			if ($y1 > $y2) {
				$y2 = $y1;
			}

			// setup the start coords of the 'pn' multicell
			$this->SetXY($x + 16, $y);
			// do the 'pn' multicell
			$this->MultiCell(32, 6, $row['pn'], 0, 'L');
			// get the end Y position of the multicell, if it is superior to the previous one them set $y2 to this one
			$y1 = $this->GetY();
			if ($y1 > $y2) {
				$y2 = $y1;
			}

			// setup the start coords of the 'qty' cell
			$this->SetXY($x + 48, $y);
			// do the 'qty' cell
			$this->Cell(12, 6, $row['qty'], 0, 0, 'C');
			// get the end Y position of the multicell, if it is superior to the previous one them set $y2 to this one
			$y1 = $this->GetY();
			if ($y1 > $y2) {
				$y2 = $y1;
			}

			// Mark start coords for the multicell (description)
			$x = $this->GetX();
			$y1 = $this->GetY();

			// MultiCell use for the description
			$desc = trim($row['en']);
			// If alt language
			if (!empty($row['fr'])) {
				switch ($this->itsLang) {
					case 'CH':
						$desc .= ' / ' . iconv('UTF-8', 'GB2312', trim($row['fr']));
						break;
					default:
						$desc .= ' / ' . trim($row['fr']);
						break;
				}
			}
			$this->MultiCell(100, 6, $desc, 0, 'L');

			// Mark End Y coord for the multicell (description)
			$y1 = $this->GetY();
			if ($y1 > $y2) {
				$y2 = $y1;
			}
			// set the height of the PMOC cells
			$yH = 6;
			// set the start coords of the PMOC cells
			$this->SetXY($x + 100, $y);

			if (!empty($row['group_p']) || $row['group_p'] === 'P') {
				$this->Cell(5, $yH, 'P', 0, 0, 'C');
			} else {
				$this->Cell(5, $yH, '', 0, 0, 'C');
			}
			if (!empty($row['group_m']) || $row['group_m'] === 'M') {
				$this->Cell(5, $yH, 'M', 0, 0, 'C');
			} else {
				$this->Cell(5, $yH, '', 0, 0, 'C');
			}
			if (!empty($row['group_o']) || $row['group_o'] === 'O') {
				$this->Cell(5, $yH, 'O', 0, 0, 'C');
			} else {
				$this->Cell(5, $yH, '', 0, 0, 'C');
			}
			if (!empty($row['group_c']) || $row['group_c'] === 'C') {
				$this->Cell(5, $yH, 'C', 0, 0, 'C');
			} else {
				$this->Cell(5, $yH, '', 0, 0, 'C');
			}
			// set the End coords of the line to the same height of the last line of the Description's MultiCell
			// $y2-6 was used because there seems to be an extra line when using the $y2 (the -6 was used to fix this problem).
			$this->SetXY($this->GetX(), $y2 - 6);
			if ($y2 + 20 > $this->itsHeight) {
				$this->doDiagramPage();
				$this->AddPage();
				if ($this->itsStatus === 'PRELIMINARY') {
					$text = $this->translate('PRELIMINARY', false);
					$text = $this->_spaceText($text);
					$this->doWatermark($text);
				}
				$this->doPartsTableTitles();
				$this->SetFont('', 'B', 10);
			} else {
				$this->ln();
			}
		}
	}

	/**
	 * Put the titles on the parts table
	 *
	 */
	public function doPartsTableTitles()
	{
		// set the fonts size and style
		$this->SetFont('', 'B', 8);
		//add top title line
		$y = $this->GetY();
		$this->Line($this->itsLMargin, $y, $this->getPrintWidth() + $this->itsLMargin, $y);
		//do the table titles
		$this->Cell(10, 6, $this->translate('Item', false), 0, 0, 'C');
		$this->Cell(36, 6, $this->translate('PN', false), 0, 0, 'C');
		$this->Cell(14, 6, $this->translate('Qty', false), 0, 0, 'C');
		$this->Cell(100, 6, $this->translate('Description', false), 0, 0, 'C');
		$this->Cell(5, 6, 'P', 0, 0, 'C');
		$this->Cell(5, 6, 'M', 0, 0, 'C');
		$this->Cell(5, 6, 'O', 0, 0, 'C');
		$this->Cell(5, 6, 'C', 0, 0, 'C');
		$this->ln();
		$y = $this->GetY();
		// add bottom title line
		$this->Line($this->itsLMargin, $y, $this->getPrintWidth() + $this->itsLMargin, $y);
	}
	//find dimensions of page

	/**
	 * Get the page width
	 *
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
	 *
	 */
	public function Header()
	{
		global $HOME_DIR;
		$header = $this->itsDoc->itsHeader;
		switch ($this->itsPageType) {
			case 'blankPage':
				break;
			case 'logoOnly':
				$this->Image("$GLOBALS[SHARED_PATH]/icons/tld-icon.jpg",
					$this->itsWidth - 5,
					$this->itsTMargin);
				break;
			default:
				$this->Image("$GLOBALS[SHARED_PATH]/icons/tld-icon.jpg",
					$this->itsWidth - 5,
					$this->itsTMargin);
				$this->SetFont('Arial', 'B', 14);
				$desc = trim($header['endescription']);
				// Encode descriptions regarding lang
				if (!empty($header['frdescription'])) {
					switch ($this->itsLang) {
						case 'CH':
							$desc .= ' / ' . iconv('UTF-8', 'GB2312', trim($header['frdescription']));
							break;
						default:
							$desc .= ' / ' . trim($header['frdescription']);
							break;
					}
				}
				$this->MultiCell($this->itsWidth - 5, 6,
					$header['factory_num'] . '_' . $header['rev'] . "\n" .
					$desc);
		}
		$this->SetY(40);
	}

	/**
	 * Page level Footer
	 *
	 */
	public function Footer()
	{
		switch ($this->itsPreviousPageType) {
			case 'blankPage':
			case 'logoOnly':
			case 'noFooter':
				;
				break;
			default:
				//Go to 1.5 cm from bottom
				$this->SetY(-15);
				//Select Arial italic 8
				$this->SetFont('', 'B', 10);
				//Print centered page number
				$this->Cell(0, 10, $this->translate('Page') . ' ' . $this->PageNo(),
					0, 0, 'C');
		}
		$this->itsPreviousPageType = $this->itsPageType;
	}

	public function setPageType($type)
	{
		$this->itsPreviousPageType = $this->itsPageType;
		$this->itsPageType = $type;
	}

	public function SetFont($family = '', $style = '', $size = 0)
	{
		return parent::SetFont( $this->itsLang === 'CH' ? 'GB' : 'Arial', $style, $size);
	}

	public function _spaceText($string)
	{
		if ($this->itsLang === 'CH') {
			$string = iconv('GB2312', 'UTF-8', $string);
		}
		$array = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
		$string = implode(' ', $array);
		if ($this->itsLang === 'CH') {
			$string = iconv('UTF-8', 'GB2312', $string);
		}
		return $string;
	}
}

/**
 * Class for handling PDF manuals
 *
 * @package Publications
 */
class tldOnlineManualPDF extends tldOnlineDocumentPDF
{
    public $itsManual;

	/**
	 * tldOnlineManualPDF constructor
	 *
	 */
	public function __construct($id, $erp = '', $lang = 'en')
	{
		$this->itsId = $id;
		$this->itsERP = $erp;
        $this->itsLang = strtoupper(manual::getIso2Language($lang));
		$this->itsManual = new manual($id, $erp, $lang);
		$this->itsStatus = $this->itsManual->itsHeader['status'];
		$this->doPageSetup();
		$this->doPartsbook();
	}

	/**
	 * Create the partsbook
	 *
	 */
	public function doPartsbook()
	{
		$rows = $this->itsManual->getDetails();
		if (count($rows) == 0) {
			return false;
		}
		$this->SetTitle("Manual $this->itsERP-$this->itsId");

		$done = [];
		foreach ($rows as $row) {
			if ($row['doc_type'] === 'PARTS DIAGRAM') {
				//if this pn has already been done then skip to next
				if (in_array($row['factory_num'], $done)) {
					continue;
				}
				$done[] = $row['factory_num'];
				$this->itsDoc = new document(
					$row['id'], $this->itsERP, $this->itsLang
				);
				$header = $this->itsDoc->getHeader();
				$header['page'] = $this->doBooklet();

				$toc[$row['category']][$row['factory_num']] = $header;
			}
		}

		$this->doTableOfContentsPage($toc);
	}

	/**
	 * Create the TOC page
	 *
	 */
	public function doTableOfContentsPage($rows)
	{
		global $HOME_DIR;
		//turn off the header and footer
		//record at what page we started so we can move everything
		//to the front later
		$start = $this->PageNo();
		//Cover page
		$this->setPageType('logoOnly');
		$this->AddPage();
		if ($this->itsStatus === 'PRELIMINARY') {
			$text = $this->translate('PRELIMINARY', false);
			$text = $this->_spaceText($text);
			$this->doWatermark($text);
		}
		//Go to 1.5 cm from bottom
		$this->SetY(40);
		// Cleanup Manual description
		$ManDesc = str_replace(['<br>', '<br/>'], "\n", $this->itsManual->itsHeader['description']);
		$t =
			$this->translate('Chapter 4 - Illustrated Parts List') . "\n" .
			$this->translate('Model') . ': ' . $this->itsManual->itsHeader['model'] . "\n" .
			$this->translate('Description') . ': ' . $ManDesc . "\n" .
			$this->translate('Published Date') . ': ' . $this->itsManual->itsHeader['date'] . "\n" .
			$this->translate('Language') . ': ' . $this->itsManual->itsHeader['lang'] . "\n";
		$this->SetFont('', 'B', 18);
		$this->MultiCell(0, 24,
			$t,
			0, 'C');
		//add blank page
		if ($this->PageNo() % 2 <> 0) {
			$this->setPageType('blankPage');
			$this->AddPage();
			if ($this->itsStatus === 'PRELIMINARY') {
				$text = $this->translate('PRELIMINARY', false);
				$text = $this->_spaceText($text);
				$this->doWatermark($text);
			}
		}
		//do TOC
		$this->setPageType('logoOnly');
		$this->AddPage();
		if ($this->itsStatus === 'PRELIMINARY') {
			$text = $this->translate('PRELIMINARY', false);
			$text = $this->_spaceText($text);
			$this->doWatermark($text);
		}
		$this->Image("$GLOBALS[SHARED_PATH]/icons/tld-icon.jpg",
			$this->itsWidth - 5,
			$this->itsTMargin
		);
		$this->SetFont('', 'B', 24);
		$this->SetY($this->itsTMargin);
		$this->MultiCell($this->itsWidth - 5, 6, $this->translate('Table of Contents'), 0, 'C');
		$this->SetY(40);

		$this->SetFont('', 'B', 10);
		//table header

		$this->Cell(35, 6, $this->translate('Part Number', false), 'TB', 0, 'R');
		$this->Cell($this->itsWidth - 70, 6, $this->translate('Description', false), 'TB', 0, 'C');
		$this->Cell(0, 6, $this->translate('Page Number', false), 'TB', 0, 'R');
		$this->ln();

		foreach ((array)$rows as $category => $cat) {
			$this->Cell(30, 6, $this->translate($category), 0, 0, 'L');
			$this->ln();
			foreach ($cat as $row) {
				$this->SetX(20);
				$this->Cell(20, 6, $row['factory_num'], 0, 'L', 0);
				// Mark start coords for the multicell (description)
				// MultiCell use for the description (due to French case, description for Europe display En description and FR is available)
				$x = $this->GetX();
				$y1 = $this->GetY();
				$desc = trim($row['endescription']);
				// Encode descriptions regarding lang
				if (!empty($row['frdescription'])) {
					switch ($this->itsLang) {
						case 'CH':
							$desc .= ' / ' . iconv('UTF-8', 'GB2312', trim($row['frdescription']));
							break;
						default:
							$desc .= ' / ' . trim($row['frdescription']);
							break;
					}
				}
				$this->MultiCell($this->itsWidth - 45, 6, $desc, 0, 'L', 0);
				// Mark End Y coord for the multicell (description)
				$y2 = $this->GetY();
				// set the start Y coord of the page cell
				$this->SetY($y1);
				$this->Cell(0, 6, $row['page'], 0, 0, 'R');
				// set the End Y coord
				$this->SetY($y2 - 6);
				$this->ln();
			}
		}
		if ($this->PageNo() % 2 <> 1) {
			$this->setPageType('blankPage');
			$this->AddPage();
			if ($this->itsStatus === 'PRELIMINARY') {
				$text = $this->translate('PRELIMINARY', false);
				$text = $this->_spaceText($text);
				$this->doWatermark($text);
			}
		}
		/*
         * NOTE: fpdf indexes $this->pages starting with 1 NOT zero
         */
		//move the pages to the front of the manual
		for ($i = $this->PageNo(); $i > $start; $i--) {
			//get page from end
			$t = array_pop($this->pages);
			//stick it on the front
			array_unshift($this->pages, $t);
		}
		array_unshift($this->pages, 'empty');
	}

	/**
	 * Change status of a manual (RELEASED or PRELIMINARY)
	 *
	 * @param string $status string text of status to change to
	 *
	 */
	public function changeStatus($status)
	{

		$query = <<<EOF
			UPDATE manuals
			SET status='$status'
			WHERE id=$this->itsID
			LIMIT 1
EOF;
		return tldUtils::sqlQuery($query);

	}
}
