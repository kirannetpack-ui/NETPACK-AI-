<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\RiderRating;
use App\Models\ShipmentAssignment;
use Illuminate\Http\Request;

class RiderRatingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Submit rating and performance feedback for a completed delivery rider
     */
    public function store(Request $request, $assignmentId)
    {
        $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'professionalism' => 'nullable|integer|min:1|max:5',
            'timeliness' => 'nullable|integer|min:1|max:5',
            'parcel_handling' => 'nullable|integer|min:1|max:5',
            'communication' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        $assignment = ShipmentAssignment::with('riderProfile')
            ->where('seller_id', auth()->id())->where('status', 'completed')->findOrFail($assignmentId);

        if (! $assignment->rider_profile_id || ! $assignment->riderProfile) {
            return redirect()->back()->with('error', 'No rider profile associated with this assignment.');
        }

        // Avoid duplicate ratings from same user
        $existing = RiderRating::where('assignment_id', $assignment->id)
            ->where('rated_by_user_id', auth()->id())
            ->first();

        if ($existing) {
            return redirect()->back()->with('info', 'You have already submitted a rating for this delivery.');
        }

        RiderRating::create([
            'rider_profile_id' => $assignment->rider_profile_id,
            'assignment_id' => $assignment->id,
            'rated_by_user_id' => auth()->id(),
            'overall_rating' => $request->overall_rating,
            'professionalism' => $request->professionalism ?: $request->overall_rating,
            'timeliness' => $request->timeliness ?: $request->overall_rating,
            'parcel_handling' => $request->parcel_handling ?: $request->overall_rating,
            'communication' => $request->communication ?: $request->overall_rating,
            'feedback' => $request->feedback,
        ]);

        // Recalculate dynamic trust score
        $assignment->riderProfile->recalculateTrustScore();

        return redirect()->back()->with('success', 'Thank you! Rider rating recorded and trust score updated.');
    }
}
