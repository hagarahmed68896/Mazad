@extends('layouts.main')

@section('title', 'إشعاراتي | مزاد')

@push('styles')
    <style>
        .notifications-container {
            margin-top: 60px;
            margin-bottom: 80px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .notif-card-list {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .notif-card-item:first-child {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .notif-card-item:last-child {
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .notif-card-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 24px 32px;
            border-bottom: 1px solid #F1F5F9;
            transition: all 0.2s;
            position: relative;
            text-decoration: none;
        }

        .notif-card-item:last-child {
            border-bottom: none;
        }

        .notif-card-item.unread {
            background-color: #EBF4FF;
        }

        .notif-card-item:hover {
            background-color: #F8FAFC;
        }

        .notif-avatar-lg {
            width: 56px;
            height: 56px;
            background: #1A73E8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .notif-info {
            flex: 1;
        }

        .notif-info h3 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1E293B;
            margin-bottom: 6px;
            margin-top: 0;
        }

        .notif-info p {
            font-size: 0.95rem;
            color: #64748B;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .notif-card-time {
            font-size: 0.85rem;
            color: #94A3B8;
            font-weight: 600;
        }

        .notif-actions-menu {
            position: relative;
        }

        .menu-btn {
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .menu-btn:hover {
            background: #F1F5F9;
            color: #1E293B;
        }

        .actions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #F1F5F9;
            min-width: 160px;
            z-index: 100;
            display: none;
            padding: 8px 0;
            margin-top: 5px;
        }

        .actions-dropdown.active {
            display: block;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            width: 100%;
            background: none;
            border: none;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-align: right;
            transition: all 0.2s;
            font-family: inherit;
        }

        .action-item:hover {
            background: #F8FAFC;
            color: #1A73E8;
        }

        .action-item.delete {
            color: #EF4444;
        }

        .action-item.delete:hover {
            background: #FEF2F2;
            color: #DC2626;
        }
    /* Pagination Styling */
        .custom-pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 40px;
            margin-bottom: 60px;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: white;
            color: #64748B;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .pagination-btn.active {
            background: #1A73E8;
            color: white;
        }

        .pagination-btn:hover:not(.active) {
            background: #F1F5F9;
            color: #1A73E8;
        }

        .pagination-text {
            padding: 0 16px;
            font-weight: 700;
            color: #64748B;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
    </style>
@endpush

@section('content')
    <div class="container notifications-container">
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-8 font-bold">
            <a href="/" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-[8px]"></i>
            <span class="text-primary">إشعاراتي</span>
        </div>

        <div class="page-header mb-12">
            <h1 class="text-4xl font-black text-gray-900">الإشعارات</h1>
        </div>

        @if($notifications->count() > 0)
            <div class="notif-card-list">
                @foreach($notifications as $notif)
                    <div class="notif-card-item {{ is_null($notif->read_at) ? 'unread' : '' }}" id="notif-{{ $notif->id }}">
                        <div class="notif-avatar-lg">
                            <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="notif-info">
                            <h3>{{ $notif->title }}</h3>
                            <p>{{ $notif->content }}</p>
                            <span class="notif-card-time">{{ $notif->created_at->locale('ar')->diffForHumans() }}</span>
                        </div>
                        <div class="notif-actions-menu">
                            <button class="menu-btn" onclick="toggleActionsMenu(event, '{{ $notif->id }}')">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <div class="actions-dropdown" id="dropdown-{{ $notif->id }}">
                                @if(is_null($notif->read_at))
                                    <button class="action-item" onclick="markAsRead('{{ $notif->id }}')">
                                        <i class="fa-regular fa-envelope-open"></i>
                                        <span>تحديد كمقروء</span>
                                    </button>
                                @endif
                                <button class="action-item delete" onclick="deleteNotification('{{ $notif->id }}')">
                                    <i class="fa-regular fa-trash-can"></i>
                                    <span>حذف الإشعار</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="custom-pagination">
                @if($notifications->onFirstPage())
                    <span class="pagination-text">سابق</span>
                @else
                    <a href="{{ $notifications->previousPageUrl() }}" class="pagination-text">سابق</a>
                @endif

                @foreach($notifications->getUrlRange(max(1, $notifications->currentPage() - 2), min($notifications->lastPage(), $notifications->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="pagination-btn {{ $page == $notifications->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($notifications->hasMorePages())
                    <a href="{{ $notifications->nextPageUrl() }}" class="pagination-text">التالي</a>
                @else
                    <span class="pagination-text">التالي</span>
                @endif
            </div>
        @else
            <div class="notif-card-list">
                <div style="text-align: center; padding: 80px 20px;">
                    <i class="fa-solid fa-bell-slash" style="font-size: 4rem; color: #E2E8F0; margin-bottom: 24px;"></i>
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;">لا توجد إشعارات</h2>
                    <p style="color:#64748B;">ليس لديك أي تنبيهات جديدة في الوقت الحالي.</p>
                </div>
            </div>
        @endif
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
    <script>
        function toggleActionsMenu(event, id) {
            event.preventDefault();
            event.stopPropagation();
            
            // Close all other dropdowns
            document.querySelectorAll('.actions-dropdown').forEach(d => {
                if (d.id !== `dropdown-${id}`) d.classList.remove('active');
            });

            const dropdown = document.getElementById(`dropdown-${id}`);
            dropdown.classList.toggle('active');
        }

        // Close dropdowns when clicking anywhere else
        document.addEventListener('click', () => {
            document.querySelectorAll('.actions-dropdown').forEach(d => d.classList.remove('active'));
        });

        async function markAsRead(id) {
            try {
                const response = await fetch(`/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const card = document.getElementById(`notif-${id}`);
                    if (card.classList.contains('unread')) {
                        card.classList.remove('unread');
                        updateBadgeCount(-1);
                    }
                    
                    const dropdown = document.getElementById(`dropdown-${id}`);
                    const markBtn = dropdown.querySelector('button:not(.delete)');
                    if (markBtn) markBtn.remove();
                }
            } catch (error) {
                console.error('Error marking as read:', error);
            }
        }

        async function deleteNotification(id) {

            try {
                const response = await fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const card = document.getElementById(`notif-${id}`);
                    const wasUnread = card.classList.contains('unread');
                    
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(20px)';
                    
                    if (wasUnread) updateBadgeCount(-1);

                    setTimeout(() => {
                        card.remove();
                        if (document.querySelectorAll('.notif-card-item').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        }

        function updateBadgeCount(change) {
            const badges = document.querySelectorAll('.nav-badge');
            badges.forEach(badge => {
                let currentCount = parseInt(badge.textContent) || 0;
                let newCount = currentCount + change;
                
                if (newCount <= 0) {
                    badge.style.display = 'none';
                    badge.textContent = '0';
                } else {
                    badge.style.display = 'flex';
                    badge.textContent = newCount;
                }
            });
        }
    </script>
@endpush
