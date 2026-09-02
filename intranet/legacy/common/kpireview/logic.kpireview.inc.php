<?php
$DEFAULT_TITLE .= "\KPI Review";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpireview">Home</a>
&nbsp;|&nbsp;<a href="kpireview/kpireview_admin.php">KPI Review Admin</a>
EOF;

switch($m[1]){
case 'ajax':
	// AJAX operations from jQuery user interface
	$template = "NO_TEMPLATE";
	$output_array = array();
	$json_flag = JSON_FORCE_OBJECT;
	
	switch($m[2]){
		case 'comments':
			switch($m[3]){
				case 'insert':
					if(empty($msg) || empty($name_id) || empty($xval) || empty($zval) || strlen($yval) <= 0){
						$output_array['error'] = "ERROR: Unable to save comment, empty values were given";
						break;
					}
					if(count($rows = tldModKPIReview::byNameID(TldDatabase::escape($name_id),array('xval'=>TldDatabase::escape($xval),'zval'=>TldDatabase::escape($zval)))) > 0){
						// Already has parent
						$kpireview = new tldModKPIReview($rows[0]['id']);
					}else{
						// Create new parent
						$error = tldModKPIReview::insert(array(
							'name_id' => TldDatabase::escape($name_id),
							'xval' => TldDatabase::escape($xval),
							'zval' => TldDatabase::escape($zval),
							'last_yval' => TldDatabase::escape($yval)
						));
						if(!is_numeric($error)){
							$output_array['error'] = "ERROR: Unable to create new row for reason: $error";
							break;
						}
						$kpireview = new tldModKPIReview($error);
					}
					$error = $kpireview->insertComment($user->getID(), TldDatabase::escape(trim($msg)));
					if(!is_numeric($error)){
						$output_array['error'] = "ERROR: Unable to save comment for reason: $error";
						break;
					}
					$output_array = _AJAXReadCommentByID($error);
				break;
				case 'update':
					if(strlen($yval) <= 0 || empty($msg)){
						$output_array['error'] = "ERROR: Unable to update comment, empty values were given";
						break;
					}
					$kpireview_comment = new tldModKPIReviewComments((int)$id);
					if($kpireview_comment->isEmpty()){
						$output_array['error'] = "ERROR: Unable to update comment, original comment was not found";
						break;
					}
					if($kpireview_comment->itsHeader['poster'] <> $user->getID() AND !$user->isInGroup('gg_ADMIN')){
						$output_array['error'] = "ERROR: Unable to update comment, only the original poster or admin can do this";
						break;
					}
					$kpireview_comment->update(array(
						'comment' => TldDatabase::escape(trim($msg))
					));
					$kpireview = new tldModKPIReview($kpireview_comment->itsHeader['parent_id']);
					$kpireview->update(array(
						'last_yval' => TldDatabase::escape($yval)
					));
					$output_array = _AJAXReadCommentByID((int)$id);
				break;
				case 'delete':
					if(empty($name_id) || empty($id)){
						$output_array['error'] = "ERROR: Unable to delete comment, empty values were given";
						break;
					}
					$kpireview_comment = new tldModKPIReviewComments((int)$id);
					if($kpireview_comment->isEmpty()){
						$output_array['error'] = "ERROR: Unable to delete comment, original comment was not found";
						break;
					}
					if($kpireview_comment->itsHeader['poster'] <> $user->getID() AND !$user->isInGroup('gg_ADMIN')){
						$output_array['error'] = "ERROR: Unable to delete comment, only the original poster or admin can do this";
						break;
					}
					tldModKPIReviewComments::delete((int)$id);
					// Delete parent if there are no other child comments
					$kpireview = new tldModKPIReview($kpireview_comment->itsHeader['parent_id']);
					$comments = $kpireview->getComments();
					if(count($comments) == 0){
						tldModKPIReview::delete($kpireview->getID());
					}
				break;
				case 'read':
					if(empty($name_id) || empty($xval) || empty($zval)){
						$output_array['error'] = "ERROR: Unable to read comments, empty values were given";
						break;
					}
					$res = tldModKPIReview::byConstraints(array(
						'name_id' => TldDatabase::escape($name_id),
						'xval' => TldDatabase::escape($xval),
						'zval' => TldDatabase::escape($zval)
					));
					if(!count($res)){
						break;
					}
					$kpireview = new tldModKPIReview($res[0]['id']);
					$comments = $kpireview->getComments();
					foreach($comments AS $comment){
						$output_array[] = _AJAXReadCommentByID($comment['id']);
					}
				break;
			}
		break;
		case 'list':
			switch($m[3]){
				case 'getHistory':
					$rows = tldModKPIReview::byNameID(TldDatabase::escape($name_id));
					foreach($rows AS $row){
						$output_array[$row['xval']][$row['zval']][] = $row['last_yval'];
					}
				break;
			}
		break;
	}
	
	echo json_encode($output_array, $json_flag);
break;
default:
    $body.="<p>Welcome to KPI Review common module</p>";
break;
}

function _AJAXReadCommentByID($id){
	$kpireview_comment = new tldModKPIReviewComments($id);
	$header = $kpireview_comment->itsHeader;
	foreach(array_keys($header) AS $key){
		if(is_int($key)) unset($header[$key]);
	}
	if($header['updated'] == '0000-00-00 00:00:00'){
		$header['updated'] = NULL;
	}
	return $header;
}

