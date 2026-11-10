<?php

use App\Support\DmaStatus;

it('keeps completed technical work out of refused and received requests', function () {
    $summary = DmaStatus::summary(['terminee_agent' => 3, 'cloturee' => 2, 'refusee_dts' => 1, 'cloturee_receptionnee' => 4]);

    expect($summary['reception'])->toBe(5)
        ->and($summary['refusees'])->toBe(1)
        ->and($summary['cloturees'])->toBe(4);
});

it('accounts for drafts and unknown statuses without losing requests', function () {
    $summary = DmaStatus::summary(['brouillon' => 5, 'soumise' => 2, 'futur_statut' => 3]);

    expect(array_sum($summary))->toBe(10)
        ->and($summary['brouillons'])->toBe(5)
        ->and($summary['autres'])->toBe(3);
});

it('provides an empty summary with no requests', function () {
    expect(array_sum(DmaStatus::summary([])))->toBe(0);
});

it('provides readable labels for the next step and unknown statuses', function () {
    expect(DmaStatus::label('validee_dts'))->toBe('Avis des structures attendu')
        ->and(DmaStatus::label('statut_inconnu'))->toContain('Statut à vérifier');
});
