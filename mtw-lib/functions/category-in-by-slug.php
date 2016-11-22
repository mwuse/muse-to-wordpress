<?php
function category__in_by_slug($query){
	if( $query['category__in_by_slug'] )
	{
		foreach ($query['category__in_by_slug'] as $key => $cat) {
			$term = get_term_by( "slug", $cat, "category" );
			$query['category__in'][] = $term->term_id ;
		}
		
		unset( $query['category__in_by_slug'] );
	}
	return $query;
}
?>