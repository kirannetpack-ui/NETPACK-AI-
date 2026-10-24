<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display support tickets
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $query = SupportTicket::where('user_id', $userId);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => SupportTicket::where('user_id', $userId)->count(),
            'open' => SupportTicket::where('user_id', $userId)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('user_id', $userId)->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('user_id', $userId)->where('status', 'resolved')->count(),
        ];
        
        return view('seller.support.index', compact('tickets', 'stats'));
    }

    /**
     * Show create ticket form
     */
    public function create()
    {
        $orders = Order::where('seller_id', Auth::id())
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->get();
            
        return view('seller.support.create', compact('orders'));
    }

    /**
     * Store new support ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'priority' => 'nullable|in:low,normal,medium,high',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority ?? 'normal',
            'status' => 'open',
            'ticket_number' => $this->generateTicketNumber(),
        ]);

        // Create initial message
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->description,
            'is_admin' => false,
        ]);

        return redirect()->route('seller.support.show', $ticket->id)
            ->with('success', 'Support ticket created successfully! Ticket #' . $ticket->ticket_number);
    }

    /**
     * Display support ticket
     */
    public function show($id)
    {
        $ticket = SupportTicket::where('user_id', Auth::id())->findOrFail($id);
        $messages = SupportMessage::where('ticket_id', $ticket->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return view('seller.support.show', compact('ticket', 'messages'));
    }

    /**
     * Reply to support ticket
     */
    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'message' => 'required|string',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin' => false,
        ]);

        // Update ticket status if closed
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->route('seller.support.show', $ticket->id)
            ->with('success', 'Reply sent successfully!');
    }

    /**
     * Close support ticket
     */
    public function close($id)
    {
        $ticket = SupportTicket::where('user_id', Auth::id())->findOrFail($id);
        
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()->route('seller.support.show', $ticket->id)
            ->with('success', 'Ticket closed successfully!');
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $ticketNumber = $prefix . '-' . $date . '-' . $random;
        
        // Ensure uniqueness
        while (SupportTicket::where('ticket_number', $ticketNumber)->exists()) {
            $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $ticketNumber = $prefix . '-' . $date . '-' . $random;
        }
        
        return $ticketNumber;
    }
}