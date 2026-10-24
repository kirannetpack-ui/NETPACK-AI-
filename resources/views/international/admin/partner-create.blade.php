@extends('layouts.app')

@section('title', 'Add Overseas Partner')

@section('styles')
<style>
    .country-item {
        transition: all 0.15s ease;
    }
    .country-item:hover {
        background-color: #f0fdf4;
    }
    .country-item.selected {
        background-color: #f0fdf4;
        color: #0d9488;
    }
    #countryDropdown {
        min-width: 280px;
    }
    .country-item {
        border-left: 3px solid transparent;
    }
    .country-item.selected {
        border-left-color: #0d9488;
    }
    #countrySearch:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }
    #countryDropdownBtn {
        transition: all 0.15s ease;
    }
    #countryDropdownBtn:hover {
        border-color: #0d9488;
    }
    #countryDropdown {
        animation: slideDown 0.2s ease;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    /* Scrollbar styling */
    #countryList::-webkit-scrollbar {
        width: 6px;
    }
    #countryList::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    #countryList::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    #countryList::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Add Overseas Partner</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new overseas partner account</p>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('international.partners.store') }}">
                @csrf

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Partner Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Partner Information</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Partner Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500 @enderror"
                               placeholder="e.g., John Doe">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('company_name') border-red-500 @enderror"
                               placeholder="e.g., ABC Logistics">
                        @error('company_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person *</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('contact_person') border-red-500 @enderror"
                               placeholder="e.g., John Doe">
                        @error('contact_person')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror"
                               placeholder="partner@company.com">
                        <p class="text-xs text-gray-500 mt-1">This email will receive the auto-generated password</p>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number with Country Code -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number with Country Code *</label>
                        <div class="flex gap-2">
                            <div class="w-2/5 relative">
                                <input type="hidden" name="phone_code" id="phone_code" value="{{ old('phone_code', '+977') }}">
                                
                                <button type="button" id="countryDropdownBtn" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white flex items-center justify-between">
                                    <span id="selectedCountryDisplay" class="flex items-center gap-2">
                                        <span class="text-lg">🇳🇵</span>
                                        <span class="font-medium">+977</span>
                                        <span class="text-gray-500 text-sm hidden md:inline">Nepal</span>
                                    </span>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>
                                
                                <div id="countryDropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-hidden">
                                    <div class="sticky top-0 bg-white p-2 border-b">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                            <input type="text" id="countrySearch" placeholder="Search country..." 
                                                   class="w-full border rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                                        </div>
                                    </div>
                                    <div id="countryList" class="overflow-y-auto max-h-60">
                                    </div>
                                </div>
                            </div>
                            <div class="w-3/5">
                                <input type="tel" name="phone" value="{{ old('phone') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-500 @enderror"
                                       placeholder="Enter phone number (e.g., 1234567890)">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Select country code and enter phone number</p>
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Country *</label>
                        <input type="text" name="country" value="{{ old('country') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('country') border-red-500 @enderror"
                               placeholder="e.g., USA">
                        @error('country')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g., New York">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="123 Main Street">
                    </div>

                    <!-- Mandatory HUB Information -->
                    <div class="md:col-span-2 mt-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-blue-800 mb-2">
                                <i class="fas fa-exclamation-circle mr-2"></i> 
                                Mandatory HUB Information
                            </h4>
                            <p class="text-sm text-blue-600 mb-3">
                                Each overseas partner must have at least one HUB. This is the main hub location for the partner.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">HUB Name *</label>
                                    <input type="text" name="hub_name" value="{{ old('hub_name') }}" required 
                                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('hub_name') border-red-500 @enderror"
                                           placeholder="e.g., New York Main Hub">
                                    @error('hub_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">HUB Location *</label>
                                    <input type="text" name="hub_location" value="{{ old('hub_location') }}" required 
                                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('hub_location') border-red-500 @enderror"
                                           placeholder="e.g., New York">
                                    @error('hub_location')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">HUB Country *</label>
                                    <input type="text" name="hub_country" value="{{ old('hub_country') }}" required 
                                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('hub_country') border-red-500 @enderror"
                                           placeholder="e.g., USA">
                                    @error('hub_country')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional TRANSIT Points -->
                    <div class="md:col-span-2 mt-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-2">
                                <i class="fas fa-plus-circle text-teal-600 mr-2"></i> 
                                Optional Transit Points
                            </h4>
                            <p class="text-sm text-gray-500 mb-3">
                                You can add transit points now or add them later. Transit points are intermediate stops for shipments.
                            </p>
                            
                            <div id="transit-points-container">
                                <div class="transit-point-item grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Transit Point Name</label>
                                        <input type="text" name="transit_name[]" 
                                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                               placeholder="e.g., Chicago Transit">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Location</label>
                                        <input type="text" name="transit_location[]" 
                                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                               placeholder="e.g., Chicago">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Country</label>
                                        <input type="text" name="transit_country[]" 
                                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                               placeholder="e.g., USA">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" onclick="addTransitPoint()" class="text-teal-600 hover:text-teal-800 text-sm font-medium mt-2">
                                <i class="fas fa-plus mr-1"></i> Add Another Transit Point
                            </button>
                        </div>
                    </div>

                    <!-- Password Notice -->
                    <div class="md:col-span-2 mt-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-green-800 mb-2">
                                <i class="fas fa-key mr-2"></i> 
                                Password Information
                            </h4>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>✓ <strong>Auto-generated password</strong> will be created by the system</li>
                                <li>✓ The password will be sent to the partner's email address</li>
                                <li>✓ Partner must <strong>change password</strong> on first login</li>
                                <li>✓ No need to manually set a password</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Partner
                    </button>
                    <a href="{{ route('international.partners') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Country data with codes, flags, and names
const countries = [
    // Asia
    { code: '+93', flag: '🇦🇫', name: 'Afghanistan' },
    { code: '+374', flag: '🇦🇲', name: 'Armenia' },
    { code: '+994', flag: '🇦🇿', name: 'Azerbaijan' },
    { code: '+973', flag: '🇧🇭', name: 'Bahrain' },
    { code: '+880', flag: '🇧🇩', name: 'Bangladesh' },
    { code: '+975', flag: '🇧🇹', name: 'Bhutan' },
    { code: '+673', flag: '🇧🇳', name: 'Brunei' },
    { code: '+855', flag: '🇰🇭', name: 'Cambodia' },
    { code: '+86', flag: '🇨🇳', name: 'China' },
    { code: '+852', flag: '🇭🇰', name: 'Hong Kong' },
    { code: '+91', flag: '🇮🇳', name: 'India' },
    { code: '+62', flag: '🇮🇩', name: 'Indonesia' },
    { code: '+98', flag: '🇮🇷', name: 'Iran' },
    { code: '+964', flag: '🇮🇶', name: 'Iraq' },
    { code: '+972', flag: '🇮🇱', name: 'Israel' },
    { code: '+81', flag: '🇯🇵', name: 'Japan' },
    { code: '+962', flag: '🇯🇴', name: 'Jordan' },
    { code: '+7', flag: '🇰🇿', name: 'Kazakhstan' },
    { code: '+965', flag: '🇰🇼', name: 'Kuwait' },
    { code: '+996', flag: '🇰🇬', name: 'Kyrgyzstan' },
    { code: '+856', flag: '🇱🇦', name: 'Laos' },
    { code: '+961', flag: '🇱🇧', name: 'Lebanon' },
    { code: '+853', flag: '🇲🇴', name: 'Macau' },
    { code: '+60', flag: '🇲🇾', name: 'Malaysia' },
    { code: '+960', flag: '🇲🇻', name: 'Maldives' },
    { code: '+976', flag: '🇲🇳', name: 'Mongolia' },
    { code: '+95', flag: '🇲🇲', name: 'Myanmar' },
    { code: '+977', flag: '🇳🇵', name: 'Nepal' },
    { code: '+850', flag: '🇰🇵', name: 'North Korea' },
    { code: '+968', flag: '🇴🇲', name: 'Oman' },
    { code: '+92', flag: '🇵🇰', name: 'Pakistan' },
    { code: '+970', flag: '🇵🇸', name: 'Palestine' },
    { code: '+63', flag: '🇵🇭', name: 'Philippines' },
    { code: '+974', flag: '🇶🇦', name: 'Qatar' },
    { code: '+966', flag: '🇸🇦', name: 'Saudi Arabia' },
    { code: '+65', flag: '🇸🇬', name: 'Singapore' },
    { code: '+82', flag: '🇰🇷', name: 'South Korea' },
    { code: '+94', flag: '🇱🇰', name: 'Sri Lanka' },
    { code: '+963', flag: '🇸🇾', name: 'Syria' },
    { code: '+886', flag: '🇹🇼', name: 'Taiwan' },
    { code: '+992', flag: '🇹🇯', name: 'Tajikistan' },
    { code: '+66', flag: '🇹🇭', name: 'Thailand' },
    { code: '+90', flag: '🇹🇷', name: 'Turkey' },
    { code: '+993', flag: '🇹🇲', name: 'Turkmenistan' },
    { code: '+971', flag: '🇦🇪', name: 'UAE' },
    { code: '+998', flag: '🇺🇿', name: 'Uzbekistan' },
    { code: '+84', flag: '🇻🇳', name: 'Vietnam' },
    { code: '+967', flag: '🇾🇪', name: 'Yemen' },
    
    // Europe
    { code: '+355', flag: '🇦🇱', name: 'Albania' },
    { code: '+376', flag: '🇦🇩', name: 'Andorra' },
    { code: '+43', flag: '🇦🇹', name: 'Austria' },
    { code: '+375', flag: '🇧🇾', name: 'Belarus' },
    { code: '+32', flag: '🇧🇪', name: 'Belgium' },
    { code: '+387', flag: '🇧🇦', name: 'Bosnia' },
    { code: '+359', flag: '🇧🇬', name: 'Bulgaria' },
    { code: '+385', flag: '🇭🇷', name: 'Croatia' },
    { code: '+357', flag: '🇨🇾', name: 'Cyprus' },
    { code: '+420', flag: '🇨🇿', name: 'Czech Republic' },
    { code: '+45', flag: '🇩🇰', name: 'Denmark' },
    { code: '+372', flag: '🇪🇪', name: 'Estonia' },
    { code: '+358', flag: '🇫🇮', name: 'Finland' },
    { code: '+33', flag: '🇫🇷', name: 'France' },
    { code: '+995', flag: '🇬🇪', name: 'Georgia' },
    { code: '+49', flag: '🇩🇪', name: 'Germany' },
    { code: '+30', flag: '🇬🇷', name: 'Greece' },
    { code: '+36', flag: '🇭🇺', name: 'Hungary' },
    { code: '+354', flag: '🇮🇸', name: 'Iceland' },
    { code: '+353', flag: '🇮🇪', name: 'Ireland' },
    { code: '+39', flag: '🇮🇹', name: 'Italy' },
    { code: '+383', flag: '🇽🇰', name: 'Kosovo' },
    { code: '+371', flag: '🇱🇻', name: 'Latvia' },
    { code: '+423', flag: '🇱🇮', name: 'Liechtenstein' },
    { code: '+370', flag: '🇱🇹', name: 'Lithuania' },
    { code: '+352', flag: '🇱🇺', name: 'Luxembourg' },
    { code: '+389', flag: '🇲🇰', name: 'Macedonia' },
    { code: '+356', flag: '🇲🇹', name: 'Malta' },
    { code: '+373', flag: '🇲🇩', name: 'Moldova' },
    { code: '+377', flag: '🇲🇨', name: 'Monaco' },
    { code: '+382', flag: '🇲🇪', name: 'Montenegro' },
    { code: '+31', flag: '🇳🇱', name: 'Netherlands' },
    { code: '+47', flag: '🇳🇴', name: 'Norway' },
    { code: '+48', flag: '🇵🇱', name: 'Poland' },
    { code: '+351', flag: '🇵🇹', name: 'Portugal' },
    { code: '+40', flag: '🇷🇴', name: 'Romania' },
    { code: '+378', flag: '🇸🇲', name: 'San Marino' },
    { code: '+381', flag: '🇷🇸', name: 'Serbia' },
    { code: '+421', flag: '🇸🇰', name: 'Slovakia' },
    { code: '+386', flag: '🇸🇮', name: 'Slovenia' },
    { code: '+34', flag: '🇪🇸', name: 'Spain' },
    { code: '+46', flag: '🇸🇪', name: 'Sweden' },
    { code: '+41', flag: '🇨🇭', name: 'Switzerland' },
    { code: '+380', flag: '🇺🇦', name: 'Ukraine' },
    { code: '+44', flag: '🇬🇧', name: 'United Kingdom' },
    { code: '+379', flag: '🇻🇦', name: 'Vatican City' },
    
    // North America
    { code: '+1', flag: '🇺🇸', name: 'United States' },
    { code: '+1', flag: '🇨🇦', name: 'Canada' },
    { code: '+52', flag: '🇲🇽', name: 'Mexico' },
    { code: '+501', flag: '🇧🇿', name: 'Belize' },
    { code: '+506', flag: '🇨🇷', name: 'Costa Rica' },
    { code: '+53', flag: '🇨🇺', name: 'Cuba' },
    { code: '+1', flag: '🇩🇲', name: 'Dominica' },
    { code: '+1', flag: '🇩🇴', name: 'Dominican Republic' },
    { code: '+503', flag: '🇸🇻', name: 'El Salvador' },
    { code: '+502', flag: '🇬🇹', name: 'Guatemala' },
    { code: '+504', flag: '🇭🇳', name: 'Honduras' },
    { code: '+1', flag: '🇯🇲', name: 'Jamaica' },
    { code: '+505', flag: '🇳🇮', name: 'Nicaragua' },
    { code: '+507', flag: '🇵🇦', name: 'Panama' },
    { code: '+1', flag: '🇵🇷', name: 'Puerto Rico' },
    { code: '+1', flag: '🇹🇹', name: 'Trinidad and Tobago' },
    
    // South America
    { code: '+54', flag: '🇦🇷', name: 'Argentina' },
    { code: '+591', flag: '🇧🇴', name: 'Bolivia' },
    { code: '+55', flag: '🇧🇷', name: 'Brazil' },
    { code: '+56', flag: '🇨🇱', name: 'Chile' },
    { code: '+57', flag: '🇨🇴', name: 'Colombia' },
    { code: '+593', flag: '🇪🇨', name: 'Ecuador' },
    { code: '+592', flag: '🇬🇾', name: 'Guyana' },
    { code: '+595', flag: '🇵🇾', name: 'Paraguay' },
    { code: '+51', flag: '🇵🇪', name: 'Peru' },
    { code: '+597', flag: '🇸🇷', name: 'Suriname' },
    { code: '+598', flag: '🇺🇾', name: 'Uruguay' },
    { code: '+58', flag: '🇻🇪', name: 'Venezuela' },
    
    // Africa
    { code: '+213', flag: '🇩🇿', name: 'Algeria' },
    { code: '+244', flag: '🇦🇴', name: 'Angola' },
    { code: '+267', flag: '🇧🇼', name: 'Botswana' },
    { code: '+226', flag: '🇧🇫', name: 'Burkina Faso' },
    { code: '+257', flag: '🇧🇮', name: 'Burundi' },
    { code: '+237', flag: '🇨🇲', name: 'Cameroon' },
    { code: '+238', flag: '🇨🇻', name: 'Cape Verde' },
    { code: '+236', flag: '🇨🇫', name: 'Central African Republic' },
    { code: '+235', flag: '🇹🇩', name: 'Chad' },
    { code: '+269', flag: '🇰🇲', name: 'Comoros' },
    { code: '+243', flag: '🇨🇩', name: 'Congo' },
    { code: '+242', flag: '🇨🇬', name: 'Congo Republic' },
    { code: '+225', flag: '🇨🇮', name: 'Ivory Coast' },
    { code: '+253', flag: '🇩🇯', name: 'Djibouti' },
    { code: '+20', flag: '🇪🇬', name: 'Egypt' },
    { code: '+240', flag: '🇬🇶', name: 'Equatorial Guinea' },
    { code: '+291', flag: '🇪🇷', name: 'Eritrea' },
    { code: '+268', flag: '🇸🇿', name: 'Eswatini' },
    { code: '+251', flag: '🇪🇹', name: 'Ethiopia' },
    { code: '+241', flag: '🇬🇦', name: 'Gabon' },
    { code: '+220', flag: '🇬🇲', name: 'Gambia' },
    { code: '+233', flag: '🇬🇭', name: 'Ghana' },
    { code: '+224', flag: '🇬🇳', name: 'Guinea' },
    { code: '+245', flag: '🇬🇼', name: 'Guinea-Bissau' },
    { code: '+254', flag: '🇰🇪', name: 'Kenya' },
    { code: '+266', flag: '🇱🇸', name: 'Lesotho' },
    { code: '+231', flag: '🇱🇷', name: 'Liberia' },
    { code: '+218', flag: '🇱🇾', name: 'Libya' },
    { code: '+261', flag: '🇲🇬', name: 'Madagascar' },
    { code: '+265', flag: '🇲🇼', name: 'Malawi' },
    { code: '+223', flag: '🇲🇱', name: 'Mali' },
    { code: '+222', flag: '🇲🇷', name: 'Mauritania' },
    { code: '+230', flag: '🇲🇺', name: 'Mauritius' },
    { code: '+212', flag: '🇲🇦', name: 'Morocco' },
    { code: '+258', flag: '🇲🇿', name: 'Mozambique' },
    { code: '+264', flag: '🇳🇦', name: 'Namibia' },
    { code: '+227', flag: '🇳🇪', name: 'Niger' },
    { code: '+234', flag: '🇳🇬', name: 'Nigeria' },
    { code: '+250', flag: '🇷🇼', name: 'Rwanda' },
    { code: '+239', flag: '🇸🇹', name: 'Sao Tome' },
    { code: '+221', flag: '🇸🇳', name: 'Senegal' },
    { code: '+248', flag: '🇸🇨', name: 'Seychelles' },
    { code: '+232', flag: '🇸🇱', name: 'Sierra Leone' },
    { code: '+252', flag: '🇸🇴', name: 'Somalia' },
    { code: '+27', flag: '🇿🇦', name: 'South Africa' },
    { code: '+211', flag: '🇸🇸', name: 'South Sudan' },
    { code: '+249', flag: '🇸🇩', name: 'Sudan' },
    { code: '+255', flag: '🇹🇿', name: 'Tanzania' },
    { code: '+228', flag: '🇹🇬', name: 'Togo' },
    { code: '+216', flag: '🇹🇳', name: 'Tunisia' },
    { code: '+256', flag: '🇺🇬', name: 'Uganda' },
    { code: '+260', flag: '🇿🇲', name: 'Zambia' },
    { code: '+263', flag: '🇿🇼', name: 'Zimbabwe' },
    
    // Oceania
    { code: '+61', flag: '🇦🇺', name: 'Australia' },
    { code: '+679', flag: '🇫🇯', name: 'Fiji' },
    { code: '+682', flag: '🇨🇰', name: 'Cook Islands' },
    { code: '+674', flag: '🇳🇷', name: 'Nauru' },
    { code: '+64', flag: '🇳🇿', name: 'New Zealand' },
    { code: '+675', flag: '🇵🇬', name: 'Papua New Guinea' },
    { code: '+685', flag: '🇼🇸', name: 'Samoa' },
    { code: '+677', flag: '🇸🇧', name: 'Solomon Islands' },
    { code: '+676', flag: '🇹🇴', name: 'Tonga' },
    { code: '+688', flag: '🇹🇻', name: 'Tuvalu' },
    { code: '+678', flag: '🇻🇺', name: 'Vanuatu' },
    
    // Caribbean
    { code: '+1', flag: '🇦🇬', name: 'Antigua and Barbuda' },
    { code: '+1', flag: '🇧🇸', name: 'Bahamas' },
    { code: '+1', flag: '🇧🇧', name: 'Barbados' },
    { code: '+1', flag: '🇧🇲', name: 'Bermuda' },
    { code: '+1', flag: '🇻🇬', name: 'British Virgin Islands' },
    { code: '+1', flag: '🇰🇾', name: 'Cayman Islands' },
    { code: '+1', flag: '🇬🇩', name: 'Grenada' },
    { code: '+509', flag: '🇭🇹', name: 'Haiti' },
    { code: '+1', flag: '🇱🇨', name: 'Saint Lucia' },
    { code: '+1', flag: '🇻🇨', name: 'Saint Vincent' },
    { code: '+1', flag: '🇹🇨', name: 'Turks and Caicos' },
];

// Sort countries by name
countries.sort((a, b) => a.name.localeCompare(b.name));

// DOM Elements
const dropdownBtn = document.getElementById('countryDropdownBtn');
const dropdown = document.getElementById('countryDropdown');
const countryList = document.getElementById('countryList');
const searchInput = document.getElementById('countrySearch');
const selectedDisplay = document.getElementById('selectedCountryDisplay');
const hiddenInput = document.getElementById('phone_code');

// Current selected country
let selectedCountry = countries.find(c => c.code === '+977') || countries[0];

// Render country list
function renderCountries(filter = '') {
    const filtered = countries.filter(c => 
        c.name.toLowerCase().includes(filter.toLowerCase()) ||
        c.code.includes(filter)
    );
    
    countryList.innerHTML = filtered.map(c => `
        <div class="country-item px-4 py-2 hover:bg-teal-50 cursor-pointer flex items-center gap-3 transition-colors ${c.code === selectedCountry.code ? 'bg-teal-50 text-teal-600 selected' : ''}"
             data-code="${c.code}" data-flag="${c.flag}" data-name="${c.name}">
            <span class="text-xl">${c.flag}</span>
            <span class="font-medium">${c.code}</span>
            <span class="text-gray-600 text-sm">${c.name}</span>
        </div>
    `).join('');

    // Add click event listeners
    document.querySelectorAll('.country-item').forEach(item => {
        item.addEventListener('click', function() {
            const code = this.dataset.code;
            const flag = this.dataset.flag;
            const name = this.dataset.name;
            selectCountry({ code, flag, name });
        });
    });
}

// Select a country
function selectCountry(country) {
    selectedCountry = country;
    hiddenInput.value = country.code;
    selectedDisplay.innerHTML = `
        <span class="text-lg">${country.flag}</span>
        <span class="font-medium">${country.code}</span>
        <span class="text-gray-500 text-sm hidden md:inline">${country.name}</span>
    `;
    closeDropdown();
    renderCountries(searchInput.value);
}

// Toggle dropdown
function toggleDropdown() {
    dropdown.classList.toggle('hidden');
    if (!dropdown.classList.contains('hidden')) {
        searchInput.value = '';
        renderCountries();
        setTimeout(() => searchInput.focus(), 100);
    }
}

// Close dropdown
function closeDropdown() {
    dropdown.classList.add('hidden');
}

// Event Listeners
dropdownBtn.addEventListener('click', toggleDropdown);

searchInput.addEventListener('input', function() {
    renderCountries(this.value);
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!dropdownBtn.contains(event.target) && !dropdown.contains(event.target)) {
        closeDropdown();
    }
});

// Keyboard support
dropdownBtn.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleDropdown();
    }
});

// Initial render
renderCountries();

// Set default selected
selectCountry(selectedCountry);

// Transit Points - Add/Remove functionality
function addTransitPoint() {
    const container = document.getElementById('transit-points-container');
    const newItem = document.createElement('div');
    newItem.className = 'transit-point-item grid grid-cols-1 md:grid-cols-3 gap-3 mb-3';
    newItem.innerHTML = `
        <div>
            <label class="block text-sm font-medium mb-1">Transit Point Name</label>
            <input type="text" name="transit_name[]" 
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                   placeholder="e.g., Chicago Transit">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Location</label>
            <input type="text" name="transit_location[]" 
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                   placeholder="e.g., Chicago">
        </div>
        <div class="flex items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Country</label>
                <input type="text" name="transit_country[]" 
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="e.g., USA">
            </div>
            <button type="button" onclick="this.closest('.transit-point-item').remove()" class="ml-2 text-red-600 hover:text-red-800 mb-2">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newItem);
}
</script>
@endsection