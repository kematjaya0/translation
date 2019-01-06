# Translation plugin for Symfony 4.2
1. install
   ```
   composer require kematjaya/translation
   ```
2. add to config/bundles.php
   ```
   Kematjaya\Translation\TranslationBundle::class => ['all' => true]
   ```
3. add to parameters in services.yml
   ```
   parameters:
     locale: 'en'
     # state code for translation, example : id (Indonesian) and en (English)
     app_locales: en|id
     locale_supported: ['en','id']
   ```
4. add to config/packages/framework.yml
   ```
   framework:
     translator: { fallback: '%locale%' }
   ```
5. import routing in config/routes.yml
   ```
   kematjaya:
     resource: '@TranslationBundle/Resources/config/routing/all.xml'
   ```
6. add to config/routes/annotations.yaml inside controllers tag : 
   ```
   controllers:
     resource: ../../src/Controller/
     type: annotation
     prefix: /{_locale}
       requirements:
         _locale: '%app_locales%'
       defaults:
         _locale: '%locale%'
   ```
7. if done, tour URL will be automatically redirect to URL with language, example ```http://localhost:8000/kmj/language``` to ```http://localhost:8000/en/kmj/language```
8. for add, edit, or delete translation, you can access URL ```http:host/kmj/language``` or view using console with execute command ```php bin/console debug:router```

thank you
