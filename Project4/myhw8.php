<?php

header("Access-Control-Allow-Origin: *");
date_default_timezone_set("America/Los_Angeles");

if ($_GET["type"] == "lookup") {

  $term = $_GET["search"];
  $autourl = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/json?input=".$term;

  try {
    $auto_xml_data = @file_get_contents($autourl);
  }
  catch(Exception $error){
    return;
    }
    if ($auto_xml_data == FALSE ) {
    return;
  }
   echo $auto_xml_data;
}


if ($_GET["type"] == "chart") {

  $term = $_GET["symbol"];
  $chartJSON = "http://dev.markitondemand.com/MODApis/Api/v2/InteractiveChart/json?parameters=".$term;

  try {
    $chart_xml_data = @file_get_contents($chartJSON);
  }
  catch(Exception $error){
    return;
    }
    if ($chart_xml_data == FALSE ) {
    return;
  }
   echo $chart_xml_data;
}

if ($_GET["type"] == "news") {

  $accountKey = 'f/4Ut4CYBKVD67yKMTDcCqjLuSaELuGSXsT0X/D1kKs';
    $context = stream_context_create(array(
        'http' => array(
            'request_fulluri' => true,
            'header'  => "Authorization: Basic " . base64_encode($accountKey . ":" . $accountKey)
        )
    ));

  $WebSearchURL = 'https://api.datamarket.azure.com/Bing/Search/v1/News?Query=';

  $request = $WebSearchURL . urlencode( '\''.$_GET["search"].'\'').'&$format=json';

  $response = file_get_contents($request, 0, $context);


    echo $response;
}

if($_GET["type"] == "getQuote") {
  
  $symbol = $_GET['symbol'];
  $companyurl = "http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=".$symbol;

  try {
    $company_xml_data = @file_get_contents($companyurl);
  }
  catch(Exception $error){
    return;
    }
  if ($company_xml_data == FALSE ) {
    return;
  }

  $jsonObject = json_decode($company_xml_data, true);

  if (!empty($jsonObject['Message'])) {
        $arr=array("Status"=>"No");
        echo json_encode($arr); 
        return;
  }
  if ($jsonObject['Status'] == "SUCCESS") {
        $stock_name = $jsonObject['Name'];
        $stock_symbol = $jsonObject['Symbol'];
        $stock_lastprice = getRounded($jsonObject['LastPrice']);
        $stock_change = getRounded($jsonObject['Change']);
        $stock_changepct = getRounded($jsonObject['ChangePercent']);
        $stock_time = date("j F Y, g:i:s a",strtotime($jsonObject['Timestamp']));
        $stock_marketcap = getMC($jsonObject['MarketCap']);
        $stock_volume = $jsonObject['Volume'];
        $stock_changeytd = getRounded($jsonObject['ChangeYTD']);

        $stock_changepctytd = getRounded($jsonObject['ChangePercentYTD']);

        $stock_high = getRounded($jsonObject['High']);
        $stock_low = getRounded($jsonObject['Low']);
        $stock_open = getRounded($jsonObject['Open']);

          
        $arr=array("Status"=>"Success",
                 "Name"=>$stock_name,
                 "Symbol"=>$stock_symbol,
                 "LastPrice"=>$stock_lastprice,
                 "Change"=>$stock_change,
                 "ChangePercent"=>$stock_changepct,
                 "Timestamp"=>$stock_time,
                 "MarketCap"=>$stock_marketcap,
                 "Volume"=>$stock_volume,
                 "ChangeYTD"=>$stock_changeytd,
                 "ChangePercentYTD"=>$stock_changepctytd,
                 "High"=>$stock_high,
                 "Low"=>$stock_low,
                 "Open"=>$stock_open);
        echo json_encode($arr); 
        return;
  }
  else if ($jsonObject['Status'] == "Failure|APP_SPECIFIC_ERROR") {
        $arr=array("Status"=>"Fail");
        echo json_encode($arr); 
        return;
  }
}


function getRounded($beforeNum) {
  $afterNum = number_format($beforeNum,2);
  return $afterNum;
}

function getMC($number) {
  $updatedNum;
  if ($number/1000000000 >= 1) {
    $updatedNum = getRounded($number/1000000000)." Billion";
  }
  else if ($number/1000000 >= 1) {
    $updatedNum = getRounded($number/1000000)." Million";
  }
  else {
    $updatedNum = getRounded($number);
  }
  return $updatedNum;
}
?>