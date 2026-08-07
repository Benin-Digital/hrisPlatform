<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    /**
     * Subscribe to the newsletter.
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email',
        ], [
            'email.unique' => 'Cet email est déjà inscrit à notre newsletter.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', $validator->errors()->first());
        }

        Newsletter::create([
            'email' => $request->email,
            'subscribed_at' => now(),
            'is_active' => true,
        ]);

        return back()->with('success', 'Inscription réussie ! Merci de votre intérêt.');
    }

    /**
     * Display a listing of subscribers (Super Admin).
     */
    public function index()
    {
        return Inertia::render('Newsletter/Index', [
            'subscribers' => Newsletter::latest()->get(),
        ]);
    }

    /**
     * Remove a subscriber (Super Admin).
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return back()->with('success', 'Abonné supprimé avec succès.');
    }
}
