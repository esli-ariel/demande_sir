<?php

use Illuminate\Support\Facades\Route;

uses(Tests\TestCase::class);

beforeEach(function () {
    Route::get('/security-cors-check', fn () => response()->json(['ok'=>true]));
});

it('does not grant cross origin reads with an empty allowlist', function () {
    config(['cors.allowed_origins'=>[]]);
    $this->withHeader('Origin','https://untrusted.example')->get('/security-cors-check')
        ->assertOk()->assertHeaderMissing('Access-Control-Allow-Origin')->assertHeaderMissing('Access-Control-Allow-Credentials');
});

it('allows only the exact trusted origin and refuses sibling origins and ports', function () {
    config(['cors.allowed_origins'=>['https://dma.example.com']]);
    $this->withHeader('Origin','https://dma.example.com')->get('/security-cors-check')->assertOk()->assertHeader('Access-Control-Allow-Origin','https://dma.example.com')->assertHeaderMissing('Access-Control-Allow-Credentials');
    foreach (['https://evil.example','https://dma.example.com.evil.example','http://dma.example.com','https://dma.example.com:444','https://sub.dma.example.com','null'] as $origin) {
        $response=$this->withHeader('Origin',$origin)->get('/security-cors-check')->assertOk();
        expect($response->headers->get('Access-Control-Allow-Origin'))->not->toBe($origin)->not->toBe('*');
    }
});

it('handles preflights without wildcard methods headers or credentials', function () {
    config(['cors.allowed_origins'=>['https://dma.example.com']]);
    $this->options('/security-cors-check',[],['Origin'=>'https://dma.example.com','Access-Control-Request-Method'=>'POST','Access-Control-Request-Headers'=>'content-type,x-csrf-token'])
        ->assertNoContent()->assertHeader('Access-Control-Allow-Origin','https://dma.example.com')->assertHeaderMissing('Access-Control-Allow-Credentials')
        ->assertHeader('Access-Control-Allow-Methods','GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS');
    $response=$this->options('/security-cors-check',[],['Origin'=>'https://untrusted.example','Access-Control-Request-Method'=>'POST'])->assertNoContent();
    expect($response->headers->get('Access-Control-Allow-Origin'))->not->toBe('https://untrusted.example')->not->toBe('*');
});

it('returns generic errors in production without exposing exception details', function () {
    config(['app.debug'=>false,'app.env'=>'production']);
    Route::get('/security-error-check', fn () => throw new RuntimeException('PRIVATE_INTERNAL_ERROR_SENTINEL'));
    $this->getJson('/security-error-check')->assertStatus(500)->assertJson(['message'=>'Server Error'])->assertDontSee('PRIVATE_INTERNAL_ERROR_SENTINEL');
});
