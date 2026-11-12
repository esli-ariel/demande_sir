<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin=\Tests\Support\resetWorkspaceDatabase();
});

it('serves both entry pages with the original refinery watermark and working access links', function () {
    $this->get(route('login'))->assertOk()->assertSee('images/Raffinerie-SIR.jpeg')
        ->assertSee('Mot de passe oublié ?')->assertSee(route('register'));
    $this->get(route('register'))->assertOk()->assertSee('images/Raffinerie-SIR.jpeg')
        ->assertSee('Votre compte demandeur')->assertSee('name="role" value="demandeur"',false)
        ->assertDontSee('<select',false)->assertSee(route('login'));
    $this->get(route('password.request'))->assertOk();
});

it('preserves the entered email and renders an accessible login error without echoing passwords', function () {
    $this->from(route('login'))->post(route('login'),['email'=>$this->admin->email,'password'=>'wrong-secret-not-to-render'])->assertSessionHasErrors('email');
    $this->get(route('login'))->assertOk()->assertSee('role="alert"',false)->assertSee('aria-invalid="true"',false)
        ->assertSee($this->admin->email)->assertDontSee('wrong-secret-not-to-render');
    $this->assertGuest();
});

it('renders malformed registration values safely after validation without a server error', function () {
    $this->from(route('register'))->post(route('register'),[
        'name'=>['invalid'],'prenom'=>'Prénom conservé','email'=>['invalid'],
        'password'=>'valid-test-password','password_confirmation'=>'different-password','role'=>'demandeur',
    ])->assertSessionHasErrors(['name','email','password']);
    $this->get(route('register'))->assertOk()->assertSee('Prénom conservé')->assertSee('role="alert"',false)
        ->assertSee('aria-invalid="true"',false)->assertDontSee('valid-test-password');
    expect(User::count())->toBe(1);
});

it('registers a requester from the simplified form and keeps privileged roles forbidden', function () {
    $payload=['name'=>'Nouveau','prenom'=>'Compte','email'=>'new-auth-page@example.invalid','password'=>'registration-test-password','password_confirmation'=>'registration-test-password','role'=>'demandeur'];
    $this->post(route('register'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('dashboard.demandeur'));
    $user=User::where('email',$payload['email'])->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->getRoleNames()->all())->toBe(['demandeur'])->and(Hash::check($payload['password'],$user->password))->toBeTrue();
    $this->post(route('logout'));
    $payload['email']='forged-auth-page@example.invalid';$payload['role']='admin';
    $this->post(route('register'),$payload)->assertSessionHasErrors('role');
    expect(User::where('email',$payload['email'])->exists())->toBeFalse();
});
