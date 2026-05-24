<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>403 - Access Denied</title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>">
	<link href="<?php echo base_url('assets/font-awesome/css/font-awesome.min.css'); ?>" rel="stylesheet" type="text/css" />
	<style>
		body { 
			background: #f8f9fa; 
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		}
		.container { 
			max-width: 640px; 
			margin-top: 10vh; 
		}
		.error-icon {
			font-size: 5rem;
			color: #dc3545;
			margin-bottom: 1rem;
		}
		.alert-box {
			background: #fff;
			border-radius: 8px;
			padding: 2rem;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			margin-bottom: 1.5rem;
		}
		.permission-info {
			background: #f8f9fa;
			border-left: 4px solid #dc3545;
			padding: 1rem;
			margin-top: 1rem;
			border-radius: 4px;
		}
		.permission-info strong {
			color: #495057;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="alert-box text-center">
			<div class="error-icon">🔒</div>
			<h1 class="display-4 text-danger">403</h1>
			<h2 class="h4 mb-3">Access Denied</h2>
			<p class="lead text-muted">You don't have permission to access this resource.</p>
			
			<div class="mt-4">
				<p class="text-muted">Please contact your system administrator if you believe you should have access to this feature.</p>
				<div class="d-flex justify-content-center mt-3 flex-wrap">
					<a href="<?php echo site_url('home'); ?>" class="btn btn-primary mr-2 mb-2">
					<i class="fa fa-home"></i> Go to Dashboard
				</a>
					<a href="<?php echo site_url('logout'); ?>" class="btn btn-outline-secondary mb-2">
						<i class="fa fa-sign-out"></i> Logout
					</a>
				</div>
			</div>
		</div>
	</div>
</body>
</html>


