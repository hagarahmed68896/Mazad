@extends('layouts.main')

@section('title', 'مبيعاتي | تمت')

@push('styles')
<style>
    .my-sales-page {
        background-color: #ffffff;
        min-height: 80vh;
        padding: 3rem 0;
        direction: rtl;
        text-align: right;
    }

    .breadcrumbs-pill {
        background-color: #1A73E8;
        border-radius: 12px;
        color: white !important;
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none !important;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
        transition: all 0.2s ease;
    }

    .breadcrumbs-pill:hover {
        background-color: #1557B0;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.25);
    }

    .page-title {
        font-size: 2.25rem;
        font-weight: 900;
        color: #1E293B;
        margin-bottom: 2rem;
    }

    /* Filter Tabs */
    .filter-tabs-container {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
        background: #F1F5F9;
        padding: 0.5rem;
        border-radius: 20px;
        width: fit-content;
    }

    .filter-tab {
        padding: 0.85rem 1.75rem;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #64748B;
        text-decoration: none;
        transition: all 0.25s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .filter-tab:hover {
        color: #1E293B;
        background: rgba(255, 255, 255, 0.5);
    }

    .filter-tab.active {
        background-color: #1A73E8;
        color: white;
        box-shadow: 0 8px 20px rgba(26, 115, 232, 0.2);
    }

    /* Empty State */
    .empty-state-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 32px;
        padding: 5rem 2rem;
        text-align: center;
        max-width: 680px;
        margin: 0 auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.02);
    }

    /* Sidebar and Filters styling matching search page */
    .filter-card {
        background: #F8FAFC;
        border-radius: 24px;
        border: 1px solid #E2E8F0;
        padding: 1.5rem;
    }
    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1.5px solid #E2E8F0;
        padding-bottom: 1rem;
    }
    .filter-header h3 {
        font-size: 1.15rem;
        font-weight: 900;
        color: #1E293B;
    }
    .reset-btn {
        font-size: 0.85rem;
        font-weight: 700;
        color: #94A3B8;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s;
        text-decoration: none !important;
    }
    .reset-btn:hover { color: #64748B; }

    .filter-group {
        border-bottom: 1.5px solid #E2E8F0;
        padding: 1.25rem 0;
    }
    .filter-group:last-child { border-bottom: none; }
    .filter-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 800;
        font-size: 0.95rem;
        color: #334155;
    }
    .filter-group-content {
        margin-top: 1rem;
        display: block;
    }
    .filter-group-content.hidden { display: none; }

    .filter-select {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        outline: none;
        background-color: #ffffff;
        cursor: pointer;
    }
    .filter-select:focus { border-color: #1A73E8; }

    .filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .filter-pill-label {
        position: relative;
        cursor: pointer;
    }
    .filter-pill-label input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .filter-pill-label span {
        display: inline-block;
        padding: 0.5rem 1.25rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #64748B;
        transition: all 0.2s;
        background: white;
    }
    .filter-pill-label input:checked + span {
        background-color: #E8F1FE;
        border-color: #1A73E8;
        color: #1A73E8;
    }

    .filter-categories-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .category-filter-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.5rem 0;
    }
    .category-filter-item input {
        display: none;
    }
    .category-filter-item .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #CBD5E1;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        transition: all 0.2s;
        background: white;
    }
    .category-filter-item input:checked + .radio-custom {
        border-color: #1A73E8;
        background-color: #1A73E8;
    }
    .category-filter-item input:checked + .radio-custom::after {
        content: '';
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .category-filter-item .cat-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
    }

    /* Action bar inputs */
    .search-action-bar {
        background: white;
        padding: 1.25rem 1.5rem;
        border-radius: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 2.5rem;
        border: 1.5px solid #F1F5F9;
    }

    .search-bar-input {
        width: 100%;
        height: 48px;
        padding-right: 3rem;
        padding-left: 1rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #1E293B;
        outline: none;
        background-color: #F8FAFC;
    }
    .search-bar-input:focus { border-color: #1A73E8; background-color: white; }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        height: 44px;
        padding: 0 1.25rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        background: white;
        color: #64748B;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn:hover { border-color: #1A73E8; color: #1A73E8; }
    .action-btn.active { background-color: #1A73E8; border-color: #1A73E8; color: white !important; }

    .sort-dropdown-container {
        position: relative;
    }
    .sort-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: auto;
        margin-top: 0.5rem;
        background: white;
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        z-index: 50;
        min-width: 180px;
        overflow: hidden;
    }
    .sort-dropdown-container.active .sort-options {
        display: block;
    }
    .sort-options a {
        display: block;
        padding: 0.75rem 1.25rem;
        color: #475569;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        text-align: right;
        transition: background 0.2s;
    }
    .sort-options a:hover { background: #F8FAFC; color: #1A73E8; }
    .sort-options a.active { background: #E8F1FE; color: #1A73E8; }

    /* View Toggles style */
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        background: white;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
    }
    .view-btn:hover { border-color: #1A73E8; color: #1A73E8; }
    .view-btn.active { background-color: #1A73E8; border-color: #1A73E8; color: white !important; }

    /* Card styling */
    .p-ad-card {
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.3s ease;
        height: 100%;
    }
    .p-ad-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    
    .p-card-main-content {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .p-card-image {
        position: relative;
        height: 220px;
        width: 100%;
        overflow: hidden;
    }
    .p-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .p-badges {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        left: 0.75rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.4rem;
        pointer-events: none;
    }
    .p-badge-pill {
        padding: 0.35rem 0.85rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.75rem;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    
    /* Custom tag colors matches screenshot */
    .p-badge-pill.status-pending,
    .p-badge-pill.status-receiving { background-color: #F97316 !important; opacity: 1 !important; } /* Orange - قيد الاستلام */
    .p-badge-pill.status-pending-payment { background-color: #22C55E !important; opacity: 1 !important; } /* Green - قيد الدفع */
    .p-badge-pill.status-paid { background-color: #22C55E !important; opacity: 1 !important; } /* Green - تم الدفع */
    .p-badge-pill.status-received { background-color: #1A73E8 !important; opacity: 1 !important; } /* Blue - تم الاستلام */
    .p-badge-pill.status-expired { background-color: #EF4444 !important; opacity: 1 !important; } /* Red - منتهي */
    
    .p-badge-pill.type-direct { background-color: #F97316; } /* Orange - شراء فوري */
    .p-badge-pill.type-auction { background-color: #EAB308; } /* Yellow - الكتروني / هجين */

    .p-countdown {
        position: absolute;
        bottom: 0.75rem;
        right: 0.75rem;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-family: 'Courier New', Courier, monospace;
        font-variant-numeric: tabular-nums;
    }

    .p-card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .p-category-pill {
        background-color: #E8F1FE;
        color: #1A73E8;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        width: fit-content;
        margin-bottom: 0.75rem;
    }
    .p-ad-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1E293B;
        line-height: 1.4;
        margin-bottom: 0.75rem;
        min-height: 2.8rem;
    }
    .p-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.25rem 0;
    }
    .p-price-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748B;
    }
    .p-price-value {
        color: #1A73E8;
        font-weight: 900;
        font-size: 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    /* User / Buyer details style */
    .buyer-info-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-top: 1.5px solid #F1F5F9;
        margin-top: auto;
    }
    .buyer-avatar-placeholder {
        width: 32px;
        height: 32px;
        background: #1A73E8;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 800;
    }
    .buyer-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
    }

    .p-card-footer {
        padding: 1rem 1.25rem 1.25rem 1.25rem;
        border-top: 1.5px solid #F1F5F9;
        display: flex;
        gap: 0.5rem;
    }

    /* Horizontal/List View layout exactly matching the first screenshot */
    #mySalesContainer.list-view {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    
    #mySalesContainer.list-view .p-ad-card {
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        overflow: hidden !important;
        border-radius: 24px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border: 1px solid #E2E8F0 !important;
    }

    #mySalesContainer.list-view .p-card-main-content {
        display: flex !important;
        flex-direction: row !important; /* Image on right, details on left in RTL */
        align-items: stretch !important;
        min-height: 200px !important;
    }

    #mySalesContainer.list-view .p-card-image {
        width: 300px !important;
        height: auto !important;
        flex-shrink: 0 !important;
        position: relative !important;
        border-radius: 0 20px 20px 0 !important;
        overflow: hidden !important;
    }

    #mySalesContainer.list-view .p-card-image img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    #mySalesContainer.list-view .p-card-body {
        flex: 1 !important;
        padding: 1.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    #mySalesContainer.list-view .p-category-pill {
        align-self: flex-start !important;
        margin-bottom: 0.5rem !important;
    }

    #mySalesContainer.list-view .p-ad-title {
        font-size: 1.2rem !important;
        margin-bottom: 0.5rem !important;
        min-height: auto !important;
    }

    #mySalesContainer.list-view .p-price-row {
        margin-bottom: 0.5rem !important;
    }

    #mySalesContainer.list-view .buyer-info-row {
        margin-top: 0.5rem !important;
        padding-top: 0.5rem !important;
    }

    #mySalesContainer.list-view .p-card-footer {
        width: 100% !important;
        padding: 0.75rem 1.5rem 1.5rem 1.5rem !important;
        border-top: none !important;
        margin-top: 0 !important;
    }

    @media (max-width: 768px) {
        #mySalesContainer.list-view .p-card-main-content {
            flex-direction: column !important;
        }
        #mySalesContainer.list-view .p-card-image {
            width: 100% !important;
            height: 200px !important;
            border-radius: 20px 20px 0 0 !important;
        }
    }

    /* Bulk selection checkbox styling */
    .bulk-select-checkbox-container {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 10;
        cursor: pointer;
        display: inline-block;
    }
    
    .bulk-select-checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }
    
    .bulk-checkbox-custom {
        height: 28px;
        width: 28px;
        background-color: white;
        border: 2px solid #CBD5E1;
        border-radius: 8px;
        display: block;
        transition: all 0.2s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    
    .bulk-select-checkbox-container:hover input ~ .bulk-checkbox-custom {
        border-color: #1A73E8;
    }
    
    .bulk-select-checkbox-container input:checked ~ .bulk-checkbox-custom {
        background-color: #1A73E8;
        border-color: #1A73E8;
    }
    
    .bulk-checkbox-custom:after {
        content: "";
        position: absolute;
        display: none;
        left: 10px;
        top: 5px;
        width: 6px;
        height: 12px;
        border: solid white;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg);
    }
    
    .bulk-select-checkbox-container input:checked ~ .bulk-checkbox-custom:after {
        display: block;
    }

    /* Highlight card when selected */
    .p-ad-card.selected-for-bulk {
        border-color: #1A73E8 !important;
        box-shadow: 0 10px 25px rgba(26, 115, 232, 0.08) !important;
        background-color: #F8FAFC !important;
    }

    /* Dynamic badge shifting when checkbox is visible */
    .p-ad-card.has-bulk-checkbox .p-badges {
        left: 3.5rem !important;
    }

    /* Floating action bar styling */
    .bulk-action-bar {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        width: calc(100% - 2rem);
        max-width: 600px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        padding: 1rem 1.5rem;
        box-shadow: 0 20px 40px rgba(15, 23, 24, 0.15);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
        opacity: 0;
        pointer-events: none;
    }
    
    .bulk-action-bar.active {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    
    .bulk-count-badge {
        background-color: #E8F1FE;
        color: #1A73E8;
        padding: 0.4rem 1rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.9rem;
    }

    /* Dashboard Stats Grid */
    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }
    .stat-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-card-icon.blue { background-color: #E8F1FE; color: #1A73E8; }
    .stat-card-icon.green { background-color: #ECFDF5; color: #10B981; }
    .stat-card-icon.orange { background-color: #FFF7ED; color: #F97316; }
    .stat-card-icon.purple { background-color: #F5F3FF; color: #8B5CF6; }
    .stat-card-icon.cyan { background-color: #E0F7FA; color: #00ACC1; }
    .stat-card-icon.gold { background-color: #FFFDE7; color: #FBC02D; }

    /* Analytics Section Styling */
    .analytics-section {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .analytics-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 340px;
    }
    .card-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        border-bottom: 1.5px solid #F1F5F9;
        padding-bottom: 0.75rem;
    }
    .card-header-row h3 {
        font-size: 1.1rem;
        font-weight: 850;
        color: #1E293B;
        margin: 0;
    }
    .card-header-row .sub-text {
        font-size: 0.8rem;
        color: #64748B;
        font-weight: 700;
    }
    .card-header-row .view-all-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1A73E8;
        text-decoration: none;
    }
    .card-header-row .view-all-link:hover {
        text-decoration: underline;
    }
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
        flex-grow: 1;
    }
    
    /* Activity List Styling */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex-grow: 1;
        justify-content: center;
    }
    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 0.75rem;
        border-radius: 16px;
        background: #F8FAFC;
        border: 1px solid #F1F5F9;
        transition: transform 0.2s, background-color 0.2s;
    }
    .activity-item:hover {
        transform: translateX(-3px);
        background: #F1F5F9;
    }
    .activity-avatar {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #E8F1FE;
        color: #1A73E8;
        font-weight: 850;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .activity-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        gap: 0.15rem;
        min-width: 0;
    }
    .activity-details .buyer-name {
        font-size: 0.85rem;
        font-weight: 800;
        color: #1E293B;
    }
    .activity-details .ad-title {
        font-size: 0.75rem;
        color: #64748B;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .activity-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    .activity-meta .price-value {
        font-size: 0.85rem;
        font-weight: 850;
        color: #1E293B;
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-dot.status-pending_payment { background-color: #EAB308; }
    .status-dot.status-paid { background-color: #22C55E; }
    .status-dot.status-receiving { background-color: #F97316; }
    .status-dot.status-received { background-color: #1A73E8; }
    .empty-activity {
        padding: 3rem 1rem;
        text-align: center;
        color: #94A3B8;
        font-weight: 700;
        font-size: 0.9rem;
    }

    /* Stepper Styling */
    .order-stepper-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #E2E8F0;
        gap: 0.15rem;
    }
    .stepper-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        opacity: 0.35;
        transition: opacity 0.3s;
    }
    .stepper-step.active {
        opacity: 1;
    }
    .stepper-circle {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #94A3B8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 900;
        transition: background-color 0.3s;
    }
    .stepper-step.active .stepper-circle {
        background-color: #1A73E8;
    }
    .stepper-step.active:nth-child(1) .stepper-circle { background-color: #F97316; } /* Orange */
    .stepper-step.active:nth-child(3) .stepper-circle { background-color: #22C55E; } /* Green */
    .stepper-step.active:nth-child(5) .stepper-circle { background-color: #3B82F6; } /* Blue */
    .stepper-step.active:nth-child(7) .stepper-circle { background-color: #10B981; } /* Emerald */

    .stepper-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #64748B;
    }
    .stepper-step.active .stepper-label {
        color: #1E293B;
    }
    .stepper-line {
        flex-grow: 1;
        height: 2px;
        background-color: #E2E8F0;
        margin-bottom: 0.75rem;
        transition: background-color 0.3s;
    }
    .stepper-line.active {
        background-color: #1A73E8;
    }
    
    .stat-card-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .stat-card-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748B;
    }
    .stat-card-value {
        font-size: 1.4rem;
        font-weight: 900;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
</style>
@endpush

@section('content')
<div class="my-sales-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <a href="{{ route('home') }}" class="breadcrumbs-pill">
            <span>الرئيسية</span>
            <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
            <span>مبيعاتي</span>
        </a>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="page-title !mb-0" style="margin-bottom: 0 !important;">مبيعاتي</h1>
        </div>

        <!-- Dashboard Stats Grid -->
        <div class="dashboard-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <!-- Total Sales -->
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">إجمالي المبيعات</span>
                    <span class="stat-card-value">
                        <span>{{ number_format($totalSalesAmount) }}</span>
                        <x-riyal-icon class="w-5 h-5 text-[#1A73E8]" />
                    </span>
                </div>
            </div>

            <!-- Net Earnings -->
            <div class="stat-card">
                <div class="stat-card-icon green">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">الأرباح الصافية</span>
                    <span class="stat-card-value">
                        <span>{{ number_format($netEarnings) }}</span>
                        <x-riyal-icon class="w-5 h-5 text-[#10B981]" />
                    </span>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="stat-card">
                <div class="stat-card-icon purple">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">متوسط قيمة الطلب</span>
                    <span class="stat-card-value">
                        <span>{{ number_format($averageOrderValue) }}</span>
                        <x-riyal-icon class="w-5 h-5 text-[#8B5CF6]" />
                    </span>
                </div>
            </div>

            <!-- New Orders -->
            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="fa-solid fa-cart-plus"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">الطلبات الجديدة</span>
                    <span class="stat-card-value">
                        <span>{{ $newOrdersCount }}</span>
                        <span class="text-sm font-bold text-slate-500 mr-1">طلب</span>
                    </span>
                </div>
            </div>



            <!-- Seller Rating -->
            <div class="stat-card">
                <div class="stat-card-icon gold">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">تقييم المتجر/البائع</span>
                    <span class="stat-card-value">
                        @if($sellerRating > 0)
                            <span>{{ $sellerRating }}</span>
                            <span class="text-xs font-bold text-[#FBC02D] mr-1">من 5</span>
                        @else
                            <span class="text-sm font-bold text-slate-500">لا توجد تقييمات</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>



        <!-- Filter Tabs -->
        <div class="filter-tabs-container">
            <a href="{{ route('sales.my', ['status' => 'all'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">الكل</a>
            <a href="{{ route('sales.my', ['status' => 'sold'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'sold' ? 'active' : '' }}">تم البيع</a>
            <a href="{{ route('sales.my', ['status' => 'not_sold'] + request()->except('status', 'page')) }}" class="filter-tab {{ $status === 'not_sold' ? 'active' : '' }}">لم يتم البيع</a>
        </div>

        <!-- Action / Search Bar -->
        <div class="search-action-bar flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <!-- Right: Search input & Filter toggle -->
            <div class="flex items-center gap-3 flex-1 max-w-[560px]">
                <!-- Filter Toggle Button (Desktop & Mobile) -->
                <button type="button" class="action-btn active flex-shrink-0" id="filterToggleBtn" onclick="toggleMySidebar()" title="تصفية" style="height: 48px; border-radius: 16px;">
                    <i class="fa-solid fa-sliders"></i>
                </button>

                <form action="{{ route('sales.my') }}" method="GET" style="position: relative; width: 100%;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    @foreach(request()->except('q', 'page', 'status') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $subK => $subV)
                                        <input type="hidden" name="{{ $key }}[{{ $k }}][{{ $subK }}]" value="{{ $subV }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}">
                                @endif
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في مبيعاتي..." class="search-bar-input">
                    <i class="fa-solid fa-magnifying-glass search-bar-icon" style="position: absolute; top: 50%; transform: translateY(-50%); right: 1.25rem; left: auto; color: #94A3B8; font-size: 1.1rem;"></i>
                </form>
            </div>

            <!-- Left: Actions group -->
            <div class="flex items-center gap-3 flex-wrap">
                @if($status === 'not_sold' && !$ads->isEmpty())
                    <label class="flex items-center gap-2 cursor-pointer transition-colors px-4 py-2 rounded-xl text-sm font-bold text-slate-700 hover:text-blue-600" style="height: 44px; border: 1.5px solid #E2E8F0; background: white; user-select: none;">
                        <input type="checkbox" id="selectAllBulkCheckbox" onchange="toggleSelectAllBulk(this)" class="w-4 h-4 cursor-pointer" style="accent-color: #1A73E8;">
                        <span>تحديد الكل</span>
                    </label>
                @endif
                <!-- Layout Toggles (Grid & List) -->
                <button type="button" class="view-btn active" id="gridViewBtn" onclick="toggleAdsLayout('grid')" title="عرض شبكي">
                    <i class="fa-solid fa-table-cells-large"></i>
                </button>
                <button type="button" class="view-btn" id="listViewBtn" onclick="toggleAdsLayout('list')" title="عرض قائمة">
                    <i class="fa-solid fa-list-ul"></i>
                </button>

                <!-- Order/Sort dropdown -->
                <div class="sort-dropdown-container" id="mySortContainer" onclick="this.classList.toggle('active')">
                    <button class="action-btn">
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <span>الترتيب</span>
                    </button>
                    <div class="sort-options">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="{{ request('sort') == 'newest' || !request('sort') ? 'active' : '' }}">الأحدث</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" class="{{ request('sort') == 'price_high' ? 'active' : '' }}">الأعلى سعراً</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" class="{{ request('sort') == 'price_low' ? 'active' : '' }}">الأقل سعراً</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Sidebar + Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Right Pane: Sidebar Filters -->
            <aside class="lg:col-span-1 block" id="mySidebarPane">
                <div class="filter-card">
                    <div class="filter-header">
                        <h3>تصفية النتائج</h3>
                        <a href="{{ route('sales.my', ['status' => $status]) }}" class="reset-btn">إعادة تعيين</a>
                    </div>

                    <form id="filterForm" action="{{ route('sales.my') }}" method="GET" class="space-y-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        <!-- الموقع (City) -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>الموقع</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <select name="city" class="filter-select font-bold" onchange="this.form.submit()">
                                    <option value="">كل المدن</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- نوع الاعلان -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>نوع الإعلان</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <div class="filter-pills">
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>الكل</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="auction" {{ request('type') == 'auction' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>مزادات</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="direct_purchase" {{ request('type') == 'direct_purchase' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>شراء فوري</span>
                                    </label>
                                    <label class="filter-pill-label">
                                        <input type="radio" name="type" value="tender" {{ request('type') == 'tender' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span>مناقصات</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- الفئات -->
                        <div class="filter-group">
                            <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                <span>الفئة الرئيسية</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="filter-group-content">
                                <div class="filter-categories-list">
                                    <label class="category-filter-item">
                                        <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="radio-custom"></span>
                                        <span class="cat-name font-bold">كل الفئات</span>
                                    </label>
                                    @foreach($categories as $cat)
                                        <label class="category-filter-item">
                                            <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="radio-custom"></span>
                                            <span class="cat-name font-bold">{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Category Properties -->
                        @if(isset($activeCategory))
                            <div class="active-category-info" style="background: #E8F1FE; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(26, 115, 232, 0.15);">
                                <div style="width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.1rem;">
                                    <i class="fa-solid fa-tags"></i>
                                </div>
                                <div class="flex-1">
                                    <span style="display: block; font-size: 0.75rem; color: #64748B; font-weight: 700;">الفئة المختارة</span>
                                    <span style="display: block; font-size: 1rem; color: #1A73E8; font-weight: 800;">{{ $activeCategory->name }}</span>
                                </div>
                                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'props' => null]) }}" style="color: #EF4444; font-size: 0.9rem;" title="إلغاء الفئة"><i class="fa-solid fa-circle-xmark"></i></a>
                            </div>

                            @if($activeCategory->properties->count() > 0)
                                @foreach($activeCategory->properties->sortBy('sort_order') as $prop)
                                    <div class="filter-group">
                                        <div class="filter-group-header" onclick="toggleSidebarGroup(this)">
                                            <span>{{ $prop->label }}</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                        <div class="filter-group-content">
                                            @if($prop->input_type == 'select')
                                                <select name="props[{{ $prop->id }}]" class="filter-select font-bold" onchange="this.form.submit()">
                                                    <option value="">{{ $prop->placeholder ?? 'الكل' }}</option>
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ (request('props')[$prop->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($prop->input_type == 'radio' || $prop->input_type == 'checkbox')
                                                <div class="filter-categories-list">
                                                    @foreach($prop->options ?? [] as $opt)
                                                        <label class="category-filter-item">
                                                            <input type="{{ $prop->input_type }}" name="props[{{ $prop->id }}]{{ $prop->input_type == 'checkbox' ? '[]' : '' }}" value="{{ $opt }}" 
                                                                {{ in_array($opt, (array)(request('props')[$prop->id] ?? [])) ? 'checked' : '' }} onchange="this.form.submit()">
                                                            <span class="radio-custom"></span>
                                                            <span class="cat-name font-bold">{{ $opt }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                 <input type="{{ $prop->input_type == 'price' ? 'number' : ($prop->input_type ?: 'text') }}" name="props[{{ $prop->id }}]" class="filter-select font-bold" placeholder="{{ $prop->placeholder }}" value="{{ is_array(request('props')[$prop->id] ?? '') ? '' : (request('props')[$prop->id] ?? '') }}" onchange="this.form.submit()">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    </form>
                </div>
            </aside>

            <!-- Left Pane: Sales Grid -->
            <div class="lg:col-span-3 transition-all duration-300" id="mySalesGridContent">
                @if($ads->isEmpty())
                    <!-- Empty State -->
                    <div class="empty-state-card">
                        <div class="empty-illustration-container mx-auto mb-6" style="width: 150px; height: 150px; background: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid #F1F5F9;">
                            <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="25" y="15" width="46" height="58" rx="8" fill="#E2E8F0" stroke="#94A3B8" stroke-width="3" />
                                <circle cx="58" cy="58" r="23" fill="white" stroke="#1A73E8" stroke-width="4.5" />
                                <path d="M58 48C58 48 62 45 65 48C68 51 63 57 58 62C53 57 48 51 51 48C54 45 58 48 58 48Z" fill="#1A73E8" stroke="#1A73E8" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M74 74L86 86" stroke="#64748B" stroke-width="5.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="empty-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.5rem; color: #1E293B;">لا يوجد مبيعات حالياً</h2>
                        <p class="empty-subtext" style="color: #64748B; margin-bottom: 0;">المبيعات والعمليات التجارية ستظهر هنا فور إتمامها من قبل المشترين.</p>
                    </div>
                @else
                    <!-- Sales Grid List -->
                    <div id="mySalesContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 transition-all duration-300">
                        @foreach($ads as $ad)
                            @php
                                $image = $ad->image ? (Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/')) : null;
                                $order = $ad->orders->first();
                            @endphp
                            
                            <div class="p-ad-card {{ $status === 'not_sold' ? 'has-bulk-checkbox' : '' }}" data-ad-id="{{ $ad->id }}">
                                @if($status === 'not_sold')
                                    <label class="bulk-select-checkbox-container">
                                        <input type="checkbox" name="bulk_ad_ids[]" value="{{ $ad->id }}" class="bulk-ad-checkbox" onchange="toggleCardBulkSelection(this)">
                                        <span class="bulk-checkbox-custom"></span>
                                    </label>
                                @endif
                                <div class="p-card-main-content" style="cursor: pointer;" onclick="if(!event.target.closest('.bulk-select-checkbox-container')) window.location='{{ in_array($ad->type, ['electronic_auction', 'hybrid_auction']) ? route('bids.history-page', $ad->id) : route('ads.show', $ad->id) }}'">
                                    <div class="p-card-image">
                                        <a href="{{ in_array($ad->type, ['electronic_auction', 'hybrid_auction']) ? route('bids.history-page', $ad->id) : route('ads.show', $ad->id) }}" class="h-full w-full block bg-gray-50">
                                            @if($image)
                                                <img src="{{ $image }}" alt="{{ $ad->title }}">
                                            @else
                                                <div class="flex items-center justify-center h-full w-full text-gray-300">
                                                    <i class="fa-solid fa-image text-4xl"></i>
                                                </div>
                                            @endif
                                        </a>
                                        
                                        <div class="p-badges">
                                            <!-- Sale Type Tag -->
                                            <span class="p-badge-pill {{ $ad->type == 'direct_purchase' ? 'type-direct' : 'type-auction' }}">
                                                @if($ad->type == 'electronic_auction') الكتروني
                                                @elseif($ad->type == 'hybrid_auction') هجين
                                                @elseif($ad->type == 'direct_purchase') شراء فوري
                                                @else مناقصة @endif
                                            </span>
                                            
                                            <!-- Order / Ad Status Tag -->
                                            @if($order)
                                                <span class="p-badge-pill status-{{ str_replace('_', '-', $order->status) }}">
                                                    @if($order->status == 'pending_payment') قيد الدفع
                                                    @elseif($order->status == 'paid') تم الدفع
                                                    @elseif($order->status == 'receiving') قيد الاستلام
                                                    @elseif($order->status == 'received') تم الاستلام
                                                    @endif
                                                </span>
                                            @else
                                                @php
                                                    $is_finished = ($ad->status == 'finished' || ($ad->end_date && \Carbon\Carbon::parse($ad->end_date)->isPast()));
                                                    $status_class = $is_finished ? 'status-expired' : ($ad->status == 'upcoming' ? 'status-receiving' : 'status-received');
                                                    $status_text = $is_finished ? 'منتهي' : ($ad->status == 'upcoming' ? 'قادم' : 'قائم');
                                                @endphp
                                                <span class="p-badge-pill {{ $status_class }}">
                                                    {{ $status_text }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!$order && in_array($ad->status, ['active', 'upcoming']))
                                            <div class="p-countdown" data-end="{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->toIso8601String() : '' }}">
                                                <i class="fa-regular fa-clock"></i>
                                                <span class="countdown-timer">جاري الحساب...</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="p-card-body">
                                        <span class="p-category-pill">{{ $ad->category?->name ?? 'غير مصنف' }}</span>
                                        <a href="{{ in_array($ad->type, ['electronic_auction', 'hybrid_auction']) ? route('bids.history-page', $ad->id) : route('ads.show', $ad->id) }}" style="text-decoration: none; color: inherit;">
                                            <h3 class="p-ad-title">{{ $ad->title }}</h3>
                                        </a>
                                        
                                        <div class="p-price-row">
                                            @php
                                                $price_label = 'السعر الحالي';
                                                if($ad->type == 'direct_purchase') {
                                                    $price_label = 'سعر الشراء';
                                                } elseif($ad->type == 'hybrid_auction') {
                                                    $price_label = 'السعر الابتدائي';
                                                }
                                                
                                                // Update price label for sold items
                                                if($order) {
                                                    if($order->status == 'received') {
                                                        $price_label = 'سعر الشراء';
                                                    } else {
                                                        $price_label = 'السعر النهائي';
                                                    }
                                                }
                                            @endphp
                                            <span class="p-price-label">{{ $price_label }}</span>
                                            <div class="p-price-value font-black text-blue-600">
                                                <span>{{ number_format($order ? $order->final_price : $ad->price) }}</span>
                                                <x-riyal-icon class="w-5 h-5 text-blue-600" />
                                            </div>
                                        </div>
                                        
                                        @if($order)
                                            <!-- Buyer details (only for sold items) -->
                                            <div class="buyer-info-row">
                                                <div class="buyer-avatar-placeholder">
                                                    {{ mb_substr($order->buyer?->name ?? 'م', 0, 1) }}
                                                </div>
                                                <span class="buyer-name">{{ $order->buyer?->name ?? 'مستخدم محذوف' }}</span>
                                            </div>

                                            <!-- Order Status Stepper -->
                                            <div class="order-stepper-container">
                                                <div class="stepper-step {{ in_array($order->status, ['pending_payment', 'paid', 'receiving', 'received']) ? 'active' : '' }}">
                                                    <div class="stepper-circle">1</div>
                                                    <span class="stepper-label">طلب</span>
                                                </div>
                                                <div class="stepper-line {{ in_array($order->status, ['paid', 'receiving', 'received']) ? 'active' : '' }}"></div>
                                                
                                                <div class="stepper-step {{ in_array($order->status, ['paid', 'receiving', 'received']) ? 'active' : '' }}">
                                                    <div class="stepper-circle">2</div>
                                                    <span class="stepper-label">دفع</span>
                                                </div>
                                                <div class="stepper-line {{ in_array($order->status, ['receiving', 'received']) ? 'active' : '' }}"></div>
                                                
                                                <div class="stepper-step {{ in_array($order->status, ['receiving', 'received']) ? 'active' : '' }}">
                                                    <div class="stepper-circle">3</div>
                                                    <span class="stepper-label">شحن</span>
                                                </div>
                                                <div class="stepper-line {{ $order->status === 'received' ? 'active' : '' }}"></div>
                                                
                                                <div class="stepper-step {{ $order->status === 'received' ? 'active' : '' }}">
                                                    <div class="stepper-circle">4</div>
                                                    <span class="stepper-label">استلام</span>
                                                </div>
                                            </div>
                                        @else
                                            <!-- General stats (only for unsold items) -->
                                            <div class="buyer-info-row" style="justify-content: space-between; border-top: 1.5px solid #F1F5F9; padding-top: 0.5rem; margin-top: auto;">
                                                <span style="font-size: 0.8rem; color: #64748B; font-weight: 700;">
                                                    <i class="fa-solid fa-eye" style="margin-left: 0.25rem;"></i>
                                                    {{ $ad->views_count ?? 0 }} مشاهدة
                                                </span>
                                                <span style="font-size: 0.8rem; color: #64748B; font-weight: 700;">
                                                    <i class="fa-solid fa-gavel" style="margin-left: 0.25rem;"></i>
                                                    {{ $ad->bids()->where('type', '!=', 'deposit')->count() }} مزايدين
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Action buttons -->
                                @php
                                    $show_footer = false;
                                    if (!$order) {
                                        $show_footer = true; // Unsold ads have action buttons
                                    } else {
                                        if ($order->status == 'receiving') {
                                            $show_footer = true; // "receiving" status has "توثيق الاستلام" button
                                        }
                                    }
                                @endphp

                                @if($show_footer)
                                    <div class="p-card-footer">
                                        @if($order)
                                            @if($order->status == 'receiving')
                                                <!-- Confirm delivery button -->
                                                <button type="button" onclick="openDeliveryModal('{{ route('orders.confirm-delivery', $order->id) }}')" class="btn btn-p-primary" style="width: 100%; background-color: #1A73E8; border-color: #1A73E8; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 42px; cursor: pointer; font-weight: bold;">
                                                    <span>توثيق الاستلام</span>
                                                </button>
                                            @endif
                                        @else
                                            <div style="display: flex; gap: 0.5rem; width: 100%;">
                                                @if($ad->status == 'finished')
                                                    <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="flex: 1; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px; font-weight: bold;">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        <span>تعديل</span>
                                                    </a>
                                                    <form action="{{ route('ads.repost', $ad->id) }}" method="POST" style="flex: 1; display: flex;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-p-primary" style="width: 100%; background-color: #1A73E8; border-color: #1A73E8; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px; cursor: pointer; text-decoration: none; font-weight: bold;">
                                                            <i class="fa-solid fa-rotate text-sm"></i>
                                                            <span>إعادة البيع</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-p-outline" style="width: 100%; border-color: #1A73E8; color: #1A73E8; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 12px; height: 40px; font-weight: bold;">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        <span>تعديل</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-12 flex justify-center">
                        {{ $ads->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delivery Code Modal -->
<div id="deliveryCodeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-[32px] p-8 max-w-[480px] w-full mx-4 shadow-2xl relative" style="direction: rtl; text-align: right;">
        <!-- Close Button -->
        <button type="button" onclick="closeDeliveryModal()" class="absolute top-6 left-6 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <!-- Title -->
        <h3 class="text-center text-2xl font-black text-slate-800 mb-2 mt-4" style="font-size: 1.5rem;">توثيق الاستلام</h3>
        <p class="text-center text-sm font-bold text-slate-500 mb-6 leading-relaxed">أدخل كود التسليم الذي وصلك من المشتري لإتمام عملية التسليم.</p>

        <!-- Form -->
        <form id="confirmDeliveryForm" action="" method="POST" class="space-y-6">
            @csrf
            <!-- Hidden combined input -->
            <input type="hidden" name="delivery_code" id="combined_code">

            <!-- Code digits inputs (6 separate boxes) -->
            <div class="flex justify-center gap-3" style="direction: ltr;">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
                <input type="text" maxlength="1" pattern="[0-9]*" class="digit-input w-12 h-12 text-center text-xl font-bold bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition-all">
            </div>

            <!-- Footer Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black transition-all" style="background-color: #1A73E8;">إرسال</button>
                <button type="button" onclick="closeDeliveryModal()" class="flex-1 h-12 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-black transition-all">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Floating Action Bar -->
@if($status === 'not_sold')
    <div id="bulkActionBar" class="bulk-action-bar">
        <div class="flex items-center gap-3">
            <span class="bulk-count-badge" id="bulkSelectedCount">0</span>
            <span class="text-sm font-black text-slate-700">تم تحديدها لإجراء عملية بيع جماعية</span>
        </div>
        <button type="button" onclick="openBulkConfirmModal()" class="btn font-bold transition-all hover:scale-105" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border: none; font-size: 0.9rem; padding: 0.7rem 1.8rem; border-radius: 14px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>تم البيع </span>
        </button>
    </div>

    <!-- Bulk Mark Sold Confirmation Modal -->
    <div id="bulkConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-[32px] p-8 max-w-[480px] w-full mx-4 shadow-2xl relative" style="direction: rtl; text-align: right;">
            <!-- Close Button -->
            <button type="button" onclick="closeBulkConfirmModal()" class="absolute top-6 left-6 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <!-- Illustration / Icon -->
            <div class="mx-auto mb-6 flex items-center justify-center" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; color: #10B981;">
                <i class="fa-solid fa-circle-check text-4xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-center text-2xl font-black text-slate-800 mb-2 mt-4" style="font-size: 1.5rem;">تأكيد البيع </h3>
            <p class="text-center text-sm font-bold text-slate-500 mb-6 leading-relaxed">
                هل أنت متأكد من تسجيل هذه الإعلانات كـ <span class="text-emerald-600 font-extrabold">"تم البيع"</span> دفعة واحدة؟ سيتم إدراج طلبات بيع مكتملة لهم ونقلهم فوراً إلى تبويب "تم البيع".
            </p>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="button" onclick="submitBulkMarkSold()" class="btn font-bold transition-all hover:scale-[1.02]" style="flex: 1; background: #10B981; color: white; border: none; height: 48px; border-radius: 14px; cursor: pointer;">تأكيد البيع </button>
                <button type="button" onclick="closeBulkConfirmModal()" class="btn font-bold transition-all hover:scale-[1.02]" style="flex: 1; background: #F1F5F9; color: #475569; border: none; height: 48px; border-radius: 14px; cursor: pointer;">إلغاء</button>
            </div>
        </div>
    </div>

    <!-- Hidden Bulk Mark Sold Form -->
    <form id="bulkMarkSoldForm" action="{{ route('sales.bulk-mark-sold') }}" method="POST" style="display: none;">
        @csrf
        <div id="bulkMarkSoldInputs"></div>
    </form>
@endif

@endsection

@push('scripts')
<script>
    // Toggle sidebar pane visibility dynamically on both desktop and mobile
    window.toggleMySidebar = function() {
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('mySalesGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (!pane || !gridContent) return;
        
        if (pane.classList.contains('hidden')) {
            // Show Sidebar
            pane.classList.remove('hidden');
            pane.classList.add('block');
            gridContent.classList.remove('lg:col-span-4');
            gridContent.classList.add('lg:col-span-3');
            if (toggleBtn) toggleBtn.classList.add('active');
            localStorage.setItem('mySalesFilterVisible', 'true');
        } else {
            // Hide Sidebar
            pane.classList.remove('block');
            pane.classList.add('hidden');
            gridContent.classList.remove('lg:col-span-3');
            gridContent.classList.add('lg:col-span-4');
            if (toggleBtn) toggleBtn.classList.remove('active');
            localStorage.setItem('mySalesFilterVisible', 'false');
        }
    };

    // Toggle layout between Grid and List
    window.toggleAdsLayout = function(mode) {
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        const container = document.getElementById('mySalesContainer');
        
        if (!gridBtn || !listBtn || !container) return;
        
        if (mode === 'list') {
            gridBtn.classList.remove('active');
            listBtn.classList.add('active');
            container.classList.add('list-view');
        } else {
            listBtn.classList.remove('active');
            gridBtn.classList.add('active');
            container.classList.remove('list-view');
        }
        
        // Store layout choice in localStorage
        localStorage.setItem('mySalesLayout', mode);
    };

    // Close sorting dropdown on click outside
    document.addEventListener('click', function(e) {
        const sortContainer = document.getElementById('mySortContainer');
        if (sortContainer && !sortContainer.contains(e.target)) {
            sortContainer.classList.remove('active');
        }
    });

    // Toggle filter groups inside sidebar
    window.toggleSidebarGroup = function(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('i');
        if (content) {
            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    };

    // Modal Control functions
    window.openDeliveryModal = function(actionUrl) {
        const modal = document.getElementById('deliveryCodeModal');
        const form = document.getElementById('confirmDeliveryForm');
        const inputs = document.querySelectorAll('.digit-input');
        
        if (modal && form) {
            form.setAttribute('action', actionUrl);
            modal.classList.remove('hidden');
            
            // Clear inputs and focus first
            inputs.forEach(inp => inp.value = '');
            if (inputs[0]) inputs[0].focus();
        }
    };

    window.closeDeliveryModal = function() {
        const modal = document.getElementById('deliveryCodeModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // 6-digit digits interaction
    const digitInputs = document.querySelectorAll('.digit-input');
    digitInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value;
            // Only allow digits
            if (!/^[0-9]$/.test(val)) {
                e.target.value = '';
                return;
            }
            // Auto focus next
            if (index < digitInputs.length - 1) {
                digitInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (input.value === '' && index > 0) {
                    digitInputs[index - 1].focus();
                    digitInputs[index - 1].value = '';
                } else {
                    input.value = '';
                }
                e.preventDefault();
            }
        });

        // Paste support
        input.addEventListener('paste', (e) => {
            const pasteData = e.clipboardData.getData('text');
            if (/^[0-9]{6}$/.test(pasteData)) {
                digitInputs.forEach((inp, idx) => {
                    inp.value = pasteData[idx];
                });
                digitInputs[digitInputs.length - 1].focus();
                e.preventDefault();
            }
        });
    });

    document.getElementById('confirmDeliveryForm').addEventListener('submit', (e) => {
        let combined = '';
        digitInputs.forEach(inp => combined += inp.value);
        if (combined.length !== 6) {
            Swal.fire({
                title: 'تنبيه',
                text: 'يرجى إدخال كود التسليم المكون من 6 أرقام.',
                icon: 'warning',
                confirmButtonText: 'حسناً',
                customClass: { popup: 'rounded-[32px]' }
            });
            e.preventDefault();
            return;
        }
        document.getElementById('combined_code').value = combined;
    });

    // Load saved preferences on startup
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Layout Preference
        const savedLayout = localStorage.getItem('mySalesLayout');
        if (savedLayout === 'list') {
            toggleAdsLayout('list');
        }

        // 2. Filter Visibility Preference
        const filterVisible = localStorage.getItem('mySalesFilterVisible');
        const pane = document.getElementById('mySidebarPane');
        const gridContent = document.getElementById('mySalesGridContent');
        const toggleBtn = document.getElementById('filterToggleBtn');
        
        if (filterVisible === 'false') {
            if (pane && gridContent) {
                pane.classList.add('hidden');
                pane.classList.remove('block');
                gridContent.classList.add('lg:col-span-4');
                gridContent.classList.remove('lg:col-span-3');
                if (toggleBtn) toggleBtn.classList.remove('active');
            }
        } else {
            if (pane && gridContent) {
                pane.classList.remove('hidden');
                pane.classList.add('block');
                gridContent.classList.remove('lg:col-span-4');
                gridContent.classList.add('lg:col-span-3');
                if (toggleBtn) toggleBtn.classList.add('active');
            }
        }

        // Initialize timers
        const timerElements = document.querySelectorAll('.p-countdown');
        timerElements.forEach(el => {
            const endDateStr = el.getAttribute('data-end');
            if (!endDateStr) return;
            
            const timerSpan = el.querySelector('.countdown-timer');
            const endDate = new Date(endDateStr).getTime();
            
            const updateTimer = () => {
                const now = new Date().getTime();
                const distance = endDate - now;
                
                if (distance < 0) {
                    timerSpan.innerHTML = "منتهي";
                    clearInterval(interval);
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                timerSpan.innerHTML = `${days} أيام : ${hours} ساعة : ${minutes} دقيقة : ${seconds} ثانية`;
            };
            
            updateTimer();
            const interval = setInterval(updateTimer, 1000);
        });

        // Toggle individual card selection
        window.toggleCardBulkSelection = function(input) {
            const card = input.closest('.p-ad-card');
            if (card) {
                if (input.checked) {
                    card.classList.add('selected-for-bulk');
                } else {
                    card.classList.remove('selected-for-bulk');
                }
            }
            updateBulkSelectionState();
        };

        // Toggle select all checkbox
        window.toggleSelectAllBulk = function(selectAllInput) {
            const checkboxes = document.querySelectorAll('.bulk-ad-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllInput.checked;
                const card = cb.closest('.p-ad-card');
                if (card) {
                    if (selectAllInput.checked) {
                        card.classList.add('selected-for-bulk');
                    } else {
                        card.classList.remove('selected-for-bulk');
                    }
                }
            });
            updateBulkSelectionState();
        };

        // Update floating action bar count and visibility
        function updateBulkSelectionState() {
            const checkboxes = document.querySelectorAll('.bulk-ad-checkbox');
            const checkedCount = document.querySelectorAll('.bulk-ad-checkbox:checked').length;
            
            const actionBar = document.getElementById('bulkActionBar');
            const countBadge = document.getElementById('bulkSelectedCount');
            const selectAllCheckbox = document.getElementById('selectAllBulkCheckbox');
            
            if (countBadge) {
                countBadge.innerText = checkedCount;
            }
            
            if (actionBar) {
                if (checkedCount > 0) {
                    actionBar.classList.add('active');
                } else {
                    actionBar.classList.remove('active');
                }
            }
            
            if (selectAllCheckbox && checkboxes.length > 0) {
                selectAllCheckbox.checked = (checkedCount === checkboxes.length);
            }
        }

        // Open confirm bulk mark sold modal
        window.openBulkConfirmModal = function() {
            const modal = document.getElementById('bulkConfirmModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        };

        // Close confirm bulk mark sold modal
        window.closeBulkConfirmModal = function() {
            const modal = document.getElementById('bulkConfirmModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        // Submit bulk action form
        window.submitBulkMarkSold = function() {
            const checkedBoxes = document.querySelectorAll('.bulk-ad-checkbox:checked');
            const inputsContainer = document.getElementById('bulkMarkSoldInputs');
            const form = document.getElementById('bulkMarkSoldForm');
            
            if (!inputsContainer || !form) return;
            
            // Clear previous inputs
            inputsContainer.innerHTML = '';
            
            // Add selected ad IDs to form
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ad_ids[]';
                input.value = cb.value;
                inputsContainer.appendChild(input);
            });
            
            // Submit
            form.submit();
        };
    });
</script>
@endpush
