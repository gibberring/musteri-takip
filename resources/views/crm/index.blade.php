<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="WRAPCODERS" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>Duralux || Dashboard</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/bootstrap.min.css') }}" />
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/daterangepicker.min.css') }}" />
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/theme.min.css') }}" />
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Manu !-->
    <!--! ================================================================ !-->
    <nav class="nxl-navigation">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="#!" class="b-brand">
                    <!-- ========   change your logo hear   ============ -->
                    <img src="{{ asset('crm_assets/images/logo-full.png') }}" alt="" class="logo logo-lg" />
                    <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
                </a>
            </div>
            <div class="navbar-content">
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Navigation</label>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboards</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">CRM</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Analytics</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-cast"></i></span>
                            <span class="nxl-mtext">Reports</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Sales Report</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Leads Report</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Project Report</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Timesheets Report</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-send"></i></span>
                            <span class="nxl-mtext">Applications</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Chat</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Email</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Tasks</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Notes</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Storage</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Calendar</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-at-sign"></i></span>
                            <span class="nxl-mtext">Proposal</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Proposal</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Proposal View</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Proposal Edit</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Proposal Create</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                            <span class="nxl-mtext">Payment</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Payment</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Invoice View</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Invoice Create</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">Customers</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Customers</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Customers View</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Customers Create</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-alert-circle"></i></span>
                            <span class="nxl-mtext">Leads</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Leads</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Leads View</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Leads Create</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                            <span class="nxl-mtext">Projects</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Projects</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Projects View</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Projects Create</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-layout"></i></span>
                            <span class="nxl-mtext">Widgets</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">Lists</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Tables</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Charts</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Statistics</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Miscellaneous</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-settings"></i></span>
                            <span class="nxl-mtext">Settings</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="#!">General</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">SEO</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Tags</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Email</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Tasks</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Leads</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Support</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Finance</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Gateways</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Customers</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Localization</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">reCAPTCHA</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">Miscellaneous</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-power"></i></span>
                            <span class="nxl-mtext">Authentication</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Login</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-login-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-login-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-login-creative.html">Creative</a></li>
                                </ul>
                            </li>
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Register</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-register-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-register-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-register-creative.html">Creative</a></li>
                                </ul>
                            </li>
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Error-404</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-404-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-404-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-404-creative.html">Creative</a></li>
                                </ul>
                            </li>
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Reset Pass</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-reset-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-reset-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-reset-creative.html">Creative</a></li>
                                </ul>
                            </li>
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Verify OTP</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-verify-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-verify-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-verify-creative.html">Creative</a></li>
                                </ul>
                            </li>
                            <li class="nxl-item nxl-hasmenu">
                                <a href="javascript:void(0);" class="nxl-link">
                                    <span class="nxl-mtext">Maintenance</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>
                                <ul class="nxl-submenu">
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-maintenance-cover.html">Cover</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-maintenance-minimal.html">Minimal</a></li>
                                    <li class="nxl-item"><a class="nxl-link" href="./auth-maintenance-creative.html">Creative</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-life-buoy"></i></span>
                            <span class="nxl-mtext">Help Center</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item"><a class="nxl-link" href="https://wrapcoders.tawk.help">Support</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="#!">KnowledgeBase</a></li>
                            <li class="nxl-item"><a class="nxl-link" href="./../../../docs/documentations.html">Documentations</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="card text-center">
                    <div class="card-body">
                        <i class="feather-sunrise fs-4 text-dark"></i>
                        <h6 class="mt-4 text-dark fw-bolder">Downloading Center</h6>
                        <p class="fs-11 my-3 text-dark">Duralux is a production ready CRM to get started up and running easily.</p>
                        <a href="javascript:void(0);" class="btn btn-primary text-dark w-100">Download Now</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Manu !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [Start] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                        <i class="feather-align-left"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu-toggle !-->
                <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                    <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                        <i class="feather-align-left"></i>
                    </a>
                </div>
                <!--! [End] nxl-lavel-mega-menu-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu !-->
                <div class="nxl-drp-link nxl-lavel-mega-menu">
                    <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                        <a href="javascript:void(0)" id="nxl-lavel-mega-menu-hide">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <!--! [Start] nxl-lavel-mega-menu-wrapper !-->
                    <div class="nxl-lavel-mega-menu-wrapper d-flex gap-3">
                        <!--! [Start] nxl-lavel-menu !-->
                        <div class="dropdown nxl-h-item nxl-lavel-menu">
                            <a href="javascript:void(0);" class="avatar-text avatar-md bg-primary text-white" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <i class="feather-plus"></i>
                            </a>
                            <div class="dropdown-menu nxl-h-dropdown">
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-send"></i>
                                            <span>Applications</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Chat</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Email</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Tasks</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Notes</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Storage</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Calendar</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-cast"></i>
                                            <span>Reports</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Sales Report</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Leads Report</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Project Report</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Timesheets Report</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-at-sign"></i>
                                            <span>Proposal</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Proposal</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Proposal View</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Proposal Edit</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Proposal Create</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-dollar-sign"></i>
                                            <span>Payment</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Payment</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Invoice View</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Invoice Create</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-users"></i>
                                            <span>Customers</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Customers</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Customers View</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Customers Create</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-alert-circle"></i>
                                            <span>Leads</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Leads</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Leads View</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Leads Create</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown nxl-level-menu">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <span class="hstack">
                                            <i class="feather-briefcase"></i>
                                            <span>Projects</span>
                                        </span>
                                        <i class="feather-chevron-right ms-auto me-0"></i>
                                    </a>
                                    <div class="dropdown-menu nxl-h-dropdown">
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Projects</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Projects View</span>
                                        </a>
                                        <a href="#!" class="dropdown-item">
                                            <i class="wd-5 ht-5 bg-gray-500 rounded-circle me-3"></i>
                                            <span>Projects Create</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--! [End] nxl-lavel-menu !-->
                        <!--! [Start] nxl-lavel-menu !-->
                        <div class="dropdown nxl-h-item nxl-lavel-menu">
                            <a href="javascript:void(0);" class="avatar-text avatar-md bg-info text-white"> <i class="feather-trending-up"></i> </a>
                        </div>
                        <!--! [End] nxl-lavel-menu !-->
                        <!--! [Start] nxl-lavel-menu !-->
                        <div class="dropdown nxl-h-item nxl-lavel-menu">
                            <a href="javascript:void(0);" class="avatar-text avatar-md bg-warning text-dark"> <i class="feather-grid"></i> </a>
                        </div>
                        <!--! [End] nxl-lavel-menu !-->
                        <!--! [Start] nxl-lavel-menu !-->
                        <div class="dropdown nxl-h-item nxl-lavel-menu">
                            <a href="javascript:void(0);" class="avatar-text avatar-md bg-success text-white"> <i class="feather-user-plus"></i> </a>
                        </div>
                        <!--! [End] nxl-lavel-menu !-->
                    </div>
                    <!--! [End] nxl-lavel-mega-menu-wrapper !-->
                </div>
                <!--! [End] nxl-lavel-mega-menu !-->
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right d-flex align-items-center gap-4">
                <!--! [Start] Search !-->
                <div class="dropdown nxl-h-item nxl-search">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-search"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-search-dropdown">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." aria-label="Search..." aria-describedby="button-addon2" />
                            <button class="btn btn-light-brand" type="button" id="button-addon2"><i class="feather-search"></i></button>
                        </div>
                        <div class="dropdown-divider mt-3 mb-1"></div>
                        <!--! BEGIN: Recent Search List !-->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fs-11 text-uppercase text-muted fw-bold mb-0">Recent Searches</h6>
                            <a href="javascript:void(0);" class="fs-11 text-uppercase text-muted fw-bold mb-0">Clear All</a>
                        </div>
                        <div class="vstack gap-2">
                            <a href="javascript:void(0);" class="hstack gap-3">
                                <div class="avatar-icon avatar-sm bg-soft-primary text-primary">
                                    <i class="feather-users"></i>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="fs-13 fw-normal mb-0">Members Statistics</h6>
                                    <span class="fs-11 text-muted">Apps / User Management</span>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="hstack gap-3">
                                <div class="avatar-icon avatar-sm bg-soft-success text-success">
                                    <i class="feather-dollar-sign"></i>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="fs-13 fw-normal mb-0">Billing Reports</h6>
                                    <span class="fs-11 text-muted">Reports / Accounting</span>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="hstack gap-3">
                                <div class="avatar-icon avatar-sm bg-soft-danger text-danger">
                                    <i class="feather-calendar"></i>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="fs-13 fw-normal mb-0">Calendar App</h6>
                                    <span class="fs-11 text-muted">Apps / Calendar</span>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="hstack gap-3">
                                <div class="avatar-icon avatar-sm bg-soft-info text-info">
                                    <i class="feather-unlock"></i>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="fs-13 fw-normal mb-0">Security Settings</h6>
                                    <span class="fs-11 text-muted">Settings / Security</span>
                                </div>
                            </a>
                        </div>
                        <!--! END: Recent Search List !-->
                    </div>
                </div>
                <!--! [End] Search !-->
                <!--! [Start] Notifications !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        <span class="badge bg-danger nxl-h-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-dropdown">
                        <div class="markup">
                            <div>
                                <div class="d-flex align-items-center justify-content-between nxl-h-dropdown-header">
                                    <h6 class="fs-11 text-uppercase text-muted fw-bold mb-0">Notifications</h6>
                                    <a href="javascript:void(0);" class="fs-11 text-uppercase text-muted fw-bold mb-0">Clear All</a>
                                </div>
                                <div class="nxl-h-dropdown-body" data-scrollbar-target="#psScrollbarInit">
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Message</a>
                                                <span class="fs-11 text-muted">23 min ago</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">Clintwin wants to connect with you</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <span class="d-flex align-items-center justify-content-center text-white rounded-circle wd-30 ht-30 bg-primary">
                                                <i class="feather-layers"></i>
                                            </span>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Your order is placed</a>
                                                <span class="fs-11 text-muted">1 day ago</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">Your Order #12345 has been confirmed and is being prepared for shipment.</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <span class="d-flex align-items-center justify-content-center text-white rounded-circle wd-30 ht-30 bg-success">
                                                <i class="feather-send"></i>
                                            </span>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Congratulations! Available Now</a>
                                                <span class="fs-11 text-muted">Sep 29, 2022, 07:13 PM</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">Your account has been successfully activated.</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-image avatar-sm">
                                                <img src="{{ asset('crm_assets/images/avatar/2.png') }}" alt="" class="img-fluid" />
                                            </div>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Waylon Dalton</a>
                                                <span class="fs-11 text-muted">Sep 28, 2022, 04:22 PM</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">Commented on your post</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-image avatar-sm">
                                                <img src="{{ asset('crm_assets/images/avatar/4.png') }}" alt="" class="img-fluid" />
                                            </div>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Helen Meyer</a>
                                                <span class="fs-11 text-muted">Sep 27, 2022, 09:35 PM</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">Added you to the Duralux project</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-image avatar-sm">
                                                <img src="{{ asset('crm_assets/images/avatar/5.png') }}" alt="" class="img-fluid" />
                                            </div>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Lloyd Johnson</a>
                                                <span class="fs-11 text-muted">Sep 26, 2022, 01:18 PM</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">marked the task Development as completed.</p>
                                        </div>
                                    </div>
                                    <!--! BEGIN: [Notification] !-->
                                    <div class="d-flex align-items-center p-3 nxl-h-dropdown-item">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-image avatar-sm">
                                                <img src="{{ asset('crm_assets/images/avatar/6.png') }}" alt="" class="img-fluid" />
                                            </div>
                                        </div>
                                        <div class="ps-2 flex-fill">
                                            <span class="d-flex align-items-center justify-content-between">
                                                <a href="javascript:void(0);" class="fs-13 fw-bold text-truncate me-2">Noah Walls</a>
                                                <span class="fs-11 text-muted">Sep 25, 2022, 10:13 PM</span>
                                            </span>
                                            <p class="fs-12 text-muted mb-0 text-truncate-2-line">marked the task UI/UX Design as completed.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="nxl-h-dropdown-footer d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" class="fs-11 text-uppercase text-muted fw-bold mb-0">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--! [End] Notifications !-->
                <!--! [Start] Apps !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-grid"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-apps-dropdown">
                        <div class="d-flex align-items-center justify-content-between nxl-h-dropdown-header">
                            <h6 class="fs-11 text-uppercase text-muted fw-bold mb-0">My Apps</h6>
                            <a href="javascript:void(0);" class="fs-11 text-uppercase text-muted fw-bold mb-0">Show All</a>
                        </div>
                        <!--! BEGIN: [Apps] !-->
                        <div class="nxl-h-dropdown-body d-flex justify-content-center align-items-center" data-scrollbar-target="#psScrollbarInit">
                            <div class="row row-cols-3 g-1">
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/spotify.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Spotify</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/figma.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Figma</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/shopify.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Shopify</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/paypal.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Paypal</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/gmail.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Gmail</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/dropbox.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Dropbox</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/google-drive.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Drive</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/github.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Github</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/gitlab.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Gitlab</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/facebook.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Facebook</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/pinterest.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Pinterest</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/instagram.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Instagram</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/twitter.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Twitter</h6>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="javascript:void(0);" class="d-flex flex-column justify-content-center align-items-center text-center gap-1 p-3 bg-hover-light rounded-2">
                                        <img src="{{ asset('crm_assets/images/brand/youtube.png') }}" alt="" class="wd-30 ht-30" />
                                        <h6 class="fs-11 fw-normal text-dark mb-0">Youtube</h6>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--! END: [Apps] !-->
                    </div>
                </div>
                <!--! [End] Apps !-->
                <!--! [Start] Language !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-globe"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-language-dropdown">
                        <div class="d-flex align-items-center justify-content-between nxl-h-dropdown-header">
                            <h6 class="fs-11 text-uppercase text-muted fw-bold mb-0">Language</h6>
                        </div>
                        <!--! BEGIN: [Language] !-->
                        <div class="nxl-h-dropdown-body" data-scrollbar-target="#psScrollbarInit">
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item active">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/us.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">English</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/sa.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Arabic</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/bd.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Bengali</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/ch.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Chinese</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/nl.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Dutch</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/fr.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">French</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/de.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">German</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/in.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Hindi</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/ru.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Russian</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/es.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Spanish</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/tr.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Turkish</h6>
                            </a>
                            <a href="javascript:void(0);" class="d-flex align-items-center gap-3 nxl-h-dropdown-item">
                                <div class="avatar-image wd-30 ht-30">
                                    <img src="{{ asset('crm_assets/images/flags/1x1/pk.svg') }}" alt="" class="img-fluid" />
                                </div>
                                <h6 class="fs-13 fw-normal mb-0">Urdo</h6>
                            </a>
                        </div>
                        <!--! END: [Language] !-->
                    </div>
                </div>
                <!--! [End] Language !-->
                <!--! [Start] Profile !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <div class="d-flex align-items-center">
                            <div class="avatar-image avatar-sm">
                                <img src="{{ asset('crm_assets/images/avatar/1.png') }}" alt="" class="img-fluid" />
                            </div>
                            <div class="next-avatar ps-2 d-none d-sm-inline-block">
                                <span class="fs-13 fw-bold">Alex Corporation</span>
                                <span class="fs-11 d-block text-muted">Project Manager</span>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="avatar-image avatar-lg">
                                    <img src="{{ asset('crm_assets/images/avatar/1.png') }}" alt="" />
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="hstack justify-content-between gap-2">
                                        <span class="fs-14 fw-bold">Alexandra Della</span>
                                        <a href="javascript:void(0);" class="badge bg-soft-success text-success">Pro</a>
                                    </div>
                                    <span class="fs-12 d-block text-muted">alex@example.com</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <!--! BEGIN: [Dropdown Items] !-->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profile Details</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-settings"></i>
                            <span>Account Settings</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-credit-card"></i>
                            <span>Payment Methods</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-dollar-sign"></i>
                            <span>Upgrade Plan</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-help-circle"></i>
                            <span>Knowledge Base</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-compass"></i>
                            <span>Support</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-log-out"></i>
                            <span>Logout</span>
                        </a>
                        <!--! END: [Dropdown Items] !-->
                    </div>
                </div>
                <!--! [End] Profile !-->
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- [ page-header ] start -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Dashboard</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#!">CRM</a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="page-header-right-items">
                        <div class="d-flex d-none d-md-flex">
                            <div class="form-group">
                                <div class="input-group" id="reportrange">
                                    <input type="text" class="form-control" placeholder="Select date range..." />
                                    <span class="input-group-text"><i class="feather-calendar"></i></span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-light-brand success">
                                <i class="feather-plus me-2"></i>
                                <span>Create New</span>
                            </a>
                        </div>
                        <div class="d-md-none d-flex">
                            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                                <i class="feather-arrow-left"></i>
                            </a>
                            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                                <i class="feather-align-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ page-header ] end -->
            <!-- [ Main Content ] start -->
            <div class="main-content">
                <div class="row">
                    <!--! BEGIN: [Top Four Card] !-->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card stretch stretch-full">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex gap-4">
                                        <div class="avatar-text avatar-lg bg-gray-200">
                                            <i class="feather-dollar-sign"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold text-dark">$23,560.00</div>
                                            <div class="fs-13 fw-semibold text-muted text-uppercase">Total Revenue</div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);">
                                        <i class="feather-more-vertical"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-md-6">
                        <div class="card stretch stretch-full">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex gap-4">
                                        <div class="avatar-text avatar-lg bg-gray-200">
                                            <i class="feather-users"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold text-dark">12,500</div>
                                            <div class="fs-13 fw-semibold text-muted text-uppercase">Total Customers</div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);">
                                        <i class="feather-more-vertical"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-md-6">
                        <div class="card stretch stretch-full">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex gap-4">
                                        <div class="avatar-text avatar-lg bg-gray-200">
                                            <i class="feather-box"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold text-dark">1,876</div>
                                            <div class="fs-13 fw-semibold text-muted text-uppercase">Active Projects</div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);">
                                        <i class="feather-more-vertical"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-md-6">
                        <div class="card stretch stretch-full">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex gap-4">
                                        <div class="avatar-text avatar-lg bg-gray-200">
                                            <i class="feather-check-square"></i>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold text-dark">2,345</div>
                                            <div class="fs-13 fw-semibold text-muted text-uppercase">Complete Task</div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);">
                                        <i class="feather-more-vertical"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--! END: [Top Four Card] !-->
                    <!--! BEGIN: [Sales Analytics] !-->
                    <div class="col-xxl-8">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Sales Analytics</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action">
                                <div id="salesAnalytics"></div>
                            </div>
                            <div class="card-footer pt-0">
                                <div class="row">
                                    <div class="col-6 col-sm-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-icon avatar-sm bg-soft-primary text-primary">
                                                <i class="feather-target"></i>
                                            </span>
                                            <div class="flex-fill">
                                                <span class="fs-11 text-muted">Target</span>
                                                <h5 class="mb-0">9.8k</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-icon avatar-sm bg-soft-warning text-warning">
                                                <i class="feather-layers"></i>
                                            </span>
                                            <div class="flex-fill">
                                                <span class="fs-11 text-muted">Total Sales</span>
                                                <h5 class="mb-0">11.6k</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-icon avatar-sm bg-soft-success text-success">
                                                <i class="feather-shield"></i>
                                            </span>
                                            <div class="flex-fill">
                                                <span class="fs-11 text-muted">Orders</span>
                                                <h5 class="mb-0">4.5k</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-icon avatar-sm bg-soft-danger text-danger">
                                                <i class="feather-user"></i>
                                            </span>
                                            <div class="flex-fill">
                                                <span class="fs-11 text-muted">Customers</span>
                                                <h5 class="mb-0">15.6k</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--! END: [Sales Analytics] !-->
                    <!--! BEGIN: [Profit Analytics] !-->
                    <div class="col-xxl-4">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Profit Analytics</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action">
                                <div id="profitAnalytics"></div>
                            </div>
                            <div class="card-footer pt-0">
                                <div class="row">
                                    <div class="col">
                                        <div class="fs-11 text-muted text-uppercase">Total Profit</div>
                                        <h5 class="mb-0">$24.5k</h5>
                                    </div>
                                    <div class="col">
                                        <div class="fs-11 text-muted text-uppercase">Total Loss</div>
                                        <h5 class="mb-0">$11.2k</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--! END: [Profit Analytics] !-->
                    <!--! BEGIN: [Top Leads] !-->
                    <div class="col-xxl-4">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Top Leads</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-muted opacity-50">Profile</th>
                                                <th class="text-muted opacity-50 text-end">Worth</th>
                                                <th class="text-muted opacity-50 text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-image avatar-lg">
                                                            <img src="{{ asset('crm_assets/images/avatar/2.png') }}" alt="" class="img-fluid" />
                                                        </div>
                                                        <div>
                                                            <a href="javascript:void(0);" class="fw-bold d-block">Jennifer Garcia</a>
                                                            <span class="fs-12 text-muted">Business Development</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="fs-14 fw-bold text-dark">$10,500</div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="javascript:void(0);" class="badge bg-soft-success text-success">Deal Won</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-image avatar-lg">
                                                            <img src="{{ asset('crm_assets/images/avatar/3.png') }}" alt="" class="img-fluid" />
                                                        </div>
                                                        <div>
                                                            <a href="javascript:void(0);" class="fw-bold d-block">Robert Smith</a>
                                                            <span class="fs-12 text-muted">Sales Manager</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="fs-14 fw-bold text-dark">$2,500</div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="javascript:void(0);" class="badge bg-soft-danger text-danger">Deal Lost</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-image avatar-lg">
                                                            <img src="{{ asset('crm_assets/images/avatar/4.png') }}" alt="" class="img-fluid" />
                                                        </div>
                                                        <div>
                                                            <a href="javascript:void(0);" class="fw-bold d-block">John Lewis</a>
                                                            <span class="fs-12 text-muted">Marketing Manager</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="fs-14 fw-bold text-dark">$5,000</div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="javascript:void(0);" class="badge bg-soft-warning text-warning">Pending</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-image avatar-lg">
                                                            <img src="{{ asset('crm_assets/images/avatar/5.png') }}" alt="" class="img-fluid" />
                                                        </div>
                                                        <div>
                                                            <a href="javascript:void(0);" class="fw-bold d-block">Larry Miller</a>
                                                            <span class="fs-12 text-muted">Account Manager</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="fs-14 fw-bold text-dark">$7,000</div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="javascript:void(0);" class="badge bg-soft-success text-success">Deal Won</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-image avatar-lg">
                                                            <img src="{{ asset('crm_assets/images/avatar/6.png') }}" alt="" class="img-fluid" />
                                                        </div>
                                                        <div>
                                                            <a href="javascript:void(0);" class="fw-bold d-block">Daniel Davis</a>
                                                            <span class="fs-12 text-muted">Frontend Developer</span>
                                                        </div>
                                                    </div>
                                                </td>
                                        <div class="d-flex flex-grow-1 align-items-center">
                                            <div class="progress w-100 me-3 ht-5">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 54%" aria-valuenow="54" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted">54%</span>
                                        </div>
                                    </div>
                                    <hr class="border-dashed my-3" />
                                    <div class="mb-4 pb-1 d-flex">
                                        <div class="d-flex w-50 align-items-center me-3">
                                            <img src="{{ asset('crm_assets/images/brand/figma.png') }}" alt="figma-logo" class="me-3" width="35" />
                                            <div>
                                                <a href="javascript:void(0);" class="text-truncate-1-line">Dashboard Design</a>
                                                <div class="fs-11 text-muted">App UI Kit</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-grow-1 align-items-center">
                                            <div class="progress w-100 me-3 ht-5">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 86%" aria-valuenow="86" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted">86%</span>
                                        </div>
                                    </div>
                                    <hr class="border-dashed my-3" />
                                    <div class="mb-4 pb-1 d-flex">
                                        <div class="d-flex w-50 align-items-center me-3">
                                            <img src="{{ asset('crm_assets/images/brand/facebook.png') }}" alt="vue-logo" class="me-3" width="35" />
                                            <div>
                                                <a href="javascript:void(0);" class="text-truncate-1-line">Facebook Marketing</a>
                                                <div class="fs-11 text-muted">Marketing</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-grow-1 align-items-center">
                                            <div class="progress w-100 me-3 ht-5">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted">90%</span>
                                        </div>
                                    </div>
                                    <hr class="border-dashed my-3" />
                                    <div class="mb-4 pb-1 d-flex">
                                        <div class="d-flex w-50 align-items-center me-3">
                                            <img src="{{ asset('crm_assets/images/brand/github.png') }}" alt="react-logo" class="me-3" width="35" />
                                            <div>
                                                <a href="javascript:void(0);" class="text-truncate-1-line">React Dashboard Github</a>
                                                <div class="fs-11 text-muted">Dashboard</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-grow-1 align-items-center">
                                            <div class="progress w-100 me-3 ht-5">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: 37%" aria-valuenow="37" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted">37%</span>
                                        </div>
                                    </div>
                                    <hr class="border-dashed my-3" />
                                    <div class="d-flex">
                                        <div class="d-flex w-50 align-items-center me-3">
                                            <img src="{{ asset('crm_assets/images/brand/paypal.png') }}" alt="sketch-logo" class="me-3" width="35" />
                                            <div>
                                                <a href="javascript:void(0);" class="text-truncate-1-line">Paypal Payment Gateway</a>
                                                <div class="fs-11 text-muted">Payment</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-grow-1 align-items-center">
                                            <div class="progress w-100 me-3 ht-5">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 29%" aria-valuenow="29" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted">29%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center">Upcomming Projects</a>
                        </div>
                    </div>
                    <!--! END: [Project Status] !-->
                    <!--! BEGIN: [Team Progress] !-->
                    <div class="col-xxl-4">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Team Progress</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action">
                                <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
                                    <div class="hstack gap-3">
                                        <div class="avatar-image">
                                            <img src="{{ asset('crm_assets/images/avatar/1.png') }}" alt="" class="img-fluid" />
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Alexandra Della</a>
                                            <div class="fs-11 text-muted">Frontend Developer</div>
                                        </div>
                                    </div>
                                    <div class="team-progress-1"></div>
                                </div>
                                <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
                                    <div class="hstack gap-3">
                                        <div class="avatar-image">
                                            <img src="{{ asset('crm_assets/images/avatar/2.png') }}" alt="" class="img-fluid" />
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Archie Cantones</a>
                                            <div class="fs-11 text-muted">UI/UX Designer</div>
                                        </div>
                                    </div>
                                    <div class="team-progress-2"></div>
                                </div>
                                <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
                                    <div class="hstack gap-3">
                                        <div class="avatar-image">
                                            <img src="{{ asset('crm_assets/images/avatar/3.png') }}" alt="" class="img-fluid" />
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Malanie Hanvey</a>
                                            <div class="fs-11 text-muted">Backend Developer</div>
                                        </div>
                                    </div>
                                    <div class="team-progress-3"></div>
                                </div>
                                <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-2">
                                    <div class="hstack gap-3">
                                        <div class="avatar-image">
                                            <img src="{{ asset('crm_assets/images/avatar/4.png') }}" alt="" class="img-fluid" />
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Kenneth Hune</a>
                                            <div class="fs-11 text-muted">Digital Marketer</div>
                                        </div>
                                    </div>
                                    <div class="team-progress-4"></div>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center">Update 30 Min Ago</a>
                        </div>
                    </div>
                    <!--! END: [Team Progress] !-->
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
        <!-- [ Footer ] start -->
        <footer class="footer">
            <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                <span>Copyright ©</span>
                <script>
                    document.write(new Date().getFullYear());
                </script>
            </p>
            <div class="d-flex align-items-center gap-4">
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Help</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Terms</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Privacy</a>
            </div>
        </footer>
        <!-- [ Footer ] end -->
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <div class="theme-customizer">
        <div class="customizer-handle">
            <a href="javascript:void(0);" class="cutomizer-open-trigger bg-primary">
                <i class="feather-settings"></i>
            </a>
        </div>
        <div class="customizer-sidebar-wrapper">
            <div class="customizer-sidebar-header px-4 ht-80 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Theme Settings</h5>
                <a href="javascript:void(0);" class="cutomizer-close-trigger d-flex">
                    <i class="feather-x"></i>
                </a>
            </div>
            <div class="customizer-sidebar-body position-relative p-4" data-scrollbar-target="#psScrollbarInit">
                <!--! BEGIN: [Navigation] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Navigation</label>
                    <div class="row g-2 theme-options-items app-navigation" id="appNavigationList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-navigation-light" name="app-navigation" value="1" data-app-navigation="app-navigation-light" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-navigation-light">Light</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-navigation-dark" name="app-navigation" value="2" data-app-navigation="app-navigation-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-navigation-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Navigation] !-->
                <!--! BEGIN: [Header] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set mt-5">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Header</label>
                    <div class="row g-2 theme-options-items app-header" id="appHeaderList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-header-light" name="app-header" value="1" data-app-header="app-header-light" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-header-light">Light</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-header-dark" name="app-header" value="2" data-app-header="app-header-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-header-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Header] !-->
                <!--! BEGIN: [Skins] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Skins</label>
                    <div class="row g-2 theme-options-items app-skin" id="appSkinList">
                        <div class="col-6 text-center position-relative single-option light-button active">
                            <input type="radio" class="btn-check" id="app-skin-light" name="app-skin" value="1" data-app-skin="app-skin-light" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-light">Light</label>
                        </div>
                        <div class="col-6 text-center position-relative single-option dark-button">
                            <input type="radio" class="btn-check" id="app-skin-dark" name="app-skin" value="2" data-app-skin="app-skin-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Skins] !-->
                <!--! BEGIN: [Typography] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-0 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Typography</label>
                    <div class="row g-2 theme-options-items font-family" id="fontFamilyList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-lato" name="font-family" value="1" data-font-family="app-font-family-lato" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-lato">Lato</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-rubik" name="font-family" value="2" data-font-family="app-font-family-rubik" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-rubik">Rubik</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-inter" name="font-family" value="3" data-font-family="app-font-family-inter" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-inter">Inter</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-cinzel" name="font-family" value="4" data-font-family="app-font-family-cinzel" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-cinzel">Cinzel</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-nunito" name="font-family" value="6" data-font-family="app-font-family-nunito" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-nunito">Nunito</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto" name="font-family" value="7" data-font-family="app-font-family-roboto" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto">Roboto</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ubuntu" name="font-family" value="8" data-font-family="app-font-family-ubuntu" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ubuntu">Ubuntu</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-poppins" name="font-family" value="9" data-font-family="app-font-family-poppins" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-poppins">Poppins</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-raleway" name="font-family" value="10" data-font-family="app-font-family-raleway" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-raleway">Raleway</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-system-ui" name="font-family" value="11" data-font-family="app-font-family-system-ui" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-system-ui">System UI</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-noto-sans" name="font-family" value="12" data-font-family="app-font-family-noto-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-noto-sans">Noto Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-fira-sans" name="font-family" value="13" data-font-family="app-font-family-fira-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-fira-sans">Fira Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-work-sans" name="font-family" value="14" data-font-family="app-font-family-work-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-work-sans">Work Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-open-sans" name="font-family" value="15" data-font-family="app-font-family-open-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-open-sans">Open Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-maven-pro" name="font-family" value="16" data-font-family="app-font-family-maven-pro" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-maven-pro">Maven Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-quicksand" name="font-family" value="17" data-font-family="app-font-family-quicksand" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-quicksand">Quicksand</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat" name="font-family" value="18" data-font-family="app-font-family-montserrat" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat">Montserrat</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-josefin-sans" name="font-family" value="19" data-font-family="app-font-family-josefin-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-josefin-sans">Josefin Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ibm-plex-sans" name="font-family" value="20" data-font-family="app-font-family-ibm-plex-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ibm-plex-sans">IBM Plex Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-source-sans-pro" name="font-family" value="5" data-font-family="app-font-family-source-sans-pro" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-source-sans-pro">Source Sans Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat-alt" name="font-family" value="21" data-font-family="app-font-family-montserrat-alt" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat-alt">Montserrat Alt</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto-slab" name="font-family" value="22" data-font-family="app-font-family-roboto-slab" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto-slab">Roboto Slab</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Typography] !-->
            </div>
            <div class="customizer-sidebar-footer px-4 ht-60 border-top d-flex align-items-center gap-2">
                <div class="flex-fill w-50">
                    <a href="javascript:void(0);" class="btn btn-danger" data-style="reset-all-common-style">Reset</a>
                </div>
                <div class="flex-fill w-50">
                    <a href="javascript:void(0);" class="btn btn-primary">Download</a>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('crm_assets/vendors/js/vendors.min.js') }}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="{{ asset('crm_assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/circle-progress.min.js') }}"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="{{ asset('crm_assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('crm_assets/js/dashboard-init.min.js') }}"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="{{ asset('crm_assets/js/theme-customizer-init.min.js') }}"></script>
    <!--! END: Theme Customizer !-->
</body>

</html>