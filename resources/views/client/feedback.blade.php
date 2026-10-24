@extends('layouts.app')

@section('title', 'Feedback')
@section('page-title', 'Feedback & Support')

@section('sidebar')
    <a href="{{ route('client.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('shipments.create') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-plus-circle w-5"></i>
        <span>New Shipment</span>
    </a>
    <a href="{{ route('tracking.page') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-search w-5"></i>
        <span>Track Shipment</span>
    </a>
    <a href="{{ route('grocery.box') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-shopping-bag w-5"></i>
        <span>Grocery Box</span>
    </a>
    <a href="{{ route('client.feedback') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-comment w-5"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('client.support') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-headset w-5"></i>
        <span>Support</span>
    </a>
    <a href="{{ route('profile') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-user w-5"></i>
        <span>Profile</span>
    </a>
    <a href="{{ route('client.settings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800">Feedback & Support</h2>
        <p class="text-gray-500 mt-1">We value your feedback. Let us know how we can improve your experience.</p>
    </div>

    <!-- Feedback Form -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Send Feedback</h3>
            <form action="{{ route('feedback.submit') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Feedback Type</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="general">General Feedback</option>
                            <option value="bug">Bug Report</option>
                            <option value="feature">Feature Request</option>
                            <option value="complaint">Complaint</option>
                            <option value="suggestion">Suggestion</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" placeholder="Brief subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="4" placeholder="Describe your feedback in detail..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                        <div class="flex space-x-2">
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400" data-value="1">★</button>
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400" data-value="2">★</button>
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400" data-value="3">★</button>
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400" data-value="4">★</button>
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400" data-value="5">★</button>
                            <input type="hidden" name="rating" id="rating" value="0" />
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Recent Feedback</h3>
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-sm">Great Service!</p>
                            <p class="text-xs text-gray-500">2 days ago</p>
                        </div>
                        <span class="text-yellow-400">★★★★★</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">The delivery was fast and the package arrived in perfect condition.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-sm">Tracking Issue</p>
                            <p class="text-xs text-gray-500">5 days ago</p>
                        </div>
                        <span class="text-yellow-400">★★★☆☆</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">The tracking system was not updating properly.</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-sm">Excellent Support</p>
                            <p class="text-xs text-gray-500">1 week ago</p>
                        </div>
                        <span class="text-yellow-400">★★★★★</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">The support team was very helpful and resolved my issue quickly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            document.getElementById('rating').value = value;
            document.querySelectorAll('.rating-star').forEach(s => {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            });
            for (let i = 0; i < value; i++) {
                document.querySelectorAll('.rating-star')[i].classList.remove('text-gray-300');
                document.querySelectorAll('.rating-star')[i].classList.add('text-yellow-400');
            }
        });
    });
</script>
@endsection