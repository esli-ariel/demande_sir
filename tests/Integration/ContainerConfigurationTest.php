<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

uses(Tests\TestCase::class);

it('pins external images and configures the application and database without root', function () {
    $dockerfile=file_get_contents(base_path('Dockerfile'));
    $aliases=[];
    foreach (preg_split('/\R/',$dockerfile) as $line) {
        if (! preg_match('/^FROM\s+(\S+)(?:\s+AS\s+(\S+))?/i',$line,$match)) continue;
        $image=$match[1];
        if (! in_array($image,$aliases,true)) expect($image)->toMatch('/@sha256:[a-f0-9]{64}$/');
        if (isset($match[2])) $aliases[]=$match[2];
    }
    expect($dockerfile)->toContain('USER 10001:10001')->not->toContain('key:generate')->not->toContain('migrate')->not->toContain('seed');
    $config=Yaml::parseFile(base_path('docker-compose.yml'));
    expect($config['services']['app']['user'])->toBe('10001:10001')
        ->and($config['services']['app']['read_only'])->toBeTrue()
        ->and($config['services']['app']['cap_drop'])->toBe(['ALL'])
        ->and($config['services']['mysql']['user'])->toBe('mysql:mysql')
        ->and($config['services']['mysql']['image'])->toMatch('/@sha256:[a-f0-9]{64}$/');
    foreach ($config['services'] as $service) {
        expect($service['environment'])->not->toHaveKey('DB_PASSWORD')->not->toHaveKey('APP_KEY')->not->toHaveKey('MYSQL_ROOT_PASSWORD')->not->toHaveKey('MYSQL_PASSWORD');
    }
    expect($config['services']['mysql'])->not->toHaveKey('ports');
    expect(file_get_contents(base_path('.dockerignore')))->toContain('docker/secrets','storage','bootstrap/cache')->not->toContain('composer.lock');
});

it('fails closed without runtime secrets and never executes application commands as root', function () {
    $missing=new Process(['/bin/sh',base_path('docker/entrypoint.sh'),'true'],base_path(),[
        'APP_KEY'=>false,'APP_KEY_FILE'=>false,'DB_PASSWORD'=>false,'DB_PASSWORD_FILE'=>false,
    ]);
    $missing->run();expect($missing->isSuccessful())->toBeFalse();
    $directory=sys_get_temp_dir().'/dma-entrypoint-'.bin2hex(random_bytes(8));mkdir($directory,0700);
    try {
        file_put_contents($directory.'/id',"#!/bin/sh\nprintf '0\\n'\n");chmod($directory.'/id',0700);
        $root=new Process(['/bin/sh',base_path('docker/entrypoint.sh'),'true'],base_path(),['PATH'=>$directory.':'.getenv('PATH')]);
        $root->run();expect($root->isSuccessful())->toBeFalse()->and($root->getErrorOutput())->toContain('sans root');
    } finally { unlink($directory.'/id');rmdir($directory); }
});

it('loads runtime secret files without logging values and runs the supplied command', function () {
    $directory=sys_get_temp_dir().'/dma-entrypoint-'.bin2hex(random_bytes(8));mkdir($directory,0700);
    try {
        file_put_contents($directory.'/id',"#!/bin/sh\nprintf '10001\\n'\n");chmod($directory.'/id',0700);
        file_put_contents($directory.'/php',"#!/bin/sh\nexit 0\n");chmod($directory.'/php',0700);
        file_put_contents($directory.'/app_key','test-key-marker');file_put_contents($directory.'/db_password','test-password-marker');
        file_put_contents($directory.'/verify',"#!/bin/sh\n[ \"\$APP_KEY\" = 'test-key-marker' ] && [ \"\$DB_PASSWORD\" = 'test-password-marker' ] && printf 'runtime-ok\\n'\n");chmod($directory.'/verify',0700);
        $process=new Process(['/bin/sh',base_path('docker/entrypoint.sh'),$directory.'/verify'],base_path(),[
            'PATH'=>$directory.':'.getenv('PATH'),'APP_KEY'=>false,'DB_PASSWORD'=>false,
            'APP_KEY_FILE'=>$directory.'/app_key','DB_PASSWORD_FILE'=>$directory.'/db_password',
        ]);
        $process->run();expect($process->isSuccessful())->toBeTrue()->and($process->getOutput())->toBe("runtime-ok\n")
            ->and($process->getErrorOutput())->not->toContain('test-key-marker','test-password-marker');
        $conflict=new Process(['/bin/sh',base_path('docker/entrypoint.sh'),'true'],base_path(),[
            'PATH'=>$directory.':'.getenv('PATH'),'APP_KEY'=>'conflict-marker','APP_KEY_FILE'=>$directory.'/app_key',
        ]);
        $conflict->run();expect($conflict->isSuccessful())->toBeFalse()->and($conflict->getErrorOutput())->not->toContain('conflict-marker','test-key-marker');
    } finally { foreach (glob($directory.'/*') as $file) unlink($file);rmdir($directory); }
});
