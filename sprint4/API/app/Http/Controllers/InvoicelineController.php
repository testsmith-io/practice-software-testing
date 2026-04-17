<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Models\Invoiceline;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InvoicelineController extends Controller
{

    public function index()
    {
        return $this->preferredFormat(Invoiceline::paginate());
    }

    public function store(Request $request)
    {
        return $this->preferredFormat(Invoiceline::create($request->all()), ResponseAlias::HTTP_CREATED);
    }

    public function show($id)
    {
        return $this->preferredFormat(Invoiceline::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        Invoiceline::findOrFail($id)->update($request->all());
        return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            Invoiceline::findOrFail($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this invoice is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
