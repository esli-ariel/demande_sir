<?php

use App\Support\CorsOrigins;

it('denies cross origin access by default and normalizes exact origins', function () {
    expect(CorsOrigins::parse(''))->toBe([])
        ->and(CorsOrigins::parse(' https://DMA.example.com/ ,http://localhost:5173,https://dma.example.com '))->toBe(['https://dma.example.com','http://localhost:5173']);
});

it('rejects wildcard and malformed origins instead of silently broadening access', function (string $origin) {
    expect(fn () => CorsOrigins::parse($origin))->toThrow(InvalidArgumentException::class);
})->with(['*','https://*.example.com','https://dma.example.com/path','https://dma.example.com?test=1','https://dma.example.com#fragment','https://user:password@example.com','example.com','null','file:///tmp/example','https://trusted.example,*']);

it('permits debug only for local development and isolated tests', function (string $environment, bool $expected) {
    $oldEnv=$_ENV['APP_ENV'] ?? null;$oldDebug=$_ENV['APP_DEBUG'] ?? null;
    $serverEnv=$_SERVER['APP_ENV'] ?? null;$serverDebug=$_SERVER['APP_DEBUG'] ?? null;
    try {
        $_ENV['APP_ENV']=$_SERVER['APP_ENV']=$environment;
        $_ENV['APP_DEBUG']=$_SERVER['APP_DEBUG']='true';
        $config=require __DIR__.'/../../config/app.php';
        expect($config['debug'])->toBe($expected);
    } finally {
        foreach (['APP_ENV'=>[$oldEnv,$serverEnv], 'APP_DEBUG'=>[$oldDebug,$serverDebug]] as $key=>[$env,$server]) {
            if ($env===null) unset($_ENV[$key]); else $_ENV[$key]=$env;
            if ($server===null) unset($_SERVER[$key]); else $_SERVER[$key]=$server;
        }
    }
})->with([['production',false],['staging',false],['local',true],['testing',true]]);
