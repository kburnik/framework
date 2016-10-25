<?php

function curl_get_contents( $url ) {

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


  $response = curl_exec($ch);
  $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return  array($http_status,$response);
}


//


function parsefloat($input) {
  return floatval(preg_replace("[^-0-9\.]","",$input));
}

function format_number($number,$decimals = 2) {

  $number=str_replace(",",".",$number)*1;
  //$number = cnv($number);
  $number=round($number,$decimals);
  if (!strpos($number,".")) $number.=".".str_repeat('0',$decimals);

  $out=str_replace(".",",",$number."")."";
  if ((strlen($out)-strpos($out,","))<3) $out.="0";

  list ($int,$dec) = explode(',',$out);
  $outint = "";
  $j=0;
  for ($i=strlen($int)-1;$i>=0; $i--) {
    $j++;
    $outint = $int[$i] . $outint;
    if ($j%3==0 && $i > 0) $outint = ".".$outint;
  }
  $out = "{$outint},{$dec}";
  return $out;

}

function display_price($number) {
  return format_number( convertRealPrice( $number ) ) . " kn";
}

function display_price_full_default_currency($number) {
  return format_number($number, 8) . " kn";
}

function display_price_null($number) {
  if (empty($number)) return '';
  return display_price( $number );
}

function display_offercount($offercount){
  if ($offercount > 0) {
    return $offercount;
  } else {
    return 'nema';
  }
}

function display_datetime( $date_string ) {
  $tm = strtotime($date_string);
  return date("d.m.Y. \u H:i", $tm  );
}

function display_datetime_accurate( $date_string ) {
  $tm = strtotime($date_string);
  return date("d.m.Y. H:i:s", $tm  );
}

function display_date( $date_string ) {
  $tm = strtotime($date_string);
  return date("d.m.Y.", $tm  );
}

function display_short( $string ) {
  return shorten($string,30);
}

function display_attribute( $string ) {
  return htmlentities( $string ,ENT_QUOTES | ENT_IGNORE, "UTF-8" );
}

function display_percentage( $decimal ){
  return str_replace('.', ',' , round($decimal*100,2)) . '%';
}



// todo: place elsewhere
function toCroatianAsc( $string ) {
  $croatian_utf=array("/Č/","/č/","/Ć/","/ć/","/Ž/","/ž/","/Đ/","/đ/","/š/","/Š/","/&scaron;/","/&Scaron;/");
  $croatian_asc_spec=array("Cz","cz","Cz","cz","Zz","zz","Dz","dz","sz","Sz","sz","Sz");
  return preg_replace( $croatian_utf , $croatian_asc_spec, $string );
}

function utf8Shorten( $string , $length = 100 , $append='' ) {
  $oldEncoding = mb_internal_encoding();
  mb_internal_encoding('UTF-8');
  $strLength = mb_strlen($string);

  if ($strLength <= $length) {
    $result = $string;
  } else {
    $result = mb_substr($string, 0, $length) . $append;
  }

  mb_internal_encoding($oldEncoding);

  return $result;
}

function view( $name ) {
  return constant('PROJECT_VIEW_DIR').'/'.$name;
}

function readview( $name ) {
  return file_get_contents( view ( $name ) );
}

