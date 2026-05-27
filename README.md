# Open Demat Admin Bundle

Bundle Symfony d'administration pour Open Demat.

## Installation

Le package Composer du bundle est :

```bash
composer require open-demat/admin-bundle
```

Dans l'application Open Demat, les bundles de type `open-demat-bundle` sont installés dans `app_open_demat/`.

## Conventions

- Package Composer : `open-demat/admin-bundle`
- Type Composer : `open-demat-bundle`
- Namespace PHP : `OpenDemat\AdminBundle`
- Routes admin : `open_demat_admin_*`
- Templates Twig : `@OpenDemat/admin-bundle/...`

## Fonctionnalités

- Tableau de bord d'administration
- Gestion des utilisateurs et des rôles
- Administration des pièces jointes
- CRUD générique des référentiels
- CRUD générique des entités de process déclarées dans le registre

## Licence

Ce projet est distribué sous licence GNU Affero General Public License v3.0
ou ultérieure, avec une exception spécifique pour les bundles/plugins.

Le cœur du logiciel reste libre : si vous modifiez le cœur et que vous le
redistribuez ou le rendez accessible à des utilisateurs via un réseau, vous devez
rendre disponible le code source correspondant de cette version modifiée.

Les bundles, plugins ou modules développés via les API publiques d’extension
documentées peuvent rester privés ou être distribués sous une autre licence,
conformément à l’exception décrite dans `LICENSE-EXCEPTION.md`.

Voir :

- `LICENSE`
- `LICENSE-EXCEPTION.md`
- `NOTICE.md`

## Exception pour les bundles

Le cœur du projet est distribué sous licence AGPLv3.

Par exception, les bundles, plugins ou modules Symfony développés pour des besoins
internes peuvent rester privés ou être distribués sous une autre licence, à condition
qu’ils interagissent avec le cœur uniquement via les API publiques d’extension
documentées.

Cette exception ne couvre pas les modifications du cœur, ni le code copié depuis
le cœur vers un bundle. Toute modification du cœur reste soumise à l’AGPLv3.

## Support commercial

Un support professionnel est disponible pour :

- installation et configuration ;
- maintenance corrective et évolutive ;
- hébergement managé ;
- intégration avec LDAP, CAS, SAML, API métiers ;
- développement de bundles spécifiques ;
- formation administrateurs et utilisateurs ;
- accompagnement au déploiement.

Contact : contact@example.com

## Configuration

```yaml
open_demat_admin:
  organization:
    name: 'Mon organisation'
    logo: 'assets/img/organization-logo.svg'
```

Le `logo` est un chemin d'asset public. S'il est vide, l'interface affiche une icône générique.
