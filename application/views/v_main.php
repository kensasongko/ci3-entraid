<!DOCTYPE html>

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed"
    dir="ltr"
    data-theme="theme-semi-dark"
    data-assets-path="<?php echo base_url(); ?>assets/"
    data-template="vertical-menu-template"
>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

        <title><?php echo $title;?> | SOP Online</title>

        <meta name="description" content="" />

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="<?php echo base_url(); ?>assets/img/favicon/ficon.ico" />

        <link href="<?php echo base_url(); ?>assets/fonts/gfont_publicsans.css" rel="stylesheet" />

        <!-- Icons -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/fonts/fontawesome.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/fonts/tabler-icons.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/fonts/flag-icons.css" />

        <!-- Core CSS -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/demo.css" />

        <!-- Vendors CSS -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/node-waves/node-waves.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/typeahead-js/typeahead.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/flatpickr/flatpickr.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/select2/select2.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/datatables-bs5/select.dataTables.min.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/sweetalert2/sweetalert2.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendor/libs/formvalidation/dist/css/formValidation.min.css" />
        <!-- Page CSS -->

        <!-- Extra style -->
        <style type="text/css">
            .center-align {
                text-align: center !important;
            }

            #overlay{   
                position: fixed;
                top: 0;
                z-index: 9999;
                width: 100%;
                height:100%;
                display: none;
                background: rgba(0,0,0,0.6);
            }
            .cv-spinner {
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;  
            }
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px #ddd solid;
                border-top: 4px #2e93e6 solid;
                border-radius: 50%;
                animation: sp-anime 0.8s infinite linear;
            }
            @keyframes sp-anime {
                100% { 
                    transform: rotate(360deg); 
                }
            }
            .is-hide{
                display:none;
            }

            @media (pointer: coarse)  {
                /* mobile device */
                .imglogo {
                    width: 45px;
                }
            }

            @media (pointer: fine), (pointer: none) {
                /* desktop */
                .imglogo {
                    width: calc(100% - calc(1.5rem * 2) - 2.5rem);
                }
            }

            @media (pointer: fine) and (any-pointer: coarse) {
                /* touch desktop */
                .imglogo {
                    width: calc(100% - calc(1.5rem * 2) - 2.5rem);
                }
            }
        </style>
        <!-- /Extra style -->

        <!-- Helpers -->
        <script src="<?php echo base_url(); ?>assets/vendor/js/helpers.js"></script>

        <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
        <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
        <script src="<?php echo base_url(); ?>assets/vendor/js/template-customizer.js"></script>
        <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
        <script src="<?php echo base_url(); ?>assets/js/config.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/jquery/jquery.js"></script>
    </head>

    <body>
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
            
                <!-- Menu -->
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                    <div class="app-brand demo">
                        <a href="Dashboard" class="app-brand-link">
                            <span class="app-brand-logo demo" style="height: auto !important;">
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold">codeigniter3</span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
                        </a>
                    </div>

                    <div class="menu-inner-shadow"></div>

                    
                </aside>
                <!-- / Menu -->

                <!-- Layout container -->
                <div class="layout-page">
                  <!-- Navbar -->

                    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="ti ti-menu-2 ti-sm"></i>
                          </a>
                        </div>

                        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                            <div class="navbar-nav align-items-center">
                                <div class="nav-item navbar-search-wrapper mb-0">
                                    <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="Dashboard">
                                    </a>
                                </div>
                            </div>
                            <div class="navbar-nav align-items-center">
                                <div class="nav-item navbar-search-wrapper mb-0">
                                  <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                                    <i class="ti ti-search ti-md me-2"></i>
                                    <!-- <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span> -->
                                    <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search (Ctrl+/)" aria-label="Search..." id="searching_text" />
                                  </a>
                                </div>
                            </div>
                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <!-- Style Switcher -->
                                <li class="nav-item me-2 me-xl-0">
                                    <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                                        <i class="ti ti-md"></i>
                                    </a>
                                </li>
                                <!--/ Style Switcher -->
                                
                                <!-- User -->
                                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                        <div class="avatar avatar-online">
                                            <img src="<?php echo $this->session->userdata("profilepict"); ?>" alt class="h-auto rounded-circle" />
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="Profile">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar avatar-online">
                                                            <img src="<?php echo $this->session->userdata("profilepict"); ?>" alt class="h-auto rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <span class="fw-semibold d-block"><?php echo $this->session->userdata("user")["username"]; ?></span></small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="Account/logout">
                                                <i class="ti ti-logout me-2 ti-sm"></i>
                                                <span class="align-middle">Log Out</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            <!--/ User -->
                            </ul>
                        </div>
                    </nav>
                    <!-- / Navbar -->

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        <?php $this->load->view($content);?>


                        <!-- Footer -->
                        <footer class="content-footer footer bg-footer-theme">
                            <div class="container-xxl">
                                <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                    <div>
                                        ©
                                        <script>
                                          document.write(new Date().getFullYear());
                                        </script>
                                        , made with ❤️ by <a href="#" class="fw-semibold">codeigniter3</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        <!-- / Footer -->

                        <div class="content-backdrop fade"></div>
                    </div>
                    <!-- Content wrapper -->

                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>

            <!-- Drag Target Area To SlideIn Menu On Small Screens -->
            <div class="drag-target"></div>
        </div>
        <!-- / Layout wrapper -->

        <div id="overlay">
            <div class="cv-spinner">
                <span class="spinner"></span>
            </div>
        </div>

        <!-- Core JS -->

        <!-- build:js assets/vendor/js/core.js -->
        <script src="<?php echo base_url(); ?>assets/vendor/libs/popper/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/js/bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/node-waves/node-waves.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/hammer/hammer.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/i18n/i18n.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/typeahead-js/typeahead.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/js/menu.js"></script>
        <!-- endbuild -->

        <!-- Vendor js -->
        <script src="<?php echo base_url(); ?>assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/datatables-bs5/dataTables.select.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/flatpickr/flatpickr.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/select2/select2.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>
        <!-- end vendor -->

        <!-- Main JS -->
        <script src="<?php echo base_url(); ?>assets/js/main.js"></script>

        <script type="text/javascript">
            var startdate, enddate, datepick1, datepick2;
            var pageactive = "<?php echo $active; ?>";

            $(document).ajaxSend(function() {
                $("#overlay").fadeIn(300);
            });

            $(document).ajaxComplete(function() {
                $("#overlay").fadeOut(300);
            });

            

            $("document").ready(function(){
                
            });

        </script>
    </body>
</html>
