<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Proposal;

class ProposalPDF
{
    public function generate(Proposal $proposal)
    {
        $company = auth()->user()->company;
        
        $pdf = PDF::loadView('pdfs.proposal', [
            'proposal' => $proposal,
            'company' => $company
        ]);
        
        $pdf->setPaper('a4');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true
        ]);
        
        return $pdf;
    }
}