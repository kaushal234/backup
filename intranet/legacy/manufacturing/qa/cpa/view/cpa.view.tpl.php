<?php
ob_start();
?>

<script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css" />

<script type="text/javascript">
$(function(){
	$("#accordion").accordion({
		'collapsible': 	true,
		'active': 		<?= _getAccordionIndexSectionToDisplay() ?>,
		'heightStyle': 	"content"
	});
});
</script>

<style>
.ui-accordion-header {
	font-family: arial,helvetica !important;
	font-weight: bold !important;
	color: #666666 !important;
}
</style>

<!-- CPA HEAD -->

<hr>
<h3>CPA# <?= $cpa->getID() ?> - <?= $cpa->getStatus() ?></h3>
<?php  if($cpa->getShortDesc()): ?>
<p><b><?= $cpa->getShortDesc() ?></b></p>
<?php  endif; ?>
<hr>

<!-- CPA CONTENTS -->

<table width="100%">
  <tr>

  	<!-- CPA CONTENTS - LEFT COLUMN -->

    <td width="65%">

		<!-- CPA Last log -->
        <h3>Last log entry</h3>
        <?php
        $log = $cpa->getLog();
        if(count($log) && !empty($log[0]['comment'])):
        ?>
        <p><?= $log[0]['comment'] ?></p>
        <?php  else: ?>
        <p>No log entry yet</p>
        <?php  endif; ?>

		<!-- MSG info -->
        <?php  if($cpa->getStatus()=="SUSPENDED"): ?>
        	<p class="alert">This CPA was SUSPENDED <?= $cpa->itsHeader['date_suspended'] ?></a>
        <?php  endif; ?>
        	<h3>
        	IF: <?= $cpa->getIFactor() ?>&nbsp;&nbsp;
        <?php  if(!in_array($cpa->getStatus(),array("REJECTED","CLOSED"))): ?>
        	DF: <?= $cpa->getDFactor() ?>&nbsp;&nbsp;
        	FW: <?= $cpa->getFocusWeight() ?>&nbsp;&nbsp;
        	Months Open: <?= $cpa->itsHeader['monthsOpen'] ?>&nbsp;&nbsp;
        <?php  endif; ?>
			</h3>
		<!-- CPA HEADER -->
        <?= _getView() ?>

        <br>

    	<!-- CPA WORKFLOW / ACTIVITY -->

    	<div id="accordion">
    	    <h3>PENDING</h3>
    		<div>
    			<?= _getPendingView() ?>
    		</div>
    		<h3>INVESTIGATION</h3>
    		<div>
    			<?= _getInvestigationView() ?>
    		</div>
    		<h3>ACTION</h3>
    		<div>
    		    <?= _getActionView() ?>
    		</div>
    		<h3>CLOSURE</h3>
    		<div>
        		<?= _getClosureView() ?>
    		</div>
    	</div>

    </td>

    <td width="5%"></td>

    <!-- CPA CONTENTS - RIGHT COLUMN -->

	<td width="30%" padding="10">

		<!-- CPA HEADER FILE -->

		<p align="right">
            <?php
            $fileName = $cpa->getFileName();
            $file = new basicFile(tldCPA::getPathToUploadFile($fileName));
            $fileMime = $file->getMimeTypeFromExtension();
            $fileMime = preg_split("#/#", $fileMime);
            if(strtolower($fileMime[0])=="image"):
            ?>
            <a href="/en/private/uploads/cpa/<?= $fileName ?>">
            	<img src="/en/private/uploads/cpa/<?= $fileName ?>" width="250">
            </a>
            <?php  elseif(!empty($fileName)): ?>
            <a href="/en/private/uploads/cpa/<?= $fileName ?>">
            	<img src="/shared/bluesphere/64x64/mimetypes/document.png" alt="Download Attachment">
            </a>
            <?php  endif; ?>
        </p>

        <br>

		<!-- CPA LINKS -->
		<?= _getLinksView() ?>

		<!-- CPA FILES -->
		<?= _getFilesView() ?>
	</td>
  </tr>
</table>


<?php

function _getView(){
    global $cpa;
    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'id'=>'CPA#',
            'date'=>'Date',
            'poster_fullname'=>'Poster',
            'initiator_fullname'=>'Initiator',
            'proj_leader_fullname'=>'Project Leader',
            'date_target'=>'Target Date',
            'status'=>'Status',
            'bu_fullname'=>'Factory',
            'dept'=>'Department',
            'type'=>'Type',
            'short_desc'=>'Short description',
            'description'=>'Description',
            'verification_description' => 'Verification',
        )
    );
    return $report->fetch();
}

function _getLinksView(){
    global $cpa;
    $report = new tldReportColumnar(
        $cpa->getLinksFromHere(),
        array(
            "xItems"=>array(
                "id"    =>"ID#",
                "type"  =>"Module",
                "item"  =>"Ref#",
                "dsca"  =>"Description"
            ),
            "title"=>"Links FROM Here...",
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&id="
            )
        )
    );
    $body.= $report->fetch();
    $report = new tldReportColumnar(
        $cpa->getLinksToHere(),
        array(
            "xItems"=>array(
                "id"        =>"ID#",
                "module"    =>"Module",
                "parent_id" =>"Ref#",
                "dsca"      =>"Description"
            ),
            "title"=>"Links TO Here...",
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id="
            )
        )
    );
    $body.= $report->fetch();
    return $body;
}

function _getFilesView(){
    global $cpa;
    $report = new tldReportColumnar(
        $cpa->getFiles(),
        array(
            "xItems"=>array(
                "id"=>"File#",
                "date"=>"Date",
                "description"=>"File Description"
            ),
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id="
            ),
            "title"=>"Files"
        )
    );
    return $report->fetch();
}

function _getPendingView(){
    global $cpa;
    $body = NULL;
    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'containment_action'=>'Containment actions',
        )
        );
    $body.= $report->fetch();
    $body.= _getTasksViewByStatus("PENDING");
    return $body;
}

function _getInvestigationView(){
    global $cpa;
    $body = NULL;
    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'root_cause'=>'Final root cause',
        )
    );
    $body.= $report->fetch();
    $body.= _getTasksViewByStatus("INVESTIGATION");
    return $body;
}

function _getActionView(){
    global $cpa;
    $body = NULL;
    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'corrective_action'=>'Corrective action',
            'preventive_action'=>'Preventive action',
        )
    );
    $body.= $report->fetch();
    $body.= _getTasksViewByStatus("ACTION");
    return $body;
}

function _getClosureView(){
    global $cpa;
    $body = NULL;
    if($cpa->getStatus()=="CLOSED"){
        $report = new tldAssocTable(
            $cpa->itsHeader,
            array(
                'date_closed'=>'Closed date',
                'final_fweight'=>'Final FW',
                'resolution'=>'Resolution'
            )
        );
        $body = $report->fetch();
    }elseif($cpa->getStatus()=="REJECTED"){
        $report = new tldAssocTable(
            $cpa->itsHeader,
            array(
                'rejection_reason'=>'Reason for Rejecting'
            )
        );
        $body = $report->fetch();
    }else{
    	$body = "<p>Not closed yet</p>";
    }
	return $body;
}

function _getTasksViewByStatus($status){
    global $cpa;
    $statusTitle = ucfirst(strtolower($status));
    $report = new tldReportColumnar(
        $cpa->getStatusTasksByConstraints(array('cpa_status'=>$status)),
        array(
            "xItems"=>array(
                "id"                =>"Task#",
                "status"            =>"Status",
                "due_date"          =>"Due date",
                "task"              =>"Task",
                "assignee_fullname" =>"Assignee"
            ),
            "links"=>array(
                "id"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
            ),
            "title"=>"$statusTitle tasks"
        )
    );
    return $report->fetch();
}

function _getAccordionIndexSectionToDisplay(){
    global $cpa;
    switch($cpa->getStatus()){
    case 'INVESTIGATION':
        return 0;
        break;
    case 'ACTION':
        return 1;
        break;
    case 'CLOSED':
    case 'REJECTED':
        return 2;
        break;
    }
    return 'false';
}


// Handle buffer
return ob_get_clean();
