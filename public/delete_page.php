<?php require_once("../includes/layout/session.php");?>
<?php require_once("../includes/layout/db_connection.php");?>
<?php require_once("../includes/layout/functions.php");?>
<?php
	$current_page=find_page_by_id($_GET["page"],false);
	if(!$current_page)
	{
		redirect_to("manage_content.php");
	}
	$id=$current_page["id"];
	$query= "DELETE FROM pages WHERE id = {$id} LIMIT 1";
	$result=mysqli_query($connection,$query);
	if($result && mysqli_affected_rows($connection)==1)
	{
		$_SESSION["message"]="page deleted.";
		redirect_to("manage_content.php");
	}
	else
	{
		$_SESSION["message"]="page deletion failed.";
		redirect_to("manage_content.php?page={$id}");
	}

?>