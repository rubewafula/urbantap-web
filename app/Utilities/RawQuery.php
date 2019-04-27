<?php
// This can be found in the Symfony\Component\HttpFoundation\Response class
// Reuben Wafula

namespace App\Utilities;
use Illuminate\Support\Facades\DB;

class RawQuery{
	const PAGE = 1;
	const NUM_ROWS = 20;
	const OFFSET = 0;
	
	public static function paginate($rawQuery, $page = null, $limit = null, $params=null) {
        if(is_null($limit) || $limit < 1) $limit = RawQuery::NUM_ROWS;
        if(is_null($page) || $page < 1) $page = RawQuery::PAGE;

        $offset = ($page-1) * $limit;


		$countQuery = preg_replace('/(select)(.*)( from .*$)/i', "$1 count(*) as c $3", $rawQuery);


		if(!is_null($params)){
			$rawResult = DB::select( DB::raw($countQuery ), $params );
		}else{
			$rawResult = DB::select( DB::raw($countQuery ) );
		}

		

		if(empty($rawResult)){
			return [];
		}

		$totalCount  = $rawResult[0]->c;

		if($offset > $totalCount){
			return [];
		}

		
		$actualQuery = $rawQuery . " limit ". $offset . ", " . $limit;

		if(!is_null($params)){
			$rawResult = DB::select( DB::raw($actualQuery ),$params );
		}else{
			$rawResult = DB::select( DB::raw($actualQuery));
		}

		

		$output = ['result' => $rawResult, 'total' => $totalCount, 'page' => $page, 
		'page_count' => $limit ];


		return $output;  
	}

	public static function query($rawQuery, $params=null) {
		//DB::enableQueryLog();
       	if(!is_null($params)){
       		$results =  DB::select( DB::raw($rawQuery ), $params);
       	}else{
			$results =  DB::select( DB::raw($rawQuery ));
		}
		//echo print_r(DB::getQueryLog(), 1);
		return $results;

		
	}

	public static function  queryMultiple(array $queries){
		$results = [];
		
		foreach ($queries as $key => $sql) {
			$results[]  = DB::select(DB::raw($sql));

		}
		return $results;

	}
	

}

