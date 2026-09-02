<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

{if isset($json)}

{literal}
    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
<link rel="stylesheet"
      href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>
    <script type="text/javascript">

        $(function () {
            'use strict';

            function getRandomData() {
                var numbers = [];
                while (numbers.length < 12) {
                    var number = Math.ceil(Math.random() * 100);
                    if (numbers.indexOf(number) === -1) {
                        numbers.push(number);
                    }
                }
                return numbers;
            }

            $('#container').highcharts({

                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Total PDI CRABS generated, grouped by subtypes <br>(CSC: Customer Scope Change, SOL: SOL Incomplete)'
                },
                xAxis: {
                    categories: {/literal}{$months}{literal}
                },
                yAxis: {
                    allowDecimals: false,
                    min: 0,
                    title: {
                        text: 'Number of PDI CRABS'
                    }
                },
                tooltip: {
                    formatter: function () {
                        return '<b>' + this.series.options.stack + '</b><br/>' +
                                this.series.name + ': ' + this.y + ' CRAB' + (this.y > 1 ? 'S' : '') + '<br/>' +
                                'Total: ' + this.point.stackTotal + ' CRAB' + (this.y > 1 ? 'S' : '');
                    }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        size: '95%',
                        borderWidth: 0,
                        events: {
                            legendItemClick: function () {
                                return false;
                            }
                        },
                        stacking: 'normal'
                    }
                },
                credits: {
                    enabled: false
                },
                series: {/literal}{$json}{literal}
            });
        });

    </script>
{/literal}


{/if}