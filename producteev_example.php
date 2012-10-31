<?php

// ini_set('display_errors', 1);

session_start();

if($_GET['logout'] == 1)
	{
	session_destroy();
	header('Location: producteev_example.php');
	exit;
	}
	

require("producteev.php");

?>

<html>
	<head>
		
		
		<link rel="stylesheet" type="text/css" href="jquery_ui_css/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" href="jquery_ui_css/jquery.ui.theme.css" />
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		<script src="producteev.js"></script>
		<title>Producteev API example</title>
	</head>
	<body>
	
		<?php
		
		$producteev = new producteev_api();
		
		// checks if user is connected
		$producteev->whoAreYou();
		
		if($producteev->getToken())
			{
			
			if($_GET['id_task'] != '')
				{
				$task = $producteev->getTask($_GET['id_task']);
				
				if(is_array($task))
					{
					echo "<big><a href='producteev_example.php'>Back to list of tasks</a></big><br/><br/>";
					
					echo "<table cellspacing='5'>
							<tr>
								<td class='ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all'>Key</td>
								<td class='ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all'>Value</td>
							</tr>";
					
					foreach($task as $k => $v)
						{
						echo "<tr>
								<td>$k</td>
								<td>".print_r($v, true)."</td>
							</tr>";
						}
						
					echo "</table>";
					}
				}
			else
				{
				$tasksList = $producteev->tasksList();
				
				echo "<big><a href='producteev_example.php?logout=1'>Logout</a></big><br/><br/>";
				
				if(is_array($tasksList))
					{
					echo "<table cellspacing='5'>
							<tr>
								<td class='ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all'>Title</td>
								<td class='ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all'>Deadline</td>
							</tr>";
					
					foreach($tasksList as $task)
						{
						$task = $task['task'];
						
						echo "<tr>
								<td><a href='?id_task=".$task['id_task']."'>".$task['title']."</a></td>
								<td>".date('Y-m-d', strtotime($task['deadline']))."</td>
							</tr>";
						}
						
					echo "</table>";
					}
				else
					{
					echo "No task have been found !";
					}
				}
				
				
			}
		
		
		
		?>
		
	</body>
</html>