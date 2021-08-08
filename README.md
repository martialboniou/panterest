NOTES
=====

Based on [Créer un clone de Pinterest avec Symfony 5](https://www.youtube.com/watch?v=A8JxqOG2wi4&list=PLlxQJeQRaKDRs9WlWQiXNqWU0blyaZBzo&index=10) by *LES TEACHERS DU NET*; made with Symfony 5.2:

Part 1
------

### Conventions

- les noms des chemins avec des *dashes*; les noms des `name` (*càd* des *paths*) avec des *underscores*;
- utiliser les bonnes méthodes HTTP;
- ...

Part 5
------

```bash
composer require vich/uploader-bundle
```

with `Vich\UploaderBundle\Naming\SmartUniqueNamer`. Change the setter of imageFile (update date linked to our database with Doctrine so that the event listeners' calls should ensure the file is saved).

Check the method in a Type with `$options['method'] === 'PUT'` in `buildForm()` for example in order to match any form in the update case (and not the create one); then we might get our product thru `$options['data']` and check it (`$product && $product->getId()` to check the object as been *persisted*).

`pecl install imagick` (install `imagemagick` & `pcre2`; I add to link the header manually `ln -s /opt/homebrew/Cellar/pcre2/10.37_1/include/pcre2.h /opt/homebrew/Cellar/php/8.0.9/include/php/ext/pcre` for the build and restart Apache); en production, faire un *queue* avec Symfony Messenger.
Test with the following command at the project root:
`sc liip:imagine:cache:resolve uploads/pins/640x360-610e77314fe27642699988.jpg --filter=imagick`.

Put placeholders in `asset` and use `npm install file-loader@"^6.0.0" --save-dev` with a `copyFiles` configuration in `webpack.config.js`. `manifest.json` contains the alias for `asset()`.

Part 6
------

Use `symfony console security:encode-password` to encode the password.

Drop database with `symfony console d:d:d --force` in *dev* when you have a migration with non-null foreign key.

`symfony console psysh` is very useful to access our Symfony project by using the CLI:
- `use App\Entity\{User, Pin}`;
- `$em = $container->get('doctrine')->getManager();`;
- `$userRepo = $em->getRepository('App:User');` (or `(User::class)` as argument);
- `$user = $userRepo->find(2)` (to get the second user ie with `id` at 2);
- `$user2 = $userRepo->findOneBy(['email' => 'johndoe@example.com']);`;

Part 7
------

Les authentifications sont gérées par des *firewalls* (`dev` a une `security: false` pour la *debug bar*, le *profiler*...).

Rappel: `$request->query()` récupère le `GET`, `$request->request()` récupère le `POST`.

Bonne idée: on peut ne pas renvoyer un message explicite "Pas de compte trouvée" parce que ça informe un *hacker* de l'existence ou non d'un compte.

`$providerKey` dans la méthode `onAuthenticationSuccess()` de notre *authenticator* retourne le *firewall*.

`start()` dans `AbstractFormLoginAuthenticator` permet de renvoyer un utilisateur vers la page de *login* non-autorisé (elle peut être *customisée*).

`logout()` dans `SecurityController` n'est pas utilisé; elle est juste là pour définir une route. C'est le *firewall* qui gère (cf. `security.yaml`).

Part 8
------

Préférez `symfony console` plutôt que `php bin/console`; les variables d'environnement ne seront pas uniquement les variables locales de votre `.env`; indispensable dans un *container* Docker.

Pour accéder à l'utilisateur connecté hors contrôleur, injectez `Security` dans le constructeur de la classe pour l'attacher à une variable d'instance.

Injectez `SlugInterface` lorsque vous avez besoin d'un *slug*.

N'hésitez pas à utiliser `new EnglishInflector()` ou `new FrenchInflector()` pour singulariser certains mots.

Dans le formulaire `RegistrationType`, il y a une méthode `configureOptions()`; elle s'assure que `data_class` est bien à la valeur de `User::class` (on n'aurait plus besoin donc de lier un `$user = new User()` à notre `createForm()` en second argument); c'est utile pour les formulaires imbriqués.

NOTE: `RegistrationController::verifyUserEmail` should return a redirection to `app_home` on success (with the *success* flash).

Part 9
------

C'est une bonne idée d'écrire explicitement les types de champs dans les classes `*Type` de formulaire.

Un ajout de la protection *CSRF* pour la déconnexion; vérifiez la configuration `symfony console config:dump security`; regarder les injections possibles au niveau du *token* (`symfony console autowiring token`).

Ajouter `methods="POST"` dans les options de `@Route` de `app_logout` (dans `SecurityController`) puis ajouter ces deux *snippets*:

```yaml
# security.yaml
security:
    firewalls:
    (...)
        main:
            anonymous: true
            lazy: true
            provider: app_user_provider
            guard:
                authenticators:
                    - App\Security\LoginFormAuthenticator
            logout:
                # the UrlLogoutGenerator must implement this interface
                csrf_token_generator: Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
                path: app_logout
                # these two values are set by default (just here as documentation)
                csrf_parameter: _csrf_token
                csrf_token_id: logout
                # where to redirect after logout
                # target: app_any_route
            remember_me:
                secret: '%kernel.secret%'
            switch_user: true
```

```twig
{# navigation.html.twig #}
<li class="nav-item">
    <a class="nav-link" href="#"
    onclick="event.preventDefault(); document.getElementById('js-logout-form').submit();"
    >Logout</a>
</li>
<form id="js-logout-form" action="{{ path('app_logout') }}" method="POST" style="display: none;">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('logout') }}">
</form>
```

La version `GET` consiste à changer l'action par `{{ path('app_logout') }}?_csrf_token={{ csrf_token('logout') }}`; on enlève donc le formulaire et on change le `href` pour la valeur précédemment citée pour l'action.

Enfin, avec la même configuration `security.yaml` (important pour le `csrf_token_generator`), vous pouvez utiliser dans le `href`, `{{ logout_path('main') }}`: **cela ajoute automatiquement la validation CSRF en méthode** `GET`.

Utilisez `DoctrineFixtureBundle` pour générer des *fixtures* (`foundry` ou `mockaroo`). `mockaroo` est utilisé dans la démo de l'application d'Honoré (`template`: champ libre, `sentences` pour former un titre (1 à 2), `paragraphs` pour former une *text area* (3 à 5)...).

Ne pas surcharger `.env` et utiliser `services.yaml` pour mettre les paramètres globaux **qui ne changent pas d'une machine à une autre** (préfixés par `app.`), puis utilisez `$this->getParameter(...)` dans votre contrôleur.
Une constante qui change peu peut aussi être définie dans une entité.

Le système de gestion de secret de Symfony est à privilégier pour les **configurations sensibles**.

Part 10
-------

Les contraintes doivent être obligatoirement ajoutés au niveau du formulaire `*Type` **lorsqu'il n'y a pas de lien avec une entité**.

Dans `ResetPasswordRequestFormType`, pensez à ajouter une contrainte sur l'`Email`. Notez que `$form->get('email')->getData()` est équivalent à `$form['email']->getData()`.

`eraseCredentials()` dans `UserInterface` permet d'implémenter l'effacement d'informations sensibles stockés dans une entité (mais pas stocké en base).

Nous pouvons choisir d'ajouter des champs à un formulaire qui ne sera pas actif par défaut et *forcer* leur utilisation dans certaines pages: `createForm(..., null, ['current_password_is_required' => true])` avec une option créée et passée en **troisième argument**. Pour celà, on ajoute dans le formulaire:

```php
public function configureOptions(OptionsResolver $resolver): void
{
  $resolver->setDefaults([
    'current_password_is_required' => false,
  ]);
}
```

Ensuite, on filtre pour ajouter au `$builder` les champs selon l'état de notre booléen (on peut *typer* l'option avec `$resolver->setAllowedTypes('current_password_is_required', 'bool');`).
