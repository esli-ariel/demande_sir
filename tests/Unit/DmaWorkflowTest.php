<?php

use App\Support\DmaWorkflow;

it('places each live status at the correct workflow step', function (string $status, int $position) {
    $steps = DmaWorkflow::steps($status, true);
    expect($steps[$position]['state'])->toBe('current');
    foreach ($steps as $index => $step) {
        expect($step['state'])->toBe($index < $position ? 'complete' : ($index === $position ? 'current' : 'upcoming'));
    }
})->with(['brouillon'=>['brouillon',0], 'soumise'=>['soumise',1], 'exploitation'=>['validee_exploitation',2], 'dts'=>['validee_dts',3], 'structures'=>['validee_structure_specialisee',4], 'avant travaux'=>['validee_controle_avancee',5], 'travaux'=>['en_cours_traitement',5], 'après travaux'=>['terminee_agent',6], 'contrôle final'=>['cloturee',6]]);

it('stops the diagram at the role that refused', function (string $status, int $position) {
    $steps = DmaWorkflow::steps($status, true);
    foreach ($steps as $index => $step) {
        expect($step['state'])->toBe($index < $position ? 'complete' : ($index === $position ? 'refused' : 'blocked'));
    }
})->with([['refusee_exploitation',1], ['refusee_dts',2], ['refusee_structure_specialisee',3], ['rejetee_structure_specialisee',3], ['refusee_controle_avancee',4]]);

it('skips unrequested structures and completes the circuit at final reception', function () {
    expect(DmaWorkflow::steps('validee_structure_specialisee',false)[3]['state'])->toBe('skipped');
    expect(array_column(DmaWorkflow::steps('cloturee_receptionnee',true),'state'))->toBe(array_fill(0,7,'complete'));
    expect(DmaWorkflow::steps('validee_dts',false)[3]['state'])->toBe('current');
});

it('does not invent progress for unknown or unassigned refusal statuses', function () {
    foreach (['futur_statut','refusee'] as $status) {
        expect(array_unique(array_column(DmaWorkflow::steps($status,false),'state')))->toBe(['unknown']);
    }
});
