
<?php
	$query=mysqli_query($connection,"select count(activity_id) from tbl_activity");
	$row = mysqli_fetch_row($query);
        
	$rows = $row[0];
	$page_rows = 32;
	$last = ceil($rows/$page_rows);

	if($last < 1){
	        $last = 1;
	}

	$pagenum = 1;

	if(isset($_GET['pn'])){
	      $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
	}

	if ($pagenum < 1) { 
                                $pagenum = 1; 
	} else if ($pagenum > $last) { 
	         $pagenum = $last; 
	}

	$limit = 'LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;
	 $nquery=mysqli_query($connection,"select * from tbl_activity order by activity_id desc $limit");

	$paginationCtrls = '';

	if($last != 1){
		
                                        if ($pagenum > 1) {
                                                 $previous = $pagenum - 1;
                                                $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$previous.'" class="btn btn-default"><i class="fa-solid fa-angle-left"></i></a> &nbsp; &nbsp; ';

                                                for($i = $pagenum-3; $i < $pagenum; $i++){
                                                        if($i > 0){
                                                                $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'" class="btn btn-default">'.$i.'</a> &nbsp; ';
                                                       }
                                                  }
                                        }
	
                                        $paginationCtrls .= ''.$pagenum.' &nbsp; ';
	
                                        for($i = $pagenum+1; $i <= $last; $i++){
		$paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'" class="btn btn-default">'.$i.'</a> &nbsp; ';
		if($i >= $pagenum+4){
			break;
		}
                                        }

                                        if ($pagenum != $last) {
                                            $next = $pagenum + 1;
                                            $paginationCtrls .= ' &nbsp; &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'" class="btn btn-default"><i class="fa-solid fa-angle-right"></i></a> ';
                                        }
	}

?>