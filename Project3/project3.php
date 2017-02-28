<!-- CS571 Homework#6 -->
<!-- Author: Siqi Dong -->

<!DOCTYPE html>
<?php
	date_default_timezone_set("America/Los_Angeles");

	function outputStockList($inputtext) {

		$address = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/xml?input=".$inputtext;
		$content = @@file_get_contents($address);
		if($content == FALSE || strlen($content) == 0) {
			displayNoRecords();
			return;
		}
		$xmlfile = @simplexml_load_file($address) or die(displayNoRecords());
		echo "<div id='hiddenform'></div>";
		echo "<div id='lookup'>";
		echo "<table><tr><th>Name</th><th>Symbol</th><th>Exchange</th><th>Details</th></tr>";
		foreach($xmlfile->children() as $oneresult) {
			$name = $oneresult->Name;
			$symbol = $oneresult->Symbol;
			$exchange = $oneresult->Exchange;
			echo "<tr><td>".$name."</td>";
			echo "<td>".$symbol."</td>";
			echo "<td>".$exchange."</td>";
			echo "<td><a href='javascript:void(0)' onclick='getInfo(\"$symbol\",\"$inputtext\")'>More Info</a></td></tr>";

		} 
		echo '</table></div>';
            
	}

	function outputDetails($symbol) {

		$companyurl = "http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=".$symbol;
		try {
			$xml_data = @file_get_contents($companyurl);
		}
		catch(Exception $error){
			displayNoDetials();
			return;
        }
		if ($xml_data == FALSE) {
			displayNoDetials();
			return;
		}
		$jsonObject = json_decode($xml_data, true);
		if ($jsonObject['Status'] != "SUCCESS") {
			displayNoDetials();
			return;
		}
		$stock_name = $jsonObject['Name'];
		$stock_symbol = $jsonObject['Symbol'];
		$stock_lastprice = $jsonObject['LastPrice'];
		$stock_change = getRounded($jsonObject['Change']);
		$stock_icon1 = getIcon($stock_change);
		$stock_changepct = getRounded($jsonObject['ChangePercent']);
		$stock_icon2 = getIcon($stock_changepct);
		$stock_time = date("Y-m-d H:i A",strtotime($jsonObject['Timestamp']));
		$stock_marketcap = getMC($jsonObject['MarketCap']);
		$stock_volume = number_format($jsonObject['Volume']);
		$changeytd_orin = $jsonObject['ChangeYTD'];
		$changeytd = getRounded($stock_lastprice-$changeytd_orin);
		$stock_changeytd = checkNegative($changeytd);
		$stock_icon3 = getIcon($changeytd);
		$stock_changepctytd = getRounded($jsonObject['ChangePercentYTD']);
		$stock_icon4 = getIcon($stock_changepctytd);
		$stock_high = $jsonObject['High'];
		$stock_low = $jsonObject['Low'];
		$stock_open = $jsonObject['Open'];


		echo "<div id='query'>";
		echo "<table><tr><th>Name</th><td>".$stock_name."</td></tr>";
		echo "<tr><th>Symbol</th><td>".$stock_symbol."</td></tr>";
		echo "<tr><th>Last Price</th><td>".$stock_lastprice."</td></tr>";
		echo "<tr><th>Change</th><td>".$stock_change.$stock_icon1."</td></tr>";
		echo "<tr><th>Change Percent</th><td>".$stock_changepct."%".$stock_icon2."</td></tr>";
		echo "<tr><th>Timestamp</th><td>".$stock_time." PST</td></tr>";
		echo "<tr><th>Market Cap</th><td>".$stock_marketcap."</td></tr>";
		echo "<tr><th>Volume</th><td>".$stock_volume."</td></tr>";
		echo "<tr><th>Change YTD</th><td>".$stock_changeytd.$stock_icon3."</td></tr>";
		echo "<tr><th>Change Percent YTD</th><td>".$stock_changepctytd."%".$stock_icon4."</td></tr>";
		echo "<tr><th>High</th><td>".$stock_high."</td></tr>";
		echo "<tr><th>Low</th><td>".$stock_low."</td></tr>";
		echo "<tr><th>Open</th><td>".$stock_open."</td></tr>";
		echo "</table></div>";
	}

	function checkNegative($number) {
		if ($number >= 0) {
			return $number;
		}
		else {
			return "(".$number.")";
		}
	}

	function getMC($number) {
		$updatedNum;
		if ($number/1000000000 >= 0.01) {
			$updatedNum = getRounded($number/1000000000)." B";
		}
		else {
			$updatedNum = getRounded($number/1000000)." M";
		}
		return $updatedNum;
	}

	function getRounded($beforeNum) {
		$afterNum = round($beforeNum,2);
		return $afterNum;
	}

	function getIcon($number) {
		$image;
		$imagesrc = "";
		if ($number > 0) {
			$image = "Green_Arrow_Up.png";
			$imagesrc = "<img class=\"icon\" src=\"http://cs-server.usc.edu:45678/hw/hw6/images/".$image." \" alt=\"".$image."\"";
		}
		if ($number < 0){
			$image = "Red_Arrow_Down.png";
			$imagesrc = "<img class=\"icon\" src=\"http://cs-server.usc.edu:45678/hw/hw6/images/".$image." \" alt=\"".$image."\"";
		}
		return $imagesrc;
	}

	function displayNoRecords() {
		echo "<div id='norecords'><p class='nodisplay'>No Records has been found</p></div>";
	}

	function displayNoDetials() {
		echo "<div id='nodetails'><p class='nodisplay'>There is no stock information available</p></div>";
	}

?>

<html>
<head>
	<title>Homework 6 | Siqi Dong</title>
	<style>
		body {
			text-align: center;
			line-height: 10px;
		}
		div.box {
			border: 2px solid #E6E6E6;
			background-color: #F3F3F3;
			padding: 18px 10px 20px 10px;
			margin: 10px auto;
			width: 400px;
			height: 125px;
			position: relative;
		}
		img.icon {
			width:15px;
			height:15px;
		}
		p.nodisplay {
			font-family: Arial, Helvetica, sans-serif;
			border: 2px solid #CCCCCC;
			background-color: #FAFAFA;
			margin: 10px auto;
			padding: 8px;
			width: 600px;
		}
		#buttons {
			position: absolute;
			left: 240px;
			top: 85px;
		}
		#mand {
			position: absolute;
			font-style:italic;
			text-align: left;
			left: 10px;
			top: 115px;
		}
		#link {
			position: absolute;
			left: 175px;
			top: 135px;
		}
		#lookup table {
			font-family: Arial, Helvetica, sans-serif;
			text-align: left;
			border: 2px solid #CCCCCC;
			border-collapse: collapse;
			margin: 10px auto;
			width: 660px;
		}
		#lookup table th {
			border: 1px solid #CCCCCC;
			background-color: #F3F3F3;
			height: 25px;
		}
		#lookup table td {
			border: 1px solid #CCCCCC;
			background-color: #FAFAFA;
			height: 25px;
		}
		#query table {
			font-family: Arial, Helvetica, sans-serif;
			border: 2px solid #CCCCCC;
			border-collapse: collapse;
			margin: 10px auto;
			width: 650px;
		}
		#query table th {
			border: 1px solid #CCCCCC;
			background-color: #F3F3F3;
			text-align: left;
			height: 25px;
		}
		#query table td {
			border: 1px solid #CCCCCC;
			background-color: #FAFAFA;
			text-align: center;
			height: 25px;
		}
		
		</style>

	<script language="javascript" type="text/javascript">

		function getInfo(company, originInput) {
			
			var hiddenhtml = "<form name=\"search2\" method=\"GET\" action=\"<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>\">";
			hiddenhtml += "<input type=\"hidden\" name=\"query\" value="+company+">";
			hiddenhtml += "<input type=\"hidden\" name=\"originInput\" value="+originInput+"></form>";
			document.getElementById("hiddenform").innerHTML = hiddenhtml;
			document.search2.submit();
		}

		function clearForm() {
			
			document.getElementById("myForm").inputtext.value="";
			if (document.getElementById("lookup")!=null) {
				document.getElementById("lookup").innerHTML = "";
			}
			if (document.getElementById("query")!=null) {
				document.getElementById("query").innerHTML = "";
			}
			if (document.getElementById("norecords")!=null) {
				document.getElementById("norecords").innerHTML = "";
			}
			if (document.getElementById("nodetails")!=null) {
				document.getElementById("nodetails").innerHTML = "";
			}
		}

	</script>
</head>

<body>
	<div class="box">
		<form id="myForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<span style="font-style:italic;font-size:30px;font-weight:bold;">Stock Search</span><hr color="#DCDCDC">
		<p style="text-align:left;">Enter Company Name or Symbol:*
		<input id="inputtext" name="inputtext" required x-moz-errormessage="Please enter Name or Symbol"
		style="text-align: left;" value="<?php if(isset($_POST["inputtext"])) { echo $_POST["inputtext"]; }
			    if(isset($_GET["originInput"])) {echo $_GET["originInput"];} 
		?>"/></p>
		<span id="buttons">
		<input type="submit" name="submit" value="Search" onclick="submitForm()">
        <input type="button" value="Clear" onclick="clearForm()">
		</span>
		</form>
		<span id="mand">* - Mandatory fields.</span>
		<span id="link"><a href="http://www.markit.com/product/markit-on-demand">Powered by Markit on Demand</a></span>
	</div>

	<?php 
		if(isset($_POST["submit"])) {
            if(isset($_POST["inputtext"])) { 
				outputStockList($_POST["inputtext"]);  
            }
        }

        if(isset($_GET["query"])) {
            outputDetails($_GET["query"]); 
        }
        
    ?>
<noscript>
</body>
</html>