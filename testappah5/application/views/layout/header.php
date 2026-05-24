<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Troja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Vehicle Service Center Management ERP System" name="description" />
    <meta content="0711381628" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/images/favicon.ico">
    <!-- jsvectormap css -->
    <link href="<?php echo base_url(); ?>assets/libs/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css" />
    <!-- gridjs css -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libs/gridjs/theme/mermaid.min.css">
    <!-- Layout config Js -->
    <script src="<?php echo base_url(); ?>assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="<?php echo base_url(); ?>assets/css/custom.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Enhanced Sidebar CSS -->
    <link href="<?php echo base_url(); ?>assets/css/sidebar-enhanced.css" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert css-->
    <link href="<?php echo base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />


    <link href="<?php echo base_url(); ?>assets/toastify/toastify.css" rel="stylesheet" type="text/css" />
    <script src="<?php echo base_url(); ?>assets/toastify/toastify.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    
    <!-- Base URL for JavaScript -->
    <script>
        var base_url = '<?php echo base_url(); ?>';
    </script>

    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>

    <!-- Select2 CSS with Bootstrap 5 theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <style>
        :root {
            --theme-default: #009db5;
        }

        .swal2-confirm {
            background-color: #009db5 !important;
            color: #fff !important;
            border: none;
            padding: 0.45rem 1.75rem;
        }

        .swal2-cancel {
            background-color: #d33 !important;
            color: #fff !important;
            border: none;
            padding: 0.45rem 1.75rem;
        }

        .navbar-menu {
            background: #071f47;
            background-color: #1e1e2d;
        }

        .navbar-menu .navbar-nav .nav-link[data-bs-toggle=collapse][aria-expanded=true] {
            color: #f99b0d;
        }

        .navbar-menu .navbar-nav .nav-sm .nav-link.active {
            color: #009db5;
        }

        .navbar-menu .navbar-nav .nav-link.active {
    color: #f99b0d;
}

        .topbar-user {
            background: #fff
        }

        .dt-length {
            display: flex;
            flex-direction: column;
        }

        #dt-length-0+label {
            order: -1;
            margin-bottom: 5px;
            /* Adjust spacing */
            text-transform: capitalize
        }

        .container-fliud {
            padding-top: 20px;
            margin-right: auto;
            margin-left: auto;
        }

        .alert-danger p {
            margin-bottom: 0rem;
        }

        .alert-success p {
            margin-bottom: 0rem;
        }

        .swal-button {
            background-color: var(--theme-default);
        }

        .btn-secondary {
            --vz-btn-bg: var(--theme-default);
            --vz-btn-border-color: var(--theme-default);
        }

        .btn-success {
            --vz-btn-bg: rgb(0, 118, 37);
            --vz-btn-border-color: rgb(0, 118, 37);
        }

       
        .table-info {
            --vz-table-color: rgb(255, 255, 255);
            --vz-table-bg: rgb(4, 71, 86);
        }

        .table-warning {
            --vz-table-color: rgb(255, 255, 255);
            --vz-table-bg: rgb(159, 138, 4);
        }

        .table-primary {
            --vz-table-color: rgb(255, 255, 255);
            --vz-table-bg: rgb(62, 22, 134);
        }

        /* Make Select2 match Bootstrap input height */
.select2-container .select2-selection--single {
    height: 38px !important;
    padding: 5px 12px !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
}

/* Adjust dropdown arrow alignment */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}

/* Adjust dropdown menu width and styling */
.select2-container--default .select2-dropdown {
    border-color: #ced4da !important;
    border-radius: 0.375rem !important;
}

/* Match Bootstrap focus state */
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #ced4da !important;
}

.errorDiv p {
    margin-bottom: 0rem;
}

/* Success Notification (Green) */
.toastify-success {
    background: #007625;
    background: linear-gradient(to right, #007625, #00a335);
    color: white;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0, 118, 37, 0.3);
}

/* Error Notification (Red) */
.toastify-error {
    background: #d32f2f;
    background: linear-gradient(to right, #d32f2f, #f44336);
    color: white;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
}

/* Warning Notification (Orange/Yellow) */
.toastify-warning {
    background: #ff9800;
    background: linear-gradient(to right, #ff9800, #ffc107);
    color: #333;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
}

/* Info Notification (Blue) - Bonus */
.toastify-info {
    background: #1976d2;
    background: linear-gradient(to right, #1976d2, #2196f3);
    color: white;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
}
    </style>
</head>

<body>
    <div id="layout-wrapper">