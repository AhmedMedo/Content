
<?php

	function redirect_to($new_location)
	{
		header("Location:". $new_location);
		exit;
	}
	function mysql_prep($string)
	{
		global $connection;
		$escaped_string=mysqli_real_escape_string($connection,$string);
		return $escaped_string;

	}
	function confirm_query($result_set)
	{
		if(!$result_set)
		{
			die("Database query faild.");
		}
	}
	function form_errors($errors=array())
	{
			$output="";
			if(!empty($errors))
			{
				$output .= "<div class=\"error\">";
				$output .= "Please fix the following errors";
				$output .= "<ul>";
				foreach ($errors as $key => $error){
					$output .= "<li>";
					$output .=htmlentities($error);
					$output .= "</li>";
				}
				$output .="</ul>";
				$output .="</div>";
		 	}
 	return $output;
 }

	function find_all_subjects($public=true){
		global $connection;
		$query=" SELECT * ";
		$query .="FROM subjects ";
		if($public)
		{
			$query .="WHERE visible =1 ";
		}	
		$query .="ORDER BY position ASC";
		$subject_set=mysqli_query($connection,$query);
		confirm_query($subject_set);
		return $subject_set;

	}

	function find_pages_for_subject($subject_id, $public=true)
	{
		global $connection;
		$safe_id=mysqli_real_escape_string($connection,$subject_id);
		$query  =" SELECT * ";
		$query .= "FROM pages ";
		$query .=" WHERE subject_id={$subject_id}  ";
		if($public)
		{
			$query .="AND visible=1 ";
		}
		$query .="ORDER BY position ASC";
		$page_set=mysqli_query($connection,$query);
		confirm_query($page_set);
		return $page_set;

	}
	function find_all_admins()
	{
		global $connection;
		$query  ="SELECT * ";
		$query .="FROM admin ";
		$query .="ORDER BY username ASC";
		$admin_set=mysqli_query($connection,$query);
		confirm_query($admin_set);
		return $admin_set;


	}
	function find_subject_by_id($subject_id)
	{
		global $connection;
		$safe_id=mysqli_real_escape_string($connection,$subject_id,$public=true);
		$query  ="SELECT * FROM subjects ";
		$query .=" WHERE id={$subject_id} ";
		if($public)
		{
			$query .="AND visible=1";
		}
		$query .=" LIMIT 1 ";
		$subject_set=mysqli_query($connection,$query);
		confirm_query($subject_set);
		if($subject=mysqli_fetch_assoc($subject_set))
		{
		return $subject;
		}
		else
		{
		return null;
		}


	}
	function find_page_by_id($page_id,$public=ture)
	{
		global $connection;
		$safe_id=mysqli_real_escape_string($connection,$page_id);
		$query  ="SELECT * FROM pages ";
		$query .=" WHERE id={$safe_id} ";
		if($public)
		{
			$query .="AND visible=1 ";
		}
		$query .=" LIMIT 1 ";
		$page_set=mysqli_query($connection,$query);
		confirm_query($page_set);
		if($page=mysqli_fetch_assoc($page_set))
		{
		return $page;
		}
		else
		{
		return null;
		}

	}
	function find_admin_by_id($admin_id)
	{
		global $connection;
		$safe_id=mysqli_real_escape_string($connection,$admin_id);
		$query ="SELECT * ";
		$query .="FROM admin ";
		$query .="WHERE id={$safe_id} ";
		$query .="LIMIT 1";
		$admin_set=mysqli_query($connection,$query);
		confirm_query($admin_set);
		if($admin=mysqli_fetch_assoc($admin_set))
		{
			return $admin;
		}
		else
		{
			return null;
		}
	}
	function find_default_page_for_subject($subject_id)
	{
		$page_set=find_pages_for_subject($subject_id);
		if($first_page=mysqli_fetch_assoc($page_set))
		{
		return $first_page;
		}
		else
		{
		return null;
		}

	}
	function find_selected_pages($public=false)
	{
			global $current_subject;
			global $current_page;
			if(isset($_GET["subject"]))
		{
			//use get request to get my subject id
			$selected_subject_id=$_GET["subject"];
			//get my subject details by id from the database
			$current_subject=find_subject_by_id($selected_subject_id,$public);
			$selected_page_id=null;
			if($current_subject &&$public)
			{
			$current_page=find_default_page_for_subject($current_subject["id"]);
			}
			else
			{
				$current_page=null;
			}

		}
		elseif(isset($_GET["page"]))

		{   
			$selected_subject_id=null;
			$current_subject=null;
			$selected_page_id=$_GET["page"];
			$current_page=find_page_by_id($selected_page_id);
			
		}
		else
		{
			$selected_subject_id=null;
			$current_subject=null;
			$selected_page_id=null;
			$current_page=null;
		}
	}


?>
<?php 
	function Navigation($subject_array,$page_array)
	{
		$output="<ul class=\"subjects\">";
		$subject_set=find_all_subjects(false);
		while($subject=mysqli_fetch_assoc($subject_set))
		{		
				
				$output .= "<li";
				if($subject_array &&$subject["id"]==$subject_array["id"])
				{
					$output.=" class=\"selected\"";

				}
				 $output .=">";
				 $output .="<a href=\"manage_content.php?subject=";
				 $output .= urlencode($subject["id"]);
				 $output .="\">";
				 $output .= htmlentities($subject["menu_name"]);
				 $output .="</a>";

			    $page_set=find_pages_for_subject($subject["id"], false);
				$output .="<ul class=\"pages\">";
				while($page=mysqli_fetch_assoc($page_set))
				{
					$output.= "<li";
					if($page_array && $page["id"]==$page_array["id"])
					{
						$output.= " class=\"selected\"";

					}
						  $output .= ">";
						  $output .="<a href=\"manage_content.php?page="; 
						  $output .= urlencode($page["id"]);
						  $output .="\">"; 
						  $output .= htmlentities($page["menu_name"]);
						  $output .="</a></li>";
						 }
						 mysqli_free_result($page_set);
						  $output.="</ul></li>";
				
		}
				
		mysqli_free_result($subject_set);
		$output.="</ul>";
		return $output;
	}
	function public_Navigation($subject_array,$page_array)
	{
		$output="<ul class=\"subjects\">";
		$subject_set=find_all_subjects();
		while($subject=mysqli_fetch_assoc($subject_set))
		{		
				
				$output .= "<li";
				if($subject_array &&$subject["id"]==$subject_array["id"])
				{
					$output.=" class=\"selected\"";

				}
				 $output .=">";
				 $output .="<a href=\"index.php?subject=";
				 $output .= urlencode($subject["id"]);
				 $output .="\">";
				 $output .= htmlentities($subject["menu_name"]);
				 $output .="</a>";
				 
				 if($subject_array["id"]==$subject["id"]|| $page_array["subject_id"]==$subject["id"])
				 {
					    $page_set=find_pages_for_subject($subject["id"]);
						$output .="<ul class=\"pages\">";
						while($page=mysqli_fetch_assoc($page_set))
						{
							$output.= "<li";
							if($page_array && $page["id"]==$page_array["id"])
							{
								$output.= " class=\"selected\"";

							}
								  $output .= ">";
								  $output .="<a href=\"index.php?page="; 
								  $output .= urlencode($page["id"]);
								  $output .="\">"; 
								  $output .= htmlentities($page["menu_name"]);
								  $output .="</a></li>";
								 }
								 $output.="</ul>";
								 mysqli_free_result($page_set);
				}
						 $output .="</li>";
				
		}
				
		mysqli_free_result($subject_set);
		$output.="</ul>";
		return $output;
	}


?>