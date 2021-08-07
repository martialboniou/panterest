NOTES
=====

Based on [Créer un clone de Pinterest avec Symfony 5](https://www.youtube.com/watch?v=A8JxqOG2wi4&list=PLlxQJeQRaKDRs9WlWQiXNqWU0blyaZBzo&index=10) by *LES TEACHERS DU NET*; made with Symfony 5.2:

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
