<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/img/ico/favicon.ico">

    <title>Signin</title>
    <link href="<?php echo $stylesheet ?>" rel="stylesheet" type="text/css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
    	<div class="panel panel-default form-signin-panel">
  			<div class="panel-heading">Please Sign In</div>
  			<div class="panel-body">
  			
		  			<?php if( ! empty($messages)): ?>
		  				<div class="alert alert-danger">
		  				<?php foreach($messages as $message): ?>
		  					<p><i class="fa fa-warning"></i> <?= $message ?></p>
		  				<?php endforeach; ?>
		  				</div>
		  			<?php endif; ?>
  			
			      <form role="form" class="form-signin" method="POST">
			        <input name="email" type="email" class="form-control" placeholder="Email address" required autofocus>
			        <input name="password" type="password" class="form-control" placeholder="Password">
			        <div class="help clearfix">
				        <a class="pull-right" href="javascript:alert('Coming soon !')">Help <i class="fa fa-question-circle"></i></a>
			    	</div>
			        <div class="clearfix">
				        <label class="remember pull-left">
				          <input type="checkbox" name="remember-me" value="1"> Remember me
				        </label>
			        	<button class="btn btn-primary pull-right" type="submit">Sign in</button>
			        </div>
			      </form>
  			</div>
		</div>
    </div> <!-- /container -->
  </body>
</html>