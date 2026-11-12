# Dépendances — contrôle du 2026-11-11

Les fichiers `composer.lock` et `package-lock.json` déterminent les versions vérifiées. Les modifications ci-dessous sont préparées en local, sans déploiement ni migration de la base métier.

## Laravel

Laravel passe de **12.31.1 à 12.72.0**, version exacte déclarée dans `composer.json`, avec une seule copie du framework dans le verrouillage. Le projet reste sur Laravel 12.

| Avis des mainteneurs | Première version corrigée de la branche 12 |
| --- | --- |
| [GHSA-5vg9-5847-vvmq](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) | 12.60.0 |
| [GHSA-crmm-hgp2-wgrp](https://github.com/laravel/framework/security/advisories/GHSA-crmm-hgp2-wgrp) | 12.61.1 |
| [GHSA-f9x3-7vq9-cph6](https://github.com/laravel/framework/security/advisories/GHSA-f9x3-7vq9-cph6) | 12.68.0 |
| [GHSA-jh5r-qr3c-85q8](https://github.com/laravel/framework/security/advisories/GHSA-jh5r-qr3c-85q8) | 12.69.0 |

La requête OSV pour `Packagist:laravel/framework` en version `12.72.0` ne retourne aucun avis. Les dépendances PHP nécessaires ont été mises à jour. L’audit a aussi conduit à corriger PHPUnit, PsySH et Symfony YAML ; Pest reste dans sa branche 4.

## npm

Axios reste verrouillé en **1.21.1**. Les autres dépendances ont été actualisées dans leurs branches majeures existantes ; les versions minimales déclarées ont été relevées aux versions vérifiées. Aucun `audit fix --force` ni override de dépendance n’a été utilisé.

Principales versions : Vite **7.3.8**, PostCSS **8.5.32**, Concurrently **9.2.5**, Tailwind CSS **3.4.19**. Une réinstallation propre avec `npm ci --ignore-scripts --no-audit` reproduit le verrouillage et permet la compilation.

## Vérifications

- `npm audit` : **0 vulnérabilité signalée**, dépendances de développement incluses.
- `composer audit` : **0 avis de sécurité**, aucun paquet abandonné.
- `composer check-platform-reqs` : exigences satisfaites sur le PHP **8.4.12** local.
- `composer install --dry-run --no-scripts` : verrouillage installable sans opération supplémentaire.
- `npm run build` : compilation réussie ; avertissement préexistant de référence à l’image de raffinerie non résolue à la compilation.
- `php vendor/bin/pest tests/Integration tests/Unit` : **92 tests réussis, 1 086 assertions**, sur une base de tests isolée.
- Connexion, déconnexion, refus des identifiants invalides et validation des emails contenant CR/LF testés, en complément du circuit DMA, des accès, notifications et règles CORS/debug.
- Cache des routes et compilation des vues vérifiés ; cache des routes ensuite retiré pour conserver le fonctionnement de développement.

Ces résultats portent sur les avis connus au moment du contrôle. Relancer les audits avant chaque livraison. Les réglages de production restent décrits dans [SECURITE_DEPLOIEMENT.md](SECURITE_DEPLOIEMENT.md).
