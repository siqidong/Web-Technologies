
<html>
<head>
<title> Assignment 6</title>
<style type="text/css">
#table1 {
    border: 2px solid #000000;
}
#table2 td
{
    width:150px;
}
#table2
{
    left:600px;
    width:750px;
}
#searchResultsDiv
{   
    left:300px;
    width:1000px;
}
#searchRSSDiv
{   left:200px;
    width:1000px;
}
li
{
    font-weight:normal;
    font-size:16px;
}
.hrclass
{
        height:3px;
        border:none;
        color:#333;
        background-color:#333;
        margin-top:1px;
}
</style>
<script type="text/javascript">
function checkFormSubmit()
{
    if(document.getElementById("company_input") == null || document.getElementById("company_input").value == "" )
    {
        alert("Please enter a Company symbol");
        return false;
    }
    else
    {
   
        document.forms[0].method="POST";
        document.forms[0].action="search_stock.php";
        document.forms[0].submit();
        return true;
    }
}
</script>


<!-- Here is the PHP Script which we will check whether we are using a get or post. If, we are using the get, we dont do anything.
     Else if there is post, fetch the company name and fetch both the XMLs from the respective sources. Once we get both the XMLs,
     we parse the XMLs and set the values accordingly in the slot we designed.-->
<?php
    
    class YahooRSS
    {
        private $title;
        private $link;
        public function __construct($title,$link)
        {
            $this->title = $title;
            $this->link = $link;
        }
        
        public function getTitle()
        {
            return $this->clean($this->title); 
        }
         public function getLink()
        {
            return $this->clean($this->link); 
        }
        
        public function clean($strValue)
        {
            if($strValue || $strValue==="")
                return $strValue;
             else return 'N/A';   
        }
        
    }
    
   class YahooStock
    {
        private $yahooXML;
        private $rssXml;
        private $rssList;
        public function __construct($yahooXML,$rssXml) {
        $this->rssXml= $rssXml;
        $this->yahooXML = $yahooXML;
        }
        public function validateRSSXML()
         {
            if((string)$this->rssXml->channel->title ==="Yahoo! Finance: RSS feed not found")
                return false;
             return true;
         }
        
        public function processRSSFeed()
        {
            if($this->validateRSSXML())
            {
                $this->rssList = array();    
                $rssItems = $this->rssXml->channel->children();
                foreach($rssItems as $posItems)
                {
                    if($posItems->getName()==="item")
                    {
                        $yahooRSS = new YahooRSS(htmlEntities($posItems->title, ENT_QUOTES, 'UTF-8'),$posItems->link);
                        array_push($this->rssList,$yahooRSS);    
                    }
                    
                }
                
                
            }
            else
            {
                $this->rssList = NULL;
            }
            
        }
        
  
        
        public function getRSSList()
        {
            return $this->rssList;
        }
        
         public function getBid()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->Bid);
         }  
          public function getVolume()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->Volume);
         }  
          public function getChange()
         {
             return $this->yahooXML->results->quote->Change;
         }
          public function getOpen()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->Open);
         }         
         public function getDaysLow()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->DaysLow);
         }
         public function getDaysHigh()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->DaysHigh);
         }
         public function getYearLow()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->YearLow);
         }
         public function getYearHigh()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->YearHigh);
         }
         public function getMarketCapitalization()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->MarketCapitalization);
         }
public function getLastTradePriceOnly()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->LastTradePriceOnly);
         }
public function getName()
         {
 
            return  $this->clean($this->yahooXML->results->quote->Name);
         }
public function getPreviousClose()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->PreviousClose);
         }
         public function getChangeInPercent()
         {
           return  $this->clean2($this->yahooXML->results->quote->ChangeinPercent);
         }         
          public function getSymbol()
         {
            
            return  $this->clean($this->yahooXML->results->quote->Symbol);
         }
            public function getOneyrTargetPrice()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->OneyrTargetPrice);
         }
public function getAsk()
         {
            
            return  $this->clean2($this->yahooXML->results->quote->Ask);
         }         
    public function getAverageDailyVolume()
         {
            return $this->clean2($this->yahooXML->results->quote->AverageDailyVolume);
            
         }
    
    public function clean($strValue)
        {
            if($strValue || $strValue!=="")
                return $strValue;
             else return 'N/A';   
        }
    public function clean2($strValue)
        {
            if($strValue || $strValue==="")
                return $strValue;
             else return '0';   
        }
    
    }
?>
     
</head>
    <body>
        <h2 style="text-align: center">Market Stock Search</h2>
        <div id="formDiv">
        <form name="market_search" method="POST" action="#" onsubmit="return checkFormSubmit()">
            <table id="table1" align="center">
                <tr>
                <td>Company Symbol :</td>
                <td style="width:240px;"><input type="text" id="company_input" name="company_input" maxlength="50" style="width:230px;" onkeypress="this.value=this.value.toUpperCase();" autofocus/></td>
            <td><input type="submit" id="submit" name="submit" value="Search"/> </td>
            </tr>
            <tr><td colspan="3">Example : <I>GOOG, MSFT, YHOO,FB,AAPL..etc</I></td></tr>
            </table>
        </form>
        </div>
    <?php
    try
    {
        if($_SERVER['REQUEST_METHOD']=== 'POST')
        {
       
            if(isset($_POST['company_input']))
            {
                //Check if query 1 returns something
                $content = @file_get_contents("http://query.yahooapis.com/v1/public/yql?q=Select%20Name%2C%20Symbol%2C%20LastTradePriceOnly%2C%20Change%2C%20ChangeinPercent%2C%20PreviousClose%2C%20DaysLow%2C%20DaysHigh%2C%20Open%2C%20YearLow%2C%20YearHigh%2C%20Bid%2C%20Ask%2C%20AverageDailyVolume%2C%20OneyrTargetPrice%2C%20MarketCapitalization%2C%20Volume%2C%20Open%2C%20YearLow%20from%20yahoo.finance.quotes%20where%20symbol%3D%22".$_POST['company_input']."%22&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys");
                if($content === false || strlen($content) === 0) { ?>
                <h3 style="text-align: center"><?php echo "Stock Information Not Available."?></h3>
                <h3 style="text-align: center"><?php echo "Could not retrieve page. Please fix your query,refresh the page and try again"?></h3>
                <?php return NULL; } 
                $yahooStockDetails = new SimpleXMLElement($content); 
                if($yahooStockDetails === false) 
                { ?> 
                    <h3 style="text-align: center"><?php echo "Malformed XML.Please fix your query and try again."?></h3>
                <?php
                    return NULL;
                }
                
               
            
                //$request_URI1 = "http://query.yahooapis.com/v1/public/yql?q=Select%20Name%2C%20Symbol%2C%20LastTradePriceOnly%2C%20Change%2C%20ChangeinPercent%2C%20PreviousClose%2C%20DaysLow%2C%20DaysHigh%2C%20Open%2C%20YearLow%2C%20YearHigh%2C%20Bid%2C%20Ask%2C%20AverageDailyVolume%2C%20OneyrTargetPrice%2C%20MarketCapitalization%2C%20Volume%2C%20Open%2C%20YearLow%20from%20yahoo.finance.quotes%20where%20symbol%3D%22".$_POST['company_input']."%22&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
                $request_URI2 = "http://feeds.finance.yahoo.com/rss/2.0/headline?s=".$_POST['company_input']."&region=US&lang=en-US";
               $yahooData =$yahooStockDetails;// @simplexml_load_file($request_URI1) or die('<h1>Zero results found!');
               $yahooRSS = @simplexml_load_file($request_URI2) or die('<h1>Zero results found!');
                $yahooStock  = new YahooStock($yahooData,$yahooRSS);
            }
            
        }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
        //Check for Not Found conditions 1) Name== symbol 2) change = 0 3) prev close =0 4 1y Target Est = 0
            if(strcmp($yahooStock->getChange(),"")==0)
       // if( strcmp($yahooStock->getName(),$yahooStock->getSymbol())== 0 and floatval($yahooStock->getChange())==0 and
       //     floatval($yahooStock->getPreviousClose())==0 and floatval($yahooStock->getOneyrTargetPrice())==0 )
            { ?>
           
                 <h2 style="text-align: center;">Stock information is not available</h2>
            <?php }
            else
            {
    ?>
        <div id="searchTag" style="display:block">
        <h2 style="text-align: center;">Search Results</h2>
        </div>
        <div id="searchResultsDiv" style="display:block;margin-left:250px;">
        <div style="vertical-align:top;line-height:1; height=4px;letter-spacing:0.8px;"><font size="+2"><B><?php echo $yahooStock->getName();?>(<?php echo $yahooStock->getSymbol();?>)</B></font> &nbsp;<font size="+1"> <?php echo number_format(floatVal($yahooStock->getLastTradePriceOnly()),2,'.',',');?></font>&nbsp;
        <?php $color="#0b7520";
              $image = "http://www-scf.usc.edu/~csci571/2014Spring/hw6/up_g.gif";
              $changeValue = "NEG";
                if(floatVal($yahooStock->getChange())>0.0){ $changeValue = "POS";}
               else if(strpos($yahooStock->getChange(),"+")===true){$changeValue="POS";}
               else if(floatVal($yahooStock->getChange())===0.0){$changeValue = "NEU";}
                if( $changeValue ==="NEG")
               {
                   $color = "#FF0000";
                   $image = "http://www-scf.usc.edu/~csci571/2014Spring/hw6/down_r.gif";
               }     
               else if($changeValue==="NEU")
               {
                   $color = "#0b7520";
                   $image = "";
                    
               }
        ?>
        <?php if($changeValue !="NEU")
               {?>
               
        <image src="<?php echo $image;?>"/>
            
            <?php } ?>
        <font size="+1" color="<?php echo $color; ?>">    
        <?php echo number_format(abs(floatVal($yahooStock->getChange())),2,'.',',');?>(<?php echo number_format(abs(floatVal($yahooStock->getChangeInPercent())),2,'.',',');?>%) 
        </font>
        <hr align="left" width="76%" class="hrclass" style="margin-top:1px;">
        </div>
        <table id="table2" cellpadding="0">
        <tr ><td style="text-align:left;">Prev Close:</td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getPreviousClose()),2,'.',',');?></td><td style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Day's Range:</td><td></td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getDaysLow()),2,'.',',');?> - <?php echo number_format(floatVal($yahooStock->getDaysHigh()),2,'.',',');?></td></tr>
        <tr><td style="text-align:left;">Open:</td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getOpen()),2,'.',',');?></td><td style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;52wk Range:</td><td></td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getYearLow()),2,'.',',');?> - <?php echo number_format(floatVal($yahooStock->getYearHigh()),2,'.',',');?></td></tr>
        <tr><td style="text-align:left;">Bid:</td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getBid()),2,'.',',');?></td><td style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Volume:</td><td></td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getVolume()),0,'',',');?></td></tr>
        <tr><td style="text-align:left;">Ask:</td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getAsk()),2,'.',',');?></td><td style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avg Vol(3m):</td><td></td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getAverageDailyVolume()),0,'',',');?></td></tr>
        <tr><td style="text-align:left;">1y Target Est:</td><td style="text-align:right;"><?php echo number_format(floatVal($yahooStock->getOneyrTargetPrice()),2,'.',',');?></td><td style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Market Cap:</td><td></td><td style="text-align:right;">
        <?php 
            $marketCap =  $yahooStock->getMarketCapitalization();
            echo number_format(floatVal(substr($marketCap,0,strlen($marketCap)-1)),1,'.',',').substr($marketCap,strlen($marketCap)-1,strlen($marketCap));
        ?></td></tr>
        
        </table>
        <br/>
        <div><font size="+2"><B>News Headlines</B>
        <hr align="left" width="76%"  class="hrclass">
        </div>
  
        <?php 
        
             // Check if the query2 exists on the web
                 $RSSFeedURI = "http://feeds.finance.yahoo.com/rss/2.0/headline?s=".$_POST['company_input']."&region=US&lang=en-US"; 
                 $content = @file_get_contents($RSSFeedURI); if($content === false || strlen($content) === 0) 
                 { 
                    ?><h3 style="text-align: center"><?php echo "Could not retrieve page. Please fix your query  try again"; ?></h3><?php 
                    return NULL; 
                 } 
                 $yahooRSSDetails = new SimpleXMLElement($content); 
                 if($yahooRSSDetails === false)
                 { 
                 ?> 
                 <h3 style="text-align: center"><?php echo "Malformed XML.Please fix your query and try again."?></h3>
                 <?php 
                    return NULL;
                 }
        
              $yahooStock->processRSSFeed();
              $outputList = $yahooStock->getRSSList();
             
        ?>
        
        
         <?php 
            if($outputList != NULL || count($outputList) > 0)
                { ?>
     
        <div style="margin-top :20px;margin-left:20px;" >
        <?php foreach($outputList as $list) { ?>
        <li><a target="_blank" href="<?php echo $list->getLink()?>"><?php echo $list->getTitle() ?></href></li>
         <?php } ?> 
     
     </div>
        <?php }else{?>
        <h4 style="margin-left:150px;">Financial Company News is not available&nbsp;&nbsp;</h2>
        <?php } ?>
        </div>
      <?php } ?>
      <?php } ?>  
     <?php }catch(Exception $e)
        { echo 'Caught Exception: '.$e->getMessage().'\n';} ?>
    <NOSCRIPT>   
    </body>

</html>