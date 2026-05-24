<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Login | Troja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/font/flaticon.css">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/style.css">
</head>

<body>
    <section class="fxt-template-layout24" data-bg-image="<?php echo base_url(); ?>assets/login/img/figure/bg24-l.jpg">

        <!-- Video Area Start Here -->
        <div class="fxt-video-background">
            <div class="fxt-video">
                <div id="fxtVideo" data-property="{
                    videoURL:'F_7ZoAQ3aJM', 
                    autoPlay:true, 
                    mute:true, 
                    loop:true, 
                    startAt:0, 
                    opacity:1, 
                    quality:'default', 
                    showControls:false, 
                    optimizeDisplay:true,
                    containment:'.fxt-video-background'
                }">
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-3">
                    <div class="fxt-header text-center">
                        <a href="{{ route('login" class="fxt-logo">
                            <img src="<?php echo base_url(); ?>assets/images/logo-dark.png" alt="Logo">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="fxt-content">
                        <h2>Login to your account</h2>
                        
                        <div class="fxt-form">
                        <form method="POST" action="<?php echo site_url('login/login_validation'); ?>">
    <div class="form-group">
        <input 
            type="text" 
            id="email" 
            class="form-control" 
            name="email" 
            placeholder="Email/User Name" 
            value="<?php echo set_value('email'); ?>" 
            required 
            autocomplete="username"
        >
        <?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
    </div>
    <div class="form-group">
        <input 
            type="password" 
            id="password" 
            class="form-control" 
            name="password" 
            placeholder="Password" 
            required 
            autocomplete="current-password"
        >
        <i toggle="#password" class="fa fa-eye toggle-password field-icon"></i>
        <?php echo form_error('password', '<div class="text-danger">', '</div>'); ?>
    </div>
    
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger">
            <?php echo $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>

    <div class="form-group d-flex justify-content-between align-items-center">
        <div class="checkbox">
            <!-- Optional: Remember me functionality -->
        </div>
        <a href="<?php echo site_url('forgot-password'); ?>" class="switcher-text">Forgot Password?</a>
    </div>
    
    <div class="form-group">                               
        <button type="submit" class="fxt-btn-fill">Log in</button>
    </div>
</form>
                        </div>
                        <div class="fxt-footer">
                            <p>Don't have an account? Please contact system admin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="<?php echo base_url(); ?>assets/login/js/jquery.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/imagesloaded.pkgd.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/jquery.mb.YTPlayer.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/validator.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/main.js"></script>
</body>
</html>
