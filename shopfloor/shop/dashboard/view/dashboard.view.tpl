<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<div id="dataForemanDashboad" style="display: none;">
    <div id="factory"  ></div>
    <ul id="factories">
        {foreach from=$factories item=factory}
            <li>{$factory}</li>
        {/foreach}

    </ul>
    <ul id="families">
        {foreach from=$familyMatrix item=family}
            <li data-factory ="{$family.factory}" data-id="{$family.id}">{$family.family}</li>
        {/foreach}

    </ul>
</div>
<div id="containerForemanDashboard">
</div>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">
<link rel="stylesheet" href="/shop/css/groupLeaderDashboard.css" media="screen">
<link rel="stylesheet" href="/shop/css/groupLeaderPrintDashboard.css" media="print">
<script src="/shop/dist/js/dashboard.js"></script>