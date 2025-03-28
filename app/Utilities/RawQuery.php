<?php
// This can be found in the Symfony\Component\HttpFoundation\Response class
// Reuben Wafula

namespace App\Utilities;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RawQuery{
    const PAGE = 1;
    const NUM_ROWS = 8;
    const OFFSET = 0;

    public static function paginate($rawQuery, $page = null, $limit = null, $params=null) {
        if(is_null($limit) || $limit < 1) $limit = RawQuery::NUM_ROWS;
        if(is_null($page) || $page < 1) $page = RawQuery::PAGE;

        $offset = ($page-1) * $limit;
       
        #TODO: Replace this with a fucking good single regex;
        if(preg_match('/group\s+by/', $rawQuery)){
            $regex = "/(select)(.*)(from.*)\s+?group\s+by(.*)$/i";
            preg_match($regex,  $rawQuery, $matches);
            $cols = preg_replace('/(asc|desc)/i', '', array_get($matches, 4, '*'));
            $cols = "distinct $cols";
        }else{
            $regex = "/(select)(.*)(from .*)$/i";
            preg_match($regex,  $rawQuery, $matches);
            $cols = '*';
        }
        $from = array_get($matches, 3);
        $countQuery = "select count($cols) as c $from  ";

        #echo $rawQuery ;
        #die;
        #DB::enableQueryLog();

        if(!is_null($params)){
            $rawResult = DB::select( DB::raw($countQuery ), $params );
        }else{
            $rawResult = DB::select( DB::raw($countQuery ) );
        }

       # echo print_r(DB::getQueryLog(), 1);

        if(empty($rawResult)){
            return ['result' => [], 'total' => 0, 'page' => $page, 
                   'page_count' => 0 , 'next_page' => 0, 'prev_page'=> 0, 
                   'per_page'=>0, 'page_range' =>0, 'per_page'=>$limit ];
        }

        $totalCount  = $rawResult[0]->c;

        if($offset > $totalCount){
            return ['result' => [], 'total' => 0, 'page' => 1, 
                   'page_count' => 0 , 'next_page' => 1, 'prev_page'=>1, 
                   'page_range' => 0, 'per_page'=>$limit ];
        }


        $actualQuery = $rawQuery . " limit ". $offset . ", " . $limit;

        if(!is_null($params)){
            $rawResult = DB::select( DB::raw($actualQuery ),$params );
        }else{
            $rawResult = DB::select( DB::raw($actualQuery));
        }
        
        //echo print_r(DB::getQueryLog(), 1);
        Log::info("Page $page Limit $limit Total $totalCount");

        $next_page = ($page*$limit < $totalCount) ? $page+1 : $page;

        $prev_page = ($page <=1 ) ? 1: $page-1;
        $page_range = [];
        $total_pages = 0;
        if($totalCount > 0){
            $total_pages = ceil($totalCount/$limit);
        }

        $start = $page >= 3 ? $page-3 : 0;
        $end = $page+3 + ($start < 4 ? 3-$start: 0);
        foreach (range($start, $end) as $number) {
            if($number >= 0 & $number*$limit <= $totalCount){
                array_push($page_range, $number+1);
            } 
        }

        $output = ['result' => $rawResult, 'total' => $totalCount, 
            'page' => $page, 'page_count' => $total_pages ,
            'page_range' =>$page_range, 'next_page' => $next_page, 
            'prev_page'=> $prev_page, 'per_page'=>$limit ];


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

