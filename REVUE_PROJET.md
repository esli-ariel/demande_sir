# Revue du projet DMA — 12 novembre 2026

Version : **0.1.0-dev**. Le projet fonctionne en local. La recette entreprise et la préparation de la production restent à terminer.

## Vérifications du jour

| Vérification | Résultat |
| --- | --- |
| Serveur Laravel | Relancé sur http://127.0.0.1:8000 |
| Connexion / inscription | HTTP 200 sur les deux pages |
| Archives sans authentification | Redirection HTTP 302 vers la connexion |
| MySQL natif | Connecté, version locale 9.3.0 ; compte applicatif distinct de root |
| Migrations du dépôt | Aucune en attente ; aucun seeder ni migration exécuté pendant cette revue |
| Données principales | 28 utilisateurs, 9 rôles, 34 demandes, 11 structures spécialisées |
| Tests relancés | **141 réussis, 1 614 assertions**, suite Integration/Unit sur base SQLite isolée |
| Compilation des ressources | Dernier build réussi après le rétablissement du fond vert/orange ; pas de modification du front pendant cette revue |

L’inspection MySQL a été effectuée en lecture seule, avec rollback, et limitée aux métadonnées et agrégats. Les tests isolés ne remplacent pas la recette métier sur la future infrastructure.

## Fonctionnel et vérifié en local

| Partie | Résultat disponible |
| --- | --- |
| Charte et interface | Orange #FF8C00 / vert #2E8B57, modes jour/nuit et mobile ; photo de raffinerie et coloration initiale conservées sur connexion/inscription |
| Comptes et accès | Connexion, inscription demandeur, administration des rôles, désactivation/réactivation avec conservation des dossiers et révocation des accès |
| Espaces des rôles | Administration, demandeur, exploitation, DTS, structures spécialisées, contrôle avancé, service technique, chef de structure et réception harmonisés |
| Demandes | Création, modification des brouillons, envoi, recherche, filtres, pagination et consultation détaillée |
| Workflow | Accords/refus et transitions contrôlés, avis par structure explicitement affectée, verrouillage des actions concurrentes ; étape spécialisée facultative |
| Travaux | Début et fin distincts, dates et compte rendu, contrôle final facultatif |
| Clôture finale | Le demandeur propriétaire confirme la réception avec visa textuel et consentement ; décision/date/statut enregistrés ensemble, répétition refusée. Réception en suivi sans pouvoir de clôture |
| Suivi | Diagramme d’avancement, décisions et notifications dans l’application ; lecture, compteurs et accès aux dossiers protégés |
| Archives | Dossiers définitivement clôturés, consultation en lecture seule, recherche et filtres par unité/année/période, droits de consultation conservés |
| Base de données | Numérotation annuelle atomique et unique, contraintes de sélection/statut, références métier conservées lors des départs, date des nouvelles clôtures et compte SQL applicatif restreint |
| Sécurité préparée | Validation des entrées et requêtes liées, debug/CORS encadrés, dépendances mises à jour, seeders de démonstration protégés. Les audits de dépendances précédents ne constituent pas un nouveau scan effectué aujourd’hui |
| Documentation | Journal, progression, tâches, charte, rapports d’audit et procédure d’exploitation locale |

Le téléversement de pièces jointes fonctionne, mais leur stockage et leur téléchargement privés restent à réaliser. Les notifications disponibles sont celles de l’application ; aucun canal e-mail ou SMS supplémentaire n’est présenté comme livré.

## Travaux restants

| Priorité | Chantier | Ce qu’il manque | Référence |
| --- | --- | --- | --- |
| Prochaine étape demandée | Visa | Le visa est actuellement un texte saisi, associé à un utilisateur et une décision. Définir la forme souhaitée et les règles de confirmation/traçabilité, puis l’implémenter | DMA-039 |
| Haute avant livraison | Pièces jointes | Stockage privé, téléchargement contrôlé par les droits sur la DMA, reprise des fichiers existants et recherche des huit fichiers manquants | DMA-023 |
| Haute avant livraison | Sauvegarde/restauration | Pipeline base + documents, copie sécurisée hors machine, rétention, contrôle des échecs et restauration isolée ; destination et objectifs de reprise à définir | DMA-036 / DMA-034 |
| Haute | Données historiques | Distinguer démonstration et dossiers réels, rapprocher les anciennes validations, qualifier les états incomplets sans inventer dates, visas ou documents | DMA-033 |
| Haute | Règles métier | Confirmer la visibilité par unité/structure, les règles de reprise après refus et les critères de fin du projet | DMA-001 |
| Haute avant livraison | Recette entreprise | Faire valider les parcours des neuf rôles avec les utilisateurs, les indicateurs/notifications et un scénario complet sur l’environnement cible | DMA-007 / DMA-008 |
| Haute | Point de version | Relire et commiter les changements locaux, puis créer le point de contrôle GitHub. Vérifier l’exclusion des secrets, sauvegardes et données de test | DMA-041 |
| Haute avant production | Exploitation et déploiement | Choisir l’hébergement et une cible MySQL maintenue, configurer domaine/HTTPS/secrets, droits et surveillance, puis tester la mise à niveau et la reprise | DMA-034 / DMA-008 |

Le Docker optionnel a été préparé et vérifié statiquement ; son build et son exécution n’ont pas été validés. Le fonctionnement local actuel utilise PHP et MySQL natifs.

## Données à reprendre : résultats actuels

- **8 références documentaires sur 8** sans fichier présent sur le disque public configuré. Des archives ou d’autres machines n’ont pas été recherchées pendant cette revue.
- **3 dossiers terminés ou réceptionnés** sans dates de travaux et/ou compte rendu attendus.
- **1 dossier historiquement clôturé** sans date de clôture connue : il reste consultable avec cette mention, sans date fabriquée.
- **9 dossiers avancés** sans décision dans l’historique moderne, à rapprocher des **3 validations anciennes** et des jeux de démonstration.
- **3 dossiers historiques au statut validee_dts** sans structure sélectionnée : aucune structure ne doit être ajoutée artificiellement, car cette étape est facultative. Vérifier seulement qu’ils peuvent progresser selon la règle actuelle.

Ces constats ne prouvent pas une corruption de tous ces dossiers : le seeder de démonstration crée certains états directement. Une qualification métier est nécessaire avant correction ou purge.

## État Git et livraison

Le point de consolidation du code est **43ae8dd**, publié sur `main` de `esli-ariel/demande_sir` le 12 novembre 2026 à la demande explicite du porteur du projet. Il regroupe 105 fichiers de code, tests et documentation, notamment sécurité, archives, clôture par le demandeur et pages d’accès. Secrets de configuration, sauvegardes et données locales exclus après contrôle des contenus indexés.

La revue préalable avait constaté un HEAD local `334ba56` et les changements non commitées. Ce constat est désormais résolu : fetch avant publication sans divergence, commit et push normal confirmés. Les fichiers de suivi sont actualisés après la publication du point de code.

La version reste **0.1.0-dev** ; ce push n’est pas une livraison en production. Aucun pourcentage global ni date de livraison n’est annoncé : le périmètre final, le visa et les critères de recette entreprise restent à confirmer.

## Ordre proposé

1. Point de version du travail actuel : **réalisé**, commit `43ae8dd` publié sur `main`.
2. Traiter le visa, prochaine fonctionnalité demandée.
3. Protéger les pièces jointes et qualifier/récupérer les documents manquants.
4. Mettre en place le pipeline de sauvegarde et restauration, en incluant base et fichiers.
5. Finaliser les règles d’accès/reprise et la recette métier, puis préparer la livraison.
