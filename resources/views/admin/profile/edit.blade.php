<x-app-layout>
<div class="admin-dashboard admin-profile">
    <header class="admin-page-heading"><h1>Mon profil</h1><span class="admin-badge">{{ auth()->user()->hasRole('admin') ? 'Administrateur' : (\App\Support\StaffWorkspace::role(auth()->user()) ? \App\Support\StaffWorkspace::ROLES[\App\Support\StaffWorkspace::role(auth()->user())]['label'] : 'Demandeur') }}</span></header>
    <div class="admin-profile-grid">
        <section class="admin-panel admin-profile-section admin-profile-details">@include('profile.partials.update-profile-information-form')</section>
        <section class="admin-panel admin-profile-section admin-profile-details">@include('profile.partials.update-password-form')</section>
    </div>
    <section class="admin-panel admin-profile-section admin-danger-zone admin-spaced">@include('profile.partials.delete-user-form')</section>
</div>
</x-app-layout>
