<!-- Tracking Update Modal (Air Cargo & Agency Telemetry) -->
<div id="trackingUpdateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-satellite-dish text-indigo-600 dark:text-indigo-400"></i>
                Update Air Cargo Tracking & Telemetry
            </h3>
            <button onclick="closeTrackingModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="trackingUpdateForm" class="space-y-4">
            @csrf
            <input type="hidden" id="modalShipmentId" name="shipment_id" value="">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tracking Number</label>
                    <input type="text" id="modalTrackingNumber" readonly 
                           class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-xs font-bold text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Status / Lifecycle Milestone <span class="text-rose-500">*</span></label>
                    <select id="modalStatus" name="status" required class="w-full border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <optgroup label="7-Stage Agency Lifecycle">
                            <option value="booked">1. Bookings Done</option>
                            <option value="packaging_completed">2. Packaging Completed</option>
                            <option value="export_cleared">3. Export Clearance Done (Nepal)</option>
                            <option value="airline_departed">4. In Transit to Hub by Airlines</option>
                            <option value="arrival_notice">5. Arrival Notice (At Destination Hub)</option>
                            <option value="customs_cleared">6. Import Clearance Completed</option>
                            <option value="handed_over_last_mile">7. Handed Over for Last Mile Delivery</option>
                        </optgroup>
                        <optgroup label="Last Mile & Delivery">
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered to Consignee</option>
                            <option value="failed_delivery">Delivery Attempt Failed</option>
                            <option value="delayed">Operational Delay / Exception</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- Exact Date, Time & Location (User Requirement) -->
            <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                    <i class="fas fa-clock text-indigo-500"></i> Exact Event Telemetry
                </span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Event Date <span class="text-rose-500">*</span></label>
                        <input type="date" id="modalEventDate" name="event_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Event Time <span class="text-rose-500">*</span></label>
                        <input type="time" id="modalEventTime" name="event_time" value="{{ date('H:i') }}" required
                               class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Exact Location <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalLocation" name="location" required list="locationPresets"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                           placeholder="e.g. Dubai Cargo Village (DXB), LHR Port London, Sydney Airport">
                    <datalist id="locationPresets">
                        <option value="Kathmandu (KTM) Export Cargo Terminal, Nepal">
                        <option value="Dubai International Hub (DXB), UAE">
                        <option value="London Heathrow (LHR) Cargo Port, United Kingdom">
                        <option value="Sydney Kingsford Smith (SYD) Gateway, Australia">
                        <option value="Auckland International Airport (AKL), New Zealand">
                        <option value="Toronto Pearson (YYZ) Cargo Terminal, Canada">
                    </datalist>
                </div>
            </div>

            <!-- Last Mile Handover Carrier & Tracking Link -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Last Mile Courier Company</label>
                    <input type="text" id="modalLastMileCarrier" name="last_mile_carrier_name"
                           placeholder="Canpar, Obibox, Royal Mail, AusPost..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Forwarding Tracking #</label>
                    <input type="text" id="modalLastMileTracking" name="last_mile_tracking_number"
                           placeholder="e.g. D10012345678"
                           class="w-full font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Operational Description / Notes</label>
                <textarea id="modalNotes" name="notes" rows="2" 
                          class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                          placeholder="Cargo details, flight departure, customs clearance confirmation..."></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="modalNotifyClient" name="notify_client" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                <label for="modalNotifyClient" class="text-xs font-medium text-slate-700 dark:text-slate-300">
                    Dispatch branded email notification to consignee and shipper
                </label>
            </div>

            <div id="modalMessage" class="hidden text-xs rounded-xl p-3 font-semibold"></div>

            <div class="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeTrackingModal()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn" 
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition">
                    <i class="fas fa-save mr-1.5"></i> Update Milestone & Telemetry
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTrackingModal(shipmentId, trackingNumber) {
    document.getElementById('modalShipmentId').value = shipmentId;
    document.getElementById('modalTrackingNumber').value = trackingNumber;
    document.getElementById('trackingUpdateModal').classList.remove('hidden');
    document.getElementById('modalMessage').classList.add('hidden');
    document.getElementById('modalStatus').focus();
}

function closeTrackingModal() {
    document.getElementById('trackingUpdateModal').classList.add('hidden');
}

// Handle form submission
document.getElementById('trackingUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const shipmentId = document.getElementById('modalShipmentId').value;
    const status = document.getElementById('modalStatus').value;
    const eventDate = document.getElementById('modalEventDate').value;
    const eventTime = document.getElementById('modalEventTime').value;
    const location = document.getElementById('modalLocation').value;
    const carrier = document.getElementById('modalLastMileCarrier').value;
    const lastMileTracking = document.getElementById('modalLastMileTracking').value;
    const notes = document.getElementById('modalNotes').value;
    const notifyClient = document.getElementById('modalNotifyClient').checked;
    
    const messageDiv = document.getElementById('modalMessage');
    const submitBtn = document.getElementById('modalSubmitBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Recording Telemetry...';
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    fetch('/tracking/update-status/' + shipmentId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status,
            event_code: status,
            event_date: eventDate,
            event_time: eventTime,
            location: location,
            last_mile_carrier_name: carrier,
            last_mile_tracking_number: lastMileTracking,
            description: notes,
            notify_client: notifyClient
        })
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.className = 'p-3 rounded-xl text-xs font-semibold ' + (data.success ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800');
        messageDiv.textContent = data.message || (data.success ? '✅ Status updated and client notified!' : '❌ Update failed');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-1.5"></i> Update Milestone & Telemetry';
        }
    })
    .catch(error => {
        messageDiv.className = 'p-3 rounded-xl bg-rose-100 text-rose-800 text-xs font-semibold';
        messageDiv.textContent = '❌ Error: ' + error.message;
        messageDiv.classList.remove('hidden');
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-1.5"></i> Update Milestone & Telemetry';
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTrackingModal();
    }
});

document.getElementById('trackingUpdateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTrackingModal();
    }
});
</script>