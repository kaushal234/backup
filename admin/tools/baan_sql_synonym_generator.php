<?php
$DEFAULT_TITLE .= "/Synonyms";
$DEFAULT_MENU .= <<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=synonyms">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=synonyms&m[1]=syn">List synonyms</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=synonyms&m[1]=shared">List shared tables</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=synonyms&m[1]=sql">Generate SQL</a>
EOF;

switch($m[1]){
case 'sql':
    $DEFAULT_ERROR[]="WARNING: PLEASE USE THIS TOOL ONLY IF YOU KNOW WHAT YOU ARE DOING!!!";
    error_log("SYNONYM TOOL USED BY ".$user->getEmail());
    $SQL_ARRAY = array();
    // 1 - Remove all actual synonyms
    $syns = tldBaanDBStructure::getSynonymsListByConstraints();
    foreach($syns as $syn){
        $sql = "DROP SYNONYM {$syn['name']};";
        $SQL_ARRAY[]=$sql;
    }
    // 2 - get shared table list
    $shares = tldBaanDBStructure::getSharingTableListByConstraints();
    foreach($shares as $share){
        $shareType = trim($share['t_tabg']);    // Table or Module
        $moduleOrTable = "t".trim($share['t_tabl']);// Table name or Module name
        $erpFrom = trim($share['t_lcmp']);      // ERP# we are looking for
        $erpTo = trim($share['t_fcmp']);        // Shared ERP#, where the data is
        
        switch($shareType){
        // Table
        case 1:
            $SQL_ARRAY[$moduleOrTable.$erpFrom]="CREATE SYNONYM $moduleOrTable$erpFrom FOR $moduleOrTable$erpTo;";
        break;
        // Module
        case 2:
            //$SYNONYM_SQL_LIST[]="-- MODULE: $moduleOrTable";
            // Select all tables of the module
            $tableList = array();
            $query = "SELECT DISTINCT SUBSTRING(table_name,1,9) AS table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name LIKE '$moduleOrTable%'";
            $tableList = tldUtils::getSqlToAssocArray(query, 'odbc', ['src' => 'baan']);
            foreach($tableList as $table){
                $tableName = $table['table_name'];
                $SQL_ARRAY[$tableName.$erpFrom]="CREATE SYNONYM $tableName$erpFrom FOR $tableName$erpTo;";
            }
        break;
        }
    }
    // 3 - Construct textarea and display
    $txtarea = implode('
',$SQL_ARRAY);
    $body = <<<EOF
<form>
  <p>List of queries to execute: (copy and paste to sql server)</p>
  <textarea rows="20" cols="80">$txtarea</textarea> 
</form>
EOF;
break;
case 'shared':
    $rows = tldBaanDBStructure::getSharingTableListByConstraints();
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "t_tabg"=>"Share type",
                "t_tabl"=>"Name",
                "t_lcmp"=>"ERP# FROM",
                "t_fcmp"=>"ERP# TO"
            ),
            "title"=>"List of actual Shared table declared in tttaad420000",
            "sortable"=>"NO",
            "options.showNumberOfRows"=>"YES"
        )
    );
    $body = $report->fetch();
break;
case 'syn':
    $rows = tldBaanDBStructure::getSynonymsListByConstraints();
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "object_id"=>"Object#",
                "name"=>"Name",
                "base_object_name"=>"Pointing to",
                "create_date"=>"Date creation",
                "modify_date"=>"Date modification"
            ),
            "title"=>"List of actual Synonyms setup in SQL DB",
            "sortable"=>"NO",
            "options.showNumberOfRows"=>"YES"
        )
    );
    $body = $report->fetch();
break;
default:
    $SYN = tldUtils::optionsByKeyValue(
        tldBaanDBStructure::getSynonymsListByConstraints(),
        "object_id",
        "name"
    );
    $SHA = array();
    $shares = tldBaanDBStructure::getSharingTableListByConstraints();
    foreach($shares as $share){
        $shareType = trim($share['t_tabg']);    // Table or Module
        $moduleOrTable = "t".trim($share['t_tabl']);// Table name or Module name
        $erpFrom = trim($share['t_lcmp']);      // ERP# we are looking for
        $erpTo = trim($share['t_fcmp']);        // Shared ERP#, where the data is
        
        switch($shareType){
        // Table
        case 1:
            $SHA[] = $moduleOrTable.$erpFrom;
        break;
        // Module
        case 2:
            // Select all tables of the module
            $tableList = array();
            $query = "SELECT DISTINCT SUBSTRING(table_name,1,9) AS table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name LIKE '$moduleOrTable%'";
            $tableList = tldUtils::getSqlToAssocArray(query, 'odbc', ['src' => 'baan']);
            foreach($tableList as $table){
                $tableName = $table['table_name'];
                $SHA[] = $tableName.$erpFrom;
            }
        break;
        }
    }
    // Create list to check
    $rows = array();
    foreach($SHA as $sha){
        if(in_array($sha,$SYN)) continue;
        $rows[] = array(
            "share"=>$sha
        );
    }
    // Display report
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "share"=>"Shared table name"
            ),
            "title"=>"List of shared tables NOT created in DB as SYNONYM !!!",
            "sortable"=>"NO",
            "options.showNumberOfRows"=>"YES"
        )
    );
    $body = $report->fetch();
break;
}

/**
 * Class for manipulating BAAN DB structure
 * @author jlambert
 * @package Admin Tool
 */
class tldBaanDBStructure {
    
        function __construct(){}
    
    function getSynonymsListByConstraints($a=NULL){
        if(is_array($a)){
            $a = tldUtils::constructWhere($a);
        }
        if(!empty($a)){
            $WHERE = "WHERE $a";
        }
        $query = <<<EOF
SELECT 
    *,
    SUBSTRING(CONVERT(VARCHAR, create_date, 120), 0, 11) AS create_date,
    SUBSTRING(CONVERT(VARCHAR, modify_date, 120), 0, 11) AS modify_date
FROM
	sys.synonyms
$WHERE
ORDER BY
	name
EOF;
        return tldUtils::getSqlToAssocArray(query, 'odbc', ['src' => 'baan']);
    }
    
    function getSharingTableListByConstraints($a=NULL){
        if(is_array($a)){
            $a = tldUtils::constructWhere($a);
        }
        if(!empty($a)){
            $WHERE = "WHERE $a";
        }
        $query = "SELECT * FROM tttaad420000 $WHERE";
        return tldUtils::getSqlToAssocArray(query, 'odbc', ['src' => 'baan']);
    }

}
