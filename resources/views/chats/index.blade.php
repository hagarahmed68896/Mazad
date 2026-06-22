@extends('layouts.main')

@section('title', 'الدردشات')

@section('content')
<div class="chat-page bg-[#F8FAFC] min-h-screen pb-20">
    <div class="container mx-auto px-4 md:px-16 pt-12">
        <!-- Breadcrumbs -->
        <div class="mb-6 md:mb-8">
            <nav class="bg-primary px-4 md:px-6 py-2.5 md:py-3 w-auto md:w-fit rounded-full flex items-center gap-2 md:gap-3 text-white text-xs md:text-sm font-bold shadow-lg shadow-primary/20">
                <a href="{{ route('home') }}" class="hover:text-blue-100 transition-colors whitespace-nowrap">الرئيسية</a>
                <i class="fa-solid fa-chevron-left text-[8px] md:text-[10px] opacity-50"></i>
                <span class="text-blue-100 whitespace-nowrap">الدردشات</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 mt-6">الدردشات</h1>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-[32px] md:rounded-[40px] shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row h-[600px] md:h-[800px]">
            
            <!-- Sidebar: Conversations List -->
            <div class="w-full md:w-[350px] lg:w-[400px] border-b md:border-b-0 md:border-l border-gray-100 flex flex-col bg-white overflow-hidden shrink-0 {{ isset($activeConversation) ? 'hidden md:flex' : 'flex' }}">
                <!-- Sidebar Header -->
                <div class="p-4 md:p-6 border-b border-gray-50 bg-white sticky top-0 z-10">
                    <form action="{{ route('chats.index') }}" method="GET" class="relative mb-4">
                        @if(request('filter'))
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                        @endif
                        @if(request('id'))
                            <input type="hidden" name="id" value="{{ request('id') }}">
                        @endif
                        <i class="fa-solid fa-magnifying-glass absolute right-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" id="conversationSearch" value="{{ request('search') ?: request('q') }}" placeholder="ابحث في المحادثات..." class="w-full bg-gray-50 border-none rounded-2xl py-3.5 pr-12 pl-4 focus:ring-2 focus:ring-primary/20 font-bold text-sm transition-all">
                    </form>
                    
                    <div class="flex gap-2">
                        @php $currentFilter = request('filter', 'all'); @endphp
                        <a href="{{ route('chats.index', ['filter' => 'all'] + (request()->has('id') ? ['id' => request('id')] : [])) }}" 
                           class="px-5 py-2 {{ $currentFilter == 'all' ? 'bg-primary text-white shadow-md shadow-blue-100' : 'bg-gray-50 text-gray-400 hover:bg-gray-100' }} rounded-xl font-black text-sm transition-all">الكل</a>
                        
                        <a href="{{ route('chats.index', ['filter' => 'unread'] + (request()->has('id') ? ['id' => request('id')] : [])) }}" 
                           class="px-5 py-2 {{ $currentFilter == 'unread' ? 'bg-primary text-white shadow-md shadow-blue-100' : 'bg-gray-50 text-gray-400 hover:bg-gray-100' }} rounded-xl font-bold text-sm transition-all">غير مقروء</a>
                    </div>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto bg-white">
                    @forelse($conversations as $conv)
                        @php
                            $otherUser = $conv->user_one_id == auth()->id() ? $conv->userTwo : $conv->userOne;
                            $lastMessage = $conv->messages->last();
                            $isActive = isset($activeConversation) && $activeConversation->id == $conv->id;
                            $unreadCount = $conv->messages->where('is_read', false)->where('sender_id', '!=', auth()->id())->count();
                            $hasUnread = $unreadCount > 0;
                            $isPinned = ($conv->user_one_id == auth()->id() ? $conv->user_one_pinned : $conv->user_two_pinned);
                            $isMuted = ($conv->user_one_id == auth()->id() ? $conv->user_one_muted : $conv->user_two_muted);
                        @endphp
                        <div class="relative group">
                            <a href="{{ route('chats.index', ['id' => $conv->id]) }}" 
                               class="conversation-item flex items-center gap-4 p-5 hover:bg-gray-100 transition-all border-b border-gray-50 {{ $isActive ? 'bg-blue-50/50' : ($hasUnread ? 'bg-gray-50' : '') }}">
                                <div class="relative">
                                    <img src="{{ $otherUser->avatar ? asset($otherUser->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=1A73E8&color=fff' }}" 
                                         class="w-14 h-14 rounded-full object-cover shadow-sm">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="font-black text-gray-900 truncate">{{ $otherUser->name }}</h4>
                                        <div class="flex items-center gap-2">
                                            @if($isPinned)
                                                <i class="fa-solid fa-thumbtack text-[10px] text-primary rotate-45"></i>
                                            @endif
                                            <span class="text-[10px] text-gray-400 font-bold">
                                                @if($conv->last_message_at)
                                                    @php $lastDate = \Carbon\Carbon::parse($conv->last_message_at); @endphp
                                                    {{ $lastDate->isToday() ? $lastDate->format('H:i') : ($lastDate->isYesterday() ? 'أمس' : $lastDate->format('j/n')) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <p class="text-xs {{ $hasUnread ? 'text-gray-900 font-black' : 'text-gray-500' }} truncate opacity-70">
                                            @if($lastMessage && $lastMessage->sender_id == auth()->id()) أنت: @endif
                                            {{ $lastMessage ? $lastMessage->content : 'لا توجد رسائل' }}
                                        </p>
                                        @if($hasUnread)
                                            <span class="unread-count-badge w-5 h-5 bg-primary text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg shadow-blue-100">{{ $unreadCount }}</span>
                                        @elseif($isMuted)
                                            <i class="fa-solid fa-bell-slash text-[10px] text-gray-300"></i>
                                        @endif
                                    </div>
                                </div>
                            </a>

                            <!-- Sidebar Item Dropdown -->
                            <div class="absolute left-4 top-4 z-20">
                                <button onclick="event.preventDefault(); event.stopPropagation(); toggleSidebarItemDropdown({{ $conv->id }})" 
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-primary hover:bg-white shadow-sm opacity-0 group-hover:opacity-100 transition-all bg-gray-50/50">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div id="sidebarDropdown-{{ $conv->id }}" class="sidebar-item-dropdown hidden absolute left-0 top-full mt-1 w-48 bg-white rounded-2xl shadow-2xl border border-gray-50 z-30 py-2 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <button onclick="event.stopPropagation(); togglePin({{ $conv->id }})" class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition-all group">
                                        <span class="text-xs font-bold">{{ $isPinned ? 'إلغاء التثبيت' : 'تثبيت في الأعلى' }}</span>
                                        <i class="fa-solid fa-thumbtack text-sm opacity-50 group-hover:opacity-100 transition-all"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); confirmDeleteChat({{ $conv->id }})" class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-red-50 text-red-500 transition-all group">
                                        <span class="text-xs font-bold">حذف</span>
                                        <i class="fa-regular fa-trash-can text-sm opacity-50 group-hover:opacity-100 transition-all"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); toggleBlock({{ $otherUser->id }})" class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition-all group">
                                        <span class="text-xs font-bold">{{ \App\Models\BlockedUser::where('user_id', auth()->id())->where('blocked_user_id', $otherUser->id)->exists() ? 'إلغاء الحظر' : 'حظر' }}</span>
                                        <i class="fa-solid fa-ban text-sm opacity-50 group-hover:opacity-100 transition-all"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); toggleMute({{ $conv->id }})" class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition-all group">
                                        <span class="text-xs font-bold">{{ $isMuted ? 'تفعيل الإشعارات' : 'كتم الإشعارات' }}</span>
                                        <i class="fa-solid fa-bell-slash text-sm opacity-50 group-hover:opacity-100 transition-all"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <i class="fa-solid fa-comment-dots text-5xl text-gray-200 mb-4 block"></i>
                            <p class="text-gray-400 font-bold">لا توجد محادثات</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="flex-1 flex flex-col bg-gray-50/20 overflow-hidden relative {{ isset($activeConversation) ? 'flex' : 'hidden md:flex' }}">
                @if(isset($activeConversation))
                    <!-- Chat Header -->
                    <div class="p-4 md:p-6 bg-white border-b border-gray-100 flex items-center justify-between z-10">
                        <div class="flex items-center gap-2 md:gap-4">
                            <!-- Back Button for Mobile -->
                            <a href="{{ route('chats.index') }}" class="md:hidden w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-primary transition-all">
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </a>
                            <div class="relative">
                                <img src="{{ $activeConversation->otherUser()->avatar ? asset($activeConversation->otherUser()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($activeConversation->otherUser()->name).'&background=1A73E8&color=fff' }}" 
                                     class="w-12 h-12 rounded-full object-cover {{ $isBlocked ? 'grayscale opacity-50' : '' }}">
                                @if($isBlocked)
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fa-solid fa-ban text-red-500 text-xs"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-gray-900 text-lg leading-tight flex items-center gap-2">
                                        {{ $activeConversation->otherUser()->name }}
                                        @if($activeConversation->otherUser()->company_name)
                                            <span class="text-gray-300 font-normal mx-1">•</span>
                                            <a href="{{ route('users.profile', $activeConversation->otherUser()->id) }}" class="text-sm text-gray-500 font-bold hover:text-primary transition-all underline decoration-gray-200 hover:decoration-primary underline-offset-4">
                                                {{ $activeConversation->otherUser()->company_name }}
                                            </a>
                                        @endif
                                    </h3>
                                    @if($isBlocked)
                                        <span class="bg-red-50 text-red-500 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">محظور</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative flex items-center group">
                                <input type="text" id="messageSearchInput" placeholder="بحث في الرسائل..." 
                                       class="w-0 opacity-0 bg-gray-50 border-none rounded-xl py-2 pr-10 pl-4 focus:ring-2 focus:ring-primary/10 font-bold text-sm transition-all duration-300">
                                <button id="toggleMessageSearch" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-gray-100 transition-all absolute right-0">
                                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                                </button>
                            </div>
                            <div class="relative">
                                <button id="chatMoreBtn" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-gray-100 transition-all">
                                    <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                                </button>
                                <div id="chatMoreDropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-white rounded-3xl shadow-2xl border border-gray-50 z-[100] py-2 animate-in fade-in zoom-in duration-200">
                                    <form id="deleteChatForm-{{ $activeConversation->id }}" action="{{ route('chats.destroy', $activeConversation->id) }}" method="POST" class="px-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="event.stopPropagation(); confirmDeleteChat({{ $activeConversation->id }})" class="w-full flex items-center justify-between p-4 hover:bg-red-50 text-red-500 rounded-2xl transition-all group">
                                            <span class="text-sm font-black">حذف المحادثة</span>
                                            <i class="fa-regular fa-trash-can text-lg opacity-50 group-hover:opacity-100"></i>
                                        </button>
                                    </form>
                                    <div class="h-px bg-gray-50 my-1 mx-4"></div>
                                    <div class="px-2">
                                        <button type="button" onclick="event.stopPropagation(); showReportUserModal({{ $activeConversation->otherUser()->id }})" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 text-gray-700 rounded-2xl transition-all group">
                                            <span class="text-sm font-bold">الإبلاغ عن هذا المستخدم</span>
                                            <i class="fa-regular fa-flag text-lg opacity-50 group-hover:opacity-100"></i>
                                        </button>
                                        <form id="reportUserForm" action="{{ route('users.report', $activeConversation->otherUser()->id) }}" method="POST" class="hidden">@csrf</form>
                                        
                                        <a href="{{ route('users.profile', $activeConversation->otherUser()->id) }}" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 text-gray-700 rounded-2xl transition-all group">
                                            <span class="text-sm font-bold">الملف الشخصي للمستخدم</span>
                                            <i class="fa-regular fa-circle-user text-lg opacity-50 group-hover:opacity-100"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-6" id="messagesContainer">
                        @if($isBlocked)
                            <div class="bg-red-50/50 border border-red-100 rounded-[32px] p-6 mb-4 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                                <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-500 shrink-0">
                                    <i class="fa-solid fa-shield-halved text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-black text-red-900 text-sm mb-1">هذا المستخدم محظور</h4>
                                </div>
                            </div>
                        @endif
                        @if($activeConversation->ad)
                            <!-- Ad Context Card -->
                            <div class="bg-white rounded-3xl p-3 border border-gray-100 shadow-sm flex items-center gap-4 max-w-md mx-auto mb-4">
                                <img src="{{ $activeConversation->ad->image ? (Str::startsWith($activeConversation->ad->image, 'http') ? $activeConversation->ad->image : '/' . ltrim($activeConversation->ad->image, '/')) : 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=200' }}" 
                                     class="w-16 h-16 rounded-2xl object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-black text-gray-900 truncate">{{ $activeConversation->ad->title }}</h4>
                                    <div class="flex items-center gap-1 text-primary mt-1">
                                        <span class="font-black text-sm">{{ number_format($activeConversation->ad->price) }}</span>
                                        <span class="text-[10px] font-bold">ريال</span>
                                    </div>
                                </div>
                                <a href="{{ route('ads.show', $activeConversation->ad->id) }}" class="bg-gray-50 text-gray-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-gray-100 transition-all shrink-0">
                                    شاهد الاعلان
                                </a>
                            </div>
                        @endif

                        <div class="text-center">
                            <span class="bg-white px-4 py-1.5 rounded-full text-[10px] font-black text-gray-400 border border-gray-50">
                                @if($activeConversation->messages->first())
                                    {{ \Carbon\Carbon::parse($activeConversation->messages->first()->created_at)->translatedFormat('j F Y') }}
                                @else
                                    {{ now()->translatedFormat('j F Y') }}
                                @endif
                            </span>
                        </div>

                        @foreach($activeConversation->messages as $msg)
                            @php $isMine = $msg->sender_id == auth()->id(); @endphp
                            <div class="flex {{ $isMine ? 'justify-start flex-row-reverse' : 'justify-start' }} gap-4">
                                <img src="{{ $msg->sender->avatar ? asset($msg->sender->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($msg->sender->name).'&background=1A73E8&color=fff' }}" 
                                     class="w-10 h-10 rounded-full shrink-0 shadow-sm border border-gray-100">
                                <div class="max-w-[70%]">
                                    <div class="flex items-center gap-2 mb-1 {{ $isMine ? 'flex-row-reverse' : '' }}">
                                        <span class="text-xs font-black text-gray-900">{{ $isMine ? 'أنت' : $msg->sender->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }} م</span>
                                    </div>
                                    <div class="p-4 rounded-2xl font-bold text-sm leading-relaxed {{ $isMine ? 'bg-primary text-white rounded-tl-none' : 'bg-white text-gray-700 shadow-sm border border-gray-50 rounded-tr-none' }}">
                                        @if($msg->attachment)
                                            <div class="mb-2">
                                                @php $ext = pathinfo($msg->attachment, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <a href="{{ asset($msg->attachment) }}" target="_blank">
                                                        <img src="{{ asset($msg->attachment) }}" class="rounded-xl max-w-full h-auto shadow-sm hover:opacity-90 transition-all mb-2">
                                                    </a>
                                                @else
                                                    <a href="{{ asset($msg->attachment) }}" target="_blank" class="flex items-center gap-3 p-3 {{ $isMine ? 'bg-white/20' : 'bg-gray-100' }} rounded-xl hover:opacity-80 transition-all mb-2">
                                                        <i class="fa-solid fa-file-lines text-xl"></i>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-[10px] opacity-70 mb-0.5">مرفق</div>
                                                            <div class="text-xs truncate">{{ basename($msg->attachment) }}</div>
                                                        </div>
                                                        <i class="fa-solid fa-download text-xs opacity-50"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                        {{ $msg->content }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Chat Footer -->
                    <div class="p-4 md:p-6 bg-white border-t border-gray-100 sticky bottom-0">
                        @if($isBlocked)
                            <div class="bg-gray-50 rounded-2xl p-5 text-center border border-dashed border-gray-200">
                                <p class="text-gray-400 font-bold text-sm">
                                    <i class="fa-solid fa-ban ml-2"></i> لا يمكنك المراسلة في هذه المحادثة
                                </p>
                            </div>
                        @else
                            <form id="chatForm" action="{{ route('chats.send', $activeConversation->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                                @csrf
                                <button type="submit" class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center shadow-lg shadow-blue-100 hover:scale-105 transition-all">
                                    <i class="fa-solid fa-paper-plane transform -rotate-45"></i>
                                </button>
                                <div class="flex-1 relative">
                                    <!-- Attachment Preview -->
                                    <div id="attachmentPreview" class="hidden absolute bottom-full left-0 right-0 mb-4 animate-in slide-in-from-bottom-2 duration-300">
                                        <div class="flex items-center justify-between p-3 bg-primary/5 border border-primary/10 rounded-2xl backdrop-blur-sm">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                                    <i class="fa-solid fa-paperclip"></i>
                                                </div>
                                                <span id="previewFileName" class="text-xs font-black text-primary truncate max-w-[200px]"></span>
                                            </div>
                                            <button type="button" onclick="clearAttachment()" class="w-8 h-8 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-all flex items-center justify-center">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <input type="text" name="content" id="messageInput" placeholder="اكتب رسالتك هنا..." class="w-full bg-gray-50 border-none rounded-2xl py-4 pr-6 pl-12 focus:ring-2 focus:ring-primary/10 font-bold text-sm">
                                    <input type="file" name="attachment" id="attachmentInput" class="hidden" onchange="handleFileSelect(this)">
                                    <button type="button" onclick="document.getElementById('attachmentInput').click()" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-all">
                                        <i id="attachmentIcon" class="fa-solid fa-paperclip text-lg transition-colors"></i>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-12">
                        <div class="max-w-sm">
                            <div class="w-40 h-40 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                                <div class="relative">
                                    <i class="fa-solid fa-envelope-open-text text-6xl text-gray-200"></i>
                                    <div class="absolute -top-4 -right-4 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-paper-plane text-primary text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            <h2 class="text-2xl font-black text-gray-900 mb-4">لا توجد رسائل بعد</h2>
                            <p class="text-gray-400 font-bold leading-relaxed">
                                ابدأ المحادثة الآن وتواصل مباشرة مع المعلن للحصول على التفاصيل.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messagesContainer');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('messageInput');
        
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        window.handleFileSelect = function(input) {
            const preview = document.getElementById('attachmentPreview');
            const fileName = document.getElementById('previewFileName');
            const icon = document.getElementById('attachmentIcon');
            
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
                preview.classList.remove('hidden');
                icon.classList.add('text-primary');
                icon.classList.remove('text-gray-400');
            }
        }

        window.clearAttachment = function() {
            const input = document.getElementById('attachmentInput');
            const preview = document.getElementById('attachmentPreview');
            const icon = document.getElementById('attachmentIcon');
            
            input.value = '';
            preview.classList.add('hidden');
            icon.classList.remove('text-primary');
            icon.classList.add('text-gray-400');
        }

        function appendMessage(data) {
            const container = document.getElementById('messagesContainer');
            if (!container) return;

            const msg = data.message;
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex justify-start flex-row-reverse gap-4 animate-in fade-in slide-in-from-bottom-2 duration-300`;

            let attachmentHtml = '';
            if (msg.attachment) {
                const ext = msg.attachment.split('.').pop().toLowerCase();
                const assetUrl = `/${msg.attachment}`;
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);

                if (isImage) {
                    attachmentHtml = `
                        <div class="mb-2">
                            <a href="${assetUrl}" target="_blank">
                                <img src="${assetUrl}" class="rounded-xl max-w-full h-auto shadow-sm hover:opacity-90 transition-all mb-2">
                            </a>
                        </div>`;
                } else {
                    const baseName = msg.attachment.split('/').pop();
                    attachmentHtml = `
                        <div class="mb-2">
                            <a href="${assetUrl}" target="_blank" class="flex items-center gap-3 p-3 bg-white/20 rounded-xl hover:opacity-80 transition-all mb-2">
                                <i class="fa-solid fa-file-lines text-xl"></i>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] opacity-70 mb-0.5">مرفق</div>
                                    <div class="text-xs truncate">${baseName}</div>
                                </div>
                                <i class="fa-solid fa-download text-xs opacity-50"></i>
                            </a>
                        </div>`;
                }
            }

            messageDiv.innerHTML = `
                <img src="${data.sender_avatar}" class="w-10 h-10 rounded-full shrink-0 shadow-sm border border-gray-100">
                <div class="max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1 flex-row-reverse">
                        <span class="text-xs font-black text-gray-900">أنت</span>
                        <span class="text-[10px] text-gray-400 font-bold">${data.time} م</span>
                    </div>
                    <div class="p-4 rounded-2xl font-bold text-sm leading-relaxed bg-primary text-white rounded-tl-none">
                        ${attachmentHtml}
                        ${msg.content || ''}
                    </div>
                </div>
            `;

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const attachmentInput = document.getElementById('attachmentInput');
                const formData = new FormData(this);
                
                if (!input.value.trim() && (!attachmentInput || attachmentInput.files.length === 0)) return;

                submitBtn.disabled = true;
                const originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'خطأ في السيرفر');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        appendMessage(data);
                        input.value = '';
                        clearAttachment();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;

                        // Update sidebar preview dynamically
                        @if(isset($activeConversation))
                        const activeItem = document.querySelector('a[href*="id={{ $activeConversation->id }}"]');
                        if (activeItem) {
                            const textEl = activeItem.querySelector('p.text-xs');
                            if (textEl) {
                                textEl.textContent = `أنت: ${data.message.content || 'مرفق'}`;
                                textEl.className = 'text-xs text-gray-500 truncate opacity-70';
                            }
                            const timeEl = activeItem.querySelector('span.text-\\[10px\\]');
                            if (timeEl) {
                                timeEl.textContent = data.time;
                            }
                        }
                        @endif
                    } else {
                        throw new Error(data.message || 'فشل الإرسال');
                    }
                })
                .catch(error => {
                    console.error('Chat Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: error.message
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                });
            });
        }

        // Sidebar Conversation Search
        const conversationSearch = document.getElementById('conversationSearch');
        if (conversationSearch) {
            conversationSearch.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                const items = document.querySelectorAll('.conversation-item');
                
                items.forEach(item => {
                    const name = item.querySelector('h4').textContent.toLowerCase();
                    if (name.includes(term)) {
                        item.classList.remove('hidden');
                        item.classList.add('flex');
                    } else {
                        item.classList.remove('flex');
                        item.classList.add('hidden');
                    }
                });
            });
        }

        // Inside Chat Message Search
        const toggleMessageSearch = document.getElementById('toggleMessageSearch');
        const messageSearchInput = document.getElementById('messageSearchInput');
        
        if (toggleMessageSearch && messageSearchInput) {
            toggleMessageSearch.addEventListener('click', function() {
                const isHidden = messageSearchInput.classList.contains('w-0');
                if (isHidden) {
                    messageSearchInput.classList.remove('w-0', 'opacity-0');
                    messageSearchInput.classList.add('w-48', 'opacity-100', 'mr-2');
                    messageSearchInput.focus();
                } else {
                    messageSearchInput.classList.add('w-0', 'opacity-0');
                    messageSearchInput.classList.remove('w-48', 'opacity-100', 'mr-2');
                    messageSearchInput.value = '';
                    // Reset filters and highlights when closing
                    const messages = document.querySelectorAll('#messagesContainer > div');
                    messages.forEach(msg => {
                        msg.classList.remove('hidden');
                        const bubble = msg.querySelector('.p-4');
                        if (bubble && bubble.hasAttribute('data-original')) {
                            bubble.innerHTML = bubble.getAttribute('data-original');
                        }
                    });
                }
            });

            messageSearchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                const messages = document.querySelectorAll('#messagesContainer > div');
                
                messages.forEach(msg => {
                    const bubble = msg.querySelector('.p-4');
                    if (!bubble) return;

                    // Store original text for restoration
                    if (!bubble.hasAttribute('data-original')) {
                        bubble.setAttribute('data-original', bubble.innerHTML);
                    }

                    const originalHTML = bubble.getAttribute('data-original');
                    
                    if (term === '') {
                        bubble.innerHTML = originalHTML;
                        msg.classList.remove('hidden');
                        return;
                    }

                    const textContent = bubble.textContent.toLowerCase();
                    if (textContent.includes(term)) {
                        msg.classList.remove('hidden');
                        // Highlight matching text
                        const regex = new RegExp(`(${term})`, 'gi');
                        bubble.innerHTML = originalHTML.replace(regex, '<mark class="bg-yellow-200 text-gray-900 px-0.5 rounded-sm">$1</mark>');
                    } else {
                        msg.classList.add('hidden');
                        bubble.innerHTML = originalHTML;
                    }
                });
            });
        }

        // Dropdown Toggle logic
        const chatMoreBtn = document.getElementById('chatMoreBtn');
        const chatMoreDropdown = document.getElementById('chatMoreDropdown');
        
        if (chatMoreBtn && chatMoreDropdown) {
            chatMoreBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                chatMoreDropdown.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!chatMoreBtn.contains(e.target) && !chatMoreDropdown.contains(e.target)) {
                    chatMoreDropdown.classList.add('hidden');
                }
            });
        }

        // Sidebar Item Dropdowns
        document.addEventListener('click', function(e) {
            const allDropdowns = document.querySelectorAll('.sidebar-item-dropdown');
            allDropdowns.forEach(d => d.classList.add('hidden'));
        });
    });

    function toggleSidebarItemDropdown(id) {
        const dropdown = document.getElementById('sidebarDropdown-' + id);
        const allDropdowns = document.querySelectorAll('.sidebar-item-dropdown');
        
        allDropdowns.forEach(d => {
            if (d.id !== 'sidebarDropdown-' + id) d.classList.add('hidden');
        });
        
        dropdown.classList.toggle('hidden');
    }

    function togglePin(id) {
        fetch(`/chats/${id}/toggle-pin`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function toggleMute(id) {
        fetch(`/chats/${id}/toggle-mute`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function toggleBlock(userId) {
        fetch(`/users/${userId}/toggle-block`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: data.is_blocked ? 'تم الحظر بنجاح' : 'تم إلغاء الحظر'
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    function confirmDeleteChat(id) {
        Swal.fire({
            title: 'هل تريد حذف المحادثة؟',
            text: "سيتم حذف المحادثة نهائياً من قائمة الدردشات، ولن تتمكن من استعادتها لاحقاً.",
            showCancelButton: true,
            confirmButtonText: 'حذف',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-[40px] p-8 md:p-12',
                title: 'text-2xl md:text-3xl font-black text-gray-900 mb-4',
                htmlContainer: 'text-gray-400 font-bold leading-relaxed mb-10',
                actions: 'flex gap-4 w-full max-w-sm mx-auto',
                confirmButton: 'flex-1 bg-[#EF4444] hover:bg-red-600 text-white font-black py-4 rounded-2xl transition-all text-lg',
                cancelButton: 'flex-1 bg-gray-100 hover:bg-gray-200 text-gray-500 font-black py-4 rounded-2xl transition-all text-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteChatForm-' + id).submit();
            }
        });
    }

    function showReportUserModal(userId) {
        Swal.fire({
            title: 'الإبلاغ عن هذا المستخدم',
            html: `
                <div class="text-right mb-3">
                    <label class="text-sm font-bold text-gray-700">سبب البلاغ</label>
                </div>
                <textarea id="reportReason" class="w-full bg-gray-50 border-none rounded-2xl p-5 focus:ring-2 focus:ring-primary/10 font-bold text-sm h-32 leading-relaxed" placeholder="اكتب تفاصيل البلاغ هنا..."></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'إرسال',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
            buttonsStyling: false,
            showCloseButton: true,
            customClass: {
                popup: 'rounded-[40px] p-8 md:p-12 relative',
                title: 'text-2xl md:text-3xl font-black text-gray-900 mb-8 text-center',
                actions: 'flex gap-4 w-full mt-8',
                confirmButton: 'flex-1 bg-primary hover:bg-blue-700 text-white font-black py-4 rounded-2xl transition-all text-lg',
                cancelButton: 'flex-1 bg-gray-100 hover:bg-gray-200 text-gray-500 font-black py-4 rounded-2xl transition-all text-lg',
                closeButton: 'absolute top-8 left-8 text-gray-300 hover:text-gray-500 transition-colors'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = document.getElementById('reportReason').value.trim();
                if (!reason) return;

                const form = document.getElementById('reportUserForm');
                const url = form.getAttribute('action');
                const token = form.querySelector('input[name="_token"]').value;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ reason: reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الإرسال',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'rounded-[32px]',
                                title: 'font-black'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء إرسال البلاغ',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ في الاتصال بالخادم',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    }
</script>

<style>
    .chat-page {
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }
    
    ::-webkit-scrollbar {
        width: 4px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #E2E8F0;
        border-radius: 10px;
    }
</style>
@endsection
