<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Change Password | Troja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/font/flaticon.css">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/style.css">
    <style>
        .security-notice {
            background: transparent;
            color: white;
            padding: 15px;
            border: 2px solid white;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }
        .security-notice h4 {
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }
        .security-notice p {
            margin: 0;
            color: white;
            font-size: 14px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .optional-field {
            color: #666;
            font-style: italic;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
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
                        <a href="#" class="fxt-logo">
                            <img src="<?php echo base_url(); ?>assets/images/logo-dark.png" alt="Logo">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="fxt-content">
                        <div class="security-notice">
                            <h4><i class="fa fa-shield-alt"></i> Security Notice</h4>
                            <p>For your security, you must change your password before accessing the system. Only you will know your new password.</p>
                        </div>
                        
                        <h2>Change Your Password</h2>
                        
                        <div class="fxt-form">
                            <form id="changePasswordForm">
                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <input 
                                        type="password" 
                                        id="new_password" 
                                        class="form-control" 
                                        name="new_password" 
                                        placeholder="Enter new password (minimum 6 characters)" 
                                        required 
                                        autocomplete="new-password"
                                        minlength="6"
                                    >
                                    <i toggle="#new_password" class="fa fa-eye toggle-password field-icon"></i>
                                    <div id="password-strength" class="password-strength"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        class="form-control" 
                                        name="confirm_password" 
                                        placeholder="Confirm your new password" 
                                        required 
                                        autocomplete="new-password"
                                    >
                                    <i toggle="#confirm_password" class="fa fa-eye toggle-password field-icon"></i>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_username">New Username <span class="optional-field">(Optional)</span></label>
                                    <input 
                                        type="text" 
                                        id="new_username" 
                                        class="form-control" 
                                        name="new_username" 
                                        placeholder="Enter new username (optional)" 
                                        autocomplete="username"
                                        pattern="[a-zA-Z0-9]+"
                                        title="Username can only contain letters and numbers"
                                    >
                                    <small class="form-text text-muted">Leave blank to keep current username</small>
                                </div>
                                
                                <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                                <div id="success-message" class="alert alert-success" style="display: none;"></div>
                                
                                <div class="form-group">                               
                                    <button type="submit" class="fxt-btn-fill" id="submitBtn">
                                        <i class="fa fa-key"></i> Change Password & Continue
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="fxt-footer">
                            <p><i class="fa fa-info-circle"></i> Your password is encrypted and only you will have access to it.</p>
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
    
    <script>
        $(document).ready(function() {
            // Password strength checker
            $('#new_password').on('input', function() {
                var password = $(this).val();
                var strength = checkPasswordStrength(password);
                var strengthDiv = $('#password-strength');
                
                if (password.length > 0) {
                    strengthDiv.show();
                    strengthDiv.removeClass('strength-weak strength-medium strength-strong');
                    strengthDiv.addClass('strength-' + strength.level);
                    strengthDiv.text(strength.text);
                } else {
                    strengthDiv.hide();
                }
            });
            
            // Password confirmation checker
            $('#confirm_password').on('input', function() {
                var password = $('#new_password').val();
                var confirmPassword = $(this).val();
                
                if (confirmPassword.length > 0) {
                    if (password === confirmPassword) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                    }
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });
            
            // Form submission
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                var submitBtn = $('#submitBtn');
                var originalText = submitBtn.html();
                
                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                
                // Hide previous messages
                $('#error-message, #success-message').hide();
                
                $.ajax({
                    url: '<?php echo site_url('login/process_password_change'); ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#success-message').text(response.message).show();
                            setTimeout(function() {
                                window.location.href = '<?php echo site_url('home'); ?>';
                            }, 2000);
                        } else {
                            $('#error-message').text(response.message).show();
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        $('#error-message').text('An error occurred. Please try again.').show();
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            function checkPasswordStrength(password) {
                var strength = 0;
                var text = '';
                
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                if (strength < 2) {
                    return { level: 'weak', text: 'Weak password' };
                } else if (strength < 4) {
                    return { level: 'medium', text: 'Medium strength password' };
                } else {
                    return { level: 'strong', text: 'Strong password' };
                }
            }
        });
    </script>
</body>
</html>
