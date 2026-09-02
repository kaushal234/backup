<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="/shared/sales_app/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/fixedHeader.dataTables.min.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/bootstrap-theme.min.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/datepicker.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/multi-select.css"/>
    <link rel="stylesheet" href="/shared/sales_app/css/style.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $TITLE ?></title>
  </head>
  <body class="<?= $CSS_BODY_CLASS ?>">

    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <div class="row">
        	<div class="col-xs-3 col-xm-4 navbar-header">
        	  <ul class="navbar-brand">
        	    <li><?= $TITLE ?></li>
        	  </ul>
        	</div>
            <div class="col-xs-6 col-xm-4 text-center">
                <br/>
                <?php if (!empty(array_intersect(['role_VPM', 'role_CEO', 'role_GCOO', 'role_GTD', 'ROLE_LATE_GT_GCEO', 'role_GSD'], $userGroups))): ?>
                    <a class="btn btn-default btn-danger" href="<?= "$PHPSELF?page=customers&action=SSOList" ?>">eCustomers by SSO</a><br/>
                <?php elseif (!empty(array_intersect(['role_EVP'], $userGroups))): ?>
                    <a class="btn btn-default btn-danger" href="<?= "$PHPSELF?page=customers&action=myASMList" ?>">eCustomers by ASM</a><br/>
                <?php endif ?>
            </div>
        	<div class="col-xs-3 col-xm-4">
    		  <ul class="nav navbar-nav navbar-right tldlogo">
    	        <li><a href="<?= "$PHPSELF" ?>"><img src="/shared/sales_app/img/tld-logo.png" alt="TLD"></a></li>
    	      </ul>
          	</div>
        </div>
      </div>
    </nav>

	<div class="container">

	  <?php if(!empty($ERROR)): ?>
	  <div class="alert alert-danger alert-dismissible" role="alert">
	    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	    <strong>Error!</strong> <?= $ERROR ?>
	  </div>
	  <?php endif; ?>

	  <?php if(!empty($WARNING)): ?>
	  <div class="alert alert-warning alert-dismissible" role="alert">
	    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	    <strong>Warning!</strong> <?= $WARNING ?>
	  </div>
	  <?php endif; ?>

	  <?php if(!empty($INFO)): ?>
	  <div class="alert alert-info alert-dismissible" role="alert">
	    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	    <strong>Info!</strong> <?= $INFO ?>
	  </div>
	  <?php endif; ?>

	  <?php if(!empty($SUCCESS)): ?>
	  <div class="alert alert-primary alert-dismissible" role="alert">
	    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	    <strong>Success!</strong> <?= $SUCCESS ?>
	  </div>
	  <?php endif; ?>

	  <?= $BODY ?>
	</div>

	<script src="/shared/sales_app/js/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/bootstrap-datepicker.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/dataTables.fixedHeader.min.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/jquery.multi-select.js" type="text/javascript"></script>
    <script src="/shared/sales_app/js/app.js" type="text/javascript"></script>

  <?= $ticket ?>

  </body>
</html>
