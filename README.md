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

- entity (ext_translations)
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

- entity (for personal translations)
```
/**
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="YourEntityTranslation")
 */
class YourEntity
{
    /**
     * @ORM\OneToMany(targetEntity="YourEntityTranslation", mappedBy="object", cascade={"persist", "remove"})
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(YourEntityTranslation $newTranslation)
    {
        if($newTranslation->getContent())
        {
            $found = false;
            foreach($this->translations as $translation)
            {
                if(($translation->getLocale() === $newTranslation->getLocale()) && ($translation->getField() === $newTranslation->getField()))
                {
                    $found = true;
                    $translation->setContent($newTranslation->getContent());
                    break;
                }
            }
            
            if(!$found)
            {
                $newTranslation->setObject($this);
                $this->translations[] = $newTranslation;
            }
        }
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
