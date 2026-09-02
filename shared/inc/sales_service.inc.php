<?php
/**
 *    Sales and Service related classes
 *
 * @package   SalesAndService
 * @desc      All classes related to Sales and Service are kept in this file
 * @access    public
 * @author    Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 */

use ApiBundle\Client;
use GuzzleHttp\Exception\ClientException;
use Shared\Provider\Common\AirportProvider;
use Shared\Provider\Manufacturing\EquipmentRecordProvider;

require_once('XML/Unserializer.php');
include_once('user.inc.php');
include_once('dms.inc.php');

$constantClassHack = "interface HackCatalogueInterface {
    const CATALOGUE_FILE_PATH = '%s/sales_catalogue';
}";

eval(sprintf($constantClassHack, $GLOBALS['UPLOADS_PATH']));

/**
 * Class for logging/getting customer feedback from Extranet and tld-group.com
 *
 * @package SalesAndService
 */
class tldCustomerFeedback
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Generic method to get the FROM query part
     *
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM customer_feedback
    LEFT JOIN customers ON customer_feedback.customer_id=customers.id
    LEFT JOIN extranet_users ON customer_feedback.ext_user_id=extranet_users.id
EOF;
    }

    /**
     * Generic method to get the SELECT query part
     *
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    customer_feedback.*, customers.customer_name as customer_fullname
EOF;
    }

    /**
     * Generic method by constraints
     *
     * @param mixed string or array $constraints
     * @param array|string $opt
     *
     * @return array
     */
    public static function byConstraints($constraints, $opt = [])
    {
        // Construct constraints
        if (is_array($constraints)) {
            $HAVING = tldUtils::constructWhere($constraints);
        } else {
            $HAVING = $constraints;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        if (isset($opt['orderBy'])) {
            $ORDERBY = TldDatabase::escape($opt['orderBy']);
        } else {
            $ORDERBY = 'id DESC';
        }
        $SELECT_extra = '';
        if (isset($opt['select'])) {
            $SELECT_extra = ',' . $opt['select'];
        }
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

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader(): array
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE customer_feedback.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Delete a customer feedback
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = "DELETE FROM customer_feedback WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Create a new customer feedback row in the database
     *
     * @param array $p
     *
     * @return boolean
     */
    public static function insert($p)
    {
        $fields = [
            'source',
            'recipients',
            'subject',
            'customer_name',
            'customer_id',
            'ext_user_id',
            'name',
            'email',
            'title',
            'country',
            'phone',
            'er_sn',
            'message',
        ];
        $query = 'INSERT INTO customer_feedback SET dt=NOW(),';
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Generic customer_feedback update method
     *
     * @param              $data   array of esr datas
     * @param array|string $fields array of esr fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE customer_feedback SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Add a comment to the log
     *
     * @param     $id
     * @param     $comment
     * @param int $num_log
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'FEEDBACK';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;

        return tldModLog::insert($a);
    }

    /** Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'FEEDBACK');
    }

    /**
     * Get the latest customer feedbacks, defaults to last 20
     *
     * @param string $opt
     * @param integer $qty
     *
     * @return array
     */
    public static function byLatest($opt = '', $qty = 20)
    {
        $WHERE = '';
        if (!empty($opt)) {
            switch ($opt) {
                case 'byEXT':
                    $WHERE = "WHERE source = 'EXTRANET'";
                    break;
                case 'byGROUP':
                    $WHERE = "WHERE source = 'TLD-GROUP.COM' AND subject IN('Customer Satisfaction Feedback')";
                    break;
            }
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$WHERE
ORDER BY id DESC
LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

}

/**
 * Class for getting datasheet infotldGroup
 *
 * @package SalesAndService
 */
class tldDatasheet implements HackCatalogueInterface
{
    /**
     * ID of datasheet in datasheet table
     *
     * @var integer
     */
    public $itsID;
    public $itsLanguages = ['en', 'fr', 'de', 'pt', 'zh', 'es', 'ru'];
    public $itsTypes = [
        'Datasheet' => 'pdf',
        'Options' => 'pdf',
        'Photos' => 'pdf',
        'Configurator' => 'pdf',
        'Specs' => 'pdf',
        'Presentation' => 'ppt',
    ];

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsDetails = $this->getHeader();
        $this->theFileDir = self::CATALOGUE_FILE_PATH;
    }

    /**
     * @deprecated
     */
    public function addDocument($data)
    {
        throw new Exception('This is not used anymore');
    }

    public function getDocumentTypes()
    {
        return [
            'Datasheet',
            'Options',
            'Photos',
            'Configurator',
            'Specs',
            'Presentation',
        ];
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'DATASHEET');
    }

    public function isEmpty()
    {
        $header = $this->getHeader();
        $detail = $this->getDetail();

        return empty($header) && empty($detail);
    }

    public function getHeader()
    {
        $id = $this->itsID;
        $query = <<<EOF
			SELECT products_datasheets.*,products_categories.en AS category, products_datasheets_dms.dms_id AS dms_id
			FROM products_datasheets 
			    LEFT JOIN products_categories ON products_datasheets.parent_id=products_categories.id
			    LEFT JOIN products_datasheets_dms ON products_datasheets_dms.parent_id = products_datasheets.id AND products_datasheets_dms.type = "Photos"
			WHERE products_datasheets.id=$id
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getHistory()
    {
        $query = <<<EOF
			SELECT *
			FROM products_history
			WHERE parent_id=$this->itsID
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get models in category $id
     *
     * @param string $id
     * @param string $option
     *
     * @return array of db rows
     */
    public static function getModels($id = '', $option = '')
    {
        $WHERE = '';
        if ($id) {
            $WHERE = "WHERE p.parent_id=$id";
        }
        if ($option === 'PublicOnly') {
            $WHERE .= ' AND public = 1';
        }
        $query = <<<EOF
			SELECT p.*, dms.dms_id AS dms_id
			FROM products_datasheets p
			    LEFT JOIN products_datasheets_dms dms ON p.id = dms.parent_id AND dms.type = "Photos"
            $WHERE AND hidden = 0 
			GROUP BY p.id
            ORDER BY model
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getFilepath($type, $lang)
    {
        return $this->theFileDir . "/$lang/" .
            str_replace(' ', '_', $this->itsDetails['model']) . '_' . strtolower($type) . '.' . $this->itsTypes[$type];
    }

    public function getExtrasFilepath($type, $lang)
    {
        return $this->theFileDir . "/$lang/" .
            str_replace(' ', '_', $this->itsDetails['model']) . '_' . strtolower($type) . '.' . $this->itsTypes[$type];
    }

    /* Get last revision log for each DMS of the unit */
    public function getDMSHistory()
    {
        $id = $this->itsID;
        $query = <<<EOF
            SELECT 
            dms.id,
            dms.title,
            dms_revision.dt,
            dms_revision.purpose
            FROM products_datasheets_dms
            LEFT JOIN products_datasheets ON products_datasheets.id = products_datasheets_dms.parent_id
            LEFT JOIN dms ON dms.id = products_datasheets_dms.dms_id
            LEFT JOIN dms_revision ON dms_revision.parent_id = dms.id
            LEFT JOIN dms_revision as rev ON rev.parent_id = dms.id AND rev.dt > dms_revision.dt
            WHERE products_datasheets_dms.parent_id = $id AND rev.id IS NULL
            ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getDocument($id)
    {
        return current($this->getDocuments(['products_datasheets_dms.id' => $id]));
    }

    public function getDocuments($constraints = null)
    {
        $WHERE = null;
        if (is_array($constraints)) {
            $WHERE = ' AND ' . tldUtils::constructWhere($constraints);
        }
        $id = $this->itsID;
        $query = <<<EOF
SELECT
    products_datasheets_dms.id,
    dms.id AS dms_id,
    CONCAT(people.firstname,' ',people.lastname) AS ownerFullname,
    dms.title,
    dms.status,
    dms.dt_act,
    dms.lang,
    products_datasheets_dms.type
FROM products_datasheets_dms
    LEFT JOIN products_datasheets ON products_datasheets.id = products_datasheets_dms.parent_id
    LEFT JOIN dms ON dms.id = products_datasheets_dms.dms_id
    LEFT JOIN people ON people.id = dms.owner_id
WHERE
    products_datasheets_dms.parent_id = $id
    $WHERE
ORDER BY
    type, lang, title
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @deprecated
     */
    public function updateDocument($doc_id, $a)
    {
        throw new Exception('This is not used anymore');
    }

    /**
     * @deprecated
     */
    public function removeDocument($doc_id)
    {
        throw new Exception('This is not used anymore');
    }

    public function getFileMatrix()
    {
        $result['languages'] = $this->itsLanguages;
        $result['types'] = $this->itsTypes;
        $i = 0;
        foreach ($result['types'] as $doc_type => $extension) {
            // Spécific check for Datasheet
            if ($doc_type === 'Datasheet') {
                // check if there is a DMS with EN language
                $dmsList = $this->getDocuments(['type' => 'Datasheet']);
                $langList = array_column($dmsList, 'lang');
                // If so, do not make available nay legacy datasheet files in favor of DMS
                if (in_array('en', $langList)) {
                    return;
                }
            }
            foreach ($result['languages'] as $lang) {
                $file = $this->getFilepath($doc_type, $lang);
                if (file_exists($file)) {
                    $filename = array_reverse(explode('/', $file));
                    $result['data'][$doc_type][$lang]['file'] = $file;
                    $result['data'][$doc_type][$lang]['filename'] = $filename[0];
                    $result['data'][$doc_type][$lang]['index'] = $i;
                    $i++;
                }
            }
        }
        //search for other files
        $result['types']['Extras'] = '';
        foreach ($result['languages'] as $lang) {
            $extrasFolder = $this->theFileDir . "/$lang/" . str_replace(' ', '_', $this->itsDetails['model']);
            if (is_dir($extrasFolder)) {
                if ($dh = opendir($extrasFolder)) {
                    $fileNameIndex = [];
                    while (($file = readdir($dh)) !== false) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        if (!in_array($file, $fileNameIndex)) {
                            $fileNameIndex[] = $file;
                        }
                        $result ['data']['Extras'][array_search(
                            $file,
                            $fileNameIndex
                        )][$lang]['file'] = "$extrasFolder/$file";
                        $result ['data']['Extras'][array_search($file, $fileNameIndex)][$lang]['filename'] = $file;
                        $result ['data']['Extras'][array_search($file, $fileNameIndex)][$lang]['index'] = $i;
                        $i++;
                    }
                    closedir($dh);
                }
            }
        }

        return $result;
    }

    /**
     * Get header of datasheet by model name
     *
     * @param string $model
     *
     * @param string $option
     *
     * @return mixed string error or array
     */
    public static function byModel($model, $option = '')
    {
        if (empty($model)) {
            return;
        }
        $WHERE = '';
        if ($option === 'PublicOnly') {
            $WHERE = ' AND public = 1';
        }
        $model = TldDatabase::escape($model);
        $query = <<<EOF
			SELECT *
			FROM products_datasheets
            WHERE model LIKE '$model' $WHERE
            LIMIT 1
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }
}

/**
 * Class for getting catalogue info
 *
 * @package SalesAndService
 */
class tldCatalogue implements HackCatalogueInterface
{
    public $itsLanguages = ['en', 'fr', 'de', 'pt', 'zh', 'es'];
    private $theFileDir;

    public function __construct()
    {
        $this->theFileDir = self::CATALOGUE_FILE_PATH;
    }

    /**
     * Get distinct categories used in table
     *
     * Returns categories for all or for product specified by $id
     *
     * @param int|string $id
     *
     * @return array
     */
    public static function getCategories($id = '')
    {
        $WHERE = "WHERE public <> 0";
        if ($id) {
            $WHERE .= " AND id=$id";
        }
        $query = <<<EOF
			SELECT *
			FROM products_categories
            $WHERE
			ORDER BY en
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);

        return $rows ?: [];
    }

    public function getCategory($id)
    {
        $categories = self::getCategories($id);

        return $categories[0];
    }

    public static function getTypeModelList(bool $includeHidden = false)
    {
        $hideCondition = $includeHidden ? '' : 'AND T2.hide = 0';

        $query = <<<EOF
        SELECT 
            T1.id AS catid, 
            T1.en AS type,
            T2.id AS modelid, 
            T2.model
        FROM products_categories AS T1, models AS T2
        WHERE T2.parent_id = T1.id
        $hideCondition
        ORDER BY T1.en, T2.model
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getModelById($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return;
        }
        $query = <<<EOF
            SELECT *
			FROM models
			WHERE id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getCategoryFiles($id)
    {
        $category = $this->getCategory($id);
        $i = 0;
        $result['languages'] = $this->itsLanguages;
        foreach ($this->itsLanguages as $lang) {
            $file = $this->theFileDir . "/$lang/" . strtolower(str_replace(' ', '_', $category['en'])) . '_presentation.ppt';
            if (file_exists($file)) {
                $result['data'][$lang]['file'] = $file;
                $result['data'][$lang]['index'] = $i;
                $i++;
            }
        }

        return $result;
    }
}

/**
 * Class for accessing and manipulating Competitor data
 *
 * @package SalesAndService
 */
class tldCOR
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
            SELECT *
            FROM cor
            WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getURL()
    {
        return $this->itsHeader['url'];
    }

    public function getProducts()
    {
        $query = <<<EOF
		SELECT *
		FROM cor_prod
		WHERE parent_id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    //static functions
    public function byType($type)
    {
        $query = <<<EOF
		SELECT DISTINCT T1.id,T1.*
		FROM cor AS T1,cor_prod AS T2
		WHERE T1.id=T2.parent_id AND type='$type'
		ORDER BY T1.company_name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byLatest($num = 10)
    {
        $query = <<<EOF
		SELECT T1.*
		FROM cor AS T1
		ORDER BY id DESC
		LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Method to get competitor by constraints
     *
     * @param array $a
     * @param string $format
     *
     * @return array
     */
    public static function byConstraints($a, $format = '')
    {
        $WHERE = tldUtils::constructWhere($a);
        $query = <<<EOF
            SELECT *
            FROM cor
            WHERE $WHERE
			ORDER BY company_name
EOF;
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray(
                    $query,
                    'smartyOptions',
                    ['id', 'company_name']
                );
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
                break;
        }

        return $result;
    }

    public static function getList($format = '')
    {
        $query = <<<EOF
            SELECT * FROM cor
			ORDER BY company_name
EOF;
        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray(
                    $query,
                    'smartyOptions',
                    ['id', 'company_name']
                );
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }

        return $result;
    }
}

/**
 * Class for accessing and manipulating Customer data
 *
 * @package SalesAndService
 */
class tldCustomer
{

    public function __construct($id)
    {
        //search by name if id is not numeric
        if (!is_numeric($id)) {
            $this->itsHeader = $this->getHeader($id);
            if (!empty($this->itsHeader)) {
                $this->itsID = $this->itsHeader['id'];
            }
        } else {
            $this->itsID = $id;
            $this->itsHeader = $this->getHeader();
        }
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getAsmID()
    {
        return $this->itsHeader['asm_id'];
    }

    public function isMilitary()
    {
        return $this->itsHeader['type'] === 'Military';
    }

    public function isApproved()
    {
        return (bool) $this->itsHeader['approved'];
    }

    public function isMultiCUNO()
    {
        return $this->itsHeader['numCUNO'] > 1;
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getLogoFilePath()
    {
        return '/en/private/uploads/customers/' . $this->itsHeader['logo_file'];
    }

    public function isLogoEmpty()
    {
        return empty($this->itsHeader['logo_file']);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    customers.*,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE customers.asm_id=people.id
    ) AS asm_fullname,
    (SELECT COUNT(crt.id) FROM customers_crt AS crt
        WHERE crt.customer_id=customers.id
    ) AS numCUNO,
    (SELECT name FROM countries
        WHERE id=customers.ctry_id
    ) AS country
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM customers
EOF;
    }

    /**
     * Get header information from database
     *
     * @param string $name
     *
     * @return array
     */
    public function getHeader($name = '')
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        if ($name) {
            $WHERE = " WHERE customer_name LIKE '$name'";
        } elseif ($this->itsID) {
            $WHERE = " WHERE customers.id=$this->itsID";
        } else {
            return [];
        }
        $query = <<<EOF
$SELECT
$FROM
$WHERE
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Add log entry
     *
     * @param int $id poster
     * @param string $comment
     *
     * @return mixed int or string on error
     */
    public function addLogEntry($id, $comment)
    {
        global $kernel;

        $client = $kernel->getContainer()->get(Client::class);
        $customer = $client->findOneBy('sales/customers', ['legacyId' => $this->itsID]);

        try {
            $log = $client->post('comments', [
                'json' => [
                    'resource' => $customer['@id'],
                    'message' => $comment,
                ],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return 'ERROR: Could not add a log. Reason: ' . $errors['hydra:description'];
        }

        return $log['legacyId'];
    }

    /**
     * Get log
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CUNO');
    }

    /**
     * @deprecated
     */
    public function addChild($cid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function deleteChild($cid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get list of customer child
     *
     * @return array of rows
     */
    public function getChildList()
    {
        if (empty($this->itsID)) {
            return;
        }
        $a = ['parent_id' => $this->itsID];

        return self::byConstraints($a);
    }

    /**
     * Get child tree recursively
     *
     * @param array $result (passed by reference)
     *
     * @param int $level
     *
     * @return string|void
     */
    public function getChildListRec(&$result, $level = 0)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        $level++;
        $rows = $this->getChildList();
        if (!count($rows)) {
            return;
        }
        foreach ($rows as $row) {
            $result[$row['id']] = ['customer' => $row, 'level' => $level];
            $cust = new tldCustomer($row['id']);
            if ($level <= 50) {
                $cust->getChildListRec($result, $level);
            }
        }
    }

    /**
     * Get the root parent ID if the customer family
     *
     * @return int customer ID
     */
    public function getParentCustomerIDFamily()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        // Get first parent
        $parent_id = (int)$this->getParentID();
        if ($parent_id === 0) {
            return $this->getID();
        }
        // if first parent found, loop and search parent until we found the last one
        while ($parent_id !== 0) {
            $cust = new tldCustomer($parent_id);
            if ($cust->isEmpty()) {
                break;
            }
            $parent_id = (int)$cust->getParentID();
        }

        return $cust->getID();
    }

    /**
     * Get list of customer tree of the family
     *
     * @return array of rows
     */
    public function getCustomerFamilyTree()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        // Get root parent of the family
        $rootParentID = $this->getParentCustomerIDFamily();
        $parent = new tldCustomer($rootParentID);
        $result[$parent->getID()] = ['customer' => $parent->itsHeader, 'level' => 0];
        // Loop until we found all childs
        $parent->getChildListRec($result);

        return $result;
    }

    /**
     * Check if a specific customer ID is part of the family
     *
     * @param int $cid
     *
     * @return boolean
     */
    public function isInFamily($cid)
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }
        $listFamilyID = array_keys($this->getCustomerFamilyTree());
        if (in_array($cid, $listFamilyID)) {
            return true;
        }

        return false;
    }

    /**
     * Get the customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->itsHeader['customer_name'];
    }

    public function getContactList()
    {
        return extranetUser::byCustomerName($this->getCustomerName());
    }

    public function getArchivedContactList()
    {
        return extranetUser::archivedByCustomerName($this->getCustomerName());
    }

    public function getCUNOList()
    {
        $query = <<<EOF
		SELECT crt.*,
			(SELECT locations.erp FROM locations
				WHERE locations.id=crt.erp_location_id
			) AS erp
		FROM customers AS cust
			LEFT JOIN customers_crt AS crt ON cust.id=crt.customer_id
		WHERE cust.id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Customer Relationship Team
     *
     * @return mixed array or string error
     */
    public function getCRT()
    {
        if (empty($this->itsID)) {
            return 'Not object context';
        }

        return tldCRT::byCustomerID($this->itsID);
    }

    public function getRepListFromCRTByTypeBySSO($type, $ssoid)
    {
        // init
        $allowedRepType = [
            'services' => 'services_rep_id',
            'sales' => 'sales_rep_id',
            'parts' => 'parts_rep_id',
        ];
        $reps = [];
        // look for types requested
        if (!in_array($type, array_keys($allowedRepType))) {
            return;
        }
        // get CRT list
        $crtList = $this->getCRT();
        // Get reps
        foreach ($crtList as $crt) {
            if ($ssoid <> $crt['erp_location_id']) {
                continue;
            }
            if ($crt[$allowedRepType[$type]]) {
                $reps[] = $crt[$allowedRepType[$type]];
            }
        }
        if (!count($reps)) {
            return;
        }
        // return results
        $constraints = "id IN('" . implode(',', $reps) . "')";

        return tldUser::byConstraints($constraints);
    }

    public static function getAsmList($options = ['smartyOptions' => 1])
    {
        return tldGroup::getUserListByMultipleGroup(
            ['role_ASM', 'role_SA', 'gg_SALES', 'gg_SERVICE', 'gg_SALES_AGENTS'],
            null,
            $options
        );
    }

    /**
     * Get customer list
     *
     * @param string $format
     *
     * @param string $option
     *
     * @return array of db rows
     */
    public static function getList($format = '', $option = '')
    {
        $WHERE = 'WHERE 1 = 1';
        if (!empty($option['byASM'])) {
            $WHERE .= ' AND asm_id = ' . $option['byASM'];
        }
        if ($option === 'ExcludeInventory') {
            $WHERE .= " AND customer_name NOT LIKE '**%**'";
        }
        $query = <<<EOF
            SELECT * FROM customers
            $WHERE AND customers.deleted_at IS NULL
			ORDER BY customer_name
EOF;

        switch ($format) {
            case 'smartyOptions':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'customer_name']);
                break;
            case 'smartyOptionsCust_name':
                $result = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['customer_name', 'customer_name']);
                break;
            default:
                $result = tldUtils::getSqlToAssocArray($query);
        }

        return $result;
    }

    /**
     * @throws Exception
     * @deprecated
     */
    public static function getActiveListByBUs(array $buList = []) {
        throw new Exception('Not used anymore');
    }

    public static function getActiveListByASM($asm, $format = '', $options = [])
    {
        if (empty($asm)) {
            return 'ASM #ID is missing';
        }
        $sfr = $toc = $sor = $join = '';
        if (array_key_exists('constraintsSFR', $options)) {
            $sfr = " AND {$options['constraintsSFR']} ";
        }
        if (array_key_exists('constraintsTOC', $options)) {
            $toc = " AND {$options['constraintsTOC']} ";
        }
        if (array_key_exists('constraintsSOR', $options)) {
            $sor = " AND {$options['constraintsSOR']} ";
        }

        $queryActiveCustomersSfr = <<<EOF
            SELECT DISTINCT customers.*
            FROM customers
              INNER JOIN sfr ON sfr.buyer_customer_id = customers.id OR sfr.user_customer_id = customers.id OR sfr.cust_nama = customers.customer_name
            WHERE
              customers.asm_id = $asm AND
              (sfr.status IN ('BUDGET','IN_PROGRESS','DELAYED') OR
              DATEDIFF(sfr.dt_closed,NOW()) > -28) $sfr
            GROUP BY customers.id
EOF;
        $queryActiveCustomersToc = <<<EOF
            SELECT DISTINCT customers.*
            FROM customers
              INNER JOIN toc ON toc.cuid = customers.id
            WHERE
              customers.asm_id = $asm AND
              (toc.dt_closed='0000-00-00' OR
              DATEDIFF(toc.dt_closed,NOW()) > -28) $toc
            GROUP BY customers.id
EOF;

        $queryActiveCustomersSor = <<<EOF
            SELECT DISTINCT customers.*
            FROM customers
              INNER JOIN sor ON sor.buyer_customer_id = customers.id
              LEFT JOIN sor_lines AS sol ON sor.id=sol.parent_id
              LEFT JOIN sor_units ON sol.id=sor_units.parent_id
              LEFT JOIN service AS er ON sor_units.id=er.sor_uid
            WHERE
              customers.asm_id = $asm AND
              (sor.dt_closed='0000-00-00' OR
              DATEDIFF(sor.dt_closed,NOW()) > -28) $sor
            GROUP BY customers.id
EOF;
        $choiceFormat = $option = '';
        if ($format === 'smartyOptions') {
            $choiceFormat = 'smartyOptions';
            $option = ['id', 'customer_name'];
        }

        $activeCustomersListSfr = tldUtils::getSqlToAssocArray($queryActiveCustomersSfr, $choiceFormat, $option);
        $activeCustomersListToc = tldUtils::getSqlToAssocArray($queryActiveCustomersToc, $choiceFormat, $option);
        $activeCustomersListSor = tldUtils::getSqlToAssocArray($queryActiveCustomersSor, $choiceFormat, $option);

        if ($format !== '') {
            $activeCustomersList = $activeCustomersListSfr + $activeCustomersListToc + $activeCustomersListSor;
            asort($activeCustomersList);

            return $activeCustomersList;
        }

        $result = array_merge($activeCustomersListSfr, $activeCustomersListToc, $activeCustomersListSor);
        $activeCustomersList = array_reduce($result, function ($memo, $customer) {
            if (!array_key_exists($customer['id'], $memo)) {
                $memo[$customer['id']] = $customer;
            }
            return $memo;
        }, []);

        $filterByNames = [];
        foreach ($activeCustomersList as $key => $row) {
            $filterByNames[$key] = $row['customer_name'];
        }
        array_multisort($filterByNames, SORT_ASC, $activeCustomersList);

        return $activeCustomersList;
    }

    /**
     * Get customers by num of users
     *
     * @param int $num
     *
     * @return array of db rows
     */
    public static function byLatest($num = 10)
    {
        if (!is_numeric($num)) {
            return;
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
ORDER BY id DESC
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get customers by customer type
     *
     * @param string $type
     *
     * @return array of db rows
     *
     */
    public static function byType($type)
    {
        $a = ['type' => $type];

        return self::byConstraints($a);
    }

    public static function countByCRTByASM($asm, $sso)
    {
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $SalesAgentGrp = new tldGroup('gg_SALES_AGENTS');
            $SalesAgentList = array_column($SalesAgentGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList + $SalesAgentList);
            $WHERE = " cust.asm_id IN ($list) ";
        } else {
            $WHERE = " cust.asm_id=$asm ";
        }
        $query = <<<EOF
        SELECT
            factory.location AS erp_fullname,
            cust.type AS type,
            count(*) as num
        FROM customers AS cust
            LEFT JOIN customers_crt AS cust_crt ON cust_crt.customer_id=cust.id
            LEFT JOIN people ON people.id=cust.asm_id
            LEFT JOIN locations AS factory ON people.bu_id=factory.id
        WHERE $WHERE AND cust_crt.id IS NULL
        GROUP BY erp_fullname, type
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of customer by customer name
     *
     * @param string $name
     *
     * @return array of rows
     */
    public function byCustomerName($name)
    {
        $a = ['customer_name' => $name];

        return self::byConstraints($a);
    }

    /**
     * Get list of customer with at least a CRT linked
     *
     * @return array of rows
     */
    public function byCRTLinked()
    {
        $a = <<<EOF
(SELECT COUNT(crt.id) FROM customers_crt AS crt WHERE crt.customer_id=customers.id)>0
EOF;

        return self::byConstraints($a);
    }

    /**
     * Get list of customers by constraints
     *
     * @param array $a
     *
     * @param null $options
     *
     * @return array of db rows
     */
    public static function byConstraints($a, $options = null)
    {
        if (empty($a)) {
            return;
        }
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE
    $WHERE AND customers.deleted_at IS NULL
ORDER BY
	customer_name
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byAsmID($uid)
    {
        return self::byConstraints(['asm_id' => $uid]);
    }

    public function byUnassignedASM()
    {
        return self::byConstraints(['asm_id' => 0]);
    }

    public function byCRTByASM($factory, $type, $asm, $sso)
    {
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList);
            $WHERE = " cust.asm_id IN ($list) ";
        } else {
            $WHERE = " cust.asm_id=$asm ";
        }
        if ($factory !== 'ALL') {
            $WHERE .= " AND factory.location='$factory' ";
        }
        if ($type !== 'ALL') {
            $WHERE .= " AND cust.type='$type' ";
        }

        $query = <<<EOF
        SELECT
            cust.*,
            (SELECT CONCAT(people.firstname,' ',people.lastname)
        		FROM people WHERE cust.asm_id=people.id
    		) AS asm_fullname,
    		(SELECT COUNT(crt.id) FROM customers_crt AS crt
    		    WHERE crt.customer_id=cust.id
    		) AS numCUNO,
    		(SELECT name FROM countries
        		WHERE id=cust.ctry_id
    		) AS country,
            factory.location AS erp_fullname
        FROM customers AS cust
            LEFT JOIN customers_crt AS cust_crt ON cust_crt.customer_id=cust.id
            LEFT JOIN people ON people.id=cust.asm_id
            LEFT JOIN locations AS factory ON people.bu_id=factory.id
        WHERE $WHERE AND cust_crt.id IS NULL
        ORDER BY cust.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search for customers
     *
     * @param string $target
     *
     * @return array of db rows
     */
    public static function search($target)
    {
        $target = TldDatabase::escape($target);
        $query = <<<EOF
			SELECT cust.*,
			COUNT(xu.id) AS num_users,
			CONCAT(people.firstname,' ',people.lastname) AS asm_fullname,
			(SELECT name FROM countries
                WHERE id=cust.ctry_id
            ) AS country
			FROM customers AS cust
    			LEFT JOIN customers_crt AS crt ON crt.customer_id=cust.id
    			LEFT JOIN extranet_users_roles AS roles ON roles.crt_id=crt.id
    			LEFT JOIN extranet_users AS xu ON xu.id=roles.parent_id
    			LEFT JOIN people ON cust.asm_id = people.id
	        WHERE cust.customer_name LIKE '%$target%'
	        	OR crt.cuno LIKE '%$target%'
			GROUP BY cust.id
			ORDER BY count(*) DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function simpleSearch($name, $orderBy = 'customers.customer_name', $orderDir = 'ASC', $maxResult = 20)
    {
        $target = TldDatabase::escape($name);
        $query = <<<EOF
            SELECT customers.id, customers.customer_name
            FROM customers
            LEFT JOIN customers_crt AS crt ON crt.customer_id=customers.id
            WHERE 
                customers.deleted_at IS NULL
            AND (
                customers.customer_name LIKE '%$name%'
                OR crt.cuno LIKE '%$name%'
            )
            GROUP BY customers.id
            ORDER BY $orderBy $orderDir
            LIMIT $maxResult
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @deprecated
     */
    public static function insert(Array $p)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function update(Array $p, $id = null)
    {
        throw new Exception('This is no longer used');
    }

    public static function add($name, $assignor)
    {
        $name = trim(strtoupper($name));
        while (strpos($name, '  ') !== false) {
            str_replace('  ', ' ', $name);
        }
        $customer_name = TldDatabase::escape($name);

        $condition = "customer_name LIKE '$customer_name'";
        if (ctype_digit($name)) {
            $condition = "id = '$customer_name'";
        }

        $query = <<<SQL
    SELECT id 
    FROM customers 
    WHERE $condition; 
SQL;

        $result = tldUtils::getSqlToAssocArray($query);
        if (count($result)) {
            // Customer name already exists in db
            return $result[0]['id'];
        }
        global $kernel;

        $client = $kernel->getContainer()->get(Client::class);

        try {
            $customer = $client->findOneBy('sales/customer', ['name' => $name]);
            return $customer['legacyId'];
        } catch (Exception $e) {
            // Nothing to do, we need to create a customer
        }

        try {
            $customer = $client->post('sales/customers', [
                'json' => [
                    'name' => $name,
                ],
            ]);
            $id = $customer['legacyId'];
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            $id = 'ERROR: Could not create customer. Reason: ' . $errors['hydra:description'];
        }

        return $id;
    }

    /**
     * @deprecated
     */
    public function delete($id)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public static function isSequenceStarted($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }
        $query = <<<EOF
    	SELECT COUNT(*) AS count
    	FROM tasks
    	WHERE parent_id = $id
    	  AND seq = 'Y'
    	  AND module = 'SEQ'
    	  AND tplno = 34
EOF;
        $result = tldUtils::getSqlRowToAssocArray($query);
        if ($result['count'] > 0) {
            return true;
        }

        return false;
    }

    public function transferAsmFromTo($from, $to)
    {
        $query = "UPDATE customers SET asm_id=$to WHERE asm_id=$from";
        error_log(__CLASS__ . '::' . __METHOD__ . ' -> ' . $query);

        return tldUtils::sqlQuery($query);
    }

}

class tldCustomerCleanup
{

    public static function getTableStructure()
    {
        static $table_structure;
        if (!$table_structure) {
            $query = <<<EOF
			SELECT *
			FROM customers_tables
EOF;
            $table_structure = tldUtils::getSqlToAssocArray($query);
        }

        return $table_structure;
    }

    public static function countBySSOERP($a)
    {
        $WHERE = '1=1';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
    CASE
        WHEN sales_org LIKE '' THEN 'NO SSO'
        ELSE sales_org
    END AS sales_org,
    CASE
        WHEN man_location LIKE '' THEN 'NO FACTORY'
        ELSE man_location
    END AS man_location,
    count(*) as num
FROM
    service
WHERE
    (buyer_customer_id=0 OR buyer_customer_id IS NULL
    OR customer_id=0 OR customer_id IS NULL)
    AND $WHERE
GROUP BY
    sales_org,
    man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getOrphans($type = 'string')
    {
        if (empty($type) OR !in_array($type, ['string', 'id'])) {
            return [];
        }
        $query = [];
        foreach (self::getTableStructure() AS $table) {
            if (($type !== 'id' AND $table['type'] === 'id') OR ($type === 'id' AND $table['type'] !== 'id')) {
                continue;
            }
            if ($type === 'id') {
                $CSEL = '`id`';
                $COND = "`{$table['field']}` > 0";
            } else {
                $CSEL = '`customer_name`';
                $COND = "TRIM(`{$table['field']}`) != ''";
            }
            if (!empty($table['contraints'])) {
                $COND .= " AND ({$table['contraints']})";
            }
            $query[] = <<<EOF
			SELECT `{$table['field']}` AS customer
			FROM `{$table['table']}`
			WHERE `{$table['field']}` NOT IN (SELECT $CSEL FROM `customers`) AND $COND
EOF;
        }
        $query = implode("\nUNION\n", $query);
        $query .= "\nGROUP BY `customer`\nORDER BY `customer`";

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getImpactsByValueType($value, $type = 'id', $limit = null)
    {
        if (($type !== 'empty' AND empty($value)) OR empty($type) OR !in_array($type, ['string', 'id', 'empty'])) {
            return [];
        }
        $query = [];
        $value = ($type === 'id') ? (int)$value : "'" . TldDatabase::escape($value) . "'";
        foreach (self::getTableStructure() AS $table) {
            if (($type !== 'id' AND $table['type'] === 'id') OR ($type === 'id' AND $table['type'] !== 'id')) {
                continue;
            }
            if ($type === 'id' AND $value <= 0) {
                continue;
            }
            if (!empty($table['contraints'])) {
                $COND = "AND ({$table['contraints']})";
            } else {
                $COND = '';
            }
            if ($type === 'empty') {
                if ($table['type'] == 'id') {
                    $WHERE = "`{$table['field']}` = 0";
                } else {
                    $WHERE = "TRIM(`{$table['field']}`) = ''";
                }
                $WHERE = "($WHERE OR ISNULL(`{$table['field']}`))";
            } else {
                $WHERE = "`{$table['field']}` = $value";
            }
            $query[] = <<<EOF
			SELECT
				`id`,
				'{$table['mod']}' AS `mod`,
				'{$table['table']}' AS `table`,
				'{$table['field']}' AS `field`,
				'{$table['uri']}' AS `uri`
			FROM
				`{$table['table']}`
			WHERE
				$WHERE $COND
EOF;
        }
        $query = implode("\nUNION\n", $query);
        $query .= "\nORDER BY `mod`,`table`,`id`,`field`";
        if ($limit > 0) {
            $query .= "\nLIMIT $limit";
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getImpactsByName($name, $limit = 100)
    {
        return self::getImpactsByValueType($name, 'string', $limit);
    }

    public function getImpactsById($id, $limit = 100)
    {
        return self::getImpactsByValueType($id, 'id', $limit);
    }

    public function getImpactsByEmpty($limit = 100)
    {
        return self::getImpactsByValueType(null, 'empty', $limit);
    }

    public static function isOrphan($orphan, $type)
    {
        $CSEL = ($type === 'id') ? 'id' : 'customer_name';
        $orphan = ($type === 'id') ? (int)$orphan : "'" . TldDatabase::escape($orphan) . "'";
        $query = <<<EOF
		SELECT COUNT(*) AS count
		FROM customers
		WHERE $CSEL = $orphan
EOF;
        $result = tldUtils::getSqlRowToAssocArray($query);

        return 0 === (int) $result['count'];
    }

    public static function replaceByType($value, $replace_id, $type)
    {
        if (empty($value)) {
            return 'Value is empty';
        }
        if ($type === 'id' AND $value < 0) {
            return 'ID is invalid';
        }
        if (!in_array($type, ['string', 'id'])) {
            return 'Update type is invalid';
        }
        $replace_id = (int)$replace_id;
        $customer = new tldCustomer($replace_id);
        if ($customer->isEmpty()) {
            return 'Selected customer does not exist';
        }
        $value = ($type === 'id') ? (int)$value : "'" . TldDatabase::escape($value) . "'";
        $errors = [];
        foreach (self::getTableStructure() AS $table) {
            $id = ($type === 'id') ? $replace_id : "'" . TldDatabase::escape($customer->getCustomerName()) . "'";
            if (($type !== 'id' AND $table['type'] === 'id') OR ($type === 'id' AND $table['type'] !== 'id')) {
                continue;
            }
            if ($type === 'id' AND $value <= 0) {
                continue;
            }
            if (!empty($table['contraints'])) {
                $COND = "AND ({$table['contraints']})";
            } else {
                $COND = '';
            }
            if (strlen($table['value'])) {
                $id = sprintf($table['value'], $value);
            }
            $query = <<<EOF
			UPDATE `{$table['table']}`
			SET `{$table['field']}` = $id
			WHERE `{$table['field']}` = $value $COND
EOF;
            $error = tldUtils::sqlQuery($query);
            if ($error) {
                $errors[] = "Unable to replace table '{$table['table']}' field '{$table['field']}' from module '{$table['mod']}', please handle in module : $error";
            }
            tldUtils::log_event("CUSTOMER REPLACE BY TYPE\n\n$query");
        }

        return implode("<br/>\n", $errors);
    }

    public function replaceId($old_id, $new_id)
    {
        return self::replaceByType($old_id, $new_id, 'id');
    }

    public static function updateOrphanByName($name, $replace_id)
    {
        if (!self::isOrphan($name, 'string')) {
            return 'Not an orphan';
        }

        return self::replaceByType($name, $replace_id, 'string');
    }

    public static function updateOrphanById($id, $replace_id)
    {
        if (!self::isOrphan($id, 'id')) {
            return 'Not an orphan';
        }

        return self::replaceByType($id, $replace_id, 'id');
    }
}

/**
 * Class for accessing and manipulating Customer Relationship Team data
 *
 * @package SalesAndService
 */
class tldCRT
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader($id);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getCustomerName()
    {
        return $this->itsHeader['customer_name'];
    }

    public function getCustomerID()
    {
        return $this->itsHeader['customer_id'];
    }

    public function getCUNO()
    {
        return $this->itsHeader['cuno'];
    }

    public function getERPLocationID()
    {
        return $this->itsHeader['erp_location_id'];
    }

    public function getERP()
    {
        return $this->itsHeader['erp'];
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getSalesRepID()
    {
        return $this->itsHeader['sales_rep_id'];
    }

    public function getPartsRepID()
    {
        return $this->itsHeader['parts_rep_id'];
    }

    public function getServicesRepID()
    {
        return $this->itsHeader['services_rep_id'];
    }

    public function getSalesLocationID()
    {
        return $this->itsHeader['erp_location_id'];
    }

    public function getPartsLocationID()
    {
        return $this->itsHeader['parts_location_id'];
    }

    public function getServicesLocationID()
    {
        return $this->itsHeader['services_location_id'];
    }

    public function getContactList()
    {
        return $this->getContactListByConstraints();
    }

    public function getContactListByRole($role)
    {
        return $this->getContactListByConstraints(['roles.role' => $role]);
    }

    public function getContactListByConstraints($a = '1=1')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $WHERE .= " AND crt.id=$this->itsID";

        return extranetUser::byConstraints($WHERE);
    }

    /**
     * Get header information from database
     *
     * @return array
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
SELECT crt.*,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.sales_rep_id=people.id
    ) AS sales_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.parts_rep_id=people.id
    ) AS parts_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.services_rep_id=people.id
    ) AS services_rep,
    (SELECT location FROM locations
        WHERE crt.parts_location_id=locations.id
    ) AS parts_location,
    (SELECT location FROM locations
        WHERE crt.services_location_id=locations.id
    ) AS services_location,
    (SELECT customer_name FROM customers
        WHERE crt.customer_id=customers.id
    ) AS customer_name,
    (SELECT locations.location FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp_location,
    (SELECT locations.erp FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp
FROM customers_crt AS crt
WHERE crt.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * @deprecated
     */
    public function update()
    {
        throw new Exception('This is no longer used');
    }

    public static function processCreation($customerId, $ssoId)
    {
        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);
        $targetCustomer = $client->findOneBy('sales/customers', ['legacyId' => $customerId]);
        $targetLocation = $client->findOneBy('locations', ['legacyId' => $ssoId]);

        try {
            $crt = $client->post('sales/customer_relationship_teams', [
                'json' => [
                    'customer' => $targetCustomer['@id'],
                    'erpLocation' => $targetLocation['@id'],
                ],
            ]);

            $legacyCRT = new tldCRT($crt['legacyId']);
            $legacyCRT->addLogEntry('CRT created from TOC');

            return $crt['legacyId'];
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return 'ERROR: Could not add CRT. Reason: ' . $errors['hydra:description'];
        }
    }

    /**
     * @deprecated
     */
    public function duplicate()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Add a comment to the log
     *
     * @param int $id poster id
     * @param string $comment
     *
     * @return boolean
     */
    public function addLogEntry($comment)
    {
        global $kernel;

        $client = $kernel->getContainer()->get(Client::class);
        $crt = $client->findOneBy('sales/customer_relationship_teams', ['legacyId' => $this->itsID]);

        try {
            $log = $client->post('comments', [
                'json' => [
                    'resource' => $crt['@id'],
                    'message' => $comment,
                ],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return 'ERROR: Could not add a log. Reason: ' . $errors['hydra:description'];
        }
        return $log['legacyId'];
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CRT');
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'CRT', 'ALL');
    }

    public static function byCustomerID($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return 'Invalid parameter';
        }

        return self::byConstraints(
            [
                'cust.id' => $id,
            ]
        );
    }

    public static function byCustomerIDSSOID($id, $ssoid)
    {
        if (empty($id) || !is_numeric($id)) {
            return 'Invalid parameter';
        }
        if (empty($ssoid) || !is_numeric($ssoid)) {
            return 'Invalid parameter';
        }

        return self::byConstraints(
            [
                'cust.id' => $id,
                'crt.erp_location_id' => $ssoid,
            ]
        );
    }

    public static function byCUNO($id)
    {
        return self::byConstraints(
            [
                'cuno' => $id,
            ]
        );
    }

    public static function byErpCuno($erp, $cuno)
    {
        return self::byConstraints(
            [
                'cuno' => $cuno,
                'erp_loc.erp' => $erp,
            ]
        );
    }

    /**
     * List of CRT by Sales rep ID
     *
     * @param int $id
     *
     * @return array of rows
     */
    public static function bySalesRepID($id)
    {
        if (!is_numeric($id)) {
            return 'Invalid parameters';
        }

        return self::byConstraints(
            [
                'sales_rep_id' => $id,
            ]
        );
    }

    /**
     * List of CRT by Service rep ID
     *
     * @param int $id
     *
     * @return array of rows
     */
    public static function byServicesRepID($id)
    {
        if (!is_numeric($id)) {
            return 'Invalid parameters';
        }

        return self::byConstraints(
            [
                'services_rep_id' => $id,
            ]
        );
    }

    /**
     * List of CRT by Parts rep ID
     *
     * @param int $id
     *
     * @return array of rows
     */
    public static function byPartsRepID($id)
    {
        if (!is_numeric($id)) {
            return 'Invalid parameters';
        }

        return self::byConstraints(
            [
                'parts_rep_id' => $id,
            ]
        );
    }

    /**
     * Get CRT by constraints
     *
     * @param array $a constraints=array("mode"=>"theMode","data"=>array of data)
     *
     * @return mixed array or string error
     */
    public static function byConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = TldDatabase::escape($a);
        }

        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }

        $ORDERBY = ' ORDER BY cust.customer_name ';

        $query = <<<EOF
SELECT crt.*,
    cust.customer_name,
    CONCAT(sales_reps.firstname,' ',sales_reps.lastname) AS sales_rep,
    sales_reps.email AS sales_rep_email,
    CONCAT(parts_reps.firstname,' ',parts_reps.lastname) AS parts_rep,
    CONCAT(services_reps.firstname,' ',services_reps.lastname) AS services_rep,
    parts_loc.location AS parts_location,
    services_loc.location AS services_location,
    erp_loc.location AS erp_location,
    erp_loc.erp AS erp
FROM customers_crt AS crt
    LEFT JOIN customers AS cust ON cust.id=crt.customer_id
    LEFT JOIN people AS sales_reps ON sales_reps.id=crt.sales_rep_id
    LEFT JOIN people AS parts_reps ON parts_reps.id=crt.parts_rep_id
    LEFT JOIN people AS services_reps ON services_reps.id=crt.services_rep_id
    LEFT JOIN locations AS parts_loc ON parts_loc.id=crt.parts_location_id
    LEFT JOIN locations AS services_loc ON services_loc.id=crt.services_location_id
    LEFT JOIN locations AS erp_loc ON erp_loc.id=crt.erp_location_id
$WHERE AND crt.deleted_at IS NULL
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get latest customer reference
     *
     * @param int $max
     *
     * @return array
     */
    public static function byLatest($max = 10)
    {
        if (!is_numeric($max)) {
            return;
        }
        $query = <<<EOF
SELECT crt.*,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.sales_rep_id=people.id
    ) AS sales_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.parts_rep_id=people.id
    ) AS parts_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.services_rep_id=people.id
    ) AS services_rep,
    (SELECT location FROM locations
        WHERE crt.parts_location_id=locations.id
    ) AS parts_location,
    (SELECT location FROM locations
        WHERE crt.services_location_id=locations.id
    ) AS services_location,
    (SELECT customer_name FROM customers
        WHERE crt.customer_id=customers.id
    ) AS customer_name,
    (SELECT locations.location FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp_location,
    (SELECT locations.erp FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp
FROM customers_crt AS crt
ORDER BY crt.id DESC
LIMIT $max
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search for customer reference
     *
     * @param        $target
     * @param string $mode
     *
     * @return array
     */
    public static function search($target, $mode = 'basic')
    {
        if (empty($target)) {
            return 'Empty parameter';
        }
        $query = <<<EOF
SELECT
    crt.*,
    (SELECT customer_name FROM customers
        WHERE crt.customer_id=customers.id
    ) AS customer_name,
    (SELECT type FROM customers
        WHERE crt.customer_id=customers.id
    ) AS customer_type,
    (SELECT name FROM customers
        LEFT JOIN countries ON countries.id=customers.ctry_id
        WHERE crt.customer_id=customers.id
    ) AS customer_country,
    (SELECT customer_name FROM customers
        WHERE crt.customer_id=customers.id
    ) AS customer_name,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.sales_rep_id=people.id
    ) AS sales_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.parts_rep_id=people.id
    ) AS parts_rep,
    (SELECT CONCAT(people.firstname,' ',people.lastname)
        FROM people WHERE crt.services_rep_id=people.id
    ) AS services_rep,
    (SELECT location FROM locations
        WHERE crt.parts_location_id=locations.id
    ) AS parts_location,
    (SELECT location FROM locations
        WHERE crt.services_location_id=locations.id
    ) AS services_location,
    (SELECT locations.location FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp_location,
    (SELECT locations.erp FROM locations
        WHERE locations.id=crt.erp_location_id
    ) AS erp
FROM
    customers_crt AS crt
EOF;
        switch ($mode) {
            case 'basic':
                $query .= <<<EOF
HAVING customer_name LIKE '%$target%'
    OR erp_location LIKE '%$target%'
    OR erp LIKE '%$target%'
    OR cuno LIKE '%$target%'
EOF;
                break;
            case 'advanced':
                if (!is_array($target)) {
                    return 'Invalid parameter';
                }
                $query .= ' WHERE ' . tldUtils::constructWhere($target, 'OR');
                break;
        }
        $query .= ' ORDER BY customer_name ';

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getAddresses($erp, $cuno, $cdel, $role)
    {
        if (is_array($role)) {
            $roles = implode("','", $role);
        } else {
            $roles = $role;
        }
        $query = <<<EOF
SELECT
    GROUP_CONCAT(distinct users.email SEPARATOR ',') AS cust_emails,
    GROUP_CONCAT(distinct asms.email SEPARATOR ',') AS asm_emails,
    GROUP_CONCAT(distinct sphs.email SEPARATOR ',') AS sph_emails
FROM
    customers_crt AS crts
    JOIN locations AS erps ON crts.erp_location_id=erps.id
    JOIN extranet_users_roles AS roles ON crts.id=roles.crt_id
    JOIN extranet_users AS users ON roles.parent_id=users.id
    LEFT JOIN people AS asms ON crts.sales_rep_id=asms.id
    LEFT JOIN people AS sphs ON crts.parts_rep_id=sphs.id
WHERE
    users.email<>''
    AND erps.erp=$erp
    AND crts.cuno='$cuno'
    AND roles.role IN ('$roles')
    AND (roles.cdel='' OR roles.cdel='$cdel')
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

}

/**
 * Class for accessing and manipulating SOR data
 *
 * @package SalesAndService
 */
class tldSOR
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getID()
    {
        return $this->itsID;
    }

    /**
     * Get the database row for this SOR
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE sor.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * @deprecated
     */
    public function duplicate()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get the current status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get the Business Unit number
     *
     * @return integer
     */
    public function getBU()
    {
        return $this->itsHeader['bu'];
    }

    public function getJuridicalEntity()
    {
        return $this->itsHeader['juridical_entity'];
    }

    /**
     * Get the Area Sales Manager id linked to this SOR
     *
     * @return integer
     */
    public function getASM()
    {
        return $this->itsHeader['asm'];
    }

    public function getXML()
    {
        return $this->itsHeader['src_xml'];
    }

    /**
     * Refresh the header information from the database
     *
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * @deprecated
     */
    public static function insert()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Add a new SOR Line
     *
     * @param array $p
     * @param       $uid
     *
     * @return mixed id of newly inserted SOL or string error message
     */
    public function addLine($p, $uid)
    {
        $p['parent_id'] = $this->itsID;

        return tldSOL::insert($p, $uid);
    }

    /**
     * @deprecated
     */
    public function getFiles()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SOR', 'ALL');
    }

    /**
     * Get linked log entries
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SOR');
    }

    /**
     * @deprecated
     *
     */
    public function addLogEntry($id, $comment)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function update($data, $fields = '')
    {
        throw new Exception('This is no longer used');
    }

    public function setXMLSource($xml)
    {
        return $this->update(['src_xml' => $xml]);
    }

    public function getStatusList()
    {
        return [
            'PENDING',
//            'VALIDATE_SOR',
//            'CREATE_SALES_SO',
            'IN_PROGRESS',
            'CLOSED',
        ];
    }

    /**
     * @deprecated
     */
    public function changeStatus($status = '', $options = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get all linked SOR Lines
     *
     * @return array
     */
    public function getLines()
    {
        return tldSOL::byParent($this->itsID);
    }

    /**
     * Get all open SOR lines
     *
     * @return array
     */
    public function getOpenLines()
    {
        $result = [];
        $rows = $this->getLines();
        foreach ($rows as $row) {
            if ($row['status'] !== 'CLOSED') {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Get the latest SORs, defaults to last 10
     *
     * @param integer $qty
     *
     * @return array
     */
    public static function byLatest($qty = 10)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
ORDER BY sor.id DESC
LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * get count of SOR by sales org and status
     *
     * default is to get only active ie. not closed
     *
     * @param string $option
     *
     * @return array db rows
     */
    public function countBySalesOrgStatus($option = '')
    {
        $WHERE = '';
        if ($option !== 'ALL') {
            $WHERE = $option !== 'ALL' ? " WHERE status<>'CLOSED'" : '';
        }
        $query = <<<EOF
SELECT T1.status, T2.location as sales_org, count(*) as num
FROM sor AS T1 LEFT JOIN locations AS T2 ON T1.bu=T2.erp
    $WHERE
GROUP BY status, sales_org
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function sumBookingsBySSOERPYear($sso, $erp, $y)
    {
        if ($sso === 'ALL') {
            $sso = '%';
        }
        if ($erp === 'ALL') {
            $erp = '%';
        }
        $query = <<<EOF
SELECT
  bu_from.location AS location_from,
  bu_to.location AS location_to,
(
	SELECT DATE_FORMAT(MAX(mod_logs.date), '%Y%m')
	FROM mod_logs
	WHERE
		mod_logs.parent_id = sols.id
		AND mod_logs.module LIKE 'SOL'
		AND mod_logs.comment LIKE '%PRINT_SO_ACK%'
) AS period,
SUM(
  (SELECT
  SUM(
  IF(
	opts.pris_cur='USD',
	opts.pris,
	ROUND(pris/(
          SELECT value FROM mod_lists
          WHERE module='SOL' AND list_name='CURS'
            AND parent_id=opts.parent_id
            AND list_key=opts.pris_cur), 2)
  )
  )
  FROM sor_opts AS opts
  WHERE parent_id=sols.id
  )
) AS bookings
FROM
sor AS sors
JOIN sor_lines AS sols ON sors.id=sols.parent_id
JOIN sor_units AS sous ON sols.id=sous.parent_id
LEFT JOIN service AS ers ON sous.id=ers.sor_uid
LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
GROUP BY location_from, location_to, period
HAVING location_from LIKE '$sso'
	AND location_to LIKE '$erp'
	AND LEFT(period, 4)='$y'
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function sumBacklogBySSOERPYear($sso, $erp, $y)
    {
        if ($sso === 'ALL') {
            $sso = '%';
        }
        if ($erp === 'ALL') {
            $erp = '%';
        }
        if (empty($y)) {
            $y = date('Y');
        }

        $q = [];
        foreach (range(1, 12) as $m) {
            $ym = sprintf('%s%02d', $y, $m);
            $q[] = <<<EOF
SELECT
	bu_from.location AS location_from,
	bu_to.location AS location_to,
	'$y$m' AS period,
	SUM(
	(
	SELECT
	SUM(
	IF(
		opts.pris_cur='USD',
		opts.pris,
		ROUND(pris/(
          SELECT value FROM mod_lists
          WHERE module='SOL' AND list_name='CURS'
            AND parent_id=opts.parent_id
            AND list_key=opts.pris_cur), 2)
	)
	)
	FROM sor_opts AS opts
	WHERE parent_id=sous.parent_id
	)
	) AS backlog
FROM
	sor AS sors
	JOIN sor_lines AS sols ON sors.id=sols.parent_id
	JOIN sor_units AS sous ON sols.id=sous.parent_id
	LEFT JOIN service AS ers ON sous.id=ers.sor_uid
	LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
	LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
WHERE sors.dt_entered > '2008-09-01'
  AND (
		SELECT PERIOD_DIFF(DATE_FORMAT(CAST(MAX(mod_logs.date) AS date), '%Y%m'), '$ym')
		FROM mod_logs
		WHERE
			mod_logs.parent_id = sols.id
			AND mod_logs.module LIKE 'SOL'
			AND mod_logs.comment LIKE '%PRINT_SO_ACK%'
	) <= 0
	AND
	(ers.rrd_sso='0000-00-00'
	  OR ers.rrd_sso IS NULL
	  OR PERIOD_DIFF(DATE_FORMAT(ers.rrd_sso, '%Y%m'), '$ym') > 0
	)
GROUP BY location_from, location_to, period
HAVING location_from LIKE '$sso'
	AND location_to LIKE '$erp'
EOF;
        }

        return tldUtils::getSqlToAssocArray('(' . implode(')UNION(', $q) . ')');
    }

    public static function byASMStatus($status, $asm)
    {
        $a = [];
        if ($asm !== 'ALL') {
            $a['asm'] = $asm;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }

        return self::byConstraints($a);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    sor.*,
    sso.location,
    juridical.name AS juridical_entity,
    CONCAT(asm.lastname,', ',asm.firstname) AS asm_fullname,
    user.customer_name AS user_customer_display,
    user.type AS user_type_display,
    user.approved AS user_approved,
    buyer.customer_name AS buyer_customer_display,
    buyer.type AS buyer_type_display,
    buyer.approved AS buyer_approved
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM sor
    LEFT JOIN locations AS sso ON sor.sso=sso.id
    LEFT JOIN tld_juridical_locations AS juridical ON juridical.id=sor.juridical_entity_id
    LEFT JOIN people AS asm ON sor.asm=asm.id
    LEFT JOIN customers AS user ON sor.user_customer_id=user.id
    LEFT JOIN customers AS buyer ON sor.buyer_customer_id=buyer.id
EOF;
    }

    public static function byConstraints($a, $opt = [])
    {
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        $ORDERBY = 'id';
        if (empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * SOR Line Option
 *
 * @package SalesAndService
 */
class tldSOROpts
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information from table
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT *
FROM sor_opts
WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert and link a new option line
     *
     * Parent id of SOR to link to is in $p['parent_id']
     *
     * @param array $p
     *
     * @return mixed
     */
    public static function insert($p)
    {
        $fields = [
            'parent_id',
            'caty',
            'dsca',
            'mrsp_cur',
            'mrsp',
            'prip_cur',
            'prip',
            'pric_cur',
            'pric',
            'pris_cur',
            'pris',
        ];
        $p = tldUtils::escapeSQL($p);
        $query = <<<EOF
INSERT INTO sor_opts
    SET
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update SOR option
     *
     * @param array $p
     *
     * @return mixed
     */
    public function update($p)
    {
        if (empty($this->itsID)) {
            return;
        }
        if (empty($p)) {
            return;
        }

        $fields = [
            'caty',
            'dsca',
            'mrsp_cur',
            'mrsp',
            'prip_cur',
            'prip',
            'pric_cur',
            'pric',
            'pris_cur',
            'pris',
        ];
        $query = <<<EOF
            UPDATE sor_opts
        SET
EOF;
        $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);
        $query .= <<<EOF
        WHERE id=$this->itsID
        LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get the parent sor id of this option line
     *
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /*
	 * Get sales price grouped and summed by currency
	 *
	 * @param integer $pid SOL id number
	 * @return array
	 */
    public function getCurPrisByParent($pid)
    {
        if (empty($pid)) {
            return 'ERROR: No pid set';
        }
        $query = <<<EOF
		SELECT opts.pris_cur, SUM(opts.pris)
		FROM sor_opts AS opts
		WHERE opts.parent_id=$pid
		GROUP BY opts.pris_cur
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    /*
	 * Get cost price grouped and summed by currency
	 *
	 * @param integer $pid SOL id number
	 * @return array
	 */
    public function getCurPricByParent($pid)
    {
        if (empty($pid)) {
            return 'ERROR: No pid set';
        }
        $query = <<<EOF
		SELECT opts.pric_cur, sum(opts.pric)
		FROM sor_opts AS opts
		WHERE opts.parent_id=$pid
		GROUP BY opts.pric_cur
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Delete the current option from table
     *
     * @return mixed string on error
     */
    public function del()
    {
        if (empty($this->itsID)) {
            return 'ERROR: no sor line opt id set to delete';
        }
        $query = <<<EOF
		DELETE FROM sor_opts
		WHERE id=$this->itsID
		LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get options based on parent id
     *
     * @param integer $pid
     * @param array|string $options
     *
     * @return array
     */
    public static function byParent($pid, $options = '')
    {
        $WHERE = '';
        if (isset($options['exclude']) && is_array($options['exclude'])) {
            $WHERE .= " AND caty NOT IN ('" . implode("','", $options['exclude']) . "')";
        }
        if (isset($options['include']) && is_array($options['include'])) {
            $WHERE .= " AND caty IN ('" . implode("','", $options['include']) . "')";
        }

        if (!empty($options['description_only'])) {
            $query = <<<SQL

SELECT caty, dsca 
FROM sor_opts AS t1
WHERE parent_id='$pid' $WHERE
ORDER BY caty
SQL;
            return tldUtils::getSqlToAssocArray($query);
        }

        if (empty($options['dcur'])) {
            $dcur = (new tldSOL($pid, true))->getDCUR();
        } else {
            $dcur = $options['dcur'];
        }
        if ($dcur === 'USD') {
            $query = <<<EOF
SELECT *,
	'USD' AS dcur,
	IF(t1.mrsp_cur='USD',
		t1.mrsp,
		ROUND(mrsp/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.mrsp_cur=list_key), 2)
	) AS mrsp_in_dcur,
	IF(t1.prip_cur='USD',
		t1.prip,
		ROUND(prip/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.prip_cur=list_key), 2)
	) AS prip_in_dcur,
	IF(t1.pric_cur='USD',
		t1.pric,
		ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
	) AS pric_in_dcur,
	IF(t1.pris_cur='USD',
		t1.pris,
		ROUND(pris/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pris_cur=list_key), 2)
	) AS pris_in_dcur
FROM sor_opts AS t1
WHERE parent_id='$pid'
                $WHERE
	ORDER BY caty
EOF;
        } else {
            $query = <<<EOF
			SELECT *,
		'$dcur' AS dcur,
	CASE WHEN t1.mrsp_cur='USD' THEN
		ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'),2)
	WHEN t1.mrsp_cur='$dcur' THEN
		t1.mrsp
	ELSE
	    ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
	    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.mrsp_cur), 2)
    END AS mrsp_in_dcur,
	CASE WHEN t1.prip_cur='USD' THEN
        ROUND(prip*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
	WHEN t1.prip_cur='$dcur' THEN
		t1.prip
	ELSE
        ROUND(prip*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.prip_cur), 2)
    END AS prip_in_dcur,
	CASE WHEN t1.pric_cur='USD' THEN
        ROUND(pric*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
	WHEN t1.pric_cur='$dcur' THEN
		t1.pric
	ELSE
        ROUND(pric*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pric_cur), 2)
    END AS pric_in_dcur,
	CASE WHEN t1.pris_cur='USD' THEN
        ROUND(pris*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
	WHEN t1.pris_cur='$dcur' THEN
		t1.pris
	ELSE
        ROUND(pris*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pris_cur), 2)
    END AS pris_in_dcur
	FROM sor_opts AS t1
	WHERE parent_id='$pid'
                $WHERE
	ORDER BY caty
EOF;
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param int|string|array $pid
     * @param array $options
     * @return array|mixed
     */
    public static function totalsByParent($pid, $options = [])
    {
        $result = [
            'mrsp_tot_in_dcur' => null,
            'prip_tot_in_dcur' => null,
            'pric_tot_in_dcur' => null,
            'pris_tot_in_dcur' => null,
            'pris_tot' => null,
            'pric_tot' => null,
            'prip_tot' => null,
            'mrsp_tot' => null,
        ];

        foreach (self::totalsOptionsByParent($pid, $options) as $total) {
            foreach ($result as $key => $value) {
                $result[$key] = (string)($result[$key] + $total[$key]);
            }
        }

        return $result;
    }


    /**
     * @param int|string|array $pid
     * @param array $options
     * @return array
     */
    public static function totalsOptionsByParent($pid, $options = [])
    {
        $WHERE = "t1.parent_id=$pid";

        if (is_array($options['exclude'])) {
            $WHERE .= " AND caty NOT IN ('" . implode("','", $options['exclude']) . "')";
        }
        if (is_array($options['include'])) {
            $WHERE .= " AND caty IN ('" . implode("','", $options['include']) . "')";
        }
        $dcur = empty($options['dcur']) ? (new tldSOL($pid, true))->getDCUR() : $options['dcur'];

        if ($dcur === 'USD') {
            $query = <<<EOF
SELECT
    caty,
    SUM(IF(t1.mrsp_cur='USD',
        t1.mrsp,
        ROUND(mrsp/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.mrsp_cur=list_key), 2)
    )) AS mrsp_tot_in_dcur,
    SUM(IF(t1.prip_cur='USD',
        t1.prip,
        ROUND(prip/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.prip_cur=list_key), 2)
    )) AS prip_tot_in_dcur,
    SUM(case WHEN t1.caty='SPECIAL DISCOUNT' THEN (if(t1.pric>0,IF(t1.pric_cur='USD',
        t1.pric,
        ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
    ),0)) ELSE 
        IF(t1.pric_cur='USD',
        t1.pric,
        ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
    ) END) AS pric_tot_in_dcur,
    SUM(IF(t1.pris_cur='USD',
        t1.pris,
        ROUND(pris/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pris_cur=list_key), 2)
    )) AS pris_tot_in_dcur,
    SUM(pris) AS pris_tot,
    SUM(pric) AS pric_tot,
    SUM(prip) AS prip_tot,
    SUM(mrsp) AS mrsp_tot
FROM sor_opts AS t1
WHERE $WHERE
GROUP BY caty
EOF;
        } else {
            $query = <<<EOF
SELECT
    caty,
    SUM(CASE WHEN t1.mrsp_cur='USD' THEN
        ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'),2)
    WHEN t1.mrsp_cur='$dcur' THEN
        t1.mrsp
    ELSE
        ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
        (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.mrsp_cur), 2)
    END
    ) AS mrsp_tot_in_dcur,
    SUM(CASE WHEN t1.prip_cur='USD' THEN
        ROUND(prip*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
    WHEN t1.prip_cur='$dcur' THEN
        t1.prip
    ELSE
        ROUND(prip*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
        (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.prip_cur), 2)
    END
    ) AS prip_tot_in_dcur,
    SUM(CASE WHEN t1.pric_cur='USD' THEN
        ROUND(IF((t1.caty = 'SPECIAL DISCOUNT' and t1.pric<0) ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
    WHEN t1.pric_cur='$dcur' THEN
        t1.pric
    ELSE
        ROUND(IF((t1.caty = 'SPECIAL DISCOUNT') ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pric_cur), 2)
    END
    ) AS pric_tot_in_dcur,
    SUM(CASE WHEN t1.pris_cur='USD' THEN
        ROUND(pris*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur'), 2)
    WHEN t1.pris_cur='$dcur' THEN
        t1.pris
    ELSE
        ROUND(pris*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='$dcur')/
    (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pris_cur), 2)
    END
    ) AS pris_tot_in_dcur,
    SUM(pris) AS pris_tot,
    SUM(pric) AS pric_tot,
    SUM(prip) AS prip_tot,
    SUM(mrsp) AS mrsp_tot
FROM sor_opts AS t1
WHERE $WHERE
GROUP BY caty
EOF;
        }

        return tldUtils::getSqlToAssocArray($query);
    }
}

/**
 * Class for accessing and manipulating SOR Unit data
 *
 * @package SalesAndService
 */
class tldSORUnit
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        $query = <<<EOF
		SELECT
            er.id AS erid,
            er.*,
            units.*,
            er.dgt_est
        FROM sor_units AS units
            LEFT JOIN service AS er ON units.id=er.sor_uid
		WHERE
            units.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get parent id number
     *
     * @return integer
     */
    public function getPID()
    {
        return $this->itsHeader['parent_id'];
    }

    public static function insert($p)
    {
        $fields = [
            'parent_id',
            'short_desc',
            'long_desc',
            'del_location',
            'del_dat',
            'ddel_est1',
            'dgt_est',
            'dpas_rating',
            'batch_qty',
            'del_early',
            'ddel_asm',
            'commissioning',
        ];
        if (!isset($p['ddel_asm'])) {
            $p['ddel_asm'] = $p['ddel_est1'];
        }
        $query = 'INSERT INTO sor_units SET ';
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function setDDEL_EST1($d)
    {
        return $this->updateHeader(['ddel_est1' => $d]);
    }

    public function updateHeader($a)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No sor unit id set in updateHeader function';
        }
        if (empty($a)) {
            return 'ERROR: Empty params in sorunit/updateHeader function';
        }
        if (isset($a['ddel_est1']) && !isset($a['ddel_asm']) && ('0000-00-00' === $this->itsHeader['ddel_asm'])) {
            $a['ddel_asm'] = $a['ddel_est1'];
        }
        $fields = ['short_desc', 'del_dat', 'del_location', 'ddel_est1', 'batch_qty', 'del_early', 'ddel_asm', 'dpas_rating', 'sleep_com', 'sleep_com_sso_id', 'commissioning'];
        $array = [];
        foreach ($fields AS $field) {
            if (isset($a[$field])) {
                $array[$field] = $a[$field];
            }
        }
        $SET = tldUtils::getSqlSet($array);
        $query = <<<EOF
		UPDATE sor_units
		SET $SET
		WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * get parent id for this sor unit
     *
     * @return integer
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public static function byParentESRL($pid)
    {
        $query = <<<EOF
        SELECT t2. * , t2.id AS erid, t1. * , t2.er_batch_qty, t3.erid AS esrl_erid
        FROM sor_units AS t1
        LEFT JOIN service AS t2 ON t1.id = t2.sor_uid
        LEFT JOIN esrl AS t3 ON t2.id = t3.erid AND t2.esrid = t3.parent_id
        WHERE t1.parent_id=$pid
        AND t3.erid IS NULL AND t2.sn IS NOT NULL
        ORDER BY erid
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParent($pid)
    {
        $query = <<<EOF
		SELECT t2.*, t2.id AS erid, t1.*, t2.er_batch_qty, t3.parent_id AS esr_id
		FROM sor_units AS t1 LEFT JOIN service as t2 ON t1.id=t2.sor_uid
		LEFT JOIN esrl as t3 ON t2.id = t3.erid AND t2.esrid = t3.parent_id
		WHERE t1.parent_id=$pid
		ORDER BY erid
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParentByStatus($pid){
        $query = <<<EOF
SELECT CONCAT(service.sn,'->',service.type,'->',service.model) as id,service.sn
FROM sor_lines
         LEFT JOIN sor_units ON sor_units.parent_id = sor_lines.id
         LEFT JOIN service ON service.sor_uid = sor_units.id
WHERE service.date_shipped = '0000-00-00' AND sor_lines.status<>'CLOSED' AND sor_lines.id=$pid
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function delete()
    {
        $query = <<<EOF
			DELETE FROM sor_units WHERE id=$this->itsID
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function duplicate($parent_id = null)
    {
        if (empty($this->itsID)) {
            return 'INTERNAL ERROR: could not duplicate SOR Unit header, internal identifier is undefined.';
        }

        if (!$parent_id) {
            $parent_id = $this->getParentID();
        }

        // 1 - duplicate header
        $query = <<<SQL
            INSERT INTO sor_units (parent_id, short_desc, long_desc, del_location, del_dat, ddel_est1, dgt_est, batch_qty, del_early, ddel_asm)
            (
                SELECT $parent_id, short_desc, long_desc, del_location, del_dat, ddel_est1, dgt_est, batch_qty, del_early, ddel_asm
                FROM sor_units as t2 WHERE t2.id=$this->itsID LIMIT 1
            )
SQL;
        $newid = tldUtils::sqlInsert($query);
        if (!is_numeric($newid)) {
            return "INTERNAL ERROR: could not duplicate SOR Unit header, $newid";
        }

        return $newid;
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }
}

/**
 * Class for accessing and manipulating SOR transactions
 *
 * @author Graham
 *
 */
class tldSORTran
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get the latest SOR TRANs, defaults to last 10
     *
     * @param integer $qty
     *
     * @return array
     */
    public static function byLatest($qty = 10)
    {
        $query = <<<EOF
        SELECT T1.*
        FROM sor_tran AS T1
        ORDER BY T1.dt DESC
        LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getHeader()
    {
        $query = <<<EOF
        SELECT T1.*
		FROM sor_tran AS T1
		WHERE T1.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byParent($pid)
    {
        $query = <<<EOF
		SELECT T1.*
		FROM sor_tran AS T1
		WHERE T1.parent_id=$pid
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getPostDateByType($type, $pid, $tgrp)
    {
        if (empty($type) || empty($tgrp)) {
            return 'ERROR: no transaction type or grp specified';
        }

        $query = <<<EOF
        SELECT t1.dtran
        FROM sor_tran AS t1
        WHERE
            t1.parent_id=$pid
            AND t1.tgrp='$tgrp'
            AND t1.ttyp='$type'
        ORDER BY t1.dtran DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byParentGroup($pid, $tgrp, $dcur = 'USD')
    {
        if (empty($pid) || empty($tgrp)) {
            return 'ERROR: no pid or grp specified';
        }
        if (empty($dcur)) {
            $dcur = 'USD';
        }
        //get the eur/usd rate for the SOL
        $rate = (new tldSOL($pid, true))->getEURForex();
        if ($rate === 0) {
            return 'ERROR: Could not find EUR forex rate for SOL';
        }
        $query = <<<EOF
		SELECT t1.*,
			'$dcur' AS dcur,
			t2.rate,
			t3.rate,
			ROUND(
			(if(t3.rate is null, 1, t3.rate))/(if(t2.rate is null, 1, t2.rate)),
			2) AS dcur_rate,
			ROUND(
			t1.tval*(if(t3.rate is null, 1, t3.rate))/(if(t2.rate is null, 1, t2.rate))
			,2) AS tval_dcur
 		FROM sor_tran AS t1 LEFT JOIN erp_forex2 AS t2
 			ON PERIOD_DIFF(DATE_FORMAT(CONCAT(t2.nam_year, '-', t2.nam_month, '-01'), '%Y%m'),
			            PERIOD_ADD(DATE_FORMAT(t1.dtran, '%Y%m'), -1)) = 0
			   AND t2.nam_cur=t1.tcur AND t2.typ='END'
    		LEFT JOIN erp_forex2 AS t3
 			ON PERIOD_DIFF(DATE_FORMAT(CONCAT(t3.nam_year, '-', t3.nam_month, '-01'), '%Y%m'),
			            PERIOD_ADD(DATE_FORMAT(t1.dtran, '%Y%m'), -1)) = 0
			   AND t3.nam_cur='$dcur' AND t3.typ='END'
    WHERE
			t1.parent_id=$pid
			AND t1.tgrp='$tgrp'
                        ORDER BY t1.dtran
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Sums tval grouped by SOL id, group type and tran type
     *
     * DOES NOT take care of Revenue values by negating them before summing
     *
     * @param integer $pid SOL id
     * @param string $tgrp transaction group to sum, SSO or ERP
     * @param string $ttyp transaction type to sum, B or R
     *
     * @param         $dcur
     *
     * @return string
     */
    public static function sumByParentGroupType($pid, $tgrp, $ttyp, $dcur)
    {
        if (empty($pid) || empty($tgrp) || empty($ttyp)) {
            return 'ERROR: no pid or grp or type specified';
        }
        //get the eur/usd rate for the SOL
        $rate = (new tldSOL($pid, true))->getEURForex();
        if ($rate == 0) {
            return 'ERROR: Could not find EUR forex rate for SOL';
        }
        $query = <<<EOF
		SELECT
            ROUND(SUM(t1.tval*(if(to_rate.rate is null, 1, to_rate.rate))/(if(from_rate.rate is null, 1, from_rate.rate))), 4) AS tval_dcur
        FROM sor_tran AS t1
             LEFT JOIN sor_lines sol ON t1.parent_id = sol.id
             LEFT JOIN erp_forex2 AS from_rate
                   ON from_rate.nam_year * 12 + from_rate.nam_month =
                      YEAR(sol.dt_opened) * 12 + MONTH(sol.dt_opened) - 1
                       AND from_rate.nam_cur = t1.tcur AND from_rate.typ = 'END'
             LEFT JOIN erp_forex2 AS to_rate
                   ON to_rate.nam_year * 12 + to_rate.nam_month =
                      YEAR(sol.dt_opened) * 12 + MONTH(sol.dt_opened) - 1
                       AND to_rate.nam_cur = '$dcur' AND to_rate.typ = 'END'
		WHERE
			t1.parent_id=$pid
			AND t1.tgrp='$tgrp'
			AND t1.ttyp='$ttyp'
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['tval_dcur'];
    }

    /**
     * Add a sales order transaction
     *
     * @param integer $pid SOL ID#
     * @param array $p associative array with following keys:
     *                     dtran date of transaction posting
     *                     tgrp transaction group, either SSO (Sales Org) or ERP for factory
     *                     ttyp transcation type either B for booking or R for revenue
     *                     val transcation value in USD
     *                     notes text notes
     *
     * @return mixed
     */
    public static function insert($pid, $p)
    {
        $fields = [
            'nref',
            'dref',
            'dtran',
            'tgrp',
            'ttyp',
            'tcur',
            'tval',
            'notes',
        ];
        $query = <<<EOF
    INSERT INTO sor_tran
    SET dt=NOW(), parent_id=$pid,
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Generic SOR Tran update method
     *
     * @param              $data   array of SOR Tran datas
     * @param array|string $fields array of SOR Tran fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        if (empty($this->itsID)) {
            return 'Not object context!';
        }
        $SET = tldUtils::getSqlSet($data, $fields);
        $query = "UPDATE sor_tran SET $SET WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Add a comment to the log
     *
     * @param     $id
     * @param     $comment
     * @param int $num_log
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $num_log = 0)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SOR Tran';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $num_log;

        return tldModLog::insert($a);
    }

    /** Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SOR Tran');
    }

    public static function byConstraints($a, $opt = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT * FROM sor_tran $WHERE ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

}

/**
 * Class for accessing and manipulating SOR Line data
 *
 * @package SalesAndService
 */
class tldSOL
{

    /**
     * @var array
     */
    private static $internalCategoriesList;
    /**
     * @var array
     */
    private static $externalCategoriesList;
    public $itsID;
    public $itsHeader;
    private $defaultCurrency;

    public function __construct($id, $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    public static function getMinEstGTDate($sol_id)
    {
        $query = <<<EOF
SELECT MIN(service.dgt_rev) AS min_est_gt
FROM sor_lines
LEFT JOIN sor_units ON sor_units.parent_id = sor_lines.id
LEFT JOIN service ON service.sor_uid = sor_units.id
WHERE sor_lines.id = $sol_id
EOF;

        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['min_est_gt'];
    }

    /**
     * Function to duplicate a SOL (not static)
     *
     * @param string|int $parent_id of sol (optional)
     *
     * @return    int    $newid of new SOL
     */
    public function duplicate($parent_id = '')
    {
        if (empty($this->itsID)) {
            return;
        }
        if (empty($parent_id)) {
            $parent_id = $this->getParent();
        }
        // 1 - duplicate header
        $query = <<<EOF
			INSERT INTO sor_lines (parent_id, dt_opened, status, bu, sls_orno, model, qty, del_pen,
				conf_sls, conf_erp, delpen_cond, wrty_spec, wrty_std, conf_wrty_erp, tpay, conf_cxo,
				cu_ocur, dp_amt, dp_pc, parts_inc, docs_inc,conf_lc, notes, trans, inco, inco_loc, conf_cis, ctry)
			SELECT $parent_id, NOW(),'PENDING', bu, sls_orno, model, qty, del_pen, conf_sls, conf_erp,
				delpen_cond, wrty_spec, wrty_std, conf_wrty_erp, tpay, conf_cxo, cu_ocur, dp_amt,
				dp_pc, parts_inc, docs_inc,conf_lc, notes, trans, inco, inco_loc, conf_cis, ctry
			FROM sor_lines as t2 WHERE t2.id=$this->itsID LIMIT 1
EOF;
        $newid = tldUtils::sqlInsert($query);
        if (!is_numeric($newid)) {
            return "INTERNAL ERROR: could not duplicate SOL header, $newid";
        }
        // 2 - duplicate dcur & rates
        $query = <<<EOF
			SELECT module, list_name, list_key, list_key2, value, value2
			FROM mod_lists WHERE module='SOL' AND parent_id=$this->itsID
EOF;
        $rates = tldUtils::getSqlToAssocArray($query);
        foreach ($rates as $rate) {
            $query = <<<EOF
			INSERT INTO mod_lists
			SET 
                parent_id = $newid,
                module = 'SOL',
                list_name = '{$rate['list_name']}',
                list_key = '{$rate['list_key']}',
                list_key2 = '{$rate['list_key2']}',
                value = '{$rate['value']}',
                value2 = '{$rate['value2']}'
EOF;
            $e .= tldUtils::sqlQuery($query);
        }

        // 3 - duplicate breakdown
        $query = <<<EOF
			INSERT INTO sor_opts (parent_id, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris)
			SELECT $newid, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris
			FROM sor_opts WHERE parent_id=$this->itsID
EOF;
        $e .= tldUtils::sqlQuery($query);
        // 4 - duplicate SOU
        $query = <<<EOF
			INSERT INTO sor_units (parent_id, short_desc, long_desc, dgt_est, batch_qty,del_early)
			SELECT $newid, short_desc, long_desc, dgt_est, batch_qty, "N"
			FROM sor_units WHERE parent_id=$this->itsID
EOF;
        $e .= tldUtils::sqlQuery($query);
        if ($e) {
            error_log($e);
        }

        return $newid;
    }

    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $tos = [
            $this->getSSOERP() => [
                'role_SA',
                'role_EVP',
                'role_CEO',
                'role_RCEO',
                'role_CFO',
                'role_FC',
            ],
            900 => [
                'role_CHAIRMAN',
                'ROLE_GCH',
                'role_COO',
                'role_CFO',
                'gg_ACCT',
            ],
            $this->getERP() => [
                'role_SA',
                'role_EVP',
                'role_CEO',
                'role_RCEO',
                'role_COO',
                'role_CFO',
                'role_FC',
            ],
        ];

        if (!in_array($this->getStatus(), ['PENDING', 'CREATE_PO'])) {
            $emailList = [];
            foreach ($tos as $level => $grps) {
                foreach ($grps as $grp) {
                    $g = new tldGroup($grp, $level);
                    $emailList[] = $g->getEmailList();
                }
            }

            $emailList = array_merge(...$emailList);

            $e = tldUtils::emailAttachment(
                array_unique($emailList),
                'noreply@tld-gse.com',
                "SOL#$this->itsID has been DELETED",
                $this->getPrintVersion()
            );
        }

        // Delete SOL
        $query = "DELETE FROM sor_lines WHERE id=$this->itsID LIMIT 1";
        $e .= tldUtils::sqlQuery($query);
        // Delete TRANSACTIONS
        $query = "DELETE FROM sor_tran WHERE parent_id=$this->itsID";
        $e .= tldUtils::sqlQuery($query);
        // Delete LIST (dcur & rates)
        $query = "DELETE FROM mod_lists WHERE module='SOL' AND parent_id=$this->itsID";
        $e .= tldUtils::sqlQuery($query);
        // Delete BREAKDOWN
        $query = "DELETE FROM sor_opts WHERE parent_id=$this->itsID";
        $e .= tldUtils::sqlQuery($query);
        // Clean ER linked to UNITS
        $fieldsToSet = [
            'sor_uid' => '',
            'buyer_customer_id' => '',
            'customer_id' => '',
            'customer_name' => '',
            'rrd_sso' => '',
            'rrd_erp' => '',
        ];
        $SET = tldUtils::getSqlSet($fieldsToSet);
        $query = "UPDATE service SET $SET WHERE sor_uid IN(SELECT id FROM sor_units WHERE parent_id=$this->itsID)";
        $e .= tldUtils::sqlQuery($query);
        // Delete UNITS
        $query = "DELETE FROM sor_units WHERE parent_id=$this->itsID";
        $e .= tldUtils::sqlQuery($query);
        // Log if errors
        if ($e) {
            error_log($e);
        }
    }

    public function getCC()
    {
        if (empty($this->itsID)) {
            return;
        }
        $opts = ['list_name' => 'sol.cc', 'mode' => 'smartyOptions', 'fields' => 'value', 'smartyFields' => 'value'];

        return tldModList::byParent($this->itsID, 'SOL', $opts);
    }

    /**
     * Get the list of currencies and rates used in this SOL
     *
     * @return array of db rows
     */
    public function getCURS()
    {
        return tldModList::byListName($this->itsID, 'SOL', 'CURS');
    }

    /**
     * Is the sol valid?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * get subtotal of breakdown
     *
     * converts everything to default currency if none specified
     *
     * @return mixed
     *
     */
    public function getSubtotals()
    {
        return tldSOROpts::totalsByParent($this->itsID);
    }

    /**
     * Get the number of units ordered
     *
     * @return integer
     */
    public function getQTY()
    {
        if (!$this->isEmpty()) {
            return $this->itsHeader['qty_sou'];
        }

        $row = tldUtils::getSqlRowToAssocArray("SELECT SUM(batch_qty) AS cnt FROM sor_units WHERE parent_id={$this->itsID}");

        return $row['cnt'];
    }

    public static function getInternalCategoriesList()
    {
        if (null === self::$internalCategoriesList) {
            self::$internalCategoriesList = tldList::optionsByListNameAsListItemListItem('list.sol.caty.int');
        }

        return self::$internalCategoriesList;
    }

    public static function getExternalCategoriesList()
    {
        if (null === self::$externalCategoriesList) {
            self::$externalCategoriesList = tldList::optionsByListNameAsListItemListItem('list.sol.caty.ext');
        }

        return self::$externalCategoriesList;
    }

    public function getOptionsByCategory($categoryArray, $dcur = '')
    {
        if (empty($dcur)) {
            $dcur = $this->getDCUR();
        }

        return tldSOROpts::byParent(
            $this->getID(),
            [
                'include' => $categoryArray,
                'dcur' => $dcur,
            ]
        );
    }

    public function getInternalOptions($dcur = '')
    {
        return $this->getOptionsByCategory(self::getInternalCategoriesList(), $dcur);
    }

    public function getExternalOptions($dcur = '')
    {
        return $this->getOptionsByCategory(self::getExternalCategoriesList(), $dcur);
    }

    /**
     * Get the summary values of this sol
     *
     * @param string $options
     *
     * @return array
     */
    public function getSummary($options = '')
    {
        $id = $this->itsID;
        $header = $this->itsHeader;
        $qty = $this->getQTY();
        if (isset($options['dcur']) && in_array($options['dcur'], ['USD', 'EUR'], true)) {
            $dcur = $options['dcur'];
        } else {
            $dcur = $this->getDCUR();
        }

        $blueprint = [
            'mrsp_tot_in_dcur' => null,
            'prip_tot_in_dcur' => null,
            'pric_tot_in_dcur' => null,
            'pris_tot_in_dcur' => null,
            'pris_tot' => null,
            'pric_tot' => null,
            'prip_tot' => null,
            'mrsp_tot' => null,
        ];

        $keys = array_keys($blueprint);
        $totals = array_fill_keys(['internal', 'internalDefaultCurrency', 'external', 'externalDefaultCurrency', 'baseUnit', 'otherTotals', 'agentCommission', 'parts', 'taxes', 'transportation', 'misc', 'specialDiscount'], $blueprint);

        // Get categories of the internal transaction
        $intCaty = self::getInternalCategoriesList();
        // Get categories of the external transaction
        $extCaty = self::getExternalCategoriesList();

        foreach (tldSOROpts::totalsOptionsByParent($id, ['dcur' => $dcur]) as $total) {
            if (in_array($total['caty'], $intCaty, true)) {
                foreach ($keys as $key) {
                    $totals['internal'][$key] = (string) ($totals['internal'][$key] + $total[$key]);
                }
                if ('BASE UNIT' === $total['caty']) {
                    $totals['baseUnit'] = $total;
                    unset($totals['baseUnit']['caty']);
                }
            } elseif (in_array($total['caty'], $extCaty, true)) {
                foreach ($keys as $key) {
                    $totals['external'][$key] = (string) ($totals['external'][$key] + $total[$key]);
                }
                if (in_array($total['caty'], ['SPARE PARTS', 'COMPONENTS'], true)) {
                    foreach ($keys as $key) {
                        $totals['otherTotals'][$key] = (string) ($totals['otherTotals'][$key] + $total[$key]);
                    }
                    if ('SPARE PARTS' === $total['caty']) {
                        $totals['parts'] = $total;
                        unset($totals['parts']['caty']);
                    }
                } elseif ('AGENT COMMISSION' === $total['caty']) {
                    $totals['agentCommission'] = $total;
                    unset($totals['agentCommission']['caty']);
                }  elseif ('TAXES AND DUTIES' === $total['caty']) {
                    $totals['taxes'] = $total;
                    unset($totals['taxes']['caty']);
                } elseif ('TRANSPORTATION' === $total['caty']) {
                    $totals['transportation'] = $total;
                    unset($totals['transportation']['caty']);
                } elseif ('MISC. ITEMS' === $total['caty']) {
                    $totals['misc'] = $total;
                    unset($totals['misc']['caty']);
                } elseif ('SPECIAL DISCOUNT' === $total['caty']) {
                    $totals['specialDiscount'] = $total;
                    unset($totals['specialDiscount']['caty']);
                }
            }
        }

        if ($dcur === $this->getDCUR()) {
            $totals['internalDefaultCurrency'] = $totals['internal'];
            $totals['externalDefaultCurrency'] = $totals['external'];
        } else {
            foreach (tldSOROpts::totalsOptionsByParent($id, ['dcur' => $this->getDCUR()]) as $total) {
                if (in_array($total['caty'], $intCaty, true)) {
                    foreach ($keys as $key) {
                        $totals['internalDefaultCurrency'][$key] = (string) ($totals['internalDefaultCurrency'][$key] + $total[$key]);
                    }
                } elseif (in_array($total['caty'], $extCaty, true)) {
                    foreach ($keys as $key) {
                        $totals['externalDefaultCurrency'][$key] = (string) ($totals['externalDefaultCurrency'][$key] + $total[$key]);
                    }
                }
            }
        }

        // Get AGENT COMMISSION
        // sales
        $a['agent_com'] = $totals['agentCommission']['pris_tot_in_dcur'];
        $a['agent_com_tot'] = $a['agent_com'] * $qty;
        // cost
        $a['pric_agent_com'] = $totals['agentCommission']['pric_tot_in_dcur'];
        $a['pric_agent_com_tot'] = $a['pric_agent_com'] * $qty;

        // Get SPARE PARTS
        $a['parts'] = $totals['parts']['pris_tot_in_dcur'];
        // Get TAXES AND DUTIES
        $a['tax'] = $totals['taxes']['pris_tot_in_dcur'];
        // Get TRANSPORTATION
        $a['trans'] = $totals['transportation']['pris_tot_in_dcur'];
        $a['trans_tot'] = $a['trans'] * $qty;
        // Get MISC ITEMS
        $a['misc'] = $totals['misc']['pris_tot_in_dcur'];

        $a['spe_disc'] = $totals['specialDiscount']['pris_tot_in_dcur'];

        // Quantity
        $a['qty'] = $qty;

        // Total Internal transaction
        $a['pris_tot_int_dcur'] = $totals['internal']['pris_tot_in_dcur'] * $qty;
        // Total External transaction
        $a['pris_tot_ext_dcur'] = $totals['external']['pris_tot_in_dcur'] * $qty;

        // Unit Gross Selling Price = Internal + External transaction
        $a['pris_unit'] = $totals['internal']['pris_tot_in_dcur'] + $totals['external']['pris_tot_in_dcur'];
        // Unit Gross Selling Price in default currency
        $a['pris_unit_default_cur'] = $totals['internalDefaultCurrency']['pris_tot_in_dcur'] + $totals['externalDefaultCurrency']['pris_tot_in_dcur'];
        // Total Unit Gross Selling Price = Unit Gross Selling Price * Quantity
        $a['pris_xtot'] = $a['pris_unit'] * $qty;
        // Total Unit Gross Selling Price in default currency
        $a['pris_xtot_default_cur'] = $a['pris_unit_default_cur'] * $qty;

        // Calculate down payment if saved as a percentage, otherwise use the value
        $a['dp'] = (int) $header['dp_amt'] !== 0 ? $header['dp_amt'] : $header['dp_pc'] * $a['pris_xtot'] / 100;

        // Exworks sales price = Actual Net selling price (Internal transaction)
        $a['pris_exw_unit'] = $totals['internal']['pris_tot_in_dcur'];
        // Exworks total price = Exworks sales price * Quantity
        $a['pris_exw_xtot'] = $a['pris_exw_unit'] * $qty;

        // Unit Net Selling Price = Unit Gross Selling Price - External transactions cost
        $a['prin_unit'] = $a['pris_unit'] - $totals['external']['pric_tot_in_dcur'] + $totals['otherTotals']['pric_tot_in_dcur'];
        // Total Net Selling Price = Unit Net Selling Price * Quantity
        $a['prin_xtot'] = $a['prin_unit'] * $qty;

        // Customer discount = [Total of Published Price List + Total Cost Price of Spare Parts and Components /0.9 + (sum of External Item Costs – Cost of Spare Parts and Components)] – [Unit Gross Selling Price]
        $customerDiscount = round(
            $totals['internal']['prip_tot_in_dcur'] + ($totals['otherTotals']['pric_tot_in_dcur'] / 0.9) + ($totals['external']['pric_tot_in_dcur'] - $totals['otherTotals']['pric_tot_in_dcur']) - $a['pris_unit'],
            2
        );

        $a['discc'] = 'No discount';
        $a['discc_pc'] = '';

        if ($customerDiscount > 0 && $totals['internal']['prip_tot_in_dcur'] <> 0) {
            $a['discc'] = $customerDiscount;
            $a['discc_pc'] = round(
                $a['discc'] * 100 / ($totals['internal']['prip_tot_in_dcur'] + ($totals['external']['pric_tot_in_dcur'] / 0.9)),
                2
            );
        }

        $a['customer_discount_infos'] = [
            'discount' => $customerDiscount,
            'prip_tot_in_dcur' => $totals['internal']['prip_tot_in_dcur'],
            'pric_tot_in_dcur' => $totals['external']['pric_tot_in_dcur'],
        ];

        // Negociated TP
        $a['pris_tp_in_dcur'] = $totals['internal']['pric_tot_in_dcur'];
        // Negociated TP in default cur
        $a['pris_tp_default_dcur'] = $totals['internalDefaultCurrency']['pric_tot_in_dcur'];
        $a['pris_tp_default_curency'] = $this->getDCUR();

        // Published TP
        $a['published_tp'] = $totals['internal']['mrsp_tot_in_dcur'];

        // Factory discount = Published TP - Negotiated TP
        if ($totals['internal']['mrsp_tot_in_dcur'] > $totals['internal']['pric_tot_in_dcur']) {
            $a['discf'] = $totals['internal']['mrsp_tot_in_dcur'] - $totals['internal']['pric_tot_in_dcur'];
            $a['factory_discount'] = $a['discf'];
            $a['discf_pc'] = round($a['discf'] * 100 / $totals['internal']['mrsp_tot_in_dcur'], 2);
        } else {
            $a['discf'] = 'No discount';
            $a['factory_discount'] = 0;
            $a['discf_pc'] = '';
        }

        // Unit cost = Negotiated TP + External transaction cost
        $a['cost_unit'] = $totals['internal']['pric_tot_in_dcur'] + $totals['external']['pric_tot_in_dcur'];
        // Total cost = Unit cost * Quantity
        $a['cost_xtot'] = $a['cost_unit'] * $qty;

        // Unit margin = Gross Selling Price 锟�Unit Cost
        $a['marg_unit'] = $a['pris_unit'] - $a['cost_unit'];
        // Unit margin percentage = Unit margin*100 / Unit Net Selling Price
        if ((int)$a['prin_unit'] !== 0) {
            $a['marg_unit_pc'] = round($a['marg_unit'] * 100 / $a['prin_unit'], 2);
        } else {
            $a['marg_unit_pc'] = 0;
        }
        // Total margin = Unit margin * Quantity
        $a['marg_xtot'] = $a['marg_unit'] * $qty;

        return $a;
    }

    public function getTranSummary($dcur = '')
    {
        if (empty($dcur)) {
            $dcur = $this->getDCUR();
        }
        //SSO bookings total
        $a['ssob_tot'] = round(tldSORTran::sumByParentGroupType($this->itsID, 'SSO', 'B', $dcur), 2);
        //SSO revenue total
        $a['ssor_tot'] = round(tldSORTran::sumByParentGroupType($this->itsID, 'SSO', 'R', $dcur), 2);
        //SSO backlog total
        $a['ssok_tot'] = round($a['ssob_tot'] - $a['ssor_tot'], 2);
        //ERP bookings total
        $a['erpb_tot'] = round(tldSORTran::sumByParentGroupType($this->itsID, 'ERP', 'B', $dcur), 2);
        //ERP revenue total
        $a['erpr_tot'] = round(tldSORTran::sumByParentGroupType($this->itsID, 'ERP', 'R', $dcur), 2);
        //ERP backlog total
        $a['erpk_tot'] = round($a['erpb_tot'] - $a['erpr_tot'], 2);

        return $a;
    }

    /**
     *  Add SSO Booking transaction difference
     *
     * @param string $notes
     *
     * @return string <type>
     */
    public function addTranSSOB($notes = '')
    {
        if (empty($this->itsID)) {
            return 'ERROR: SOL ID not set.';
        }
        $query = <<<EOF
INSERT INTO sor_tran (parent_id, dtran, tgrp, ttyp, tcur, tval, notes)
SELECT $this->itsID, now(), 'SSO', 'B', curs.pris_cur,
    (SELECT IF(SUM(opts.pris) is null, 0, SUM(opts.pris)) FROM sor_opts as opts
     WHERE opts.pris_cur=curs.pris_cur AND opts.parent_id=$this->itsID)
     *(SELECT IF(SUM(batch_qty) is null, 0, SUM(batch_qty))
        FROM sor_units WHERE parent_id=$this->itsID)-
    (SELECT IF(SUM(trans.tval) is null, 0, SUM(trans.tval))
    FROM sor_tran AS trans
    WHERE trans.parent_id=$this->itsID AND trans.tcur=curs.pris_cur
        AND trans.tgrp='SSO'
        AND trans.ttyp='B'
    ) AS pris_diff,
    '$notes'
FROM
(
(SELECT DISTINCT pris_cur FROM sor_opts WHERE parent_id=$this->itsID)
UNION
(SELECT DISTINCT tcur FROM sor_tran WHERE parent_id=$this->itsID)
) AS curs
HAVING pris_diff<>0
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function addTranERPB($notes = '')
    {
        if (empty($this->itsID)) {
            return 'ERROR: SOL ID not set.';
        }
        $query = <<<EOF
INSERT INTO sor_tran (parent_id, dtran, tgrp, ttyp, tcur, tval, notes)
SELECT $this->itsID, now(), 'ERP', 'B', curs.pric_cur,
    (SELECT IF(SUM(opts.pric) is null, 0, SUM(opts.pric)) FROM sor_opts as opts
     WHERE opts.pric_cur=curs.pric_cur
       AND opts.caty IN ('OPTION','BASE UNIT')
        AND opts.parent_id=$this->itsID)
     *(SELECT IF(SUM(batch_qty) is null, 0, SUM(batch_qty))
        FROM sor_units WHERE parent_id=$this->itsID)-
    (SELECT IF(SUM(trans.tval) is null, 0, SUM(trans.tval))
    FROM sor_tran AS trans
    WHERE trans.parent_id=$this->itsID
        AND trans.tcur=curs.pric_cur
        AND trans.tgrp='ERP'
        AND trans.ttyp='B'
    ) AS pric_diff,
    '$notes'
FROM
(
(SELECT DISTINCT pric_cur FROM sor_opts WHERE parent_id=$this->itsID)
UNION
(SELECT DISTINCT tcur FROM sor_tran WHERE parent_id=$this->itsID)
) AS curs
HAVING pric_diff<>0
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get header information for this SOL
     *
     * @return array
     */
    public function getHeader()
    {
        if (!$this->itsID || !is_numeric($this->itsID)) {
            return [];
        }

        $query = <<<EOF
SELECT
    sol.*,
	sor.bu AS sso_erp,
	(SELECT location FROM locations WHERE sor.sso=locations.id) AS sso_fullname,
	(SELECT erp FROM locations WHERE sol.bu=locations.id) AS bu_erp,
    (SELECT location FROM locations WHERE sol.bu=locations.id) AS bu_fullname,
    (SELECT location FROM locations WHERE sol.factory_shipping=locations.id) AS factory_shipping_fullname,
    IF(sor.dt_closed > '0000-00-00 00:00:00', sor.cu_nama, (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)) AS user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.buyer_customer_id) AS buyer_customer_display,
    sor.cu_nama,
    sor.user_customer_id,
    sor.buyer_customer_id,
    (SELECT SUM(sous.batch_qty) FROM sor_units AS sous WHERE sol.id=sous.parent_id) AS qty_sou,
    (SELECT SUM(ers2.er_batch_qty) FROM service AS ers2, sor_units AS sous2 WHERE sol.id=sous2.parent_id AND sous2.id=ers2.sor_uid) AS qty_alloc,
    (SELECT SUM(ers3.er_batch_qty) FROM service AS ers3, sor_units AS sous3 WHERE sol.id=sous3.parent_id AND sous3.id=ers3.sor_uid AND ers3.date_shipped NOT LIKE '0000-00-00') AS qty_ship,
    asm.id AS asmID,
    asm.email AS asm_email,
    sor.eqno
FROM
    sor_lines AS sol
	LEFT JOIN sor ON sor.id=sol.parent_id
	LEFT JOIN people AS asm ON asm.id=sor.asm
WHERE
    sol.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Does this SOL have enough ERs assigned to it?
     *
     * @return boolean
     */
    public function isFullyAllocated()
    {
        return $this->itsHeader['qty_alloc'] >= $this->itsHeader['qty_sou'];
    }

    /**
     * Are all units shipped?
     *
     * @return boolean
     */
    public function isFullyShipped()
    {
        return $this->itsHeader['qty_ship'] >= $this->itsHeader['qty_sou'];
    }

    /**
     * Have all units been green tagged?
     *
     * @return boolean
     */
    public function isFullyGreenTagged()
    {
        $rows = tldSORUnit::byParent($this->itsID);
        if (!count($rows)) {
            return true;
        }
        foreach ($rows as $row) {
            if ($row['dgt_act'] == '0000-00-00') {
                return false;
            }
        }

        return true;
    }

    /**
     * Have all units have a manual linked?
     *
     * @return boolean
     */
    public function isFullyLinkedToManual()
    {
        $rows = tldSORUnit::byParent($this->itsID);
        if (!count($rows)) {
            return true;
        }
        foreach ($rows as $row) {
            $er = new tldEquipment($row['erid']);
            $manuals = $er->getManuals();
            if (count($manuals) < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Have all units have a APC?
     *
     * @return boolean
     */
    public function isAPCFullySet()
    {
        $rows = tldSORUnit::byParent($this->itsID);
        if (!count($rows)) {
            return true;
        }
        foreach ($rows as $row) {
            $er = new tldEquipment($row['erid']);
            if (empty($er->itsDetails['airport_code'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Have all tasks linked to the SOl closed?
     *
     * @return boolean
     */
    public function hasAllTasksClosed()
    {
        return empty(tldTask::byParent($this->itsID, 'SOL'));
    }

    /**
     * @return bool
     */
    public function hasMissingExportLicence()
    {
        return 'REQUIRED' === $this->itsHeader['export_licence_status'];
    }

    /**
     * @return bool
     */
    public function hasEngineeringFlag()
    {
        return (bool)$this->itsHeader['engineering_flag'];
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SOL', 'ALL');
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SOL', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SOL', $module);
    }

    /**
     * Insert a new SOL into the database
     *
     * @param array $p
     *
     * @param       $uid
     *
     * @return int|string
     */
    public static function insert($p, $uid)
    {
        $fields = [
            'parent_id',
            'bu',
            'model',
            'eng_tier',
            'qty',
            'del_pen',
            'delpen_cond',
            'wrty_spec',
            'wrty_std',
            'warranty_length',
            'warranty_length_hours',
            'tpay',
            'dp_pc',
            'dp_amt',
            'cu_ocur',
            'parts_inc',
            'docs_inc',
            'conf_lc',
            'notes',
            'conf_sls',
            'conf_erp',
            'inco',
            'inco_loc',
            'conf_cis',
            'ctry',
            'intro_new',
            'sfr_id',
            'delivery_address',
            'fms_contract_length',
            'trans',
            'factory_shipping',
        ];
        $query = <<<EOF
            INSERT INTO sor_lines
        	SET status='PENDING', dt_opened=NOW(),
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);
        $id = tldUtils::sqlInsert($query);
        if (is_int($id)) {
            $sol = new tldSOL($id);
            $sol->addLogEntry($uid, "SOL $id created");

            return $id;
        }

        return 'ERROR: There was an error inserting SOL...';
    }

    /**
     * Add an option to this SOL by creating and linking a tldSOROpts
     *
     * @param array $p
     *
     * @return array
     */
    public function addOpt($p)
    {
        $p['parent_id'] = $this->itsID;

        return tldSOROpts::insert($p);
    }

    /**
     * Refresh the header information in the object variable
     *
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get id number
     *
     * @return integer
     */
    public function getID()
    {
        return $this->itsHeader['id'];
    }

    /**
     * Get SOR ID
     *
     * @return integer
     */
    public function getParent()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getModel()
    {
        return $this->itsHeader['model'];
    }

    public function getCustomerName()
    {
        return $this->itsHeader['cu_nama'];
    }

    public function getASMID()
    {
        return $this->itsHeader['asmID'];
    }

    public function getASMEmail()
    {
        return $this->itsHeader['asm_email'];
    }

    public function getDZKSSO()
    {
        return $this->itsHeader['dzk_sso'];
    }

    public function getDZKERP()
    {
        return $this->itsHeader['dzk_erp'];
    }

    public function getCreationDate()
    {
        return $this->itsHeader['dt_opened'];
    }

    public function getEquoteID()
    {
        return $this->itsHeader['eqno'];
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SOL');
    }

    public function addCC($uid)
    {
        if (empty($this->itsID) || empty($uid) || !is_numeric($uid)) {
            return;
        }
        $a = [
            'parent_id' => $this->itsID,
            'module' => 'SOL',
            'list_name' => 'sol.cc',
            'value' => $uid,
        ];

        return tldModList::insert($a);
    }

    public function addFile($modFileInfoArray, $fileUploadArray)
    {
        $modFileInfoArray['module'] = 'SOL';
        $modFileInfoArray['parent_id'] = $this->getID();

        return tldModFile::insert($modFileInfoArray, $fileUploadArray);
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SOL');
    }

    /**
     * Get warranty conditions
     *
     * @return string
     */
    public function getWrtyCond()
    {
        return $this->itsHeader['wrty_cond'];
    }

    /**
     * Get special warranty conditions
     *
     * @return string
     */
    public function getWrtySpec()
    {
        return $this->itsHeader['wrty_spec'];
    }

    /**
     * Get standard warranty conditions
     *
     * @return string
     */
    public function getWrtyStd()
    {
        return $this->itsHeader['wrty_std'];
    }

    /**
     * Get the breakdown list of options
     *
     * @return array of db rows
     */
    public function getBreakdown($options = [])
    {
        return tldSOROpts::byParent($this->itsID, $options);
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SOL';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    /**
     * @return array
     */
    public function getPreviousStatuses()
    {
        $logs = array_column(tldModLog::byConstraints("module='SOL' AND parent_id={$this->itsID} AND comment LIKE 'Status moved to %' "), 'comment');

        $regex = "/Status moved to (\w+)/";
        $statuses = array_map(static function ($str) use ($regex) {
            $matches = [];
            preg_match($regex, $str, $matches);
            return array_pop($matches);
        }, $logs);

        return array_unique($statuses);
    }

    public function updateTran()
    {
        //check at what status we are
        $this->refresh();
        $status = $this->getStatus();
        //if before PRINT_SO_ACK then do nothing
        if (in_array($status, ['PENDING', 'CREATE_PO', 'EVP_APPROVAL'])) {
            return;
        }
        //we must be after PRINT_SO_ACK so update SSO Booking
        $this->addTranSSOB('Automated SSO Booking');

        if (count(array_intersect($this->getPreviousStatuses(), ['PRINT_SO_ACK', 'PRINT_PO', 'CREATE_FACTORY_SO'])) !== 3) {
            return;
        }

        $allowedStatuses = [
            'PRINT_FACTORY_SO_ACK',
            'ER_ASSIGNMENT',
            'ENGINEER_REVIEW',
            'ENGINEERING_APPROVAL',
            'MATERIALS_PLANNING',
            'PSM_APPROVAL',
            'IN_PROGRESS',
            'SHIPPED',
            'CLOSED',
        ];

        if (in_array('PRINT_FACTORY_SO_ACK', $this->getPreviousStatuses(), true)) {
            $allowedStatuses = array_merge(['PRINT_SO_ACK', 'PRINT_PO', 'CREATE_FACTORY_SO'], $allowedStatuses);
        }

        //if after PRINT_FACTORY_SO_ACK then update ERP Booking
        if (in_array($status, $allowedStatuses, true) && $this->getDZKERP() === '0000-00-00') {
            $this->addTranERPB('Automated ERP Booking');

            return;
        }

        //fallback if status unknown.
        return "ERROR: Status $status not known when updating transactions.";
    }

    /**
     * Add a sales order transaction
     *
     * @param string $dtran date of transaction posting
     * @param string $tgrp transaction group, either SSO (Sales Org) or ERP for factory
     * @param string $ttyp transaction type either B for booking or R for revenue
     * @param        $tcur
     * @param string $val transaction value in USD
     * @param string $notes text notes
     *
     * @param        $nref
     * @param        $dref
     *
     * @return mixed
     */
    public function addSORTran($dtran, $tgrp, $ttyp, $tcur, $val, $notes, $nref, $dref)
    {
        if (empty($this->itsID)) {
            return 'ERROR: no SOL id set in tldSOL object.';
        }
        $p = [
            'nref' => $nref,
            'dref' => $dref,
            'dtran' => $dtran,
            'tgrp' => $tgrp,
            'ttyp' => $ttyp,
            'tcur' => $tcur,
            'tval' => $val,
            'notes' => $notes,
        ];

        return tldSORTran::insert($this->itsID, $p);
    }

    public function addSORTranSSOB($dtran, $tcur, $val, $notes, $nref = '', $dref = '')
    {
        return $this->addSORTran($dtran, 'SSO', 'B', $tcur, $val, $notes, $nref, $dref);
    }

    public function addSORTranSSOR($dtran, $tcur, $val, $notes, $nref = '', $dref = '')
    {
        return $this->addSORTran($dtran, 'SSO', 'R', $tcur, $val, $notes, $nref, $dref);
    }

    public function addSORTranERPB($dtran, $tcur, $val, $notes, $nref = '', $dref = '')
    {
        return $this->addSORTran($dtran, 'ERP', 'B', $tcur, $val, $notes, $nref, $dref);
    }

    public function addSORTranERPR($dtran, $tcur, $val, $notes, $nref = '', $dref = '')
    {
        return $this->addSORTran($dtran, 'ERP', 'R', $tcur, $val, $notes, $nref, $dref);
    }

    /**
     * Negates the remaining backlog so balance is zero
     *
     * @param string $notes
     *
     * @return string on error
     */
    public function cancelKSSO($notes = '')
    {
        $dcur = $this->getDCUR();
        if (empty($dcur)) {
            return 'ERROR: No default currency set in SOL';
        }
        //set current date
        $cdate = date('Y-m-d');
        $s = $this->getTranSummary($dcur);

        //negate any booking value left
        if ((int)$s['ssok_tot'] !== 0) {
            $e = $this->addSORTranSSOB(
                $cdate,
                $dcur,
                (-1) * $s['ssok_tot'],
                'SOL Backlog Cancellation' . $notes
            );
        }

        //delete unneeded SOU

        //set zero backlog date
        $e .= $this->setDZKSSO($cdate);

        return $e;
    }

    public function cancelKERP($notes = '')
    {
        $dcur = $this->getDCUR();
        if (empty($dcur)) {
            return 'ERROR: No default currency set in SOL';
        }
        //set current date
        $cdate = date('Y-m-d');
        $s = $this->getTranSummary($dcur);

        //negate any booking value left
        if ($s['erpk_tot'] <> 0) {
            $e = $this->addSORTranERPB(
                $cdate,
                $dcur,
                (-1) * $s['erpk_tot'],
                'SOL Backlog Cancellation' . $notes
            );
        }

        //delete unneeded SOU

        //set zero backlog date
        $e .= $this->setDZKERP($cdate);

        return $e;
    }

    /**
     * Add a list entry
     *
     * @param $name
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function addListEntry($name, $key, $value)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SOL';
        $a['list_name'] = $name;
        $a['list_key'] = $key;
        $a['value'] = $value;

        return tldModList::insert($a);
    }

    /**
     * Get the default currency for this SOL
     *
     * @return string
     */
    public function getDCUR()
    {
        if (null === $this->defaultCurrency) {
            $rows = array_values(tldModList::byListKey($this->itsID, 'SOL', 'DCUR', ''));
            $this->defaultCurrency = $rows[0] ?? null;
        }

        return $this->defaultCurrency;
    }

    /**
     * get the eur/usd forex rate for this SOL
     *
     * @return string
     */
    public function getEURForex()
    {
        $rows = array_values(tldModList::byListKey($this->itsID, 'SOL', 'CURS', 'EUR'));

        return $rows[0];
    }

    /**
     * set the sales org SO number
     *
     * @param string $orno
     *
     * @return boolean
     */
    public function setSLS_ORNO($orno)
    {
        return $this->updateHeader(['sls_orno' => $orno]);
    }

    /**
     * set the factory SO number
     *
     * @param string $orno
     *
     * @return boolean
     */
    public function setERP_ORNO($orno)
    {
        return $this->updateHeader(['erp_orno' => $orno]);
    }

    /**
     * Set date of zero backlog
     *
     * @param string $dzk
     *
     * @return boolean
     */
    public function setDZKSSO($dzk)
    {
        return $this->updateHeader(['dzk_sso' => $dzk]);
    }

    public function setDZKERP($dzk)
    {
        return $this->updateHeader(['dzk_erp' => $dzk]);
    }

    /**
     * Update header using an array
     *
     * @param array $a
     *
     * @return boolean
     */
    public function updateHeader($a)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No sor line id set in updateHeader function';
        }
        if (empty($a)) {
            return 'ERROR: Empty params in updateHeader function';
        }
        $SET = tldUtils::getSqlSet($a);
        $query = <<<EOF
		UPDATE sor_lines
		SET $SET
		WHERE id=$this->itsID
        LIMIT 1
EOF;


        return tldUtils::sqlQuery($query);
    }

    public function updateMargin($a)
    {
        if (empty($this->itsID)) {
            return 'ERROR: No sor line id set in updateHeader function';
        }
        if (empty($a)) {
            return 'ERROR: Empty params in updateMargin function';
        }
        $query = <<<EOF
        UPDATE sor_lines
        SET factory_margin = $a
        WHERE id=$this->itsID
        LIMIT 1
EOF;


        return tldUtils::sqlQuery($query);
    }

    /**
     * Get all SOLs associated with a particular SOR
     *
     * @param integer $pid
     * @param string $status
     *
     * @return array
     */
    public static function byParent($pid, $status = '')
    {
        $WHERE = $status ? " AND T1.status='$status'" : '';

        $query = <<<EOF
		SELECT T1.*,
			(SELECT SUM(sor_units.batch_qty) FROM sor_units WHERE sor_units.parent_id=T1.id) AS qty_sou,
			sor.cu_nama,
			IF(sor.dt_closed > '0000-00-00 00:00:00',sor.cu_nama,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)) AS user_customer_display,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.buyer_customer_id) AS buyer_customer_display,
			(SELECT erp FROM locations WHERE locations.id=sor.bu) AS sor_erp,
			(SELECT location FROM locations WHERE T1.bu=locations.id) AS bu_fullname,
			(SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4 WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid) AS qty_alloc,
			(SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4 WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid AND T3.date_shipped != '0000-00-00') AS qty_ship
			FROM sor_lines AS T1
				LEFT JOIN sor ON sor.id=T1.parent_id
		    WHERE T1.parent_id=$pid
		    $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Is this SOR closed?
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->getStatus() === 'CLOSED';
    }

    /**
     * Get the current status of this SOR
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function areAllLinesPastEVP($pid = null)
    {
        if (empty($pid)) {
            $pid = $this->getParentID();
        }
        $query = <<<EOF
		    SELECT COUNT(*) as cnt
		    FROM sor_lines
		    WHERE parent_id=$pid AND status IN ('PENDING','CREATE_PO')
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);
        return ((int) $row['cnt']) === 0;
    }

    /**
     * Get the current SOL create_factory Date
     */
    public function isCreateFactorySO()
    {
        return $this->itsHeader['dt_create_factory'] === '0000-00-00 00:00:00';
    }

    /**
     * Get the erp company number
     */
    public function getERP()
    {
        return $this->itsHeader['bu_erp'];
    }

    public function getSSOERP()
    {
        return $this->itsHeader['sso_erp'];
    }

    public function getFactoryID()
    {
        return $this->itsHeader['bu'];
    }

    public function getFactoryERP()
    {
        return $this->getERP();
    }

    public static function getStatusList()
    {
        return [
            'PENDING',
            'CREATE_PO',
            'EVP_APPROVAL',
            'PRINT_SO_ACK',
            'CREATE_SO_ACK',
            'PRINT_PO',
            'CREATE_FACTORY_SO',
            'PRINT_FACTORY_SO_ACK',
            'ENGINEER_REVIEW',
            'ENGINEERING_APPROVAL',
            'MATERIALS_PLANNING',
            'PSM_APPROVAL',
            'IN_PROGRESS',
            'SHIPPED',
            'CLOSED',
        ];
    }

    public static function getSSOStatusList(): array
    {
        return [
            'PENDING',
            'CREATE_PO',
            'EVP_APPROVAL',
            'PRINT_SO_ACK',
            'CREATE_SO_ACK',
            'PRINT_PO',
            'CLOSED',
        ];
    }

    public static function getFactoryStatusList(): array
    {
        return [
            'CREATE_FACTORY_SO',
            'PRINT_FACTORY_SO_ACK',
            'ENGINEER_REVIEW',
            'ENGINEERING_APPROVAL',
            'MATERIALS_PLANNING',
            'PSM_APPROVAL',
            'IN_PROGRESS',
            'SHIPPED',
            'CLOSED',
        ];
    }

    /**
     * Get allowed status changes according to user id
     *
     * @param integer $uid
     *
     * @return mixed string on error, otherwise array
     */
    public function getStatusAllowed($uid)
    {
        $curStatus = $this->getStatus();
        $user = new tldUser($uid);
        $userBU = new tldLocation($user->getBUID());
        // From current status
        switch ($curStatus) {
            case 'PENDING':
                $res = ['fwd' => 'CREATE_PO'];
                break;
            case 'CREATE_PO':
                $res = ['fwd' => 'EVP_APPROVAL'];
                break;
            case 'EVP_APPROVAL':
                $res = [
                    'back' => 'CREATE_PO',
                    'fwd' => 'PRINT_SO_ACK',
                ];
                break;
            case 'PRINT_SO_ACK':
                $res = ['fwd' => 'CREATE_SO_ACK'];
                break;
            case 'CREATE_SO_ACK':
                $res = ['fwd' => 'PRINT_PO'];
                break;
            case 'PRINT_PO':
                $res = ['fwd' => 'CREATE_FACTORY_SO'];
                break;
            case 'CREATE_FACTORY_SO':
                $res = [
                    'back' => 'PRINT_PO',
                    'fwd' => 'PRINT_FACTORY_SO_ACK',
                ];
                break;
            case 'PRINT_FACTORY_SO_ACK':
                $res = [
                    'fwd' => 'ENGINEER_REVIEW',
                    'fwd2' => 'IN_PROGRESS',
                ];
                break;
            case 'ENGINEER_REVIEW':
                $res = [
                    'back' => 'PRINT_FACTORY_SO_ACK',
                    'fwd' => 'ENGINEERING_APPROVAL',
                ];
                break;
            case 'ENGINEERING_APPROVAL':
                $res = [
                    'back' => 'ENGINEER_REVIEW',
                    'fwd' => 'MATERIALS_PLANNING',
                ];
                break;
            case 'MATERIALS_PLANNING':
                $res = [
                    'back' => 'ENGINEERING_APPROVAL',
                    'fwd' => 'PSM_APPROVAL',
                ];
                break;
            case 'PSM_APPROVAL':
                $res = [
                    'back' => 'MATERIALS_PLANNING',
                    'fwd' => 'IN_PROGRESS',
                ];
                break;
            case 'IN_PROGRESS':
                $res = ['fwd' => 'SHIPPED'];
                break;
            case 'SHIPPED':
                $res = ['fwd' => 'CLOSED'];
                break;
        }
        // Check permissions based on job role and CURRENT status
        if (in_array($curStatus, ['PENDING', 'CREATE_PO', 'PRINT_PO', 'PRINT_SO_ACK', 'CREATE_SO_ACK'])
            && !$user->isInGroup(['role_SA'])
        ) {
            $res = 'ERROR: only Sales Administrator can change status of this SOL';
        }
        if ('EVP_APPROVAL' === $curStatus
            && !$user->isInGroup(['role_EVP', 'seq_sol.status.evp_approval'])
        ) {
            $res = 'ERROR: only EVP can change status of this SOL';
        }
        if ('ENGINEER_REVIEW' === $curStatus
            && !$user->isInGroup(['gg_ENG'])
        ) {
            $res = 'ERROR: only ENGINEERS can change status of this SOL';
        }
        if ('ENGINEERING_APPROVAL' === $curStatus
            && !$user->isInGroup(['role_EM'])
        ) {
            $res = 'ERROR: only Engineering Manager can change status of this SOL';
        }
        if ('MATERIALS_PLANNING' === $curStatus
            && !$user->isInGroup(['role_MLM'])
        ) {
            $res = 'ERROR: only Logistic Manager can change status of this SOL';
        }
        if ('PSM_APPROVAL' === $curStatus
            && !$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])
        ) {
            $res = 'ERROR: only PSM,PSE,PSA can change status of this SOL';
        }
        if ('SHIPPED' === $curStatus && !$user->isInGroup('role_SA')) {
            if (!$user->isInGroupLevel('gg_SUPPORT', $this->getERP())) {
                $res = 'ERROR: only SUPPORT and Sales Admin can change status of this SOL';
            } elseif ($userBU->itsDetails['factory'] === 'Y' && $this->itsHeader['inco'] !== 'EXW') {
                $res = "ERROR: Cannot close SOL, incoterm isn't EXW...";
            }
        }
        if (in_array($curStatus, ['CREATE_FACTORY_SO', 'PRINT_FACTORY_SO_ACK', 'IN_PROGRESS'], true)
            && !$user->isInGroupLevel('gg_SUPPORT', $this->getERP())
        ) {
            $res = 'ERROR: only SUPPORT can change status of this SOL';
        }

        return $res;
    }

    /** @return string|bool */
    public function checkCustomerStatus()
    {
        if (!$this->getParentID()) {
            return 'Unable to retrieve SOR a SOR linked to this SOL.';
        }

        // We don't want to rely on the API as the access to the SOR info are restricted to more people than the one in charge of moving SOL statuses
        $sor = new tldSOR($this->getParentID());

        if (true !== (bool) $sor->itsHeader['buyer_approved']) {
            return sprintf("The customer filled as SOR buyer ('%s', legacy id:%s) is not APPROVED.", $sor->itsHeader['buyer_customer_display'], $sor->itsHeader['buyer_customer_id']);
        }

        if (true !== (bool) $sor->itsHeader['user_approved']) {
            return sprintf("The customer filled as SOR end user ('%s', legacy id:%s) is not APPROVED.", $sor->itsHeader['user_customer_display'], $sor->itsHeader['user_customer_id']);
        }

        if (!empty(trim($sor->itsHeader['agnt_nama']))) {
            $agent = new tldCustomer($sor->itsHeader['agnt_nama']);
            if (!$agent->isEmpty() && !$agent->isApproved()) {
                return sprintf("The customer filled as SOR sales agent ('%s', legacy id:%s) is not APPROVED.", $sor->itsHeader['agnt_nama'], $agent->itsID);
            }
        }

        return true;
    }

    /**
     * Change the status of this SOL
     *
     * If no status is specified then the current allowed statuses are returned
     *
     * @param string $status
     * @param              $uid
     * @param array|string $options
     *
     * @return mixed
     */
    public function changeStatus($status, $uid, $options = '')
    {
        if (empty($this->itsID)) {
            return false;
        }
        $header = $this->getHeader();

        if (true !== $result = $this->checkCustomerStatus()) {
            return $result;
        }

        $SSO_ERP = $this->getSSOERP();
        $MAN_ERP = $this->getERP();

        // Set flag to make status checks by default
        $FLAG_STATUS = true;
        // Set flag to update transaction by default
        $FLAG_TRANS = true;
        // Set flag to check if normal closure or if cancel
        $FLAG_CANC = false;

        // Misc cases
        switch ($status) {
            case 'CANCEL':
                // Check ER
                $ER = tldSORUnit::byParent($this->itsID);
                foreach ($ER as $er) {
                    if (!empty($er['erid'])) {
                        return 'There is still ER assigned';
                    }
                }
                // Check transactions
                $tranSummary = $this->getTranSummary();
                foreach ($tranSummary as $key => $val) {
                    if ($val <> 0) {
                        return 'There is still transactions to manage';
                    }
                }
                // ok to close
                $status = 'CLOSED';
                $FLAG_STATUS = false;
                $FLAG_TRANS = false;
                $FLAG_CANC = true;
                break;
            case 'CLOSED':
                // Get all units and ER linked
                $ER = tldSORUnit::byParent($this->itsID);
                // TRANSPORTATION
                if (!count($ER)) {
                    $FLAG_STATUS = false;    // no unit so ok to close
                    $FLAG_TRANS = false;     // and no auto booking
                    break;
                }
                // CANCELLATION CASE
                $customerList = [];
                $erList = [];
                foreach ($ER as $er) {
                    $customerList[] = $er['customer_name'];
                    $erList[] = $er['erid'];
                }
                $customerList = array_unique($customerList);
                $erList = array_unique($erList);
                // --- Check partial cancellation (STOCK UNIT)
                if (count($customerList) === 1 && $customerList[0] === 'STOCK UNIT') {
                    $FLAG_STATUS = false;    // all ER stock units
                    $FLAG_TRANS = false;     // and no auto booking
                    break;
                }
                // --- Check complete cancellation (unit but no ER linked)
                if (count($erList) === 1 && empty($erList[0])) {
                    $FLAG_STATUS = false;    // no er id found
                    $FLAG_TRANS = false;     // and no auto booking
                    break;
                }
                break;
            case 'ENGINEER_REVIEW':
                $engReviewStatus = [
                    'ENGINEER_REVIEW',
                    'ENGINEERING_APPROVAL',
                    'MATERIALS_PLANNING',
                    'PSM_APPROVAL',
                    'IN_PROGRESS',
                    'SHIPPED',
                ];
                // Check if the FOR process not passed
                if (!in_array($this->getStatus(), $engReviewStatus, true)) {
                    break;
                }
                // Check if not PSM
                $userStatus = new tldUser($uid);
                if (!$userStatus->isInGroup(['gg_ADMIN', 'role_PSM'])) {
                    break;
                }
                // Else it is a restart of FOR process
                $FLAG_STATUS = false;
                break;
        }

        // If normal workflow, check if the status is allowed
        if ($FLAG_STATUS) {
            $allowed = $this->getStatusAllowed($uid);
            if (!is_array($allowed)) {
                return $allowed;
            }
            if (!in_array($status, $allowed, true)) {
                return "Status '$status' not allowed";
            }
        }
        // If changing to CLOSED
        $SET = '';
        if ($status === 'CLOSED') {
            $SET = ', dt_closed=NOW()';
        } elseif ($status === 'CREATE_FACTORY_SO' && $this->isCreateFactorySO()) {
            $SET = ', dt_create_factory=NOW()';
        }
        // Update the status
        $query = <<<EOF
		UPDATE sor_lines
		SET status=UCASE('$status')
            $SET
		WHERE id=$this->itsID
		LIMIT 1
EOF;
        $res = tldUtils::sqlQuery($query);

        $this->refresh();
        // Special case no Auto Booking when SOL for **DEMO** ID#3588
        if ((int)$header['user_customer_id'] === 3588 || (int)$header['buyer_customer_id'] === 3588) {
            $FLAG_TRANS = false;
        }
        //Need to add SSO Booking transactions
        if ($FLAG_TRANS) {
            $this->updateTran();
        }

        //send notification
        $message = <<<EOF
<p><a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$this->itsID">
SOL#$this->itsID status updated to $status, click here to view online.</a></p>
EOF;
        // Append any additional message from options array
        $message .= ($options['msg'] ?? ''). '<br/>';

        // Get recipients
        $TO = [];
        switch ($status) {
            case 'PENDING':
                $TO[$SSO_ERP] = ['role_SA'];
                $TO[$MAN_ERP] = ['role_PSM', 'role_PSE', 'role_PSA'];
                break;
            case 'CREATE_PO':
            case 'CREATE_SO_ACK':
            case 'PRINT_PO':
                $TO[$SSO_ERP] = ['role_SA'];
                break;
            case 'EVP_APPROVAL':
                $TO[$SSO_ERP] = [
                    'role_SA',
                    'role_EVP',
                ];
                break;
            case 'PRINT_SO_ACK':
                $TO[$SSO_ERP] = [
                    'role_SA',
                    'role_EVP',
                    'role_RCOO',
                    'role_CEO',
                    'role_RCEO',
                    'role_CFO',
                ];
                $TO[900] = [
                    'role_CHAIRMAN',
                    'ROLE_GCH',
                    'role_COO',
                    'role_RCOO',
                    'role_CFO',
                    'gg_ACCT',
                ];
                $TO[$MAN_ERP] = [
                    'role_CEO',
                    'role_RCEO',
                    'role_RCOO',
                    'role_COO',
                    'role_CFO',
                ];
                $customer = new tldCustomer($header['user_customer_id']);
                if (!$customer->isEmpty() && $customer->isMilitary()) {
                    $TO[''] = ['ROLE_VPM'];
                }

                break;
            case 'CREATE_FACTORY_SO':
                $TO[$SSO_ERP] = ['role_SA'];
                $TO[$MAN_ERP] = ['role_COO', 'role_RCOO', 'role_CEO', 'role_RCEO', 'role_CFO', 'role_PSM', 'role_PSE', 'role_PSA'];
                break;
            case 'PRINT_FACTORY_SO_ACK':
            case 'PSM_APPROVAL':
                $TO[$MAN_ERP] = ['role_PSM', 'role_PSE', 'role_PSA'];
                break;
            case 'ENGINEER_REVIEW':
                $TO[$MAN_ERP] = ['gg_ENG', 'role_EM', 'role_MLM', 'role_planner', 'role_PSM', 'role_PSE', 'role_PSA', 'role_PM', 'role_QAM'];
                break;
            case 'ENGINEERING_APPROVAL':
                $TO[$MAN_ERP] = ['role_EM'];
                break;
            case 'MATERIALS_PLANNING':
                $TO[$MAN_ERP] = ['role_MLM', 'role_planner'];
                break;
            case 'IN_PROGRESS':
                // @todo
                //$TO[$MAN_ERP] = array("gg_SUPPORT","role_PM");
                break;
            case 'SHIPPED':
                $TO[$MAN_ERP] = ['gg_SUPPORT'];
                $TO[$SSO_ERP] = ['role_SA'];
                break;
            case 'CLOSED':
                if ($FLAG_CANC) {
                    $TO[$MAN_ERP] = ['gg_SUPPORT', 'role_CA'];
                } else {
                    $TO[$MAN_ERP] = ['gg_SUPPORT'];
                }
                $TO[$SSO_ERP] = ['role_SA'];
                break;
        }
        // Notification --->
        //check if the BU is here, if no BU no email notification will be sent.
        if ((int)$header['bu'] !== 0) {
            // Get recipients
            $emailList = [];
            foreach ($TO as $level => $grps) {
                foreach ($grps as $grp) {
                    $cList = $emailList;
                    $g = new tldGroup($grp, $level === '' ? null : $level);
                    $message .= "<br/>GROUP $grp, Level $level<br/>";
                    $gList = $g->getEmailList();
                    if (count($gList)) {
                        $emailList = array_merge($cList, $gList);
                        $message .= implode(', ', $gList);
                    } else {
                        $message .= 'No users found in this group...';
                    }
                }
            }
            if ('PRINT_SO_ACK' === $status) {
                $moo = (new tldUser(tldModule::getMOOIDByModule('sol')))->getEmail();
                $emailList[] = $moo;
                $message .= "<br/>SOL MOO<br/>$moo";
            }
            // Have email list clean
            $emailList = array_unique($emailList);
            // Look for people to CC
            $CC = [];
            if (!empty($options['cc'])) {
                $CC = array_unique($options['cc']);
            }
            // Message contents
            if ($status !== 'PRINT_SO_ACK') {
                // by default always add the print version
                $message .= $this->getPrintVersion();
            }

            $e = tldUtils::emailAttachment(
                $emailList,
                'noreply@tld-gse.com',
                "{$header['model']}, {$header['qty_sou']}, {$header['cu_nama']}, SOL#$this->itsID, $status",
                $message,
                null,
                $CC
            );
        }

        return $res;
    }

    public function changeStatusAdmin(string $status): void
    {
        $query = <<<EOF
		UPDATE sor_lines
		SET status=UCASE('$status')
		WHERE id=$this->itsID
		LIMIT 1
EOF;
        tldUtils::sqlQuery($query);
    }

    /**
     * Get sor lines that have not been fulfilled yet
     *
     * @return array array of db rows
     */
    public static function byUnassigned()
    {
        $query = <<<EOF
		SELECT t1.*,
			(SELECT SUM(sous.batch_qty) FROM sor_units AS sous
                WHERE t1.id=sous.parent_id
            ) AS qty_sou,
            (SELECT SUM(ers2.er_batch_qty) FROM service AS ers2, sor_units AS sous2
                WHERE t1.id=sous2.parent_id AND sous2.id=ers2.sor_uid
            ) AS qty_alloc,
            (SELECT SUM(ers3.er_batch_qty) FROM service AS ers3, sor_units AS sous3
                WHERE t1.id=sous3.parent_id AND sous3.id=ers3.sor_uid AND ers3.date_shipped != '0000-00-00'
            ) AS qty_ship,
			sor.cu_nama,
			IF(sor.dt_closed > '0000-00-00 00:00:00',sor.cu_nama,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)) AS user_customer_display,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.buyer_customer_id) AS buyer_customer_display,
			(SELECT location FROM locations WHERE t1.bu=locations.id) AS erp_fullname
		FROM sor_lines AS t1
			LEFT JOIN sor ON sor.id=t1.parent_id
		GROUP BY t1.id
		HAVING qty_sou<>qty_alloc
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $a
     */
    public static function countBySSOERPConstraints($a = [])
    {
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        $WHERE = $a;
        $query = <<<EOF
		SELECT
			factory.location AS erp_fullname,
			sso.location AS sso_fullname,
			count(*) as num
		FROM sor_lines AS sol
			LEFT JOIN locations AS factory ON sol.bu=factory.id
			LEFT JOIN sor ON sor.id=sol.parent_id
			LEFT JOIN locations AS sso ON sor.sso=sso.id
		WHERE $WHERE
		GROUP BY erp_fullname, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByERPCustomerByASM($asm, $sso)
    {
        if (!empty($sso)) {
            $sso = new tldLocation($sso);
            $sso_erp = $sso->getERP();
            $WHERE = " sor.bu=$sso_erp";
        } else {
            $WHERE = " sor.asm=$asm ";
        }
        $query = <<<EOF
        SELECT
            factory.location AS erp_fullname,
            customers.customer_name AS customer,
            count(*) as num
        FROM sor_lines AS sol
            LEFT JOIN locations AS factory ON sol.bu=factory.id
            LEFT JOIN sor ON sor.id=sol.parent_id
            LEFT JOIN customers ON sor.buyer_customer_id=customers.id
        WHERE $WHERE AND sol.status <> 'CLOSED' AND factory.location IS NOT NULL AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(sol.dt_opened,'%Y%m')) BETWEEN 0 AND 12
        GROUP BY erp_fullname, customer
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of SOLs by factory and status
     *
     * If $uid is given then output is customized for the role of the user
     *
     *     *
     * @param int|string $uid
     *
     * @return array
     */
    public static function countByERPStatus($uid = '')
    {
        $WHERE = '';
        if ($uid > 0) {
            $groups = [];
            $erps = [];
            $user = new tldUser($uid);
            //for support people
            if ($levels = $user->isInGroup('gg_SUPPORT')) {
                if (is_array($levels)) {
                    $erps += $levels;
                } else {
                    $erps[] = $levels;
                }
                array_push(
                    $groups,
                    'CREATE_FACTORY_SO',
                    'PRINT_FACTORY_SO_ACK',
                    'ER_ASSIGNMENT',
                    'IN_PROGRESS'
                );
            }
            if (!count($groups) && !count($erps)) {
                return;
            }
            $where = [];
            if (count($groups)) {
                $where[] = " status IN('" . implode('\',\'', $groups) . "')";
            }
            if (count($erps)) {
                $where[] = ' T2.erp IN (' . implode(',', $erps) . ')';
            }
            $WHERE = ' WHERE ' . implode(' AND ', $where);
        }
        $query = <<<EOF
		SELECT T1.status, T2.location as location, count(*) as num
		FROM sor_lines AS T1 LEFT JOIN locations AS T2 ON T1.bu=T2.id
            $WHERE
		GROUP BY location,status
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of SOLs by sales org and status
     *
     * If $uid is given then output is customized for the role of the user
     *
     * @param int|string $uid
     *
     * @return array
     */
    public function countBySSOStatus($uid = '')
    {
        $WHERE = '';
        if ($uid > 0) {
            $groups = $erps = [];
            $user = new tldUser($uid);
            if ($levels = $user->isInGroup('gg_SALES')) {
                if (is_array($levels)) {
                    $erps += $levels;
                } else {
                    $erps[] = $levels;
                }
                array_push($groups, 'PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_PO');
            }
            if (!$groups && !$erps) {
                return;
            }
            $where = [];
            if (count($groups)) {
                $where[] = " T1.status IN('" . implode('\',\'', $groups) . "')";
            }
            if (count($erps)) {
                $where[] = ' sor.bu IN (' . implode(',', $erps) . ')';
            }
            $WHERE = ' AND ' . implode(' AND ', $where);
        }
        $query = <<<EOF
		SELECT T1.status, T2.location as location, count(*) as num
		FROM sor_lines AS T1, sor LEFT JOIN locations AS T2 ON sor.sso=T2.id
		WHERE sor.id=T1.parent_id
            $WHERE
		GROUP BY T1.status, location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countSalesOrderAcknowledgementBySSO($dateFrom, $dateUntil = null)
    {
        $WHERE = "AND trans.dtran >= '$dateFrom' ";
        $WHERE .= $dateUntil ? " AND trans.dtran <= '$dateUntil' " : '';

        $query = <<<SQL
SELECT
    l.location,
    COUNT(DISTINCT f.parent_id) AS uniqueSOLNumber,
    COUNT(DISTINCT f.id) AS receiptsNumber,
    GROUP_CONCAT(DISTINCT sols.id) AS solList
FROM mod_files f
INNER JOIN sor_lines sols ON sols.id=f.parent_id AND f.module='SOL'
INNER JOIN sor_tran AS trans ON sols.id=trans.parent_id
INNER JOIN sor ON sols.parent_id=sor.id
INNER JOIN locations l ON sor.sso=l.id
WHERE
    f.description REGEXP '^SOL#\\\\d{5} Acknowledgment - '
    AND trans.ttyp='B' AND trans.tgrp='SSO'
    AND sor.buyer_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    AND sor.user_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    $WHERE
GROUP BY l.location
ORDER BY l.location
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySalesOrderAcknowledgementBySSO($sso, $dateFrom, $dateUntil = null)
    {
        $WHERE = "AND trans.dtran >= '$dateFrom' ";
        $WHERE .= $dateUntil ? " AND trans.dtran <= '$dateUntil' " : '';
        $WHERE .= $sso !== 'ALL' ? " AND l.location = '$sso' " : '';
        $query = <<<SQL
SELECT
    f.parent_id as sol_id
FROM mod_files f
INNER JOIN sor_lines sols ON sols.id=f.parent_id AND f.module='SOL'
INNER JOIN sor_tran AS trans ON sols.id=trans.parent_id
INNER JOIN sor ON sols.parent_id=sor.id
INNER JOIN locations l ON sor.sso=l.id
WHERE
    f.description REGEXP '^SOL#\\\\d{5} Acknowledgment - '
    AND trans.ttyp='B' AND trans.tgrp='SSO'
    AND sor.buyer_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    AND sor.user_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    $WHERE
GROUP BY sol_id
SQL;
        $ids = array_column(tldUtils::getSqlToAssocArray($query), 'sol_id');
        if (!$ids) {
            return [];
        }

        return self::byConstraints('T1.id IN (' . implode(',', $ids) . ')', ['select' => "(SELECT CONCAT(firstname, ' ', lastname) FROM people WHERE id=T3.asm ) AS asm_fullname,"]);
    }

    public static function countSalesOrderEligibleToAcknowledgementBySSO($dateFrom, $dateUntil = null)
    {
        $WHERE = "AND trans.dtran >= '$dateFrom' ";
        $WHERE .= $dateUntil ? " AND trans.dtran <= '$dateUntil' " : '';

        $query = <<<SQL
SELECT
    l.location,
    COUNT(DISTINCT sols.id) as uniqueSOLNumber,
    GROUP_CONCAT(DISTINCT sols.id) AS solList
FROM sor_lines AS sols
INNER JOIN sor ON sor.id=sols.parent_id
INNER JOIN sor_tran AS trans ON sols.id=trans.parent_id
INNER JOIN locations AS l ON sor.sso = l.id
WHERE
    trans.ttyp='B' AND trans.tgrp='SSO'
    AND (SELECT COUNT(*) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE 'Status moved to ENGINEER_REVIEW%') > 0
    AND sor.buyer_customer_id NOT IN (SELECT id from customers where customer_name IN ('**STOCK**', '**DEMO**'))
    AND sor.user_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    $WHERE
GROUP BY l.location
ORDER BY l.location
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySalesOrderEligibleToAcknowledgementBySSO($sso, $dateFrom, $dateUntil = null)
    {
        $WHERE = "AND trans.dtran >= '$dateFrom' ";
        $WHERE .= $dateUntil ? " AND trans.dtran <= '$dateUntil' " : '';
        $WHERE .= $sso !== 'ALL' ? " AND l.location = '$sso' " : '';

        $query = <<<SQL
SELECT
    sols.id as sol_id
FROM sor_lines AS sols
INNER JOIN sor ON sor.id=sols.parent_id
INNER JOIN sor_tran AS trans ON sols.id=trans.parent_id
INNER JOIN locations AS l ON sor.sso = l.id
WHERE
    trans.ttyp='B' AND trans.tgrp='SSO'
    AND (SELECT COUNT(*) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE 'Status moved to ENGINEER_REVIEW%') > 0
    AND sor.buyer_customer_id NOT IN (SELECT id from customers where customer_name IN ('**STOCK**', '**DEMO**'))
    AND sor.user_customer_id NOT IN (SELECT id from customers where customer_name IN('**STOCK**', '**DEMO**'))
    $WHERE
GROUP BY sol_id
ORDER BY l.location
SQL;

        $ids = array_column(tldUtils::getSqlToAssocArray($query), 'sol_id');
        if (!$ids) {
            return [];
        }

        return self::byConstraints('T1.id IN (' . implode(',', $ids) . ')', ['select' => "(SELECT CONCAT(firstname, ' ', lastname) FROM people WHERE id=T3.asm ) AS asm_fullname,"]);
    }

    /**
     * Get rows of SOLs by sales org and status
     *
     * ALL special keyword
     *
     * @param string $location location name
     * @param string $status
     *
     * @return array
     */
    public static function bySSOStatus($location, $status)
    {
        $where = [];
        if ($location !== 'ALL') {
            $where[] = "T2.location='$location'";
        }
        if ($status !== 'ALL') {
            $where[] = "T1.status='$status'";
        }
        if (count($where)) {
            $WHERE = ' AND ' . implode(' AND ', $where);
        }
        $query = <<<EOF
		SELECT T1.*, T2.location ,
			(SELECT SUM(t4.batch_qty) FROM sor_units AS t4 WHERE T1.id=t4.parent_id) AS qty_sou,
			(SELECT SUM(T3.er_batch_qty) FROM service AS T3 WHERE T3.sor_lid=T1.id) AS qty_alloc,
  			(SELECT SUM(T2.er_batch_qty)
  			FROM service AS T2
			WHERE sor.id=T1.parent_id AND T2.sor_lid=T1.id AND T2.date_shipped != '0000-00-00')
			 AS qty_ship
		FROM sor_lines AS T1, sor LEFT JOIN locations AS T2 ON sor.sso=T2.id
		WHERE sor.id=T1.parent_id
            $WHERE
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function ERBySSOStatus($location)
    {
        $where = [];
        if ($location !== 'ALL' && !empty($location)) {
            $where[] = "service.man_location='$location'";
        }
        if (count($where)) {
            $WHERE = ' AND ' . implode(' AND ', $where);
        }
        $query = <<<EOF
		SELECT sor_lines.*
        FROM sor_lines
        LEFT JOIN sor_units ON sor_units.parent_id = sor_lines.id
        LEFT JOIN service ON service.sor_uid = sor_units.id
        WHERE service.date_shipped = '0000-00-00' AND sor_lines.status <> 'CLOSED'
        $WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SOR lines by Factory and Status
     *
     * @param string $location
     * @param string $status
     *
     * @return array
     */
    public static function byERPStatus($location, $status)
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['T2.location'] = $location;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }

        $conditions = "AND caty IN ('" . implode("','", tldSOL::getInternalCategoriesList()) . "')";

        $extraSelect['select'] = <<<SQL
            (SELECT GROUP_CONCAT(sor_opts.dsca  SEPARATOR '\\n *') FROM sor_opts WHERE sor_opts.parent_id = T1.id $conditions) AS options,
            T1.ctry AS 'delivery_country',
        SQL;

        return self::byConstraints($a, $extraSelect);
    }

    /**
     * Get SOR lines options by Factory and Status
     *
     * @param string $location
     * @param string $status
     *
     * @return array
     */
    public static function optionsByERPStatus($location, $status)
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['T2.location'] = $location;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }

        $options['from'] = 'JOIN sor_opts ON sor_opts.parent_id = T1.id';
        $options['select'] = "sor_opts.dsca as options, CONCAT(sor_opts.mrsp, ' ', sor_opts.mrsp_cur) as publishedTP_currency, CONCAT(sor_opts.prip, ' ', sor_opts.prip_cur) as publishedPriceList_currency, ";
        $options['where'] = " WHERE caty IN ('" . implode("','", tldSOL::getInternalCategoriesList()) . "')";

        return self::byConstraints($a, $options);
    }

    public static function byCreateFactoryDate($start, $end, $bu = '')
    {
        $a = "T1.dt_create_factory BETWEEN '$start' AND '$end'";
        if ($bu != '') {
            $a .= " AND T1.bu=$bu";
        }

        return self::byConstraints($a);
    }

    /**
     * Get SOR lines by Factory by Model
     *
     * @param string $location
     * @param        $customer
     * @param        $asm
     * @param        $sso
     *
     * @return array
     */
    public static function byERPCustomerByASM($location, $customer, $asm, $sso)
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['erp_fullname'] = $location;
        }
        if ($customer !== 'ALL') {
            $a['buyer_customer_display'] = $customer;
        }
        if (!empty($sso)) {
            $sso = new tldLocation($sso);
            $sso_erp = $sso->getERP();
            $WHERE = " T3.bu=$sso_erp";
        } else {
            $WHERE = " T3.asm=$asm ";
        }

        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $HAVING = !empty($HAVING) ? "HAVING $HAVING AND " : 'HAVING ';

        $query = <<<EOF
        SELECT
            T1.*,
            T3.cu_nama,
            T3.asm,
            T3.bu,
            CONCAT(UPPER(T5.lastname), ', ', T5.firstname) AS asm_fullname,
            T3.user_customer_id,
            T3.buyer_customer_id,
            IF(T3.dt_closed > '0000-00-00 00:00:00',
                T3.cu_nama,
                (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.user_customer_id)
            ) AS user_customer_display,
            (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.buyer_customer_id) AS buyer_customer_display,
            T2.location AS erp_fullname,
            T4.location AS sso_fullname,
            (SELECT SUM(t4.batch_qty) FROM sor_units AS t4 WHERE T1.id=t4.parent_id) AS qty_sou,
            (SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
            WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid
            ) AS qty_alloc,
            (SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
            WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid AND T3.date_shipped != '0000-00-00'
            ) AS qty_ship
        FROM sor_lines AS T1
            LEFT JOIN locations AS T2 ON T1.bu=T2.id
            LEFT JOIN sor AS T3 ON T3.id=T1.parent_id
            LEFT JOIN locations AS T4 ON T3.bu=T4.erp
            LEFT JOIN people AS T5 ON T3.asm = T5.id
        $HAVING $WHERE and T1.status <> 'CLOSED' AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(T1.dt_opened,'%Y%m')) BETWEEN 0 AND 12
        ORDER BY T1.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SOL list of SSO/ERP cash forecast
     *
     * @param string $type
     * @param string $location
     *
     * @return array or string error
     */
    public static function byCashForecast($type, $location)
    {
        if (!in_array($type, ['SSO', 'ERP'])) {
            return;
        }
        $location = TldDatabase::escape($location);
        switch ($type) {
            case 'SSO':
                $constraints = " T1.status IN('PENDING','CREATE_PO','EVP_APPROVAL') AND sso_fullname LIKE '$location' ";
                break;
            case 'ERP':
                $constraints = " T1.status NOT IN('PRINT_FACTORY_SO_ACK','ER_ASSIGNMENT','ENGINEER_REVIEW','ENGINEERING_APPROVAL','MATERIALS_PLANNING','PSM_APPROVAL','IN_PROGRESS','SHIPPED','CLOSED') AND erp_fullname LIKE '$location' ";
                break;
            default:
                $constraints = '';
        }

        return self::byConstraints($constraints);
    }

    public static function byLateFOR()
    {
        $constraints = <<<EOF
	T1.status IN('ENGINEER_REVIEW','ENGINEERING_APPROVAL','MATERIALS_PLANNING','PSM_APPROVAL')
	AND DATEDIFF(
		NOW(),
		(SELECT MIN(date) FROM mod_logs WHERE parent_id=T1.id
			AND module='SOL' AND comment LIKE 'ENGINEER_REVIEW%')
	) > 7
EOF;

        return self::byConstraints($constraints);
    }

    /**
     * Get rows of SOLs by SSO, by ERP and by status
     *
     * ALL special keyword
     *
     * @param string $sso location name
     * @param string $erp location name
     * @param string|array $status
     *
     * @return array
     */
    public static function bySSOERPStatus($sso, $erp, $status)
    {
        $where = [];
        if ($sso !== 'ALL') {
            $where[] = "T3.location='$sso'";
        }
        if ($erp !== 'ALL') {
            $where[] = "T2.location='$erp'";
        }
        if (\is_array($status) && count($status)) {
            $where[] = "T1.status IN ('" . implode("','", $status) . "')";
        } elseif (\is_string($status) && $status !== 'ALL') {
            $where[] = "T1.status='$status'";
        }

        $WHERE = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = <<<EOF
		SELECT T1.*,
		    sor.cu_nama,
			IF(sor.dt_closed > '0000-00-00 00:00:00',sor.cu_nama,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)) AS user_customer_display,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.buyer_customer_id) AS buyer_customer_display,
		    sor.orno as sls_orno,
			T2.location as erp_fullname,
			T3.location as sso_fullname,
			(SELECT SUM(sous.batch_qty) FROM sor_units AS sous
                WHERE T1.id=sous.parent_id
            ) AS qty_sou,
            (SELECT SUM(ers2.er_batch_qty) FROM service AS ers2, sor_units AS sous2
                WHERE T1.id=sous2.parent_id AND sous2.id=ers2.sor_uid
            ) AS qty_alloc,
            (SELECT SUM(ers3.er_batch_qty) FROM service AS ers3, sor_units AS sous3
                WHERE T1.id=sous3.parent_id AND sous3.id=ers3.sor_uid AND ers3.date_shipped != '0000-00-00'
            ) AS qty_ship
		FROM sor_lines AS T1
			LEFT JOIN sor ON sor.id=T1.parent_id
			LEFT JOIN locations AS T3 ON sor.sso=T3.id
			LEFT JOIN locations AS T2 ON T1.bu=T2.id
		$WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactoryEstimatedGTPeriod()
    {
        $query = <<<EOF
SELECT
    er.man_location,
    (CASE
        WHEN er.dgt_rev BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 WEEK) THEN '1 Week'
        WHEN er.dgt_rev BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 WEEK) THEN '2 Weeks'
        WHEN er.dgt_rev BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN '1 Month'
        ELSE 'ALL'
    END) AS period,
    COUNT(*) AS GTCount
FROM
    service AS er
    LEFT JOIN sor_units AS units ON er.sor_uid=units.id
    LEFT JOIN sor_lines AS sol ON units.parent_id=sol.id
    LEFT JOIN sor ON sol.parent_id=sor.id
WHERE
    sol.parts_inc='Y'
    AND er.dgt_act='0000-00-00'
    AND er.date_shipped='0000-00-00'
GROUP BY
    period, er.man_location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SOR lines by Factory and Estimated GT Date period
     *
     * @param string $factory
     * @param string $period
     *
     * @param null $a
     *
     * @return array
     */
    public static function byFactoryEstimatedGTPeriodByConstraints($factory = 'ALL', $period = 'ALL', $a = null, $strictPeriod = false)
    {
        // Constraints
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        // Factory
        if ($factory !== 'ALL') {
            $WHERE[] = "er.man_location LIKE '$factory'";
        }
        // Period
        switch ($period) {
            case '1 Week':
                $WHERE[] = 'er.dgt_rev BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 WEEK)';
                break;
            case '2 Weeks':
                $startDate = $strictPeriod ? 'DATE_ADD(CURDATE(), INTERVAL 1 WEEK)' : 'CURDATE()';
                $WHERE[] = "er.dgt_rev BETWEEN $startDate AND DATE_ADD(CURDATE(), INTERVAL 2 WEEK)";
                break;
            case '1 Month':
                $startDate = $strictPeriod ? 'DATE_ADD(CURDATE(), INTERVAL 2 WEEK)' : 'CURDATE()';
                $WHERE[] = "er.dgt_rev BETWEEN $startDate AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'Unknow':
                $WHERE[] = "(er.dgt_rev LIKE '0000-00-00' OR er.dgt_rev IS NULL)";
                break;
            default:
            case 'ALL':
                $WHERE[] = 'er.dgt_rev > CURDATE()';
                break;
        }
        // Construct constraints
        if ($WHERE) {
            $WHERE = ' AND ' . implode(' AND ', $WHERE);
        }
        // Query
        $query = <<<EOF
SELECT
    sol.*,
    sor.bu,
    (SELECT customer_name FROM customers WHERE id=sor.user_customer_id) AS user_customer_display,
    (SELECT customer_name FROM customers WHERE id=sor.buyer_customer_id) AS buyer_customer_display,
    er.sn,
    er.dgt_rev,
    er.man_location AS factory_fullname,
    (SELECT SUM(sous.batch_qty) FROM sor_units AS sous WHERE sol.id=sous.parent_id) AS qty_sou,
    (SELECT SUM(T10.er_batch_qty) FROM service AS T10 WHERE T10.sor_lid=sol.id) AS qty_alloc,
    (SELECT SUM(T11.er_batch_qty) FROM service AS T11 WHERE T11.sor_lid=sol.id AND T11.date_shipped != '0000-00-00') AS qty_ship
FROM
    service AS er
    LEFT JOIN sor_units AS units ON er.sor_uid = units.id
    LEFT JOIN sor_lines AS sol ON units.parent_id = sol.id
    LEFT JOIN sor ON sol.parent_id = sor.id
WHERE
    sol.parts_inc = 'Y'
    AND er.dgt_act='0000-00-00'
    AND er.date_shipped='0000-00-00'
    $WHERE
ORDER BY
    er.dgt_rev
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * SO Report for AERO ERP 250
     * Returns Sales Orders by Constraints
     *
     * @param string $begin
     * @param string $end
     * @param array $constraints
     *
     * @return array
     */
    public static function getSalesOrdersAeroReport($begin, $end, array $constraints = [])
    {
        $purchasingReport = [];
        $purchasingReport['receiptDate'] = 'CONVERT(VARCHAR(10), MAX(PUR2.t_date), 120) AS PO_receipt_date,';
        $where = " LOT.t_clot like '%{$constraints['t_clot']}%' ";

        // Put the date period if we do not search by SN
        if (!empty($begin) && !empty($end) && empty($constraints['t_clot'])) {
            $where = '';
            $datePeriodSales = " CONVERT(VARCHAR(10), SLS.t_odat, 120) BETWEEN '$begin%' AND '$end%' ";
            $datePeriodPur = " CONVERT(VARCHAR(10), PUR.t_odat, 120) BETWEEN '$begin%' AND '$end%' ";
        }
        // Remove Receipt Status and Date for "Spend Report" (PUR Report)
        $purchasingReport['receiptDate'] = $constraints['spend_report'] ? 'CONVERT(VARCHAR(10), MAX(PUR2.t_date), 120) AS PO_receipt_date,' : '';
        $statusPur = ($constraints['spend_report'] || $constraints['details_po']) ? 'MAX(PUR2.t_spur) AS pur_status_code,' : '';

        $wheres = [
            't_nama' => "AND COM.t_nama like '%%%s%%' ",
            't_orno' => "AND SLS.t_orno like '%%%s%%' ",
            't_cuno' => "AND SLS.t_cuno like '%%%s%%' ",
            't_eono' => "AND SLS.t_eono like '%%%s%%' ",
            'customer' => "AND upper(CUS.t_nama) like '%%%s%%' ",
            'po_number' => "AND PUR.t_orno like '%%%s%%' ",
            'supplier' => "AND upper(SUP.t_nama) like '%%%s%%' ",
            't_ssls' => "AND SLS2.t_ssls like '%%%s%%' ",
            't_cotp' => "AND PUR.t_cotp like '%%%s%%' ",
            't_spur' => "AND PUR2.t_spur like '%%%s%%' ",
            't_suno' => "AND SUP.t_suno like '%%%s%%' ",
            't_item' => "AND SLS1.t_item like '%%%s%%' ",
        ];

        foreach ($constraints as $field => $value) {
            if (!empty($value) || '0' === $value) {
                if (isset($wheres[$field])) {
                    $where .= sprintf($wheres[$field], $value);
                }
                if ('open_sls' === $field && true === $value) {
                    $where .= "AND SLS2.t_ssls not like '%7%' ";
                }
                if ('open_po' === $field && true === $value) {
                    $where .= 'AND DATEDIFF(MONTH, CONVERT(VARCHAR(10), PUR2.t_date, 120), GETDATE()) > 24 ';
                }
                if ('details_po' === $field && true === $value) {
                    $purchasingReport['fields'] = <<<EOF
  PUR1.t_cwar                            AS warehouse_code,
  PUR1.t_pric                            AS price,
  PUR1.t_item                            AS item#,
  PUR1.t_cprj                            AS project#,
  PUR1.t_pono                            AS pos,
  PUR1.t_oqua                            AS ordered_qty,
  PUR1.t_dqua                            AS del_qty,
  PUR.t_ccur                             AS currency,
  ITM.t_dsca                             AS item_desc,
  CMCS.t_dsca                            AS item_group,
  CONVERT(VARCHAR(10), PUR1.t_odat, 120) AS PO_date,
  CONVERT(VARCHAR(10), PUR1.t_ddta, 120) AS PO_planned_del_date,
  $statusPur
EOF;
                    $purchasingReport['joins'] = <<<EOF
  LEFT JOIN ttdpur041250 AS PUR1 ON PUR.t_orno = PUR1.t_orno
  LEFT JOIN ttiitm001250 AS ITM ON ITM.t_item = PUR1.t_item
  LEFT JOIN ttcmcs023250 AS CMCS ON CMCS.t_citg = PUR1.t_citg
EOF;
                    $purchasingReport['groupBy'] = <<<EOF
  PUR1.t_item,
  PUR1.t_cprj,
  ITM.t_dsca,
  PUR1.t_odat,
  PUR1.t_ddta,
  PUR1.t_cwar,
  CMCS.t_dsca,
  PUR1.t_pric,
  PUR1.t_dqua,
  PUR1.t_oqua,
  PUR1.t_pono,
  PUR.t_ccur,
EOF;
                }
            }
        }

        switch (true) {
            case isset($constraints['purchasing']):
                $query = self::getAeroPurchasingReportQuery($datePeriodPur, $where, $purchasingReport);
                break;
            case isset($constraints['shipping']):
                $query = self::getAeroShippingReportQuery($datePeriodPur, $where);
                break;
            default:
                $query = self::getAeroSalesReportQuery($datePeriodSales, $where);
                break;
        }

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * @param $period
     * @param $where
     *
     * @return string
     */
    private static function getAeroSalesReportQuery($period, $where = '')
    {
        $query = <<<SQL
SELECT
  COM.t_nama  AS sales_rep,
  CUS.t_nama  AS customer_name,
  SLS.t_cuno  AS customer#,
  SLS.t_orno  AS SO#,
  SLS.t_eono  AS customer_PO#,
  SLS1.t_item AS item#,
  ITM.t_dsca  AS item_desc,
  MAX(SLS2.t_ssls) AS status_code,
  SLS1.t_cprj AS project#,
  SLS1.t_oqua AS ordered_qty,
  ROUND(SLS1.t_pric, 2) AS price,
  SLS.t_ccur  AS currency,
  CONVERT(VARCHAR(10), SLS.t_odat, 120) AS SO_date,
  CONVERT(VARCHAR(10), SLS1.t_ddta, 120) AS SO_del_date,
  CONVERT(VARCHAR(10), SLS1.t_prdt, 120) AS SO_plan_recp_date,
  CASE WHEN DATEDIFF(DAY, CONVERT(VARCHAR(10), SLS1.t_prdt, 120), GETDATE()) <0
    THEN 0
  ELSE DATEDIFF(DAY, CONVERT(VARCHAR(10), SLS1.t_prdt, 120), GETDATE())
  END as days_past_due
FROM ttdsls040250 AS SLS
  LEFT JOIN ttdsls041250 AS SLS1 ON SLS.t_orno = SLS1.t_orno
  LEFT JOIN ttdsls045250 AS SLS2 ON SLS1.t_orno = SLS2.t_orno
  LEFT JOIN ttccom001250 AS COM ON SLS.t_crep = COM.t_emno
  LEFT JOIN ttccom010250 AS CUS ON SLS.t_cuno = CUS.t_cuno
  LEFT JOIN ttiitm001250 AS ITM ON ITM.t_item = SLS1.t_item
  LEFT JOIN ttdltc001250 AS LOT ON LOT.t_cprj = SLS1.t_cprj
WHERE
$period
$where
GROUP BY COM.t_nama,
  CUS.t_nama,
  SLS.t_cuno,
  SLS.t_orno,
  SLS.t_eono,
  SLS1.t_item,
  ITM.t_dsca,
  SLS1.t_pric,
  SLS1.t_ddta,
  SLS.t_ccur,
  SLS.t_odat,
  SLS1.t_oqua,
  SLS1.t_prdt,
  SLS1.t_cprj
ORDER BY CONVERT(VARCHAR(10), SLS.t_odat, 120);
SQL;

        return $query;
    }

    /**
     * @param $period
     * @param $where
     *
     * @return string
     */
    private static function getAeroShippingReportQuery($period, $where = '')
    {
        $query = <<<SQL
SELECT
  SLS.t_orno                             AS SO#,
  PUR.t_orno                             AS PO#,
  CUS.t_nama                             AS customer_name,
  PUR.t_cotp                             AS order_type,
  ADDR.t_namc                            AS supp_addr,
  ADDR.t_name                            AS supp_city,
  DEL.t_namc                             AS del_addr,
  DEL.t_name                             AS del_city,
  COM.t_nama                             AS sales_rep,
  SLS1.t_item                            AS part#,
  PUR1.t_cprj                            AS project#,
  ITM.t_dsca                             AS item_desc,
  CONVERT(VARCHAR(10), PUR1.t_ddta, 120) AS PO_planned_del_date,
  SLS1.t_cwar                            AS warehouse_code,
  CMCS.t_dsca                            AS warehouse_desc,
  MAX(SLS2.t_ssls)                       AS status_code,
  TXT.t_text                             AS tracking#,
  SLS.t_eono                             AS customer_PO#
FROM ttdsls040250 AS SLS
  LEFT JOIN ttdsls041250 AS SLS1 ON SLS.t_orno = SLS1.t_orno
  LEFT JOIN ttdsls045250 AS SLS2 ON SLS.t_orno = SLS2.t_orno
  LEFT JOIN ttdpur041250 as PUR1 ON SLS1.t_cprj = PUR1.t_cprj
  LEFT JOIN ttdpur040250 as PUR ON PUR.t_orno = PUR1.t_orno
  LEFT JOIN ttccom001250 AS COM ON SLS.t_crep = COM.t_emno
  LEFT JOIN ttccom010250 AS CUS ON SLS.t_cuno = CUS.t_cuno
  LEFT JOIN ttccom013250 AS DEL ON DEL.t_cuno = SLS.t_cuno AND SLS.t_cdel = DEL.t_cdel
  LEFT JOIN ttccom022250 AS ADDR ON ADDR.t_suno = PUR.t_suno
  LEFT JOIN ttiitm001250 AS ITM ON ITM.t_item = SLS1.t_item
  LEFT JOIN ttttxt010250 AS TXT ON TXT.t_ctxt = SLS.t_txta
  LEFT JOIN ttcmcs003250 AS CMCS ON CMCS.t_cwar = SLS1.t_cwar
  LEFT JOIN ttdltc001250 AS LOT ON LOT.t_cprj = SLS1.t_cprj
WHERE 
$period
$where
AND SLS1.t_cprj BETWEEN '00000' AND '9999999'
GROUP BY
  SLS.t_orno,
  PUR.t_orno,
  COM.t_nama,
  CUS.t_nama,
  PUR.t_cotp,
  SLS1.t_item,
  SLS1.t_cwar,
  DEL.t_namc,
  DEL.t_name,
  CMCS.t_dsca,
  PUR1.t_cprj,
  ITM.t_dsca,
  PUR1.t_ddta,
  TXT.t_text,
  ADDR.t_namc,
  ADDR.t_namd,
  ADDR.t_name,
  SLS.t_eono
ORDER BY SLS.t_orno
SQL;

        return $query;
    }

    /**
     * @param $period
     * @param $where
     * @param array $constraints
     *
     * @return string
     */
    private static function getAeroPurchasingReportQuery($period, $where = '', array $constraints = [])
    {
        extract($constraints, EXTR_SKIP);

        $query = <<<SQL
SELECT
$fields
$receiptDate
  PUR.t_orno                             AS PO#,
  PUR.t_cotp                             AS order_type,
  SUP.t_suno                             AS supplier#,
  SUP.t_nama                             AS supplier,
  ADDR.t_namc                            AS supp_addr,
  ADDR.t_namd                            AS supp_addr2,
  ADDR.t_name                            AS supp_city,
  COM.t_nama                             AS buyer,
  CONVERT(VARCHAR(10), PUR.t_odat, 120)  AS order_date,
  CONVERT(VARCHAR(10), PUR.t_ddat, 120)  AS delivery_date
FROM ttdpur040250 AS PUR
  LEFT JOIN ttdpur045250 AS PUR2 ON PUR.t_orno = PUR2.t_orno
  LEFT JOIN ttccom020250 AS SUP ON SUP.t_suno = PUR.t_suno
  LEFT JOIN ttccom001250 AS COM ON PUR.t_ccon = COM.t_emno
  LEFT JOIN ttccom022250 AS ADDR ON ADDR.t_suno = PUR.t_suno
$joins
WHERE 
$period
$where
GROUP BY 
$groupBy
  PUR.t_orno,
  SUP.t_nama,
  SUP.t_suno,
  COM.t_nama,
  PUR.t_cotp,
  ADDR.t_namc,
  ADDR.t_namd,
  ADDR.t_name,
  PUR.t_odat,
  PUR.t_ddat
ORDER BY PUR.t_orno
SQL;

        return $query;
    }

    /**
     * Production Report for AERO ERP 250
     * Returns Work Orders with or w/ SO by Constraints
     *
     * @param string $begin
     * @param string $end
     * @param array $constraints
     * @param bool $onlyWO
     *
     * @return array
     */
    public static function getWorkOrdersAeroReport($begin, $end, array $constraints = [], $onlyWO = false)
    {
        $joins = '';
        $project = '';
        $where = " LOT.t_clot like '%{$constraints['t_clot']}%' ";
        $constraints = array_filter($constraints);

        if (!empty($begin) && !empty($end) && empty($constraints['t_clot'])) {
            $datePeriod = " CONVERT(VARCHAR(10), PROD.t_dldt, 120) BETWEEN '$begin%' AND '$end%' ";
            $where = '';
        }

        $wheres = [
            't_nama' => "AND COM.t_nama like '%%%s%%' ",
            'customer_name' => "AND upper(CUS.t_nama) like '%%%s%%' ",
            'so_number' => "AND SLS.t_orno like '%%%s%%' ",
            'wo_number' => "AND PROD.t_pdno like '%%%s%%' ",
            't_ssls' => "AND SLS2.t_ssls like '%%%s%%' ",
            't_cprj' => "AND PROD.t_cprj like '%%%s%%' ",
            't_osta' => "AND PROD.t_osta like '%%%s%%' ",
        ];

        foreach ($constraints as $field => $value) {
            if (isset($wheres[$field])) {
                $where .= sprintf($wheres[$field], $value);
            }
            if ('open_sls' === $field) {
                $where .= "AND SLS2.t_ssls not like '%7%' ";
            }
            if ('open_wo' === $field) {
                $where .= "AND PROD.t_osta not in ('6', '7') ";
            }
        }

        // Adapt the query depend if we want the WO tied to the SO
        if (true !== $onlyWO) {
            $fields = <<<EOF
  SLS.t_orno AS SO#,
  CUS.t_nama AS customer_name,
  DEL.t_name AS del_city,
  COM.t_nama AS sales_rep,
  SLS2.t_ssls AS SO_status,
EOF;
            $joins = <<<EOF
  LEFT JOIN ttdsls041250 AS SLS1 ON SLS1.t_cprj = PROD.t_cprj
  LEFT JOIN ttdsls040250 AS SLS ON SLS.t_orno = SLS1.t_orno
  LEFT JOIN ttdsls045250 AS SLS2 ON SLS.t_orno = SLS2.t_orno
  LEFT JOIN ttccom010250 AS CUS ON SLS.t_cuno = CUS.t_cuno
  LEFT JOIN ttccom001250 AS COM ON COM.t_emno = SLS.t_crep
  LEFT JOIN ttccom013250 AS DEL ON DEL.t_cuno = SLS.t_cuno AND SLS.t_cdel = DEL.t_cdel
EOF;
            $project = "AND PROD.t_cprj BETWEEN '00000' AND '9999999'";
            $groupBy = <<<EOF
  SLS.t_orno,
  CUS.t_nama,
  DEL.t_name,
  COM.t_nama,
  SLS2.t_ssls,
EOF;
        } else {
            $fields = <<<EOF
PROD.t_osta AS WO_status,
PROD.t_qrdr AS qty_ordered,
EOF;
            $groupBy = <<<EOF
PROD.t_osta,
PROD.t_qrdr,
EOF;
        }

        $query = <<<EOF
SELECT
  $fields
  PROD.t_cprj AS project#,
  PROD.t_pdno AS WO#,
  PROD.t_mitm AS item#,
  ITM.t_dsca AS item_desc,
  PROD.t_dldt AS del_date,
  PROD.t_cwar AS warehouse_code
FROM ttisfc001250 AS PROD
  $joins
  LEFT JOIN ttiitm001250 AS ITM ON ITM.t_item = PROD.t_mitm
  LEFT JOIN ttdltc001250 AS LOT ON LOT.t_cprj = PROD.t_cprj
WHERE 
  $datePeriod
  $where
  $project
GROUP BY
  $groupBy
  PROD.t_cprj,
  PROD.t_pdno,
  PROD.t_mitm,
  ITM.t_dsca,
  PROD.t_dldt,
  PROD.t_cwar
ORDER BY PROD.t_pdno;
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    }

    /**
     * Returns array of SOL rows by constraints
     *
     * @param array or string $a
     * @param string $opt
     *                     [orderBy]  => string
     *                     [limit]    => int
     *
     * @return array
     */
    public static function byConstraints($a, $opt = [])
    {
        if (is_array($a)) {
            $HAVING = tldUtils::constructWhere($a);
        } else {
            $HAVING = $a;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = $opt['orderBy'];
        } else {
            $ORDERBY = 'T1.id';
        }
        if (!empty($opt['limit'])) {
            $LIMIT = "LIMIT {$opt['limit']}";
        }

        $EXTRA_SELECT = !empty($opt['select']) ? $opt['select'] : '';
        $EXTRA_FROM = !empty($opt['from']) ? $opt['from'] : '';
        $WHERE = !empty($opt['where']) ? $opt['where'] : '';

        // Construct query
        $query = <<<EOF
SELECT
    T1.*,
    T3.cu_nama,
    T3.user_customer_id,
    T3.buyer_customer_id,
    $EXTRA_SELECT
    IF(T3.dt_closed > '0000-00-00 00:00:00',
        T3.cu_nama,
        (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.user_customer_id)
    ) AS user_customer_display,
	(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.buyer_customer_id) AS buyer_customer_display,
	T2.location AS erp_fullname,
	T4.location AS sso_fullname,
	(SELECT SUM(t4.batch_qty) FROM sor_units AS t4 WHERE T1.id=t4.parent_id) AS qty_sou,
	(SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
        WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid
    ) AS qty_alloc,
	(SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
	   WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid AND T3.date_shipped != '0000-00-00'
	) AS qty_ship
FROM sor_lines AS T1
	LEFT JOIN locations AS T2 ON T1.bu=T2.id
	LEFT JOIN sor AS T3 ON T3.id=T1.parent_id
	LEFT JOIN locations AS T4 ON T3.sso=T4.id
$EXTRA_FROM
$WHERE
$HAVING
ORDER BY $ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of SOL by Buyer & User Customer ID
     *
     * @param int $cid customer ID
     * @param array|string $opt see tldSOL::byConstraints()
     *
     * @return array
     */
    public static function byAllCustomerID($cid, $opt = [])
    {
        $a = "buyer_customer_id=$cid OR user_customer_id=$cid";

        return self::byConstraints($a, $opt);
    }

    /**
     * Get latest SOLs
     *
     * Defaults to last 10
     *
     * @param integer $qty how manylines to get
     *
     * @return array
     */
    public static function byLatest($qty = 10)
    {
        $query = <<<EOF
		SELECT T1.*,
			sor.cu_nama,
			IF(sor.dt_closed > '0000-00-00 00:00:00',sor.cu_nama,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.user_customer_id)) AS user_customer_display,
			(SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sor.buyer_customer_id) AS buyer_customer_display,
			(SELECT location
			FROM locations
			WHERE T1.bu=locations.id) AS bu_fullname,
			(SELECT SUM(t4.batch_qty) FROM sor_units AS t4 WHERE T1.id=t4.parent_id) AS qty_sou,
			(SELECT SUM(T2.er_batch_qty) FROM service AS T2
			WHERE T2.sor_lid=T1.id AND T2.sor_lid>0) AS qty_alloc,
  			(SELECT SUM(T2.er_batch_qty) FROM service AS T2
			WHERE T2.sor_lid=T1.id AND T2.date_shipped != '0000-00-00') AS qty_ship
		FROM sor_lines AS T1
			LEFT JOIN sor ON sor.id=T1.parent_id
		ORDER BY id DESC
		LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Returns array of SOL rows by constraints
     *
     * @param string $target
     *
     * @return array
     */
    public static function search($target)
    {
        $search_data = [
            'sls_orno' => "$target%",
            'model' => "$target%",
            'user_customer_display' => "%$target%",
            'buyer_customer_display' => "%$target%",
        ];
        $a = tldUtils::constructWhere($search_data, 'OR');

        return self::byConstraints($a);
    }

    public static function getSalesAdminReport(array $asm, array $status, $sso = null, DateTime $start = null, Datetime $end = null)
    {
        $where = [];

        if (!empty($asm)) {
            $where[] = sprintf("sor.asm IN ('%s')", implode("', '", $asm));
        }

        if (!empty($status)) {
            $where[] = sprintf("sol.status IN ('%s')", implode("', '", $status));
        }

        if (!empty($sso)) {
            $where[] = "location.id = '$sso' AND location.role = 'SSO'";
        }

        if (null !== $start && null !== $end) {
            $where[] = "er.dgt_act BETWEEN '{$start->format('Y-m-d')}' AND '{$end->format('Y-m-d')}'";
        }

        $whereStatement = '';
        if (!empty($where)) {
            $whereStatement .= 'WHERE ';
            $whereStatement .= implode(' AND ', $where);
        }

        $query = <<<EOF
SELECT sol.id,
       sol.cu_ocur,
       CASE WHEN er.id IS NULL THEN sol.model ELSE er.model END AS model,
       sol.conf_cxo,
       sol.conf_cis,
       sol.parent_id,
       sol.inco,
       sol.inco_loc,
       sol.status,
       sol.notes AS sol_notes,
       sor.orno,
       sor.bu,
       sor.note AS sor_notes,
       sol.sls_orno,
       CONCAT(UPPER(people.lastname), ', ', people.firstname) AS fullname,
       customer.customer_name,
       location.location,
       er.id                                          AS er,
       er.sn,
       er.rrd_sso,
       er.rrd_erp,
       CASE WHEN er.dgt_act = '0000-00-00' THEN NULL ELSE er.dgt_act END AS dgt_act,
       CASE WHEN er.dgt_rev = '0000-00-00' THEN NULL ELSE er.dgt_rev END AS dgt_rev,
       er.date_shipped,
       er.esrid,
       er.airport_code,
       CASE WHEN unit.ddel_est1 = '0000-00-00' THEN NULL ELSE unit.ddel_est1 END AS ddel_est1
FROM sor AS sor
         LEFT JOIN sor_lines AS sol ON sol.parent_id = sor.id
         LEFT JOIN sor_units AS unit ON unit.parent_id = sol.id
         LEFT JOIN service AS er ON er.sor_uid = unit.id
         LEFT JOIN locations as location ON sol.bu = location.id
         LEFT JOIN people as people ON sor.asm = people.id
         LEFT JOIN customers as customer ON sor.buyer_customer_id = customer.id
$whereStatement
ORDER BY
    sol.id DESC
LIMIT 
    0, 10000

EOF;

        return tldUtils::getSqlToAssocArray($query);

    }

    public static function searchOptions($target, $bu, $start, $end, $model = '')
    {
        $WHERE = '';
        if ($model) {
            $WHERE = " AND T1.model='$model'";
        }
        $query = <<<EOF
SELECT
    T1.*,
    T3.cu_nama,
    T3.user_customer_id,
    T3.buyer_customer_id,
    IF(T3.dt_closed > '0000-00-00 00:00:00',
        T3.cu_nama,
        (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.user_customer_id)
    ) AS user_customer_display,
    (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=T3.buyer_customer_id) AS buyer_customer_display,
    T2.location AS erp_fullname,
    T4.location AS sso_fullname,
    (SELECT SUM(t4.batch_qty) FROM sor_units AS t4 WHERE T1.id=t4.parent_id) AS qty_sou,
    (SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
        WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid
    ) AS qty_alloc,
    (SELECT SUM(T3.er_batch_qty) FROM service AS T3, sor_units AS t4
       WHERE T1.id=t4.parent_id AND t4.id=T3.sor_uid AND T3.date_shipped != '0000-00-00'
    ) AS qty_ship
FROM
    sor_lines AS T1
    LEFT JOIN locations AS T2 ON T1.bu=T2.id
    LEFT JOIN sor AS T3 ON T3.id=T1.parent_id
    LEFT JOIN locations AS T4 ON T3.bu=T4.erp
WHERE
    T1.dt_opened BETWEEN '$start' AND '$end'
    AND T1.bu = $bu
    AND T1.id IN (SELECT parent_id FROM sor_opts WHERE caty LIKE 'OPTION' AND dsca LIKE '%$target%')
    $WHERE
ORDER BY
    T1.id
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public function getUnits()
    {
        return tldSORUnit::byParent($this->itsID);
    }

    public static function getTransportationCostAuditByConstraints($a = '1=1', $cur = 'USD')
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;

        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Query
        $query = <<<EOF
SELECT
    sol.*,
    (SELECT CONCAT(firstname, ' ', lastname) FROM people
        WHERE id=sor.asm
    ) AS asm_fullname,
    sso.location AS sso_fullname,
    factory.location AS erp_fullname,
    buyer.customer_name AS buyer_customer_display,
    '$cur' AS cur,
    (SELECT SUM(batch_qty) FROM sor_units
        WHERE sol.id=parent_id
    ) AS qty_sou,
    DATE_FORMAT(dt_opened, '%Y%m' ) AS opening_date,
    (SELECT COUNT(*) FROM esrl
        LEFT JOIN service AS er ON erid=er.id
        LEFT JOIN sor_units ON sor_units.id=er.sor_uid
        WHERE sor_units.parent_id=sol.id
    ) AS qty_esr  
FROM sor_lines AS sol
    LEFT JOIN sor ON sor.id=sol.parent_id
    LEFT JOIN locations AS sso ON sor.sso=sso.id
    LEFT JOIN locations AS factory ON sol.bu=factory.id
    LEFT JOIN customers AS buyer ON sor.buyer_customer_id=buyer.id
$WHERE
ORDER BY
    sol.dt_opened
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        // Get total costs
        foreach ($rows as $k => $row) {

            // SOL cost
            $solCost = tldSOROpts::totalsByParent(
                $row['id'],
                [
                    'include' => ['TRANSPORTATION'],
                    'dcur' => $cur,
                ]
            );
            $rows[$k]['total_sol_cur'] = round($solCost['pric_tot_in_dcur'] * $row['qty_sou'], 2);
            $rows[$k]['total_sol_sales_cur'] = round($solCost['pris_tot_in_dcur'] * $row['qty_sou'], 2);
            $rows[$k]['total_diff_sales_cur'] = round($solCost['pris_tot_in_dcur'] * $row['qty_sou'], 2);

            // ESR cost
            // -- Get ESR linked to the SOL
            $query = <<<EOF
SELECT
    esr.id AS esr_id,
    (SELECT COUNT(tesrl.id) FROM esrl AS tesrl
        WHERE tesrl.parent_id=esr.id
    ) AS nb_total_esrl,
    COUNT(esrl.id) AS nb_esrl
FROM esr
    LEFT JOIN esrl ON esrl.parent_id=esr.id
    LEFT JOIN service AS er ON er.id=esrl.erid AND esrl.parent_id = er.esrid
    LEFT JOIN sor_units ON sor_units.id=er.sor_uid
WHERE
    sor_units.parent_id={$row['id']}
GROUP BY
    esr.id
EOF;
            $esrList = tldUtils::getSqlToAssocArray($query);
            // -- Get cost for each ESR
            $esrListTotalCostValue = 0;
            foreach ($esrList AS $esrLinked) {
                $esrID = TldDatabase::escape($esrLinked['esr_id']);
                // Total mod cost entries
                $esrTotalCost = 0;
                $esrCost = tldModCost::totalsByParent($esrID, 'ESR', $cur);
                foreach ($esrCost as $cost) {
                    $esrTotalCost += $cost['price_dcur'];
                }
                // Multiply for qty assignee to the ESR
                $esrListTotalCostValue += $esrLinked['nb_esrl'] * ($esrTotalCost / $esrLinked['nb_total_esrl']);
            }
            $rows[$k]['total_esr_cur'] = round($esrListTotalCostValue, 2);
            // Calculate Gap
            $rows[$k]['total_diff_cur'] = $rows[$k]['total_sol_cur'] - $rows[$k]['total_esr_cur'];
            $rows[$k]['total_diff_sales_cur'] = $rows[$k]['total_sol_sales_cur'] - $rows[$k]['total_sol_cur'];
        }

        return $rows;
    }

    public function getPrintVersion()
    {
        if (empty($this->itsID)) {
            return;
        }
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'parent_id' => 'SOR#',
                'id' => 'SOL#',
                'user_customer_display' => 'Customer Name (END USER)',
                'buyer_customer_display' => 'Customer Name (BUYER)',
                'dt_opened' => 'Date Opened',
                'dt_closed' => 'Date Closed',
                'dzk_sso' => 'Date of Zero Backlog, SSO',
                'dzk_erp' => 'Date of Zero Backlog, Factory',
                'sls_orno' => 'SSO PO#',
                'erp_orno' => 'Factory SO#',
                'spacer1' => '---spacer---',
                'status' => 'Status',
                'bu_fullname' => 'Factory BU',
                'model' => 'Model',
                'eng_tier' => 'Emission Rating',
                'qty_sou' => 'Quantity Ordered',
                'qty_alloc' => 'Quantity Assigned',
                'qty_ship' => 'Quantity Shipped',
                'spacer2' => '---spacer---',
                'inco' => 'Inco Terms',
                'inco_loc' => 'Inco Location',
                'ctry' => 'Country of sales',
                'del_pen' => 'Late delivery penalties',
                'delpen_cond' => 'Late delivery conditions',
                'conf_sls' => 'Delivery Penalty Accepted by Sales Org?',
                'conf_erp' => 'Delivery Penalty Accepted by Factory?',
                'spacer3' => '---spacer---',
                'conf_cis' => 'Customer inspection<br>before shipment?',
                'parts_inc' => 'Ship with Spare Parts?',
                'docs_inc' => 'Special Documentary Requirements',
                'notes' => 'Notes',
            ],
            [
                'title' => 'General',
                'links' => [
                    'parent_id' => 'https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sor&m[1]=view&id=',
                    'id' => 'https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=',
                ],
            ]
        );
        $body = $report->fetch();
        // get list of ER
        $report = new tldReportColumnar(
            $this->getUnits(),
            [
                'xItems' => [
                    'short_desc' => 'Short Description',
                    'del_dat' => 'Requested Delivery Date',
                    'del_early' => 'Early delivery ok?',
                    'ddel_est1' => 'Promised Delivery Date',
                    'dgt_rev' => 'Estimated GT Date',
                    'sn' => 'SN#',
                    't_prno' => 'Project#',
                    'model' => 'Model',
                    'eng_tier' => 'Emission Rating',
                    'esrid' => 'ESR#',
                    'dgt_act' => 'GT Date',
                    'date_shipped' => 'Ship Date',
                    'batch_qty' => 'Batch Qty',
                ],
                'title' => 'SOL Units',
                'links' => [
                    'sn' => 'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
                ],
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();

        return $body;
    }

    public function getPartsRecipients()
    {
        $sso = new tldLocation(tldLocation::getIDByERP($this->getSSOERP()));
        // SPM
        $grpSPM = new tldGroup('role_SPM', $this->getSSOERP());
        $emailList = $grpSPM->getEmailList();
        // SSO parts generic email
        $partsEmail = $sso->getPartsEmail();
        if (!empty($partsEmail)) {
            $emailList[] = $sso->getPartsEmail();
        }

        return $emailList;
    }

    public function createPartsTask(tldUser $user, string $message = '')
    {
        if (in_array($this->getStatus(), ['PENDING', 'CREATE_PO'])) {
            return;
        }

        $grpSPM = new tldGroup('role_SPM', $this->getSSOERP());
        if(!($users = $grpSPM->getUserlist())) {
            return;
        }

        if ([] !== tldTask::byParent($this->itsID, 'SOL', '', " task LIKE '%, Spare Parts needed%' ")) {
            return;
        }

        $description = <<<EOF
SOL#{$this->getId()}, {$this->getStatus()}, Spare Parts needed

- End User: {$this->itsHeader['user_customer_display']}
- Buyer: {$this->itsHeader['user_customer_display']}
- Country: {$this->itsHeader['ctry']}
EOF;

        $taskId = tldTask::insert(
            $this->getId(),
            [
                'assignee' => $users[0]['id'],
                'assignor' => $user->getID(),
                'task' => $description,
            ],
            'SOL'
        );
        if (is_string($taskId)) {
            return;
        }

        $grpSA = new tldGroup('role_SA', $this->getSSOERP());

        $locationId = tldLocation::getIDByERP($this->getSSOERP());
        $sph = new tldLocation($locationId);
        $sphEmail = $sph->itsDetails['sph_email'];

        $CC = array_merge($this->getPartsRecipients(), $grpSA->getEmailList(), [$user->getEmail()]);

        if ('' !== $sphEmail) {
            $CC = [...$CC, $sphEmail];
        }

        $task = new tldTask($taskId);
        $task->notifyAssignee(
            "$message<br><br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskId\">Click here to see task</a>",
            "SOL#{$this->getId()} - Parts Requested",
            $CC
        );

    }

    public function onChangeExportLicenseStatus(tldUser $user, string $oldExportLicenceStatus, string $newExportLicenceStatus, string $solStatus)
    {
        if ($solStatus !== 'CREATE_FACTORY_SO' || $newExportLicenceStatus !== 'REQUIRED' || $oldExportLicenceStatus !== 'NOT REQUIRED') {
            return;
        }

        // search the assignee
        $assignee = null;
        // Current user is PSM on ERP
        if ($user->isInGroupLevel('role_PSM', $this->getERP())) {
            $assignee = $user->getID();
        } else {
            // Search all PSM for ERP an select first
            $group = new tldGroup('role_PSM', $this->getERP());
            $PSMs = $group->getUserlist();

            if (empty($PSMs)) {
                return sprintf('No PSM found for the factory ERP#%s', $this->getErp());
            }

            $assignee = $PSMs[0]['id'];
        }

        // Create task
        $taskId = tldTask::insert(
            $this->getId(),
            [
                'assignee' => $assignee,
                'assignor' => $user->getID(),
                'task' => "An Export License is required for SOL #{$this->itsID}",
            ],
            'SOL'
        );

        if (is_string($taskId)) {
            return $taskId;
        }
        $task = new tldTask($taskId);
        $task->notifyAssignee(
            "<br><br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskId\">Click here to see task</a>",
            "SOL#{$this->getId()} - Export License is required"
        );
    }

    public function isWarrantyClaimAcceptedByFactory()
    {
        // make sure there are special warranties
        if ($this->itsHeader['wrty_spec'] === '') {
            return true;
        }
        // if there are special warranties, check factory acceptation
        return $this->itsHeader['conf_wrty_erp'] === 'Y';
    }

    public function isDeliveryPenaltyAcceptedByFactory()
    {
        // make sure there is delivery penalties
        if ($this->itsHeader['del_pen'] !== 'Y') {
            return true;
        }

        // if there is delivery penalties, check factory acceptation
        return $this->itsHeader['conf_erp'] === 'Y';
    }

    public function notifyFactoryRejection($rejectionType, $cc = '')
    {
        $rejectionType = strtolower($rejectionType);
        $subject = "SOL#$this->itsID - " . ucwords($rejectionType) . ' not accepted by factory';
        $body = <<<EOF
<p>This is to inform you that $rejectionType for this order was not accepted by the factory.</p>
EOF;
        // look for recipients
        $to = [];
        // -- ASM
        $to[] = $this->getASMEmail();
        // -- SSD
        $ssdGrp = new tldGroup('role_EVP', $this->getSSOERP());
        $to = array_merge($to, (array)$ssdGrp->getEmailList());
        // -- SAM
        $samGrp = new tldGroup('role_SAM', $this->getSSOERP());
        $to = array_merge($to, (array)$samGrp->getEmailList());

        // send email
        return $this->notify(
            $to,
            $subject,
            $body,
            $cc
        );
    }

    public function notifyDeliveryPenaltyRejectionByFactory($cc = '')
    {
        return $this->notifyFactoryRejection('delivery penalty', $cc);
    }

    public function notifyWarrantyRejectionByFactory($cc = '')
    {
        return $this->notifyFactoryRejection('warranty conditions', $cc);
    }

    public function getForexRates()
    {
        $rows = [];
        // Get SOL Rates
        $curs = $this->getCURS();
        // foreach of them get rates
        foreach ($curs as $cur => $rate) {
            // forex
            // -- get date create minus 1 month
            $solDateCreation = new DateTime($this->getCreationDate());
            $solDateCreation->modify('first day of last month');
            // -- get forex data
            $forex = tldForex::getRate('USD', $cur, $solDateCreation->format('Y'), $solDateCreation->format('m'));
            if (empty($forex)) {
                $forex = 'Not found';
            }
            // equote
            $equote = 'N/A';
            if ($this->getEquoteID()) {
                $equote = 'Not implemented yet';
            }
            // set the data
            $rows[] = [
                'cur' => $cur,
                'forex_rate' => $forex,
                'equote_rate' => $equote,
                'sol_rate' => $rate,
            ];
        }

        return $rows;
    }

    public function getForexRatesReport()
    {
        $report = new tldReportColumnar(
            $this->getForexRates(),
            [
                'xItems' => [
                    'cur' => 'Currency',
                    'forex_rate' => 'Forex rate',
                    'equote_rate' => 'eQuote rate',
                    'sol_rate' => 'SOL rate',
                ],
                'title' => 'Default Currency is ' . $this->getDCUR() . '<br>Forex Rates per USD',
                'sortable' => 'no',
            ]
        );

        return $report->fetch();
    }

    public function notifyForexRates($cc = '')
    {
        $to = [];
        // SSO CFO
        $grp = new tldGroup('role_CFO', $this->getSSOERP());
        $to += $grp->getEmailList();
        // Factory CFO
        $grp = new tldGroup('role_CFO', $this->getFactoryERP());
        $to += $grp->getEmailList();
        // Rates report
        $body = <<<EOF
<p>SOL#{$this->getID()} has been created with below rates information</p>
{$this->getForexRatesReport()}
EOF;
        // Check if recipients
        if (!count($to)) {
            $to[] = 'devteam@tld-america.com';
            $body = '<h1>No CFO founds for this SOL</h1>' . $body;
        }

        // send email
        return $this->notify(
            $to,
            $this->getDefaultSubject() . ' - Forex rates information',
            $body,
            $cc
        );
    }

    public function getDefaultSubject()
    {
        return "{$this->getModel()}, {$this->itsHeader['qty_sou']}, {$this->getCustomerName()}, SOL#{$this->getID()}";
    }

    public function notify($to, $subject, $body, $cc = '')
    {
        $body .= <<<EOF
<p><a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$this->itsID">Click here to see SOL</a></p>
{$this->getPrintVersion()}
EOF;

        return tldUtils::emailAttachment(
            $to,

            'noreply@tld-gse.com',
            $subject,
            $body,
            null,
            $cc
        );
    }

    /**
     * Return the SOL associated to a CBOM
     * @param string $CBOM
     * @param string $loc
     * @return int id SOL
     */
    public static function getIdByCBOM($CBOM, $loc)
    {
        $query = <<<SQL
SELECT sor_lines.id
FROM sor_lines
  INNER JOIN sor_units  ON sor_lines.id = sor_units.parent_id
  INNER JOIN service AS er ON er.sor_uid = sor_units.id
WHERE er.t_prno LIKE '$CBOM' AND er.man_location LIKE '$loc'
SQL;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Return only the HTML part of the receipt
     *
     * @return mixed
     */
    public function getReceiptHTML()
    {
        $pdf = null;
        $data['header'] = $this->itsHeader;
        $sor = new tldSOR($data['header']['parent_id']);
        $sorHeader = [];
        foreach ($sor->itsHeader as $key => $val) {
            $sorHeader['sor_' . $key] = $val;
        }
        $data['header'] = array_merge($data['header'], $sorHeader);

        $data['header']['dcur'] = $this->getDCUR();
        $sorSumm = [];
        foreach ($this->getSummary(['dcur' => $data['header']['dcur']]) as $key => $val) {
            $sorSumm['sum_' . $key] = $val;
        }
        $data['header'] = array_merge($data['header'], $sorSumm);

        $data['options'] = tldSOROpts::byParent($this->itsID, ['include' => self::getInternalCategoriesList()]);
        $data['asm'] = (new tldUser($data['header']['sor_asm']))->getHeader();
        $data['er'] = tldSORUnit::byParent($this->itsID);
        $pdf = include('documents/sol/sol.receipt.php');
        return $pdf;
    }

    /**
     * @return basicFile|string
     */
    public function getReceipt()
    {
        $pdf = $this->getReceiptHTML();
        $html2pdf = new tldHTML2PDF($pdf, ['encoding' => 'utf-8']);
        $tool = new tldPDFToolKit();
        $e = $tool->addFile($html2pdf->itsConvertedPdfFile->getFilePath());
        if (is_string($e)) {
            return 'Error when creating file.';
        }

        $e = null;
        $dms = new tldDMS(4879);
        $publishFile = new tldFile($dms->itsHeader['pub_fid']);
        if (!$publishFile->isFile() || strtolower(basicFile::getExtensionFromFileName($publishFile->getOriginalFileName())) !== 'pdf') {
            return 'DMS 4879 not found.';
        }
        $publishFileName = $publishFile->getOriginalFileName();
        $publishFile->itsFile->copy("/tmp/$publishFileName");
        $e = $tool->addFile("/tmp/$publishFileName");

        if (is_string($e)) {
            return 'Error when creating file.';
        }
        $e = $tool->merge();
        if (is_string($e)) {
            return 'An error occurred during the creation of the File.';
        }

        return $tool->itsOutputFile;
    }

    public static function getExportLicenceStatuses()
    {
        return ['NOT REQUIRED', 'REQUIRED', 'OBTAINED'];
    }

    public function openIbsTask(array $previousSols, array $sor, ?string $oldEmissionRating = null): void
    {
        if (in_array('iBS', $solEmissionRatings = array_column($previousSols, 'eng_tier'))
            || in_array('ipHS + iBS', $solEmissionRatings, true)
            || !in_array($this->getHeader()['eng_tier'], ['iBS', 'ipHS + iBS'], true)
            || in_array($oldEmissionRating, ['iBS', 'ipHS + iBS'], true)
            || $sor['asm'] === null
        ) {
            return;
        }

        $asm = new tldUser($sor['asm']);
        $message = "<p>You have selected iBS as Emission Rating for the unit(s) of this SOR, please check with your customer regarding the charging infrastructure and put the required information on this TASK:</p>

<p>1) if the customer already has chargers or will source them directly, the information required is:<br>
 &bull; Charger(s) brand<br>
 &bull; Charger model<br>
 &bull; Picture of the charger name plate<br>
 &bull; Plug model<br>
 &bull; Local Distributor info</p>

<p>2) If the customer intends to buy chargers via TLD (external option in the SOL), please remember that the SSO is in charge of sourcing and shipping those chargers. Please see DMS #5773 to select the proper charger. Please just put in this TASK the brand name and model you have selected.</p>

<p>3) If the customer requires the TLD iCharger (onboard charger) sold by factory, the information below on the grid is required:<br>
 &bull; 1 or 3 phases?<br>
 &bull; If 3 phases, presence of Neutral?<br>
 &bull; Voltage level?<br>
 &bull; Current and power available?</p>

<p style=\"color: #FF0000; font-weight: bold\">Please return this task to the factory with all relevant informations and do not close it. Thank you.</p>";

        $task = tldUtils::cleanupFormInput([
            'assignee' => $asm->getID(),
            'assignor' => $asm->getSupervisor(),
            'task' => $message,
        ]);

        tldTask::insert($this->getId(), $task, 'SOL');
    }

    public function openModelIbsTask(array $previousSols, array $sor, ?string $previousModel = null): void
    {
        if (in_array('model', array_column($previousSols, 'GPU-409-iBS'), true)
            || $this->getHeader()['model'] !== 'GPU-409-iBS'
            || $previousModel === 'GPU-409-iBS'
            || $sor['asm'] === null) {
            return;
        }

        $asm = new tldUser($sor['asm']);
        $message = "<p>The GPU-409-iBS includes an on-board charger, please collect the following information from the customer regarding their 3-phase input supply:<br>
 &bull; Input supply voltage available for charging<br>
 &bull; Input supply current available for charging<br>
 &bull; Is there an earth leakage protection device (RCD) fitted to the input supply? If so, please confirm the rating (in mA)</p>";

        $task = tldUtils::cleanupFormInput([
            'assignee' => $asm->getID(),
            'assignor' => $asm->getSupervisor(),
            'task' => $message,
        ]);

        tldTask::insert($sor['id'], $task, 'SOR');
    }
}

/**
 * Equipment Shipping Record, Class for handling shipments of goods
 *
 * @package SalesAndService
 */
class tldESR
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
            $SELECT
            $FROM
            WHERE esr.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Delete an ESR
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /***
     * Return number of ESR by SSO
     *
     * @return array
     */
    public static function countBySSOStatus()
    {
        $query = <<<EOF
SELECT
    esr.status AS status,
    locations.location AS location_sso,
    count(*)AS num
FROM esr
    LEFT JOIN locations ON esr.sso_id=locations.id
GROUP BY
    status, location_sso
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic ESR update method
     *
     * @param              $data   array of esr datas
     * @param array|string $fields array of esr fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        throw new Exception('This is no longer used');
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'ESR');
    }

    public function getLines()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
        SELECT
        esrl.id AS esrl_id,
        esrl.erid,
        esrl.sqe,
        sor_units.dpas_rating,
        CASE WHEN service.sn IS NULL THEN CONCAT('T',esrl.erid) ELSE service.sn END AS equip_sn,
        sor_lines.model AS model,
        sor_lines.inco AS sol_inco,
        esr.inco AS esr_inco,
        sor_lines.inco_loc AS sol_inco_loc,
        service.delivery_location as delivery_loc,
        service.airport_code AS final_dest,
        sor_tran.nref AS er_invoice_num,
        service.date_shipped AS er_dt_shipped,
        service.dyt AS dyt,
        dt_shipped,
        dt_estimated,
        dt_arrived,
        dt_pick_up,
        service.dgt_act AS dgt_act
        FROM esrl
            LEFT JOIN esr ON esrl.parent_id=esr.id
            LEFT JOIN service ON esrl.erid=service.id 
            LEFT JOIN sor_tran ON service.tranid_sso=sor_tran.id
            LEFT JOIN sor_units ON service.sor_uid=sor_units.id
            LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
            LEFT JOIN sor ON sor_lines.parent_id=sor.id
        WHERE esrl.parent_id=$this->itsID
        ORDER by esrl_id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function addLine($a)
    {
        throw new Exception('This is no longer used');
    }

    public function getStatusAllowed()
    {
        switch ($this->getStatus()) {
            case 'PENDING':
                return ['BOOKED'];
                break;
            case 'BOOKED':
                return ['SHIPPED'];
                break;
            case 'SHIPPED':
                return ['CLOSED'];
                break;
        }

        return;
    }

    public static function getStatusList()
    {
        return ['PENDING', 'BOOKED', 'SHIPPED', 'CLOSED'];
    }

    /**
     * Update ESR status
     *
     * @param string $status
     * @param bool $bypass
     * return string on error
     *
     * @return string
     */
    public function changeStatus($status, $bypass = false)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Get the latest ESRs, defaults to last 10
     *
     * @param integer $qty
     *
     * @return array
     */
    public static function byLatest($qty = 10)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
ORDER BY dt_open DESC
LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method by constraints
     *
     * @param mixed string or array $constraints
     * @param array|string $opt
     *
     * @return array
     */
    public static function byConstraints($constraints, $opt = [])
    {
        // Construct constraints
        if (is_array($constraints)) {
            $HAVING = tldUtils::constructWhere($constraints);
        } else {
            $HAVING = $constraints;
        }
        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        }
        // Look for options
        if (isset($opt['orderBy'])) {
            $ORDERBY = TldDatabase::escape($opt['orderBy']);
        } else {
            $ORDERBY = 'dt_open DESC';
        }
        if (isset($opt['select'])) {
            $SELECT_extra = ',' . $opt['select'];
        }
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

    /**
     * Create a new ESR row in the database
     *
     * @param array $p
     *
     * @return boolean
     */
    public static function insert($p)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Add a comment to the log
     *
     * @param     $id
     * @param     $comment
     * @param int $num_log
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $num_log = 0)
    {
        throw new Exception('This is no longer used');
    }

    /** Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'ESR');
    }

    public function getCosts($dcur = 'USD')
    {
        return tldModCost::byParent($this->itsID, 'ESR', $dcur);
    }

    public function getTotalCostsByType($dcur = 'USD')
    {
        return tldModCost::totalsByParent($this->itsID, 'ESR', $dcur);
    }

    public function getFullLog()
    {
        $query = <<<EOF
        SELECT
            mod_logs.*, concat(a.lastname,', ',a.firstname) as poster_fullname
        FROM mod_logs
            LEFT JOIN people AS a ON mod_logs.poster=a.id
        WHERE
            (mod_logs.parent_id=$this->itsID
            AND mod_logs.module LIKE 'ESR' AND log_num<>10)
EOF;

        $query .= ' ORDER BY id DESC';

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Generic method to get the FROM query part
     *
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM esr
    LEFT JOIN locations ON esr.sso_id=locations.id
    LEFT JOIN customers ON esr.cuid=customers.id
    LEFT JOIN vendors AS vendors_car ON esr.car_id=vendors_car.id
    LEFT JOIN vendors AS vendors_fwd ON esr.fwd_id=vendors_fwd.id
    LEFT JOIN airport_codes AS apc_d ON esr.departure=apc_d.id
    LEFT JOIN airport_codes AS apc_a ON esr.arrival=apc_a.id
    LEFT JOIN port_codes AS ptc_d ON esr.departure=ptc_d.id
    LEFT JOIN port_codes AS ptc_a ON esr.arrival=ptc_a.id
    LEFT JOIN countries AS ctry_apc_d ON ctry_apc_d.iso_code_2=apc_d.ctry_code_2
    LEFT JOIN countries AS ctry_apc_a ON ctry_apc_a.iso_code_2=apc_a.ctry_code_2
    LEFT JOIN countries AS ctry_ptc_d ON ctry_ptc_d.iso_code_2=ptc_d.ctry_code_2
    LEFT JOIN countries AS ctry_ptc_a ON ctry_ptc_a.iso_code_2=ptc_a.ctry_code_2
EOF;
    }

    /**
     * Generic method to get the SELECT query part
     *
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    esr.*,
    CASE WHEN ship_auth = 1 THEN 'YES' ELSE 'NO' END AS ship_auth_YN,
    CONCAT(vendors_car.company,', ',vendors_car.email) AS comp_email_car,
    CONCAT(vendors_fwd.company,', ',vendors_fwd.email) AS comp_email_fwd,
    locations.location AS location_sso,
    customers.customer_name AS customer_fullname
EOF;
    }

    /**
     * Get list of linked ESR tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'ESR', 'ALL');
    }

    public static function ESRCostBySSOByYear($sso, $year)
    {
        $query = <<<EOF
SELECT
       esr.id AS esr_id,
       dt_shipped,
       count(*) AS quantity,
       sor_lines.model AS model,
       service.man_location AS factory,
       sor_lines.ctry AS country,
       service.delivery_location as delivery_loc,
       esr.inco AS esr_inco,
       esr.modality AS modality,
       CONCAT(vendors_fwd.company,', ',vendors_fwd.email) AS comp_email_fwd,
       'USD' AS currency,
       (SELECT
               SUM(ROUND(
                     price*(
                         IF(tcur.rate IS NULL, 1, tcur.rate)/
                         IF(fcur.rate IS NULL, 1, fcur.rate)
                         ),2
                       )) AS price_dcur
        FROM mod_costs
               LEFT JOIN erp_forex2 AS fcur ON PERIOD_DIFF(
                                                 DATE_FORMAT(CONCAT(fcur.nam_year, '-', fcur.nam_month, '-01'), '%Y%m'),
                                                 PERIOD_ADD(DATE_FORMAT(mod_costs.date, '%Y%m'), -1)) = 0 AND fcur.nam_cur=mod_costs.cur AND fcur.typ='END'
               LEFT JOIN erp_forex2 AS tcur ON PERIOD_DIFF(
                                                 DATE_FORMAT(CONCAT(tcur.nam_year, '-', tcur.nam_month, '-01'), '%Y%m'),
                                                 PERIOD_ADD(DATE_FORMAT(mod_costs.date, '%Y%m'), -1)) = 0 AND tcur.nam_cur='USD' AND tcur.typ='END'
        WHERE mod_costs.module ='ESR' AND mod_costs.parent_id =esr.id) AS total
FROM esrl
       LEFT JOIN esr ON esrl.parent_id=esr.id
       LEFT JOIN service ON esrl.erid=service.id AND esrl.parent_id = service.esrid
       LEFT JOIN sor_tran ON service.tranid_sso=sor_tran.id
       LEFT JOIN sor_units ON service.sor_uid=sor_units.id
       LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
       LEFT JOIN sor ON sor_lines.parent_id=sor.id
       LEFT JOIN vendors AS vendors_fwd ON esr.fwd_id=vendors_fwd.id
WHERE esr.sso_id = $sso AND DATE_FORMAT(esr.dt_open,'%Y') LIKE '$year'
GROUP by esr_id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byEstArrivalDate($sso, $start, $end)
    {
        $query = <<<EOF
        SELECT
        esr.id AS esr_id,
        esr.departure AS departure,
        esrl.id AS esrl_id,
        esrl.erid,
        service.sn AS equip_sn,
        sor_lines.model AS model,
        sor_lines.inco AS sol_inco,
        esr.inco AS esr_inco,
        sor_lines.inco_loc AS sol_inco_loc,
        sor_lines.id AS sol_id,
        service.delivery_location as delivery_loc,
        service.airport_code AS final_dest,
        sor_tran.nref AS er_invoice_num,
        service.date_shipped AS er_dt_shipped,
        dt_shipped,
        dt_estimated,
        dt_arrived,
        dt_pick_up
        FROM esrl
            LEFT JOIN esr ON esrl.parent_id=esr.id
            LEFT JOIN service ON esrl.erid=service.id AND esrl.parent_id = service.esrid
            LEFT JOIN sor_tran ON service.tranid_sso=sor_tran.id
            LEFT JOIN sor_units ON service.sor_uid=sor_units.id
            LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
            LEFT JOIN sor ON sor_lines.parent_id=sor.id
        WHERE esr.sso_id = $sso AND DATE_FORMAT(esrl.dt_estimated,'%Y-%m-%d') BETWEEN '$start' AND '$end'
        ORDER by esr_id, esrl_id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byESRShipmentDate($sso, $start, $end)
    {
        $query = <<<SQL
        SELECT
        esr.id AS esr_id,
        esrl.id AS esrl_id,
        esrl.erid,
        service.sn AS equip_sn,
        sor_lines.model AS model,
        sor_lines.inco AS sol_inco,
        esr.inco AS esr_inco,
        sor_lines.inco_loc AS sol_inco_loc,
        service.delivery_location as delivery_loc,
        service.airport_code AS final_dest,
        sor_tran.nref AS er_invoice_num,
        service.date_shipped AS er_dt_shipped,
        dt_shipped,
        dt_estimated,
        dt_arrived
        FROM esrl
            LEFT JOIN esr ON esrl.parent_id=esr.id
            LEFT JOIN service ON esrl.erid=service.id AND esrl.parent_id = service.esrid
            LEFT JOIN sor_tran ON service.tranid_sso=sor_tran.id
            LEFT JOIN sor_units ON service.sor_uid=sor_units.id
            LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
            LEFT JOIN sor ON sor_lines.parent_id=sor.id
        WHERE esr.sso_id = $sso AND DATE_FORMAT(service.date_shipped,'%Y-%m-%d') BETWEEN '$start' AND '$end'
        ORDER by esr_id, esrl_id
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySSOStatus($location, $status, $opt = [])
    {
        if ($location !== 'ALL') {
            $a['location_sso'] = $location;
        }
        if ($status !== 'ALL') {
            $a['status'] = $status;
        }

        return self::byConstraints($a, $opt);
    }
}

/**
 *
 * @author Graham
 *
 */
class tldSFR
{
    /**
     * SFR constructor
     *
     * @param $id
     *
     */
    public function __construct($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return;
        }
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE sfr.id={$this->itsID}
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }


    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }


    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getAsmID()
    {
        return $this->itsHeader['asm_id'];
    }

    public function getAsmEmail()
    {
        return $this->itsHeader['asm_email'];
    }

    public function getSsoERP()
    {
        return $this->itsHeader['sso_comp'];
    }

    public function getFactoryERP()
    {
        return $this->itsHeader['erp_comp'];
    }

    public function getBuyerCustomerID()
    {
        return $this->itsHeader['buyer_customer_id'];
    }

    public function getUserCustomerID()
    {
        return $this->itsHeader['user_customer_id'];
    }

    public static function getOpenStatuses()
    {
        return [
            'BUDGET' => 'Customer has budget requirement, no firm confirmation, no specific quote done.',
            'IN_PROGRESS' => 'The customer is actively considering the TLD Quote',
            'DELAYED' => 'SFR is still active, customer demand is delayed significantly',
        ];
    }


    public static function getCloseStatuses()
    {
        return [
            'ORDERED' => 'Customer has placed order with TLD',
            'LOST' => 'Customer has placed order with competitor',
            'CANCELLED' => 'Requirement has been cancelled',
            'PARTIAL' => 'Part of the order was ordered, other part was lost to competitor',
        ];
    }


    public function isEmpty()
    {
        return empty($this->itsHeader);
    }


    public function isClosed()
    {
        return in_array(
            $this->getStatus(),
            array_keys(self::getCloseStatuses())
        );
    }


    public function isCancelled()
    {
        return 'CANCELLED' === $this->getStatus();
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @param int log_num
     *
     * @return array
     */
    public function getLog($log_num = 0)
    {
        return tldModLog::byParent($this->itsID, 'SFR', $log_num);
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SFR');
    }

    public static function getSELECT(): string
    {
        $openStatus = implode("','", array_keys(self::getOpenStatuses()));

        return <<<EOF
SELECT
	sfr.*,
    ssolist.location AS sso_fullname,
    ssolist.erp AS sso_comp,
    erplist.location AS erp_fullname,
    erplist.erp AS erp_comp,
    CONCAT(asms.firstname, ' ', asms.lastname) AS asm_fullname,
    asms.email AS asm_email,
    (SELECT CONCAT(firstname, ' ', lastname)
    	FROM people WHERE people.id=sfr.init_id
    ) as init_fullname,
    ROUND(tld_succ_pc*cust_pur_pc/100) AS tot_succ_pc,
    CONCAT(year_id,'-',month_id,'-01') AS cust_pur_date,
    PERIOD_DIFF(
    	CONCAT(sfr.year_id, LPAD(sfr.month_id, 2, '0')),
    	date_format(NOW(), '%Y%m')
    ) AS months_out,
    IF(
    	sfr.status IN('$openStatus'),
	    CASE
	    	WHEN
	    		/* any SFR whose month of realization is less than 3 months from now would become delinquent if no update has been posted for 1 month */
	    		PERIOD_DIFF(CONCAT(sfr.year_id, LPAD(sfr.month_id, 2, '0')), DATE_FORMAT(NOW(), '%Y%m')) < 3
	    	THEN
	    		IF((SELECT COUNT(*) FROM mod_logs WHERE mod_logs.module='sfr' AND mod_logs.parent_id=sfr.id AND mod_logs.log_num = 0 AND mod_logs.date > DATE_SUB(NOW(), INTERVAL 1 MONTH)), NULL, 'Y')

	    	WHEN
	    		/* any SFR whose month of realization is between 3 and 6 months from now would become delinquent if no update has been posted for 2 months */
	    		PERIOD_DIFF(CONCAT(sfr.year_id, LPAD(sfr.month_id, 2, '0')), DATE_FORMAT(NOW(), '%Y%m')) >= 3 AND
	    		PERIOD_DIFF(CONCAT(sfr.year_id, LPAD(sfr.month_id, 2, '0')), DATE_FORMAT(NOW(), '%Y%m')) < 6
	    	THEN
	    		IF((SELECT COUNT(*) FROM mod_logs WHERE mod_logs.module='sfr' AND mod_logs.parent_id=sfr.id AND mod_logs.log_num = 0 AND mod_logs.date > DATE_SUB(NOW(), INTERVAL 2 MONTH)), NULL, 'Y')

	    	WHEN
	    		/* any SFR whose month of realization is over 6 months from now would become deliquent if no update has been posted for 3 months */
	    		PERIOD_DIFF(CONCAT(sfr.year_id, LPAD(sfr.month_id, 2, '0')), DATE_FORMAT(NOW(), '%Y%m')) >= 6
	    	THEN
	    		IF((SELECT COUNT(*) FROM mod_logs WHERE mod_logs.module='sfr' AND mod_logs.parent_id=sfr.id AND mod_logs.log_num = 0 AND mod_logs.date > DATE_SUB(NOW(), INTERVAL 3 MONTH)), NULL, 'Y')

	    	ELSE NULL
	    END,
    	NULL
    ) AS idle,
	(SELECT CONCAT(people.email,' ',mod_logs.date,': ',mod_logs.comment)
		FROM mod_logs LEFT JOIN people ON people.id=mod_logs.poster
		WHERE mod_logs.id=(SELECT MAX(mod_logs.id) FROM mod_logs
			WHERE mod_logs.module='sfr' AND mod_logs.parent_id=sfr.id)
	) AS last_log,
	(SELECT mod_logs.date
		FROM mod_logs LEFT JOIN people ON people.id=mod_logs.poster
		WHERE mod_logs.id=(SELECT MAX(mod_logs.id) FROM mod_logs
			WHERE mod_logs.module='sfr' AND mod_logs.parent_id=sfr.id)
	) AS last_asm_log_datetime,
    cust.type AS customer_type,
    DATE_FORMAT(sfr.dt_cfsso,'%Y%m') AS Ym_cfsso,
    DATE_FORMAT(sfr.dt_cferp,'%Y%m') AS Ym_cferp,
	IF(sfr.buyer_customer_id > 0, (SELECT customer_name FROM customers c WHERE c.id=sfr.buyer_customer_id), sfr.cust_nama) AS buyer_display,
	IF(sfr.user_customer_id > 0, (SELECT customer_name FROM customers c WHERE c.id=sfr.user_customer_id), sfr.cust_nama) AS user_display,
  CASE
    WHEN
      sfr.third_party_id IS NULL OR sfr.third_party_id = 0
      THEN
      'NO THIRD PARTY'
  ELSE
    (SELECT customer_name FROM customers c WHERE c.id=sfr.third_party_id)
    END AS 'third_party_display'
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM sfr
    LEFT JOIN locations AS ssolist ON sfr.sso_id=ssolist.id
    LEFT JOIN locations AS erplist ON sfr.erp_id=erplist.id
    LEFT JOIN people AS asms ON asms.id=sfr.asm_id
    LEFT JOIN customers AS cust ON cust.customer_name=sfr.cust_nama
EOF;
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SFR', 'ALL');
    }

    /**
     * Insert a new SFR
     *
     * @deprecated
     */
    public static function insert($p)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * @deprecated
     */
    public function notify($to, $from, $subject, $message, $cc = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * SFR update method for EVP Cash Forecast
     */
    public function evpUpdate($data)
    {
        $SET = tldUtils::getSqlSet(tldUtils::cleanupFormInput($data), ['dt_cfsso']);
        $query = <<<EOF
            UPDATE sfr SET $SET
            WHERE id=$this->itsID
            LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * SFR update method for COO Cash Forecast
     */
    public function cooUpdate($data)
    {
        $SET = tldUtils::getSqlSet(tldUtils::cleanupFormInput($data), ['dt_cferp']);
        $query = <<<EOF
            UPDATE sfr SET $SET
            WHERE id=$this->itsID
            LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * SFR update method for PSM
     *
     * @deprecated
     */
    public function psmUpdate($data)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * SFR update method for ASM
     *
     * @deprecated
     */
    public function asmUpdate($data)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * SFR close method for ASM
     *
     * @deprecated
     */
    public function asmClose($close_status, $equote_id = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * SFR cancel method for ASM
     *
     * @deprecated
     */
    public function asmCancel()
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * SFR quick update method
     *
     * @deprecated
     */
    public function quickUpdate($data)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Update SFR for a field by constraints (used for transfer function)
     *
     * @deprecated
     */
    public function updateFieldByConstraints($field, $val, $constraint)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Add a comment to the log
     *
     * @deprecated
     */
    public function addLogEntry($id, $comment, $num_log = 0)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Method to change SFR status
     *
     * @deprecated
     */
    public function changeStatus($status, $options = '')
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Get OPEN status SFR by constraints
     *
     * @param mixed string array $constraints
     * @param array|string $options
     *
     * @return array of rows
     */
    public static function byOpenStatusConstraints($constraints, $options = '')
    {
        $WHERE = " (status IN ('" . implode("','", array_keys(self::getOpenStatuses())) . "')
        	OR DATEDIFF(dt_closed,NOW()) > -28)
        ";
        if (!empty($options['sfr_not_closed'])) {
            $WHERE = " status IN ('" . implode("','", array_keys(self::getOpenStatuses())) . "')";
        }
        // construct constraints if applicable
        $a = is_array($constraints) ? tldUtils::constructWhere($constraints) : $constraints;

        if (!empty($a)) {
            $WHERE .= " AND $a";
        }

        return self::byConstraints($WHERE, $options);
    }

    /**
     * Get list of SFR by Constraints
     *
     * @param $constraints
     * @param $options
     */
    public static function byConstraints($constraints, $options = '')
    {
        if (empty($constraints)) {
            return 'Empty parameter';
        }
        // Construct constraints
        if (is_array($constraints)) {
            $CONDITION = tldUtils::constructWhere($constraints);
        } else {
            $CONDITION = $constraints;
        }

        if (isset($options['useWhereCondition'])){
            $CONDITION_OPERATOR = 'WHERE';
        }
        else{
            $CONDITION_OPERATOR = 'HAVING';
        }
        // Order by option
        if (empty($options['orderBy'])) {
            $ORDERBY = 'asm_fullname, sfr.cust_ctry, sfr.cust_nama, sfr.erp_id, sfr.model, sfr.status';
        } else {
            $ORDERBY = TldDatabase::escape($options['orderBy']);
        }
        // Get the generic query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$CONDITION_OPERATOR 
$CONDITION
ORDER BY 
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byOpenStatusSSOCashForecastConstraints($a, $opts = '')
    {
        $WHERE = " status NOT IN ('ORDERED','LOST','CANCELLED', 'PARTIAL', 'ORDER_CANCELLED')";
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = "$WHERE AND $a";
        }

        return self::byOpenStatusConstraints($WHERE, $opts);
    }

    public static function byOpenStatusERPCashForecastConstraints($a, $opts = '')
    {
        $WHERE = " status NOT IN ('ORDERED','LOST','CANCELLED', 'PARTIAL', 'ORDER_CANCELLED') AND
    	(PERIOD_DIFF(DATE_FORMAT(dt_cferp,'%Y%m'),DATE_FORMAT(NOW(),'%Y%m')) >= 0
    	OR DATE_FORMAT(dt_cferp,'%Y%m') LIKE '000000') ";
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = "$WHERE AND $a";
        }

        return self::byOpenStatusConstraints($WHERE, $opts);
    }

    /**
     * Get count of SFR by SSO ERP by constraints
     *
     * @param mixed string array constraints
     *
     * @return array of rows
     */
    public static function countBySSOERPConstraints($a = '')
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = " WHERE $WHERE ";
        }
        $query = <<<EOF
			SELECT
				erp.location AS erp_fullname,
				sso.location AS sso_fullname,
				COUNT(*) AS num
			FROM sfr LEFT JOIN locations AS erp ON sfr.erp_id=erp.id
				LEFT JOIN locations AS sso ON sfr.sso_id=sso.id
				LEFT JOIN customers AS cust ON cust.customer_name=sfr.cust_nama
			$WHERE
			GROUP by erp.location, sso.location
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of OPEN SFR by SSO ERP by constraints
     *
     * @param mixed string array constraints
     *
     * @return array of rows
     */
    public static function countByOpenStatusSSOERPConstraints($a = '')
    {
        $WHERE = " (status IN ('" . implode(
                "','",
                array_keys(self::getOpenStatuses())
            ) . "') OR DATEDIFF(dt_closed,NOW()) > -28)";
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = "$WHERE AND $a";
        }

        return self::countBySSOERPConstraints($WHERE);
    }

    public static function countBySSOERPCashForecastConstraints($a = '')
    {
        $WHERE = " status NOT LIKE 'ORDERED' AND status NOT LIKE 'PARTIAL' AND (
        	(PERIOD_DIFF(DATE_FORMAT(dt_cferp,'%Y%m'),DATE_FORMAT(NOW(),'%Y%m')) >= 0
        	OR DATE_FORMAT(dt_cferp,'%Y%m') LIKE '000000')
        OR
			(PERIOD_DIFF(DATE_FORMAT(dt_cfsso,'%Y%m'),DATE_FORMAT(NOW(),'%Y%m')) >= 0
        	OR DATE_FORMAT(dt_cfsso,'%Y%m') LIKE '000000')
        )";
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = "$WHERE AND $a";
        }

        return self::countByOpenStatusSSOERPConstraints($WHERE);
    }

    public static function byCashForecastConstraints($a, $opts = '')
    {
        $firstDayOfCurrentMonth = date('Y-m-01');

        $WHERE = " status NOT IN ('ORDERED', 'PARTIAL')
        AND (
            dt_cferp = '0000-00-00'
            OR dt_cferp >= '$firstDayOfCurrentMonth'
            OR dt_cfsso = '0000-00-00'
            OR dt_cfsso >= '$firstDayOfCurrentMonth'
        )
        ";
        if (is_array($a)) {
            $a = tldUtils::constructWhere($a);
        }
        if (!empty($a)) {
            $WHERE = "$WHERE AND $a";
        }

        $opts['useWhereCondition'] = true;
        return self::byOpenStatusConstraints($WHERE, $opts);
    }
}

/**
 * Master Sales Forecast Record, Class for handling groups of SFR
 *
 *
 */
class tldMSFR
{

    private $id = null;
    private $name = ''; // useless for now but will be useful in the future
    private $sfrs = []; // an array of tldSFR
    private $sfrs_rm = []; // used to keep a trace before the persist function
    private $lazy = null;

    /**
     * tldMSFR constructor.
     *
     * @param int $id
     * @param string $name
     * @param array $sfrs
     * @param bool $lazy
     */
    public function __construct($id = null, $name = '', $sfrs = [], $lazy = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->lazy = $lazy;
        if ($lazy) {
            foreach ($sfrs as $sfr) {
                $this->sfrs[(string)$sfr] = null;
            }
        } else {
            foreach ($sfrs as $sfr) {
                $this->sfrs[(string)$sfr] = new tldSFR($sfr);
            }
        }

    }

    /**
     * tldMSFR public static loader. Use it to load a MSFR from the database
     * example :
     * $msfr = tldMSFR::loader($id_sfr);
     *
     * @param int $id
     * @param bool $lazy
     * @return null|tldMSFR
     */
    public static function loader($id, $lazy = false)
    {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }

        $query = "select * from sfr_master where id='$id'";
        $res = tldUtils::getSqlToAssocArray($query);
        if (empty($res)) {
            return null;
        }
        $msfr_name = $res[0]['name'];

        $query = "select id from sfr where sfr_master_id='$id'";
        $res = tldUtils::getSqlToAssocArray($query);
        $sfrs = array_column($res, 'id');

        return new self($id, $msfr_name, $sfrs, $lazy);
    }

    /**
     * Basic get value for the id
     *
     * @return int|null
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Basic get value for the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Basic get value for the Lazy attribute
     *
     * @return bool
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * Add an sfr to the group
     *
     * @param $sfr_id
     * @return tldMSFR $this
     */
    public function addSFR($sfr_id)
    {
        $sfr_id = (string)$sfr_id;
        if (!in_array($sfr_id, $this->getSFRList())) {
            if ($this->lazy) {
                $this->sfrs[$sfr_id] = null;
            } else {
                $this->sfrs[$sfr_id] = new tldSFR($sfr_id);
            }
        }
        return $this;
    }

    /**
     * Get an array with every linked SFR id
     *
     * @return array
     */
    public function getSFRList()
    {
        return array_keys($this->sfrs);
    }

    /**
     * function to save your current object in the database.
     * Delete the msfr from the bdd if the object does not have at least one sfr.
     * return an empty string if everything went well and an error message if something went wrong.
     *
     * @deprecated
     */
    public function persist()
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Remove an sfr from the group
     *
     * @param $sfr_id
     * @return tldMSFR $this
     */
    public function removeSFR($sfr_id)
    {
        $sfr_id = (string)$sfr_id;
        if (in_array($sfr_id, $this->getSFRList())) {
            unset($this->sfrs[$sfr_id]);
            $this->sfrs_rm[] = $sfr_id;
        }
        return $this;
    }

    /**
     * Function to update every linked sfr' status
     * return an empty string if everything went well and an error message if something went wrong.
     *
     * @deprecated
     */
    public function massUpdateSFRStatus($status)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Function used to reload or get every information from SFR
     */
    public function refresh()
    {
        foreach ($this->sfrs as $key => $sfr) {
            if ($sfr === null) {
                $this->sfrs[$key] = new tldSFR($key);
            } else {
                $sfr->refresh();
            }
        }
        $this->lazy = false;
    }

    /**
     * Function used to update every linked sfr' data excepted status.
     * return an empty string if everything went well and an error message if something went wrong.
     *
     * @deprecated
     */
    public function massUpdateSFRReccord($data)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Public method to notified people when massUpdateSFRReccrd is used.
     * This only send one big email containing every information.
     * return an empty string if everything went well and an error message if something went wrong.
     *
     * @deprecated
     */
    static public function massNotification($short_com, $msg, $user_id, $sfrs)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }
}

class tldESRL
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @param $erid
     *
     * @return array
     */

    public static function byERID($erid)
    {
        $query = <<<EOF
    SELECT esr.id, esrl.id as esrl_id, dt_open, status
    FROM esrl
    LEFT JOIN esr on esrl.parent_id = esr.id
    WHERE esrl.erid=$erid
    ORDER BY esr.id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function checkER($id)
    {
        $query = <<<EOF
        SELECT
        sor_lines.inco AS sol_inco,
        service.customer_id AS er_cuid,
        service.buyer_customer_id AS er_buyer_cuid

        FROM service
            LEFT JOIN sor_units ON service.sor_uid=sor_units.id
            LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
        WHERE service.id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getHeader()
    {
        $query = <<<EOF
        SELECT *,
        CASE WHEN service.sn IS NULL THEN CONCAT('T',esrl.erid) ELSE service.sn END AS equip_sn,
        sor_lines.model AS model,
        sor_lines.inco AS sol_inco,
        esr.inco AS esr_inco,
        sor_lines.inco_loc AS sol_inco_loc,
        service.delivery_location as delivery_loc,
        service.airport_code AS final_dest,
        sor_tran.nref AS er_invoice_num,
        service.date_shipped AS er_dt_shipped,
        dt_shipped,
        dt_estimated,
        dt_arrived

        FROM esrl
            LEFT JOIN esr ON esrl.parent_id=esr.id
            LEFT JOIN service ON esrl.erid=service.id AND esrl.parent_id = service.esrid
            LEFT JOIN sor_tran ON service.tranid_sso=sor_tran.id
            LEFT JOIN sor_units ON service.sor_uid=sor_units.id
            LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
            LEFT JOIN sor ON sor_lines.parent_id=sor.id
        WHERE esrl.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Generic ESRL update method
     *
     * @param              $data   array of esrl datas
     * @param array|string $fields array of esrl fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Insert a new ESR line
     *
     * @param array $p key value pairs
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public static function insert($p)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Add a comment to the log
     *
     * @param     $id
     * @param     $comment
     * @param int $num_log
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $num_log = 0)
    {
        throw new Exception('This is no longer used');
    }

    /** Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'ESRL');
    }

    /**
     * Delete a ESR line
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function delete()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * Insert multiple new ESR line
     *
     * @param array of SPR array
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function insertMulti($p)
    {
        throw new Exception('This is no longer used');
    }

    public function updateLinkedCsrScheduledDate($newDate)
    {
        throw new Exception('This is no longer used');
    }
}

/**
 * Competitor Pricing Records - Appends to FCRs
 *
 * @author Keith Marshall
 *
 */
class tldCPR
{
    public $itsID;
    public $sfrID;
    public $fcrID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = (int)$id;
        $this->itsHeader = $this->getHeader();
        $this->sfrID = (int)$this->itsHeader['sfr_id'];
        $this->fcrID = (int)$this->itsHeader['parent_id'];
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        if (!empty($this->itsHeader)) {
            return $this->itsHeader;
        }
        $query = <<<EOF
        SELECT cpr.*,
          (SELECT company_name
          FROM cor
          WHERE cor.id = cpr.competitor) AS competitor_name,
          (SELECT lists.list_item
          FROM lists
          WHERE lists.list_name = 'list.inco.terms' AND lists.list_key = cpr.inco_terms) AS inco_dsca,
          IF(cpr.markup_percent > 0, CONCAT(cpr.markup_percent, '%'), '') AS percent_display,
          FORMAT(cpr.price / cpr.qty, 2) AS calc_unit_price,
          fcr.sub_status, fcr.reason, fcr.parent_id AS sfr_id
        FROM cpr
          LEFT JOIN fcr ON fcr.id = cpr.parent_id
        WHERE cpr.id = $this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function bySFR($sfrid, $opts = '')
    {
        $id = (int)$sfrid;

        return self::byConstraints("sfr_id = $id", $opts);
    }

    public function byFCR($fcrid, $opts = '')
    {
        $id = (int)$fcrid;

        return self::byConstraints("parent_id = $id", $opts);
    }

    public static function byLatest()
    {
        return self::byConstraints(null, ['orderBy' => 'id DESC', 'limit' => 10]);
    }

    public function byCompetitorID($id, $opt = [])
    {
        if (empty($id) || !is_numeric($id)) {
            return;
        }
        $a = ['competitor' => $id];

        return self::byConstraints($a, $opt);
    }

    /**
     * Get list of FCR by Constraints
     *
     * @param array $constraints
     * @param string $options
     */
    public static function byConstraints($constraints, $options = '')
    {
        // Construct constraints
        if (is_array($constraints)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($constraints);
        } elseif (!empty($constraints)) {
            $HAVING = 'HAVING ' . $constraints;
        } else {
            $HAVING = '';
        }
        // Order by option
        if (empty($options['orderBy'])) {
            $ORDERBY = '';
        } else {
            $ORDERBY = 'ORDER BY ' . $options['orderBy'];
        }
        // Order by option
        if (!empty($options['limit'])) {
            $LIMIT = 'LIMIT ' . $options['limit'];
        } else {
            $LIMIT = '';
        }
        // Get the generic query
        $query = <<<EOF
        SELECT cpr.*,
          (SELECT company_name
          FROM cor
          WHERE cor.id = cpr.competitor) AS competitor_name,
          (SELECT lists.list_item
          FROM lists
          WHERE lists.list_name = 'list.inco.terms' AND lists.list_key = cpr.inco_terms) AS inco_dsca,
          IF(cpr.markup_percent > 0, CONCAT(cpr.markup_percent, '%'), '') AS percent_display,
          FORMAT(cpr.price / cpr.qty, 2) AS calc_unit_price,
          fcr.sub_status, fcr.reason, fcr.parent_id AS sfr_id
        FROM cpr
          LEFT JOIN fcr ON fcr.id = cpr.parent_id
        $HAVING
        $ORDERBY
        $LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function search($keyword, $parameters = [], $options = '')
    {
        $constraints = [];
        $parameters = tldUtils::cleanupFormInput($parameters);
        if (ctype_digit($keyword) AND $keyword > 0) {
            $id = (int)$keyword;
            $constraints[] = "(id = $id OR sfr_id = $id OR markup_percent = $id)";
        } elseif (!empty($keyword)) {
            $keyword = TldDatabase::escape(stripslashes($keyword));
            $constraints[] = "(
				competitor_name LIKE '%$keyword%' OR
				inco_dsca LIKE '%$keyword%' OR
				inco_terms LIKE '%$keyword%' OR
				model LIKE '%$keyword%' OR
				options LIKE '%$keyword%' OR
				currency LIKE '%$keyword%' OR
				reason LIKE '%$keyword%'
			)";
        }
        if ($parameters['sql_date_from'] OR $parameters['sql_date_to']) {
            $from = $to = 'NOW()';
            if (!empty($parameters['sql_date_from'])) {
                $from = "'{$parameters['sql_date_from']}'";
            }
            if (!empty($parameters['sql_date_to'])) {
                $to = "'{$parameters['sql_date_to']}'";
            }
            $constraints[] = "created BETWEEN $from AND $to";
        }
        if (!empty($parameters['status'])) {
            if ($parameters['status'] === 'PARTIAL') {
                $constraints[] = "sub_status LIKE 'PARTIAL%'";
            } else {
                $constraints[] = "sub_status = '{$parameters['status']}'";
            }
        }
        if (!empty($parameters['reason'])) {
            $constraints[] = "reason LIKE '{$parameters['reason']}'";
        }
        if (!empty($parameters['competitor'])) {
            $constraints[] = "competitor = {$parameters['competitor']}";
        }

        return self::byConstraints(implode(' AND ', $constraints), $options);
    }

    public function getFromHeader($key)
    {
        return $this->itsHeader[$key];
    }

    public function getCompetitorArray()
    {
        $query = <<<EOF
    	SELECT id, company_name
    	FROM cor
    	ORDER BY company_name
EOF;

        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['id', 'company_name']);
    }

    public function getCurrencyArray()
    {
        $query = <<<EOF
    	SELECT cur.t_ccur, '('+cur.t_ccur+') '+cur.t_dsca AS dsca
    	FROM ttcmcs002300 AS cur
    	ORDER BY cur.t_ccur
EOF;

        return tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan', 'smartyOptions' => ['t_ccur', 'dsca']]);
    }

    public function getIncotermsArray()
    {
        $query = <<<EOF
    	SELECT list_key, list_item
    	FROM lists
    	WHERE list_name = 'list.inco.terms'
    	ORDER BY list_item
EOF;

        return tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['list_key', 'list_item']);
    }

    /**
     * @deprecated
     */
    public static function insert(Array $p)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }
}

/**
 * Class for Spare Parts Quote
 */
class tldSPQ
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getPosterID()
    {
        return $this->itsHeader['entered_by'];
    }

    public function getPosterFullname()
    {
        return $this->itsHeader['poster_fullname'];
    }

    public function getPosterEmail()
    {
        return $this->itsHeader['poster_email'];
    }

    public function getContactFullname()
    {
        return $this->itsHeader['contact_fullname'];
    }

    public function getContactEmail()
    {
        return $this->itsHeader['contact_email'];
    }

    public function getSPHFullname()
    {
        return $this->itsHeader['sph_fullname'];
    }

    public function getTasks($status = 'ALL')
    {
        if (!in_array($status, ['ALL', 'OPEN'])) {
            return;
        }

        return tldTask::byParent($this->itsID, 'SPQ', $status);
    }

    public static function countBySPHStatus($user_id = '')
    {
        if (!empty($user_id)) {
            $WHERE = "WHERE spq.poster_id = $user_id";
        }
        $query = <<<EOF
SELECT
    spq.status,
    IF(spq.sph_id=0,'NO SPH',sph.name) AS sph_fullname,
    COUNT(*) AS num
FROM spq
LEFT JOIN locations_sph AS sph ON spq.sph_id=sph.id
$WHERE
GROUP BY spq.status, sph_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySPHStatus($sph, $status, $user_id = '')
    {
        if ($sph !== 'ALL') {
            $a['sph_fullname'] = $sph;
        }
        if ($status !== 'ALL') {
            $a['spq.status'] = $status;
        }
        if ($user_id <> '') {
            $a['spq.poster_id'] = $user_id;
        }

        return self::byConstraints($a);
    }

    public static function byWCstatus($status, $location)
    {
        $WHERE = '';

        if ($location !== 'ALL') {
            $WHERE = "AND er.man_location = '$location'";
        }

        switch($status){
            case 'ALL':
                $date = (new DateTime('15 days ago'))->format('Y-m-d');
            break;
            case 'today':
                $date = (new DateTime('1 days ago'))->format('Y-m-d');
            break;
            case 'late';
                $date = (new DateTime('15 days ago'))->format('Y-m-d');
                $date2 = (new DateTime('1 days ago'))->format('Y-m-d');
                $WHERE .= "AND log.date < $date2";
            break;
        }
        $logComment = tldWC::NOTIFICATION_LOG.'%';
        $query = <<<EOF
            SELECT wc.id,
            part.part_number,
            part.part_description, 
            part.notes,
            log.date,
            log.comment
            FROM mod_logs AS log
                LEFT JOIN warranty AS wc ON log.parent_id = wc.id
                LEFT JOIN warranty_parts AS part ON part.parent_id = wc.id
                LEFT JOIN service AS er ON wc.parent_id = er.id
            WHERE log.comment LIKE '$logComment'
                AND log.module = 'WC'
                AND part.supply_it != 1
                AND log.date > $date
                $WHERE
            ORDER BY 
                log.date DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);

    }

    public static function fullReport($start, $end, $sph, $status = '', $customer = '')
    {
        $WHERE = '';
        if ($sph !== 'All') {
            $WHERE .= " AND sph_fullname = '$sph' ";
        }
        if (!empty($status)) {
            $WHERE .= " AND spq.status = '$status' ";
        }
        if (!empty($customer)) {
            $WHERE .= " AND spq.customer_id = $customer ";
        }
        $ORDERBY = 'ORDER BY spq.dt_open';

        $query = <<<EOF
SELECT
    spq.*,
    (SELECT customer_name FROM customers WHERE customers.id=spq.customer_id) AS customer_name,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    CONCAT(contacts.firstname,' ',contacts.lastname) AS contact_fullname,
    contacts.email AS contact_email,
    people.email AS poster_email,
    (SELECT name FROM locations_sph AS sph WHERE sph.id=spq.sph_id) AS sph_fullname
FROM spq
    LEFT JOIN people ON people.id=spq.poster_id
    LEFT JOIN extranet_users AS contacts ON contacts.id=spq.contact_id
HAVING DATE_FORMAT(spq.dt_open,'%Y-%m-%d') BETWEEN '$start' AND '$end' $WHERE
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($constraints, $orderBy = 'spq.id')
    {
        if (is_array($constraints)) {
            $WHERE = tldUtils::constructWhere($constraints);
        } else {
            $WHERE = $constraints;
        }
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
SELECT
    spq.*,
    (SELECT customer_name FROM customers WHERE customers.id=spq.customer_id) AS customer_name,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    CONCAT(contacts.firstname,' ',contacts.lastname) AS contact_fullname,
    contacts.email AS contact_email,
    people.email AS poster_email,
    (SELECT name FROM locations_sph AS sph WHERE sph.id=spq.sph_id) AS sph_fullname
FROM spq
    LEFT JOIN people ON people.id=spq.poster_id
    LEFT JOIN extranet_users AS contacts ON contacts.id=spq.contact_id
EOF;
        if ($WHERE) {
            $query .= " HAVING $WHERE";
        }
        $query .= <<<EOF
        ORDER BY $orderBy
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function search($id, $sph = '')
    {
        if (empty($id)) {
            return;
        }
        if (!empty($sph)) {
            $WHERE = "WHERE spq.sph_id = $sph";
        }
        $query = <<<EOF
SELECT
    spq.*,
    (SELECT customer_name FROM customers WHERE customers.id=spq.customer_id) AS customer_name,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    CONCAT(contacts.firstname,' ',contacts.lastname) AS contact_fullname,
    contacts.email AS contact_email,
    people.email AS poster_email,
    (SELECT name FROM locations_sph AS sph WHERE sph.id=spq.sph_id) AS sph_fullname
FROM spq
    LEFT JOIN people ON people.id=spq.poster_id
    LEFT JOIN extranet_users AS contacts ON contacts.id=spq.contact_id
    $WHERE
    HAVING customer_name like '$id'
        OR contact_fullname like '$id'
        OR status like '$id'
        OR qono like '$id'
        OR baan_so like '$id'
        OR rfq like '$id'
        OR request_type like '$id'
        OR poster_fullname like '$id'
        OR contact_email like '$id'
    ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byLatest($user_id = '', $num = 10)
    {
        if (!empty($user_id)) {
            $WHERE = "WHERE spq.poster_id = $user_id";
        }
        $query = <<<EOF
SELECT
    spq.*,
    (SELECT customer_name FROM customers WHERE customers.id=spq.customer_id) AS customer_name,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    CONCAT(contacts.firstname,' ',contacts.lastname) AS contact_fullname,
    contacts.email AS contact_email,
    people.email AS poster_email,
    (SELECT name FROM locations_sph AS sph WHERE sph.id=spq.sph_id) AS sph_fullname,
    IF(qono_val = 0, '', qono_val) AS qono_val
FROM spq
LEFT JOIN people ON people.id=spq.poster_id
LEFT JOIN extranet_users AS contacts ON contacts.id=spq.contact_id
$WHERE
ORDER BY spq.id DESC
LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    spq.*,
    (SELECT customer_name FROM customers WHERE customers.id=spq.customer_id) AS customer_name,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
    CONCAT(contacts.firstname,' ',contacts.lastname) AS contact_fullname,
    contacts.email AS contact_email,
    people.email AS poster_email,
    (SELECT name FROM locations_sph AS sph WHERE sph.id=spq.sph_id) AS sph_fullname,
    IF(qono_val = 0, '', qono_val) AS qono_val
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM spq
    LEFT JOIN people ON people.id=spq.poster_id
    LEFT JOIN extranet_users AS contacts ON contacts.id=spq.contact_id
EOF;
    }

    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE spq.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getSPQByUser($userid, $status = '')
    {
        if (empty($userid)) {
            return 'User ID# is missing';
        }
        if (empty($status)) {
            return 'Status is missing';
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE spq.poster_id=$userid AND spq.status = '$status'";

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'dt_received',
            'dt_ship',
            'request_type',
            'poster_id',
            'sph_id',
            'customer_id',
            'contact_id',
            'comment',
            'rfq',
        ];
        $SET = tldUtils::getSqlSet($p, $fields);
        $query = "INSERT INTO spq SET status='PENDING', dt_open=NOW(), $SET";

        return tldUtils::sqlInsert($query);
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE spq SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = "DELETE FROM spq WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getLastStatus()
    {
        return $this->itsHeader['last_status'];
    }

    public static function getStatusList(): array
    {
        return [
            'PENDING' => 'PENDING',
            'SUBMITTED PARTIAL' => 'SUBMITTED PARTIAL',
            'SUBMITTED FULL' => 'SUBMITTED FULL',
            'ORDERED FULL' => 'ORDERED FULL',
            'ORDERED PARTIAL' => 'ORDERED PARTIAL',
            'CANCELLED' => 'CANCELLED',
            'LOST' => 'LOST',
            'SUSPENDED' => 'SUSPENDED',
        ];
    }

    public static function getTypeList(): array
    {
        return [
            'Email' => 'Email',
            'Phone' => 'Phone',
            'Fax' => 'Fax',
            'Other' => 'Other',
        ];
    }

    public function getAllowedStatus()
    {
        switch ($this->getStatus()) {
            case 'PENDING':
                $result = ['SUBMITTED FULL', 'SUBMITTED PARTIAL', 'ORDERED FULL', 'ORDERED PARTIAL', 'SUSPENDED'];
                break;
            case 'SUBMITTED PARTIAL':
                $result = ['SUBMITTED FULL', 'SUSPENDED'];
                break;
            case 'SUBMITTED FULL':
                $result = ['ORDERED FULL', 'ORDERED PARTIAL', 'CANCELLED', 'LOST'];
                break;
            case 'ORDERED FULL':
            case 'ORDERED PARTIAL':
            case 'CANCELLED':
            case 'LOST':
                $result = ['PENDING'];
                break;
            case 'SUSPENDED':
                // Get last status
                $lastStatus = $this->getLastStatus();
                if (!empty($lastStatus)) {
                    $result = [$lastStatus];
                }
                break;
        }

        return $result;
    }

    public function changeStatus($data)
    {
        $currentStatus = $this->getStatus();
        $status = $data['status'];
        if (!in_array($status, $this->getAllowedStatus())) {
            return "$status is not a valid status";
        }
        if ($data['dt_submit']) {
            $d = DateTime::createFromFormat('Y-m-d', $data['dt_submit']);
            if ($d && $d->format('Y-m-d') == $data['dt_submit']) {
                $submit_date = "'" . $data['dt_submit'] . "'";
            } else {
                $submit_date = 'NOW()';
            }
        }
        $qono = $data['qono'];
        $qono_val = $data['qono_val'];
        $baan_so = $data['baan_so'];
        $submit = '';
        $close = '';

        switch ($status) {
            case 'SUBMITTED FULL':
                $submit .= ", dt_submit = $submit_date";
            case 'SUBMITTED PARTIAL':
                if ($currentStatus !== 'SUSPENDED') {
                    $submit .= ", qono = '$qono', qono_val = '$qono_val'";
                }
            case 'PENDING':
                if ($currentStatus === 'SUSPENDED') {
                    $actualSuspendedDate = new DateTime($this->itsHeader['dt_suspended']);
                    $today = new DateTime(date('Y-m-d'));
                    $intervalSinceSuspended = $actualSuspendedDate->diff($today);
                    $nbDaysSinceSuspended = $intervalSinceSuspended->format('%a');
                    $this->update(
                        [
                            'dt_suspended' => '0000-00-00',
                            'days_suspended' => $nbDaysSinceSuspended + $this->itsHeader['days_suspended'],
                        ]
                    );
                }
                break;
            case 'ORDERED PARTIAL':
            case 'ORDERED FULL':
                if ($currentStatus === 'PENDING') {
                    $submit .= ", qono = '$qono', qono_val = '$qono_val', dt_submit = $submit_date";
                }
                $submit .= ", baan_so = '$baan_so'";
            case 'CANCELLED':
            case 'LOST':
                $close .= ', dt_closed = NOW()';
                break;
            case 'SUSPENDED':
                if (in_array($currentStatus, ['PENDING', 'SUBMITTED PARTIAL'])) {
                    $this->update(['dt_suspended' => date('Y-m-d')]);
                }
                break;
        }

        $query = "UPDATE spq SET status='$status', last_status='$currentStatus' $submit $close WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function quickClose($data)
    {
        $status = $data['status'];
        if (!in_array($status, $this->getAllowedStatus())) {
            return "$status is not a valid status";
        }
        $query = "UPDATE spq SET status='$status', dt_closed = NOW() WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SPQ');
    }

    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SPQ';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SPQ');
    }

    public function getContact($contactID = '')
    {
        if (empty($contactID)) {
            $contactID = $this->itsHeader['contact_id'];
        }
        if (empty($contactID) || $contactID == 0) {
            return;
        }
        $query = <<<EOF
            SELECT *, CONCAT(lastname, ', ', firstname) AS contact_fullname
            FROM extranet_users
            WHERE id=$contactID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getQIR($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
ROUND(SUM(CASE WHEN DATEDIFF( dt_submit, dt_open )-days_suspended <= 1 THEN 1 ELSE 0 END)*100/count(*),0) as val
FROM spq
WHERE DATE_FORMAT(dt_submit, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND DATE_FORMAT(dt_submit, '%Y%m') != '000000' AND sph_id = $sph
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getATQ($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
IF(
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_submit,'%Y%m%d'),DATE_FORMAT(dt_open,'%Y%m%d')))/count(*),1
    ) IS NULL,
    0,
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_submit,'%Y%m%d'),DATE_FORMAT(dt_open,'%Y%m%d'))-days_suspended)/count(*),1
    )
) as val
FROM spq
WHERE DATE_FORMAT(dt_open, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND DATE_FORMAT(dt_submit, '%Y%m') != '000000' AND sph_id = $sph
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getQOL($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
DATEDIFF(DATE_FORMAT(NOW(),'%Y%m%d'), DATE_FORMAT(dt_open,'%Y%m%d')) AS val
FROM spq
WHERE DATE_FORMAT(dt_open, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND DATE_FORMAT(dt_submit, '%Y%m') = '000000' AND sph_id = $sph
ORDER BY val DESC
LIMIT 1
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function SPQAvgReactivity($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
IF(
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_submit,'%Y%m%d'),DATE_FORMAT(dt_received,'%Y%m%d')))/count(*),1
    ) IS NULL,
    0,
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_submit,'%Y%m%d'),DATE_FORMAT(dt_received,'%Y%m%d'))-days_suspended)/count(*),1
    )
) as val
FROM spq
WHERE DATE_FORMAT(dt_received, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND DATE_FORMAT(dt_submit, '%Y%m') != '000000' AND sph_id = $sph
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function SPQAvgCustAnswer($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
IF(
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_closed,'%Y%m%d'),DATE_FORMAT(dt_submit,'%Y%m%d')))/count(*),1
    ) IS NULL,
    0,
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_closed,'%Y%m%d'),DATE_FORMAT(dt_submit,'%Y%m%d')))/count(*),1
    )
) as val
FROM spq
WHERE DATE_FORMAT(dt_submit, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND status IN('ORDERED FULL','ORDERED PARTIAL','CANCELLED','LOST') AND sph_id = $sph
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function SPQAvgManagement($period_Ymd, $sph)
    {
        $date = new DateTime($period_Ymd);
        $period = $date->format('Y-m-d');
        $query = <<<EOF
SELECT
DATE_FORMAT('$period', '%Y-%m') AS period,
IF(
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_closed,'%Y%m%d'),DATE_FORMAT(dt_received,'%Y%m%d')))/count(*),1
    ) IS NULL,
    0,
    ROUND(
        SUM(DATEDIFF(DATE_FORMAT(dt_closed,'%Y%m%d'),DATE_FORMAT(dt_received,'%Y%m%d'))-days_suspended)/count(*),1
    )
) as val
FROM spq
WHERE DATE_FORMAT(dt_received, '%Y%m') = DATE_FORMAT('$period', '%Y%m') AND status IN('ORDERED FULL','ORDERED PARTIAL','CANCELLED','LOST') AND sph_id = $sph
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function getLinkableModules()
    {
        return [
            'TOC',
        ];
    }
}

/**
 * Class for Spare Parts Requests
 */
class tldSPR
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getPosterID()
    {
        return $this->itsHeader['entered_by'];
    }

    public function getPosterFullname()
    {
        return $this->itsHeader['entered_by_fullname'];
    }

    public function getPosterEmail()
    {
        return $this->itsHeader['entered_by_email'];
    }

    public function getAssignorID()
    {
        return $this->itsHeader['assignor'];
    }

    public function getAssignorFullname()
    {
        return $this->itsHeader['assignor_fullname'];
    }

    public function getAssignorEmail()
    {
        return $this->itsHeader['assignor_email'];
    }

    public function getSSOID()
    {
        return $this->itsHeader['sso_id'];
    }

    public function getSSOFullname()
    {
        return $this->itsHeader['sso_fullname'];
    }

    public function getSPHID()
    {
        return $this->itsHeader['sph_id'];
    }

    public function getSPHFullname()
    {
        return $this->itsHeader['sph_fullname'];
    }

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    spr.*,
    sso.location AS sso_fullname,
    sph.location AS sph_fullname,
    CONCAT(poster.lastname,', ',poster.firstname) AS entered_by_fullname,
    poster.email AS entered_by_email,
    CONCAT(assignor.lastname,', ',assignor.firstname) AS assignor_fullname,
    assignor.email AS assignor_email
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM spr
    LEFT JOIN locations AS sso ON sso.id=spr.sso_id
    LEFT JOIN locations AS sph ON sph.id=spr.sph_id
    LEFT JOIN people AS poster ON poster.id=spr.entered_by
    LEFT JOIN people AS assignor ON assignor.id=spr.assignor
EOF;
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE spr.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a new SPR
     *
     * @param array $p key value pairs
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public static function insert($p)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    /**
     * Update SPR
     *
     * @param        $a
     * @param string $fields
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE spr SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get SPR status
     *
     * @return string status
     */
    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public static function getStatusList()
    {
        return [
            'OPEN' => 'OPEN',
            'SHIPPED' => 'SHIPPED',
            'CLOSED' => 'CLOSED',
        ];
    }

    /**
     * Get allowed SPR status
     *
     * @return array of allowed status
     */
    public function getAllowedStatus()
    {
        switch ($this->getStatus()) {
            case 'OPEN':
                $result = ['SHIPPED'];
                break;
            case 'SHIPPED':
                $result = ['CLOSED'];
                break;
            case 'CLOSED':
                $result = ['SHIPPED', 'OPEN'];
                break;
        }

        return $result;
    }

    /**
     * Change SPR status
     *
     * @param string status we want
     *
     * @return string error if so or nothing
     */
    public function changeStatus($status)
    {
        if (!in_array($status, $this->getAllowedStatus(), true)) {
            return "$status is not a valid status";
        }
        $query = "UPDATE spr SET status='$status' WHERE id=$this->itsID";
        $e = tldUtils::sqlQuery($query);
        if (is_string($e)) {
            return $e;
        }
        // Post Action
        switch ($status) {
            case 'CLOSED':
                // Maybe linked to a SB line?
                tldSB_Line::triggerStatusChange('SPR', $this->itsID);
                break;
        }

        return $e;
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function isClosed()
    {
        return $this->getStatus() === 'CLOSED';
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SPR');
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SPR');
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SPR', 'ALL');
    }

    public function getLines()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
		SELECT *
		FROM spr_lines
        WHERE parent_id=$this->itsID
		ORDER BY id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SPR', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SPR', $module);
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SPR';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('SPR', $this->itsID, $type, $item);
    }

    public function addPart($a)
    {
        $a['parent_id'] = $this->itsID;

        return tldSPRL::insert($a);
    }

    /**
     * Search SPRs
     *
     * @param $id
     *
     * @return array
     *
     */
    public static function search($id)
    {
        if (empty($id)) {
            return;
        }
        $query = <<<EOF
		SELECT *,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.assignor
            ) AS assignor_fullname,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.entered_by
            ) AS entered_by_fullname,
			(SELECT location
			FROM locations
			WHERE locations.id=spr.sso_id) AS sso_fullname,
			(SELECT location
    		FROM locations
        	WHERE locations.id=spr.sph_id) AS sph_fullname,
        	(SELECT service.type FROM service WHERE
            	service.id=(SELECT t0.item FROM mod_links AS t0 WHERE
            		t0.parent_id=spr.id AND t0.module LIKE 'SPR'
            		AND t0.type LIKE 'ER' LIMIT 1)
        	) AS er_type,
        	COALESCE(
                (SELECT t0.parent_id FROM mod_links AS t0 WHERE
                    t0.item=spr.id AND t0.module LIKE 'SB%'
                    AND t0.type LIKE 'SPR' LIMIT 1),
                (SELECT t0.item FROM mod_links AS t0 WHERE
                    t0.parent_id=spr.id AND t0.module LIKE 'SPR'
                    AND t0.type LIKE 'SB%' LIMIT 1)
            ) AS sb_id
		FROM spr
        HAVING assignor_fullname like '$id'
            OR entered_by_fullname like '$id'
            OR status like '$id'
            OR cust_nama like '$id'
            OR cust_cona like '$id'
            OR ship_to like '$id'
            OR nota like '$id'
		ORDER BY id DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get the latest SPRs
     *
     * @param integer $num
     *
     * @return array
     */
    public static function byLatest($num = 10)
    {
        $query = <<<EOF
		SELECT *,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.assignor
            ) AS assignor_fullname,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.entered_by
            ) AS entered_by_fullname,
			(SELECT location
			FROM locations
			WHERE locations.id=spr.sso_id) AS sso_fullname,
			(SELECT location
    		FROM locations
        	WHERE locations.id=spr.sph_id) AS sph_fullname,
        	(SELECT service.type FROM service WHERE
            	service.id=(SELECT t0.item FROM mod_links AS t0 WHERE
            		t0.parent_id=spr.id AND t0.module LIKE 'SPR'
            		AND t0.type LIKE 'ER' LIMIT 1)
        	) AS er_type,
        	COALESCE(
                (SELECT t0.parent_id FROM mod_links AS t0 WHERE
                    t0.item=spr.id AND t0.module LIKE 'SB%'
                    AND t0.type LIKE 'SPR' LIMIT 1),
                (SELECT t0.item FROM mod_links AS t0 WHERE
                    t0.parent_id=spr.id AND t0.module LIKE 'SPR'
                    AND t0.type LIKE 'SB%' LIMIT 1)
            ) AS sb_id
		FROM spr
		ORDER BY id DESC
		LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byConstraints($constraints, $orderBy = 'spr.id')
    {
        if (is_array($constraints)) {
            $where = tldUtils::constructWhere($constraints);
        } else {
            $where = $constraints;
        }
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
            SELECT *,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.assignor
            ) AS assignor_fullname,
			(SELECT CONCAT(lastname,', ',firstname)
            FROM people WHERE people.id=spr.entered_by
            ) AS entered_by_fullname,
			IF(sso_id=0, 'NO SSO', (SELECT location
    			FROM locations
        		WHERE locations.id=spr.sso_id
                )
            ) AS sso_fullname,
            IF(sph_id=0, 'NO SPH', (SELECT location
    			FROM locations
        		WHERE locations.id=spr.sph_id
                )
            ) AS sph_fullname,
            (SELECT service.type FROM service WHERE
            	service.id=(SELECT t0.item FROM mod_links AS t0 WHERE
            		t0.parent_id=spr.id AND t0.module LIKE 'SPR'
            		AND t0.type LIKE 'ER' LIMIT 1)
        	) AS er_type,
            COALESCE(
                (SELECT t0.parent_id FROM mod_links AS t0 WHERE
                    t0.item=spr.id AND t0.module LIKE 'SB%'
                    AND t0.type LIKE 'SPR' LIMIT 1),
                (SELECT t0.item FROM mod_links AS t0 WHERE
                    t0.parent_id=spr.id AND t0.module LIKE 'SPR'
                    AND t0.type LIKE 'SB%' LIMIT 1)
            ) AS sb_id
		FROM spr
EOF;
        if ($where) {
            $query .= " HAVING $where";
        }
        $query .= <<<EOF
		ORDER BY $orderBy
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBySSOStatus()
    {
        $query = <<<EOF
            select spr.status,
            IF(spr.sso_id=0,
                'NO SSO',
                locations.location
            ) as sso_fullname,
            count(*) AS num
        from spr LEFT JOIN locations ON spr.sso_id=locations.id
        group by spr.status, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySSOStatus($location, $status)
    {
        if ($location !== 'ALL') {
            $a['sso_fullname'] = $location;
        }
        if ($status !== 'ALL') {
            $a['spr.status'] = $status;
        }

        return self::byConstraints($a);
    }

    /**
     * Count SPR by status, SPH
     *
     * @return array
     */
    public static function countBySPHStatus()
    {
        $query = <<<EOF
            SELECT spr.status,
            IF(spr.sph_id=0,
                'NO SPH',
                locations.location
            ) as sph_fullname,
            count(*) AS num
        FROM spr LEFT JOIN locations ON spr.sph_id=locations.id
        GROUP BY spr.status, sph_fullname
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByWcRequest($date15DaysAgo)
    {
        $logComment = tldWC::NOTIFICATION_LOG.'%';
        $query = <<<EOF
            SELECT er.man_location,
               SUM(IF(DATEDIFF(NOW(), log.date) = 0, 1, 0)) AS today,
               SUM(IF(DATEDIFF(NOW(), log.date) BETWEEN 1 AND 15, 1, 0)) AS late
            FROM mod_logs AS log
                LEFT JOIN warranty AS wc ON log.parent_id = wc.id
                LEFT JOIN warranty_parts AS part ON part.parent_id = wc.id
                LEFT JOIN service AS er ON wc.parent_id = er.id
            WHERE log.comment LIKE '$logComment'
                AND log.module = 'WC'
                AND part.supply_it != 1
                AND log.date > $date15DaysAgo
            GROUP BY
                 er.man_location
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * List SPR by status, SPH
     *
     * @param string $location
     * @param string $status
     *
     * @return array
     */
    public static function bySPHStatus($location, $status)
    {
        if ($location !== 'ALL') {
            $a['sph_fullname'] = $location;
        }
        if ($status !== 'ALL') {
            $a['spr.status'] = $status;
        }

        return self::byConstraints($a);
    }

    /**
     * Get the SPR Print Version (not static)
     *
     * @return string htmlPrintVersion
     */
    public function getPrintVersion()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id' => 'SPR#',
                'status' => 'Status',
                'dt' => 'Date/Time Opened',
                'sso_fullname' => 'SSO',
                'entered_by_fullname' => 'Entered by',
                'assignor_fullname' => 'Requestor',
                'sph_fullname' => 'SPH',
                'cust_nama' => 'Customer Name',
                'cust_cona' => 'Customer Contact',
                'cust_tela' => 'Customer Contact Tel',
                'cust_emla' => 'Customer Email',
                'ship_to' => 'Ship To Address',
                'dt_ship' => 'Ship date',
                'ship_tnum' => 'Tracking Numbers',
                'nota' => 'Notes',
            ],
            ['title' => 'General']
        );
        $document = $report->fetch();
        $report = new tldReportColumnar(
            $this->getLines(),
            [
                'xItems' => [
                    'item' => 'PN',
                    'dsca' => 'Description',
                    'um' => 'UM',
                    'oqua' => 'Qty',
                ],
                'title' => 'Line Items',
                'sortable' => 'NO',
            ]
        );
        $document .= $report->fetch();
        // 2 - get Links (SB and ER related to the SPR)
        $report = new tldReportColumnar(
            $this->getLinksFromHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links FROM Here...',
                'links' => ['id' => "https://www.tld-gse.com/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=&erp=&id="],
                'showItemNumbers' => 'YES',
                'sortable' => 'NO',
            ]
        );

        $document .= $report->fetch();
        $report = new tldReportColumnar(
            $this->getLinksToHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'module' => 'Module',
                    'parent_id' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links TO Here...',
//                'links' => ['id' => "https://www.tld-gse.com/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=$mode&reversed=1&erp=$erp&id="],
                'showItemNumbers' => 'YES',
                'sortable' => 'NO',
            ]
        );
        $document .= $report->fetch();

        return $document;
    }

    public static function getSparePartsHubEmails()
    {
        return [
            '8' => 'euparts@tld-europe.com',                  // SPH MTL
            '31' => 'euparts@tld-europe.com',                 // SPH DUB
            '7' => 'euparts@tld-europe.com',                  // SPH EUR same as MTL
            '34' => 'warrantyparts.china@tld-asia.com',       // SPH SHA
            '4' => 'warrantyparts.china@tld-asia.com',        // SPH SHA
            '1' => 'warrantyparts.asia@tld-asia.com',         // SPH HKG
            '33' => 'parts@tld-america.com',                  // SPH WIN
            '16' => 'parts@tld-america.com',                  // SPH SAL
            '11' => 'parts@tld-america.com',                  // SPH AME
            '42' => 'parts@tld-america.com',                  // TLD LAC
            '44' => 'parts@tld-america.com',                  // TLD AME-SCM
            '46' => 'parts@tld-america.com',                  // TLD NAM
            '45' => 'parts@tld-america.com',                  // TLD AERO
            '98' => 'parts@tld-america.com',                  // TLD AERO
        ];
    }

    public static function getSparePartsHubEmailByBuID($buid)
    {
        $emails = self::getSparePartsHubEmails();

        return $emails[$buid];
    }

    public function getDefaultNotificationRecipients()
    {
        $to = [];
        // Poster & assignor
        $poster = $this->getPosterEmail();
        if (!empty($poster)) {
            $to[] = $poster;
        }
        $assignor = $this->getAssignorEmail();
        if (!empty($assignor)) {
            $to[] = $assignor;
        }
        // Look for SPH BU email
        $sphEmail = self::getSparePartsHubEmailByBuID($this->getSPHID());
        if (!empty($sphEmail)) {
            $to[] = $sphEmail;
        }

        return array_unique($to);
    }

    public function notifySPH($subject, $message, $cc = '')
    {
        $to = $this->getDefaultNotificationRecipients();

        return $this->notify(
            $to,
            $subject,
            $message,
            $cc
        );
    }

    public function notify($to, $subject, $message, $cc = '')
    {
        $message .= <<<EOF
<p>
  <a href="https://www.tld-gse.com/en/private/parts/parts.php?m[0]=spr&m[1]=view&id={$this->itsID}">
  Click here to see SPR#{$this->itsID}</a>
</p>
{$this->getPrintVersion()}
EOF;

        return tldUtils::emailAttachment(
            $to,
            'noreply@tld-gse.com',
            $subject,
            $message,
            null,
            $cc
        );
    }

}

class tldSPRL
{
    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
		SELECT *
		FROM spr_lines
		WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert a new SPR line
     *
     * @param array $p key value pairs
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'parent_id',
            'item',
            'dsca',
            'oqua',
            'um',
        ];
        $query = <<<EOF
            INSERT INTO spr_lines
        SET
EOF;
        $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Delete a SPR line
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = "DELETE FROM spr_lines WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Insert multiple new SPR line
     *
     * @param array of SPR array
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public function insertMulti($p)
    {
        if (empty($p)) {
            return;
        }
        foreach ($p as $a) {
            $e .= self::insert($a);
        }

        return $e;
    }
}

/**
 * Class for accessing and manipulating SQR data
 *
 * @package SalesAndService
 */
class tldSQR
{
    /* Constructor */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Check if SQR empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if SQR have SQE
     *
     * @return boolean
     */
    public function isSQESubmited()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
			SELECT COUNT(*) as cnt
			FROM sqe
				LEFT JOIN sqr_lines ON sqr_lines.id=sqe.parent_id
				LEFT JOIN sqr ON sqr_lines.parent_id=sqr.id
			WHERE sqr.id=$this->itsID
EOF;
        $row = tldUtils::getSqlRowToAssocArray($query);

        return $row['cnt'] > 0;
    }

    /**
     * Check if SQR out of date
     *
     * @return boolean
     */
    public function isOutOfDate()
    {
        if (empty($this->itsID)) {
            return;
        }
        $date = explode('-', $this->itsHeader['dt_validity']);

        return mktime(0, 0, 0, $date[1], $date[2], $date[0]) < mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    }

    /**
     * Check if SQR can be editable
     * WARNING use as well to know if visible by PSP
     *
     * @return boolean
     */
    public function isEditable()
    {
        if (empty($this->itsID)) {
            return;
        }

        return !in_array($this->itsHeader['status'], ['PUBLISHED']);
    }

    /**
     * Get the database row for this SQR
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
			SELECT t1.*,
				(SELECT CONCAT(lastname,', ',firstname) FROM people
					WHERE people.id=t1.tld_contact
				) AS contact,
				locations.location AS entity,
				locations.company_name AS entity_fullname
			FROM sqr AS t1
				LEFT JOIN locations	ON locations.id=t1.tld_entity
			WHERE t1.id=$this->itsID
			GROUP BY t1.id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Create a new SQR row in the database
     *
     * @param array $p
     *
     * @return boolean
     */
    public static function insert($p)
    {
        $fields = ['tld_contact', 'tld_entity', 'inco', 'inco_loc', 'dt_validity', 'note', 'dt_deadline'];
        $query = "INSERT INTO sqr SET dt_open=NOW(),status='PENDING',";
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update SQR header
     *
     * @param array $p
     *
     * @return id update or string error message
     */
    public function update($p)
    {
        if (empty($this->itsID)) {
            return;
        }
        if (empty($p)) {
            return;
        }
        $fields = ['tld_contact', 'tld_entity', 'inco', 'inco_loc', 'dt_validity', 'note', 'dt_deadline'];
        $query = 'UPDATE sqr SET ';
        $query .= tldUtils::getSqlSet($p, $fields);
        $query .= <<<EOF
	        WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Duplicate SQR (not static method)
     *
     * @return array
     */
    public function duplicate()
    {
        if (empty($this->itsID)) {
            return;
        }
        // Duplicate header
        $query = <<<EOF
			INSERT INTO sqr (dt_open,status,tld_contact,tld_entity,inco,inco_loc,dt_validity,dt_deadline,note)
			SELECT NOW(),'PENDING',tld_contact,tld_entity,inco,inco_loc,dt_validity,dt_deadline,note
			FROM sqr
			WHERE id=$this->itsID LIMIT 1
EOF;
        $e = tldUtils::sqlInsert($query);
        if (!is_numeric($e)) {
            return $e;
        }
        // Duplicate the SQR Lines
        $rows = $this->getLines();
        if (count($rows)) {
            $NewSqr = new tldSQR($e);
            foreach ($rows as $row) {
                $row['parent_id'] = $e;
                tldSQRL::insert($row);
            }
        }

        return $e;
    }

    /**
     * Delete SQR (not static method)
     *
     * @return array
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $rows = $this->getLines();
        if (count($rows)) {
            foreach ($rows as $row) {
                $sqrl = new tldSQRL($row['id']);
                $sqrl->delete();
            }
        }
        $query = <<<EOF
			DELETE FROM sqr WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get SQR status
     *
     * @return string
     */
    public function getStatus()
    {
        if (empty($this->itsID)) {
            return;
        }

        return $this->itsHeader['status'];
    }

    /**
     * Get SQR allowed status
     *
     * @return string
     */
    public function getAllowedStatus()
    {
        if (empty($this->itsID)) {
            return;
        }
        switch ($this->getStatus()) {
            case 'PENDING':
                $a['fwd'] = 'PUBLISHED';
                break;
        }

        return $a;
    }

    /**
     * Update SQR status
     *
     * @param $status
     *
     * @return string
     */
    public function changeStatus($status)
    {
        if (empty($this->itsID)) {
            return;
        }
        if (!in_array($status, ['PENDING', 'PUBLISHED'])) {
            return;
        }
        $query = "UPDATE sqr SET status='$status' WHERE id=" . $this->itsID;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get linked files to this SQR
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SQR');
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SQR', 'ALL');
    }

    /**
     * Get linked log entries
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SQR');
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SQR';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('SQR', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SQR', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SQR', $module);
    }

    /**
     * Get all linked SQR Lines
     *
     * @return mixed array or string if error
     */
    public function getLines()
    {
        return tldSQRL::byParent($this->itsID);
    }

    /**
     * Get the latest SQRs, defaults to last 10
     *
     * @param integer $qty
     *
     * @param null $option
     *
     * @return array
     */
    public static function byLatest($qty = 10, $option = null)
    {
        if (!is_int($qty)) {
            $qty = 10;
        }
        $WHERE = '';
        switch ($option) {
            case 'byPSP':
                $WHERE = " WHERE t1.status='PUBLISHED' ";
                break;
        }
        $query = <<<EOF
		SELECT t1.*,
			(SELECT CONCAT(lastname,', ',firstname) FROM people
				WHERE people.id=t1.tld_contact
			) AS contact,
			locations.location AS entity,
			locations.company_name AS entity_fullname
		FROM sqr AS t1
			LEFT JOIN locations	ON locations.id=t1.tld_entity
		$WHERE
		ORDER BY t1.id DESC LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * returns array of SQR rows by field
     *
     * @param array $constraints (fields and value)
     * @param string $opts
     * @param string $orderBy (id by default)
     *
     * @return array
     */
    public static function byConstraints($constraints, $opts = '', $orderBy = 't1.id')
    {
        switch ($opts) {
            case 'valid':
                if (!empty($constraints)) {
                    $where = tldUtils::constructWhere($constraints) . ' AND DATEDIFF(t1.td_validity,CURDATE()) >= 0';
                } else {
                    $where = ' DATEDIFF(t1.td_validity,CURDATE()) >= 0';
                }
                break;
            case 'out':
                if (!empty($constraints)) {
                    $where = tldUtils::constructWhere($constraints) . ' AND DATEDIFF(t1.td_validity,CURDATE()) < 0';
                } else {
                    $where = ' DATEDIFF(t1.td_validity,CURDATE()) < 0';
                }
                break;
            default:
                $where = tldUtils::constructWhere($constraints);
                break;
        }
        $orderBy = TldDatabase::escape($orderBy);
        $query = <<<EOF
            SELECT t1.*,
				(SELECT SUM(t2.dimk) FROM sqr_lines AS t2
					WHERE t2.parent_id=t1.id
				) AS tw,
				(SELECT ROUND(tw*0.4536,2)) AS tw_lbs,
				(SELECT CONCAT(lastname,', ',firstname) FROM people
					WHERE people.id=t1.tld_contact
				) AS contact,
				locations.location AS entity,
				locations.company_name AS entity_fullname
			FROM sqr AS t1
				LEFT JOIN locations	ON locations.id=t1.tld_entity
EOF;
        if ($where) {
            $query .= " HAVING $where";
        }
        $query .= " ORDER BY $orderBy";

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search SQR by field
     *
     * @param array $target
     *
     * @param null $option
     *
     * @return array
     */
    public static function search($target, $option = null)
    {
        $WHERE = '';
        switch ($option) {
            case 'byPSP':
                $WHERE = " WHERE t1.status='PUBLISHED' ";
                break;
            default:
                $WHERE = tldUtils::constructWhere($target);
                break;
        }
        $query = <<<EOF
			SELECT t1 . * , t2.er_model, t2.id AS sqrlid,
			CONCAT(people.lastname,', ',people.firstname) AS contact,
			locations.location AS entity,
			locations.company_name AS entity_fullname
			FROM sqr AS t1
			LEFT JOIN people ON people.id=t1.tld_contact
			LEFT JOIN locations ON locations.id = t1.tld_entity
			LEFT JOIN sqr_lines AS t2 ON t2.parent_id=t1.id
EOF;
        if ($WHERE) {
            $query .= " HAVING $WHERE";
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get count of SQRs by TLD entity and status     *
     *
     * @return array
     */
    public function countByEntityStatus()
    {
        $query = <<<EOF
			SELECT
				status,
				(SELECT location FROM locations	WHERE locations.id=sqr.tld_entity) AS entity,
				count(*) as num
			FROM sqr
			GROUP BY status, entity
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get rows of SQR by entity and status
     *
     * @param        $entity
     * @param string $status
     *
     * @return array
     */
    public function byEntityStatus($entity, $status)
    {
        if (empty($entity) || empty($status)) {
            return;
        }
        $a = [];
        if ($entity !== 'ALL') {
            $a['entity'] = $entity;
        }
        if ($status !== 'ALL') {
            $a['t1.status'] = $status;
        }

        return self::byConstraints($a);
    }

    /**
     * Get SQR print version for notification
     *
     * @return string SQR and SQRL reports
     */
    public function getPrintVersion()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            [
                'id' => 'SQR#',
                'dt_open' => 'Open date',
                'status' => 'Status',
                'contact' => 'TLD shipping contact',
                'entity_fullname' => 'TLD entity',
                'inco' => 'Inco Terms',
                'inco_loc' => 'Inco locations',
                'note' => 'Note',
                'dt_validity' => 'Request validity',
                'dt_deadline' => 'Deadline date',
            ],
            ['title' => 'General']
        );
        $body = $report->fetch();
        // get lines
        $report = new tldReportColumnar(
            $this->getLines(), [
                'xItems' => [
                    'id' => 'SQRL#',
                    'is_er' => 'ER?',
                    'factory_fullname' => 'TLD factory',
                    'dt_pu' => 'Pick up date estimated',
                    'loc_pu' => 'Other pick up location',
                    'container' => 'Containerization mode',
                    'roll_er' => 'Rolling ER?',
                    'er_type' => 'Equipment Type',
                    'er_model' => 'Model',
                    'er_qty' => 'Quantity',
                ],
                'title' => 'SQR Lines',
                'sortable' => 'no',
            ]
        );
        $body .= $report->fetch();

        return $body;
    }
}

class tldSQRL
{

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Check if empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if a SQE have been chosen for this line
     *
     * @return boolean
     */
    public function isSQEChosen()
    {
        return count(tldSQE::byConstraints(['parent_id' => $this->itsID, 'chosen' => 'Y'])) > 0;
    }

    /**
     * Get linked files to this SQE
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SQRL');
    }

    /**
     * Get SQR Line header
     *
     * @return mixed array or string if error
     */
    public function getHeader()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
			SELECT t1.*,
				IF(
					t1.factory_id=0,
                	'Other',
                	locations.location
             	 ) AS factory,
				IF(
					t1.factory_id=0,
                	'Other',
                	locations.company_name
                ) AS factory_fullname,
				sqr.inco_loc
			FROM sqr_lines AS t1
				LEFT JOIN locations	ON locations.id=t1.factory_id
				LEFT JOIN sqr ON sqr.id=t1.parent_id
			WHERE t1.id=$this->itsID LIMIT 1
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get linked log entries
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SQRL');
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SQRL';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('SQRL', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'SQRL', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'SQRL', $module);
    }

    /**
     * Add a new SQR Line
     *
     * @param array $p
     *
     * @return mixed string error message
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'parent_id',
            'factory_id',
            'container',
            'noncontainer',
            'customs_code',
            'roll_er',
            'category',
            'note',
            'dt_pu',
            'inland_er',
            'loc_pu',
            'er_type',
            'er_model',
            'er_qty',
            'dimgc',
            'diml',
            'dimw',
            'dimh',
            'dimk',
            'transhipment',
        ];
        $p['dt_pu'] = implode('-', $p['dt_pu']);
        if (trim($p['er_qty']) == '') {
            $fields = [
                'parent_id',
                'factory_id',
                'container',
                'noncontainer',
                'customs_code',
                'roll_er',
                'category',
                'note',
                'dt_pu',
                'inland_er',
                'loc_pu',
                'er_type',
                'er_model',
                'dimgc',
                'diml',
                'dimw',
                'dimh',
                'dimk',
                'transhipment',
            ];
        }
        $query = <<<EOF
        	INSERT INTO sqr_lines SET
EOF;
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    public function update($p)
    {
        if (empty($p) || empty($this->itsID)) {
            return;
        }
        $fields = [
            'factory_id',
            'container',
            'noncontainer',
            'customs_code',
            'roll_er',
            'category',
            'note',
            'inland_er',
            'dt_pu',
            'loc_pu',
            'er_type',
            'er_model',
            'er_qty',
            'dimgc',
            'diml',
            'dimw',
            'dimh',
            'dimk',
            'transhipment',
        ];
        $p['dt_pu'] = implode('-', $p['dt_pu']);
        if (trim($p['er_qty']) == '') {
            $query = "UPDATE sqr_lines SET
			factory_id = '" . $p['factory_id'] . "',
			container = '" . $p['container'] . "',
			noncontainer = '" . $p['noncontainer'] . "',
			transhipment='" . $p['transhipment'] . "',
			customs_code = '" . $p['customs_code'] . "',
			roll_er = '" . $p['roll_er'] . "',
			category = '" . $p['category'] . "',
			note = '" . $p['note'] . "',
			dt_pu = '" . $p['dt_pu'] . "',
			inland_er = '" . $p['inland_er'] . "',
			loc_pu = '" . $p['loc_pu'] . "',
			er_type = '" . $p['er_type'] . "',
			er_qty = NULL,
			er_model = '" . $p['er_model'] . "',
			dimgc = '" . $p['dimgc'] . "',
			diml = '" . $p['dimgl'] . "',
			dimw = '" . $p['dimgw'] . "',
			dimh = '" . $p['dimgh'] . "',
			dimk = '" . $p['dimgk'] . "' WHERE id=" . $this->itsID;
        } else {
            $query = 'UPDATE sqr_lines SET ';
            $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);
            $query .= ' WHERE id=' . $this->itsID;
        }

        return tldUtils::sqlQuery($query);
    }

    /**
     * Duplicate SQR (not static method)
     *
     * @return array
     */
    public function duplicate()
    {
        if (empty($this->itsID)) {
            return;
        }
        // Duplicate header
        $query = <<<EOF
			INSERT INTO sqr_lines (parent_id,factory_id,container,noncontainer,customs_code,roll_er,note,
        		dt_pu,loc_pu,er_type,er_model,er_qty,dimgc,diml,dimw,dimh,dimk,transhipment)
			SELECT parent_id,factory_id,container,noncontainer,customs_code,roll_er,note,
        		dt_pu,loc_pu,er_type,er_model,er_qty,dimgc,diml,dimw,dimh,dimk,transhipment
			FROM sqr_lines
			WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlInsert($query);
    }

    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = "DELETE FROM sqr_lines WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlQuery($query);
    }

    /**
     * Search SQR Line by field
     *
     * @param array $target
     *
     * @return array
     */
    public static function search($target)
    {
        $query = <<<EOF
			SELECT t1.*, t2.status, t2.inco, t2.inco_loc,
				IF(
				t1.factory_id=0,
                'Other',
                locations.location
                ) AS factory,
				locations.company_name AS factory_fullname
			FROM sqr_lines AS t1
				LEFT JOIN locations	ON locations.id=t1.factory_id
				LEFT JOIN sqr AS t2 ON t1.parent_id=t2.id
EOF;
        $where = tldUtils::constructWhere($target);
        if ($where) {
            $query .= " HAVING $where";
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Search SQR Line by latest
     *
     * @param int $nb
     *
     * @return array
     *
     */
    public static function byLatest($nb = 10)
    {
        $query = <<<EOF
    		SELECT t1.*,
				locations.location AS factory,
				locations.company_name AS factory_fullname,
				inco_loc,
				inco
			FROM sqr_lines AS t1
				LEFT JOIN locations	ON locations.id=t1.factory_id
				LEFT JOIN sqr ON sqr.id=t1.parent_id
			ORDER BY t1.id
			LIMIT $nb
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get SQRL by parent ID (SQR id)
     *
     * @param int $pid
     *
     * @return rows
     */
    public static function byParent($pid)
    {
        if (!is_numeric($pid)) {
            return;
        }
        $a = ['t1.parent_id' => $pid];

        return self::byConstraints($a);
    }

    /**
     * Get SQRL by parent and vendor ID
     *
     * @param int $pid
     * @param int $vendorID
     */
    public function byParentVendorID($pid, $vendorID)
    {
        if (!is_numeric($pid) || !is_numeric($vendorID)) {
            return;
        }
        $a = [
            't1.parent_id' => $pid,
            'vendor_id' => $vendorID,
        ];

        return self::byConstraints($a);
    }

    /**
     * Search SQR Line by field
     *
     * @param $constraints
     *
     * @return array
     *
     */
    public static function byConstraints($constraints)
    {
        $WHERE = 'WHERE ' . tldUtils::constructWhere($constraints);
        $query = <<<EOF
    		SELECT t1.*,
				IF(
					t1.factory_id=0,
                	'Other',
                	locations.location
              	) AS factory,
    			IF(
					t1.factory_id=0,
                	'Other',
                	locations.company_name
                ) AS factory_fullname
			FROM sqr_lines AS t1
				LEFT JOIN locations	ON locations.id=t1.factory_id
			$WHERE
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

}

/**
 * Class for accessing and manipulating SQE data (Shipping Quotation Entry)
 *
 * @package SalesAndService
 */
class tldSQE
{
    /* Constructor */
    public function __construct($id)
    {
        $this->itsID = $id;
        $cur = $this->getDefaultCurrency($id);
        $this->itsHeader = $this->getHeader($cur[0]);
    }

    /**
     * Check if SQE empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Check if SQE can be editable
     *
     * @return boolean
     */
    public function isEditable()
    {
        if (empty($this->itsID)) {
            return;
        }

        return !in_array($this->itsHeader['status'], ['TLD_APPROVAL', 'CORRECT']);
    }

    /**
     * Check if SQE out of date
     *
     * @return boolean
     */
    public function isOutOfDate()
    {
        if (empty($this->itsID)) {
            return;
        }
        $date = explode('-', $this->itsHeader['validity']);

        return mktime(0, 0, 0, $date[1], $date[2], $date[0]) < mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    }

    /**
     * Check if SQE below vendor
     *
     * @param $uid
     *
     * @return bool
     */
    public function isShipperOwner($uid)
    {
        return $this->itsHeader['vendor_id'] == $uid;
    }

    /**
     * Get the database row for this SQE
     *
     * @param string $cur
     *
     * @return array
     */
    public function getHeader($cur = 'USD')
    {
        if (!$this->itsID) {
            return [];
        }
        $query = <<<EOF
			SELECT t1.*,
				(SELECT userid FROM vendors WHERE vendors.id=t1.vendor_id)
					AS vendor,
				(SELECT	ROUND(SUM(t2.price*(
					(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
					(IF(from_rate.rate IS NULL, 1, from_rate.rate))
				)),2)) AS tt_price
			FROM sqe AS t1
			LEFT JOIN sqe_lines AS t2 ON t1.id = t2.parent_id
			LEFT JOIN erp_forex2 AS to_rate
		 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND to_rate.nam_cur LIKE '$cur' AND to_rate.typ LIKE 'END'
			LEFT JOIN erp_forex2 AS from_rate
		 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'
			WHERE t1.id=$this->itsID
			GROUP BY t1.id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Duplicate SQE (not static method)
     *
     * @return array
     */
    public function duplicate()
    {
        if (empty($this->itsID)) {
            return;
        }
        // Duplicate header
        $query = <<<EOF
			INSERT INTO sqe (parent_id,dt_open,vendor_id,container,noncontainer,ca_name,ttd,eta,port_load,port_dest,qcur,dt_validity,note,chosen,status)
			SELECT parent_id,NOW(),vendor_id,container,noncontainer,ca_name,ttd,eta,port_load,port_dest,qcur,dt_validity,note,'N','PENDING'
			FROM sqe
			WHERE id=$this->itsID LIMIT 1
EOF;
        $newid = tldUtils::sqlInsert($query);
        if (!is_numeric($newid)) {
            return "ERROR: could not duplicate SQE header, $newid";
        }
        // Duplicate the SQE Lines
        $rows = $this->getLines();
        if (count($rows)) {
            foreach ($rows as $row) {
                $sqel = new tldSQEL($row['id']);
                $sqel->duplicate($newid);
            }
        }

        return $newid;
    }

    /**
     * Delete SQE (not static method)
     *
     * @return array
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $rows = $this->getLines();
        if (count($rows)) {
            foreach ($rows as $row) {
                $sqel = new tldSQEL($row['id']);
                $sqel->delete();
            }
        }
        $query = <<<EOF
			DELETE FROM sqe WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Create a new SQE row in the database
     *
     * @param array $p ,
     *
     * @return boolean
     */
    public static function insert($p)
    {
        $fields = [
            'parent_id',
            'vendor_id',
            'container',
            'transhipment',
            'noncontainer',
            'ca_name',
            'ttd',
            'eta',
            'port_load',
            'port_dest',
            'qcur',
            'dt_validity',
            'note',
            'preadviseday',
        ];
        $query = <<<EOF
            INSERT INTO sqe
        SET dt_open=NOW(),chosen='N',status='PENDING',
EOF;
        $p['eta'] = implode('-', $p['eta']);
        $p['dt_validity'] = implode('-', $p['dt_validity']);
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update SQE header
     *
     * @param array $p
     *
     * @return id update or string error message
     */
    public function update($p)
    {
        if (empty($this->itsID)) {
            return;
        }
        if (empty($p)) {
            return;
        }
        $p['eta'] = implode('-', $p['eta']);
        $p['dt_validity'] = implode('-', $p['dt_validity']);
        $fields = [
            'parent_id',
            'vendor_id',
            'container',
            'noncontainer',
            'transhipment',
            'ca_name',
            'ttd',
            'eta',
            'port_load',
            'port_dest',
            'qcur',
            'dt_validity',
            'note',
            'chosen',
            'preadviseday',
        ];
        $query = 'UPDATE sqe SET ';
        $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);
        $query .= <<<EOF
	        WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get SQE status
     *
     * @return string
     */
    public function getStatus()
    {
        if (empty($this->itsID)) {
            return;
        }

        return $this->itsHeader['status'];
    }

    /**
     * Get SQR allowed status
     *
     * @return string
     */
    public function getAllowedStatus()
    {
        if (empty($this->itsID)) {
            return;
        }
        switch ($this->getStatus()) {
            case 'PENDING':
                $a['fwd'] = 'VALID';
                break;
            case 'VALID':
                $a['fwd'] = 'TLD_APPROVAL';
                $a['back'] = 'PENDING';
                break;
            case 'TLD_APPROVAL':
                $a['fwd'] = 'CORRECT';
                $a['back'] = 'VALID';
                break;
        }

        return $a;
    }

    /**
     * Update SQR status
     *
     * @param        $status
     * @param string $offre
     *
     * @return string
     */
    public function changeStatus($status, $offre = 'N')
    {
        if (empty($this->itsID)) {
            return;
        }
        if (!in_array($status, ['PENDING', 'TLD_APPROVAL', 'VALID', 'CORRECT'])) {
            return 'Status invalid';
        }
        if ($status === 'VALID') {
            $query = "UPDATE sqe SET status='$status', chosen='$offre' WHERE id=" . $this->itsID;
        } else {
            $query = "UPDATE sqe SET status='$status' WHERE id=" . $this->itsID;
        }

        return tldUtils::sqlQuery($query);
    }

    public function changeUsed($used = false)
    {
        if (empty($this->itsID)) {
            return;
        }
        $value = (int)$used;
        $query = "UPDATE sqe SET used=$value WHERE id=" . $this->itsID;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Get contact info of the SQR
     *
     * @return array
     */
    public function getTLDContact()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
    		SELECT t3.*
			FROM sqr AS t1
				LEFT JOIN sqr_lines AS t2 ON t2.parent_id=t1.id
				LEFT JOIN people AS t3 ON t3.id=t1.tld_contact
			WHERE t2.id={$this->itsHeader['parent_id']}
			GROUP BY t1.id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Mark SQE has chosen offer or not
     *
     */
    public function mark($chosen)
    {
        if (empty($this->itsID) || empty($chosen)) {
            return;
        }
        $query = "UPDATE sqe SET chosen='$chosen' WHERE id=" . $this->itsID . ' LIMIT 1';

        return tldUtils::sqlQuery($query);
    }

    /**
     * Add a new SQE Line
     *
     * @param array $p
     *
     * @return mixed id of newly insered SQRL or string error message
     */
    public function addLine($p)
    {
        $p['parent_id'] = $this->itsID;

        return tldSQEL::insert($p);
    }

    /**
     * Get linked files to this SQE
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'SQE');
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'SQE', 'ALL');
    }

    /**
     * Get linked log entries
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'SQE');
    }

    /**
     * Add a comment to the log
     *
     * @param $id
     * @param $comment
     *
     * @return bool
     *
     */
    public function addLogEntry($id, $comment)
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'SQE';
        $a['poster'] = $id;
        $a['comment'] = $comment;

        return tldModLog::insert($a);
    }

    /**
     * Get all linked SQR Lines
     *
     * @return array
     */
    public function getLines()
    {
        return tldSQEL::byParent($this->itsID);
    }

    /**
     * Get the Total amount of lines
     *
     * @param string $CUR
     *
     * @return array
     */
    public function getTotal($CUR = 'USD')
    {
        $CUR = TldDatabase::escape($CUR);
        $query = <<<EOF
			SELECT '$CUR' as currency,
				(SELECT	ROUND(SUM(t2.price*(
					(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
					(IF(from_rate.rate IS NULL, 1, from_rate.rate))
				)),2)) AS tt_price
			FROM sqe AS t1
			LEFT JOIN sqe_lines AS t2 ON t1.id = t2.parent_id
			LEFT JOIN erp_forex2 AS to_rate
		 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND to_rate.nam_cur LIKE '$CUR' AND to_rate.typ LIKE 'END'
			LEFT JOIN erp_forex2 AS from_rate
		 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'
			WHERE t1.id=$this->itsID
			GROUP BY t1.id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get rate
     */
    public function getRate()
    {
        $query = <<<EOF
			SELECT CONCAT(nam_cur,'=',ROUND( rate, 2 )) AS rate
			FROM erp_forex2 AS to_rate
			LEFT JOIN sqe AS t1
				ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND to_rate.typ LIKE 'END'
		 	WHERE
		 		t1.id=$this->itsID
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get the latest SQEs, defaults to last 10
     *
     * @param integer $qty
     *
     * @return array
     */
    public static function byLatest($qty = 10)
    {
        if (!is_int($qty)) {
            $qty = 10;
        }
        $query = <<<EOF
		SELECT t1.*, t3.er_type, t3.er_model, t3.er_qty, t4.inco, t4.inco_loc,
			(SELECT company FROM vendors WHERE vendors.id=t1.vendor_id)
					AS vendor,
			(SELECT	ROUND(SUM(t2.price*(
				(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
				(IF(from_rate.rate IS NULL, 1, from_rate.rate))
			)),2)) AS tt_price
		FROM sqe AS t1
		LEFT JOIN sqe_lines AS t2 ON t1.id = t2.parent_id
		LEFT JOIN sqr_lines AS t3 ON t3.id=t1.parent_id
		LEFT JOIN sqr AS t4 ON t3.parent_id=t4.id
		LEFT JOIN erp_forex2 AS to_rate
	 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
			AND to_rate.nam_cur LIKE 'USD' AND to_rate.typ LIKE 'END'
		LEFT JOIN erp_forex2 AS from_rate
	 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
			AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'
		GROUP BY t1.id DESC LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get the latest SQEs for a shipper
     *
     * @param         $userID
     * @param integer $qty
     *
     * @return array
     */
    public static function byShipperLatest($userID, $qty = 10)
    {
        if (empty($userID) || !is_numeric($userID)) {
            return;
        }
        if (!is_numeric($qty)) {
            $qty = 10;
        }
        $query = <<<EOF
		SELECT t1.*,
			(SELECT userid FROM vendors WHERE vendors.id=t1.vendor_id)
					AS vendor,
			(SELECT	ROUND(SUM(t2.price*(
				(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
				(IF(from_rate.rate IS NULL, 1, from_rate.rate))
			)),2)) AS tt_price
		FROM sqe AS t1
		LEFT JOIN sqe_lines AS t2 ON t1.id = t2.parent_id
		LEFT JOIN erp_forex2 AS to_rate
	 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
			AND to_rate.nam_cur LIKE 'USD' AND to_rate.typ LIKE 'END'
		LEFT JOIN erp_forex2 AS from_rate
	 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
			AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'
		WHERE t1.vendor_id=$userID
		GROUP BY t1.id DESC LIMIT $qty
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * returns array of SQE rows by shipper
     *
     * @param int vendor id
     * @param int SQR id
     *
     * @return array
     */
    public static function byShipper($userID)
    {
        if (empty($userID)) {
            return;
        }
        $a['vendor_id'] = $userID;

        return self::byConstraints($a);
    }

    /**
     * Returns array of old SQE by vendor ID
     *
     * @param $vendorID
     *
     * @return array|void
     */
    public function byOldSQEVendorID($vendorID)
    {
        if (!is_numeric($vendorID)) {
            return;
        }
        $a = " t1.vendor_id=$vendorID AND DATEDIFF(t1.dt_validity,CURDATE()) < 0 ";

        return self::byConstraints($a);
    }

    /**
     * Search SQE by field
     *
     * @param array $target
     * @param array|string $where
     *
     * @return array
     */
    public static function search($target)
    {
        $query = <<<EOF
			SELECT t1.*, t2.price_cur, t3.er_type, t3.er_model, t3.er_qty, t4.inco, t4.inco_loc,
				(SELECT company FROM vendors WHERE vendors.id=t1.vendor_id) AS vendor,
				(SELECT	ROUND(SUM(t2.price*(
					(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
					(IF(from_rate.rate IS NULL, 1, from_rate.rate))
				)),2)) AS tt_price
			FROM sqe AS t1
			LEFT JOIN sqe_lines AS t2 ON t1.id = t2.parent_id
			LEFT JOIN sqr_lines AS t3 ON t3.id=t1.parent_id
			LEFT JOIN sqr AS t4 ON t3.parent_id=t4.id
			LEFT JOIN erp_forex2 AS to_rate
		 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND to_rate.nam_cur LIKE 'USD' AND to_rate.typ LIKE 'END'
			LEFT JOIN erp_forex2 AS from_rate
		 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(t1.dt_open)*12+MONTH(t1.dt_open)-1
				AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'

			GROUP BY t1.id
EOF;

        if ($where = tldUtils::constructWhere($target)) {
            $query .= " HAVING $where";
        }

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * returns array of SQE rows by field
     *
     * @param string   or array $constraints (fields and value)
     * @param string $orderBy (id by default)
     *
     * @return array
     */
    public static function byConstraints($constraints, $orderBy = 'sqe.id')
    {
        $orderBy = TldDatabase::escape($orderBy);
        $query = <<<EOF
            SELECT sqe.*,
            	factory.location AS factory,
            	sqr.inco_loc,
				(SELECT userid FROM vendors WHERE vendors.id=sqe.vendor_id)
					AS vendor,
				(SELECT	ROUND(SUM(t2.price*(
					(IF(to_rate.rate IS NULL, 1, to_rate.rate))/
					(IF(from_rate.rate IS NULL, 1, from_rate.rate))
				)),2)) AS tt_price
			FROM sqe
				LEFT JOIN sqe_lines AS t2 ON sqe.id = t2.parent_id
				LEFT JOIN erp_forex2 AS to_rate
			 		ON to_rate.nam_year*12+to_rate.nam_month=YEAR(sqe.dt_open)*12+MONTH(sqe.dt_open)-1
					AND to_rate.nam_cur LIKE 'USD' AND to_rate.typ LIKE 'END'
				LEFT JOIN erp_forex2 AS from_rate
			 		ON from_rate.nam_year*12+from_rate.nam_month=YEAR(sqe.dt_open)*12+MONTH(sqe.dt_open)-1
					AND from_rate.nam_cur=t2.price_cur AND from_rate.typ LIKE 'END'
				LEFT JOIN sqr_lines AS sqrl ON sqe.parent_id=sqrl.id
				LEFT JOIN locations AS factory ON factory.id=sqrl.factory_id
				LEFT JOIN sqr ON sqr.id=sqrl.parent_id
EOF;
        if ($WHERE = is_array($constraints) ? tldUtils::constructWhere($constraints) : $constraints) {
            $query .= " GROUP BY sqe.id HAVING $WHERE";
        }
        $query .= " ORDER BY $orderBy";

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get default quotation currency of the SQE
     *
     * @param $id
     *
     * @return string
     */
    public function getDefaultCurrency($id)
    {
        if (empty($id)) {
            return 'ERROR: No id set';
        }
        $query = <<<EOF
			SELECT qcur
			FROM sqe
			WHERE id=$id
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get the Print version of the SQE
     *
     * @return string
     */
    public function getPrintVersion()
    {
        if (empty($this->itsID)) {
            return;
        }
        $report = new tldAssocTable(
            $this->itsHeader,
            [
                'id' => 'SQE#',
                'parent_id' => 'SQRL#',
                'dt_open' => 'Date opened',
                'vendor' => 'eVendor user',
                'status' => 'Status',
                'container' => 'Containerization mode',
                'noncontainer' => 'Modality',
                'ca_name' => 'Carrier Name',
                'ttd' => 'Transit Time in Days',
                'eta' => 'Estimated Date of Arrival',
                'port_load' => 'Port loading',
                'port_dest' => 'Port destination',
                'dt_validity' => 'Quote Validity',
                'tt_price' => 'Total Price (in USD)',
                'chosen' => 'Chosen offer?',
            ],
            ['title' => 'General']
        );
        return $report->fetch();
    }

}

/**
 * Class for accessing and manipulating SQE line data
 *
 * @package SalesAndService
 */
class tldSQEL
{
    /* Constructor */
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Check if SQE Line empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get the database row for this SQE Line
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
			SELECT t1.*
			FROM sqe_lines AS t1
			WHERE id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Create a new SQE line row in the database
     *
     * @param array $p
     *
     * @return boolean
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = ['parent_id', 'desca', 'price_cur', 'price'];
        $query = 'INSERT INTO sqe_lines SET ';
        $query .= tldUtils::getSqlSet($p, $fields);

        return tldUtils::sqlInsert($query);
    }

    /**
     * Update SQE line header
     *
     * @param array $p
     *
     * @return id update or string error message
     */
    public function update($p)
    {
        if (empty($this->itsID)) {
            return;
        }
        if (empty($p)) {
            return;
        }
        $fields = ['parent_id', 'desca', 'price_cur', 'price'];
        $query = 'UPDATE sqe_lines SET ';
        $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);
        $query .= <<<EOF
	        WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * Duplicate SQE Line (not static method)
     *
     * @param $newid
     *
     * @return array
     */
    public function duplicate($newid)
    {
        if (empty($this->itsID) || empty($newid)) {
            return;
        }
        // Duplicate header
        $query = <<<EOF
			INSERT INTO sqe_lines (parent_id,desca,price_cur,price)
			SELECT $newid,desca,price_cur,price
			FROM sqe_lines
			WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlInsert($query);
    }

    /**
     * Delete SQE Line (not static method)
     *
     * @return array
     */
    public function delete()
    {
        if (empty($this->itsID)) {
            return;
        }
        $query = <<<EOF
			DELETE FROM sqe_lines WHERE id=$this->itsID LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    /**
     * returns array of SQEL rows by field
     *
     * @param array $constraints (fields and value)
     * @param string $orderBy (id by default)
     *
     * @return array
     */
    public static function byConstraints($constraints, $orderBy = 't1.id')
    {
        $where = tldUtils::constructWhere($constraints);
        $orderBy = TldDatabase::escape($orderBy);
        $query = '
			SELECT t1.*
			FROM sqe_lines AS t1';
        if ($where) {
            $query .= " WHERE $where";
        }
        $query .= " ORDER BY $orderBy";

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * returns array of SQRL rows by parent_id
     *
     * @param array $pid
     *
     * @return array
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }
}

/**
 * TLD On Call
 *
 * @package SalesAndService
 */
class tldTOC
{

    public $itsID;
    public $itsHeader;
    public $itsContact;
    // Diligence Factor
    public const DF = 1.257274;
    // toc file path
    public const FILE_DIR = 'toc';

    public function __construct($id, $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
        $this->itsContact = $lazy ? [] : new extranetUser($this->getCONID());
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getERID()
    {
        return $this->itsHeader['erid'];
    }

    public function isEquipmentSet()
    {
        return $this->getERID() <> 0;
    }

    public function getERSN()
    {
        return $this->itsHeader['sn'];
    }

    public function getERType()
    {
        return $this->itsHeader['type'];
    }

    public function getERModel()
    {
        return $this->itsHeader['model'];
    }

    public function getFactorySupportFlag()
    {
        return $this->itsHeader['factory_support_flag'];
    }

    public function getCUID()
    {
        return $this->itsHeader['cuid'];
    }

    public function getCustomerName()
    {
        return $this->itsHeader['customer_name'];
    }

    public function getCONID()
    {
        return $this->itsHeader['conid'];
    }

    public function getContactEmail()
    {
        return $this->itsHeader['con_email'];
    }

    public function getSSOID()
    {
        return $this->itsHeader['ssoid'];
    }

    public function getSSOERP()
    {
        return $this->itsHeader['ssoerp'];
    }

    public function getFactoryERP()
    {
        return $this->itsHeader['factory_erp'];
    }

    public static function getIFList()
    {
        return ['1' => '1', '10' => '10', '100' => '100', '1000' => '1000'];
    }

    public function getAssigneeID()
    {
        return $this->itsHeader['assid'];
    }

    public function getTechnicianID()
    {
        return $this->itsHeader['tecid'];
    }

    public function getActivityType()
    {
        return $this->itsHeader['activity_type'];
    }

    public function getType()
    {
        return $this->itsHeader['toc_type'];
    }

    public static function getActivityTypeList()
    {
        return tldList::optionsByListNameAsListItemListItem('list.toc.service.activity.type');
    }

    public static function getTypeList()
    {
        return tldList::optionsByListNameAsListItemListItem('list.service.type');
    }

    public function getUnitOperationStatus()
    {
        return $this->itsHeader['unit_operation_status'];
    }

    public function getPosterID()
    {
        return $this->itsHeader['postid'];
    }

    public function getPosterEmail()
    {
        return $this->itsHeader['poster_email'];
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function isIbs()
    {
        return (bool) $this->itsHeader['is_ibs'];
    }

    public function isLink()
    {
        return (bool) $this->itsHeader['is_link'];
    }

    public function isIhs()
    {
        return (bool) $this->itsHeader['is_ihs'];
    }

    public function getWarrantyId()
    {
        return $this->itsHeader['warranty_id'];
    }

    public function setWarrantyId(int $warrantyId)
    {
        throw new \Exception('This is no longer used');
    }


    /**
     * Get list of operation status of the ER
     */
    public static function getUnitOperationStatusList(): array
    {
        return [
            'MCF' => 'Mission Capable Fully (MCF)',
            'MCP' => 'Mission Capable Partially (MCP)',
            'NMC' => 'Non Mission Capable (NMC)',
        ];
    }

    public static function getLinkableModules(): array
    {
        return [
            'SPR',
            'SPQ2',
            'PDC',
            'SPQ',
            'TOC',
            'GWF',
        ];
    }

    /**
     * Set ER hour meter
     *
     * @param $hours int
     *
     * @return string on error
     */
    public function setERHourMeter($hours)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT $FROM WHERE toc.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Check is the toc should notify or not
     *
     * @return boolean
     */
    public function isNotificationEnable()
    {
        return ($this->itsHeader['notification'] === 'Y') ? true : false;
    }

    /**
     * Check if customer contact has the role to receive notifications
     *
     * @return boolean
     */
    public function isCustomerContactCanReceiveNotification()
    {
        return $this->itsContact->isInSSOCustomerRole(
            $this->getSSOID(),
            $this->getCUID(),
            'fl_NOT_TOC'
        );
    }

    /**
     * Insert a new TOC
     *
     * @param array $p key value pairs
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public static function insert($p)
    {
        throw new \Exception('This is no longer used');
    }

    public static function create($vars, $file = null)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Generic TOC update method
     *
     * @param              $data   array of toc datas
     * @param array|string $fields array of toc fields to update
     *
     * @return string on error
     */
    public function update($data, $fields = '')
    {
        throw new \Exception('This is no longer used');
    }

    public function setFactorySupportFlag($data, $uid)
    {
        throw new \Exception('This is no longer used');
    }

    public function setERID($erid)
    {
        throw new \Exception('This is no longer used');
    }

    public function setFurtherAction($module, $ref)
    {
        throw new \Exception('This is no longer used');
    }

    public static function getStatusList(): array
    {
        return ['IN PROGRESS', 'SUSPENDED', 'SOLVED', 'CLOSED'];
    }

    public static function getOpenStatusList(): array
    {
        return ['IN PROGRESS', 'SUSPENDED'];
    }

    public static function getClosedStatusList(): array
    {
        return ['SOLVED', 'CLOSED'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getIF()
    {
        return $this->itsHeader['ifactor'];
    }

    public function getWF()
    {
        return $this->itsHeader['wfactor'];
    }

    public static function getSSOList()
    {
        $query = 'SELECT DISTINCT(ssoid) AS id FROM toc';
        $ssoListID = array_column(tldUtils::getSqlToAssocArray($query), 'id', 'id');
        $a = 'id IN(' . implode(',', $ssoListID) . ')';

        return array_column(tldLocation::byConstraints($a), 'location', 'id');
    }

    public static function getDefaultAssignee($ssoid, $cuid, $ifactor)
    {
        $assid = 0;
        $sso = new tldLocation($ssoid);
        switch ($ifactor) {
            case 1:
            case 10:
                // try to get service rep from CRT
                $customer = new tldCustomer($cuid);
                $reps = $customer->getRepListFromCRTByTypeBySSO('services', $sso->getID());
                $assid = $reps[0]['id'];
                if (!empty($assid)) {
                    break;
                }
                // Else default to CSM
                $gcsm = new tldGroup('role_CSM', $sso->getERP());
                $csm = current($gcsm->getUserlist());
                $assid = $csm['id'];
                break;
            case 100:
                $gcsm = new tldGroup('role_CSM', $sso->getERP());
                $csm = current($gcsm->getUserlist());
                $assid = $csm['id'];
                break;
            case 1000:
                $gevp = new tldGroup('role_EVP', $sso->getERP());
                $evp = current($gevp->getUserlist());
                $assid = $evp['id'];
                break;
        }

        return $assid;
    }

    public function getStatusAllowed()
    {
        $user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
        switch ($this->getStatus()) {
            case 'OPEN':
                return ['IN PROGRESS'];
                break;
            case 'IN PROGRESS':
                $statusList = ['IN PROGRESS', 'SUSPENDED'];
                if ($this->isEquipmentSet() || $user->isInGroup(['ROLE_CSM', 'superuser'])) {
                    $statusList[] = 'SOLVED';
                }

                return $statusList;
                break;
            case 'SUSPENDED':
                $statusList = ['IN PROGRESS'];
                if ($this->isEquipmentSet() || $user->isInGroup(['ROLE_CSM', 'superuser'])) {
                    $statusList[] = 'SOLVED';
                }

                return $statusList;
                break;
            case 'SOLVED':
                return ['CLOSED', 'IN PROGRESS'];
                break;
        }

        return;
    }

    /**
     * Update TOC status
     *
     * @param string $status
     * @param boolean $force
     * @param boolean $notify
     * @param string $externalDescription
     *
     * return string on error
     *
     * @return string
     */
    public function changeStatus($status, $force = false, $notify = true, $externalDescription = '')
    {
        throw new \Exception('This is no longer used');
    }

    public function updateUnitOperationalStatus($status)
    {
        throw new \Exception('This is no longer used');
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getLogFactorySupport($num_log = 0)
    {
        $constraint = "mod_logs.parent_id=$this->itsID AND mod_logs.module LIKE 'TOC' AND mod_logs.comment LIKE 'Factory Support is Required%'";

        return tldModLog::byConstraints($constraint);
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @param int $num_log
     *
     * @return array
     */
    public function getLog($num_log = 0)
    {
        return tldModLog::byParent($this->itsID, 'TOC', $num_log);
    }

    /**
     * Get log for all associated sub process
     *
     * @return array
     */
    public function getFullLog()
    {
        $query = <<<EOF
SELECT
	mod_logs.*,
	UNIX_TIMESTAMP(mod_logs.date) AS sortingAlias,
	CONCAT(a.lastname,', ',a.firstname) as poster_fullname
FROM
    mod_logs
	LEFT JOIN people AS a ON mod_logs.poster=a.id
WHERE
	(mod_logs.parent_id=$this->itsID
	AND mod_logs.module LIKE 'TOC' AND log_num<>10)
EOF;
        // Add main process logs
        $links = $this->getLinksFromHere();

        foreach ($links as $link) {
            $query .= <<<EOF
OR (mod_logs.parent_id={$link['item']} AND mod_logs.module LIKE '{$link['type']}')
EOF;
        }
        $query .= ' ORDER BY id DESC';

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Notification log + TOC sub process log + TOC log
     *
     * @return array
     */
    public function getNotificationFullLog()
    {
        // Get main process constraints
        $WHERE = null;
        $links = $this->getLinksFromHere();
        if (!empty($links)) {
            foreach ($links as $link) {
                $WHERE .= <<<EOF
OR (mod_logs.parent_id={$link['item']} AND mod_logs.module LIKE '{$link['type']}')
EOF;
            }
        }
//         Construct query to merge all infos
        $query = <<<EOF
(
    SELECT
        mod_logs.id AS id,
        'log' AS logType,
        mod_logs.module AS module,
        mod_logs.comment AS comment,
        NULL AS recipients,
        NULL AS cc,
        NULL AS bcc,
        mod_logs.date AS dt,
        NULL AS filename,
        CONCAT(a.lastname,', ',a.firstname) AS poster_fullname,
        UNIX_TIMESTAMP(mod_logs.date) AS sortingAlias
    FROM
        mod_logs
        LEFT JOIN people AS a ON mod_logs.poster=a.id
    WHERE
        (mod_logs.parent_id=$this->itsID
        AND mod_logs.module LIKE 'TOC' AND log_num<>10)
        $WHERE
)
UNION ALL
(
    SELECT
        toc_not.id AS id,
        'not' AS logType,
        'TOC' AS module,
        toc_not.email AS comment,
        toc_not.recipients AS recipients,
        toc_not.cc AS cc,
        toc_not.bcc AS bcc,
        toc_not.dt AS dt,
        file.filename AS filename,
        CONCAT(people.firstname,' ',people.lastname) AS poster_fullname,
        UNIX_TIMESTAMP(toc_not.dt) AS sortingAlias
    FROM
        toc_not
        LEFT JOIN file ON file.id=toc_not.fid
        LEFT JOIN people ON people.id=toc_not.uid
    WHERE
        toc_not.parent_id=$this->itsID
)
ORDER BY sortingAlias DESC
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'TOC', null);
    }

    public function getMainFile()
    {
        $rows = tldModFile::byConstraints("mod_files.parent_id = $this->itsID AND module = 'TOC' AND (level = 1 OR level = 3)");

        return $rows[0];
    }

    public function addFile($a, $file_array)
    {
        throw new \Exception('This is no longer used');
    }

    public function getMembers()
    {
        return tldModMember::byParent($this->itsID, 'TOC');
    }

    public function getMembersEmail()
    {
        $emails = [];
        foreach ($this->getMembers() AS $member) {
            $user = new tldUser($member['id']);
            $email = $user->getEmail();
            if (empty($email) || in_array($email, $emails)) {
                continue;
            }
            $emails[] = $email;
        }

        return $emails;
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'TOC', 'ALL');
    }

    /**
     * Add a comment to the log
     *
     * @param      $id
     * @param      $comment
     * @param int $num_log
     * @param bool $notification
     * @param int $logId
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $num_log = 0, bool $notification = false, &$logId = null)
    {
        throw new \Exception('This is no longer used');
    }

    public function addLinkTo($type, $item)
    {
        throw new \Exception('This is no longer used');
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'TOC', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'TOC', $module);
    }

    public static function countBySSOStatusByASM($asm, $sso)
    {
        if (!empty($sso)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso), 'id', 'id');
            $SalesAgentGrp = new tldGroup('gg_SALES_AGENTS');
            $SalesAgentList = array_column($SalesAgentGrp->getUserlistBySSO($sso), 'id', 'id');
            $list = implode(',', $ASMList + $SalesAgentList);
            $WHERE = " crt.sales_rep_id IN ($list) ";
        } else {
            $WHERE = " crt.sales_rep_id=$asm ";
        }
        $query = <<<EOF
SELECT
    locations.location AS sso_fullname, status, count(DISTINCT(toc.id)) AS num
FROM toc
    LEFT JOIN customers_crt AS crt ON crt.customer_id = toc.cuid
    AND crt.erp_location_id = toc.ssoid
    LEFT JOIN customers ON customers.id=toc.cuid
    LEFT JOIN locations ON locations.id=toc.ssoid
WHERE $WHERE AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(dt,'%Y%m')) BETWEEN 0 AND 12
GROUP BY sso_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function countBySSOStatusNotClosed()
    {
        $query = <<<EOF
SELECT
    locations.location AS sso_fullname, status, count(*) AS num
FROM toc
    LEFT JOIN customers ON customers.id=toc.cuid
    LEFT JOIN locations ON locations.id=toc.ssoid
WHERE toc.status NOT IN ('CLOSED', 'SOLVED')
GROUP BY sso_fullname, status
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactorySupportRequired()
    {
        $query = <<<EOF
SELECT
    IF(toc.ssoid=0,
        'NO SSO',
        locations.location
    ) as sso_fullname,
    IF(ers.man_location IS NULL,
        'NO FACTORY',
        ers.man_location
    ) as factory_fullname,
    count(*) AS num
FROM toc
    LEFT JOIN locations ON toc.ssoid=locations.id
    LEFT JOIN service AS ers ON toc.erid=ers.id
WHERE toc.status IN ('IN PROGRESS','SUSPENDED') AND toc.factory_support_flag = 1
GROUP BY
    sso_fullname, factory_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactorySupportRequired($sso, $factory, $opt = [])
    {
        if ($sso !== 'ALL') {
            $a['sso_fullname'] = $sso;
        }
        if ($factory !== 'ALL') {
            $a['factory_fullname'] = $factory;
        }
        $a['factory_support_flag'] = '1';

        return self::byConstraints($a, $opt);
    }

    /**
     * Display TOC w/ status OPEN, IN PROGRESS and SUSPENDED
     * By SSO and Factory
     *
     * @return array
     */
    public static function countBySSOAndFactory()
    {
        $query = <<<EOF
SELECT 
    IF(toc.ssoid=0, 
        'NO SSO', 
        locations.location 
    ) AS sso_fullname, 
    IF(ers.man_location IS NULL, 
        'NO FACTORY', 
        ers.man_location 
    ) AS factory_fullname, 
    count(*) AS num 
FROM toc 
    LEFT JOIN locations ON toc.ssoid=locations.id 
    LEFT JOIN service AS ers ON toc.erid=ers.id 
WHERE toc.status IN ('IN PROGRESS','SUSPENDED') 
GROUP BY 
    sso_fullname, factory_fullname 
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByFactoryStatus()
    {
        $query = <<<EOF
SELECT
	IF(toc.status<>'IN PROGRESS',
    	toc.status,
    	CONCAT(toc.status,'_',toc.ifactor)
	) AS istatus,
	IF(ers.man_location IS NULL,
		'NO FACTORY',
		ers.man_location
	) as factory_fullname,
	count(*) AS num
FROM toc
	LEFT JOIN service AS ers ON toc.erid=ers.id
GROUP BY istatus, factory_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFactoryStatus($location, $status, $opt = [])
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['factory_fullname'] = $location;
        }
        if ($status !== 'ALL') {
            $a['istatus'] = $status;
        }

        return self::byConstraints($a, $opt);
    }

    /**
     * @param string $sso
     * @param string $factory
     * @param string $opt
     *
     * @return array
     */
    public static function bySSOAndFactoryStatus($sso, $factory, $opt = [])
    {
        $a = [];
        if ('ALL' !== $sso) {
            $a['sso_fullname'] = $sso;
        }
        if ('ALL' !== $factory) {
            $a['factory_fullname'] = $factory;
        }
        return self::byConstraints($a, $opt);
    }

    public static function countBySSOStatusByConstraints($a = '1=1')
    {
        $FROM = self::getFROM();
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    toc.status,
    locations.location AS sso_fullname,
    count(*) AS num
$FROM
$WHERE
GROUP BY
    status, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countBySSOStatus()
    {
        $query = <<<EOF
SELECT
	IF(toc.status<>'IN PROGRESS',
    	toc.status,
    	CONCAT(toc.status,'_',toc.ifactor)
	) AS istatus,
    IF(toc.ssoid=0,
        'NO SSO',
        locations.location
    ) as sso_fullname,
    count(*) AS num
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
GROUP BY
	istatus, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Count of TOC by Assignee / Status by constraints
     *
     * @param mixed string or array $a
     *
     * @return array
     */
    public static function countByAssigneeStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Query
        $query = <<<EOF
SELECT
    IF(toc.status<>'IN PROGRESS',
        toc.status,
        CONCAT(toc.status,'_',toc.ifactor)
    ) AS istatus,
    IF(people.id IS NULL,
        'NO ASSIGNEE',
        CONCAT(people.firstname,' ',people.lastname)
    ) AS ass_fullname,
    COUNT(DISTINCT(toc.id)) AS num
FROM
    toc
    LEFT JOIN people ON people.id = toc.assid
$WHERE
GROUP BY
    istatus,
    ass_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Count of TOC by Technician / Status by constraints
     *
     * @param mixed string or array $a
     *
     * @return array
     */
    public static function countByTechnicianStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        // Query
        $query = <<<EOF
SELECT
    IF(toc.status<>'IN PROGRESS',
        toc.status,
        CONCAT(toc.status,'_',toc.ifactor)
    ) AS istatus,
    IF(people.id IS NULL,
        'NO TECHNICIAN',
        CONCAT(people.firstname,' ',people.lastname)
    ) AS tech_fullname,
    COUNT(DISTINCT(toc.id)) AS num
FROM
    toc
    LEFT JOIN people ON people.id = toc.tecid
$WHERE
GROUP BY
    istatus,
    tech_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Count of TOC by Technician/Status per SSO
     *
     * @param int $ssoid
     *
     * @return array
     */
    public static function countByTechStatusBySSOID($ssoid)
    {
        if (empty($ssoid)) {
            return;
        }
        $query = <<<EOF
SELECT
	IF(toc.status<>'IN PROGRESS',
    	toc.status,
    	CONCAT(toc.status,'_',toc.ifactor)
	) AS istatus,
	IF(people.email IS NULL,
		'NO Technician',
		people.email
	) AS tech_email,
	COUNT(DISTINCT(toc.id)) AS num
FROM toc
    LEFT JOIN customers_crt AS crt ON crt.customer_id = toc.cuid
	AND crt.erp_location_id = toc.ssoid
	LEFT JOIN people ON people.id = crt.services_rep_id
WHERE
	toc.ssoid=$ssoid
GROUP BY
	istatus,
	tech_email
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of TOC by ASM/Status per SSO
     *
     * @param        $tech
     * @param string $status
     * @param string $sso
     *
     * @return array
     */
    public static function byTechStatusBySSO($tech, $status, $sso)
    {
        // Create constraints
        $a['sso_fullname'] = $sso;
        if ($tech !== 'ALL') {
            $a['tech_email'] = $tech;
        }
        if ($status !== 'ALL') {
            $a['istatus'] = $status;
        }
        // Create query
        $WHERE = tldUtils::constructWhere($a);
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
	IF(people.email IS NULL,
		'NO Technician',
		people.email
	) AS tech_email
$FROM
	LEFT JOIN customers_crt AS crt ON crt.customer_id=cus.id
		AND toc.ssoid=crt.erp_location_id
	LEFT JOIN people ON people.id=crt.services_rep_id
GROUP BY
	toc.id
HAVING
	$WHERE
ORDER BY
	toc.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Count of TOC by ASM/Status per SSO
     *
     * @param int $ssoid
     *
     * @return array
     */
    public static function countByASMStatusBySSOID($ssoid)
    {
        if (empty($ssoid)) {
            return;
        }
        $query = <<<EOF
SELECT
	IF(toc.status<>'IN PROGRESS',
    	toc.status,
    	CONCAT(toc.status,'_',toc.ifactor)
	) AS istatus,
	IF(people.email IS NULL,
		'NO ASM',
		people.email
	) AS asm_email,
	COUNT(DISTINCT(toc.id)) AS num
FROM toc
    LEFT JOIN customers_crt AS crt ON crt.customer_id = toc.cuid
	AND crt.erp_location_id = toc.ssoid
	LEFT JOIN people ON people.id = crt.sales_rep_id
WHERE
	toc.ssoid=$ssoid
GROUP BY
	istatus,
	asm_email
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of TOC by ASM/Status per SSO
     *
     * @param string $asm email
     * @param string $status
     * @param string $sso
     *
     * @return array
     */
    public static function byASMStatusBySSO($asm, $status, $sso)
    {
        // Create constraints
        $a['sso_fullname'] = $sso;
        if ($asm !== 'ALL') {
            $a['asm_email'] = $asm;
        }
        if ($status !== 'ALL') {
            $a['istatus'] = $status;
        }
        // Create query
        $WHERE = tldUtils::constructWhere($a);
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
	IF(people.email IS NULL,
		'NO ASM',
		people.email
	) AS asm_email
$FROM
	LEFT JOIN customers_crt AS crt ON crt.customer_id=cus.id
		AND toc.ssoid=crt.erp_location_id
	LEFT JOIN people ON people.id=crt.sales_rep_id
GROUP BY
	toc.id
HAVING
	$WHERE
ORDER BY
	toc.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Count of TOC by Activity type, SSO
     *
     * @return array
     */
    public static function countBySSOActivityType()
    {
        $query = <<<EOF
SELECT
	IF(toc.activity_type='',
		'No type',
		toc.activity_type
	) AS iactivity_type,
    IF(toc.ssoid=0,
        'NO SSO',
        locations.location
    ) as sso_fullname,
    count(*) AS num
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
GROUP BY
	iactivity_type, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list
     *
     * @return array
     */
    public static function bySSOActivityType($location, $activityType, $opt = [])
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['sso_fullname'] = $location;
        }
        if ($activityType !== 'ALL') {
            $a['iactivity_type'] = $activityType;
        }
        $select = self::getLightSELECT();
        $select .= <<<SQL
    ,IF(toc.activity_type='', 'No type', toc.activity_type) AS iactivity_type
SQL;

        $opt['override']['select'] = $select;
        $opt['override']['from'] = self::getLightFROM();

        return self::byConstraints($a, $opt);
    }

    /**
     * Get number of TOC by SSO and WF
     *
     * @return array
     */
    public function countBySSOWF()
    {
        $WHERE = "WHERE toc.status IN ('IN PROGRESS','SUSPENDED')";
        $query = <<<EOF
SELECT
	CASE
    	WHEN wfactor < 100 THEN 'WF_100'
    	WHEN wfactor BETWEEN 100 AND 1000 THEN '100_WF_1000'
		WHEN wfactor > 1000 THEN '1000_WF'
	END AS iwf,
    IF(toc.ssoid=0,
        'NO SSO',
        locations.location
    ) as sso_fullname,
    count(*) AS num
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
$WHERE
GROUP BY
	iwf, sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get number of TOC by Factory and WF
     *
     * @return array
     */
    public function countByFactoryWF()
    {
        $DF = self::DF;
        $WHERE = "WHERE toc.status IN ('IN PROGRESS','SUSPENDED')";
        $query = <<<EOF
SELECT
    CASE
        WHEN wfactor < 100 THEN 'WF_100'
        WHEN wfactor BETWEEN 100 AND 1000 THEN '100_WF_1000'
        WHEN wfactor > 1000 THEN '1000_WF'
    END AS iwf,
    IF(service.man_location LIKE '',
        'NO ERP',
        service.man_location
    ) as erp_fullname,
    count(*) AS num
FROM toc
    LEFT JOIN locations ON toc.ssoid=locations.id
    LEFT JOIN service ON toc.erid=service.id
$WHERE
GROUP BY
    iwf, erp_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getWFStatsBySSOByConstraints($a)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        $query = <<<EOF
SELECT
	FLOOR(SUM(wfactor)) AS swf,
	FLOOR((SUM(wfactor))/COUNT(*)) AS awf,
    IF(toc.ssoid=0, 'NO SSO', locations.location) AS sso_fullname
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
WHERE
    $WHERE
GROUP BY
	sso_fullname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getFurtherActionList()
    {
        return [
            'PDC' => 'PDC - Product Demerit claim',
            'EAP' => 'EAP - Engineering Activity Process',
            'TASK' => 'Task',
            'none' => 'No further action required',
        ];
    }

    public static function countFurtherActionsByModuleAssigneeByConstraints($a = null)
    {
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } else {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    IF(people.id IS NULL,
        'NO ASSIGNEE',
        CONCAT(people.firstname,' ',people.lastname)
    ) AS ass_fullname,
    COUNT(*) AS num,
    action_module AS module
FROM toc
    LEFT JOIN people ON people.id=toc.assid
$WHERE
GROUP BY
    ass_fullname,
    module
ORDER BY
    ass_fullname
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param array $ssoList
     *
     * @return array
     */
    public static function countSolvedByPeriod(\DateTime $start, \DateTime $end, array $ssoList = [])
    {
        $ssoList = implode(', ', $ssoList);

        $query = <<<SQL
SELECT
  COUNT(*) as toc_count,
  locations.location as sso_name
FROM toc
  LEFT JOIN locations ON toc.ssoid=locations.id
WHERE toc.status='SOLVED'
      AND dt_closed BETWEEN '{$start->format('Y-m-d')}' AND '{$end->format('Y-m-d')}'
      AND toc.ssoid IN ({$ssoList})
GROUP BY toc.ssoid;
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byFurtherActionsByModuleAssigneeByConstraints($module, $assignee, $a = null)
    {
        $constraints = [];
        if ($module !== 'ALL') {
            $constraints[] = "action_module LIKE '$module'";
        }
        if ($assignee !== 'ALL') {
            $constraints[] = "ass_fullname LIKE '$assignee'";
        }
        // additional constraints
        if (!empty($a)) {
            $constraints[] = tldUtils::constructWhere($a);
        }

        // return result
        return self::byConstraints(implode(' AND ', $constraints), ['showAllStatuses' => true]);
    }

    /**
     * Get the latest SPRs
     *
     * @param integer $num
     *
     * @return array
     */
    public static function byLatest($num = 10)
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM ORDER BY toc.id DESC LIMIT $num";

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function bySSOID($ssoid)
    {
        return self::byConstraints(['ssoid' => $ssoid]);
    }

    public function byMultiSSOID($sso)
    {
        if (!is_array($sso)) {
            return [];
        }
        $a = ' ssoid IN(';
        foreach ($sso as $id) {
            $a .= "'$id',";
        }
        $a = substr($a, 0, -1) . ') ';

        return self::byConstraints($a);
    }

    public function createMainProcess(string $module)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Check if all sub process are closed
     *
     * @return boolean
     */
    public function isItsMainProcessClosed($classList = [])
    {
        if (empty($classList)) {
            $classList = [
                'CSR' => 'tldCSR',
//                "WC" => "tldWC"
            ];
        }
        include_once('product_support.inc.php');
        $mainProcessList = $this->getLinksFromHere();
        foreach ($mainProcessList as $link) {
            if (!array_key_exists($link['type'], $classList)) {
                continue;
            }
            $obj = new ReflectionClass($classList[$link['type']]);
            if (!$obj->isInstantiable()) {
                continue;
            }
            $record = $obj->newInstance($link['item']);
            if (!$record->isClosed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generic method to get the SELECT query part
     *
     * @return string
     */
    public static function getSELECT(): string
    {
        $DF = self::DF;

        return <<<EOF
SELECT
	CONCAT(cons.lastname, ', ', cons.firstname) AS con_fullname,
	cus.customer_name,
	cus.asm_id AS asm,
	cons.phone AS con_phone,
	cons.email AS con_email,
	ers.type, pcs.zh,pcs.fr,
	ers.sn,
    ers.maintainer_customer_id,
	ers.cust_asset_num,
	ers.model,
	ers.airport_code,
	ers.date_shipped,
	IF(ers.man_location IS NULL,
		'NO FACTORY',
		ers.man_location
	) AS factory_fullname,
	factory.id AS factory_id,
	factory.id AS erpid,
	factory.erp AS factory_erp,
	IF(locations.location IS NULL,
		'NO SSO',
		locations.location
	) AS sso_fullname,
	locations.erp AS ssoerp,
	toc.*,
	CASE
	   WHEN unit_operation_status LIKE 'MCF' THEN 'Mission Capable Fully (MCF)'
	   WHEN unit_operation_status LIKE 'MCP' THEN 'Mission Capable Partially (MCP)'
	   WHEN unit_operation_status LIKE 'NMC' THEN 'Non Mission Capable (NMC)'
	   ELSE 'Unknown'
	END AS unit_operation_status_full,
	IF(toc.status<>'IN PROGRESS',
    	toc.status,
    	CONCAT(toc.status,'_',toc.ifactor)
	)  AS istatus,
	IF(toc.activity_type='',
		'No type',
		toc.activity_type
	) AS iactivity_type,
	IF(tech.id IS NULL,
        'NO TECHNICIAN',
        CONCAT(tech.firstname,' ',tech.lastname)
    ) AS tec_fullname,
	poster.email AS poster_email,
	CONCAT(poster.lastname,', ',poster.firstname) AS post_fullname,
	IF(assignee.id IS NULL,
        'NO ASSIGNEE',
        CONCAT(assignee.firstname,' ',assignee.lastname)
    ) AS ass_fullname,
	(
		(SELECT COUNT(*) FROM mod_links
		WHERE mod_links.type='TOC' AND mod_links.item=toc.id)
		+
		(SELECT COUNT(*) FROM mod_links
		WHERE mod_links.module='TOC' AND mod_links.parent_id=toc.id)
	) AS num_links,
	DATEDIFF(toc.dt_closed,toc.dt) AS days_close,
	TIMESTAMPDIFF(HOUR,toc.dt,toc.dt_closed) AS hours_close,
	DATE(toc.dt) AS dt,
	toc.dt AS dt_open,
	DATE(toc.dt_closed) AS dt_closed,
	toc.wfactor AS wf,
	IF(TO_DAYS(toc.dt_closed) IS NULL,
		DATEDIFF(NOW(),toc.dt),
		DATEDIFF(toc.dt_closed,toc.dt)
	) AS days_open,
	IF(toc.factory_support_flag = 1,
        'Yes',
        'No'
    ) AS fsf,
    IF(toc.parts_added = 1,
        'Yes',
        'No'
    ) AS parts_added,
	customer_user.id AS customer_user_id,
	customer_user.customer_name AS customer_user_name,
	customer_buyer.id AS customer_buyer_id,
    customer_buyer.customer_name AS customer_buyer_name,
    customer_user.customer_em_jira_key AS customer_em_jira_key,
    (SELECT DATE_FORMAT(MAX(date), "%Y-%m-%d") FROM mod_logs WHERE mod_logs.parent_id=toc.id AND module = 'TOC' AND log_num = 10 AND comment = 'SOLVED') AS dt_solved,
    (SELECT DATE_FORMAT(MAX(date), "%Y-%m-%d") FROM mod_logs WHERE mod_logs.parent_id=toc.id AND module = 'TOC' and log_num=0) AS last_log_update
EOF;
    }

    /**
     * Generic method to get the FROM query part
     *
     * @return string
     */
    public static function getFROM(): string
    {
        $from = self::getLightFROM();
        $from .= <<<EOF
    LEFT JOIN locations AS factory ON factory.location=ers.man_location
    LEFT JOIN products_categories AS pcs ON pcs.en=ers.type
EOF;
        return $from;
    }

    public static function getLightSELECT()
    {
        return <<<SQL
SELECT 
    toc.id,
    IF(locations.location IS NULL, 'NO SSO', locations.location) AS sso_fullname,
    IF(tech.id IS NULL, 'NO TECHNICIAN', CONCAT(tech.firstname, ' ', tech.lastname)) AS tec_fullname,
    toc.disp_tec,
    IF(assignee.id IS NULL, 'NO ASSIGNEE', CONCAT(assignee.firstname, ' ', assignee.lastname)) AS ass_fullname,
    toc.status,
    toc.activity_type iactivity_type,    
    toc.toc_type,        
    toc.is_ibs,        
    toc.is_ihs,        
    toc.is_link,        
    toc.ifactor,
    toc.wfactor AS wf,
    toc.hours,
    toc.unit_operation_status,
    ers.model,
    ers.sn,
    ers.maintainer_customer_id,
    ers.man_location as factory_fullname,
    ers.cust_asset_num,
    toc.apc,
    CONCAT(cons.lastname, ', ', cons.firstname) AS con_fullname,
    cus.customer_name,
    customer_user.id AS customer_user_id,
    customer_user.customer_name AS customer_user_name,
    customer_buyer.id AS customer_buyer_id,
    customer_buyer.customer_name AS customer_buyer_name,
    toc.short_desc,
    DATE(toc.dt) AS dt,
    DATE(toc.dt_closed) AS dt_closed,
    IF(TO_DAYS(toc.dt_closed) IS NULL,
		DATEDIFF(NOW(),toc.dt),
		DATEDIFF(toc.dt_closed,toc.dt)
	) AS days_open,
    IF(toc.factory_support_flag = 1, 'Yes', 'No') AS fsf,
    IF(toc.status<>'IN PROGRESS', toc.status, CONCAT(toc.status,'_',toc.ifactor)) AS istatus
SQL;
    }

    public static function getLightFROM()
    {
        return <<<SQL
FROM toc
	LEFT JOIN locations ON toc.ssoid=locations.id
	LEFT JOIN people AS poster ON poster.id=toc.postid
	LEFT JOIN people AS assignee ON assignee.id=toc.assid
	LEFT JOIN people AS tech ON tech.id=toc.tecid
	LEFT JOIN service AS ers ON toc.erid=ers.id
	LEFT JOIN customers AS customer_user ON ers.customer_id=customer_user.id
	LEFT JOIN customers AS customer_buyer ON ers.buyer_customer_id=customer_buyer.id
	LEFT JOIN customers AS cus ON toc.cuid=cus.id
	LEFT JOIN extranet_users AS cons ON toc.conid=cons.id
SQL;
    }

    /**
     * Generic method by constraints
     *
     * @param mixed string or array $constraints
     * @param array|string $opt
     *
     * @return array
     */
    public static function byConstraints($constraints, $opt = [])
    {
        $HAVING = '';
        // Construct constraints
        if (is_array($constraints) && !empty($constraints)) {

            if (isset($constraints['ers.maintainer_customer_id'], $constraints['cuid'])) {
                $HAVING = sprintf(
                    ' (cuid = %s OR ers.maintainer_customer_id = %s) AND ',
                    $constraints['cuid'],
                    $constraints['ers.maintainer_customer_id']
                );
                unset($constraints['ers.maintainer_customer_id'], $constraints['cuid']);
            }

            $wherePart = tldUtils::constructWhere($constraints);
            
            if (is_string($wherePart) && trim($wherePart) !== '') {
                $HAVING .= $wherePart;
            }
        } else {
            $HAVING = $constraints;
        }

        if (!empty($HAVING)) {
            $HAVING = "HAVING $HAVING";
        } else {
            $HAVING = '';
        }
        // Look for options
        $WHERE = ' WHERE 1=1 AND toc.deleted_at IS NULL';
        if (empty($opt['showAllStatuses'])) {
            $WHERE .= " AND toc.status NOT IN ('CLOSED', 'SOLVED')";
        }
        if ($opt['orderBy'] ?? null) {
            $ORDERBY = TldDatabase::escape($opt['orderBy']);
        } elseif ($opt['orderByRaw'] ?? null) {
            $ORDERBY = $opt['orderByRaw'];
        } else {
            $ORDERBY = 'wf DESC';
        }
        // Extra select
        // example --> (SELECT fct_tld_mod_toc_getDaysSuspendedByID(toc.id)) AS days_suspended
        $SELECT_extra = '';
        if (isset($opt['select'])) {
            $SELECT_extra = ',' . $opt['select'];
        }

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();

        if (!empty($opt['override'])) {
            foreach ($opt['override'] as $clause => $value) {
                ${strtoupper($clause)} = $value;
            }
        }

        if(!empty($opt['tldEurSector']))
        {
            $eastList = "'AL', 'AM', 'AT', 'AZ', 'BY', 'BA', 'BG', 'HR', 'CZ', 'EE', 'GE', 'DE', 'VA', 'HU', 'IT', 'KZ', 'XK', 'KG', 'LV', 'LI', 'LT', 'MK', 'MD', 'ME', 'NL', 'PL', 'RO',
                'RU', 'RS', 'SK', 'SI', 'CH', 'TJ', 'TR', 'TM', 'UA', 'UZ'";
            $westList = "'DZ', 'AD', 'BE', 'BJ', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'CG', 'CD', 'CU', 'CY', 'CI', 'DK', 'FO', 'FI', 'FR', 'GF', 'GA', 'GI', 'GR', 'GP', 'GG', 'GN', 'GW',
                'GY', 'IS', 'IE', 'IM', 'IL', 'JE', 'LU', 'ML', 'MT', 'MQ', 'MR', 'MC', 'MA', 'NE', 'NO', 'PT', 'RE', 'PM', 'SM', 'ST', 'SN', 'SL', 'ES', 'SE', 'TG', 'TN', 'GB', 'EH'";

            $FROM .= <<<EOF
                LEFT JOIN airport_codes ON toc.apc = airport_codes.airport_code
                LEFT JOIN countries ON airport_codes.ctry_code_2 = countries.iso_code_2
            EOF;

            $opt['tldEurSector'] === 'tldEurWestList' ? $WHERE .= " AND countries.iso_code_2 IN ($westList)" : $WHERE .= " AND countries.iso_code_2 IN ($eastList)";
        }

        // OFFSET & LIMIT : Used for pagination
        $LIMIT = isset($opt['limit']) ? 'LIMIT ' . $opt['limit'] : '';
        $OFFSET = isset($opt['offset']) ? 'OFFSET ' . $opt['offset'] : '';

        $query = <<<EOF
            $SELECT
            $SELECT_extra
            $FROM
            $WHERE
            $HAVING
            ORDER BY $ORDERBY
            $LIMIT
            $OFFSET
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function getAssigneeList()
    {
        return tldUser::byConstraints('id IN(SELECT DISTINCT(assid) FROM toc)');
    }

    public static function bySSOStatus($location, $status, $opt = [])
    {
        $a = [];
        if ($location !== 'ALL') {
            $a['sso_fullname'] = $location;
        }
        if ($status !== 'ALL') {
            $a['istatus'] = $status;
        }

        $opt['override']['select'] = self::getLightSELECT();
        $opt['override']['from'] = self::getLightFROM();

        return self::byConstraints($a, $opt);
    }

    /**
     * Get the TOCs by Status by ASM not CLOSED
     *
     * @param $sso
     * @param $status
     * @param $asm
     * @param $sso_asm
     *
     * @return array
     */
    public static function bySSOStatusByASM($sso, $status, $asm, $sso_asm)
    {
        if (!empty($sso_asm)) {
            $ASMGrp = new tldGroup('role_ASM');
            $ASMList = array_column($ASMGrp->getUserlistBySSO($sso_asm), 'id', 'id');
            $list = implode(',', $ASMList);
            $a = " asm_crt IN ($list) ";
        } else {
            $a = " asm_crt=$asm ";
        }
        if ($sso !== 'ALL') {
            $a .= " AND sso_fullname='$sso' ";
        }
        if ($status !== 'ALL') {
            $a .= " AND status='$status' ";
        }
        $a .= " AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(toc.dt,'%Y%m')) BETWEEN 0 AND 12";
        // Create query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
crt.sales_rep_id AS asm_crt
$FROM
    LEFT JOIN customers_crt AS crt ON crt.customer_id=cus.id
        AND toc.ssoid=crt.erp_location_id
    LEFT JOIN people ON people.id=crt.sales_rep_id
GROUP BY
    toc.id
HAVING
    $a
ORDER BY
    toc.id
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get the TOCs by Status by SSO not CLOSED
     *
     * @param $sso
     * @param $status
     *
     * @return array
     */
    public static function bySSOStatusNotClosed($sso, $status)
    {
        $a = '1';
        if ($sso !== 'ALL') {
            $a .= " AND sso_fullname='$sso' ";
        }
        if ($status !== 'ALL') {
            $a .= " AND status='$status'";
        }

        return self::byConstraints($a);
    }

    /**
     * Get the TOCs by Technician id not CLOSED
     *
     * @param integer $id
     *
     * @return array
     */
    public static function byTechnician($id)
    {
        if (empty($id)) {
            return;
        }
        $a = "tecid=$id AND status<>'CLOSED'";

        return self::byConstraints($a);
    }

    /**
     * Get the TOCs by Assignee id not CLOSED
     *
     * @param integer $id
     * @param bool $solvedOnly
     *
     * @return array
     */
    public static function byAssignee($id, $solvedOnly = false)
    {
        if (empty($id)) {
            return;
        }
        $a = (false === $solvedOnly) ? "assid=$id AND status<>'CLOSED'" : "assid=$id AND status='SOLVED'";
        $options['showAllStatuses'] = true;

        return self::byConstraints($a, $options);
    }

    /**
     * Search TOCs
     *
     * @param $id
     *
     * @return array
     *
     */
    public static function search($id)
    {
        if (empty($id)) {
            return;
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
HAVING
	tec_fullname like '$id'
    OR status like '$id'
    OR poster_fullname like '$id'
    OR cu_nama like '$id'
    OR cu_cona like '$id'
    OR apc like '$id'
ORDER BY
	toc.id DESC
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get Print version of the TOC
     */
    public function getPrintVersion()
    {
        $rows = $this->getHeader();
        $rows['is_ibs'] = (bool) $rows['is_ibs'] === true ? 'Yes' : 'No';
        $rows['is_ihs'] = (bool) $rows['is_ihs'] === true ? 'Yes' : 'No';
        $rows['is_link'] = (bool) $rows['is_link'] === true ? 'Yes' : 'No';
        $report = new tldAssocTable(
            $rows,
            [
                'id' => 'TOC#',
                'dt' => 'Date and Time',
                'post_fullname' => 'Poster',
                'status' => 'Status',
                'ass_fullname' => 'Assignee',
                'sso_fullname' => 'SSO',
                'tec_fullname' => 'Service Technician',
                'error_codes' => 'Error Codes',
                'customer_name' => 'Customer Name',
                'customer_user_name' => 'Customer User',
                'con_fullname' => 'Customer Contact',
                'short_desc' => 'Short Problem Description',
                'prob_dsca' => 'Customer Problem Description',
                'hours' => 'Equipment hours',
                'apc' => 'Equipment airport code',
                'activity_type' => 'Service Activity',
                'toc_type' => 'Toc Type',
                'is_ibs'=> 'Involves iBS',
                'is_ihs'=> 'Involves iHS/iPHS',
                'is_link'=> 'Involves Link',
                'unit_operation_status_full' => 'Unit operational status',
                'disp_tec' => 'Dispatch Tech?',
                'ifactor' => 'Importance factor',
                'wf' => 'Weight Factor',
                'est_hours' => 'Estimated hours'
            ],
            [
                'title' => 'TOC Details',
                'links' => ['id' => 'https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='],
            ]
        );

        return $report->fetch();
    }

    public function getERReport() {
        // ER infos :
        if($erid = $this->getERID()){
            $er = new tldEquipment($erid);
            $report = new tldAssocTable(
                $er->getHeader(),
                [
                    'sn' => 'Equipment SN#',
                    'status' => 'Status',
                    'date_entered' => 'Date Entered',
                    'cust_asset_num' => 'Customer Asset#',
                    'type' => 'Type',
                    'model' => 'Model',
                    'er_batch_qty' => 'Batch Qty',
                    'man_location' => 'Manufacturer location',
                    'apc_fullname' => 'Airport',
                    'location_short' => 'Unit Location Short',
                    'sales_org' => 'Sales Organization',
                    'agent_name' => 'Agent Name',
                    'date_shipped' => 'Actual Ship Date'
                ],
                [
                    'title' => 'Unit Details',
                    ]
            );
            return <<<EOF
<a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$erid}"  target="_blank">View ER</a><br>
{$report->fetch()}
EOF;
        }
        return null;
    }
    /**
     * For backward compatibility
     *
     * @param mixed array string $a
     * @param array $opt
     *
     * @return array kpi values
     */
    public static function getKPIPast12MonthByConstraints($a, $opt = null)
    {
        return self::getKPIByConstraints($a, $opt);
    }

    /**
     * Get TOC KPI By Constraints
     *
     * @param mixed array string $a
     * @param array $opt
     *                    ['legend'] string
     *                    ['periodConstraints'] string (default is last 12 months)
     *
     * @return array kpi values
     */
    public static function getKPIByConstraints($a, $opt = null)
    {
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }
        $LEGEND = '';
        if (isset($opt['legend'])) {
            $LEGEND = $opt['legend'];
        }
        if (!empty($opt['periodConstraints'])) {
            $WHERE_PERIOD = $opt['periodConstraints'];
        } else {
            $WHERE_PERIOD = "PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12";
        }
        $query = <<<EOF
SELECT
	'$LEGEND' AS legend,
	nam_period AS xval,
	(SELECT COUNT(*) FROM toc
        WHERE DATE_FORMAT(dt, '%Y%m')=nam_period $WHERE
    ) AS nto,
	(SELECT COUNT(*) FROM toc
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period $WHERE
    ) AS nts,
    ROUND(
        (SELECT 
        	SUM(
				CASE
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=48 THEN 1
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>48 THEN 0
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=72 THEN 1
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>72 THEN 0
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=120 THEN 1
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>120 THEN 0
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=168 THEN 1
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>168 THEN 0
				END
			)
        FROM toc
        LEFT JOIN airport_codes AS apc ON apc.airport_code = toc.apc AND apc.type LIKE 'Airport'
        LEFT JOIN airport_codes as apc2 ON apc2.airport_code = toc.apc AND apc2.type LIKE "Airport" AND apc2.city_name > apc.city_name
		LEFT JOIN countries ON countries.iso_code_2 = apc.ctry_code_2
		LEFT JOIN toc_zones ON toc_zones.parent_id = countries.id
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period AND apc2.id IS NULL
        $WHERE
        )
        /
        (SELECT COUNT(*) FROM toc
            WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            $WHERE
        ),2
    )*100 AS tir,
	(SELECT
        ROUND(
            SUM(
                TIMESTAMPDIFF(DAY,dt,dt_closed)
                -(SELECT fct_tld_mod_toc_getDaysSuspendedByID(toc.id))
            )/COUNT(*)
        )
        FROM toc
        WHERE
            DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            $WHERE
    ) AS tat,
	(SELECT
        MAX(
            TIMESTAMPDIFF(
                DAY,
                DATE_FORMAT(dt,'%Y-%m-%d'),
                LAST_DAY(CONCAT(SUBSTRING(p.nam_period,1,4), '-', SUBSTRING(p.nam_period,5,2), '-01'))
            )
        )
        FROM toc
	    WHERE status NOT IN ('SOLVED', 'CLOSED')
        AND PERIOD_DIFF(DATE_FORMAT(dt, '%Y%m'), p.nam_period) <= 0
        AND (PERIOD_DIFF(DATE_FORMAT(dt_closed, '%Y%m'), p.nam_period) > 0 OR TO_DAYS(dt_closed) IS NULL)
            $WHERE
    ) AS tol
FROM
	fin_periods AS p
WHERE
	$WHERE_PERIOD
ORDER BY
	nam_period
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $constraints
     * @param array $options
     * @return array
     */
    public static function getNtoByConstraints($constraints, $options = []){
        $WHERE = tldUtils::constructWhere($constraints);
        $legend = isset($options['legend']) ? $options['legend'] : 'NTO';
        $JOIN =  isset($options['join']) ? $options['join'] : '';
        $query=<<<SQL
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT COUNT(*) FROM toc
        $JOIN
        WHERE DATE_FORMAT(dt, '%Y%m') = nam_period AND $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $constraints
     * @param array $options
     * @return array
     */
    public static function getNtsByConstraints($constraints, $options){
        $WHERE = tldUtils::constructWhere($constraints);
        $legend = isset($options['legend']) ? $options['legend'] : 'NTO';
        $JOIN = isset($options['join']) ? $options['join'] : '';
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT COUNT(*) FROM toc
        $JOIN
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period AND $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $constraints
     * @param array $options
     * @return array
     */
    public static function getTirByConstraints($constraints, $options){
        $WHERE = tldUtils::constructWhere($constraints);
        $legend = isset($options['legend']) ? $options['legend'] : 'NTO';
        $JOIN = isset($options['join']) ? $options['join'] : '';
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
        (SELECT 
        	SUM(
				CASE
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=48 THEN 1
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>48 THEN 0
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=72 THEN 1
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>72 THEN 0
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=120 THEN 1
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>120 THEN 0
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=168 THEN 1
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>168 THEN 0
				END
			)
        FROM toc
        $JOIN
        LEFT JOIN airport_codes AS apc ON apc.airport_code = toc.apc AND apc.type LIKE 'Airport'
        LEFT JOIN airport_codes as apc2 ON apc2.airport_code = toc.apc AND apc2.type LIKE "Airport" AND apc2.city_name > apc.city_name
		LEFT JOIN countries ON countries.iso_code_2 = apc.ctry_code_2
		LEFT JOIN toc_zones ON toc_zones.parent_id = countries.id
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period AND apc2.id IS NULL
        AND $WHERE
        )
        /
        (SELECT COUNT(*) FROM toc
            $JOIN
            WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            AND $WHERE
        ),2
    )*100 AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getTatByConstraints($constraints, $options){
        $WHERE = tldUtils::constructWhere($constraints);
        $legend = isset($options['legend']) ? $options['legend'] : 'NTO';
        $JOIN = isset($options['join']) ? $options['join'] : '';
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT
            SUM(
                TIMESTAMPDIFF(DAY,dt,dt_closed)
                -(SELECT fct_tld_mod_toc_getDaysSuspendedByID(toc.id))
            )/COUNT(*)
        FROM toc
        $JOIN
        WHERE
            DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            AND $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string|array $constraints
     * @param array $options
     * @return array
     */
    public static function getTolByConstraints($constraints, $options){
        $WHERE = tldUtils::constructWhere($constraints);
        $legend = isset($options['legend']) ? $options['legend'] : 'NTO';
        $JOIN = isset($options['join']) ? $options['join'] : '';
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT 
        MAX(
            TIMESTAMPDIFF(
                DAY,
                DATE_FORMAT(dt,'%Y-%m-%d'),
                LAST_DAY(CONCAT(SUBSTRING(p.nam_period,1,4), '-', SUBSTRING(p.nam_period,5,2), '-01'))
            )
        )
        FROM toc
        $JOIN
        WHERE
            status NOT IN ('SOLVED', 'CLOSED')
            AND
            PERIOD_DIFF(DATE_FORMAT(dt, '%Y%m'), p.nam_period) <= 0
            AND (
              PERIOD_DIFF(DATE_FORMAT(dt_closed, '%Y%m'), p.nam_period) > 0
              OR TO_DAYS(dt_closed) IS NULL
            )
            AND $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getTocsLengthOfClosureKPI(string $sso, string $period): array
    {
        $query=<<<EOF
SELECT
    (CASE
         WHEN DATEDIFF(dt, dt_closed) > -7 THEN 'Under 7 days'
         WHEN DATEDIFF(dt, dt_closed) > -14 THEN '7 to 14 days'
         WHEN DATEDIFF(dt, dt_closed) > -21 THEN '14 to 21 days'
         ELSE 'More than 21 days'
    END) AS solvedIn,
    DATE_FORMAT(dt,'%Y-%m') AS creationDate,
    count(*) AS total,
    (IF(ast_arrived_at IS NULL, true, false)) AS remote
FROM
    toc
WHERE
    status = 'SOLVED'
    AND activity_type NOT IN ('Training', 'Service Bulletin', 'Unit Upgrade', 'Maintenance')
    AND toc_type != 'Payable Services'
    AND dt > '$period'
    AND ssoId = $sso
GROUP by
    creationDate,
    solvedIn,
    remote
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list of CRT using customer and SSO set in TOC
     *
     * @return array
     */
    public function getCRT()
    {
        return tldCRT::byCustomerIDSSOID(
            $this->getCUID(),
            $this->getSSOID()
        );
    }

    /*************************************
     *
     *      NOTIFICATION FUNCTIONS
     *
     *************************************/

    public function getActiveLangNotificationList()
    {
        return ['en', 'fr', 'zh', 'es', 'ru', 'ja'];
    }

    public function getLangNotification()
    {
        $lang = $this->itsContact->getPreferedLanguage();
        if (!in_array($lang, self::getActiveLangNotificationList())) {
            $lang = 'en';
        }

        return $lang;
    }

    /**
     * Generic method to send email notification
     *
     * @param array|string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     * @param string $file
     * @param string $cc
     * @param string $bcc
     * @param array|string $opt
     *
     * @return bool
     */
    public function sendEmail($to, $from, $subject, $body, $file = '', $cc = '', $bcc = '', $opt = [])
    {
        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $body,
            $file,
            $cc,
            $bcc,
            $opt
        );
    }

    /**
     * Returns a clean and centralised subject email
     *
     * @return string
     */
    public function getEmailSubject()
    {

        $daysOpened = $this->getDaysOpened();
        $strDaysOpened = $daysOpened < 1 ? '1 day' : $daysOpened . ' days';

        $subject = "TOC#$this->itsID, $strDaysOpened, {$this->itsHeader['sso_fullname']}, {$this->itsHeader['customer_name']}";
        if ($this->isEquipmentSet()) {
            $subject .= ", {$this->itsHeader['model']}";
        }

        return $subject;
    }

    /**
     * Get list of TLD recipients following IFactor
     *
     * @param int|string $ifactor
     *
     * @return array of string
     */
    public function getTLDEmailRecipients($ifactor = '')
    {
        global $user;
        $TO = [];

        // Get ER
        $er = new tldEquipment($this->getERID());

        // Get BU, SSO & Factory
        $sso = new tldLocation($this->getSSOID());
        $ssoErp = $sso->getERP();
        $factoryErp = $this->getFactoryERP();

        $ssoServiceId = tldLocation::getIDByLocation($er->getSSOService());
        $ssoService = new tldLocation($ssoServiceId);
        $ssoServiceErp = $ssoService->getERP();

        // In all cases, include...
        // -- Service department email
        if ($sso->getShortName() !== 'TLD EUR'){
            $TO[] = [$sso->getServiceEmail()];
        }

        if ($er->getSSOService() !== 'TLD EUR'){
            $TO[] = [$ssoService->getServiceEmail()];
        }

        if (in_array($er->getSSOService(), ['SAS', 'SAS TAXIBOT'])){
            $locationDTV = new TLDLocation(37);
            $locationSAS = new TLDLocation(57);
            $TO[] = [$locationSAS->getServiceEmail(), $locationDTV->getServiceEmail()];
        }

        // -- Current User
        if (!empty($user->itsDetails['email'])) {
            $TO[] = [$user->itsDetails['email']];
        }

        // -- Tech
        $techid = $this->getTechnicianID();
        if (!empty($techid)) {
            $tech = new tldUser($techid);
            $TO[] = [$tech->getEmail()];
        }
        // -- Assignee
        $assid = $this->getAssigneeID();
        if (!empty($assid)) {
            $assignee = new tldUser($assid);
            $TO[] = [$assignee->getEmail()];
        }

        // -- Members
        $membersEmail = $this->getMembersEmail();
        $TO[] = $membersEmail;

        // Following IFactor, include...
        $IF = $this->getIF();
        if (!empty($ifactor)) {
            $IF = $ifactor;
        }

        if (\in_array($IF, ['10', '100', '1000'], true)) {
            $crts = $this->getCRT();
            foreach ($crts as $crt) {
                $asm = new tldUser($crt['sales_rep_id']);
                $TO[] = [$asm->getEmail()];
            }
        }

        $factoryGroupNames = [];
        $ssoGroupNames = [];
        $alvestGroupNames = [];
        $globalGroupNames = [];
        switch ($IF) {
            case 1:
            case 10:
                $ssoGroupNames[] = ['role_CSM'];
                break;
            case 100:
                if ($factoryErp !== null) {
                    $factoryGroupNames[] = ['role_PSM', 'role_RME','role_EM', 'role_PSE', 'role_COO'];
                }
                $ssoGroupNames[] = ['role_CSM', 'role_EVP'];
                $alvestGroupNames[] = ['role_CSM'];
                if($this->isIbs()){
                    $globalGroupNames[] = ['SRME_IBS', 'SPSM_IBS'];
                }

                if($this->isLink()){
                    $globalGroupNames[] = ['SRME_LINK', 'SPSM_LINK'];
                }
                break;
            case 1000:
                if ($factoryErp !== null) {
                    $factoryGroupNames[] = ['role_PSM', 'role_RME', 'role_EM', 'role_QAM', 'role_QE', 'role_PSE', 'role_RCOO', 'role_COO', 'role_CEO', 'ROLE_TOC_1000_NOT'];
                }
                $ssoGroupNames[] = ['role_EVP', 'role_CEO', 'role_COO', 'role_CSM', 'ROLE_TOC_1000_NOT'];
                $alvestGroupNames[] = ['role_CSM','role_GTD', 'role_CEO', 'role_GPID', 'role_COO', 'role_GCOO'];
                if($this->isIbs()){
                    $globalGroupNames[] = ['SRME_IBS', 'SPSM_IBS'];
                }

                if($this->isLink()){
                    $globalGroupNames[] = ['SRME_LINK', 'SPSM_LINK'];
                }
                break;
        }

        // Following Factory Support Flag, include...
        if ($factoryErp !== null && (int)$this->getFactorySupportFlag() === 1) {
            $factoryGroupNames[] = ['role_PSM','role_PSE', 'role_RME','role_EM','role_COO'];
            if($this->isIbs()){
                $globalGroupNames[] = ['SRME_IBS', 'SPSM_IBS'];
            }

            if($this->isLink()){
                $globalGroupNames[] = ['SRME_LINK', 'SPSM_LINK'];
            }
        }

        // Other situation
        // -- ER is contracted
        // ---- SCM module -> TOC notification members
        if ($er->isServiceContracted()) {
            $scm = new tldSCM($er->getServiceContractID(), $er->getServiceContractERP());
            $TOCNotMembers = array_column($scm->getTOCNotMembers(), 'user_email', 'user_email');
            $TO[] = $TOCNotMembers;
        }

        if ($factoryErp !== null) {
            foreach(array_unique(array_merge(...$factoryGroupNames)) as $groupName){
                $group = new tldGroup($groupName, $factoryErp);
                $TO[] = $group->getEmailList();
            }
        }
        foreach(array_unique(array_merge(...$ssoGroupNames)) as $groupName){
            $group = new tldGroup($groupName, $ssoErp);
            $TO[] = $group->getEmailList();
        }

        foreach(array_unique(array_merge(...$ssoGroupNames)) as $groupName){
            $group = new tldGroup($groupName, $ssoServiceErp);
            $TO[] = $group->getEmailList();
        }

        foreach(array_unique(array_merge(...$alvestGroupNames)) as $groupName){
            $group = new tldGroup($groupName, 900);
            $TO[] = $group->getEmailList();
        }

        foreach(array_unique(array_merge(...$globalGroupNames)) as $groupName){
            $group = new tldGroup($groupName);
            $TO[] = $group->getEmailList();
        }

        // Clean up duplicates or empty val
        return array_unique(array_filter(array_merge(... $TO)));
    }

    public static function getDailyRecapEmailRecipients(int $ssoId): array {
        $recipients = [];
        $location = new tldLocation($ssoId);
        if ($ssoId !== 7) {
            $recipients[] = [$location->getServiceEmail()];
        }
        $ssoGroupNames = ['role_CSM', 'role_EVP', 'ROLE_CEO', 'ROLE_TOC_1000_NOT', 'ROLE_AST', 'ACL_TOC_ADMIN'];
        foreach($ssoGroupNames as $groupName){
            $group = new tldGroup($groupName, $location->getERP());
            $recipients[] = $group->getEmailList();
        }
        return array_unique(array_filter(array_merge(... $recipients)));
    }

    public function getDefaultEmailSubject()
    {
        return "TOC#{$this->getID()}, {$this->getCustomerName()}, {$this->getERModel()}, {$this->getERSN()}";
    }

    /**
     * Notify TLD personal
     *
     * @param $subject
     * @param $body
     *
     * @return bool
     *
     */
    public function notifyTLD($subject, $body)
    {
        global $user;
        $subject = $this->getEmailSubject() . " - $subject";
        $to = $this->getTLDEmailRecipients();

        if ((int)$this->getFactorySupportFlag() === 1) {
            $subject .= ' - FACTORY SUPPORT NEEDED';
        }

        $er = new tldEquipment($this->getERID());
        if ($er->isBlackCat()){
            $subject .= ' - BLACK CAT';
        }

        if ($user instanceof \tldGenericUser) {
            $from = $user->getEmail();
        }

        if (empty($from)) {
            $from = 'noreply@tld-gse.com';
        }

        return $this->sendEmail(array_unique($to), $from, $subject, $this->getNotificationBody($body), $file = '');
    }

    public function getNotificationBody($body = '', $logOffset = 1) {
        $logs = array_slice($this->getNotificationFullLog(), $logOffset, 5);
        $logsReportHtml = '';

        foreach ($logs as $index => &$log) {
            if ($log['comment'] === '') {
                unset($logs[$index]);
                continue;
            }
            if ($log['logType'] === 'not') {
                $log['comment'] = sprintf('Customer Notification: %s', $log['comment']);
            }
        }
        unset($log);

        if (!empty($logs)) {
            $logsReport = new tldReportColumnar(
                $logs,
                [
                    'xItems' => [
                        'dt' => 'Date',
                        'poster_fullname' => 'Poster',
                        'module' => 'Module',
                        'comment' => 'Comment',
                    ],
                    'title' => sprintf('<h3>Last %s TOC logs</h3>', count($logs)),
                    'showItemNumbers' => 'reversed',
                    'sortable' => 'no',
                ]
            );
            $logsReportHtml = $logsReport->fetch();
        }

        $body .= <<<EOF
<a href="https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&m[2]=log&m[3]=add&id=$this->itsID">The correct method of reply is to update the TOC log. Click here to reply</a></br>
{$logsReportHtml}
{$this->getNotificationReport()}
EOF;
        return $body;
    }

    public function getNotificationReport() {
        $report = new tldHTMLTable(
            [$this->getPrintVersion(), $this->getERReport()],
            [
                'cols' => 2,
                'attribs' => [
                    'table' => " width='100%'",
                    'tr' => " bgcolor='#FFFFFF'",
                    'td' => " width='50%'",
                ]
            ]
        );
        return $report->fetch();
    }

    public function addCustomerContact($cid)
    {
        throw new \Exception('This is no longer used');
    }

    public function getCustomerContacts()
    {
        $query = <<<EOF
SELECT
    toc_contacts.*,
    ext.firstname,
    ext.lastname,
    CONCAT(ext.firstname,' ',ext.lastname) AS fullname,
    ext.email,
    ext.title,
    ext.enable
FROM
    toc_contacts
    LEFT JOIN extranet_users AS ext ON ext.id=toc_contacts.contact_id
WHERE
    toc_contacts.parent_id=$this->itsID
ORDER BY
    ext.firstname
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function deleteCustomerContact($cid)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Get external email to notify TOC
     *
     * @return array
     */
    public function getExternalEmailRecipients()
    {
        $TO = [];
        // if ER is contracted
        $er = new tldEquipment($this->getERID());
        // -- SCM module -> TOC notification members
        if ($er->isServiceContracted()) {
            $scm = new tldSCM($er->getServiceContractID(), $er->getServiceContractERP());
            $extTOCNotMembers = array_column(
                $scm->getExternalTOCNotMembers(),
                'user_email',
                'user_email'
            );
            $TO = array_merge($TO, $extTOCNotMembers);
        }
        // Look additional contacts
        $contacts = $this->getCustomerContacts();
        if (count($contacts)) {
            foreach ($contacts as $contact) {
                $TO[] = $contact['email'];
            }
        }

        return array_unique($TO);
    }

    /**
     * Notify customer only
     *
     * @param        $subject
     * @param        $body
     * @param string $file
     * @param string $comment
     * @param array  $recipients
     *
     * @return bool|int|string
     */
    public function notifyCustomer($subject, $body, $file = '', $comment = '', $recipients = [])
    {
        global $user;
        // Prepare email
        $to = $this->itsContact->getEmail();
        if (is_a($user, 'tldUser') && !empty($user)) {
            $from = $user->getEmail();
        } else {
            $from = 'noreply@tld-gse.com';
        }

        ### Customize according to SSO ###
        $sso = [];
        $ssoId = (int)$this->itsHeader['ssoid'];
        switch ($ssoId) {
            case 45:
            case 98:
                $sso['name'] = $this->itsHeader['sso_fullname'];
                $sso['logo'] = 'aero.jpg';
                $sso['website'] = 'https://www.aerospecialties.com/';
                $sso['width'] = '160';
                break;
            case 57:
                $sso['name'] = $this->itsHeader['sso_fullname'];
                $sso['logo'] = 'sas.png';
                $sso['website'] = 'https://www.smart-airport-systems.com/';
                $sso['width'] = '150';
                $sso['height'] = '100';
                break;
            default :
                $sso['name'] = 'TLD';
                $sso['website'] = 'https://www.tld-gse.com';
                $sso['logo'] = 'tld-1inch.jpg';
                $sso['width'] = '100';
                $sso['height'] = '60';
        }

        $subject = sprintf('%s - %s - %s', $this->getERSN(), $this->getEmailSubject(), $subject);
        $cc = $this->getExternalEmailRecipients();
        if(!empty($recipients)){
            $cc = array_merge($cc, $recipients);
        }
        // Create nice html email contents
        $email_body = $this->constructEmailHeader($sso);
        $email_body .= $body;
        $email_body .= $this->getServiceTermsEmailContents($sso);
        $email_body .= $this->constructEmailTocInfo($sso);
        $email_body .= $this->constructEmailFooter($sso);
        // Get email PDF version
        // --- prepare html
        $dt = date('Y-m-d H:i:s');
        $email_info = <<<EOF
<p>Date: $dt<br>From: $from<br>To: $to</p>
<p>Subject: $subject</p>
EOF;
        $html = str_replace(
            '<!--#NOT_INFO#-->',
            $email_info,
            $email_body
        );
        // --- generate from html2pdf lib
        $html2pdf = new tldHTML2PDF($html, ['encoding' => 'utf-8']);

        // --- send to tldFile
        $fid = 0;
        if ($html2pdf->itsConvertedPdfFile !== null) {
            $fid = tldFile::upload(
                $html2pdf->itsConvertedPdfFile->getFilePath(),
                self::FILE_DIR,
                $html2pdf->itsConvertedPdfFile->getBasename()
            );
            if (is_string($fid)) {
                throw new \Exception($fid);
            }
        }

        $userid = $user instanceof tldUser ? $user->getID() : 0;

        // Record notification
        // - Update recipients array to string
        $cc = (is_array($cc)) ? implode(',', $cc) : $cc;

        // - Save notification (Not full email - See #1167340)
        if(!empty($comment)){
            $comment = TldDatabase::escape(mb_convert_encoding($comment, 'ISO-8859-1', 'UTF-8'));
        }

        $e = $this->addNotification(
            [
                'uid' => $userid,
                'recipients' => $to,
                'email' => $comment,
                'fid' => $fid,
                'cc' => $cc,
            ]
        );
        if (is_string($e)) {
            return $e;
        }

        // Send email
        return $this->sendEmail($to, $from, $subject, $email_body, $file, $cc, '', ['charset' => 'utf-8']);
    }

    public function constructEmailHeader($sso)
    {
        $backgroundFrame = '#ffffff';
        
        return include('documents/toc/email.header.php');
    }

    public function constructEmailTocInfo($sso)
    {
        $lang = $this->getLangNotification();
        // TOC & ER info
        $tocHeader = $this->getHeader();
        $er = new tldEquipment($tocHeader['erid']);
        $erHeader = $er->itsDetails;
        $ex = $this->itsContact;
        $exHeader = $ex->itsDetails;
        // Get CRT
        $crtList = $this->getCRT();
        // Assign reps
        $uSales = new tldUser($crtList[0]['sales_rep_id']);
        $uParts = new tldUser($crtList[0]['parts_rep_id']);
        $uService = new tldUser($crtList[0]['services_rep_id']);

        // return toc info
        return include("documents/toc/$lang/email.info.php");
    }

    public function getUnicode($word)
    {
        $word0 = iconv('gbk', 'utf-8', $word);
        $word1 = iconv('utf-8', 'gbk', $word0);
        $word = ($word1 == $word) ? $word0 : $word;

        preg_match_all('#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s', $word, $array, PREG_PATTERN_ORDER);
        $return = [];

        foreach ($array[0] as $cc) {
            $arr = str_split($cc);
            $bin_str = '';
            foreach ($arr as $value) {
                $bin_str .= decbin(ord($value));
            }
            $bin_str = preg_replace('/^.{4}(.{4}).{2}(.{6}).{2}(.{6})$/', '$1$2$3', $bin_str);
            $return[] = '&#' . bindec($bin_str) . ';';
        }

        return implode('', $return);
    }

    public function constructEmailFooter($sso)
    {
        return include('documents/toc/email.footer.php');
    }

    /**
     * Get Open date with nice format
     *
     * @return string
     */
    public function getOpenDate()
    {
        $dateTime = new DateTime($this->itsHeader['dt']);
        $timestamp = $dateTime->format('U');
        // Get the date in the language
        $lang = $this->getLangNotification();
        switch ($lang) {
            case 'fr':
                setlocale(LC_TIME, 'fr_FR.UTF8');

                return date('d/m/Y', $timestamp);
                break;
            case 'es':
                setlocale(LC_TIME, 'es_ES.UTF8');

                return date('d/m/Y', $timestamp);
                break;
            case 'zh':
                setlocale(LC_TIME, 'zh_CN.UTF8');

                return $dateTime->format('Y&#24180;m&#26376;d&#26085;');
                break;
            case 'ru':
                setlocale(LC_TIME, 'ru_RU.UTF8');

                return date('d/m/Y', $timestamp);
                break;
            default:
                setlocale(LC_TIME, 'en_US.UTF8');

                return date('d/m/Y', $timestamp);
                break;
        }
    }

    /**
     * Get wording for TOC IN PROGRESS customer notification
     *
     * @return string
     */
    public function getOpenEmailContents($sso)
    {
        $lang = $this->getLangNotification();

        return include("documents/toc/$lang/email.open.php");
    }

    /**
     * Get wording for TOC customer notification
     *
     * @param string $txt1
     * @param string $sso
     *
     * @return string
     */
    public function getInProgressEmailContents($txt1 = '', $sso='TLD')
    {
        $lang = $this->getLangNotification();

        return include("documents/toc/$lang/email.in_progress.php");
    }

    /**
     * Get wording for TOC SOLVED customer notification
     *
     * @param $tokenLink
     * @param string $csrDescription
     *
     * @return string
     */
    function getSolvedEmailContents($tokenLink, $sso, $csrDescription = '')
    {
        $lang = $this->getLangNotification();

        $roles = array_map(function ($role) {
            return $role['role'];
        }, $this->itsContact->getRoles());
        $tocLink = 'TOC#' . $this->itsID;
        if (in_array('role_TOC', $roles)) {
            $tocLink = '<a href="https://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id=' . $this->itsID . '" >TOC#' . $this->itsID . '</a>';
        }
        $csrDescription = str_replace('\r\n', '<br>', $csrDescription);

        return include("documents/toc/$lang/email.solved.php");
    }

    /**
     * Get Service Terms and conditions for TOC customer notifications
     *
     * @return string
     */
    public function getServiceTermsEmailContents($sso)
    {
        $lang = $this->getLangNotification();

        return include("documents/toc/$lang/email.terms.php");
    }

    /**
     * Get list of TOC notification sent to customer
     *
     * @return array
     */
    public function getNotifications()
    {
        return tldTOCNOT::byParent($this->itsID);
    }

    /**
     * Save TOC notification sent to customer
     *
     * @param array $a
     *
     * @return int or string on error
     */
    public function addNotification($a)
    {
        throw new \Exception('This is no longer used');
    }

    public static function getOpenTocsAsAssigneeOrTechnician(int $userId): array
    {
        // query
        $query = <<<EOF
SELECT
   id
FROM
    toc
WHERE
    toc.status NOT IN('SOLVED','CLOSED')
    AND (tecid = $userId OR assid = $userId)
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function processWFactorCalculation()
    {
        throw new \Exception('This is no longer used');
    }

    public static function getSurveyFields(): array
    {
        return [
            'survey_work' => 'Work execution',
            'survey_responsiveness' => 'Responsiveness',
            'survey_communication' => 'Communication',
            'survey_attitude' => 'Attitude',
            'survey_comment' => 'Comment',
        ];
    }

    public function getSurveyView()
    {
        $report = new tldAssocTable(
            $this->getHeader(),
            array_merge(
                [
                    'con_fullname' => 'Contact name',
                    'con_email' => 'Contact email',
                    'space' => '---spacer---',
                ],
                self::getSurveyFields()
            ),
            ['title' => 'Survey results']
        );

        return $report->fetch();
    }

    /**
     * Generate a token in order to allow TOC survey access
     *
     * @return string token
     * @throws Exception
     */
    public function generateTokenSurvey()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Provide extranet link to access TOC survey
     *
     * @param string $token
     *
     * @return string
     *
     */
    public function getSurveyLink($token)
    {
        return "https://www.tld-gse.com/extranet/index.php?m[0]=survey&m[1]=toc&token=$token";
    }

    /**
     * @param string $csrDescription
     *
     * @return bool|int|string
     */
    public function processSolvedNotification($csrDescription = '')
    {
        // Prepare survey
        // -- Generate token
        try {
            $token = $this->generateTokenSurvey();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        // -- get survey link
        $tokenLink = self::getSurveyLink($token);
        // Notify
        // -- Prepare email
        $subject = 'SOLVED';

        $sso = 'TLD';
        $ssoId = $this->itsHeader['ssoid'];
        if (in_array($ssoId, ['45', '98', '57'], true)) {
            $sso = $this->itsHeader['sso_fullname'];
        }
        $msg = $this->getSolvedEmailContents($tokenLink, $sso, $csrDescription);

        // -- Notify customer
        return $this->notifyCustomer($subject, $msg);
    }

    public function setSurveyResults($a)
    {
        throw new \Exception('This is no longer used');
    }

    public function notifySurveyCompletion()
    {
        // make sure the header is up to date
        $this->refresh();
        // prepare email
        $subject = $this->getDefaultEmailSubject() . ' - Survey results from extranet';
        $body = $this->getSurveyView();
        $body .= "<br><a href=\"https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id=$this->itsID\">Click here to view TOC#$this->itsID</a>";
        $body .= $this->getPrintVersion();
        $from = 'noreply@tld-gse.com';
        // Recipients
        $TO = [];
        $sso = new tldLocation($this->getSSOID());

        // -- Tech
        $techid = $this->getTechnicianID();
        if (!empty($techid)) {
            $tech = new tldUser($techid);
            $TO[] = [$tech->getEmail()];
        }

        // -- Assignee
        $assigneeId = $this->getAssigneeID();
        if (!empty($assigneeId)) {
            $assignee = new tldUser($assigneeId);
            $TO[] = [$assignee->getEmail()];
        }

        // -- CRT ASMs
        $crts = $this->getCRT();
        foreach ($crts as $crt) {
            $asm = new tldUser($crt['sales_rep_id']);
            $TO[] = [$asm->getEmail()];
        }

        // -- Groups
        $ssoGroupNames = ['role_CSM','role_EVP','role_CEO',];
        $alvestGroupNames = ['role_CSM', 'role_COO', 'role_GCOO'];

        // Get Emails and Filter
        foreach($ssoGroupNames as $groupName){
            $group = new tldGroup($groupName, $sso->getERP());
            $TO[] = $group->getEmailList();
        }
        foreach($alvestGroupNames as $groupName){
            $group = new tldGroup($groupName, 900);
            $TO[] = $group->getEmailList();
        }
        $TO = array_unique(array_filter(array_merge(...$TO)));

        return $this->sendEmail($TO, $from, $subject, $body);
    }

    public static function getSurveyKPIbyPeriodByConstraints($from, $to, $a = null, $options = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (is_string($a)) {
            $WHERE = $a;
        }
        if ($WHERE) {
            $WHERE = ' AND ' . $WHERE;
        }
        // query
        $query = <<<EOF
SELECT
    (SELECT COUNT(*) FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS total,
    (SELECT
        ROUND((AVG(survey_work)+AVG(survey_responsiveness)+AVG(survey_communication)+AVG(survey_attitude))/4,2)
        FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS average,
    (SELECT ROUND(AVG(survey_work),2) FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS work,
    (SELECT ROUND(AVG(survey_responsiveness),2) FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS responsiveness,
    (SELECT ROUND(AVG(survey_communication),2) FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS communication,
    (SELECT ROUND(AVG(survey_attitude),2) FROM toc
        WHERE DATE_FORMAT(dt_closed,'%Y%m') LIKE fin_periods.nam_period AND survey_work<>''
        $WHERE
    ) AS attitude,
    nam_period AS period
FROM
    fin_periods
WHERE
    nam_period BETWEEN '$from' AND '$to'
GROUP BY
    period
ORDER BY
    period
EOF;


        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getSurveyKPIbySSOPeriodByConstraints($from, $to, $a = null)
    {
        $ssoList = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        $data = [];
        foreach ($ssoList as $ssoID => $ssoName) {
            $kpiData = self::getSurveyKPIbyPeriodByConstraints($from, $to, ['ssoid' => $ssoID]);
            $data[] = ['sso_fullname' => $ssoName, 'ssoid' => $ssoID] + $kpiData[0];
        }

        return $data;
    }

    /**
     * KPI for display the average days for TOC IN PROGRESS By SSO By Period
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param array $ssoList
     *
     * @return array
     */
    public static function getAverageDaysOpenInProgressKPIBySSO(\DateTime $start, \DateTime $end, array $ssoList = [])
    {
        $ssoList = implode(', ', $ssoList);

        $query = <<<SQL
SELECT
    ROUND(SUM(DATEDIFF('{$end->format('Y-m-d')}',dt))/COUNT(*), 0) as avg,
    status,
    locations.location as sso_name,
    DATE_FORMAT(dt, '%Y-%m') as month
FROM toc
  LEFT JOIN locations ON locations.id = toc.ssoid
WHERE dt BETWEEN '{$start->format('Y-m-d')}' AND '{$end->format('Y-m-d')}' 
  AND toc.ssoid IN ({$ssoList}) 
  AND toc.status = 'IN PROGRESS'
GROUP BY toc.ssoid, toc.status, month
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function getDaysOpened()
    {
        return date_diff(new DateTime($this->itsHeader['dt']), new DateTime($this->itsHeader['dt_closed'] !== '0000-00-00' ? $this->itsHeader['dt_closed'] : null))->days;
    }

    public static function getDailyReport($title, $sqlWhere) {
        $query = <<<SQL
SELECT toc.id as toc_id,
       CONCAT(ass.firstname, ', ', ass.lastname) as assignee,
       toc.ifactor as ifactor,
       loc.location as sso,
       CONCAT(tec.firstname, ', ', tec.lastname) as technician,
       er.sn,
       er.model,
       er.man_location as factory,
       cus.customer_name,
       er.airport_code,
       toc.status,
       toc.short_desc
FROM toc
    LEFT JOIN customers cus ON cus.id = toc.cuid
    LEFT JOIN service er ON er.id = toc.erid
    LEFT JOIN people ass ON  ass.id=toc.assid
    LEFT JOIN people tec ON  tec.id=toc.tecid
    LEFT JOIN locations loc ON  toc.ssoid = loc.id
{$sqlWhere}
    AND toc.id NOT IN (
        SELECT parent_id FROM mod_logs WHERE mod_logs.comment like 'Created from extranet%'
    )
GROUP BY toc.id
ORDER BY assignee, toc.ifactor DESC
SQL;
        $rows = tldUtils::getSqlToAssocArray($query);
        if(empty($rows)) {
            return [];
        }
        return new tldReportColumnar(
            $rows,
            [
                'xItems'   => [
                    'toc_id' => 'TOC#',
                    'assignee' => 'Assignee',
                    'ifactor' => 'IF',
                    'sso' => 'SSO',
                    'technician' => 'Technician',
                    'sn' => 'SN',
                    'model' => 'Model',
                    'customer_name' => 'Customer Name',
                    'airport_code' => 'APC',
                    'status' => 'Status',
                    'short_desc' => 'Short Problem Description',
                ],
                'title'    => $title,
                'links'    => [
                    'toc_id' => 'https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='
                ],
                'sortable' => 'no'
            ]
        );
    }

    public static function getTocsFromExtranetDailyReport(string $sso, string $yesterday) {
        $query = <<<SQL
SELECT toc.id AS toc_id,
       toc.dt AS date,
       CONCAT(ass.firstname, ', ', ass.lastname) AS assignee,
       toc.ifactor,
       loc.location AS sso,
       SUBSTRING(mod_logs.comment, 26) AS poster_mail,
       CONCAT(xuser.firstname, ' ', xuser.lastname) AS poster,
       er.sn,
       er.model,
       cus.customer_name,
       er.airport_code,
       toc.status,
       toc.short_desc
FROM toc
    LEFT JOIN people ass ON ass.id = toc.assid
    LEFT JOIN customers cus ON cus.id = toc.cuid
    LEFT JOIN locations loc ON toc.ssoid = loc.id
    LEFT JOIN service er ON er.id = toc.erid
    LEFT JOIN mod_logs ON toc.id = mod_logs.parent_id
    LEFT JOIN extranet_users xuser ON xuser.email = SUBSTRING(mod_logs.comment, 26)
WHERE mod_logs.comment LIKE 'Created from extranet%'
    AND toc.dt > '{$yesterday}'
    AND loc.location = '{$sso}'
    AND toc.status NOT IN ('CLOSED', 'SOLVED')
ORDER BY assignee, toc.ifactor DESC;
SQL;
        $rows = tldUtils::getSqlToAssocArray($query);
        if(empty($rows)) {
            return [];
        }
        return new tldReportColumnar(
            $rows,
            [
                'xItems'   => [
                    'toc_id' => 'TOC#',
                    'assignee' => 'Assignee',
                    'ifactor' => 'IF',
                    'sso' => 'SSO',
                    'poster' => 'Poster',
                    'poster_mail' => 'Poster eMail',
                    'sn' => 'SN',
                    'model' => 'Model',
                    'customer_name' => 'Customer Name',
                    'airport_code' => 'APC',
                    'status' => 'Status',
                    'short_desc' => 'Short Problem Description',
                ],
                'title'    => sprintf('New TOCs Recap Opened By Extranet User - SSO: %s', $sso),
                'links'    => [
                    'toc_id' => 'https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=toc&m[1]=view&id='
                ],
                'sortable' => 'no'
            ]
        );
    }
}

class tldTOCNOT
{

    public $itsID;
    public $itsHeader;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     *
     * @return int
     */
    public function getID()
    {
        return $this->itsID;
    }

    /**
     * @return int
     */
    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    /**
     * @return int
     */
    public function getFileID()
    {
        return $this->itsHeader['fid'];
    }

    /**
     * Check if empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get SELECT mysql statement
     *
     * @return string
     */
    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    toc_not.*,
    file.filename,
    CONCAT(people.firstname,' ',people.lastname) AS poster_fullname
EOF;
    }

    /**
     * Get FROM mysql statement
     *
     * @return string
     */
    public static function getFROM(): string
    {
        return <<<EOF
FROM
    toc_not
    LEFT JOIN file ON file.id=toc_not.fid
    LEFT JOIN people ON people.id=toc_not.uid
EOF;
    }

    /**
     * Get header
     *
     * @return row
     */
    public function getHeader()
    {
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE toc_not.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Insert new NOT
     *
     * @param array $a
     *
     * @return int or string on error
     */
    public static function insert($a)
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Update NOT
     *
     * @param array $a
     * @param array|string $fields (optional)
     *
     * @return int or string on error
     */
    public function update($a, $fields = '')
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Delete NOT
     *
     * @return string on error
     */
    public function delete()
    {
        throw new \Exception('This is no longer used');
    }

    /**
     * Get NOT by constraints
     *
     * @param array $a constraints
     * @param array|string $opt options
     *
     * @return array of rows
     */
    public static function byConstraints($a, $opt = [])
    {
        if (empty($a)) {
            return 'empty parameter';
        }
        // Look for constraints
        if (is_array($a)) {
            $HAVING = 'HAVING ' . tldUtils::constructWhere($a);
        } else {
            $HAVING = "HAVING $a";
        }
        // Look for options
        if (!empty($opt['orderBy'])) {
            $ORDERBY = 'ORDER BY ' . $opt['orderBy'];
        } else {
            $ORDERBY = 'ORDER BY id';
        }
        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * By parent ID
     *
     * @param int $pid
     *
     * @return string on error
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['parent_id' => $pid]);
    }

}

/**
 * Market Intelligence Module class
 *
 * @package SalesAndService
 */
class tldMIM
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
            SELECT
                CONCAT(posters.lastname, ', ', posters.firstname)
                    AS poster_fullname,
                CONCAT(initiators.lastname, ', ', initiators.firstname)
                    AS initiator_fullname,
                mims.*,
                cats.en AS type_fullname,
                cus.customer_name AS cuid_fullname,
                cors.company_name AS cor_fullname
            FROM mim AS mims
                LEFT JOIN people AS posters ON mims.poster=posters.id
                LEFT JOIN people AS initiators ON mims.initiator=initiators.id
                LEFT JOIN customers AS cus ON mims.cuid=cus.id
                LEFT JOIN cor AS cors ON mims.cor_id=cors.id
                LEFT JOIN products_categories AS cats ON mims.type_id=cats.id
            WHERE mims.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'MIM', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'MIM', $module);
    }

    /**
     * @deprecated
     */
    private function insert($p)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     *
     */
    public function update($vars)
    {
        throw new Exception('This is no longer used');
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getPoster()
    {
        return $this->itsHeader['poster'];
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function getTypeList()
    {
        return tldList::optionsByListNameAsListItemListItem('list.mim.status');
    }

    /**
     * Get the latest MIMs
     *
     * @param integer $num
     *
     * @return array
     */
    public static function byLatest($num = 10)
    {
        $query = <<<EOF
		SELECT mims.*,
            cats.en AS type_fullname,
            CONCAT(posters.lastname, ', ', posters.firstname)
                AS poster_fullname,
            CONCAT(initiators.lastname, ', ', initiators.firstname)
                AS initiator_fullname,
                cus.customer_name AS cuid_fullname,
                cors.company_name AS cor_fullname
		FROM mim AS mims
            LEFT JOIN people AS posters ON mims.poster=posters.id
            LEFT JOIN people AS initiators ON mims.initiator=initiators.id
            LEFT JOIN customers AS cus ON mims.cuid=cus.id
            LEFT JOIN cor AS cors ON mims.cor_id=cors.id
            LEFT JOIN products_categories AS cats ON mims.type_id=cats.id
		ORDER BY mims.id DESC
		LIMIT $num
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get list by competitor ID
     *
     * @param int $id
     *
     * @param string $opt
     *
     * @return array
     */
    public function byCompetitorID($id, $opt = [])
    {
        if (empty($id) || !is_numeric($id)) {
            return;
        }
        $a = ['corid' => $id];

        return self::byConstraints($a, $opt);
    }

    /**
     * Get list by customer ID
     *
     * @param int $id
     *
     * @param string $opt
     *
     * @return array
     */
    public function byCustomerID($id, $opt = [])
    {
        $a = ['cuid' => $id];

        return self::byConstraints($a, $opt);
    }

    /**
     *
     * Enter description here ...
     *
     * @param mixed array string $a
     * @param string $opt
     *
     * @return array
     *
     */
    public static function byConstraints($a, $opt = [])
    {
        $HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $LIMIT = '';
        if (!empty($opt['limit']) && is_numeric($opt['limit'])) {
            $LIMIT = ' LIMIT ' . $opt['limit'];
        }

        $query = <<<EOF
SELECT
    mims.*,locations.erp, locations.location, mims.dt,
    DATE_FORMAT(mims.dt, '%Y%m') AS period, dpt.dpt AS department,
    cats.en AS type_fullname,
    CONCAT(posters.lastname, ', ', posters.firstname)
        AS poster_fullname,
    CONCAT(initiators.lastname, ', ', initiators.firstname)
        AS initiator_fullname,
	cus.customer_name AS cuid_fullname,
	cors.company_name AS cor_fullname,
	cors.id as corid
FROM mim AS mims
    LEFT JOIN people AS posters ON mims.poster=posters.id
    LEFT JOIN people AS initiators ON mims.initiator=initiators.id
    LEFT JOIN locations ON locations.id = posters.bu_id
    LEFT JOIN tld_departments AS dpt ON dpt.id=posters.dpt_id
    LEFT JOIN customers AS cus ON mims.cuid=cus.id
    LEFT JOIN cor AS cors ON mims.cor_id=cors.id
    LEFT JOIN products_categories AS cats ON mims.type_id=cats.id
HAVING $HAVING
ORDER BY mims.id DESC
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @deprecated
     */
    public static function search($a)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function byMIMNOTID($notid)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getLog($log_num = 0)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getFiles()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getTasks()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function addLogEntry($id, $comment, $log_num = 0)
    {
        throw new Exception('This is no longer used');
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('MIM', $this->itsID, $type, $item);
    }

    /**
     * @deprecated
     */
    public function getTagCloud($field = 'poster', $num = 20)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function getPrintVersion()
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function create($vars, $file = null)
    {
        throw new Exception('This is no longer used');
    }
}

/**
 * MIM Notification class
 *
 * @package SalesAndService
 */
class tldMIMNOT
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT
    CONCAT(posters.lastname, ', ', posters.firstname)
        AS poster_fullname,
    nots.*,
    cats.en AS type_fullname,
    cus.customer_name AS cuid_fullname,
    cors.company_name AS cor_fullname
FROM mim_not AS nots
    LEFT JOIN people AS posters ON nots.poster=posters.id
    LEFT JOIN customers AS cus ON nots.cuid=cus.id
    LEFT JOIN cor AS cors ON nots.cor_id=cors.id
    LEFT JOIN products_categories AS cats ON nots.type_id=cats.id
WHERE nots.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * @deprecated
     */
    public function notify($id, $msg = '', $opt = '')
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @deprecated
     */
    public function delete($poster)
    {
        throw new Exception('This is no longer used');
    }

    /**
     * @Êeprecated
     */
    public static function insert($p)
    {
        throw new Exception('This is no longer used');
    }

    public function byPoster($uid)
    {
        $c = ['poster' => $uid];

        return self::byConstraints($c);
    }
}

/**
 * Customer Communication Record
 *
 * @package SalesAndService
 */
class tldCCR
{
    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Get header information
     *
     * @return array
     */
    public function getHeader()
    {
        $query = <<<EOF
SELECT
    ccr.*,
    IF(NOW() > dt_eta AND status IN ('TLD', 'CUSTOMER'),
    CONCAT(status, " - LATE"),
    status
    ) AS status_fullname,
    cus.customer_name AS cuid_fullname,
    CONCAT(exu.lastname, ', ', exu.firstname)
        AS exu_id_fullname
FROM ccr
    LEFT JOIN customers AS cus ON ccr.cuid=cus.id
    LEFT JOIN extranet_users AS exu ON ccr.exu_id=exu.id
WHERE
    ccr.id=$this->itsID
EOF;

        return tldUtils::getSqlRowToAssocArray($query);
    }

    /**
     * Get email contacts
     *
     * @return array
     */
    public function getCCREmails()
    {
        $to = [];
        if (!empty($this->itsHeader['email'])) {
            $to[] = $this->itsHeader['email'];
        }
        if (!empty($this->itsHeader['email_cc'])) {
            $to[] = $this->itsHeader['email_cc'];
        }
        if (count($to) === 0) {
            $to[] = 'noreply@tld-gse.com';
        }

        return $to;
    }

    /**
     * Get list of CRT of the CCR customer
     */
    public function getCRTS()
    {
        return tldCRT::byCustomerID($this->itsHeader['cuid']);
    }

    /**
     * Get sales Reps from CRT
     *
     * @return array
     */
    public function getSalesRepEmails()
    {
        $to = [];
        $crts = $this->getCRTS();
        if (count($crts)) {
            foreach ($crts as $crt) {
                $to[] = $crt['sales_rep_email'];
            }
        }

        return $to;
    }

    /**
     * Notify team only
     *
     * @param        $subject
     * @param        $body
     * @param string $file
     *
     * @param string $charset
     *
     * @return bool
     */
    public function notifyTeam($subject, $body, $file = '', $charset = 'ISO-8859-1')
    {
        $reps = $this->getSalesRepEmails();
        $to = implode(',', array_unique($reps));

        return $this->sendEmail($to, $subject, $body, $file, '', $charset);
    }

    /**
     * Notify team and cc customer
     *
     * @param        $subject
     * @param        $body
     * @param string $file
     *
     * @param string $charset
     *
     * @return bool
     */
    public function notifyTeamCCCustomer($subject, $body, $file = '', $charset = 'ISO-8859-1')
    {
        $reps = $this->getSalesRepEmails();
        $to = implode(',', array_unique($reps));
        $emails = $this->getCCREmails();
        $cc = implode(',', array_unique($emails));

        return $this->sendEmail($to, $subject, $body, $file, $cc, $charset);
    }

    /**
     * Notify customer only
     *
     * @param        $subject
     * @param        $body
     * @param string $file
     *
     * @param string $charset
     *
     * @return bool
     */
    public function notifyCustomer($subject, $body, $file = '', $charset = 'ISO-8859-1')
    {
        $emails = $this->getCCREmails();
        $to = implode(',', array_unique($emails));

        return $this->sendEmail($to, $subject, $body, $file, '', $charset);
    }

    /**
     * Notify customer and cc team
     *
     * @param        $subject
     * @param        $body
     * @param string $file
     *
     * @param string $charset
     *
     * @return bool
     */
    public function notifyCustomerCCTeam($subject, $body, $file = '', $charset = 'ISO-8859-1')
    {
        $emails = $this->getCCREmails();
        $to = implode(',', array_unique($emails));
        $reps = $this->getSalesRepEmails();
        $cc = implode(',', array_unique($reps));

        return $this->sendEmail($to, $subject, $body, $file, $cc, $charset);
    }

    /**
     * Generic method to send email notification
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $file
     * @param string $cc
     *
     * @param string $charset
     *
     * @return bool
     */
    public function sendEmail($to, $subject, $body, $file = '', $cc = '', $charset = 'ISO-8859-1')
    {
        return tldUtils::emailAttachment(
            $to,
            'noreply@tld-gse.com',
            $subject,
            $body,
            $file,
            $cc,
            null,
            ['charset' => $charset]
        );
    }

    /**
     * Update CCR header info
     */
    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    /**
     * Insert a new CCR
     *
     * @param array $p key value pairs
     *
     * @return mixed boolean if ok, otherwise string with error message
     */
    public static function insert($p)
    {
        if (empty($p)) {
            return;
        }
        $fields = [
            'model',
            'dscb',
            'dsca',
            'ifactor',
            'exu_id',
            'dt_eta',
            'type',
            'buid',
            'cuid',
            'email',
            'email_cc',
        ];
        $query = <<<EOF
            INSERT INTO ccr
            SET status='PENDING', dt=NOW(),
EOF;
        $query .= tldUtils::getSqlSet(tldUtils::cleanupFormInput($p), $fields);

        return tldUtils::sqlInsert($query);
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getCUID()
    {
        return $this->itsHeader['cuid'];
    }

    public function getStatusAllowed()
    {
        switch ($this->getStatus()) {
            case 'PENDING':
                $result = ['REVIEW', 'CLOSED'];
                break;
            case 'REVIEW':
                $result = ['TLD', 'CUSTOMER'];
                break;
            case 'TLD':
                $result = ['CUSTOMER', 'CLOSED'];
                break;
            case 'CUSTOMER':
                $result = ['TLD', 'CLOSED'];
                break;
            default:
                $result = [];
        }

        return array_combine($result, $result);
    }

    public function changeStatus($status)
    {
        if (empty($this->itsID)) {
            return 'Can not change status, CCR ID not set';
        }
        $SET = $status === 'CLOSED' ? ', dt_closed=NOW()' : '';

        $allowed = $this->getStatusAllowed();
        if (!is_array($allowed)) {
            return 'Could not get list of allowed statuses';
        }
        if (!in_array($status, $allowed, true)) {
            return "Could not change status to $status";
        }
        $query = <<<EOF
UPDATE ccr
SET status=UCASE('$status')
$SET
WHERE id=$this->itsID
LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function changeDate($date)
    {
        if (empty($this->itsID)) {
            return 'Can not change date, CCR ID not set';
        }
        $query = <<<EOF
UPDATE ccr
SET dt_eta='$date'
WHERE id=$this->itsID
LIMIT 1
EOF;

        return tldUtils::sqlQuery($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CCR');
    }

    /**
     * Get associated log entries from mod_log system
     *
     * @return array
     */
    public function getMessages()
    {
        return tldModLog::byParent($this->itsID, 'CCR', 2);
    }

    /**
     * Get associated log entries from mod_file system
     *
     * @return array
     */
    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'CCR');
    }

    /**
     * Get list of linked tasks
     *
     * @return array
     */
    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'CCR', 'ALL');
    }

    /**
     * Add a comment to the log
     *
     * @param        $id
     * @param        $comment
     * @param string $log_num
     *
     * @return bool
     */
    public function addLogEntry($id, $comment, $log_num = '')
    {
        $a['parent_id'] = $this->itsID;
        $a['module'] = 'CCR';
        $a['poster'] = $id;
        $a['comment'] = $comment;
        $a['log_num'] = $log_num;

        return tldModLog::insert($a);
    }

    public function addMessage($id, $msg)
    {
        return $this->addLogEntry($id, $msg, 2);
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('CCR', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'CCR', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'CCR', $module);
    }

    /**
     * Get the latest SPRs
     *
     * @param string $options
     *
     * @return array
     *
     */
    public static function byLatest()
    {
        $query = <<<EOF
SELECT
    ccr.*,
    IF(NOW() > dt_eta AND status IN ('TLD', 'CUSTOMER'),
    CONCAT(status, " - LATE"),
    status
    ) AS status_fullname,
    cus.customer_name AS cuid_fullname,
    CONCAT(exu.lastname, ', ', exu.firstname)
        AS exu_id_fullname
FROM
    ccr
    LEFT JOIN customers AS cus ON ccr.cuid=cus.id
    LEFT JOIN extranet_users AS exu ON ccr.exu_id=exu.id
ORDER BY id DESC
LIMIT 10
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function countByTypeStatus()
    {
        $query = <<<EOF
SELECT
    IF(NOW() > dt_eta AND status IN ('TLD', 'CUSTOMER'),
    CONCAT(status, " - LATE"),
    ccr.status
    ) AS status_fullname,
    ccr.type,
    COUNT(*) AS num
FROM
    ccr
GROUP BY status_fullname, ccr.type
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byTypeStatus($type, $status, $options = '')
    {
        $a = [];
        if ($type !== 'ALL') {
            $a['ccr.type'] = $type;
        }
        if ($status !== 'ALL') {
            $a['status_fullname'] = $status;
        }
        if (isset($options['byCUID'])) {
            $a['cuid'] = $options['byCUID'];
        }

        return self::byConstraints($a);
    }

    public static function byConstraints($constraints, $orderBy = 'ccr.id')
    {
        $where = tldUtils::constructWhere($constraints);
        $orderBy = TldDatabase::escape($orderBy);

        $query = <<<EOF
SELECT
    ccr.*,
    IF(NOW() > dt_eta AND status IN ('TLD', 'CUSTOMER'),
    CONCAT(status, " - LATE"),
    status
    ) AS status_fullname,
    cus.customer_name AS cuid_fullname,
    CONCAT(exu.lastname, ', ', exu.firstname)
        AS exu_id_fullname
FROM ccr
    LEFT JOIN customers AS cus ON ccr.cuid=cus.id
    LEFT JOIN extranet_users AS exu ON ccr.exu_id=exu.id
EOF;
        if ($where) {
            $query .= " HAVING $where";
        }
        $query .= <<<EOF
		ORDER BY $orderBy
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldCSR
{

    public $itsID;
    public $itsHeader;

    public function __construct($id, $lazy = false)
    {
        $this->itsID = $id;
        $this->itsHeader = $lazy ? [] : $this->getHeader();
    }

    /*******************************************
     *      GETTERS
     ******************************************/

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getERSN()
    {
        return $this->itsHeader['sn'];
    }

    public function getERType()
    {
        return $this->itsHeader['type'];
    }

    public function getERModel()
    {
        return $this->itsHeader['model'];
    }

    public function getWorkType()
    {
        return $this->itsHeader['work_type'];
    }

    public function getWorkDate()
    {
        return $this->itsHeader['dt_work'];
    }

    public function getScheduledDate()
    {
        return $this->itsHeader['dt_sche'];
    }

    public function getStatus()
    {
        return $this->itsHeader['status'];
    }

    public function getModuleID()
    {
        return $this->itsHeader['module_id'];
    }

    public function getModule()
    {
        return $this->itsHeader['module'];
    }

    public function getContactID()
    {
        return $this->itsHeader['con_id'];
    }

    public function getSSOID()
    {
        return $this->itsHeader['sso_id'];
    }

    public function getSSOERP()
    {
        return $this->itsHeader['sso_erp'];
    }

    public function getCustomerUserID()
    {
        return $this->itsHeader['customer_id'];
    }

    public function getCustomerUserName()
    {
        return $this->itsHeader['user_customer'];
    }

    public function getShortDesc()
    {
        return $this->itsHeader['short_desc'];
    }

    public function getTechId()
    {
        return $this->itsHeader['tech_id'];
    }

    /*******************************************
     *      CRUD methods
     ******************************************/

    public static function getSELECT(): string
    {
        return <<<EOF
SELECT
    csr.*,
    er.delivery_location,
    er.sn,
    er.cust_asset_num,
    er.model,
    er.type,
    er.dgt_act,
    er.man_location AS factory,
    er.buyer_customer_id,
    (SELECT customer_name FROM customers
        WHERE customers.id=er.buyer_customer_id
    ) AS buyer_customer,
    er.customer_id,
    (SELECT customer_name FROM customers
        WHERE customers.id=er.customer_id
    ) AS user_customer,
    er.dt_commissioned,
    CONCAT(poster.firstname,', ',poster.lastname) AS poster_fullname,
    sso.location AS sso,
    sso.erp AS sso_erp,
    CONCAT(tech.firstname,', ',tech.lastname) AS tech_fullname,
    tech.email AS tech_email,
    CONCAT(contact.firstname,', ',contact.lastname) AS contact_fullname,
    contact.phone AS contact_phone,
    contact.email AS contact_email,
    faq.symptom AS symptom,
    faq.problem AS problem,
    faq.solution AS solution
EOF;
    }

    public static function getFROM(): string
    {
        return <<<EOF
FROM csr
    LEFT JOIN service AS er ON er.id=csr.parent_id
    LEFT JOIN people AS poster ON poster.id=csr.entered_by
    LEFT JOIN locations AS sso ON sso.id=csr.sso_id
    LEFT JOIN people AS tech ON tech.id=csr.tech_id
    LEFT JOIN extranet_users AS contact ON contact.id=csr.con_id
    LEFT JOIN mod_faq AS faq ON faq.parent_id = csr.id AND faq.module = 'CSR'
EOF;
    }

    static function dashboardCountTasksForAST($id)
    {
        $query = <<<SQL
SELECT
   SUM(CASE WHEN DATEDIFF(due_date, NOW()) >= 0 THEN 1 ELSE 0 END) as 'IN PROGRESS',
   SUM(CASE WHEN DATEDIFF(due_date, NOW()) < 0 THEN 1 ELSE 0 END) as 'late'
FROM tld.tasks
 WHERE tasks.assignee = $id
 AND tasks.status IN ('IN PROGRESS', 'OPEN')
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    static function dashboardSeparatedCountTOCForAST($id)
    {
        $query = <<<SQL
SELECT  ifactor,
       (SELECT count(*)
        FROM tld.toc
        WHERE DATEDIFF(dt, NOW()) >= -14
            AND toc.tecid = $id
            AND toc.ifactor = current.ifactor
            AND toc.status in ('IN PROGRESS')) as 'IN PROGRESS',
       (SELECT count(*)
        FROM tld.toc
        WHERE DATEDIFF(dt, NOW()) < -14
            AND toc.tecid = $id
            AND toc.ifactor = current.ifactor
            AND toc.status in ('IN PROGRESS')) as late,
       (SELECT count(*)
        FROM tld.toc
        WHERE toc.tecid = $id
            AND toc.ifactor = current.ifactor
            AND toc.status in ('SUSPENDED')) as SUSPENDED
FROM tld.toc current
WHERE current.tecid = $id
    AND current.status in ('SUSPENDED', 'IN PROGRESS')
group by ifactor
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public function refresh()
    {
        $this->itsHeader = $this->getHeader();
    }

    public function getHeader()
    {
        if (!$this->itsID) {
            return [];
        }
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = "$SELECT $FROM WHERE csr.id=$this->itsID";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function getSurveyResult()
    {
        return tldModKPI::byParent($this->itsID, 'CSR', 'commissioning');
    }

    /**
     * @param $a array|string
     * @param $opt string
     *
     * @return array
     */
    public static function byConstraints($a, $opt = [])
    {
        // Look for constraints
        $constraints = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $where = !empty($constraints) ? "WHERE $constraints" : '';
        // Look for options
        $orderBy = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id';
        $limit = !empty($opt['limit']) ? "LIMIT {$opt['limit']}" : '';

        // Construct query
        $select = self::getSELECT();
        $from = self::getFROM();
        $query = <<<SQL
$select
$from
$where
$orderBy
$limit
SQL;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function search($a, $opt = [])
    {
        $CONSTRAINTS = is_array($a) ? implode('AND', $a) : $a;
        $HAVING = !empty($CONSTRAINTS) ? "HAVING $CONSTRAINTS" : '';

        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id';
        $LIMIT = !empty($opt['limit']) ? "LIMIT {$opt['limit']}" : '';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
$HAVING
$ORDERBY
$LIMIT
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }


    /**
     * @throws Exception
     */
    public static function insert($a)
    {
        throw new Exception('This is no longer used');
    }

    public function update($a, $fields = '')
    {
        if (empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE csr SET $SET WHERE id=$this->itsID";

        return tldUtils::sqlQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM csr WHERE id=$this->itsID LIMIT 1";

        return tldUtils::sqlExecute($query);
    }

    /**
     * @throws Exception
     */
    public function duplicate($a = [])
    {
        throw new Exception('This is no longer used');
    }

    /*******************************************
     *   STATIC / Reference methods
     ******************************************/

    public static function getWorkTypeList()
    {
        return tldList::optionsByListNameAsListItemListItem('list.service.activity.type');
    }

    public static function getStatusList()
    {
        return [
            'PENDING' => 'PENDING',
            'IN PROGRESS' => 'IN PROGRESS',
            'COMPLETED' => 'COMPLETED',
            'CLOSED' => 'CLOSED',
        ];
    }

    public static function getOpenStatusList()
    {
        return ['PENDING', 'IN PROGRESS'];
    }

    public static function getClosedStatusList()
    {
        return ['COMPLETED', 'CLOSED'];
    }

    public static function getModuleList()
    {
        return [
            '' => '',
            'TOC' => 'TOC',
            'SB3' => 'SB3',
            'SRVO' => 'SRVO',
        ];
    }

    public static function getBillToList()
    {
        return ['CUSTOMER' => 'CUSTOMER'] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
    }

    /*******************************************
     *   LOGIC methods
     *******************************************/

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function isStatusValid($status)
    {
        return in_array($status, self::getStatusList(), true);
    }

    public function isClosed()
    {
        return in_array($this->getStatus(), self::getClosedStatusList(), true);
    }

    public function setERHourMeter($hours)
    {
        $er = new tldEquipment($this->getParentID());
        if ($er->isEmpty()) {
            return "ER#{$this->getParentID()} not found";
        }

        return $er->setHourMeter($hours, 'CSR', $this->getID());
    }

    public function getAllowedStatus()
    {
        $result = [];
        switch ($this->getStatus()) {
            case 'PENDING':
                $result = ['IN PROGRESS'];
                break;
            case 'IN PROGRESS':
                $result = ['COMPLETED'];
                break;
            case 'COMPLETED':
                $result = ['CLOSED'];
                break;
            case 'CLOSED':
                $result = ['IN PROGRESS', 'COMPLETED'];
                break;
        }

        return $result;
    }

    /**
     * @param $newStatus
     * @param int $uid
     * @param bool $force
     * @param bool $customerNotification
     *
     * @return string|void
     */
    public function updateStatus($newStatus, $uid = 0, $force = false, $customerNotification = true)
    {
        if (!$this->isStatusValid($newStatus)) {
            return "Status $newStatus not valid";
        }

        // PRE checks

        // If status change use normal workflow or is forced
        if (!$force) {
            // normal workflow, check if new status is allowed
            $allowedStatus = $this->getAllowedStatus();
            if (!in_array($newStatus, $allowedStatus)) {
                return "Status $newStatus not allowed from actual status ({$this->getStatus()})";
            }
        }

        switch ($this->getModule()) {
            case 'SB3':
                switch ($newStatus) {
                    case 'COMPLETED':
                    case 'CLOSED':
                        // check if can change status depending
                        // of SB LINE WORKFLOW + Process module in progress
                        $resp = tldSB_Line::isTriggerStatusChangeAllowed('CSR', $this->itsID);
                        if (is_string($resp)) {
                            return $resp;
                        }
                        break;
                }
                break;
        }

        // Update status
        $e = $this->update(['status' => $newStatus]);
        if (is_string($e)) {
            return $e;
        }
        $this->addLogEntry($uid, $newStatus);

        // POST actions
        // -- by status
        switch ($newStatus) {
            case 'COMPLETED':
                $date = date('Y-m-d H:i:s');
                $erid = (int) $this->getParentID();
                $this->update(['dt_completed' => $date]);
                if ($erid !== 0 && $this->getWorkType() === 'Commissioning') {
                    $er = new tldEquipment($erid);
                    $er->setCommissioningDate($date);
                }
                break;
            case 'CLOSED':
                $this->update(['dt_closed' => date('Y-m-d H:i:s')]);
                break;
        }
        //-- by module process associated
        switch ($this->getModule()) {
            case 'SRVO':
                switch ($newStatus) {
                    case 'COMPLETED':
                    case 'CLOSED':
                        // Look for TOC linked
                        $links = $this->getLinksToHere();
                        foreach ($links as $link) {
                            if ($link['module'] !== 'TOC') {
                                continue;
                            }
                            $tocid = $link['parent_id'];
                        }
                        if (empty($tocid)) {
                            break;
                        }
                        $toc = new tldTOC($tocid);
                        // if not found OR already SOLVED or CLOSED
                        if ($toc->isEmpty() || in_array($toc->getStatus(), ['SOLVED', 'CLOSED'])) {
                            break;
                        }
                        // Check if TOC has other main process not closed
                        if (!$toc->isItsMainProcessClosed()) {
                            break;
                        }
                        $error = $toc->changeStatus('SOLVED', true, $customerNotification);
                        if (is_string($error)) {
                            break;
                        }
                        $toc->addLogEntry($uid, 'SOLVED from CSR#' . $this->getID());
                        break;
                }
                break;
            case 'TOC':
                switch ($newStatus) {
                    case 'COMPLETED':
                        $tocid = $this->getModuleID();
                        $toc = new tldTOC($tocid);
                        if ($toc->isEmpty()) {
                            break;
                        }
                        // Check if TOC has other main process not closed
                        if (!$toc->isItsMainProcessClosed()) {
                            break;
                        }
                        $errorStatus = $toc->changeStatus('SOLVED', false, $customerNotification);
                        $errorUnitOpStatus = $toc->updateUnitOperationalStatus('MCF');

                        if (is_string($errorStatus) || is_string($errorUnitOpStatus)) {
                            break;
                        }
                        $toc->addLogEntry($uid, 'SOLVED from CSR#' . $this->getID());
                        break;
                }
                break;
            case 'SB3':
                switch ($newStatus) {
                    case 'COMPLETED':
                    case 'CLOSED':
                        tldSB_Line::triggerStatusChange('CSR', $this->itsID);
                        break;
                }
                break;
        }

        // return status change response
        return $e;
    }

    public static function getCSRByConstraints($a)
    {
        $CONSTRAINTS = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $HAVING = !empty($CONSTRAINTS) ? "HAVING $CONSTRAINTS" : '';
        $query = <<<EOF
SELECT
    csr.*,
    er.delivery_location,
    er.sn,
    er.cust_asset_num,
    er.model,
    er.type,
    er.dgt_act,
    er.man_location AS factory,
    er.buyer_customer_id,
    (SELECT customer_name FROM customers
        WHERE customers.id=er.buyer_customer_id
    ) AS buyer_customer,
    er.customer_id,
    (SELECT customer_name FROM customers
        WHERE customers.id=er.customer_id
    ) AS user_customer,
    er.dt_commissioned,
    CONCAT(poster.firstname,', ',poster.lastname) AS poster_fullname,
    sso.location AS sso,
    sso.erp AS sso_erp,
    CONCAT(tech.firstname,', ',tech.lastname) AS tech_fullname,
    tech.email AS tech_email,
    CONCAT(contact.firstname,', ',contact.lastname) AS contact_fullname,
    contact.phone AS contact_phone,
    contact.email AS contact_email,
    part.pn,
    part.dsc,
    link.type,
    (SELECT CONCAT(status, ' <i>(','SPR#', spr.id,')</i>')
    FROM spr WHERE link.item=spr.id) AS spr_status
FROM csr
  LEFT JOIN service AS er ON er.id=csr.parent_id
  LEFT JOIN people AS poster ON poster.id=csr.entered_by
  LEFT JOIN locations AS sso ON sso.id=csr.sso_id
  LEFT JOIN people AS tech ON tech.id=csr.tech_id
  LEFT JOIN extranet_users AS contact ON contact.id=csr.con_id
  LEFT JOIN mod_parts AS part ON csr.id=part.parent_id AND part.module = 'CSR'
  LEFT JOIN mod_links AS link ON csr.id=link.parent_id AND link.module = 'CSR' AND link.type ='SPR'
$HAVING
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getCommissioningResultsByConstraints($a, $opt = null)
    {
        $ANDWHERE = is_array($a) ? tldUtils::constructWhere($a) : " AND $a";
        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY id';

        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT,
    IF(service_serials.component LIKE 'ENGINE', service_serials.serial, NULL) AS serial,
    (SELECT val FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'aspect'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS aspect,
    (SELECT comments FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'aspect'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS aspect_desc,
    (SELECT val FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'conformity'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS conformity,
    (SELECT comments FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'conformity'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS conformity_desc,
    (SELECT val FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'operational'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS operational,
    (SELECT comments FROM mod_kpi WHERE name LIKE 'commissioning' AND key1 LIKE 'operational'
        AND module LIKE 'CSR' AND parent_id=csr.id LIMIT 1
    ) AS operational_desc
$FROM
    LEFT JOIN service_serials ON service_serials.parent_id = er.id AND service_serials.component LIKE 'ENGINE'
WHERE
    csr.work_type LIKE 'Commissioning'
    AND csr.status IN('COMPLETED','CLOSED')
$ANDWHERE
GROUP BY csr.id
$ORDERBY
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }

    public static function getMappingDataFromServiceOrder($so)
    {
        // Get ER
        $erid = 0;
        $sosn = $so->getInstallationSN();
        if (!empty($sosn)) {
            $rows = tldEquipment::byConstraints(['sn' => $sosn]);
            $erid = $rows[0]['id'];
        }

        $er = new tldEquipment($erid);
        // Get Poster
        $soPoster = $so->getPosterUser();
        $uid = 0;
        if (!empty($soPoster)) {
            $rows = tldUser::byConstraints(['baan_id' => $soPoster]);
            $uid = $rows[0]['id'];
        }
        $poster = new tldUser($uid);
        // Get Tech
        $uid = 0;
        $soTech = $so->getTechEmail();
        if (!empty($soTech)) {
            $rows = tldUser::byConstraints(['email' => $so->getTechEmail()]);
            $uid = $rows[0]['id'];
        }
        $tech = new tldUser($uid);
        // Get customer contact
        $uid = 0;
        $soContact = $so->getContactEmail();
        if (!empty($soContact)) {
            $rows = extranetUser::byConstraints(['email' => $soContact]);
            $uid = $rows[0]['id'];
        }
        $contact = new extranetUser($uid);
        // Apc
        $apc = '';
        $soLoc = $so->getLocation();
        if (!empty($soLoc)) {
            $location = explode('-', $soLoc);
            $apc = end($location);
        }
        // Work date
        $work_date = new DateTime($so->getWorkDate());
        // Description
        $description = $so->getDescription();
        if (!empty($so->itsHeader['t_cjob'])) {
            $description = $so->itsHeader['job_desc'];
        }
        // Map fields
        $csrData = [
            'parent_id' => $er->getID(),
            'entered_by' => $poster->getID(),
            'sso_id' => tldLocation::getIDByERP($so->getERP()),
            'work_type' => $so->getOrderSeriesDescription(),
            'tech_id' => $tech->getID(),
            'dt_work' => $work_date->format('Y-m-d'),
            'dt_sche' => $work_date->format('Y-m-d'),
            'con_id' => $contact->getID(),
            'apc' => $apc,
            'hourmeter' => $so->getHourmeter(),
            'short_desc' => $description,
            'int_desc' => $description,
            'ext_desc' => $description,
            'erp_inv' => null,
            'module' => 'SRVO',
            'module_id' => $so->getID(),
            'ship_to' => null,
            'send_tech' => 'Y',
        ];

        return $csrData;
    }

    public function createSPR($user)
    {
        throw new Exception('This page has been migrated and should not be used anymore');
    }

    public function getSPRList()
    {
        $linksFrom = array_column($this->getLinksFromHere('SPR'), 'item', 'item');
        $linksTo = array_column($this->getLinksToHere('SPR'), 'item', 'item');
        $sprIDs = array_merge($linksFrom, $linksTo);

        return !empty($sprIDs) ? tldSPR::byConstraints('id IN(' . implode(',', $sprIDs) . ')') : [];
    }

    public function getMemberRecipients()
    {
        $to = [];
        foreach ($this->getMembers() as $member) {
            if (empty($member['email'])) {
                continue;
            }
            $to[] = $member['email'];
        }

        return $to;
    }

    public function notifyMembers($from, $subject, $body)
    {
        $to = $this->getMemberRecipients();
        if (empty($to)) {
            return;
        }

        return $this->sendEmail($to, $from, $subject, $body);
    }

    public function getDefaultEmailSubject()
    {
        return "CSR#{$this->getID()}, {$this->getCustomerUserName()}, {$this->getERModel()}, {$this->getERSN()}";
    }

    public function sendEmail($to, $from, $subject, $body, $cc = '')
    {
        $body .= $this->getPrintVersion();

        return tldUtils::emailAttachment(
            $to,
            $from,
            $subject,
            $body,
            null,
            $cc
        );
    }

    public function getSurveyResultView()
    {
        $report = new tldReportColumnar(
            $this->getSurveyResult(),
            [
                'xItems' => [
                    'key1' => 'Survey Content',
                    'val' => 'Value (5 max)',
                    'comments' => 'Comments',
                ],
                'title' => 'Survey Results',
                'sortable' => 'No',
                'showzero' => true,
            ]
        );

        return $report->fetch();
    }

    public function getPrintVersion()
    {
        $print = null;
        // add survey if commissioning
        if ($this->getWorkType() === 'Commissioning') {
            $print = $this->getSurveyResultView();
        }
        $report = new tldAssocTable(
            $this->itsHeader,
            [
                'id' => 'CSR#',
                'dt' => 'Date',
                'poster_fullname' => 'Entered by',
                'sso' => 'SSO',
                'status' => 'Status',
                'user_customer' => 'USER customer',
                'spacer1' => '---spacer---',
                'work_type' => 'Work Type',
                'tech_fullname' => 'Technician',
                'dt_sche' => 'Scheduled Date',
                'dt_work' => 'Work date',
                'erp_inv' => 'ERP Invoice#',
                'ship_to' => 'Ship to',
                'spacer2' => '---spacer---',
                'short_desc' => 'Short description',
                'int_desc' => 'Internal description<br>(Visible by TLD)',
                'ext_desc' => 'External description<br>(Visible by TLD and Customer)',
                'spacer3' => '---spacer---',
                'sn' => 'SN',
                'model' => 'Model',
                'apc' => 'APC',
                'hourmeter' => 'Hourmeter',
                'spacer4' => '---spacer---',
                'module' => 'Module',
                'module_id' => 'Ref#',
            ],
            ['title' => 'CSR Details']
        );
        $print .= $report->fetch();
        // add parts
        $report = new tldReportColumnar(
            $this->getParts(),
            [
                'xItems' => [
                    'pn' => 'Part Number',
                    'dsc' => 'Description',
                    'qty' => 'Quantity',
                ],
                'title' => 'Parts',
                'showItemNumbers' => true,
                'sortable' => 'no',
            ]
        );
        $print .= $report->fetch();
        // add contact
        $con = new extranetUser($this->getContactID());
        $report = new tldAssocTable(
            $con->itsDetails,
            [
                'id' => 'Contact ID#',
                'customer_name' => 'Customer Name',
                'fullname' => 'Full Name',
                'division' => 'Division',
                'department' => 'Dept',
                'title' => 'Title',
                'email' => 'Email',
                'phone' => 'Tel',
                'direct_phone' => 'Tel, Direct',
                'fax' => 'Fax',
                'shipping_address' => 'Address, Shipping',
            ],
            ['title' => 'Contact Details']
        );
        $print .= $report->fetch();

        return $print;
    }

    public static function getCommissioningSurveyQuestions(): array
    {
        return [
            'aspect' => 'General aspect of the unit as presented on site',
            'conformity' => 'Conformity/compliance with the specifications (including options)',
            'operational' => 'Unit operational at first start',
        ];
    }

    public static function getCommissioningSurveyValues(): array
    {
        return [
            0 => 'Not good at all or non-compliant at all (0%)',
            1 => 'Not good or non-compliant (with major issues) (20%)',
            2 => 'Not good or non-compliant (with minor issues) (40%)',
            3 => 'Unit is operational (with major issues) (60%)',
            4 => 'Unit is operational (with minor issues) (80%)',
            5 => '100% correct',
        ];
    }

    public function processCommissioningSurveyResults($user, $a)
    {
        $possibleValues = $this->getCommissioningSurveyValues();
        $maxValues = count($possibleValues) - 1;
        // Add survey results in log
        $log = '<p>Survey results:<br>';
        $values = [];
        foreach ($a as $key => $val) {
            if (is_numeric($val)) {
                $log .= ucfirst($key) . ": $val/$maxValues<br>";
            }
        }
        // Add kpi - add comments for each survey
        foreach ($a as $key => $val) {
            $values = array_values($val);
            foreach ($val as $i => $j) {
                if (is_numeric($j)) {
                    $log .= ucfirst($i) . ": $j/$maxValues<br>";
                }
                $this->addKPI('commissioning', $a['survey'][$i], $a['comments'][$i], ['key1' => $i]);
            }
            break;
        }
        $values = implode('/', $values);

        $log .= '</p>';
        $this->addLogEntry($user->getID(), $log);
        // Send email notification
        $TO = [];
        // -- factory recipients
        $erpFactory = tldLocation::getERPByLocation($this->itsHeader['factory']);
        $pmGrp = new tldGroup('role_PM', $erpFactory);
        $TO = array_merge($TO, $pmGrp->getEmailList());
        $qamGrp = new tldGroup('role_QAM', $erpFactory);
        $TO = array_merge($TO, $qamGrp->getEmailList());
        $qeGrp = new tldGroup('role_QE', $erpFactory);
        $TO = array_merge($TO, $qeGrp->getEmailList());
        $rmeGrp = new tldGroup('role_RME', $erpFactory);
        $TO = array_merge($TO, $rmeGrp->getEmailList());
        $emGrp = new tldGroup('role_EM', $erpFactory);
        $TO = array_merge($TO, $emGrp->getEmailList());
        $psmGrp = new tldGroup('role_PSM', $erpFactory);
        $TO = array_merge($TO, $psmGrp->getEmailList());
        $pseGrp = new tldGroup('role_PSE', $erpFactory);
        $TO = array_merge($TO, $pseGrp->getEmailList());
        $psaGrp = new tldGroup('role_PSA', $erpFactory);
        $TO = array_merge($TO, $psaGrp->getEmailList());
        $cooGrp = new tldGroup('role_COO', $erpFactory);
        $TO = array_merge($TO, $cooGrp->getEmailList());
        $rcooGrp = new tldGroup('role_RCOO', $erpFactory);
        $TO = array_merge($TO, $rcooGrp->getEmailList());
        $rceoGrp = new tldGroup('role_CEO', $erpFactory);
        $TO = array_merge($TO, $rceoGrp->getEmailList());
        $pleGrp = new tldGroup('ROLE_PLE', $erpFactory);
        $TO = array_merge($TO, $pleGrp->getEmailList());
        $peGrp = new tldGroup('ROLE_PE', $erpFactory);
        $TO = array_merge($TO, $peGrp->getEmailList());
        $seeGrp = new tldGroup('ROLE_SEE', $erpFactory);
        $TO = array_merge($TO, $seeGrp->getEmailList());
        $psGrp = new tldGroup('ROLE_PS', $erpFactory);
        $TO = array_merge($TO, $psGrp->getEmailList());
        $cooGrp = new tldGroup('role_COO', 900);
        $TO = array_merge($TO, $cooGrp->getEmailList());
        $cmoGrp = new tldGroup('role_CMO', 900);
        $TO = array_merge($TO, $cmoGrp->getEmailList());
        $csdGrp = new tldGroup('role_CSD', 900);
        $TO = array_merge($TO, $csdGrp->getEmailList());
        // -- SSO recipients
        $csmGrp = new tldGroup('role_CSM', $this->getSSOERP());
        $TO = array_merge($TO, $csmGrp->getEmailList());
        $evpGrp = new tldGroup('role_EVP', $this->getSSOERP());
        $TO = array_merge($TO, $evpGrp->getEmailList());
        $ceoGrp = new tldGroup('role_CEO', $this->getSSOERP());
        $TO = array_merge($TO, $ceoGrp->getEmailList());
        // ASM from CRT
        $cc = [];
        $cc[] = $this->itsHeader['tech_email'];
        $crtList = tldCRT::byCustomerIDSSOID(
            $this->getCustomerUserID(),
            $this->getSSOID()
        );
        foreach ($crtList as $crtVal) {
            if (empty($crtVal['sales_rep_email']) || in_array($crtVal['sales_rep_email'], $cc)) {
                continue;
            }
            $cc[] = $crtVal['sales_rep_email'];
        }
        // Prepare contents
        $subject = "CSR#$this->itsID Completed - $values - ER#{$this->itsHeader['sn']} commissioned";
        $message = <<<EOF
<p>ER#{$this->itsHeader['sn']} ({$this->itsHeader['model']} from {$this->itsHeader['factory']}) commissioned for customer {$this->itsHeader['user_customer']}</p>

<p><a href="https://www.tld-gse.com/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=$this->itsID">Click here to view details</a></p>
EOF;
        // Send email
        $return = $this->sendEmail(
            $TO,
            $user->getEmail(),
            $subject,
            $message,
            $cc
        );
    }

    /*******************************************
     *   TLD MOD methods
     ******************************************
     *
     * @param      $name
     * @param      $val
     * @param      $comments
     * @param null $opt
     *
     * @return mixed
     */

    public function addKPI($name, $val, $comments, $opt = null)
    {
        return tldModKPI::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'CSR',
                'key1' => $opt['key1'],
                'name' => $name,
                'val' => $val,
                'comments' => $comments,
            ]
        );
    }

    public function getKPIs($name)
    {
        return tldModKPI::byParent($this->itsID, 'CSR', $name);
    }

    /**
     * Get CSR KPI By Constraints
     *
     * @param mixed array string $a
     * @param array $opt
     *                    ['legend'] string
     *                    ['periodConstraints'] string (default is last 12 months)
     *
     * @return array kpi values
     */
    public static function getKPIDataByConstraints($a, $opt = null)
    {
        $data = [];
        $DATE = 'service.dgt_act';
        if (isset($opt['type']) && $opt['type'] === 'bySSO') {
            $DATE = 'csr.dt';
        }
        $WHERE = is_array($a) ? tldUtils::constructWhere($a) : $a;
        if (!empty($WHERE)) {
            $WHERE = "AND $WHERE";
        }

        $WHERE_PERIOD = !empty($opt['periodConstraints']) ? $opt['periodConstraints'] : "PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT($DATE, '%Y%m')) BETWEEN 1 AND 12";

        $queryCQ = <<<EOF
SELECT
	DATE_FORMAT($DATE, '%Y%m') AS xval,
	ROUND(SUM(
	    CASE
	        WHEN
	        (SELECT COUNT(*) FROM mod_kpi LEFT JOIN csr csr_kpi ON mod_kpi.parent_id = csr_kpi.id WHERE mod_kpi.key1 = 'solIncomplete' AND csr_kpi.id = csr.id) > 0 
	            AND mod_kpi.key1 = 'conformity'
	        THEN 5
	        ELSE mod_kpi.val
	    END
	)/COUNT(distinct csr.id),1) as val,
	COUNT(distinct csr.id) AS csr_count,
	mod_kpi.key1 as component
FROM
	mod_kpi
	LEFT JOIN csr ON mod_kpi.parent_id = csr.id
	LEFT JOIN service ON service.id = csr.parent_id
WHERE
	mod_kpi.module ='CSR' AND csr.work_type = 'Commissioning' AND mod_kpi.key1 <> 'solIncomplete'
	AND $WHERE_PERIOD
	$WHERE
GROUP BY
	mod_kpi.key1, DATE_FORMAT( $DATE, '%Y%m' )
EOF;

        $rowsCQ = tldUtils::getSqlToAssocArray($queryCQ);
        $data['cq'] = $rowsCQ;

        $queryACR = <<<EOF
    		SELECT
	DATE_FORMAT($DATE, '%Y%m') AS xval,
	ROUND((SUM(CASE
	        WHEN
	        (SELECT COUNT(*) FROM mod_kpi LEFT JOIN csr csr_kpi ON mod_kpi.parent_id = csr_kpi.id WHERE mod_kpi.key1 = 'solIncomplete' AND csr_kpi.id = csr.id) > 0 
	            AND mod_kpi.key1 = 'conformity'
	        THEN 5
	        ELSE mod_kpi.val
	    END)/(COUNT(distinct csr.id)*15)*100),1) as val,
	COUNT(distinct csr.id) AS csr_count
FROM
	mod_kpi
	LEFT JOIN csr ON mod_kpi.parent_id = csr.id
	LEFT JOIN service ON service.id = csr.parent_id
WHERE
	mod_kpi.module ='CSR' AND csr.work_type = 'Commissioning' AND mod_kpi.key1 <> 'solIncomplete'
	AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT( $DATE, '%Y%m' )) BETWEEN 1 AND 12
	$WHERE
GROUP BY
	DATE_FORMAT( $DATE, '%Y%m' )
EOF;
        $rowsACR = tldUtils::getSqlToAssocArray($queryACR);
        $data['acr'] = $rowsACR;

        return $data;
    }


    /**
     * Get CSR KPI for SOL Incomplete
     *
     * @param mixed array string $a
     * @return      array kpi values
     */
    public static function getKPIDataForSOLIncomplete(array $a)
    {
        $WHERE = '';
        if (!empty($a)) {
            $WHERE = 'AND ' . tldUtils::constructWhere($a);
        }

        $query = <<<EOF
SELECT
	DATE_FORMAT(csr.dt, '%Y%m') AS xval,
	ROUND(SUM(CASE WHEN mod_kpi.key1 = 'solIncomplete' THEN 1 ELSE 0 END)/COUNT(distinct csr.id)*100, 1) as val,
	COUNT(distinct csr.id) AS csr_count
FROM
	mod_kpi
	LEFT JOIN csr ON mod_kpi.parent_id = csr.id
	LEFT JOIN service ON service.id = csr.parent_id
WHERE
	mod_kpi.module ='CSR'
	AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(csr.dt, '%Y%m')) BETWEEN 1 AND 12
    $WHERE
GROUP BY
	DATE_FORMAT(csr.dt, '%Y%m')
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * Get CSR KPI data for FDA and DPA
     *
     * @param string $begin
     * @param string $end
     * @param mixed array string $a
     *
     * @return array kpi values
     */
    public static function getKPIDataFDAAndDPA($begin, $end, $a)
    {
        $where = empty($a) ? '' : ' AND ' . tldUtils::constructWhere($a);

        $query = <<<EOF
SELECT
  DATE_FORMAT(csr.dt, '%Y-%m') AS xValue,
  ROUND(AVG(CASE WHEN csr.send_tech = 'Y' THEN DATEDIFF(csr.dt_sche, toc.dt) END), 1) AS FDA,
  ROUND(AVG(CASE WHEN csr.dt_work IS NULL THEN DATEDIFF(csr.dt_completed, csr.dt_sche) ELSE DATEDIFF(csr.dt_work, csr.dt_sche) END), 1) AS DPA,
  ROUND(COUNT(CASE WHEN csr.dt_work IS NULL 
                THEN DATEDIFF(csr.dt_completed, csr.dt_sche) 
                ELSE DATEDIFF(csr.dt_work, csr.dt_sche)
              END = 0) / COUNT(csr.send_tech = 'Y') * 100, 1) AS percentDPA
FROM csr
  LEFT JOIN service ON service.id = csr.parent_id
  LEFT JOIN toc ON toc.id = csr.module_id
  LEFT JOIN warranty AS wc ON wc.serial_number = service.sn
WHERE
  csr.module = 'TOC' AND
  DATE_FORMAT(csr.dt, '%Y-%m') BETWEEN '$begin' AND '$end'
  $where
GROUP BY
  xValue
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function kpi_perfectCommissioning_byPeriodByFactory($from, $to, $factory, $models = [], $rollingResults = false, $gtDateFilter = true)
    {
        $GROUP_BY = 'GROUP BY factory, month';
        if ($rollingResults) {
            $GROUP_BY = 'GROUP BY factory';
        } elseif ($rollingResults && $factory === 'ALL') {
            $GROUP_BY = '';
        }

        $WHERE = $factory !== 'ALL' ? " AND service.man_location LIKE '$factory'" : '';
        if (!empty($models)) {
            $WHERE = sprintf('%s AND service.model IN ("%s")', $WHERE, implode('", "', $models));
        }

        if ($gtDateFilter) {
            $WHERE_DATE = "AND PERIOD_DIFF(DATE_FORMAT(service.dgt_act,'%Y%m'),DATE_FORMAT('$from','%Y%m')) >= 0
            AND PERIOD_DIFF(DATE_FORMAT(service.dgt_act,'%Y%m'),DATE_FORMAT('$to','%Y%m')) <= 0 ";
        } else {
            $WHERE_DATE = "AND PERIOD_DIFF(DATE_FORMAT(service.dt_commissioned,'%Y%m'),DATE_FORMAT('$from','%Y%m')) >= 0 ";
        }

        $query = <<<EOF
SELECT
    service.man_location AS factory,
    DATE_FORMAT(service.dgt_act, '%Y%m') AS month,
    (
        SUM(
            IF(
              (SELECT COUNT(*) FROM mod_kpi 
                WHERE module='CSR' AND parent_id=csr.id AND name='commissioning' AND val=5 AND key1 != 'solIncomplete'
              ) = 3,
              1, 0
            )
        ) * 100 / COUNT(*)
    ) AS val,
    COUNT(*) AS nb_commissioned,
    GROUP_CONCAT(service.sn) AS list_sn
FROM
    csr 
    LEFT JOIN service ON service.id = csr.parent_id
WHERE
    csr.work_type='Commissioning'
    $WHERE_DATE
    AND (SELECT COUNT(*) FROM mod_kpi WHERE module='CSR' AND parent_id=csr.id AND name='commissioning' AND key1 != 'solIncomplete')>0
    $WHERE
$GROUP_BY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public function addLogEntry($uid, $comment, $num_log = 0)
    {
        return tldModLog::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'CSR',
                'poster' => $uid,
                'comment' => $comment,
                'log_num' => $num_log,
            ]
        );
    }

    public function getLog()
    {
        return tldModLog::byParent($this->itsID, 'CSR');
    }

    public function getTasks()
    {
        return tldTask::byParent($this->itsID, 'CSR', 'ALL');
    }

    public function getFiles()
    {
        return tldModFile::byParent($this->itsID, 'CSR');
    }

    public function getCustomerFiles()
    {
        return tldModFile::byParent($this->itsID, 'CSR', 1); // Level 1 for customer files
    }

    public function addLinkTo($type, $item)
    {
        return tldModLink::insert('CSR', $this->itsID, $type, $item);
    }

    public function getLinksFromHere($type = '')
    {
        return tldModLink::byParent($this->itsID, 'CSR', $type);
    }

    public function getLinksToHere($module = '')
    {
        return tldModLink::byItem($this->itsID, 'CSR', $module);
    }

    public function getCosts($dcur = 'USD')
    {
        return tldModCost::byParent($this->itsID, 'CSR', $dcur);
    }

    public function getTotalCostsByType($dcur = 'USD')
    {
        return tldModCost::totalsByParent($this->itsID, 'CSR', $dcur);
    }

    public function addFAQ($a)
    {
        return tldModFAQ::insert(
            [
                'parent_id' => $this->itsID,
                'module' => 'CSR',
                'symptom' => $a['symptom'],
                'problem' => $a['problem'],
                'solution' => $a['solution'],
            ]
        );
    }

    public function getFAQ()
    {
        return tldModFAQ::byParent($this->itsID, 'CSR');
    }

    public function getParts()
    {
        return tldModParts::byParent($this->itsID, 'CSR');
    }

    public function getLabors()
    {
        return tldModLabor::byParent($this->itsID, 'CSR');
    }

    public function getMembers()
    {
        return tldModMember::byParent($this->itsID, 'CSR');
    }

    /*******************************************
     *   STATS & LISTING methods
     ******************************************
     *
     * @param null $a
     *
     * @return array
     */

    public static function countBySSOStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    csr.status AS status,
    sso.location AS sso,
    COUNT(*) AS num
FROM csr
    LEFT JOIN locations AS sso ON sso.id=csr.sso_id
$WHERE
GROUP BY
    status, sso
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string $sso
     * @param string $status
     * @param $a array|string
     *
     * @return array
     */
    public static function bySSOStatusByConstraints($sso, $status, $a = null)
    {
        $WHERE = [];
        if ('ALL' !== $sso) {
            $WHERE[] = "sso.location LIKE '$sso'";
        }
        if ('ALL' !== $status) {
            $WHERE[] = "csr.status LIKE '$status'";
        }
        // construct constraints if applicable
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = implode(' AND ', $WHERE);
        }

        return self::byConstraints($WHERE);
    }

    public static function countByModuleStatusByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<EOF
SELECT
    status,
    module,
    COUNT(*) AS num
FROM csr
$WHERE
GROUP BY
    status, module
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }

    public static function byModuleStatusByConstraints($module, $status, $a = null)
    {
        $WHERE = [];
        if ($module !== 'ALL') {
            $WHERE[] = "csr.module LIKE '$module'";
        }
        if ($status !== 'ALL') {
            $WHERE[] = "csr.status LIKE '$status'";
        }
        // construct constraints if applicable
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = implode(' AND ', $WHERE);
        }

        return self::byConstraints($WHERE);
    }

    /**
     * @param $a array|string
     *
     * @return array
     */
    public static function countByStatusWorkTypeByConstraints($a = null)
    {
        $WHERE = '';
        if (is_array($a)) {
            $WHERE = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = "WHERE $WHERE";
        }
        $query = <<<SQL
SELECT
    status,
    work_type,
    COUNT(*) AS num
FROM csr
$WHERE
GROUP BY
    status, work_type
SQL;

        return tldUtils::getSqlToAssocArray($query);
    }

    /**
     * @param string $status
     * @param string $workType
     * @param $a array|string
     *
     * @return array
     */
    public static function byStatusWorkTypeByConstraints($status, $workType, $a = null)
    {
        $WHERE = [];
        if ('ALL' !== $workType) {
            $WHERE[] = "csr.work_type LIKE '$workType'";
        }
        if ('ALL' !== $status) {
            $WHERE[] = "csr.status LIKE '$status'";
        }
        // construct constraints if applicable
        if (is_array($a)) {
            $WHERE[] = tldUtils::constructWhere($a);
        } elseif (!empty($a)) {
            $WHERE[] = $a;
        }
        if (!empty($WHERE)) {
            $WHERE = implode(' AND ', $WHERE);
        }

        return self::byConstraints($WHERE);
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public static function byLatest($limit = 10)
    {
        return self::byConstraints('1=1', ['limit' => $limit, 'orderBy' => 'id DESC']);
    }

    /**
     * @param int $pid
     *
     * @return array
     */
    public static function byParent($pid)
    {
        return self::byConstraints(['csr.parent_id' => $pid]);
    }

    public static function getCostsTotalsByConstraints($cur, $a, $opt = [])
    {
        $CONSTRAINTS = is_array($a) ? tldUtils::constructWhere($a) : $a;
        $HAVING = !empty($CONSTRAINTS) ? "HAVING $CONSTRAINTS" : '';
        $ORDERBY = !empty($opt['orderBy']) ? "ORDER BY {$opt['orderBy']}" : 'ORDER BY dt';
        $OPTHAVE = !empty($opt['have']) ? $opt['have'] : '';
        $OPTSELECT = !empty($opt['select']) ? $opt['select'] : '';
        $OPTFROM = !empty($opt['where']) ? $opt['where'] : '';

        // Construct query
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        // Add all cost type in SELECT
        $costTypeList = tldModCost::getCostTypeByModule('CSR');
        $fromModCost = tldModCost::getFROM($cur);
        foreach ($costTypeList as $k => $costType) {

            $SELECT .= <<<EOF
, (
    SELECT SUM(
      ROUND(
        mod_costs.price*(
          IF(tcur.rate IS NULL, 1, tcur.rate)/
          IF(fcur.rate IS NULL, 1, fcur.rate)
        ),2
      )
    )
    $fromModCost
    WHERE
        mod_costs.type LIKE '{$costType['type']}'
        AND mod_costs.module LIKE 'CSR'
        AND mod_costs.parent_id=csr.id
) AS cost_$k
EOF;
        }
        $query = <<<EOF
$SELECT,$OPTSELECT
    (SELECT
        SUM(
          ROUND(
            mod_costs.price*(
              IF(tcur.rate IS NULL, 1, tcur.rate)/
              IF(fcur.rate IS NULL, 1, fcur.rate)
            ),2
          )
        )
        $fromModCost
        WHERE
            mod_costs.module LIKE 'CSR'
            AND mod_costs.parent_id=csr.id
    ) AS cost_total,
    '$cur' AS cur
$FROM
$OPTFROM
$HAVING
$OPTHAVE
$ORDERBY
EOF;

        return tldUtils::getSqlToAssocArray($query);
    }
}
