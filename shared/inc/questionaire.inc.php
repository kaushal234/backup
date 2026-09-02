<?php
/**
 *	questionaire related classes
 *
 *@package Questionaire
* @desc All classes related to the Questionaires are kept in this file
* @access public
* @author Graham K.L. Fong <graham.fong@tld-america.com>
* @copyright TLD
 */

 /**
 * @package Questionaire
 */
class question{
	public function __construct($id, $equip_cat="", $question_cat=""){
		if($id){
			$where = " WHERE id = $id";
		}elseif($equip_cat || $question_cat){
			$where = " WHERE ";
			if($equip_cat)
				$where .= " category='".TldDatabase::escape($equip_cat)."'";
			if($question_cat){
				if($equip_cat)
					$where .= " AND ";
				$where .= " category2='".TldDatabase::escape($question_cat)."'";
			}
			$where .= " ORDER BY RAND() LIMIT 1";
		}else{
			$where = " ORDER BY RAND() LIMIT 1";
		}
		$query = <<<EOF
		SELECT *
		FROM questionaire
		$where
EOF;

		$this->itsDetails = tldUtils::getSqlRowToAssocArray($query);
	}

	function getItsDetails(){
		return $this->itsDetails;
	}

	function checkAnswer($ans,$user){
		$result = $this->getAnswer() == $ans;
		$this->setAnsweredBy($user,$result);
		return $result;
	}

	function getAnswer(){
		return $this->itsDetails["answer"];
	}
	function getReason(){
		return $this->itsDetails["reason"];
	}
	function getResponses(){
		$result[] = $this->itsDetails["response1"];
		$result[] = $this->itsDetails["response2"];
		$result[] = $this->itsDetails["response3"];
		$result[] = $this->itsDetails["response4"];
		return $result;
	}

	function getId(){
		return $this->itsDetails["id"];
	}

	function setAnsweredBy($user,$result){
		$id = $this->getId();
		$query = <<<EOF
		INSERT INTO questionaire_results
		SET
		parent_id='$id',
		date=now(),
		user='$user',
		result='$result'
EOF;

		return tldUtils::sqlInsert($query);
	}
}
