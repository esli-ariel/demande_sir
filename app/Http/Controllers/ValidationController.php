<?php

namespace App\Http\Controllers;

class ValidationController extends Controller
{
    public function index()
    {
        abort(410, 'Cet ancien circuit a été retiré. Utilisez le circuit DMA actuel.');
    }

    public function valider()
    {
        abort(410, 'Cet ancien circuit a été retiré. Utilisez le circuit DMA actuel.');
    }

    public function rejeter()
    {
        abort(410, 'Cet ancien circuit a été retiré. Utilisez le circuit DMA actuel.');
    }
}
