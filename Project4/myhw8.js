/* CS571 Homework#8 */
/* Author: Siqi Dong */ 


var company_name = null;
var company_symbol = null;
var company_lastprice = null;
var company_change = null;
var company_percent = null;
var company_chart = null;
var refreshIntervalId;
$(document).ready(function(){

  //  localStorage.clear();
    $("#myBtn1").prop("disabled",true);
    loadBoard();

    $( "#inputtext" ).autocomplete({
    source: function( request, response ) {
        $.ajax({
            url: "http://siqidong.us-west-1.elasticbeanstalk.com",
            dataType: "json",
            data: {
                search: request.term,
                type: "lookup" 
            },
            success: function (data) {
            response( $.map( data, function( item ) {
            return {
                value: item["Symbol"],
                label: item["Name"],
                desc: item["Exchange"]
            }
            }));
            }
        });
    },
    select: function( event, ui ) {
    $( "#inputtext" ).val( ui.item.value );
        return false;
    }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
    return $( "<li>" )
    .append( item.value + " - " + item.label + " ( " + item.desc + " )" )
    .appendTo( ul );
    };

    $("#myCarousel").carousel("pause");

    $("#myBtn1").click(function(){
        $("#myCarousel").carousel(1);
    });

    $("#myBtn2").click(function(){
        loadBoard();
        $("#myCarousel").carousel(0);
    });

    $("#refresh").change(function() {
        if ($(this).is(':checked')){
            refreshIntervalId = window.setInterval(function(){
                loadBoard();
            }, 5000);
        }else {
            clearInterval(refreshIntervalId);
        }
    })

    $("#autorefresh").click(function(){
        loadBoard();
    }); 

    $("#favoriteBtn").click(function(){
        $("#myStar").toggleClass("bis");

        var list = localStorage.getItem("favList");
        var obj = [];
        if(list) {
            obj = JSON.parse(list);
            var listLen = obj.length;
            for (var i = 0; i<listLen; i++) {
                if(obj[i].symbol===company_symbol) {
                    //delete this symbol
                    obj.splice(i,1);
                    localStorage.setItem("favList",JSON.stringify(obj));
                    return;
                }
            }
            //add this symbol
            obj.push({"symbol":company_symbol});
            localStorage.setItem("favList",JSON.stringify(obj));
            return;
        }
        else {
            obj.push({"symbol":company_symbol});
            localStorage.setItem("favList",JSON.stringify(obj));
            return;
        }

    });

    $("#myForm").submit(function(){
        var input=document.getElementById("inputtext").value;

        ajaxRequest(input, function(data) {
            var status = data.Status;

            if (status==="Success") {

                $("#message").html("");
                $("#myBtn1").prop("disabled",false);
                $("#myCarousel").carousel(1);
                checkFavorite(input);

                company_name = data.Name;
                company_symbol = data.Symbol;
                company_lastprice = data.LastPrice;
                company_change = data.Change;
                company_percent = data.ChangePercent;
                company_chart = "http://chart.finance.yahoo.com/t?s=".concat(company_symbol, "&lang=en-US&width=200&height=200");
               
                getStockDetails(data);
                getHistorical(company_symbol);
                getNewsFeeds(company_symbol);
            }
            if (status==="No") {
                $("#message").html("Select a valid entry");
            }
            if (status==="Fail") {
                $("#message").html("Stock information is not available");
            }

        });

        return false;
      
    });


    $("#fbBtn").click("click", function(){
        var message = null;
        message = {
            title: ('Current Stock Price of ').concat(company_name,' is $',company_lastprice),
            description:('Stock Information of ').concat(company_name, ' (', company_symbol,')'),
            picture: company_chart,
            subtitle: ('Last Traded Price: $ ').concat(company_lastprice,', Change: ',company_change,' (',company_percent,'%)')
            
        }; 
        FB.ui({
            method: 'feed',
            name: message.title,
            picture: message.picture,
            description: message.description,
            caption: message.subtitle,
            link: 'http://dev.markitondemand.com/'
          },
          function(response) {
            if (response && response.post_id) {
                alert("Posted Successfully");
            }
            else {
                alert("Not Posted");
            }   
          });
    });
});

function checkFavorite(toCheck) {
    var list = localStorage.getItem("favList");
    if(list) {
        var obj = JSON.parse(list);
        var listLen = obj.length;
        for (var i = 0; i<listLen; i++) {
            if(obj[i].symbol.toUpperCase()===toCheck.toUpperCase()) {
                $("#myStar").attr('class', 'glyphicon glyphicon-star bis');
                return;
            }
        }
    }
    $("#myStar").attr('class', 'glyphicon glyphicon-star');
    return;
}

function loadBoard() {
    var boardHTML = "<div class=\"table-responsive\"><table class=\"table table-striped\"><tbody>";
    boardHTML += "<tr><th>Symbol</th><th>Company Name</th><th>Stock Price</th><th>Change (Change Percent)</th><th>Market Cap</th><th></th></tr>";

    var list = localStorage.getItem("favList");
    if (list==null) {
            boardHTML += "</tbody></table></div>";
            $("#myBoard").html(boardHTML);
    }
    else {
        var thisHTML = [];
        var promises = [];
        var favObj = JSON.parse(list);
        var objLen = favObj.length;

        for (var i = 0; i < objLen; i++) {

            (function(i) {
                var company = favObj[i].symbol;
                var request = $.ajax({
                    url: "http://siqidong.us-west-1.elasticbeanstalk.com/",
                    type: "GET",
                    data: { symbol: company,
                            type: "getQuote"
                        },
                    success: function(response){
                        var returnedData = JSON.parse(response);
                        thisHTML[i] = writeTable(returnedData, i);
                    },
                    error: function(xhr, status, error) { 
                        alert(xhr.status);         
                    }
                })
                promises.push(request);
            })(i);
        }
        $.when.apply(null, promises).done(function() {
            for (var i = 0; i < thisHTML.length; i++) {
                boardHTML += thisHTML[i];
            }
            boardHTML += "</tbody></table></div>";
            $("#myBoard").html(boardHTML);
        })
    }
}

function writeTable(data, id) {
    var tableHTML = "";
    tableHTML += "<tr><td class=\"link\" onclick=\"displayStock(event)\">"+data.Symbol+"</td>";
    tableHTML += "<td>"+data.Name+"</td>";
    tableHTML += "<td>$ "+data.LastPrice+"</td>";
    tableHTML += "<td>"+processChange(data.Change,data.ChangePercent)+"</td>";
    tableHTML += "<td>"+data.MarketCap+"</td>";
    tableHTML += "<td id=\""+data.Symbol+"\"><button type=\"button\" class=\"btn btn-default btn-md\" onclick=\"deleteStock(event)\" title=\"Delete stock\"><span class=\"glyphicon glyphicon-trash\"></span></button></td></tr>";
    return tableHTML;
}

function clearForm() {
    $("#myForm")[0].reset();
    $("#message").html("");
    $("#myCarousel").carousel(0);
    $("#myBtn1").prop("disabled",true);
}

function ajaxRequest(input, callBack) {

    $.ajax({
        url: "http://siqidong.us-west-1.elasticbeanstalk.com/",
        type: "GET",
        data: {symbol: input,
               type: "getQuote"
               },
        success: function(response){
            var returnedData = JSON.parse(response);
            callBack(returnedData);
        },
        error: function(xhr, status, error) { 
            alert(xhr.status);         
        }
    });

  return;
}


function displayStock(e) {

    var info = e.target.innerText;

    $("#message").html("");
    $("#myCarousel").carousel(1);
    $("#myStar").attr('class', 'glyphicon glyphicon-star bis');

    ajaxRequest(info, function(data) {

        company_name = data.Name;
        company_symbol = data.Symbol;
        company_lastprice = data.LastPrice;
        company_change = data.Change;
        company_percent = data.ChangePercent;
        company_chart = "http://chart.finance.yahoo.com/t?s=".concat(company_symbol, "&lang=en-US&width=200&height=200");

        getStockDetails(data);
        getHistorical(data.Symbol);
        getNewsFeeds(data.Symbol);
    });
}


function deleteStock(e) {
    var info = e.target.closest("td").id;

    var favorite = localStorage.getItem("favList");
    var favStock = [];
    obj = JSON.parse(favorite);
    var listLen = obj.length;
    for (var i = 0; i<listLen; i++) {
        if(obj[i].symbol===info) {
            obj.splice(i,1);
            localStorage.setItem("favList",JSON.stringify(obj));
            loadBoard();
            return;
        }
    }
}

function getStockDetails(data) {

    var change = processChange(data.Change, data.ChangePercent);
    var changeYTD = processChange(data.ChangeYTD, data.ChangePercentYTD);

    var stockDetails = "<p><b>Stock Details</b></p>";
    stockDetails += "<table class=\"table table-striped\">";
    stockDetails += "<tr><th>Name</th><td>"+data.Name+"</td></tr>";
    stockDetails += "<tr><th>Symbol</th><td>"+data.Symbol+"</td></tr>";
    stockDetails += "<tr><th>Last Price</th><td>$ "+data.LastPrice+"</td></tr>";
    stockDetails += "<tr><th>Change (Change Percent)</th><td>"+change+"</td></tr>";
    stockDetails += "<tr><th>Time and Date</th><td>"+data.Timestamp+"</td></tr>";
    stockDetails += "<tr><th>Market Cap</th><td>"+data.MarketCap+"</td></tr>";
    stockDetails += "<tr><th>Volume</th><td>"+data.Volume+"</td></tr>";
    stockDetails += "<tr><th>Change YTD (Change Percent YTD)</th><td>"+changeYTD+"</td></tr>";
    stockDetails += "<tr><th>High Price</th><td>$ "+data.High+"</td></tr>";
    stockDetails += "<tr><th>Low Price</th><td>$ "+data.Low+"</td></tr>";
    stockDetails += "<tr><th>Opening Price</th><td>$ "+data.Open+"</td></tr>";
    stockDetails += "</table>";
   
    $("#menu1detail").html(stockDetails);

    var stockChart = "<img class=\"img-responsive\"  style=\"width:100%;\" src=\"http://chart.finance.yahoo.com/t?s="+data.Symbol+"&lang=en-US&width=500&height=400\" alt=\"Stock_Chart\">";
    $("#menu1chart").html(stockChart);
}

function getHistorical(symbol) {

    var para = { Normalized: false,
                  NumberOfDays: 1095,
                  DataPeriod: "Day",
                  Elements: [ { Symbol: symbol, Type: "price", Params: ["ohlc"] } ] };

    var chartURL = JSON.stringify(para);

    $.ajax({
        beforeSend:function(){
            $("#menu2").text("Loading chart...");
        },
        url: "http://siqidong.us-west-1.elasticbeanstalk.com/",
        type: "GET",
        data: {symbol: chartURL,
               type: "chart"
               },
        success: function(response){
            var returnedData = JSON.parse(response);
            plotChart(returnedData, symbol);

        },
        error: function(xhr, status, error) { 
            alert(xhr.status);         
        }
    });
    return;

}

function getNewsFeeds(symbol) {

    var newsHTML = "";

    $.ajax({
        url: "http://siqidong.us-west-1.elasticbeanstalk.com/",
        type: "GET",
        data: {search: symbol,
               type: "news"
               },
        success: function(response){
            var returnedData = JSON.parse(response);
            var dataLen = returnedData.d.results.length;

            for (var i = 0; i < dataLen; i++) {
                newsHTML += "<div class=\"well\" style=\"background:linear-gradient(rgb(238,238,238),rgb(248,248,248));\">";
                newsHTML += "<p><a href=\""+returnedData.d.results[i].Url+"\" target=\"_blank\">"+returnedData.d.results[i].Title+"</a></p>";
                newsHTML += "<p>"+returnedData.d.results[i].Description+"</p><br>";
                newsHTML += "<p style=\"font-weight:bold\">Publisher: "+returnedData.d.results[i].Source+"</p><br>";
                newsHTML += "<p style=\"font-weight:bold\">Date: "+moment(returnedData.d.results[i].Date).format('DD MMM YYYY HH:mm:ss')+"</p></div>";
            }

            $("#menu3").html(newsHTML);

        },
        error: function(xhr, status, error) { 
            alert(xhr.status);         
        }
    });
    return;

}


function processChange(number1, number2) {
    var processNum = "";
    if (number2 > 0) {
        processNum += "<font color=\"green\">"+number1+" ( "+number2+" % ) </font>";
        processNum += "<img src=\"http://cs-server.usc.edu:45678/hw/hw8/images/up.png\" alt=\"Up_Icon\">";
    }
    else if (number2 < 0) {
        processNum += "<font color=\"red\">"+number1+" ( "+number2+" % ) </font>";
        processNum += "<img src=\"http://cs-server.usc.edu:45678/hw/hw8/images/down.png\" alt=\"Down_Icon\">";
    }
    else {
        processNum += number1+" ( "+number2+" % )";
    }
    return processNum;
}

function plotChart(chartData, symbol) {

    var dates = chartData.Dates || [];
    var elements = chartData.Elements || [];
    var stock = [];

    if (elements[0]){

        for (var i = 0, datLen = dates.length; i < datLen; i++) {
            var predat = new Date( dates[i] );
            var dat = Date.UTC(predat.getFullYear(), predat.getMonth(), predat.getDate());
            var pointData = [
                dat,
                elements[0].DataSeries['open'].values[i],
                elements[0].DataSeries['high'].values[i],
                elements[0].DataSeries['low'].values[i],
                elements[0].DataSeries['close'].values[i]
            ];
            stock.push( pointData );
        };
    }


        // Create the chart
        $('#menu2').highcharts('StockChart', {

            rangeSelector : {
                buttons: [{type: 'week',count: 1,text: '1w'}, 
                          {type: 'month',count: 1,text: '1m'}, 
                          {type: 'month',count: 3,text: '3m'}, 
                          {type: 'month',count: 6,text: '6m'}, 
                          {type: 'ytd',text: 'YTD'}, 
                          {type: 'year',count: 1,text: '1y'}, 
                          {type: 'all',text: 'All'}],
                selected : 0,
                inputEnabled: false
            },
            exporting: {
                enabled: false
            },
            title : {
                text : symbol + ' Stock Value'
            },
            yAxis: [{
                title: {
                    text: 'Stock Value'
                },
                height: 200,
                lineWidth: 2
            }],    
            series : [{
                type : 'area',
                name : symbol,
                data : stock,
                threshold : null,
                tooltip: {
                valueDecimals: 2,
                valuePrefix: '$'
                },
                fillColor : {
                    linearGradient : {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops : [
                        [0, Highcharts.getOptions().colors[0]],
                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                    ]
                }
            }]
        });

}



