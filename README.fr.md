# Multi-site Configuration

[English documentation](README.md)

Multi-site Configuration (`multisite_config`) permet à des sites Drupal partageant
le même stockage de configuration active d'utiliser des valeurs propres à chaque
site. Il s'adresse aux installations multisites partageant une base de données
et une configuration commune.

## Fonctionnalités

- Lecture de la configuration commune avec des surcharges propres à chaque site.
- Enregistrement des configurations via la fabrique de configuration Drupal sous
  un préfixe propre au site.
- Initialisation des nouvelles configurations propres au site à partir des
  valeurs communes effectives, sans les propriétés de premier niveau `_core` et
  `uuid`.
- Séparation des compartiments de cache par clé de site grâce à des décorateurs
  de fabriques de cache.
- Suppression de la vérification standard des surcharges dans les formulaires de
  configuration pour les sites disposant d'une clé.

## Prérequis

- Les métadonnées du module déclarent une compatibilité Drupal `^10 || ^11`.
  La compatibilité avec chaque version ciblée reste à valider : l'implémentation
  utilise des hooks sous forme d'attributs (`Drupal\Core\Hook\Attribute\Hook`).
- La syntaxe du module nécessite PHP 8.2 ou ultérieur ; votre version de Drupal
  peut exiger une version plus récente.
- Les sites doivent déjà partager le même stockage de configuration active.
  Le module ne configure ni le routage multisite ni les connexions à la base.
- Aucune dépendance à un module contribué supplémentaire n'est déclarée. Drush
  est facultatif et sert aux exemples de commandes ci-dessous.

## Installation

1. Placez le répertoire `multisite_config` dans un répertoire de modules Drupal,
   par exemple `web/modules/custom/multisite_config`.
2. Activez le module sur l'installation partagée en précisant l'URI appropriée :

   ```sh
   drush --uri=https://main.example.org en multisite_config -y
   ```

Le module ne contient actuellement pas de `composer.json`. Ces instructions
utilisent une installation manuelle sans présumer l'existence d'un paquet
Composer publié.

## Configuration

Dans le fichier `settings.php` de chaque site, définissez une clé unique sous
forme de chaîne de caractères non vide :

```php
$settings['multisite_config_key'] = 'site_a';
```

Utilisez une autre clé pour un autre site :

```php
$settings['multisite_config_key'] = 'site_b';
```

Laissez ce paramètre non défini sur le site qui gère la configuration commune.
Le module ne possède pas de page d'administration spécifique.

Reconstruisez les caches de chaque site après l'activation du module ou la
modification de sa clé :

```sh
drush --uri=https://main.example.org cr
drush --uri=https://a.example.org cr
drush --uri=https://b.example.org cr
```

Conservez des clés stables : changer une clé sélectionne un autre préfixe de
configuration et un autre espace de cache, sans migrer les données existantes.

## Exemple d'utilisation

Supposons que la configuration commune `system.site` contienne le nom
`Shared site`. Sur le site dont la clé est `site_a`, ouvrez **Configuration >
Système > Informations du site** (`/admin/config/system/site-information`),
remplacez le nom du site par `Site A`, puis enregistrez.

Le code utilisant la fabrique de configuration suit le même fonctionnement :

```php
// Sur le site dont la clé est "site_a".
\Drupal::configFactory()
  ->getEditable('system.site')
  ->set('name', 'Site A')
  ->save();

$name = \Drupal::config('system.site')->get('name'); // Site A.
```

La configuration modifiable est enregistrée sous le nom `site_a.system.site`.
La lecture de `system.site` fusionne les valeurs enregistrées pour ce site avec
la configuration commune, en donnant priorité aux valeurs du site. Un site sans
clé continue à lire et modifier directement `system.site`.

La première copie modifiable est initialisée à partir des valeurs communes
effectives. L'enregistrement d'un formulaire peut donc sauvegarder davantage que
le seul champ modifié. Les valeurs de cette copie continuent à surcharger les
modifications ultérieures de la configuration commune.

Les noms des compartiments de cache reçoivent le suffixe `.multisite_site_a`
pour ce site lorsqu'ils sont obtenus via les fabriques de cache décorées.

## Périmètre et limites

- Le module remplace la fabrique de configuration et décore les fabriques de
  cache. Vérifiez sa compatibilité avec les autres modules intervenant sur ces
  services.
- Il n'isole pas les contenus, les utilisateurs, les sessions, les fichiers ou
  les tables de base de données en général. Il ne constitue pas une frontière de
  sécurité entre des clients indépendants.
- Le code accédant directement au stockage de configuration contourne le
  comportement de la fabrique.
- Les configurations préfixées résident dans le même stockage de configuration
  active. Le module ne fournit ni procédure dédiée d'export/import, ni adaptation
  des schémas de configuration, ni outil de migration pour ces données. Validez
  la synchronisation de configuration et les opérations sur les entités de
  configuration avant le déploiement.
- Le retrait d'une clé ou la désinstallation du module ne fournit pas de migration
  des configurations préfixées vers la configuration commune. Prévoyez leur
  nettoyage séparément.
- Le répertoire du module ne contient actuellement aucun test automatisé.

## Contribution et maintenance

Utilisez le gestionnaire de tickets et la procédure de contribution du dépôt qui
distribue ce module. Pour signaler un problème, indiquez les versions de Drupal,
de PHP et des modules concernés, des clés de site anonymisées, les étapes de
reproduction ainsi que les comportements attendu et constaté. Ne transmettez
jamais d'identifiants ni de fichier `settings.php` de production complet.

Les contributions doivent préciser leur impact sur la configuration commune,
les écritures propres à chaque site et la séparation des caches. Le module ne
fournit actuellement ni contact de maintenance ni URL d'assistance dédiée.

## Licence

Le fichier [LICENSE](LICENSE) contient la licence publique générale GNU,
version 3 (GPLv3).
