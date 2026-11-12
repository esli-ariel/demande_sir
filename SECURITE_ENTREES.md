# Requêtes et entrées HTTP — contrôle du 2026-11-11

Le projet utilise actuellement des routes Laravel web, également appelables en JSON. Les règles s’appliquent côté serveur à ces deux usages ; aucune API séparée n’a été créée.

## Requêtes paramétrées

Les valeurs provenant des utilisateurs passent par Eloquent et le Query Builder, qui effectuent les liaisons PDO. Le connecteur installé désactive par défaut l’émulation des requêtes préparées ; la configuration du projet ne réactive pas cette option.

Les noms de tables, colonnes, tris et opérateurs sont définis dans le code. La construction d’un motif de recherche PHP comme `'%'.$search.'%'` ne concatène pas la recherche au SQL : ce motif reste un paramètre lié.

Les expressions `selectRaw` restantes sont des agrégats SQL constants. Le calcul mensuel choisit explicitement une expression constante selon le pilote de base, sans interpolation d’entrée HTTP. Les `DB::unprepared` des anciennes migrations créent ou suppriment un trigger à partir de SQL statique ; ils ne reçoivent pas de données de requête.

Les tests injectent des fragments SQL dans les recherches demandeur, admin, personnel et utilisateurs : ils vérifient leur présence dans les paramètres liés, leur absence du texte SQL et le respect du périmètre du demandeur. Un fragment SQL dans un motif de DMA est stocké comme donnée ordinaire, sans exécution.

## Schémas et limites

- `DemandePayloadRequest` contrôle les créations et modifications avant le contrôleur : autorisation, champs scalaires, longueurs, listes et fichiers. Le contrôleur ne persiste que les champs validés et explicitement autorisés.
- Champs courts : 255 caractères ; motif et situations : 10 000 caractères.
- Structures : liste de 100 identifiants existants maximum, sans doublon. Affectations des comptes spécialistes également bornées et vérifiées.
- Pièces jointes : liste de 10 vrais fichiers maximum, 5 Mo par fichier, formats PDF/Word/JPG/PNG. Un chemin transmis en texte n’est pas un fichier accepté.
- Dates de travaux : format `Y-m-d`, plage compatible avec MySQL ; date de fin comparée au début conservé en base sous verrou. La validation de format précède la transaction de travail.
- Recherches : 150 caractères maximum ; pagination : entier entre 1 et 100 000 ; filtres de vues et catégories contrôlés, rôles scalaires vérifiés en base.
- Paramètres de route : identifiants numériques pour les DMA, utilisateurs et pièces ; format UUID pour les notifications. Les autorisations et le rattachement à l’utilisateur restent vérifiés séparément.
- Authentification : types et longueurs explicites pour emails, mots de passe et jetons ; octet nul refusé dans les mots de passe avant le hachage ; case « Se souvenir de moi » normalisée et validée. Les mots de passe sont bornés à 4 096 caractères en entrée ; la politique du hacheur reste celle de Laravel.

Les champs métier sensibles comme le propriétaire, le statut et les rôles de l’inscription publique ne sont pas librement attribuables depuis les corps de requête. Les champs non utilisés sont ignorés ; la validation ne remplace pas les permissions.

Une entrée invalide dans une requête JSON reçoit HTTP 422 avec les erreurs de validation. Les formulaires HTML conservent les redirections et messages Laravel. Une adresse de ressource au format incorrect ne correspond pas à une route (HTTP 404).

## Vérifications et périmètre

Les tests vérifient aussi l’absence d’écriture de DMA, fichiers et notifications quand la validation échoue, les types imbriqués inattendus, les limites de fichiers, les faux chemins de fichier, la confirmation de mot de passe, la suppression de compte et les dates incorrectes. Ils utilisent une base isolée, sans modifier les données métier existantes.

Toute nouvelle route doit définir son schéma et utiliser uniquement ses valeurs validées. Ne pas transformer une donnée HTTP en nom de colonne ou fragment SQL brut. Références : [Query Builder Laravel](https://laravel.com/framework/docs/queries) et [validation Laravel](https://laravel.com/framework/docs/validation).
