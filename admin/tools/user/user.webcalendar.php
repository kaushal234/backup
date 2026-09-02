<?php

class tldCalUser {

    public $itsID;
    public $itsHeader;

        function __construct($id){
            $this->itsID = $id;
            $this->itsHeader = $this->getHeader($id);
        }

    function getID(){
		return $this->itsID;
	}

	function getLogin(){
	    return $this->itsHeader["cal_login"];
	}

	function getFirstname(){
		return $this->itsHeader["cal_firstname"];
	}

	function getLastname(){
		return $this->itsHeader["cal_lastname"];
	}

	function getEmail(){
		return $this->itsHeader["cal_email"];
	}

    function getHeader(){
        $query = "SELECT * FROM webcalendar.webcal_user WHERE cal_login LIKE '$this->itsID'";

        return tldUtils::getSqlRowToAssocArray($query);
    }

    function isEmpty(){
        return empty($this->itsHeader);
    }

    function insert($a){
        $fields = array(
            "cal_login","cal_passwd","cal_lastname","cal_firstname","cal_is_admin",
            "cal_email","cal_enabled","cal_telephone","cal_address","cal_title"
        );
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO webcalendar.webcal_user SET $SET";
        return tldUtils::sqlInsert($query);
    }

    function update($a, $fields=""){
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "UPDATE webcalendar.webcal_user SET $SET WHERE cal_login LIKE '$this->itsID'";
        return tldUtils::sqlExecute($query);
    }

    function setPassword($pass){
        $seed = md5($pass);
        return $this->update(array("cal_passwd"=>$seed));
    }

    function getPermissions(){
        $query = "SELECT * FROM webcalendar.webcal_access_user WHERE cal_login LIKE '$this->itsID'";
        return tldUtils::getSqlToAssocArray($query);
    }

    function deletePermissions(){
        $query = "DELETE FROM webcalendar.webcal_access_user WHERE cal_login LIKE '$this->itsID'";
        return tldUtils::sqlExecute($query);
    }

    function addPermissions($a){
        $fields = array(
            "cal_login","cal_other_user","cal_can_view","cal_can_edit",
            "cal_can_approve","cal_can_invite","cal_can_email","cal_see_time_only"
        );
        $SET = tldUtils::getSqlSet($a, $fields);
        $query = "INSERT INTO webcalendar.webcal_access_user SET $SET";
        return tldUtils::sqlInsert($query);
    }

    function getPrintVersion(){
        $report = new tldAssocTable(
            $this->itsHeader,
            array(
                "cal_login"=>"Login",
                "cal_lastname"=>"Lastname",
                "cal_firstname"=>"Firstname",
                "cal_title"=>"Title",
                "cal_is_admin"=>"Admin?",
                "cal_email"=>"Email",
                "cal_enabled"=>"Enable?",
                "cal_telephone"=>"Phone",
                "cal_address"=>"Address"
            ),
            array("title"=>"Webcal account")
        );
        return $report->fetch();
    }

    function getUserListByConstraints($a=""){
        if(is_array($a)){
            $WHERE = tldUtils::constructWhere($a);
        }else{
            $WHERE = $a;
        }
        if(!empty($WHERE)) $WHERE = "WHERE $WHERE";
        $query = "SELECT * FROM webcalendar.webcal_user $WHERE ORDER BY cal_login";
        return tldUtils::getSqlToAssocArray($query);
    }

    function getUserListEnable(){
        return tldCalUser::getUserListByConstraints(array("cal_enabled"=>"Y"));
    }

}
