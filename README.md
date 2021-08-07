NOTES
=====

Based on [Créer un clone de Pinterest avec Symfony 5](https://www.youtube.com/watch?v=A8JxqOG2wi4&list=PLlxQJeQRaKDRs9WlWQiXNqWU0blyaZBzo&index=10) by *LES TEACHERS DU NET*; made with Symfony 5.2:

Part 5
------

```bash
composer require vich/uploader-bundle
```

with `Vich\UploaderBundle\Naming\SmartUniqueNamer`. Change the setter of imageFile (update date linked to our database with Doctrine so that the event listeners' calls should ensure the file is saved).
