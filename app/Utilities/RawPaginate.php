<?php
// This can be found in the Symfony\Component\HttpFoundation\Response class
// Reuben Wafula

namespace App\Utilities;
use Illuminate\Support\Facades\DB;

class RawPaginate{
	const PAGE = 1;
	const NUM_ROWS = 20;
	const OFFSET = 0;
	
	public static function paginate($rawQuery, $page = null, $limit = null) {
        if(is_null($limit) || $limit < 1) $limit = RawPaginate::NUM_ROWS;
        if(is_null($page) || $page < 1) $page = RawPaginate::PAGE;

        $offset = ($page-1) * $limit;


		$countQuery = preg_replace('/(select)(.*)(from.*$)/i', "$1 count(*) as c $3", $rawQuery);

		$rawResult = DB::select( DB::raw($countQuery ) );

		if(empty($rawResult)){
			return [];
		}

		$totalCount  = $rawResult[0]->c;

		if($offset > $totalCount){
			return [];
		}

		
		$actualQuery = $rawQuery . " limit ". $offset . ", " . $limit;

		$rawResult = DB::select( DB::raw($actualQuery ) );

		$output = ['result' => $rawResult, 'total' => $totalCount, 'page' => $page, 
		'page_count' => $limit ];


		return $output;  
	}

	

}

