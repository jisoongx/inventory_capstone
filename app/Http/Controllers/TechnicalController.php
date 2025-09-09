<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TechnicalController extends Controller
{ 
    public function index()
    {
        
        if (Auth::guard('super_admin')->check()) {
            $super_admin = Auth::guard('super_admin')->user();
            $staff_id = $super_admin->super_id;

            return view('dashboards.super_admin.technical');

        } elseif (Auth::guard('owner')->check()) {
            return view('dashboards.owner.technical_request');
            
        } elseif (Auth::guard('staff')->check()) {
            return view('dashboards.staff.technical_request');
        }
    }


    public function show($req_id = null)
    {
        
        if (!Auth::guard('owner')->check() && !Auth::guard('staff')->check() && !Auth::guard('super_admin')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $requests = collect();
        $recentreq = null;
        $convos = collect();

        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $owner_id = $owner->owner_id;

            $requests = collect(DB::select("
                SELECT tr.*, cm_max.last_message_id
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) as last_message_id
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                WHERE tr.owner_id = ?
                ORDER BY cm_max.last_message_id DESC
            ", [$owner_id]));

            if ($req_id === null && $requests->isNotEmpty()) {
                $recentreq = $requests->first();
            } else {
                $recentreq = $requests->where('req_id', $req_id)->first();
            }

            if ($recentreq) {
                $convos = collect(DB::select("
                    SELECT cm.*, tr.req_title, tr.req_status
                    FROM technical_request tr
                    LEFT JOIN conversation_message cm
                        ON tr.req_id = cm.req_id
                    WHERE tr.owner_id = ?
                    AND tr.req_id = ?
                    ORDER BY cm.msg_date ASC
                ", [$owner_id, $recentreq->req_id]));
            }

            return view('dashboards.owner.technical_request', [
                'requests' => $requests,
                'recentreq' => $recentreq,
                'convos' => $convos,
            ]);
        }

    
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff_id = $staff->staff_id;

            $requests = collect(DB::select("
                SELECT tr.*, cm_max.last_message_id
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) as last_message_id
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                WHERE tr.staff_id = ?
                ORDER BY cm_max.last_message_id DESC
            ", [$staff_id]));

            if ($req_id === null && $requests->isNotEmpty()) {
                $recentreq = $requests->first();
            } else {
                $recentreq = $requests->where('req_id', $req_id)->first();
            }

            if ($recentreq) {
                $convos = collect(DB::select("
                    SELECT cm.*, tr.req_title, tr.req_status
                    FROM technical_request tr
                    LEFT JOIN conversation_message cm
                        ON tr.req_id = cm.req_id
                    WHERE tr.staff_id = ?
                    AND tr.req_id = ?
                    ORDER BY cm.msg_date ASC
                ", [$staff_id, $recentreq->req_id]));
            }

            return view('dashboards.staff.technical_request', [
                'requests' => $requests,
                'recentreq' => $recentreq,
                'convos' => $convos,
            ]);
        }
    }


    public function add_message(Request $request, $req_id)
    {
        
        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $owner_id = $owner->owner_id;

            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $msg_date = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'owner', ?, ?, ?)
            ", [$req_id, $owner_id, $request->message, $msg_date]);

            return redirect()->route('dashboards.owner.technical_request', ['req_id' => $req_id]);
        }

        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff_id = $staff->staff_id;

            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $msg_date = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'staff', ?, ?, ?)
            ", [$req_id, $staff_id, $request->message, $msg_date]);

            return redirect()->route('dashboards.staff.technical_request', ['req_id' => $req_id]);
        }

        if (Auth::guard('super_admin')->check()) {
            $super_admin = Auth::guard('super_admin')->user();
            $super_id = $super_admin->super_id;

            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $msg_date = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'super', ?, ?, ?)
            ", [$req_id, $super_id, $request->message, $msg_date]);

            return redirect()->route('dashboards.super_admin.technical_show', ['req_id' => $req_id]);
        }

        return redirect()->route('login')->with('error', 'Please login first.');
    }


    public function add_request(Request $request)
    {

        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $owner_id = $owner->owner_id;

            $request->validate([
                'body' => 'required|string|max:1000',
                'title' => 'required|string|max:70',
            ]);

            $req_ticket = $this->generateTicket();
            $req_date = Carbon::now('Asia/Manila')->format('Y-m-d');
            $msg_date = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            DB::insert("
                INSERT INTO technical_request (req_title, req_date, req_status, req_ticket, owner_id, staff_id)
                VALUES (?, ?, 'Pending', ?, ?, NULL)
            ", [$request->title, $req_date, $req_ticket, $owner_id]);

            $req_id = DB::getPdo()->lastInsertId();

            
            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'owner', ?, ?, ?)
            ", [$req_id, $owner_id, $request->body, $msg_date]);

            return redirect()->route('dashboards.owner.technical_request', ['req_id' => $req_id]);
        }

        
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff_id = $staff->staff_id;

            $request->validate([
                'body' => 'required|string|max:1000',
                'title' => 'required|string|max:70',
            ]);

            $req_ticket = $this->generateTicket();
            $req_date = Carbon::now('Asia/Manila')->format('Y-m-d');
            $msg_date = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            
            DB::insert("
                INSERT INTO technical_request (req_title, req_date, req_status, req_ticket, owner_id, staff_id)
                VALUES (?, ?, 'Pending', ?, NULL, ?)
            ", [$request->title, $req_date, $req_ticket, $staff_id]);

            $req_id = DB::getPdo()->lastInsertId();

            
            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'staff', ?, ?, ?)
            ", [$req_id, $staff_id, $request->body, $msg_date]);

            return redirect()->route('dashboards.staff.technical_request', ['req_id' => $req_id]);
        }

        return redirect()->route('login')->with('error', 'Please login first.');
    }

    public function generateTicket(){
        do {
            $ticket = strtolower(
                Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
            );

            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM technical_request 
                WHERE req_ticket = ?
            ", [$ticket]);

        } while ($result[0]->count > 0);

        return $ticket;
    }

}



?>

<!-- 
$requests = collect(DB::select("
            SELECT 
                tr.req_id,
                tr.req_ticket,
                tr.req_title,
                tr.req_status,
                tr.req_date,
                cr.convo_id,
                cm.msg_id,
                cm.sender_type,
                cm.sender_id,
                cm.message,
                cm.msg_date
            FROM technical_request tr
            JOIN conversation_request cr 
                ON tr.req_id = cr.req_id
            LEFT JOIN conversation_message cm 
                ON cr.convo_id = cm.convo_id
            WHERE tr.owner_id = ?
            ORDER BY tr.req_id DESC, cm.msg_date ASC
        ", [$owner_id])); -->
