<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('entity')->get();
        return response()->json($contacts);
    }
    
    public function store(Request $request)
    {
        try {
            $lastContact = Contact::orderBy('id', 'desc')->first();
            $number = $lastContact ? str_pad(intval($lastContact->number) + 1, 6, '0', STR_PAD_LEFT) : '000001';
            
            $contact = Contact::create([
                'entity_id' => $request->entity_id,
                'number' => $number,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_active' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto criado',
                'contact' => $contact
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $contact = Contact::with('entity')->findOrFail($id);
        return response()->json($contact);
    }
    
    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::findOrFail($id);
            
            $contact->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto atualizado'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contact->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}