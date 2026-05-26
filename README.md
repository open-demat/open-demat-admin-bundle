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
