$.ajaxSetup({async: false});

var map;
var clickarea=0;
$.getJSON('DengueTN.json', function (data) {  //DengueTN.json  OpenDataCase
    DengueTN = data
});
$.getJSON('OpenDataBI.json', function (data) { 
    BITN = data
});
function CancelAreaFocus()
{
        clickarea=0; //取消關注區域
        $('#detial > #title').empty();
}

function initialize() {
    /*map setting*/
    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('cunliTN.json', function (data) {
        cunli = map.data.addGeoJson(data);
    });

    cunli.forEach(function (value) {
        var key = value.getProperty('T_Name') + value.getProperty('V_Name');
        var count = 0;
        if(DengueTN[key]) {
            DengueTN[key].forEach(function(val) {
                count += val[1];
            });
        }
        value.setProperty('num', count);
    });

    map.data.setStyle(function (feature) {
        var num = feature.getProperty('num');
        color = ColorBar(num);
        return {
            fillColor: color,
            fillOpacity: 0.6,
            strokeColor: 'gray',
            strokeWeight: 1
        }
    });

    map.data.addListener('mouseover', function (event) {
        if(!clickarea)
        {
            map.data.revertStyle();
            var Cunli = event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
            map.data.overrideStyle(event.feature, {fillColor: 'pink'});
            $('#detial > #content').empty();
            $('#detial > #content').append('<div>' + Cunli + ' ：' + event.feature.getProperty('num') + ' 例</div>');
            //Try to list all case of 登革熱
            $('#detial > #scroll_page').empty();
            if(DengueTN[Cunli]) {
                $('#detial > #scroll_page').append('<table id="t1" border="1"><tr><td align="center">日期</td><td>案例數</td><td>布氏指數</td></tr>');
                var rowi=0;
                DengueTN[Cunli].forEach(function(val) {
                rowi++;
                    $('#detial > #scroll_page > #t1').append('<tr id="r'+rowi+'"><td>'+val[0]+'</td><td align="right">'+val[1]+'</td>');//</tr>');
                    if(BITN[Cunli])
                    {
                        var NumOfData=BITN[Cunli].length;
                        var findtarget=0;
                        for(var i=0;i<NumOfData;i++)
                        {   //只會有一筆資料
                            if(BITN[Cunli][i][0]==val[0])
                            {
                            findtarget=1;
                            $('#t1 td:last').after('<td align="right">'+BITN[Cunli][i][1]+'</td></tr>');
                            break;
                            }
                        }
                        if(!findtarget) $('#t1 td:last').after('<td></td></tr>');
                    }else $('#t1 td:last').after('<td></td></tr>');
                }//end of function(val)
                );//end of forEach
            }//end of if(DengueTN[Cunli]) 
            $('#detial > #scroll_page >#t1').append('</table>');
        }
    });//end of  map.data.addListener('mouseover', function (event)

    map.data.addListener('mouseout', function (event) {
    
        if(!clickarea) //若無關注
        {
        map.data.revertStyle();
        $('#detial > #content').empty();
        $('#detial > #scroll_page').empty();

        }
    });
    map.data.addListener('click', function (event) {
    if(event.ub.button==1) //按下中鍵
    {
        clickarea=0; //取消關注區域
        $('#detial > #title').empty();
    }
    else //按下左鍵
    {
        if(clickarea==1)
        {
        alert("欲更換關注目標，請先按滑鼠中鍵取消原關注區域");
        return;
        }
        clickarea =1;//關注區域
        var Cunli = event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
         $('#detial > #title').append("關注"+Cunli+"中<br>(請按滑鼠中鍵取消關注)");
        if ($('#myTab a[name|="' + Cunli + '"]').tab('show').length == 0) {
            $('#myTab').append('<li><a name="' + Cunli + '" href="#' + Cunli + '" data-toggle="tab">' + Cunli +
                    '<button class="close" onclick="closeTab(this.parentNode)">×</button></a></li>');  //recordPNode this.parentNode
            $('#myTabContent').append('<div class="tab-pane fade" id="' + Cunli + '"><div></div></div>');
            $('#myTab a:last').tab('show');
            createStockChart(Cunli);
            $('#myTab li a:last').click(function (e) {
                $(window).trigger('resize');
            });//end of $('#myTab li a:last').click(function (e) 
        }//end of if ($('#myTab a[name|="' + Cunli + '"]').tab('show').length == 0)
    }//end of else
    });
    createStockChart('total');
}

function createStockChart(Cunli) {
    var series = [];
    if(!DengueTN[Cunli]) return;
    for (var i = 0; i < DengueTN[Cunli].length; i = i + 1) {
        series.push([new Date(DengueTN[Cunli][i][0]).getTime(), DengueTN[Cunli][i][1]]);
    }
    if((!(Cunli=="total"))&&(BITN[Cunli]))
    {
        var series2=[];
        for(var j=0;j<BITN[Cunli].length;j++)
        {
         series2.push([new Date(BITN[Cunli][j][0]).getTime(),BITN[Cunli][j][1]]);
        }
        $('#' + Cunli).highcharts('StockChart', {
            chart: {
                alignTicks: false,
                width: $('#myTabContent').width(),
                height: $('#myTabContent').height()
            },
            rangeSelector: {
                enabled: false
            },
            tooltip: {
                enabled: true,
                positioner: function () {
                    return {x: 10, y: 30}
                }
            },
            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                            
                            }
                        }
                    },
                }
            },
            series: [{
                    type: 'column',
                    name: Cunli,
                    data: series,
                },{type:'column', name:"布氏指數", data:series2,}]
        });



    }else
    { 
        $('#' + Cunli).highcharts('StockChart', {
            chart: {
                alignTicks: false,
                width: $('#myTabContent').width(),
                height: $('#myTabContent').height()
            },
            rangeSelector: {
                enabled: false
            },
            tooltip: {
                enabled: true,
                positioner: function () {
                    return {x: 10, y: 30}
                }
            },
            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                            
                            }
                        }
                    },
                }
            },
            series: [{
                    type: 'column',
                    name: Cunli,
                    data: series,
                }]
        });
    }//end of if(cunli=="'total'")...else
}

$(window).resize(function () {
    var len = $('#myTabContent > div').length;
    for (var i = 1; i <= len; i = i + 1) {
        $('#myTabContent > div:nth-child(' + i + ')').highcharts().setSize($('#myTabContent').width(), $('#myTabContent').height());
    }
});

function closeTab(node) {
    var nodename = node.name;
    node.parentNode.remove();
    $('#' + nodename).remove();
    $('#myTab a:first').tab('show');
}

google.maps.event.addDomListener(window, 'load', initialize);