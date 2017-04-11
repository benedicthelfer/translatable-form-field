# translatable-form-field
This bundle is responsible for translatable form fields in symfony2 / sonata admin.

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
    default_locale: en_GB
    locales: ['de_DE', 'en_GB', 'es_ES', 'fr_FR', 'hu_HU', 'ru_RU', 'sv_SE']
    templating: 'BnhTranslatableFieldBundle:FormType:bnhtranslations.html.twig'
```

- entity
```
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
// ...

class YourEntity implements Translatable
{
    /**
     * @Gedmo\Translatable
     * @ORM\Column(...
     */
    private $fieldname;

    public function setfieldname($fieldname)
    {
        $this->fieldname = $fieldname;
        return $this;
    }

    /**
     * @Gedmo\Locale
     */
    private $locale;

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
```

- sonata admin page
```
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('fieldname', 'bnhtranslations');
    }
```
