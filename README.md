# translatable-form-field
This bundle is responsible for translatable form fields in symfony2 / sonata admin.

Keep in mind, that this bundle is under development, not for production use!

Usage:

- add to 'AppKernel'
```
    public function registerBundles()
    {
        $bundles = array(
        // ...
        new Bnh\TranslatableFieldBundle\BnhTranslatableFieldBundle()
        );
    }
```
- config
```
    bnh_translatable_field:
    locales: ['de_DE', 'en_GB', 'es_ES', 'fr_FR', 'hu_HU', 'ru_RU', 'sv_SE']
    templating: 'BnhTranslatableFieldBundle:FormType:bnhtranslations.html.twig'
```
- in sonata admin page
```
    protected function configureFormFields(FormMapper $formMapper)
    {
        $objectClass = $this->getClass();
        $id = $this->getSubject()->getId();
        
        $formMapper->add('fieldname', 'bnhtranslations', array('translation_data_class' => $objectClass, 'object_id' => $id));
    }
```
