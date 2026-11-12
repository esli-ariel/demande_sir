<?php

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->user = \Tests\Support\resetWorkspaceDatabase();
});

it('preserves password login session authentication and logout after the framework update', function () {
    $this->post(route('login'), ['email'=>$this->user->email,'password'=>'test-password'])->assertSessionHasNoErrors()->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($this->user);
    $this->get(route('dashboard'))->assertOk();
    $this->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('rejects invalid or non string login credentials without authenticating', function () {
    foreach (['incorrect-password', ['unexpected'=>'value']] as $password) {
        $this->post(route('login'), ['email'=>$this->user->email,'password'=>$password])->assertSessionHasErrors();
        $this->assertGuest();
    }
});

it('rejects carriage returns and line feeds in user supplied email addresses', function () {
    foreach (["user@example.com\r\nBcc:someone@example.com", "user@example.com\n"] as $email) {
        expect(validator(['email'=>$email], ['email'=>'required|email'])->fails())->toBeTrue();
    }
});
