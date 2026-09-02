<html>
    <head>
        {literal}
        <style>
            html {
                font-family: Arial, sans-serif;
                font-size: 22pt;
            }
            p {
                text-align: center;
                font-weight: bold;
            }
        </style>
        {/literal}
    </head>
    <body>
        <p><img src="http://www.tld-gse.com/shared/tld_logos/{$image}.jpg"/></p>
        <p><img src="http://www.tld-gse.com/products/prod_image/{$model|replace:' ':'_'}.jpg" width="400px" /></p>
        <br />
        <p style="font-size: 30pt;">OPERATION AND<br />MAINTENANCE MANUAL</p>
        <p style="font-size: 24pt;">{$model}</p>
        <p>{$serialNumber}</p>
    </body>
</html>