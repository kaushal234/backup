<?php
/**
 *	Vendor related classes
 *
 *@package Vendor
* @desc All classes related to the vendors are kept in this file
* @access public
* @author Graham K.L. Fong <graham.fong@tld-america.com>
* @copyright TLD
 */

/**
 * Need these functions
 */
include_once 'common.inc.php';
include_once 'vault.inc.php';
include_once 'product_support.inc.php';
include_once 'erp.inc.php';

/**
 * Vendor function library
 *
 * @package Vendor
 */
class vendor{
	public $itsCreationTime;
	public $itsUsername;
	public $itsVendorid;
	public $itsDetails;
	public $itsMenu;
	public $itsBOM;
	public $itsBOMid;
	public $itsPOs;
	public $itsPONumbers;
	public $itsRFQs;
	public $itsRFQNumbers;
    public $itsERPCompany;
	public $itsERP;

	function __construct($username){
		$this->itsUsername = $username;								//Save userid
		$query="SELECT * FROM vendors WHERE userid='$username'";
		$rows=TldDatabase::query($query);
		if(TldDatabase::numRows($rows)==1){
			$this->itsDetails=TldDatabase::fetchArray($rows);
			$this->itsVendorid = $this->itsDetails['vendorid'];
			$this->itsERP = $this->itsDetails['erp'];
		}
	}

	function outFile($id){
		if(empty($this->itsBOM[$id])) {
			return;
		}
		$file=$this->itsBOM[$id]['cdrw'];
		$aReleasedController = new tldReleasedController();

		$aReleasedController->outFileInVault($this->itsERP,$file);
	}

	function listOverDueLineItemsByPO($poid){
		if(empty($poid) || empty($this->itsPOs)) {
			return;
		}
        global $PHP_SELF;
        $numLines = 0;
		$result = '<table border="1">';
		$result .= '<tr class="table_title"><td>Item</td><td>Part Number</td>' .
            '<td>Qty</td><td>UM</td><td>Order Date</td><td>Delivery Date</td></tr>';
		foreach($this->itsPOs as $line){
			if($line['ddat'] < date('Y-m-d') && $line['orno']==$poid){
				$result .= "<tr><td><a href=\"$PHP_SELF?m=Show+BOM&id=".$line['item']. '&date=' .$line['ddat']. '">' .$line['pono'].
                    '</a></td><td>' .$line['pono']. '</td><td>' .$line['oqua']. '</td><td>' .$line['cuqp'].
                    '</td><td>' .$line['odat']. '</td><td>' .$line['ddat']."</td></tr>\n";
				$numLines++;
			}
		}
		$result .= '</table>';

		return $numLines === 0 ? '&nbsp;' : $result;
	}

	function listPurchaseOrders(){
		$vendorid = $this->itsVendorid;
        $erpcompany = $this->itsERP;
		global $PHP_SELF;
		$result = $this->getMenu(0,"<a href=\"$PHP_SELF\">Home</a>");
		$result .= '<h3>POs for ' .strtoupper($this->itsUsername). '</h3>';
            $query = 'select * ' .
                'from erp_pur ' .
                    "where suno='$vendorid' AND erp='$erpcompany' order by orno,pono";
		if(empty($this->itsPOs)){
			$this->itsPOs=tldUtils::getSqlToAssocArray($query);

			if(empty($this->itsPOs)) {
				return $result.
					"<p class=\"alert\">No Purchase Orders found for Vendor $vendorid</p>";
			}
		}
		$this->itsPONumbers=array();
		$result .= '<table border="1">';
		$result .= '<tr class="table_title"><td>Purchase Order Number</td>';
		$result .= '</tr>';
		foreach($this->itsPOs as $line){
			if(!in_array($line['orno'],$this->itsPONumbers)){
				$this->itsPONumbers[] = $line['orno'];
				$result .= "<tr><td><a href=\"$PHP_SELF?m=Show+a+PO&id=".$line['orno']. '">' .$line['orno']. '</a></td>';
				$result .="</tr>\n";
			}
		}
		$result .= '</table>';

		return $result;
	}

	function showPurchaseOrderDetail($poid){
		global $PHP_SELF;
		$poFields=array('pono' => 'Item#',
						'item' => 'Part#',
						'oqua' => 'Order Qty',
						'dqua' => 'Del Qty',
						'bqua' => 'Back Qty',
						'dsca' => 'Description',
						'cuqp' => 'UM',
						'ddat' => 'Del Date',
						'resc' => 'Reschedule Date'
						);

		$result = $this->getMenu(1,"<a href=\"$PHP_SELF?m=Show+a+PO&id=$poid\">PO#$poid</a>");
		$result .= "<h2>Purchase Order $poid Detail</h2>";
		if(empty($this->itsPOs)) {
			return $result.
				'<p class="alert">No Purchase Order Line Items</p>';
		}
		$result .= '<table border="1">';
		$result .= '<tr class="table_title">';
		foreach ($poFields as $key=>$value){
			$result .= "<td class=\"xsmalltext\">$value</td>";
		}
		$result .= "<td>&nbsp;</td></tr>\n";
		$numLines = 0;
		foreach($this->itsPOs as $line){
			if($line['orno'] == $poid){
				$result .= '<tr>';
				foreach ($poFields as $key=>$value){
					$result .= '<td class="xsmalltext">' .$line[$key]. '</td>';
				}
				$result .= "<td class=\"xsmalltext\"><a href=\"$PHP_SELF?m=Show+BOM&id=".$line['item']. '&date=' .$line['odat']. '">BOM</a>';
				$result .= $this->checkForPartChange($line['item'],$line['odat']);
				$result .= "</td></tr>\n";
				$numLines++;
			}
		}
		$result .= '</table>';

		return $numLines ? $result : "$result<p class=\"alert\">$poid does not exist in your profile. Please hit List POs.</p>";
	}


	function getAllPOLines($field= '', $order= 'ASC'){
		if(empty($field)) {
			$field = 'ddat';
		}
		if(empty($order)) {
			$order = 'ASC';
		}
		$query = "SELECT * FROM erp_pur WHERE suno='".$this->itsVendorid."' AND erp=".$this->itsERP." ORDER BY $field $order";
		return tldUtils::getSqlToAssocArray($query);
	}

    /**
     * @param string $field
     * @param string $order
     * @return string
     */
    function showAllPurchaseOrderDetailsByPriority($field= '', $order= 'ASC'){
		global $PHP_SELF;
        if (empty($field)) {
			$field = 'ddat';
		}
        if (empty($order)) {
			$order = 'ASC';
		}
        $fields = ['orno' => 'Purch. Order#',
            'pono' => 'Line No.',
            'odat' => 'PO Order Date',
            'item' => 'Part Number',
            'dsca' => 'Description',
            'ddat' => 'Current Del. Date',
            'resc' => 'Resched Del. Date',
//						"expt"=>"Inst",
            'oqua' => 'Order Qty',
            'dqua' => 'Del Qty',
            'bqua' => 'Back Qty'
        ];
		$result = $this->getMenu(0,"<a href=\"$PHP_SELF\">Home</a>");
		$result .= '<img src="/shared/tld_logos/tld-halfinch.jpg"><h2>Purchase Order Lines by Planned Delivery Date</h2>';
		$poLines=$this->getAllPOLines($field,$order);
		if (empty($poLines)) {
		    return $result.'<p class="alert">No Purchase Order Line Items</p>';
        }
        $result .= '<table border="1" width="100%">';
        $result .= '<tr>';
        if ($order == 'DESC') {
            $newOrder = 'ASC';
            $arrow = '1downarrow.png';
        } else {
            $newOrder = 'DESC';
            $arrow = '1uparrow.png';
        }
        foreach ($fields as $key => $value) {
            $result .= '<td class="xsmalltext">';
            if ($key == $field) {
				$result .= "<img src=\"/shared/bluesphere/16x16/actions/$arrow\" align=\"right\">";
			}
			$result .= "<a href=\"$PHP_SELF?m=showAllPurchaseOrderDetailsByPriority&field=$key&order=$newOrder\">";
			$result .= "$value</a>";
			$result .= '</td>';
		}
        $result .= "</tr>\n";
        $numLines = 0;
        foreach ($poLines as $line) {
            $result .= $line['resc'] < date('Y-m-d') ? '<tr bgcolor="#FF0000">' : '<tr>';

            foreach ($fields as $key => $value) {
                $result .= '<td class="xsmalltext">';
                $data = empty($line[$key]) || $line[$key] === '0000-00-00' ? '&nbsp;' : $line[$key];
                $result .= $key === 'resc' ? '<b>' . $data . '</b>' : $data;
                $result .= '</td>';
            }
            $result .= "</tr>\n";
            $numLines++;
        }
        $result .= '</table>';

        return $numLines ? $result : "$result<p class=\"alert\">No  does not exist in your profile. Please hit List POs.</p>";
    }

    function listRFQs()
    {
        $vendorid = $this->itsVendorid;
        global $PHP_SELF;
        $result = $this->getMenu(0, "<a href=\"$PHP_SELF\">Home</a>");
        $result .= '<h3>RFQs for ' . strtoupper($this->itsUsername) . '</h3>';
        if (empty($this->itsRFQs)) {
            $this->itsRFQs = tldUtils::getSqlToAssocArray("select * from erp_rfq where suno=$vendorid AND small < 4 order by qono,pono");

            if (empty($this->itsRFQs)) {
                return "$result<p class=\"alert\">No RFQs found for Vendor $vendorid</p>";
            }
        }
        $this->itsRFQNumbers = array();
        $result .= '<table border="1">';
        $result .= '<tr class="table_title"><td>RFQ Number</td>';
        $result .= '</tr>';
        foreach ($this->itsRFQs as $line) {
            if (!in_array($line['qono'], $this->itsRFQNumbers)) {
                $this->itsRFQNumbers[] = $line['qono'];
                $result .= "<tr><td><a href=\"$PHP_SELF?m=Show+an+RFQ&id=" . $line['qono'] . '">' . $line['qono'] . '</a></td>';
                $result .= "</tr>\n";
            }
        }
        $result .= '</table>';

        return $result;
    }

    function showRFQDetail($rfqid)
    {
        global $PHP_SELF;
        $rfqFields = [
            'pono' => 'Item#',
            'item' => 'Part#',
            'oqua' => 'Qty',
            'dsca' => 'Description',
            'cuqp' => 'UM',
            'ddat' => 'Del Date',
            'rtdt' => 'Reply Date'
        ];
        $result = $this->getMenu(1, "<a href=\"$PHP_SELF?m=Show+an+RFQ&id=$rfqid\">RFQ#$rfqid</a>");
        $result .= "<h2>RFQ $rfqid Detail</h2>";
        if (empty($this->itsRFQs)) {
			return $result.
				'<p class="alert">No RFQ Line Items</p>';
		}
        $result .= '<table border="1">';
        $result .= '<tr class="table_title">';
        foreach ($rfqFields as $key => $value) {
            $result .= "<td class=\"xsmalltext\">$value</td>";
        }
        $result .= "<td>&nbsp;</td></tr>\n";
        $numLines = 0;
        foreach ($this->itsRFQs as $line) {
            if ($line['qono'] == $rfqid) {
                $result .= '<tr>';
                foreach ($rfqFields as $key => $value) {
                    $result .= '<td class="xsmalltext">' . $line[$key] . '</td>';
                }
                $result .= "<td class=\"xsmalltext\"><a href=\"$PHP_SELF?m=Show+BOM&id=" . $line['item'] . '&date=' . $line['qdat'] . '">BOM</a>';
                $result .= $this->checkForPartChange($line['item'], $line['qdat']);
                $result .= "</td></tr>\n";
                $numLines++;
            }
        }
        $result .= '</table>';

        return $numLines ? $result : "$result<p class=\"alert\">$rfqid does not exist in your profile.</p>";
	}

	function checkForPartChange($id,$date){
		$erp = $this->itsERP;
		//check for bom change
		$query = <<<EOF
			SELECT *
			FROM bom
			WHERE erp=$erp and mitm='$id' and
				('$date' > indt and exdt='0000-00-00'))
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
        $result = '';
		if(count($rows) && $rows[0]['exdt']<> '0000-00-00'){
			$result .= '&nbsp;<img src="/shared/bluesphere/16x16/actions/idea.png" alt="There has been a BOM change since order date">';
		}
		//check for drawing change
		$query = <<<EOF
			SELECT *
			FROM bomedm
			WHERE erp=$erp and eitm='$id' and
				('$date' > bindt and bexdt='0000-00-00')
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		if(count($rows) && $rows[0]['bexdt']<> '0000-00-00'){
			$result .= '&nbsp;<img src="/shared/bluesphere/16x16/actions/idea.png" alt="There has been a DRAWING change since order date">';
		}

		return $result ?: $this->checkForPartChangeRec($id,$date);
	}

	function checkForPartChangeRec($id,$date){
		$rows=tldUtils::getSqlToAssocArray(	'select pono,sitm,indt,exdt,cdrw,bindt,bexdt ' .
            'from bom,bomedm ' .
            'where bom.erp=' .$this->itsERP. ' and bomedm.erp=' .$this->itsERP.
							" and mitm='$id' and (('$date' between indt and exdt) or ('$date' > indt and exdt='0000-00-00')) ".
							"and (('$date' >= bindt and '$date' < bexdt) or ('$date' >= bindt and bexdt='0000-00-00')) and sitm=eitm ".
            'order by pono;');

		if(empty($rows)) {
		    return;
        }

		$row = current($rows);
		if ($row['exdt'] !== '0000-00-00' || $row['bexdt'] !== '0000-00-00') {
		    return '&nbsp;<img src="/shared/bluesphere/16x16/actions/idea.png" alt="There has been a BOM OR DRAWING change since order date">';
		}

		return $this->checkForPartChangeRec($row['sitm'],$date);
	}

	function listBomByPartNumber($id,$date){
		if(empty($id)) {
			return;
		}
		$result = $this->getMenu(2, '');
		$match = 0;

		//Check if exists on a PO
		if(!empty($this->itsPOs)){
			foreach($this->itsPOs as $line){
				if($line['item']==$id){
					$match=1;
					break;
				}
			}
		}
		//Check if exists on a RFQ
		if(!empty($this->itsRFQs)){
			foreach($this->itsRFQs as $line){
				if($line['item']==$id){
					$match=1;
					break;
				}
			}
		}
		if($match < 1) {
			return "$result<p class=\"alert\">$id does not exist in your profile.</p>";
		}


		if(empty($this->itsBOM) || $this->itsBOMid<>$id){
			$this->itsBOM=array();
			//Must only get bom.mitm,bom.indt,bom.exdt,bomedm.bindt,bomedm.bexdt, revi
			//as printing checks for sitm to decide whether to print
			$query = 'select bom.mitm,bom.indt,bom.exdt,bomedm.bindt,bomedm.bexdt,revi,cdrw,parts.DESCRIPTION as partdesc ' .
                'from bom,bomedm,parts ' .
                'where bom.erp=' .$this->itsERP. ' and bomedm.erp=' .$this->itsERP.
					" and mitm='$id' and (('$date' >= indt and '$date' < exdt) or ('$date' >= indt and exdt='0000-00-00')) ".
					"and (('$date' >= bindt and '$date' < bexdt) or ('$date' >= bindt and bexdt='0000-00-00')) and mitm=eitm ".
                'and bomedm.eitm=parts.item ' .
                'order by pono;';

			$row = tldUtils::getSqlRowToAssocArray($query);
			//If row is empty, then there's no BOM, must get data from EDM instead
			if($row){
				$this->itsBOM[$id] = $row;
				$this->listBomByPartNumberRec($id,$date, '');
			}else{
				$query = 'select bomedm.bindt,bomedm.bexdt,cdrw,revi,parts.DESCRIPTION as partdesc ' .
                    'from bomedm,parts ' .
                    'where bomedm.erp=' .$this->itsERP.
						" and eitm='$id'".
						" and (('$date' >= bindt and '$date' < bexdt) or ('$date' >= bindt and bexdt='0000-00-00'))".
                    ' and bomedm.eitm=parts.item';
				$this->itsBOM[$id] = tldUtils::getSqlRowToAssocArray($query);
			}
		}
		//Cache bom id
		$this->itsBOMid=$id;
		$result .= "<h3>Drawing List for $id as of $date</h3>\n";
		$result .= $id. ', ' .$this->itsBOM[$id]['partdesc']. ' [' .$this->itsBOM[$id]['revi']. ']';
//		$result .= count($rows)."\n".$id;
		if((!empty($this->itsBOM[$id]['exdt']) && $this->itsBOM[$id]['exdt']<> '0000-00-00') ||
			(!empty($this->itsBOM[$id]['bexdt']) && $this->itsBOM[$id]['bexdt']<> '0000-00-00')){
			$result .= "&nbsp;<img src=\"/shared/bluesphere/16x16/actions/idea.png\" alt=\"$id has had a BOM OR DRAWING change since order date\">\n";
		}
		if($this->itsBOM[$id]['cdrw']<>''){
			$result .= $this->linkPartToFile($id);
		}
		$result .= '<br/>';
		if(count($this->itsBOM) > 0){
			foreach($this->itsBOM as $row){
				if(empty($row['sitm'])) {
					continue;
				}
				$result .= $row['text'];
				if($row['cdrw']<>'') {
					$result .= $this->linkPartToFile($row['sitm']);
				}
				$result .= "<br>\n";
			}
			return $result;
		}else{
			return $result. 'No BOM for this item';
		}
	}

	function listBomByPartNumberRec($id,$date,$indent){
		if(empty($id)) {
			return;
		}
		$rows=tldUtils::getSqlToAssocArray(	'select pono,sitm,indt,exdt,cdrw,bindt,bexdt,revi,DESCRIPTION ' .
            'from bom,bomedm,parts ' .
            'where bom.erp=' .$this->itsERP. ' and bomedm.erp=' .$this->itsERP.
							" and mitm='$id' and (('$date' between indt and exdt) or ('$date' > indt and exdt='0000-00-00')) ".
							"and (('$date' >= bindt and '$date' < bexdt) or ('$date' >= bindt and bexdt='0000-00-00')) and sitm=eitm ".
            'and bomedm.eitm=parts.ITEM ' .
            'order by pono;');

		if($rows){
			$indentation= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			foreach($rows as $row){
				$row['text'] = $indent.$indentation.$row['pono']. ', ' .$row['sitm']. ', ' .$row['DESCRIPTION']. ' [' .$row['revi']. ']';
				if($row['exdt']<> '0000-00-00' || $row['bexdt']<> '0000-00-00'){
					$row['text'].= '&nbsp;<img src="/shared/bluesphere/16x16/actions/idea.png" alt="' .$row['sitm']." has had a BOM OR DRAWING change since order date\">\n";
				}
				$this->itsBOM[$row['sitm']] = $row;
				$this->listBomByPartNumberRec($row['sitm'],$date,$indent.$indentation);
			}
		}
	}

	function linkPartToFile($id){
		global $PHP_SELF;
		return "<a href=\"$PHP_SELF?m=getFile&id=$id\">".
				"<img src=\"/shared/bluesphere/16x16/actions/filesaveas.png\" alt=\"Save file $id to your hard disk\"></a>\n";
	}

	function findFile($id){
		$vault= '/mnt/mercury_eng';
		$folder=substr($id,0,3);
		$subFolder=substr($id,0,4);
		$letters=array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$fileType=array('.dwg', '.doc', '.tif');
		$path=$vault. '/' .$folder. '/' .$subFolder;

		if ($dir = @opendir($path)) {
		  while (($file = readdir($dir)) !== false) {
			$filenames[strtoupper($file)]=$file;
		  }
		  closedir($dir);
		}
		return $path;
	}

	function getMenu($level,$link){
		$this->itsMenu[$level]=$link;
		if($level==0) {
			return;
		}
		$result = '<p>';
		for($i=0;$i<$level;$i++){
			$result .= $this->itsMenu[$i]. ' | ';
		}
		$result =substr($result,0,-2);
		$result .= '</p>';
		return $result;
	}
	function showTable($rows,$fields){
		if(count($rows ?? []) > 0){
			$result="\n<table><tr class=\"table_title\">\n";
			foreach($fields as $field){
				$result .= "<td>$field</td>";
			}
			$result .= "</tr>\n";
			foreach($rows as $row){
				$result.= '<tr>';
					foreach($row as $column){
						if($column) {
							$result .= '<td>'.$column.'</td>';
						}
					}
				$result.="</tr>\n";
			}
			$result.= '</table>';
			return $result;
		}
	}
	function getDetails(){
		return $this->itsDetails;
	}
	function getUserId(){
		return $this->itsDetails['userid'];
	}
	function getTLDRepId(){
		return $this->itsDetails['tld_rep_id'];
	}

	function getServer(){
		return $this->itsDetails['server'];
	}
	function getCompanyName(){
		return $this->itsDetails['company'];
	}
	function getAddress(){
		return $this->itsDetails['address'];
	}
	function getPassword(){
		return $this->itsDetails['password'];
	}
	function toString(){
		return 	'<p>Userid: ' .$this->itsDetails['userid']."<br>\nCompany: ".
				$this->itsDetails['company']."<br>\nAddress: ".
				$this->itsDetails['address']. '</p>';
	}
}


?>
