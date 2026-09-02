<?php
ob_start();

$catList = ['A'=>'A','B'=>'B','C'=>'A'];
?>

<form method="post" action="<?= "$php_self?m[0]=gwf&m[1]=view&m[2]=tasks&m[3]=quickEdit&id=$id" ?>">
  <input type="hidden" name="form_submit" value="1">
  <table width="100%">
	<thead>
	  <tr bgcolor="#d0d0d0">
	  	<th>Task#</th>
	  	<th>Description</th>
	  	<th>Assignor</th>
	  	<th>Assignee</th>
	  	<th>Status</th>
	  	<th>Due date</th>
	  	<th>Category</th>
	  </tr>
	</thead>
	<tbody>

    <?php
       $i = 0;
       foreach($rows as $task):
       $i++;
       $bgcolor = $i%2==0 ? "#eeeeee" : "#d0d0d0";
    ?>
	  <tr bgcolor="<?= $bgcolor ?>">
	  	<td>
	  	  <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=<?= $task['id'] ?>"><?= $task['id'] ?></a>
	  	</td>
	  	<td><?= $task['task'] ?></td>
	  	<td><?= $task['assignor_fullname'] ?></td>
	  	<td><?= $task['assignee_fullname'] ?></td>
	  	<td><?= $task['status'] ?></td>
	  	<td><input type="text" name="task[<?= $task['id'] ?>][due_date]" value="<?= $task['due_date'] ?>" class="datepicker" size="10" /></td>
	  	<td><select name="task[<?= $task['id'] ?>][cat]" ?>" >
                <option value="A" <?php if($task['cat']=='A') echo "selected='selected'"; ?>>A</option>
                <option value="B" <?php if($task['cat']=='B') echo "selected='selected'"; ?>>B</option>
                <option value="C" <?php if($task['cat']=='C') echo "selected='selected'"; ?>>C</option>
            </select></td>
	  </tr>
	<?php  endforeach; ?>

	</tbody>
  </table>
  <p align="right"><input type="submit" value="Submit" /></p>
</form>

<?php
return ob_get_clean();
