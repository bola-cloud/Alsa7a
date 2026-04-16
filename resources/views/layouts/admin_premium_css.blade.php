    <style>
        @media (max-width: 720px) {
            .card.p-5 {
                padding: 0px !important;
            }
        }

        /* Search Dropdown CSS */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            margin-top: 5px;
        }

        .search-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            align-items: center;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
        }

        .search-item:last-child {
            border-bottom: none;
        }

        .search-item:hover {
            background: #f3f4f6;
            text-decoration: none;
            color: #333;
        }

        .search-item i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: #9ca3af;
        }

        html[lang="ar"] .search-item i {
            margin-right: 0;
            margin-left: 15px;
        }

        .search-type {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: auto;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Premium Sidebar UI Upgrade */
        .main-menu.menu-light {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
            border-right: none !important;
        }

        .main-menu .navigation-main .nav-item a {
            border-radius: 10px !important;
            margin: 4px 15px !important;
            padding: 12px 18px !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            display: flex !important;
            align-items: center !important;
            color: #4b5563 !important;
            border: 1px solid transparent;
        }

        /* Hover State */
        .main-menu .navigation-main .nav-item a:hover {
            background: #f8fafc !important;
            border-color: #f1f5f9;
            color: #0ea5e9 !important;
        }
        
        .main-menu .navigation-main .nav-item a:hover {
            transform: translateX({{ App::getLocale() == 'ar' ? '-5px' : '5px' }});
        }

        /* Active State */
        .main-menu .navigation-main .nav-item.active > a {
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important;
            font-weight: 600 !important;
            border-color: transparent !important;
            transform: translateY(-2px);
        }

        /* Icons */
        .main-menu .navigation-main .nav-item a i {
            font-size: 1.5rem !important;
            transition: all 0.3s ease !important;
            color: #9ca3af !important;
        }
        .main-menu .navigation-main .nav-item a i {
            margin-right: {{ App::getLocale() == 'ar' ? '0' : '14px' }} !important;
            margin-left: {{ App::getLocale() == 'ar' ? '14px' : '0' }} !important;
        }

        /* Icon Active / Hover States */
        .main-menu .navigation-main .nav-item.active > a i {
            color: #ffffff !important;
        }
        .main-menu .navigation-main .nav-item a:hover i {
            color: #0ea5e9 !important;
            transform: scale(1.1);
        }

        /* Sidebar Headers (Now Interactive) */
        .main-menu .navigation-main .navigation-header {
            border-bottom: 1px solid rgba(0,0,0,0.03) !important;
            padding: 20px 20px 10px 20px !important;
            margin: 10px 0 5px 0 !important;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-menu .navigation-main .navigation-header span {
            color: #475569 !important;
            font-size: 1.1rem !important;
            letter-spacing: 0.5px !important;
            font-weight: 700 !important;
            transition: color 0.3s ease;
        }
        .main-menu .navigation-main .navigation-header:hover span,
        .main-menu .navigation-main .navigation-header:hover i {
            color: #0ea5e9 !important;
        }
        .nav-section-icon {
            font-size: 1.1rem !important;
            color: #94a3b8;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .navigation-header.section-closed .nav-section-icon {
            transform: rotate(-90deg);
        }
        /* Hide class for nav items */
        .nav-item-hidden {
            display: none !important;
            opacity: 0;
            transform: translateY(-10px);
        }

        /* =========================================
           🌟 DYNAMIC BRAND IDENTITY DASHBOARD 🌟
           ========================================= */
        
        /* 1. Global Background (Animated Breathing Gradient) */
        @keyframes liquidBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        body.vertical-layout {
            background: linear-gradient(-45deg, #f0fdfa, #ecfdf5, #f8fafc, #e0f2fe) !important;
            background-size: 400% 400% !important;
            animation: liquidBackground 20s ease infinite !important;
            background-attachment: fixed !important;
        }
        
        /* Custom Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9; 
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }

        /* 2. Top Navbar - Preserved Gradient but Enhanced Shadow */
        .header-navbar {
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%) !important;
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.25) !important;
            border-bottom: none !important;
        }
        .header-navbar .navbar-container {
            background: transparent !important;
        }

        /* 3. The Sidebar - Structural Wrapper */
        .main-menu.menu-light {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* The Inner Floating Sidebar with Spinning LED Border */
        .main-menu-content {
            border-radius: {{ App::getLocale() == 'ar' ? '30px 0 0 30px' : '0 30px 30px 0' }} !important;
            box-shadow: 0 0 30px rgba(14, 165, 233, 0.15), inset 0 0 10px rgba(52, 211, 153, 0.05) !important;
            overflow: hidden !important; 
            position: relative;
            background: transparent !important;
            height: calc(100vh - 110px) !important; /* Prevents overflow pushing grid down */
            z-index: 100;

            /* Hardware acceleration fix for animated bleeding over rounded corners */
            transform: translateZ(0);
            -webkit-mask-image: -webkit-radial-gradient(white, black);
            isolation: isolate;
        }

        /* The Spinning Gradient Background */
        .main-menu-content::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: conic-gradient(transparent, transparent, transparent, #34d399, #0ea5e9, #34d399, transparent);
            animation: rotateBorder 3s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        /* The Inner Mask (The actual white background) */
        .main-menu-content::after {
            content: '';
            position: absolute;
            inset: 3px; /* border thickness */
            background: #ffffff !important;
            border-radius: {{ App::getLocale() == 'ar' ? '27px 0 0 27px' : '0 27px 27px 0' }} !important;
            z-index: 1;
            pointer-events: none;
        }

        /* Elevating Content above the Mask */
        .navigation-main {
            position: relative;
            z-index: 5 !important;
            background: transparent !important;
            height: 100%;
            overflow-y: auto !important; /* Allow the inner menu to scroll since parent is hidden */
            padding-bottom: 30px !important;
        }

        @keyframes rotateBorder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 📱 Mobile Responsiveness Fix for Sidebar */
        @media (max-width: 991.98px) {
            .main-menu-content {
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #ffffff !important;
                height: 100vh !important;
            }
            .main-menu-content::before, 
            .main-menu-content::after {
                display: none !important;
            }
        }

        ul#main-menu-navigation {
            margin-top: 31px;
        }
        /* Dynamic Hover Links */
        .main-menu .navigation-main .nav-item a {
            border-radius: 12px !important;
            margin: 6px 18px !important;
            padding: 12px 18px !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important; /* Spring Physics */
            color: #475569 !important;
            font-weight: 600 !important;
            border: 1px solid transparent;
        }
        .main-menu .navigation-main .nav-item a:hover {
            background: rgba(14, 165, 233, 0.06) !important;
            border-color: rgba(14, 165, 233, 0.1);
            color: #0ea5e9 !important;
            transform: translateX({{ App::getLocale() == 'ar' ? '-6px' : '6px' }});
        }
        .main-menu .navigation-main .nav-item a i {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            color: #94a3b8 !important;
            margin-right: {{ App::getLocale() == 'ar' ? '0' : '14px' }} !important;
            margin-left: {{ App::getLocale() == 'ar' ? '14px' : '0' }} !important;
        }
        .main-menu .navigation-main .nav-item a:hover i {
            transform: scale(1.25) rotate({{ App::getLocale() == 'ar' ? '5deg' : '-5deg' }});
            color: #0ea5e9 !important;
        }

        /* Brand Active State */
        .main-menu .navigation-main .nav-item.active > a {
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.35) !important;
            transform: translateY(-2px);
            font-weight: 700 !important;
            border-color: transparent !important;
        }
        .main-menu .navigation-main .nav-item.active > a i {
            color: #ffffff !important;
        }

        /* 4. Dynamic Interactive Cards */
        .card {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05) !important;
            background-color: #ffffff !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px !important;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%);
            transform: scaleX(0);
            transform-origin: {{ App::getLocale() == 'ar' ? 'right' : 'left' }};
            transition: transform 0.4s ease;
            z-index: 10;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(14, 165, 233, 0.15) !important;
        }
        .card:hover::before {
            transform: scaleX(1);
        }
        .card .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid rgba(0,0,0,0.03) !important;
            padding: 1.5rem 1.5rem 0.5rem 1.5rem !important;
        }

        /* 5. Fluid Buttons */
        .btn {
            border-radius: 12px !important;
            padding: 0.6rem 1.8rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            border: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .btn-success, .btn-primary, .btn-info {
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(52, 211, 153, 0.3) !important;
        }
        .btn:hover {
            transform: translateY(-3px) scale(1.02) !important;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3) !important;
        }

        /* 6. Inputs & Selects with Brand Glow */
        .form-control, .select2-container .select2-selection--single {
            border-radius: 14px !important;
            border: 2px solid #f1f5f9 !important;
            padding: 0.7rem 1.2rem !important;
            height: auto !important;
            background-color: #f8fafc !important;
            color: #334155 !important;
            transition: all 0.3s ease !important;
            box-shadow: none !important;
            font-weight: 500;
        }
        .form-control:focus, .select2-container--focus .select2-selection--single {
            border-color: #34d399 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15) !important;
        }

        /* 7. Floating Transparent Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0 8px; /* Adds space between rows */
        }
        .table thead th {
            border: none !important;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: #64748b !important;
            background: transparent !important;
            padding: 1rem 1.5rem !important;
        }
        .table tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .table tbody tr td {
            border: none !important;
            padding: 1.2rem 1.5rem !important;
            vertical-align: middle !important;
            font-weight: 600;
            color: #475569 !important;
        }
        /* Rounded corners for floating table rows */
        .table tbody tr td:first-child {
            border-radius: {{ App::getLocale() == 'ar' ? '0 12px 12px 0' : '12px 0 0 12px' }} !important;
        }
        .table tbody tr td:last-child {
            border-radius: {{ App::getLocale() == 'ar' ? '12px 0 0 0' : '0 12px 12px 0' }} !important;
        }
        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.1) !important;
            position: relative;
            z-index: 5;
        }

        /* 8. Global Badges / Pills */
        .badge {
            padding: 0.5em 1em !important;
            border-radius: 8px !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
        }
        .badge-success { background: #dcfce3 !important; color: #16a34a !important; border:none!important; box-shadow:none!important;}

        /* Entrance Animation & Content Margin */
        @keyframes fadeUpIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .content-body {
            animation: fadeUpIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
    </style>
