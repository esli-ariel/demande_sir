# Audit des migrations et de la base DMA

Date : 11 novembre 2026 — tâche DMA-030.

## Avis

Les constats initiaux ci-dessous précèdent les corrections DMA-031/DMA-032 ; leur état actualisé figure à la fin du document.

La base a une structure relationnelle exploitable, mais plusieurs corrections sont nécessaires avant un usage métier en entreprise. Cet audit technique ne certifie pas une conformité à « toutes les normes » : aucun référentiel contractuel, politique de conservation ou cahier des charges de disponibilité n’a été fourni.

Inspection des migrations, modèles, contrôleurs et métadonnées de la base locale `bd_demande1`, avec contrôles SQL agrégés dans des transactions en lecture seule terminées par rollback. Aucun changement de schéma, de compte ou de donnée ; aucun seeder exécuté. Aucun nom, email, contenu de demande ou secret exporté dans ce rapport. Les constats concernent cet environnement local, pas une production.

## Ce qui est déjà solide

- 23 tables en InnoDB et utf8mb4_unicode_ci ; mode SQL strict actif, 16 clés étrangères.
- Email des utilisateurs unique ; rôles séparés, associations explicites entre comptes et structures.
- Unicité du couple demande/structure pour les décisions spécialisées et du couple structure/utilisateur pour les affectations de comptes.
- Les actions actuelles du workflow utilisent des transactions et des verrous sur la demande, avec vérification de l’autorisation sous verrou.
- Aucun doublon de numéro DMA, aucune date de fin antérieure au début, aucune référence orpheline détectée sur les propriétaires de demandes, auteurs de décisions et pièces jointes contrôlés. Ces contrôles ne couvrent pas toutes les relations polymorphes techniques.

## Mesures sur les données existantes

| Contrôle | Résultat |
| --- | --- |
| Utilisateurs / rôles / structures spécialisées | 28 / 9 / 11 |
| Demandes / décisions modernes / anciennes validations | 34 / 15 / 3 |
| Numéros DMA absents | 3 : un brouillon, un refus DTS, une validation exploitation ; obligation et moment d’attribution à définir |
| Demandes terminées ou réceptionnées incomplètes | 3 sur 3 : début, fin et compte rendu absents |
| Demandes au statut `validee_dts` sans structure affectée | 3 sur 5 ; absence d’affectation autorisée par le métier. Vérifier uniquement si ces dossiers historiques restent bloqués : le parcours actuel passe directement au contrôle avancé quand aucune structure n’est sélectionnée |
| Demandes avancées sans décision dans `demandes_validations` | 9 ; à rapprocher des anciennes validations et des données de démonstration |
| Décisions spécialisées sans structure / affectations dupliquées | 0 / 0 |
| Pièces jointes référencées mais absentes du disque public configuré | 8 sur 8 ; non recherchées dans des archives ou sur d’autres machines |

Le seeder de démonstration crée des demandes directement à différents statuts sans construire leur historique ni les travaux associés. Il peut expliquer une partie de ces écarts, sans prouver l’origine de chaque ligne. Ne pas inventer de dates, de visas ou de décisions pour compléter les dossiers.

## Corrections prioritaires

### 1. Préserver les dossiers lors du départ d’un utilisateur — priorité haute

`demandes.user_id`, les auteurs de décisions et les anciennes validations utilisent `ON DELETE CASCADE`. `UserController::destroy()` supprime physiquement le compte. Selon les relations concernées, cette opération peut donc effacer les demandes du salarié, leurs décisions et leurs métadonnées de pièces jointes. Les cascades SQL ne suppriment pas les fichiers physiques via les événements Eloquent. C’est contraire à l’objectif de conservation d’un historique métier. MySQL documente ces effets de [CASCADE et RESTRICT](https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html).

Prévoir la désactivation des comptes avec refus de connexion et révocation des sessions, puis des contraintes RESTRICT sur les références métier conservées. Un simple soft delete ne suffit pas si la connexion reste possible ou si une suppression SQL directe déclenche encore les cascades. Définir séparément la purge autorisée et l’anonymisation éventuelle.

Sources locales : `database/migrations/2025_09_29_234425_create_demandes_table.php`, `2025_10_02_081753_create_demandes_validations_table.php`, `app/Http/Controllers/UserController.php`.

### 2. Fiabiliser le numéro DMA — priorité haute

Le trigger réellement présent calcule `COUNT(*) + 1` sur les demandes de l’année, puis écrase systématiquement `NEW.numero_dma`. Il n’existe aucun index UNIQUE sur ce champ. Deux créations simultanées, une suppression ou une date de création absente peuvent conduire à réutiliser un numéro. L’absence de doublon aujourd’hui ne protège pas les prochaines insertions. Le contrôleur possède en plus une génération ULID de secours au contrôle avancé : deux mécanismes coexistent.

Choisir un seul mécanisme : soit une référence dérivée d’un identifiant unique, soit, si le format annuel séquentiel est requis, une table de compteurs par année incrémentée sous verrou transactionnel. Ajouter UNIQUE(numero_dma), préserver les références historiques et définir quand numéroter une demande. MySQL explique pourquoi un compteur partagé nécessite une [lecture verrouillée](https://docs.oracle.com/cd/E17952_01/mysql-8.0-en/innodb-locking-reads.html).

Sources : migration `2025_10_08_133657_add_trigger_before_insert_demandes.php`, `app/Http/Controllers/WorkflowController.php`.

### 3. Réconcilier les migrations et le schéma — priorité haute

Les 14 migrations du dépôt sont enregistrées comme appliquées, mais MySQL contient 16 entrées. Deux fichiers historiques manquent : `2025_10_30_101444_create_structures_specialisees_table` et `2025_10_30_102829_create_demande_structure_specialisee_table`. La table résiduelle `structure_specialisees` est vide ; la table active `structures_specialisees` contient 11 lignes.

Autre écart confirmé : `demandes_pieces_jointes.uploaded_by` est CASCADE dans MySQL alors que la migration actuelle prévoit RESTRICT. Le statut des demandes est nullable dans la base réelle, sans valeur NULL actuellement. La reproductibilité exacte n’est donc pas acquise. Une installation MySQL vierge n’a pas été exécutée pendant cet audit.

Récupérer l’historique Git, comparer un schéma neuf sur une base jetable avec la base existante, puis produire de nouvelles migrations correctives. Ne pas modifier silencieusement les anciennes migrations déjà appliquées ni supprimer des entrées de la table `migrations` pour forcer leur réexécution.

### 4. Sécuriser le compte SQL et l’installation initiale — priorité haute

La connexion actuelle utilise le compte MySQL `root`, avec des droits globaux d’administration. Créer un compte applicatif limité à la base DMA et aux opérations réellement nécessaires ; séparer le compte de migration du compte utilisé par les requêtes web. La portée des [privilèges MySQL](https://dev.mysql.com/doc/refman/9.7/en/privileges-provided.html) permet cette séparation.

`DatabaseSeeder` appelle `UserSeeder`, qui définit des mots de passe de démonstration connus, y compris pour l’administration. Séparer les données de démonstration de l’initialisation entreprise, interdire ces seeders en production et prévoir une création sécurisée du premier administrateur. Aucun contrôle des mots de passe des comptes existants n’a été effectué.

### 5. Rendre les données et les documents cohérents — priorité haute

Qualifier chaque anomalie mesurée : démonstration, ancien dossier ou donnée réelle. Compléter les dossiers réels à partir de justificatifs ; isoler explicitement les jeux de démonstration. Rechercher les huit fichiers manquants dans les sources de sauvegarde avant toute purge de leurs références.

Les nouveaux fichiers sont stockés sur le disque public et les vues génèrent des liens directs. Prévoir un stockage privé et une route autorisée par les droits sur la DMA, avec reprise contrôlée des anciens fichiers : tâche DMA-023 existante. Conserver disque, chemin, nom original, MIME vérifié, taille et éventuellement empreinte de contrôle ; prévoir une gestion des échecs entre écriture du fichier et transaction SQL.

### 6. Conserver un historique fiable et complet — priorité haute

`demandes_validations.date_validation` est de type DATE : l’heure transmise par `now()` y est perdue, même si `created_at` conserve un horodatage technique. Les relations `Demande::validations()` et `User::validations()` pointent toujours vers l’ancienne table `validations`, qui contient trois lignes. Le contrôleur de consultation utilise explicitement la table moderne ; une future réutilisation des relations pourrait fournir un historique incomplet.

Réconcilier les deux historiques sans perdre la provenance ni inventer des événements ; nommer clairement les relations. Prévoir des événements append-only avec demande, acteur, rôle au moment de l’action, ancien/nouveau statut, horodatage UTC et éventuelle structure. Séparer ces événements des décisions uniques : le service technique écrit légitimement un début puis une fin, et une future reprise après refus peut créer un nouveau cycle. Un visa textuel n’est pas, à lui seul, une signature électronique certifiée.

## Modélisation, contraintes et performances

- Le modèle est partiellement normalisé : structures spécialisées et associations sont séparées. `structure` et `unite_concernee` restent des textes libres. Pour les droits par site/unité et les statistiques fiables, prévoir des référentiels d’unités et de structures avec codes uniques et affectations explicites. Confirmer la hiérarchie réelle de l’entreprise avant de créer ces tables.
- Le nom et la structure copiés dans une demande peuvent être des instantanés historiques légitimes. Définir ce sens avant de supprimer ces champs au titre de la normalisation. Les dépendances métier ne sont pas suffisamment définies pour certifier une troisième forme normale complète.
- Après traitement des données : statut NOT NULL, UNIQUE(demande_id, structure_id) sur le pivot de sélection, contrôles de cohérence des dates et de complétude des travaux pour les états terminés. Aucune contrainte CHECK n’est présente aujourd’hui ; l’ENUM encadre néanmoins les valeurs de statut et de décision.
- L’unicité des décisions spécialisées existe déjà. L’index unique avec `structure_id` nullable ne garantit pas l’unicité des décisions des autres rôles. Définir étape et cycle avant d’ajouter une contrainte : ne pas imposer UNIQUE(demande_id, role), qui bloquerait les deux actions techniques.
- Les demandes n’ont actuellement que les index primaire et propriétaire. Étudier `(statut, created_at, id)` pour les files de travail et `(user_id, created_at, id)` pour les listes demandeur. Étudier `(user_id, role, demande_id)` pour les décisions. Vérifier leur utilité avec EXPLAIN et un volume représentatif ; 34 demandes ne permettent pas de conclure sur les performances futures.
- Une recherche `LIKE '%texte%'` ne devient pas efficace simplement en ajoutant un index B-tree au texte. Prévoir la recherche exacte/préfixe du numéro et évaluer FULLTEXT seulement si le besoin le justifie.

## Exploitation en entreprise

MySQL local est en **9.3.0**, une version Innovation. Préparer une cible LTS maintenue et tester les migrations sur cette version. Les versions Innovation ne sont supportées que jusqu’à la suivante, selon le [modèle de publication MySQL](https://dev.mysql.com/doc/refman/8.4/en/mysql-releases.html). La configuration Docker optionnelle cible actuellement 8.0 : elle ne constitue pas une procédure de migration depuis 9.3. Ne pas réutiliser directement le répertoire de données d’une autre version.

Le journal binaire est actif et `innodb_flush_log_at_trx_commit=1`, mais cela ne prouve pas l’existence d’une sauvegarde restaurable. Aucune procédure opérationnelle de sauvegarde/restauration testée n’a été trouvée dans les fichiers examinés. Prévoir sauvegarde chiffrée de la base ET des documents, copie hors machine, rétention, surveillance des échecs et restauration périodique sur environnement séparé. Définir avec l’entreprise la perte de données maximale acceptable (RPO) et le délai de reprise (RTO), puis vérifier ces objectifs par exercice. Voir la [documentation MySQL sur les sauvegardes et la restauration](https://dev.mysql.com/doc/refman/9.7/en/backup-and-recovery.html).

L’application utilise UTC ; la session MySQL indique SYSTEM. Aligner explicitement les horodatages et vérifier les conversions. `require_secure_transport` est OFF sur la machine locale ; pour une connexion réseau distante, préparer TLS, réseau privé et restriction des hôtes autorisés. La présente inspection n’est pas un audit réseau ou système complet.

## Plan proposé et critères de fin

1. **Sécuriser la conservation et l’accès** : désactivation de compte, contraintes de conservation, compte SQL dédié, seeders séparés. Vérifier qu’un départ salarié préserve demandes, décisions et documents et empêche sa connexion.
2. **Rendre le schéma reproductible et le numéro unique** : réconciliation par migrations nouvelles, mécanisme de numérotation unique, contrôle sous créations concurrentes sur MySQL isolé, comparaison installation neuve/mise à niveau.
3. **Réconcilier les dossiers et protéger les documents** : registre des anomalies sans données personnelles dans Git, qualification métier, historique unifié, fichiers privés, contrôles d’intégrité. Aucun justificatif fabriqué.
4. **Structurer le périmètre de l’entreprise** : unités/sites, affectations, visibilité, remplacements, règles de refus/reprise, archivage et conservation validés par les responsables métier.
5. **Recetter sur la cible** : parcours des neuf rôles sur MySQL, tests de concurrence, restauration réussie avec fichiers, mesures EXPLAIN et volume attendu. Les tests SQLite existants n’exercent pas le trigger MySQL historique.

Le point de départ recommandé est la conservation des dossiers lors de la désactivation d’un compte, suivie de la numérotation et de la réconciliation du schéma. Ces corrections ne sont pas appliquées dans le cadre de cet audit.

## Précision métier confirmée le 11 novembre 2026

Le passage par une structure spécialisée est facultatif. Après accord DTS, une demande sans structure sélectionnée doit aller directement au contrôle avancé. L’absence de structure ne constitue donc pas une anomalie de données et ne doit pas être interdite par une contrainte. Le statut technique actuel de cette étape sautée est `validee_structure_specialisee` ; il ne représente pas un avis spécialisé effectivement donné. Les trois dossiers historiques à `validee_dts` sans affectation restent à examiner uniquement pour vérifier leur progression, sans leur affecter artificiellement une structure.

## Corrections appliquées après l’audit — 11 novembre 2026

DMA-031 et DMA-032 sont terminées en local : désactivation/réactivation des comptes et contrôle de l’authentification, révocation des sessions/jetons, quatre clés étrangères métier RESTRICT, compte SQL applicatif restreint avec accès de migration séparé, seeders de démonstration protégés et création confidentielle d’un administrateur. Aucun compte existant n’a été désactivé ni aucun mot de passe utilisateur changé.

Le trigger utilise désormais un compteur annuel atomique et conserve le format MSAAAA/0001 ; UNIQUE sur les numéros et les sélections demande/structure, statut NOT NULL. Les références historiques sont inchangées ; les dossiers sans numéro n’ont pas été renumérotés. Les deux anciens noms de migrations ont été expliqués par les renommages Git, sans réexécution de créations de tables.

Sauvegardes locales réalisées et restauration vérifiée. Installation neuve et mise à niveau comparées sur MySQL 9.3 : colonnes, index et clés étrangères identiques hors ancienne table vide explicitement préservée. Empreintes des données initiales conservées sur les copies ; 32 insertions concurrentes distinctes, doublons interdits, passage à 10000 et non-réutilisation après suppression vérifiés. Suite Integration/Unit : 122 tests réussis, 1 359 assertions. Deux migrations enregistrées sur la base active ; volumes conservés : 28 utilisateurs, 34 demandes, 15 décisions modernes et 8 références documentaires.

Les anomalies historiques et fichiers manquants restent à qualifier/récupérer (DMA-033 et DMA-023). La cible MySQL maintenue et la sauvegarde opérationnelle restent à préparer (DMA-034). Voir EXPLOITATION_BASE_DONNEES.md pour la procédure locale et les limites ; aucune conformité globale entreprise n’est déclarée.

## Précision métier ultérieure — DMA-037

La clôture définitive appartient désormais au demandeur propriétaire, depuis Mes réceptions ou la fiche, après la fin des travaux. Le rôle réception suit les dossiers sans pouvoir de clôture. L’auteur de la nouvelle confirmation est tracé comme demandeur ; le statut final et les archives sont conservés. Les anciens dossiers clôturés et décisions réception restent inchangés, sans réattribution d’auteur ni nouvelle date. Cette correction n’ajoute aucune migration.
