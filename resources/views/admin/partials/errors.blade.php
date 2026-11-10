@if($errors->any())
<div class="admin-errors" role="alert"><strong>Vérifiez les informations saisies.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
