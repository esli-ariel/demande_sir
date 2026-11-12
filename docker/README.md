# Configuration Docker optionnelle

Le développement actuel reste sur PHP et MySQL installés sur le Mac. Aucun conteneur n’a été lancé pour cette modification. Cette configuration prépare un futur usage Docker ; sa construction et son démarrage réels devront être vérifiés avant de l’utiliser.

## Mesures préparées

- Images externes PHP, Node, Composer et MySQL épinglées à des digests SHA-256 lus dans le registre officiel le 2026-11-11 ; manifests disponibles pour amd64 et arm64.
- Build en plusieurs étapes : dépendances et assets construits à partir de `composer.lock` et `package-lock.json`, sans copier les installations locales.
- Image finale exécutée sous UID/GID `10001:10001`, Nginx et PHP-FPM compris ; port HTTP non privilégié **8080**. Le script refuse un démarrage sous root.
- Service MySQL explicitement exécuté sous son compte `mysql:mysql`. Le compte applicatif SQL reste distinct du compte administrateur de la base.
- Capacités Linux retirées et acquisition de nouveaux privilèges interdite. Système de fichiers de l’application en lecture seule, avec volumes dédiés aux données et caches et répertoire temporaire en mémoire.
- Aucun mot de passe ni clé dans les instructions `ARG` ou `ENV` du Dockerfile ; aucune clé générée pendant le build. `APP_ENV` et `APP_DEBUG` sont des réglages publics, pas des secrets.
- `.env`, fichiers d’authentification, secrets, stockage métier et caches locaux exclus du contexte de build. Aucun dossier source de l’hôte monté dans l’application à l’exécution ; MySQL n’est pas publié sur le réseau de l’hôte.

Les réglages Nginx, PHP-FPM et Supervisor se trouvent dans ce dossier. L’ancien hook manquant a été remplacé par `entrypoint.sh`. Il charge les secrets à l’exécution, prépare les caches Laravel, puis lance la commande demandée. Il n’effectue aucune migration ni aucun seeder automatique.

## Secrets pour un éventuel usage ultérieur

Compose attend trois fichiers, qui ne sont ni créés par le build ni suivis par Git :

- `docker/secrets/app_key` : clé Laravel persistante, au format `base64:` suivi d’une clé de 32 octets encodée ;
- `docker/secrets/db_password` : mot de passe du compte SQL applicatif ;
- `docker/secrets/db_root_password` : mot de passe administrateur de la base du conteneur.

Compose monte ces fichiers en lecture seule dans `/run/secrets/`. Le conteneur applicatif reçoit uniquement sa clé et son mot de passe SQL, pas le mot de passe administrateur. Le script les exporte dans l’environnement du processus PHP au démarrage ; ils ne sont pas enregistrés dans les couches de l’image. Les caches Laravel créés à l’exécution peuvent contenir cette configuration : traiter le volume de cache comme une donnée confidentielle.

Conserver le dossier source des secrets accessible uniquement à son propriétaire sur l’hôte (mode `0700`). Avec les secrets Compose provenant de fichiers, les permissions réelles de chaque fichier doivent permettre leur lecture aux utilisateurs non-root des conteneurs : Compose utilise des bind mounts et ne remappe pas les UID/GID. Pour un usage local, des fichiers `0444` placés dans ce dossier privé permettent cette lecture sans rendre le dossier accessible aux autres comptes de l’hôte. Un orchestrateur de production peut fournir directement des secrets avec les propriétaires et modes adaptés.

La clé Laravel doit rester stable entre les reconstructions. Les mots de passe d’initialisation MySQL ne modifient pas automatiquement les comptes d’un volume déjà existant. Ne pas supprimer de volume pour changer un mot de passe ; planifier la rotation avec l’administrateur de la base.

Le futur déploiement devra aussi vérifier la terminaison HTTPS, les sauvegardes, les permissions des volumes et la procédure de migration, notamment les droits nécessaires au trigger historique. Le lancement du projet natif ne dépend pas de ces fichiers.

## Contrôles effectués sans lancer Docker

- `docker compose config --no-interpolate --no-env-resolution --no-path-resolution --quiet` : modèle Compose valide ;
- `sh -n docker/entrypoint.sh` : syntaxe du script valide ;
- tests de configuration, refus de root/secrets absents, chargement de secrets synthétiques sans affichage de leur contenu ; les commandes PHP sont simulées dans ces tests, aucune base n’est démarrée ;
- suite applicative : **113 tests réussis, 1 274 assertions**.

Le build des images et le fonctionnement réel des conteneurs ne sont pas encore validés. L’épinglage rend la référence immuable, mais ne remplace pas les mises à jour régulières ni un audit de l’image construite.

Références : [bonnes pratiques Docker](https://docs.docker.com/build/building/best-practices/), [secrets de build](https://docs.docker.com/reference/build-checks/secrets-used-in-arg-or-env/), [secrets Compose](https://docs.docker.com/compose/how-tos/use-secrets/).
