# Translation plugin for Symfony 4.2
1. install
   ```
   composer require kematjaya/translation
   ```
2. add to config/bundles.php
   ```
   Kematjaya\Translation\TranslationBundle::class => ['all' => true]
   ```
3. run command for automatically configuration
   ```
   php bin/console kematjaya:translation:configure en
   ```
   en is language code. Separate with comma "," character if more than one

4. if done, tour URL will be automatically redirect to URL with language, example ```http://localhost:8000/kmj/language``` to ```http://localhost:8000/en/kmj/language```
5. for add, edit, or delete translation, you can access URL ```http:host/kmj/language``` or view using console with execute command ```php bin/console debug:router```

thank you
