<?php
include_once("common.inc.php");
include_once("calendar.inc.php");
include_once("erp.inc.php");

define('AP_BASEPATH_SRC', $GLOBALS['ONLINE_APPROVAL_PATH'].'/invoices/spool/300/JAPAN/');
define('AP_BASEPATH_DES', $GLOBALS['ONLINE_APPROVAL_PATH'].'/invoices/ARCHIVE/300/JAPAN/');

class tldJapanInvoices
{

    const DEFAULT_ASSIGNOR_ID = 2720;      // IRIE ARISA
    const DEFAULT_ASSIGNEE_ID = 2720;      // IRIE ARISA
    const BASEPATH_SRC = AP_BASEPATH_SRC;
    const BASEPATH_DES = AP_BASEPATH_DES;

    protected static $_dueDate = array('value' => 2, 'unit' => 'DAY');

    protected static $_process = array(

        array(
            'seq' => 'acct.payment.ap.japan.NonPOInvoice.pre_approved',
            'title' => 'Pre-approved payment for TLD Japan (under $5000)',
            'src' => 'Pre-Approved/Under5000/',
            'des' => 'Pre-Approved/',
        ),

        array(
            'seq' => 'acct.evp_coo.ap.japan.NonPOInvoice.pre_approved',
            'title' => 'Operating expense invoice approval process for TLD Japan (pre-approved under $5000)',
            'src' => 'Pre-Approved/Under5000/',
        ),

        array(
            'seq' => 'acct.payment.ap.japan.NonPOInvoice.pre_approved',
            'title' => 'Pre-approved payment for TLD Japan (over $5000)',
            'src' => 'Pre-Approved/Over5000/',
            'des' => 'Pre-Approved/',
        ),

        array(
            'seq' => 'acct.evp_coo_ceo.ap.japan.NonPOInvoice.pre_approved',
            'title' => 'Operating expense invoice approval process for TLD Japan (pre-approved over $5000)',
            'src' => 'Pre-Approved/Over5000/',
        ),

        array(
            'seq' => 'acct.evp_coo.ap.japan.NonPOInvoice.approval',
            'title' => 'Operating expense invoice approval process for TLD Japan (under $5000)',
            'src' => 'Other/Under5000/',
            'des' => 'Other/',
        ),

        array(
            'seq' => 'acct.evp_coo_ceo.ap.japan.NonPOInvoice.approval',
            'title' => 'Operating expense invoice approval process for TLD Japan (over $5000)',
            'src' => 'Other/Over5000/',
            'des' => 'Other/',
        ),
    );

    protected static $_srcFiles = array();

    protected static $_desFile;
    protected $_date;
    protected $_assignor;


    public function __construct(){
        $this->_date = time();
        $this->_assignor = $this->_getUserId('seq_acct.japan.invoice.approval.step0', 310);
        if(empty($this->_assignor) || $this->_assignor==0){
            $this->_assignor = self::DEFAULT_ASSIGNOR_ID;
        }
    }


    public function process(){

        $this->_log("RUN {$_SERVER['PHP_SELF']}");

        foreach (self::$_process AS $seq) {

            $this->_log("Process {$seq['title']}");

            $this->_log("Get files");
            $files = $this->_getFiles(self::BASEPATH_SRC . $seq['src']);

            foreach ($files AS $file) {
                if (strtolower(substr($file, -4)) != '.pdf') continue;

                $this->_log("Found $file");
                $id = $this->_newId();

                if (!empty($seq['des'])) {
                    $newBaseName = $this->_rename($file, $id);

                    $this->_log("Archive $file");
                    $result = $this->_archive($file, $newBaseName, self::BASEPATH_DES . $seq['des'], $id);

                    if(is_numeric($result)) {
                        $this->_log("Archived '$file' as '" . self::BASEPATH_DES . $seq['des'] . self::$_desFile . "'  #$result");
                    } else {
                        $this->_log("There was a problem moving the file - $result");
                    }
                }

                $this->_log("Create sequence");
                $this->_seq($seq['seq'], $seq['title'], $id);
            }

        }

        $this->_log("File cleanup");
        $this->_cleanUp();

        $this->_log("END OF {$_SERVER['PHP_SELF']}");
    }


    protected function _seq($seq, $title, $id){
        // Find assignee
        $query = <<<EOF
            SELECT
              (
                SELECT cstn.group_name
                FROM cal_seq_tpl_nodes AS cstn
                WHERE cstn.parent_id = cst.id
                ORDER BY cstn.step ASC
                LIMIT 1
              ) AS role,
              cst.id,
              cst.def_escalation_trigger
            FROM cal_seq_tpl AS cst
            WHERE cst.name = '$seq'
EOF;

        $result = tldUtils::getSqlToAssocArray($query);

        $assignee = $this->_getUserId($result[0]['role'], 300);
        if(empty($assignee) || $assignee==0){
            $assignee = self::DEFAULT_ASSIGNEE_ID;
        }

        $task = <<<EOL
TITLE: $title
DOC ID: $id
TYPE: TLDJAPANInvoices
BU: 300
EOL;

        $link = '';
        $file = false;

        if (is_file(self::BASEPATH_DES . 'Pre-Approved/' . self::$_desFile)) {
            $file = 'Pre-Approved/' . self::$_desFile;

            $link = <<<EOL
<!-- FILE --><a href="https://www.tld-gse.com/en/private/invoices/archive/300/JAPAN/$file" target="_blank">Click here to see Scanned PDF</a><!-- FILE -->
EOL;
        } else if (is_file(self::BASEPATH_DES . 'Other/' . self::$_desFile)) {
            $file = 'Other/' . self::$_desFile;

            $link = <<<EOL
<!-- FILE --><a href="https://www.tld-gse.com/en/private/invoices/archive/300/JAPAN/$file" target="_blank">Click here to see Scanned PDF</a><!-- FILE -->
EOL;
        }

        $seqId = tldSEQ::insert(
            0,
            array(
                'assignor' => $this->_assignor,
                'assignee' => $assignee,
                'erp' => 300,
                'task' => $task . PHP_EOL . $link,
                'due_date' => self::$_dueDate,
                'escalation_trigger'=>(int)$result[0]['def_escalation_trigger'],
                'file_info' => array(),
                'close_params' => array(
                	'file' => ($file) ? self::BASEPATH_DES . $file : false,
            		'title' => $title,
            		'datetime' => $this->_date,
            		'id' => $id,
            	),
            ),
            (int)$result[0]['id']
        );
        if(is_string($seqId)){
            $this->_log("Error at SEQ creation: $seqId");
            return $seqId;
        }

        $sequence = new tldSEQ($seqId);

        $this->_log("Created SEQ #$seqId - Template ID: {$result[0]['id']}  Template Name: $seq  Assigner: {$this->_assignor}  Assignee: $assignee");

        // for reports matter, insert a record in the mod_keys table
        // - key1 => ERP
        // - key2 => document ID
        // - key3 => seq template no
        // - key4 => archived filename
        // - key5 => empty for now
        tldModKey::insert(array(
            "parent_id" => $seqId,
            "module" => "APJAPAN",
            "type" => "TLDJAPANInvoices",
            "key1" => 300,
            "key2" => $id,
            "key3" => $result[0]['id'],
            "key4" => self::$_desFile,
            "key5" => NULL
        ));

        // link added in the task to get to the sequence on the TLD-GSE website.
        $message=<<<EOF
<a href="http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&single=1&id=$seqId">Sequence #$seqId requires your attention.</a>
<br><br><br>
EOF;

        // send the email notification to the assignee and assignor of the new task associated to the sequence.
        $sequence->notifyAssignee("$task:<br/>\n$message", "SEQ#$seqId: $title");
    }


    protected function _getFiles($src){
        $files = array();

        if ($dir = dir($src)) {
            while ($file = $dir->read()) {
                if (is_file($src . $file)) $files[] = $src . $file;
            }
        }

        return $files;
    }


    protected function _archive($srcFile, $newBaseName, $desPath, $id){
        $y = date('Y', $this->_date);
        $m = date('m', $this->_date);

        if (!is_dir($desPath . $y)) mkdir($desPath . $y);
        if (!is_dir($desPath . "$y/$m")) mkdir($desPath . "$y/$m");

        self::$_desFile = "$y/$m/$newBaseName";

        if (copy($srcFile, $desPath . self::$_desFile)) {
            self::$_srcFiles[] = $srcFile;

            $desFile = TldDatabase::escape(self::$_desFile);

            return tldArchive::insert(300, 'TLDJAPANInvoices', $id, date('Y-m-d', $this->_date), $desFile);
        }

        return 'ERROR: Unable to archive file as ' . self::$_desFile;
    }


    protected function _rename($file, $id){
        $ext = substr($file, strrpos($file, '.'));

        $base = basename($file, $ext);

        $base = str_replace(' ', '_', $base);

        while (strstr($base, '__')) $base = str_replace('__', '_', $base);

        return $id . '.' . $base . strtolower($ext);
    }


    protected function _newId(){
        return date('Ymd', $this->_date) . '.' . strtoupper(substr(md5(uniqid()), -5));
    }


    protected function _getUserId($role, $erp){
        $user = tldGroup::getUserlistByMultipleGroup(array($role), $erp);

        return (int)$user[0]['id'];
    }


    protected function _log($message, $throwException = false){
        if ($throwException) {
            throw new Exception($message);
            exit;
        } else {
            error_log($message . PHP_EOL);
        }
    }


    protected function _cleanUp(){
        foreach (self::$_srcFiles AS $file) unlink($file);
    }
}


$invoices = new tldJapanInvoices();
$invoices->process();

echo PHP_EOL;
exit;
