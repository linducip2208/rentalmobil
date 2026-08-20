<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::active()->sorted()->get()->groupBy('category');

        return view('faq.index', compact('faqs'));
    }
}
