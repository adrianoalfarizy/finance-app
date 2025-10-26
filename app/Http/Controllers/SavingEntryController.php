<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\SavingEntry;
use Illuminate\Http\Request;

class SavingEntryController extends Controller
{
    public function store(Request $request, Saving $saving)
    {
        $this->authorize('update', $saving->account);
        $data = $request->validate([
            'type'=>'required|in:deposit,withdraw',
            'amount'=>'required|numeric|min:0.01',
            'transacted_at'=>'required|date',
            'note'=>'nullable|string|max:255'
        ]);

        SavingEntry::create(array_merge($data,['saving_id'=>$saving->id]));
        return back()->with('success','Entri tabungan tersimpan.');
    }
}
