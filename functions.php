<?

function sql($query=null) {
	if ($query != null) {
		return Project::GetQDP()->execute( $query );
	} else {
		return Project::GetQDP();
	}
}


?>