<?php

class tldConfigurator {

    public $itsID;
    public $itsHeader;

        public function __construct($id){
            $this->itsID = $id;
            $this->itsHeader = $this->getHeader();
        }

    /*******************************************
     * GETTERS
     *******************************************/

    public function getID(){
        return $this->itsID;
    }

    public function getParentID(){
        return $this->itsHeader['parent_id'];
    }

    public function getName(){
        return $this->itsHeader['name'];
    }

    public function getDescription(){
        return $this->itsHeader['description'];
    }

    /*******************************************
     * CRUD functions
     *******************************************/

    public static function getSELECT(){
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(){
        return <<<EOF
FROM
    configurator
EOF;
    }

    public function getHeader(){
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE configurator.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a,$opt=""){
        if(empty($a)){
            return "empty parameter";
        }
        // Look for constraints
        if(is_array($a)){
            $HAVING = "HAVING ".tldUtils::constructWhere($a);
        }else{
            $HAVING = "HAVING $a";
        }
        // Look for options
        if(!empty($opt['limit'])){
            $LIMIT = "LIMIT ".$opt['limit'];
        }
        if(!empty($opt['orderBy'])){
            $ORDERBY = "ORDER BY ".$opt['orderBy'];
        }else{
            $ORDERBY = "ORDER BY id";
        }
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

    public function insert($a){
        if(empty($a)) {
            return;
        }
        $fields = array('parent_id','name','description');
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO configurator SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a,$fields=""){
        if(empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE configurator SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete(){
        $query = "DELETE FROM configurator WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     *******************************************/

    public function addLogEntry($uid, $comment, $num_log=0){
        return tldModLog::insert(array(
            'parent_id' =>$this->itsID,
            'module'    =>'CONF',
            'poster'    =>$uid,
            'comment'   =>$comment,
            'log_num'   =>$num_log)
        );
    }

    public function getLog() {
        return tldModLog::byParent($this->itsID, 'CONF');
    }

   /*******************************************
     * Logic Methods
     *******************************************/

    public function isEmpty(){
        return empty($this->itsHeader);
    }

    public function addOption($a){
        $a['parent_id']=$this->getID();
        $a['type']='CONFIGURATOR';
        return tldConfiguratorOption::insert($a);
    }

    public function getOptions(){
        return tldConfiguratorOption::byParentType($this->getID(),'CONFIGURATOR');
    }

    public function getOptionsByKey($key){
        return tldConfiguratorOption::byParentTypeKey($this->getID(),'CONFIGURATOR',$key);
    }

    public function getRootElements(){
        return tldConfiguratorElement::byConstraints(array(
            'configurator_id'=>$this->getID(),
            'parent_id'=>0
        ));
    }

}


class tldConfiguratorElement {

    public $itsID;
    public $itsHeader;
    public $itsOptions;

        public function __construct($id){
            $this->itsID = $id;
            $this->itsHeader = $this->getHeader();
            $this->itsOptions = $this->getOptions();
        }

    /*******************************************
     * GETTERS
     *******************************************/

    public function getID(){
        return $this->itsID;
    }

    public function getParentID(){
        return $this->itsHeader['parent_id'];
    }

    public function getValue(){
        return $this->itsHeader['value'];
    }

	public function isEmpty(){
        return empty($this->itsHeader);
    }

    public function getConfiguratorID(){
        return $this->itsHeader['configurator'];
    }

    /*******************************************
     * CRUD functions
     *******************************************/

    public static function getSELECT(){
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(){
        return <<<EOF
FROM
    configurator_elements
EOF;
    }

    public function getHeader(){
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE configurator_elements.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a,$opt=""){
        if(empty($a)){
            return "empty parameter";
        }
        // Look for constraints
        if(is_array($a)){
            $HAVING = "HAVING ".tldUtils::constructWhere($a);
        }else{
            $HAVING = "HAVING $a";
        }
        // Look for options
        if(!empty($opt['limit'])){
            $LIMIT = "LIMIT ".$opt['limit'];
        }
        if(!empty($opt['orderBy'])){
            $ORDERBY = "ORDER BY ".$opt['orderBy'];
        }else{
            $ORDERBY = "ORDER BY id";
        }
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

    public static function insert($a){
        if(empty($a)) {
            return;
        }
        $fields = array('parent_id','configurator_id','value');
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO configurator_elements SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a,$fields=""){
        if(empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE configurator_elements SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete(){
        // Delete options
        $e = tldConfiguratorOption::deleteByTypeByParentID($type,$parentID);
        if(is_string($e)) {
            return $e;
        }
        // Delete element
        $query = "DELETE FROM configurator_elements WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    /*******************************************
     * COMMON MOD methods
     *******************************************/

    public function addLogEntry($uid, $comment, $num_log=0){
        return tldModLog::insert(array(
            'parent_id' =>$this->itsID,
            'module'    =>'CONF.ELEM',
            'poster'    =>$uid,
            'comment'   =>$comment,
            'log_num'   =>$num_log)
        );
    }

    public function getLog() {
        return tldModLog::byParent($this->itsID, 'CONF.ELEM');
    }

   /*******************************************
     * Logic Methods
     *******************************************/

    public function addOption($a){
        $a['parent_id']=$this->getID();
        $a['type']='ELEMENT';
        return tldConfiguratorOption::insert($a);
    }

    public function addOptions($options){
        $error = array();
        foreach($options as $option){
            $e = $this->addOption($option);
            if(is_string($e)) {
                $error[] = $e;
            }
        }
        return (count($error)) ? implode(' ',$e) : null;
    }

    public function getOptions(){
        return tldConfiguratorOption::byParentType($this->getID(),'ELEMENT');
    }

    public function getOptionByKey($key){
        foreach($this->itsOptions as $option){
            if($option['keytag']<>$key) {
                continue;
            }
            return $option;
        }
        return null;
    }

    public function getChildElements(){
        return self::byConstraints(array(
            'parent_id'=>$this->getID()
        ));
    }
}


class tldConfiguratorOption {

    public $itsID;
    public $itsHeader;

        public function __construct($id){
            $this->itsID = $id;
            $this->itsHeader = $this->getHeader();
        }

    public function getID(){
        return $this->itsID;
    }

    public function getParentID(){
        return $this->itsHeader['parent_id'];
    }

    public function getType(){
        return $this->itsHeader['type'];
    }

    public function getKey(){
        return $this->itsHeader['keytag'];
    }

    public function getValue(){
        return $this->itsHeader['value'];
    }

    public function isEmpty(){
        return empty($this->itsHeader);
    }

    public static function getSELECT(){
        return <<<EOF
SELECT
    *
EOF;
    }

    public static function getFROM(){
        return <<<EOF
FROM
    configurator_options
EOF;
    }

    public function getHeader(){
        $SELECT = self::getSELECT();
        $FROM = self::getFROM();
        $query = <<<EOF
$SELECT
$FROM
WHERE configurator_options.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public static function byConstraints($a,$opt=""){
        if(empty($a)){
            return "empty parameter";
        }
        // Look for constraints
        if(is_array($a)){
            $HAVING = "HAVING ".tldUtils::constructWhere($a);
        }else{
            $HAVING = "HAVING $a";
        }
        // Look for options
        if(!empty($opt['orderBy'])){
            $ORDERBY = "ORDER BY ".$opt['orderBy'];
        }else{
            $ORDERBY = "ORDER BY id";
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

    public static function insert($a){
        if(empty($a)) {
            return;
        }
        $fields = array('parent_id','type','keytag','value');
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO configurator_options SET $SET";
        return tldUtils::sqlInsert($query);
    }

    public function update($a,$fields=""){
        if(empty($a)) {
            return;
        }
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE configurator_options SET $SET WHERE id=$this->itsID";
        return tldUtils::sqlQuery($query);
    }

    public function delete(){
        $query = "DELETE FROM configurator_options WHERE id=$this->itsID LIMIT 1";
        return tldUtils::sqlExecute($query);
    }

    public static function deleteByTypeByParentID($type,$parentID){
        $query = "DELETE FROM configurator_options WHERE parent_id=$parentID AND type LIKE '$type'";
        return tldUtils::sqlExecute($query);
    }

    public static function byParentType($pid,$type){
        return self::byConstraints(array('parent_id'=>$pid,'type'=>$type));
    }

    public static function byParentTypeKey($pid,$type,$key){
        return self::byConstraints(array('parent_id'=>$pid,'type'=>$type,'keytag'=>$key));
    }

}


class tldSalesAppConfigurator {

    const CONFIGURATOR_ID = 1;
    private $itsConfigurator;

    public function  __construct(){
        $this->itsConfigurator = new tldConfigurator(self::CONFIGURATOR_ID);
    }

	public function getID(){
        return $this->itsConfigurator->itsID();
    }

    public function isEmpty(){
        return $this->itsConfigurator->isEmpty();
    }

    public function getHeader(){
        return $this->itsConfigurator->itsHeader;
    }

    public function getRootElements(){
        return $this->itsConfigurator->getRootElements();
    }

    public static function addElement($a){
        $a['configurator_id'] = self::CONFIGURATOR_ID;
        return tldSalesAppConfiguratorElement::create($a);
    }


}

class tldSalesAppConfiguratorElement extends tldConfiguratorElement{

    public function getType(){
        return $this->getOptionByKey('type');
    }

	public function getTypeValue(){
        $value = $this->getOptionByKey('type');
		return $value['value'];
    }

    public function getLocationValue(){
        $value = $this->getOptionByKey('location');
        return $value['value'];
    }

    public function isEnable(){
        $encryption = $this->getStatus();
        if($encryption['value'] == "ENABLE"){
            return true;
        }else{
            return false;
        }
    }

    public function getPosterID(){
        return $this->getOptionByKey('poster_id');
    }

    public function getEncriptionChoice(){
        return $this->getOptionByKey('encryption');
    }

    public function getStatus(){
        return $this->getOptionByKey('status');
    }

    public static function getOptionList(){
        return array(
            'type',
            'poster_id',
            'encryption',
            'status',
            'location'
        );
    }

    public static function create($a){
        // Create element
        $e = parent::insert($a);
        if(is_string($e)) {
            return $e;
        }
        $element = new tldSalesAppConfiguratorElement($e);
        // Create options
        $allowedOptions = self::getOptionList();
        foreach($allowedOptions as $option){
            if(empty($a[$option])) {
                continue;
            }
            $element->addOption(array(
                'keytag'=>$option,
                'value'=>$a[$option]
            ));
        }
        // Finally return element#
        return $e;
    }

	public function update($a,$fields=""){
        // Update element
        if($a['value']){
            $e = parent::update($a,array("value"));
            if(is_string($e)) {
                return $e;
            }
        }
        // Update options
        $allowedOptions = self::getOptionList();
        foreach($allowedOptions as $allowedOption){
        	if(empty($a[$allowedOption])) {
                continue;
            }
        	$opt = parent::getOptionByKey($allowedOption);
			if($opt && $opt['value'] != $a[$allowedOption]){
				$option = new tldConfiguratorOption($opt['id']);
				$e = $option->update(array('keytag'=>$allowedOption,'value'=>$a[$allowedOption]),array('keytag','value'));
				if(is_string($e)) {
                    return $e;
                }
			}elseif(empty($opt)){
				 parent::addOption(array(
                'keytag'=>$allowedOption,
                'value'=>$a[$allowedOption]
            ));
			}
		}
        return $e;
    }

    public static function getTypeList(){
        return array(
            'FOLDER'=>'Folder',
            'GALLERY'=>'Gallery Link',
            'DMS'=>'DMS#',
            'LINK'=>'Hyperlink (URL)',
        );
    }

    public static function getStatusList(){
        return array(
            'ENABLE'=>'ENABLE',
            'DISABLE'=>'DISABLE'
        );
    }

    public function getActionList(){
        return array(
            'update'=>'Update',
            'delete'=>'Delete',
            'status'=>'Status'
        );
    }
}
