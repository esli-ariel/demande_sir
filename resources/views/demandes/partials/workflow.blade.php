@php($workflowSteps = \App\Support\DmaWorkflow::steps($demande->statut, $demande->structuresSpecialisees->isNotEmpty()))
<section class="admin-panel dma-workflow" aria-labelledby="dma-workflow-heading">
    <div class="admin-panel-heading"><h2 id="dma-workflow-heading">Avancement de la demande</h2><span class="dma-workflow-status">{{ \App\Support\DmaStatus::label($demande->statut) }}</span></div>
    <ol class="dma-workflow-steps" aria-label="Étapes du circuit de la DMA">
        @foreach($workflowSteps as $step)
        <li class="dma-workflow-step is-{{ $step['state'] }}" @if(in_array($step['state'], ['current','refused'])) aria-current="step" @endif>
            <span class="dma-workflow-marker" aria-hidden="true">{{ match($step['state']) {'complete'=>'✓', 'refused'=>'×', 'skipped'=>'–', default=>$loop->iteration} }}</span>
            <div class="dma-workflow-content"><h3>{{ $step['label'] }}</h3><span>{{ $step['detail'] }}</span></div>
        </li>
        @endforeach
    </ol>
</section>
