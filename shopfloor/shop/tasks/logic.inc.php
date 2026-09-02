<?php

switch($m[1] ?? null){
    case 'view':
        if(empty($id)){
            $DEFAULT_ERROR[] = _("ERROR: Task ID not set...");
            break;
        }
        $task = new tldTask($id);
        if($task->isEmpty()) {
            $DEFAULT_ERROR[] =  sprintf(_("ERROR: Could not find task# %s"),$id);
            return;
        }
        $report = new tldAssocTable($task->getHeader(),
                        array(	"id"				=>_("Task#"),
                                "status"			=>_("Status"),
                                "due_date"			=>_("Due"),
                                "task"				=>_("Task"),
                                "assignee_fullname"	=>_("Assignee")
                        )
		);
        $body .= $report->fetch();
        $report= new tldReportColumnar($task->getComments(), array(
        			"xItems"=>array(
                    	"date"				=>_("Date"),
						"poster_fullname"	=>_("Poster"),
						"comment"			=>_("Comment")),
					"title"=>_("Comments")
			)
		);
        $body .= $report->fetch();
	break;
}

?>
