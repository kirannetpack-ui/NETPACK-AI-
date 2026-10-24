<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\DomesticPartnerAssignment;
use App\Models\DomesticRate;
use App\Models\LogisticsService;
use App\Models\User;
use App\Notifications\DomesticPartnerAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DomesticPartnerAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = DomesticPartnerAssignment::with(['zone', 'partner', 'creator'])
            ->when($request->filled('zone_id'), fn ($query) => $query->where('zone_id', $request->zone_id))
            ->when($request->filled('partner_id'), fn ($query) => $query->where('partner_id', $request->partner_id))
            ->when($request->filled('leg_type'), fn ($query) => $query->where('leg_type', $request->leg_type))
            ->orderByDesc('is_default')->orderBy('priority')->paginate(25)->withQueryString();

        return view('admin.domestic.assignments.index', [
            'assignments' => $assignments,
            'zones' => DeliveryZone::where('is_active', true)->orderBy('zone_name')->get(),
            'partners' => User::where('user_type', 'partner')->where('verification_status', 'approved')->orderBy('name')->get(),
            'services' => $this->services(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = $request->user()->id;

        $assignment = DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                DomesticPartnerAssignment::where('zone_id', $data['zone_id'])
                    ->where('leg_type', $data['leg_type'])
                    ->where('service_type', $data['service_type'])
                    ->update(['is_default' => false]);
            }

            return DomesticPartnerAssignment::updateOrCreate(
                collect($data)->only(['zone_id', 'partner_id', 'leg_type', 'service_type'])->all(),
                $data
            );
        });

        $assignment->load('zone');
        $assignment->partner->notify(new DomesticPartnerAssignmentNotification($assignment));

        return back()->with('success', 'Partner territory assignment saved.');
    }

    public function update(Request $request, DomesticPartnerAssignment $assignment)
    {
        $data = $request->validate($this->rules($assignment));
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($assignment, $data) {
            if ($data['is_default']) {
                DomesticPartnerAssignment::whereKeyNot($assignment->id)
                    ->where('zone_id', $data['zone_id'])
                    ->where('leg_type', $data['leg_type'])
                    ->where('service_type', $data['service_type'])
                    ->update(['is_default' => false]);
            }
            $assignment->update($data);
        });

        $assignment->load('zone', 'partner');
        $assignment->partner->notify(new DomesticPartnerAssignmentNotification($assignment));

        return back()->with('success', 'Partner territory assignment updated.');
    }

    public function destroy(DomesticPartnerAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Partner territory assignment removed.');
    }

    private function rules(?DomesticPartnerAssignment $assignment = null): array
    {
        return [
            'zone_id' => ['required', 'exists:delivery_zones,id'],
            'partner_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('user_type', 'partner')->where('verification_status', 'approved'))],
            'leg_type' => ['required', Rule::in(['pickup', 'logistics', 'delivery', 'door_to_door'])],
            'service_type' => ['required', Rule::in(array_keys($this->services()))],
            'priority' => 'required|integer|min:1|max:999',
            'daily_capacity' => 'nullable|integer|min:1|max:100000',
            'cutoff_time' => 'nullable|date_format:H:i',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function services(): array
    {
        $configured = LogisticsService::active()->category('domestic')->orderBy('sort_order')->pluck('name', 'code')->all();

        return $configured ?: DomesticRate::SERVICE_NAMES;
    }
}
