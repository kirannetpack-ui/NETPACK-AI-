@extends('layouts.app')

@section('title', 'Support')
@section('page-title', 'Support Center')

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
    <a href="{{ route('client.feedback') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-comment w-5"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('client.support') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
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
        <h2 class="text-2xl font-bold text-gray-800">Support Center</h2>
        <p class="text-gray-500 mt-1">How can we help you today?</p>
    </div>

    <!-- Quick Support Options -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center card-hover transition">
            <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-phone text-2xl text-teal-600"></i>
            </div>
            <h4 class="font-semibold">Call Us</h4>
            <p class="text-sm text-gray-500 mt-1">Available 24/7</p>
            <p class="font-medium text-teal-600 mt-2">+977-1-4234567</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center card-hover transition">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-envelope text-2xl text-blue-600"></i>
            </div>
            <h4 class="font-semibold">Email Us</h4>
            <p class="text-sm text-gray-500 mt-1">Response within 24hrs</p>
            <p class="font-medium text-blue-600 mt-2">support@netpack.com</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center card-hover transition">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-comment-dots text-2xl text-purple-600"></i>
            </div>
            <h4 class="font-semibold">Live Chat</h4>
            <p class="text-sm text-gray-500 mt-1">Chat with an agent</p>
            <button class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg mt-2 transition">
                <i class="fas fa-comment mr-2"></i> Start Chat
            </button>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">Frequently Asked Questions</h3>
        <div class="space-y-3">
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                <button class="w-full flex justify-between items-center faq-toggle">
                    <span class="font-medium text-sm">How do I track my shipment?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="faq-answer hidden mt-2 text-sm text-gray-600">
                    You can track your shipment by entering your tracking number in the "Track Shipment" section on your dashboard.
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                <button class="w-full flex justify-between items-center faq-toggle">
                    <span class="font-medium text-sm">What are the delivery charges?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="faq-answer hidden mt-2 text-sm text-gray-600">
                    Delivery charges vary based on the weight, dimensions, and destination of your package. You can calculate the cost using our rate calculator.
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                <button class="w-full flex justify-between items-center faq-toggle">
                    <span class="font-medium text-sm">How long does delivery take?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="faq-answer hidden mt-2 text-sm text-gray-600">
                    Domestic deliveries typically take 1-3 business days, while international deliveries may take 5-10 business days depending on the destination.
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                <button class="w-full flex justify-between items-center faq-toggle">
                    <span class="font-medium text-sm">How do I cancel a shipment?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="faq-answer hidden mt-2 text-sm text-gray-600">
                    You can cancel a shipment from your dashboard if it hasn't been picked up yet. Contact support for assistance with shipments already in transit.
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">Contact Support</h3>
        <form action="#" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" placeholder="Enter subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea rows="4" placeholder="Describe your issue in detail..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
            </div>
            <button type="submit" class="mt-4 bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 px-6 rounded-lg transition">
                <i class="fas fa-paper-plane mr-2"></i> Send Message
            </button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.faq-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('.fa-chevron-down');
            answer.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });
</script>
@endsection