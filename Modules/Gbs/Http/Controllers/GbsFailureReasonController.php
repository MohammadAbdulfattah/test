<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Modules\Gbs\Entities\GbsFailureReason;
use DB;

class GbsFailureReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (request()->ajax()) {
        $reasons = GbsFailureReason::select(['id', 'reason']);
        return Datatables::of($reasons)
        ->addColumn('action', function ($row) {
            $html = '';
            if (auth()->user()->can('gbs.edit_reasons')){
            $html .= '<button data-href="' . action([\Modules\Gbs\Http\Controllers\GbsFailureReasonController::class, 'edit'], [$row->id]) . '" 
                class="btn btn-xs btn-primary btn-modal" 
                data-container=".failure_reason_modal">
                <i class="fa fa-edit"></i> ' . __("messages.edit") . '
            </button> ';
            }
            if (auth()->user()->can('gbs.delete_reasons')){
            $html .= '<button href="' . action([\Modules\Gbs\Http\Controllers\GbsFailureReasonController::class, 'destroy'], [$row->id]) . '" 
                class="btn btn-xs btn-danger delete-reason">
                <i class="fas fa-trash"></i> ' . __("messages.delete") . '
            </button>';
            }
            return $html;
        })
        ->rawColumns(['action'])
        ->make(true);
    
     
        }

        return view('gbs::reasons.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!auth()->user()->can('gbs.add_reasons')) {
            abort(403, 'Unauthorized action.');
        }
        return view('gbs::reasons.create_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        GbsFailureReason::create([
            'reason' => $request->reason
        ]);

        return response()->json(['success' => true, 'msg' => __('messages.success')]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('gbs::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (!auth()->user()->can('gbs.edit_reasons')) {
            abort(403, 'Unauthorized action.');
        }
        $reason = GbsFailureReason::findOrFail($id);
      
        return view('gbs::reasons.create_edit', compact('reason'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $reason = GbsFailureReason::findOrFail($id);
        $reason->update(['reason' => $request->reason]);

        return response()->json(['success' => true, 'msg' => __('gbs::lang.updated_success')]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('gbs.delete_reasons')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $reason = GbsFailureReason::findOrFail($id);
    
           
            $usedInVisits = DB::table('gbs_daily_visits')
                ->where('reason_id', $reason->id)
                ->exists();
    
            if ($usedInVisits) {
                return response()->json([
                    'success' => false,
                    'msg' => __('gbs::lang.reason_cannot_be_deleted_used_in_visits')
                ]);
            }
    
            $reason->delete();
    
            return response()->json([
                'success' => true,
                'msg' => __('gbs::lang.deleted_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }
    
}
