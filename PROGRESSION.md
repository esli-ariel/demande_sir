# Progression — Gestion des DMA

Dernière mise à jour : **2026-11-12**

## Revue actuelle

Contrôle du 12 novembre 2026 : projet relancé, MySQL connecté, aucune migration en attente ; **141 tests réussis, 1 614 assertions**. Les corrections sont regroupées dans le point de consolidation **43ae8dd**, poussé sur `main` le 12 novembre 2026. Le visa, les fichiers privés, le pipeline de sauvegarde/restauration et la recette métier restent à terminer. Voir [REVUE_PROJET.md](REVUE_PROJET.md) pour le détail et les priorités.

## Où en est-on ?

- Version de travail : **0.1.0-dev**, définie dans [VERSION](VERSION).
- Phase : espaces de tous les rôles harmonisés ; recette fonctionnelle avant livraison à poursuivre.
- Base de reprise : commit `b3971f5`, branche `main`.
- Dépôt récupéré en local et ouvert dans VS Code.
- Environnement local démarré avec PHP et le serveur MySQL déjà installé, sans Docker. Base existante `bd_demande1` connectée : 28 utilisateurs et 34 demandes au contrôle initial.
- Page de connexion et fichiers CSS/JS vérifiés (HTTP 200). Toutes les migrations du dépôt sont déjà appliquées. La commande `migrate --seed` a ensuite été exécutée avec succès : `RoleSeeder` et `UserSeeder`, 28 utilisateurs, 34 demandes et 9 rôles après exécution. Les espaces de chaque rôle ont ensuite été vérifiés.
- Dashboard administrateur refondu (DMA-010) : recherche, filtres, pagination, consultation et affichage mobile vérifiés. Après revue : accents orange SIR ajoutés et textes explicatifs secondaires retirés.
- Modes jour/nuit disponibles dans la navbar admin (DMA-011), préférence mémorisée et rendu mobile vérifié.
- Autres pages admin harmonisées (DMA-012) : utilisateurs, demandes, fiche DMA, profil et notifications. Douze tests réussis (62 assertions) dans une base isolée.
- Charte de référence : vert `#2E8B57`, orange `#FF8C00`. Voir [CHARTE_GRAPHIQUE.md](CHARTE_GRAPHIQUE.md).
- Ajustement visuel : mode nuit noir brillant et badges de demande plus vifs ; contrastes vérifiés et charte mise à jour.
- Cartes admin et limites de sections retravaillées : contours renforcés, bandeaux de titre, repères colorés et espacement cohérent sur ordinateur/mobile.
- Point de contrôle admin poussé sur GitHub : `afd10d3` sur `main`.
- Espace demandeur refondu (DMA-013), avec 20 tests réussis (126 assertions). La refonte est regroupée dans le point de contrôle demandeur sur `main`.
- Les 11 structures spécialisées existantes sont désormais accessibles dans le formulaire ; incohérence de nom de table corrigée.
- Rôle exploitation livré : DMA-014, 24 tests réussis (159 assertions) ; point de contrôle dédié sur `main`.
- Rôle DTS livré : DMA-015, 27 tests réussis (178 assertions) ; point de contrôle dédié sur `main`.
- Rôle structures spécialisées livré : DMA-016, 30 tests réussis (206 assertions) ; point de contrôle dédié sur `main`.
- Rôle contrôle avancé livré : DMA-017, 33 tests réussis (229 assertions) ; point de contrôle dédié sur `main`.
- Rôle service technique livré : DMA-018, 34 tests réussis (253 assertions) ; point de contrôle dédié sur `main`.
- Rôle chef de structure livré : DMA-019, 35 tests réussis (274 assertions) ; point de contrôle dédié sur `main`.
- Rôle réception livré : DMA-020, 37 tests réussis (320 assertions) ; point de contrôle dédié sur `main`.
- Fiche de consultation : libellés et valeurs différenciés, séparations ajoutées ; compilation et rendu jour/nuit vérifiés, publiée dans `fe657f3`.
- Diagramme d’avancement ajouté à chaque fiche DMA (DMA-021), adapté aux thèmes et au mobile ; 53 tests réussis, 432 assertions. Publié dans `fe657f3`.
- Notifications du circuit raccordées (DMA-022) : prochains intervenants, suivi du demandeur, structures ciblées, lecture et compteur automatique ; 60 tests réussis, 498 assertions. Publié dans `fe657f3`.
- Nettoyage des routes et contrôles des accès réalisé (DMA-003) : 69 tests réussis et 1 013 assertions, noms uniques, cache des routes vérifié.
- Sécurité debug/CORS préparée (DMA-024) : aucune production encore déployée ; debug production désactivé et origines CORS externes interdites par défaut, tests de sécurité et régression réussis. Changements locaux.
- Axios corrigé (DMA-025) : version unique 1.21.1, audit Axios/OSV sans avis et compilation réussie ; disponible en local. Les 16 alertes npm restantes ont ensuite été corrigées dans DMA-026.
- Dépendances sécurisées (DMA-026/DMA-027) : Laravel 12.72.0, Axios 1.21.1 et mises à jour compatibles ; audits npm/Composer à zéro, OSV sans avis Laravel, 92 tests réussis et 1 086 assertions. Disponible en local.
- Entrées HTTP et requêtes contrôlées (DMA-028) : schéma DMA partagé, types/limites/authentification renforcés et SQL paramétré vérifié ; 110 tests réussis, 1 241 assertions et compilation validée. Changements locaux.
- Docker préparé pour un usage ultérieur (DMA-029) : non-root, digests vérifiés et secrets hors build ; Compose/script contrôlés et 113 tests réussis, 1 274 assertions. Aucun conteneur lancé ni image construite ; validation d’exécution reportée à un futur usage Docker.
- Audit de la base terminé (DMA-030) : écarts schéma/migrations et données historiques identifiés en lecture seule ; voir AUDIT_BASE_DONNEES.md. DMA-031/DMA-032 corrigées ensuite ; reprise des données et exploitation DMA-033/DMA-034 encore à faire.
- Conservation et schéma corrigés en local (DMA-031/DMA-032) : désactivation/réactivation, accès SQL restreint, numérotation annuelle atomique et contraintes réconciliées ; 122 tests réussis, 1 359 assertions. Sauvegarde restaurée, installation neuve et mise à niveau comparées sur MySQL ; détails dans EXPLOITATION_BASE_DONNEES.md.
- Archives ajoutées (DMA-035) : menu commun, recherche et filtres par unité/année/période de clôture, consultation en lecture seule et périmètres existants conservés ; 131 tests réussis, 1 481 assertions, migration locale appliquée après restauration sur copie ; rendu jour/nuit/mobile et navigation vérifiés sur données synthétiques.
- Clôture finale confiée au demandeur propriétaire (DMA-037) : visa et confirmation explicite sur Mes réceptions et sur la fiche, autorisation sous verrou et rôle réception en suivi ; historique/date/notifications/archives raccordés. 137 tests réussis, 1 570 assertions ; recette jour/nuit/mobile et clôture depuis la page vérifiées sur données synthétiques.
- Pages de connexion/inscription harmonisées (DMA-038) avec photo de raffinerie et coloration vert/orange d’origine conservées, jour/nuit/mobile et erreurs accessibles ; 141 tests réussis, 1 614 assertions, recette Chrome complète sur données synthétiques.
- Prochaine étape demandée : revoir le visa (DMA-039). Le pipeline de sauvegarde/restauration (DMA-036) reste prévu, en coordination avec DMA-034. Restent la protection/récupération des pièces jointes (DMA-023), la qualification des dossiers historiques (DMA-033), les accès par unité et les règles après refus.

## État des fonctionnalités

« Présent dans le code » ne signifie pas « testé et terminé ».

| Partie | État constaté | Reste à faire |
| --- | --- | --- |
| Connexion, utilisateurs et rôles | Espaces et accès couverts par tests isolés | Compléter la recette métier des comptes existants |
| Création et modification des demandes | Vérifiées avec et sans pièces jointes | Recette utilisateur |
| Circuit de validation | Transitions et affectations des structures testées | Recette métier et périmètres par unité |
| Travaux du service technique | Début et fin distincts, dates et compte rendu testés | Recette utilisateur |
| Clôture et réception | Le demandeur propriétaire réceptionne et clôture après travaux ; contrôle final possible avant sa confirmation, rôle réception en suivi | Recette métier |
| Tableaux de bord et notifications | Espaces harmonisés pour les neuf rôles, notifications de suivi vérifiées | Compléter la recette métier |
| Autres parties manquantes | À préciser | Recueillir les besoins avant de créer les tâches |

## Étapes vers la première livraison

| Étape | Tâches de référence | État | Condition de validation |
| --- | --- | --- | --- |
| Organiser le suivi | DMA-009 | Terminé | Fichiers de suivi créés et liens vérifiés |
| Définir les parties manquantes | DMA-001 | À préciser | Périmètre et circuit métier confirmés |
| Démarrer en local | DMA-002 | Terminé | Application accessible et procédure documentée |
| Corriger et compléter le parcours | DMA-003 à DMA-007 | À vérifier | Critères de chaque tâche vérifiés |
| Valider la première livraison | DMA-008 | À faire | Parcours complet vérifié, anomalies bloquantes corrigées |

Les statuts détaillés et les critères de fin se trouvent dans [SUIVI_PROJET.md](SUIVI_PROJET.md). Mettre ce résumé à jour quand une tâche change l’état d’une étape.

## Points à clarifier

- Quelles parties ne sont pas encore créées ?
- Quelles règles supplémentaires de périmètre métier doivent être appliquées aux équipes ? Chef : visibilité après exploitation ; clôture finale : confirmation du demandeur propriétaire après travaux (règle corrigée dans DMA-037).
- Quels scénarios doivent être opérationnels pour la première livraison ?

Le périmètre étant encore à préciser, aucun pourcentage global ni date de fin ne sont annoncés.

## Dernière séance

12 novembre 2026 : relance du serveur natif, revue du code/suivi/Git et contrôle MySQL en lecture seule. 28 utilisateurs, 34 demandes et huit fichiers référencés toujours absents ; 141 tests réussis, 1 614 assertions. Compte applicatif distinct de root, migrations sans attente, pages publiques HTTP 200. Revue dans [REVUE_PROJET.md](REVUE_PROJET.md). Consolidation publiée sur GitHub dans `43ae8dd` après demande explicite ; visa en prochaine étape fonctionnelle.
