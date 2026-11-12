# Préparation du déploiement — debug et CORS

L’application n’est pas encore en production. La configuration locale conserve `APP_ENV=local` et `APP_DEBUG=true` pour le développement.

## Mode debug

Sur le futur serveur :

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.example
CORS_ALLOWED_ORIGINS=
```

Remplacer l’adresse d’exemple avant déploiement. Le code n’autorise le debug que dans les environnements `local` et `testing` ; il reste désactivé en production même si `APP_DEBUG=true` y est renseigné par erreur. Le manifeste Render et les valeurs par défaut du Dockerfile sont alignés sur la production ; aucun hébergement ni conteneur n’est lancé par ces changements.

## CORS

Le front Blade et le serveur Laravel utilisent actuellement la même origine. Ils n’ont donc besoin d’aucune autorisation CORS externe : laisser `CORS_ALLOWED_ORIGINS` vide.

Si un front est plus tard hébergé séparément, fournir uniquement ses origines exactes, séparées par des virgules, par exemple :

```dotenv
CORS_ALLOWED_ORIGINS=https://front.votre-domaine.example
```

Une origine comprend le protocole, le nom d’hôte et, si nécessaire, le port ; aucun chemin, identifiant, joker `*` ou motif de sous-domaines n’est accepté. En production, utiliser des adresses HTTPS. Les méthodes et en-têtes autorisés sont limités explicitement. Les requêtes CORS avec cookies ne sont pas activées ; l’authentification actuelle sur la même origine continue de fonctionner.

CORS contrôle ce que les navigateurs peuvent lire entre origines ; il ne remplace ni l’authentification, ni les permissions, ni la protection CSRF.

## Lors du déploiement effectif

1. Renseigner les variables de production et les secrets chez l’hébergeur, dont la clé Laravel et les identifiants MySQL. Le mot de passe codé en dur dans le manifeste Render a été retiré ; s’il a été utilisé réellement, le remplacer chez le fournisseur concerné.
2. Après configuration de l’environnement, régénérer le cache :

```sh
php artisan config:cache
```

3. Vérifier sur l’adresse HTTPS réelle que les erreurs restent génériques et que les réponses ne permettent aucune lecture CORS depuis une origine externe non autorisée. Avec une liste vide, aucun en-tête `Access-Control-Allow-Origin` n’est émis ; avec une origine unique, le framework peut renvoyer cette origine constante, même à un client tiers : son navigateur bloque la lecture car les origines ne correspondent pas.

Les tests locaux vérifient les règles du code. Ils ne certifient pas encore un serveur de production ni la validité complète des anciens fichiers d’hébergement.
